jQuery(document).ready(function($) {
    "use strict";
    $(".b5-folder").draggable({
        containment: '#b5-folders-id',
        //placeholder: "ui-state-highlight",
        cursor: 'move',
        opacity: 0.6,
        revert: true,
        start: function(evento, ui) {
            ui.helper.parent().find('div.b5-info').fadeOut('fast');
        },
        stop: function(evento, ui) {
            ui.helper.parent().find('div.b5-info').fadeIn('slow');
        }
    });

    $(".b5-folder").droppable({
        over: function(event, ui) {
            $(this).addClass('b5-folder-over');
            $(this).find('.dashicons').animate({
                'font-size': 70 + 'px'
            }, 200);
        },
        drop: function(event, ui) {
            ui.draggable.parent().fadeOut(0);

            var $this = $(this),
                data = {
                    action: 'set_parent_folder',
                    _ajax_nonce: $(this).parent().data('set_parent_nonce'),
                    folder_id: ui.draggable.parent().data('folder_id'),
                    parent_id: $(this).parent().data('folder_id')
                };

              $.post(ajaxurl, data, function(r) {
                if (r.success) {
                    $this.removeClass('b5-folder-over');
                    $this.find('.dashicons').animate({
                        'font-size': 60 + 'px'
                    }, 200);
                    $this.parent().find('.b5-file-subfolders').html(r.data['subfolders']);
                }
            }, 'json');
        },
        out: function(e,ui) {
            $(this).removeClass('b5-folder-over');
            $(this).find('.dashicons').animate({
                'font-size': 60 + 'px'
            }, 200);
        }
    });
});