<?php
/**
 * Plugin Name: آوایار
 * Description: دستیار استعدادیابی موسیقی
 * Version: 1.0.1
 * Text Domain: avayaar
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AVAYAAR_VERSION', '1.0.0' );
define( 'AVAYAAR_PATH', plugin_dir_path( __FILE__ ) );
define( 'AVAYAAR_URL', plugin_dir_url( __FILE__ ) );

require_once AVAYAAR_PATH . 'includes/class-avayaar-install.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-admin-menu.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-leads-list-table.php';
require_once AVAYAAR_PATH . 'includes/data/class-avayaar-questions.php';
require_once AVAYAAR_PATH . 'includes/data/class-avayaar-recommendations.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-scoring.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-ajax.php';

register_activation_hook( __FILE__, array( 'AVAYAAR_Install', 'activate' ) );

add_action( 'plugins_loaded', function() {
    new AVAYAAR_Admin_Menu();
    new Avayaar_Ajax();
} );