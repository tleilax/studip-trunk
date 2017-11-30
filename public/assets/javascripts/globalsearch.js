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
            $.ajax(
                STUDIP.ABSOLUTE_URI_STUDIP +'dispatch.php/globalsearch/find/' + searchterm,
                {
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
                    success: function (data, status, xhr) {
                        $('#globalsearch-loading-text').remove();
                        $('#globalsearch-loading-icon').remove();
                        if ($(data).length > 0) {
                            resultsDiv.attr('align', null);
                            // Iterate over each result category.
                            $.each(data, function(name, value) {
                                // Create an <article> for category...
                                var category = $('<article>').
                                    append($('<header>').
                                        html(value.name + ' (' + value.content.length + ')'));
                                resultsDiv.append(category);

                                // Process results and create corresponding entries.
                                $.each(value.content, function(index, result) {
                                    // Build detail text.
                                    var detail = '';
                                    if (result.description != null) {
                                        detail += result.description + '<br>';
                                    }
                                    if (result.additional != null) {
                                        detail += result.additional + '<br>';
                                    }
                                    // Create single result entry.
                                    var single = $('<section>');
                                    if (result.img != null) {
                                        single.append($('<div class="globalsearch-result-img">').
                                            append(singleImg = $('<img>').
                                                attr('height', '50').
                                                attr('width', '50').
                                                attr('src', result.img)));
                                    }
                                    var dataDiv = $('<div>').
                                        attr('class', 'globalsearch-result-data');
                                    single.append(dataDiv);
                                    dataDiv.append($('<div>').
                                        attr('class', 'globalsearch-result-link').
                                        append($('<a>').
                                            attr('href', result.url).
                                            html(result.name)));
                                    var singleDetails = $('<div>').
                                        attr('class', 'globalsearch-result-details').
                                        html(detail);
                                    dataDiv.append(singleDetails);
                                    if (result.date != null) {
                                        var singleTime = $('<div>').
                                            attr('class', 'globalsearch-result-time').
                                            css('max-width', '20%').
                                            html(result.date);
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
