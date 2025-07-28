<?php
/**
 * Plugin Name:       Weather Block
 * Description:       A WordPress block that displays weather information for a specified location.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       weather-block
 *
 * @package WeatherBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// For testing purposes, define the API key here.
// In a real plugin, this would be in wp-config.php or a settings page.
if ( ! defined( 'WEATHER_BLOCK_API_KEY' ) ) {
	define( 'WEATHER_BLOCK_API_KEY', get_option( 'weather_block_api_key', '' ) );
}

// Define plugin constants.
define( 'WEATHER_BLOCK_VERSION', '0.1.0' );
define( 'WEATHER_BLOCK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WEATHER_BLOCK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function weather_block_init() {
	register_block_type( __DIR__ . '/build/weather-block' );
}
add_action( 'init', 'weather_block_init' );

/**
 * Load plugin classes and hooks.
 */
require_once WEATHER_BLOCK_PLUGIN_DIR . 'includes/class-weather-api.php';
require_once WEATHER_BLOCK_PLUGIN_DIR . 'includes/class-weather-admin.php';

// Initialize the admin settings page.
add_action( 'init', array( 'Weather_Block_Admin', 'init' ) );

/**
 * Enqueue frontend and editor scripts with localized data.
 */
function weather_block_enqueue_scripts() {
	// Enqueue for both frontend and editor.
	wp_localize_script(
		'weather-block-weather-block-view-script',
		'weatherBlockAjax',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'weather_block_nonce' ),
		)
	);
}
add_action( 'enqueue_block_assets', 'weather_block_enqueue_scripts' );

/**
 * Enqueue editor scripts with localized data.
 */
function weather_block_enqueue_editor_scripts() {
	wp_localize_script(
		'weather-block-weather-block-editor-script',
		'weatherBlockAjax',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'weather_block_nonce' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'weather_block_enqueue_editor_scripts' );

/**
 * AJAX handler for weather data requests.
 */
add_action( 'wp_ajax_get_weather_data', 'weather_block_ajax_handler' );
add_action( 'wp_ajax_nopriv_get_weather_data', 'weather_block_ajax_handler' );

/**
 * Handle AJAX requests for weather data.
 */
function weather_block_ajax_handler() {
	// Verify nonce for security.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'weather_block_nonce' ) ) {
		wp_die( __( 'Security check failed.', 'weather-block' ) );
	}

	$location = sanitize_text_field( $_POST['location'] );
	$units = sanitize_text_field( $_POST['units'] );

	if ( empty( $location ) ) {
		wp_send_json_error( __( 'Location is required.', 'weather-block' ) );
	}

	$weather_api = new Weather_Block_API();
	$weather_data = $weather_api->get_weather_data( $location, $units );

	if ( is_wp_error( $weather_data ) ) {
		error_log( 'Weather Block API Error: ' . $weather_data->get_error_message() );
		wp_send_json_error( __( 'Could not fetch weather data. Please check the location and try again.', 'weather-block' ) );
	}

	wp_send_json_success( $weather_data );
}
