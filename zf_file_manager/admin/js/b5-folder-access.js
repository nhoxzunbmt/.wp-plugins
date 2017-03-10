jQuery(document).ready(function($) {
    $('.b5-admin-tabs-container').each(function() {
        $('.b5-admin-tabs li a').click(function() {

            var t = $(this).attr('href');
            $(this).parent().addClass('b5-tabs').siblings('li').removeClass('b5-tabs');
            $(this).parent().parent().siblings('.b5-tabs-panel').hide();
            $(t).show();

            return false;
        });
    });

    /**/
    $('#folder_users_access').bind('change', function() {
        var folder_acces = this.checked;
        folder_acces == true ? $('.b5-admin-tabs-container').slideUp() : $('.b5-admin-tabs-container').slideDown();
        return false;
    })

    /*Users group*/
    $('#folder_users_group_all').bind('change', function() {
        var check_all_value = this.checked;
        $('input[type="checkbox"][class^=folder_users_group_]').not(':disabled').each(function() {
            $(this).prop('checked', check_all_value);
        })
    });

    /*Users*/
    $('#folder_user_all').bind('change', function() {
        var check_all_value = this.checked;
        $('input[type="checkbox"][class^=folder-user-]').not(':disabled').each(function() {
            $(this).prop('checked', check_all_value);
        })
    });

    // Delete user via Ajax
    $('.b5-users-group').on('click', '.b5-delete-user', function() {
        var $this = $(this),
            $parent = $this.parents('li'),
            $container = $this.closest('.b5-users-group'),
            data = {
                action: 'b5_delete_user',
                _ajax_nonce: $container.data('delete_nonce'),
                post_id: $('#post_ID').val(),
                field_id: $container.data('field_id'),
                user_id: $this.data('user_id')
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
                $container.trigger('update.b5User');
            }

            $('.b5-users-group').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
                $(this).remove();
                $container.trigger('update.b5User');
            });
        }, 'json');

        return false;
    });

    //Remove deleted user
    $('.b5-users-group').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
        $(this).remove();
    });

    $('body').on('update.b5User', '.b5-users-group', function() {

        var $userList = $(this),
            numUsers = $userList.children().length;

        numUsers > 0 ? $userList.removeClass('hidden') : $userList.addClass('hidden');

        return false;
    });

    jQuery('#b5-file-group-users-id').click(function() {
        tb_show(b5UsersSelect.frameTitle, 'admin-ajax.php?group_id=' + $('#post_ID').val() + '&action=b5_meta_all_users_html');
        return false;
    });

    $('.b5-admin-public-folder').bind('change', function() {
        var $this = $(this),
            check_public_value = $this.attr('checked') == 'checked' ? 'on' : 'off',
            data = {
                action: 'folder_access_public',
                post_id: $this.val(),
                public_value: check_public_value
            };

        $.post(ajaxurl, data, function(r) {
            if(!r.success) {
                alert(r.data);
            }
        }, 'json');
        return false;
    });

    $('.b5-file-advanced-users-remove-all').on('click', function(e) {
        e.preventDefault();

        var answer = confirm(b5UsersSelect.removeAllMessage);

        if(answer) {
            var $this = $(this),
                $userList = $this.siblings('ul.b5-users-group'),
                data = {
                    action: 'b5_remove_all_users',
                    post_id: $('#post_ID').val(),
                    field_id: $this.data('field_id'),
                    _ajax_nonce: $this.data('remove_all_users_nonce')
                };
            $.post(ajaxurl, data, function(r) {
                if (r.success) {
                    $userList.children().remove();
                }
            }, 'json');
        }

        return false;
    });
});

function b5_users_group_add(obj) {
    "use strict";
    jQuery(function($) {
        var template = $('#tmpl-b5-users-group').html();

        var $insertButton = $(obj),
            id_user,
            media_container = $insertButton.parent(),
            $userList = $('.b5-users-group');

        id_user = $insertButton.attr('data-user_id');

        var data = {
            action: 'b5_attach_users_group',
            group_id: $('#post_ID').val(),
            user_id: id_user,
            _ajax_nonce: $insertButton.attr('data-select_user_group_nonce')
        };

        var user_group = new Object();
        user_group.id = id_user;
        user_group.username = $insertButton.parent().find('span.title').html();
        user_group.display_name = $insertButton.parent().find('input.user-display-name').val();
        user_group.edit_url = $insertButton.parent().find('input.user-edit-link').val();

        var selection = new Array();

        selection.push(user_group);

        $.post(ajaxurl, data, function(r) {
            if (r.success) {
                $userList
                    .append(_.template(template, { users: selection }, {
                        evaluate:    /<#([\s\S]+?)#>/g,
                        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                        escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                    }))
                    .trigger('update.b5User');

                media_container.fadeOut('fast', function() {
                    if($(this).siblings().size()==0) {
                        var p = $("<p />", {
                            "text" : b5UsersSelect.frameNoUsers
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