/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

STUDIP.Files = {
    openAddFilesWindow: function (folder_id) {
        if (jQuery('.files_source_selector').length > 0) {
            STUDIP.Dialog.show(jQuery('.files_source_selector').html(), {
                title: 'Datei hinzufügen'.toLocaleString()
            });
        } else {
            STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL("dispatch.php/file/add_files_window/" + folder_id), {
                title: 'Datei hinzufügen'.toLocaleString()
            });
        }
    },
    upload: function (filelist) {
        var files = 0;
        var folder_id = jQuery(".files_source_selector").data("folder_id");
        var data = new FormData();

        //Open upload-dialog
        jQuery(".file_uploader .filenames").html("");
        jQuery.each(filelist, function (index, file) {
            if (file.size > 0) {
                data.append("file[]", file);
                jQuery(".file_uploader .filenames").append(jQuery("<li/>").text(file.name));
                files += 1;
            }
        });
        if (jQuery(".file_uploader").length > 0) {
            STUDIP.Dialog.show(jQuery(".file_uploader").html(), {
                title: "Datei hochladen".toLocaleString()
            });
        } else {
            STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL("dispatch.php/file/upload_window"), {
                title: 'Datei hochladen'.toLocaleString()
            });
        }

        //start upload
        jQuery(".documents[data-folder_id] tbody > tr.dragover").removeClass('dragover');
        if (files > 0) {
            jQuery(".uploadbar").css("background-size", "0% 100%");
            jQuery.ajax({
                'url': STUDIP.URLHelper.getURL("dispatch.php/file/upload/" + folder_id),
                'data': data,
                'cache': false,
                'contentType': false,
                'processData': false,
                'type': 'POST',
                'xhr': function () {
                    var xhr = jQuery.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function (event) {
                            var percent = 0;
                            var position = event.loaded || event.position;
                            var total = event.total;
                            if (event.lengthComputable) {
                                percent = Math.ceil(position / total * 100);
                            }
                            //Set progress
                            jQuery(".uploadbar").css("background-size", percent + "% 100%");
                        }, false);
                    }
                    return xhr;
                },
                'success': function (json) {
                    jQuery(".uploadbar").css("background-size", "100% 100%");
                    if (json.redirect) {
                        STUDIP.Dialog.fromURL(json.redirect, {
                            title: json.window_title || 'Lizenz auswählen'.toLocaleString()
                        });
                    } else if (json.message) {
                        jQuery(".uploadbar").hide().parent().append(json.message);
                    } else {
                        jQuery.each(json.new_html, function (index, tr) {
                            STUDIP.Files.addFile(tr, index * 200);
                        });
                        STUDIP.Dialog.close();
                    }
                },
                'complete': function () {
                    /*jQuery(textarea).removeClass("hovered");
                     writer.removeClass("uploading");
                     jQuery(textarea).next(".uploader").removeClass("uploading");*/
                }
            });
        }
    },
    addFile: function (html, delay) {
        if (typeof delay === "undefined") {
            delay = 0;
        }
        var redirect = true;
        if (typeof html.html !== "undefined") {
            redirect = html.redirect;
            html = html.html;
        }
        if (!redirect) {
            window.setTimeout(STUDIP.Dialog.close, 20);
        } else {
            STUDIP.Dialog.fromURL(redirect);
        }
        var tr;
        if ((typeof html !== "array") && (typeof html !== "object")) {
            html = [html];
        }
        for (var i in html) {
            tr = jQuery(html[i]);
            if (jQuery("#" + tr.attr("id")).length > 0) {
                jQuery("#" + tr.attr("id")).replaceWith(tr);
            } else {
                tr.hide().appendTo(".documents[data-folder_id] tbody.files").delay(500 + delay + i * 200).fadeIn(300);
            }
        }
        jQuery(".subfolders .empty").hide("fade");
        // update tablesorter cache
        jQuery('table.documents').trigger('update');    
        var $sort = jQuery('table.documents').get(0).config.sortList;
        jQuery("table.documents").trigger("sorton",[$sort]);
    },
    removeFile: function (fileref_id) {
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL("dispatch.php/file/delete/" + fileref_id),
            type: "post",
            success: function () {
                jQuery(".documents tbody.files > tr#fileref_" + fileref_id).fadeOut(300, function () {
                    jQuery(this).remove();
                    if (jQuery(".subfolders > *").length + jQuery(".files > *").length < 2) {
                        jQuery(".subfolders .empty").show("fade");
                    }
                });
            }
        });

    },
    reloadPage: function () {
        STUDIP.Dialog.close();
        location.reload();
    },

    getFolders: function(name) {

        var element_name = 'folder_select_'+name;

    	var context =  $('#'+element_name+'-destination').val();
    	var range = null;

    	if ($.inArray( context, [ "courses"] ) > -1) {
    		range = $('#'+element_name+'-range-course > div > input').first().val();
    	} else if ($.inArray( context, [ "institutes"] ) > -1) {
    		range = $('#'+element_name+'-range-inst > div > input').first().val();
    	} else if ($.inArray( context, [ "myfiles"] ) > -1) {
    		range = $('#'+element_name+'-range-user_id').val();
    	}

    	if (range != null) {
	    	$.post(STUDIP.URLHelper.getURL("dispatch.php/file/getFolders"), {"range": range}, function( data ) {
			    if (data) {
			    	$('#'+element_name+'-subfolder select').empty();
			    	$.each(data, function( index, value ) {
			    		$.each(value, function( label, folder_id ) {
			    			$('#'+element_name+'-subfolder select').append('<option value="' + folder_id + '">' + label + '</option>');
			    		});
		    		});
			    }
			}, "json").done(function(){
				$('#'+element_name+'-subfolder').show();
			});
    	}

    },

    changeFolderSource: function(name) {
        var element_name = 'folder_select_'+name;

        console.log(element_name);

        $('#'+element_name+'-range-course').hide();
        $('#'+element_name+'-range-inst').hide();
        $('#'+element_name+'-subfolder').hide();
        $('#'+element_name+'-subfolder select').empty();

        var elem = jQuery('#'+element_name+'-destination');

        if ($.inArray( elem.val(), [ "courses"] ) > -1) {
            $('#'+element_name+'-range-course').show();
        } else if ($.inArray( elem.val(), [ "institutes" ] ) > -1) {
            $('#'+element_name+'-range-inst').show();
        } else if ($.inArray( elem.val(), [ "myfiles" ] ) > -1) {
            $('#'+element_name+'-subfolder').show();
            STUDIP.Files.getFolders(name);
        }
    },
    
    toggleBulkButtons: function() {
        //At the bottom of each file list there are buttons for bulk actions.
        //These have to be activated when at least one element is checked.
        
        var buttons = jQuery('table.documents tfoot .button');

        //The bulk checkbox wasn't clicked: check each of the elements:
        var total_elements = jQuery('table.documents tbody tr[role=row] td input');
        var checked_elements = jQuery('table.documents tbody tr[role=row] td input:checked');

        if(checked_elements.length > 0) {
            //at least one element is checked: activate buttons
            jQuery(buttons).removeAttr('disabled');
            //...and set the "select-all-checkbox" in the third state (undefined),
            //if not all elements are checked:
            
            
            if(checked_elements.length < total_elements.length) {
                //not all elements checked
                jQuery('table.documents thead th input[data-proxyfor]').prop('indeterminate', true);
            } else {
                //all elements checked
                jQuery('table.documents thead th input[data-proxyfor]').prop('indeterminate', null);
                jQuery('table.documents thead th input[data-proxyfor]').prop('checked', true);
            }
            
        } else {
            //no element is checked: deactivate buttons
            jQuery(buttons).attr('disabled', 'disabled');
            //... and uncheck "select-all-checkbox"
            jQuery('table.documents thead th input[data-proxyfor]').prop('indeterminate', null);
            jQuery('table.documents thead th input[data-proxyfor]').prop('checked', false);
        }
    }
};

jQuery(function () {
    jQuery(".documents[data-folder_id] tbody > tr")
        .on('dragover dragleave', function (event) {
            jQuery(this).toggleClass('dragover', event.type === 'dragover');
            return false;
        });
    jQuery(".documents[data-folder_id]").on("drop", function (event) {
        event.preventDefault();
        var filelist = event.originalEvent.dataTransfer.files || {};
        STUDIP.Files.upload(filelist);
    });

    jQuery(document).on("change", "table.documents :checkbox", STUDIP.Files.toggleBulkButtons);

    // workaround to wait for tables.js to be executed first
    jQuery(function () {
        if (jQuery('table.documents').length > 0) {
            jQuery('table.documents').data('tablesorter').widgets = ['filter'];
            jQuery('table.documents').data('tablesorter').widgetOptions = {
                filter_columnFilters: false,
                filter_saveFilters: true,
                filter_reset: '.reset',
                filter_ignoreCase: true,
                filter_startsWith: false
            };
            jQuery('table.documents.flat').trigger('applyWidgets');
            jQuery.tablesorter.filter.bindSearch(jQuery('table.documents'), jQuery('.tablesorterfilter'));
        }
    });
});
