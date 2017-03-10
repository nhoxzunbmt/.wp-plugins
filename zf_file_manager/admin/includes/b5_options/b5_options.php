<?php

    function b5_file_manager_icon_options() {
        // Set the Options Array
        $b5_icon_options = array();
        $b5_icon_options['b5_icon_extensions'] = array(
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

        return $b5_icon_options;
    }

    function b5_file_manager_date_options() {
        $b5_date_format_options = array();
        $b5_date_format_options['b5_date_format'] = array(
            'wordpress'=>__('Wordpress date format', B5FILEMANAGER_PLUGIN_NAME),
            'F j, Y'=>__('F j, Y', B5FILEMANAGER_PLUGIN_NAME),
            'Y/m/d'=>__('Y/m/d', B5FILEMANAGER_PLUGIN_NAME),
            'm/d/Y'=>__('m/d/Y', B5FILEMANAGER_PLUGIN_NAME),
            'd/m/Y'=>__('d/m/Y', B5FILEMANAGER_PLUGIN_NAME),
            'M j, Y'=>__('M j, Y', B5FILEMANAGER_PLUGIN_NAME)
        );

        return $b5_date_format_options;
    }

    function b5_file_manager_default_sort_options() {
        $b5_default_sort_options = array();
        $b5_default_sort_options['b5_default_sort_by'] = array(
            'original'=>__('Original order', B5FILEMANAGER_PLUGIN_NAME),
            'date'=>__('Date', B5FILEMANAGER_PLUGIN_NAME),
            'title'=>__('Title', B5FILEMANAGER_PLUGIN_NAME),
            'weight'=>__('Weight', B5FILEMANAGER_PLUGIN_NAME),
            'type'=>__('Type', B5FILEMANAGER_PLUGIN_NAME)
        );

        return $b5_default_sort_options;
    }