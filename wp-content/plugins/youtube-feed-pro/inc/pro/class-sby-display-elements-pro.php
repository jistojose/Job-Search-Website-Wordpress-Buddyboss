<?php
/**
 * Class SBY_Display_Elements_Pro
 *
 * @since 5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SBY_Display_Elements_Pro extends SBY_Display_Elements
{

	/**
	 * Pro - First looks for Pro only icons
	 *
	 * @param $type
	 * @param $icon_type
	 * @param $styles
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_icon( $type, $icon_type, $styles = '' ) {
		$icon = self::get_pro_icons( $type, $icon_type, $styles );

		if ( $icon === '' ) {
			$icon = self::get_basic_icons( $type, $icon_type );
		}
		return $icon;
	}

	/**
	 * @param $type
	 * @param $icon_type
	 * @param $styles
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	private static function get_pro_icons( $type, $icon_type, $styles = '' ) {
		if ( $type === 'date' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg ' . $styles . ' class="svg-inline--fa fa-clock fa-w-16" aria-hidden="true" data-fa-processed="" data-prefix="far" data-icon="clock" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8L334.6 349c-3.9 5.3-11.4 6.5-16.8 2.6z"></path></svg>';
			} else {
				return '<i class="fa fa-clock" aria-hidden="true"></i>';
			}

		} elseif ( $type === 'likes' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg ' . $styles . ' class="svg-inline--fa fa-heart fa-w-18" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="heart" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M414.9 24C361.8 24 312 65.7 288 89.3 264 65.7 214.2 24 161.1 24 70.3 24 16 76.9 16 165.5c0 72.6 66.8 133.3 69.2 135.4l187 180.8c8.8 8.5 22.8 8.5 31.6 0l186.7-180.2c2.7-2.7 69.5-63.5 69.5-136C560 76.9 505.7 24 414.9 24z"></path></svg>';
			} else {
				return '<i class="fa fa-heart" aria-hidden="true"></i>';
			}
		} elseif ( $type === 'comments' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg ' . $styles . ' class="svg-inline--fa fa-comment fa-w-18" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="comment" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M576 240c0 115-129 208-288 208-48.3 0-93.9-8.6-133.9-23.8-40.3 31.2-89.8 50.3-142.4 55.7-5.2.6-10.2-2.8-11.5-7.7-1.3-5 2.7-8.1 6.6-11.8 19.3-18.4 42.7-32.8 51.9-94.6C21.9 330.9 0 287.3 0 240 0 125.1 129 32 288 32s288 93.1 288 208z"></path></svg>';
			} else {
				return '<i class="fa fa-comment" aria-hidden="true"></i>';
			}

		} elseif ( $type === 'newlogo' ) {
			return '<svg ' . $styles . ' class="sby_new_logo fa-instagram fa-w-14" aria-hidden="true" data-fa-processed="" data-prefix="fab" data-icon="instagram" role="img" viewBox="0 0 448 512">
                <path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
            </svg>';

			//return '<i class="sby_new_logo" aria-hidden="true"></i>';
		} elseif ( $type === 'map_marker' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg ' . $styles . ' class="svg-inline--fa fa-map-marker fa-w-12" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="map-marker" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0z"></path></svg>';
			} else {
				return '<i class="fa fa-map-marker" aria-hidden="true"></i>';
			}

		} elseif ( $type === 'photo' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg class="svg-inline--fa fa-image fa-w-16" aria-hidden="true" data-fa-processed="" data-prefix="far" data-icon="image" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 448H48c-26.51 0-48-21.49-48-48V112c0-26.51 21.49-48 48-48h416c26.51 0 48 21.49 48 48v288c0 26.51-21.49 48-48 48zM112 120c-30.928 0-56 25.072-56 56s25.072 56 56 56 56-25.072 56-56-25.072-56-56-56zM64 384h384V272l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L208 320l-55.515-55.515c-4.686-4.686-12.284-4.686-16.971 0L64 336v48z"></path></svg>';
			} else {
				return '<i class="fa fa-image" aria-hidden="true"></i>';
			}
		} elseif ( $type === 'user' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg class="svg-inline--fa fa-user fa-w-16" style="margin-right: 3px;" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="user" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M96 160C96 71.634 167.635 0 256 0s160 71.634 160 160-71.635 160-160 160S96 248.366 96 160zm304 192h-28.556c-71.006 42.713-159.912 42.695-230.888 0H112C50.144 352 0 402.144 0 464v24c0 13.255 10.745 24 24 24h464c13.255 0 24-10.745 24-24v-24c0-61.856-50.144-112-112-112z"></path></svg>';
			} else {
				return '<i class="fa fa-user" aria-hidden="true"></i>';
			}
		} elseif ( $type === 'views' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-eye fa-w-18"><path fill="currentColor" d="M288 144a110.94 110.94 0 0 0-31.24 5 55.4 55.4 0 0 1 7.24 27 56 56 0 0 1-56 56 55.4 55.4 0 0 1-27-7.24A111.71 111.71 0 1 0 288 144zm284.52 97.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400c-98.65 0-189.09-55-237.93-144C98.91 167 189.34 112 288 112s189.09 55 237.93 144C477.1 345 386.66 400 288 400z" class=""></path></svg>';
			} else {
				return '<i class="fa fa-eye" aria-hidden="true"></i>';
			}

		} else {
			return '';
		}
	}

	/**
	 * The sby_link element for each item has different styles applied if
	 * the lightbox is disabled.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_sby_link_classes( $settings ) {
		if ( ! empty( $settings['disablelightbox'] ) && ($settings['disablelightbox'] === 'on' || $settings['disablelightbox'] === 'true' || $settings['disablelightbox'] === true) ) {
			return ' sby_disable_lightbox';
		}
		return '';
	}

	/**
	 * Custom background color for the hover element. Slightly opaque.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_sby_link_styles( $settings ) {
		if ( ! empty( $settings['hovercolor'] ) && $settings['hovercolor'] !== '#000' ) {
			return 'style="background: rgba(' . esc_attr( sby_hextorgb( $settings['hovercolor'] ) ) . ',0.85)"';
		}
		return '';
	}

	/**
	 * Text color for the hover element.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_hover_styles( $settings ) {
		if ( ! empty( $settings['hovertextcolor'] ) && $settings['hovertextcolor'] !== '#000' ) {
			return 'style="color: rgba(' . esc_attr( sby_hextorgb( $settings['hovertextcolor'] ) ) . ',1)"';
		}
		return '';
	}

	/**
	 * Inline styles applied to the caption/like count/comment count information appearing
	 * underneath each post by default.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_sby_info_styles( $settings ) {
		$styles = '';
		if ( (! empty( $settings['captionsize'] ) && $settings['captionsize'] !== 'inherit') || ! empty( $settings['captioncolor'] ) ) {
			$styles = 'style="';
			if ( ! empty( $settings['captionsize'] ) && $settings['captionsize'] !== 'inherit' ) {
				$styles .= 'font-size: '. esc_attr( $settings['captionsize'] ) . 'px;';
			}
			if ( ! empty( $settings['captioncolor'] ) ) {
				$styles .= 'color: rgb(' . esc_attr( sby_hextorgb( $settings['captioncolor'] ) ). ');';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * Color of the likes heart icon and the comment voice box icon in the
	 * sby_info area.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_sby_meta_color_styles( $settings ) {
		if ( ! empty( $settings['likescolor'] ) ) {
			return 'style="color: rgb(' . esc_attr( sby_hextorgb( $settings['likescolor'] ) ). ');"';
		}
		return '';
	}

	/**
	 * Size of the likes heart icon and the comment voice box icon in the
	 * sby_info area.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_sby_meta_size_color_styles( $settings ) {
		$styles = '';
		if ( (! empty( $settings['likessize'] ) && $settings['likessize'] !== 'inherit') || ! empty( $settings['likescolor'] ) ) {
			$styles = 'style="';
			if ( ! empty( $settings['likessize'] ) && $settings['likessize'] !== 'inherit' ) {
				$styles .= 'font-size: '. esc_attr( $settings['likessize'] ) . 'px;';
			}
			if ( ! empty( $settings['likescolor'] ) ) {
				$styles .= 'color: rgb(' . esc_attr( sby_hextorgb( $settings['likescolor'] ) ). ');';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * A not very elegant but useful method to abstract out how the settings
	 * work for displaying certain elements in the feed.
	 *
	 * @param string $element specific key, view below for supported ones
	 * @param $settings
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	public static function should_show_element( $element, $context, $settings ) {
		//user, views, date
		if ( $context === 'item-hover' ) {
			if ( $settings['layout'] === 'list' ) {
				$include_array = array();
			} else {
				$include_array = $settings['hoverinclude'];
			}
		} elseif ( $context === 'result' ) {
			$include_array = array();
		} elseif ( $context === 'single' ) {
			$cpt_settings = SBY_CPT::get_sby_cpt_settings();
			$include_array = $cpt_settings['include'];
		} else {
			$include_array = $settings['include'];
		}

		$include_array = is_array( $include_array ) ? $include_array : explode( ',', str_replace( ' ', '', $include_array ) );

		if ( $element === 'meta' ) {
			if ( in_array( 'user', $include_array, true )
				|| in_array( 'views', $include_array, true )
				|| in_array( 'date', $include_array, true ) ) {
				return true;
			} else {
				return false;
			}

		} elseif ( in_array( $element, $include_array, true ) ) {
			return true;
		}

		return false;
	}

	public static function date_format_setting( $date_format, $custom_format ) {
		if ( empty( $date_format ) ) {
			$date_format = get_option( 'date_format' );
			if ( ! $date_format ) {
				$date_format = 'F j, Y';
			}
			$time_format = get_option( 'time_format' );
			if ( ! $time_format ) {
				$date_format .= ' g:i a';
			} else {
				$date_format .= ' ' . $time_format;
			}
			return $date_format;
		} else {
			switch ( $date_format ) {
				case '1':
					return 'F jS, g:i a';
				case '2':
					return 'F jS';
				case '3':
					return 'D F jS';
				case '4':
					return 'l F jS';
				case '5':
					return 'D M jS, Y';
				case '6':
					return 'l F jS, Y - g:i a';
				case '7':
					return 'm.d.y';
				case '8':
					return'm.d.y - G:i';
				case '9':
					return'm/d/y';
					break;
				case '10':
					return 'd.m.y';
				case '11':
					return 'd/m/y';
				case '12':
					return 'l jS F Y, G:i';
				case 'custom':
					if ( empty( $custom_format ) ) {
						return 'F jS, g:i a';
					}
					return $custom_format;
				default:
					// if the value is not one of the number options and not "custom" then it's likely
					// the person set their own custom format in the shortcode
					return $date_format;
			}
		}
	}

	public static function full_date( $timestamp, $settings, $include_time = false ) {
		return date_i18n( SBY_Display_Elements_Pro::date_format_setting( $settings['dateformat'], $settings['customdate'] ), $timestamp + sby_get_utc_offset() );
	}

	public static function format_date( $timestamp, $settings = false, $include_time = false ) {
		if ( ! $settings ) {
			global $sby_settings;
			$settings = $sby_settings;
		}

		$now = time();
		$difference = $now - $timestamp;
		$do_not_use_relative = ! $settings['userelative'];

		// future date, is a live stream
		if ( $difference < 0 || $do_not_use_relative ) {
			return SBY_Display_Elements_Pro::full_date( $timestamp, $settings, $include_time );
		} elseif ( $difference < 2 * HOUR_IN_SECONDS ) {
			$unit_text = $difference < 60 ? $settings['minutetext'] : $settings['minutestext'];
			$num_text = round( $difference / 60, 0 );
			return $num_text . ' ' . esc_html( $unit_text ) . ' ' . esc_html( $settings['agotext'] );
		} elseif ( $difference < DAY_IN_SECONDS ) {
			$unit_text = $settings['hourstext'];
			$num_text = round( $difference / HOUR_IN_SECONDS, 0 );
			return $num_text . ' ' . esc_html( $unit_text ) . ' ' . esc_html( $settings['agotext'] );
		} else  {
			return SBY_Display_Elements_Pro::full_date( $timestamp, $settings, $include_time );
		}

	}

	//sby_escaped_live_streaming_time_string
	public static function escaped_live_streaming_time_string( $post, $misc_data = array() ) {
		$live_broadcast_type = SBY_Parse_Pro::get_live_broadcast_content( $post ); // 'none', 'upcoming', 'live', 'completed'
		if ( $live_broadcast_type === 'none' ) {
			return '';
		}

		$scheduled_start_timestamp = SBY_Parse_Pro::get_scheduled_start_timestamp( $post, $misc_data );
		$actual_start_timestamp = SBY_Parse_Pro::get_actual_start_timestamp( $post, $misc_data );
		$actual_end_timestamp = SBY_Parse_Pro::get_actual_end_timestamp( $post, $misc_data );

		global $sby_settings;

		if ( $actual_end_timestamp !== 0
		     || $actual_start_timestamp !== 0
		     || $live_broadcast_type === 'live'
			 || $live_broadcast_type === 'completed' ) {
			return esc_html( $sby_settings['watchnowtext'] );
		}

		if ( $scheduled_start_timestamp === 0 ) {
			return esc_html( $sby_settings['beforedatetext'] ) . ' ' . '<span class="sby_loader sby_hidden" style="background-color: rgb(255, 255, 255);"></span>';
		}

		$now = time();
		$difference = $scheduled_start_timestamp - $now;

		if ( $difference < 0 ) {
			$text = esc_html( $sby_settings['watchnowtext'] );
		} elseif ( $difference < 2 * HOUR_IN_SECONDS ) {
			$unit_text = $difference < 60 ? $sby_settings['minutetext'] : $sby_settings['minutestext'];
			$num_text = round( $difference / 60, 0 );
			$text = esc_html( $sby_settings['beforestreamtimetext'] ) . ' ' . $num_text . ' ' . esc_html( $unit_text );
		} elseif ( $difference < DAY_IN_SECONDS ) {
			$unit_text = $sby_settings['hourstext'];
			$num_text = round( $difference / HOUR_IN_SECONDS, 0 );
			$text = esc_html( $sby_settings['beforestreamtimetext'] ) . ' ' . $num_text . ' ' . esc_html( $unit_text );
		} else  {
			$text = esc_html( $sby_settings['beforedatetext'] ) . ' ' . esc_html( SBY_Display_Elements_Pro::format_date( $scheduled_start_timestamp, $sby_settings, true ) );
		}

		return $text;
	}

	public static function escaped_formatted_count_string( $count, $type ) {
		global $sby_settings;

		$type_text = isset( $sby_settings[ $type . 'text' ] ) ? ' ' . $sby_settings[ $type . 'text' ] : '';

		if ( $count === '' ) {
			return SBY_Display_Elements_Pro::spinner() . $type_text;
		}

		$count = (int)$count;

		if ( $count < 1000 ) {
			$text = esc_html( number_format_i18n( (float)$count ) );
		} elseif ( $count < 1000000 ) {
			$thousands = $count/1000;
			$num_text = round( $thousands, 1 );
			$text = esc_html( $num_text . $sby_settings['thousandstext'] );
		} else {
			$millions = $count/1000000;
			$num_text = round( $millions, 1 );
			$text = esc_html( $num_text . $sby_settings['millionstext'] );
		}


		return $text . $type_text;
	}

	/**
	 * Not used with the core feed but can be used for customizations.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_feed_type_class( $settings ) {
		return 'sby_feed_type_' . esc_attr( $settings['type'] );
	}

	/**
	 * Boxed style headers have more color options - primary color
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_boxed_header_styles( $settings ) {
		if ( ! empty( $settings['headerprimarycolor'] ) ) {
			return 'style="background: rgb(' . esc_attr( sby_hextorgb( $settings['headerprimarycolor'] ) ). ');"';
		}
		return '';
	}

	/**
	 * Boxed style headers have more color options - secondary color
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_header_bar_styles( $settings ) {
		if ( ! empty( $settings['headersecondarycolor'] ) ) {
			return 'style="background: rgb(' . esc_attr( sby_hextorgb( $settings['headersecondarycolor'] ) ). ');"';
		}
		return '';
	}

	/**
	 * For text, likes counts, post counts
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_header_info_styles( $settings ) {
		if ( ! empty( $settings['headerprimarycolor'] ) ) {
			return 'style="color: rgb(' . esc_attr( sby_hextorgb( $settings['headerprimarycolor'] ) ). ');"';
		}
		return '';
	}

	/**
	 * Layout for mobile feeds altered with the class added here based on settings.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_mobilecols_class( $settings ) {
		$disable_mobile = $settings['disablemobile'];
		( $disable_mobile == 'on' || $disable_mobile == 'true' || $disable_mobile == true ) ? $disable_mobile = true : $disable_mobile = false;
		if( $settings[ 'disablemobile' ] === 'false' ) $disable_mobile = '';

		if ( $disable_mobile !== ' sby_disable_mobile' && $settings['colsmobile'] !== 'same' ) {
			$colsmobile = (int)( $settings['colsmobile'] ) > 0 ? (int)$settings['colsmobile'] : 'auto';
			return ' sby_mob_col_' . $colsmobile;
		} else {
			$colsmobile = (int)( $settings['cols'] ) > 0 ? (int)$settings['cols'] : 4;
			return ' sby_disable_mobile sby_mob_col_' . (int)$settings['cols'];
		}
	}

	public static function spinner() {
		return '<span class="sby_loader" style="background-color: rgb(255, 255, 255);"></span>';
	}
}