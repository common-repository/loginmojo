<?php

namespace LOGINMOJO;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class LOGINMOJO_Option
{

	/**
	 * Get the whole Plugin Options
	 *
	 * @param string $setting_name
	 * @param bool $pro
	 *
	 * @return mixed|void
	 */
	public static function getOptions($pro = false, $setting_name = '')
	{
		if (!$setting_name) {
			if ($pro) {
				global $loginmojo_pro_option;

				return $loginmojo_pro_option;
			}
			global $loginmojo_option;
			return $loginmojo_option;
		}
		return get_option($setting_name);
	}


	/**
	 * Get the only Option that we want
	 *
	 * @param $option_name
	 * @param string $setting_name
	 * @param bool $pro
	 *
	 * @return string
	 */
	public static function getOption($option_name, $pro = false, $setting_name = '')
	{
		if (!$setting_name) {
			if ($pro) {
				global $loginmojo_pro_option;

				return isset($loginmojo_pro_option[$option_name]) ? $loginmojo_pro_option[$option_name] : '';
			}

			global $loginmojo_option;

			return isset($loginmojo_option[$option_name]) ? $loginmojo_option[$option_name] : '';
		}
		$options = self::getOptions($setting_name);

		return isset($options[$option_name]) ? $options[$option_name] : '';
	}

	/**
	 * Add an option
	 *
	 * @param $option_name
	 * @param $option_value
	 */
	public static function addOption($option_name, $option_value)
	{
		add_option($option_name, $option_value);
	}

	/**
	 * Update Option
	 *
	 * @param $key
	 * @param $value
	 * @param bool $pro
	 */
	public static function updateOption($key, $value, $pro = false)
	{
	    if ($pro) {
			$setting_name = 'lwwa_pp_settings';
		} else {
			$setting_name = 'loginmojo_settings';
		}

		$options         = self::getOptions($pro);
		$options[$key] = $value;

		update_option($setting_name, $options);
	}
}