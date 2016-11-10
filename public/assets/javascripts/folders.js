


STUDIP.Folders = {
    
    openAddFoldersWindow: function(folder_id, range_id) {
        STUDIP.Dialog.fromURL(
            STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/folder/new?rangeId=' + range_id + '&parent_folder_id=' + folder_id + '&js=1',
            {
                title: 'Datei hinzufügen'.toLocaleString()
            }
        );
    },
    
    
    sendNewFolderForm: function() {
        var new_folder_form = jQuery('#new_folder_form');
        
        //get folder attributes:
        
        var folder_name = jQuery(new_folder_form).find('input[name="name"]').val();
        var folder_description = jQuery(new_folder_form).find('input[name="description"]').val();
        var folder_type = jQuery(new_folder_form).find('input[name="folder_type"]').val();
        
        if(folder_name && folder_type) {
            var form_data = new FormData();
            //all necessary parameters are set
            new_folder_form.append('name', folder_name);
            new_folder_form.append('description', folder_description);
            new_folder_form.append('folder_type', folder_type);
            new_folder_form.append('js', '1');
            
            jQuery.ajax({
                'url': STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/folder/new',
                'data': form_data,
                'cache': false,
                'type': 'POST'
            });
            
        }
    },
    
    /*
     * 'success': function(result) {
                    STUDIP.Folder.updateFolderListEntry(result.folder_id, result.tr);
                }
     * */
    
    updateFolderListEntry: function(html, delay) {
        //updates the folder entry in the folder list
        var documents_table = jQuery('.documents[data-folder_id]');
        
        if(jQuery('#' + folder_id).length > 0) {
            jQuery('#' + folder_id).replaceWith(html);
        } else {
            jQuery(documents_table).append(html);
        }
        
    },
    
    showFolderHtml: function(folder_html) {
        //this is executed when the html code in the AJAX response
        //shall be put onto the page
    }
};
