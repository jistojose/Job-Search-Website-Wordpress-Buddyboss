<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SBY_API_Connect
{
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var object
	 */
	protected $response;

	public function __construct( $connected_account_or_url, $endpoint = '', $params = array() ) {
		if ( is_array( $connected_account_or_url ) && isset( $connected_account_or_url['access_token'] ) ) {
			$this->set_url( $connected_account_or_url, $endpoint, $params );
		} elseif ( is_array( $connected_account_or_url ) ) {
			$this->set_url( $connected_account_or_url, $endpoint, $params );
		} elseif ( strpos( $connected_account_or_url, 'https' ) !== false ) {
			$this->url = $connected_account_or_url;
		} else {
			$this->url = '';
		}
	}

	public function get_data() {
		if (!is_wp_error($this->response) && !empty($this->response['data'])) {
			return $this->response['data'];
		} else {
			return $this->response;
		}
	}

	public function get_wp_error() {
		if ( $this->is_wp_error() ) {
			return array( 'response' => $this->response, 'url' => $this->url );
		} else {
			return false;
		}
	}

	public function get_next_page( $params = false ) {
		if ( ! empty( $this->response['nextPageToken'] ) ) {
			return $this->response['nextPageToken'];
		} else {
			return '';
		}
	}

	public function set_url_from_args( $url ) {
		$this->url = $url;
	}

	public function get_url() {
		return $this->url;
	}

	public function is_wp_error() {
		return is_wp_error( $this->response );
	}


	public function is_youtube_error() {
		return (is_wp_error( $this->response ) || isset( $this->response['error'] ));
	}

	public function connect() {
		$args = array(
			'timeout' => 60,
			'sslverify' => false
		);
		$response = wp_remote_get( esc_url_raw( $this->url ), $args );

		if ( ! is_wp_error( $response ) ) {
			// certain ways of representing the html for double quotes causes errors so replaced here.
			$response = json_decode( str_replace( '%22', '&rdquo;', $response['body'] ), true );
		}

		$this->response = $response;
	}

	public static function handle_youtube_error( $response, $error_connected_account, $request_type = '' ) {
		//
		if ( isset( $response['error'] ) ) {
			if ( isset( $response['error']['errors'][0]['reason'] ) && $response['error']['errors'][0]['message'] === 'Invalid Credentials' ) {
				$error_message = '<p><b>' . __( 'Reconnect to YouTube to show this feed.', 'custom-facebook-feed' ) . '</b></p>';
				$error_message .= '<p>' . __( 'To create a new feed, first connect to YouTube using the "Connect to YouTube to Create a Feed" button on the settings page and connect any account.', SBY_TEXT_DOMAIN ) . '</p>';

				if ( current_user_can( 'manage_youtube_feed_options' ) ) {
					$error_message .= '<a href="' . admin_url( 'admin.php?page=youtube-feed' ) . '" target="blank" rel="noopener nofollow">' . __( 'Reconnect in the YouTube Feed Settings Area' ) . '</a>';
				}
				global $sby_posts_manager;

				$sby_posts_manager->add_frontend_error( 'accesstoken', $error_message );
				$sby_posts_manager->add_error( 'accesstoken', array( 'Trying to connect a new account', $error_message ) );

				return false;
			} elseif ( isset( $response['error']['errors'][0]['reason'] ) ) {
				$error = $response['error']['errors'][0]['message'];

				$error_message = '<p><b>'. sprintf( __( 'Error %s: %s.', 'instagram-feed' ), $response['error']['code'], $error )  .'</b></p>';
				$error_message .= '<p>Domain code: ' . $response['error']['errors'][0]['domain'];
				$error_message .= '<br>Reason code: ' . $response['error']['errors'][0]['reason'];
				if ( current_user_can( 'manage_youtube_feed_options' ) ) {
					if ( isset( $response['error']['errors'][0]['extendedHelp'] ) ) {
						$error_message .= '<br>Extended Help Link: ' . $response['error']['errors'][0]['extendedHelp'];
					}
					$error_message .= '</p>';

					$error_message .= '<a href="https://smashballoon.com/youtube-feed/docs/errors/" target="blank" rel="noopener nofollow">' . __( 'Directions on how to resolve this issue' ) . '</a>';
				} else {
					$error_message .= '</p>';
				}

				global $sby_posts_manager;

				$sby_posts_manager->add_frontend_error( 'api', $error_message );
				$sby_posts_manager->add_error( 'api', array( 'Error connecting', $error_message ) );

				$sby_posts_manager->add_api_request_delay( 300 );
			}
		}
	}

	public static function handle_wp_remote_get_error( $response ) {
		$message = sprintf( __( 'Error connecting to %s.', SBY_TEXT_DOMAIN ), $response['url'] ). ' ';
		if ( isset( $response['response'] ) && isset( $response['response']->errors ) ) {
			foreach ( $response['response']->errors as $key => $item ) {
				$message .= ' '.$key . ' - ' . $item[0] . ' |';
			}
		}

		global $sby_posts_manager;

		$sby_posts_manager->add_api_request_delay( 300 );

		$sby_posts_manager->add_error( 'connection', array( 'Error connecting', $message ) );
	}

	protected function set_url( $connected_account, $endpoint_slug, $params ) {
		$num = ! empty( $params['num'] ) && isset( $connected_account['api_key'] ) ? (int)$params['num'] : 50;

		if ( $endpoint_slug === 'tokeninfo' ) {
			$url = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' . $connected_account['access_token'];
		} elseif ( $endpoint_slug === 'channels' ) {
			$channel_param = 'mine=true';
			if ( isset( $params['channel_name'] ) ) {
				$channel_param = 'forUsername=' . $params['channel_name'];
			} elseif ( isset( $params['channel_id'] ) ) {
				$channel_param = 'id=' . $params['channel_id'];
			}

			$access_credentials = isset( $connected_account['api_key'] ) ? 'key=' . $connected_account['api_key'] : 'access_token=' . $connected_account['access_token'];

			$url = 'https://www.googleapis.com/youtube/v3/channels?part=id,snippet,contentDetails&'.$channel_param.'&' . $access_credentials;
		} elseif ( $endpoint_slug === 'playlistItems' ) {
			$access_credentials = isset( $connected_account['api_key'] ) ? 'key=' . $connected_account['api_key'] : 'access_token=' . $connected_account['access_token'];
			$next_page = '';
			if ( isset( $params['nextPageToken'] ) ) {
				$next_page = '&pageToken=' . $params['nextPageToken'];
			}

			$url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=id,snippet&maxResults='.$num.'&playlistId='.$params['playlist_id'].'&' . $access_credentials . $next_page;
		} else {
			$channel_param = 'mine=true';
			if ( isset( $params['username'] ) ) {
				$channel_param = 'forUsername=' . $params['username'];
			} elseif ( isset( $params['channel_id'] ) ) {
				$channel_param = 'id=' . $params['channel_id'];
			}
			$access_credentials = isset( $connected_account['api_key'] ) ? 'key=' . $connected_account['api_key'] : 'access_token=' . $connected_account['access_token'];

			$url = 'https://www.googleapis.com/youtube/v3/channels?part=id,snippet&'.$channel_param.'&' . $access_credentials;
		}

		$this->set_url_from_args( $url );
	}

	public static function refresh_token( $client_id, $refresh_token, $client_secret ) {
		$response = wp_remote_post( 'https://www.googleapis.com/oauth2/v4/token/?client_id=' . $client_id . '&client_secret=' . $client_secret . '&refresh_token='. $refresh_token . '&grant_type=refresh_token' );

		if ( $response['response']['code'] === 200 ) {
			$return = json_decode( $response['body'], true );
		} else {
			$return = array();
		}

		return $return;
	}

}