/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

STUDIP.Files = {
    openAddFilesWindow: function (folder_id) {
        if (jQuery('.files_source_selector').length > 0) {
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
                    if (json.redirect) {
                        STUDIP.Dialog.fromURL(json.redirect, {
                            title: 'Lizenz auswählen'.toLocaleString()
                        });
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
        window.setTimeout(STUDIP.Dialog.close, 20);
        var tr;
        if ((typeof html !== "array") && (typeof html !== "object")) {
            html = [html];
        }
        for (var i in html) {
            tr = jQuery(html[i]);
            if (jQuery("#" + tr.attr("id")).length > 0) {
                jQuery("#" + tr.attr("id")).replaceWith(tr);
            } else {
                jQuery(".documents[data-folder_id] tbody.files").append(html[i]);
                tr.hide().delay(delay + i * 200).fadeIn(300);
            }
        }
    },
    removeFile: function (fileref_id) {
        console.log(fileref_id);
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/file/delete/" + fileref_id,
            type: "post",
            success: function () {
                console.log(jQuery(".documents tbody.files > tr#fileref_" + fileref_id));
                jQuery(".documents tbody.files > tr#fileref_" + fileref_id).fadeOut(300, function () {
                    jQuery(this).remove();
                });
            }
        });
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
/*	jQuery('.documents[data-folder_id]').tablesorter({
        textExtraction: function (node) {
            var $node = $(node);
            return String($node.data('timestamp') || $node.text()).trim();
        },
        cssAsc: 'sortasc',
        cssDesc: 'sortdesc',
        sortList: [[2, 0]],
        // initialize zebra striping and filter widgets
        widgets: ["filter"],

        // headers: { 5: { sorter: false, filter: false } },

        widgetOptions : {

          // extra css class applied to the table row containing the filters & the inputs within that row
          filter_cssFilter   : '',

          filter_columnFilters: false,
          filter_saveFilters : true,
          filter_reset: '.reset'
          // If there are child rows in the table (rows with class name from "cssChildRow" option)
          // and this option is true and a match is found anywhere in the child row, then it will make that row
          // visible; default is false
          filter_childRows   : false,

          // if true, filters are collapsed initially, but can be revealed by hovering over the grey bar immediately
          // below the header row. Additionally, tabbing through the document will open the filter row when an input gets focus
          filter_hideFilters : false,

          // Set this option to false to make the searches case sensitive
          filter_ignoreCase  : true,

          // jQuery selector string of an element used to reset the filters
          filter_reset : '.reset',

          // Use the $.tablesorter.storage utility to save the most recent filters
          filter_saveFilters : true,

          // Delay in milliseconds before the filter widget starts searching; This option prevents searching for
          // every character while typing and should make searching large tables faster.
          filter_searchDelay : 300,

          // Set this option to true to use the filter to find text from the start of the column
          // So typing in "a" will find "albert" but not "frank", both have a's; default is false
          filter_startsWith  : false,

        }
    });
/**/
  var $table = $('table').tablesorter({
    theme: 'blue',
    widgets: ["filter"],
    widgetOptions : {
      // use the filter_external option OR use bindSearch function (below)
      // to bind external filters.
      // filter_external : '.search',

      filter_columnFilters: false,
      filter_saveFilters : true,
      filter_reset: '.reset',
          // Set this option to false to make the searches case sensitive
          filter_ignoreCase  : true,
          // Set this option to true to use the filter to find text from the start of the column
          // So typing in "a" will find "albert" but not "frank", both have a's; default is false
          filter_startsWith  : false
    }
  });

  // Target the $('.search') input using built in functioning
  // this binds to the search using "search" and "keyup"
  // Allows using filter_liveSearch or delayed search &
  // pressing escape to cancel the search
  $.tablesorter.filter.bindSearch( $table, $('.tablesorterfilter') );

  // Basic search binding, alternate to the above
  // bind to search - pressing enter and clicking on "x" to clear (Webkit)
  // keyup allows dynamic searching
  /*
  $(".search").bind('search keyup', function (e) {
    $('table').trigger('search', [ [this.value] ]);
  });
  */

  // Allow changing an input from one column (any column) to another
  $('select').change(function(){
    // modify the search input data-column value (swap "0" or "all in this demo)
    $('.selectable').attr( 'data-column', $(this).val() );
    // update external search inputs
    $.tablesorter.filter.bindSearch( $table, $('.tablesorterfilter'), false );
  });
jQuery.tablesorter.filter.bindSearch( $table, $('.tablesorterfilter') );

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
