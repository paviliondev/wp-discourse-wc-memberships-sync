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
define('SYNCED_WC_MEMBERSHIP_PLAN_ID', 30);
define('SYNCED_WC_GROUP_NAME', 'locker');

const ACTIVE_STATUSES = array('active');

add_action('wc_memberships_user_membership_status_changed', function($membership, $old, $new) {
    if( $membership->get_plan_id() == SYNCED_WC_MEMBERSHIP_PLAN_ID) {
        if(in_array($membership->get_status(), ACTIVE_STATUSES)) {
            \WPDiscourse\Utilities\Utilities::add_user_to_discourse_group($membership->get_user_id(), SYNCED_WC_GROUP_NAME);
        } else {
            \WPDiscourse\Utilities\Utilities::remove_user_from_discourse_group($membership->get_user_id(), SYNCED_WC_GROUP_NAME);            
        }
    }
}, 10, 3);
