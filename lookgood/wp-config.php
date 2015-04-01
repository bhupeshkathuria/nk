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
define('DB_NAME', 'netakart_dev');

/** MySQL database username */
define('DB_USER', 'netakart_root');

/** MySQL database password */
define('DB_PASSWORD', 'india@123');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'Tp>5]?O/5$j^ru-,WqXR5)q-s:pZFHj[{HHM};m+]9:|Tg}tydVXv6lRQoW<P pH');
define('SECURE_AUTH_KEY',  'LaJ)9vzHV<LL{z0`sN+Y*X0B8v55`V4D&Mzp*zX`<z24dd>753fJhZk-i)FdBqs{');
define('LOGGED_IN_KEY',    '2v}!GY@8=/$Uai:tgz+=JXg[0#/ 8>paxC{/bjSQ!tdj]7#)NoABr+Dxd+1Qq0Rt');
define('NONCE_KEY',        'JB`1@MFOAml~{BB`|$cq<~^z)3l6X;!@Y4fQE7*r+r~fmP-368}qoaR{6bVKxA<_');
define('AUTH_SALT',        'TE:8K:;-on=E$sZ`hb-!fv[RlKZ>+U5Jw/VH17Tefqpz;co$G=K}g[6.Q#9Jp#+{');
define('SECURE_AUTH_SALT', 'D?e|Np1f#M_d[xm/@2QPF5uCc[!.XZb3LWuy_i09U9X|,|H?1nco5t*zwb6K%BQP');
define('LOGGED_IN_SALT',   'A?NO~2Ld`kB!l9jVOJE]7P170<`6.TYDnTB.t1{_s.kq]83r#qJ_xbcu+]Yu`68N');
define('NONCE_SALT',       'I[H=7p|Tf.u<TR8iAg:+UAUCs#~L-&<Y9[Qy^n)Ks< DT?2E+`@=Gc$cJ|c|R})h');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'clay_wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
