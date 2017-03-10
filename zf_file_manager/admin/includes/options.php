<?php
    include_once('list-table-class/downloads-all-list-table.php');
    include_once('list-table-class/downloads-file-list-table.php');
    include_once('list-table-class/downloads-folder-list-table.php');
    include_once('list-table-class/downloads-username-list-table.php');

    add_action('admin_menu', 'b5_file_manager_configuration');
    function b5_file_manager_configuration() {

        add_submenu_page(
            'edit.php?post_type=folder',
            __('Users Group', B5FILEMANAGER_PLUGIN_NAME),
            __('All Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'edit_pages', 'edit.php?post_type=users_group_file'
        );

        add_submenu_page(
            'edit.php?post_type=folder',
            __('Users Group', B5FILEMANAGER_PLUGIN_NAME),
            __('Add Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'edit_pages', 'post-new.php?post_type=users_group_file'
        );

        add_submenu_page(
            'edit.php?post_type=folder',
            __('External Files', B5FILEMANAGER_PLUGIN_NAME),
            __('All External Files', B5FILEMANAGER_PLUGIN_NAME),
            'edit_pages', 'edit.php?post_type=external_file'
        );

        add_submenu_page(
            'edit.php?post_type=folder',
            __('External Files', B5FILEMANAGER_PLUGIN_NAME),
            __('Add External File', B5FILEMANAGER_PLUGIN_NAME),
            'edit_pages', 'post-new.php?post_type=external_file'
        );

        add_submenu_page(
            'edit.php?post_type=folder',
            __('Downloads', B5FILEMANAGER_PLUGIN_NAME),
            __('Downloads', B5FILEMANAGER_PLUGIN_NAME),
            'edit_pages',
            'b5_all_downloads',
            'b5_file_manager_create_all_logs_page'
        );

        add_submenu_page(
            'edit.php?post_type=folder',
            __('Folder Configuration', B5FILEMANAGER_PLUGIN_NAME),
            __('Configuration', B5FILEMANAGER_PLUGIN_NAME),
            'edit_pages', 'b5_folder_configuration',
            'b5_file_manager_configuration_html'
        );
    }

    add_filter('plugin_action_links', 'b5_file_manager_plugin_settings_link', 10, 2);
    function b5_file_manager_plugin_settings_link($links, $file) {
        if(!is_admin() || !current_user_can('edit_pages'))
            return $links;

        if($file == B5FILEMANAGER_PLUGIN_BASENAME) {
            $settings_link = sprintf('<a href="%s">%s</a>', admin_url('edit.php?post_type=folder&page=b5_folder_configuration'), __('Settings', B5FILEMANAGER_PLUGIN_NAME));
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    function b5_file_manager_create_all_logs_page() {
        if(isset($_GET['file_id']) && !empty($_GET['file_id']) && intval($_GET['file_id']) > 0) {
            $downloadsListTable = new downloads_File_List_Table($_GET['file_id']);
            $page_title = __('Download file logs', B5FILEMANAGER_PLUGIN_NAME);
            $subtitle = sprintf('<span class="subtitle">' . __('File: %s') . '</span>', esc_html($downloadsListTable->get_file_name()));
        } else if(isset($_GET['folder_id']) && !empty($_GET['folder_id']) && intval($_GET['folder_id']) > 0) {
            $downloadsListTable = new downloads_Folder_List_Table($_GET['folder_id']);
            $page_title = __('Download folder logs', B5FILEMANAGER_PLUGIN_NAME);
            $subtitle = sprintf('<span class="subtitle">' . __('Folder: %s') . '</span>', esc_html($downloadsListTable->get_folder_name()));
        } else if(isset($_GET['username']) && !empty($_GET['username'])) {
            $downloadsListTable = new downloads_Username_List_Table($_GET['username']);
            $page_title = __('Download username logs', B5FILEMANAGER_PLUGIN_NAME);
            $subtitle = sprintf('<span class="subtitle">' . __('Username: %s') . '</span>', esc_html($downloadsListTable->username));
        } else {
            $downloadsListTable = new downloads_All_List_Table();
            $page_title = __('Download logs', B5FILEMANAGER_PLUGIN_NAME);
            $subtitle = "";
        }

        $downloadsListTable->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"><br/></div>
                <h2>
                    <?php
                        echo esc_html($page_title);
                        echo $subtitle;
                    ?>
                </h2>
                <?php $downloadsListTable->views(); ?>
                <form id="b5_file_manager_downloads-filter" method="post">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <?php $downloadsListTable->display();?>
                </form>

            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('.fade').click(function() {
                        $(this).fadeOut('slow');
                    });
                });
            </script>
        <?php
    }

    function b5_file_manager_configuration_html() {
        $b5_file_manager_icon_options = b5_file_manager_icon_options();
        $b5_file_manager_format_date = b5_file_manager_date_options();
        $b5_file_manager_default_sort_options = b5_file_manager_default_sort_options();?>

        <div id="b5_file_manager_configuration_container">
            <div class="b5_file_manager_wrap">

                <div id="b5_file_manager_header">
                    <div class="b5_file_manager_logo"></div>
                    <div class="b5_file_manager_buttonContainer">
                        <input type="button" id="b5_file_manager_reset_settings_button" name="reset" class="button-primary" value="<?php _e('Reset Settings', B5FILEMANAGER_PLUGIN_NAME);?>" />
                        <input type="button" id="b5_file_manager_update_settings_button" name="update" class="button-primary" value="<?php _e('Update Settings', B5FILEMANAGER_PLUGIN_NAME);?>" />
                    </div>
                    <div style="clear:both"></div>
                </div>

                <form method="post" action="<?php admin_url('admin.php?page=b5_folder_configuration');?>"><?php echo settings_fields('b5-file-manager-option-group');?>
                    <div class="b5_file_manager_tabs_container">
                        <div class="b5_file_manager_tabs">
                            <div class="b5_file_manager_tab selected first" id="tab_menu_1">
                                <div class="link"><div class="file_icon icon_settings"></div><span><?php _e('General Settings', B5FILEMANAGER_PLUGIN_NAME);?></span></div>
                            </div>
                            <div class="b5_file_manager_tab" id="tab_menu_2">
                                <div class="link"><div class="file_icon icon_colors"></div><span><?php _e('Colors', B5FILEMANAGER_PLUGIN_NAME);?></span></div>
                            </div>
                            <div class="b5_file_manager_tab" id="tab_menu_3">
                                <div class="link"><div class="file_icon icon_messages"></div><span><?php _e('Messages', B5FILEMANAGER_PLUGIN_NAME);?></span></div>
                            </div>
                            <div class="b5_file_manager_tab" id="tab_menu_4">
                                <div class="link"><div class="file_icon icon_icons"></div><span><?php _e('Icons', B5FILEMANAGER_PLUGIN_NAME);?></span></div>
                            </div>
                            <div class="b5_file_manager_tab" id="tab_menu_5">
                                <div class="link"><div class="file_icon icon_upload"></div><span><?php _e('Upload Settings', B5FILEMANAGER_PLUGIN_NAME);?></span></div>
                            </div>
                        </div><!-- tabs -->
                        <div class="b5_file_manager_curvedContainer">
                            <!-- tab_content_1 -->
                            <div class="b5_file_manager_tabcontent" id="b5_file_manager_tab_content_1" style="display:block">
                                <!-- Option1 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Default view', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <select id="b5_file_manager_default_view">
                                            <option <?php echo 'grid' == get_option('b5-file-manager-default-view', 'grid') ? ' selected="selected" ' :'';?> value="grid"><?php _e('Grid', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                            <option <?php echo 'list' == get_option('b5-file-manager-default-view', 'grid') ? ' selected="selected" ' :'';?> value="list" ><?php _e('List', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                        </select>
                                        <p class="b5_file_manager_description"><?php _e('Files default view.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option1 -->

                                <!-- Option2 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Default sort', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <select id="b5_file_manager_default_sort">
                                            <option <?php echo 'asc' == get_option('b5-file-manager-default-sort', 'asc') ? ' selected="selected" ' :'';?> value="asc"><?php _e('Asc', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                            <option <?php echo 'desc' == get_option('b5-file-manager-default-sort', 'asc') ? ' selected="selected" ' :'';?> value="desc" ><?php _e('Desc', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                        </select>
                                        <p class="b5_file_manager_description"><?php _e('Files default sort direction.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option2 -->

                                <!-- Option3 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Default sort by', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <select id="b5_file_manager_default_sort_by">
                                            <?php foreach($b5_file_manager_default_sort_options['b5_default_sort_by'] as $key => $sort_by):?>
                                                <option <?php selected(get_option('b5-file-manager-default-sort-by', 'original'), $key, true);?> value="<?php echo $key;?>"><?php echo $sort_by;?></option>
                                            <?php endforeach;?>
                                            <?php foreach(get_option('b5-file-manager-extra-fields', array()) as $key => $extra_field_name):?>
                                                <option <?php selected(get_option('b5-file-manager-default-sort-by', 'original'), $key, true);?> value="<?php echo $key;?>"><?php echo $extra_field_name;?></option>
                                            <?php endforeach;?>
                                        </select>
                                        <p class="b5_file_manager_description"><?php _e('Files default sort by.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option3 -->

                                <!-- Option4 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Delete all plugin options', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_delete_disabled" <?php checked(get_option('b5-file-manager-delete-disabled', 'on'), 'on', true);?> type="checkbox">
                                        <p class="b5_file_manager_description"><?php _e('Delete all plugin options when disabled.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option4 -->

                                <!-- Option5 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show file extension?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_file_extension" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-file-extension', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Concatenate file extension to title.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option5 -->

                                <!-- Option6 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show file weight?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_file_weight" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-file-weight', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show file weight.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option6 -->

                                <!-- Option7 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show file date?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_file_date" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-file-date', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show file date.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option7 -->

                                <!-- Option8 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show file downloads?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_file_downloads" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-file-downloads', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show file downloads number.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option8 -->

                                <!-- Option9 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Download information', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_download_information" type="checkbox" data-on="<?php _e('Local', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('All', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-download-information', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Download information to show in folder.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option9 -->

                                <!-- Option10 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show spinner?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_spinner" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-spinner', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show spinner when file loading.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option10 -->

                                <!-- Option11 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show sort controls?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_sort_controls" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-sort-controls', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show sort controls.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option11 -->

                                <!-- Option12 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show view selector?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_view_selector" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-view-selector', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show view selector.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option12 -->

                                <!-- Option13 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show filter controls?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_filter_controls" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-filter-controls', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show filter controls.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option13 -->

                                <!-- Option14 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show folder information?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_folder_information" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-folder-information', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show folder footer information.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option14 -->

                                <!-- Option15 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show login form?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_login_form" type="checkbox" data-on="<?php _e('Enabled', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Disabled', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-login-form', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show login form if the current visitor is not logged in.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option15 -->

                                <!-- Option16 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Date format', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <select id="b5_file_manager_date_format">
                                            <?php foreach($b5_file_manager_format_date['b5_date_format'] as $key => $format_date):?>
                                                <option <?php selected(get_option('b5-file-manager-date-format', 'wordpress'), $key, true);?> value="<?php echo $key;?>"><?php echo $format_date;?></option>
                                            <?php endforeach;?>
                                        </select>
                                        <p class="b5_file_manager_description"><?php _e('Date format.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option16 -->

                                <!-- Option17 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Weight decimal places', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_date_decimal" type="text" value="<?php echo intval(get_option('b5-file-manager-date-decimal', 0));?>">
                                        <p class="b5_file_manager_description"><?php _e('Precision of number of weight decimal places.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option17 -->

                                <!-- Option18 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Extra fields', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <div id="b5_file_manager_extra_fields">
                                            <?php foreach(get_option('b5-file-manager-extra-fields', array()) as $key => $extra_field_name):?>
                                                <div class="b5_extra_field">
                                                    <a class="toggle describe-toggle-on b5-extra-field-delete" data-field_id="<?php echo $key;?>" data-delete_nonce="<?php echo wp_create_nonce("b5-delete_extra_field_nonce");?>" href="#"><?php _e('Delete', B5FILEMANAGER_PLUGIN_NAME);?></a>
                                                    <div class="b5_extra_field_name">
                                                        <input type="text" name="extra_field_name" data-fied_id="<?php echo $key;?>" value="<?php echo $extra_field_name;?>" />
                                                    </div>
                                                </div>
                                            <?php endforeach;?>
                                        </div>
                                        <a href="#" id="b5_file_manager_add_extra_field" data-generate_field_nonce="<?php echo wp_create_nonce("b5-generate_extra_field_nonce");?>"  class="button button-primary"><?php _e('Add', B5FILEMANAGER_PLUGIN_NAME);?></a>
                                        <p class="b5_file_manager_description"><?php _e('Media Files meta extra fields.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option18 -->

                                <!-- Option19 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Custom CSS', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <textarea id="b5_file_manager_custom_css"><?php echo esc_textarea(stripslashes(get_option('b5-file-manager-custom-css', '')));?></textarea>
                                        <p class="b5_file_manager_description"><?php _e('Add some CSS.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option19 -->
                            </div><!-- End tab_content_1 -->

                            <!-- tab_content_2 -->
                            <div class="b5_file_manager_tabcontent" id="b5_file_manager_tab_content_2">
                                <!-- Option1 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Dashicons Color', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_dashicons_color" value="<?php echo get_option('b5-file-manager-dashicons-color', '#d8d8d8');?>" class="b5-file-manager-popup-colorpicker" data-default-color="#d8d8d8"/>
                                        <p class="b5_file_manager_description"><?php _e('Dashicons color.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option1 -->

                                <!-- Option2 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Hover background color', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_hover_background_color" value="<?php echo get_option('b5-file-manager-hover-bg-color', '#f2f2f2');?>" class="b5-file-manager-popup-colorpicker" data-default-color="#f2f2f2"/>
                                        <p class="b5_file_manager_description"><?php _e('File item hover background color.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option2 -->

                                <!-- Option3 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Item border color', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_item_border_color" value="<?php echo get_option('b5-file-manager-item-border-color', '#C7C7C7');?>" class="b5-file-manager-popup-colorpicker" data-default-color="#C7C7C7"/>
                                        <p class="b5_file_manager_description"><?php _e('List view item border color.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option3 -->

                                <!-- Option4 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Download button background color', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_button_bg_color" value="<?php echo get_option('b5-file-manager-button-bg-color', '#CCC');?>" class="b5-file-manager-popup-colorpicker" data-default-color="#CCC"/>
                                        <p class="b5_file_manager_description"><?php _e('Download button background color.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option4 -->

                                <!-- Option5 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Menu Bar and Folder Information border color', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_menu_border_color" value="<?php echo get_option('b5-file-manager-menu-border-color', '#e5e5e5');?>" class="b5-file-manager-popup-colorpicker" data-default-color="#e5e5e5"/>
                                        <p class="b5_file_manager_description"><?php _e('Menu Bar and Folder Information border color.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option5 -->
                            </div><!-- End tab_content_2 -->

                            <!-- tab_content_3  -->
                            <div class="b5_file_manager_tabcontent" id="b5_file_manager_tab_content_3">
                                <!-- Option1 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Empty folders message', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_folder_empty_message" value="<?php echo get_option('b5-file-manager-folder-empty-message', __('Empty folder', B5FILEMANAGER_PLUGIN_NAME));?>" />
                                        <p class="b5_file_manager_description"><?php _e('Empty folders message.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option1 -->

                                <!-- Option2 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Download link message', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_download_message" value="<?php echo get_option('b5-file-manager-download-message', 'Download');?>" />
                                        <p class="b5_file_manager_description"><?php _e('Download link message.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option2 -->

                                <!-- Option2 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Filter placeholder message', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input type="text" id="b5_file_manager_filter_message" value="<?php echo get_option('b5-file-manager-filter-message', 'Filter');?>" />
                                        <p class="b5_file_manager_description"><?php _e('Filter placeholder message.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option2 -->
                            </div><!-- End tab_content_3 -->

                            <!-- tab_content_4 -->
                            <div class="b5_file_manager_tabcontent" id="b5_file_manager_tab_content_4">
                                <!-- Option1 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Icons type', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <select id="b5_file_manager_icon_type" class="b5_file_manager_icon_type">
                                            <option <?php echo 'dashicons' == get_option('b5-file-manager-icon-type') ? ' selected="selected" ' :''?> value="dashicons" ><?php _e('Dashicons', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                            <option <?php echo 'custom' == get_option('b5-file-manager-icon-type') ? ' selected="selected" ' :''?> value="custom"><?php _e('Custom', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                        </select>
                                        <p class="b5_file_manager_description"><?php _e('Icons type', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option1 -->

                                <!-- Option2 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Icon size', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_icon_size" type="text" value="<?php echo get_option('b5-file-manager-icon-size', '60') ?>">
                                        <p class="b5_file_manager_description"><?php _e('Icons size, do not include "px", by default a 60 px size is used.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option2 -->

                                <!-- Option3 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Thumbnails for images?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_image_thumbnails" type="checkbox" data-on="<?php _e('Show image thumbnail', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Show image icon', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-image-thumbnails', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show thumbnails for images', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option3 -->

                                <!-- Option4 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Lightbox for thumbnail images?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_image_lightbox" type="checkbox" data-on="<?php _e('Active thumbnail lightbox', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Hide thumbnail lightbox', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-image-lightbox', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show Lightbox for thumbnail images.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option4 -->

                                <!-- Option5 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Show download icon?', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <input id="b5_file_manager_show_download_icon" type="checkbox" data-on="<?php _e('Show icon', B5FILEMANAGER_PLUGIN_NAME);?>" data-off="<?php _e('Hide icon', B5FILEMANAGER_PLUGIN_NAME);?>" <?php checked('on', get_option('b5-file-manager-show-download-icon', 'on'), true);?>>
                                        <p class="b5_file_manager_description"><?php _e('Show download icon.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option5 -->

                                <!-- Option6 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Download icon position', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column">
                                        <select id="b5_file_manager_icon_position">
                                            <option <?php selected('before', get_option('b5-file-manager-icon-position', 'before'), true);?> value="before" ><?php _e('Before', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                            <option <?php selected('after', get_option('b5-file-manager-icon-position', 'before'), true);?> value="after"><?php _e('After', B5FILEMANAGER_PLUGIN_NAME);?></option>
                                        </select>
                                        <p class="b5_file_manager_description"><?php _e('Download icon position.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option6 -->

                                <!-- Option7 -->
                                <div class="info_section b5_file_manager_icon">
                                    <h3 class="b5_file_manager_section"><?php _e('Custom Folder Icon', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div class="b5_file_manager_theme_column b5_file_manager_columnhalf">
                                        <input class="b5_options_upload_image_id" type="hidden" id="b5_file_manager_folder_icon_id" value="<?php echo get_option('b5-file-manager-folder-icon-id', '0');?>" />
                                        <input class="b5_options_upload_image_button button-primary interior" type="button" value="<?php _e('Upload Image', B5FILEMANAGER_PLUGIN_NAME);?>" <?php echo ((get_option('b5-file-manager-folder-icon-id') != '' && get_option('b5-file-manager-folder-icon-id') != '0')) ? 'style="display:none;"' : '';?> />
                                        <p class="description"><?php _e('Upload a image that will represent Folder icon.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                    </div>
                                    <div id="options_preview" class="theme_column b5_file_manager_columnhalf">
                                        <div class="img_preview">
                                            <div id="folder_image_holder" class="b5_icon_image_holder" data-image_extension="folder">
                                                <?php if(get_option('b5-file-manager-folder-icon-id', 0) != 0) {
                                                    echo wp_get_attachment_image(get_option('b5-file-manager-folder-icon-id'), 'thumbnail', true);
                                                } ?>
                                            </div>
                                        </div>
                                        <input class="b5_options_remove_image_button button-primary interior" type="button" <?php echo ((get_option('b5-file-manager-folder-icon-id') == '' || get_option('b5-file-manager-folder-icon-id') == '0')) ? 'style="display:none;"' : '';?>  value="<?php _e('Remove Image', B5FILEMANAGER_PLUGIN_NAME);?>" />
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option7 -->

                                <?php foreach ($b5_file_manager_icon_options['b5_icon_extensions'] as $key => $icon_extension) : ?>
                                    <div class="info_section b5_file_manager_icon">
                                        <h3 class="b5_file_manager_section"><?php _e('Custom '.$icon_extension.' Icon', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                        <div class="b5_file_manager_theme_column b5_file_manager_columnhalf">
                                            <input class="b5_options_upload_image_id" type="hidden" id="b5_file_manager_<?php echo $key?>_icon_id" value="<?php echo get_option('b5-file-manager-'.$key.'-icon-id', '0') ?>" />
                                            <input class="b5_options_upload_image_button button-primary interior" type="button" value="<?php _e('Upload Image', B5FILEMANAGER_PLUGIN_NAME);?>" <?php echo ((get_option('b5-file-manager-'.$key.'-icon-id') != '' && get_option('b5-file-manager-'.$key.'-icon-id') != '0')) ? 'style="display:none;"' : '';?> />
                                            <p class="description"><?php _e('Upload a image that will represent '.$icon_extension.' icon.', B5FILEMANAGER_PLUGIN_NAME);?></p>
                                        </div>
                                        <div id="options_preview" class="theme_column b5_file_manager_columnhalf">
                                            <div class="img_preview">
                                                <div id="<?php echo $key?>_image_holder" class="b5_icon_image_holder" data-image_extension="<?php echo $key;?>">
                                                    <?php if(get_option('b5-file-manager-'.$key.'-icon-id', 0) != 0) {
                                                        echo wp_get_attachment_image(get_option('b5-file-manager-'.$key.'-icon-id'), 'thumbnail', true);
                                                    } ?>
                                                </div>
                                            </div>
                                            <input class="b5_options_remove_image_button button-primary interior" type="button" <?php echo ((get_option('b5-file-manager-'.$key.'-icon-id') == '' || get_option('b5-file-manager-'.$key.'-icon-id') == '0')) ? 'style="display:none;"' : '';?>  value="<?php _e('Remove Image', B5FILEMANAGER_PLUGIN_NAME);?>" />
                                        </div>
                                        <div class="clear"></div>
                                    </div><!-- End info_section -->
                                <?php endforeach;?>
                            </div><!-- End tab_content_4 -->

                            <!-- tab_content_5 -->
                            <div class="b5_file_manager_tabcontent" id="b5_file_manager_tab_content_5">
                                <!-- Option1 -->
                                <div class="info_section">
                                    <h3 class="b5_file_manager_section"><?php _e('Manage Upload Settings', B5FILEMANAGER_PLUGIN_NAME);?></h3>
                                    <div id="b5_file_manager_upload_types" class="b5_file_manager_theme_column">
                                        <?php $b5_file_manager_mime_types = get_allowed_mime_types();?>
                                        <input type="hidden" id="b5_delete_upload_type_nonce" value="<?php echo wp_create_nonce("b5-delete-upload_type");?>">
                                        <div class="b5_mime_item">
                                            <div>
                                                <div class="b5_mime extension"><span class="title"><strong><?php _e('Extension', B5FILEMANAGER_PLUGIN_NAME);?></strong></span></div>
                                                <div class="b5_mime mime"><span class="title"><strong><?php _e('Mime Type', B5FILEMANAGER_PLUGIN_NAME);?></strong></span></div>
                                            </div>
                                        </div>
                                        <?php foreach ($b5_file_manager_mime_types as $extension => $mimetype) : ?>
                                            <div class="b5_mime_item">
                                                <a class="toggle describe-toggle-on b5-upload-type-delete" data-upload_extension="<?php echo $extension;?>" href="#"><?php _e('Delete', B5FILEMANAGER_PLUGIN_NAME);?></a>
                                                <div>
                                                    <div class="b5_mime extension"><span class="title"><?php echo $extension;?></span></div>
                                                    <div class="b5_mime mime"><span class="title"><?php echo $mimetype;?></span></div>
                                                </div>
                                            </div>
                                        <?php endforeach;?>
                                        <div id="b5_mime_form" class="b5_mime_item">
                                            <!--<a class="toggle describe-toggle-on b5-upload-type-upload-list" href="#"><?php _e('Upload from list', B5FILEMANAGER_PLUGIN_NAME);?></a>-->
                                            <div>
                                                <div class="b5_mime extension upload"><input type="text" id="b5_mime_extension" /></div>
                                                <div class="b5_mime mime upload">
                                                    <input type="text" id="b5_mime_mime" />
                                                    <input id="b5_button_add_mime" data-add_upload_type_nonce="<?php echo wp_create_nonce("b5-add-upload_type");?>" type="button" class="button" value="<?php _e('Add', B5FILEMANAGER_PLUGIN_NAME);?>" />
                                                    <a id="b5_button_show_all_delete_mimes" class="toggle describe-toggle-on b5-upload-type-all-delete" href="#"><?php _e('Show all delete mimes', B5FILEMANAGER_PLUGIN_NAME);?></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="">
                                            <?php _e('You can check the complete MIME type list ', B5FILEMANAGER_PLUGIN_NAME);?>
                                            <a href="http://www.sitepoint.com/web-foundations/mime-types-complete-list/"><?php _e('here', B5FILEMANAGER_PLUGIN_NAME);?></a>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- End info_section -->
                                <!-- End Option1 -->
                            </div><!-- End tab_content_5 -->
                        </div><!-- End curvedContainer -->
                        <div style="clear:both"></div>
                    </div><!-- End tabsContainer -->
                </form>

                <div style="clear:both"></div>
            </div><!-- wrap -->

            <div id="b5_file_manager_savemessage" style="color:red;"/>

        </div>
        
       <p>Created by <a href="http://www.zerofractal.com" target="_blank"> Zerofractal</a></p>
    <?php
    }

/*
 * save general settings page on post.
 */
add_action('wp_ajax_b5_file_manager_update_settings', 'b5_file_manager_add_general_settings'); //creating Ajax call for WordPress
function b5_file_manager_add_general_settings() {
    global $wpdb; // this is how you get access to the database
    $b5_file_manager_icon_options = b5_file_manager_icon_options();

    // options
    if(get_option('b5-file-manager-icon-type')==true) {
        update_option('b5-file-manager-icon-type', 'dashicons');
    }

    if(get_option('b5-file-manager-image-thumbnails')==true) {
        update_option('b5-file-manager-image-thumbnails', 'on');
    }

    if(get_option('b5-file-manager-image-lightbox')==true) {
        update_option('b5-file-manager-image-lightbox', 'on');
    }

    if(get_option('b5-file-manager-folder-icon-id')==true) {
        update_option('b5-file-manager-folder-icon-id', 0);
    }

    if(get_option('b5-file-manager-dashicons-color')==true) {
        update_option('b5-file-manager-dashicons-color', '#d8d8d8');
    }

    if(get_option('b5-file-manager-menu-border-color')==true) {
        update_option('b5-file-manager-menu-border-color', '#e5e5e5');
    }

    if(get_option('b5-file-manager-icon-size')==true) {
        update_option('b5-file-manager-icon-size', 60);
    }

    if(get_option('b5-file-manager-default-view')==true) {
        update_option('b5-file-manager-default-view', 'grid');
    }

    if(get_option('b5-file-manager-default-sort-by')==true) {
        update_option('b5-file-manager-default-sort-by', 'original');
    }

    if(get_option('b5-file-manager-default-sort')==true) {
        update_option('b5-file-manager-default-sort', 'asc');
    }

    if(get_option('b5-file-manager-folder-empty-message')==true) {
        update_option('b5-file-manager-folder-empty-message', 'Empty folder');
    }

    if(get_option('b5-file-manager-download-message')==true) {
        update_option('b5-file-manager-download-message', 'Download');
    }

    if(get_option('b5-file-manager-filter-message')==true) {
        update_option('b5-file-manager-filter-message', 'Filter');
    }

    if(get_option('b5-file-manager-custom-css')==true) {
        update_option('b5-file-manager-custom-css', '');
    }

    if(get_option('b5-file-manager-show-file-extension')==true) {
        update_option('b5-file-manager-show-file-extension', 'on');
    }

    if(get_option('b5-file-manager-show-filter-controls')==true) {
        update_option('b5-file-manager-show-filter-controls', 'on');
    }

    if(get_option('b5-file-manager-show-folder-information')==true) {
        update_option('b5-file-manager-show-folder-information', 'on');
    }

    if(get_option('b5-file-manager-show-login-form')==true) {
        update_option('b5-file-manager-show-login-form', 'on');
    }

    if(get_option('b5-file-manager-hover-bg-color')==true) {
        update_option('b5-file-manager-hover-bg-color', '#f2f2f2');
    }

    if(get_option('b5-file-manager-date-format')==true) {
        update_option('b5-file-manager-date-format', 'wordpress');
    }

    if(get_option('b5-file-manager-date-decimal')==true) {
        update_option('b5-file-manager-date-decimal', 0);
    }

    if(get_option('b5-file-manager-show-download-icon')==true) {
        update_option('b5-file-manager-show-download-icon', 'on');
    }

    if(get_option('b5-file-manager-show-file-weight')==true) {
        update_option('b5-file-manager-show-file-weight', 'on');
    }

    if(get_option('b5-file-manager-show-file-date')==true) {
        update_option('b5-file-manager-show-file-date', 'on');
    }

    if(get_option('b5-file-manager-show-file-downloads')==true) {
        update_option('b5-file-manager-show-file-downloads', 'on');
    }

    if(get_option('b5-file-manager-download-information')==true) {
        update_option('b5-file-manager-download-information', 'on');
    }

    if(get_option('b5-file-manager-show-spinner')==true) {
        update_option('b5-file-manager-show-spinner', 'on');
    }

    if(get_option('b5-file-manager-show-sort-controls')==true) {
        update_option('b5-file-manager-show-sort-controls', 'on');
    }

    if(get_option('b5-file-manager-show-view-selector')==true) {
        update_option('b5-file-manager-show-view-selector', 'on');
    }

    if(get_option('b5-file-manager-delete-disabled')==true) {
        update_option('b5-file-manager-delete-disabled', 'on');
    }

    if(get_option('b5-file-manager-icon-position')==true) {
        update_option('b5-file-manager-icon-position', 'before');
    }

    if(get_option('b5-file-manager-item-border-color')==true) {
        update_option('b5-file-manager-item-border-color', '#C7C7C7');
    }

    if(get_option('b5-file-manager-button-bg-color')==true) {
        update_option('b5-file-manager-button-bg-color', '#CCC');
    }

    foreach ($b5_file_manager_icon_options['b5_icon_extensions'] as $key => $icon_extension) {
        if(get_option('b5-file-manager-'.$key.'-icon-id') == true) {
            update_option('b5-file-manager-'.$key.'-icon-id', 0);
        }
    }

    if(!empty($_POST)) {
        $b5_file_manager_icon_type = $_POST['b5_file_manager_icon_type'];
        if(!empty($b5_file_manager_icon_type)) {
            update_option('b5-file-manager-icon-type', $b5_file_manager_icon_type);
        }

        $b5_file_manager_image_thumbnails = $_POST['b5_file_manager_image_thumbnails'];
        if(!empty($b5_file_manager_image_thumbnails)) {
            update_option('b5-file-manager-image-thumbnails', $b5_file_manager_image_thumbnails);
        }

        $b5_file_manager_image_lightbox = $_POST['b5_file_manager_image_lightbox'];
        if(!empty($b5_file_manager_image_lightbox)) {
            update_option('b5-file-manager-image-lightbox', $b5_file_manager_image_lightbox);
        }

        $b5_file_manager_folder_icon_id = $_POST['b5_file_manager_folder_icon_id'];
        if(!empty($b5_file_manager_folder_icon_id)) {
            update_option('b5-file-manager-folder-icon-id', $b5_file_manager_folder_icon_id);
        }

        $b5_file_manager_dashicons_color = $_POST['b5_file_manager_dashicons_color'];
        if(!empty($b5_file_manager_dashicons_color)) {
            update_option('b5-file-manager-dashicons-color', $b5_file_manager_dashicons_color);
        }

        $b5_file_manager_menu_border_color = $_POST['b5_file_manager_menu_border_color'];
        if(!empty($b5_file_manager_menu_border_color)) {
            update_option('b5-file-manager-menu-border-color', $b5_file_manager_menu_border_color);
        }

        $b5_file_manager_icon_size = $_POST['b5_file_manager_icon_size'];
        if(!empty($b5_file_manager_icon_size)) {
            update_option('b5-file-manager-icon-size', $b5_file_manager_icon_size);
        }

        $b5_file_manager_default_view = $_POST['b5_file_manager_default_view'];
        if(!empty($b5_file_manager_default_view)) {
            update_option('b5-file-manager-default-view', $b5_file_manager_default_view);
        }

        $b5_file_manager_default_sort = $_POST['b5_file_manager_default_sort'];
        if(!empty($b5_file_manager_default_sort)) {
            update_option('b5-file-manager-default-sort', $b5_file_manager_default_sort);
        }

        $b5_file_manager_default_sort_by = $_POST['b5_file_manager_default_sort_by'];
        if(!empty($b5_file_manager_default_sort_by)) {
            update_option('b5-file-manager-default-sort-by', $b5_file_manager_default_sort_by);
        }

        $b5_file_manager_folder_empty_message = $_POST['b5_file_manager_folder_empty_message'];
        if(!empty($b5_file_manager_folder_empty_message)) {
            update_option('b5-file-manager-folder-empty-message', $b5_file_manager_folder_empty_message);
        }

        $b5_file_manager_download_message = $_POST['b5_file_manager_download_message'];
        //if(!empty($b5_file_manager_download_message)) {
            update_option('b5-file-manager-download-message', $b5_file_manager_download_message);
        //}

        $b5_file_manager_filter_message = $_POST['b5_file_manager_filter_message'];
        //if(!empty($b5_file_manager_filter_message)) {
        update_option('b5-file-manager-filter-message', $b5_file_manager_filter_message);
        //}

        $b5_file_manager_custom_css = $_POST['b5_file_manager_custom_css'];
        if(!empty($b5_file_manager_custom_css)) {
            update_option('b5-file-manager-custom-css', $b5_file_manager_custom_css);
        }

        $b5_file_manager_show_file_extension = $_POST['b5_file_manager_show_file_extension'];
        if(!empty($b5_file_manager_show_file_extension)) {
            update_option('b5-file-manager-show-file-extension', $b5_file_manager_show_file_extension);
        }

        $b5_file_manager_show_filter_controls = $_POST['b5_file_manager_show_filter_controls'];
        if(!empty($b5_file_manager_show_filter_controls)) {
            update_option('b5-file-manager-show-filter-controls', $b5_file_manager_show_filter_controls);
        }

        $b5_file_manager_show_folder_information = $_POST['b5_file_manager_show_folder_information'];
        if(!empty($b5_file_manager_show_folder_information)) {
            update_option('b5-file-manager-show-folder-information', $b5_file_manager_show_folder_information);
        }

        $b5_file_manager_show_login_form = $_POST['b5_file_manager_show_login_form'];
        if(!empty($b5_file_manager_show_login_form)) {
            update_option('b5-file-manager-show-login-form', $b5_file_manager_show_login_form);
        }

        $b5_file_manager_hover_background_color = $_POST['b5_file_manager_hover_background_color'];
        if(!empty($b5_file_manager_hover_background_color)) {
            update_option('b5-file-manager-hover-bg-color', $b5_file_manager_hover_background_color);
        }

        $b5_file_manager_date_format = $_POST['b5_file_manager_date_format'];
        if(!empty($b5_file_manager_date_format)) {
            update_option('b5-file-manager-date-format', $b5_file_manager_date_format);
        }

        $b5_file_manager_date_decimal = $_POST['b5_file_manager_date_decimal'];
        if(!empty($b5_file_manager_date_decimal)) {
            update_option('b5-file-manager-date-decimal', $b5_file_manager_date_decimal);
        }

        $b5_file_manager_show_download_icon = $_POST['b5_file_manager_show_download_icon'];
        if(!empty($b5_file_manager_show_download_icon)) {
            update_option('b5-file-manager-show-download-icon', $b5_file_manager_show_download_icon);
        }

        $b5_file_manager_show_file_weight = $_POST['b5_file_manager_show_file_weight'];
        if(!empty($b5_file_manager_show_file_weight)) {
            update_option('b5-file-manager-show-file-weight', $b5_file_manager_show_file_weight);
        }

        $b5_file_manager_show_file_date = $_POST['b5_file_manager_show_file_date'];
        if(!empty($b5_file_manager_show_file_date)) {
            update_option('b5-file-manager-show-file-date', $b5_file_manager_show_file_date);
        }

        $b5_file_manager_show_file_downloads = $_POST['b5_file_manager_show_file_downloads'];
        if(!empty($b5_file_manager_show_file_downloads)) {
            update_option('b5-file-manager-show-file-downloads', $b5_file_manager_show_file_downloads);
        }

        $b5_file_manager_download_information = $_POST['b5_file_manager_download_information'];
        if(!empty($b5_file_manager_download_information)) {
            update_option('b5-file-manager-download-information', $b5_file_manager_download_information);
        }

        $b5_file_manager_show_spinner = $_POST['b5_file_manager_show_spinner'];
        if(!empty($b5_file_manager_show_spinner)) {
            update_option('b5-file-manager-show-spinner', $b5_file_manager_show_spinner);
        }

        $b5_file_manager_show_sort_controls = $_POST['b5_file_manager_show_sort_controls'];
        if(!empty($b5_file_manager_show_sort_controls)) {
            update_option('b5-file-manager-show-sort-controls', $b5_file_manager_show_sort_controls);
        }

        $b5_file_manager_show_view_selector = $_POST['b5_file_manager_show_view_selector'];
        if(!empty($b5_file_manager_show_view_selector)) {
            update_option('b5-file-manager-show-view-selector', $b5_file_manager_show_view_selector);
        }

        $b5_file_manager_delete_disabled = $_POST['b5_file_manager_delete_disabled'];
        if(!empty($b5_file_manager_delete_disabled)) {
            update_option('b5-file-manager-delete-disabled', $b5_file_manager_delete_disabled);
        }

        $b5_file_manager_icon_position = $_POST['b5_file_manager_icon_position'];
        if(!empty($b5_file_manager_icon_position)) {
            update_option('b5-file-manager-icon-position', $b5_file_manager_icon_position);
        }

        $b5_file_manager_item_border_color = $_POST['b5_file_manager_item_border_color'];
        if(!empty($b5_file_manager_item_border_color)) {
            update_option('b5-file-manager-item-border-color', $b5_file_manager_item_border_color);
        }

        $b5_file_manager_button_bg_color = $_POST['b5_file_manager_button_bg_color'];
        if(!empty($b5_file_manager_button_bg_color)) {
            update_option('b5-file-manager-button-bg-color', $b5_file_manager_button_bg_color);
        }

        foreach ($b5_file_manager_icon_options['b5_icon_extensions'] as $key => $icon_extension) {
            if(!empty($_POST['b5_file_manager_'.$key.'_icon_id'])) {
                update_option('b5-file-manager-'.$key.'-icon-id', $_POST['b5_file_manager_'.$key.'_icon_id']);
            }
        }

        if(isset($_POST['b5_file_manager_extra_fields'])) {
            $b5_file_manager_extra_fields = $_POST['b5_file_manager_extra_fields'];
            if(!empty($b5_file_manager_extra_fields)) {
                update_option('b5-file-manager-extra-fields', $b5_file_manager_extra_fields);
            } else {
                update_option('b5-file-manager-extra-fields', array());
            }
        } else {
            update_option('b5-file-manager-extra-fields', array());
        }
    }

    wp_send_json_success();
}

add_action('wp_ajax_b5_file_manager_reset_settings', 'b5_file_manager_reset_general_settings'); //creating Ajax call for WordPress
function b5_file_manager_reset_general_settings() {
    //setting default options
    $b5_file_manager_icon_options = b5_file_manager_icon_options();

    // options
    update_option('b5-file-manager-image-thumbnails', 'on');
    update_option('b5-file-manager-image-lightbox', 'on');
    update_option('b5-file-manager-folder-icon-id', 0);
    update_option('b5-file-manager-dashicons-color', '#d8d8d8');
    update_option('b5-file-manager-menu-border-color', '#e5e5e5');
    update_option('b5-file-manager-icon-size', 60);
    update_option('b5-file-manager-icon-type', 'dashicons');
    update_option('b5-file-manager-default-view', 'grid');
    update_option('b5-file-manager-default-sort', 'asc');
    update_option('b5-file-manager-default-sort-by', 'original');
    update_option('b5-file-manager-folder-empty-message', 'Empty folder');
    update_option('b5-file-manager-download-message', 'Download');
    update_option('b5-file-manager-filter-message', 'Filter');
    update_option('b5-file-manager-custom-css', '');
    update_option('b5-file-manager-show-file-extension', 'on');
    update_option('b5-file-manager-show-filter-controls', 'on');
    update_option('b5-file-manager-show-folder-information', 'on');
    update_option('b5-file-manager-show-login-form', 'on');
    update_option('b5-file-manager-hover-bg-color', '#f2f2f2');
    update_option('b5-file-manager-date-format', 'wordpress');
    update_option('b5-file-manager-date-decimal', 0);
    update_option('b5-file-manager-show-download-icon', 'on');
    update_option('b5-file-manager-show-file-date', 'on');
    update_option('b5-file-manager-show-file-weight', 'on');
    update_option('b5-file-manager-show-file-downloads', 'on');
    update_option('b5-file-manager-download-information', 'on');
    update_option('b5-file-manager-show-spinner', 'on');
    update_option('b5-file-manager-show-sort-controls', 'on');
    update_option('b5-file-manager-show-view-selector', 'on');
    update_option('b5-file-manager-delete-disabled', 'on');
    update_option('b5-file-manager-icon-position', 'before');
    update_option('b5-file-manager-item-border-color', '#C7C7C7');
    update_option('b5-file-manager-button-bg-color', '#CCC');

    foreach($b5_file_manager_icon_options['b5_icon_extensions'] as $key => $icon_extension) {
        update_option('b5-file-manager-'.$key.'-icon-id', 0);
    }

    update_option('b5-file-manager-extra-fields', array());

    delete_option('b5-file-manager-delete-mimes');
    delete_option('b5-file-manager-mime-types');

    wp_send_json_success();
}

add_action('wp_ajax_b5_file_manager_delete_upload_type', 'b5_file_manager_delete_upload_type');
function b5_file_manager_delete_upload_type() {

    check_ajax_referer('b5-delete-upload_type');

    $extension = $_POST['extension_to_delete'];

    $b5_file_manager_mime_types = get_option('b5-file-manager-mime-types');

    $all_delete_mimes = get_option('b5-file-manager-delete-mimes');
    $all_delete_mimes[$extension] = $b5_file_manager_mime_types[$extension];
    update_option('b5-file-manager-delete-mimes', $all_delete_mimes);

    unset($b5_file_manager_mime_types[$extension]);

    update_option('b5-file-manager-mime-types', $b5_file_manager_mime_types);

    wp_send_json_success(__('Removed', B5FILEMANAGER_PLUGIN_NAME)." ".$extension);
}

add_action('wp_ajax_b5_file_manager_add_upload_type', 'b5_file_manager_add_upload_type');
function b5_file_manager_add_upload_type() {

    check_ajax_referer('b5-add-upload_type');

    $extension = $_POST['extension_to_add'];
    $mimetype  = $_POST['mimetype_to_add'];

    $b5_file_manager_mime_types = get_option('b5-file-manager-mime-types');

    $b5_file_manager_mime_types[$extension] = $mimetype;

    update_option('b5-file-manager-mime-types', $b5_file_manager_mime_types);

    wp_send_json_success(__('Added', B5FILEMANAGER_PLUGIN_NAME)." ".$extension." => ".$mimetype);
}

add_action('wp_ajax_b5_get_all_delete_mimes', 'b5_file_manager_all_delete_mimes');
function b5_file_manager_all_delete_mimes() {

    $html = '<div id="b5-all-delete-mime-list">';

    $all_delete_mimes = (array) get_option('b5-file-manager-delete-mimes', array());

    if(count($all_delete_mimes) > 0) {
        foreach($all_delete_mimes as $extension => $mimetype) {
            $html .= b5_file_manager_mime_html($extension, $mimetype);
        }
    } else {
        $html .= '<p>'.__('No delete mimes to show', B5FILEMANAGER_PLUGIN_NAME).'</p>';
    }

    echo $html."</div>";

    die();
}

function b5_file_manager_mime_html($extension, $mimetype) {
    $classes = array('toggle', 'describe-toggle-on');
    $classes = implode(' ', $classes);

    $restore_mime_nonce = wp_create_nonce("b5-restore_mime");

    $html = '<div class="%s">
                <a class="%s" href="#" title="%s" data-extension="%s" data-mime_type="%s" data-retore_mime_nonce="%s" onclick="javascript:b5_file_mime_restore(this);return false;">%s</a>
                <div class="extension"><span class="title">%s</span></div>
            </div>';

    return sprintf(
        $html,
        'mime-item',
        $classes,
        $mimetype,
        $extension,
        $mimetype,
        $restore_mime_nonce,
        __('Restore', B5FILEMANAGER_PLUGIN_NAME),
        $extension
    );
}

add_action('print_media_templates', 'b5_file_manager_print_mime_template');
function b5_file_manager_print_mime_template() {
    $i18n_delete = __('Delete', B5FILEMANAGER_PLUGIN_NAME);?>
    <script id="tmpl-b5-mime-advanced" type="text/html">
        <# _.each(mimes, function(mime) { #>
            <div class="b5_mime_item">
                <a class="toggle describe-toggle-on b5-upload-type-delete" data-upload_extension="{{{ mime.extension }}}" href="#"><?php echo $i18n_delete;?></a>
                <div>
                    <div class="b5_mime extension"><span class="title">{{{ mime.extension }}}</span></div>
                    <div class="b5_mime mime"><span class="title">{{{ mime.type }}}</span></div>
                </div>
            </div>
        <# } ); #>
    </script>
<?php
}

add_action('print_media_templates', 'b5_file_manager_print_extra_field_template');
function b5_file_manager_print_extra_field_template() {
    $i18n_delete = __('Delete', B5FILEMANAGER_PLUGIN_NAME);
    $delete_nonce = wp_create_nonce("b5-delete_extra_field_nonce");?>
    <script id="tmpl-b5-extra-field-advanced" type="text/html">
        <# _.each(fields, function(field) { #>
            <div class="b5_extra_field b5_new_extra_field">
                <a class="toggle describe-toggle-on b5-extra-field-delete" data-delete_nonce="<?php echo $delete_nonce;?>" data-field_id="{{{ field.id }}}" href="#"><?php echo $i18n_delete;?></a>
                <div class="b5_extra_field_name">
                    <input type="text" name="extra_field_name" data-fied_id="{{{ field.id }}}" />
                </div>
            </div>
            <# } ); #>
    </script>
<?php
}

add_action('wp_ajax_b5_generate_extra_field', 'b5_file_manager_generate_extra_field');
function b5_file_manager_generate_extra_field() {
    check_ajax_referer("b5-generate_extra_field_nonce");
    wp_send_json_success(uniqid('b5-fm-extra-f-'));
}

add_action('wp_ajax_b5_delete_extra_field', 'b5_file_manager_delete_extra_field');
function b5_file_manager_delete_extra_field() {
    check_ajax_referer("b5-delete_extra_field_nonce");
    $extra_field_id = isset($_POST['extra_field']) ? $_POST['extra_field'] : '';

    $fields = array();

    foreach(get_option('b5-file-manager-extra-fields', array()) as $key => $extra_field_name) {
        if($key != $extra_field_id) {
            $fields[$key] = $extra_field_name;
        }
    }

    update_option('b5-file-manager-extra-fields', $fields);

    $args = array(
        'post_type' => array('external_file', 'attachment'),
        'post_status' => 'any',
        'numberposts' => -1
    );

    $allposts = get_posts($args);

    foreach($allposts as $postinfo) {
        delete_post_meta($postinfo->ID, $extra_field_id);
    }

    wp_send_json_success();
}

add_action('wp_ajax_b5_restore_mime', 'b5_file_manager_restore_mime');
function b5_file_manager_restore_mime() {
    $extension = isset($_POST['extension']) ? $_POST['extension'] : '';

    $b5_file_manager_mime_types = get_option('b5-file-manager-mime-types');

    $all_delete_mimes = get_option('b5-file-manager-delete-mimes');

    $b5_file_manager_mime_types[$extension] = $all_delete_mimes[$extension];
    update_option('b5-file-manager-mime-types', $b5_file_manager_mime_types);

    unset($all_delete_mimes[$extension]);
    update_option('b5-file-manager-delete-mimes', $all_delete_mimes);

    wp_send_json_success();
}

/*
 * ajax code for admin menu
 */
add_action('admin_head', 'b5_file_manager_ajax_to_header');
function b5_file_manager_ajax_to_header() { ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            //update settings
            jQuery('#b5_file_manager_update_settings_button').click(function() {
                jQuery('#b5_file_manager_savemessage').show();
                jQuery('#b5_file_manager_savemessage').html('<?php _e('Loading...', B5FILEMANAGER_PLUGIN_NAME);?>');

                //this is the ajax for general settings
                //get field values

                //options
                var b5_file_manager_icon_type = jQuery('#b5_file_manager_icon_type').val();
                var b5_file_manager_image_thumbnails = jQuery('#b5_file_manager_image_thumbnails').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_image_lightbox = jQuery('#b5_file_manager_image_lightbox').attr('checked') == 'checked' ? 'on' : 'off';

                var b5_file_manager_folder_icon_id = jQuery('#b5_file_manager_folder_icon_id').val();

                var b5_file_manager_image_icon_id = jQuery('#b5_file_manager_image_icon_id').val();
                var b5_file_manager_audio_icon_id = jQuery('#b5_file_manager_audio_icon_id').val();
                var b5_file_manager_video_icon_id = jQuery('#b5_file_manager_video_icon_id').val();
                var b5_file_manager_document_icon_id = jQuery('#b5_file_manager_document_icon_id').val();
                var b5_file_manager_spreadsheet_icon_id = jQuery('#b5_file_manager_spreadsheet_icon_id').val();
                var b5_file_manager_interactive_icon_id = jQuery('#b5_file_manager_interactive_icon_id').val();
                var b5_file_manager_text_icon_id = jQuery('#b5_file_manager_text_icon_id').val();
                var b5_file_manager_archive_icon_id = jQuery('#b5_file_manager_archive_icon_id').val();
                var b5_file_manager_code_icon_id = jQuery('#b5_file_manager_code_icon_id').val();
                var b5_file_manager_file_icon_id = jQuery('#b5_file_manager_file_icon_id').val();

                var b5_file_manager_dashicons_color = jQuery('#b5_file_manager_dashicons_color').val();
                var b5_file_manager_menu_border_color = jQuery('#b5_file_manager_menu_border_color').val();
                var b5_file_manager_icon_size = jQuery('#b5_file_manager_icon_size').val();

                var b5_file_manager_default_view = jQuery('#b5_file_manager_default_view').val();
                var b5_file_manager_default_sort = jQuery('#b5_file_manager_default_sort').val();
                var b5_file_manager_default_sort_by = jQuery('#b5_file_manager_default_sort_by').val();

                var b5_file_manager_folder_empty_message = jQuery('#b5_file_manager_folder_empty_message').val();
                var b5_file_manager_download_message = jQuery('#b5_file_manager_download_message').val();
                var b5_file_manager_filter_message = jQuery('#b5_file_manager_filter_message').val();

                var b5_file_manager_custom_css = jQuery('#b5_file_manager_custom_css').val();
                var b5_file_manager_show_file_extension = jQuery('#b5_file_manager_show_file_extension').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_filter_controls = jQuery('#b5_file_manager_show_filter_controls').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_folder_information = jQuery('#b5_file_manager_show_folder_information').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_login_form = jQuery('#b5_file_manager_show_login_form').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_hover_background_color = jQuery('#b5_file_manager_hover_background_color').val();
                var b5_file_manager_date_format = jQuery('#b5_file_manager_date_format').val();
                var b5_file_manager_date_decimal = jQuery('#b5_file_manager_date_decimal').val();
                var b5_file_manager_show_download_icon = jQuery('#b5_file_manager_show_download_icon').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_file_date = jQuery('#b5_file_manager_show_file_date').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_file_downloads = jQuery('#b5_file_manager_show_file_downloads').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_download_information = jQuery('#b5_file_manager_download_information').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_file_weight = jQuery('#b5_file_manager_show_file_weight').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_spinner = jQuery('#b5_file_manager_show_spinner').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_sort_controls = jQuery('#b5_file_manager_show_sort_controls').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_show_view_selector = jQuery('#b5_file_manager_show_view_selector').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_delete_disabled = jQuery('#b5_file_manager_delete_disabled').attr('checked') == 'checked' ? 'on' : 'off';
                var b5_file_manager_icon_position = jQuery('#b5_file_manager_icon_position').val();
                var b5_file_manager_item_border_color = jQuery('#b5_file_manager_item_border_color').val();
                var b5_file_manager_button_bg_color = jQuery('#b5_file_manager_button_bg_color').val();

                var b5_file_manager_extra_fields = {};

                $('input[type="text"][name="extra_field_name"]').each(function() {
                    if($(this).val() != '') {
                        b5_file_manager_extra_fields[$(this).attr('data-fied_id')] = $(this).val()
                    }
                });

                var data = {
                    action: 'b5_file_manager_update_settings',

                    // options
                    b5_file_manager_icon_type: b5_file_manager_icon_type,
                    b5_file_manager_image_thumbnails: b5_file_manager_image_thumbnails,
                    b5_file_manager_image_lightbox: b5_file_manager_image_lightbox,
                    b5_file_manager_folder_icon_id: b5_file_manager_folder_icon_id,

                    b5_file_manager_image_icon_id: b5_file_manager_image_icon_id,
                    b5_file_manager_audio_icon_id: b5_file_manager_audio_icon_id,
                    b5_file_manager_video_icon_id: b5_file_manager_video_icon_id,
                    b5_file_manager_document_icon_id: b5_file_manager_document_icon_id,
                    b5_file_manager_spreadsheet_icon_id: b5_file_manager_spreadsheet_icon_id,
                    b5_file_manager_interactive_icon_id: b5_file_manager_interactive_icon_id,
                    b5_file_manager_text_icon_id: b5_file_manager_text_icon_id,
                    b5_file_manager_archive_icon_id: b5_file_manager_archive_icon_id,
                    b5_file_manager_code_icon_id: b5_file_manager_code_icon_id,
                    b5_file_manager_file_icon_id: b5_file_manager_file_icon_id,

                    b5_file_manager_dashicons_color: b5_file_manager_dashicons_color,
                    b5_file_manager_menu_border_color: b5_file_manager_menu_border_color,
                    b5_file_manager_icon_size: b5_file_manager_icon_size,

                    b5_file_manager_default_view: b5_file_manager_default_view,
                    b5_file_manager_default_sort: b5_file_manager_default_sort,
                    b5_file_manager_default_sort_by: b5_file_manager_default_sort_by,

                    b5_file_manager_folder_empty_message: b5_file_manager_folder_empty_message,
                    b5_file_manager_download_message: b5_file_manager_download_message,
                    b5_file_manager_filter_message: b5_file_manager_filter_message,

                    b5_file_manager_custom_css: b5_file_manager_custom_css,
                    b5_file_manager_show_file_extension: b5_file_manager_show_file_extension,
                    b5_file_manager_show_filter_controls: b5_file_manager_show_filter_controls,
                    b5_file_manager_show_folder_information: b5_file_manager_show_folder_information,
                    b5_file_manager_show_login_form: b5_file_manager_show_login_form,
                    b5_file_manager_hover_background_color: b5_file_manager_hover_background_color,
                    b5_file_manager_date_format: b5_file_manager_date_format,
                    b5_file_manager_date_decimal: b5_file_manager_date_decimal,
                    b5_file_manager_show_download_icon: b5_file_manager_show_download_icon,
                    b5_file_manager_show_file_date: b5_file_manager_show_file_date,
                    b5_file_manager_show_file_downloads: b5_file_manager_show_file_downloads,
                    b5_file_manager_download_information: b5_file_manager_download_information,
                    b5_file_manager_show_file_weight: b5_file_manager_show_file_weight,
                    b5_file_manager_show_spinner: b5_file_manager_show_spinner,
                    b5_file_manager_show_sort_controls: b5_file_manager_show_sort_controls,
                    b5_file_manager_show_view_selector: b5_file_manager_show_view_selector,
                    b5_file_manager_delete_disabled: b5_file_manager_delete_disabled,
                    b5_file_manager_icon_position: b5_file_manager_icon_position,
                    b5_file_manager_item_border_color: b5_file_manager_item_border_color,
                    b5_file_manager_button_bg_color: b5_file_manager_button_bg_color,

                    b5_file_manager_extra_fields: b5_file_manager_extra_fields
                };

                jQuery.post(ajaxurl, data, function(response) {
                    jQuery('#b5_file_manager_savemessage').html('<?php _e('Settings Saved', B5FILEMANAGER_PLUGIN_NAME);?>').fadeOut(1000);
                });
            });

            jQuery('#b5_file_manager_upload_types').on('click', '.b5-upload-type-delete', function() {
                var $this = $(this),
                    extension = $this.data('upload_extension'),
                    data = {
                        action: 'b5_file_manager_delete_upload_type',
                        extension_to_delete:extension,
                        _ajax_nonce: $('#b5_delete_upload_type_nonce').val()
                    };

                var delete_confirm = confirm("<?php _e('Are you sure you want to disallow uploading files with an extension matched by', B5FILEMANAGER_PLUGIN_NAME);?> '" + extension + "'?");

                if (!delete_confirm) return false;

                $.post(ajaxurl, data, function(r) {
                    if (!r.success) {
                        alert(r.data);
                        return;
                    } else {
                        $this.parent().fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }
                }, 'json');

                return false;
            })

            jQuery('#b5_mime_extension, #b5_mime_mime').bind('change paste keyup', function() {
                $(this).removeClass('error');
            })

            jQuery('#b5_button_add_mime').on('click', function() {
                var $this = $(this),
                    extension = $('#b5_mime_extension'),
                    mime = $('#b5_mime_mime'),
                    template = $('#tmpl-b5-mime-advanced').html(),
                    data = {
                        action: 'b5_file_manager_add_upload_type',
                        extension_to_add:extension.val(),
                        mimetype_to_add:mime.val(),
                        _ajax_nonce: $this.data('add_upload_type_nonce')
                    };

                if(extension.val() === "" || mime.val() === "") {
                    if(extension.val() === "" && !$(extension).hasClass('error')) {
                        (extension).addClass('error')
                    }

                    if(mime.val() === "" && !$(mime).hasClass('error')) {
                        $(mime).addClass('error')
                    }

                    return;
                }

                var mime_obj = new Object();
                mime_obj.extension = extension.val();
                mime_obj.type = mime.val();

                var selection = new Array();
                selection.push(mime_obj);

                $.post(ajaxurl, data, function(r) {
                    if (!r.success) {
                        alert(r.toSource());
                        return;
                    } else {
                        extension.val('');
                        mime.val('');
                        $('#b5_mime_form')
                            .before(_.template(template, { mimes: selection }, {
                                evaluate:    /<#([\s\S]+?)#>/g,
                                interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                                escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                            }))
                    }
                }, 'json');

                return false;
            })

            jQuery('#b5_button_show_all_delete_mimes').click(function() {
                tb_show('<?php _e('Delete mimes', B5FILEMANAGER_PLUGIN_NAME);?>', 'admin-ajax.php?action=b5_get_all_delete_mimes');
                return false;
            })

            jQuery('#b5_file_manager_add_extra_field').on('click', function(e) {
                e.preventDefault();
                var $this = $(this),
                    template = $('#tmpl-b5-extra-field-advanced').html(),
                    data = {
                        action: 'b5_generate_extra_field',
                        _ajax_nonce: $this.data('generate_field_nonce')
                    };

                $.post(ajaxurl, data, function(r) {
                    if (!r.success) {
                        alert(r.data);
                    } else {
                        var extra_field = new Object(),
                            fields = new Array();

                        extra_field.id = r.data;
                        fields.push(extra_field);

                        $('#b5_file_manager_extra_fields')
                            .append(_.template(template, { fields: fields }, {
                                evaluate:    /<#([\s\S]+?)#>/g,
                                interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                                escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                            }));
                    }
                }, 'json');

                return false;
            });

            jQuery('#b5_file_manager_extra_fields').on('click', '.b5-extra-field-delete', function(e) {
                e.preventDefault();
                var $this = $(this),
                    extra_field_container = $this.parent();

                if(!extra_field_container.hasClass('b5_new_extra_field')) {
                    data = {
                        action: 'b5_delete_extra_field',
                        extra_field: $this.data('field_id'),
                        _ajax_nonce: $this.data('delete_nonce')
                    };

                    $.post(ajaxurl, data, function(r) {
                        if (!r.success) {
                            alert(r.data);
                        }
                    }, 'json');
                }

                extra_field_container.fadeOut('fast', function() {
                    $(this).remove();
                });
            });
        });

        function b5_file_mime_restore(obj) {
            "use strict";
            jQuery(function($) {
                var template = $('#tmpl-b5-mime-advanced').html();

                var $restoreButton = $(obj),
                    extension = $restoreButton.attr('data-extension'),
                    mime_type = $restoreButton.attr('data-mime_type'),
                    mime_container = $restoreButton.parent();

                var data = {
                    action: 'b5_restore_mime',
                    extension: extension,
                    _ajax_nonce: $restoreButton.attr('data-retore_mime_nonce')
                };

                var mime_obj = new Object();
                mime_obj.extension = extension;
                mime_obj.type = mime_type;

                var selection = new Array();
                selection.push(mime_obj);

                $.post(ajaxurl, data, function(r) {
                    if (r.success) {
                        $('#b5_mime_form')
                            .before(_.template(template, { mimes: selection }, {
                                evaluate:    /<#([\s\S]+?)#>/g,
                                interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                                escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                            }));

                        mime_container.fadeOut('fast', function() {
                            if($(this).siblings().size()==0) {
                                var p = $("<p />", {
                                    "text" : '<?php _e('No delete mimes to show', B5FILEMANAGER_PLUGIN_NAME);?>'
                                });
                                $(this).parent().append(p);
                            }
                            $(this).remove();
                        });
                    }
                }, 'json');
            })
            return false;
        }
    </script>
<?php
}