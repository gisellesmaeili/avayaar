<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AVAYAAR_Leads_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'lead',
            'plural'   => 'leads',
            'ajax'     => false,
        ) );
    }

    public function get_columns() {
        return array(
            'full_name'  => 'نام',
            'phone'      => 'شماره تماس',
            'archetype'  => 'پروفایل',
            'created_at' => 'تاریخ',
            'contacted'  => 'تماس گرفته شد',
        );
    }

    protected function get_views() {
        global $wpdb;
        $table = $wpdb->prefix . 'avayaar_submissions';
        $waiting = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE contacted = 0" );
        $called  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE contacted = 1" );

        $current = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'waiting';
        $base_url = admin_url( 'admin.php?page=avayaar' );

        return array(
            'waiting' => sprintf(
                '<a href="%s" class="%s">در انتظار تماس (%d)</a>',
                esc_url( add_query_arg( 'status', 'waiting', $base_url ) ),
                $current === 'waiting' ? 'current' : '',
                $waiting
            ),
            'called' => sprintf(
                '<a href="%s" class="%s">تماس گرفته شد (%d)</a>',
                esc_url( add_query_arg( 'status', 'called', $base_url ) ),
                $current === 'called' ? 'current' : '',
                $called
            ),
        );
    }

    public function prepare_items() {
        global $wpdb;
        $table = $wpdb->prefix . 'avayaar_submissions';
        $status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'waiting';
        $contacted = ( $status === 'called' ) ? 1 : 0;

        $this->_column_headers = array( $this->get_columns(), array(), array() );

        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE contacted = %d ORDER BY created_at DESC",
                $contacted
            )
        );
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'full_name':
            case 'phone':
            case 'archetype':
            case 'created_at':
                return esc_html( $item->$column_name );
            case 'contacted':
                return $item->contacted
                    ? '✅'
                    : '<button type="button" class="button avayaar-mark-called" data-id="' . esc_attr( $item->id ) . '">تماس گرفته شد</button>';
        }
    }
}