<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Elementor_Integration {

    public function __construct() {
        // This action only ever fires if Elementor is active, so it's
        // safe to hook unconditionally — harmless no-op otherwise.
        add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
    }

    public function register_widget( $widgets_manager ) {
        require_once AVAYAAR_PATH . 'includes/class-avayaar-elementor-widget.php';
        $widgets_manager->register( new Avayaar_Elementor_Widget() );
    }
}