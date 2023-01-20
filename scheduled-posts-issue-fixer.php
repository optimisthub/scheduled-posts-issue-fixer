<?php
/**
 * Plugin Name:     Scheduled Posts Issue Fixer
 * Plugin URI:      https://github.com/optimisthub/scheduled-posts-issue-fixer
 * Description:     This plugin does one thing and does it well: it fixes the missed schedule error and triggers your scheduled posts to publish on time. We’ve developed this post scheduler plugin with performance in mind, so it won’t affect the speed or performance of your website.
 * Author:          @optimisthub
 * Author URI:      https://optimisthub.com
 * Text Domain:     scheduled-posts-issue-fixer 
 * Version:         1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.1.1
 * Requires PHP: 7.1
 * License: GPLv2
 */

class ScheduledPostsIssueFixer
{

    const CRON_NAME = 'scheduled_posts_issue_fixed';
    const CRON_TIME = 'every_minute';
    const SQL_ROW_LIMIT = 20;

    public function __construct()
    {
        register_activation_hook(__FILE__,[$this, 'registerCron']);
        register_deactivation_hook(__FILE__,[$this, 'deRegisterCron']);
        add_action(self::CRON_NAME, [$this, 'publishPosts']);
    }

    public function registerCron() 
    {
        if (! wp_next_scheduled(self::CRON_NAME )) 
        {
            wp_schedule_event(time(), self::CRON_TIME, self::CRON_NAME);
        }
    }

    public function deRegisterCron()
    {
        wp_clear_scheduled_hook(CRON_NAME);
    }

    public function publishPosts()
    {
        global $wpdb;

        $currentTime = current_time( 'mysql', 0 );

        $postIds = $wpdb->get_col(
            $wpdb->prepare
            (
                "SELECT ID FROM {$wpdb->posts} WHERE post_date <= %s AND post_status='future' LIMIT %d", $currentTime, self::SQL_ROW_LIMIT 
            ) 
        );

        if ( ! count( $postIds ) )
        {
            return;
        }

        array_map( 'wp_publish_post', $postIds );
    }

}

new ScheduledPostsIssueFixer();
