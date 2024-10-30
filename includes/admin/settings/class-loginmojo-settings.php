<?php

namespace LOGINMOJO;

if (!defined('ABSPATH')) {
	exit;
} // No direct access allowed ;)

class LOGINMOJO_Settings
{

	public $setting_name;
	public $options = array();

	public function __construct()
	{
		$this->setting_name = 'loginmojo_settings';
		$this->get_settings();
		$this->options = get_option($this->setting_name);

		if (empty($this->options)) {
			update_option($this->setting_name, array());
		}

		add_action('admin_menu', array($this, 'add_settings_menu'), 11);

		if (isset($_GET['page']) and sanitize_text_field($_GET['page']) == 'loginmojo-settings' or isset($_POST['option_page']) and sanitize_text_field($_POST['option_page']) == 'loginmojo_settings') {

			add_action('admin_init', array($this, 'register_settings'));
		}
	}

	/**
	 * Add WP-SMSto Package admin page settings
	 * */
	public function add_settings_menu()
	{
		add_submenu_page('loginmojo', __('Settings', 'loginmojo'), __('Settings', 'loginmojo'), 'loginmojo_setting', 'loginmojo-settings', array(
			$this,
			'render_settings'
		));
	}

	/**
	 * Gets saved settings from WP core
	 *
	 * @since           2.0
	 * @return          array
	 */
	public function get_settings()
	{
		$settings = get_option($this->setting_name);

		if (!$settings) {
			update_option($this->setting_name, array(
				'loginmojo' => 1,
				'international_mobile_phone' => 1,
				'login_no_of_retries_value' => 10,
				'add_mobile_phone_field' => 1,
			));
		}
		return apply_filters('loginmojo_get_settings', $settings);
	}

	/**
	 * Registers settings in WP core
	 *
	 * @since           2.0
	 * @return          void
	 */
	public function register_settings()
	{
		if (false == get_option($this->setting_name)) {
			add_option($this->setting_name);
		}

		foreach ($this->get_registered_settings() as $tab => $settings) {
			add_settings_section(
				'loginmojo_settings_' . $tab,
				__return_null(),
				'__return_false',
				'loginmojo_settings_' . $tab
			);

			if (empty($settings)) {
				return;
			}

			foreach ($settings as $option) {
				$name = isset($option['name']) ? $option['name'] : '';

				add_settings_field(
					'loginmojo_settings[' . $option['id'] . ']',
					$name,
					array($this, $option['type'] . '_callback'),
					'loginmojo_settings_' . $tab,
					'loginmojo_settings_' . $tab,
					array(
						'id'      => isset($option['id']) ? $option['id'] : null,
						'desc'    => !empty($option['desc']) ? $option['desc'] : '',
						'name'    => isset($option['name']) ? $option['name'] : null,
						'section' => $tab,
						'size'    => isset($option['size']) ? $option['size'] : null,
						'options' => isset($option['options']) ? $option['options'] : '',
						'std'     => isset($option['std']) ? $option['std'] : '',
						'readonly'     => isset($option['readonly']) ? (($option['readonly'] == true) ?  'readonly' : '')  : ''
					)
				);
				register_setting($this->setting_name, $this->setting_name, array($this, 'settings_sanitize'));
			}
		}
	}

	public function header_callback($args)
	{
		echo '<hr/>';
	}

	public function html_callback($args)
	{
		echo $args['options'];
	}

	public function notice_callback($args)
	{
		echo $args['desc'];
	}

	/**
	 * Gets settings tabs
	 *
	 * @since               2.0
	 * @return              array Tabs list
	 */
	public function get_tabs()
	{
		$tabs = array(
			'gateway'       => __('Gateway', 'loginmojo'),
			'feature'       => __('Features', 'loginmojo'),
			'shortcode'       => __('Shortcode', 'loginmojo'),
		);
		return $tabs;
	}

	/**
	 * Sanitizes and saves settings after submit
	 *
	 * @since               2.0
	 *
	 * @param               array $input Settings input
	 *
	 * @return              array New settings
	 */
	public function settings_sanitize($input = array())
	{

		if (empty($_POST['_wp_http_referer'])) {
			return $input;
		}


		parse_str(sanitize_text_field($_POST['_wp_http_referer']), $referrer);

		$settings = $this->get_registered_settings();
		$tab      = isset($referrer['tab']) ? $referrer['tab'] : 'wp';

		$input = $input ? $input : array();
		$input = apply_filters('loginmojo_settings_' . $tab . '_sanitize', $input);

		// Loop through each setting being saved and pass it through a sanitization filter
		foreach ($input as $key => $value) {

		    // Get the setting type (checkbox, select, etc)
			$type = isset($settings[$tab][$key]['type']) ? $settings[$tab][$key]['type'] : false;

			if ($type) {
				// Field type specific filter
				$input[$key] = apply_filters('loginmojo_settings_sanitize_' . $type, $value, $key);
			}

			// General filter
			$input[$key] = apply_filters('loginmojo_settings_sanitize', $value, $key);

            $input[$key] = sanitize_text_field($input[$key]);
		}

		// Loop through the whitelist and unset any that are empty for the tab being saved
		if (!empty($settings[$tab])) {
			foreach ($settings[$tab] as $key => $value) {

				// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
				if (is_numeric($key)) {
					$key = $value['id'];
				}

				if (empty($input[$key])) {
					unset($this->options[$key]);
				}
			}
		}

        // Merge our new settings with the existing
		$output = array_merge($this->options, $input);

		add_settings_error('loginmojo-notices', '', __('Settings updated', 'loginmojo'), 'updated');

		return $output;
	}

	/**
	 * Get settings fields
	 *
	 * @since           2.0
	 * @return          array Fields
	 */
	public function get_registered_settings()
	{

		$options = array(
			'enable'  => __('Enable', 'loginmojo'),
			'disable' => __('Disable', 'loginmojo')
		);

		$settings = apply_filters('loginmojo_login_registered_settings', array(

			// Gateway tab
			'gateway'       => apply_filters('loginmojo_gateway_settings', array(
				// Gateway
				'gayeway_title'             => array(
					'id'   => 'gayeway_title',
					'name' => __('Gateway information', 'loginmojo'),
					'type' => 'header'
				),
				'gateway_loginmojo_api_key'          => array(
					'id'   => 'gateway_loginmojo_api_key',
					'name' => __('API Key', 'loginmojo'),
					'type' => 'text',
					'desc' => __('Enter Api Key', 'loginmojo')
				),
				'gateway_loginmojo_webhook_url'          => array(
					'id'   => 'gateway_loginmojo_webhook_url',
					'name' => __('Your Webhook URL', 'loginmojo'),
					'type' => 'text',
					'desc' => __('Generate Your Webhook URL', 'loginmojo')
				)
			)),

			// Feature tab
			'feature'       => apply_filters('loginmojo_feature_settings', array(
				'login_title'       => array(
					'id'   => 'login_title',
					'name' => __('Login', 'loginmojo'),
					'type' => 'header'
				),
				'loginmojo'         => array(
					'id'      => 'loginmojo',
					'name'    => __('Login with WhatsApp', 'loginmojo'),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __('This option adds login with WhatsApp in the login form.', 'loginmojo'),
				),
				'woocommerce_integration'         => array(
					'id'      => 'woocommerce_integration',
					'name'    => __('WooCommerce Integration', 'loginmojo'),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __('WooCommerce Integration.', 'loginmojo'),
				),
				'international_mobile_phone_only_countries'      => array(
					'id'      => 'international_mobile_phone_only_countries',
					'name'    => __('Only Countries', 'loginmojo'),
					'type'    => 'countryselect',
					'options' => $this->get_countries_list(),
					'desc'    => __('In the dropdown Country select display only the countries you specify.', 'loginmojo')
				),
				'international_mobile_phone_preferred_countries' => array(
					'id'      => 'international_mobile_phone_preferred_countries',
					'name'    => __('Prefix Countries', 'loginmojo'),
					'type'    => 'countryselect',
					'options' => $this->get_countries_list(),
					'desc'    => __('Specify the countries to appear at the top of the list.', 'loginmojo')
				),
			)),

			// Short Code tab
			'shortcode'       => apply_filters('loginmojo_shortcode_settings', array(
				// Shortcode
				'shortcode_title'             => array(
					'id'   => 'shortcode_title',
					'name' => __('General Information', 'loginmojo'),
					'type' => 'header'
				),
				'shortcode'          => array(
					'id'   => 'shortcode',
					'name' => __('Shortcode', 'loginmojo'),
					'type' => 'text',
					'desc' => __('Shortcode', 'loginmojo'),
					'std' => '[WHATS_LOGIN]',
					'readonly' => true
				)
			)),
		));
		return $settings;
	}

	public function checkbox_callback($args)
	{
		$checked = isset($this->options[$args['id']]) ? checked(1, $this->options[$args['id']], false) : '';
		$html    = '<input type="checkbox" id="loginmojo_settings[' . $args['id'] . ']" name="loginmojo_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
		$html    .= '<label for="loginmojo_settings[' . $args['id'] . ']"> ' . __('Active', 'loginmojo') . '</label>';
		$html    .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function multicheck_callback($args)
	{
		$html = '';
		foreach ($args['options'] as $key => $value) {
			$option_name = $args['id'] . '-' . $key;
			$this->checkbox_callback(array(
				'id'   => $option_name,
				'desc' => $value
			));
			echo '<br>';
		}

		echo $html;
	}

	public function text_callback($args)
	{
		if (isset($this->options[$args['id']]) and $this->options[$args['id']]) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}

		if ($args['id'] == 'gateway_loginmojo_webhook_url') {
			$size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';

			$html = '<input type="text" readonly class="gateway_loginmojo_webhook_url ' . $size . '-text" id="loginmojo_settings[' . $args['id'] . ']" name="loginmojo_settings[' . $args['id'] . ']" value="' . esc_attr(stripslashes($value)) . '"/>';
			$html .= '<input type="hidden" id="base_url" value="' . get_site_url() . '">';
			$html .= '<span class="description"> <span id="webhook_url_generate" class="button button-success">' . __('Generate', 'loginmojo') . '</span> <span id="webhook_url_copy" class="button button-success">' . __('Copy', 'loginmojo') . '</span> </span>';
			$html .= '<p class="description"> ' . $args['desc'] . '</p>';
		} else {
			$size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
			$html = '<input type="text" class="' . $size . '-text" id="loginmojo_settings[' . $args['id'] . ']" name="loginmojo_settings[' . $args['id'] . ']" value="' . esc_attr(stripslashes($value)) . '"/>';
			$html .= '<p class="description"> ' . $args['desc'] . '</p>';
		}
		echo $html;
	}

	public function countryselect_callback($args)
	{
		if (isset($this->options[$args['id']])) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}

		$html     = '<select id="loginmojo_settings[' . $args['id'] . ']" name="loginmojo_settings[' . $args['id'] . '][]" multiple="true" class="chosen-select"/>';
		$selected = '';

		foreach ($args['options'] as $option => $country) :
			if (isset($value) and is_array($value)) {
				if (in_array($country['code'], $value)) {
					$selected = " selected='selected'";
				} else {
					$selected = '';
				}
			}
			$html .= '<option value="' . $country['code'] . '" ' . $selected . '>' . $country['name'] . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function render_settings()
	{
		$active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $this->get_tabs()) ? sanitize_text_field($_GET['tab']) : 'gateway';
		ob_start();
        ?>
		<div class="wrap loginmojo-settings-wrap">
			<?php do_action('loginmojo_settings_page'); ?>
			<h2><?php _e('Settings', 'loginmojo') ?></h2>
			<div class="loginmojo-tab-group">
				<ul class="loginmojo-tab">
					<li id="loginmojo-logo" class="loginmojo-logo-group">
						<!--- Logo -->
						<div class="image-box">
							<img src="<?php echo esc_url(LOGINMOJO_URL.'assets/images/loginmojo.png');?>" />
						</div>
						<h4><?php echo sprintf( __( "World's 1<sup>st</sup> WhatsApp Seamless Verification SaaS Platform", 'loginmojo' ), LOGINMOJO_VERSION ); ?></h4>
						<p><?php echo sprintf(__('LOGINMOJO - v%s', 'loginmojo'), LOGINMOJO_VERSION); ?></p>
						<?php do_action('loginmojo_after_setting_logo'); ?>
					</li>
					<?php
					foreach ($this->get_tabs() as $tab_id => $tab_name) {

						$tab_url = add_query_arg(array(
							'settings-updated' => false,
							'tab'              => $tab_id
						));

						$active = $active_tab == $tab_id ? 'active' : '';

						echo '<li><a href="' . esc_url($tab_url) . '" title="' . esc_attr($tab_name) . '" class="' . $active . '">';
						echo $tab_name;
						echo '</a></li>';
					}
					?>
				</ul>
				<?php echo settings_errors('loginmojo-notices'); ?>
				<div class="loginmojo-tab-content">
					<form method="post" action="options.php">
						<table class="form-table">
							<?php
							settings_fields($this->setting_name);
							do_settings_fields('loginmojo_settings_' . $active_tab, 'loginmojo_settings_' . $active_tab);
							?>
						</table>
						<?php
						if (isset($_GET['tab'])) {
							if (strtoupper($_GET['tab']) == 'SHORTCODE') {
							} else {
								submit_button();
							}
						} else {
							submit_button();
						}
						?>
					</form>
				</div>
			</div>
		</div>
        <?php
		echo ob_get_clean();
	}

	/**
	 * Get countries list
	 *
	 * @return array|mixed|object
	 */
	public function get_countries_list()
	{
		// Load countries list file
		$file   = LOGINMOJO_DIR . 'assets/countries.json';
		$file   = file_get_contents($file);
		$result = json_decode($file, true);

		return $result;
	}
}

new LOGINMOJO_Settings();
