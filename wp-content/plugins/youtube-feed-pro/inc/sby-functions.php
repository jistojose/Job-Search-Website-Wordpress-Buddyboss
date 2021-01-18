<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_shortcode('youtube-feed', 'sby_youtube_feed');
function sby_youtube_feed( $atts = array() ) {
	$database_settings = sby_get_database_settings();

	if ( !$database_settings['ajaxtheme'] ) {
		wp_enqueue_script( 'sby_scripts' );
	}

	if ( $database_settings['enqueue_css_in_shortcode'] ) {
		wp_enqueue_style( 'sby_styles' );
	}

	if ( empty( $database_settings['connected_accounts'] ) && empty( $database_settings['api_key'] ) ) {
		$style = current_user_can( 'manage_youtube_feed_options' ) ? ' style="display: block;"' : '';
		ob_start(); ?>
        <div id="sbi_mod_error" <?php echo $style; ?>>
            <span><?php _e('This error message is only visible to WordPress admins', 'feeds-for-youtube' ); ?></span><br />
            <p><b><?php _e( 'Error: No connected account.', 'feeds-for-youtube' ); ?></b>
            <p><?php _e( 'Please go to the YouTube Feed settings page to connect an account.', 'feeds-for-youtube' ); ?></p>
        </div>
		<?php
		$html = ob_get_contents();
		ob_get_clean();
		return $html;
	}

	$youtube_feed_settings = new SBY_Settings_Pro( $atts, $database_settings );
	$youtube_feed_settings->set_feed_type_and_terms();
	$youtube_feed_settings->set_transient_name();
	$transient_name = $youtube_feed_settings->get_transient_name();
	$settings = $youtube_feed_settings->get_settings();
	$feed_type_and_terms = $youtube_feed_settings->get_feed_type_and_terms();

	$youtube_feed = new SBY_Feed_Pro( $transient_name );

	if ( $database_settings['caching_type'] === 'background' ) {
		$youtube_feed->add_report( 'background caching used' );
		if ( $youtube_feed->regular_cache_exists() ) {
			$youtube_feed->add_report( 'setting posts from cache' );
			$youtube_feed->set_post_data_from_cache();
		}

		if ( $youtube_feed->need_to_start_cron_job() ) {
			$youtube_feed->add_report( 'setting up feed for cron cache' );
			$to_cache = array(
				'atts' => $atts,
				'last_requested' => time(),
			);

			$youtube_feed->set_cron_cache( $to_cache, $youtube_feed_settings->get_cache_time_in_seconds() );

			SBY_Cron_Updater_Pro::do_single_feed_cron_update( $youtube_feed_settings, $to_cache, $atts, false );

			$youtube_feed->set_post_data_from_cache();

		} elseif ( $youtube_feed->should_update_last_requested() ) {
			$youtube_feed->add_report( 'updating last requested' );
			$to_cache = array(
				'last_requested' => time(),
			);

			$youtube_feed->set_cron_cache( $to_cache, $youtube_feed_settings->get_cache_time_in_seconds() );
		}

	} elseif ( $youtube_feed->regular_cache_exists() ) {
		$youtube_feed->add_report( 'page load caching used and regular cache exists' );
		$youtube_feed->set_post_data_from_cache();

		if ( $youtube_feed->need_posts( $settings['num'] ) && $youtube_feed->can_get_more_posts() ) {
			while ( $youtube_feed->need_posts( $settings['num'] ) && $youtube_feed->can_get_more_posts() ) {
				$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
			}
			$youtube_feed->cache_feed_data( $youtube_feed_settings->get_cache_time_in_seconds() );
		}

	} else {
		$youtube_feed->add_report( 'no feed cache found' );

		while ( $youtube_feed->need_posts( $settings['num'] ) && $youtube_feed->can_get_more_posts() ) {
			$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
		}

		if ( ! $youtube_feed->should_use_backup() ) {
			$youtube_feed->cache_feed_data( $youtube_feed_settings->get_cache_time_in_seconds() );
		}

	}

	if ( $youtube_feed->should_use_backup() ) {
		$youtube_feed->add_report( 'trying to use backup' );
		$youtube_feed->maybe_set_post_data_from_backup();
		$youtube_feed->maybe_set_header_data_from_backup();
	}

	$settings['feed_avatars'] = array();
	if ( $youtube_feed->need_avatars( $settings ) ) {
		$youtube_feed->set_up_feed_avatars( $youtube_feed_settings->get_connected_accounts_in_feed(), $feed_type_and_terms );
		$settings['feed_avatars'] = $youtube_feed->get_channel_id_avatars();
	}

	// if need a header
	if ( $youtube_feed->need_header( $settings, $feed_type_and_terms ) && ! $youtube_feed->should_use_backup() ) {
		if ( $database_settings['caching_type'] === 'background' ) {
			$youtube_feed->add_report( 'background header caching used' );
			$youtube_feed->set_header_data_from_cache();
		} elseif ( $youtube_feed->regular_header_cache_exists() ) {
			// set_post_data_from_cache
			$youtube_feed->add_report( 'page load caching used and regular header cache exists' );
			$youtube_feed->set_header_data_from_cache();
		} else {
			$youtube_feed->add_report( 'no header cache exists' );
			$youtube_feed->set_remote_header_data( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );

			$youtube_feed->cache_header_data( $youtube_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}
	} else {
		if ( $settings['showheader'] ) {
			$settings['generic_header'] = true;
			$youtube_feed->add_report( 'using generic header' );
		} else {
			$youtube_feed->add_report( 'no header needed' );
		}
	}

	return $youtube_feed->get_the_feed_html( $settings, $atts, $youtube_feed_settings->get_feed_type_and_terms(), $youtube_feed_settings->get_connected_accounts_in_feed() );
}

/**
 * Outputs an organized error report for the front end.
 * This hooks into the end of the feed before the closing div
 *
 * @param object $youtube_feed
 * @param string $feed_id
 */
function sby_error_report( $youtube_feed, $feed_id ) {
	global $sby_posts_manager;

	$style = current_user_can( 'manage_youtube_feed_options' ) ? ' style="display: block;"' : '';

	$error_messages = $sby_posts_manager->get_frontend_errors();
	if ( ! empty( $error_messages ) ) {?>
		<div id="sby_mod_error"<?php echo $style; ?>>
			<span><?php _e('This error message is only visible to WordPress admins', SBY_TEXT_DOMAIN ); ?></span><br />
            <?php if ( isset( $error_messages['accesstoken'] ) ) :
	            echo $error_messages['accesstoken'];

	            ?>
        <?php else: ?>
			<?php foreach ( $error_messages as $error_message ) {
				echo $error_message;
			} ?>
        <?php endif; ?>
		</div>
		<?php
	}

	$sby_posts_manager->reset_frontend_errors();
}
add_action( 'sby_before_feed_end', 'sby_error_report', 10, 2 );

/**
 * Called after the load more button is clicked using admin-ajax.php
 */
function sby_get_next_post_set() {
	if ( ! isset( $_POST['feed_id'] ) || strpos( $_POST['feed_id'], 'sby' ) === false ) {
		die( 'invalid feed ID');
	}

	$feed_id = sanitize_text_field( $_POST['feed_id'] );
	$atts_raw = isset( $_POST['atts'] ) ? json_decode( stripslashes( $_POST['atts'] ), true ) : array();
	if ( is_array( $atts_raw ) ) {
		array_map( 'sanitize_text_field', $atts_raw );
	} else {
		$atts_raw = array();
	}
	$atts = $atts_raw; // now sanitized

	$offset = isset( $_POST['offset'] ) ? (int)$_POST['offset'] : 0;

	$database_settings = sby_get_database_settings();
	$youtube_feed_settings = new SBY_Settings_Pro( $atts, $database_settings );

	if ( empty( $database_settings['connected_accounts'] ) && empty( $database_settings['api_key'] ) ) {
		die( 'error no connected account' );
	}

	$youtube_feed_settings->set_feed_type_and_terms();
	$youtube_feed_settings->set_transient_name();
	$transient_name = $youtube_feed_settings->get_transient_name();

	$settings = $youtube_feed_settings->get_settings();

	$feed_type_and_terms = $youtube_feed_settings->get_feed_type_and_terms();

	$transient_name = $feed_id;

	$youtube_feed = new SBY_Feed_Pro( $transient_name );

	if ( $settings['caching_type'] === 'permanent' && empty( $settings['doingModerationMode'] ) ) {
		$youtube_feed->add_report( 'trying to use permanent cache' );
		$youtube_feed->maybe_set_post_data_from_backup();
	} elseif ( $settings['caching_type'] === 'background' ) {
		$youtube_feed->add_report( 'background caching used' );
		if ( $youtube_feed->regular_cache_exists() ) {
			$youtube_feed->add_report( 'setting posts from cache' );
			$youtube_feed->set_post_data_from_cache();
		}

		if ( $youtube_feed->need_posts( $settings['num'], $offset ) && $youtube_feed->can_get_more_posts() ) {
			while ( $youtube_feed->need_posts( $settings['num'], $offset ) && $youtube_feed->can_get_more_posts() ) {
				$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
			}

			if ( $youtube_feed->need_to_start_cron_job() ) {
				$youtube_feed->add_report( 'needed to start cron job' );
				$to_cache = array(
					'atts' => $atts,
					'last_requested' => time(),
				);

				$youtube_feed->set_cron_cache( $to_cache, $youtube_feed_settings->get_cache_time_in_seconds() );

			} else {
				$youtube_feed->add_report( 'updating last requested and adding to cache' );
				$to_cache = array(
					'last_requested' => time(),
				);

				$youtube_feed->set_cron_cache( $to_cache, $youtube_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
			}
		}

	} elseif ( $youtube_feed->regular_cache_exists() ) {
		$youtube_feed->add_report( 'regular cache exists' );
		$youtube_feed->set_post_data_from_cache();

		if ( $youtube_feed->need_posts( $settings['num'], $offset ) && $youtube_feed->can_get_more_posts() ) {
			while ( $youtube_feed->need_posts( $settings['num'], $offset ) && $youtube_feed->can_get_more_posts() ) {
				$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
			}

			$youtube_feed->add_report( 'adding to cache' );
			$youtube_feed->cache_feed_data( $youtube_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}


	} else {
		$youtube_feed->add_report( 'no feed cache found' );

		while ( $youtube_feed->need_posts( $settings['num'], $offset ) && $youtube_feed->can_get_more_posts() ) {
			$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
		}

		if ( $youtube_feed->should_use_backup() ) {
			$youtube_feed->add_report( 'trying to use a backup cache' );
			$youtube_feed->maybe_set_post_data_from_backup();
		} else {
			$youtube_feed->add_report( 'transient gone, adding to cache' );
			$youtube_feed->cache_feed_data( $youtube_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}
	}

	$settings['feed_avatars'] = array();
	if ( $youtube_feed->need_avatars( $settings ) ) {
		$youtube_feed->set_up_feed_avatars( $youtube_feed_settings->get_connected_accounts_in_feed(), $feed_type_and_terms );
		$settings['feed_avatars'] = $youtube_feed->get_channel_id_avatars();
	}

	$feed_status = array( 'shouldPaginate' => $youtube_feed->should_use_pagination( $settings, $offset ) );

	$feed_status['cacheAll'] = $youtube_feed->do_page_cache_all();

	$return_html = $youtube_feed->get_the_items_html( $settings, $offset, $youtube_feed_settings->get_feed_type_and_terms(), $youtube_feed_settings->get_connected_accounts_in_feed() );

	$post_data = $youtube_feed->get_post_data();
	if ( ($youtube_feed->are_posts_with_no_details() || $youtube_feed->successful_video_api_request_made())
         && ! empty( $post_data ) ) {
		if ( $settings['storage_process'] === 'page' ) {
			foreach ( $post_data as $post ) {
				$wp_post            = new SBY_WP_Post( $post, $this->regular_feed_transient_name );
				$sby_video_settings = SBY_CPT::get_sby_cpt_settings();
				$wp_post->update_post( $sby_video_settings['post_status'] );
			}
		} elseif ( $settings['storage_process'] === 'background' ) {
			$feed_status['checkWPPosts'] = true;
			$feed_status['cacheAll']     = true;
		}
	}

	/*if ( $settings['disable_js_image_loading'] || $settings['imageres'] !== 'auto' ) {
		global $sby_posts_manager;
		$post_data = array_slice( $youtube_feed->get_post_data(), $offset, $settings['minnum'] );

		if ( ! $sby_posts_manager->image_resizing_disabled() ) {
			$image_ids = array();
			foreach ( $post_data as $post ) {
				$image_ids[] = SBY_Parse::get_post_id( $post );
			}
			$resized_images = SBY_Feed::get_resized_images_source_set( $image_ids, 0, $feed_id );

			$youtube_feed->set_resized_images( $resized_images );
		}
	}*/

	$return = array(
		'html' => $return_html,
		'feedStatus' => $feed_status,
		'report' => $youtube_feed->get_report(),
		'resizedImages' => array()
		//'resizedImages' => SBY_Feed::get_resized_images_source_set( $youtube_feed->get_image_ids_post_set(), 0, $feed_id )
	);

	//SBY_Feed::update_last_requested( $youtube_feed->get_image_ids_post_set() );

	echo wp_json_encode( $return );

	global $sby_posts_manager;

	$sby_posts_manager->update_successful_ajax_test();

	die();
}
add_action( 'wp_ajax_sby_load_more_clicked', 'sby_get_next_post_set' );
add_action( 'wp_ajax_nopriv_sby_load_more_clicked', 'sby_get_next_post_set' );

function sby_get_live_retrieve() {
	if ( ! isset( $_POST['feed_id'] ) || strpos( $_POST['feed_id'], 'sby' ) === false ) {
		die( 'invalid feed ID');
	}

	$feed_id = sanitize_text_field( $_POST['feed_id'] );
	$atts_raw = isset( $_POST['atts'] ) ? json_decode( stripslashes( $_POST['atts'] ), true ) : array();
	$video_id = sanitize_text_field( $_POST['video_id'] );
	if ( is_array( $atts_raw ) ) {
		array_map( 'sanitize_text_field', $atts_raw );
	} else {
		$atts_raw = array();
	}
	$atts = $atts_raw; // now sanitized

    if ( isset( $atts['live'] ) ) {
        unset( $atts['live'] );
    }
    $atts['type'] = 'single';
	$atts['single'] = $video_id;
	$offset = 0;

	$database_settings = sby_get_database_settings();
	$youtube_feed_settings = new SBY_Settings_Pro( $atts, $database_settings );

	if ( empty( $database_settings['connected_accounts'] ) && empty( $database_settings['api_key'] ) ) {
		die( 'error no connected account' );
	}

	$youtube_feed_settings->set_feed_type_and_terms();
	$youtube_feed_settings->set_transient_name( $feed_id );
	$transient_name = $youtube_feed_settings->get_transient_name();

	if ( $transient_name !== $feed_id ) {
		die( 'id does not match' );
	}

	$settings = $youtube_feed_settings->get_settings();

	$feed_type_and_terms = $youtube_feed_settings->get_feed_type_and_terms();

	$youtube_feed = new SBY_Feed_Pro( $transient_name );
	$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
	if ( $database_settings['caching_type'] === 'background' ) {
        $to_cache = array(
            'atts' => $atts,
            'last_requested' => time(),
        );
        $youtube_feed->set_cron_cache( $to_cache, $youtube_feed_settings->get_cache_time_in_seconds() );
	} else {
		$youtube_feed->cache_feed_data( $youtube_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
	}

	$feed_status = array( 'shouldPaginate' => $youtube_feed->should_use_pagination( $settings, $offset ) );

	$feed_status['cacheAll'] = $youtube_feed->do_page_cache_all();

	$return_html = $youtube_feed->get_the_items_html( $settings, $offset, $youtube_feed_settings->get_feed_type_and_terms(), $youtube_feed_settings->get_connected_accounts_in_feed() );
    $post_data = $youtube_feed->get_post_data();
	if ( ($youtube_feed->are_posts_with_no_details() || $youtube_feed->successful_video_api_request_made())
	     && ! empty( $post_data ) ) {
		if ( $settings['storage_process'] === 'page' ) {
			foreach ( $youtube_feed->get_post_data() as $post ) {
				$wp_post            = new SBY_WP_Post( $post, $this->regular_feed_transient_name );
				$sby_video_settings = SBY_CPT::get_sby_cpt_settings();
				$wp_post->update_post( $sby_video_settings['post_status'] );
			}
		} elseif ( $settings['storage_process'] === 'background' ) {
			$feed_status['checkWPPosts'] = true;
			$feed_status['cacheAll']     = true;
		}
	}

	$return = array(
		'html' => $return_html,
		'feedStatus' => $feed_status,
		'report' => $youtube_feed->get_report(),
		'resizedImages' => array()
		//'resizedImages' => SBY_Feed::get_resized_images_source_set( $youtube_feed->get_image_ids_post_set(), 0, $feed_id )
	);

	//SBY_Feed::update_last_requested( $youtube_feed->get_image_ids_post_set() );

	echo wp_json_encode( $return );

	global $sby_posts_manager;

	$sby_posts_manager->update_successful_ajax_test();

	die();
}
add_action( 'wp_ajax_sby_live_retrieve', 'sby_get_live_retrieve' );
add_action( 'wp_ajax_nopriv_sby_live_retrieve', 'sby_get_live_retrieve' );


/**
 * Posts that need resized images are processed after being sent to the server
 * using AJAX
 *
 * @return string
 */
function sby_process_wp_posts() {
	if ( ! isset( $_POST['feed_id'] ) || strpos( $_POST['feed_id'], 'sby' ) === false ) {
		die( 'invalid feed ID');
	}

	$feed_id = sanitize_text_field( $_POST['feed_id'] );

	$atts_raw = isset( $_POST['atts'] ) ? json_decode( stripslashes( $_POST['atts'] ), true ) : array();
	if ( is_array( $atts_raw ) ) {
		array_map( 'sanitize_text_field', $atts_raw );
	} else {
		$atts_raw = array();
	}
	$atts = $atts_raw; // now sanitized

	$offset = isset( $_POST['offset'] ) ? (int)$_POST['offset'] : 0;
	$vid_ids = isset( $_POST['posts'] ) && is_array( $_POST['posts'] ) ? $_POST['posts'] : array();

	if ( ! empty( $vid_ids ) ) {
		array_map( 'sanitize_text_field', $vid_ids );
	}

	$cache_all = isset( $_POST['cache_all'] ) ? $_POST['cache_all'] === 'true' : false;

	$info = sby_add_or_update_wp_posts( $vid_ids, $feed_id, $atts, $offset, $cache_all );

    echo wp_json_encode( $info );

    //global $sby_posts_manager;

	//$sby_posts_manager->update_successful_ajax_test();

	die();
}
add_action( 'wp_ajax_sby_check_wp_submit', 'sby_process_wp_posts' );
add_action( 'wp_ajax_nopriv_sby_check_wp_submit', 'sby_process_wp_posts' );

function sby_add_or_update_wp_posts( $vid_ids, $feed_id, $atts, $offset, $cache_all ) {
	if ( $cache_all ) {
		$database_settings = sby_get_database_settings();
		$youtube_feed_settings = new SBY_Settings_Pro( $atts, $database_settings );
		$youtube_feed_settings->set_feed_type_and_terms();
		$youtube_feed_settings->set_transient_name( $feed_id );
		$transient_name = $youtube_feed_settings->get_transient_name();

		$feed_id = $transient_name;
	}

	$database_settings = sby_get_database_settings();
	$sby_settings = new SBY_Settings_Pro( $atts, $database_settings );

	$settings = $sby_settings->get_settings();

	$youtube_feed = new SBY_Feed_Pro( $feed_id );
	if ( $youtube_feed->regular_cache_exists() || $feed_id === 'sby_single' ) {
		$youtube_feed->set_post_data_from_cache();

		if ( !$cache_all || $feed_id === 'sby_single'  ) {
			if ( empty( $vid_ids ) || $feed_id !== 'sby_single' ) {
				$posts = array_slice( $youtube_feed->get_post_data(), max( 0, $offset - $settings['minnum'] ), $settings['minnum'] );
			} else {
				$posts = $vid_ids;
			}
		} else {
			$posts = $youtube_feed->get_post_data();
		}

		return sby_process_post_set_caching( $posts, $feed_id );
	}

	return array();
}

function sby_process_post_set_caching( $posts, $feed_id ) {

    // if is an array of video ids already, don't need to get them
    if ( isset( $posts[0] ) && SBY_Parse::get_video_id( $posts[0] ) === '' ) {
	    $vid_ids = $posts;
    } else {
	    $vid_ids = array();
	    foreach ( $posts as $post ) {
		    $vid_ids[] = SBY_Parse::get_video_id( $post );
		    $wp_post = new SBY_WP_Post( $post, $feed_id );
		    $sby_video_settings = SBY_CPT::get_sby_cpt_settings();
		    $wp_post->update_post( $sby_video_settings['post_status'] );
	    }
    }


	if ( ! empty( $vid_ids ) ) {
		$details_query = new SBY_YT_Details_Query( array( 'video_ids' => $vid_ids ) );
		$videos_details = $details_query->get_video_details_to_update();

		$updated_details = array();
		foreach ( $videos_details as $video ) {
			$vid_id = SBY_Parse::get_video_id( $video );
			$live_broadcast_type = SBY_Parse_Pro::get_live_broadcast_content( $video );
			$live_streaming_timestamp = SBY_Parse_Pro::get_live_streaming_timestamp( $video );
			$single_updated_details = array(
				"sby_view_count" => SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_view_count( $video ), 'views' ),
				"sby_like_count" => SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_like_count( $video ), 'likes' ),
				"sby_comment_count" => SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_comment_count( $video ), 'comments' ),
                'sby_live_broadcast' => array(
                    'broadcast_type' => $live_broadcast_type,
                    'live_streaming_string' => SBY_Display_Elements_Pro::escaped_live_streaming_time_string( $video ),
                    'live_streaming_date' => SBY_Display_Elements_Pro::format_date( $live_streaming_timestamp, false, true ),
                    'live_streaming_timestamp' => $live_streaming_timestamp
                ),
                'raw' => array(
                    'views' => SBY_Parse_Pro::get_view_count( $video ),
                    'likes' => SBY_Parse_Pro::get_like_count( $video ),
                    'comments' => SBY_Parse_Pro::get_comment_count( $video )
                )
            );

			$description = SBY_Parse_Pro::get_caption( $video );
			if ( ! empty( $description ) ) {
				$single_updated_details['sby_description'] = sby_esc_html_with_br( $description );
            }
			$post = new SBY_WP_Post( $video, '' );

			$post->update_video_details();

			$updated_details[ $vid_id ] = apply_filters( 'sby_video_details_return', $single_updated_details, $video, $post->get_wp_post_id() );
		}

		return $updated_details;
	}

	return array();
}

function sby_debug_report( $youtube_feed, $feed_id ) {

	if ( ! isset( $_GET['sby_debug'] ) ) {
		return;
	}

	?>
    <p>Status</p>
    <ul>
        <li>Time: <?php echo date( "Y-m-d H:i:s", time() ); ?></li>
		<?php foreach ( $youtube_feed->get_report() as $item ) : ?>
            <li><?php echo esc_html( $item ); ?></li>
		<?php endforeach; ?>

    </ul>

	<?php
	$database_settings = sby_get_database_settings();

	$public_settings_keys = SBY_Settings_Pro::get_public_db_settings_keys();
	?>
    <p>Settings</p>
    <ul>
		<?php foreach ( $public_settings_keys as $key ) : if ( isset( $database_settings[ $key ] ) ) : ?>
            <li>
                <small><?php echo esc_html( $key ); ?>:</small>
				<?php if ( ! is_array( $database_settings[ $key ] ) ) :
					echo $database_settings[ $key ];
				else : ?>
                    <pre>
<?php var_export( $database_settings[ $key ] ); ?>
</pre>
				<?php endif; ?>
            </li>

		<?php endif; endforeach; ?>
    </ul>
	<?php
}
add_action( 'sby_before_feed_end', 'sby_debug_report', 11, 2 );

function sby_clear_cache() {
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

	sby_clear_page_caches();
}
add_action( 'sby_settings_after_configure_save', 'sby_clear_cache' );

/**
 * When certain events occur, page caches need to
 * clear or errors occur or changes will not be seen
 */
function sby_clear_page_caches() {
	if ( isset( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ){
		/* Clear WP fastest cache*/
		$GLOBALS['wp_fastest_cache']->deleteCache();
	}

	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}

	if ( class_exists('W3_Plugin_TotalCacheAdmin') ) {
		$plugin_totalcacheadmin = & w3_instance('W3_Plugin_TotalCacheAdmin');

		$plugin_totalcacheadmin->flush_all();
	}

	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}

	if ( class_exists( 'autoptimizeCache' ) ) {
		/* Clear autoptimize */
		autoptimizeCache::clearall();
	}
}

/**
 * Triggered by a cron event to update feeds
 */
function sby_cron_updater() {
    global $sby_settings;

	if ( $sby_settings['caching_type'] === 'background' ) {
		SBY_Cron_Updater_Pro::do_feed_updates();
	}

}
add_action( 'sby_feed_update', 'sby_cron_updater' );

function sby_update_or_connect_account( $args ) {
	global $sby_settings;
	$account_id = $args['channel_id'];
	$sby_settings['connected_accounts'][ $account_id ] = array(
		'access_token' => $args['access_token'],
		'refresh_token' => $args['refresh_token'],
		'channel_id' => $args['channel_id'],
		'username' => $args['username'],
		'is_valid' => true,
		'last_checked' => time(),
		'profile_picture' => $args['profile_picture'],
		'privacy' => $args['privacy'],
		'expires' => $args['expires']
    );

	update_option( 'sby_settings', $sby_settings );

	return $sby_settings['connected_accounts'][ $account_id ];
}

function sby_get_first_connected_account() {
	global $sby_settings;
	$an_account = array();

	if ( ! empty( $sby_settings['api_key'] ) ) {
		$an_account = array(
			'access_token' => '',
			'refresh_token' => '',
			'channel_id' => '',
			'username' => '',
			'is_valid' => true,
			'last_checked' => '',
			'profile_picture' => '',
			'privacy' => '',
			'expires' => '2574196927',
			'api_key' => $sby_settings['api_key']
		);
	} else {
		$connected_accounts = $sby_settings['connected_accounts'];
		foreach ( $connected_accounts as $account ) {
			if ( empty( $an_account ) ) {
				$an_account = $account;
			}
		}
	}

	if ( empty( $an_account ) ) {
		$an_account = array( 'rss_only' => true );
	}

	return $an_account;
}

function sby_get_feed_template_part( $part, $settings = array() ) {
	$file = '';

	$using_custom_templates_in_theme = apply_filters( 'sby_use_theme_templates', $settings['customtemplates'] );
	$generic_path = trailingslashit( SBY_PLUGIN_DIR ) . 'templates/';

	if ( $using_custom_templates_in_theme ) {
		$custom_header_template = locate_template( 'sby/header.php', false, false );
		$custom_header_generic_template = locate_template( 'sby/header-generic.php', false, false );
		$custom_player_template = locate_template( 'sby/player.php', false, false );
		$custom_item_template = locate_template( 'sby/item.php', false, false );
		$custom_footer_template = locate_template( 'sby/footer.php', false, false );
		$custom_feed_template = locate_template( 'sby/feed.php', false, false );
		$custom_info_template = locate_template( 'sby/info.php', false, false );
		$custom_cta_template = locate_template( 'sby/cta.php', false, false );
		$custom_shortcode_template = locate_template( 'sby/shortcode-content.php', false, false );
		$form_template = locate_template( 'sby/form.php', false, false );
		$results_template = locate_template( 'sby/results.php', false, false );
		$result_template = locate_template( 'sby/result.php', false, false );
	} else {
		$custom_header_template = false;
		$custom_header_generic_template = false;
		$custom_player_template = false;
		$custom_item_template = false;
		$custom_footer_template = false;
		$custom_feed_template = false;
		$custom_info_template = false;
		$custom_cta_template = false;
		$custom_shortcode_template = false;
		$form_template = false;
		$results_template = false;
		$result_template = false;
	}

	if ( $part === 'header' ) {
		if ( isset( $settings['generic_header'] ) ) {
			if ( $custom_header_generic_template ) {
				$file = $custom_header_generic_template;
			} else {
				$file = $generic_path . 'header-generic.php';
			}
		} else {
			if ( $custom_header_template ) {
				$file = $custom_header_template;
			} else {
				$file = $generic_path . 'header.php';
			}
		}
	} elseif ( $part === 'header-generic' ) {
		if ( $custom_header_generic_template ) {
			$file = $custom_header_generic_template;
		} else {
			$file = $generic_path . 'header-generic.php';
		}
	} elseif ( $part === 'player' ) {
		if ( $custom_player_template ) {
			$file = $custom_player_template;
		} else {
			$file = $generic_path . 'player.php';
		}
	} elseif ( $part === 'item' ) {
		if ( $custom_item_template ) {
			$file = $custom_item_template;
		} else {
			$file = $generic_path . 'item.php';
		}
	} elseif ( $part === 'footer' ) {
		if ( $custom_footer_template ) {
			$file = $custom_footer_template;
		} else {
			$file = $generic_path . 'footer.php';
		}
	} elseif ( $part === 'feed' ) {
		if ( $custom_feed_template ) {
			$file = $custom_feed_template;
		} else {
			$file = $generic_path . 'feed.php';
		}
	} elseif ( $part === 'info' ) {
		if ( $custom_info_template ) {
			$file = $custom_info_template;
		} else {
			$file = $generic_path . 'info.php';
		}
	} elseif ( $part === 'cta' ) {
		if ( $custom_cta_template ) {
			$file = $custom_cta_template;
		} else {
			$file = $generic_path . 'cta.php';
		}
	} elseif ( $part === 'shortcode-content' ) {
		if ( $custom_shortcode_template ) {
			$file = $custom_shortcode_template;
		} else {
			$file = $generic_path . 'single/shortcode-content.php';
		}
	} elseif ( $part === 'form' ) {
		if ( $form_template ) {
			$file = $form_template;
		} else {
			$file = $generic_path . 'search/form.php';
		}
	} elseif ( $part === 'results' ) {
		if ( $results_template ) {
			$file = $results_template;
		} else {
			$file = $generic_path . 'search/results.php';
		}
	} elseif ( $part === 'result' ) {
		if ( $result_template ) {
			$file = $result_template;
		} else {
			$file = $generic_path . 'search/result.php';
		}
	}

	return $file;
}

/**
 * Get the settings in the database with defaults
 *
 * @return array
 */
function sby_get_database_settings() {
	global $sby_settings;

	$defaults = sby_settings_defaults();

	return array_merge( $defaults, $sby_settings );
}

function sby_get_channel_id_from_channel_name( $channel_name ) {
	$channel_ids = get_option( 'sby_channel_ids', array() );

	if ( isset( $channel_ids[ strtolower( $channel_name ) ] ) ) {
		return $channel_ids[ strtolower( $channel_name ) ];
	}

	return false;
}

function sby_set_channel_id_from_channel_name( $channel_name, $channel_id ) {
	$channel_ids = get_option( 'sby_channel_ids', array() );

	$channel_ids[ strtolower( $channel_name ) ] = $channel_id;

	update_option( 'sby_channel_ids', $channel_ids, false );
}

function sby_icon( $icon, $class = '' ) {
	$class = ! empty( $class ) ? ' ' . trim( $class ) : '';
	if ( $icon === SBY_SLUG ) {
		return '<svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="youtube" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-youtube fa-w-18'.$class.'"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" class=""></path></svg>';
	} else {
		return '<i aria-hidden="true" role="img" class="fab fa-youtube"></i>';
	}
}


/**
 * @param $a
 * @param $b
 *
 * @return false|int
 */
function sby_date_sort( $a, $b ) {
	$time_stamp_a = SBY_Parse::get_timestamp( $a );
	$time_stamp_b = SBY_Parse::get_timestamp( $b );

	if ( isset( $time_stamp_a ) ) {
		return $time_stamp_b - $time_stamp_a;
	} else {
		return rand ( -1, 1 );
	}
}

/**
 * @param $a
 * @param $b
 *
 * @return false|int
 */
function sby_rand_sort( $a, $b ) {
	return rand ( -1, 1 );
}

/**
 * Converts a hex code to RGB so opacity can be
 * applied more easily
 *
 * @param $hex
 *
 * @return string
 */
function sby_hextorgb( $hex ) {
	// allows someone to use rgb in shortcode
	if ( strpos( $hex, ',' ) !== false ) {
		return $hex;
	}

	$hex = str_replace( '#', '', $hex );

	if ( strlen( $hex ) === 3 ) {
		$r = hexdec( substr( $hex,0,1 ).substr( $hex,0,1 ) );
		$g = hexdec( substr( $hex,1,1 ).substr( $hex,1,1 ) );
		$b = hexdec( substr( $hex,2,1 ).substr( $hex,2,1 ) );
	} else {
		$r = hexdec( substr( $hex,0,2 ) );
		$g = hexdec( substr( $hex,2,2 ) );
		$b = hexdec( substr( $hex,4,2 ) );
	}
	$rgb = array( $r, $g, $b );

	return implode( ',', $rgb ); // returns the rgb values separated by commas
}

function sby_get_utc_offset() {
	return get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;
}

function sby_is_pro_version() {
    return defined( 'SBY_PLUGIN_EDD_NAME' );
}

function sby_strip_after_hash( $string ) {
	$string_array = explode( '#', $string );
	$finished_string = $string_array[0];

	return $finished_string;
}

function sby_get_default_type_and_terms() {
	$yt_atts = array();
	$yt_database_settings = sby_get_database_settings();

	$youtube_feed_settings = new SBY_Settings_Pro( $yt_atts, $yt_database_settings );

	$youtube_feed_settings->set_feed_type_and_terms();
	$yt_feed_type_and_terms = $youtube_feed_settings->get_feed_type_and_terms();

	$return = array(
		'type' => '',
		'term_label' => '',
		'terms' => ''
	);
	$terms_array = array();
	foreach ( $yt_feed_type_and_terms as $key => $values ) {
		if ( empty( $type ) ) {
			if ( $key === 'channels' ) {
				$return['type'] = 'channels';
				$return['term_label'] = 'channel(s)';
				foreach ( $values as $value ) {
				    $terms_array[] = $value['term'];
				}
			}
		}

	}

	$return['terms'] = implode( ', ', $terms_array );

	return $return;
}

function sby_get_account_and_feed_info() {
	$yt_atts = array();
	$yt_database_settings = sby_get_database_settings();

	$youtube_feed_settings = new SBY_Settings_Pro( $yt_atts, $yt_database_settings );

	$youtube_feed_settings->set_feed_type_and_terms();
	$yt_feed_type_and_terms = $youtube_feed_settings->get_feed_type_and_terms();

	$type_and_terms = array();
	$terms_array = array();
	foreach ( $yt_feed_type_and_terms as $key => $values ) {
        if ( empty( $type_and_terms['type'] ) ) {
            $type_and_terms['type'] = $key;
            $type_and_terms['term_label'] = '';
            foreach ( $values as $value ) {
                $terms_array[] = $value['term'];
            }
        }

	}

	$type_and_terms['terms'] =  $terms_array;

	$return['type_and_terms'] = $type_and_terms;
	$return['connected_accounts'] = sby_get_first_connected_account();
	if ( isset( $return['connected_accounts']['api_key'] ) ) {
		$return['available_types'] = array(
			'channels' => array(
				'label' => 'Channel',
				'shortcode' => 'channel',
				'term_shortcode' => 'channel',
				'input' => 'text',
				'instructions' => __( 'Any channel ID', 'youtube-feed' )
			),
			'playlist' => array(
				'label' => 'Playlist',
				'shortcode' => 'playlist',
				'term_shortcode' => 'playlist',
				'input' => 'text',
				'instructions' => __( 'Any playlist ID', 'youtube-feed' )
			),
			'favorites' => array(
				'label' => 'Favorites',
				'shortcode' => 'favorites',
				'term_shortcode' => 'channel',
				'input' => 'text',
				'instructions' => __( 'Any channel ID', 'youtube-feed' )
			),
			'search' => array(
				'label' => 'Search',
				'shortcode' => 'search',
				'term_shortcode' => 'search',
				'input' => 'text',
				'instructions' => __( 'A search term', 'youtube-feed' )
			),
			'livestream' => array(
				'label' => 'Live Stream',
				'shortcode' => 'livestream',
				'term_shortcode' => 'channel',
				'input' => 'text',
				'instructions' => __( 'Any channel ID', 'youtube-feed' )
			),
			'single' => array(
				'label' => 'Single',
				'shortcode' => 'single',
				'term_shortcode' => 'single',
				'input' => 'text',
				'instructions' => __( 'Video IDs (separated by comma)', 'youtube-feed' )
			),
		);
    } else {
		$return['available_types'] = array(
			'channels' => array(
				'label' => 'Channel',
				'shortcode' => 'channel',
				'term_shortcode' => 'channel',
				'input' => 'text',
				'instructions' => __( 'Any channel ID', 'youtube-feed' )
			)
		);
    }

	$return['settings'] = array(
		'type' => 'type'
	);

	$channel_ids_names = array();

	global $sby_settings;
	$connected_accounts = $sby_settings['connected_accounts'];

	foreach ( $connected_accounts as $connected_account ) {
	    if ( ! empty( $connected_account['username'] ) && ! empty( $connected_account['channel_id'] ) ) {
	        $channel_ids_names[ $connected_account['channel_id'] ] = $connected_account['username'];
        }
    }

	$return['channel_ids_names'] = $channel_ids_names;
	return $return;
}

function sby_get_account_bottom() {
	return '';
}

function sby_get_account_top() {
	return '';
}

function sby_replace_double_quotes( &$element, $index ) {
	$element = str_replace( array( '"', "\nn", "\n" ), array( "&quot;", '<br />', '<br />' ), $element );
}

function sby_esc_html_with_br( $text ) {
	return str_replace( array( '&lt;br /&gt;', '&lt;br&gt;' ), '<br>', esc_html( nl2br( $text ) ) );
}

function sby_esc_attr_with_br( $text ) {
	return str_replace( array( '&lt;br /&gt;', '&lt;br&gt;' ), '&lt;br /&gt;', esc_attr( nl2br( $text ) ) );
}

function sby_maybe_shorten_text( $string, $feed_settings ) {

	$limit = isset( $feed_settings['textlength'] ) ? $feed_settings['textlength'] : 120;

	if ( strlen( $string ) <= $limit ) {
		return $string;
	}

	$string = str_replace( '<br />', "\n\r", $string );

	$parts = preg_split( '/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE );
	$parts_count = count( $parts );

	$length = 0;
	$last_part = 0;
	for ( ; $last_part < $parts_count; ++$last_part ) {
		$length += strlen( $parts[ $last_part ] );
		if ( $length > $limit ) { break; }
	}

	$last_part = $last_part !== 0 ? $last_part - 1 : 0;
	$parts[ $last_part ] = $parts[ $last_part ] . '...';

	$return_parts = array_slice( $parts, 0, $last_part + 1 );

	$return = implode( ' ', $return_parts );

	return $return;
}

/**
 * Adds the ajax url and custom JavaScript to the page
 */
function sby_custom_js() {
	global $sby_settings;

	$js = isset( $sby_settings['custom_js'] ) ? trim( $sby_settings['custom_js'] ) : '';

	echo '<!-- YouTube Feed JS -->';
	echo "\r\n";
	echo '<script type="text/javascript">';
	echo "\r\n";

	if ( ! empty( $js ) ) {
		echo "\r\n";
		echo "jQuery( document ).ready(function($) {";
		echo "\r\n";
		echo "window.sbyCustomJS = function(){";
		echo "\r\n";
		echo stripslashes($js);
		echo "\r\n";
		echo "}";
		echo "\r\n";
		echo "});";
	}

	echo "\r\n";
	echo '</script>';
	echo "\r\n";
}
add_action( 'wp_footer', 'sby_custom_js' );

//Custom CSS
add_action( 'wp_head', 'sby_custom_css' );
function sby_custom_css() {
	global $sby_settings;

	$css = isset( $sby_settings['custom_css'] ) ? trim( $sby_settings['custom_css'] ) : '';

	//Show CSS if an admin (so can see Hide Photos link), if including Custom CSS or if hiding some photos
	if ( current_user_can( 'manage_youtube_feed_options' ) || current_user_can( 'manage_options' ) ||  ! empty( $css ) ) {

		echo '<!-- Instagram Feed CSS -->';
		echo "\r\n";
		echo '<style type="text/css">';

		if ( ! empty( $css ) ){
			echo "\r\n";
			echo stripslashes($css);
		}

		if ( current_user_can( 'manage_youtube_feed_options' ) || current_user_can( 'manage_options' ) ){
			echo "\r\n";
			echo "#sby_mod_link, #sby_mod_error{ display: block !important; width: 100%; float: left; box-sizing: border-box; }";
		}

		echo "\r\n";
		echo '</style>';
		echo "\r\n";
    }

}

/**
 * Makes the JavaScript file available and enqueues the stylesheet
 * for the plugin
 */
function sby_scripts_enqueue( $enqueue = false ) {
	//Register the script to make it available

	//Options to pass to JS file
	global $sby_settings;

	$js_file = 'js/sb-youtube.min.js';
	if ( isset( $_GET['sby_debug'] ) ) {
		$js_file = 'js/sb-youtube.js';
	}

	if ( isset( $sby_settings['enqueue_js_in_head'] ) && $sby_settings['enqueue_js_in_head'] ) {
		wp_enqueue_script( 'sby_scripts', trailingslashit( SBY_PLUGIN_URL ) . $js_file, array('jquery'), SBYVER, false );
	} else {
		wp_register_script( 'sby_scripts', trailingslashit( SBY_PLUGIN_URL ) . $js_file, array('jquery'), SBYVER, true );
	}

	if ( isset( $sby_settings['enqueue_css_in_shortcode'] ) && $sby_settings['enqueue_css_in_shortcode'] ) {
		wp_register_style( 'sby_styles', trailingslashit( SBY_PLUGIN_URL ) . 'css/sb-youtube.min.css', array(), SBYVER );
	} else {
		wp_enqueue_style( 'sby_styles', trailingslashit( SBY_PLUGIN_URL ) . 'css/sb-youtube.min.css', array(), SBYVER );
	}

	$data = array(
		'adminAjaxUrl' => admin_url( 'admin-ajax.php' ),
		'placeholder' => trailingslashit( SBY_PLUGIN_URL ) . 'img/placeholder.png',
		'placeholderNarrow' => trailingslashit( SBY_PLUGIN_URL ) . 'img/placeholder-narrow.png',
		'lightboxPlaceholder' => trailingslashit( SBY_PLUGIN_URL ) . 'img/lightbox-placeholder.png',
		'lightboxPlaceholderNarrow' => trailingslashit( SBY_PLUGIN_URL ) . 'img/lightbox-placeholder-narrow.png',
		'autoplay' => $sby_settings['playvideo'] === 'automatically',
		'semiEagerload' => $sby_settings['eagerload'],
		'eagerload' => false
    );
	//Pass option to JS file
	wp_localize_script('sby_scripts', 'sbyOptions', $data );

	if ( $enqueue ) {
		wp_enqueue_style( 'sby_styles' );
		wp_enqueue_script( 'sby_scripts' );
	}
}
add_action( 'wp_enqueue_scripts', 'sby_scripts_enqueue', 2 );