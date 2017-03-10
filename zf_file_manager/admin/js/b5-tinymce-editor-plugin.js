(function() {
    tinymce.create('tinymce.plugins.b5_file_manager_shortcodes', {

        init : function(ed, url) {
        },
        createControl : function(n, cm) {
            if(n=='b5_file_manager_shortcodes') {
                var mlb = cm.createListBox('b5_file_manager_shortcodes', {
                    title : 'File Manager',
                    onselect : function(v) {
                        if(tinyMCE.activeEditor.selection.getContent() == '' && v != '') {
                            tinyMCE.activeEditor.selection.setContent('[file_manager root_folder="'+v+'" /]')
                        }
                    }
                });

                for(var i in b5_file_manager_root_folders) {
                    mlb.add(b5_file_manager_root_folders[i][1], b5_file_manager_root_folders[i][0]);
                }

                return mlb;
            }
            return null;
        }
    });
    tinymce.PluginManager.add('b5_file_manager_shortcodes', tinymce.plugins.b5_file_manager_shortcodes);
})();