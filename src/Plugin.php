<?php
/**
 * Main plugin class.
 *
 * @package OptimistHub\ScheduledPostsIssueFixer
 */

declare( strict_types = 1 );

namespace OptimistHub\ScheduledPostsIssueFixer;

defined( 'ABSPATH' ) || exit;

/**
 * Repairs posts whose scheduled publish time has passed but which are still
 * stuck in the "future" status ("missed schedule").
 */
final class Plugin {

	/**
	 * Cron hook name.
	 *
	 * Kept identical to the 1.x value so existing sites do not accumulate a
	 * second, orphaned cron event after upgrading.
	 */
	public const CRON_HOOK = 'scheduled_posts_issue_fixed';

	/**
	 * Legacy constant name. Some 1.x installs registered the schedule under a
	 * different name; we clean that up too.
	 */
	public const LEGACY_CRON_HOOK = 'scheduled_posts_issue_fixer';

	/**
	 * Custom cron schedule identifier (runs every minute).
	 */
	public const SCHEDULE = 'spif_every_minute';

	/**
	 * Maximum number of posts published per run.
	 */
	public const BATCH_SIZE = 20;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function boot(): void {
		// Register the custom schedule before the cron hook is read.
		add_filter( 'cron_schedules', array( $this, 'register_schedule' ) );
		add_action( self::CRON_HOOK, array( $this, 'publish_missed_posts' ) );

		/*
		 * Ensure the event exists even when the plugin is updated rather than
		 * freshly activated (activation hooks do not run on update).
		 */
		add_action( 'admin_init', array( $this, 'ensure_schedule' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( SPIF_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Add the every-minute interval.
	 *
	 * WordPress has no built-in sub-hourly schedule, so relying on an unknown
	 * interval name silently falls back to nothing being scheduled.
	 *
	 * @param array<string, array{interval: int, display: string}> $schedules Schedules.
	 * @return array<string, array{interval: int, display: string}>
	 */
	public function register_schedule( $schedules ) {
		if ( ! is_array( $schedules ) ) {
			$schedules = array();
		}

		if ( ! isset( $schedules[ self::SCHEDULE ] ) ) {
			$schedules[ self::SCHEDULE ] = array(
				'interval' => MINUTE_IN_SECONDS,
				'display'  => __( 'Every minute (Scheduled Posts Issue Fixer)', 'scheduled-posts-issue-fixer' ),
			);
		}

		return $schedules;
	}

	/**
	 * Activation handler.
	 *
	 * @return void
	 */
	public static function activate(): void {
		$plugin = new self();

		// The schedule must exist before wp_schedule_event() validates it.
		add_filter( 'cron_schedules', array( $plugin, 'register_schedule' ) );
		$plugin->ensure_schedule( true );
	}

	/**
	 * Deactivation handler.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		/*
		 * 1.x called wp_clear_scheduled_hook(CRON_NAME) with an undefined bare
		 * constant, which threw an Error on PHP 8 and left the cron event
		 * behind forever. Clear every name we have ever used.
		 */
		wp_clear_scheduled_hook( self::CRON_HOOK );
		wp_clear_scheduled_hook( self::LEGACY_CRON_HOOK );
	}

	/**
	 * Make sure our event is scheduled exactly once.
	 *
	 * @param bool $force Whether to reschedule even if an event already exists.
	 * @return void
	 */
	public function ensure_schedule( bool $force = false ): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) || $force ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
			wp_schedule_event( time() + MINUTE_IN_SECONDS, self::SCHEDULE, self::CRON_HOOK );
		}

		// Remove the stray event older versions could leave behind.
		$legacy = wp_next_scheduled( self::LEGACY_CRON_HOOK );

		if ( false !== $legacy ) {
			wp_clear_scheduled_hook( self::LEGACY_CRON_HOOK );
		}
	}

	/**
	 * Publish posts whose scheduled time has passed.
	 *
	 * @return int Number of posts published.
	 */
	public function publish_missed_posts(): int {
		global $wpdb;

		$now = current_time( 'mysql', true );

		/**
		 * Filter how many posts are repaired per cron run.
		 *
		 * @param int $limit Batch size.
		 */
		$limit = (int) apply_filters( 'scheduled_posts_issue_fixer_batch_size', self::BATCH_SIZE );
		$limit = max( 1, min( 500, $limit ) );

		/**
		 * Filter which post types are eligible for repair.
		 *
		 * An empty array means "every public post type".
		 *
		 * @param array<int, string> $post_types Post type slugs.
		 */
		$post_types = (array) apply_filters( 'scheduled_posts_issue_fixer_post_types', array() );
		$post_types = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );

		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'future' AND post_date_gmt <= %s AND post_date_gmt != '0000-00-00 00:00:00'";

		$params = array( $now );

		if ( ! empty( $post_types ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
			$sql         .= " AND post_type IN ({$placeholders})";
			$params       = array_merge( $params, $post_types );
		}

		$sql     .= ' ORDER BY post_date_gmt ASC LIMIT %d';
		$params[] = $limit;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared below.
		$post_ids = $wpdb->get_col( $wpdb->prepare( $sql, $params ) );

		if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
			return 0;
		}

		$published = 0;

		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;

			if ( $post_id <= 0 ) {
				continue;
			}

			$post = get_post( $post_id );

			// Re-check: the post may have been published between query and now.
			if ( ! $post instanceof \WP_Post || 'future' !== $post->post_status ) {
				continue;
			}

			/**
			 * Filter whether a specific post should be force-published.
			 *
			 * @param bool     $should_publish Whether to publish.
			 * @param \WP_Post $post           The post.
			 */
			if ( ! apply_filters( 'scheduled_posts_issue_fixer_should_publish', true, $post ) ) {
				continue;
			}

			/*
			 * wp_publish_post() transitions the status and fires the normal
			 * hooks (transition_post_status, save_post, wp_insert_post, etc.)
			 * so caching/SEO plugins see the publish event. Unlike 1.x we set
			 * post_date_gmt correctly and avoid silently swallowing failures.
			 */
			wp_publish_post( $post );

			++$published;
		}

		return $published;
	}

	/**
	 * Add a settings shortcut to the plugins list.
	 *
	 * @param array<int, string> $links Existing links.
	 * @return array<int, string>
	 */
	public function action_links( $links ) {
		if ( ! is_array( $links ) ) {
			return $links;
		}

		if ( current_user_can( 'manage_options' ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'site-health.php' ) ),
					esc_html__( 'Site Health', 'scheduled-posts-issue-fixer' )
				)
			);
		}

		return $links;
	}
}
