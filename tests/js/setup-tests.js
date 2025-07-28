/* eslint-env jest */
/**
 * Jest setup file.
 */

// Mock WordPress globals and functions.
global.wp = {
	i18n: {
		__: jest.fn( ( text ) => text ),
		_e: jest.fn( ( text ) => text ),
		sprintf: jest.fn( ( text ) => text ),
	},
	element: {
		useState: jest.fn(),
		useEffect: jest.fn(),
	},
	blockEditor: {
		useBlockProps: jest.fn( () => ( {} ) ),
		InspectorControls: jest.fn( ( { children } ) => children ),
	},
	components: {
		PanelBody: jest.fn( ( { children } ) => children ),
		TextControl: jest.fn(),
		ToggleControl: jest.fn(),
		RadioControl: jest.fn(),
		Notice: jest.fn(),
		Spinner: jest.fn(),
	},
};

// Mock global window object.
global.window = {
	weatherBlockAjax: {
		ajaxUrl: '/wp-admin/admin-ajax.php',
		nonce: 'test-nonce',
	},
	fetch: jest.fn(),
};

// Mock console methods.
global.console = {
	...console,
	log: jest.fn(),
	error: jest.fn(),
	warn: jest.fn(),
};
