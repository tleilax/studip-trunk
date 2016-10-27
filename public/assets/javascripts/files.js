/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

STUDIP.Files = {
    upload: function (filelist) {
        console.log(filelist);
        var files = 0;
        var folder_id = jQuery("table.documents").data("folder_id");
        console.log(folder_id);

        //Open upload-dialog

        //start upload
        var data = new FormData();
        jQuery.each(filelist, function (index, file) {
            if (file.size > 0) {
                data.append(index, file);
                files += 1;
            }
        });
        if (files > 0) {
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
                            console.log(percent);
                            //Set progress
                            //jQuery(writer).css("background-size", percent + "% 5px");
                        }, false);
                    }
                    return xhr;
                },
                'success': function (json) {
                    /*if (typeof json.inserts === "object") {
                     jQuery.each(json.inserts, function (index, text) {
                     jQuery(textarea).val(jQuery(textarea).val() + " " + text);
                     });
                     }
                     if (typeof json.errors === "object") {
                     alert(json.errors.join("\n"));
                     } else if (typeof json.inserts !== "object") {
                     alert("Fehler beim Dateiupload.");
                     }
                     jQuery(textarea).trigger("keydown");*/
                },
                'complete': function () {
                    /*jQuery(textarea).removeClass("hovered");
                     writer.removeClass("uploading");
                     jQuery(textarea).next(".uploader").removeClass("uploading");*/
                }
            });
        }

    }
};