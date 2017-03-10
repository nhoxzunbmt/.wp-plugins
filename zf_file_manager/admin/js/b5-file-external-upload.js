jQuery(document).ready(function($) {

    "use strict";

    jQuery('#b5-file-external-upload-id').click(function() {
        tb_show(b5ExternalFileUpload.frameTitle, 'admin-ajax.php?folder_id=' + $('#post_ID').val() + '&action=b5_meta_all_external_files_html');
        return false;
    });

    // Delete file via Ajax
    $('.b5-external-uploaded').on('click', '.b5-delete-file', function() {

        var $this = $(this),
            $parent = $this.parents('li'),
            $container = $this.closest('.b5-external-uploaded'),
            data = {
                action: 'b5_delete_file',
                _ajax_nonce: $container.data('delete_nonce'),
                post_id: $('#post_ID').val(),
                field_id: $container.data('field_id'),
                attachment_id: $this.data('attachment_id')
            };

        $.post(ajaxurl, data, function(r) {
            if (!r.success) {
                alert(r.data);
                return;
            }

            $parent.addClass('removed');

            // If transition events not supported
            if (
                !('ontransitionend' in window)
                    && ('onwebkittransitionend' in window)
                    && !('onotransitionend' in myDiv || navigator.appName == 'Opera')
                )
            {
                $parent.remove();
                $container.trigger('update.b5ExternalFile');
            }

            $('.b5-external-uploaded').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
                $(this).remove();
                $container.trigger('update.b5ExternalFile');
            });
        }, 'json');
        return false;
    });

    //Remove deleted file
    $('.b5-external-uploaded').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
        $(this).remove();
    });

    $('body').on('update.b5ExternalFile', '.b5-external-uploaded', function() {

        var $fileList = $(this),
            numFiles = $fileList.children().length;

        numFiles > 0 ? $fileList.removeClass('hidden') : $fileList.addClass('hidden');

        return false;
    });
});

function b5_file_external_add(obj) {
    "use strict";
    jQuery(function($) {
        var template = $('#tmpl-b5-file-advanced').html();

        var $insertButton = $(obj),
            id_thumbnail,
            media_container = $insertButton.parent(),
            $fileList = $('.b5-external-uploaded');

        id_thumbnail = $insertButton.attr('data-thumbnail-id');

        var data = {
            action: 'b5_attach_thumbnail_file',
            post_id: $('#post_ID').val(),
            field_id: $fileList.attr('data-field_id'),
            attachment_id: id_thumbnail,
            _ajax_nonce: $insertButton.attr('data-attach_file_thumbnail_nonce')
        };

        var external_file = new Object();
        external_file.id = id_thumbnail;
        external_file.title = $insertButton.parent().find('span.title').html();
        external_file.type = 'none';
        external_file.icon = $insertButton.parent().find('img.pinkynail').attr('src');
        external_file.url = $insertButton.parent().find('input.external-url').val();
        external_file.mime = $insertButton.parent().find('input.external-file-extension').val();
        external_file.editLink = $insertButton.parent().find('input.external-edit-link').val();

        var selection = new Array();

        selection.push(external_file);

        $.post(ajaxurl, data, function(r) {
            if (r.success) {
                $fileList
                    .append(_.template(template, { attachments: selection }, {
                        evaluate:    /<#([\s\S]+?)#>/g,
                        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                        escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                    }))
                    .trigger('update.b5ExternalFile');

                media_container.fadeOut('slow', function() {
                    if($(this).siblings().size()==0) {
                        var p = $("<p />", {
                            "text" : b5ExternalFileUpload.frameNoExternalFiles
                        });
                        $(this).parent().append(p);
                    }
                    $(this).remove();
                });
            }
        }, 'json');
    })
    return false;
}