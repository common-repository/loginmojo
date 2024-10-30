<?php

namespace LOGINMOJO;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class Install
{

    public function __construct()
    {
    }

    /**
     * Adding new MYSQL Table in Activation Plugin
     *
     * @param Not param
     */
    public static function create_table($network_wide)
    {
        global $wpdb;

        if (is_multisite() && $network_wide) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);

                self::table_sql();

                restore_current_blog();
            }
        } else {
            self::table_sql();
        }
    }

    /**
     * Table SQL
     *
     * @param Not param
     */
    public static function table_sql()
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . 'lwaa_sessions';
        if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
            $create_wpsmstootp_send = ("CREATE TABLE IF NOT EXISTS {$table_name}(
            ID int(10) NOT NULL auto_increment,
            website_session varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            token varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            mobile varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            name varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY(ID)) $charset_collate");
            dbDelta($create_wpsmstootp_send);
        }
    }

    /**
     * Creating plugin tables
     *
     * @param $network_wide
     */
    static function install($network_wide)
    {
        global $loginmojo_db_version;

        self::create_table($network_wide);

        add_option('loginmojo_db_version', LOGINMOJO_VERSION);

        if (is_admin()) {
            self::upgrade();
        }
    }

    /**
     * Upgrade plugin requirements if needed
     */
    static function upgrade()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $installer_wplwwa_ver = get_option('loginmojo_db_version');

        if ($installer_wplwwa_ver < LOGINMOJO_VERSION) {

            update_option('loginmojo_db_version', LOGINMOJO_VERSION);
        }
    }
}
new Install();
