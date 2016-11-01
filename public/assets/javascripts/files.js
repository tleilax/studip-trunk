/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

STUDIP.Files = {
    openAddFilesWindow: function (folder_id) {
        if (jQuery('.files_source_selector').length > 0) {
            console.log(jQuery('.files_source_selector').html());
            STUDIP.Dialog.show(jQuery('.files_source_selector').html(), {
                title: 'Datei hinzufügen'.toLocaleString()
            });
        } else {
            STUDIP.Dialog.fromURL(STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/files/add_files_window/" + folder_id, {
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
            STUDIP.Dialog.fromURL(STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/files/upload_window", {
                title: 'Datei hochladen'.toLocaleString()
            });
        }

        //start upload
        jQuery(".documents[data-folder_id] tbody > tr.dragover").removeClass('dragover');
        if (files > 0) {
            jQuery(".uploadbar").css("background-size", "0% 100%");
            jQuery.ajax({
                'url': STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/file/upload/" + folder_id,
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
                    STUDIP.Files.reloadPage();
                },
                'complete': function () {
                    /*jQuery(textarea).removeClass("hovered");
                     writer.removeClass("uploading");
                     jQuery(textarea).next(".uploader").removeClass("uploading");*/
                }
            });
        }

    },

    reloadPage: function () {
        STUDIP.Dialog.close();
        location.reload();
    },

    getFolders: function () {
    	
    	var context =  $("#copymove-destination").find('select').first().val();
    	var range = null;
    	
    	if ($.inArray( context, [ "courses"] ) > -1) {    		
    		range = $("#copymove-range-course > div > input").first().val();
    	} else if ($.inArray( context, [ "institutes"] ) > -1) {
    		range = $("#copymove-range-inst > div > input").first().val();
    	} else if ($.inArray( context, [ "myfiles"] ) > -1) {
    		range = $("#copymove-range-user_id").val();
    	}

    	if (range != null) {
	    	$.post(STUDIP.URLHelper.getURL("dispatch.php/file/getFolders"), {"range": range}, function( data ) {
			    if (data) {
			    	$("#copymove-subfolder select").empty();
			    	$.each(data, function( index, value ) {
			    		$.each(value, function( label, folder_id ) {
			    			$("#copymove-subfolder select").append('<option value="' + folder_id + '">' + label + '</option>');
			    		});
		    		});
			    }
			}, "json").done(function(){
				$("#copymove-subfolder").show();
			});
    	}
        
    }
};

jQuery(function () {
	jQuery('.documents[data-folder_id]').tablesorter({
        textExtraction: function (node) {
            var $node = $(node);
            return String($node.data('timestamp') || $node.text()).trim();
        },
        cssAsc: 'sortasc',
        cssDesc: 'sortdesc',
        sortList: [[2, 0]]
    });

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
    
    $(document).on('change', '#copymove-destination', function (event) {
    	
    	$("#copymove-range-course").hide();
		$("#copymove-range-inst").hide();
		$("#copymove-subfolder").hide();
		$("#copymove-subfolder select").empty();
		
    	var elem = jQuery(this).find('select').first();
    	
    	if ($.inArray( elem.val(), [ "courses"] ) > -1) {   
    		$("#copymove-range-course").show();
    	} else if ($.inArray( elem.val(), [ "institutes" ] ) > -1) {    		
    		$("#copymove-range-inst").show();
    	} else if ($.inArray( elem.val(), [ "myfiles" ] ) > -1) {    		
    		$("#copymove-subfolder").show();
    		STUDIP.Files.getFolders();
    	}
    });
    
});
