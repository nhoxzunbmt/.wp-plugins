<?php

    /*
     * Plugin Name: ZF File Manager
     * Plugin URI: http://codecanyon.net/item/file-manager/full_screen_preview/8999688
     * Description: Avanced File Manager Plugin for wordpress, allow user to access folders and download files, Administrator can create users and set permissions, create folders and upload all type of files, also can configurate the mime types for extensions not recognized by default for wordpress.
     * Author: Zerofractal
     * Author URI: http://www.zerofractal.com/
     * Text Domain: zf_file_manager
     * Version: 1.0.8
     */
	 
	// Remove update check from wp repositories
	add_filter( 'http_request_args', 'zf_prevent_update_check', 10, 2 );
	function zf_prevent_update_check( $r, $url ) {  
		if ( FALSE !== strpos( $url, '//api.wordpress.org/plugins/update-check/' ) ) {
		   
			$my_plugin = plugin_basename( __FILE__ );      

			$plugins = json_decode($r['body']['plugins'], TRUE);     

			unset( $plugins['plugins'][$my_plugin] );
			unset( $plugins['active'][array_search( $my_plugin, $plugins['active'] )] );

			$r['body']['plugins'] = json_encode($plugins, TRUE);
		   
		}  

		return $r;
	}

    define('B5FILEMANAGER_VERSION', '1.0.8');

    if(!defined('B5FILEMANAGER_PLUGIN_BASENAME')) {
        define('B5FILEMANAGER_PLUGIN_BASENAME', plugin_basename(__FILE__));
    }

    if(!defined('B5FILEMANAGER_PLUGIN_NAME')) {
        define('B5FILEMANAGER_PLUGIN_NAME', trim(dirname(B5FILEMANAGER_PLUGIN_BASENAME), '/'));
    }

    if(!defined('B5FILEMANAGER_PLUGIN_SLUG')) {
        define('B5FILEMANAGER_PLUGIN_SLUG', 'b5-file-manager-options');
    }

    if(!defined('B5FILEMANAGER_PLUGIN_DIR')) {
        define('B5FILEMANAGER_PLUGIN_DIR', untrailingslashit(dirname(__FILE__)));
    }

    if(!defined('B5FILEMANAGER_PLUGIN_ADMIN_DIR')) {
        define('B5FILEMANAGER_PLUGIN_ADMIN_DIR', B5FILEMANAGER_PLUGIN_DIR . '/admin');
    }

    if(!defined('B5FILEMANAGER_PLUGIN_URL')) {
        define('B5FILEMANAGER_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));
    }

    if(!defined('B5FILEMANAGER_PLUGIN_ADMIN_URL')) {
        define('B5FILEMANAGER_PLUGIN_ADMIN_URL', B5FILEMANAGER_PLUGIN_URL . '/admin');
    }

    if(!defined('B5FILEMANAGER_PLUGIN_IMAGES_DIR')) {
        define('B5FILEMANAGER_PLUGIN_IMAGES_DIR', B5FILEMANAGER_PLUGIN_DIR . '/images');
    }

    require_once B5FILEMANAGER_PLUGIN_DIR . '/settings.php';