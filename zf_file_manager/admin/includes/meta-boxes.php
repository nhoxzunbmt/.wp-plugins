<?php

add_action('add_meta_boxes', 'b5_file_manager_add_custom_box');
function b5_file_manager_add_custom_box() {
    global $current_screen;

	// Adding layout meta users box for folder
	add_meta_box('folder-access', __('Configure access', B5FILEMANAGER_PLUGIN_NAME), 'b5_file_manager_folder_access', 'folder', 'side', 'default');

    // Adding layout meta files box for folder
    add_meta_box('folder-files', __('Select files', B5FILEMANAGER_PLUGIN_NAME), 'b5_meta_select_files', 'folder', 'normal', 'default');

    // Adding layout select external files box for folder
    add_meta_box('folder-external-files', __('External files', B5FILEMANAGER_PLUGIN_NAME), 'b5_meta_select_external_files', 'folder', 'normal', 'default');

    // Adding layout meta thumbnail for file
    add_meta_box('file-thumbnail', __('Featured image', B5FILEMANAGER_PLUGIN_NAME), 'b5_file_manager_meta_select_thumbnail', 'attachment', 'side', 'default');

    // Adding layout media external file
    add_meta_box('external-file', __('External file details', B5FILEMANAGER_PLUGIN_NAME), 'b5_file_manager_meta_details_external_files', 'external_file', 'normal', 'default');

    // Adding layout downloads file
    if($current_screen->action != 'add') {
        add_meta_box('file-downloads', __('Downloads', B5FILEMANAGER_PLUGIN_NAME), 'b5_file_manager_meta_downloads', 'external_file', 'side', 'default');
        add_meta_box('file-downloads', __('Downloads', B5FILEMANAGER_PLUGIN_NAME), 'b5_file_manager_meta_downloads', 'attachment', 'side', 'default');
        add_meta_box('file-downloads', __('Downloads', B5FILEMANAGER_PLUGIN_NAME), 'b5_file_manager_meta_folder_downloads', 'folder', 'side', 'default');

        // Adding layout meta parent box for folder
        add_meta_box('folder-child', __('Subfolders', B5FILEMANAGER_PLUGIN_NAME), 'b5_meta_show_folders_child', 'folder', 'normal', 'default');
    }

    // Adding layout meta files box for folder
    add_meta_box('users-group', __('Users', B5FILEMANAGER_PLUGIN_NAME), 'b5_meta_users_group', 'users_group_file', 'normal', 'default');

    // Adding extra fields
    $extra_fields = get_option('b5-file-manager-extra-fields', array());
    if(count($extra_fields) > 0) {
        add_meta_box('external-file-extra-fields', __('File Manager extra fields', B5FILEMANAGER_PLUGIN_NAME), 'b5_meta_extra_fields', 'external_file', 'normal', 'default');
        add_meta_box('media-extra-fields', __('File Manager extra fields', B5FILEMANAGER_PLUGIN_NAME), 'b5_meta_extra_fields', 'attachment', 'normal', 'default');
    }
}

/****************************************************************************************/
function b5_file_manager_folder_access() {
    global $post, $current_screen;

    $users_meta = b5_file_manager_folder_users($post->ID);
    $parent_users = b5_file_manager_folder_users($post->post_parent);
    $users_group_meta = b5_file_manager_folder_group($post->ID);
    $parent_users_group = b5_file_manager_folder_group($post->post_parent);
    ?>

    <div class="b5-admin-tabs-container" style="display: <?php echo b5_file_manager_get_folder_access($post->ID) ? 'none' : 'block';?>">
        <ul class="b5-admin-tabs">
            <li class="b5-tabs"><a href="#folder-access-group"><?php _e('Users group', B5FILEMANAGER_PLUGIN_NAME);?></a></li>
            <li><a href="#folder-access-users"><?php _e('All users', B5FILEMANAGER_PLUGIN_NAME);?></a></li>
        </ul>
        <div id="folder-access-group" class="b5-tabs-panel">
            <ul class="b5-tabs-list">
                <li>
                    <label class="b5-admin-all-groups"><input type="checkbox" id="folder_users_group_all" /><?php _e('Select all', B5FILEMANAGER_PLUGIN_NAME);?></label>
                </li>
                <?php foreach (get_posts(array('post_type' => 'users_group_file', 'numberposts' => -1)) as $group) {
                    $group_name = !empty($group->post_title) ? $group->post_title : __("Unnamed group", B5FILEMANAGER_PLUGIN_NAME);?>
                    <li>
                        <label><input type="checkbox" class="folder_users_group_<?php echo $group->ID;?>" name="folder-users-group[]" <?php checked(in_array($group->ID, $users_group_meta), true, true);?> <?php /*disabled(!$post->post_parent ? false : !in_array($group->ID, $parent_users_group), true, true);*/?> value="<?php echo $group->ID;?>" /><?php echo $group_name;?></label>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div id="folder-access-users" class="b5-tabs-panel" style="display:none;">
            <ul class="b5-tabs-list">
                <li>
                    <label class="b5-admin-all-users"><input type="checkbox" id="folder_user_all" /><?php _e('Select all', B5FILEMANAGER_PLUGIN_NAME);?></label>
                </li>
                <?php foreach (get_users(array('fields' => array('ID','user_login','display_name'))) as $user) { ?>
                    <li>
                        <label><input type="checkbox" class="folder-user-<?php echo $user->ID;?>" name="folder-users[]" <?php checked(in_array($user->ID, $users_meta), true, true);?> <?php /*disabled(!in_array($user->ID, $parent_users), true, true);*/?> value="<?php echo $user->ID;?>" /><?php echo $user->display_name;?></label>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <label><input type="checkbox" name="folder-users-access" id="folder_users_access" <?php checked(b5_file_manager_get_folder_access($post->ID), true);?> /><?php _e('Make this folder public', B5FILEMANAGER_PLUGIN_NAME);?></label>
<?php }

add_action('save_post', 'b5_file_manager_save_users_group_meta');
function b5_file_manager_save_users_group_meta($post_id) {
    // Checks save status
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);

    // Exits script depending on save status
    if($is_autosave || $is_revision) {
        return;
    }

    if(isset($_POST['folder-users-access'])) {
        update_post_meta($post_id, 'b5_file_manager_folder_access', true);
    } else {
        delete_post_meta($post_id, 'b5_file_manager_folder_access');

        // Checks for input and sanitizes/saves if needed
        if(isset($_POST['folder-users-group'])) {
            update_post_meta($post_id, 'b5_file_manager_folder_groups', $_POST['folder-users-group']);
        } else {
            delete_post_meta($post_id, 'b5_file_manager_folder_groups');
        }

        // Checks for input and sanitizes/saves if needed
        if(isset($_POST['folder-users'])) {
            update_post_meta($post_id, 'b5_file_manager_folder_users', $_POST['folder-users']);
        } else {
            delete_post_meta($post_id, 'b5_file_manager_folder_users');
        }
    }
}

add_action('wp_ajax_folder_access_public', 'b5_file_manager_folder_access_public');
function b5_file_manager_folder_access_public() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $value = $_POST['public_value']=='on' ? true : false;
    if($value) {
        update_post_meta($post_id, 'b5_file_manager_folder_access', true);
    } else {
        delete_post_meta($post_id, 'b5_file_manager_folder_access');
    }
    wp_send_json_success();
}

add_action('delete_post', 'b5_file_manager_delete_folder');
function b5_file_manager_delete_folder($folder_id) {
    global $post_type;
    if ($post_type != 'folder') return;

    b5_file_manager_delete_folder_downloads($folder_id);
}

/****************************************************************************************/
function b5_file_manager_meta_select_thumbnail() {

    global $post;

    $html = b5_file_manager_get_uploaded_thumbnail();

    $classesAdd = implode(' ', array('button', 'b5-file-thumbnail-advanced-upload', 'hide-if-no-js', 'new-files'));
    $classesRemove = implode(' ', array('button', 'b5-delete-thumbnail-file'));

    $file = get_post_meta($post->ID, 'b5_file_manager_thumbnail');

    if(count($file) == 0) {
        $attach_nonce = wp_create_nonce("b5-attach-file_b5_file_manager_thumbnail");
        $html .= "<a href='#' class='{$classesAdd}' data-attach_file_thumbnail_nonce={$attach_nonce}>".__('Set featured image', B5FILEMANAGER_PLUGIN_NAME)."</a>";

        $attach_nonce = wp_create_nonce("b5-delete-file_b5_file_manager_files");
        $html .= "<a href='#' style='display: none' class='{$classesRemove}' data-attach_file_thumbnail_nonce={$attach_nonce}>".__('Remove featured image', B5FILEMANAGER_PLUGIN_NAME)."</a>";
    } else {
        $attach_nonce = wp_create_nonce("b5-attach-file_b5_file_manager_thumbnail");
        $html .= "<a href='#' style='display: none' class='{$classesAdd}' data-attach_file_thumbnail_nonce={$attach_nonce}>".__('Set featured image', B5FILEMANAGER_PLUGIN_NAME)."</a>";

        $attach_nonce = wp_create_nonce("b5-delete-file_b5_file_manager_files");
        $html .= "<a href='#' class='{$classesRemove}' data-attach_file_thumbnail_nonce={$attach_nonce}>".__('Remove featured image', B5FILEMANAGER_PLUGIN_NAME)."</a>";
    }

    echo $html;
}

function b5_file_manager_get_uploaded_thumbnail() {
    global $post;
    $delete_nonce = wp_create_nonce("b5-delete-file_b5_file_manager_files");
    $classes = array('b5-file', 'b5-uploaded');

    $ul = '<ul class="%s" data-field_id="%s" data-delete_nonce="%s">';
    $html = sprintf(
        $ul,
        implode(' ', $classes),
        'b5_file_manager_thumbnail',
        $delete_nonce
    );

    $file = get_post_meta($post->ID, 'b5_file_manager_thumbnail');

    foreach ($file as $attachment_id) {
        $html .= b5_file_manager_thumbnail_get_html($attachment_id);
    }

    $html .= '</ul>';

    return $html;
}

function b5_file_manager_thumbnail_get_html($attachment_id) {
    $li = '<li class="b5-thumbnail" data-attachment_id="%s">
			  <div class="b5-icon">%s</div>
		   </li>';

    $size = 266;

    return sprintf(
        $li,
        $attachment_id,
        wp_get_attachment_image($attachment_id, array(is_integer((int)$size)?$size:70, is_integer((int)$size)?$size:70), true)
    );
}

add_action('wp_ajax_b5_attach_thumbnail_file', 'b5_file_manager_attach_thumbnail_file');
function b5_file_manager_attach_thumbnail_file() {
    $post_id = is_numeric($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;
    $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : 0;
    $attachment_id = isset($_POST['attachment_id']) ? $_POST['attachment_id'] : '';

    check_ajax_referer("b5-attach-file_b5_file_manager_thumbnail");
    add_post_meta($post_id, $field_id, $attachment_id, false);

    wp_send_json_success();
}

add_action('print_media_templates', 'print_b5_thumbnail_templates');
function print_b5_thumbnail_templates() {?>
    <script id="tmpl-b5-file-thumbnail-advanced" type="text/html">
        <# _.each(attachments, function(attachment) { #>
            <li class="b5-thumbnail" data-attachment_id="{{{ attachment.id }}}">
                <div class="b5-icon"><img src="<# if (attachment.type == 'image') { if (attachment.sizes.medium) { #> {{{ attachment.sizes.medium.url }}} <# } else { #> {{{ attachment.sizes.full.url }}} <# } } else { #> {{{ attachment.icon }}} <# } #>"></div>
            </li>
        <# } ); #>
    </script>
<?php
}

/****************************************************************************************/
function b5_meta_select_external_files() {

    $html = '';

    $classes = array('button', 'b5-file-external-upload');
    $classes = implode(' ', $classes);

    $attach_nonce = wp_create_nonce("b5-attach-file_b5_file_external");

    $html .= b5_meta_external_files_html();
    $html .= "<a href='#' id='b5-file-external-upload-id' class='{$classes}' data-attach_file_nonce='{$attach_nonce}' title='".__('External Files', B5FILEMANAGER_PLUGIN_NAME)."'>".__('Select External Files', B5FILEMANAGER_PLUGIN_NAME)."</a>";

    $classes = array('button', 'b5-file-advanced-remove-all', 'hide-if-no-js');
    $classes = implode(' ', $classes);

    $remove_all_nonce = wp_create_nonce("b5-remove-all-file_b5_file_manager_files");

    $html .= "<a href='#' class='{$classes}' data-field_id='b5_external_file' data-remove_all_file_nonce='{$remove_all_nonce}' title='".__('Remove All External Files', B5FILEMANAGER_PLUGIN_NAME)."'>".__('Remove All External Files', B5FILEMANAGER_PLUGIN_NAME)."</a>";

    echo $html;
}

function b5_meta_external_files_html() {
    global $post;

    $external_files = b5_file_manager_folder_external_files($post->ID);

    $classes = array('b5-file', 'b5-external-uploaded');
    $delete_nonce = wp_create_nonce("b5-delete-file_b5_file_manager_files");

    $ol = '<ul class="%s" data-field_id="%s" data-delete_nonce="%s">';
    $html = sprintf(
        $ol,
        implode(' ', $classes),
        'b5_external_file',
        $delete_nonce
    );

    foreach ($external_files as $external_file_id) {
        $html .= b5_meta_external_file_html($external_file_id);
    }

    $html .= '</ul>';

    return $html;
}

function b5_meta_external_file_html($external_file_id) {
    global $post;
    $i18n_delete = apply_filters('b5_file_delete_string', _x('Delete', 'file upload', B5FILEMANAGER_PLUGIN_NAME));
    $i18n_edit   = apply_filters('b5_file_edit_string', _x('Edit', 'file upload', B5FILEMANAGER_PLUGIN_NAME));
    $i18n_downloads = __('Downloads', B5FILEMANAGER_PLUGIN_NAME);
    $li = '<li id="item_%s">
				<div class="b5-icon">%s</div>
				<div class="b5-info">
					<a href="%s" target="_blank">%s</a>
					<p>%s</p>
					<p>%s %s</p>
					<a title="%s" href="%s" target="_blank">%s</a> |
					<a title="%s" class="b5-delete-file" href="#" data-attachment_id="%s">%s</a>
				</div>
			</li>';

    $file_external_link_meta = get_post_meta($external_file_id, 'b5_external_file_link', true);

    $post_thumbnail_id = get_post_thumbnail_id($external_file_id);
    $external_file_thumbnail = wp_get_attachment_image($post_thumbnail_id, array(70, 70), true);

    $extension = trim(b5_file_manager_extension($external_file_id));

    return sprintf(
        $li,
        $external_file_id,
        $external_file_thumbnail,
        $file_external_link_meta,
        trim(get_the_title($external_file_id)) ? get_the_title($external_file_id) : __('(no title)'),
        !empty($extension) ? $extension : __('Unknown extension', B5FILEMANAGER_PLUGIN_NAME),
        b5_file_manager_file_folder_downloads($post->ID, $external_file_id),
        $i18n_downloads,
        $i18n_edit,
        get_edit_post_link($external_file_id),
        $i18n_edit,
        $i18n_delete,
        $external_file_id,
        $i18n_delete
    );
}

add_action('wp_ajax_b5_meta_all_external_files_html', 'b5_meta_all_external_files_html');
function b5_meta_all_external_files_html() {

    $html = '';

    $folder_id = is_numeric($_REQUEST['folder_id']) ? $_REQUEST['folder_id'] : 0;

    $all_external_files = b5_meta_external_files();
    $external_files = b5_file_manager_folder_external_files($folder_id);

    if(count($all_external_files) > 0) {
        $value = false;
        foreach ($all_external_files as $post_external) {
            if(!in_array($post_external->ID, $external_files)) {
                $value = true;
                $html .= b5_meta_external_file_modal_html($post_external->ID);
            }
        }
        if(!$value) {
            $html.= '<p>'.__('No external files to show', B5FILEMANAGER_PLUGIN_NAME).'</p>';
        }
    } else {
        $html.= '<p>'.__('No external files to show', B5FILEMANAGER_PLUGIN_NAME).'</p>';
    }

    echo $html;

    die();
}

function b5_meta_external_file_modal_html($post_id) {
    $classes = array('toggle', 'describe-toggle-on', 'b5-external-file-insert');
    $classes = implode(' ', $classes);

    $attach_nonce = wp_create_nonce("b5-attach-file_b5_file_manager_thumbnail");

    $html = '<div class="%s">
                <img class="%s" src="%s">
                <a class="%s" href="#" data-thumbnail-id="%s" data-attach_file_thumbnail_nonce="%s" onclick="javascript:b5_file_external_add(this);return false;">%s</a>
                <div class="filename"><span class="title">%s</span></div>
                <input type="hidden" value="%s" class="external-url" />
                <input type="hidden" value="%s" class="external-edit-link" />
                <input type="hidden" value="%s" class="external-file-extension" />
            </div>';

    $post_thumbnail_id = get_post_thumbnail_id($post_id);
    $array_thumbnail = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail', true);
    $file_external_link_meta = get_post_meta($post_id, 'b5_external_file_link');

    $extension = b5_file_manager_extension($post_id);

    return sprintf(
        $html,
        'media-item',
        'pinkynail',
        $array_thumbnail[0],
        $classes,
        $post_id,
        $attach_nonce,
        __('Insert', B5FILEMANAGER_PLUGIN_NAME),
        trim(get_the_title($post_id)) ? get_the_title($post_id) : __('(no title)'),
        $file_external_link_meta[0],
        get_edit_post_link($post_id),
        !empty($extension) ? $extension : __('Unknown extension', B5FILEMANAGER_PLUGIN_NAME)
    );
}

function b5_meta_external_files() {
    $args = array(
        'post_parent' => '0',
        'post_type'   => 'external_file',
        'numberposts' => -1);

    return get_posts($args);
}

add_action('delete_post', 'b5_delete_external_file');
function b5_delete_external_file($file_id) {
    global $post_type;
    if ($post_type != 'external_file') return;

    $args = array(
        'post_type' => 'folder',
        'numberposts' => -1
    );

    b5_file_manager_delete_file_downloads($file_id);

    $all_post_folder = get_posts($args);

    foreach($all_post_folder as $postinfo) {
        delete_post_meta($postinfo, 'b5_external_file_link', $file_id);
    }
}

/****************************************************************************************/
function b5_file_manager_meta_details_external_files() {
    global $post;

    $link = get_post_meta($post->ID, 'b5_external_file_link', true);
    $weight = get_post_meta($post->ID, 'b5_external_file_weight', true);

    $html = '<p>
                <label for="external_file_link"><strong>'.__('Direct link to file', B5FILEMANAGER_PLUGIN_NAME).'</strong></label>
                <input class="widefat" id="external_file_link" value="'.$link.'" name="external-file-link" type="text" />
             </p>';

    $html .= '<p>
                <p class="b5-meta-label"><label for="external_file_weight"><strong>'.__('File weight in bytes', B5FILEMANAGER_PLUGIN_NAME).'</strong></label></p>
                <input id="external_file_weight" value="'.intval($weight).'" name="external-file-weight" type="number" />
             </p>';

    echo $html;
}

add_action('save_post', 'b5_file_manager_save_media_meta');
function b5_file_manager_save_media_meta($post_id) {
    // Checks save status
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_link_nonce = (isset($_POST['external-file-link']) && wp_verify_nonce($_POST['external-file-link'], basename(__FILE__))) ? 'true' : 'false';
    $is_valid_weight_nonce = (isset($_POST['external-file-weight']) && wp_verify_nonce($_POST['external-file-weight'], basename(__FILE__))) ? 'true' : 'false';

    // Exits script depending on save status
    if ($is_autosave || $is_revision || !$is_valid_link_nonce || !$is_valid_weight_nonce) {
        return;
    }

    // Checks for input and sanitizes/saves if needed
    if(isset($_POST['external-file-link'])) {
        update_post_meta($post_id, 'b5_external_file_link', $_POST['external-file-link']);
    } else {
        delete_post_meta($post_id, 'b5_external_file_link');
    }

    if(isset($_POST['external-file-weight'])) {
        update_post_meta($post_id, 'b5_external_file_weight', intval($_POST['external-file-weight']));
    } else {
        delete_post_meta($post_id, 'b5_external_file_weight');
    }
}

/****************************************************************************************/
function b5_meta_select_files() {

    $html = b5_get_uploaded_files();

    $classes = array('button', 'b5-file-advanced-upload', 'hide-if-no-js', 'new-files');
    $classes = implode(' ', $classes);

    $attach_nonce = wp_create_nonce("b5-attach-file_b5_file_manager_files");

    $html .= "<a href='#' class='{$classes}' data-attach_file_nonce={$attach_nonce}>".__('Select or Upload Files', B5FILEMANAGER_PLUGIN_NAME)."</a>";

    $classes = array('button', 'b5-file-advanced-remove-all', 'hide-if-no-js');
    $classes = implode(' ', $classes);

    $remove_all_nonce = wp_create_nonce("b5-remove-all-file_b5_file_manager_files");

    $html .= "<a href='#' class='{$classes}' data-field_id='b5_file_manager_files' data-remove_all_file_nonce={$remove_all_nonce}>".__('Remove All Files', B5FILEMANAGER_PLUGIN_NAME)."</a>";

    echo $html;
}

function b5_get_uploaded_files() {
    global $post;
    $delete_nonce = wp_create_nonce("b5-delete-file_b5_file_manager_files");
    $classes = array('b5-file', 'b5-uploaded');

    $ol = '<ul class="%s" data-field_id="%s" data-delete_nonce="%s">';
    $html = sprintf(
        $ol,
        implode(' ', $classes),
        'b5_file_manager_files',
        $delete_nonce
    );

    $files = b5_file_manager_folder_local_files($post->ID);

    foreach ($files as $attachment_id) {
        $html .= b5_file_get_html($attachment_id);
    }

    $html .= '</ul>';

    return $html;
}

function b5_file_get_html($attachment_id) {
    global $post;
    $i18n_delete = apply_filters('b5_file_delete_string', _x('Delete', 'file upload', B5FILEMANAGER_PLUGIN_NAME));
    $i18n_edit   = apply_filters('b5_file_edit_string', _x('Edit', 'file upload', B5FILEMANAGER_PLUGIN_NAME));
    $i18n_downloads = __('Downloads', B5FILEMANAGER_PLUGIN_NAME);
    $li = '<li id="item_%s">
				<div class="b5-icon">%s</div>
				<div class="b5-info">
					<a href="%s" target="_blank">%s</a>
					<p>%s</p>
					<p>%s %s</p>
					<a title="%s" href="%s" target="_blank">%s</a> |
					<a title="%s" class="b5-delete-file" href="#" data-attachment_id="%s">%s</a>
				</div>
			</li>';

    $file_thumbnail = null;
    $thumbnail = get_post_meta($attachment_id, 'b5_file_manager_thumbnail');

    if(count($thumbnail) > 0) {
        $file_thumbnail = wp_get_attachment_image($thumbnail[0], array(70, 70), true);
    } else {
        $file_thumbnail = wp_get_attachment_image($attachment_id, array(70, 70), true);
    }

    $mime_type = get_post_mime_type($attachment_id);
    return sprintf(
        $li,
        $attachment_id,
        $file_thumbnail,
        wp_get_attachment_url($attachment_id),
        trim(get_the_title($attachment_id)) ? get_the_title($attachment_id) : __('(no title)'),
        $mime_type,
        b5_file_manager_file_folder_downloads($post->ID, $attachment_id),
        $i18n_downloads,
        $i18n_edit,
        get_edit_post_link($attachment_id),
        $i18n_edit,
        $i18n_delete,
        $attachment_id,
        $i18n_delete
    );
}

add_action('print_media_templates', 'print_b5_folders_templates');
function print_b5_folders_templates() {
    $i18n_delete = __('Delete', B5FILEMANAGER_PLUGIN_NAME);
    $i18n_edit   = __('Edit', B5FILEMANAGER_PLUGIN_NAME);
    ?>
    <script id="tmpl-b5-file-advanced" type="text/html">
        <# _.each(attachments, function(attachment) { #>
            <li id="item_{{{ attachment.id }}}">
                <div class="b5-icon"><img src="<# if (attachment.type == 'image' && attachment.sizes) { if (attachment.sizes.thumbnail) { #> {{{ attachment.sizes.thumbnail.url }}} <# } else { #> {{{ attachment.sizes.full.url }}} <# } } else { #> {{{ attachment.icon }}} <# } #>"></div>
                <div class="b5-info">
                    <a href="{{{ attachment.url }}}" target="_blank">{{{ attachment.title }}}</a>
                    <p>{{{ attachment.mime }}}</p>
                    <a title="<?php echo $i18n_edit;?>" href="{{{ attachment.editLink }}}" target="_blank"><?php echo $i18n_edit;?></a> |
                    <a title="<?php echo $i18n_delete;?>" class="b5-delete-file" href="#" data-attachment_id="{{{ attachment.id }}}"><?php echo $i18n_delete;?></a>
                </div>
            </li>
            <# } ); #>
    </script>
<?php
}

add_action('wp_ajax_b5_delete_file', 'wp_ajax_b5_delete_file');
function wp_ajax_b5_delete_file() {
    $post_id       = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $field_id      = isset($_POST['field_id']) ? $_POST['field_id'] : 0;
    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;

    check_ajax_referer("b5-delete-file_b5_file_manager_files");

    delete_post_meta($post_id, $field_id, $attachment_id);

    b5_file_manager_delete_file_folder_downloads($post_id, $attachment_id);

    wp_send_json_success();
}

add_action('wp_ajax_b5_attach_file', 'wp_ajax_b5_attach_file');
function wp_ajax_b5_attach_file() {
    $post_id = is_numeric($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;
    $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : 0;
    $attachment_ids = isset($_POST['attachment_ids']) ? $_POST['attachment_ids'] : array();

    check_ajax_referer("b5-attach-file_b5_file_manager_files");
    foreach($attachment_ids as $attachment_id) {
        add_post_meta($post_id, $field_id, $attachment_id, false);
    }

    wp_send_json_success();
}

add_action('wp_ajax_b5_remove_all_files', 'b5_file_manager_remove_all_file');
function b5_file_manager_remove_all_file() {
    $post_id = is_numeric($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;
    $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : 0;

    check_ajax_referer("b5-remove-all-file_b5_file_manager_files");

    foreach(get_post_meta($post_id, $field_id) as $file_id) {
        b5_file_manager_delete_file_folder_downloads($post_id, $file_id);
    }

    delete_post_meta($post_id, $field_id);

    wp_send_json_success();
}

/****************************************************************************************/
function b5_meta_show_folders_child() {
    global $post;
    $classes = array('b5-folders', 'ui-sortable');

    $ol = '<ul id="b5-folders-id" class="%s">';
    $html = sprintf(
        $ol,
        implode(' ', $classes)
    );

    $children_array = b5_file_manager_children_folders($post->ID);

    foreach($children_array as $children) {
        $html .= b5_folder_get_html($children->ID);
    }

    $html .= '</ul>';

    $classes = array('button', 'b5-file-advanced-add-folder');
    $classes = implode(' ', $classes);

    $add_folder_nonce = wp_create_nonce("b5-add-folder_b5_file_manager_children_folder");

    $html .= "<a href='#' id='b5-file-advanced-add-folder-id' class='{$classes}' title='".__('Add folder', B5FILEMANAGER_PLUGIN_NAME)."'>".__('Add folder', B5FILEMANAGER_PLUGIN_NAME)."</a><span id='b5-file-form-add-folder' class='hide-if-js'><input type='text' id='b5-file-child-folder-name' placeholder='".__('Enter title here', B5FILEMANAGER_PLUGIN_NAME)."' /><a class='button b5-file-create-folder' id='b5-file-create-folder-id' data-add_folder_nonce='{$add_folder_nonce}'>".__('OK', B5FILEMANAGER_PLUGIN_NAME)."</a><a class='button-cancel b5-file-button-cancel' id='b5-file-button-cancel-id' href='#'>".__('Cancel', B5FILEMANAGER_PLUGIN_NAME)."</a></span>";

    echo $html;
}

add_action('wp_ajax_add_child_folder', 'b5_file_manager_add_child_folder');
function b5_file_manager_add_child_folder() {
    $parent_folder_id = isset($_POST['parent_folder']) ? intval($_POST['parent_folder']) : 0;
    $folder_name = sanitize_text_field($_POST['folder_name']);

    $post_array = array(
        'post_type'=>'folder',
        'post_parent'=>$parent_folder_id,
        'post_title'=>$folder_name,
        'post_status'=>'publish'
    );

    $post_id = wp_insert_post($post_array, true);

    if(is_wp_error($post_id)) {
        wp_send_json_error(__('Internal error saving folder', B5FILEMANAGER_PLUGIN_NAME));
    } else {
        wp_send_json_success(array('id'=>$post_id, 'name'=>$folder_name, 'edit_link'=>esc_url(get_edit_post_link($post_id)), 'trash_link'=>esc_url(get_delete_post_link($post_id))));
    }
}

function b5_folder_get_html($folder_id) {
    $i18n_trash = __('Trash', B5FILEMANAGER_PLUGIN_NAME);
    $i18n_edit = __('Edit', B5FILEMANAGER_PLUGIN_NAME);
    $li = '
			<li id="item_%s" class="b5-folder-child" data-folder_id="%s" data-set_parent_nonce="%s">
				<div class="b5-folder">
				    <div class="dashicons dashicons-category"></div>
				</div>
				<div class="b5-info">
				    <p>%s</p>
					<p>%s: %s</p>
					<p>%s: <span class="b5-file-subfolders">%s</span></p>
					<a title="%s" href="%s">%s</a> |
					<a title="%s" class="b5-delete-folder" href="%s" data-attachment_id="%s">%s</a>
				</div>
			</li>';

    //$mime_type = get_post_mime_type($attachment_id);
    return sprintf(
        $li,
        $folder_id,
        $folder_id,
        wp_create_nonce("b5-attach-file_b5_change_folder_parent"),
        trim(get_the_title($folder_id)) ? get_the_title($folder_id) : __('(no title)'),
        __('Files', B5FILEMANAGER_PLUGIN_NAME),
        b5_file_manager_folder_files_number($folder_id),
        __('Subfolders', B5FILEMANAGER_PLUGIN_NAME),
        b5_file_manager_children_folders_number($folder_id),
        $i18n_edit,
        get_edit_post_link($folder_id),
        $i18n_edit,
        $i18n_trash,
        get_delete_post_link($folder_id),
        $folder_id,
        $i18n_trash
    );
}

add_action('print_media_templates', 'print_b5_folders_child_templates');
function print_b5_folders_child_templates() {
    $i18n_trash = __('Trash', B5FILEMANAGER_PLUGIN_NAME);
    $i18n_edit = __('Edit', B5FILEMANAGER_PLUGIN_NAME);?>
    <script id="tmpl-b5-folder-child-advanced" type="text/html">
        <# _.each(attachments, function(attachment) { #>
            <li id="item_{{{ attachment.id }}}" class="b5-folder-child" data-folder_id="{{{ attachment.id }}}" data-set_parent_nonce="<?php echo wp_create_nonce("b5-attach-file_b5_change_folder_parent");?>">
                <div class="b5-folder">
                    <div class="dashicons dashicons-category"></div>
                </div>
                <div class="b5-info">
                    <p>{{{ attachment.name }}}</p>
                    <p><?php echo __('Files', B5FILEMANAGER_PLUGIN_NAME);?>: 0</p>
                    <p><?php echo __('Subfolders', B5FILEMANAGER_PLUGIN_NAME);?>: <span class="b5-file-subfolders">0</span></p>
                    <a title="<?php echo $i18n_edit;?>" href="{{{ attachment.edit_link }}}"><?php echo $i18n_edit;?></a> |
                    <a title="<?php echo $i18n_trash;?>" class="b5-delete-folder" href="{{{ attachment.trash_link }}}" data-attachment_id="{{{ attachment.id }}}"><?php echo $i18n_trash;?></a>
                </div>
            </li>
            <# } ); #>
    </script>
<?php }

add_action('wp_ajax_set_parent_folder', 'wp_ajax_b5_set_parent_folder');
function wp_ajax_b5_set_parent_folder() {
    global $wpdb;
    $folder_id = is_numeric($_REQUEST['folder_id']) ? $_REQUEST['folder_id'] : 0;
    $new_parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : 0;

    check_ajax_referer("b5-attach-file_b5_change_folder_parent");

    $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_parent = %s WHERE ID = %s AND post_type = 'folder'", $new_parent_id, $folder_id));

    wp_send_json_success(array('subfolders'=>b5_file_manager_children_folders_number($new_parent_id)));
}

add_action('before_delete_post', 'b5_before_delete_external_file');
function b5_before_delete_external_file($postid) {
    global $post_type;
    if($post_type != 'external_file') return;

    $args = array(
        'post_type' => 'folder',
        'numberposts' => -1
    );

    $all_post_folder = get_posts($args);

    foreach($all_post_folder as $postinfo) {
        delete_post_meta($postinfo->ID, 'b5_external_file', $postid);
    }
}

add_action('delete_post', 'b5_delete_media_file');
function b5_delete_media_file($postid) {
    global $post_type;
    if($post_type != 'attachment') return;

    $args = array(
        'post_type' => 'folder',
        'numberposts' => -1
    );

    b5_file_manager_delete_file_downloads($postid);

    $all_post_folder = get_posts($args);

    foreach($all_post_folder as $postinfo) {
        delete_post_meta($postinfo->ID, 'b5_file_manager_files', $postid);
    }
}

/****************************************************************************************/
function b5_meta_users_group() {
    $html = '';

    $classes = array('button', 'b5-file-users-add');
    $classes = implode(' ', $classes);

    $attach_nonce = wp_create_nonce("b5-attach-file_b5_users_group");

    $html .= b5_meta_users_group_html();

    $html .= "<a href='#' id='b5-file-group-users-id' class='{$classes}' data-attach_file_nonce={$attach_nonce} title='".__('Add Users', B5FILEMANAGER_PLUGIN_NAME)."'>".__('Add Users', B5FILEMANAGER_PLUGIN_NAME)."</a>";

    $classes = array('button', 'b5-file-advanced-users-remove-all', 'hide-if-no-js');
    $classes = implode(' ', $classes);

    $remove_all_nonce = wp_create_nonce("b5-remove-all-file_b5_file_manager_users");

    $html .= "<a href='#' class='{$classes}' data-field_id='b5_file_manager_users' data-remove_all_users_nonce='{$remove_all_nonce}' title='".__('Remove All Users', B5FILEMANAGER_PLUGIN_NAME)."'>".__('Remove All Users', B5FILEMANAGER_PLUGIN_NAME)."</a>";

    echo $html;
}

function b5_meta_users_group_html() {
    global $post;

    $users_group = b5_file_manager_users_group($post->ID);

    $classes = array('b5-file', 'b5-users-group');
    $delete_nonce = wp_create_nonce("b5-delete-user-group");

    $ol = '<ul class="%s" data-field_id="%s" data-delete_nonce="%s">';
    $html = sprintf(
        $ol,
        implode(' ', $classes),
        'b5_file_manager_users',
        $delete_nonce
    );

    foreach($users_group as $user_id) {
        $html .= b5_meta_user_html($user_id);
    }

    $html .= '</ul>';

    return $html;
}

function b5_meta_user_html($user_id) {
    $i18n_delete = __('Delete', B5FILEMANAGER_PLUGIN_NAME);
    $i18n_edit = __('Edit', B5FILEMANAGER_PLUGIN_NAME);
    $li = '<li id="item_%s">
				<div class="b5-info">
					<a href="%s" target="_blank">%s</a>
					<p>%s</p>
					<a title="%s" href="%s" target="_blank">%s</a> |
					<a title="%s" class="b5-delete-user" href="#" data-user_id="%s">%s</a>
				</div>
			</li>';

    $user = get_user_by('id', $user_id);

    return sprintf(
        $li,
        $user_id,
        get_edit_user_link($user_id),
        $user->user_login,
        $user->display_name,
        $i18n_edit,
        get_edit_user_link($user_id),
        $i18n_edit,
        $i18n_delete,
        $user_id,
        $i18n_delete
    );
}

add_action('wp_ajax_b5_meta_all_users_html', 'b5_meta_all_users_html');
function b5_meta_all_users_html() {
    $group_id = is_numeric($_REQUEST['group_id']) ? $_REQUEST['group_id'] : 0;

    $html = '';

    $users = get_users(array('fields' => array('ID', 'user_login', 'display_name')));

    if(count($users) > 0) {
        $value = false;
        foreach($users as $user) {
            if(!b5_file_manager_user_in_group($group_id, $user->ID)) {
                $value = true;
                $html .= b5_meta_user_modal_html($user->ID);
            }
        }
        if(!$value) {
            $html .= '<p>'.__('No users to show', B5FILEMANAGER_PLUGIN_NAME).'</p>';
        }
    } else {
        $html .= '<p>'.__('No users to show', B5FILEMANAGER_PLUGIN_NAME).'</p>';
    }

    echo $html;

    die();
}

function b5_meta_user_modal_html($user_id) {
    $classes = array('toggle', 'describe-toggle-on', 'b5-user-insert');
    $classes = implode(' ', $classes);

    $attach_nonce = wp_create_nonce("b5-select_user_group_nonce");

    $user = get_user_by('id', $user_id);

    $html = '<div class="%s">
                <a class="%s" href="#" data-user_id="%s" data-select_user_group_nonce="%s" onclick="javascript:b5_users_group_add(this);return false;">%s</a>
                <div class="username"><span class="title">%s</span></div>
                <input type="hidden" value="%s" class="user-edit-link" />
                <input type="hidden" value="%s" class="user-display-name" />
            </div>';

    return sprintf(
        $html,
        'user-item',
        $classes,
        $user_id,
        $attach_nonce,
        __('Insert', B5FILEMANAGER_PLUGIN_NAME),
        $user->user_login,
        get_edit_user_link($user_id),
        $user->display_name
    );
}

add_action('wp_ajax_b5_attach_users_group', 'b5_attach_users_group');
function b5_attach_users_group() {
    $group_id = is_numeric($_REQUEST['group_id']) ? $_REQUEST['group_id'] : 0;
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;

    check_ajax_referer("b5-select_user_group_nonce");
    add_post_meta($group_id, 'b5_file_manager_users', $user_id, false);

    wp_send_json_success();
}

add_action('print_media_templates', 'print_b5_users_group_templates');
function print_b5_users_group_templates() {
    $i18n_delete = __('Delete', B5FILEMANAGER_PLUGIN_NAME);
    $i18n_edit = __('Edit', B5FILEMANAGER_PLUGIN_NAME);
    ?>
    <script id="tmpl-b5-users-group" type="text/html">
        <# _.each(users, function(user) { #>
            <li id="item_{{{ user.id }}}">
                <div class="b5-info">
                    <a href="#" target="_blank">{{{ user.username }}}</a>
                    <p>{{{ user.display_name }}}</p>
                    <a title="<?php echo $i18n_edit;?>" href="{{{ user.edit_url }}}" target="_blank"><?php echo $i18n_edit;?></a> |
                    <a title="<?php echo $i18n_delete;?>" class="b5-delete-user" href="#" data-user_id="{{{ user.id }}}"><?php echo $i18n_delete;?></a>
                </div>
            </li>
            <# } ); #>
    </script>
<?php
}

add_action('wp_ajax_b5_delete_user', 'b5_file_manager_delete_user');
function b5_file_manager_delete_user() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    check_ajax_referer("b5-delete-user-group");

    delete_post_meta($post_id, $field_id, $user_id);

    wp_send_json_success();
}

add_action('wp_ajax_b5_remove_all_users', 'b5_file_manager_remove_all_users');
function b5_file_manager_remove_all_users() {
    $post_id = is_numeric($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;
    $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : 0;

    check_ajax_referer("b5-remove-all-file_b5_file_manager_users");

    delete_post_meta($post_id, $field_id);

    wp_send_json_success();
}

add_action('delete_post', 'b5_delete_user_group');
function b5_delete_user_group($group_id) {
    global $post_type;
    if($post_type != 'users_group_file') return;

    $args = array(
        'post_type' => 'folder',
        'numberposts' => -1
    );

    $all_post_folder = get_posts($args);

    foreach($all_post_folder as $postinfo) {
        $users_groups = get_post_meta($postinfo->ID, 'b5_file_manager_folder_groups', true);
        $final_array = array();
        if(in_array($group_id, $users_groups)) {
            foreach($users_groups as $group) {
                if($group_id != $group) {
                    $final_array[] = $group;
                }
            }
        }
        update_post_meta($postinfo->ID, 'b5_file_manager_folder_groups', $final_array);
    }
}

/****************************************************************************************/
function b5_file_manager_meta_downloads() {
    global $post;
    $html = '<div>
                <p class="b5-meta-label">'.__('Times', B5FILEMANAGER_PLUGIN_NAME).': <strong id="b5-meta-downloads-number">'.b5_file_manager_file_downloads($post->ID).'</strong></p><br />
                <div><a href="edit.php?post_type=folder&page=b5_all_downloads&file_id='.$post->ID.'">'.__('View all logs', B5FILEMANAGER_PLUGIN_NAME).'</a></div>
                <div class="b5-meta-button"><a id="b5_downloads_reset" data-remove_all_nonce="'.wp_create_nonce("b5-remove_all_file_download_nonce").'" class="button">'.__("Reset", B5FILEMANAGER_PLUGIN_NAME).'</a></div>
         </div>';
    echo $html;
}

function b5_file_manager_meta_folder_downloads() {
    global $post;
    $html = '<div>
                <p class="b5-meta-label">'.__('Times', B5FILEMANAGER_PLUGIN_NAME).': <strong id="b5-meta-downloads-number">'.b5_file_manager_folder_downloads($post->ID).'</strong></p><br />
                <div><a href="edit.php?post_type=folder&page=b5_all_downloads&folder_id='.$post->ID.'">'.__('View all logs', B5FILEMANAGER_PLUGIN_NAME).'</a></div>
                <div class="b5-meta-button"><a id="b5_downloads_folder_reset" data-remove_all_nonce="'.wp_create_nonce("b5-remove_all_folder_download_nonce").'" class="button">'.__("Reset", B5FILEMANAGER_PLUGIN_NAME).'</a></div>
         </div>';
    echo $html;
}

add_action('wp_ajax_remove_all_file_downloads', 'b5_file_manager_remove_all_file_downloads');
function b5_file_manager_remove_all_file_downloads() {

    $file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;

    check_ajax_referer("b5-remove_all_file_download_nonce");

    b5_file_manager_delete_file_downloads($file_id);

    wp_send_json_success();
}

add_action('wp_ajax_remove_all_folder_downloads', 'b5_file_manager_remove_all_folder_downloads');
function b5_file_manager_remove_all_folder_downloads() {

    $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;

    check_ajax_referer("b5-remove_all_folder_download_nonce");

    b5_file_manager_delete_folder_downloads($folder_id);

    wp_send_json_success();
}
/****************************************************************************************/
function b5_meta_extra_fields() {
    global $post;

    $extra_fields = get_option('b5-file-manager-extra-fields', array());

    $html = '<input type="hidden" name="b5-extra-field-values" value="1" />';

    foreach($extra_fields as $key => $field_name) {
        $meta_field_value = get_post_meta($post->ID, $key, true);
        $html .= '<p>
                <label for="'.$key.'"><strong>'.$field_name.'</strong></label>
                <input class="widefat" value="'.$meta_field_value.'" id="'.$key.'" name="'.$key.'" type="text">
             </p>';
    }

    echo $html;
}

add_action('save_post', 'b5_file_manager_ext_file_save_extra_fields');
function b5_file_manager_ext_file_save_extra_fields($post_id) {
    global $post_type;

    if($post_type != 'external_file') return;

    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST['b5-extra-field-values']) && wp_verify_nonce($_POST['b5-extra-field-values'], basename(__FILE__))) ? 'true' : 'false';

    // Exits script depending on save status

    if($is_autosave || $is_revision || !$is_valid_nonce || !isset($_POST['b5-extra-field-values'])) {
        return;
    }

    $extra_fields = get_option('b5-file-manager-extra-fields', array());

    foreach($extra_fields as $key => $field_name) {
        if(isset($_POST[$key])) {
            update_post_meta($post_id, $key, $_POST[$key]);
        } else {
            delete_post_meta($post_id, $key);
        }
    }
}

add_action('edit_attachment', 'b5_file_manager_attachment_save_extra_fields');
function b5_file_manager_attachment_save_extra_fields($post_id) {
    global $post_type;

    if($post_type != 'attachment') return;

    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST['b5-extra-field-values']) && wp_verify_nonce($_POST['b5-extra-field-values'], basename(__FILE__))) ? 'true' : 'false';

    // Exits script depending on save status

    if($is_autosave || $is_revision || !$is_valid_nonce || !isset($_POST['b5-extra-field-values'])) {
        return;
    }

    $extra_fields = get_option('b5-file-manager-extra-fields', array());

    foreach($extra_fields as $key => $field_name) {
        if(isset($_POST[$key])) {
            update_post_meta($post_id, $key, $_POST[$key]);
        } else {
            delete_post_meta($post_id, $key);
        }
    }
}