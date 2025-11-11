<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Easy_Visual_MCP
 */

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

global $wpdb;

// Drop all plugin tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_queue");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_tools");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_profile_tools");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_profiles");

// Delete all plugin options
delete_option('easy_visual_mcp_token');
delete_option('easy_visual_mcp_token_user');

// For multisite installations
if (is_multisite()) {
	$sites = get_sites(['number' => 0]);
	foreach ($sites as $site) {
		switch_to_blog($site->blog_id);
		
		// Drop tables for each site
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_queue");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_tools");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_profile_tools");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}evmcp_profiles");
		
		// Delete options for each site
		delete_option('easy_visual_mcp_token');
		delete_option('easy_visual_mcp_token_user');
		
		restore_current_blog();
	}
}

// Clear any cached data
wp_cache_flush();
