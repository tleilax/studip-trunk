/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

STUDIP.Files = {
    upload: function (filelist) {
        var files = 0;
        var folder_id = jQuery("table.documents").data("folder_id");
        console.log(folder_id);
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
        STUDIP.Dialog.show(jQuery(".file_uploader").html(), {
            title: "Datei hochladen"
        });

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
                    console.log(json);
                    STUDIP.Dialog.close();
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
});