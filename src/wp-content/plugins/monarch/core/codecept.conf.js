const BROWSER = 'chrome';
const SITE    = 'divi';
const OUTPUT  = `./tests/_output/${SITE}`;

let TEST_PRODUCTS        = process.env.TEST_PRODUCTS || '*';
let EXCLUDED_TEST_SUITES = process.env.EXCLUDED_TEST_SUITES || '';

if ( TEST_PRODUCTS ) {
	TEST_PRODUCTS = TEST_PRODUCTS.includes(',') ? `{${process.env.TEST_PRODUCTS}}` : TEST_PRODUCTS;
}

if ( EXCLUDED_TEST_SUITES ) {
	EXCLUDED_TEST_SUITES = `!(${process.env.EXCLUDED_TEST_SUITES})`;
}

global.SITE = SITE;

const config = {
	tests: `./tests/acceptance/{*.js,${TEST_PRODUCTS}/**/${EXCLUDED_TEST_SUITES}*.js}`,
	timeout:  10000,
	output:   OUTPUT,
	multiple: {
		all: {
			browsers: [
				{
					browser:             'chrome',
					desiredCapabilities: {
						os:         'Windows',
						os_version: '10',
					},
				},
				// {
				// 	browser:             'safari',
				// 	desiredCapabilities: {
				// 		os:                                    'OS X',
				// 		os_version:                            'Sierra',
				// 		'browserstack.safari.allowAllCookies': true,
				// 	},
				// },
			],
		},
	},
	helpers:  {
		WebDriverIO: {
			driver:              'browserstack',
			user:                process.env.BROWSERSTACK_USER,
			key:                 process.env.BROWSERSTACK_KEY,
			url:                 `http://${process.env.VIRTUAL_HOST}/${SITE}`,
			host:                'hub-cloud.browserstack.com',
			port:                80,
			windowSize:          'maximize',
			smartWait:           5000,
			restart:             false,
			keepCookies:         true,
			browser:             'chrome',
			uniqueScreenshotNames: true,
			waitForTimeout:        10000,
			desiredCapabilities: {
				resolution:                     '1920x1080',
				project:                        process.env.BS_PROJECT,
				build:                          process.env.BS_BUILD,
				name:                           process.env.BS_NAME,
				clear_cookies:                  false,
				acceptSslCerts:                 true,
				'browserstack.debug':           true,
				'browserstack.local':           true,
				'browserstack.console':         'errors',
				'browserstack.timezone':        'Los_Angeles',
				'browserstack.localIdentifier': process.env.CIRCLE_BUILD_NUM,
			},
		},
		BaseHelper:  {
			require: './tests/acceptance/_support/base-helper.js',
			profile: BROWSER.toLowerCase(),
			site:    SITE,
		},
		Mochawesome: {
			uniqueScreenshotNames: true,
		},
	},
	include:  {
		I:             './tests/acceptance/AcceptanceTester.js',
		login_page:    './tests/acceptance/_support/pages/Login.js',
		e_panel:       './tests/acceptance/_support/pages/EPanel.js',
		post_new_page: './tests/acceptance/_support/pages/PostNew.js',
		divi_builder:  './tests/acceptance/_support/fragments/DiviBuilder.js',
		divi_library:  './tests/acceptance/_support/fragments/Library.js',
		VB:            './tests/acceptance/_support/fragments/VisualBuilder.js',
	},
	mocha:    {
		reporterOptions: {
			reportDir: `${OUTPUT}/report`,
		},
	},
};


module.exports.config = config;
