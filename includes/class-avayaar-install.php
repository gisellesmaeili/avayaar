<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AVAYAAR_Install {

    public static function activate() {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'avayaar_submissions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			full_name varchar(191) NOT NULL,
			phone varchar(32) NOT NULL,
			module_scores longtext NOT NULL,
			answers_log longtext NOT NULL,
			archetype varchar(100) DEFAULT NULL,
			recommended_instruments text DEFAULT NULL,
			contacted tinyint(1) NOT NULL DEFAULT 0,
			contacted_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY contacted (contacted),
			KEY created_at (created_at)
		) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'avayaar_db_version', AVAYAAR_VERSION );
    }
}