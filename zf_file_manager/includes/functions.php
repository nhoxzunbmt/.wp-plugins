<?php

    function b5_file_manager_plugin_path($path = '') {
        return path_join(B5FILEMANAGER_PLUGIN_DIR, trim($path, '/'));
    }

    function b5_file_manager_plugin_url($path = '') {
        $url = untrailingslashit(B5FILEMANAGER_PLUGIN_URL);

        if (!empty($path) && is_string($path) && false === strpos($path, '..')) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    add_action('wp_enqueue_scripts', 'b5_file_manager_enqueue_scripts');
    function b5_file_manager_enqueue_scripts() {
        wp_enqueue_script('jquery');

        wp_register_script('isotope', b5_file_manager_plugin_url('includes/plugins/isotope.pkgd.min.js'), array('jquery'), false, true);
        wp_enqueue_script('isotope');

        wp_register_script('lightbox-js', b5_file_manager_plugin_url('includes/js/lightbox.min.js'), array('jquery'), false, true);
        wp_enqueue_script('lightbox-js');

        wp_register_style('css-b5-style', b5_file_manager_plugin_url('includes/css/b5-file-manager.css'));
        wp_enqueue_style('css-b5-style');

        wp_register_style('css-lightbox', b5_file_manager_plugin_url('includes/css/lightbox.css'));
        wp_enqueue_style('css-lightbox');

        wp_enqueue_style('dashicons');
    }

    add_action('wp_ajax_update_tree', 'b5_file_manager_update_tree'); //creating Ajax call for WordPress
    add_action('wp_ajax_nopriv_update_tree', 'b5_file_manager_update_tree');
    function b5_file_manager_update_tree() {
        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if(b5_file_manager_show_folder_for_user($folder_id, get_current_user_id())) {
            $html['folder_tree'] = b5_file_manager_show_folder_html($folder_id, $post_id);
            $html['breadcrumbs'] = b5_file_manager_show_breadcrumbs_html($folder_id);
            $html['folder_information'] = b5_file_manager_folder_information_html($folder_id);

            wp_send_json_success($html);
        } else {
            wp_send_json_error(__('You don\'t have permission to view this folder.', B5FILEMANAGER_PLUGIN_NAME));
        }
    }

    add_action('template_redirect','b5_file_manager_redirect');
    function b5_file_manager_redirect() {

        if (isset($_GET['b5-file']) && !empty($_GET['b5-file']) &&  isset($_GET['b5-folder']) && !empty($_GET['b5-folder'])) {

            $errors = new WP_Error();

            $file_id = esc_attr($_GET['b5-file']);
            $folder_id = esc_attr($_GET['b5-folder']);

            if(b5_file_manager_file_in_folder($file_id, $folder_id) && b5_file_manager_show_folder_for_user($folder_id, get_current_user_id())) {

                $file_post = get_post($file_id);
                $filepath = get_attached_file($file_id);
                $filename = esc_html(wp_basename($file_post->guid));

                if(is_file($filepath)) {

                    $fileSize = filesize($filepath);

                    while(ob_get_level()) {
                        @ob_end_clean();
                    }

                    if(headers_sent($headersFile, $headersLine)) {
                        die('Headers file:' . $headersFile . ' on line: ' . $headersLine);
                    }

                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private", false); // required for certain browsers

                    $mimeType = 'application/octet-stream';

                    header('Content-Description: File Transfer');
                    header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Length: " . $fileSize);

                    header("Content-type: " . $mimeType);
                    readfile($filepath);

                    b5_file_manager_register_download($file_id, $folder_id);
                    exit;
                }
                else {
                    header("Pragma: public");
                    header("Expires: -1");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Length: 0");
                    header('Content-Disposition: attachment; filename="broken-download.txt"');
                    exit;
                }
            } else {
                $errors->add('access_denied', '<span>'.__('ERROR', B5FILEMANAGER_PLUGIN_NAME).'</span>: '.__('You don\'t have access to this file.', B5FILEMANAGER_PLUGIN_NAME));
            }
            //exit;
        }
    }

    add_action('wp_ajax_external_download', 'b5_file_manager_register_external_download'); //creating Ajax call for WordPress
    add_action('wp_ajax_nopriv_external_download', 'b5_file_manager_register_external_download');
    function b5_file_manager_register_external_download() {
        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;

        if(b5_file_manager_file_in_folder($file_id, $folder_id) && b5_file_manager_show_folder_for_user($folder_id, get_current_user_id())) {
            b5_file_manager_register_download($file_id, $folder_id);
            wp_send_json_success();
        } else {
            wp_send_json_error(__('You don\'t have access to this file.', B5FILEMANAGER_PLUGIN_NAME));
        }
    }

    /******************************* Folder functions *************************************/
    function b5_file_manager_show_folder_html($folder_id, $post_id = 0) {

        $html = '';

        $children_array = b5_file_manager_children_folders($folder_id);
        foreach ($children_array as $children) {
            if(b5_file_manager_show_folder_for_user($children->ID, get_current_user_id())) {
                $html .= b5_file_manager_folder_get_html($children->ID);
            }
        }

        $local_files_array = b5_file_manager_folder_local_files($folder_id);
        foreach ($local_files_array as $attachment_id) {
            $html .= b5_file_manager_file_get_html($attachment_id, $folder_id, $post_id);
        }

        $external_files_array = b5_file_manager_folder_external_files($folder_id);
        foreach ($external_files_array as $attachment_id) {
            $html .= b5_file_manager_file_get_html($attachment_id, $folder_id, $post_id);
        }

        if(empty($children_array) && empty($local_files_array) && empty($external_files_array)) {
            $html .= b5_file_manager_empty_folder_get_html();
        }

        return $html;
    }

    //Folder get all children
    function b5_file_manager_children_folders($folder_id) {
        $args = array(
            'post_parent' => $folder_id,
            'numberposts' => -1,
            'post_type'   => 'folder');

        return get_children($args);
    }

    //Folder number of children
    function b5_file_manager_children_folders_number($folder_id) {
        return (int) count(b5_file_manager_children_folders($folder_id));
    }

    function b5_file_manager_children_folders_user_access_number($post_id, $user_id) {
        $folders = b5_file_manager_children_folders($post_id);
        $number = 0;
        foreach($folders as $folder) {
            if(b5_file_manager_show_folder_for_user($folder->ID, $user_id)) {
                $number ++;
            }
        }

        return $number;
    }

    function b5_file_manager_empty_folder_get_html() {
        $div = '<div class="%s">%s</div>';
        return sprintf(
            $div,
            'b5-message b5-empty-folder',
            get_option('b5-file-manager-folder-empty-message', 'Empty folder')
        );
    }

    function b5_file_manager_folder_get_html($folder_id) {
        $size = get_option('b5-file-manager-icon-size', '60');
        $folder_post = get_post($folder_id);

        $date_format = get_option('b5-file-manager-date-format', 'wordpress') == 'wordpress' ? get_option('date_format', 'j/n/Y') : get_option('b5-file-manager-date-format', 'd/m/Y');
        $show_date = get_option('b5-file-manager-show-file-date', 'on') == 'on' ? true : false;
        $date_html = $show_date ? '<li><p class="b5-folder-date">'.date($date_format, strtotime($folder_post->post_date)).'</p></li>' : '';

        $extra_fields = get_option('b5-file-manager-extra-fields', array());
        $extra_fields_item_html = "";

        foreach($extra_fields as $key => $extra_field_name) {
            $extra_fields_item_html .= ' data-'.$key.'="" ';
        }

        $li = '<li class="%s">
                    <input type="hidden" class="b5-item-data" data-title="%s" data-weight="0" data-date="%s" data-type="%s" data-identifier="a" %s/>
                    <div class="%s">
                        <div class="%s">
                            <a href="#" class="%s" data-folder_id="%s">%s</a>
                        </div>
                        <div class="%s">
                            <p class="%s"><a href="#" class="%s" data-folder_id="%s">%s</a></p>
                            <ul class="b5-infowrapper">
                            '.$date_html.'
                            </ul>
                         </div>
                    </div>
                </li>';

        $folder_icon = '<div class="dashicons dashicons-category"></div>';

        $show_custom_icon = get_option('b5-file-manager-icon-type', 'dashicons');

        if($show_custom_icon == 'custom') {
            $folder_icon_id = get_option('b5-file-manager-folder-icon-id', 0);
            if($folder_icon_id != 0) {
                $folder_icon = wp_get_attachment_image($folder_icon_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
            }
        }

        $classes = array('b5-folder');

        return sprintf(
            $li,
            'b5-item',
            sanitize_text_field(strtolower($folder_post->post_title)),
            //$folder_post->post_title,
            strtotime($folder_post->post_date),
            'folder',
            $extra_fields_item_html,
            implode(' ', $classes),
            'b5-icon',
            'b5-folder-name',
            $folder_id,
            $folder_icon,
            'b5-info',
            'b5-title',
            'b5-folder-name',
            $folder_id,
            trim($folder_post->post_title) ? $folder_post->post_title : __('(no title)')
        );
    }

    function b5_file_manager_show_breadcrumbs_html($folder_id) {
        $html = '';

        $folders_array = array();

        $folder_post = get_post($folder_id);
        while($folder_post->post_parent > 0) {
            if(b5_file_manager_show_folder_for_user($folder_post->ID, get_current_user_id())) {
                $folders_array[] = $folder_post;
            }
            $folder_post = get_post($folder_post->post_parent);
        }

        if(b5_file_manager_show_folder_for_user($folder_post->ID, get_current_user_id())) {
            $folders_array[] = $folder_post;
        }

        foreach (array_reverse($folders_array) as $folder) {
            $html .= sprintf('<li><a href="#" class="%s%s" data-folder_id="%s">%s</a></li>',
                'b5-folder-name',
                $folder->ID==$folder_id ? ' b5-last' : '',
                $folder->ID,
                trim($folder->post_title) ? $folder->post_title : __("(no title)")
            );
        }

        return $html;
    }

    function b5_file_manager_folder_information_html($folder_id) {
        $html = '';

        $subfolders = b5_file_manager_children_folders_user_access_number($folder_id, get_current_user_id());

        if($subfolders == 1) {
            $html .= $subfolders." ".__('Folder', B5FILEMANAGER_PLUGIN_NAME);
        } else {
            $html .= $subfolders." ".__('Folders', B5FILEMANAGER_PLUGIN_NAME);
        }

        $files = b5_file_manager_folder_files_number($folder_id);

        if($files == 1) {
            $html .= ", ".$files." ".__('File', B5FILEMANAGER_PLUGIN_NAME)." (".b5_file_manager_folder_files_weight_format($folder_id).")";
        } else {
            if($files > 0) {
                $html .= ", ".$files." ".__('Files', B5FILEMANAGER_PLUGIN_NAME)." (".b5_file_manager_folder_files_weight_format($folder_id).")";
            }
        }

        return $html;
    }

    //Folder get all users
    function b5_file_manager_folder_users($folder_id) {
        $users_meta = get_post_meta($folder_id, 'b5_file_manager_folder_users');
        if(!empty($users_meta) && is_array($users_meta)) {
            return $users_meta[0];
        } else {
            return array();
        }
    }

    function b5_file_manager_show_folder_for_user($folder_id, $user_id) {
        $access = false;
        $folder_post = get_post($folder_id);
        if($folder_post) {
            if(b5_file_manager_get_folder_access($folder_post->ID)) {
                $access = true;
            } else {
                $groups = b5_file_manager_folder_group($folder_id);
                $all_users = array();
                foreach($groups as $group_id) {
                    $users = b5_file_manager_users_group($group_id);
                    foreach($users as $userid) {
                        $all_users[] = $userid;
                    }
                }

                $folder_users = b5_file_manager_folder_users($folder_id);

                foreach($folder_users as $userid) {
                    $all_users[] = $userid;
                }

                $access = in_array($user_id, $all_users);
            }
        }

        return $access;
    }

    /******************************* File functions *************************************/
    function b5_file_manager_file_get_html($file_id, $folder_id, $post_id = 0) {

        $classes = array('b5-file');
        $i18n_download = get_option('b5-file-manager-download-message', 'Download');
        $show_extension = get_option('b5-file-manager-show-file-extension', 'on') == 'on' ? true : false;
        $date_format = get_option('b5-file-manager-date-format', 'wordpress') == 'wordpress' ? get_option('date_format', 'j/n/Y') : get_option('b5-file-manager-date-format', 'd/m/Y');
        $show_download_icon = get_option('b5-file-manager-show-download-icon', 'on') == 'on' ? true : false;
        $download_icon_position = get_option('b5-file-manager-icon-position', 'before');

        $file_post = get_post($file_id);
        $show_date = get_option('b5-file-manager-show-file-date', 'on') == 'on' ? true : false;
        $show_weight = get_option('b5-file-manager-show-file-weight', 'on') == 'on' ? true : false;
        $show_download_number = get_option('b5-file-manager-show-file-downloads', 'on') == 'on' ? true : false;

        $weight_html = $show_weight ? '<li><p class="b5-item-weight">'.b5_file_manager_file_weight($file_id).'</p></li>' : '';
        $date_html = $show_date ? '<li><p class="b5-item-date">'.date($date_format, strtotime($file_post->post_date)).'</p></li>' : '';
        $downloads = get_option('b5-file-manager-download-information', 'on') == 'on' ? b5_file_manager_file_folder_downloads($folder_id, $file_id) : b5_file_manager_file_downloads($file_id);
        $downloads_html = $show_download_number ? '<li><p class="b5-downloads">'.$downloads.' downloads'.'</p></li>' : '';

        $extra_fields = get_option('b5-file-manager-extra-fields', array());
        $extra_fields_html = "";
        $extra_fields_item_html = "";

        foreach($extra_fields as $key => $extra_field_name) {
            $meta_field_value = get_post_meta($file_id, $key, true);
            $extra_fields_html .= '<li><p class="b5-item-extra-field '.$key.'">'.$meta_field_value.'</p></li>';
            $extra_fields_item_html .= ' data-'.$key.'="'.sanitize_text_field($meta_field_value).'" ';
        }

        $li = '<li class="%s">
                    <input type="hidden" class="b5-item-data" data-title="%s" data-weight="%s" data-date="%s" data-type="%s" data-identifier="b" %s/>
                    <div class="%s">
                        <div class="%s">'.b5_file_manager_icon_html($file_id).'</div>
                        <div class="%s">
                            <p class="%s">%s%s</p>
                            <ul class="b5-infowrapper">
                                '.$extra_fields_html.'
                                '.$weight_html.'
                                '.$date_html.'
                                '.$downloads_html.'
                                <li><a class="%s%s%s%s" href="%s" data-field_id="%s" data-folder_id="%s" target="%s" title="%s">%s</a></li>
                            </ul>
                         </div>
                    </div>
                </li>';

        return sprintf(
            $li,
            'b5-item',
            sanitize_text_field(strtolower($file_post->post_title)),
            b5_file_manager_size($file_id),
            strtotime($file_post->post_date),
            b5_file_manager_file_extension_type($file_id),
            $extra_fields_item_html,
            implode(' ', $classes),
            'b5-icon',
            'b5-info',
            'b5-title',
            trim($file_post->post_title) ? $file_post->post_title : __('(no title)'),
            $show_extension ? '.'.b5_file_manager_extension($file_id).'' : '',
            'b5-download',
            $show_download_icon ? ' b5-download-icon' : '',
            $show_download_icon ? ' '.$download_icon_position : '',
            b5_file_manager_is_external_file($file_id) ? ' b5-external-file' : '',
            b5_file_manager_download_url($file_id, $folder_id, $post_id),
            $file_id,
            $folder_id,
            b5_file_manager_is_external_file($file_id) ? '_blank' : '_self',
            __('Download file', B5FILEMANAGER_PLUGIN_NAME),
            $i18n_download
        );
    }

    function b5_file_manager_download_url($file_id, $folder_id, $post_id = 0) {
        $post_file = get_post($file_id);
        $download_link = null;
        if($post_file->post_type == 'external_file') {
            $download_link = get_post_meta($file_id, 'b5_external_file_link', true);
        } else {
            // $download_link = esc_url(add_query_arg('b5-file', $file_id, get_permalink($post_id)));
            // $download_link = esc_url(add_query_arg('b5-folder', $folder_id, $download_link));
            $download_link = wp_get_attachment_url($file_id);
        }
        return $download_link;
    }

    function b5_file_manager_file_url($file_id) {
        $post_file = get_post($file_id);
        $file_link = null;
        if($post_file->post_type == 'external_file') {
            $file_link = get_post_meta($file_id, 'b5_external_file_link', true);
        } else {
            //$file_link = esc_url(add_query_arg('b5-file', $post_id, get_permalink()));
            $file_link = $post_file->guid;
        }
        return $file_link;
    }

    function b5_file_manager_is_external_file($file_id) {
        return get_post_type($file_id) === 'external_file';
    }

    function b5_file_manager_file_weight($file_id) {
        $file_size = b5_file_manager_size($file_id);
        return size_format($file_size, intval(get_option('b5-file-manager-date-decimal', 0)));
    }

    function b5_file_manager_icon_html($attachment_id) {

        $icon_type = get_option('b5-file-manager-icon-type', 'dashicons');
        $attachment_image = null;

        switch($icon_type) {
            case 'custom':
                $attachment_image = b5_file_manager_file_custom_html($attachment_id);
                break;
            case 'dashicons':
            default: $attachment_image = b5_file_manager_file_dashicon_html($attachment_id);
                break;
        }

        return $attachment_image;
    }

    function b5_file_manager_file_custom_html($attachment_id) {

        $size = get_option('b5-file-manager-icon-size', '60');
        $ext_type = b5_file_manager_file_extension_type($attachment_id);

        $attachment_post = get_post($attachment_id);

        $custom_html = null;
        $thumbnail_id = null;

        switch($ext_type) {
            case 'image':
                //$custom_html = wp_get_attachment_image($attachment_id, 'thumbnail', true);
                if(get_option('b5-file-manager-image-thumbnails', 'on') == 'on') {
                    if(get_post_type($attachment_id) == 'external_file') {
                        $thumbnail_id = get_post_thumbnail_id($attachment_id);
                        if($thumbnail_id > 0) {
                            $post_thumbnail = get_post($thumbnail_id);
                            if(get_option('b5-file-manager-image-lightbox', 'on') == 'on') {
                                $custom_html = '<a href="'.$post_thumbnail->guid.'" data-lightbox="b5-images-preview" data-title="'.$attachment_post->post_title.'">'.wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true).'</a>';
                            } else {
                                $custom_html = wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                            }
                        } else {
                            $image_thumb_id = get_option('b5-file-manager-'.$ext_type.'-icon-id', '0');
                            if($image_thumb_id == '0') {
                                $custom_html = wp_get_attachment_image($attachment_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                            } else {
                                $custom_html = wp_get_attachment_image($image_thumb_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                            }
                        }
                    } else {
                        $thumbnail = get_post_meta($attachment_id, 'b5_file_manager_thumbnail');
                        if(count($thumbnail) > 0) {
                            $thumbnail_id = $thumbnail[0];
                        }
                        if($thumbnail_id > 0) {
                            $post_thumbnail = get_post($thumbnail_id);
                            if(get_option('b5-file-manager-image-lightbox', 'on') == 'on') {
                                $custom_html = '<a href="'.$post_thumbnail->guid.'" data-lightbox="b5-images-preview" data-title="'.$attachment_post->post_title.'">'.wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true).'</a>';
                            } else {
                                $custom_html = wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                            }
                        } else {
                            if(get_option('b5-file-manager-image-lightbox', 'on') == 'on') {
                                $custom_html = '<a href="'.$attachment_post->guid.'" data-lightbox="b5-images-preview" data-title="'.$attachment_post->post_title.'">'.wp_get_attachment_image($attachment_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true).'</a>';
                            } else {
                                $custom_html = wp_get_attachment_image($attachment_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                            }

                        }
                    }
                } else {
                    $image_thumb_id = get_option('b5-file-manager-image-icon-id', '0');
                    if($image_thumb_id != '0') {
                        $custom_html = wp_get_attachment_image($image_thumb_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                    } else {
                        $custom_html = wp_get_attachment_image($attachment_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                    }
                }
                break;
            case 'audio':
            case 'video':
            case 'spreadsheet':
            case 'interactive':
            case 'document':
            case 'text':
            case 'archive':
            case 'code':
            case 'file':
            default:

                if(get_post_type($attachment_id) == 'external_file') {
                    $thumbnail_id = get_post_thumbnail_id($attachment_id);
                } else {
                    $thumbnail = get_post_meta($attachment_id, 'b5_file_manager_thumbnail');
                    if(count($thumbnail) > 0) {
                        $thumbnail_id = $thumbnail[0];
                    }
                }

                if($thumbnail_id) {
                    $thumbnail_post = get_post($thumbnail_id);
                    if(get_option('b5-file-manager-image-thumbnails', 'on') == 'on') {
                        $preview_guid =  $thumbnail_post->guid;
                        $custom_html = '<a href="'.$preview_guid.'" data-lightbox="b5-images-preview" data-title="'.$attachment_post->post_title.'">'.wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true).'</a>';
                    } else {
                        $custom_html = wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                    }
                } else {
                    $image_thumb_id = get_option('b5-file-manager-'.$ext_type.'-icon-id', '0');
                    if($image_thumb_id != '0') {
                        $custom_html = wp_get_attachment_image($image_thumb_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                    } else {
                        $custom_html = wp_get_attachment_image($attachment_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                    }
                }

                break;
        }

        return $custom_html;
    }

    function b5_file_manager_file_dashicon_html($attachment_id) {

        global $wp_version;

        $size = get_option('b5-file-manager-icon-size', '60');

        $ext_type = b5_file_manager_file_extension_type($attachment_id);
        $attachment_post = get_post($attachment_id);


        $file_thumbnail = null;
        $thumbnail_post = null;
        $thumbnail_id = b5_file_manager_thumbnail_id($attachment_id);

        $dashicon_html = null;

        if($thumbnail_id > 0) {
            $thumbnail_post = get_post($thumbnail_id);
            if(get_option('b5-file-manager-image-lightbox', 'on') == 'on') {
                $preview_guid =  $thumbnail_post->guid;
                $dashicon_html = '<a href="'.$preview_guid.'" data-lightbox="b5-images-preview" data-title="'.$attachment_post->post_title.'">'.wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true).'</a>';
            } else {
                $dashicon_html = wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
            }
        }

        switch($ext_type) {
            case 'image':
                if(get_option('b5-file-manager-image-thumbnails', 'on') == 'on') {

                    if($thumbnail_id > 0) {
                        $file_thumbnail = wp_get_attachment_image($thumbnail_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                        $thumbnail_post = get_post($thumbnail_id);
                    } else {
                        if(get_post_type($attachment_id) == 'attachment') {
                            $file_thumbnail = wp_get_attachment_image($attachment_id, array(is_integer((int)$size)?$size:60, is_integer((int)$size)?$size:60), true);
                        } else {
                            $file_thumbnail = '<div class="dashicons dashicons-format-image"></div>';
                        }
                    }

                    if(get_option('b5-file-manager-image-lightbox', 'on') == 'on' && ($thumbnail_id > 0 || get_post_type($attachment_id) == 'attachment')) {
                        $preview_guid = $thumbnail_post ? $thumbnail_post->guid : $attachment_post->guid;
                        $dashicon_html = '<a href="'.$preview_guid.'" data-lightbox="b5-images-preview" data-title="'.$attachment_post->post_title.'">'.$file_thumbnail.'</a>';
                    } else {
                        $dashicon_html = $file_thumbnail;
                    }
                } else {
                    $dashicon_html = '<div class="dashicons dashicons-format-image"></div>';
                }
                break;
            case 'audio':
                if(!$dashicon_html) {
                    $dashicon_html = $wp_version < 3.9 ? '<div class="dashicons dashicons-format-audio"></div>' : '<div class="dashicons dashicons-media-audio"></div>';
                }
                break;
            case 'video':
                if(!$dashicon_html) {
                    $dashicon_html = $wp_version < 3.9 ? '<div class="dashicons dashicons-format-video"></div>' : '<div class="dashicons dashicons-media-video"></div>';
                }
                break;
            case 'spreadsheet':
                if(!$dashicon_html) {
                    $dashicon_html = $wp_version < 3.9 ? '<div class="dashicons dashicons-analytics"></div>' : '<div class="dashicons dashicons-media-spreadsheet"></div>';
                }
                break;
            case 'interactive':
            case 'document':
            case 'text':
            case 'archive':
            case 'code':
                if(!$dashicon_html) {
                    $dashicon_html = $wp_version < 3.9 ? '<div class="dashicons dashicons-format-standard"></div>' : '<div class="dashicons dashicons-media-'.$ext_type.'"></div>';
                }
                break;
            case 'file':
            default:
                if(!$dashicon_html) {
                    $dashicon_html = $wp_version < 3.9 ? '<div class="dashicons dashicons-format-standard"></div>' : '<div class="dashicons dashicons-media-default"></div>';
                }
                break;
        }

        return $dashicon_html;
    }

    /*Downloads logs*/
    function b5_file_manager_file_folder_downloads($folder_id, $file_id) {
        global $wpdb;
        return intval($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM " . $wpdb->prefix . "b5_file_manager_downloads WHERE file_id = '%s' AND folder_id = '%s';", $file_id,  $folder_id)));
    }

    function b5_file_manager_file_downloads($file_id) {
        global $wpdb;
        return intval($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM " . $wpdb->prefix . "b5_file_manager_downloads WHERE file_id = '%s';", $file_id)));
    }

    function b5_file_manager_folder_downloads($folder_id) {
        global $wpdb;
        return intval($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM " . $wpdb->prefix . "b5_file_manager_downloads WHERE folder_id = '%s';", $folder_id)));
    }

    function b5_file_manager_delete_file_folder_downloads($folder_id, $file_id) {
        global $wpdb;
        $wpdb->query(
            'DELETE FROM ' . $wpdb->prefix . 'b5_file_manager_downloads WHERE folder_id="'.$folder_id.'" AND file_id="'.$file_id.'";'
        );
    }

    function b5_file_manager_delete_file_downloads($file_id) {
        global $wpdb;
        $wpdb->query(
            'DELETE FROM ' . $wpdb->prefix . 'b5_file_manager_downloads WHERE file_id="'.$file_id.'";'
        );
    }

    function b5_file_manager_delete_folder_downloads($folder_id) {
        global $wpdb;
        $wpdb->query(
            'DELETE FROM ' . $wpdb->prefix . 'b5_file_manager_downloads WHERE folder_id="'.$folder_id.'";'
        );
    }

    function b5_file_manager_register_download($file_id, $folder_id) {
        $visitor_ip = $_SERVER["REMOTE_ADDR"];
        $date_time = current_time('mysql');

        if(is_user_logged_in()) {
            global $current_user;
            get_currentuserinfo();
            $visitor_name = $current_user->user_login;
        } else {
            $visitor_name = __('Anonymous', B5FILEMANAGER_PLUGIN_NAME);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'b5_file_manager_downloads';
        $data = array(
            'file_id' => $file_id,
            'folder_id' => $folder_id,
            'visitor_ip' => $visitor_ip,
            'visitor_id' => get_current_user_id(),
            'date_time' => $date_time,
            'visitor_name' => $visitor_name
        );

        $wpdb->insert($table, $data);
    }

    //File in folder
    function b5_file_manager_file_in_folder($file_id, $folder_id) {
        return in_array($file_id, b5_file_manager_all_files($folder_id));
    }

    //Folder number of files
    function b5_file_manager_folder_files_number($folder_id) {
        return b5_file_manager_folder_local_files_number($folder_id) + b5_file_manager_folder_external_files_number($folder_id);
    }

    //Folder number of local files
    function b5_file_manager_folder_local_files_number($folder_id) {
        return (int) count(b5_file_manager_folder_local_files($folder_id));
    }

    //Folder number of external files
    function b5_file_manager_folder_external_files_number($folder_id) {
        return (int) count(b5_file_manager_folder_external_files($folder_id));
    }

    //Folder get all local files
    function b5_file_manager_folder_local_files($folder_id) {
        $local_files_meta = get_post_meta($folder_id, 'b5_file_manager_files');

        return $local_files_meta;
    }

    function b5_file_manager_folder_local_files_weight($folder_id) {
        $external_files_ids = b5_file_manager_folder_local_files($folder_id);
        $files_weight = 0;

        foreach($external_files_ids as $id) {
            $files_weight += b5_file_manager_size($id);
        }

        return $files_weight;
    }

    //Folder get all external files
    function b5_file_manager_folder_external_files($folder_id) {
        $external_files_meta = (array) get_post_meta($folder_id, 'b5_external_file');
        $ids = array();
        foreach($external_files_meta as $id) {
            if(get_post($id)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    //Folder get all files
    function b5_file_manager_all_files($folder_id) {
        return array_merge(b5_file_manager_folder_external_files($folder_id), b5_file_manager_folder_local_files($folder_id));
    }

    function b5_file_manager_folder_external_files_weight($folder_id) {
        $external_files_ids = b5_file_manager_folder_external_files($folder_id);
        $files_weight = 0;

        foreach($external_files_ids as $id) {
            $files_weight += b5_file_manager_size($id);;
        }

        return $files_weight;
    }

    function b5_file_manager_folder_files_weight($folder_id) {
        return b5_file_manager_folder_local_files_weight($folder_id) + b5_file_manager_folder_external_files_weight($folder_id);
    }

    function b5_file_manager_folder_files_weight_format($folder_id) {
        return size_format(b5_file_manager_folder_files_weight($folder_id), intval(get_option('b5-file-manager-date-decimal', 0)));
    }

    //Users group get all user from group
    function b5_file_manager_users_group($groupid) {
        return  get_post_meta($groupid, 'b5_file_manager_users');
    }

    //Check if user in group
    function b5_file_manager_user_in_group($group_id, $user_id) {
        $users_meta = b5_file_manager_users_group($group_id);

        return in_array($user_id, $users_meta);
    }

    //Folder get all users group
    function b5_file_manager_folder_group($folder_id) {
        $groups_meta = get_post_meta($folder_id, 'b5_file_manager_folder_groups');

        if(!empty($groups_meta) && is_array($groups_meta)) {
            return $groups_meta[0];
        } else {
            return array();
        }
    }

    //Folder number of users group
    function b5_file_manager_users_group_number($group_id) {
        return (int) count(b5_file_manager_users_group($group_id));
    }

    function b5_file_manager_extension($file_id) {
        $file_link = b5_file_manager_file_url($file_id);
        return trim(preg_replace('/^.+?\.([^.]+)$/', '$1', $file_link));
    }

    function b5_file_manager_file_extension_type($attachment_id) {
        $type = 'file';
        $ext = b5_file_manager_extension($attachment_id);
        if ($ext_type = wp_ext2type($ext)) {
            $type = $ext_type;
        }
        return $type;
    }

    function b5_file_manager_size($attachment_id) {
        $file_size = 0;
        if(get_post_type($attachment_id) == 'attachment') {
            $file  = get_attached_file($attachment_id);
            if (file_exists($file)) {
                $file_size = filesize($file);
            }
        } else {
            $file_size = get_post_meta($attachment_id, 'b5_external_file_weight', true);
        }
        return intval($file_size);
    }

    function b5_file_manager_thumbnail_id($file_id) {
        $thumbnail_id = null;
        if(get_post_type($file_id) == 'external_file') {
            $thumbnail_id = get_post_thumbnail_id($file_id);
        } else {
            $thumbnail = get_post_meta($file_id, 'b5_file_manager_thumbnail');
            if(count($thumbnail) > 0) {
                $thumbnail_id = $thumbnail[0];
            }
        }

        return $thumbnail_id;
    }

    //Folder get access
    function b5_file_manager_get_folder_access($folder_id) {
        $folder_access = get_post_meta($folder_id, 'b5_file_manager_folder_access', false);
        return count($folder_access) > 0 ? $folder_access[0] : false;
    }