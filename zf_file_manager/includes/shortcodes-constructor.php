<?php

    function b5_file_manager_shortcode_show_folder($atts, $content = null) {
        extract(shortcode_atts(array("root_folder"=>'0'), $atts));

        $folder = get_post($root_folder);

        if(!$folder) {
            $html = __('The folder you are looking for does not exist.', B5FILEMANAGER_PLUGIN_NAME);
        } else {
            if(b5_file_manager_get_folder_access($folder->ID)) {
                $html = b5_file_manager_get_folder_front_html($folder->ID);
            } else {
                if(!is_user_logged_in()) {
                    $html = '<p class="b5-login-text">'.__('You need login to view this folder.', B5FILEMANAGER_PLUGIN_NAME).'</p>';
                    if(get_option('b5-file-manager-show-login-form', 'on')=='on') {
                        $html .= '<div class="b5-file-manager-login-form">'.wp_login_form(array('echo'=>false)).'<a class="b5-lost-password" href="'.wp_lostpassword_url(get_permalink()).'" title="'.__('Lost Password', B5FILEMANAGER_PLUGIN_NAME).'">'.__('Lost Password', B5FILEMANAGER_PLUGIN_NAME).'</a></div>';
                       
                    }
                } else {
                    if (b5_file_manager_show_folder_for_user($root_folder, get_current_user_id())) {
                        $html = b5_file_manager_get_folder_front_html($folder->ID);
                    } else {
                        $html = __('You don\'t have permission to view this folder.', B5FILEMANAGER_PLUGIN_NAME);
                    }
                }
            }
        }

        return $html;
    }
    add_shortcode('file_manager', 'b5_file_manager_shortcode_show_folder');

    function b5_file_manager_get_folder_front_html($id_folder) {
        $folder = get_post($id_folder);

        $classes = array('b5-file-manager', 'b5-'.get_option('b5-file-manager-default-view', 'grid'));
        $out = '<div class="b5-file-manager-folder">
                    <input type="hidden" value="%s" class="b5-fm-post-id" />
                            <div class="b5-menu-bar">
                            	<div class="b5-bar-controls">%s%s%s%s</div>
                    ';

        $extra_fields = get_option('b5-file-manager-extra-fields', array());
        $extra_fields_options_html = "";

        foreach($extra_fields as $key => $extra_field_name) {
            $extra_fields_options_html .= '<option value="'.str_replace("-", "_", $key).'" '.selected($key, get_option('b5-file-manager-default-sort-by', 'original'), false).'>'.$extra_field_name.'</option>';
        }

        $html = sprintf(
            $out,
            get_the_ID(),
            get_option('b5-file-manager-show-filter-controls', 'on') == 'on' ? sprintf('<input type="text" placeholder="%s" class="b5-filter-value" />', get_option('b5-file-manager-filter-message', 'Filter')) : '',
            get_option('b5-file-manager-show-sort-controls', 'on') == 'on' ? sprintf('<select name="b5-sort-by" class="b5-sort-by-id"><option value="original-order" %s>%s</option><option value="date" %s>%s</option><option value="title" %s>%s</option><option value="weight" %s>%s</option><option value="type" %s>%s</option>%s</select>', selected(get_option('b5-file-manager-default-sort-by', 'original'), 'original', false), __("Original order", B5FILEMANAGER_PLUGIN_NAME), selected(get_option('b5-file-manager-default-sort-by', 'original'), 'date', false), __("Date", B5FILEMANAGER_PLUGIN_NAME), selected(get_option('b5-file-manager-default-sort-by', 'original'), 'title', false), __("Title", B5FILEMANAGER_PLUGIN_NAME), selected(get_option('b5-file-manager-default-sort-by', 'original'), 'weight', false), __("Weight", B5FILEMANAGER_PLUGIN_NAME), selected(get_option('b5-file-manager-default-sort-by', 'original'), 'type', false), __("Type", B5FILEMANAGER_PLUGIN_NAME), $extra_fields_options_html) : '',
            get_option('b5-file-manager-show-sort-controls', 'on') == 'on' ? sprintf('<div class="b5-sort-selector"><div class="b5-item-sort b5-selected %s"></div></div>', get_option('b5-file-manager-default-sort', 'asc')) : '',
            get_option('b5-file-manager-show-view-selector', 'on') == 'on' ? sprintf('<div class="b5-view-selector"><div class="b5-list-view %s"></div><div class="b5-grid-view %s"></div></div>', get_option('b5-file-manager-default-view', 'grid') == 'list' ? 'b5-selected' : '', get_option('b5-file-manager-default-view', 'grid') == 'grid' ? 'b5-selected' : '') : ''
        );

        $title = '
                <ul class="b5-breadcrumbs">
                    <li>
                        <a class="b5-folder-name b5-last" href="#">%s</a>
                    </li>
                </ul>
                 <div style="clear:both"></div>
             </div>%s<ul class="b5-file-manager-container %s">';

        $html .= sprintf(
            $title,
            trim($folder->post_title) ? $folder->post_title : __("(no title)", B5FILEMANAGER_PLUGIN_NAME),
            get_option('b5-file-manager-show-spinner', 'on') == 'on' ? '<div class="b5-loading"></div>' : '',
            implode(' ', $classes)
        );

        $html .= b5_file_manager_show_folder_html($id_folder);
        $folder_information = get_option('b5-file-manager-show-folder-information', 'on') == 'on' ? sprintf('<div class="b5-file-manager-information">%s</div>', b5_file_manager_folder_information_html($id_folder)) : '';

        $html .= '</ul>'.$folder_information.'</div>';

        return $html;
    }

    add_action('wp_head', 'b5_file_manager_script_to_header');
    function b5_file_manager_script_to_header() { ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                var ajax_url = '<?php echo admin_url('admin-ajax.php');?>',
                    default_view = '<?php echo get_option('b5-file-manager-default-view', 'grid') == 'grid' ? 'masonry' : 'vertical'?>',
                    default_sort = '<?php echo get_option('b5-file-manager-default-sort', 'asc');?>';

                $('.b5-file-manager-folder').each(function() {
                    var $folder = $(this),
                        $foldersContainer = $('ul.b5-file-manager', $folder),
                        $folderInformation = $('.b5-file-manager-information', $folder),
                        $foldersBreadcrumbs = $('ul.b5-breadcrumbs', $folder),
                        sort_by = $('.b5-sort-by-id', $folder),
                        sort_ascending = $('.b5-sort-selector .b5-item-sort', $folder),
                        $b5_file_manager_container = create_isotope_instance($folder, default_view),
                        filterFns = {
                            title: function() {
                                var item_title = $(this).find('.b5-item-data').attr('data-title');
                                return item_title.match(new RegExp($('.b5-filter-value', $folder).val().replace(/[\(\)]/g, '').toLowerCase()));
                            }
                        };

                    $b5_file_manager_container.isotope({sortBy: ['identifier', sort_by ? sort_by.val() : 'title'], filter: filterFns['title'],sortAscending: sort_ascending.attr('class') ? sort_ascending.hasClass('asc') : default_sort == 'asc' ? true : false});

                    $('.b5-file-manager-container, .b5-breadcrumbs', $folder).on('click', '.b5-folder-name', function() {
                        if(!$(this).hasClass('b5-last')) {

                            var $folderButton = $(this),
                                loading = $('.b5-loading', $folder);

                            if(loading) {
                                $foldersContainer.fadeTo("fast", 0.20, function() {
                                    loading.fadeIn(300);
                                });
                            }

                            var data = {
                                action: 'update_tree',
                                post_id: $('.b5-fm-post-id', $folder).val(),
                                folder_id: $folderButton.data('folder_id')
                            }

                            $.post(ajax_url, data, function(response) {
                                if(response.success) {
                                    $('li', $foldersContainer).remove();
                                    $('li', $foldersBreadcrumbs).remove();
                                    $foldersContainer.html(response.data.folder_tree);
                                    $foldersBreadcrumbs.html(response.data.breadcrumbs);
                                    $folderInformation.html(response.data.folder_information);
                                    $b5_file_manager_container.isotope('destroy');
                                    $b5_file_manager_container = create_isotope_instance($folder, default_view);
                                } else {
                                    alert(response.data);
                                }

                                if(loading) {
                                    $foldersContainer.fadeTo("fast", 1, function() {
                                        loading.fadeOut(300);
                                    });
                                }

                                var filterFns = {
                                    title: function() {
                                        var item_title = $(this).find('.b5-item-data').attr('data-title');
                                        return item_title.match(new RegExp($('.b5-filter-value', $folder).val().replace(/[\(\)]/g, '').toLowerCase()));
                                    }
                                };

                                $b5_file_manager_container.isotope({sortBy: ['identifier', sort_by ? sort_by.val() : 'title'], filter: filterFns['title'], sortAscending: sort_ascending.attr('class') ? sort_ascending.hasClass('asc') : default_sort == 'asc' ? true : false});
                            });
                        }
                        return false;
                    });

                    $('.b5-sort-by-id', $folder).on('change', function() {
                        var sort= $(this),
                            ascending = $('.b5-sort-selector .b5-item-sort', $folder),
                            filterFns = {
                                title: function() {
                                    var item_title = $(this).find('.b5-item-data').attr('data-title');
                                    return item_title.match(new RegExp($('.b5-filter-value', $folder).val().replace(/[\(\)]/g, '').toLowerCase()));
                                }
                            };

                        $b5_file_manager_container.isotope({sortBy: ['identifier', sort ? sort.val() : 'title'], filter: filterFns['title'], sortAscending: ascending.attr('class') ? ascending.hasClass('asc') : default_sort == 'asc' ? true : false});
                    });

                    $('.b5-sort-selector .b5-item-sort', $folder).on('click', function() {
                        var sort = $('.b5-sort-by-id', $folder),
                            ascending = $('.b5-sort-selector .b5-item-sort', $folder),
                            filterFns = {
                                title: function() {
                                    var item_title = $(this).find('.b5-item-data').attr('data-title');
                                    return item_title.match(new RegExp($('.b5-filter-value', $folder).val().replace(/[\(\)]/g, '').toLowerCase()));
                                }
                            };
                        if($(this).hasClass('asc')) {
                            $(this).removeClass('asc').addClass('desc');
                            $b5_file_manager_container.isotope({ sortBy: ['identifier', sort ? sort.val() : 'title'], filter: filterFns['title'], sortAscending: ascending.attr('class') ? ascending.hasClass('asc') : default_sort == 'asc' ? true : false});
                        } else if($(this).hasClass('desc')) {
                            $(this).removeClass('desc').addClass('asc');
                            $b5_file_manager_container.isotope({ sortBy: ['identifier', sort ? sort.val() : 'title'], filter: filterFns['title'], sortAscending: ascending.attr('class') ? ascending.hasClass('asc') : default_sort == 'asc' ? true : false});
                        }
                    });

                    $('.b5-view-selector .b5-list-view', $folder).on('click', function() {
                        if(!$(this).hasClass('b5-selected')) {
                            $('.b5-view-selector .b5-grid-view', $folder).removeClass('b5-selected');
                            $(this).addClass('b5-selected');
                            $('.b5-file-manager-container', $folder).removeClass('b5-grid').addClass('b5-list');

                            var sort = $('.b5-sort-by-id', $folder),
                                ascending = $('.b5-sort-selector .b5-item-sort', $folder),
                                filterFns = {
                                    title: function() {
                                        var item_title = $(this).find('.b5-item-data').attr('data-title');
                                        return item_title.match(new RegExp($('.b5-filter-value', $folder).val().replace(/[\(\)]/g, '').toLowerCase()));
                                    }
                                };
                            $b5_file_manager_container.isotope({ layoutMode: 'vertical', filter: filterFns['title'], sortBy: ['identifier', sort ? sort.val() : 'title'], sortAscending: ascending.attr('class') ? ascending.hasClass('asc') : default_sort == 'asc' ? true : false});
                        }
                    });

                    $('.b5-view-selector .b5-grid-view', $folder).on('click', function() {
                        if(!$(this).hasClass('b5-selected')) {
                            $('.b5-view-selector .b5-list-view', $folder).removeClass('b5-selected');
                            $(this).addClass('b5-selected');
                            $('.b5-file-manager-container', $folder).removeClass('b5-list').addClass('b5-grid');

                            var sort = $('.b5-sort-by-id', $folder),
                                ascending = $('.b5-sort-selector .b5-item-sort', $folder),
                                filterFns = {
                                    title: function() {
                                        var item_title = $(this).find('.b5-item-data').attr('data-title');
                                        return item_title.match(new RegExp($('.b5-filter-value', $folder).val().replace(/[\(\)]/g, '').toLowerCase()));
                                    }
                                };
                            $b5_file_manager_container.isotope({ layoutMode: 'masonry', filter: filterFns['title'], sortBy: ['identifier', sort ? sort.val() : 'title'], sortAscending: ascending.attr('class') ? ascending.hasClass('asc') : default_sort == 'asc' ? true : false});
                        }
                    });

                    $('.b5-filter-value', $folder).on('keyup', function() {
                        var $this = $(this);
                        $b5_file_manager_container.isotope({
                            filter: function() {
                                var item_title = $(this).find('.b5-item-data', $folder).attr('data-title');
                                return item_title.replace(/[\(\)]/g, '').match(new RegExp($this.val().replace(/[\(\)]/g, '').toLowerCase()));
                            }
                        });
                    });

                    $('.b5-file-manager-container', $folder).on('click', '.b5-external-file', function() {
                        var $downloadButton = $(this),
                            data = {
                                action: 'external_download',
                                file_id: $downloadButton.data('field_id'),
                                folder_id: $downloadButton.data('folder_id')
                            };

                        $.post(ajax_url, data);
                        return true;
                    });
                });

                function create_isotope_instance(folder, default_view) {
                    var $isotope_container = $('.b5-file-manager-container', folder).isotope({
                        layoutMode: default_view,
                        itemSelector: '.b5-item',
                        getSortData: {
                            title: function(itemElem) { // function
                                var item_title = $(itemElem).find('.b5-item-data', folder).attr('data-title');
                                return item_title.replace(/[\(\)]/g, '');
                            },
                            weight: function(itemElem) {
                                var item_weight = $(itemElem).find('.b5-item-data', folder).attr('data-weight');
                                return parseInt(item_weight);
                            },
                            date: function(itemElem) {
                                var item_date = $(itemElem).find('.b5-item-data', folder).attr('data-date');
                                return parseInt(item_date);
                            },
                            type: function(itemElem) {
                                var item_type = $(itemElem).find('.b5-item-data', folder).attr('data-type');
                                return item_type;
                            },
                            identifier: function(itemElem) {
                                var item_identifier = $(itemElem).find('.b5-item-data', folder).attr('data-identifier');
                                return item_identifier;
                            },
                            <?php
                                $extra_fields = get_option('b5-file-manager-extra-fields', array());
                                foreach($extra_fields as $key => $extra_field_name) {
                                    echo str_replace("-", "_", $key).": function(itemElem) {".
                                    "var item_extra = $(itemElem).find('.b5-item-data', folder).attr('data-".$key."');".
                                    "return item_extra;".
                                    "},";
                                }
                            ?>
                            extra: function(itemElem) {
                                return "";
                            }/*,
                            filter: function(itemElem) {
                                var item_title = $(itemElem).find('.b5-item-data', folder).attr('data-title');
                                return item_title.match(new RegExp($('.b5-filter-value', folder).val()));
                            }*/
                        },
                        sortBy: ['identifier']
                    });

                    return $isotope_container;
                }
            });
        </script>
    <?php }