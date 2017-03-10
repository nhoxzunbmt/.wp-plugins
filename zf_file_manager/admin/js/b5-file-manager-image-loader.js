jQuery(document).ready(function($) {
	
	//"use strict";

    jQuery('.b5_options_upload_image_button').on('click', function(event) {

        var activeFileUploadContext = jQuery(this).parent().parent();

        event.preventDefault();

        // if its not null, its broking custom_file_frame's onselect "activeFileUploadContext"
        custom_file_frame = null;

        // Create the media frame.
        custom_file_frame = wp.media.frames.customHeader = wp.media({
            // Set the title of the modal.
            title: jQuery(this).data("choose"),

            // Tell the modal to show only images. Ignore if want ALL
            library: {
                type: 'image'
            },
            // Customize the submit button.
            button: {
                // Set the text of the button.
                text: jQuery(this).data("update")
            }
        });

        custom_file_frame.on("select", function() {

            // Grab the selected attachment.
            var attachment = custom_file_frame.state().get("selection").first();

            var srcIcon = attachment.attributes.url
            if(attachment.attributes.sizes.thumbnail) {
                srcIcon = attachment.attributes.sizes.thumbnail.url
            }

            // Update value of the targetfield input with the attachment url.
            //jQuery('.b5_file_manager_redux_opts_screenshot', activeFileUploadContext).attr('src', attachment.attributes.url);
            jQuery('<img>', {
                src: srcIcon
            }).appendTo(jQuery('.b5_icon_image_holder', activeFileUploadContext));
            jQuery('.b5_options_upload_image_id', activeFileUploadContext).val(attachment.attributes.id);
            jQuery('.b5_options_upload_image_button', activeFileUploadContext).hide();
            //jQuery('.b5_file_manager_redux_opts_screenshot', activeFileUploadContext).show();
            jQuery('.b5_options_remove_image_button', activeFileUploadContext).show();
        });

        custom_file_frame.open();
    });

    jQuery('.b5_options_remove_image_button').on('click', function(event) {
        var activeFileUploadContext = jQuery(this).parent().parent();

        event.preventDefault();

        //jQuery('.b5_file_manager_redux_opts_screenshot', activeFileUploadContext).fadeOut('slow');
        jQuery('img', activeFileUploadContext).remove();
        jQuery(this).fadeOut('slow');
        jQuery('.b5_options_upload_image_id', activeFileUploadContext).val('0');
        jQuery('.b5_options_upload_image_button', activeFileUploadContext).fadeIn('slow');
    });
});