jQuery(document).ready(function($) {

    "use strict";

    $('#b5_file_manager_icon_type').val() == 'custom' ? showIconsSelect() : hiddenIconsSelect();

    $('#b5_file_manager_icon_type').on('change', function() {
        if($(this).val()=='custom') {
            showIconsSelect();
        } else {
            hiddenIconsSelect();
        }
    })

    function hiddenIconsSelect() {
        $('div.b5_file_manager_icon').fadeOut('fast');
    }

    function showIconsSelect() {
        $('div.b5_file_manager_icon').fadeIn('slow');
    }

    $('input.b5-file-manager-popup-colorpicker').wpColorPicker({
        palettes: ['#27CCC0', '#78cd6e', '#29c1e7', '#ae81f9', '#f78224', '#FF4629']
    });

    $(".b5_file_manager_tabs .b5_file_manager_tab[id^=tab_menu]").click(function() {
        var curMenu=$(this);
        $(".b5_file_manager_tabs .b5_file_manager_tab[id^=tab_menu]").removeClass("b5-selected");
        curMenu.addClass("b5-selected");
        var index=curMenu.attr("id").split("tab_menu_")[1];
        $(".b5_file_manager_curvedContainer .b5_file_manager_tabcontent").css("display","none");
        $(".b5_file_manager_curvedContainer #b5_file_manager_tab_content_"+index).css("display","block");
    });

    //get_option values
    var b5_file_manager_resetmsg = b5_file_manager_options.b5_file_manager_reset_msg;

    $('#b5_file_manager_reset_settings_button').click(function() {
        var answer = confirm(b5_file_manager_resetmsg);

        if(answer) {
            $('#b5_file_manager_savemessage').show();
            $('#b5_file_manager_savemessage').html('Loading...');

            var data = {
                action: 'b5_file_manager_reset_settings'
            }

            $.post(ajaxurl, data, function(response) {
                $(".b5_file_manager_tabs_container").fadeOut(1000);
                window.setTimeout(function() {
                    location.reload();
                }, 1000);

                $(".tabscontainer").fadeIn(1000);

                setTimeout(function() {
                    $('#b5_file_manager_savemessage').html('Settings Saved').fadeOut(1000);
                }, 1000); //wait one second to run function
            });
        }
        return false;
    })
 });