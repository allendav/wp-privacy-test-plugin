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
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ), 8 );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
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

	function register_exporter( $exporters ) {
		$exporters['privacy-test-plugin'] = array(
			'exporter_friendly_name' => __( 'Privacy Test Plugin' ),
			'callback'               => array( $this, 'personal_data_exporter' )
		);

		return $exporters;
	}

	function personal_data_exporter( $email_address, $page = 1 ) {
		$data = array(
			'name'  => 'Tracking ID',
			'value' => md5( $page )
		);

		$export_items = array(
			array(
				'group_id'    => 'privacy-test-plugin',
				'group_label' => __( 'Privacy Test Plugin', 'wpprivacytestplugin' ),
				'item_id'     => "ptp-{$page}",
				'data'        => array( $data ),
			)
		);

		return array(
			'data' => $export_items,
			'done' => ( 2 === $page ),
		);
	}

	function register_eraser( $erasers ) {
		$erasers['privacy-test-plugin'] = array(
			'eraser_friendly_name' => __( 'Privacy Test Plugin' ),
			'callback'             => array( $this, 'personal_data_eraser' )
		);

		return $erasers;
	}

	function personal_data_eraser( $email_address, $page = 1 ) {
		$state = get_option( 'privacy-test-plugin-eraser-state', 0 );
		$state = (int) $state;

		$retention_messages = array(
			'Order 1234 was not erased because it is less than 180 days old',
			'Comment 5678 was not erased because it was a really good comment'
		);

		// All of the personal data found for this user was removed.
		if ( 0 === $state ) {
			$items_removed = true;
			$items_retained = false;
			$messages = array();
		}

		// Personal data was found for this user but some of the personal data found was not removed.
		if ( 1 === $state ) {
			$items_removed = true;
			$items_retained = true;
			$messages = $retention_messages;
		}

		// No personal data was found for this user.
		if ( 2 === $state ) {
			$items_removed = false;
			$items_retained = false;
			$messages = array();
		}

		// Personal data was found for this user but was not removed.
		if ( 3 === $state ) {
			$items_removed = false;
			$items_retained = true;
			$messages = $retention_messages;
		}

		$state = $state + 1;
		if ( 4 === $state ) {
			$state = 0;
		}

		update_option( 'privacy-test-plugin-eraser-state', $state );

		return array(
			'items_removed' => $items_removed,
			'items_retained' => $items_retained,
			'messages' => $messages,
			'done' => true
		);
	}
}

WP_Privacy_Test_Plugin::getInstance();
