<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function register_menu() {
        add_menu_page( 'آوایار', 'آوایار', 'manage_options', 'avayaar', array( $this, 'render_page' ), 'dashicons-format-audio', 26 );
        add_submenu_page( 'avayaar', 'سرنخ‌ها', 'سرنخ‌ها', 'manage_options', 'avayaar', array( $this, 'render_page' ) );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'avayaar' ) === false ) return;

        wp_enqueue_style( 'avayaar-admin', AVAYAAR_URL . 'assets/css/admin.css', array(), AVAYAAR_VERSION );
        wp_enqueue_script( 'avayaar-admin', AVAYAAR_URL . 'assets/js/admin.js', array(), AVAYAAR_VERSION, true );
        wp_localize_script( 'avayaar-admin', 'AvayaarAdmin', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'avayaar_admin_actions' ),
        ) );
    }

    public function render_page() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

        if ( $action === 'view' && isset( $_GET['id'] ) ) {
            $this->render_detail_page( absint( $_GET['id'] ) );
            return;
        }

        $this->render_leads_page();
    }

    private function render_leads_page() {
        $list_table = new Avayaar_Leads_List_Table();
        $list_table->prepare_items();

        $export_url = wp_nonce_url( admin_url( 'admin-post.php?action=avayaar_export_csv' ), 'avayaar_admin_actions' );
        ?>
        <div class="wrap">
            <h1>سرنخ‌های آزمون استعدادیابی
                <a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">خروجی CSV</a>
            </h1>
            <form method="get">
                <input type="hidden" name="page" value="avayaar" />
                <?php $list_table->views(); ?>
                <?php $list_table->display(); ?>
            </form>
        </div>
        <?php
    }

    private function render_detail_page( $id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}avayaar_submissions WHERE id = %d", $id ) );

        if ( ! $row ) {
            echo '<div class="wrap"><p>موردی یافت نشد.</p></div>';
            return;
        }

        $top_families   = json_decode( $row->module_scores, true );
        $answers        = json_decode( $row->answers_log, true );
        $instrument_ids = json_decode( $row->recommended_instruments, true );

        $family_labels = array(
                'string' => 'زهی', 'keys' => 'کلاویه‌ای', 'wind' => 'بادی', 'perc' => 'کوبه‌ای',
                'plucked' => 'زهی مضرابی', 'vocal' => 'آواز', 'iranian' => 'سنتی ایرانی',
        );
        ?>
        <div class="wrap avayaar-detail">
            <h1><a href="<?php echo esc_url( admin_url( 'admin.php?page=avayaar' ) ); ?>">&rarr; بازگشت</a></h1>
            <h2><?php echo esc_html( $row->full_name ); ?> — <?php echo esc_html( $row->phone ); ?></h2>
            <p>تاریخ: <?php echo esc_html( date_i18n( 'Y/m/d H:i', strtotime( $row->created_at ) ) ); ?> — نشان: <strong><?php echo esc_html( $row->archetype ); ?></strong></p>

            <h3>بهترین تطبیق‌ها</h3>
            <table class="widefat striped">
                <?php foreach ( (array) $top_families as $family => $pct ) : ?>
                    <tr><td><?php echo esc_html( $family_labels[ $family ] ?? $family ); ?></td><td><?php echo esc_html( $pct ); ?>٪</td></tr>
                <?php endforeach; ?>
            </table>

            <h3>سازهای پیشنهادی</h3>
            <ul>
                <?php foreach ( (array) $instrument_ids as $iid ) : ?>
                    <li><?php echo esc_html( get_the_title( $iid ) ?: ( 'شناسه ' . $iid ) ); ?></li>
                <?php endforeach; ?>
            </ul>

            <h3>پاسخ‌ها</h3>
            <table class="widefat striped">
                <tr><td>حس ضرب</td><td><?php echo esc_html( $answers['ear']['tempo'] ?? '-' ); ?></td></tr>
                <tr><td>ریتم منظم‌تر</td><td><?php echo esc_html( $answers['ear']['regularity'] ?? '-' ); ?></td></tr>
                <tr><td>سبک‌های پسندیده</td><td><?php echo esc_html( implode( '، ', $answers['style'] ?? array() ) ); ?></td></tr>
                <tr><td>تعطیلات</td><td><?php echo esc_html( $answers['personality']['vacation'] ?? '-' ); ?></td></tr>
                <tr><td>حس روی صحنه</td><td><?php echo esc_html( $answers['personality']['stage'] ?? '-' ); ?></td></tr>
                <tr><td>حال‌وهوا</td><td><?php echo esc_html( $answers['mood'] ?? '-' ); ?></td></tr>
            </table>
        </div>
        <?php
    }
}