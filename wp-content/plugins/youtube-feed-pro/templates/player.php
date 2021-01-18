<?php
$post_id = SBY_Parse::get_post_id( $placeholder_post );
$timestamp = SBY_Parse::get_timestamp( $placeholder_post );
$video_id = SBY_Parse::get_video_id( $placeholder_post );
$protocol = is_ssl() ? 'https' : 'http';
$media_url               = SBY_Display_Elements::get_optimum_media_url( $placeholder_post, $settings );
$media_full_res          = SBY_Parse::get_media_url( $placeholder_post );
$media_all_sizes_json    = SBY_Parse::get_media_src_set( $placeholder_post );
$permalink = SBY_Parse::get_permalink( $placeholder_post );
$img_alt                 = SBY_Parse::get_caption( $placeholder_post, __( 'Image for post' ) . ' ' . $post_id );
$player_outer_wrap_style_attr = SBY_Display_Elements::get_style_att( 'player_outer_wrap', $settings );
$title = SBY_Parse::get_video_title( $placeholder_post );

// Pro Elements
$caption             = SBY_Parse_Pro::get_caption( $placeholder_post, '', $misc_data );
//$avatar              = SBY_Parse_Pro::get_item_avatar( $post, $settings['feed_avatars'] );
$avatar              = SBY_Parse_Pro::get_item_avatar( $placeholder_post, $settings['feed_avatars'] );

$username            = SBY_Parse_Pro::get_channel_title( $placeholder_post, $misc_data );
$likes_count         = SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_like_count( $placeholder_post, $misc_data ), 'likes' );
$comments_count      = SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_comment_count( $placeholder_post, $misc_data ), 'comments' );
$views_count_string      = SBY_Display_Elements_Pro::escaped_formatted_count_string( SBY_Parse_Pro::get_view_count( $placeholder_post, $misc_data ), 'views' );

$live_broadcast_type = SBY_Parse_Pro::get_live_broadcast_content( $placeholder_post ); // 'none', 'upcoming', 'live', 'completed'
$live_streaming_timestamp = SBY_Parse_Pro::get_live_streaming_timestamp( $placeholder_post, $misc_data );
$live_streaming_time_string = SBY_Display_Elements_Pro::escaped_live_streaming_time_string( $placeholder_post, $misc_data );
$formatted_date_string      = $live_broadcast_type === 'none' ? SBY_Display_Elements_Pro::format_date( $timestamp, $settings ) : SBY_Display_Elements_Pro::format_date( $live_streaming_timestamp, $settings, true );

//$location_info       = SBY_Parse_Pro::get_location_info( $post ); // array( 'name' => $name, 'id' => $int, 'longitude' => $lon_int , 'lattitude' => $lat_int )
//$lightbox_media_atts = SBY_Parse_Pro::get_lightbox_media_atts( $post ); // array( 'video' => $url, 'carousel' => $json )
$sby_link_classes    = SBY_Display_Elements_Pro::get_sby_link_classes( $settings ); // // ' sby_disable_lightbox'

// Pro Styles
$link_styles                = SBY_Display_Elements_Pro::get_sby_link_styles( $settings ); // style="background: rgba(30,115,190,0.85)" already escaped
$hover_styles               = SBY_Display_Elements_Pro::get_hover_styles( $settings ); // style="color: rgba(153,231,255,1)" already escaped
$sby_info_styles            = SBY_Display_Elements_Pro::get_sby_info_styles( $settings ); // style="font-size: 13px;" already escaped
$sby_meta_color_styles      = SBY_Display_Elements_Pro::get_sby_meta_color_styles( $settings ); // style="font-size: 13px;" already escaped
$sby_meta_size_color_styles = SBY_Display_Elements_Pro::get_sby_meta_size_color_styles( $settings ); // style="font-size: 13px;color: rgba(153,231,255,1)" already escaped?>
<div id="sby_player_<?php echo esc_attr( $post_id ); ?>" class="sby_player_outer_wrap sby_player_item" <?php echo $player_outer_wrap_style_attr; ?>>
    <div class="sby_video_thumbnail_wrap">
        <a class="sby_video_thumbnail sby_player_video_thumbnail" href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener" data-full-res="<?php echo esc_url( $media_full_res ); ?>" data-img-src-set="<?php echo esc_attr( wp_json_encode( $media_all_sizes_json ) ); ?>" data-video-id="<?php echo esc_attr( $video_id ); ?>">
            <span class="sby-screenreader"><?php echo sprintf( __( 'YouTube Video %s', 'feeds-for-youtube' ), $post_id ); ?></span>
            <img src="<?php echo esc_url( $media_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>">
            <span class="sby_loader sby_hidden" style="background-color: rgb(255, 255, 255);"></span>
        </a>
        <div class="sby_player_wrap">
            <div class="sby_player"></div>
        </div>
	    <?php include sby_get_feed_template_part( 'cta', $settings ); ?>
    </div>

    <?php if ( false ) : ?>

	    <?php
	    $context = 'player';
	    include sby_get_feed_template_part( 'info', $settings ); ?>

	    <?php if ( SBY_Display_Elements_Pro::should_show_element( 'description', $context, $settings ) ) : ?>
            <p class="sby_caption_wrap sby_item_caption_wrap">
                <span class="sby_caption" <?php echo $sby_info_styles; ?>><?php echo sby_esc_html_with_br( $caption ); ?></span><span class="sby_expand"> <a href="#"><span class="sby_more">...</span></a></span>
            </p>
	    <?php endif; ?>
    <?php endif; ?>

</div>
