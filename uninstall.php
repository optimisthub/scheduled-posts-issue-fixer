<?php
/**
 * Uninstall routine.
 *
 * Removes the cron event the plugin registered so no orphaned (and therefore
 * constantly failing) scheduled task is left behind.
 *
 * @package OptimistHub\ScheduledPostsIssueFixer
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'scheduled_posts_issue_fixed' );
wp_clear_scheduled_hook( 'scheduled_posts_issue_fixer' );
