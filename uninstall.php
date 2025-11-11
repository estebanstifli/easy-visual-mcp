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

$wrap_table = static function ($table_name) {
	$clean = preg_replace('/[^A-Za-z0-9_]/', '', (string) $table_name);
	return '`' . $clean . '`';
};

$drop_tables = static function (array $tables, $wpdb, $wrap_table) {
	foreach ($tables as $table_suffix) {
		$table_name = $wpdb->prefix . $table_suffix;
		$table_sql = $wrap_table($table_name);
		$drop_sql = sprintf('DROP TABLE IF EXISTS %s', $table_sql);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared -- uninstall must remove plugin-managed tables explicitly.
		$wpdb->query($drop_sql);
	}
};

// Drop all plugin tables
$drop_tables(array('evmcp_queue', 'evmcp_tools', 'evmcp_profile_tools', 'evmcp_profiles'), $wpdb, $wrap_table);

// Delete all plugin options
delete_option('easy_visual_mcp_token');
delete_option('easy_visual_mcp_token_user');

// For multisite installations
if (is_multisite()) {
	$easy_visual_mcp_sites = get_sites(['number' => 0]);
	foreach ($easy_visual_mcp_sites as $easy_visual_mcp_site) {
		switch_to_blog($easy_visual_mcp_site->blog_id);
		
		// Drop tables for each site
		$drop_tables(array('evmcp_queue', 'evmcp_tools', 'evmcp_profile_tools', 'evmcp_profiles'), $wpdb, $wrap_table);
		
		// Delete options for each site
		delete_option('easy_visual_mcp_token');
		delete_option('easy_visual_mcp_token_user');
		
		restore_current_blog();
	}
}

// Clear any cached data
wp_cache_flush();
