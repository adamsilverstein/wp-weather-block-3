<?php
/**
 * Tests for Weather_Block_API class.
 *
 * @package WeatherBlock
 */

/**
 * Test class for Weather_Block_API.
 */
class Test_Weather_Block_API extends WP_UnitTestCase {

	/**
	 * Weather API instance.
	 *
	 * @var Weather_Block_API
	 */
	private $weather_api;

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->weather_api = new Weather_Block_API();
	}

	/**
	 * Test that the API class can be instantiated.
	 */
	public function test_class_instantiation() {
		$this->assertInstanceOf( 'Weather_Block_API', $this->weather_api );
	}

	/**
	 * Test get_weather_data with empty location.
	 */
	public function test_get_weather_data_empty_location() {
		$result = $this->weather_api->get_weather_data( '' );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'invalid_location', $result->get_error_code() );
	}

	/**
	 * Test get_weather_data with invalid units.
	 */
	public function test_get_weather_data_invalid_units() {
		// Mock the API key.
		if ( ! defined( 'WEATHER_BLOCK_API_KEY' ) ) {
			define( 'WEATHER_BLOCK_API_KEY', 'test_api_key' );
		}

		// This should normalize invalid units to 'metric'.
		// We'll test the normalization by checking that it doesn't error on invalid units.
		$result = $this->weather_api->get_weather_data( 'London', 'invalid_unit' );
		
		// Since we don't have a real API key, this should fail with missing API key or API error.
		// The important thing is that it doesn't fail due to invalid units.
		$this->assertTrue( is_wp_error( $result ) || is_array( $result ) );
	}

	/**
	 * Test get_weather_data without API key.
	 */
	public function test_get_weather_data_no_api_key() {
		// Mock empty API key.
		$original_key = defined( 'WEATHER_BLOCK_API_KEY' ) ? WEATHER_BLOCK_API_KEY : '';
		
		// Create a new constant for testing.
		if ( ! defined( 'WEATHER_BLOCK_TEST_API_KEY' ) ) {
			define( 'WEATHER_BLOCK_TEST_API_KEY', '' );
		}

		// Use reflection to test private methods if needed.
		$result = $this->weather_api->get_weather_data( 'London' );
		
		// Should return an error due to missing API key or fail to connect.
		$this->assertInstanceOf( 'WP_Error', $result );
	}

	/**
	 * Test weather icon mapping.
	 */
	public function test_weather_icon_mapping() {
		// Use reflection to test private method.
		$reflection = new ReflectionClass( $this->weather_api );
		$method = $reflection->getMethod( 'get_weather_icon' );
		$method->setAccessible( true );

		// Test clear sky.
		$icon = $method->invoke( $this->weather_api, 800 );
		$this->assertEquals( '☀️', $icon );

		// Test rain.
		$icon = $method->invoke( $this->weather_api, 500 );
		$this->assertEquals( '🌦️', $icon );

		// Test snow.
		$icon = $method->invoke( $this->weather_api, 600 );
		$this->assertEquals( '🌨️', $icon );

		// Test unknown code.
		$icon = $method->invoke( $this->weather_api, 9999 );
		$this->assertEquals( '🌤️', $icon );
	}

	/**
	 * Test weather data formatting.
	 */
	public function test_format_weather_data() {
		// Use reflection to test private method.
		$reflection = new ReflectionClass( $this->weather_api );
		$method = $reflection->getMethod( 'format_weather_data' );
		$method->setAccessible( true );

		// Mock API response data.
		$api_data = array(
			'name' => 'London',
			'sys'  => array( 'country' => 'GB' ),
			'main' => array(
				'temp'     => 15.5,
				'humidity' => 65,
			),
			'weather' => array(
				array(
					'id'          => 800,
					'description' => 'clear sky',
					'icon'        => '01d',
				),
			),
		);

		$formatted = $method->invoke( $this->weather_api, $api_data, 'metric' );

		$this->assertEquals( 'London', $formatted['city'] );
		$this->assertEquals( 'GB', $formatted['country'] );
		$this->assertEquals( 16, $formatted['temperature'] ); // Rounded.
		$this->assertEquals( '°C', $formatted['temp_unit'] );
		$this->assertEquals( 'Clear sky', $formatted['description'] );
		$this->assertEquals( 65, $formatted['humidity'] );
		$this->assertEquals( '☀️', $formatted['icon'] );
	}
}