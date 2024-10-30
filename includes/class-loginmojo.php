<?php

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class LOGINMOJO
{

	public function __construct()
	{
		/*
		 * Plugin Loaded Action
		 */
		add_action('plugins_loaded', array($this, 'loginmojo_plugin_setup'));

		/**
		 * Install And Upgrade plugin
		 */
		require_once LOGINMOJO_DIR . 'includes/class-loginmojo-install.php';

		register_activation_hook(LOGINMOJO_DIR . 'loginmojo.php', array('\LOGINMOJO\Install', 'install'));

		add_action('init', array($this, 'redirect_to_general_url_handler'));
	}

	/**
	 * Constructors plugin Setup
	 *
	 * @param Not param
	 */
	public function loginmojo_plugin_setup()
	{
		// Load text domain
		add_action('init', array($this, 'loginmojo_load_textdomain'));
		$this->includes();
	}

	/**
	 * Redirect to specific tab
	 * If the page = =loginmojo-settings then redirect to tab=general
	 * 
	 */
	public function redirect_to_general_url_handler()
	{
		if (substr($_SERVER["REQUEST_URI"], -20) == 'page=loginmojo-settings') {
			$url = $_SERVER["REQUEST_URI"] . '&tab=general';
			wp_redirect($url);
		}
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function loginmojo_load_textdomain()
	{
		load_plugin_textdomain('wp-loginwithwa', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	/**
	 * Includes plugin files
	 *
	 * @param Not param
	 */
	public function includes()
	{

		// Utility classes.
		require_once LOGINMOJO_DIR . 'includes/class-loginmojo-features.php';

		if (is_admin()) {
			// Admin classes.
			require_once LOGINMOJO_DIR . 'includes/admin/class-loginmojo-admin.php';

			// Settings classes.
			require_once LOGINMOJO_DIR . 'includes/admin/settings/class-loginmojo-settings.php';
		}

		if (!is_admin()) {
			// Front Class.
			require_once LOGINMOJO_DIR . 'includes/class-front.php';
		}
		// Template functions.
		require_once LOGINMOJO_DIR . 'includes/template-functions.php';
	}
}
