/**
 * Jest configuration for Weather Block plugin.
 */

module.exports = {
	preset: '@wordpress/jest-preset-default',
	setupFilesAfterEnv: [ '<rootDir>/tests/js/setup-tests.js' ],
	testMatch: [ '<rootDir>/tests/js/**/*.test.js' ],
	transform: {
		'^.+\\.[jt]sx?$': 'babel-jest',
	},
	moduleNameMapping: {
		'\\.(scss|css)$': 'identity-obj-proxy',
	},
	collectCoverageFrom: [ 'src/**/*.js', '!src/**/index.js' ],
	coverageReporters: [ 'text', 'html' ],
	testEnvironment: 'jsdom',
};
