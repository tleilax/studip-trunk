(function ($) {
    'use strict';

    STUDIP.Avatar = {

        cropControls: function() {
            $('#upload-avatar').on('change', function() { STUDIP.Avatar.readFile(this); });
        },

        readFile: function(input) {
            var $profilePicPreview = $('#avatar-preview').croppie({
                viewport: {
                    width: 250,
                    height: 250,
                    type: 'square'
                },
                boundary: {
                    width: 300,
                    height: 300
                },
                enableExif: true
            });

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (event) {
                    $profilePicPreview.croppie('bind', {
                        url: event.target.result
                    });
                };

                reader.readAsDataURL(input.files[0]);
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
