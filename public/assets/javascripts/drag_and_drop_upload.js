/* Drag and drop file upload */
STUDIP.DragAndDropUpload = {
    bind: function (form) {
        form = form || document;

        jQuery('input[type=file]', form).change(function () {
            jQuery(this).closest('form').submit();
        });

        // The drag event handling is seriously messed up
        // see http://www.quirksmode.org/blog/archives/2009/09/the_html5_drag.html
        jQuery(form).bind('dragover dragleave', function (event) {
            jQuery(this).toggleClass('hovered', event.type === 'dragover');
            return false;
        });
    }
};
jQuery(document).ready(function ($) {
    $('form.drag-and-drop').each(function () {
        STUDIP.DragAndDropUpload.bind(this);
    });
});
