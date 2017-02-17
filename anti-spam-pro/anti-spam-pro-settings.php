<?php
/*
Anti-spam Pro settings code
used WordPress Settings API - http://codex.wordpress.org/Settings_API
*/

if ( ! defined( 'ABSPATH' ) ) { // prevent full path disclosure
	exit;
}


function antispampro_menu() { // add menu item
	add_options_page('Anti-spam Pro', 'Anti-spam Pro', 'manage_options', 'anti-spam-pro', 'antispampro_settings');
}
add_action('admin_menu', 'antispampro_menu');


function antispampro_admin_init() {
	register_setting('antispampro_settings_group', 'antispampro_settings', 'antispampro_settings_validate');

	add_settings_section('antispampro_settings_automatic_section', '', 'antispampro_section_callback', 'antispampro_automatic_page');
	add_settings_section('antispampro_settings_manual_section', '', 'antispampro_section_callback', 'antispampro_manual_page');

	add_settings_field('send_automatic_spam_to_email', 'Automatic spam log', 'antispampro_field_send_automatic_spam_to_email_callback', 'antispampro_automatic_page', 'antispampro_settings_automatic_section');
	add_settings_field('email', 'Log email', 'antispampro_field_email_callback', 'antispampro_automatic_page', 'antispampro_settings_automatic_section');
	add_settings_field('code', 'Code for copy&paste', 'antispampro_field_code_callback', 'antispampro_automatic_page', 'antispampro_settings_automatic_section');
	add_settings_field('input_name_suffix', 'Input name suffix', 'antispampro_field_input_name_suffix_callback', 'antispampro_automatic_page', 'antispampro_settings_automatic_section');
	add_settings_field('allow_trackbacks', 'Trackback Spam', 'antispampro_field_allow_trackbacks_callback', 'antispampro_automatic_page', 'antispampro_settings_automatic_section');

	add_settings_field('block_manual_spam', 'Manual spam', 'antispampro_field_block_manual_spam_callback', 'antispampro_manual_page', 'antispampro_settings_manual_section');
	add_settings_field('send_manual_spam_to_email', 'Manual spam log', 'antispampro_field_send_manual_spam_to_email_callback', 'antispampro_manual_page', 'antispampro_settings_manual_section');
	add_settings_field('max_spam_points', 'Max spam points', 'antispampro_field_max_spam_points_callback', 'antispampro_manual_page', 'antispampro_settings_manual_section');
	add_settings_field('max_links_number', 'Max links number', 'antispampro_field_max_links_number_callback', 'antispampro_manual_page', 'antispampro_settings_manual_section');
	add_settings_field('max_comment_length', 'Max comment length', 'antispampro_field_max_comment_length_callback', 'antispampro_manual_page', 'antispampro_settings_manual_section');
	add_settings_field('spam_words', 'Spam words', 'antispampro_field_spam_words_callback', 'antispampro_manual_page', 'antispampro_settings_manual_section');
}
add_action('admin_init', 'antispampro_admin_init');


function antispampro_settings_init() { // set default settings
	global $antispampro_settings;
	$antispampro_settings = antispampro_get_settings();
	update_option('antispampro_settings', $antispampro_settings);
}
add_action('admin_init', 'antispampro_settings_init');


function antispampro_settings_validate($input) {
	$default_settings = antispampro_get_settings();

	// checkboxes
	$output['send_automatic_spam_to_email'] = $input['send_automatic_spam_to_email'];
	$output['allow_trackbacks'] = $input['allow_trackbacks'];

	if (isset($input['email'])) {
		if (is_email($input['email'])) {
			$output['email'] = trim($input['email']);
		} else {
			$output['email'] = $default_settings['email'];
			add_settings_error('antispampro_settings', 'invalid_email', 'You have entered an invalid e-mail address.');
		}
	}

	if (isset($input['input_name_suffix'])) {
		if (!empty($input['input_name_suffix']) && !preg_match('/[^A-Za-z0-9]/', $input['input_name_suffix'])) { // not empty and contain only letters and numbers
			$output['input_name_suffix'] = trim($input['input_name_suffix']);
		} else {
			$output['input_name_suffix'] = $default_settings['input_name_suffix'];
			add_settings_error('antispampro_settings', 'invalid_input_name_suffix', 'You have entered an invalid input name suffix.');
		}
	}

	if (isset($input['code'])) {
		if (!empty($input['code']) && !preg_match('/[^A-Za-z0-9]/', $input['code'])) { // not empty and contain only letters and numbers
			$output['code'] = trim($input['code']);
		} else {
			$output['code'] = $default_settings['code'];
			add_settings_error('antispampro_settings', 'invalid_code', 'You have entered an invalid code.');
		}
	}

	// checkboxes
	$output['block_manual_spam'] = $input['block_manual_spam'];
	$output['send_manual_spam_to_email'] = $input['send_manual_spam_to_email'];

	// numbers sanitazation
	if (isset($input['max_spam_points'])) {
		if (is_numeric($input['max_spam_points']) && $input['max_spam_points']>=0) {
			$output['max_spam_points'] = trim($input['max_spam_points']);
		} else {
			$output['max_spam_points'] = $default_settings['max_spam_points'];
			add_settings_error('antispampro_settings', 'invalid_number', 'You have entered an invalid number.');
		}
	}

	if (isset($input['max_links_number'])) {
		if (is_numeric($input['max_links_number']) && $input['max_links_number']>=0) {
			$output['max_links_number'] = trim($input['max_links_number']);
		} else {
			$output['max_links_number'] = $default_settings['max_links_number'];
			add_settings_error('antispampro_settings', 'invalid_number', 'You have entered an invalid number.');
		}
	}

	if (isset($input['max_comment_length'])) {
		if (is_numeric($input['max_comment_length']) && $input['max_comment_length']>=0) {
			$output['max_comment_length'] = trim($input['max_comment_length']);
		} else {
			$output['max_comment_length'] = $default_settings['max_comment_length'];
			add_settings_error('antispampro_settings', 'invalid_number', 'You have entered an invalid number.');
		}
	}

	$output['spam_words'] = trim($input['spam_words']);

	return $output;
}


function antispampro_section_callback() { // Anti-spam Pro settings description
	echo '';
}


function antispampro_field_send_automatic_spam_to_email_callback() {
	$settings = antispampro_get_settings();
	echo '<label><input type="checkbox" name="antispampro_settings[send_automatic_spam_to_email]" '.checked(1, $settings['send_automatic_spam_to_email'], false).' value="1" />';
	echo ' Send blocked automatic spam comments to email</label>';
	echo '<p class="description">Useful for testing how plugin works</p>';
}


function antispampro_field_email_callback() {
	$settings = antispampro_get_settings();
	echo '<input type="email" name="antispampro_settings[email]" class="regular-text" value="'.$settings['email'].'" required="required" />';
	echo '<p class="description">All blocked spam comments will be sent to this email</p>';
}


function antispampro_field_code_callback() {
	$settings = antispampro_get_settings();
	echo '<input type="text" pattern="^[A-Za-z0-9]+$" name="antispampro_settings[code]" class="regular-text" value="'.$settings['code'].'" required="required" />';
	echo '<p class="description">Unique for each site for adding extra level of security. Random sample: '.antispampro_random_string_generator().'</p>';
}


function antispampro_field_input_name_suffix_callback() {
	$settings = antispampro_get_settings();
	echo '<input type="text" pattern="^[A-Za-z0-9]+$" name="antispampro_settings[input_name_suffix]" class="regular-text" value="'.$settings['input_name_suffix'].'" required="required" />';
	echo '<p class="description">Unique for each site for adding extra level of security. Random sample: '.antispampro_random_string_generator().'</p>';
}


function antispampro_field_allow_trackbacks_callback() {
	$settings = antispampro_get_settings();
	echo '<label><input type="checkbox" name="antispampro_settings[allow_trackbacks]" '.checked(1, $settings['allow_trackbacks'], false).' value="1" />';
	echo ' Allow trackbacks</label>';
	echo '<p class="description"><a href="http://web-profile.com.ua/web/trackback-vs-pingback/" target="_blank">Difference between trackbacks and pingbacks</a>. Pingbacks are always enabled.</p>';
}

// ==================== manual spam ====================

function antispampro_field_block_manual_spam_callback() {
	$settings = antispampro_get_settings();
	echo '<label><input type="checkbox" name="antispampro_settings[block_manual_spam]" '.checked(1, $settings['block_manual_spam'], false).' value="1" />';
	echo ' Block manual spam using "spam-points algorithm"</label>';
	echo '<p class="description">More info about "<a href="http://codecanyon.net/item/antispam-pro/6491169?ref=webvitaly" target="_blank">spam-points algorithm</a>"</p>';
}


function antispampro_field_send_manual_spam_to_email_callback() {
	$settings = antispampro_get_settings();
	echo '<label><input type="checkbox" name="antispampro_settings[send_manual_spam_to_email]" '.checked(1, $settings['send_manual_spam_to_email'], false).' value="1" />';
	echo ' Send blocked manual spam comments to email</label>';
	echo '<p class="description">Useful for testing how plugin works</p>';
}


function antispampro_field_max_spam_points_callback() {
	$settings = antispampro_get_settings();
	$default_settings = antispampro_default_settings();
	echo '<input type="number" min="0" step="1" name="antispampro_settings[max_spam_points]" class="regular-text" value="'.$settings['max_spam_points'].'" required="required" />';
	echo '<p class="description">If more - it is spam. Range: 0-8. Default: '.$default_settings['max_spam_points'].'</p>';
}


function antispampro_field_max_links_number_callback() {
	$settings = antispampro_get_settings();
	$default_settings = antispampro_default_settings();
	echo '<input type="number" min="0" step="1" name="antispampro_settings[max_links_number]" class="regular-text" value="'.$settings['max_links_number'].'" required="required" />';
	echo '<p class="description">+1 spam point if more. Default: '.$default_settings['max_links_number'].'</p>';
}


function antispampro_field_max_comment_length_callback() {
	$settings = antispampro_get_settings();
	$default_settings = antispampro_default_settings();
	echo '<input type="number" min="0" step="1" name="antispampro_settings[max_comment_length]" class="regular-text" value="'.$settings['max_comment_length'].'" required="required" />';
	echo '<p class="description">+1 spam point if more. Default: '.$default_settings['max_comment_length'].'</p>';
}


function antispampro_field_spam_words_callback() {
	$settings = antispampro_get_settings();
	$default_settings = antispampro_default_settings();
	echo '<textarea name="antispampro_settings[spam_words]" class="large-text" style="width: 25em;">'.$settings['spam_words'].'</textarea>';
	echo '<p class="description">+1 spam point if comment contains at least 1 word from this list. <br>Comma separated and case-insensitive. Default: '.$default_settings['spam_words'].'</p>';
}


function antispampro_settings() {
	$antispam_stats = get_option('antispam_stats', array());
	$blocked_total = $antispam_stats['blocked_total'];
	if (empty($blocked_total)) {
		$blocked_total = 0;
	}
	?>
	<div class="wrap">
		
		<h2><span class="dashicons dashicons-shield"></span> Anti-spam Pro</h2>

		<div class="antispam-panel-info">
			<p style="margin: 0;">
				<span class="dashicons dashicons-chart-bar"></span>
				<strong><?php echo $blocked_total; ?></strong> spam comments were blocked by <a href="http://codecanyon.net/item/antispam-pro/6491169?ref=webvitaly" target="_blank">Anti-spam Pro</a> plugin so far.
			</p>
		</div>

		<h2 class="nav-tab-wrapper">
			<a href="#" class="nav-tab antispampro-tab-automatic">Automatic Spam</a>
			<a href="#" class="nav-tab antispampro-tab-manual">Manual Spam</a>
		</h2>

		<form method="post" action="options.php">
			<?php settings_fields('antispampro_settings_group'); ?>
			<div class="antispampro-group-automatic">
				<?php do_settings_sections('antispampro_automatic_page'); ?>
			</div>
			<div class="antispampro-group-manual">
				<?php do_settings_sections('antispampro_manual_page'); ?>
			</div>
			<?php submit_button(); ?>
		</form>

		<script>
			jQuery(function($){
				$('.antispampro-tab-automatic').click(function(event) {
					event.preventDefault();
					$(this).addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
					$('.antispampro-group-automatic').slideDown();
					$('.antispampro-group-manual').slideUp();
				});

				$('.antispampro-tab-manual').click(function(event) {
					event.preventDefault();
					$(this).addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
					$('.antispampro-group-manual').slideDown();
					$('.antispampro-group-automatic').slideUp();
				});

				$('.antispampro-tab-automatic').click();
			});
		</script>

	</div>
	<?php
}
