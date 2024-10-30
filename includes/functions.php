<?php

use LOGINMOJO\LOGINMOJO_Option;
use LOGINMOJO\LOGINMOJO_Features;

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


// AJAX Call
add_action('wp_ajax_loginmojo_authenticate_form', 'loginmojo_authenticate_form');
add_action('wp_ajax_nopriv_loginmojo_authenticate_form', 'loginmojo_authenticate_form');
function loginmojo_authenticate_form()
{
    if (is_user_logged_in()) {
        $response_init = array('status' => true, 'logged_in' => true, 'redirect_to' => get_site_url(), 'message' => 'You are already logged in. Redirecting...');
        $response = array_map('sanitize_text_field', $response_init);
        echo json_encode($response);
    }

    require_once LOGINMOJO_DIR . 'includes/class-loginmojo-option.php';

    global $wpdb;

    session_start();

    $api_key = null;

    $_lww_main_url = 'https://app.loginmojo.com';
    $_lww_create_token_url = '/api/v1/token/create';
    $whatSenderURL = $_lww_main_url . $_lww_create_token_url;

    // Get the api key
    if (isset(LOGINMOJO_Option::getOptions($pro = true, $setting_name = 'loginmojo_settings')['gateway_loginmojo_api_key'])) {
        $api_key = LOGINMOJO_Option::getOptions($pro = true, $setting_name = 'loginmojo_settings')['gateway_loginmojo_api_key'];
    }

    $uuid = wp_generate_uuid4();
    $body = json_encode(array('website_session' => $uuid));

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    );
    $args = array(
        'timeout' => '100',
        'redirection' => '10',
        'httpversion' => '1.0',
        'blocking' => true,
        'body'    => $body,
        'headers' => $headers,
    );

    $response = wp_remote_post($whatSenderURL, $args);

    if (!is_wp_error($response) && 200 == wp_remote_retrieve_response_code($response)) {
        $res = json_decode(wp_remote_retrieve_body($response), true);
    } else {
        $res = array();
    }

    $url = null;
    if (isset($res['token']) && isset($res['code']) && $res['code'] == 'S_TOKEN') {
        $wpdb->insert(
            $wpdb->prefix . "lwaa_sessions",
            array(
                'website_session' => $uuid,
                'token' => $res['token'],
                'mobile' => '',
                'name' => '',
                'created_at' => LOGINMOJO_CURRENT_DATE,
                'updated_at' => LOGINMOJO_CURRENT_DATE
            )
        );
        $_SESSION['whatserver_session'] = $wpdb->insert_id;

        $url = "https://api.whatsapp.com/send/";
        $url = $url . '?phone=' . $res['server_mobile'];
        $url = $url . '&text=' . $res['message'];
    }
    if (!empty($url)) {
        $response = array('status' => true, 'logged_in' => false, 'redirect_to' => $url, 'message' => 'Got URL Successfully.');
        echo json_encode($response);
        exit;
    } else {
        $response_init = array('status' => false, 'logged_in' => false, 'message' => 'Waiting...');
        $response = array_map('sanitize_text_field', $response_init);
        echo json_encode($response);
        exit;
    }
    exit;
}

// AJAX Call
add_action('wp_ajax_loginmojo_after_authenticate_form', 'loginmojo_after_authenticate_form');
add_action('wp_ajax_nopriv_loginmojo_after_authenticate_form', 'loginmojo_after_authenticate_form');
function loginmojo_after_authenticate_form()
{
    if (is_user_logged_in()) {
        $response_init = array('status' => true, 'logged_in' => true, 'redirect_to' => get_site_url(), 'message' => 'You have already logged.');
        $response = array_map('sanitize_text_field', $response_init);
        echo json_encode($response);
        exit;
    }
    $status = false;
    $validate_mobile = null;
    $message = '';
    session_start();

    $_ID = (isset($_SESSION['whatserver_session'])) ? $_SESSION['whatserver_session'] : null;

    $response = check_user_is_logged_in_or_not($_ID);

    if ($response['status'] == true && $response['mobile'] != null) {
        $phone = str_replace(" ", "", $response['mobile']);
        // Get User from Mobile Number
        $UserID = get_user_id_by_phone($phone);

        if (!empty($UserID)) {
            $_SESSION['whatserver_session'] = '';
            unset($_SESSION['whatserver_session']);

            $validate_mobile = true;

            $status = true;

            // Dynamic Loign By User ID
            wp_clear_auth_cookie();
            wp_set_current_user($UserID); // Set the current user detail
            wp_set_auth_cookie($UserID); // Set auth details in cookie
            $message = 'Logged in Successfully. Redirecting sortly.';
        } else {
            $validate_mobile = false;
            $message = 'Mobile (' . $phone . ') is not available in our records. Please update in your account or create new account.';
        }
    } else {
        $message = 'Send WhatsApp Message to get Login..';
    }
    if (!empty($status)) {
        $response_init = array('status' => $status, 'validate' => $validate_mobile, 'logged_in' => false, 'redirect_to' => get_site_url(), 'message' => $message);
        $response = array_map('sanitize_text_field', $response_init);
        echo json_encode($response);
        exit;
    } else {
        $response_init = array('status' => $status, 'validate' => $validate_mobile, 'logged_in' => false, 'message' => $message);
        $response = array_map('sanitize_text_field', $response_init);
        echo json_encode($response);
        exit;
    }
    exit;
}

require_once LOGINMOJO_DIR . 'includes/class-loginmojo-option.php';

// Get User By Phone
function get_user_id_by_phone($phone = null)
{
    global $wpdb;
    $phone = '+' . str_ireplace('+', '', str_ireplace(' ', '', $phone));
    $getUser = $wpdb->get_row($wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "usermeta` WHERE REPLACE(meta_value,' ','') = '" . $phone . "'"));
    if (isset($getUser->user_id)) {
        return $getUser->user_id;
    } else {
        return null;
    }
}

// Custom
function check_user_is_logged_in_or_not($_ID)
{
    global $wpdb;
    session_start();
    $getRecord = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "lwaa_sessions WHERE ID = %d", $_ID));
    if (isset($getRecord->mobile) && !empty($getRecord->mobile)) {
        return array('status' => true, 'mobile' => $getRecord->mobile);
    } else {
        return array('status' => false);
    }
}

// function that runs when shortcode is called
function whats_login_form()
{
    LOGINMOJO_Features::login_button();
}
// register shortcode
add_shortcode('WHATS_LOGIN', 'whats_login_form');


/**
 * Register Route
 */
add_action('rest_api_init', 'wp_sms_otp_login_register_route');
function wp_sms_otp_login_register_route()
{
    register_rest_route('loginmojo/v1', '/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => 'POST',
        'callback' => 'callback_wp_sms_otp_login',
        'permission_callback' => '__return_true'
    ));
}
/**
 * Update DB
 */
function callback_wp_sms_otp_login(WP_REST_Request $request)
{
    global $wpdb;
    // Parameters is an array
    $parameters = $request->get_params();
    $post = file_get_contents('php://input');
    if (!empty($parameters) && !empty($post)) {
        $slug = null;
        if (isset($parameters['slug'])) {
            $slug = $parameters['slug'];
            unset($parameters['slug']);
        }
        $params = json_decode($post, true);
        $params = array_map('sanitize_text_field', $params);
        $db_slug = null;
        if (isset(LOGINMOJO_Option::getOptions($pro = true, $setting_name = 'loginmojo_settings')['gateway_loginmojo_webhook_url'])) {
            $db_slug = LOGINMOJO_Option::getOptions($pro = true, $setting_name = 'loginmojo_settings')['gateway_loginmojo_webhook_url'];
        }
        if (basename($db_slug) == $slug && isset($params['mobile']) && isset($params['name']) && isset($params['token'])) {
            $params['mobile'] = str_replace(" ", "", $params['mobile']);
            $data = ['mobile' => $params['mobile'], 'name' => $params['name'], 'updated_at' => LOGINMOJO_CURRENT_DATE];
            $where = ['token' => $params['token']];

            $wpdb->update($wpdb->prefix . 'lwaa_sessions', $data, $where);
        }
    }
}
