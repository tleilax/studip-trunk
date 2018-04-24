(function ($) {
    'use strict';

    STUDIP.Avatar = {

        cropControls: function() {
            $('#avatar-upload').on('change', function() { STUDIP.Avatar.readFile(this); });
        },

        readFile: function(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                var cropper;

                reader.onload = function (event) {
                    $('#new-avatar').attr('src', event.target.result);
                    var image = document.getElementById('new-avatar');
                    cropper = new Cropper(image, {
                        aspectRatio: 1 / 1,
                        viewMode: 2
                    });
                };

                reader.readAsDataURL(input.files[0]);

                $('#avatar-buttons').removeClass('hidden-js');
                $('label.avatar-upload').hide();
                $('#avatar-zoom-in').on('click', function() {
                    cropper.zoom(0.1);
                    return false;
                });
                $('#avatar-zoom-out').on('click', function() {
                    cropper.zoom(-0.1);
                    return false;
                });
                $('#avatar-rotate-clockwise').on('click', function() {
                    cropper.rotate(90);
                    return false;
                });
                $('#avatar-rotate-counter-clockwise').on('click', function() {
                    cropper.rotate(-90);
                    return false;
                });

                $('#submit-avatar').on('click', function(event) {
                    $('#cropped-image').attr('value', cropper.getCroppedCanvas().toDataURL());
                });
            } else {
                alert('Sorry - your browser doesn\'t support the FileReader API');
            }
        }
    };

}(jQuery));

jQuery(function () {
    $(document).on('dialog-open', function() {
        STUDIP.Avatar.cropControls();
    });
});
