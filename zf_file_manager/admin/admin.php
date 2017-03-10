<?php
    include_once B5FILEMANAGER_PLUGIN_ADMIN_DIR . '/includes/boot-strap.php';
    include_once B5FILEMANAGER_PLUGIN_ADMIN_DIR . '/includes/b5_options/b5_options.php';
    include_once B5FILEMANAGER_PLUGIN_ADMIN_DIR . '/admin-functions.php';
    include_once B5FILEMANAGER_PLUGIN_ADMIN_DIR . '/includes/meta-boxes.php';

    include_once B5FILEMANAGER_PLUGIN_ADMIN_DIR . '/includes/options.php';

    /******************************************************
    /* theme support
    /******************************************************/
    add_theme_support('post-thumbnails', array('external_file'));

    /******************************************************
    /* custom messages
    /******************************************************/
    add_filter('post_updated_messages', 'b5_file_manager_updated_messages');

    function b5_file_manager_updated_messages($messages) {
        global $post, $post_ID;

        $messages['folder'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __('Folder updated.'),
            2 => __('Custom folder updated.'),
            3 => __('Custom folder deleted.'),
            4 => __('Folder updated.'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('Folder restored to revision from %s'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => sprintf(__('Folder published. <a href="%s">View folder</a>'), esc_url(get_permalink($post_ID))),
            7 => __('Folder saved.'),
            8 => sprintf(__('Folder submitted. <a target="_blank" href="%s">Preview folder</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            9 => sprintf(__('Folder scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview folder</a>'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
            10 => sprintf(__('Folder draft updated. <a target="_blank" href="%s">Preview folder</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
        );

        return $messages;
    }

    /******************************************************
    /* media init
    /******************************************************/
    add_action('init', 'b5_file_manager_init');
    function b5_file_manager_init() {
        $folder_labels = array(
            'name' => __('Folder', 'post type general name', B5FILEMANAGER_PLUGIN_NAME),
            'singular_name' => __('Folder', B5FILEMANAGER_PLUGIN_NAME),
            'search_items' => __('Search Folder', B5FILEMANAGER_PLUGIN_NAME),
            'all_items' => __('All Folders', B5FILEMANAGER_PLUGIN_NAME),
            'parent_item' => __('Parent Folder', B5FILEMANAGER_PLUGIN_NAME),
            'edit_item' => __('Edit Folder', B5FILEMANAGER_PLUGIN_NAME),
            'update_item' => __('Update Folder', B5FILEMANAGER_PLUGIN_NAME),
            'not_found_in_trash' => __('No folders found in Trash', B5FILEMANAGER_PLUGIN_NAME),
            'view_item' => __('View Folder', B5FILEMANAGER_PLUGIN_NAME),
            'search_items' => __('Search Folders', B5FILEMANAGER_PLUGIN_NAME),
            'add_new' => _x('Add New Folder', 'folder', B5FILEMANAGER_PLUGIN_NAME),
            'add_new_item' => __('Add New Folder', B5FILEMANAGER_PLUGIN_NAME),
            'menu_name' => __('File Manager', B5FILEMANAGER_PLUGIN_NAME)
        );

        // some arguments and in the last line 'supports', we say to WordPress what features are supported on the Portfolio post type
        $folder_args = array(
            'labels' => $folder_labels,
            'public' => true,
            'singular_label' => __('B5 File Manager', B5FILEMANAGER_PLUGIN_NAME),
            'show_ui' => true,
            'show_in_menu' => true,
            'hierarchical' => true,
            'show_in_nav_menus' => false,
            'capability_type' => 'page',
            'rewrite' => false,
            'has_archive' => true,
            'menu_icon' => 'dashicons-category',
            'supports' => array('title', 'page-attributes')
        );

        // register the custom post type
        register_post_type('folder', $folder_args);

        $extenal_labels = array(
            'name' => __('External File', 'post type general name', B5FILEMANAGER_PLUGIN_NAME),
            'singular_name' => __('File', B5FILEMANAGER_PLUGIN_NAME),
            'search_items' => __('Search File', B5FILEMANAGER_PLUGIN_NAME),
            'all_items' => __('All Files', B5FILEMANAGER_PLUGIN_NAME),
            'parent_item' => __('Parent Folder', B5FILEMANAGER_PLUGIN_NAME),
            'edit_item' => __('Edit File', B5FILEMANAGER_PLUGIN_NAME),
            'update_item' => __('Update File', B5FILEMANAGER_PLUGIN_NAME),
            'not_found_in_trash' => __('No files found in Trash', B5FILEMANAGER_PLUGIN_NAME),
            'view_item' => __('View File', B5FILEMANAGER_PLUGIN_NAME),
            'search_items' => __('Search Files', B5FILEMANAGER_PLUGIN_NAME),
            'add_new' => _x('Add New', 'file', B5FILEMANAGER_PLUGIN_NAME),
            'add_new_item' => __('Add New External File', B5FILEMANAGER_PLUGIN_NAME),
            'menu_name' => __('B5 File Manager', B5FILEMANAGER_PLUGIN_NAME)
        );

        $external_args = array(
            'labels' => $extenal_labels,
            'public' => true,
            'singular_label' => __('B5 External File Manager', B5FILEMANAGER_PLUGIN_NAME),
            'show_ui' => true,
            'show_in_menu' => false,
            'hierarchical' => false,
            'show_in_nav_menus' => false,
            'capability_type' => 'page',
            'rewrite' => false,
            'has_archive' => true,
            'menu_icon' => 'dashicons-category',
            'supports' => array('title', 'thumbnail')
        );

        // register the custom post type
        register_post_type('external_file', $external_args);

        $users_group_labels = array(
            'name' => __('Users group', 'post type general name', B5FILEMANAGER_PLUGIN_NAME),
            'singular_name' => __('Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'search_items' => __('Search Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'all_items' => __('All Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'parent_item' => __('Parent Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'edit_item' => __('Edit Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'update_item' => __('Update Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'not_found_in_trash' => __('No users group found in Trash', B5FILEMANAGER_PLUGIN_NAME),
            'view_item' => __('View Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'search_items' => __('Search Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'add_new' => _x('Add New', 'users group', B5FILEMANAGER_PLUGIN_NAME),
            'add_new_item' => __('Add New Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'menu_name' => __('B5 Users Group', B5FILEMANAGER_PLUGIN_NAME)
        );

        $users_group_args = array(
            'labels' => $users_group_labels,
            'public' => true,
            'singular_label' => __('B5 Users Group', B5FILEMANAGER_PLUGIN_NAME),
            'show_ui' => true,
            'show_in_menu' => false,
            'hierarchical' => false,
            'show_in_nav_menus' => false,
            'capability_type' => 'page',
            'rewrite' => false,
            'has_archive' => true,
            'menu_icon' => 'dashicons-category',
            'supports' => array('title')
        );

        // register the custom post type
        register_post_type('users_group_file', $users_group_args);
    }

    /*add_action('contextual_help', 'add_help_text', 10, 3);
    function add_help_text($contextual_help, $screen_id, $screen) {
        if ('folder' == $screen->id) {
            $contextual_help =
                '<p>' . __('Things to remember when adding or editing a book:') . '</p>';
        } elseif ('edit-folder' == $screen->id) {
            $contextual_help =
                '<p>' . __('This is the help screen displaying the table of folders.') . '</p>' ;
        }
        return $contextual_help;
    }*/