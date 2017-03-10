<?php

    if (!defined('WP_UNINSTALL_PLUGIN')) exit();

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

    $b5_file_manager_icon_options = array(
        'image'=>__('Image', B5FILEMANAGER_PLUGIN_NAME),
        'audio'=>__('Audio', B5FILEMANAGER_PLUGIN_NAME),
        'video'=>__('Video', B5FILEMANAGER_PLUGIN_NAME),
        'document'=>__('Document', B5FILEMANAGER_PLUGIN_NAME),
        'spreadsheet'=>__('Spreadsheet', B5FILEMANAGER_PLUGIN_NAME),
        'interactive'=>__('Interactive', B5FILEMANAGER_PLUGIN_NAME),
        'text'=>__('Text', B5FILEMANAGER_PLUGIN_NAME),
        'archive'=>__('Archive', B5FILEMANAGER_PLUGIN_NAME),
        'code'=>__('Code', B5FILEMANAGER_PLUGIN_NAME),
        'file'=>__('Default', B5FILEMANAGER_PLUGIN_NAME)
    );

    foreach($b5_file_manager_icon_options as $key=>$icon_extension) {
        delete_option('b5-file-manager-'.$key.'-icon-id');
    }

    delete_option('b5-file-manager-extra-fields');

    delete_option('b5-file-manager-delete-mimes');
    delete_option('b5-file-manager-mime-types');