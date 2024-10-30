<?php
if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

/**
 * Check get_plugin_data function exist
 */
if (!function_exists('get_plugin_data')) {
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Set Plugin path and url defines.
define('LOGINMOJO_URL', plugin_dir_url(dirname(__FILE__)));
define('LOGINMOJO_DIR', plugin_dir_path(dirname(__FILE__)));


// Get plugin Data.
$plugin_data = get_plugin_data(LOGINMOJO_DIR . 'loginmojo.php');

// Set another useful Plugin defines.
define('LOGINMOJO_VERSION', $plugin_data['Version']);
define('LOGINMOJO_ADMIN_URL', get_admin_url());
define('LOGINMOJO_SITE', 'https://loginmojo.com/');
define('LOGINMOJO_MOBILE_REGEX', '/^[\+|\(|\)|\d|\- ]*$/');
define('LOGINMOJO_CURRENT_DATE', date('Y-m-d H:i:s', current_time('timestamp')));
