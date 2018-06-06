/*jslint browser: true */
/*global jQuery, STUDIP, Cropper */
(function ($, STUDIP, Cropper) {
    'use strict';

    STUDIP.Avatar = {

        cropControls: function () {
            $('#avatar-upload').on('change', function () {
                STUDIP.Avatar.readFile(this);
            });
        },

        readFile: function (input) {
            if (window.FileReader && input.files && input.files[0]) {
                var reader = new window.FileReader(),
                    cropper;

                if (input.files[0].size <= $(input).data('max-size')) {

                    var container = $('div#avatar-preview'),
                        dialog = container.closest('div[role="dialog"]');
                    // We are in a modal dialog
                    if (dialog.length > 0) {
                        // Adjust maximal cropper container height to dialog dimensions.
                        container.css('height', dialog.height() - 200);
                        container.css('width', dialog.width() - 100);
                        container.css('max-height', dialog.height() - 200);
                        container.css('max-width', dialog.width() - 100);
                    // No dialog, full page.
                    } else {
                        dialog = $('#layout_content');
                        // Adjust maximal cropper container height to page dimensions.
                        container.css('height', 0.75 * dialog.height());
                        container.css('width', 0.75 * dialog.width());
                        container.css('max-height', 0.75 * dialog.height());
                        container.css('max-width', 0.75 * dialog.width());
                        // Adjust tool button positions.
                        $('#avatar-buttons').css('left', container.width() + 10);
                        $('#avatar-buttons').css('right', 0);
                    }

                    reader.onload = function (event) {

                        $('#new-avatar').attr('src', event.target.result);
                        var image = document.getElementById('new-avatar');
                        cropper = new Cropper(image, {
                            aspectRatio: 1, // 1 / 1,
                            viewMode: 2
                        });
                    };

                    reader.readAsDataURL(input.files[0]);

                    $('#avatar-buttons').removeClass('hidden-js');
                    $('label.file-upload').hide();
                    $('#avatar-zoom-in').on('click', function () {
                        cropper.zoom(0.1);
                        return false;
                    });
                    $('#avatar-zoom-out').on('click', function () {
                        cropper.zoom(-0.1);
                        return false;
                    });
                    $('#avatar-rotate-clockwise').on('click', function () {
                        cropper.rotate(90);
                        return false;
                    });
                    $('#avatar-rotate-counter-clockwise').on('click', function () {
                        cropper.rotate(-90);
                        return false;
                    });

                    $('#submit-avatar').on('click', function () {
                        $('#cropped-image').attr('value', cropper.getCroppedCanvas().toDataURL());
                    });

                } else {
                    alert($(input).data('message-too-large'));
                }

            } else {
                alert("Sorry - your browser doesn't support the FileReader API");
            }
        }
    };

    $(function () {
        $(document).on('dialog-update', function () {
            STUDIP.Avatar.cropControls();
        });
        STUDIP.Avatar.cropControls();
    });

}(jQuery, STUDIP, Cropper));
