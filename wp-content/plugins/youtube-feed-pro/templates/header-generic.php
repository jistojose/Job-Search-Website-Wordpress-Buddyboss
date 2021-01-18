<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
$header_style_attr       = SBY_Display_Elements::get_style_att( 'items', $settings );
$header_text_color_style = SBY_Display_Elements::get_header_text_color_styles( $settings ); // style="color: #517fa4;" already escaped
$size_class              = SBY_Display_Elements::get_header_size_class( $settings );
?>
<div class="sb_youtube_header sby_header_type_generic <?php echo esc_attr( $size_class ); ?>"<?php echo $header_style_attr; ?>>
	<div class="sby_header_text sby_no_bio">
		<h3 <?php echo $header_text_color_style; ?>>YouTube</h3>
	</div>
	<div class="sby_header_img">
		<div class="sby_header_icon"><?php echo SBY_Display_Elements::get_icon( 'newlogo', $icon_type ); ?></div>
	</div>
</div>