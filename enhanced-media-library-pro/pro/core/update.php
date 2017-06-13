<?php

if ( ! defined( 'ABSPATH' ) )
    exit;



/**
 *  wpuxss_eml_pro_site_transient_update_plugins
 *
 *  @since    2.0
 *  @created  13/10/14
 */

add_filter ('pre_set_site_transient_update_plugins', 'wpuxss_eml_pro_site_transient_update_plugins');

if ( ! function_exists( 'wpuxss_eml_pro_site_transient_update_plugins' ) ) {

    function wpuxss_eml_pro_site_transient_update_plugins( $transient ) {

        global $wpuxss_eml_version;


        $wpuxss_eml_pro_basename = wpuxss_get_eml_basename();


        if ( isset( $transient->response[$wpuxss_eml_pro_basename] ) ) {
            return $transient;
        }



        $license_key = get_option( 'wpuxss_eml_pro_license_key', '' );
        $info = wpuxss_eml_pro_remote_info( 'get-info', $license_key );


        $eml_license_active = ( ! empty( $license_key ) && ! is_wp_error( $info ) && 'active' === $info->license_status ) ? 1 : 0;
        set_site_transient( 'eml_license_active', $eml_license_active );


        if ( is_wp_error( $info ) ) {
            $transient->eml_error = $info->get_error_message();
            return $transient;
        }
        elseif ( version_compare( $info->version, $wpuxss_eml_version, '<=' ) ) {
            return $transient;
        }


        $wpuxss_eml_pro_slug = wpuxss_get_eml_slug();


        $args = array(
            'action' => 'update',
            'key' => $license_key
        );

        $new_info = new stdClass();

        $new_info->name = $info->name;
        $new_info->slug = $wpuxss_eml_pro_slug;
        $new_info->plugin = $wpuxss_eml_pro_basename;
        $new_info->new_version = $info->version;
        $new_info->requires = $info->requires;
        $new_info->tested = $info->tested;
        $new_info->added = $info->added;
        $new_info->last_updated = $info->last_updated;
        $new_info->url = $info->url;
        $new_info->package = ( ! empty( $license_key ) && 'active' === $info->license_status ) ? wpuxss_eml_pro_get_url() . '?' . build_query( $args ) : '';

        $transient->response[$wpuxss_eml_pro_basename] = $new_info;


        return $transient;
    }
}



/**
 *  wpuxss_eml_update_request_error_message
 *
 *  @since    2.4.2
 *  @created  17/01/17
 */

add_action( 'after_plugin_row_' . wpuxss_get_eml_basename(), 'wpuxss_eml_update_request_error_message', 10, 3 );

if ( ! function_exists( 'wpuxss_eml_update_request_error_message' ) ) {

    function wpuxss_eml_update_request_error_message( $file, $plugin_data, $status ) {

        if ( ! current_user_can( 'update_plugins' ) ) {
            return false;
        }


        $transient = get_site_transient( 'update_plugins' );

        if ( ! isset( $transient->eml_error ) ) {
            return false;
        }


        $plugin_name   = __( 'Enhanced Media Library PRO', 'enhanced-media-library' );
        $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );


        if ( is_network_admin() || ! is_multisite() ) {
            if ( is_network_admin() ) {
                $active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
            } else {
                $active_class = is_plugin_active( $file ) ? ' active' : '';
            }

            echo '<tr class="plugin-update-tr' . $active_class . '" id="' . esc_attr( wpuxss_get_eml_slug() . '-update-error' ) . '" data-slug="' . esc_attr( wpuxss_get_eml_slug() ) . '" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';

            printf( __( '%1$s could not establish a secure connection to request information about available updates. An error occurred: %2$s. Please <a href="%3$s">contact plugin authors</a>.', 'enhanced-media-library' ),
                $plugin_name,
                '<strong>'.$transient->eml_error.'</strong>',
                'https://www.wpuxsolutions.com/support/'
            );

            echo '</p></div></td></tr>';
        }
    }
}



/**
 *  wpuxss_eml_pro_plugins_api
 *
 *  @since    2.0
 *  @created  13/10/14
 */

add_filter( 'plugins_api', 'wpuxss_eml_pro_plugins_api', 10, 3 );

if ( ! function_exists( 'wpuxss_eml_pro_plugins_api' ) ) {

    function wpuxss_eml_pro_plugins_api( $res, $action, $args ) {

        $wpuxss_eml_pro_slug = wpuxss_get_eml_slug();


        if ( ! isset( $args->slug ) || $args->slug != $wpuxss_eml_pro_slug ) {
            return $res;
        }


        // getting info from the free version
        $args->slug = 'enhanced-media-library';

        $url = $http_url = 'http://api.wordpress.org/plugins/info/1.0/';
        if ( $ssl = wp_http_supports( array( 'ssl' ) ) ) {
            $url = set_url_scheme( $url, 'https' );
        }

        $http_args = array(
            'timeout' => 15,
            'body' => array(
                'action' => $action,
                'request' => serialize( $args )
            )
        );
        $request = wp_remote_post( $url, $http_args );

        if ( $ssl && is_wp_error( $request ) ) {

            trigger_error( __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.' ) . ' ' . __( '(WordPress could not establish a secure connection to WordPress.org. Please contact your server administrator.)','enhanced-media-library' ), headers_sent() || WP_DEBUG ? E_USER_WARNING : E_USER_NOTICE );
            $request = wp_remote_post( $http_url, $http_args );
        }

        if ( is_wp_error($request) ) {

            $res = new WP_Error('plugins_api_failed', __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.','enhanced-media-library' ), $request->get_error_message() );
        }
        else {

            $res = maybe_unserialize( wp_remote_retrieve_body( $request ) );
            if ( ! is_object( $res ) && ! is_array( $res ) )
            $res = new WP_Error('plugins_api_failed', __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.','enhanced-media-library' ), wp_remote_retrieve_body( $request ) );
        }


        // getting info for PRO from the latest transient
        $transient = get_site_transient( 'update_plugins' );
        $wpuxss_eml_pro_basename = wpuxss_get_eml_basename();
        $info = $transient->response[$wpuxss_eml_pro_basename];


        if ( empty ( $info ) ) {
            return $res;
        }

        $res->name          = $info->name;
        $res->slug          = $info->slug;
        $res->version       = $info->new_version;
        $res->requires      = $info->requires;
        $res->tested        = $info->tested;
        $res->added         = $info->added;
        $res->last_updated  = $info->last_updated;
        $res->download_link = $info->package;

        return $res;
    }
}



/**
 *  wpuxss_eml_pro_in_plugin_update_message
 *
 *  @since    2.0
 *  @created  13/10/14
 */

add_action( 'in_plugin_update_message-' . wpuxss_get_eml_basename(), 'wpuxss_eml_pro_in_plugin_update_message', 10, 2 );

if ( ! function_exists( 'wpuxss_eml_pro_in_plugin_update_message' ) ) {

    function wpuxss_eml_pro_in_plugin_update_message( $plugin_data, $r ) {

        $transient = get_site_transient( 'update_plugins' );
        $wpuxss_eml_pro_basename = wpuxss_get_eml_basename();


        if ( ! empty( $transient->response[$wpuxss_eml_pro_basename]->package ) ) {
            return;
        }


        echo '<br />' . sprintf(
            __('To unlock updates, please <a href="%s">activate your license</a>. You can get your license key in <a href="%s">Your Account</a>. If you do not have a license, you are welcome to <a href="%s">purchase it</a>.', 'enhanced-media-library'),
            admin_url('options-general.php?page=eml-settings#eml-license-key-section'),
            'https://www.wpuxsolutions.com/account/',
            'https://www.wpuxsolutions.com/pricing/'
        );
    }
}



/**
 *  wpuxss_eml_pro_remote_info
 *
 *  @since    2.1
 *  @created  28/10/15
 */

if ( ! function_exists( 'wpuxss_eml_pro_remote_info' ) ) {

    function wpuxss_eml_pro_remote_info( $action, $license_key = '' ) {

        global $wpuxss_eml_version;


        $url = wpuxss_eml_pro_get_url();

        $args = array(
            'timeout' => 15,
            'body' => array(
                'action' => $action,
                'key' => $license_key,
                'version' => $wpuxss_eml_version
            )
        );

        $request = wp_remote_post( $url, $args );

        if ( ! is_wp_error( $request ) )
            $response = maybe_unserialize( wp_remote_retrieve_body( $request ) );
        else
            $response = $request;

        return $response;
    }
}



/**
 *  wpuxss_eml_pro_get_url
 *
 *  @since    2.1
 *  @created  28/10/15
 */

if ( ! function_exists( 'wpuxss_eml_pro_get_url' ) ) {

    function wpuxss_eml_pro_get_url() {

        return 'https://www.wpuxsolutions.com/downloads/plugins/enhanced-media-library-pro/';
    }
}

?>
