<?php
/**
 * Plugin Name:       Scheduled Posts Issue Fixer
 * Plugin URI:        https://github.com/optimisthub/scheduled-posts-issue-fixer
 * Description:       Fixes the "missed schedule" error and publishes your scheduled posts, pages and custom post types on time. Lightweight and performance friendly.
 * Version:           2.0.0
 * Requires at least: 6.0
 * Tested up to:      7.1
 * Requires PHP:      7.4
 * Author:            Optimist Hub
 * Author URI:        https://optimisthub.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       scheduled-posts-issue-fixer
 * Domain Path:       /languages
 *
 * @package OptimistHub\ScheduledPostsIssueFixer
 */

declare( strict_types = 1 );

namespace OptimistHub\ScheduledPostsIssueFixer;

defined( 'ABSPATH' ) || exit;

define( 'SPIF_VERSION', '2.0.0' );
define( 'SPIF_FILE', __FILE__ );
define( 'SPIF_DIR', plugin_dir_path( __FILE__ ) );

if ( is_readable( SPIF_DIR . 'vendor/autoload.php' ) ) {
	require_once SPIF_DIR . 'vendor/autoload.php';
} else {
	spl_autoload_register(
		static function ( $class ) {
			$prefix = __NAMESPACE__ . '\\';

			if ( 0 !== strpos( $class, $prefix ) ) {
				return;
			}

			$relative = substr( $class, strlen( $prefix ) );
			$path     = SPIF_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';

			if ( is_readable( $path ) ) {
				require_once $path;
			}
		}
	);
}

require_once SPIF_DIR . 'src/Plugin.php';

register_activation_hook( __FILE__, array( Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Plugin::class, 'deactivate' ) );

/**
 * Boot the plugin.
 *
 * @return Plugin
 */
function plugin() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin();
		$instance->boot();
	}

	return $instance;
}

plugin();
