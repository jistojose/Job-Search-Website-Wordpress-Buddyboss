<?php
/**
 * Plugin Name:           AutomatorWP - BuddyBoss integration
 * Plugin URI:            https://wordpress.org/plugins/automatorwp-buddyboss-integration/
 * Description:           Connect AutomatorWP with BuddyBoss.
 * Version:               1.1.1
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-buddyboss-integration
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\BuddyBoss
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_BuddyBoss_Integration {

    /**
     * @var         AutomatorWP_BuddyBoss_Integration $instance The one true AutomatorWP_BuddyBoss_Integration
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_BuddyBoss_Integration self::$instance The one true AutomatorWP_BuddyBoss_Integration
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_BuddyBoss_Integration();
            
            if( ! self::$instance->pro_installed() ) {

                self::$instance->constants();
                self::$instance->includes();
                self::$instance->load_textdomain();

            }

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_BUDDYBOSS_VER', '1.1.1' );

        // Plugin file
        define( 'AUTOMATORWP_BUDDYBOSS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_BUDDYBOSS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_BUDDYBOSS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            // Includes
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/account-activation.php';
            // BuddyBoss Follow
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/start-following.php';
            // BuddyBoss Email Invites
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/send-email-invite.php';
            // BuddyBoss Profile
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/update-avatar.php';
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/update-cover.php';
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/update-profile.php';
            // BuddyBoss Activity
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/publish-activity.php';
            // BuddyBoss Groups
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/join-group.php';
            // BuddyBoss Forums
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/create-forum.php';
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/triggers/create-topic.php';

            // Actions
            // BuddyBoss Profile
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/actions/set-user-member-type.php';
            // BuddyBoss Activity
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/actions/add-user-activity.php';
            // BuddyBoss Groups
            require_once AUTOMATORWP_BUDDYBOSS_DIR . 'includes/actions/add-user-group.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'buddyboss', array(
            'label' => 'BuddyBoss',
            'icon'  => AUTOMATORWP_BUDDYBOSS_URL . 'assets/buddyboss.svg',
        ) );

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - BuddyBoss requires %s and %s in order to work. Please install and activate them.', 'automatorwp-buddyboss-integration' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://www.buddyboss.com/" target="_blank">BuddyBoss</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php elseif ( $this->pro_installed() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php echo __( 'You can uninstall AutomatorWP - BuddyBoss Integration because you already have the pro version installed and includes all the features of the free version.', 'automatorwp-buddyboss-integration' ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        if ( ! defined( 'BP_PLATFORM_VERSION' ) ) {
            return false;
        }

        return true;

    }

    /**
     * Check if the pro version of this integration is installed
     *
     * @since  1.0.0
     *
     * @return bool True if pro version installed
     */
    private function pro_installed() {

        if ( ! class_exists( 'AutomatorWP_BuddyBoss' ) ) {
            return false;
        }

        return true;

    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        // Set filter for language directory
        $lang_dir = AUTOMATORWP_BUDDYBOSS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_buddyboss_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-buddyboss-integration' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-buddyboss-integration', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-buddyboss-integration/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-buddyboss-integration/ folder
            load_textdomain( 'automatorwp-buddyboss-integration', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-buddyboss-integration/languages/ folder
            load_textdomain( 'automatorwp-buddyboss-integration', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-buddyboss-integration', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_BuddyBoss_Integration instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_BuddyBoss_Integration The one true AutomatorWP_BuddyBoss_Integration
 */
function AutomatorWP_BuddyBoss_Integration() {
    return AutomatorWP_BuddyBoss_Integration::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_BuddyBoss_Integration' );
