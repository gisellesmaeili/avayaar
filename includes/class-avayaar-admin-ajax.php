<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Admin_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_avayaar_mark_called', array( $this, 'handle_mark_called' ) );
    }

    public function handle_mark_called() {
        check_ajax_referer( 'avayaar_admin_actions', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز.' ) );
        }

        $id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
        $status = isset( $_POST['status'] ) ? absint( $_POST['status'] ) : 1;

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'شناسه نامعتبر است.' ) );
        }

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'avayaar_submissions',
            array(
                'contacted'    => $status ? 1 : 0,
                'contacted_at' => $status ? current_time( 'mysql' ) : null,
            ),
            array( 'id' => $id )
        );

        wp_send_json_success( array( 'id' => $id, 'status' => $status ? 1 : 0 ) );
    }
}