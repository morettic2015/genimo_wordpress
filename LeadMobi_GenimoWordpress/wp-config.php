<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wpmultisite_com');
define('FS_METHOD','direct');
define('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);
/** MySQL database username */
define('DB_USER', 'wpms');

/** MySQL database password */
define('DB_PASSWORD', 'W0rdpr3ss@');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**define( 'WP_ALLOW_MULTISITE', true );*/
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'dev.citywatch.com.br');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);


/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ':*sYJph{RKK&C6<0FG?C>f,3Oq@H{P.pTx!;k/5_SDi[2ZKj2td<yw6c6aqeg`PB');
define('SECURE_AUTH_KEY',  '!|3k^uPvu<j>DL,-pt80C y-X|.2T/HIG8.I72Q4B`;*N,/*2*~[y10L4ySw*C;&');
define('LOGGED_IN_KEY',    'DJTxg1D%gLD,AFoO84.^j#SJg]..we!e$ WueRjt_ACJrxUB!Csz71pgAF8(?SP}');
define('NONCE_KEY',        '?OGG9t/ycC!yT GT&8e+%+@NH^?PG0Y *0LhwGh+{H=dMGVH8_Z_a`9Bk~p?T+`,');
define('AUTH_SALT',        '@mc56NaG[]H/PZgaFj<Lqr[PX1m5Kkn9fS2Mq|o2&[SOfgY&+./tOsuWIO53GojV');
define('SECURE_AUTH_SALT', 'tB;f)lR`~h~!l0DnwU$%(ri9FfRNDC*iQ&:/z8E6.;A;;}f9~I?gPxaDk]k>[E[L');
define('LOGGED_IN_SALT',   'a++p: )8  l19eo Git:GEmab^L2gbJj>krxor<4#%c[sjGT:7VAc+IzSQTpqILi');
define('NONCE_SALT',       '0niA{L4@R{m-?5^3&%6noQGUIV%W;sJ&*tu37d/L;f4NW|FV~HTx.,.wUI2P*Eam');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
