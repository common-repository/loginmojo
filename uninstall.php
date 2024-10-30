<?php

namespace LOGINMOJO;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$option_name = 'loginmojo_settings';

delete_option($option_name);

$option_name = 'lwwa_pp_settings';

delete_option($option_name);

// drop a custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lwaa_sessions");
