<?php

/**
 * Plugin Name: loginMojo - Login With WA
 * Plugin URI:
 * Description:Login with WhatsApp is providing seamless user experiance to get login
 * Version: 1.0.0
 * Author:loginMojo
 * Author URI: https://loginmojo.com
 * Text Domain: loginmojo.com
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Load Plugin Defines
 */
require_once 'includes/defines.php';

/**
 * Load plugin Special Functions
 */
require_once LOGINMOJO_DIR . 'includes/functions.php';

/**
 * Get plugin options
 */
$loginmojo_option = get_option('loginmojo_settings');

/**
 * Initial gateway
 */

/**
 * Load Plugin
 */
require LOGINMOJO_DIR . 'includes/class-loginmojo.php';

new LOGINMOJO();
