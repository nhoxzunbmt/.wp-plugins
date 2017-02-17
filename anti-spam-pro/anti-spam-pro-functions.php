<?php

if ( ! defined( 'ABSPATH' ) ) { // prevent full path disclosure
	exit;
}


function antispampro_default_settings() {
	$settings = array(
		'send_automatic_spam_to_email' => 0,
		'email' => get_option('admin_email'),
		'code' => antispampro_random_string_generator(),
		'input_name_suffix' => antispampro_random_string_generator(),
		'allow_trackbacks' => 0,

		'block_manual_spam' => 0,
		'send_manual_spam_to_email' => 0,
		'max_spam_points' => 2,
		'max_links_number' => 3,
		'max_comment_length' => 2000,
		'spam_words' => 'viagra, cialis'
	);
	return $settings;
}


function antispampro_get_settings() {
	$antispampro_settings = (array) get_option('antispampro_settings');
	$default_settings = antispampro_default_settings();
	$antispampro_settings = array_merge($default_settings, $antispampro_settings); // set empty options with default values
	return $antispampro_settings;
}


function antispampro_random_string_generator($readable = 1, $length = 6) {
	$random_string = '';
	if ($readable) { // create readable random string like 'suzuki'
		$chars_b = 'bcdfghjklmnpqrstvwxz';
		$chars_a = 'aeiouy';
		$ab = 'b';
		for ($i = 0; $i < $length; $i++) {
			if ($ab == 'b') {
				$random_string .= $chars_b[rand(0, strlen($chars_b) - 1)];
				$ab = 'a';
			} else {
				$random_string .= $chars_a[rand(0, strlen($chars_a) - 1)];
				$ab = 'b';
			}
		}
	} else { // create fully random string like 'q3WLtN'
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		for ($i = 0; $i < $length; $i++) {
			$random_string .= $chars[rand(0, strlen($chars) - 1)];
		}
	}
	return $random_string;
}


function antispampro_log_stats() {
	$antispam_stats = get_option('antispam_stats', array());
	if (array_key_exists('blocked_total', $antispam_stats)){
		$antispam_stats['blocked_total']++;
	} else {
		$antispam_stats['blocked_total'] = 1;
	}
	update_option('antispam_stats', $antispam_stats);
}


function antispampro_string_contains_words($string, $words) {
	// spam word 'cialis' will not match word 'socialism'
	// but spam word 'Viagra' will match string 'buy VIAGRA!!!'

	$match_flag = 0;

	$string = str_replace('.', ' ', $string);
	$string = str_replace(',', ' ', $string);
	$string = str_replace(':', ' ', $string);
	$string = str_replace(';', ' ', $string);
	$string = str_replace('!', ' ', $string);
	$string = str_replace('?', ' ', $string);
	$string = str_replace('   ', ' ', $string); // reduce number of elements in array to check
	$string = str_replace('  ', ' ', $string);

	$string_array = explode(' ', strtolower($string));

	$words_array = explode(',', strtolower($words));

	foreach ($string_array as $string_item) {
		foreach ($words_array as $word_item) {
			if ( !empty($string_item) && !empty($word_item) && trim($string_item) == trim($word_item)) {
				$match_flag = 1;
			}
		}
	}

	return $match_flag;
}