=== Scheduled Posts Issue Fixer ===
Contributors: optimisthub, fatih-toprak
Tags: missed schedule, scheduled posts, publish scheduled posts, cron, post scheduler
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fixes the WordPress "missed schedule" error. Posts, pages and custom post types that miss their scheduled time are published automatically by a lightweight cron job.

== Description ==

WordPress relies on WP-Cron to publish scheduled posts. On low-traffic sites, or when WP-Cron is delayed by caching, a post can be left showing the "missed schedule" error and never goes live.

Scheduled Posts Issue Fixer solves this. It registers a cron job that runs every minute, finds posts whose scheduled time has passed but which are still stuck in the `future` status, and publishes them using WordPress's own publishing functions.

**Why it is safe for performance**

* Processes at most 20 posts per run, so it never loads your whole post table.
* Uses an indexed query against `post_date_gmt` and `post_status`.
* Returns immediately when there is nothing to publish.
* Removes its cron job when the plugin is deactivated or uninstalled.

**Features**

* Publishes overdue posts, pages and custom post types automatically
* No settings screen, works as soon as it is activated
* Uses `wp_publish_post()`, so normal publishing hooks still fire for caching, SEO and notification plugins
* Filterable batch size and post types
* Cleans up its cron event on deactivation and uninstall

== Installation ==

### INSTALL "Scheduled Posts Issue Fixer" FROM WITHIN WORDPRESS

1. Visit the plugins page within your dashboard and select 'Add New';
2. Search for 'Scheduled Posts Issue Fixer';
3. Activate Scheduled Posts Issue Fixer from your Plugins page;
4. You are done.

### INSTALL "Scheduled Posts Issue Fixer" MANUALLY

1. Upload the 'scheduled-posts-issue-fixer' folder to the /wp-content/plugins/ directory;
2. Activate the Scheduled Posts Issue Fixer through the 'Plugins' menu in WordPress;
3. You are done.

== Frequently Asked Questions ==

= I have activated the plugin, why can't I see it in the admin panel? =

Scheduled Posts Issue Fixer is a "set and forget" plugin. There are no settings. Your scheduled posts are checked automatically once the plugin is active.

= Does it work with custom post types? =

Yes. By default every post type is checked. You can limit it to specific types with the `scheduled_posts_issue_fixer_post_types` filter:

`
add_filter( 'scheduled_posts_issue_fixer_post_types', function ( $types ) {
    return array( 'post', 'page', 'product' );
} );
`

= How many posts are published at a time? =

20 per run by default. Change it with the `scheduled_posts_issue_fixer_batch_size` filter (maximum 500).

= My site uses a real server cron. Will this still work? =

Yes. The plugin schedules a standard WP-Cron event, so it works with both WP-Cron and a system cron that calls `wp-cron.php`.

= Can I exclude a specific post? =

Yes, use the `scheduled_posts_issue_fixer_should_publish` filter:

`
add_filter( 'scheduled_posts_issue_fixer_should_publish', function ( $should_publish, $post ) {
    if ( 123 === $post->ID ) {
        return false;
    }
    return $should_publish;
}, 10, 2 );
`

= Does deactivating the plugin remove the cron job? =

Yes, on deactivation and on uninstall.

== Changelog ==

= 2.0.0 =

**Fixed**

* Fixed a fatal error on deactivation: `deRegisterCron()` called `wp_clear_scheduled_hook( CRON_NAME )` with an undefined bare constant instead of `self::CRON_NAME`. On PHP 8 this threw `Error: Undefined constant "CRON_NAME"` and left the cron job behind permanently.
* Fixed the cron job never actually running on a schedule. The plugin registered the interval name `every_minute`, which is not a valid WordPress cron schedule, so no working event was created. A real every-minute schedule is now registered.
* Fixed the cron job not being created when the plugin was updated rather than freshly activated, because activation hooks do not run on update.
* Fixed the timezone handling. The query now compares against `post_date_gmt` in GMT instead of applying `gmt_offset` twice.

**Changed**

* Rewritten as a namespaced, PSR-4 autoloaded plugin (`OptimistHub\ScheduledPostsIssueFixer`).
* Posts are now published through `wp_publish_post()` with a fresh post object, and each post's status is re-checked before publishing, so posts edited between the query and the update are not published by mistake.
* Added an `uninstall.php` that removes the cron event and the legacy cron event name.
* Added filters: `scheduled_posts_issue_fixer_batch_size`, `scheduled_posts_issue_fixer_post_types` and `scheduled_posts_issue_fixer_should_publish`.
* Requires WordPress 6.0 and PHP 7.4 or newer.

= 1.0.10 =

* gmt_offset issue fixed.

= 1.0.9 - 1.0.5 =

* Github automatization completed.

= 1.0.4 =

* Github readme file update and assets added.

= 1.0.3 =

* Github action.

= 1.0.2 =

* Plugin creator alias issue.

= 1.0.1 =

* Manual installation and activation details added to readme setup

= 1.0.0 =

* Stable version released

== Upgrade Notice ==

= 2.0.0 =

Fixes a fatal error on deactivation and a broken cron schedule that could stop your scheduled posts from publishing. Upgrading is strongly recommended.
