<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
$feed_styles = SBY_Display_Elements::get_feed_style( $settings );
$cols_setting = SBY_Display_Elements::get_cols( $settings );
$mobile_cols_setting = SBY_Display_Elements::get_cols_mobile( $settings );
$items_wrap_classes = $settings['infoposition'] === 'side' ? ' sby_info_side' : '';
$items_wrap_style_attr = SBY_Display_Elements::get_style_att( 'items_wrap', $settings );
$num_setting = $settings['num'];
$nummobile_setting = $settings['nummobile'];

if ( $settings['showheader'] && ! empty( $posts ) && $settings['headeroutside'] ) {
	include sby_get_feed_template_part( 'header', $settings );
}
?>

<div id="sb_youtube_<?php echo esc_attr( preg_replace( "/[^A-Za-z0-9 ]/", '', $feed_id ) ); ?>" class="sb_youtube sby_layout_<?php echo esc_attr( $settings['layout'] ); ?> sby_col_<?php echo esc_attr( $cols_setting ); ?> sby_mob_col_<?php echo esc_attr( $mobile_cols_setting ); ?> <?php echo esc_attr( $additional_classes ); ?>" data-feedid="<?php echo esc_attr( $feed_id ); ?>" data-shortcode-atts="<?php echo esc_attr( $shortcode_atts ); ?>" data-cols="<?php echo esc_attr( $cols_setting ); ?>" data-colsmobile="<?php echo esc_attr( $mobile_cols_setting ); ?>" data-num="<?php echo esc_attr( $num_setting ); ?>" data-nummobile="<?php echo esc_attr( $nummobile_setting ); ?>"<?php echo $other_atts . $feed_styles; ?>>
	<?php
	if ( $settings['showheader'] && ! empty( $posts ) && !$settings['headeroutside'] ) {
		include sby_get_feed_template_part( 'header', $settings );
	}
	?>
    <?php if ( $settings['layout'] === 'gallery' && isset( $posts[0] ) ) {
        $placeholder_post = $posts[0];
	    $misc_data = $this->get_misc_data( $this->regular_feed_transient_name, $posts );
	    include sby_get_feed_template_part( 'player', $settings );
    } ?>
    <div class="sby_items_wrap<?php echo $items_wrap_classes;?>"<?php echo $items_wrap_style_attr; ?>>
		<?php
		if ( ! in_array( 'ajaxPostLoad', $flags, true ) ) {
		    $settings['feed_id'] = $feed_id;
			$this->posts_loop( $posts, $settings );
		}
		?>
    </div>
    <?php if ( isset( $posts[0] ) ) $this->maybe_add_live_html( $posts[0] ); ?>

	<?php if ( ! empty( $posts ) ) { include sby_get_feed_template_part( 'footer', $settings ); } ?>

    <?php if ( $settings['layout'] === 'grid' || $settings['layout'] === 'carousel' ) { include sby_get_feed_template_part( 'cta', $settings ); } ?>

	<?php
	/**
	 * Things to add before the closing "div" tag for the main feed element. Several
	 * features rely on this hook such as local images and some error messages
	 *
	 * @param object SBY_Feed
	 * @param string $feed_id
	 *
	 * @since 1.0
	 */
	do_action( 'sby_before_feed_end', $this, $feed_id ); ?>
</div>