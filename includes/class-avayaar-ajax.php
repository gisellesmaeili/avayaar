<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_avayaar_submit', array( $this, 'handle_submit' ) );
        add_action( 'wp_ajax_nopriv_avayaar_submit', array( $this, 'handle_submit' ) );
    }

    public function handle_submit() {
        check_ajax_referer( 'avayaar_submit', 'nonce' );

        $full_name   = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';
        $phone       = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $raw_answers = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : '';

        if ( empty( $full_name ) || empty( $phone ) || empty( $raw_answers ) ) {
            wp_send_json_error( array( 'message' => 'اطلاعات ناقص است.' ) );
        }

        $answers = json_decode( $raw_answers, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => 'داده نامعتبر است.' ) );
        }

        $result = Avayaar_Scoring::calculate_result( $answers );

        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'avayaar_submissions', array(
            'full_name'               => $full_name,
            'phone'                   => $phone,
            'module_scores'           => wp_json_encode( $result['module_scores'] ),
            'answers_log'             => wp_json_encode( $answers ),
            'archetype'               => $result['archetype'],
            'recommended_instruments' => wp_json_encode( $result['recommended_instruments'] ),
            'contacted'               => 0,
        ) );

        wp_send_json_success( $result );
    }
}