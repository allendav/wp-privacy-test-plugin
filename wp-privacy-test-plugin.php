<?php
/*
Plugin Name: WP Privacy Test Plugin
Plugin URI: http://www.allendav.com/
Description: Makes it easier to exercise WordPress core privacy tools
Version: 1.0.0
Author: allendav
Author URI: http://www.allendav.com
License: GPL2
*/

class WP_Privacy_Test_Plugin {
	private static $instance;

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	protected function __construct() {
		add_action( 'admin_init', array( $this, 'add_privacy_policy_content' ) );
	}

	function add_privacy_policy_content() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			$content = sprintf(
				__( 'When you leave a comment on this site, we send your name, email
				address, IP address and comment text to the example.com spam detection
				service to prevent spam from appearing on this site. Example.com does
				not retain your personal data.

				The example.com privacy policy is <a href="%s" target="_blank">here</a>.',
				'my_plugin_textdomain' ),
				'https://example.com/'
			);

			wp_add_privacy_policy_content(
				'Privacy Test Plugin',
				wp_kses_post( wpautop( $content, false ) )
			);
		}
	}
}

WP_Privacy_Test_Plugin::getInstance();
