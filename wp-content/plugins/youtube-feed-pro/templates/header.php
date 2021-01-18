<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$avatar                  = SBY_Parse::get_avatar( $header_data, $settings );
$channel_title           = SBY_Parse::get_channel_title( $header_data );
$channel_description     = SBY_Parse::get_channel_description( $header_data );
$subscriber_count       = SBY_Parse_Pro::get_subscriber_count( $header_data );
$should_show_subscriber         = $settings['showsubscribers'] && $subscriber_count !== '';
$permalink               = SBY_Parse::get_channel_permalink( $header_data );
$header_style_attr       = SBY_Display_Elements::get_style_att( 'items', $settings );
$header_text_color_style = SBY_Display_Elements::get_header_text_color_styles( $settings ); // style="color: #517fa4;" already escaped
$size_class              = SBY_Display_Elements::get_header_size_class( $settings );
$should_show_bio         = $settings['showdescription'] && $channel_description !== '';
$bio_class               = ! $should_show_bio ? ' sby_no_bio' : ' sby_has_bio';
$subscribers_class               = ! $should_show_subscriber ? ' sby_no_sub' : ' sby_has_sub';
?>
<div class="sb_youtube_header <?php echo esc_attr( $size_class ); ?>"<?php echo $header_style_attr; ?>>
    <a href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener" title="@<?php echo esc_attr( $channel_title ); ?>" class="sby_header_link">
        <div class="sby_header_text<?php echo esc_attr( $bio_class . $subscribers_class ); ?>">
            <h3 <?php echo $header_text_color_style; ?>><?php echo esc_html( $channel_title ); ?></h3>
	        <?php if ( $should_show_subscriber ) : ?>
            <span class="sby_subscribers"><?php echo SBY_Display_Elements_Pro::escaped_formatted_count_string($subscriber_count, 'subscribers' ); ?></span>
	        <?php endif; ?>
			<?php if ( $should_show_bio ) : ?>
                <p class="sby_bio" <?php echo $header_text_color_style; ?>><?php echo sby_esc_html_with_br( $channel_description ); ?></p>
			<?php endif; ?>
        </div>
        <div class="sby_header_img" data-avatar-url="<?php echo esc_attr( $avatar ); ?>">
            <div class="sby_header_img_hover"><?php echo SBY_Display_Elements::get_icon( 'newlogo', $icon_type ); ?></div>
            <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $channel_title ); ?>" width="50" height="50">
        </div>
    </a>
</div>