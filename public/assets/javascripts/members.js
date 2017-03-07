/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

jQuery(document).on('click', 'a[rel~="comment_dialog"]', function (event) {
    var href      = jQuery(this).attr('href'),
        container = jQuery('<div/>');

    // Load response into a helper container, open dialog after loading
    // has finished.
    container.load(href, function (response, status, xhr) {
        jQuery(this).dialog({
            title:      decodeURIComponent(xhr.getResponseHeader('X-Title')) || '',
            width:      '40em',
            modal:      true,
            resizable:  false
        });
    });

    event.preventDefault();
});

jQuery(function() {
    jQuery('a.get-course-members').on('click', function() {
        var dataEl = jQuery('article#course-members-' + jQuery(this).data('course-id'));
        if (jQuery.trim(dataEl.html()) == '') {
            $.ajax({
                url: jQuery(this).data('get-members-url'),
                dataType: 'html',
                beforeSend: function (xhr, settings) {
                    dataEl.html($('<img>').attr('width', 32).attr('height', 32).attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg'));
                },
                success: function (html) {
                    dataEl.html(html);
                }
            });
        }
    });
});
