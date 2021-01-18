<?php
/**
 * Class to add reviews shortcode.
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 * @package  BuddyPress_Member_Reviews
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BUPR_Shortcodes' ) ) {

	/**
	 * Class to serve AJAX Calls.
	 *
	 * @author   Wbcom Designs
	 * @since    1.0.0
	 */
	class BUPR_Shortcodes {

		/**
		 * Constructor.
		 *
		 * @since    1.0.0
		 * @author   Wbcom Designs
		 */
		public function __construct() {
			add_shortcode( 'add_profile_review_form', array( $this, 'bupr_shortcode_review_form' ) );
			add_shortcode( 'bupr_display_top_members', array( $this, 'bupr_display_top_members_display' ) );
		}

		/**
		 * Display top members on front-end.
		 *
		 * @since    1.0.9
		 * @author   Wbcom Designs
		 */
		public function bupr_display_top_members_display( $atts ) {
			global $wpdb;
			global $bupr;
			$atts            = shortcode_atts(
				array(
					'title'        => '',
					'total_member' => 5,
					'type'         => 'top rated',
					'avatar'       => 'show',
				),
				$atts
			);
			$bupr_type       = 'integer';
			$bupr_avg_rating = 0;
			$user_id         = get_current_user_id();
			// Our variables from the widget settings.
			$bupr_title  = $atts['title'];
			$memberLimit = $atts['total_member'];
			$topMember   = $atts['type'];
			$avatar      = $atts['avatar'];

			$bupr_users              = get_users();
			$bupr_max_review         = array();
			$bupr_star_rating        = array();
			$bupr_member_count       = 0;
			$bupr_total_review_count = '';
			foreach ( $bupr_users as $user ) {
				$id              = $user->data->ID;
				$bupr_type       = 'integer';
				$bupr_avg_rating = 0;
				/* Gather all the members reviews */
				$bupr_args = array(
					'post_type'      => 'review',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'category'       => 'bp-member',
					'meta_query'     => array(
						array(
							'key'     => 'linked_bp_member',
							'value'   => $id,
							'compare' => '=',
						),
					),
				);

				$reviews             = get_posts( $bupr_args );
				$bupr_admin_settings = get_option( 'bupr_admin_settings' );
				if ( ! empty( $bupr_admin_settings ) ) {
					$bupr_review_rating_fields = $bupr_admin_settings['profile_rating_fields'];
				}
				$bupr_total_rating       = $rate_counter             = 0;
				$bupr_reviews_count      = count( $reviews );
				$bupr_total_review_count = '';
				if ( $bupr_reviews_count != 0 ) {
					foreach ( $reviews as $review ) {
						$rate                = 0;
						$reviews_field_count = 0;
						$review_ratings      = get_post_meta( $review->ID, 'profile_star_rating', false );
						if ( ! empty( $review_ratings[0] ) ) {
							// $reviews_field_count  = count( $bupr_review_rating_fields );
							if ( ! empty( $bupr_review_rating_fields ) && ! empty( $review_ratings[0] ) ) :
								foreach ( $review_ratings[0] as $field => $value ) {
									if ( array_key_exists( $field, $bupr_review_rating_fields ) ) {
										$rate += $value;
										$reviews_field_count++;
									}
								}
								if ( $reviews_field_count != 0 ) {
									$bupr_total_rating += (int) $rate / $reviews_field_count;
									$bupr_total_review_count ++;
									$rate_counter++;
								}
							endif;
						}
					}

					if ( $bupr_total_review_count != 0 ) {
						$bupr_avg_rating = $bupr_total_rating / $bupr_total_review_count;
						$bupr_type       = gettype( $bupr_avg_rating );
					}

					$bupr_stars_on = $stars_off        = $stars_half       = '';
					if ( $bupr_total_review_count != 0 ) {
						$bupr_avg_rating = $bupr_total_rating / $bupr_total_review_count;
						$bupr_type       = gettype( $bupr_avg_rating );
					}

					$bupr_max_review[ $user->data->ID ]  = array(
						'user_id'      => $user->data->ID,
						'max_review'   => $bupr_reviews_count,
						'avg_rating'   => $bupr_avg_rating,
						'member_name'  => $user->data->user_nicename,
						'avr_type'     => $bupr_type,
						'rate_counter' => $rate_counter,
					);
					$bupr_star_rating[ $user->data->ID ] = array(
						'user_id'      => $user->data->ID,
						'max_review'   => $bupr_reviews_count,
						'avg_rating'   => $bupr_avg_rating,
						'member_name'  => $user->data->user_nicename,
						'avr_type'     => $bupr_type,
						'rate_counter' => $rate_counter,
					);
					$bupr_member_count++;
				}
			}

			$bupr_members_ratings_data = array();
			if ( $topMember === 'top rated' ) {
				usort( $bupr_star_rating, array( $this, 'bupr_get_sort_max_stars' ) );
				$bupr_members_ratings_data = $bupr_star_rating;
			} elseif ( $topMember === 'top reviewed' ) {
				usort( $bupr_max_review, array( $this, 'bupr_get_sort_max_review' ) );
				$bupr_members_ratings_data = $bupr_max_review;
			}
			echo '<div class="bupr-shortcode-top-members-contents bupr_members_review_setting ">';
			?>
			<input type="hidden" value="<?php echo $bupr['rating_color']; ?>" class="bupr-display-rating-color">
			<?php
			$bupr_user_count = 0;
			echo '<h2>' . $bupr_title . '</h2>';
			echo '<ul class="bupr-member-main">';
			if ( $bupr_member_count != 0 ) {
				foreach ( $bupr_members_ratings_data as $buprKey => $buprValue ) {
					if ( $bupr_user_count == $memberLimit ) {
						break;
					} else {

						if ( $avatar == 'show' ) {
							echo '<li class="bupr-members"><div class="bupr-img-widget">';
							echo get_avatar( $buprValue['user_id'], 50 );
							echo '</div>';
							echo '<div class="bupr-content-widget">';
						} else {
							echo '<li class="bupr-members bupr-hide"><div class="bupr-content-widget">';
						}
						$members_profile = bp_core_get_userlink( $buprValue['user_id'] );
						echo '<div class="bupr-member-title">';
						echo $members_profile;
						echo '</div>';

						$bupr_avg_rating    = $buprValue['avg_rating'];
						$bupr_reviews_count = $buprValue['max_review'];
						$stars_on           = $stars_off            = $stars_half           = '';
						$remaining          = $bupr_avg_rating - (int) $bupr_avg_rating;
						if ( $remaining > 0 ) {
							$stars_on        = intval( $bupr_avg_rating );
							$stars_half      = 1;
							$bupr_half_squar = 1;
							$stars_off       = 5 - ( $stars_on + $stars_half );
						} else {
							$stars_on   = $bupr_avg_rating;
							$stars_off  = 5 - $bupr_avg_rating;
							$stars_half = 0;
						}

						echo '<div class="bupr-member-rating">';
						if ( $bupr_avg_rating > 0 ) {
							?>

							<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
								<span itemprop="ratingValue"  content="<?php echo $bupr_avg_rating; ?>"></span>
								<span itemprop="bestRating"   content="5"></span>
								<span itemprop="ratingCount"  content="<?php echo $buprValue['rate_counter']; ?>"></span>
								<span itemprop="reviewCount"  content="<?php echo $bupr_reviews_count; ?>"></span>
								<span itemprop="itemReviewed" content="Person"></span>
								<span itemprop="name" content="<?php echo bp_core_get_username( $buprValue['user_id'] ); ?>"></span>
								<span itemprop="url" content="<?php echo bp_core_get_userlink( $buprValue['user_id'], false, true ); ?>"></span>
							</div>
							<?php
						}

						for ( $i = 1; $i <= $stars_on; $i++ ) {
							?>
							<span class="fas fa-star bupr-star-rate"></span>
							<?php
						}

						for ( $i = 1; $i <= $stars_half; $i++ ) {
							?>
							<span class="fas fa-star-half-alt bupr-star-rate"></span>
							<?php
						}

						for ( $i = 1; $i <= $stars_off; $i++ ) {
							?>
							<span class="far fa-star bupr-star-rate"></span>
							<?php
						}
						echo '</div>';

						$bupr_avg_rating = round( $bupr_avg_rating, 2 );
						echo '<span class="bupr-meta">';
						echo sprintf( esc_html__( 'Rating : ( %1$s )', 'bp-member-reviews' ), esc_html( $bupr_avg_rating ) );
						echo '</span><span class="bupr-meta">';
						echo sprintf( esc_html__( 'Total %1$s : %2$s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ), esc_attr( $bupr_reviews_count ) );
						echo '</span></div></li>';
					}

					$bupr_user_count++;
				}
			} else {
				echo '<p>';
				esc_html_e( 'No member has been reviewed yet.', 'bp-member-reviews' );
				echo '</p>';
			}
			echo '</div>';
		}

		/**
		 * Sort member list according to max review.
		 *
		 * @since    1.0.9
		 * @author   Wbcom Designs
		 */
		public function bupr_get_sort_max_review( $bupr_rating1, $bupr_rating2 ) {
			return strcmp( $bupr_rating2['max_review'], $bupr_rating1['max_review'] );
		}

		/**
		 * Sort member list according to max star.
		 *
		 * @since    1.0.9
		 * @author   Wbcom Designs
		 */
		public function bupr_get_sort_max_stars( $bupr_rating1, $bupr_rating2 ) {
			return strcmp( $bupr_rating2['avg_rating'], $bupr_rating1['avg_rating'] );
		}

		/**
		 * Display add review form on front-end.
		 *
		 * @since    1.0.0
		 * @author   Wbcom Designs
		 */
		public function bupr_display_review_form() {
			global $bp;
			global $bupr;
			$login_user       = get_current_user_id();
			$bupr_spinner_src = includes_url() . 'images/spinner.gif';

			$bupr_review_succes = false;
			$bupr_flag          = false;
			$bupr_member        = array();
			foreach ( get_users() as $user ) {
				if ( $user->ID !== get_current_user_id() ) {
					$bupr_member[] = array(
						'member_id'   => $user->ID,
						'member_name' => $user->data->display_name,
					);
				}
			}

			$member_args = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => array(
					'draft',
					'publish',
				),
				'author'         => $login_user,
				'category'       => 'bp-member',
				'meta_query'     => array(
					array(
						'key'     => 'linked_bp_member',
						'value'   => bp_displayed_user_id(),
						'compare' => '=',
					),
				),
			);

			$reviews_args = new WP_Query( $member_args );

			if ( ! bp_is_members_component() && ! bp_is_user() ) {
				$bp_template_option = bp_get_option( '_bp_theme_package_id' );
				if ( 'nouveau' == $bp_template_option ) {
					?>
					<div id="message" class="success success_review_msg bp-feedback bp-messages bp-template-notice">
						<span class="bp-icon" aria-hidden="true"></span>
				<?php } else { ?>
						<div id="message" class="success success_review_msg">
					<?php } ?>
						<p>
						</p>
					</div>
				<?php
			}

			if ( 0 === bp_displayed_user_id() ) {
				$this->bupr_review_form( $login_user, $bupr_spinner_src, $bupr_review_succes, $bupr_flag, $bupr_member );
			} else {
				if ( 'no' == $bupr['multi_reviews'] ) {
					$user_post_count = $reviews_args->post_count;
				} else {
					$user_post_count = 0;
				}
				if ( $user_post_count == 0 ) {
					$this->bupr_review_form( $login_user, $bupr_spinner_src, $bupr_review_succes, $bupr_flag, $bupr_member );
				} else {
					$bp_template_option = bp_get_option( '_bp_theme_package_id' );
					if ( 'nouveau' == $bp_template_option ) {
						?>
							<div id="message" class="error bp-feedback bp-messages bp-template-notice">
								<span class="bp-icon" aria-hidden="true"></span>
					<?php } else { ?>
								<div id="message" class="error">
							<?php } ?>
								<p><?php echo sprintf( esc_html__( 'You already posted a %s for this member.', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?> </p>
							</div>
					<?php
				}
			}
		}

		/**
		 * Bupr review form.
		 *
		 * @since    1.0.0
		 * @param    string $login_user             Login  User.
		 * @param    string $bupr_spinner_src       Spinner  User.
		 * @param    string $bupr_review_succes     Review Success.
		 * @param    int    $bupr_flag              Flag.
		 * @param    array  $bupr_member            Member array
		 * @author   Wbcom Designs
		 */
		public function bupr_review_form( $login_user, $bupr_spinner_src, $bupr_review_succes, $bupr_flag, $bupr_member ) {
			global $bupr;
			$flag = false;

			if ( 'yes' == $bupr['anonymous_reviews'] ) {
				$flag = true;
			}
			$bp_template_option = bp_get_option( '_bp_theme_package_id' );
			if ( 'nouveau' == $bp_template_option ) {
				?>
						<div id="message" class="success add_review_msg success_review_msg bp-feedback bp-messages bp-template-notice">
							<span class="bp-icon" aria-hidden="true"></span>
			<?php } else { ?>
							<div id="message" class="success add_review_msg success_review_msg">
						<?php } ?>
							<p>
							</p>
						</div>
						<form action="" method="POST">
							<input type="hidden" value="<?php echo $bupr['rating_color']; ?>" class="bupr-display-rating-color">
							<input type="hidden" id="reviews_pluginurl" value="<?php echo esc_url( BUPR_PLUGIN_URL ); ?>">
							<div class="bp-member-add-form">

								<p>
			<?php echo sprintf( esc_html__( 'Fill in details to submit %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
								</p>

			<?php if ( 0 === bp_displayed_user_id() ) { ?>
									<p>
										<select name="bupr_member_id" id="bupr_member_review_id">
											<option value=""><?php esc_html_e( '--Select--', 'bp-member-reviews' ); ?></option>
				<?php
				if ( ! empty( $bupr_member ) ) {
					foreach ( $bupr_member as $user ) {
						echo '<option value="' . esc_attr( $user['member_id'] ) . '">' . esc_attr( $user['member_name'] ) . '</option>';
					}
				}
				?>
										</select><br/>
										<span class="bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></span>
									</p>
			<?php } ?>
								<input type="hidden" id="bupr_member_review_id" value="<?php echo esc_attr( bp_displayed_user_id() ); ?>">
								<p class="bupr-hide-subject">
									<input name="review-subject" id="review_subject" type="text" placeholder="<?php esc_html_e( 'Review Subject', 'bp-member-reviews' ); ?>" ><br/><span class="bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></span>
								</p>
								<textarea name="review-desc" id="review_desc" placeholder="<?php echo sprintf( esc_html__( '%s Description', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>" rows="4" cols="50"></textarea><br/><span class="bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></span>

			<?php
			if ( $bupr['multi_criteria_allowed'] ) {

				if ( ! empty( $bupr['active_rating_fields'] ) ) {

					$field_counter = 1;
					$flage         = true;

					foreach ( $bupr['active_rating_fields'] as $bupr_rating_fields => $bupr_criteria_setting ) :
						if ( 'yes' == $bupr_criteria_setting ) {
							?>
								<div class="multi-review">
									<div class="bupr-col-4 bupr-criteria-label">
									<?php esc_html_e( html_entity_decode( $bupr_rating_fields ), 'bp-member-reviews' ); ?>
									</div>
									<div class="bupr-col-4 bupr-criteria-content" id="member_review<?php echo esc_attr( $field_counter ); ?>">
										<input type="hidden" id="<?php echo 'clicked' . esc_attr( $field_counter ); ?>" value="<?php echo 'not_clicked'; ?>">
										<input type="hidden" name="member_rated_stars[]" id="member_rated_stars" class="member_rated_stars bupr-star-member-rating" id="<?php echo 'member_rated_stars' . esc_attr( $field_counter ); ?>" value="0">
									<?php	for ( $i = 1; $i <= 5; $i++ ) { ?>
											<span class="far member_stars <?php echo esc_attr( $i ); ?> fa-star bupr-stars bupr-star-rate <?php echo esc_attr( $i ); ?>" id="<?php echo esc_attr( $field_counter ) . esc_attr( $i ); ?>" data-attr="<?php echo esc_attr( $i ); ?>" ></span>
										<?php } ?>
									</div>
									<div class="bupr-col-12 bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></div>
								</div>
								<?php
								$field_counter++;
						}
						endforeach;
					?>
					<input type="hidden" id="member_rating_field_counter" value="<?php echo esc_attr( --$field_counter ); ?>">
					<?php
				}
			}
			?>
			<?php if ( $flag ) { ?>
				<p>
					<label for="bupr_anonymous_review"><input style="width:auto !important" type="checkbox" id="bupr_anonymous_review" value="value"><?php echo sprintf( esc_html__( 'Send %s anonymously.', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?></label>
				</p>
			<?php } ?>
			<p>
				<?php wp_nonce_field( 'save-bp-member-review', 'security-nonce' ); ?>
				<button type="button" class="btn btn-default" id="bupr_save_review" name="submit-review">
					<?php echo sprintf( esc_html__( 'Submit %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
				</button>
				<input type="hidden" value="<?php echo esc_attr( $login_user ); ?>" id="bupr_current_user_id" />
				<img src="<?php echo esc_url( $bupr_spinner_src ); ?>" class="bupr-save-reivew-spinner" />
			</p>
		</div>
	</form>
			<?php
		}

		/**
		 * Create shortcode for review form.
		 *
		 * @since    1.0.0
		 * @author   Wbcom Designs
		 */
		public function bupr_shortcode_review_form() {
			$this->bupr_display_review_form();
		}

	}

	new BUPR_Shortcodes();
}
