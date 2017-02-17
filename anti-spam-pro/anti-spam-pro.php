<?php
/*
Plugin Name: Anti-spam Pro
Plugin URI: http://codecanyon.net/item/antispam-pro/6491169
Description: No spam in comments. No captcha. Extended Pro version of Anti-spam plugin - http://wordpress.org/plugins/anti-spam/
Version: 4.1
Author: webvitaly
Author URI: http://profiles.wordpress.org/webvitaly/
License: GPLv3
*/

if ( ! defined( 'ABSPATH' ) ) { // prevent full path disclosure
	exit;
}

define('ANTISPAM_PRO_VERSION', '4.1');

include('anti-spam-pro-functions.php');
include('anti-spam-pro-settings.php');


function antispampro_enqueue_script() {
	$antispampro_settings = antispampro_get_settings();

	if (is_singular() && comments_open()) { // load script only for pages with comments form
		wp_enqueue_script('antispampro-script', plugins_url('/js/anti-spam-pro-4.1.js', __FILE__), array('jquery'), null, true);
		// add js vars for creating antispampro input using js if theme does not use 'comment_form' action
		wp_localize_script('antispampro-script', 'antispampro_vars', 
			array(
				'code' => $antispampro_settings['code'],
				'input_name_suffix' => $antispampro_settings['input_name_suffix']
			)
		);
	}
}
add_action('wp_enqueue_scripts', 'antispampro_enqueue_script');


function antispampro_form_part() {
	$antispampro_settings = antispampro_get_settings();
	$rn = "\r\n"; // .chr(13).chr(10)

	if ( ! is_user_logged_in()) { // add anti-spam fields only for not logged in users
		echo '		<p class="antispam-group antispam-group-q" style="clear: both;">
			<label>Copy and paste this code: <strong class="antspmpro-input-a">'.$antispampro_settings['code'].'</strong> <span class="required">*</span></label>
			<input type="hidden" name="antspmpro-a" class="antispam-control antispam-control-a" value="'.$antispampro_settings['code'].'" />
			<input type="text" name="antspmpro-q-'.$antispampro_settings['input_name_suffix'].'" class="antispam-control antispam-control-q" value="'.ANTISPAM_PRO_VERSION.'" />
		</p>'.$rn; // question (hidden with js)

		echo '		<p class="antispam-group antispam-group-e" style="display: none;">
			<label>Leave this field empty</label>
			<input type="text" name="antspmpro-e-email-url-website-'.$antispampro_settings['input_name_suffix'].'" class="antispam-control antispam-control-e" value="" />
		</p>'.$rn; // empty field (hidden with css); trap for spammers because many bots will try to put email or url here
	}
}
add_action('comment_form', 'antispampro_form_part'); // add anti-spam input to the comment form


function antispampro_check_comment($commentdata) {
	$settings = antispampro_get_settings();
	$rn = "\r\n"; // .chr(13).chr(10)

	extract($commentdata);

	$message_prepend = '<p><strong><a href="javascript:window.history.back()">Go back</a></strong> and try again.</p>';
	$message_error = '';
	$message_error_manual = '';

	if ($settings['send_automatic_spam_to_email'] || $settings['send_manual_spam_to_email']) {

		$post = get_post($comment->comment_post_ID);
		$message_info  = 'Spam for post: "'.$post->post_title.'"' . $rn;
		$message_info .= get_permalink($comment->comment_post_ID) . $rn.$rn;

		$message_info .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . $rn;
		$message_info .= 'User agent: ' . $_SERVER['HTTP_USER_AGENT'] . $rn;
		$message_info .= 'Referer: ' . $_SERVER['HTTP_REFERER'] . $rn.$rn;

		$message_info .= 'Comment data:'.$rn; // lets see what comment data spammers try to submit
		foreach ($commentdata as $key => $value) {
			$message_info .= '$commentdata['.$key. '] = '.$value.$rn;
		}
		$message_info .= $rn.$rn;

		$message_info .= 'Post vars:'.$rn; // lets see what post vars spammers try to submit
		foreach ($_POST as $key => $value) {
			$message_info .= '$_POST['.$key. '] = '.$value.$rn;
		}
		$message_info .= $rn.$rn;

		$message_info .= 'Cookie vars:'.$rn; // lets see what cookie vars spammers try to submit
		foreach ($_COOKIE as $key => $value) {
			$message_info .= '$_COOKIE['.$key. '] = '.$value.$rn;
		}
		$message_info .= $rn.$rn;

		$message_append = '-----------------------------'.$rn;
		$message_append .= 'This spam comment is rejected by Anti-spam Pro plugin - http://codecanyon.net/item/antispam-pro/6491169'.$rn;
		$message_append .= 'You may disable these notifications in the [Settings] - [Anti-spam Pro] section.'.$rn;
	}

	if ( ! is_user_logged_in() && $comment_type != 'pingback' && $comment_type != 'trackback') { // logged in user is not a spammer
		$automatic_spam_flag = false;
		$spam_points = 0;

		if (trim($_POST['antspmpro-q-'.$settings['input_name_suffix']]) != $settings['code']) { // answer is wrong - it is spam
			$automatic_spam_flag = true;
			if (empty($_POST['antspmpro-q-'.$settings['input_name_suffix']])) { // empty answer - it is spam
				$message_error .= 'Error: empty answer. ['.esc_attr( $_POST['antspmpro-q-'.$settings['input_name_suffix']] ).']<br> '.$rn;
			} else {
				$message_error .= 'Error: answer is wrong. ['.esc_attr( $_POST['antspmpro-q-'.$settings['input_name_suffix']] ).']<br> '.$rn;
			}
		}

		if ( ! empty($_POST['antspmpro-e-email-url-website-'.$settings['input_name_suffix']])) { // field is not empty - it is spam
			$automatic_spam_flag = true;
			$message_error .= 'Error: field should be empty. ['.esc_attr( $_POST['antspmpro-e-email-url-website-'.$settings['input_name_suffix']] ).']<br> '.$rn;
		}

		
		if ($settings['block_manual_spam']) { // check for manual spam

			if ( ! empty($commentdata['comment_author_url'])) { // probably spam
				$spam_points += 1;
				$message_error_manual .= 'Info: URL field is not empty. +1 spam point.<br> '.$rn;
			}

			$links_count = substr_count($commentdata['comment_content'], 'http');
			if ($links_count > $settings['max_links_number']) { // probably spam
				$spam_points += 1;
				$message_error_manual .= 'Info: comment contains too many links ['.$links_count.' links; max = '.$settings['max_links_number'].']. +1 spam point.<br> '.$rn;
			}

			if (strpos($commentdata['comment_content'], '</') !== false) { // probably spam
				$spam_points += 1;
				$message_error_manual .= 'Info: comment contains html. +1 spam point.<br> '.$rn;
			}

			$comment_length = strlen($commentdata['comment_content']);
			if ($comment_length > $settings['max_comment_length']) { // probably spam
				$spam_points += 1;
				$message_error_manual .= 'Info: comment is too long ['.$comment_length.' chars; max = '.$settings['max_comment_length'].']. +1 spam point.<br> '.$rn;
			}

			if (strpos($commentdata['comment_content'], 'rel=\"nofollow\"') !== false) { // probably spam
				$spam_points += 1;
				$message_error_manual .= 'Info: comment contains rel="nofollow" code. +1 spam point.<br> '.$rn;
			}

			if (strpos($commentdata['comment_content'], '[/url]') !== false) { // probably spam
				$spam_points += 1;
				$message_error_manual .= 'Info: comment contains [/url] code. +1 spam point.<br> '.$rn;
			}

			if (antispampro_string_contains_words($commentdata['comment_content'], $settings['spam_words'])) {
				$spam_points += 1;
				$message_error_manual .= 'Info: comment contains at least 1 of the spam words: '.$settings['spam_words'].'. +1 spam point.<br> '.$rn;
			}

			if ($spam_points > 0) {
				$message_error_manual .= 'Total spam points = '.$spam_points.' [max = '.$settings['max_spam_points'].']<br> '.$rn;
			}
		}

		if ($automatic_spam_flag) { // it is automatic spam
			$message_error .= '<strong>Comment was blocked because it is automatic spam.</strong><br> ';
			if ($settings['send_automatic_spam_to_email']) { // remove this extra check !!!
				$message_subject = 'Automatic spam comment on site ['.get_bloginfo('url').']'; // email subject
				$message = '';
				$message .= $message_error . $rn.$rn;
				$message .= $message_info; // spam comment, post, cookie and other data
				$message .= $message_append;
				@wp_mail($settings['email'], $message_subject, $message); // send spam comment to admin email
			}
			antispampro_log_stats();
			wp_die($message_prepend . $message_error); // die - do not send comment and show errors
		} elseif ($spam_points > $settings['max_spam_points'] && $settings['block_manual_spam']) { // it is manual spam
			$message_error_manual .= '<strong>Comment was blocked because it is manual spam.</strong><br> ';
			if ($settings['send_manual_spam_to_email'] && $spam_points > $settings['max_spam_points']) {
				$message_subject = 'Manual spam on site ['.get_bloginfo('url').']'; // email subject
				$message = '';
				$message .= $message_error_manual . $rn.$rn;
				$message .= $message_info; // spam comment, post, cookie and other data
				$message .= $message_append;
				@wp_mail($settings['email'], $message_subject, $message); // send spam comment to admin email
			}
			antispampro_log_stats();
			wp_die($message_prepend . $message_error_manual); // die - do not send comment and show errors
		}
	}

	// trackbacks almost not used by users, but mostly used by spammers; pingbacks are always enabled
	// more about the difference between trackback and pingback - http://web-profile.com.ua/web/trackback-vs-pingback/
	if ( ! $settings['allow_trackbacks']) { // if trackbacks are blocked (pingbacks are alowed)
		if ($comment_type == 'trackback') { // if trackbacks (|| $comment_type == 'pingback')
			$message_error .= 'Error: trackbacks are disabled.<br> ';
			if ($settings['send_automatic_spam_to_email']) {
				$message_subject = 'Trackback spam on site ['.get_bloginfo('url').']'; // email subject
				$message = '';
				$message .= $message_error . $rn.$rn;
				$message .= $message_info; // spam comment, post, cookie and other data
				$message .= $message_append;
				@wp_mail($settings['email'], $message_subject, $message); // send trackback comment to admin email
			}
			antispampro_log_stats();
			wp_die($message_prepend . $message_error); // die - do not send trackback
		}
	}

	return $commentdata; // if comment does not looks like spam
}

if ( ! is_admin()) {
	add_filter('preprocess_comment', 'antispampro_check_comment', 1);
}
