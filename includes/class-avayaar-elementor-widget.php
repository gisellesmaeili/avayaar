<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'avayaar_quiz'; }
    public function get_title() { return 'آوایار - آزمون استعدادیابی'; }
    public function get_icon() { return 'eicon-form-horizontal'; }
    public function get_categories() { return array( 'general' ); }

    protected function render() {
        // Reuses the shortcode's own render/enqueue logic rather than
        // duplicating it — one code path for both delivery methods.
        echo do_shortcode( '[avayaar]' );
    }
}