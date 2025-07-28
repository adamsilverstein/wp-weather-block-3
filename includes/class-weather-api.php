<?php
/**
 * Weather API handler class.
 *
 * @package WeatherBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Weather_Block_API
 *
 * Handles weather data fetching from OpenWeatherMap API with caching.
 */
class Weather_Block_API {

	/**
	 * OpenWeatherMap API base URL.
	 */
	const API_BASE_URL = 'https://api.openweathermap.org/data/2.5/weather';

	/**
	 * Cache expiration time in seconds (15 minutes).
	 */
	const CACHE_EXPIRATION = 900;

	/**
	 * Get weather data for a location.
	 *
	 * @param string $location The city name.
	 * @param string $units    Temperature units (metric or imperial).
	 * @return array|WP_Error Weather data array or WP_Error on failure.
	 */
	public function get_weather_data( $location, $units = 'metric' ) {
		// Validate inputs.
		if ( empty( $location ) ) {
			return new WP_Error( 'invalid_location', __( 'Location cannot be empty.', 'weather-block' ) );
		}

		if ( ! in_array( $units, array( 'metric', 'imperial' ), true ) ) {
			$units = 'metric';
		}

		// Check for cached data first.
		$cache_key = 'weather_block_' . md5( $location . $units );
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		// Get API key.
		$api_key = WEATHER_BLOCK_API_KEY;
		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'OpenWeatherMap API key is not configured.', 'weather-block' ) );
		}

		// Prepare API request.
		$api_url = add_query_arg(
			array(
				'q'     => $location,
				'appid' => $api_key,
				'units' => $units,
			),
			self::API_BASE_URL
		);

		// Make API request.
		$response = wp_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_request_failed', __( 'Failed to connect to weather service.', 'weather-block' ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			$error_data = json_decode( $response_body, true );
			$error_message = isset( $error_data['message'] ) ? $error_data['message'] : __( 'Unknown API error.', 'weather-block' );
			return new WP_Error( 'api_error', $error_message );
		}

		// Parse response.
		$weather_data = json_decode( $response_body, true );

		if ( ! $weather_data ) {
			return new WP_Error( 'invalid_response', __( 'Invalid response from weather service.', 'weather-block' ) );
		}

		// Format data for frontend use.
		$formatted_data = $this->format_weather_data( $weather_data, $units );

		// Cache the data.
		set_transient( $cache_key, $formatted_data, self::CACHE_EXPIRATION );

		return $formatted_data;
	}

	/**
	 * Format weather data for frontend use.
	 *
	 * @param array  $data  Raw weather data from API.
	 * @param string $units Temperature units.
	 * @return array Formatted weather data.
	 */
	private function format_weather_data( $data, $units ) {
		$weather = isset( $data['weather'][0] ) ? $data['weather'][0] : array();
		$main = isset( $data['main'] ) ? $data['main'] : array();

		// Get weather icon.
		$icon_map = $this->get_weather_icon_map();
		$weather_code = isset( $weather['id'] ) ? $weather['id'] : 0;
		$weather_icon = $this->get_weather_icon( $weather_code );

		// Format temperature unit.
		$temp_unit = 'metric' === $units ? '°C' : '°F';

		return array(
			'city'        => isset( $data['name'] ) ? esc_html( $data['name'] ) : '',
			'country'     => isset( $data['sys']['country'] ) ? esc_html( $data['sys']['country'] ) : '',
			'temperature' => isset( $main['temp'] ) ? round( $main['temp'] ) : 0,
			'temp_unit'   => $temp_unit,
			'description' => isset( $weather['description'] ) ? esc_html( ucfirst( $weather['description'] ) ) : '',
			'humidity'    => isset( $main['humidity'] ) ? intval( $main['humidity'] ) : 0,
			'icon'        => $weather_icon,
			'icon_code'   => isset( $weather['icon'] ) ? esc_attr( $weather['icon'] ) : '',
		);
	}

	/**
	 * Get weather icon based on weather condition code.
	 *
	 * @param int $weather_code Weather condition code from OpenWeatherMap.
	 * @return string Weather icon emoji.
	 */
	private function get_weather_icon( $weather_code ) {
		// Weather condition code to emoji mapping.
		$icon_map = array(
			// Clear sky.
			800 => '☀️',
			// Few clouds.
			801 => '🌤️',
			// Scattered clouds.
			802 => '⛅',
			// Broken/overcast clouds.
			803 => '☁️',
			804 => '☁️',
			// Rain.
			500 => '🌦️',
			501 => '🌧️',
			502 => '🌧️',
			503 => '🌧️',
			504 => '🌧️',
			511 => '🌧️',
			520 => '🌦️',
			521 => '🌧️',
			522 => '🌧️',
			531 => '🌧️',
			// Drizzle.
			300 => '🌦️',
			301 => '🌦️',
			302 => '🌦️',
			310 => '🌦️',
			311 => '🌦️',
			312 => '🌦️',
			313 => '🌦️',
			314 => '🌦️',
			321 => '🌦️',
			// Thunderstorm.
			200 => '⛈️',
			201 => '⛈️',
			202 => '⛈️',
			210 => '⛈️',
			211 => '⛈️',
			212 => '⛈️',
			221 => '⛈️',
			230 => '⛈️',
			231 => '⛈️',
			232 => '⛈️',
			// Snow.
			600 => '🌨️',
			601 => '❄️',
			602 => '❄️',
			611 => '🌨️',
			612 => '🌨️',
			613 => '🌨️',
			615 => '🌨️',
			616 => '🌨️',
			620 => '🌨️',
			621 => '❄️',
			622 => '❄️',
			// Atmosphere.
			701 => '🌫️', // Mist.
			711 => '🌫️', // Smoke.
			721 => '🌫️', // Haze.
			731 => '🌫️', // Dust.
			741 => '🌫️', // Fog.
			751 => '🌫️', // Sand.
			761 => '🌫️', // Dust.
			762 => '🌫️', // Ash.
			771 => '🌬️', // Squall.
			781 => '🌪️', // Tornado.
		);

		return isset( $icon_map[ $weather_code ] ) ? $icon_map[ $weather_code ] : '🌤️';
	}

	/**
	 * Get weather icon map for reference.
	 *
	 * @return array Weather icon mapping.
	 */
	private function get_weather_icon_map() {
		return array(
			'clear'        => '☀️',
			'few_clouds'   => '🌤️',
			'clouds'       => '☁️',
			'rain'         => '🌧️',
			'thunderstorm' => '⛈️',
			'snow'         => '❄️',
			'mist'         => '🌫️',
		);
	}
}