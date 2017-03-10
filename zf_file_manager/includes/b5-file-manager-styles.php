<?php

    add_action('wp_head', 'b5_file_manager_custom_styles');
    function b5_file_manager_custom_styles() {

        //dashicons
        $b5_file_manager_dashicons_color = get_option('b5-file-manager-dashicons-color', '#d8d8d8');
        $b5_file_manager_icon_size = get_option('b5-file-manager-icon-size', 60);
        $b5_file_manager_hover_background_color = get_option('b5-file-manager-hover-bg-color', '#f2f2f2');
        $b5_file_manager_item_border_color = get_option('b5-file-manager-item-border-color', '#C7C7C7');
        $b5_file_manager_button_bg_color = get_option('b5-file-manager-button-bg-color', '#CCC');
        $b5_file_manager_menu_border_color = get_option('b5-file-manager-menu-border-color', '#e5e5e5');

        $b5_file_manager_custom_css = stripslashes(get_option('b5-file-manager-custom-css', ''));

        ?>
            <style type='text/css'>
                .b5-file-manager-container .b5-item .dashicons,  .b5-file-manager-container .b5-item .dashicons-before:before{
                    color:<?php echo $b5_file_manager_dashicons_color;?>;
                    font-size:<?php echo is_integer((int)$b5_file_manager_icon_size)?$b5_file_manager_icon_size:'60';?>px;
                    width:<?php echo is_integer((int)$b5_file_manager_icon_size)?$b5_file_manager_icon_size:'60';?>px;
                    height:<?php echo is_integer((int)$b5_file_manager_icon_size)?$b5_file_manager_icon_size:'60';?>px;
                }

                .b5-grid .b5-icon a{
	                width:<?php echo is_integer((int)$b5_file_manager_icon_size)?$b5_file_manager_icon_size:'60';?>px;
                    height:<?php echo is_integer((int)$b5_file_manager_icon_size)?$b5_file_manager_icon_size:'60';?>px;
                }

                .b5-list li.b5-item{
                    border-bottom:1px solid <?php echo $b5_file_manager_item_border_color;?>;
                }

                li.b5-item:hover{
                    background-color:<?php echo $b5_file_manager_hover_background_color;?>;
                }

                .b5-file-manager a.b5-download {
                    background:<?php echo $b5_file_manager_button_bg_color;?>;
                }

                .b5-menu-bar, .b5-file-manager-information {
                    border-color:<?php echo $b5_file_manager_menu_border_color;?>;
                }

                <?php echo $b5_file_manager_custom_css;?>
            </style>
        <?php
    }