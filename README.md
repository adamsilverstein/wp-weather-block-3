# Weather Block Plugin

A WordPress block plugin that displays current weather information for a specified location using the OpenWeatherMap API.

## Features

- **Location Input**: Enter any city name to display weather information
- **Temperature Units**: Choose between Celsius and Fahrenheit
- **Display Modes**: Light, Dark, or Auto (follows user preference)
- **Weather Information**: City, temperature, weather icon, description, and humidity
- **Caching**: Weather data is cached for 15 minutes to improve performance
- **Accessibility**: WCAG 2.1 AA compliant with proper ARIA roles and keyboard navigation
- **Responsive Design**: Works on all screen sizes
- **Error Handling**: User-friendly error messages with proper logging

## Requirements

- WordPress 6.7 or higher
- PHP 7.4 or higher
- OpenWeatherMap API key (free)

## Installation

1. Clone or download this repository
2. Install dependencies:
   ```bash
   npm install
   composer install
   ```
3. Build the plugin:
   ```bash
   npm run build
   ```
4. Upload the plugin folder to your WordPress `wp-content/plugins/` directory
5. Activate the plugin through the WordPress admin
6. Configure your OpenWeatherMap API key in Settings > Weather Block

## Getting an API Key

1. Visit [OpenWeatherMap](https://openweathermap.org/api)
2. Sign up for a free account
3. Navigate to your API keys section
4. Copy your API key
5. Enter it in the WordPress admin under Settings > Weather Block

## Usage

1. In the WordPress block editor, add a new block
2. Search for "Weather Block" and add it to your post or page
3. In the block settings sidebar, configure:
   - **Location**: Enter a city name (e.g., "New York", "London")
   - **Temperature Units**: Toggle between Celsius and Fahrenheit
   - **Display Mode**: Choose Light, Dark, or Auto
4. The weather information will automatically load and display

## Development

### Available Scripts

- `npm run start` - Start development mode with hot reloading
- `npm run build` - Build the plugin for production
- `npm run lint:js` - Lint JavaScript files
- `npm run lint:css` - Lint CSS/SCSS files
- `npm run test:unit` - Run JavaScript unit tests
- `npm run plugin-zip` - Create a distributable ZIP file

### PHP Scripts

- `composer lint` - Run PHPCS linting
- `composer lint:fix` - Fix PHPCS issues automatically
- `composer test` - Run PHPUnit tests
- `composer analyze` - Run PHPStan analysis

### Code Quality

This plugin follows WordPress coding standards and includes:

- **PHPCS** with WordPress ruleset
- **PHPStan** at level 5
- **ESLint** for JavaScript
- **Jest** for JavaScript testing
- **PHPUnit** for PHP testing
- **Playwright** for end-to-end testing

### Testing

Run all tests:
```bash
# PHP tests
composer test

# JavaScript tests
npm run test:unit

# End-to-end tests (requires WordPress test environment)
npm run test:e2e
```

## Architecture

### Files Structure

```
weather-block/
├── src/weather-block/          # Block source files
│   ├── block.json             # Block configuration
│   ├── edit.js                # Editor component
│   ├── save.js                # Save component
│   ├── view.js                # Frontend script
│   ├── editor.scss            # Editor styles
│   └── style.scss             # Frontend styles
├── includes/                   # PHP classes
│   ├── class-weather-api.php   # API handler
│   └── class-weather-admin.php # Admin settings
├── tests/                      # Test files
│   ├── php/                   # PHPUnit tests
│   └── js/                    # Jest tests
├── weather-block.php          # Main plugin file
├── package.json               # Node.js dependencies
├── composer.json              # PHP dependencies
└── README.md                  # This file
```

### API Integration

The plugin uses the OpenWeatherMap Current Weather Data API:
- **Endpoint**: `https://api.openweathermap.org/data/2.5/weather`
- **Caching**: 15-minute transients to minimize API calls
- **Error Handling**: Proper error logging and user-friendly messages

### Security

- All API requests are authenticated with nonces
- All output is properly escaped
- Input validation and sanitization
- Secure AJAX handling

## Customization

### Styling

The plugin includes CSS custom properties for easy customization:

```css
.wp-block-weather-block-weather-block {
  --weather-block-background: #74b9ff;
  --weather-block-text-color: #ffffff;
  --weather-block-border-radius: 12px;
}
```

### Filters and Actions

Available WordPress filters:

- `weather_block_api_data` - Filter weather data before caching
- `weather_block_cache_time` - Filter cache expiration time
- `weather_block_error_message` - Filter error messages

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Run the test suite
6. Submit a pull request

## Changelog

### 0.1.0
- Initial release
- Basic weather display functionality
- OpenWeatherMap API integration
- Admin settings page
- Comprehensive test suite
- Accessibility features

## License

This plugin is licensed under the GPL-2.0-or-later license. See the LICENSE file for details.

## Support

For support and questions, please use the GitHub issues page.

## Credits

- Built with [WordPress Block Editor](https://developer.wordpress.org/block-editor/)
- Weather data from [OpenWeatherMap](https://openweathermap.org/)
- Icons and styling inspired by modern weather apps