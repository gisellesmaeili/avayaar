<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Avayaar_Leads_List_Table extends WP_List_Table {

    private $current_status;

    public function __construct() {
        parent::__construct( array( 'singular' => 'lead', 'plural' => 'leads', 'ajax' => false ) );
        $this->current_status = ( isset( $_GET['status'] ) && $_GET['status'] === 'called' ) ? 'called' : 'waiting';
    }

    public function get_columns() {
        return array(
            'full_name'  => 'نام',
            'phone'      => 'شماره تماس',
            'archetype'  => 'پروفایل',
            'created_at' => 'تاریخ',
            'contacted'  => 'وضعیت تماس',
        );
    }

    protected function get_views() {
        global $wpdb;
        $table = $wpdb->prefix . 'avayaar_submissions';
        $waiting = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE contacted = 0" );
        $called  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE contacted = 1" );
        $base_url = admin_url( 'admin.php?page=avayaar' );

        return array(
            'waiting' => sprintf(
                '<a href="%s" class="%s">در انتظار تماس (%d)</a>',
                esc_url( add_query_arg( 'status', 'waiting', $base_url ) ),
                $this->current_status === 'waiting' ? 'current' : '',
                $waiting
            ),
            'called' => sprintf(
                '<a href="%s" class="%s">تماس گرفته شد (%d)</a>',
                esc_url( add_query_arg( 'status', 'called', $base_url ) ),
                $this->current_status === 'called' ? 'current' : '',
                $called
            ),
        );
    }

    public function prepare_items() {
        global $wpdb;
        $table = $wpdb->prefix . 'avayaar_submissions';
        $contacted = ( $this->current_status === 'called' ) ? 1 : 0;

        $this->_column_headers = array( $this->get_columns(), array(), array() );
        $this->items = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE contacted = %d ORDER BY created_at DESC", $contacted )
        );
    }

    protected function column_full_name( $item ) {
        $detail_url = add_query_arg(
            array( 'page' => 'avayaar', 'action' => 'view', 'id' => $item->id ),
            admin_url( 'admin.php' )
        );
        $actions = array( 'view' => '<a href="' . esc_url( $detail_url ) . '">مشاهده پاسخ‌ها</a>' );

        return esc_html( $item->full_name ) . $this->row_actions( $actions );
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'phone':
            case 'archetype':
                return esc_html( $item->$column_name );
            case 'created_at':
                return esc_html( date_i18n( 'Y/m/d H:i', strtotime( $item->created_at ) ) );
            case 'contacted':
                $target_status = $item->contacted ? 0 : 1;
                $label = $item->contacted ? 'بازگشت به لیست انتظار' : 'تماس گرفته شد';
                return '<button type="button" class="button avayaar-mark-called" data-id="' . esc_attr( $item->id ) . '" data-status="' . esc_attr( $target_status ) . '">' . esc_html( $label ) . '</button>';
        }
        return '';
    }
}