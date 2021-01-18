<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SBY_Settings {
	/**
	 * @var array
	 */
	protected $atts;

	/**
	 * @var array
	 */
	protected $db;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array
	 */
	protected $feed_type_and_terms;

	/**
	 * @var array
	 */
	protected $connected_accounts;

	/**
	 * @var array
	 */
	protected $connected_accounts_in_feed;

	/**
	 * @var string
	 */
	protected $transient_name;

	/**
	 * SBY_Settings constructor.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @param array $atts shortcode settings
	 * @param array $db settings from the wp_options table
	 */
	public function __construct( $atts, $db ) {
		$atts = is_array( $atts ) ? $atts : array();

		// convert string 'false' and 'true' to booleans
		foreach ( $atts as $key => $value ) {
			if ( $value === 'false' ) {
				$atts[ $key ] = false;
			} elseif ( $value === 'true' ) {
				$atts[ $key ] = true;
			}
		}

		$this->atts = $atts;
		$this->db   = $db;

		$this->connected_accounts = isset( $db['connected_accounts'] ) ? $db['connected_accounts'] : array();

		if ( ! empty( $this->db['api_key'] ) ) {
			$this->connected_accounts = array(
				'own' => array(
					'access_token' => '',
					'refresh_token' => '',
					'channel_id' => '',
					'username' => '',
					'is_valid' => true,
					'last_checked' => '',
					'profile_picture' => '',
					'privacy' => '',
					'expires' => '2574196927',
					'api_key' => $this->db['api_key']
				)
			);
		}

		$this->settings = wp_parse_args( $atts, $db );

		if ( empty( $this->connected_accounts ) ) {
			$this->settings['showheader'] = false;
			$this->connected_accounts = array( 'rss_only' => true );
		}

		$this->settings['nummobile'] = $this->settings['num'];
		$this->settings['minnum'] = $this->settings['num'];

		$this->after_settings_set();
	}

	protected function after_settings_set() {

	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * The plugin will output settings on the frontend for debugging purposes.
	 * Safe settings to display are added here.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public static function get_public_db_settings_keys() {
		$public = array(
		);

		return $public;
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	public function get_connected_accounts() {
		return $this->connected_accounts;
	}

	/**
	 * @return array|bool
	 *
	 * @since 1.0
	 */
	public function get_connected_accounts_in_feed() {
		if ( isset( $this->connected_accounts_in_feed ) ) {
			return $this->connected_accounts_in_feed;
		} else {
			return false;
		}
	}

	/**
	 * @return bool|string
	 *
	 * @since 1.0
	 */
	public function get_transient_name() {
		if ( isset( $this->transient_name ) ) {
			return $this->transient_name;
		} else {
			return false;
		}
	}

	/**
	 * Uses the feed types and terms as well as as some
	 * settings to create a semi-unique feed id used for
	 * caching and other features.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @param string $transient_name
	 *
	 * @since 1.0
	 */
	public function set_transient_name( $transient_name = '' ) {

		if ( ! empty( $transient_name ) ) {
			$this->transient_name = $transient_name;
		} elseif ( ! empty( $this->settings['feedid'] ) ) {
			$this->transient_name = 'sby_' . $this->settings['feedid'];
		} else {
			$feed_type_and_terms = $this->feed_type_and_terms;

			$sby_transient_name = 'sby_';

			if ( isset( $feed_type_and_terms['channels'] ) ) {
				foreach ( $feed_type_and_terms['channels'] as $term_and_params ) {
					$channel = $term_and_params['term'];
					$sby_transient_name .= $channel;
				}
			}

			$num = $this->settings['num'];

			$num_length = strlen( $num ) + 1;

			//Add both parts of the caching string together and make sure it doesn't exceed 45
			$sby_transient_name = substr( $sby_transient_name, 0, 45 - $num_length );

			$sby_transient_name .= '#' . $num;

			$this->transient_name = $sby_transient_name;
		}

	}

	/**
	 * @return array|bool
	 *
	 * @since 1.0
	 */
	public function get_feed_type_and_terms() {
		if ( isset( $this->feed_type_and_terms ) ) {
			return $this->feed_type_and_terms;
		} else {
			return false;
		}
	}

	/**
	 * Based on the settings related to retrieving post data from the API,
	 * this setting is used to make sure all endpoints needed for the feed are
	 * connected and stored for easily looping through when adding posts
	 *
	 * Overwritten in the Pro version.
	 *
	 * @since 1.0
	 */
	public function set_feed_type_and_terms() {
		//global $sby_posts_manager;

		$connected_accounts_in_feed = array();
		$feed_type_and_terms = array(
			'channels' => array()
		);

		if ( ! empty( $this->settings['id'] ) ) {
			$channel_array = is_array( $this->settings['id'] ) ? $this->settings['id'] : explode( ',', str_replace( ' ', '',  $this->settings['id'] ) );
			foreach ( $channel_array as $channel ) {
				if ( isset( $this->connected_accounts[ $channel ] ) ) {
					$feed_type_and_terms['channels'][] = array(
						'term' => $this->connected_accounts[ $channel ]['channel_id'],
						'params' => array(
							'channel_id' => $this->connected_accounts[ $channel ]['channel_id']
						)
					);
					$connected_accounts_in_feed[ $this->connected_accounts[ $channel ]['channel_id'] ] = $this->connected_accounts[ $channel ];
				}
			}

			if ( empty( $connected_accounts_in_feed ) ) {
				$an_account = array();
				foreach ( $this->connected_accounts as $account ) {
					if ( empty( $an_account ) ) {
						$an_account = $account;
					}
				}

				foreach ( $channel_array as $channel ) {
					$feed_type_and_terms['channels'][] = array(
						'term' => $channel,
						'params' => array(
							'channel_id' => $channel
						)
					);
					$connected_accounts_in_feed[ $channel ] = $an_account;
				}
			}

		} elseif ( ! empty( $this->settings['channel'] ) ) {
			$channel_array = is_array( $this->settings['channel'] ) ? $this->settings['channel'] : explode( ',', str_replace( ' ', '',  $this->settings['channel'] ) );

			$an_account = array();
			foreach ( $this->connected_accounts as $account ) {
				if ( empty( $an_account ) ) {
					$an_account = $account;
				}
			}

			foreach ( $channel_array as $channel ) {
				if ( strpos( $channel, 'UC' ) !== 0 ) {
					$channel_id = sby_get_channel_id_from_channel_name( $channel );
					if ( $channel_id ) {
						$feed_type_and_terms['channels'][] = array(
							'term' => $channel_id,
							'params' => array(
								'channel_id' => $channel_id
							)
						);
						$connected_accounts_in_feed[ $channel_id ] = $an_account;
					} else {
						$feed_type_and_terms['channels'][] = array(
							'term' => $channel,
							'params' => array(
								'channel_name' => $channel
							)
						);
						$connected_accounts_in_feed[ $channel ] = $an_account;
					}

				} else {
					$feed_type_and_terms['channels'][] = array(
						'term' => $channel,
						'params' => array(
							'channel_id' => $channel
						)
					);
					$connected_accounts_in_feed[ $channel ] = $an_account;
				}
			}

		} else {
			foreach ( $this->connected_accounts as $connected_account ) {
				if ( empty( $feed_type_and_terms['channels'] ) ) {
					$feed_type_and_terms['channels'][] = array(
						'term' => $connected_account['channel_id'],
						'params' => array(
							'channel_id' => $connected_account['channel_id']
						)
					);
					$connected_accounts_in_feed[ $connected_account['channel_id'] ] = $connected_account;
				}

			}
		}

		$this->connected_accounts_in_feed = $connected_accounts_in_feed;
		$this->feed_type_and_terms = $feed_type_and_terms;
	}

	/**
	 * @return float|int
	 *
	 * @since 1.0
	 */
	public function get_cache_time_in_seconds() {
		if ( $this->db['caching_type'] === 'background' ) {
			return SBY_CRON_UPDATE_CACHE_TIME;
		} else {
			//If the caching time doesn't exist in the database then set it to be 1 hour
			$cache_time = isset( $this->settings['cache_time'] ) ? (int)$this->settings['cache_time'] : 1;
			$cache_time_unit = isset( $this->settings['cache_time_unit'] ) ? $this->settings['cache_time_unit'] : 'hours';

			//Calculate the cache time in seconds
			if ( $cache_time_unit == 'minutes' ) $cache_time_unit = 60;
			if ( $cache_time_unit == 'hours' ) $cache_time_unit = 60*60;
			if ( $cache_time_unit == 'days' ) $cache_time_unit = 60*60*24;

			$cache_time = max( 900, $cache_time * $cache_time_unit );

			return $cache_time;
		}
	}
}