<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class downloads_Username_List_Table extends WP_List_Table {

    var $username;

    function no_items() {
        _e('No download logs found for this username.', B5FILEMANAGER_PLUGIN_NAME);
    }

    function __construct($username) {
        $this->username = $username;
        parent::__construct(array(
            'singular'=>__('Download', B5FILEMANAGER_PLUGIN_NAME),
            'plural'=>__('Downloads', B5FILEMANAGER_PLUGIN_NAME),
            'ajax'=>false
        ));
    }

    function get_views() {
        $total_logs = $this->get_total_logs_number();

        $logs_links = array();
        if(count($total_logs) > 0) {
            $logs_links['all_logs'] = "<a href='?post_type=folder&page=".$_REQUEST['page']."'>" . sprintf(_nx('All downloads <span class="count">(%s)</span>', 'All downloads <span class="count">(%s)</span>', $total_logs, 'logs'), number_format_i18n($total_logs)) . '</a>';
        }

        return $logs_links;
    }

    function get_columns() {
        $columns = array(
            'cb'=>'<input type="checkbox" />', //Render a checkbox instead of text
            'file_name'=>__('File', B5FILEMANAGER_PLUGIN_NAME),
            'folder_name'=>__('Folder', B5FILEMANAGER_PLUGIN_NAME),
            'visitor_ip'=>__('Visitor IP', B5FILEMANAGER_PLUGIN_NAME),
            'date'=>__('Date', B5FILEMANAGER_PLUGIN_NAME)
        );
        return $columns;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'visitor_ip':
            case 'date':
            case 'folder_name':
            case 'file_name':
                return $item[$column_name];
            break;
            default:
                return print_r($item, true);
            break;
        }
    }

    function column_file_name($item) {
        $actions = array(
            'delete' => sprintf('<a href="?post_type=folder&page=%s&username=%s&action=%s&download=%s">' . __('Delete', B5FILEMANAGER_PLUGIN_NAME) . '</a>', $_REQUEST['page'], $this->username, 'delete', $item['ID'])
        );

        return sprintf('<strong><a href="?post_type=folder&page=%1$s&file_id=%2$s">%3$s</a></strong> %4$s',
            $_REQUEST['page'],
            $item['file_id'],
            $item['file_name'],
            $this->row_actions($actions)
        );
    }

    function column_folder_name($item) {
        return sprintf('<a href="?post_type=folder&page=%1$s&folder_id=%2$s">%3$s</a>',
            $_REQUEST['page'],
            $item['folder_id'],
            $item['folder_name']
        );
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['ID']
        );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'file_name'=>array('file_name', false),
            'folder_name'=>array('folder_name', false),
            'date'=>array('date', false)
        );
        return $sortable_columns;
    }

    function extra_tablenav( $which ) { ?>
        <div class="alignleft actions">
        <?php submit_button(__('Delete all logs'), 'apply', 'delete_all_logs', false);?>
        </div><?php
    }

    function get_bulk_actions() {
        $actions = array();
        $actions['delete_permanently'] = __('Delete Permanently', B5FILEMANAGER_PLUGIN_NAME);

        return $actions;
    }

    function current_action() {
        if (isset($_REQUEST['delete_all_logs']))
            return 'delete_all_logs';

        return parent::current_action();
    }

    function process_bulk_action() {
        global $wpdb;
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action))
                wp_die(__('Nope! Security check failed!', B5FILEMANAGER_PLUGIN_NAME));
        }

        if ('delete_permanently' === $this->current_action()) {
            if (!isset($_POST['download']) || $_POST['download'] == null) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('No entries were selected.', B5FILEMANAGER_PLUGIN_NAME) . '</strong></p><p><em>' . __('Click to Dismiss', B5FILEMANAGER_PLUGIN_NAME) . '</em></p></div>';
                return;
            }

            foreach ($_POST['download'] as $item) {
                $del_row = $wpdb->query(
                    'DELETE FROM ' . $wpdb->prefix . 'b5_file_manager_downloads WHERE id = "' . $item . '";'
                );
            }

            if ($del_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entries Deleted!', B5FILEMANAGER_PLUGIN_NAME) . '</strong></p><p><em>' . __('Click to Dismiss', B5FILEMANAGER_PLUGIN_NAME) . '</em></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', B5FILEMANAGER_PLUGIN_NAME) . '</strong></p><p><em>' . __('Click to Dismiss', B5FILEMANAGER_PLUGIN_NAME) . '</em></p></div>';
            }
        }

        if ('delete' === $this->current_action()) {
            $item_id = isset($_GET['download']) ? $_GET['download'] : '';
            $del_download_row = $wpdb->query(
                'DELETE FROM ' . $wpdb->prefix . 'b5_file_manager_downloads WHERE id = "' . $item_id . '";'
            );

            if ($del_download_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entry Deleted!', B5FILEMANAGER_PLUGIN_NAME) . '</strong></p><p><em>' . __('Click to Dismiss', B5FILEMANAGER_PLUGIN_NAME) . '</em></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', B5FILEMANAGER_PLUGIN_NAME) . '</strong></p><p><em>' . __('Click to Dismiss', B5FILEMANAGER_PLUGIN_NAME) . '</em></p></div>';
            }
        }

        if ('delete_all_logs' === $this->current_action()) {
            $del_all_download = $wpdb->query(
                'DELETE FROM ' . $wpdb->prefix . 'b5_file_manager_downloads WHERE visitor_name="'.$this->username.'";'
            );

            if ($del_all_download) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('All logs deleted!', B5FILEMANAGER_PLUGIN_NAME) . '</strong></p><p><em>' . __('Click to Dismiss', B5FILEMANAGER_PLUGIN_NAME) . '</em></p></div>';
            }
        }
    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries
        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        //$data = $this->example_data;

        function usort_reorder($a, $b) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date';
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc';

            $result = strnatcasecmp($a[$orderby], $b[$orderby]);
            return ($order === 'asc') ? $result : -$result;
        }

        $data_results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'b5_file_manager_downloads WHERE visitor_name="'.$this->username.'"');
        $data = array();
        foreach ($data_results as $data_result) {
            $data[] = array(
                'ID'=>$data_result->id,
                'file_name'=>$this->get_file_name($data_result->file_id),
                'file_id'=>$data_result->file_id,
                'visitor_ip' => $data_result->visitor_ip,
                'date' => $data_result->date_time,
                'folder_name' => $this->get_folder_name($data_result->folder_id),
                'folder_id' => $data_result->folder_id
            );
        }

        usort($data, 'usort_reorder');
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;
        $this->set_pagination_args(array(
            'total_items'=>$total_items,
            'per_page'=>$per_page,
            'total_pages'=>ceil($total_items / $per_page)
        ));
    }

    function get_folder_name($folder_id) {
        $folder_post = get_post($folder_id);

        if(!$folder_post) {
            $folder_name = __('Delete folder', B5FILEMANAGER_PLUGIN_NAME);
        } else {
            $folder_name = trim($folder_post->post_title) ? $folder_post->post_title : __("(no title)");
        }

        return $folder_name;
    }

    function get_file_name($file_id) {
        $file_post = get_post($file_id);
        if(!$file_post) {
            $file_name = __('Delete file', B5FILEMANAGER_PLUGIN_NAME);
        } else {
            $file_name = trim($file_post->post_title) ? $file_post->post_title : __("(no title)");
        }

        return $file_name;
    }

    function get_total_logs_number() {
        global $wpdb;
        return intval($wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."b5_file_manager_downloads;"));
    }
}