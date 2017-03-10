<?php

    require_once B5FILEMANAGER_PLUGIN_DIR . '/includes/functions.php';
    require_once B5FILEMANAGER_PLUGIN_DIR . '/includes/b5-file-manager-upload-types.php';
    include_once B5FILEMANAGER_PLUGIN_DIR . '/includes/b5-file-folder-extra-widget.php';

    if(is_admin()) {
        require_once B5FILEMANAGER_PLUGIN_DIR . '/admin/admin.php';
    } else {
        require_once B5FILEMANAGER_PLUGIN_DIR . '/includes/shortcodes-constructor.php';
        require_once B5FILEMANAGER_PLUGIN_DIR . '/includes/b5-file-manager-styles.php';
    }