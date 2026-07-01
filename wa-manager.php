<?php
/**
 * Plugin Name:       WA Manager – WhatsApp Per Page
 * Plugin URI:        https://github.com/aqdushammad/wa-manager
 * Description:       Assign different WhatsApp numbers to different pages with a beautiful floating chat widget. Auto-updates from GitHub.
 * Version:           1.1.1
 * Author:            Aqdus Hammad
 * Author URI:        https://github.com/aqdushammad
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wa-manager
 * Requires at least: 5.6
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WAM_VERSION',      '1.1.1' );
define( 'WAM_PLUGIN_DIR',   plugin_dir_path( __FILE__ ) );
define( 'WAM_PLUGIN_URL',   plugin_dir_url( __FILE__ ) );
define( 'WAM_PLUGIN_FILE',  __FILE__ );
define( 'WAM_PLUGIN_SLUG',  'wa-manager' );
define( 'WAM_GITHUB_USER',  'aqdushammad' );
define( 'WAM_GITHUB_REPO',  'wa-manager' );
define( 'WAM_DB_VERSION',   '3' );

require_once WAM_PLUGIN_DIR . 'includes/class-wam-db.php';
require_once WAM_PLUGIN_DIR . 'includes/class-wam-admin.php';
require_once WAM_PLUGIN_DIR . 'includes/class-wam-frontend.php';
require_once WAM_PLUGIN_DIR . 'includes/class-wam-updater.php';

register_activation_hook( __FILE__,   array( 'WAM_DB', 'install' ) );
register_deactivation_hook( __FILE__, array( 'WAM_DB', 'deactivate' ) );

/**
 * Run DB upgrade check on every load (safe — dbDelta only alters if needed).
 */
function wam_maybe_upgrade_db() {
    if ( get_option( 'wam_db_version' ) !== WAM_DB_VERSION ) {
        WAM_DB::install();
    }
}
add_action( 'plugins_loaded', 'wam_maybe_upgrade_db', 5 );

function wam_init() {
    new WAM_Admin();
    new WAM_Frontend();
    new WAM_Updater( WAM_PLUGIN_FILE, WAM_GITHUB_USER, WAM_GITHUB_REPO, WAM_VERSION );
}
add_action( 'plugins_loaded', 'wam_init' );
