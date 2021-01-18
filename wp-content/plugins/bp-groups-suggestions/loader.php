<?php
/**
 *  Plugin Name: BP Groups Suggestions
 *  Author: lenasterg
 *  Author URI: http://lenasterg.wordpress.com
 *  Description: Adds a suggestion section to BuddyPress groups. based on  BP Groups Suggest Widget of buddydev.com
 *  Version: 1.8
 * License:  GNU General Public License 3.0 or newer (GPL) http://www.gnu.org/licenses/gpl.html
 * Last Updated: December 10, 2018
 * Description: Group suggestion section
 * Text Domain: bp-groups-suggestions
 *  Domain Path: /languages/
 */
define( 'BP_GROUP_SUGGESTIONS_LS_VERSION', '1.8' );
define( 'BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL' , '8' ) ;
define('BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL_TYPE', 'WEEK');

function bpgrsugls_loader() {
    global $wpdb;
    if (is_multisite() && BP_ROOT_BLOG != $wpdb->blogid) {
        return;
    }
    if (!class_exists('BP_Group_Extension')) {
        // Groups component is not enabled; don't initialize the extension
        return;
    }
    // Because our loader file uses BP_Component, it requires BP 1.5 or greater.
    if (version_compare(BP_VERSION, '1.5', '>')) {

        if (!defined('BP_GROUP_SUGGESTIONS_LS_SLUG')) {
            define('BP_GROUP_SUGGESTIONS_LS_SLUG', 'groups-suggestions');
        }

        if (!defined('BP_GROUP_SUGGESTIONS_LS_DIR')) {
            define('BP_GROUP_SUGGESTIONS_LS_DIR', WP_PLUGIN_DIR . '/bp-group-suggestions/');
        }

        if (!defined('BP_GROUP_SUGGESTIONS_LS_URL')) {
            define('BP_GROUP_SUGGESTIONS_LS_URL', WP_PLUGIN_URL . '/bp-group-suggestions/');
        }


        require_once( dirname(__FILE__) . '/bp-group-suggest.php' );
    }
    bpgrsugls_textdomain();
}

add_action('bp_include', 'bpgrsugls_loader');

/**
 *
 * @return type
 */
function bpgrsugls_textdomain() {
    $locale = get_locale();

    // First look in wp-content/languages, where custom language files will not be overwritten by upgrades. Then check the packaged language file directory.
    $mofile_custom = WP_CONTENT_DIR . "/languages/bp-groups-suggestions-$locale.mo";
    $mofile_packaged = BP_GROUP_SUGGESTIONS_LS_DIR . "languages/bp-groups-suggestions-$locale.mo";

    if (file_exists($mofile_custom)) {
        load_textdomain('bp-groups-suggestions', $mofile_custom);
        return;
    } else if (file_exists($mofile_packaged)) {
        load_textdomain('bp-groups-suggestions', $mofile_packaged);
        return;
    }
}

