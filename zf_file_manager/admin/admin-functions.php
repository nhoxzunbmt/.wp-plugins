<?php

    /*Folder*/
    add_filter('manage_edit-folder_columns', 'b5_file_manager_folder_edit_columns');
    function b5_file_manager_folder_edit_columns($columns) {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'users_group' => __('Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'users' => __('Users', B5FILEMANAGER_PLUGIN_NAME),
            'public' => __('Public', B5FILEMANAGER_PLUGIN_NAME),
            'files' => __('Files', B5FILEMANAGER_PLUGIN_NAME),
            'date' => __('Date', B5FILEMANAGER_PLUGIN_NAME)
        );

        return $columns;
    }

    add_action('manage_folder_posts_custom_column', 'b5_file_manager_folder_columns', 10, 2);
    function b5_file_manager_folder_columns($column, $post_id) {

        switch($column) {
            case 'users_group' :
                $users_group = b5_file_manager_folder_group($post_id);//get_post_meta($post_id, 'b5_file_manager_folder_groups');
                if(!empty($users_group)) {
                    $out = array();
                    foreach ($users_group as $groupid) {
                        $group = get_post($groupid);
                        $group_name = !empty($group->post_title) ? $group->post_title : __("Unnamed group", B5FILEMANAGER_PLUGIN_NAME);
                        $out[] = '<a href="'.get_edit_post_link($groupid).'">'.$group_name.'</a>';
                    }
                    echo join(', ', $out);
                } else {
                    _e('No Users Group', B5FILEMANAGER_PLUGIN_NAME);
                }
                break;
            case 'users' :
                $users_id = b5_file_manager_folder_users($post_id);
                if(!empty($users_id)) {
                    $users = array();
                    foreach ($users_id as $userid) {
                        $user = get_user_by('id', $userid);
                        $users[] = '<a href="'.get_edit_user_link($userid).'" title="'.$user->display_name.'">'.$user->user_login.'</a>';
                    }
                    echo join(', ', $users);
                } else {
                    _e('No Users', B5FILEMANAGER_PLUGIN_NAME);
                }
                break;
            case 'files' :
                echo b5_file_manager_folder_files_number($post_id);
                break;
            case 'public' :
                echo '<input type="checkbox" name="folder_public" class="b5-admin-public-folder" value="'.$post_id.'" '.checked(true, b5_file_manager_get_folder_access($post_id), false).' />';
                break;
            default :
                break;
        }
    }

    /*External files*/
    add_filter('manage_edit-external_file_columns', 'b5_file_manager_external_file_edit_columns');
    function b5_file_manager_external_file_edit_columns($columns) {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'icon' => '',
            'title' => __('Title', B5FILEMANAGER_PLUGIN_NAME),
            'weight' => __('Weight', B5FILEMANAGER_PLUGIN_NAME),
            'downloads' => __('Downloads', B5FILEMANAGER_PLUGIN_NAME),
            'date' => __('Date', B5FILEMANAGER_PLUGIN_NAME)
        );

        return $columns;
    }

    add_action('manage_external_file_posts_custom_column', 'b5_file_manager_external_file_columns', 10, 2);
    function b5_file_manager_external_file_columns($column, $post_id) {
        switch($column) {
            case 'icon' :
                echo wp_get_attachment_image(b5_file_manager_thumbnail_id($post_id), array(60, 60), true);
                break;
            case 'weight':
                $weight = intval(get_post_meta($post_id, 'b5_external_file_weight', true));
                $size_format = $weight > 0 ? " (".size_format($weight, intval(get_option('b5-file-manager-date-decimal', 2))).")" : "";
                echo $weight." B".$size_format;
                break;
            case 'downloads':
                echo b5_file_manager_file_downloads($post_id);
                break;
            default :
                break;
        }
    }

    //Sortable external file columns
    add_filter('manage_edit-external_file_sortable_columns', 'b5_file_manager_external_file_edit_sortable_columns');

    function b5_file_manager_external_file_edit_sortable_columns($columns) {
        $columns['weight'] = 'weight';
        return $columns;
    }

    add_filter('request', 'b5_file_manager_external_file_column_weight');
    function b5_file_manager_external_file_column_weight($vars) {
        if(isset($vars['post_type']) && 'external_file' == $vars['post_type']) {
            if(isset($vars['orderby']) && 'weight' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key'=>'b5_external_file_weight',
                    'orderby'=>'meta_value_num'
                ));
            }
        }

        return $vars;
    }

    add_action('post_edit_form_tag', 'b5_file_manager_post_edit_form_tag');
    function b5_file_manager_post_edit_form_tag() {
        global $post;

        $post_type = get_post_type($post->ID);

        if('folder' != $post_type) {
            return;
        }

        echo ' enctype="multipart/form-data"';
    }

    /*Users group*/
    add_filter('manage_edit-users_group_file_columns', 'b5_users_group_file_edit_columns');
    function b5_users_group_file_edit_columns($columns) {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', B5FILEMANAGER_PLUGIN_NAME),
            'users' => __('Users', B5FILEMANAGER_PLUGIN_NAME),
            'date' => __('Date', B5FILEMANAGER_PLUGIN_NAME)
        );

        return $columns;
    }

    add_action('manage_users_group_file_posts_custom_column', 'b5_manage_users_group_file_columns', 10, 2);
    function b5_manage_users_group_file_columns($column, $post_id) {

        switch($column) {
            case 'users' :
                $users_group = get_post_meta($post_id, 'b5_file_manager_users');
                if(!empty($users_group)) {
                    $users = array();
                    foreach ($users_group as $userid) {
                        $user = get_user_by('id', $userid);
                        $users[] = '<a href="'.get_edit_user_link($userid).'" title="'.$user->display_name.'">'.$user->user_login.'</a>';
                    }
                    echo join(', ', $users);
                } else {
                    _e('No Users', B5FILEMANAGER_PLUGIN_NAME);
                }
                break;
            default :
                break;
        }
    }

    add_action('admin_enqueue_scripts', 'b5_file_manager_admin_enqueue_scripts');
    function b5_file_manager_admin_enqueue_scripts() {

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-draggable');

        wp_enqueue_media();
        add_thickbox();
        //wp_enqueue_script('media-upload');

        wp_register_script('b5_file_manager_drag_drop', b5_file_manager_plugin_url('admin/js/b5-file-manager-drag-drop.js'), array('jquery'), false, true);
        wp_enqueue_script('b5_file_manager_drag_drop');

        wp_register_script('b5-folder-access', b5_file_manager_plugin_url('admin/js/b5-folder-access.js'), array('jquery'), false, true);
        wp_localize_script('b5-folder-access', 'b5UsersSelect', array(
            'frameTitle'=>__('Select Users', B5FILEMANAGER_PLUGIN_NAME),
            'frameNoUsers'=>__('No users to show', B5FILEMANAGER_PLUGIN_NAME),
            'removeAllMessage'=>__('Click OK to remove all users from group.')
        ));
        wp_enqueue_script('b5-folder-access');

        wp_register_script('b5-file-external-upload', b5_file_manager_plugin_url('admin/js/b5-file-external-upload.js'), array('jquery'), false, true);
        wp_localize_script('b5-file-external-upload', 'b5ExternalFileUpload', array(
            'frameTitle'=>__('External Files', B5FILEMANAGER_PLUGIN_NAME),
            'frameNoExternalFiles'=>__('No external files to show', B5FILEMANAGER_PLUGIN_NAME)
        ));
        wp_enqueue_script('b5-file-external-upload');

        wp_register_script('b5-file-upload', b5_file_manager_plugin_url('admin/js/b5-file-upload.js'), array('jquery'), false, true);
        wp_localize_script('b5-file-upload', 'b5FileUpload', array(
            'frameTitle'=>__('Select Files', B5FILEMANAGER_PLUGIN_NAME),
            'removeAllMessage'=>__('Click OK to remove all files from folder.')
        ));
        wp_enqueue_script('b5-file-upload');

        wp_register_script('b5-file-thumbnail-upload', b5_file_manager_plugin_url('admin/js/b5-file-thumbnail-upload.js'), array('jquery'), false, true);
        wp_localize_script('b5-file-thumbnail-upload', 'b5ThumbnailUpload', array(
            'frameTitle'=>__('Select Files', B5FILEMANAGER_PLUGIN_NAME)
        ));
        wp_enqueue_script('b5-file-thumbnail-upload');

        wp_register_script('b5_file_manager_tab_menu_script', b5_file_manager_plugin_url('admin/js/b5-file-manager-options.js'), array('jquery'), false, true);
        $b5_file_manager_translation_array = array(
            'b5_file_manager_reset_msg'=>__('Click OK to reset. All settings will be replaced with default values!', B5FILEMANAGER_PLUGIN_BASENAME)
        );
        wp_localize_script('b5_file_manager_tab_menu_script', 'b5_file_manager_options', $b5_file_manager_translation_array);
        wp_enqueue_script('b5_file_manager_tab_menu_script');

        wp_register_script('b5_file_manager_image_loader', b5_file_manager_plugin_url('admin/js/b5-file-manager-image-loader.js'), array('jquery'), false, true);
        wp_enqueue_script('b5_file_manager_image_loader');

        wp_register_style('tzCheckbox-css', b5_file_manager_plugin_url('admin/css/tzCheckbox/jquery.tzCheckbox.css'));
        wp_enqueue_style('tzCheckbox-css');
        wp_register_script('tzCheckbox', b5_file_manager_plugin_url('admin/js/tzCheckbox/jquery.tzCheckbox.js'), array('jquery'), false, true);
        wp_enqueue_script('tzCheckbox');
        wp_register_script('tzCheckbox-init', b5_file_manager_plugin_url('admin/js/tzCheckbox/script.js'), array('jquery'), false, true);
        $b5_file_manager_tzCheckbox_array= array(
            'b5_file_manager_enable'=>__('Enable', B5FILEMANAGER_PLUGIN_BASENAME),
            'b5_file_manager_disable'=>__('Disable', B5FILEMANAGER_PLUGIN_BASENAME),
        );
        wp_localize_script('tzCheckbox-init', 'b5_file_manager_tzchekbox', $b5_file_manager_tzCheckbox_array);
        wp_enqueue_script('tzCheckbox-init');

        wp_register_style('b5-style-css', b5_file_manager_plugin_url('admin/css/style.css'));
        wp_enqueue_style('b5-style-css');
        wp_register_style('b5-file-css', b5_file_manager_plugin_url('admin/css/file.css'));
        wp_enqueue_style('b5-file-css');
        wp_register_style('b5_file_options-css', b5_file_manager_plugin_url('admin/css/b5-file-manager-options.css'));
        wp_enqueue_style('b5_file_options-css');
    }

    add_action('admin_enqueue_scripts', 'b5_file_manager_add_color_picker_styles_scripts');
    function b5_file_manager_add_color_picker_styles_scripts() {
        //Access the global $wp_version variable to see which version of WordPress is installed.
        global $wp_version;

        //If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
        if(3.5 <= $wp_version) {
            //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }
        //If the WordPress version is less than 3.5 load the older farbtasic color picker.
        else {
            //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
            wp_enqueue_style('farbtastic');
            wp_enqueue_script('farbtastic');
        }
    }

    // Create TinyMCE's editor button
    add_action('init', 'b5_file_manager_folder_sc_button');
    function b5_file_manager_folder_sc_button() {
        if (current_user_can('edit_posts') &&  current_user_can('edit_pages')) {
            add_filter('mce_external_plugins', 'b5_file_manager_add_folder_plugin');
            add_filter('mce_buttons', 'b5_file_manager_register_folder_button');
        }
    }

    function b5_file_manager_register_folder_button($button) {
        array_push($button, '|', 'b5_file_manager_shortcodes');
        return $button;
    }

    add_action('admin_head', 'b5_file_manager_folder_in_js');
    function b5_file_manager_folder_in_js() {
        $args = array(
            'post_parent' => '0',
            'numberposts' => -1,
            'post_type'   => 'folder');

        $post_array = get_posts($args);

        $root_folders = array();
        foreach($post_array as $post_folder) {
            $root_folders[] = array('text'=>trim($post_folder->post_title) ? $post_folder->post_title : __("(no title)"), 'value'=>$post_folder->ID);
        }
        ?>
        <script type="text/javascript">
            var b5_file_manager_root_folders = <?php echo json_encode($root_folders);?>;
        </script>
    <?php
    }

    function b5_file_manager_add_folder_plugin($plugins) {
        global $wp_version;
        if ($wp_version >= 3.9) {
            $plugins['b5_file_manager_shortcodes'] = b5_file_manager_plugin_url('admin/js/b5-tinymce-editor-plugin-wp-3-9.js');
	    } else {
            $plugins['b5_file_manager_shortcodes'] = b5_file_manager_plugin_url('admin/js/b5-tinymce-editor-plugin.js');
        }

        return $plugins;
    }