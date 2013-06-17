<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'campanha');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('DOMAIN_CURRENT_SITE', 'campanha.mu' );


/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'XAp{X PZZvR%qC?02%U!-i&Nhv%xZFfRgA.F:Hh<JJSsU{Yh{r]jI{N&X[BF]aGy');
define('SECURE_AUTH_KEY',  'rLlT|Jbf]:Ii5+N)3!6-}wPD|+mXp*#N#siX|}!YgN7t7(~_RwQFFo6usT,(>r1g');
define('LOGGED_IN_KEY',    'eVps1dn|+ERF#CU;oA@q5QJUEb@f{AA-Y(B1l,eoT_#8B+<>gpq_ss+> MWe|CPV');
define('NONCE_KEY',        'c<|[B%7HCRj2#Z{k*FM~EB3LEH5Rm=} pJS2VZ dQ&zCzbEg]).`%EZ1a?b@25|h');
define('AUTH_SALT',        'OErjE+`V@%RN]+}iA/|fc~o/qD`Clb/t -l+EY+48gpYuw6=NlW*%1tv,NIL|%Q|');
define('SECURE_AUTH_SALT', '3PB(-?Y[j7oU},Uq<CG-RAHy>2R}Pprk57YJCisM,$Pa:$=|0u5lgd:_S6v8|umh');
define('LOGGED_IN_SALT',   's#z4_ X6s[cPWM`:SfI/X3a+EFn2fp5xt km=Mc^jCfMxNVV/+lI8?PjdU-ETdRi');
define('NONCE_SALT',       'k(x8B>sLS-I1t~ljMXD@DJ@G%%lf{_{%@Js(>DRVLQt@(bfYAnhO  T2@Tiq}n3a');

define('WP_ALLOW_MULTISITE', true);

define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
$base = '/';
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/**
 * Redirect to page below when user try to access
 * a subdomain that doesn't exist.
 */
define('NOBLOGREDIRECT', 'http://campanha.mu');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
