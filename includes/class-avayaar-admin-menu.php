<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function register_menu() {
        $icon = plugin_dir_url( __FILE__ ) . '../assets/icons/music.svg';
        add_menu_page( 'آوایار', 'آوایار', 'manage_options', 'avayaar', array( $this, 'render_page' ), $icon, 26 );
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

        $scores         = json_decode( $row->module_scores, true );
        $answers        = json_decode( $row->answers_log, true );
        $instrument_ids = json_decode( $row->recommended_instruments, true );

        $goals_questions = array();
        foreach ( Avayaar_Questions::get_goals_questions() as $q ) $goals_questions[ $q['id'] ] = $q;

        $ear_questions = array();
        foreach ( Avayaar_Questions::get_ear_questions() as $q ) $ear_questions[ $q['id'] ] = $q;
        ?>
        <div class="wrap avayaar-detail">
            <h1><a href="<?php echo esc_url( admin_url( 'admin.php?page=avayaar' ) ); ?>">&rarr; بازگشت</a></h1>
            <h2><?php echo esc_html( $row->full_name ); ?> — <?php echo esc_html( $row->phone ); ?></h2>
            <p>
                تاریخ: <?php echo esc_html( date_i18n( 'Y/m/d H:i', strtotime( $row->created_at ) ) ); ?> —
                پروفایل: <strong><?php echo esc_html( $row->archetype ); ?></strong> —
                امتیاز ریتم: <?php echo esc_html( $scores['rhythm'] ?? '-' ); ?>٪ —
                امتیاز شنوایی: <?php echo esc_html( $scores['ear'] ?? '-' ); ?>٪
            </p>

            <h3>سازهای پیشنهادی</h3>
            <ul>
                <?php if ( is_array( $instrument_ids ) && $instrument_ids ) : ?>
                    <?php foreach ( $instrument_ids as $iid ) : ?>
                        <li><?php echo esc_html( get_the_title( $iid ) ?: ( 'شناسه ' . $iid ) ); ?></li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>موردی ثبت نشده.</li>
                <?php endif; ?>
            </ul>

            <h3>پاسخ‌های بخش اهداف</h3>
            <table class="widefat striped">
                <?php foreach ( ( $answers['goals'] ?? array() ) as $qid => $val ) :
                    $q = $goals_questions[ $qid ] ?? null;
                    if ( ! $q ) continue;
                    ?>
                    <tr>
                        <td><?php echo esc_html( $q['question'] ); ?></td>
                        <td><?php echo esc_html( $q['options'][ $val ] ?? $val ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h3>پاسخ‌های بخش شنوایی</h3>
            <table class="widefat striped">
                <thead><tr><th>سوال</th><th>نتیجه</th></tr></thead>
                <tbody>
                <?php foreach ( ( $answers['ear'] ?? array() ) as $entry ) :
                    $q = $ear_questions[ $entry['id'] ] ?? null;
                    if ( ! $q ) continue;
                    $is_correct = $entry['answer'] === $q['correct'];
                    ?>
                    <tr>
                        <td><?php echo esc_html( $entry['id'] ); ?></td>
                        <td><?php echo $is_correct ? '✅ درست' : '❌ نادرست'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h3>پاسخ‌های بخش ریتم</h3>
            <table class="widefat striped">
                <thead><tr><th>الگو</th><th>میانگین خطا (میلی‌ثانیه)</th><th>ارزیابی</th></tr></thead>
                <tbody>
                <?php foreach ( ( $answers['rhythm'] ?? array() ) as $entry ) :
                    $deltas = $entry['tap_deltas_ms'] ?? array();
                    $avg    = $deltas ? array_sum( array_map( 'abs', $deltas ) ) / count( $deltas ) : 0;
                    $label  = $avg < 120 ? 'عالی' : ( $avg < 250 ? 'خوب' : 'قابل تمرین' );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $entry['id'] ); ?></td>
                        <td><?php echo esc_html( round( $avg ) ); ?></td>
                        <td><?php echo esc_html( $label ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}