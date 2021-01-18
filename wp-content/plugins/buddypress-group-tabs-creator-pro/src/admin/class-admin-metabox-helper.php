<?php
/**
 * Plugin admin file add new meta boxes to our post type
 *
 * @package BPGTC
 * @subpackage Admin
 */

namespace PressThemes\BPGTC\Admin;

/**
 * If file accessed directly if will exit
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_MetaBox_Helper
 */
class Admin_MetaBox_Helper {

	/**
	 * Class boot
	 */
	public static function boot() {
		$self = new self();

		$self->setup();
		$self->add_meta_box();
	}

	/**
	 * Setup hooks.
	 */
	private function setup() {
		// GD fixes.
		add_action( 'media_buttons', array( $this, 'fix_media_buttons_for_ayecode' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'media_button_fixes' ) );
    }

	/**
	 * Add meta boxes
	 */
	private function add_meta_box() {
		add_action( 'cmb2_admin_init', array( $this, 'render_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );

		add_action( 'save_post', array( $this, 'save_details' ) );
	}

	/**
	 * Render format meta box
	 */
	public function render_meta_box() {

		$prefix = '_bpgtc_';

		$visibility_options = array(
			'do_not_modify' => __( 'Do Not Modify(only applies when modifying existing tab or subnav)', 'buddypress-group-tabs-creator-pro' ),
			'all'           => __( 'Anyone', 'buddypress-group-tabs-creator-pro' ),
			'logged_in'     => __( 'Logged In', 'buddypress-group-tabs-creator-pro' ),
			'members_only'  => __( 'Members Only', 'buddypress-group-tabs-creator-pro' ),
			'admin_only'    => __( 'Group Admin Only', 'buddypress-group-tabs-creator-pro' ),
		);

		// Tabs Meta.
		$cmb_tabs = new_cmb2_box( array(
			'id'           => $prefix . 'tabs_meta',
			'title'        => __( 'Tab Settings', 'buddypress-group-tabs-creator-pro' ),
			'object_types' => array( bpgtc_get_post_type() ),
			'context'      => 'advanced',
			'priority'     => 'high',
			'show_names'   => true,
		) );

		$cmb_tabs->add_field( array(
			'name' => __( 'Need help?', 'buddypress-group-tabs-creator-pro' ),
			'desc' => sprintf( __( 'Get started with the <a href="%s">new tab options</a>', 'buddypress-group-tabs-creator-pro' ), 'https://buddydev.com/docs/buddypress-group-tabs-creator-pro/adding-new-buddypress-group-tab/' ),
			'type' => 'title',
			'id'   => 'this_is_un_wanted_id_and_will_not_be_used_1001',
		) );

		$cmb_tabs->add_field( array(
			'name'    => __( 'Is enabled?', 'buddypress-group-tabs-creator-pro' ),
			'id'      => $prefix . 'tab_is_active',
			'type'    => 'checkbox',
			'default' => 0,
			'desc'    => __( 'Only enabled tabs will be visible.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field(
			array(
				'name'    => __( 'Tab Label', 'buddypress-group-tabs-creator-pro' ),
				'id'      => $prefix . 'tab_label',
				'type'    => 'text',
				'default' => '',
				'desc'    => __( 'Tab label. If Tab label is not provided, the entry title will be used as label.', 'buddypress-group-tabs-creator-pro' ),
			)
		);

		$cmb_tabs->add_field(
			array(
				'name'    => __( 'Tab Slug', 'buddypress-group-tabs-creator-pro' ),
				'id'      => $prefix . 'tab_slug',
				'type'    => 'text',
				'default' => '',
				'desc'    => __( 'Preferably unique tab slug. If tab slug is not provided, the entry slug will be used as label.', 'buddypress-group-tabs-creator-pro' ),
			)
		);


		$cmb_tabs->add_field( array(
			'name' => __( 'Set it as default component?', 'buddypress-group-tabs-creator-pro' ),
			'id'   => $prefix . 'is_default_component',
			'type' => 'checkbox',
			'desc' => __( 'Set it as default component?', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field( array(
			'name' => __( 'Are we modifying predefined tab?', 'buddypress-group-tabs-creator-pro' ),
			'id'   => $prefix . 'tab_is_existing',
			'type' => 'checkbox',
			'desc' => __( 'Are we modifying a tab added by BuddyPress or some other plugin?', 'buddypress-group-tabs-creator-pro' ),
		) );

		// replace with tab visibility.
		$cmb_tabs->add_field( array(
			'name'    => __( 'Update label?', 'buddypress-group-tabs-creator-pro' ),
			'id'      => $prefix . 'tab_update_label',
			'type'    => 'checkbox',
			'default' => 0,
			'classes' => 'bpgtc-existing-tab-show',
			//'show_on_cb' => array( $this, 'show_for_new' ),
			'desc'    => __( 'Should we update the tab label with new label?', 'buddypress-group-tabs-creator-pro' ),
		) );

		// replace with tab visibility.
		$cmb_tabs->add_field( array(
			'name'    => __( 'Site admin only?', 'buddypress-group-tabs-creator-pro' ),
			'id'      => $prefix . 'site_admin_only',
			'type'    => 'checkbox',
			'default' => 0,
			'desc'    => __( 'Is this tab visible to site admin only?', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field( array(
			'name'    => __( 'Enabled for?', 'buddypress-group-tabs-creator-pro' ),
			'id'      => $prefix . 'tab_enabled_for',
			'type'    => 'multicheck',
			'default' => 'all',
			'classes' => 'bpgtc-tab-enabled-for',
			'options' => array(
				'all'     => __( 'All', 'buddypress-group-tabs-creator-pro' ),
				'public'  => __( 'Public Groups', 'buddypress-group-tabs-creator-pro' ),
				'private' => __( 'Private Groups', 'buddypress-group-tabs-creator-pro' ),
				'hidden'  => __( 'Hidden Groups', 'buddypress-group-tabs-creator-pro' ),
			),
			'desc'    => __( 'Tab will be available to these group status', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field( array(
			'name'    => __( 'Tab Visibility', 'buddypress-group-tabs-creator-pro' ),
			'id'      => $prefix . 'tab_visible_for',
			'type'    => 'multicheck',
			'default' => 'all',
			'classes' => 'bpgtc-tab-visible-for',
			'options' => $visibility_options,
			'desc'    => __( 'Tab will be visible to these users when they visit the group', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field( array(
			'name' => __( 'Position', 'buddypress-group-tabs-creator-pro' ),
			'id'   => $prefix . 'tab_position',
			'type' => 'text',
			'desc' => __( 'Number, Required. Determines position of tab.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field( array(
			'name' => __( 'Tab Link', 'buddypress-group-tabs-creator-pro' ),
			'id'   => $prefix . 'tab_link',
			'type' => 'text',
			'desc' => __( 'Optional. Only use if you need dynamic url or link to absolute urls. See our <a href="https://buddydev.com/docs/buddypress-group-tabs-creator-pro/buddypress-group-tab-link-configuration/">documentation</a> about this.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field( array(
			'name' => __( 'Default Subnav Slug', 'buddypress-group-tabs-creator-pro' ),
			'id'   => $prefix . 'tab_default_subnav_slug',
			'type' => 'text',
			'desc' => __( 'Optional. Specify the slug of the subnav to be used. If not specified, first sub nav will be default.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb_tabs->add_field( array(
			'name' => __( 'Item CSS ID', 'buddypress-group-tabs-creator-pro' ),
			'id'   => $prefix . 'tab_item_css_id',
			'type' => 'text',
			'desc' => __( 'Optional. CSS id for the nav item.', 'buddypress-group-tabs-creator-pro' ),
		) );

		// sub navs.
		$cmb = new_cmb2_box( array(
			'id'           => $prefix . 'sub_tabs_meta',
			'title'        => __( 'Subnav', 'buddypress-group-tabs-creator-pro' ),
			'object_types' => array( bpgtc_get_post_type() ),
			'context'      => 'advanced',
			'priority'     => 'high',
			'show_names'   => true,
		) );

		$cmb->add_field( array(
			'name' => __( 'Need help?', 'buddypress-group-tabs-creator-pro' ),
			'desc' => sprintf( __( 'Get started with the <a href="%s">sub nav options</a>', 'buddypress-group-tabs-creator-pro' ), 'https://buddydev.com/docs/buddypress-group-tabs-creator-pro/adding-group-tab-sub-navs/' ),
			'type' => 'title',
			'id'   => 'this_is_un_wanted_id_and_will_not_be_used_1002',
		) );

		$group_field_id = $cmb->add_field( array(
			'id'      => $prefix . 'subnav_items',
			'type'    => 'group',
			'options' => array(
				'group_title'   => __( 'Subnav {#}', 'buddypress-group-tabs-creator-pro' ),
				'add_button'    => __( 'New', 'buddypress-group-tabs-creator-pro' ),
				'remove_button' => __( 'Remove', 'buddypress-group-tabs-creator-pro' ),
				'sortable'      => true,
			),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'    => __( 'Is enabled?', 'buddypress-group-tabs-creator-pro' ),
			'id'      => 'is_active',
			'type'    => 'checkbox',
			'default' => 0,
			'desc'    => __( 'Only enabled sub navs are visible.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Label', 'buddypress-group-tabs-creator-pro' ),
			'id'   => 'label',
			'type' => 'text',
			'desc' => __( 'Required. Label for the sub nav item.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Slug', 'buddypress-group-tabs-creator-pro' ),
			'id'   => 'slug',
			'type' => 'text',
			'desc' => __( 'Required. Unique slug for this sub nav.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Position', 'buddypress-group-tabs-creator-pro' ),
			'id'   => 'position',
			'type' => 'text',
			'desc' => __( 'Required. Please specify a position for the sub nav.', 'buddypress-group-tabs-creator-pro' ),
			//'show_on_cb' => array( $this, 'show_for_existing' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'    => __( 'Site Admin Only', 'buddypress-group-tabs-creator-pro' ),
			'id'      => 'site_admin_only',
			'type'    => 'checkbox',
			'default' => 0,
			'classes' => 'bpgtc-existing-subnav-hide',
			'desc'    => __( 'Is this sub nav visible to site admin only?', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'    => __( 'Enabled for?', 'buddypress-group-tabs-creator-pro' ),
			'id'      => 'enabled_for',
			'type'    => 'multicheck',
			'default' => 'all',
			'options' => array(
				'all'     => __( 'All', 'buddypress-group-tabs-creator-pro' ),
				'public'  => __( 'Public Groups', 'buddypress-group-tabs-creator-pro' ),
				'private' => __( 'Private Groups', 'buddypress-group-tabs-creator-pro' ),
				'hidden'  => __( 'Hidden Groups', 'buddypress-group-tabs-creator-pro' ),
			),
			'classes' => 'bpgtc-subnav-enabled-for',
			'desc'    => __( 'Tab will be available to these groups', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'    => __( 'Visible for?', 'buddypress-group-tabs-creator-pro' ),
			'id'      => 'visible_for',
			'type'    => 'multicheck',
			'default' => 'all',
			'options' => $visibility_options,
			'classes' => 'bpgtc-subnav-visibile-for',
			'desc'    => __( 'Tab will be visible to these users.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Url', 'buddypress-group-tabs-creator-pro' ),
			'id'   => 'url',
			'type' => 'text',
			'desc' => __( '(Optional), if you want to link to some other page.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'CSS ID', 'buddypress-group-tabs-creator-pro' ),
			'id'   => 'item_css_id',
			'type' => 'text',
			'desc' => __( 'Optional, CSS id for the sub nav item.', 'buddypress-group-tabs-creator-pro' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'            => __( 'Content', 'buddypress-group-tabs-creator-pro' ),
			'id'              => 'content',
			'type'            => 'wysiwyg',
			'classes'         => 'bpgtc-existing-subnav-hide',
			'desc'            => sprintf( __( 'Any type of content( shortcodes too ) will work. Please see <a href="%s">documentation</a> for more details.', 'buddypress-group-tabs-creator-pro' ), 'https://buddydev.com/docs/buddypress-group-tabs-creator-pro/group-sub-tab-options/#bpgtc-tab-content' ),
			'options'         => array(
				'textarea_rows' => 5,
			),
			'sanitization_cb' => false,
		) );
	}

	/**
	 * Register the metabox for the WooCommerce Memberships plugin membership association to the member type.
	 */
	public function register_metabox() {
		add_meta_box( '_bpgtc_associate_groups', __( 'Associated BuddyPress Groups', 'buddypress-group-tabs-creator-pro' ), array(
			$this,
			'render_metabox',
		), bpgtc_get_post_type(), 'side' );
	}

	/**
	 * Render metabox.
	 *
	 * @param WP_Post $post currently editing member type post object.
	 */
	public function render_metabox( $post ) {
		$meta            = get_post_custom( $post->ID );
		$selected_groups = isset( $meta['_bpgtc_tab_groups'] ) ? $meta['_bpgtc_tab_groups'][0] : array();
		$selected_groups = maybe_unserialize( $selected_groups );

		if ( ! empty( $selected_groups ) ) {
			$groups = groups_get_groups( array(
				'include'     => $selected_groups,
				'show_hidden' => true,
			) );
			$groups = $groups['groups'];
		} else {
			$groups = array();
		}

		?>
		<ul id="bpgtc-selected-groups-list">
			<?php foreach ( $groups as $group ): ?>
				<li class="bpgtc-group-entry" id="bpgtc-group-<?php echo esc_attr( $group->id );?>">
					<input type="hidden" value="<?php echo esc_attr( $group->id );?>" name="_bpgtc_tab_groups[]" />
					<a class="bpgtc-remove-group" href="#">X</a>
					<a href="<?php echo bp_get_group_permalink( $group );?>"><?php echo $group->name;?> </a>
				</li>
			<?php endforeach; ?>
		</ul>
		<h3><?php _e( 'Select Group', 'buddypress-group-tabs-creator-pro' );?></h3>
		<p>
			<input type="text" placeholder="<?php _e( 'Type group name.', 'buddypress-group-tabs-creator-pro' );?>" id="bpgtc-group-selector" />
		</p>

		<style type="text/css">
			.bpgtc-remove-group {
				padding-right: 5px;
				color: red;
			}
		</style>

		<?php
	}

	/**
	 * Save the subscription association
	 *
	 * @param int $post_id numeric post id of the post containing member type details.
	 */
	public function save_details( $post_id ) {

		if ( bpgtc_get_post_type() != get_post_type( $post_id ) ) {
			return;
		}

		$groups = isset( $_POST['_bpgtc_tab_groups'] ) ? $_POST['_bpgtc_tab_groups'] : false;

		if ( $groups ) {
			$groups = array_unique( $groups );
			// should we validate the groups?
			// Let us trust site admins.
			update_post_meta( $post_id, '_bpgtc_tab_groups', $groups );
		} else {
			delete_post_meta( $post_id, '_bpgtc_tab_groups' );
		}
	}

	/**
	 * Callback for showing the metabox for existing tabs only.
	 *
	 * @param  object $cmb CMB2 object.
	 *
	 * @return bool True/false whether to show the metabox
	 */
	public function show_for_existing( $cmb ) {

		$status = get_post_meta( $cmb->object_id(), '_bpgtc_tab_is_existing', 1 );

		return $status == 'on';
	}

	/**
	 * Callback for showing the metabox for new tabs only.
	 *
	 * @param  object $cmb CMB2 object.
	 *
	 * @return bool True/false whether to show the metabox
	 */
	public function show_for_new( $cmb ) {
		return ! $this->show_for_existing( $cmb );
	}

	/**
	 * Disable custom media buttons.
	 */
	public function media_button_fixes() {
		if ( is_admin() && function_exists( 'get_current_screen' ) && bpgtc_get_post_type() === get_current_screen()->post_type ) {
			remove_all_actions( 'media_buttons' );
		}
	}

	/**
	 * Fix for Geodirectory breaking tinymce editor.
	 *
	 * @param string $editor_id editor id.
	 */
	public function fix_media_buttons_for_ayecode( $editor_id = 'content' ) {
		global $post, $shortcode_insert_button_once;

		if ( ! empty( $post ) && ! empty( $post->post_type ) && $post->post_type == bpgtc_get_post_type() ) {
			$shortcode_insert_button_once = true;
		}
	}
}
