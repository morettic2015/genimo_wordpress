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
define( 'WP_DEBUG', true );
define('FS_METHOD','direct');
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'imobiliaria_com');

/** MySQL database username */
define('DB_USER', 'imobi');

/** MySQL database password */
define('DB_PASSWORD', 'M0r3tt02013@');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '>0$?8#aohl5$|L&1a|4bg&Mn,0wo=z KB4J= L3(Ws]aX (!^,-M2~DtMZ<(IB5L');
define('SECURE_AUTH_KEY',  'W2TyC?E+*`e/b2G=98(a<GaQS,L~<P4]<%,u0/=BR{eQ5K-pr6&IOF{9& ATus#+');
define('LOGGED_IN_KEY',    '~N9KC,b~ws_DBQ<[qIpS_e)oh3Nz=?(h2bP7$*RBYMz.}G<X%!8^U^F$-oE~{xXC');
define('NONCE_KEY',        'oX,[VS]VgJHy,~i@0q/!gQC@4Q1L*MRG:s68(vr:iG#O*}SQ.PZM@^v$MHBbGWRk');
define('AUTH_SALT',        '}${*`nHUgV!(a53kq=nCaX*m4X|qF>JojRCT9)C|5XA,&S,4zY|G,8w4v&V?JTU3');
define('SECURE_AUTH_SALT', '=|6nu`{:QU`H7R +p,L@1HG~=Q[:?eR*%W>LOw&S;F5Y8F0QqO?pMFtK27?M2Zb/');
define('LOGGED_IN_SALT',   '+s#TGM*onChk-!!=jZz]w^ct#jPhBV`GFyDCJL,^Lxeo2FB*$h4!8h ~Ye$^ADO$');
define('NONCE_SALT',       ';_@4b#&&{ep(N*4+*sET~2wOsG8D]qCp#LR(`.UqBuT?nz6e6t{*2/]r+=b=D}/X');

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
