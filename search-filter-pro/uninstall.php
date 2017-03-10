<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Search & Filter Pro
 * 
 * @package   Search_Filter
 * @author    Ross Morsali
 * @link      http://www.designsandcode.com/
 * @copyright 2015 Designs & Code
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if ( is_multisite() ) {
	// store the current blog id
    $current_blog = $wpdb->blogid;
    
    // Get all blogs in the network and activate plugin on each one
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        uninstall_search_filter_pro();
        restore_current_blog();
    }

}
else
{

	//check for existence of caching database, if not install it
	uninstall_search_filter_pro();
}

function uninstall_search_filter_pro()
{	

	global $wpdb;
	// @TODO: Define uninstall functionality here
	delete_option( "search-filter-cache" );

	$cache_table_name = $wpdb->prefix . 'search_filter_cache';
	$term_results_table_name = $wpdb->prefix . 'search_filter_term_results';

	$wpdb->query("DROP TABLE IF EXISTS $cache_table_name");
	$wpdb->query("DROP TABLE IF EXISTS $term_results_table_name");

	$post_status = array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash');

	$search_form_query = new WP_Query('post_type=search-filter-widget&post_status='.implode(",",$post_status).'&posts_per_page=-1');
	$search_forms = $search_form_query->get_posts();
	foreach($search_forms as $search_form)
	{
		wp_delete_post( $search_form->ID, true );
	}

	// flush rewrite rules in order to remove the rewrite rule
	global $wp_rewrite;
	$wp_rewrite->flush_rules();

}

