

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
        var folder_type = jQuery(new_folder_form).find('select[name="folder_type"]').val();
        var parent_folder_id = jQuery(new_folder_form).find('input[name="parent_folder_id"]').val();
        
        if(folder_name && folder_type && parent_folder_id) {
            
            jQuery.ajax({
                method: 'POST',
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/folder/new',
                data: new_folder_form.serialize(),
                cache: false,
                success: function(data) {
                    STUDIP.Folders.updateFolderListEntry(data.folder_id, data.tr);
                    STUDIP.Dialog.close();
                }
            });
            
        }
    },
    
    
    updateFolderListEntry: function(folder_id, html, delay) {
        //updates the folder entry in the folder list
        var documents_table = jQuery('.documents[data-folder_id]');
        
        if(jQuery('#' + folder_id).length > 0) {
            jQuery('#' + folder_id).replaceWith(html);
        } else {
            jQuery(documents_table).append(html);
        }
        
    }
};
