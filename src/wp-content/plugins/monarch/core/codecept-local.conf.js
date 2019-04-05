let BROWSER;
let SITE;
let SUITES;

try {
	[SITE, ...SUITES] = process.profile.split( ',' );
} catch ( err ) {
	BROWSER = 'chrome';
	SITE    = 'divi';
}

const OUTPUT     = `./tests/_output/${SITE}`;

global.SITE = SITE;


const config = {
	tests:    `**/acceptance/{${SITE},divi-builder}/**/**.js`,
	timeout:  10000,
	output:   OUTPUT,
	helpers:  {
		WebDriverIO: {
			url: `http://${process.env.VIRTUAL_HOST}/${SITE}`,
			browser: "chrome",
			debug: true,
			restart: false,
			keepCookies: true,
			debug_log_entries: 20,
			desiredCapabilities: {
				browserName: BROWSER,
				os: 'OS X',
				os_version:  'Sierra',
				resolution: '1920x1080',
				project: process.env.BS_PROJECT,
				build: process.env.BS_BUILD,
				name: process.env.BS_NAME,
				clear_cookies: false,
				clearCookies: false,
				acceptSslCerts: true,
			},
		},
		BaseHelper:  {
			require: './tests/acceptance/_support/base-helper.js',
			profile: BROWSER.toLowerCase(),
			site:    SITE,
		},
	},
	include:  {
		I:             './tests/acceptance/AcceptanceTester.js',
		login_page:    './tests/acceptance/_support/pages/Login.js',
		e_panel:       './tests/acceptance/_support/pages/EPanel.js',
		post_new_page: './tests/acceptance/_support/pages/PostNew.js',
		divi_builder:  './tests/acceptance/_support/fragments/DiviBuilder.js',
		divi_library:  './tests/acceptance/_support/fragments/Library.js',
	},
	mocha:    {
		reporterOptions: {
			reportDir: `${OUTPUT}/report`,
		},
	},
};


module.exports.config = config;
