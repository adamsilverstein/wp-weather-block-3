<?php
/**
 * Weather Block Admin Settings.
 *
 * @package WeatherBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Weather_Block_Admin
 *
 * Handles admin settings page for the Weather Block plugin.
 */
class Weather_Block_Admin {

	/**
	 * Initialize admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'settings_init' ) );
	}

	/**
	 * Add admin menu page.
	 */
	public static function add_admin_menu() {
		add_options_page(
			__( 'Weather Block Settings', 'weather-block' ),
			__( 'Weather Block', 'weather-block' ),
			'manage_options',
			'weather-block-settings',
			array( __CLASS__, 'settings_page' )
		);
	}

	/**
	 * Initialize settings.
	 */
	public static function settings_init() {
		register_setting(
			'weather_block_settings',
			'weather_block_api_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'description'       => __( 'OpenWeatherMap API Key', 'weather-block' ),
			)
		);

		add_settings_section(
			'weather_block_api_section',
			__( 'API Configuration', 'weather-block' ),
			array( __CLASS__, 'api_section_callback' ),
			'weather_block_settings'
		);

		add_settings_field(
			'weather_block_api_key',
			__( 'OpenWeatherMap API Key', 'weather-block' ),
			array( __CLASS__, 'api_key_field_callback' ),
			'weather_block_settings',
			'weather_block_api_section'
		);
	}

	/**
	 * API section description callback.
	 */
	public static function api_section_callback() {
		echo '<p>' . esc_html__( 'Configure your OpenWeatherMap API key to enable weather data fetching.', 'weather-block' ) . '</p>';
		echo '<p>' . sprintf(
			/* translators: %s: OpenWeatherMap API documentation URL */
			__( 'You can get a free API key from <a href="%s" target="_blank" rel="noopener noreferrer">OpenWeatherMap</a>.', 'weather-block' ),
			'https://openweathermap.org/api'
		) . '</p>';
	}

	/**
	 * API key field callback.
	 */
	public static function api_key_field_callback() {
		$api_key = get_option( 'weather_block_api_key', '' );
		echo '<input type="text" id="weather_block_api_key" name="weather_block_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Enter your OpenWeatherMap API key. This is required for the weather block to function.', 'weather-block' ) . '</p>';
	}

	/**
	 * Settings page content.
	 */
	public static function settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php settings_errors(); ?>
			
			<form action="options.php" method="post">
				<?php
				settings_fields( 'weather_block_settings' );
				do_settings_sections( 'weather_block_settings' );
				submit_button();
				?>
			</form>
			
			<div class="card">
				<h2><?php esc_html_e( 'How to Use', 'weather-block' ); ?></h2>
				<ol>
					<li><?php esc_html_e( 'Get your free API key from OpenWeatherMap.', 'weather-block' ); ?></li>
					<li><?php esc_html_e( 'Enter your API key in the field above and save the settings.', 'weather-block' ); ?></li>
					<li><?php esc_html_e( 'Add the Weather Block to any post or page in the block editor.', 'weather-block' ); ?></li>
					<li><?php esc_html_e( 'Configure the location, units, and display mode in the block settings.', 'weather-block' ); ?></li>
				</ol>
			</div>
			
			<div class="card">
				<h2><?php esc_html_e( 'Block Features', 'weather-block' ); ?></h2>
				<ul>
					<li><?php esc_html_e( 'Location input: Enter any city name to display weather information.', 'weather-block' ); ?></li>
					<li><?php esc_html_e( 'Temperature units: Choose between Celsius and Fahrenheit.', 'weather-block' ); ?></li>
					<li><?php esc_html_e( 'Display modes: Light, Dark, or Auto (follows user preference).', 'weather-block' ); ?></li>
					<li><?php esc_html_e( 'Weather information: City, temperature, icon, description, and humidity.', 'weather-block' ); ?></li>
					<li><?php esc_html_e( 'Caching: Weather data is cached for 15 minutes to improve performance.', 'weather-block' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}
}