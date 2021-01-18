<?php
/*
Plugin Name: Feeds for YouTube Pro Developer
Plugin URI: http://smashballoon.com/youtube-feed
Description: Feeds for YouTube allows you to display completely customizable YouTube feeds from any channel.
Version: 1.2
Author: Smash Balloon
Author URI: http://smashballoon.com/
Text Domain: feeds-for-youtube
*/
/*
Copyright 2020  Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

define( 'SBY_STORE_URL', 'https://smashballoon.com/' );
define( 'SBY_PLUGIN_EDD_NAME', 'YouTube Feed Pro Developer' );

update_option('sby_license_key', '*********');
update_option('sby_license_status', 'valid');

// The ID of the product. Used for renewals
$sby_download_id = 762322; // 762236, 762320, 762322

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! defined( 'SBYVER' ) ) {
	define( 'SBYVER', '1.2' );
}
if ( ! defined( 'SBY_DBVERSION' ) ) {
	define( 'SBY_DBVERSION', 1.2 );
}
// Upload folder name for local image files for posts
if ( ! defined( 'SBY_UPLOADS_NAME' ) ) {
	define( 'SBY_UPLOADS_NAME', 'sby-local-media' );
}
// Name of the database table that contains instagram posts
if ( ! defined( 'SBY_ITEMS' ) ) {
	define( 'SBY_ITEMS', 'sby_items' );
}
// Name of the database table that contains feed ids and the ids of posts
if ( ! defined( 'SBY_ITEMS_FEEDS' ) ) {
	define( 'SBY_ITEMS_FEEDS', 'sby_items_feeds' );
}
// Name of the database table that contains feed ids and the ids of posts
if ( ! defined( 'SBY_CPT' ) ) {
	define( 'SBY_CPT', 'sby_videos' );
}
// Plugin Folder Path.
if ( ! defined( 'SBY_PLUGIN_DIR' ) ) {
	define( 'SBY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
// Plugin Folder URL.
if ( ! defined( 'SBY_PLUGIN_URL' ) ) {
	define( 'SBY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SBY_MINIMUM_WALL_VERSION' ) ) {
	define( 'SBY_MINIMUM_WALL_VERSION', '1.0' );
}

if ( function_exists( 'sby_init' ) ) {
	wp_die( "Please deactivate Feeds for YouTube.<br /><br />Back to the WordPress <a href='".get_admin_url( null, 'plugins.php' )."'>Plugins page</a>." );
} else {
	/**
	 * Define constants and load plugin files
	 *
	 * @since  2.0
	 */
	function sby_init() {
		// Plugin Base Name
		if ( ! defined( 'SBY_PLUGIN_BASENAME' ) ) {
			define( 'SBY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}
		// Cron Updating Cache Time 60 days
		if ( ! defined( 'SBY_CRON_UPDATE_CACHE_TIME' ) ) {
			define( 'SBY_CRON_UPDATE_CACHE_TIME', 60 * 60 * 24 * 60 );
		}
		// Plugin Base Name
		if ( ! defined( 'SBY_BACKUP_PREFIX' ) ) {
			define( 'SBY_BACKUP_PREFIX', '!' );
		}
		if ( ! defined( 'SBY_USE_BACKUP_PREFIX' ) ) {
			define( 'SBY_USE_BACKUP_PREFIX', '&' );
		}
		if ( ! defined( 'SBY_CHANNEL_CACHE_PREFIX' ) ) {
			define( 'SBY_CHANNEL_CACHE_PREFIX', '~sby_' );
		}
		// Max Records in Database for Image Resizing
		if ( ! defined( 'SBY_MAX_RECORDS' ) ) {
			define( 'SBY_MAX_RECORDS', 100 );
		}
		if ( ! defined( 'SBY_MAX_SINGLE_PAGE' ) ) {
			define( 'SBY_MAX_SINGLE_PAGE', 50 );
		}
		if ( ! defined( 'SBY_TEXT_DOMAIN' ) ) {
			define( 'SBY_TEXT_DOMAIN', 'feeds-for-youtube' );
		}
		if ( ! defined( 'SBY_SLUG' ) ) {
			define( 'SBY_SLUG', 'youtube-feed' );
		}
		if ( ! defined( 'SBY_SEARCH_SLUG' ) ) {
			define( 'SBY_SEARCH_SLUG', 'youtube-feed-search' );
		}
		if ( ! defined( 'SBY_SEARCH_NAME' ) ) {
			define( 'SBY_SEARCH_NAME', 'sbys' );
		}
		if ( ! defined( 'SBY_PLUGIN_NAME' ) ) {
			define( 'SBY_PLUGIN_NAME', __( 'Feeds for YouTube', SBY_TEXT_DOMAIN ) );
		}
		if ( ! defined( 'SBY_INDEF_ART' ) ) {
			define( 'SBY_INDEF_ART', __( 'a', SBY_TEXT_DOMAIN ) );
		}
		if ( ! defined( 'SBY_SOCIAL_NETWORK' ) ) {
			define( 'SBY_SOCIAL_NETWORK', __( 'YouTube', SBY_TEXT_DOMAIN ) );
		}
		if ( ! defined( 'SBY_SETUP_URL' ) ) {
			define( 'SBY_SETUP_URL', 'https://smashballoon.com/youtube-feed/free');
		}
		if ( ! defined( 'SBY_SUPPORT_URL' ) ) {
			define( 'SBY_SUPPORT_URL', 'https://smashballoon.com/youtube-feed/support');
		}
		if ( ! defined( 'SBY_OAUTH_PROCESSOR_URL' ) ) {
			define( 'SBY_OAUTH_PROCESSOR_URL', 'https://smashballoon.com/youtube-login/?return_uri=');
		}
		if ( ! defined( 'SBY_DEMO_URL' ) ) {
			define( 'SBY_DEMO_URL', 'https://smashballoon.com/youtube-feed/demo' );
		}
		if ( ! defined( 'SBY_PRO_LOGO' ) ) {
			define( 'SBY_PRO_LOGO', 'https://smashballoon.com/wp-content/themes/smashballoon/img/smash-balloon-logo-small.png' );
		}

		global $sby_settings;
		$sby_settings = get_option( 'sby_settings', array() );
		$sby_settings = wp_parse_args( $sby_settings, sby_settings_defaults() );

		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-vars.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/sby-functions.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-api-connect.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-cron-update.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-display-elements.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-feed.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-parse.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-posts-manager.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-rss-connect.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-settings.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/class-sby-wp-post.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/widget.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/blocks/class-sby-blocks.php';
		include_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/admin/class-sby-tracking.php';

		$sby_blocks = new SBY_Blocks();

		if ( $sby_blocks->allow_load() ) {
			$sby_blocks->load();
		}

		if ( is_admin() ) {
			require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/admin/admin-functions.php';
			require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/admin/class-sbspf-admin.php';
			require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/admin/class-sby-admin.php';
			sby_admin_init();
		}

		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-display-elements-pro.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-feed-pro.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-parse-pro.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-search.php';
		$sby_search = new SBY_Search();
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-settings-pro.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-api-connect-pro.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-cpt.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-cron-update-pro.php';
		$sby_cpt = new SBY_CPT();
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-video-details-query.php';
		require_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/pro/class-sby-video-query.php';

		global $sby_posts_manager;
		$sby_posts_manager = new SBY_Posts_Manager( 'sby', get_option( 'sby_errors', array() ), get_option( 'sby_ajax_status', array( 'tested' => false, 'successful' => false ) ) );
	}
	add_action( 'plugins_loaded', 'sby_init' );

	function sby_register_cpt() {
		register_post_type( SBY_CPT, array(
			'label'           => SBY_SOCIAL_NETWORK,
			'labels'          => array(
				'name'          => SBY_SOCIAL_NETWORK . ' ' . __( 'Videos', SBY_TEXT_DOMAIN ),
				'singular_name' => __( SBY_SOCIAL_NETWORK . ' ' . 'Video', SBY_TEXT_DOMAIN ),
				'add_new' => __( 'Add New Video', SBY_TEXT_DOMAIN ),
				'add_new_item' => __( 'Add New Video', SBY_TEXT_DOMAIN ),
				'edit_item'          => __( 'Edit Video', SBY_TEXT_DOMAIN ),
				'view_item'          => __( 'View Video', SBY_TEXT_DOMAIN ),
				'all_items'          => __( 'All Videos', SBY_TEXT_DOMAIN ),
			),
			'public'          => true,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-video-alt3',
			'rewrite'         => array ( 'slug' => 'videos' ),
			'has_archive'     => true,
			'hierarchical'    => false,
			'capability_type' => array( 'sby_video', 'sby_videos' ),
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ) //'comments'
		) );
	}

	add_action( 'init', 'sby_register_cpt' );

	function sby_add_caps() {
		global $wp_roles;

		$pto = get_post_type_object( SBY_CPT );

		$admin_caps = array(
			'edit_' . SBY_CPT,
			'read_' . SBY_CPT,
			'delete_' . SBY_CPT,
			'edit_' . SBY_CPT,
			'edit_others_' . SBY_CPT,
			'publish_' . SBY_CPT,
			'read_private_' . SBY_CPT,
			'read',
			'delete_' . SBY_CPT,
			'delete_private_' . SBY_CPT,
			'delete_published_' . SBY_CPT,
			'delete_others_' . SBY_CPT,
			'edit_private_' . SBY_CPT,
			'edit_published_' . SBY_CPT,
		);
		$author_caps = array(
			'edit_' . SBY_CPT,
			'read_' . SBY_CPT,
			'delete_' . SBY_CPT,
			'edit_' . SBY_CPT,
			'publish_' . SBY_CPT,
			'read',
			'delete_' . SBY_CPT,
			'delete_published_' . SBY_CPT,
			'edit_published_' . SBY_CPT,
		);

		if ( ! empty( $pto ) ) {
			foreach ( array( 'administrator', 'editor' ) as $role_id ) {
				foreach ( $admin_caps as $cap ) {
					$wp_roles->add_cap( $role_id, $cap );
				}
			}
			foreach ( $author_caps as $cap ) {
				$wp_roles->add_cap( 'author', $cap );
			}
		}
	}
	add_action( 'admin_init', 'sby_add_caps', 90 );

	/**
	 * Add the custom interval of 30 minutes for cron caching
	 *
	 * @param  array $schedules current list of cron intervals
	 *
	 * @return array
	 *
	 * @since  2.0
	 */
	function sby_cron_custom_interval( $schedules ) {
		$schedules['sby30mins'] = array(
			'interval' => 30 * 60,
			'display'  => __( 'Every 30 minutes' )
		);

		return $schedules;
	}

	add_filter( 'cron_schedules', 'sby_cron_custom_interval' );

	/**
	 * Create database tables, schedule cron events, initiate capabilities
	 *
	 * @param  bool $network_wide is a multisite network activation
	 *
	 * @since  2.0 database tables and capabilties created
	 * @since  1.0
	 */
	function sby_activate( $network_wide ) {
		include_once trailingslashit( SBY_PLUGIN_DIR ) . 'inc/sby-functions.php';
		//Clear page caching plugins and autoptomize
		if ( is_callable( 'sby_clear_page_caches' ) ) {
			sby_clear_page_caches();
		}

		//Run cron twice daily when plugin is first activated for new users
		if ( ! wp_next_scheduled( 'sby_cron_job' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'sby_cron_job' );
		}

		$sby_settings = get_option( 'sby_settings', array() );
		if ( isset( $sby_settings['caching_type'] ) && $sby_settings['caching_type'] === 'background' ) {
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'inc/class-sby-cron-update.php';
			SBY_Cron_Updater::start_cron_job( $sby_settings['cache_cron_interval'], $sby_settings['cache_cron_time'], $sby_settings['cache_cron_am_pm'] );
		}

		if ( is_multisite() && $network_wide && function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
			// Get all blogs in the network and activate plugin on each one
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$upload     = wp_upload_dir();
				$upload_dir = $upload['basedir'];
				$upload_dir = trailingslashit( $upload_dir ) . SBY_UPLOADS_NAME;
				if ( ! file_exists( $upload_dir ) ) {
					$created = wp_mkdir_p( $upload_dir );
				}
				restore_current_blog();
			}

		} else {
			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = trailingslashit( $upload_dir ) . SBY_UPLOADS_NAME;
			if ( ! file_exists( $upload_dir ) ) {
				$created = wp_mkdir_p( $upload_dir );
			}
		}

		global $wp_roles;
		$wp_roles->add_cap( 'administrator', 'manage_youtube_feed_options' );
		//sby_videos

		//Delete all transients
		global $wpdb;
		$table_name = $wpdb->prefix . "options";
		$wpdb->query( "
        DELETE
        FROM $table_name
        WHERE `option_name` LIKE ('%\_transient\_sby\_%')
        " );
		$wpdb->query( "
        DELETE
        FROM $table_name
        WHERE `option_name` LIKE ('%\_transient\_timeout\_sby\_%')
        " );
		$wpdb->query( "
        DELETE
        FROM $table_name
        WHERE `option_name` LIKE ('%\_transient\_&sby\_%')
        " );
		$wpdb->query( "
        DELETE
        FROM $table_name
        WHERE `option_name` LIKE ('%\_transient\_timeout\_&sby\_%')
        " );
		$wpdb->query( "
        DELETE
        FROM $table_name
        WHERE `option_name` LIKE ('%\_transient\_\$sby\_%')
        " );
		$wpdb->query( "
        DELETE
        FROM $table_name
        WHERE `option_name` LIKE ('%\_transient\_timeout\_\$sby\_%')
        " );
	}
	register_activation_hook( __FILE__, 'sby_activate' );

	/**
	 * Stop cron events when deactivated
	 *
	 * @since  1.0
	 */
	function sby_deactivate() {
		wp_clear_scheduled_hook( 'sby_cron_job' );
	}
	register_deactivation_hook( __FILE__, 'sby_deactivate' );


	/**
	 * Compares previous plugin version and updates database related
	 * items as needed
	 *
	 * @since  2.0
	 */
	function sby_check_for_db_updates() {

		$db_ver = get_option( 'sby_db_version', 0 );

		if ( (float) $db_ver < 1.2 ) {
			sby_add_caps();

			update_option( 'sby_db_version', SBY_DBVERSION );
		}

	}
	add_action( 'wp_loaded', 'sby_check_for_db_updates' );

	/**
	 * Create database tables for sub-site if multisite
	 *
	 * @param  int $blog_id
	 * @param  int $user_id
	 * @param  string $domain
	 * @param  string $path
	 * @param  string $site_id
	 * @param  array $meta
	 *
	 * @since  2.0
	 */
	function sby_on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		if ( is_plugin_active_for_network( 'youtube-feed-pro/youtube-feed.php' ) ) {
			switch_to_blog( $blog_id );
			restore_current_blog();
		}
	}

	add_action( 'wpmu_new_blog', 'sby_on_create_blog', 10, 6 );

	/**
	 * Delete custom tables if not preserving settings
	 *
	 * @param  array $tables tables to drop
	 *
	 * @return array
	 *
	 * @since  2.0
	 */
	function sby_on_delete_blog( $tables ) {
		$options                        = get_option( 'sby_settings' );
		$preserve_settings = $options['preserve_settings'];
		if ( $preserve_settings ) {
			return;
		}

		global $wpdb;
		$tables[] = $wpdb->prefix . 'sby_items';
		$tables[] = $wpdb->prefix . 'sby_items_feeds';

		return $tables;
	}

	add_filter( 'wpmu_drop_tables', 'sby_on_delete_blog' );

	function sby_settings_defaults() {
		$defaults = array(
			'connected_accounts' => array(),
			'type' => 'channel',
			'channel' => '',
			'num' => 9,
			'nummobile' => 9,
			'minnum' => 9,
			'widthresp' => true,
			'class' => '',
			'height' => '',
			'heightunit' => '%',
			'disablemobile' => false,
			'itemspacing' => 5,
			'itemspacingunit' => 'px',
			'background' => '',
			'headercolor' => '',
			'subscribecolor' => '',
			'subscribetextcolor' => '',
			'buttoncolor' => '',
			'buttontextcolor' => '',
			'layout' => 'grid',
			'playvideo' => 'automatically',
			'sortby' => 'none',
			'imageres' => 'auto',
			'showheader' => true,
			'showdescription' => true,
			'showbutton' => true,
			'headersize' => 'small',
			'headeroutside' => false,
			'showsubscribe' => true,
			'buttontext' => __( 'Load More...', 'feeds-for-youtube' ),
			'subscribetext' => __( 'Subscribe', 'feeds-for-youtube' ),
			'caching_type' => 'page',
			'cache_time' => 1,
			'cache_time_unit' => 'hours',
			'backup_cache_enabled' => true,
			'resizeprocess' => 'background',
			'disable_resize' => true,
			'storage_process' => 'background',
			'favor_local' => false,
			'disable_js_image_loading' => false,
			'ajax_post_load' => false,
			'ajaxtheme' => false,
			'enqueue_css_in_shortcode' => false,
			'font_method' => 'svg',
			'customtemplates' => false,
			'gallerycols' => 3,
			'gallerycolsmobile' => 2,
			'gridcols' => 3,
			'gridcolsmobile' => 2,
			'playerratio' => '9:16',
			'eagerload' => false,
			'custom_css' => '',
			'custom_js' => '',

			// pro only
			'usecustomsearch' => false,
			'headerchannel' => '',
			'customsearch' => '',
			'showlikes' => true,
			'carouselcols' => 3,
			'carouselcolsmobile' => 2,
			'carouselarrows' => true,
			'carouselpag' => true,
			'carouselautoplay' => false,
			'infoposition' => 'below',
			'include' => array( 'title', 'icon', 'user', 'views', 'date', 'countdown' ),
			'hoverinclude' => array( 'description', 'stats' ),
			'descriptionlength' => 150,
			'userelative' => true,
			'dateformat' => '0',
			'customdate' => '',
			'showsubscribers' => true,

			'subscriberstext' => __( 'subscribers', 'feeds-for-youtube' ),
			'viewstext' => __( 'views', 'feeds-for-youtube' ),
			'agotext' => __( 'ago', 'feeds-for-youtube' ),
			'beforedatetext' => __( 'Streaming live', 'feeds-for-youtube' ),
	        'beforestreamtimetext' => __( 'Streaming live in', 'feeds-for-youtube' ),
			'minutetext' => __( 'minute', 'feeds-for-youtube' ),
			'minutestext' => __( 'minutes', 'feeds-for-youtube' ),
			'hourstext' => __( 'hours', 'feeds-for-youtube' ),
			'thousandstext' => __( 'K', 'feeds-for-youtube' ),
			'millionstext' => __( 'M', 'feeds-for-youtube' ),
			'watchnowtext' => __( 'Watch Now', 'feeds-for-youtube' ),
			'cta' => 'related',

			'linktext' => __( 'Learn More', 'feeds-for-youtube' ),
			'linkurl' => '',
			'linkopentype' => 'same',
			'linkcolor' => '',
			'linktextcolor' => '',
		);

		return $defaults;
	}

	// Add a Settings link to the plugin on the Plugins page
	$plugin_file = 'youtube-feed-pro/youtube-feed.php';
	add_filter( "plugin_action_links_{$plugin_file}", 'sby_add_settings_link', 10, 2 );
	function sby_add_settings_link( $links, $file ) {
		$sby_settings_link = '<a href="' . admin_url( 'admin.php?page=youtube-feed' ) . '">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $sby_settings_link );
		return $links;
	}

	function sby_text_domain() {
		load_plugin_textdomain( 'feeds-for-youtube', false, basename( dirname(__FILE__) ) . '/languages' );
	}
	add_action( 'plugins_loaded', 'sby_text_domain' );

	function sby_plugin_updater() {
		// retrieve license key from the DB
		$sby_license_key = trim( get_option( 'sby_license_key' ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( SBY_STORE_URL, __FILE__, array(
				'version'   => SBYVER,                   // current version number
				'license'   => $sby_license_key,        // license key
				'item_name' => SBY_PLUGIN_EDD_NAME,         // name of this plugin
				'author'    => 'Smash Balloon'          // author of this plugin
			)
		);
	}
	add_action( 'admin_init', 'sby_plugin_updater', 0 );
}

