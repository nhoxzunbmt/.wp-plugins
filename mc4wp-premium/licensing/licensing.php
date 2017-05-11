<?php

defined('ABSPATH') or exit;

global $mc4wp_license_manager;

/**
 * @ignore
 */
function _mc4wp_premium_show_license_form() {
	global $mc4wp_license_manager;
	echo '<div class="mc4wp-license-form medium-margin">';
	$mc4wp_license_manager->show_license_form(false);
	echo '</div>';
}

// only load in admin section
if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
	$product = new MC4WP_Product();
	$license_manager = new DVK_Plugin_License_Manager($product);
	$license_manager->setup_hooks();
	$mc4wp_license_manager = $license_manager;

	add_action('mc4wp_admin_before_other_settings', '_mc4wp_premium_show_license_form');
}
