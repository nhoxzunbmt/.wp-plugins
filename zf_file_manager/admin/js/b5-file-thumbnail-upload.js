jQuery(function($) {

	var template = $('#tmpl-b5-file-thumbnail-advanced').html();

	$('.b5-file-thumbnail-advanced-upload').on('click', function(e) {
		e.preventDefault();

		var $uploadButton = $(this),
			$fileList = $uploadButton.siblings('.b5-uploaded'),
			frame,
            removeButton = $('.b5-delete-thumbnail-file'),
			frameOptions = {
				className: 'media-frame',
				multiple: false,
				title: b5ThumbnailUpload.frameTitle,
                library: {
                    type: 'image'
                }
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
				id_thumbnail;

			// Get only files that haven't been added to the list
			// Also prevent duplication when send ajax request
			selection = _.filter(selection, function(attachment) {
				return $fileList.children('li#item_' + attachment.id).length == 0;
			});
            //id_thumbnail = _.pluck(selection, 'id');

            id_thumbnail = frame.state().get("selection").first().attributes.id;

			if (id_thumbnail > 0) {

				// Attach attachment to field and get HTML
				var data = {
					action: 'b5_attach_thumbnail_file',
					post_id: $('#post_ID').val(),
					field_id: $fileList.data('field_id'),
					attachment_id: id_thumbnail,
					_ajax_nonce: $uploadButton.data('attach_file_thumbnail_nonce')
				};

                $.post(ajaxurl, data, function(r) {
					if (r.success) {
						$fileList
							.append(_.template(template, { attachments: selection }, {
								evaluate:    /<#([\s\S]+?)#>/g,
								interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
								escape:      /\{\{([^\}]+?)\}\}(?!\})/g
							}))

                        $uploadButton.fadeOut('fast');
                        removeButton.fadeIn('slow');

					}
				}, 'json');
			}
		});
	});

    // Delete file via Ajax
    $('.b5-delete-thumbnail-file').on('click', function() {
        var $this = $(this),
            $parent = $('li.b5-thumbnail'),
            $uploader = $('.b5-file-thumbnail-advanced-upload'),
            $container = $('.b5-uploaded');
            data = {
                action: 'b5_delete_file',
                _ajax_nonce: $container.data('delete_nonce'),
                post_id: $('#post_ID').val(),
                field_id: $container.data('field_id'),
                attachment_id: $parent.data('attachment_id')
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
            }

            $this.fadeOut('fast');
            $uploader.fadeIn('slow');

            $('.b5-uploaded').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
                $(this).remove();
            });

        }, 'json');

        return false;
    });

    //Remove deleted file
    $('.b5-uploaded').on('transitionend webkitTransitionEnd otransitionend', 'li.removed', function() {
        $(this).remove();
    });
});