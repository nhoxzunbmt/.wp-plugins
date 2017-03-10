(function() {
    tinymce.PluginManager.add('b5_file_manager_shortcodes' , function(editor) {
        editor.addButton('b5_file_manager_shortcodes', {
            type: 'listbox',
            text: 'File Manager',
            classes: 'widget btn menubtn listbox b5-file-manager',
            onselect: function() {
                editor.insertContent('[file_manager root_folder="'+ this.value()+'" /]');
            },
            values: b5_file_manager_root_folders
        });
    });
})();