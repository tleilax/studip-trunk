/*jslint browser: true */
/*global jQuery, STUDIP, Cropper */
(function ($, STUDIP, Cropper) {
    'use strict';

    STUDIP.Avatar = {

        cropControls: function () {
            var container = $('div#avatar-preview'),
                dialog = container.closest('div[role="dialog"]');
            if (container.length > 0) {
                // Adjust maximal cropper container height to dialog dimensions.
                container.css('max-height', dialog.height() - 200);
                container.css('max-width', dialog.width() - 50);
            }
            $('#avatar-upload').on('change', function () {
                STUDIP.Avatar.readFile(this);
            });
        },

        readFile: function (input) {
            if (window.FileReader && input.files && input.files[0]) {
                var reader = new window.FileReader(),
                    cropper;

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
                $('label.avatar-upload').hide();
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
                window.alert("Sorry - your browser doesn't support the FileReader API");
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
