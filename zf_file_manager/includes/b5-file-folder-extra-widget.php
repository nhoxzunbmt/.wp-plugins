<?php

    function b5_file_manager_folder_extra_init() {
        register_widget('B5_Folder_Extra_Widget');
    }
    add_action('widgets_init', 'b5_file_manager_folder_extra_init');

    class B5_Folder_Extra_Widget extends WP_Widget {

        function B5_Folder_Extra_Widget() {
            $widget_ops = array(
                'classname' => 'b5_folder_extra_widget',
                'description' => __("Show folder content", B5FILEMANAGER_PLUGIN_NAME)
            );
            $this->__construct('b5-file-folder', __('File Manager Folder', B5FILEMANAGER_PLUGIN_NAME), $widget_ops);
            $this->alt_option_name = 'b5_folder_extra_widget';

            add_action('save_post', array(&$this, 'flush_widget_cache'));
            add_action('deleted_post', array(&$this, 'flush_widget_cache'));
            add_action('switch_theme', array(&$this, 'flush_widget_cache'));
        }

        function widget($args, $instance) {
            $cache = wp_cache_get('b5_folder_extra_widget', 'widget');

            if(!is_array($cache))
                $cache = array();

            if(isset($cache[$args['widget_id']])) {
                echo $cache[$args['widget_id']];
                return;
            }

            ob_start();
            extract($args);

            $title = apply_filters('widget_title', empty($instance['title']) ? __('No title', B5FILEMANAGER_PLUGIN_NAME) : $instance['title']);
            $show_title = isset($instance['show_title']) ? $instance['show_title'] : true;

            if(!isset($instance['folder_id']) || !$folder = (int) $instance['folder_id']) {
                $folder = 0;
            }

            echo $before_widget;
            if($title && $show_title) {
                echo $before_title . $title . $after_title;
            }

            echo do_shortcode('[file_manager root_folder="'.$folder.'" /]');

            echo $after_widget;
            wp_reset_query();

            $cache[$args['widget_id']] = ob_get_flush();
            wp_cache_add('b5_folder_extra_widget', $cache, 'widget');
        }

        function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['folder_id'] = (int) $new_instance['folder_id'];
            $instance['show_title'] = isset($new_instance['show_title']) ? (bool) $new_instance['show_title'] : false;
            $this->flush_widget_cache();

            $alloptions = wp_cache_get('alloptions', 'options');
            if(isset($alloptions['b5_folder_extra_widget'])) {
                delete_option('b5_folder_extra_widget');
            }

            return $instance;
        }

        function flush_widget_cache() {
            wp_cache_delete('b5_folder_extra_widget', 'widget');
        }

        function form($instance) {
            $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
            $show_title = isset($instance['show_title']) ? (bool) $instance['show_title'] : true;

            if(!isset($instance['folder_id']) || !$folder = (int) $instance['folder_id']) {
                $folder = 0;
            }?>

            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', B5FILEMANAGER_PLUGIN_NAME); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

            <?php
                $args = array(
                    'post_parent'=>'0',
                    'numberposts'=>-1,
                    'post_type'=>'folder');

                $post_array = get_posts($args); ?>

                <p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Folder:', B5FILEMANAGER_PLUGIN_NAME); ?></label>
                <select class="widefat" name="<?php echo $this->get_field_name('folder_id'); ?>" id="<?php echo $this->get_field_id('folder_id'); ?>">
                    <?php foreach($post_array as $post_folder): ?>
                        <option <?php selected($post_folder->ID == $folder)?> value="<?php echo $post_folder->ID; ?>"><?php echo get_the_title($post_folder->ID); ?></option>
                    <?php endforeach; ?>
                </select></p>
               <?php wp_reset_query();
            ?>

            <p><input class="checkbox" <?php checked($show_title); ?> id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox">
            <label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show widget title?', B5FILEMANAGER_PLUGIN_NAME); ?></label></p>
        <?php
        }
    }