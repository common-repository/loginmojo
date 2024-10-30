<?php

namespace LOGINMOJO;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class LOGINMOJO_Features
{

    public $loginmojo;
    public $date;
    public $options;
    protected $db;
    protected $tb_prefix;

    /**
     * LOGINMOJO_Features constructor.
     */
    public function __construct()
    {
        global $loginmojo, $wpdb;

        $this->db = $wpdb;
        $this->tb_prefix = $wpdb->prefix;
        $this->date = LOGINMOJO_CURRENT_DATE;
        $this->options = LOGINMOJO_Option::getOptions();

        $hook_name = 'register_form';
        $search_value = 'add_loginwithwa_mobile_phone_field_to_register_form';
        global $wp_filter;
        if (isset($wp_filter[$hook_name])) {
            $encoded_hook_name = json_encode($wp_filter[$hook_name]);
            $no_of_occurances = substr_count($encoded_hook_name, $search_value);
            if ($no_of_occurances == 0) {
                add_action('register_form', array($this, 'add_loginwithwa_mobile_phone_field_to_register_form'));
                add_action('user_new_form', array($this, 'add_loginwithwa_mobile_phone_field_to_newuser_form'));
                add_filter('user_contactmethods', array($this, 'add_loginwithwa_mobile_phone_field_to_profile_form'));
            }
        } else {
            add_action('register_form', array($this, 'add_loginwithwa_mobile_phone_field_to_register_form'));
            add_action('user_new_form', array($this, 'add_loginwithwa_mobile_phone_field_to_newuser_form'));
            add_filter('user_contactmethods', array($this, 'add_loginwithwa_mobile_phone_field_to_profile_form'));
        }

        add_filter('registration_errors', array($this, 'registration_errors'), 10, 3);
        add_action('user_register', array($this, 'save_register'));

        add_action('user_register', array($this, 'check_admin_duplicate_number'));
        add_action('profile_update', array($this, 'check_admin_duplicate_number'));

        if (isset($this->options['loginmojo'])) {
            // add_action( 'login_enqueue_scripts', array($this, 'login_type'), 20, 1);
            add_action('login_form', array($this, 'login_button'), 10, 2);
            if (isset($this->options['woocommerce_integration'])) {
                // add the action
                add_action('woocommerce_login_form', array($this, '_woocommerce_whats_login_button'), 10, 1);
                add_action('woocommerce_register_form', array($this, '_woocommerce_register_page'));
                add_action('woocommerce_register_post', array($this, '_woocommerce_register_error_fields'), 10, 3);
                add_action('woocommerce_created_customer', array($this, '_woocommerce_register_save_fields'));
                add_action('woocommerce_checkout_fields', array($this, '_woocommerce_checkout_add_fields'));
                add_action('woocommerce_after_checkout_validation', array($this, '_woocommerce_after_checkout_validation'), 10, 2);
            }
        }

        add_action('wp_enqueue_scripts', array($this, 'load_international_input'));
        add_action('admin_enqueue_scripts', array($this, 'load_international_input'));
        add_action('login_enqueue_scripts', array($this, 'load_international_input'));
    }

    /**
     * Add OTP in login form
     */
    public static function login_button()
    {
        wp_enqueue_style('loginmojo-admin-css', LOGINMOJO_URL . 'assets/css/admin.css', true, LOGINMOJO_VERSION);
        wp_enqueue_script('login_enqueue_scripts', LOGINMOJO_URL . 'assets/js/otp-login-form.js', true, LOGINMOJO_VERSION);
        include_once LOGINMOJO_DIR . "includes/templates/otp-login-form.php";
    }

    // WooCommerce : Add Login With Mobile Button
    function _woocommerce_whats_login_button()
    {
        include_once LOGINMOJO_DIR . "includes/templates/otp-login-form.php";
        wp_enqueue_script('login_enqueue_scripts', LOGINMOJO_URL . 'assets/js/otp-login-form.js', true, LOGINMOJO_VERSION);
    }

    // WooCommerce : Register Page Add Mobile Number
    function _woocommerce_register_page()
    {
        woocommerce_form_field(
            'mobile_phone',
            array(
                'type'        => 'text',
                'required'    => true, // Just adds an "*"
                'label'       => 'Mobile Phone',
                'input_class'       => array('loginmojo-input-mobile_phone')
            ),
            (isset($_POST['mobile_phone']) ? $_POST['mobile_phone'] : '')
        );
    }

    // WooCommerce : Register Error
    function _woocommerce_register_error_fields($username, $email, $errors)
    {
        $error = false;
        if (empty($_POST['mobile_phone'])) {
            $errors->add('first_name_error', __('<strong>ERROR</strong>: You must include a mobile_phone number.', 'loginmojo'));
        }
        if (!$error && preg_match('/^[0-9\-\(\)\/\+\s]*$/', sanitize_text_field($_POST['mobile_phone']), $matches) == false) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && !isset($matches[0])) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && isset($matches[0]) && strlen($matches[0]) < 10) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && isset($matches[0]) && strlen($matches[0]) > 14) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if ($this->check_mobile_phone_number(sanitize_text_field($_POST['mobile_phone']))) {
            $errors->add('duplicate_mobile_phone_number', __('<strong>ERROR</strong>: This mobile_phone is already registered, please choose another one.', 'loginmojo'));
        }
    }

    // WooCommerce : Register Save Fields
    function _woocommerce_register_save_fields($customer_id)
    {
        if (isset($_POST['mobile_phone'])) {
            $_POST['mobile_phone'] = sanitize_text_field( $_POST['mobile_phone'] );
            update_user_meta($customer_id, 'mobile_phone', wc_clean($_POST['mobile_phone']));
        }
    }

    // WooCommerce : Add Mobile Filed In Checkout Page
    function _woocommerce_checkout_add_fields($fields)
    {
        $fields['billing']['mobile_phone']['required'] = true;
        $fields['billing']['mobile_phone']['label'] = __('Mobile Phone', 'woocommerce');
        $fields['billing']['mobile_phone']['placeholder'] = '';
        $fields['billing']['mobile_phone']['input_class'] = array('loginmojo-input-mobile_phone');
        return $fields;
    }

    // WooCommerce : Validate Mobile Filed In Checkout Page
    function _woocommerce_after_checkout_validation($fields, $errors)
    {
        $error = false;
        if (!$error && empty($fields['mobile_phone'])) {
            $errors->add('first_name_error', __('You must include a mobile_phone number.', 'loginmojo'));
            $error = true;
        } else if ($error == false && preg_match('/^[0-9\-\(\)\/\+\s]*$/', sanitize_text_field($fields['mobile_phone']), $matches) == false) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && !isset($matches[0])) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && isset($matches[0]) && strlen($matches[0]) < 10) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && isset($matches[0]) && strlen($matches[0]) > 14) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && $this->check_mobile_phone_number(sanitize_text_field($fields['mobile_phone']))) {
            $errors->add('duplicate_mobile_phone_number', __('This mobile_phone is already registered, please choose another one.', 'loginmojo'));
        }
    }

    /**
     * Add javascript for login page
     *
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     */

    function login_type()
    {
        wp_enqueue_script('login_enqueue_scripts', LOGINMOJO_URL . 'assets/js/otp-login-form.js', true, LOGINMOJO_VERSION);
    }

    /**
     * @param $mobile_phone_number
     * @param null $user_id
     *
     * @return bool
     */
    private function check_mobile_phone_number($mobile_phone_number, $user_id = null)
    {

        $trimmed_mobile_phone_number = str_replace(' ', '', $mobile_phone_number);
        if ($user_id) {
            $result = $this->db->get_results("SELECT * from `{$this->tb_prefix}usermeta` WHERE meta_key = 'mobile_phone' AND REPLACE(meta_value, ' ', '') = '{$trimmed_mobile_phone_number}' AND user_id != '{$user_id}'");
        } else {
            $result = $this->db->get_results("SELECT * from `{$this->tb_prefix}usermeta` WHERE meta_key = 'mobile_phone' AND REPLACE(meta_value, ' ', '') = '{$trimmed_mobile_phone_number}'");
        }

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $user_id
     */
    private function delete_user_mobile_phone($user_id)
    {
        $this->db->delete(
            $this->tb_prefix . "usermeta",
            array(
                'user_id' => $user_id,
                'meta_key' => 'mobile_phone',
            )
        );
    }

    /**
     * @param $user_id
     */
    public function check_admin_duplicate_number($user_id)
    {
        // Get user mobile_phone
        $user_mobile_phone = get_user_meta($user_id, 'mobile_phone', true);

        if (empty($user_mobile_phone)) {
            return;
        }

        // Delete user mobile_phone
        if ($this->check_mobile_phone_number($user_mobile_phone, $user_id)) {
            $this->delete_user_mobile_phone($user_id);
        }
    }

    public function add_loginwithwa_mobile_phone_field_to_newuser_form()
    {
        include_once LOGINMOJO_DIR . "includes/templates/mobile_phone-field.php";
    }

    /**
     * @param $fields
     *
     * @return mixed
     */
    public function add_loginwithwa_mobile_phone_field_to_profile_form($fields)
    {
        $fields['mobile_phone'] = __('Mobile Number', 'loginmojo');

        return $fields;
    }

    public function add_loginwithwa_mobile_phone_field_to_register_form()
    {
        $mobile_phone = (isset($_POST['mobile_phone'])) ? sanitize_text_field($_POST['mobile_phone']) : '';
        include_once LOGINMOJO_DIR . "includes/templates/mobile_phone-field-register.php";
    }

    /**
     * @param $errors
     * @param $sanitized_user_login
     * @param $user_email
     *
     * @return mixed
     */
    public function registration_errors($errors, $sanitized_user_login, $user_email)
    {
        $error = false;
        if (empty($_POST['mobile_phone'])) {
            $errors->add('first_name_error', __('<strong>ERROR</strong>: You must include a mobile_phone number.', 'loginmojo'));
        }

        if (preg_match('/^[0-9\-\(\)\/\+\s]*$/', sanitize_text_field($_POST['mobile_phone']), $matches) == false) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }
        if (!$error && !isset($matches[0])) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }

        if (!$error && isset($matches[0]) && strlen($matches[0]) < 10) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
            $error = true;
        }

        if (!$error && isset($matches[0]) && strlen($matches[0]) > 14) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'loginmojo'));
        }

        if ($this->check_mobile_phone_number(sanitize_text_field($_POST['mobile_phone']))) {
            $errors->add('duplicate_mobile_phone_number', __('<strong>ERROR</strong>: This mobile_phone is already registered, please choose another one.', 'loginmojo'));
        }
        return $errors;
    }

    /**
     * @param $user_id
     */
    public function save_register($user_id)
    {
        if (isset($_POST['mobile_phone'])) {
            update_user_meta($user_id, 'mobile_phone', sanitize_text_field($_POST['mobile_phone']));
        }
    }

    public function load_international_input()
    {

        //Register IntelTelInput Assets
        wp_enqueue_style('loginmojo-intel-tel-input', LOGINMOJO_URL . 'assets/css/intlTelInput.min.css', true, LOGINMOJO_VERSION);
        wp_enqueue_script('loginmojo-intel-tel-input', LOGINMOJO_URL . 'assets/js/intel/intlTelInput.min.js', array('jquery'), LOGINMOJO_VERSION, true);
        wp_enqueue_script('loginmojo-intel-script', LOGINMOJO_URL . 'assets/js/intel/intel-script.js', true, LOGINMOJO_VERSION, true);

        // Localize the IntelTelInput
        $tel_intel_vars = array();
        $only_countries_option = LOGINMOJO_Option::getOption('international_mobile_phone_only_countries');
        $preferred_countries_option = LOGINMOJO_Option::getOption('international_mobile_phone_preferred_countries');


        if ($only_countries_option) {
            $tel_intel_vars['only_countries'] = $only_countries_option;
        } else {
            $tel_intel_vars['only_countries'] = '';
        }

        if ($preferred_countries_option) {
            $tel_intel_vars['preferred_countries'] = $preferred_countries_option;
        } else {
            $tel_intel_vars['preferred_countries'] = '';
        }

        if (LOGINMOJO_Option::getOption('international_mobile_phone_auto_hide')) {
            $tel_intel_vars['auto_hide'] = true;
        } else {
            $tel_intel_vars['auto_hide'] = false;
        }

        if (LOGINMOJO_Option::getOption('international_mobile_phone_national_mode')) {
            $tel_intel_vars['national_mode'] = true;
        } else {
            $tel_intel_vars['national_mode'] = false;
        }

        if (LOGINMOJO_Option::getOption('international_mobile_phone_separate_dial_code')) {
            $tel_intel_vars['separate_dial'] = true;
        } else {
            $tel_intel_vars['separate_dial'] = false;
        }

        $tel_intel_vars['util_js'] = LOGINMOJO_URL . 'assets/js/intel/utils.js';

        wp_localize_script('loginmojo-intel-script', 'loginmojo_intel_tel_input', $tel_intel_vars);
    }
}
new LOGINMOJO_Features();
