/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * WordPress components for the editor.
 */
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RadioControl,
	Notice,
	Spinner,
} from '@wordpress/components';

/**
 * React hooks.
 */
import { useState, useEffect } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates block attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { location, units, displayMode } = attributes;
	const [ weatherData, setWeatherData ] = useState( null );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ error, setError ] = useState( null );

	/**
	 * Fetch weather data when location or units change.
	 */
	useEffect( () => {
		if ( location ) {
			fetchWeatherData();
		} else {
			setWeatherData( null );
			setError( null );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ location, units ] );

	/**
	 * Fetch weather data from the API.
	 */
	const fetchWeatherData = async () => {
		setIsLoading( true );
		setError( null );

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
				setWeatherData( result.data );
			} else {
				setError(
					result.data ||
						__( 'Failed to fetch weather data.', 'weather-block' )
				);
			}
		} catch ( err ) {
			setError(
				__( 'Network error. Please try again.', 'weather-block' )
			);
		} finally {
			setIsLoading( false );
		}
	};

	/**
	 * Handle location input change.
	 *
	 * @param {string} value The new location value.
	 */
	const handleLocationChange = ( value ) => {
		setAttributes( { location: value } );
	};

	/**
	 * Handle units toggle change.
	 *
	 * @param {boolean} value Whether to use imperial units.
	 */
	const handleUnitsChange = ( value ) => {
		setAttributes( { units: value ? 'imperial' : 'metric' } );
	};

	/**
	 * Handle display mode change.
	 *
	 * @param {string} value The new display mode value.
	 */
	const handleDisplayModeChange = ( value ) => {
		setAttributes( { displayMode: value } );
	};

	/**
	 * Render weather display.
	 */
	const renderWeatherDisplay = () => {
		if ( isLoading ) {
			return (
				<div className="weather-block__loading">
					<Spinner />
					<p>{ __( 'Loading weather data…', 'weather-block' ) }</p>
				</div>
			);
		}

		if ( error ) {
			return (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			);
		}

		if ( ! weatherData ) {
			return (
				<div className="weather-block__placeholder">
					<p>
						{ __(
							'Enter a location to see weather information.',
							'weather-block'
						) }
					</p>
				</div>
			);
		}

		return (
			<div
				className={ `weather-block__display weather-block__display--${ displayMode }` }
			>
				<div className="weather-block__header">
					<h3 className="weather-block__city">
						{ weatherData.city }
						{ weatherData.country && `, ${ weatherData.country }` }
					</h3>
				</div>
				<div className="weather-block__content">
					<div className="weather-block__temperature">
						<span className="weather-block__temp-value">
							{ weatherData.temperature }
						</span>
						<span className="weather-block__temp-unit">
							{ weatherData.temp_unit }
						</span>
					</div>
					<div className="weather-block__icon">
						{ weatherData.icon }
					</div>
				</div>
				<div className="weather-block__details">
					<p className="weather-block__description">
						{ weatherData.description }
					</p>
					<p className="weather-block__humidity">
						{ __( 'Humidity:', 'weather-block' ) }{ ' ' }
						{ weatherData.humidity }%
					</p>
				</div>
			</div>
		);
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Weather Settings', 'weather-block' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Location', 'weather-block' ) }
						value={ location }
						onChange={ handleLocationChange }
						placeholder={ __(
							'Enter city name (e.g., New York)',
							'weather-block'
						) }
						help={ __(
							'Enter the name of the city to display weather information for.',
							'weather-block'
						) }
					/>
					<ToggleControl
						label={ __( 'Use Fahrenheit', 'weather-block' ) }
						checked={ units === 'imperial' }
						onChange={ handleUnitsChange }
						help={ __(
							'Toggle to switch between Celsius and Fahrenheit.',
							'weather-block'
						) }
					/>
					<RadioControl
						label={ __( 'Display Mode', 'weather-block' ) }
						selected={ displayMode }
						options={ [
							{
								label: __( 'Light', 'weather-block' ),
								value: 'light',
							},
							{
								label: __( 'Dark', 'weather-block' ),
								value: 'dark',
							},
							{
								label: __(
									'Auto (follows user preference)',
									'weather-block'
								),
								value: 'auto',
							},
						] }
						onChange={ handleDisplayModeChange }
						help={ __(
							'Choose how the weather block should be displayed.',
							'weather-block'
						) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>{ renderWeatherDisplay() }</div>
		</>
	);
}
