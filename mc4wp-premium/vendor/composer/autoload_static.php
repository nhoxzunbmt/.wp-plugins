<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9d984f7128efc372996ed8198bf21b01
{
    public static $prefixesPsr0 = array (
        'x' => 
        array (
            'xrstf\\Composer52' => 
            array (
                0 => __DIR__ . '/..' . '/xrstf/composer-php52/lib',
            ),
        ),
    );

    public static $classMap = array (
        'DVK_API_Request' => __DIR__ . '/../..' . '/licensing/includes/class-api-request.php',
        'DVK_License_Manager' => __DIR__ . '/../..' . '/licensing/includes/class-license-manager.php',
        'DVK_Plugin_License_Manager' => __DIR__ . '/../..' . '/licensing/includes/class-plugin-license-manager.php',
        'DVK_Plugin_Update_Manager' => __DIR__ . '/../..' . '/licensing/includes/class-plugin-update-manager.php',
        'DVK_Product' => __DIR__ . '/../..' . '/licensing/includes/class-product-base.php',
        'DVK_Update_Manager' => __DIR__ . '/../..' . '/licensing/includes/class-update-manager.php',
        'MC4WP_AJAX_Forms' => __DIR__ . '/../..' . '/ajax-forms/includes/class-ajax-forms.php',
        'MC4WP_AJAX_Forms_Admin' => __DIR__ . '/../..' . '/ajax-forms/includes/class-admin.php',
        'MC4WP_Custom_Color_Theme' => __DIR__ . '/../..' . '/custom-color-theme/includes/class-theme.php',
        'MC4WP_Custom_Color_Theme_Admin' => __DIR__ . '/../..' . '/custom-color-theme/includes/class-admin.php',
        'MC4WP_Dashboard_Log_Widget' => __DIR__ . '/../..' . '/logging/includes/class-dashboard-log-widget.php',
        'MC4WP_Email_Notification' => __DIR__ . '/../..' . '/email-notifications/includes/class-email-notification.php',
        'MC4WP_Form_Notification_Factory' => __DIR__ . '/../..' . '/email-notifications/includes/class-factory.php',
        'MC4WP_Form_Notifications_Admin' => __DIR__ . '/../..' . '/email-notifications/includes/class-admin.php',
        'MC4WP_Form_Widget_Enhancements' => __DIR__ . '/../..' . '/multiple-forms/includes/class-widget-enhancements.php',
        'MC4WP_Forms_Table' => __DIR__ . '/../..' . '/multiple-forms/includes/class-forms-table.php',
        'MC4WP_Graph' => __DIR__ . '/../..' . '/logging/includes/class-graph.php',
        'MC4WP_Log_Exporter' => __DIR__ . '/../..' . '/logging/includes/class-log-exporter.php',
        'MC4WP_Log_Item' => __DIR__ . '/../..' . '/logging/includes/class-log-item.php',
        'MC4WP_Log_Table' => __DIR__ . '/../..' . '/logging/includes/class-log-table.php',
        'MC4WP_Logger' => __DIR__ . '/../..' . '/logging/includes/class-logger.php',
        'MC4WP_Logging_Admin' => __DIR__ . '/../..' . '/logging/includes/class-admin.php',
        'MC4WP_Logging_Installer' => __DIR__ . '/../..' . '/logging/includes/class-installer.php',
        'MC4WP_Multiple_Forms_Admin' => __DIR__ . '/../..' . '/multiple-forms/includes/class-admin.php',
        'MC4WP_Product' => __DIR__ . '/../..' . '/licensing/includes/class-product.php',
        'MC4WP_RGB_Color' => __DIR__ . '/../..' . '/custom-color-theme/includes/class-rgb-color.php',
        'MC4WP_Required_Plugins_Notice' => __DIR__ . '/../..' . '/includes/class-required-plugins-notice.php',
        'MC4WP_Styles_Builder' => __DIR__ . '/../..' . '/styles-builder/includes/class-styles-builder.php',
        'MC4WP_Styles_Builder_Admin' => __DIR__ . '/../..' . '/styles-builder/includes/class-admin.php',
        'MC4WP_Styles_Builder_Public' => __DIR__ . '/../..' . '/styles-builder/includes/class-public.php',
        'xrstf\\Composer52\\AutoloadGenerator' => __DIR__ . '/..' . '/xrstf/composer-php52/lib/xrstf/Composer52/AutoloadGenerator.php',
        'xrstf\\Composer52\\Generator' => __DIR__ . '/..' . '/xrstf/composer-php52/lib/xrstf/Composer52/Generator.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit9d984f7128efc372996ed8198bf21b01::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit9d984f7128efc372996ed8198bf21b01::$classMap;

        }, null, ClassLoader::class);
    }
}
