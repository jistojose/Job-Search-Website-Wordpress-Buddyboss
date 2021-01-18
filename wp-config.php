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
define( 'DB_NAME', 'dbfdmjzdzxegim' );

/** MySQL database username */
define( 'DB_USER', 'u7nzorkad0gpg' );

/** MySQL database password */
define( 'DB_PASSWORD', 'sqddgdndlkty' );

/** MySQL hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'ZaKT[a16iFNQAFjMp!QaKb H5hUz&*Sf&n7%KfCYl87;&ZM#hVhA{(/7L}XOBq-n' );
define( 'SECURE_AUTH_KEY',   ';SkH?5OlA0%`Oq;./VY;&B@^<,avMg+>2bfnr#Ebs:?:4S#cf+siX^v(>67_{rr-' );
define( 'LOGGED_IN_KEY',     'pc~xUwn$fw57ZzR4%|<MMqA&!$VuF}`CQ9Gh6M@bo 1{]M1UoptB^RVl;FRE~95y' );
define( 'NONCE_KEY',         'JJ*!Cvk/lju%?=8k8;R>mdxI{(9NMv1&#&=i:(%a#rp><&;m4~upnyn`HGT0uU#.' );
define( 'AUTH_SALT',         'O~Jx_#+o#DV#-Y7weh9bA6!;(XokZ?(_TJw}ZO_jW& x).;SzXq_9~vbpj4 /,V)' );
define( 'SECURE_AUTH_SALT',  '8>%~/,i5{o_<t>>0b=^wTLqO?vg?B~ulNIqhzudx.N$VvN`D?G*75%{O|Z}xq,YY' );
define( 'LOGGED_IN_SALT',    '51<%=~vB>aLAV_ 68Nr%xDmN5vs_,GchPoF,5YIk6D G:ibs!^.Q[.tKX)gF>NYV' );
define( 'NONCE_SALT',        '<z=smnT&zPR+?C7oj)4Kwzo6FTi+$[?r0A8DN^}OABPqP-YN]q 4Y$}5QY;atU*1' );
define( 'WP_CACHE_KEY_SALT', ',^+}A(AD[3A!nr7#BF&,:hI4+Gc5bpYe~n0:7:C]FRH:1=aHAQ@uy_st-.j+^(f5' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ltf_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system
