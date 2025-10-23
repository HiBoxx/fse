<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/**
 * SECURITY: For production, use environment variables instead of hardcoded values
 * Example: define( 'DB_NAME', getenv('DB_NAME') ?: 'local' );
 */

/** The name of the database for WordPress */
define( 'DB_NAME', getenv('DB_NAME') ?: 'local' );

/** Database username */
define( 'DB_USER', getenv('DB_USER') ?: 'root' );

/** Database password */
define( 'DB_PASSWORD', getenv('DB_PASSWORD') ?: 'root' );

/** Database hostname */
define( 'DB_HOST', getenv('DB_HOST') ?: 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '|0=V/u(~7|C;}Hlk0e`o(OHRYNf[!&?sxi^=$X*/~}QUB0,mZU@@1!4L =WpxBjb' );
define( 'SECURE_AUTH_KEY',   '(YnFF,6J3Z,h[ B:kmLtXwZQn^6}2+2MB0#^x^ &M`3Rtepa;;@#k}(~ l@^Vji2' );
define( 'LOGGED_IN_KEY',     'Ln_!}U|-FJci`q{+71w^A4+2I;,!Lfq#KW20OeH,N+Y<+cy8]P -tuq1`oiAa<Bd' );
define( 'NONCE_KEY',         ':h(}a !PCQb9Wbk>:ICUbws2>S60HM2}$su}]E +GWknli[;Q]udnA~!^$LW[h%Z' );
define( 'AUTH_SALT',         'Z|^HRhCv@MZ?~B S/ n3r_{H<i3jte5Xr%0(45.HrvsQrUP!KHSvMO6Xi}uM9-XZ' );
define( 'SECURE_AUTH_SALT',  '.e$]a,~:*L9CI-cf[Ui9#HS&YmE.Zp5:F=L~oxHpB/Xa3&]K&L!b%J~;+]p.9!wZ' );
define( 'LOGGED_IN_SALT',    ')f^_l2l~cv^(&yH{r+Ips~}t:_5stw-.~@mt8m1XG)|o|acy}yQvC,5>b[Eoj#Mk' );
define( 'NONCE_SALT',        'VP}:u3TQ$)LF04+YkI2ePh87,Y6?h@vx/th}D(>Kv{PPS|64z]eE3Pk?uP(kl|A;' );
define( 'WP_CACHE_KEY_SALT', 'EY~n=lph}6:/La8lQ$(bwJhhfuijaCep[$m{,-URJ|rx`lL3gCSpZ~m..jsGjZpl' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

/**
 * Custom CGT Configuration
 */
// Email pour les notifications d'adhésion (peut être surchargé par variable d'environnement)
define( 'CGT_ADMIN_EMAIL', getenv('CGT_ADMIN_EMAIL') ?: 'webmaster.fsetud@cgt.fr' );

// Sécurité: Désactiver l'éditeur de fichiers dans l'admin
define( 'DISALLOW_FILE_EDIT', true );

// Sécurité: Limiter les révisions de posts
define( 'WP_POST_REVISIONS', 5 );

// Performance: Activer la compression
define( 'COMPRESS_CSS', true );
define( 'COMPRESS_SCRIPTS', true );



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );

// Définir WordPress en français
define( 'WPLANG', 'fr_FR' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
