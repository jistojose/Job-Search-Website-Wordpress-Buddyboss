<?php
/**
 * Class SBY_Feed_Pro
 *
 * The Pro class mostly adds additional methods
 * used in the "display_instagram" function for supporting
 * additional features.
 *
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SBY_Feed_Pro extends SBY_Feed
{
	private $stats_cache;

	private $channel_id_avatars;

	public function set_next_pages( $next_pages ) {
		$this->next_pages = $next_pages;
	}


	/**
	 * Uses the settings to determine if avatars are going to be used.
	 * Can make feed creation faster if not.
	 *
	 * @param $settings
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	public function need_avatars( $settings ) {
		if ( isset( $settings['type'] ) && $settings['type'] === 'hashtag' ) {
			return false;
		} elseif ( isset( $settings['disablelightbox'] ) && ($settings['disablelightbox'] === 'true' || $settings['disablelightbox'] === 'on') ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Available avatars are added to the feed as an attribute so they can be used in the lightbox
	 *
	 * @param $connected_accounts_in_feed
	 * @param $feed_types_and_terms
	 *
	 * @since 5.0
	 */
	public function set_up_feed_avatars( $connected_accounts_in_feed, $feed_types_and_terms ) {
		foreach ( $feed_types_and_terms as $type => $terms ) {
			foreach ( $terms as $term_and_params ) {
				$existing_channel_cache = $this->get_channel_cache( $term_and_params['term'], true );
				$avatar = SBY_Parse::get_avatar( $existing_channel_cache );
				$channel_id = SBY_Parse::get_channel_id( $existing_channel_cache );
				$this->set_avatar( $channel_id, $avatar );
			}
		}
	}

	/**
	 * Creates a key value pair of the username and the url of
	 * the avatar image
	 *
	 * @param $name
	 * @param $url
	 *
	 * @since 5.0
	 */
	public function set_avatar( $channel_id, $url ) {
		$this->channel_id_avatars[ $channel_id ] = $url;
	}

	/**
	 * @return array
	 */
	public function get_channel_id_avatars() {
		return $this->channel_id_avatars;
	}

	public function need_header( $settings, $feed_types_and_terms ) {
		if ( $settings['showheader'] ) {
			if ( ! empty( $settings['headerchannel'] ) || isset( $feed_types_and_terms['channels'] ) ) {
				return true;
			}
		}
		return false;
	}

	public function get_first_user( $feed_types_and_terms, $settings = array() ) {
		if ( ! empty( $settings['headerchannel'] ) ) {
			return $settings['headerchannel'];
		} elseif ( isset( $feed_types_and_terms['channels'][0] ) ) {
			return $feed_types_and_terms['channels'][0]['term'];
		} else {
			return '';
		}
	}

	public function maybe_add_live_html( $post ) {
		if ( ! isset( $post['iframe'] ) ) {
			return;
		}

		echo '<iframe  id="sby_live_player'. esc_attr( $post['iframe'] ) .'" width="640" height="360"  data-origwidth="640" data-origheight="360"  data-relstop="1"  src="https://www.youtube.com/embed/live_stream?enablejsapi=1&#038;channel='. esc_attr( $post['iframe'] ) .'&#038;rel=0&#038;modestbranding=1&#038;autoplay=0&#038;cc_load_policy=0&#038;iv_load_policy=1&#038;loop=0&#038;fs=1&#038;playsinline=0&#038;autohide=2&#038;theme=dark&#038;color=red&#038;controls=1&#038;" class="sby_live_player" title="YouTube player"  allow="autoplay; encrypted-media" allowfullscreen data-no-lazy="1" data-skipgform_ajax_framebjll=""></iframe>';
	}

	/**
	 * the API_Connect class can use either a premade url or
	 * settings from a connected account, type, and parameters
	 *
	 * @param array|string $connected_account_or_page
	 * @param null $type
	 * @param null $params
	 *
	 * @return object|SB_Instagram_API_Connect_Pro
	 */
	public function make_api_connection( $connected_account_or_page, $type = NULL, $params = NULL ) {
		return new SBY_API_Connect_Pro( $connected_account_or_page, $type, $params );
	}

	public function is_efficient_type( $type ) {
		return in_array( $type, array( 'playlist', 'channel' ), true );
	}

	public function get_play_list_for_term( $type, $term, $connected_account_for_term, $params ) {
		if ( $type === 'playlist' ) {
			return $term;
		}
		if ( $type === 'search' || $type === 'single' || $type === 'live' ) {
			return false;
		}

		$existing_channel_cache = $this->get_channel_cache( $term );

		if ( $existing_channel_cache ) {
			$this->channels_data[ $term ] = $existing_channel_cache;
		}

		if ( empty( $this->channels_data[ $term ] ) ) {
			if ( $connected_account_for_term['expires'] < time() + 5 ) {
				$error_message = '<p><b>' . __( 'Reconnect to YouTube to show this feed.', 'custom-facebook-feed' ) . '</b></p>';
				$error_message .= '<p>' . __( 'To create a new feed, first connect to YouTube using the "Connect to YouTube to Create a Feed" button on the settings page and connect any account.', SBY_TEXT_DOMAIN ) . '</p>';

				if ( current_user_can( 'manage_youtube_feed_options' ) ) {
					$error_message .= '<a href="' . admin_url( 'admin.php?page=youtube-feed' ) . '" target="blank" rel="noopener nofollow">' . __( 'Reconnect in the YouTube Feed Settings Area' ) . '</a>';
				}
				global $sby_posts_manager;

				$sby_posts_manager->add_frontend_error( 'accesstoken', $error_message );
				$sby_posts_manager->add_error( 'accesstoken', array( 'Trying to connect a new account', $error_message ) );

				return false;
			}
			$channel_data         = array();
			$api_connect_channels = $this->make_api_connection( $connected_account_for_term, 'channels', $params );

			$this->add_report( 'channel api call made for ' . $term . ' - ' . $type );

			$api_connect_channels->connect();
			if ( ! $api_connect_channels->is_wp_error() && ! $api_connect_channels->is_youtube_error() ) {
				$channel_data = $api_connect_channels->get_data();
				$channel_id = SBY_Parse::get_channel_id( $channel_data );
				$this->set_channel_cache( $channel_id, $channel_data );

				if ( isset( $params['channel_name'] ) ) {
					sby_set_channel_id_from_channel_name( $params['channel_name'], $channel_id );
					$this->set_channel_cache( $params['channel_name'], $channel_data );
				}

				$params = array( 'channel_id' => $channel_id );
				$this->channels_data[ $channel_id ] = $channel_data;
				$this->channels_data[ $term ] = $channel_data;
			} else {
				if ( ! $api_connect_channels->is_wp_error() ) {
					$return = SBY_API_Connect::handle_youtube_error( $api_connect_channels->get_data(), $connected_account_for_term );
					if ( $return && isset( $return['access_token'] ) ) {
						$connected_account_for_term['access_token'] = $return['access_token'];
						$connected_accounts_for_feed[ $term ]['access_token'] = $return['access_token'];
						$connected_account_for_term['expires'] = $return['expires_in'] + time();
						$connected_accounts_for_feed[ $term ]['expires'] = $return['expires_in'] + time();

						sby_update_or_connect_account( $connected_account_for_term );
						$this->add_report( 'refreshing access token for ' . $connected_account_for_term['channel_id'] );

						$sby_api_connect_channel = $this->make_api_connection( $connected_account_for_term, 'channels', $params );
						$sby_api_connect_channel->connect();
						if ( ! $sby_api_connect_channel->is_youtube_error() ) {
							$channel_data = $sby_api_connect_channel->get_data();
							$channel_id = SBY_Parse::get_channel_id( $channel_data );
							$this->set_channel_cache( $channel_id, $channel_data );

							if ( isset( $params['channel_name'] ) ) {
								sby_set_channel_id_from_channel_name( $params['channel_name'], $channel_id );
								$this->set_channel_cache( $params['channel_name'], $channel_data );
							}

							$this->channels_data[ $channel_id ] = $channel_data;
							$this->channels_data[ $term ] = $channel_data;

						}
					} else {
						$this->add_report( 'error connecting to channel' );
					}
				} else {
					$api_connect_channels->handle_wp_remote_get_error( $api_connect_channels->get_data() );
				}
			}
		}

		if ( $type === 'favorites' ) {
			$playlist = isset( $this->channels_data[ $term ]['items'][0]['contentDetails']['relatedPlaylists']['favorites'] ) ? $this->channels_data[ $term ]['items'][0]['contentDetails']['relatedPlaylists']['favorites'] : false;
			if ( $playlist === false ) {
				$this->add_report( 'No favorites playlist found' );
			}

		} else {
			$playlist = isset( $this->channels_data[ $term ]['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ) ? $this->channels_data[ $term ]['items'][0]['contentDetails']['relatedPlaylists']['uploads'] : false;
		}


		return $playlist;
	}

	protected function sort_posts( $post_set, $settings ) {
		if ( empty( $post_set ) ) {
			return $post_set;
		}

		// sorting done with "merge_posts" to be more efficient
		if ( $settings['sortby'] === 'alternate' || $settings['sortby'] === 'relevance' || $settings['sortby'] === 'api' ) {
			$return_post_set = $post_set;
		} elseif ( $settings['sortby'] === 'random' ) {
			/*
             * randomly selects posts in a random order. Cache saves posts
             * in this random order so paginating does not cause some posts to show up
             * twice or not at all
             */
			usort( $post_set, 'sby_rand_sort' );
			$return_post_set = $post_set;

		} else {
			// compares posted on dates of posts
			usort( $post_set, 'sby_date_sort' );
			$return_post_set = $post_set;
		}

		return $return_post_set;
	}

	/**
	 * Used for filtering a single API request worth of posts
	 *
	 * @param $post_set
	 *
	 * @return mixed
	 *
	 * @since 5.0
	 * @since 5.1 support for filtering "includes any includeword
	 *  and also does not include any excludeword"
	 */
	protected function filter_posts( $post_set, $settings = array() ) {

		if ( empty( $settings['includewords'] )
		     && empty( $settings['excludewords'] )
		     && empty( $settings['whitelist'] )
		     && empty( $settings['hidevideos'] ) ) {
			return $post_set;
		}

		$includewords = ! empty( $settings['includewords'] ) ? explode( ',', $settings['includewords'] ) : array();
		$excludewords = ! empty( $settings['excludewords'] ) ? explode( ',', $settings['excludewords'] ) : array();
		$hide_videos = ! empty( $settings['hidevideos'] ) && empty( $settings['doingModerationMode'] ) ? explode( ',', str_replace( ' ', '', $settings['hidevideos'] ) ) : array();
		$white_list = ! empty( $settings['whitelist'] ) && empty( $settings['doingModerationMode'] ) ? get_option( 'sb_youtube_white_lists_'.$settings['whitelist'], array() ) : false;

		$filtered_posts = array();
		foreach ( $post_set as $post ) {
			$keep_post = false;

			$padded_caption = ' ' . str_replace( array( '+', '%0A' ), ' ',  urlencode( str_replace( '#', ' HASHTAG', strtolower( SBY_Parse_Pro::get_caption( $post ) ) ) ) ) . ' ';
			$padded_title = ' ' . str_replace( array( '+', '%0A' ), ' ',  urlencode( str_replace( '#', ' HASHTAG', strtolower( SBY_Parse_Pro::get_video_title( $post ) ) ) ) ) . ' ';

			$id = SBY_Parse_Pro::get_video_id( $post );
			$post_id = SBY_Parse_Pro::get_post_id( $post );


			$is_hidden = false;
			if ( ! empty( $hide_videos )
			     && ((in_array( $id, $hide_videos, true ) || in_array( 'sby_' . $id, $hide_videos, true )) || (in_array( $post_id, $hide_videos, true ) || in_array( 'sby_' . $post_id, $hide_videos, true ))) ) {
				$is_hidden = true;
			}

			// any blocked photos will not pass any additional filters so don't bother processing
			if ( ! $is_hidden ) {
				$is_on_white_list = false;
				$has_includeword = false;
				$has_excludeword = false;
				$passes_word_filter = false;

				if ( $white_list ) {
					if ( in_array( $id, $white_list, true ) || in_array( 'sby_' . $id, $white_list, true ) ) {
						$is_on_white_list = true;
					}
				} elseif ( ! empty( $includewords ) || ! empty( $excludewords ) ) {
					if ( ! empty( $includewords ) ) {
						foreach ( $includewords as $includeword ) {
							if ( ! empty( $includeword ) ) {
								$converted_includeword = trim( str_replace( '+', ' ', urlencode( str_replace( '#', 'HASHTAG', strtolower( $includeword ) ) ) ) );
								if ( preg_match( '/\b' . $converted_includeword . '\b/i', $padded_caption, $matches ) ) {
									$has_includeword = true;
								} elseif ( preg_match( '/\b' . $converted_includeword . '\b/i', $padded_title, $matches ) ) {
									$has_includeword = true;
								}
							}
						}
					}

					if ( ! empty( $excludewords ) ) {
						foreach ( $excludewords as $excludeword ) {
							if ( ! empty( $excludeword ) ) {
								$converted_excludeword = trim( str_replace('+', ' ', urlencode( str_replace( '#', 'HASHTAG', strtolower( $excludeword ) ) ) ) );
								if ( preg_match('/\b'.$converted_excludeword.'\b/i', $padded_caption, $matches ) ) {
									$has_excludeword = true;
								} elseif ( preg_match( '/\b' . $converted_excludeword . '\b/i', $padded_title, $matches ) ) {
									$has_excludeword = true;
								}
							}
						}
					}
					if ( ! empty( $excludewords ) && ! empty( $includewords ) ) {
						$passes_word_filter = $has_includeword && ! $has_excludeword;
					} elseif ( ! empty( $includewords ) ) {
						$passes_word_filter = $has_includeword;
					} else {
						$passes_word_filter = !$has_excludeword;
					}

				} else {
					// no other filters so it belongs in the feed
					$keep_post = true;
				}

				if ( $is_on_white_list || $passes_word_filter ) {
					$keep_post = true;
				}
			}

			$keep_post = apply_filters( 'sby_passes_filter', $keep_post, $post, $settings );
			if ( $keep_post ) {
				$filtered_posts[] = $post;
			}

		}

		return $filtered_posts;
	}

	/**
	 * Total number of IDs in the white list already exist in the feed. Used
	 * to prevent further pagination when no more white listed posts will be
	 * found
	 *
	 * @param array $settings
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	protected function xfeed_is_complete( $settings, $offset = 0 ) {
		if ( ! empty( $settings['whitelist_ids'] ) ) {
			if ( isset( $settings['doingModerationMode'] ) && $settings['doingModerationMode'] ) {
				return false;
			}
			$total_posts_loaded = $settings['num'] + $offset;

			if ( (int)$settings['whitelist_num'] <= $total_posts_loaded ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Adds various data attributes to the main feed divthat are used
	 * by the JavaScript file to layout the feed, trigger certain features,
	 * and launchvmoderation mode
	 *
	 * @param $other_atts
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	protected function add_other_atts( $other_atts, $settings ) {
		$options_att_arr = array();

		$layout = $settings['layout'];
		if ( ! in_array( $layout, array( 'masonry', 'highlight', 'carousel' ) ) ) {
			$layout = 'grid';
		}

		if ( $layout === 'carousel' ) {
			$arrows = $settings['carouselarrows'] == 'true' || $settings['carouselarrows'] == 'on' || $settings['carouselarrows'] == 1 || $settings['carouselarrows'] == '1';
			$pag = $settings['carouselpag'] == 'true' || $settings['carouselpag'] == 'on' || $settings['carouselpag'] == 1 || $settings['carouselpag'] == '1';
			$autoplay = $settings['carouselautoplay'] == 'true' || $settings['carouselautoplay'] == 'on' || $settings['carouselautoplay'] == 1 || $settings['carouselautoplay'] == '1';
			$time = $autoplay ? (int)$settings['carouseltime'] : false;
			$loop = ! empty( $settings['carouselloop'] ) && ($settings['carouselloop'] !== 'rewind') ? false : true;
			$rows = ! empty( $settings['carouselrows'] ) ? min( (int)$settings['carouselrows'], 2 ) : 1;
			$options_att_arr['carousel'] = array( $arrows, $pag, $autoplay, $time, $loop, $rows );
		}

		$options_att_arr['cta'] = array(
			'type' => 'default'
		);
		if ( $settings['cta'] === 'link' ) {
			$options_att_arr['cta']['type'] = 'link';
		} else if ( $settings['cta'] === 'related' ) {
			$options_att_arr['cta']['type'] = 'related';
			$options_att_arr['cta']['defaultPosts'] = array();

			if ( $settings['num'] < 5 ) {
				$options_att_arr['cta']['defaultPosts'] = $this->get_cta_posts();
			}
		}

		$options_att_arr['cta']['defaultLink'] = $settings['linkurl'];
		$options_att_arr['cta']['defaultText'] = $settings['linktext'];
		$options_att_arr['cta']['openType'] = $settings['linkopentype'];
		$button_color = str_replace( '#', '', $settings['linkcolor'] );
		$button_text_color = str_replace( '#', '', $settings['linktextcolor'] );
		$options_att_arr['cta']['color'] = ! empty( $button_color ) ? sby_hextorgb( $button_color ) : '';
		$options_att_arr['cta']['textColor'] = ! empty( $button_text_color ) ? sby_hextorgb( $button_text_color ) : '';

		if ( ! empty( $settings['descriptionlength'] ) ) {
			$options_att_arr['descriptionlength'] = (int)$settings['descriptionlength'];
		}

		$other_atts .= ' data-options="'.esc_attr( wp_json_encode( $options_att_arr ) ).'"';

		return $other_atts;
	}

	public function get_cta_posts() {
		$posts = $this->get_post_data();


		if ( count( $posts ) >= 4 ) {
			$cta_posts_indices = array_rand( $posts, min( count( $posts ), 5 ) );
			$cta_array = array();
			foreach ( $cta_posts_indices as $cta_post_index ) {
				$cta_array[] = array(
					'videoID' =>  SBY_Parse::get_video_id( $posts[ $cta_post_index ] ),
					'thumbnail' => SBY_Parse::get_media_url( $posts[ $cta_post_index ], 'medium' ),
					'title' => SBY_Parse::get_video_title( $posts[ $cta_post_index ] )
				);
			}
		} else {
			$cta_array = array();
			foreach ( $posts as $post ) {
				$cta_array[] = array(
					'videoID' =>  SBY_Parse::get_video_id( $post ),
					'thumbnail' => SBY_Parse::get_media_url( $post, 'medium' ),
					'title' => SBY_Parse::get_video_title( $post )
				);
			}
		}




		return $cta_array;
	}

	public function requires_workaround_connection( $type ) {
		return $type === 'live';
	}

	public function make_workaround_connection( $connected_account_for_term, $type, $params ) {

		$params['channel_id'] = $params['channelId'];
		$rss_connect_playlist_items = new SBY_RSS_Connect( 'playlistItems', $params );
		$rss_connect_playlist_items->connect();

		$one_successful_connection = true;

		$data = $rss_connect_playlist_items->get_data();
		//var_dump( $data );

		$ids = array();
		if ( is_array( $data ) ) {
			foreach ( $data as $post ) {
				$ids[] = SBY_Parse::get_video_id( $post );
			}
		}
		$params['video_ids'] = $ids;

		$connection = $this->make_api_connection( $connected_account_for_term, 'single', $params );
		$connection->connect();
		$potential_live_streams = $connection->get_data();

		$confirmed_upcoming_or_live = array();
		if ( is_array( $potential_live_streams['items'] ) ) {

			foreach ( $potential_live_streams['items'] as $post ) {
				if ( empty( $post['liveStreamingDetails']['actualEndTime'] ) ) {
					if ( ! empty( $post['liveStreamingDetails']['scheduledStartTime'] ) ) {
						$confirmed_upcoming_or_live[] = $post;
					}
				}
			}
		}
		$confirmed_upcoming_or_live = array_reverse( $confirmed_upcoming_or_live );
		$potential_live_streams['items'] = $confirmed_upcoming_or_live;

		$connection->set_response( $potential_live_streams );

		return $connection;
	}

	/**
	 * Creates an array of standard classes to be added to the main feed div.
	 *
	 * @param $settings
	 *
	 * @return array
	 *
	 * @since 5.0
	 */
	protected function xadd_classes( $settings ) {
		$classes = array();

		$moderation_mode = (isset ( $_GET['sbi_moderation_mode'] ) && $_GET['sbi_moderation_mode'] === 'true' && current_user_can( 'edit_posts' ));

		if ( $moderation_mode ) {
			$classes[] = 'sbi_moderation_mode';
		}
		return array();
	}

	public function convert_feed_id_to_stats_transient( $feed_id ) {
		$attempt = str_replace( 'sby_', 'sby_-', $feed_id );
		if ( $attempt === $feed_id ) {
			$attempt = '-' . $attempt;
		}

		return $attempt;
	}

	public function get_misc_data( $feed_id, $posts ) {
		$misc = array(
			'stats' => array()
		);

		if ( ! empty( $feed_id ) ) {
			$stats_cache = $this->get_regular_stats_cache( $feed_id );
			if ( $stats_cache ) {
				$this->add_report('Found stats cache');
			} else {
				$this->add_report('No stats cache found');
			}
			$misc['stats'] = $stats_cache ? $stats_cache : array();
		}

		if ( ! empty( $posts ) ) {
			$vid_ids = array();
			foreach ( $posts as $post ) {
				$vid_ids[] = SBY_Parse::get_video_id( $post );
			}

			if ( ! empty( $vid_ids ) ) {
				$details_query = new SBY_YT_Details_Query( array( 'video_ids' => $vid_ids ) );
				$stats_cached_results = $details_query->get_cached_details_for_posts();
				$this->add_report('Getting cached stats for posts');


				$organized_stats = array();
				$organized_live_streaming_details = array();
				foreach ( $stats_cached_results as $post ) {
					$organized_stats[ $post['sby_video_id'] ] = $post;
					$organized_live_streaming_details[ $post['sby_video_id'] ] = $post;
				}
				$misc['stats'] = $organized_stats;
				$misc['live_streaming_details'] = $organized_live_streaming_details;
				if ( ! empty( $feed_id ) ) {
					$this->add_report('Adding to stats cache');

					$this->add_stats_to_cache( $feed_id, $misc['stats'] );
				}

			}
		}

		return $misc;
	}

	protected function add_stats_to_cache( $feed_id, $stats ) {
		$stats_array = is_array( $stats ) ? $stats : array();

		$cache = $this->get_regular_stats_cache( $feed_id );
		if ( is_array( $cache ) ) {
			$stats_array = array_merge( $stats_array, $cache );
		}

		$stats_json = wp_json_encode( $stats_array );

		set_transient( $this->convert_feed_id_to_stats_transient( $feed_id ), $stats_json );

		$this->set_stats_data( $stats_array );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	public function get_stats_data() {
		return $this->stats_cache;
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	public function set_stats_data( $stats_array ) {
		$this->stats_cache = $stats_array;
	}

	public function are_posts_with_no_details() {
		return (! empty( $this->post_ids_with_no_details ));
	}

	protected function posts_loop( $posts, $settings, $offset = 0 ) {

		$image_ids = array();
		$post_index = $offset;
		if ( ! isset( $settings['feed_id'] ) ) {
			$settings['feed_id'] = $this->regular_feed_transient_name;
		}
		$misc_data = $this->get_misc_data( $settings['feed_id'], $posts );
		$icon_type = $settings['font_method'];

		foreach ( $posts as $post ) {
			$post_id = SBY_Parse::get_post_id( $post );
			$video_id = SBY_Parse::get_video_id( $post );
			$image_ids[] = $post_id;
			if ( empty( $misc_data['stats'][ $video_id ] ) ) {
				$this->post_ids_with_no_details[] = $video_id;
			}
			include sby_get_feed_template_part( 'item', $settings );
			$post_index++;
		}

		$this->image_ids_post_set = $image_ids;
	}

	/**
	 * Checks the database option related the transient expiration
	 * to ensure it will be available when the page loads
	 *
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function get_regular_stats_cache( $feed_id ) {
		//Check whether the cache transient exists in the database and is available for more than one more minute
		$transient = get_transient( $this->convert_feed_id_to_stats_transient( $feed_id ) );

		if ( $transient ) {
			$transient = json_decode( $transient );
		}

		return $transient;
	}
}