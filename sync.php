<?php
/**
 * Plugin Name: Discourse WC Memberships Sync
 * Description: Use Discourse as a community engine for your WordPress blog
 * Version: 1.0.0
 * Author: fzngagan@gmail.com
 * Author URI: https://github.com/discourse/wp-discourse
 * Plugin URI: https://github.com/discourse/wp-discourse
 * GitHub Plugin URI: https://github.com/discourse/wp-discourse
 */
defined('ABSPATH') or exit;
define('SYNCED_WC_MEMBERSHIP_PLAN_ID', 7133);
define('SYNCED_WC_GROUP_NAME', 'members');

const ACTIVE_STATUSES = array('active', 'complimentary');

// using _saved hook instead of _created so that it will be called on manual creation too
add_action('wc_memberships_user_membership_saved', 'pv_update_discourse_membership_created', 10, 2);
add_action('wc_memberships_user_membership_deleted', 'pv_update_discourse_membership_deleted');
add_action('wc_memberships_user_membership_status_changed', 'pv_update_discourse_membership_updated');
function pv_update_discourse_membership_created($plan, $opts) {
    if($plan && ($plan->get_id() == SYNCED_WC_MEMBERSHIP_PLAN_ID)) {
        \WPDiscourse\Utilities\Utilities::add_user_to_discourse_group($opts['user_id'], SYNCED_WC_GROUP_NAME);
    }
}

function pv_update_discourse_membership_deleted($membership) {
    if( $membership->get_plan_id() == SYNCED_WC_MEMBERSHIP_PLAN_ID) {
        \WPDiscourse\Utilities\Utilities::remove_user_from_discourse_group($membership->get_user_id(), SYNCED_WC_GROUP_NAME);
    }
}

function pv_update_discourse_membership_updated($membership) {
    if( $membership->get_plan_id() == SYNCED_WC_MEMBERSHIP_PLAN_ID) {
        if(in_array($membership->get_status(), ACTIVE_STATUSES)) {
            \WPDiscourse\Utilities\Utilities::add_user_to_discourse_group($membership->get_user_id(), SYNCED_WC_GROUP_NAME);
        } else {
            \WPDiscourse\Utilities\Utilities::remove_user_from_discourse_group($membership->get_user_id(), SYNCED_WC_GROUP_NAME);            
        }
    }
}

add_filter( 'wpdc_sso_params', 'wpdc_custom_sso_params', 10, 2 );
function wpdc_custom_sso_params( $params, $user ) {
    $memberships = wc_memberships_get_user_memberships($user);
    $synced_membership = array_filter($memberships, function ($membership) {
        return $membership->get_plan_id() == SYNCED_WC_MEMBERSHIP_PLAN_ID;
    });

    if ($synced_membership[0] && in_array($synced_membership[0]->get_status(), ACTIVE_STATUSES  )) {
        $params['add_groups'] = SYNCED_WC_GROUP_NAME; // Don't use spaces between names.
    } else {
        $params['remove_groups'] = SYNCED_WC_GROUP_NAME;
    }

    return $params;
}
