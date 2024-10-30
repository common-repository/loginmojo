<?php

namespace LOGINMOJO;

class LOGINMOJO_Admin
{

    public $loginmojo;
    protected $db;
    protected $tb_prefix;
    protected $options;

    public function __construct()
    {
        global $wpdb;

        $this->db        = $wpdb;
        $this->tb_prefix = $wpdb->prefix;
        $this->options   = LOGINMOJO_Option::getOptions();
        $this->init();

        // Add Actions
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));

        // Add Admin Menu
        add_action('admin_menu', array($this, 'admin_menu'));

        // Add Filters
        add_filter('plugin_row_meta', array($this, 'meta_links'), 0, 2);
    }

    /**
     * Include admin assets
     */
    public function admin_assets()
    {
        if (stristr(get_current_screen()->id, "loginmojo")) {
            wp_register_style('loginmojo-admin', LOGINMOJO_URL . 'assets/css/admin.css', true, LOGINMOJO_VERSION);
            wp_enqueue_style('loginmojo-admin');
            wp_enqueue_style('loginmojo-chosen', LOGINMOJO_URL . 'assets/css/chosen.min.css', true, LOGINMOJO_VERSION);
            wp_enqueue_script('loginmojo-chosen', LOGINMOJO_URL . 'assets/js/chosen.jquery.min.js', true, LOGINMOJO_VERSION);
            wp_enqueue_script('loginmojo-word-and-character-counter', LOGINMOJO_URL . 'assets/js/jquery.word-and-character-counter.min.js', true, LOGINMOJO_VERSION);
            wp_enqueue_script('loginmojootprepeater', LOGINMOJO_URL . 'assets/js/jquery.repeater.min.js', true, LOGINMOJO_VERSION);
            wp_enqueue_script('loginmojootpblocktimerepeater', LOGINMOJO_URL . 'assets/js/jquery.repeater.min.js', true, LOGINMOJO_VERSION);
            wp_enqueue_script('loginmojo-admin', LOGINMOJO_URL . 'assets/js/admin.js', true, LOGINMOJO_VERSION);
        }
    }

    /**
     * Administrator admin_menu
     */
    public function admin_menu()
    {
        add_menu_page(__('LOGINMOJO', 'loginmojo'), __('loginMojo', 'loginmojo'), 'loginmojo_login_register_action', 'loginmojo', array($this, 'login_register_action_callback'), 'dashicons-smartphone');
    }

    /**
     * Callback outbox page.
     */
    public function login_register_action_callback()
    {
        $page = new Login_register_action();
        $page->render_page();
    }

    /**
     * Administrator add Meta Links
     *
     * @param $links
     * @param $file
     *
     * @return array
     */
    public function meta_links($links, $file)
    {
        if ($file == 'loginmojo/loginmojo.php') {
            $rate_url = 'http://wordpress.org/support/view/plugin-reviews/loginmojo?rate=5#postform';
            $links[]  = '<a href="' . $rate_url . '" target="_blank" class="loginmojo-plugin-meta-link" title="' . __('Click here to rate and review this plugin on WordPress.org', 'loginmojo') . '">' . __('Rate this plugin', 'loginmojo') . '</a>';
        }
        return $links;
    }

    /**
     * Adding new capability in the plugin
     */
    public function add_cap()
    {
        // Get administrator role
        $role = get_role('administrator');
        $role->add_cap('loginmojo_setting');
    }

    /**
     * Initial plugin
     */
    private function init()
    {
        // Check exists require function
        if (!function_exists('wp_get_current_user')) {
            include(ABSPATH . "wp-includes/pluggable.php");
        }

        // Add plugin caps to admin role
        if (is_admin() and is_super_admin()) {
            $this->add_cap();
        }
    }
}
new LOGINMOJO_Admin();
