<?php
/**
 * Class SBY_Parse_Pro
 *
 * @since 5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SBY_Parse_Pro extends SBY_Parse {

	public static function get_subscriber_count( $channel_data ) {
		if ( isset( $channel_data['items'][0]['statistics']['subscriberCount'] ) ) {
			return $channel_data['items'][0]['statistics']['subscriberCount'];
		}

		return '';
	}

	public static function get_view_count( $post, $misc_data = array() ) {
		if ( isset( $post['statistics']['viewCount'] ) ) {
			return $post['statistics']['viewCount'];
		} elseif ( isset( $misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_view_count'] ) ) {
			return $misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_view_count'];
		} elseif ( isset( $misc_data['sby_view_count'][0] ) ) {
			return $misc_data['sby_view_count'][0];
		}

		return '';
	}

	/**
	 * @param $post array
	 *
	 * @return int
	 *
	 * @since 5.0
	 */
	public static function get_like_count( $post, $misc_data = array() ) {
		if ( isset( $post['statistics']['likeCount'] ) ) {
			return $post['statistics']['likeCount'];
		} elseif ( isset( $misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_like_count'] ) ) {
			return $misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_like_count'];
		} elseif ( isset( $misc_data['sby_like_count'][0] ) ) {
			return (float)$misc_data['sby_like_count'][0];
		}

		return '';
	}

	/**
	 * @param $post array
	 *
	 * @return int
	 *
	 * @since 5.0
	 */
	public static function get_comment_count( $post, $misc_data = array() ) {
		if ( isset( $post['statistics']['commentCount'] ) ) {
			return $post['statistics']['commentCount'];
		} elseif ( isset( $misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_comment_count'] ) ) {
			return (float)$misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_comment_count'];
		} elseif ( isset( $misc_data['sby_comment_count'][0] ) ) {
			return (float)$misc_data['sby_comment_count'][0];
		}

		return '';
	}

	public static function get_caption( $post, $default = '', $misc_data = array() ) {
		$caption = $default;
		if ( isset( $misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_description'] ) ) {
			$caption = $misc_data['stats'][ SBY_Parse::get_video_id( $post ) ]['sby_description'];
		} elseif ( isset( $post['snippet']['description'] ) ) {
			$caption = $post['snippet']['description'];
		}

		return $caption;
	}

	public static function get_item_avatar( $post, $avatars = array() ) {
		if ( empty ( $avatars ) ) {
			return '';
		} else {
			$username = SBY_Parse_Pro::get_channel_id( $post );
			if ( isset( $avatars[ $username ] ) ) {
				return $avatars[ $username ];
			}
		}

		return '';
	}

	/**
	 * Number of posts made by account
	 *
	 * @param $header_data
	 *
	 * @return int
	 *
	 * @since 5.0
	 */
	public static function get_post_count( $header_data ) {
		if ( isset( $header_data['data']['counts'] ) ) {
			return $header_data['data']['counts']['media'];
		} elseif ( isset( $header_data['counts'] ) ) {
			return $header_data['counts']['media'];
		} elseif ( isset( $header_data['media_count'] ) ) {
			return $header_data['media_count'];
		}

		return 0;
	}

	/**
	 * Number of followers for account
	 *
	 * @param $header_data
	 *
	 * @return int
	 *
	 * @since 5.0
	 */
	public static function get_follower_count( $header_data ) {
		if ( isset( $header_data['data']['counts'] ) ) {
			return $header_data['data']['counts']['followed_by'];
		} elseif ( isset( $header_data['counts'] ) ) {
			return $header_data['counts']['followed_by'];
		} elseif ( isset( $header_data['followers_count'] ) ) {
			return $header_data['followers_count'];
		}

		return 0;
	}

	public static function get_live_broadcast_content( $post, $misc_data = array() ) {
		if ( isset( $post['snippet']['liveBroadcastContent'] ) ) {
			return $post['snippet']['liveBroadcastContent'];
		} elseif ( isset( $misc_data['sby_live_broadcast_content'][0] ) ) {
			return $misc_data['sby_live_broadcast_content'][0];
		} elseif ( isset( $post['sby_live_broadcast_content'] ) ) {
			return $post['sby_live_broadcast_content'];
		}

		return 'none';
	}

	public static function get_live_streaming_timestamp( $post, $misc_data = array() ) {
		$actual_start_timestamp = SBY_Parse_Pro::get_actual_start_timestamp( $post, $misc_data );
		if ( $actual_start_timestamp > 0 ) {
			return $actual_start_timestamp;
		}

		return SBY_Parse_Pro::get_scheduled_start_timestamp( $post, $misc_data );
	}

	public static function get_scheduled_start_timestamp( $post, $misc_data = array() ) {

		if ( ! empty( $post['liveStreamingDetails']['scheduledStartTime'] ) ) {
			$remove_extra = str_replace( array( 'T', '+00:00', '.000Z', '+' ), ' ', $post['liveStreamingDetails']['scheduledStartTime'] );
			$timestamp    = strtotime( $remove_extra );

			return $timestamp;
		} elseif ( isset( $misc_data['live_streaming_details'][ SBY_Parse::get_video_id( $post ) ]['sby_scheduled_start_time'] ) ) {
			return strtotime( $misc_data['live_streaming_details'][ SBY_Parse::get_video_id( $post ) ]['sby_scheduled_start_time'] );
		} elseif ( isset( $misc_data['sby_scheduled_start_time'][0] ) ) {
			return strtotime( $misc_data['sby_scheduled_start_time'][0] );
		} elseif ( isset( $post['sby_scheduled_start_time'] ) ) {
			return strtotime( $post['sby_scheduled_start_time'] );
		}

		return 0;
	}

	public static function get_actual_start_timestamp( $post, $misc_data = array() ) {

		if ( ! empty( $post['liveStreamingDetails']['actualStartTime'] ) ) {
			$remove_extra = str_replace( array( 'T', '+00:00', '.000Z', '+' ), ' ', $post['liveStreamingDetails']['actualStartTime'] );
			$timestamp    = strtotime( $remove_extra );

			return $timestamp;
		} elseif ( isset( $misc_data['live_streaming_details'][ SBY_Parse::get_video_id( $post ) ]['sby_actual_start_time'] ) ) {
			return strtotime( $misc_data['live_streaming_details'][ SBY_Parse::get_video_id( $post ) ]['sby_actual_start_time'] );
		} elseif ( isset( $misc_data['sby_actual_start_time'][0] ) ) {
			return strtotime( $misc_data['sby_actual_start_time'][0] );
		} elseif ( isset( $post['sby_actual_start_time'] ) ) {
			return strtotime( $post['sby_actual_start_time'] );
		}

		return 0;
	}

	public static function get_actual_end_timestamp( $post, $misc_data = array() ) {

		if ( ! empty( $post['liveStreamingDetails']['actualEndTime'] ) ) {
			$remove_extra = str_replace( array( 'T', '+00:00', '.000Z', '+' ), ' ', $post['liveStreamingDetails']['actualEndTime'] );
			$timestamp    = strtotime( $remove_extra );

			return $timestamp;
		} elseif ( isset( $misc_data['live_streaming_details'][ SBY_Parse::get_video_id( $post ) ]['sby_actual_end_time'] ) ) {
			return strtotime( $misc_data['live_streaming_details'][ SBY_Parse::get_video_id( $post ) ]['sby_actual_end_time'] );
		} elseif ( isset( $misc_data['sby_actual_end_time'][0] ) ) {
			return strtotime( $misc_data['sby_actual_end_time'][0] );
		} elseif ( isset( $post['sby_actual_end_time'] ) ) {
			return strtotime( $post['sby_actual_end_time'] );
		}

		return 0;
	}
}
	//liveStreamingDetails, scheduledStartTime