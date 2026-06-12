<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'hortivisionai' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '!8:O ;sYW1KdjY<gMYB>W{R#4wVm8H@KoBnQblcdkcXZi.C36E,{R8kCb,U6IQDr' );
define( 'SECURE_AUTH_KEY',  'QnEg]#+NM,y6hQ2 m)slJ=%>gP6z_QPPLOmb2bb$)o+&-.FUes1mYbtf$iidq/7U' );
define( 'LOGGED_IN_KEY',    'sbO=.J#lfPBc^S?&//e,]=vIy3DEV>cih7}oHYF6=var3G8ZF4MkMZj=// <W&17' );
define( 'NONCE_KEY',        'Oc|tyUdVZ0S^;~DV}nR@t1S,o|P&J1/>@]+[[9nct~ea(Y4-m4GUFMoL%JdF7-:}' );
define( 'AUTH_SALT',        '4Ch.1`L4uT2B9hWxBitJa2!z[kRN==GPS<tU F[q&geugmVYKyUUubcz@7oLbonn' );
define( 'SECURE_AUTH_SALT', 'iS`,j6w`HNNIRl/DkxKPZ1>QGNgQ<B/LtU1bI6R^*[QF$<`7*VF8qU1e!!]o|({+' );
define( 'LOGGED_IN_SALT',   'Dz7lixj5@l5Pxm~?Kzx~0+`&R.dS/;%A[^`MqM/ARBG6xC$Qx)$(0oWs+%3J8imR' );
define( 'NONCE_SALT',       'sLa^#30}xf{?ZngQtMyg.KJK<5h@}3bDw}28?{cCq7KTI^D`Y5Lu}c/EU[p6m^2N' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
