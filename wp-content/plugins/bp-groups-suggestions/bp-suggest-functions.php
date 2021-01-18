<?php

/**
 *
 * @param type $users_id
 * @return type
 */
function friends_for_group_suggestion_ls($users_id) {
    if (is_user_logged_in()) {
        $user_id = bp_loggedin_user_id();
        $my_friends = (array) friends_get_friend_user_ids($user_id); //get all friend ids
        add_filter('ls_group_suggestions_description', 'friends_grsug_ls_description');
        if ($users_id) {
            $users_id = array_unique( array_merge( $my_friends, $users_id ) );
	    return $users_id;
        } else {
            $users_id = $my_friends;
	    return $users_id;
        }
    }
}

add_filter('add_users_for_group_search', 'friends_for_group_suggestion_ls');

/**
 *
 * @param type $description
 * @return type
 */
function friends_grsug_ls_description($description) {
    return $description . ', ' . __( "your's friends groups", "bp-groups-suggestions" );
}

/**
 * Exclude user's groups, get all current user  groups, includes pending,already a member, banned ,kicked etc
 * @global type $wpdb
 * @param type $my_external_excluded
 * @return type
 */
function my_groups_to_exclude($my_external_excluded) {
    global  $wpdb;
    $bp= buddypress();
    if (is_user_logged_in()) {
        $user_id = bp_loggedin_user_id();
        $my_all_groups_sql = $wpdb->prepare("SELECT group_id FROM {$bp->groups->table_name_members}  WHERE user_id = %d  ", $user_id);
        $my_groups = $wpdb->get_col($my_all_groups_sql);
        if ($my_external_excluded) {
            $my_external_excluded = array_unique(array_merge($my_external_excluded, $my_groups));
        } else {
            $my_external_excluded = $my_groups;
        }
        return $my_external_excluded;
    }
}

add_filter('add_excluded_groups_suggestions', 'my_groups_to_exclude');





/* * ADD DEFAULT SUGGESTED GROUPS* */

//add_filter( 'get_possible_groups_suggestions_by_plugin', 'my_groups_suggestions',1 );
/*
 * *ADD DEFAULT SUGGESTED GROUPS
 * @param type $groups
 * @return type
 */
function my_groups_suggestions( $groups ) {
    $groups[] = '7';
    $groups[] = '5';
    $groups[] = '20';
    add_filter( 'ls_group_suggestions_description', 'my_groups_suggestions_description' );
    return $groups;
}

/**
 *Add the description for the suggested groups
 * @param type $description
 * @return type
 */
function my_groups_suggestions_description( $description ) {
     $current_site = get_current_site();
    return $description . sprintf( __( "%s suggestions", "bp-groups-suggestions" ), $current_site->site_name );
}

?>
<?php

/**
 * /**
 * Return array with group_ids based on users_id and excluded_group_ids which are recently active.
 * Recently active means 'last_activity' date is < BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL . " " . BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL_TYPE
 * Both BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL and BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL_TYPE are defined in loader.php file
 * @version 4, 7/3/2014, added array_filter
 * v3.0, 23/10/2013
 * @author stergatu
 * @todo Add count friends per group
 * @global type $wpdb
 * @param type $users_ids
 * @param type $excluged_groups_ids
 * @return array
 */
function get_users_groups($users_ids = array(), $excluged_groups_ids = array()) {
    global $wpdb;
    $bp= buddypress();
    $user_id = bp_loggedin_user_id();

   $users_groups_ids = wp_cache_get( 'get_users_groups_for_user' . $user_id );
   
    if (false === $users_groups_ids) {
        $users_groups_sql = "SELECT DISTINCT g.id, count(m.user_id) as friends FROM  {$bp->groups->table_name} g
        Inner Join {$bp->groups->table_name_members} AS m ON m.group_id = g.id
        Inner Join {$bp->groups->table_name_groupmeta} as t ON g.id = t.group_id
            WHERE  (g.status='public' OR g.status='private') " ;

        $users_ids = array_filter( $users_ids , 'strlen' ) ;
        $excluged_groups_ids = array_filter( $excluged_groups_ids , 'strlen' ) ;
        if (count($users_ids) > 0) {
            $users_list = "(" . join(",", $users_ids) . ")";
            $users_groups_sql .="  AND m.user_id in {$users_list} AND is_confirmed= 1 ";
        }
        if (count($excluged_groups_ids) > 0) {
            $not_these_groups = "(" . join(",", $excluged_groups_ids) . ")";
            $users_groups_sql .= "AND g.id not in {$not_these_groups}";
        }
        if (BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL > 0) {
            $users_groups_sql .= " AND t.meta_key =  'last_activity'
                AND
                t.meta_value > DATE_SUB(CURDATE(), INTERVAL " . BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL . " " . BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL_TYPE . ")";
        }
        $users_groups_sql.="   group by g.id order by friends desc";
	$users_groups_ids = (array) $wpdb->get_col($users_groups_sql, 0);
	wp_cache_set('get_users_groups_for_user' . $user_id, $users_groups_ids);
    }
    return $users_groups_ids;

}

/**
 * Prints the "I'm not interested" button
 * @param type $group
 * @author stergatu
 *  @since  21/10/2013
 *  @version 1, 21/10/2013
 */
function bp_group_remove_suggestion_button($group = false) {
    echo bp_get_group_remove_suggestion_button($group);
}

/**
 * Creates the remove suggestion button
 * @param type $group
 * @return boolean
 *  @author stergatu
 *  @since  21/10/2013
 *  @version 2, 21/11/2014, replaced template_notice with echo
 *  @global type $groups_template
 */
function bp_get_group_remove_suggestion_button($group = false) {
    global $groups_template;

    if (empty($group))
        $group = & $groups_template->group;

    if (!is_user_logged_in() || bp_group_is_user_banned($group))
        return false;

    //Group is already hidden from suggestions
    if (is_array(BPGroupSuggest::get_hidden())) {
        if (in_array($group->id, BPGroupSuggest::get_hidden())) {
           echo bp_gs_ls_is_hidden_message();
	    return false;
        }
    }
    // Group creation was not completed or status is unknown
    if (!$group->status)
        return false;

    // Already a member
    if (isset($group->is_member) && $group->is_member) {
        return false;
        // Not a member
    } else {
        // Show different buttons based on group status
        switch ($group->status) {
            case 'hidden' :
                return false;
                break;
            case 'public':
                $button = array(
                    'id' => 'removegroupsuggestion-',
                    'component' => 'groups',
                    'must_be_logged_in' => true,
                    'block_self' => false,
                    'wrapper_class' => 'group-button ' . $group->status,
                    'wrapper_id' => 'removesuggestion-group-' . $group->id,
                    'link_href' => wp_nonce_url(bp_get_root_domain() . "/remove-group-suggestion/?suggest_id=" . $group->id, 'group-suggestion-remove-' . $group->id),
                    'link_text' => __('Hide this suggestion', 'bp-groups-suggestions'),
                    'link_title' => __('Hide this suggestion', 'bp-groups-suggestions'),
                    'link_class' => 'group-button removesuggestion-group',
                );
                break;
            case 'private' :
                // Member has not requested membership yet
                if (!bp_group_has_requested_membership($group)) {
                    $button = array(
                        'id' => 'removegroupsuggestion-',
                        'component' => 'groups',
                        'must_be_logged_in' => true,
                        'block_self' => false,
                        'wrapper_class' => 'group-button ' . $group->status,
                        'wrapper_id' => 'removesuggestion-group-' . $group->id,
                        'link_href' => wp_nonce_url(bp_get_root_domain() . "/remove-group-suggestion/?suggest_id=" . $group->id, 'group-suggestion-remove-' . $group->id),
                        'link_text' => __('Hide this suggestion', 'bp-groups-suggestions'),
                        'link_title' => __('Hide this suggestion', 'bp-groups-suggestions'),
                        'link_class' => 'group-button removesuggestion-group',
                    );

                    // Member has requested membership already
                } else {
                    return false;
                }

                break;
        }
    }

    // Filter and return the HTML button
    return bp_get_button(apply_filters('bp_get_group_remove_suggestion_button', $button));
}

function bp_gs_ls_is_hidden_message() {
    return '<div id="message" class="info"><p>' . __( 'You have previously select that you are not interested for this group.', 'bp-groups-suggestions' ) . '</p></div>';
}

/**
 *
 * @param type $group_id
 * @param type $user_id
 */
function bpgsls_remove_hidden_suggestion_and_cache($group_id, $user_id) {
    $hidden_from_suggestions = (array) BPGroupSuggest::get_hidden($user_id);

   //Group is  hidden from suggestions
    if (in_array($group_id, $hidden_from_suggestions)) {
        $hidden_from_suggestions = array_diff($hidden_from_suggestions, array($group_id));
        update_user_meta($user_id, 'hidden_group_suggestions', $hidden_from_suggestions);
        wp_cache_delete('get_users_groups_for_user' . $user_id);
    }
}

add_action('groups_join_group', 'bpgsls_remove_hidden_suggestion_and_cache', 10, 2);
add_action('groups_leave_group', 'bpgsls_remove_hidden_suggestion_and_cache', 10, 2);
add_action('groups_ban_member', 'bpgsls_remove_hidden_suggestion_and_cache', 10, 2);
add_action( 'groups_unban_member', 'bpgsls_remove_hidden_suggestion_and_cache', 10, 2 );
add_action( 'delete_user', 'bpgsls_remove_hidden_suggestion_and_cache', 10, 2 );
add_action( 'friends_friendship_accepted', 'bpgsls_remove_hidden_suggestion_and_cache', 10, 2 );
add_action( 'friends_friendship_deleted', 'bpgsls_remove_hidden_suggestion_and_cache', 10, 2 );
