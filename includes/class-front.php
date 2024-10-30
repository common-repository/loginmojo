<?php

namespace LOGINMOJO;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class LOGINMOJO_Front
{

    public function __construct()
    {

        $this->options = LOGINMOJO_Option::getOptions();

        // Load assets
        add_action('wp_enqueue_scripts', array($this, 'front_assets'));
    }

    /**
     * Include front table
     *
     * @param  Not param
     */
    public function front_assets()
    {
        // Check if "Disable Style" in frontend is active or not
        if (empty($this->options['disable_style_in_front']) or (isset($this->options['disable_style_in_front']) and !$this->options['disable_style_in_front'])) {
            wp_register_style('loginmojo-subscribe', LOGINMOJO_URL . 'assets/css/subscribe.css', true, LOGINMOJO_VERSION);
            wp_enqueue_style('loginmojo-subscribe');
        }
    }
}

new LOGINMOJO_Front();
