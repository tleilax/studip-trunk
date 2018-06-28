/*global jQuery, STUDIP */
STUDIP.Avatar = {

    image: '',

    readFile: function (input) {
        if (window.FileReader && input.files && input.files[0]) {
            var reader = new window.FileReader(),
                cropper;

            if (input.files[0].size <= jQuery(input).data('max-size')) {

                var container = jQuery('div#avatar-preview'),
                    dialog = container.closest('div[role="dialog"]');
                // We are in a modal dialog
                if (dialog.length > 0) {
                    // Adjust maximal cropper container height to dialog dimensions.
                    container.css('height', dialog.height() - 200);
                    container.css('width', dialog.width() - 220);
                    container.css('max-height', dialog.height() - 200);
                    container.css('max-width', dialog.width() - 220);
                    // No dialog, full page.
                } else {
                    dialog = jQuery('#layout_content');
                    // Adjust maximal cropper container height to page dimensions.
                    container.css('height', dialog.height() - 220);
                    container.css('width', 0.95 * dialog.width());
                    container.css('max-height',  dialog.height() * 220);
                    container.css('max-width', 0.95 * dialog.width());
                }

                reader.onload = function (event) {

                    STUDIP.Avatar.image = jQuery('#new-avatar');
                    STUDIP.Avatar.image.attr('src', event.target.result);
                    STUDIP.Avatar.image.cropper({
                        aspectRatio: 1, // 1 / 1,
                        viewMode: 2
                    });

                    cropper = STUDIP.Avatar.image.data('cropper');
                };

                reader.readAsDataURL(input.files[0]);

                jQuery('#avatar-buttons').removeClass('hidden-js');
                jQuery('label.file-upload').hide();
                jQuery('#avatar-zoom-in').on('click', function () {
                    cropper.zoom(0.1);
                    return false;
                });
                jQuery('#avatar-zoom-out').on('click', function () {
                    cropper.zoom(-0.1);
                    return false;
                });
                jQuery('#avatar-rotate-clockwise').on('click', function () {
                    cropper.rotate(90);
                    return false;
                });
                jQuery('#avatar-rotate-counter-clockwise').on('click', function () {
                    cropper.rotate(-90);
                    return false;
                });

                jQuery('#submit-avatar').on('click', function () {
                    jQuery('#cropped-image').attr('value', STUDIP.Avatar.image.cropper('getCroppedCanvas').toDataURL());
                });

            } else {
                alert(jQuery(input).data('message-too-large'));
            }

        } else {
            alert("Sorry - your browser doesn't support the FileReader API");
        }
    },

    checkImageSize: function () {
        var data = STUDIP.Avatar.image.cropper('getData');

        // Show a warning if cropped area is smaller than 250x250px.
        if (data.width < 250 || data.height < 250) {
            return confirm(jQuery('#new-avatar').data('message-too-small'));
        }
        return true;
    }

};
/*jslint browser: true */
(function ($, STUDIP) {

    'use strict';

    jQuery(function () {
        jQuery(document).on('dialog-update', function () {
            jQuery('#avatar-upload').on('change', function () {
                STUDIP.Avatar.readFile(this);
            });
            jQuery('form.settings-avatar').on('submit', function(event) {
                return STUDIP.Avatar.checkImageSize(event);
            });
        });
        jQuery('#avatar-upload').on('change', function () {
            STUDIP.Avatar.readFile(this);
        });
        jQuery('form.settings-avatar').on('submit', function(event) {
            return STUDIP.Avatar.checkImageSize(event);
        });
    });

}(jQuery, STUDIP));
