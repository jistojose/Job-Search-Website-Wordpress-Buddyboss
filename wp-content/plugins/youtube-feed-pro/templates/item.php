<?php
/**
 * Item Template
 * Adds an image, link, and other data for each post in the feed
 *
 * @version 1.0 by Smash Balloon
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
$context = 'item';
$classes = SBY_Display_Elements::get_item_classes( $settings, $offset );
$post_id = SBY_Parse::get_post_id( $post );
$timestamp = SBY_Parse::get_timestamp( $post );
$video_id = SBY_Parse::get_video_id( $post );
$media_url               = SBY_Display_Elements::get_optimum_media_url( $post, $settings );
$media_full_res          = SBY_Parse::get_media_url( $post );
$media_all_sizes_json    = SBY_Parse::get_media_src_set( $post );
$permalink = SBY_Parse::get_permalink( $post );
$img_alt                 = SBY_Parse::get_caption( $post, __( 'Image for post' ) . ' ' . $post_id );
$items_style_attr = SBY_Display_Elements::get_style_att( 'item', $settings );
$title = SBY_Parse::get_video_title( $post );

// Pro Elements
$caption             = SBY_Parse_Pro::get_caption( $post, '', $misc_data );
//$avatar              = SBY_Parse_Pro::get_item_avatar( $post, $settings['feed_avatars'] );
$avatar              = SBY_Parse_Pro::get_item_avatar( $post, $settings['feed_avatars'] );

$username            = SBY_Parse_Pro::get_channel_title( $post, $misc_data );
$likes_count         = SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_like_count( $post, $misc_data ), 'likes' );
$comments_count      = SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_comment_count( $post, $misc_data ), 'comments' );
$views_count_string      = SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_view_count( $post, $misc_data ), 'views' );

$live_broadcast_type = SBY_Parse_Pro::get_live_broadcast_content( $post ); // 'none', 'upcoming', 'live', 'completed'
$live_streaming_timestamp = SBY_Parse_Pro::get_live_streaming_timestamp( $post, $misc_data );
$live_streaming_time_string = SBY_Display_Elements_Pro::escaped_live_streaming_time_string( $post, $misc_data );
$formatted_date_string      = $live_broadcast_type === 'none' ? SBY_Display_Elements_Pro::format_date( $timestamp, $settings ) : SBY_Display_Elements_Pro::format_date( $live_streaming_timestamp, $settings, true );

//$location_info       = SBY_Parse_Pro::get_location_info( $post ); // array( 'name' => $name, 'id' => $int, 'longitude' => $lon_int , 'lattitude' => $lat_int )
//$lightbox_media_atts = SBY_Parse_Pro::get_lightbox_media_atts( $post ); // array( 'video' => $url, 'carousel' => $json )
$sby_link_classes    = SBY_Display_Elements_Pro::get_sby_link_classes( $settings ); // // ' sby_disable_lightbox'

// Pro Styles
$link_styles                = SBY_Display_Elements_Pro::get_sby_link_styles( $settings ); // style="background: rgba(30,115,190,0.85)" already escaped
$hover_styles               = SBY_Display_Elements_Pro::get_hover_styles( $settings ); // style="color: rgba(153,231,255,1)" already escaped
$sby_info_styles            = SBY_Display_Elements_Pro::get_sby_info_styles( $settings ); // style="font-size: 13px;" already escaped
$sby_meta_color_styles      = SBY_Display_Elements_Pro::get_sby_meta_color_styles( $settings ); // style="font-size: 13px;" already escaped
$sby_meta_size_color_styles = SBY_Display_Elements_Pro::get_sby_meta_size_color_styles( $settings ); // style="font-size: 13px;color: rgba(153,231,255,1)" already escaped

$additional_atts = apply_filters( 'sby_item_additional_data_atts', array(), $post, $misc_data );
$additional_atts_string = SBY_Display_Elements::escaped_data_att_string( $additional_atts );
?>
<div class="sby_item <?php echo esc_attr( $classes ); ?>" id="sby_<?php echo esc_html( $post_id ); ?>" data-date="<?php echo esc_html( $timestamp ); ?>" data-video-id="<?php echo esc_attr( $video_id ); ?>" data-video-title="<?php echo esc_attr( $title ); ?>"<?php echo $items_style_attr; ?> data-live-date="<?php echo esc_attr( $live_streaming_timestamp ); ?>"<?php echo $additional_atts_string; ?>>
    <div class="sby_inner_item">
        <div class="sby_video_thumbnail_wrap sby_item_video_thumbnail_wrap">
            <a class="sby_video_thumbnail sby_item_video_thumbnail" href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener" data-full-res="<?php echo esc_url( $media_full_res ); ?>" data-img-src-set="<?php echo esc_attr( wp_json_encode( $media_all_sizes_json ) ); ?>" data-video-id="<?php echo esc_attr( $video_id ); ?>"  data-user="<?php echo esc_attr( $username ); ?>" data-url="<?php echo esc_attr( $permalink ); ?>" data-avatar="<?php echo esc_attr( $avatar ); ?>" data-title="<?php echo sby_esc_attr_with_br( $caption ); ?>" >
                <img src="<?php echo esc_url( $media_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>">

                <div class="sby_thumbnail_hover sby_item_video_thumbnail_hover">
                    <div class="sby_thumbnail_hover_inner">
                        <?php if ( SBY_Display_Elements_Pro::should_show_element( 'title', $context . '-hover', $settings ) ) : ?>
                        <span class="sby_video_title"><?php echo esc_html( $title ); ?></span>
                        <?php endif; ?>

                        <?php if ( $live_broadcast_type !== 'none'
                                    && SBY_Display_Elements_Pro::should_show_element( 'countdown', $context . '-hover', $settings ) ) : ?>
                        <p class="sby_ls_message_wrap"><span class="sby_ls_message"><?php echo $live_streaming_time_string; ?></span></p>
                        <?php endif; ?>

                        <?php if ( SBY_Display_Elements_Pro::should_show_element( 'meta', $context . '-hover', $settings ) ) : ?>
                        <p class="sby_meta" <?php echo $sby_meta_color_styles; ?>>

                            <?php if ( SBY_Display_Elements_Pro::should_show_element( 'user', $context . '-hover', $settings ) ) : ?>
                            <span class="sby_username"><?php echo esc_html( $username ); ?></span>
                            <?php endif; ?>
                            <?php if ( SBY_Display_Elements_Pro::should_show_element( 'views', $context . '-hover', $settings ) ) : ?>
                            <span class="sby_view_count"><?php echo $views_count_string; ?></span>
                            <?php endif; ?>
                            <?php if ( SBY_Display_Elements_Pro::should_show_element( 'date', $context . '-hover', $settings ) ) : ?>
                            <span class="sby_date sby_live_broadcast_type_<?php echo esc_attr( $live_broadcast_type ); ?>"><?php echo $formatted_date_string; ?></span>
                            <?php endif; ?>

                        </p>
                        <?php endif; ?>

                        <?php if ( SBY_Display_Elements_Pro::should_show_element( 'description', $context . '-hover', $settings ) ) : ?>
                            <p class="sby_caption" <?php echo $sby_info_styles; ?>><?php echo sby_esc_html_with_br( $caption ); ?></p>
                        <?php endif; ?>

                        <?php if ( SBY_Display_Elements_Pro::should_show_element( 'stats', $context . '-hover', $settings ) ) : ?>
                        <p class="sby_stats" <?php echo $sby_meta_color_styles; ?>>
                            <span class="sby_likes" <?php echo $sby_meta_size_color_styles; ?>>
                            <?php echo SBY_Display_Elements_Pro::get_icon( 'likes', $icon_type, $sby_meta_size_color_styles ); ?>
                                <span class="sby_like_count"><?php echo $likes_count; ?></span>
                            </span>
                            <span class="sby_comments" <?php echo $sby_meta_size_color_styles; ?>>
                                <?php echo SBY_Display_Elements_Pro::get_icon( 'comments', $icon_type, $sby_meta_size_color_styles ); ?>
                                <span class="sby_comment_count"><?php echo $comments_count; ?></span>
                            </span>
                        </p>
                        <?php endif; ?>

                    </div>
                </div>

                <span class="sby-screenreader"><?php echo sprintf( __( 'YouTube Video %s', 'feeds-for-youtube' ), $post_id ); ?></span>
                <?php if ( SBY_Display_Elements::should_show_element( 'icon', $context, $settings ) ) : ?>
                    <div class="sby_play_btn">
                        <span class="sby_play_btn_bg"></span>
                    <?php echo SBY_Display_Elements::get_icon( 'play', 'svg' ); ?>
                    </div>
                <?php endif; ?>
                <span class="sby_loader sby_hidden" style="background-color: rgb(255, 255, 255);"></span>
            </a>

            <?php if ( $settings['layout'] === 'list' ) : ?>
                <div class="sby_player_wrap">
                    <div id="sby_player_<?php echo esc_html( $video_id ); ?>"></div>
                </div>
            <?php include sby_get_feed_template_part( 'cta', $settings ); ?>
            <?php endif; ?>

        </div>

        <?php include sby_get_feed_template_part( 'info', $settings ); ?>

        <?php if ( SBY_Display_Elements_Pro::should_show_element( 'description', $context, $settings ) ) : ?>
            <p class="sby_caption_wrap sby_item_caption_wrap">
                <span class="sby_caption" <?php echo $sby_info_styles; ?>><?php echo sby_esc_html_with_br( $caption ); ?></span><span class="sby_expand"> <a href="#"><span class="sby_more">...</span></a></span>
            </p>
        <?php endif; ?>
    </div>
</div>