STUDIP.GlobalSearch = {

    toggle: function() {
        var input = jQuery('#globalsearch-input');
        if (input.hasClass('hidden-js')) {
            input.removeClass('hidden-js');
            setTimeout(function() {
                input.focus();
                jQuery('#globalsearch-list').removeClass('hidden-js');
            }, 500);
        } else {
            jQuery('#globalsearch-list').addClass('hidden-js');
            input.addClass('hidden-js');
        }
        return false;
    }

};

jQuery(function () {
    // Handle search icon click.
    jQuery('#globalsearch-icon').on('click', function() {
        STUDIP.GlobalSearch.toggle();
    });
    // Close search on click on page.
    jQuery('div#flex-header, div#layout_page, div#layout_footer').on('click', function() {
        STUDIP.GlobalSearch.toggle();
    });
    // Show/hide hints on click.
    jQuery('#globalsearch-togglehints').on('click', function() {
        var toggle = jQuery('#globalsearch-togglehints');
        var currentText = toggle.html();
        toggle.html(toggle.data('toggle-text'));
        toggle.data('toggle-text', currentText);
        var hints = jQuery('#globalsearch-hints');
        hints.toggleClass('hidden-js');
    });
    // Bind search to STRG + Space.
    $(window).keydown(function (e) {
        // ctrl + space
        if (e.which === 32 && e.ctrlKey && !e.altKey && !e.metaKey && !e.shiftKey) {
            e.preventDefault();
            STUDIP.GlobalSearch.toggle();
        }
    });

});
