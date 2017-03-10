<?php
    add_action('activate_' . B5FILEMANAGER_PLUGIN_BASENAME, 'b5_file_manager_activate');
    function b5_file_manager_activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'b5_file_manager_downloads';

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  file_id mediumint(9) NOT NULL,
			  folder_id mediumint(9) NOT NULL,
			  visitor_ip mediumtext NOT NULL,
			  date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
			  visitor_id mediumint(9) NOT NULL,
			  visitor_name mediumtext NOT NULL,
			  UNIQUE KEY id (id)
		);';

        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    add_action('init', 'b5_file_manager_check_database');
    function b5_file_manager_check_database() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'b5_file_manager_downloads';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            b5_file_manager_activate();
        }
    }

    function b5_file_manager_empty_download_logs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'b5_file_manager_downloads';

        $sql = 'DROP TABLE IF EXISTS ' . $table_name.';';

        $wpdb->query($sql);
    }

    add_action('deactivate_' . B5FILEMANAGER_PLUGIN_BASENAME, 'b5_file_manager_deactivate');
    function b5_file_manager_deactivate() {
        if(get_option('b5-file-manager-delete-disabled', 'on') == 'on') {
            b5_file_manager_remove_options();
        }
    }

    function b5_file_manager_remove_options() {
        //setting default options
        $b5_file_manager_icon_options = b5_file_manager_icon_options();

        // options
        delete_option('b5-file-manager-delete-disabled');
        delete_option('b5-file-manager-image-thumbnails');
        delete_option('b5-file-manager-image-lightbox');
        delete_option('b5-file-manager-folder-icon-id');
        delete_option('b5-file-manager-dashicons-color');
        delete_option('b5-file-manager-menu-border-color');
        delete_option('b5-file-manager-icon-size');
        delete_option('b5-file-manager-icon-type');
        delete_option('b5-file-manager-default-view');
        delete_option('b5-file-manager-default-sort');
        delete_option('b5-file-manager-default-sort-by');
        delete_option('b5-file-manager-folder-empty-message');
        delete_option('b5-file-manager-download-message');
        delete_option('b5-file-manager-filter-message');
        delete_option('b5-file-manager-custom-css');
        delete_option('b5-file-manager-show-file-extension');
        delete_option('b5-file-manager-show-filter-controls');
        delete_option('b5-file-manager-show-folder-information');
        delete_option('b5-file-manager-show-login-form');
        delete_option('b5-file-manager-hover-bg-color');
        delete_option('b5-file-manager-date-format');
        delete_option('b5-file-manager-date-decimal');
        delete_option('b5-file-manager-show-download-icon');
        delete_option('b5-file-manager-show-file-weight');
        delete_option('b5-file-manager-show-file-downloads');
        delete_option('b5-file-manager-download-information');
        delete_option('b5-file-manager-show-file-date');
        delete_option('b5-file-manager-show-spinner');
        delete_option('b5-file-manager-show-sort-controls');
        delete_option('b5-file-manager-show-view-selector');
        delete_option('b5-file-manager-icon-position');
        delete_option('b5-file-manager-item-border-color');
        delete_option('b5-file-manager-button-bg-color');

        foreach($b5_file_manager_icon_options['b5_icon_extensions'] as $key => $icon_extension) {
            delete_option('b5-file-manager-'.$key.'-icon-id');
        }

        delete_option('b5-file-manager-extra-fields');

        delete_option('b5-file-manager-delete-mimes');
        delete_option('b5-file-manager-mime-types');

        b5_file_manager_empty_download_logs();
    }