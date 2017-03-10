jQuery(function($) {
	var template = $('#tmpl-b5-file-advanced').html();

	$('.b5-file-advanced-upload').on('click', function(e) {
		e.preventDefault();

		var $uploadButton = $(this),
			$fileList = $uploadButton.siblings('.b5-uploaded'),
			frame,
			frameOptions = {
                title: b5FileUpload.frameTitle,
				className: 'media-frame',
				multiple: 'add'
			};

		frame = wp.media(frameOptions);

		// Open media uploader
		frame.open();

		// Remove all attached 'select' event
		frame.off('select');

		// Handle selection
		frame.on('select', function() {
			// Get selections
			var selection = frame.state().get('selection').toJSON(),
				ids;

			// Get only files that haven't been added to the list
			// Also prevent duplication when send ajax request
			selection = _.filter(selection, function(attachment) {
				return $fileList.children('li#item_' + attachment.id).length == 0;
			});
			ids = _.pluck(selection, 'id');

			if (ids.length > 0) {
				// Attach attachment to field and get HTML
				var data = {
					action: 'b5_attach_file',
					post_id: $('#post_ID').val(),
					field_id: $fileList.data('field_id'),
					attachment_ids: ids,
					_ajax_nonce: $uploadButton.data('attach_file_nonce')
				};
                $.post(ajaxurl, data, function(r) {
					if (r.success) {
						$fileList
							.append(_.template(template, { attachments: selection }, {
								evaluate:    /<#([\s\S]+?)#>/g,
								interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
								escape:      /\{\{([^\}]+?)\}\}(?!\})/g
							}))
							.trigger('update.b5File');
					}
				}, 'json');
			}
		});
	});

    // Delete file via Ajax
    $('.b5-uploaded').on('click', '.b5-delete-file', function() {
        var $this = $(this),
            $parent = $this.parents('li'),
            $container = $this.closest('.b5-uploaded'),
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
                $container.trigger('update.b5File');
            }

            $('.b5-uploaded').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
                $(this).remove();
                $container.trigger('update.b5File');
            });
        }, 'json');

        return false;
    });

    //Remove deleted file
    $('.b5-uploaded').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
        $(this).remove();
    });

    $('body').on('update.b5File', '.b5-uploaded', function() {

        var $fileList = $(this),
            numFiles = $fileList.children().length;

        numFiles > 0 ? $fileList.removeClass('hidden') : $fileList.addClass('hidden');

        return false;
    });

    $('.b5-file-advanced-remove-all').on('click', function(e) {
        e.preventDefault();

        var answer = confirm(b5FileUpload.removeAllMessage);

        if(answer) {
            var $this = $(this),
                $fileList = $this.siblings('ul.b5-file'),
                data = {
                action: 'b5_remove_all_files',
                post_id: $('#post_ID').val(),
                field_id: $this.data('field_id'),
                _ajax_nonce: $this.data('remove_all_file_nonce')
            };
            $.post(ajaxurl, data, function(r) {
                if (r.success) {
                    $fileList.children().remove();
                }
            }, 'json');
        }

        return false;
    });

    $('#b5_downloads_reset').on('click', function(e) {
        e.preventDefault();
        var $this = $(this),
            data = {
                action: 'remove_all_file_downloads',
                file_id: $('#post_ID').val(),
                _ajax_nonce: $this.data('remove_all_nonce')
            };

        $.post(ajaxurl, data, function(r) {
            if (r.success) {
                $('#b5-meta-downloads-number').html('0');
            }
        }, 'json');
    })

    $('#b5_downloads_folder_reset').on('click', function(e) {
        e.preventDefault();
        var $this = $(this),
            data = {
                action: 'remove_all_folder_downloads',
                folder_id: $('#post_ID').val(),
                _ajax_nonce: $this.data('remove_all_nonce')
            };

        $.post(ajaxurl, data, function(r) {
            if (r.success) {
                $('#b5-meta-downloads-number').html('0');
            }
        }, 'json');
    })

    jQuery('#b5-file-advanced-add-folder-id').click(function(e) {
        $('#b5-file-form-add-folder').slideDown("fast").find("input").focus();
        $(this).hide();
        e.preventDefault();
    });

    jQuery('#b5-file-button-cancel-id').click(function(e) {
        $('#b5-file-form-add-folder').slideUp('fast', function(){$('#b5-file-advanced-add-folder-id').show();}).find('input').removeClass('error').val('');
        e.preventDefault();
    });

    jQuery('#b5-file-create-folder-id').click(function(e) {
        var $this = $(this),
            template = $('#tmpl-b5-folder-child-advanced').html(),
            folder_name = $('#b5-file-child-folder-name'),
            parent_folder = $('#post_ID').val(),
            folder_list = $('#b5-folders-id');
            data = {
                action:'add_child_folder',
                folder_name:folder_name.val(),
                parent_folder:parent_folder,
                _ajax_nonce:$this.data('add_folder_nonce')
            };

        if(folder_name.val() === "") {
            (folder_name).addClass('error');
            return;
        } else {
            $.post(ajaxurl, data, function(r) {
                if(!r.success) {
                    alert(r.data);
                    return;
                } else {
                    var folder = new Object();
                    var selection = new Array();

                    folder.id = r.data['id'];
                    folder.name = r.data['name'];
                    folder.edit_link = r.data['edit_link'];
                    folder.trash_link = r.data['trash_link'];

                    selection.push(folder);

                    folder_list
                        .prepend(_.template(template, { attachments: selection }, {
                            evaluate:    /<#([\s\S]+?)#>/g,
                            interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                            escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                        }))
                        .trigger('update.b5ChildFolder');
                }
                $('#b5-file-form-add-folder').slideUp('fast', function(){$('#b5-file-advanced-add-folder-id').show();}).find('input').removeClass('error').val('');

            }, 'json');
            return false;
        }
    });

    jQuery('#b5-file-child-folder-name').bind('change paste keyup', function() {
        $(this).removeClass('error');
    });

    $('body').on('update.b5ChildFolder', '.b5-folders', function() {
        var $fileList = $(this),
            numFiles = $fileList.children().length;

        numFiles > 0 ? $fileList.removeClass('hidden') : $fileList.addClass('hidden');

        return false;
    });
});