<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Export {

    public function __construct() {
        add_action( 'admin_post_avayaar_export_csv', array( $this, 'handle_export' ) );
    }

    public function handle_export() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'دسترسی غیرمجاز.' );
        }
        check_admin_referer( 'avayaar_admin_actions' );

        global $wpdb;
        $rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}avayaar_submissions ORDER BY created_at DESC" );

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=avayaar-leads-' . date( 'Y-m-d' ) . '.csv' );

        $output = fopen( 'php://output', 'w' );
        fwrite( $output, "\xEF\xBB\xBF" ); // UTF-8 BOM — without it, Excel shows Persian text as mojibake.

        fputcsv( $output, array( 'نام', 'شماره تماس', 'تاریخ', 'پروفایل', 'امتیاز ریتم', 'امتیاز شنوایی', 'سازهای پیشنهادی', 'تماس گرفته شد', 'تاریخ تماس' ) );

        foreach ( $rows as $row ) {
            $scores = json_decode( $row->module_scores, true );
            $ids    = json_decode( $row->recommended_instruments, true );
            $titles = array();
            if ( is_array( $ids ) ) {
                foreach ( $ids as $iid ) {
                    $t = get_the_title( $iid );
                    if ( $t ) $titles[] = $t;
                }
            }

            fputcsv( $output, array(
                $row->full_name,
                $row->phone,
                $row->created_at,
                $row->archetype,
                $scores['rhythm'] ?? '',
                $scores['ear'] ?? '',
                implode( '، ', $titles ),
                $row->contacted ? 'بله' : 'خیر',
                $row->contacted_at ?: '',
            ) );
        }

        fclose( $output );
        exit;
    }
}