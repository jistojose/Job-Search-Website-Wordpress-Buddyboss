<?php
/**
 * Plugin assets loader class
 *
 * @package    BPGTC
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace PressThemes\BPGTC\Bootstrap;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Assets_Loader.
 */
class Assets_Loader {

	/**
	 * Boot Assets_Loader.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Bind hooks
	 */
	private function setup() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Load plugin assets
	 *
	 * @param string $hook_suffix Hook suffix.
	 */
	public function load_assets( $hook_suffix ) {

		if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix ) {
			return;
		}

		if ( ! function_exists( 'bpgtc_group_tabs' ) || ! bpgtc_group_tabs()->is_admin_add_edit() ) {
			return;
		}

		$url = bpgtc_group_tabs()->get_url();

		wp_register_style( 'bpgtc_admin_css', $url . 'assets/css/bpgtc-admin-style.css' );
		wp_register_script( 'bpgtc_admin_js', $url . 'assets/js/bpgtc-edit.js', array( 'jquery' ) );
		wp_register_script( 'bpgtc_admin_groups_helper_js', $url . 'assets/js/bpgtc-admin-groups-helper.js', array( 'jquery' ) );

		wp_enqueue_style( 'bpgtc_admin_css' );
		wp_enqueue_script( 'bpgtc_admin_js' );
		wp_enqueue_script( 'bpgtc_admin_groups_helper_js' );
	}
}
