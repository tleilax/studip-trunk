/*global jQuery, STUDIP */
STUDIP.Avatar = {

    image: '',

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

                    image = $('#new-avatar');
                    image.attr('src', event.target.result);
                    image.cropper({
                        aspectRatio: 1, // 1 / 1,
                        viewMode: 2
                    });

                    cropper = image.data('cropper');
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
                    $('#cropped-image').attr('value', image.cropper('getCroppedCanvas').toDataURL());
                });

            } else {
                alert($(input).data('message-too-large'));
            }

        } else {
            alert("Sorry - your browser doesn't support the FileReader API");
        }
    },

    checkImageSize: function () {
        var data = image.cropper('getData');

        // Show a warning if cropped area is smaller than 250x250px.
        if (data.width < 250 || data.height < 250) {
            return confirm('The image is too small. Do you really want to continue?');
        }
        return true;
    }

};
/*jslint browser: true */
(function ($, STUDIP) {

    'use strict';

    $(function () {
        $(document).on('dialog-update', function () {
            $('#avatar-upload').on('change', function () {
                console.log('File changed in dialog.');
                STUDIP.Avatar.readFile(this);
            });
            $('form.settings-avatar').on('submit', function(event) {
                return STUDIP.Avatar.checkImageSize(event);
            });
        });
        $('#avatar-upload').on('change', function () {
            console.log('File changed.');
            STUDIP.Avatar.readFile(this);
        });
        $('form.settings-avatar').on('submit', function(event) {
            return STUDIP.Avatar.checkImageSize(event);
        });
    });

}(jQuery, STUDIP));
