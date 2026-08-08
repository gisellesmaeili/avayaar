<?php
/**
 * Plugin Name: آوایار
 * Description: دستیار استعدادیابی موسیقی
 * Version: 1.0.8
 * Text Domain: avayaar
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AVAYAAR_VERSION', '1.0.8' );
define( 'AVAYAAR_PATH', plugin_dir_path( __FILE__ ) );
define( 'AVAYAAR_URL', plugin_dir_url( __FILE__ ) );
define( 'AVAYAAR_ACADEMY_PHONE', '01132331323' );

require_once AVAYAAR_PATH . 'includes/class-avayaar-install.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-admin-menu.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-leads-list-table.php';
require_once AVAYAAR_PATH . 'includes/data/class-avayaar-questions.php';
require_once AVAYAAR_PATH . 'includes/data/class-avayaar-recommendations.php';
require_once AVAYAAR_PATH . 'includes/data/class-avayaar-family-weights.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-scoring.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-ajax.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-shortcode.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-admin-ajax.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-export.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-archetypes.php';
require_once AVAYAAR_PATH . 'includes/class-avayaar-elementor-integration.php';

register_activation_hook( __FILE__, array( 'AVAYAAR_Install', 'activate' ) );

add_action( 'plugins_loaded', function() {
    new AVAYAAR_Admin_Menu();
    new Avayaar_Ajax();
    new Avayaar_Shortcode();
    new Avayaar_Admin_Ajax();
    new Avayaar_Export();
    new Avayaar_Elementor_Integration();
} );