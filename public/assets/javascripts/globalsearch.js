STUDIP.GlobalSearch = {

    // Toggles visibility of search input field and hints.
    toggle: function() {
        var input = $('#globalsearch-input');
        if (input.hasClass('hidden-js')) {
            input.removeClass('hidden-js');
            setTimeout(function() {
                input.focus();
                $('#globalsearch-list').removeClass('hidden-js');
            }, 500);
        } else {
            $('#globalsearch-list').addClass('hidden-js');
            input.addClass('hidden-js');
        }
        return false;
    },

    // Performs the actual search.
    doSearch: function() {
        var searchterm = $('#globalsearch-input').val();
        if ($('#globalsearch-input').val() != '') {
            var resultsDiv = $('#globalsearch-results');
            // Call AJAX endpoint and get search results.
            $.ajax(
                STUDIP.ABSOLUTE_URI_STUDIP +'dispatch.php/globalsearch/find',
                {
                    data: {
                        'search': searchterm
                    },
                    // Display spinner symbol, user should always see something is happening.
                    beforeSend: function(xhr, settings) {
                        resultsDiv.attr('align', 'center');
                        resultsDiv.html('');
                        resultsDiv.removeClass('hidden-js');
                        resultsDiv.append(
                            $('<div>').
                                attr('id', 'globalsearch-loading-text').
                                html(resultsDiv.data('loading-text')));
                        resultsDiv.append($('<img>').
                            attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
                            attr('id', 'globalsearch-loading-icon'));
                    },
                    // Success: show search results.
                    success: function (data, status, xhr) {
                        resultsDiv.html('');
                        // Some results found...
                        if ($(data).length > 0) {
                            resultsDiv.attr('align', null);
                            $('#globalsearch-list').css('max-height', ($('html').height() - 100));
                            // Iterate over each result category.
                            $.each(data, function(name, value) {
                                // Create an <article> for category...
                                var category = $('<article>').
                                    append($('<header>').
                                        text(value.name + ' (' + value.content.length + ')'));
                                resultsDiv.append(category);

                                // Process results and create corresponding entries.
                                $.each(value.content, function(index, result) {
                                    // Build detail text.
                                    var description = null;
                                    if (result.description != null) {
                                        description = $('<div>').
                                            attr('class', 'globalsearch-result-description').
                                            html($.parseHTML(result.description));
                                    }
                                    var additional = null;
                                    if (result.additional != null) {
                                        additional = $('<div>').
                                            attr('class', 'globalsearch-result-additional').
                                            html($.parseHTML(result.additional));
                                    }
                                    // Create single result entry.
                                    var single = $('<section>');
                                    // Optional image...
                                    if (result.img != null) {
                                        single.append($('<div class="globalsearch-result-img">').
                                            append(singleImg = $('<img>').
                                                attr('height', '36').
                                                attr('width', '36').
                                                attr('src', result.img)));
                                    }
                                    // Name/title
                                    var dataDiv = $('<div>').
                                        attr('class', 'globalsearch-result-data');
                                    single.append(dataDiv);
                                    dataDiv.append($('<div>').
                                        attr('class', 'globalsearch-result-link').
                                        append($('<a>').
                                            attr('href', result.url).
                                            html($.parseHTML(result.name))));
                                    // Details like:
                                    var singleDetails = $('<div>').
                                        attr('class', 'globalsearch-result-details');
                                    // Descriptional text
                                    if (description != null) {
                                        singleDetails.append(description);
                                    }
                                    // Additional information
                                    if (additional != null) {
                                        singleDetails.append(additional);
                                    }
                                    dataDiv.append(singleDetails);
                                    // Date/Time of entry
                                    if (result.date != null) {
                                        var singleTime = $('<div>').
                                            attr('class', 'globalsearch-result-time').
                                            css('max-width', '20%').
                                            html($.parseHTML(result.date));
                                        single.append(singleTime);
                                    }
                                    category.append(single);
                                });
                            });
                        } else {
                            resultsDiv.html(resultsDiv.data('no-result'));
                        }
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }
            );
        }
    }

};

$(function () {
    // Handle search icon click.
    $('#globalsearch-icon').on('click', function() {
        STUDIP.GlobalSearch.toggle();
    });
    // Close search on click on page.
    $('div#flex-header, div#layout_page, div#layout_footer').on('click', function() {
        if (!$('#globalsearch-input').hasClass('hidden-js')) {
            STUDIP.GlobalSearch.toggle();
        }
    });
    // Show/hide hints on click.
    $('#globalsearch-togglehints').on('click', function() {
        var toggle = $('#globalsearch-togglehints');
        var currentText = toggle.html();
        toggle.html(toggle.data('toggle-text'));
        toggle.data('toggle-text', currentText);
        var hints = $('#globalsearch-hints');
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
    $('#globalsearch-input').keyup(_.debounce(function() { STUDIP.GlobalSearch.doSearch(); }, 750));
});
