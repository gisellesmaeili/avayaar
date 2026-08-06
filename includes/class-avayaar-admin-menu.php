<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AVAYAAR_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
    }

    public function register_menu() {
        add_menu_page(
            'آوایار',
            'آوایار',
            'manage_options',
            'avayaar',
            array( $this, 'render_leads_page' ),
            'dashicons-audio',
            26
        );

        add_submenu_page(
            'avayaar',
            'سرنخ',
            'سرنخ',
            'manage_options',
            'avayaar',
            array( $this, 'render_leads_page' )
        );

        // Recommendation rules + Settings submenus get added here in Phase 4.
    }

    public function render_leads_page() {
        if ( ! class_exists( 'AVAYAAR_Leads_List_Table' ) ) {
            require_once AVAYAAR_PATH . 'includes/class-avayaar-leads-list-table.php';
        }
        $list_table = new AVAYAAR_Leads_List_Table();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1>سرنخ‌های آزمون استعدادیابی</h1>
            <form method="get">
                <input type="hidden" name="page" value="avayaar" />
                <?php $list_table->views(); ?>
                <?php $list_table->display(); ?>
            </form>
        </div>
        <?php
    }
}