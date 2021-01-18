<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SBY_Parse
{
	/**
	 * @param $post array
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public static function get_post_id( $post ) {
		if ( isset( $post['id'] ) && ! is_array( $post['id'] ) ) {
			return $post['id'];
		} else {
			return SBY_Parse::get_channel_id( $post ) . '_' . SBY_Parse::get_video_id( $post );
		}
	}

	public static function get_video_id( $post ) {
		if ( isset( $post['snippet']['resourceId']['videoId'] ) ) {
			return $post['snippet']['resourceId']['videoId'];
		} elseif ( isset( $post['id']['videoId'] ) ) {
			return $post['id']['videoId'];
		} elseif ( isset( $post['id']) ) {
			return $post['id'];
		}

		return '';
	}

	/**
	 * @param $post array
	 *
	 * @return false|int
	 *
	 * @since 1.0
	 */
	public static function get_timestamp( $post ) {
		$timestamp = 0;
		if ( isset( $post['snippet']['publishedAt'] ) ) {
			$remove_extra = str_replace( array( 'T', '+00:00', '.000Z', '+' ), ' ', $post['snippet']['publishedAt'] );
			$timestamp = strtotime( $remove_extra );
		}

		return $timestamp;
	}

	/**
	 * @param $post array
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public static function get_permalink( $post ) {
		if ( isset( $post['snippet']['resourceId']['videoId'] ) ) {
			return 'https://www.youtube.com/watch?v=' . $post['snippet']['resourceId']['videoId'];
		} elseif ( isset( $post['snippet']['channelId'] ) ) {
			return 'https://www.youtube.com/channel/' . $post['snippet']['channelId'];
		}

		return 'https://www.youtube.com/';
	}

	/**
	 * @param array $post
	 * @param string $resolution
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_media_url( $post, $resolution = 'lightbox' ) {
		$thumbnail_key = 'standard';
		switch ( $resolution ) {
			case 'thumb' :
				$thumbnail_key = 'default';
				break;
			case 'medium' :
				$thumbnail_key = 'medium';
				break;
			case 'high' :
				$thumbnail_key = 'high';
				break;
			case 'lightbox' :
				$thumbnail_key = 'maxres';
				break;
		}

		if ( isset( $post['snippet']['thumbnails'][ $thumbnail_key ]['url'] ) ) {
			return $post['snippet']['thumbnails'][ $thumbnail_key ]['url'];
		} elseif ( isset( $post['snippet']['thumbnails']['high']['url'] ) ) {
			return $post['snippet']['thumbnails']['high']['url'];
		} elseif ( isset( $post['snippet']['thumbnails']['medium']['url'] ) ) {
			return $post['snippet']['thumbnails']['medium']['url'];
		}

		return '';
	}

	/**
	 * Uses the existing data for the individual YouTube post to
	 * set the best image sources for each resolution size. Due to
	 * random bugs or just how the API works, different post types
	 * need special treatment.
	 *
	 * @param array $post
	 * @param array $resized_images
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public static function get_media_src_set( $post, $resized_images = array() ) {
		$media_urls = array();
		$thumbnails = isset( $post['snippet']['thumbnails'] ) ? $post['snippet']['thumbnails'] : false;
		$largest_found = '';

		if ( $thumbnails ) {
			if ( isset( $thumbnails['default']['url'] ) ) {
				$media_urls['120'] = $thumbnails['default']['url'];
				$largest_found = $thumbnails['default']['url'];
			} else {
				$media_urls['120'] = $largest_found;
			}
			if ( isset( $thumbnails['medium']['url'] ) ) {
				$media_urls['320'] = $thumbnails['medium']['url'];
				$largest_found = $thumbnails['medium']['url'];
			} else {
				$media_urls['320'] = $largest_found;
			}
			if ( isset( $thumbnails['high']['url'] ) ) {
				$media_urls['480'] = $thumbnails['high']['url'];
				$largest_found = $thumbnails['high']['url'];
			} else {
				$media_urls['480'] = $largest_found;
			}
			if ( isset( $thumbnails['standard']['url'] ) ) {
				$media_urls['640'] = $thumbnails['standard']['url'];
			} else {
				$media_urls['640'] = $largest_found;
			}
		}


		return $media_urls;
	}

	/**
	 * A default can be set in the case that the user doesn't use captions
	 * for posts as this is also used as the alt text for the image.
	 *
	 * @param $post
	 * @param string $default
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_caption( $post, $default = '' ) {
		$caption = $default;
		if ( isset( $post['snippet']['description'] ) ) {
			$caption = $post['snippet']['description'];
		}

		return $caption;
	}

	public static function get_video_title( $channel_or_playlist_item_data ) {
		if ( isset( $channel_or_playlist_item_data['items'][0]['snippet']['title'] ) ) {
			return $channel_or_playlist_item_data['items'][0]['snippet']['title'];
		} else if ( isset( $channel_or_playlist_item_data['snippet']['title'] ) ) {
			return $channel_or_playlist_item_data['snippet']['title'];
		}
		return '';
	}

	public static function get_channel_id( $channel_or_playlist_item_data ) {
		if ( isset( $channel_or_playlist_item_data['items'][0]['id'] ) ) {
			return $channel_or_playlist_item_data['items'][0]['id'];
		} elseif ( isset( $channel_or_playlist_item_data['snippet']['channelId'] ) ) {
			return $channel_or_playlist_item_data['snippet']['channelId'];
		}
		return '';
	}

	public static function get_channel_title( $channel_or_playlist_item_data ) {
		if ( isset( $channel_or_playlist_item_data['items'][0]['snippet']['title'] ) ) {
			return $channel_or_playlist_item_data['items'][0]['snippet']['title'];
		} elseif ( isset( $channel_or_playlist_item_data['snippet']['channelTitle'] ) ) {
			return $channel_or_playlist_item_data['snippet']['channelTitle'];
		}
		return '';
	}

	public static function get_channel_permalink( $channel_data ) {
		return 'https://www.youtube.com/channel/' . SBY_Parse::get_channel_id( $channel_data ) . '/';
	}

	/**
	 * @param array $header_data
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_avatar( $header_data, $settings = array( 'favor_local' => false ) ) {
		if ( $settings['favor_local'] && ! empty( $header_data['local_avatar'] ) ) {
			return $header_data['local_avatar'];
		} else {
			if ( isset( $header_data['items'][0]['snippet']['thumbnails'] ) ) {
				$header_size = isset( $settings['headersize'] ) ? $settings['headersize'] : '';
				if ( $header_size === 'large' ) {
					return $header_data['items'][0]['snippet']['thumbnails']['high']['url'];
				} elseif ( $header_size === 'medium' ) {
					return $header_data['items'][0]['snippet']['thumbnails']['medium']['url'];
				} else {
					return $header_data['items'][0]['snippet']['thumbnails']['default']['url'];
				}
			}
		}
		return '';
	}

	public static function get_item_avatar( $post, $avatars ) {
		if ( empty ( $avatars ) ) {
			return '';
		} else {
			$username = SBY_Parse::get_channel_id( $post );
			if ( isset( $avatars[ $username ] ) ) {
				return $avatars[ $username ];
			}
		}
		return '';
	}

	/**
	 * Account bio/description used in header
	 *
	 * @param $header_data
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_channel_description( $header_data ) {
		if ( isset( $header_data['items'][0]['snippet']['description'] ) ) {
			return $header_data['items'][0]['snippet']['description'];
		}
		return '';
	}
}