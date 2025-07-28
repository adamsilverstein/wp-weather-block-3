/**
 * Use this file for any public-facing functionality.
 */

/**
 * Initialize weather blocks on the frontend.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	const weatherBlocks = document.querySelectorAll(
		'.weather-block__wrapper'
	);

	weatherBlocks.forEach( function ( block ) {
		initializeWeatherBlock( block );
	} );
} );

/**
 * Initialize a single weather block.
 *
 * @param {Element} block The weather block element.
 */
function initializeWeatherBlock( block ) {
	const location = block.dataset.location;
	const units = block.dataset.units || 'metric';

	if ( ! location ) {
		showError( block, 'No location specified.' );
		return;
	}

	fetchWeatherData( block, location, units );
}

/**
 * Fetch weather data for a block.
 *
 * @param {Element} block    The weather block element.
 * @param {string}  location The location to fetch weather for.
 * @param {string}  units    The temperature units.
 */
async function fetchWeatherData( block, location, units ) {
	try {
		const formData = new FormData();
		formData.append( 'action', 'get_weather_data' );
		formData.append( 'location', location );
		formData.append( 'units', units );
		formData.append( 'nonce', window.weatherBlockAjax?.nonce || '' );

		const response = await fetch(
			window.weatherBlockAjax?.ajaxUrl || '/wp-admin/admin-ajax.php',
			{
				method: 'POST',
				body: formData,
			}
		);

		const result = await response.json();

		if ( result.success ) {
			displayWeatherData( block, result.data );
		} else {
			showError( block, result.data || 'Failed to fetch weather data.' );
		}
	} catch ( error ) {
		showError( block, 'Network error. Please try again later.' );
	}
}

/**
 * Display weather data in a block.
 *
 * @param {Element} block       The weather block element.
 * @param {Object}  weatherData The weather data to display.
 */
function displayWeatherData( block, weatherData ) {
	const displayMode = block.dataset.displayMode || 'auto';

	block.innerHTML = `
		<div class="weather-block__display weather-block__display--${ displayMode }">
			<div class="weather-block__header">
				<h3 class="weather-block__city">
					${ weatherData.city }${
						weatherData.country ? `, ${ weatherData.country }` : ''
					}
				</h3>
			</div>
			<div class="weather-block__content">
				<div class="weather-block__temperature">
					<span class="weather-block__temp-value">${ weatherData.temperature }</span>
					<span class="weather-block__temp-unit">${ weatherData.temp_unit }</span>
				</div>
				<div class="weather-block__icon">
					${ weatherData.icon }
				</div>
			</div>
			<div class="weather-block__details">
				<p class="weather-block__description">${ weatherData.description }</p>
				<p class="weather-block__humidity">
					Humidity: ${ weatherData.humidity }%
				</p>
			</div>
		</div>
	`;
}

/**
 * Show an error message in a block.
 *
 * @param {Element} block   The weather block element.
 * @param {string}  message The error message to display.
 */
function showError( block, message ) {
	block.innerHTML = `
		<div class="weather-block__error">
			<p>⚠️ ${ message }</p>
		</div>
	`;
}
