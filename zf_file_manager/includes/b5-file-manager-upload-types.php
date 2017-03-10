<?php

    add_action('activate_' . B5FILEMANAGER_PLUGIN_BASENAME, 'b5_file_manager_install');
    function b5_file_manager_install() {
        if ($opt = get_option('b5-file-manager-mime-types')) {
            return;
        }

        $mime_types = get_allowed_mime_types();
        add_option('b5-file-manager-mime-types', $mime_types);
    }

    function b5_file_manager_load_mime_types($mime_types=array()) {
        $b5_file_manager_mime_types = get_option('b5-file-manager-mime-types');
        if ($b5_file_manager_mime_types === false) {
            add_option('b5-file-manager-mime-types', $mime_types);
        }
        return $b5_file_manager_mime_types;
    }
    add_filter('upload_mimes', 'b5_file_manager_load_mime_types');