STUDIP.GlobalSearch = {

    // Toggles visibility of search input field and hints.
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
    },

    // Performs the actual search.
    doSearch: function() {
        var searchterm = jQuery('#globalsearch-input').val();
        if (jQuery('#globalsearch-input').val() != '') {
            var resultsDiv = jQuery('#globalsearch-results');
            jQuery.ajax(
                'find/' + searchterm,
                {
                    beforeSend: function(xhr, settings) {
                        resultsDiv.attr('align', 'center');
                        resultsDiv.html('');
                        resultsDiv.removeClass('hidden-js');
                        resultsDiv.append(
                            jQuery('<div>').
                                attr('id', 'globalsearch-loading-text').
                                html(resultsDiv.data('loading-text')));
                        resultsDiv.append(jQuery('<img>').
                            attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
                            attr('id', 'globalsearch-loading-icon'));
                    },
                    success: function (data, status, xhr) {
                        /*var items = $.parseJSON(data);
                        jQuery('#globalsearch-loading-results').remove();
                        if (items.length > 0) {
                        }*/
                        console.log(data);
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }
            );
        }
    }
};

jQuery(function () {
    // Handle search icon click.
    jQuery('#globalsearch-icon').on('click', function() {
        STUDIP.GlobalSearch.toggle();
    });
    // Close search on click on page.
    jQuery('div#flex-header, div#layout_page, div#layout_footer').on('click', function() {
        if (!jQuery('#globalsearch-input').hasClass('hidden-js')) {
            STUDIP.GlobalSearch.toggle();
        }
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

    // Start searching 750 ms after user stopped typing.
    jQuery('#globalsearch-input').keyup(_.debounce(function() { STUDIP.GlobalSearch.doSearch(); }, 750));
});
