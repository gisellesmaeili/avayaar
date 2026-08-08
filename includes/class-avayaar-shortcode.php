<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Shortcode {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
        add_shortcode( 'avayaar', array( $this, 'render' ) );
    }

    public function register_assets() {
        wp_register_style( 'avayaar-quiz', AVAYAAR_URL . 'assets/css/quiz.css', array(), AVAYAAR_VERSION );
        wp_register_script( 'avayaar-quiz', AVAYAAR_URL . 'assets/js/quiz.js', array(), AVAYAAR_VERSION, true );
    }

    public function render( $atts ) {
        // Enqueuing here (inside the shortcode callback) rather than via
        // has_shortcode() on post_content is deliberate: Elementor stores
        // shortcode widgets in the _elementor_data JSON postmeta, not in
        // post_content, so a has_shortcode() pre-check would silently miss
        // the assets on any Elementor-built page. Enqueuing at render time
        // works regardless of how the shortcode got onto the page.
        wp_enqueue_style( 'avayaar-quiz' );
        wp_enqueue_script( 'avayaar-quiz' );

        wp_localize_script( 'avayaar-quiz', 'AvayaarData', array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'avayaar_submit' ),
            'rhythm'       => Avayaar_Questions::get_rhythm_patterns(),
            'ear'          => Avayaar_Questions::get_ear_stage(),
            'style'        => Avayaar_Questions::get_style_clips(),
            'personality'  => Avayaar_Questions::get_personality_questions(),
            'mood'         => Avayaar_Questions::get_mood_options(),
            'ageRanges'    => Avayaar_Questions::get_age_ranges(),
            'musicHistory' => Avayaar_Questions::get_music_history_options(),
            'instruments'  => Avayaar_Questions::get_instrument_options(),
            'whyMusic'     => Avayaar_Questions::get_why_music_options(),
            'rhythmBank'   => Avayaar_Questions::get_rhythm_bank(),
            'earBank'      => Avayaar_Questions::get_ear_bank(),
            'audioBaseUrl' => AVAYAAR_URL . 'assets/audio/',
        ) );

        return '<div id="avayaar-root" dir="rtl" class="avayaar-root"></div>';
    }
}