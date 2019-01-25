const Search = {
    lastSearch: null,
    lastSearchFilter: null,

    /**
     * This function starts the actual search via AJAX call.
     *
     * @param {Object} filter object with filter information (e.g. 'category', 'semester', etc.)
     *               that is set by the filter selects in the sidebar.
     */
    doSearch: function (filter) {

        var cache           = STUDIP.Cache.getInstance(),
            searchterm      = $('#search-input').val().trim() || cache.get('searchterm'),
            hasValue        = searchterm && searchterm.length >= 3,
            resultsDiv      = $('#search-results'),
            resultsPerType  = resultsDiv.data('results-per-type'),
            allResultsText  = resultsDiv.data('all-results'),
            wrapper         = $('#search');

        if (searchterm === '') {
            return;
        }

        if (!hasValue || STUDIP.Search.lastSearch === searchterm
            && JSON.stringify(STUDIP.Search.lastSearchFilter) === JSON.stringify(filter)) {
            return;
        }

        var resultsInCategory = false;

        $('#search-no-result').hide();
        $('#reset-search').show();

        STUDIP.Search.resetSearchCategories();
        STUDIP.Search.greyOutSearchCategories();

        cache.set('searchterm', searchterm);
        STUDIP.Search.lastSearch = searchterm;
        STUDIP.Search.lastSearchFilter = filter;

        // Display spinner symbol, user should always see something is happening.
        wrapper.addClass('is-searching');

        // Call AJAX endpoint and get search results.
        $.getJSON(STUDIP.URLHelper.getURL('dispatch.php/globalsearch/find'), {
            search: searchterm,
            filters: JSON.stringify(filter)
        }).done(function (json) {
            resultsDiv.html('');

            // No results found...
            if (!$.isPlainObject(json) || $.isEmptyObject(json)) {
                wrapper.removeClass('is-searching');
                $('#search-no-result').show();
                STUDIP.Search.setActiveCategory('show_all_categories');
                return;
            }

            // Iterate over each result category.
            $.each(json, function (name, value) {
                // Create an <article> for category.
                var category = $(`<article id="search-${name}" class="studip">`),
                    header   = $('<header>').appendTo(category),
                    categoryBodyDiv = $(`<div id="${name}-body">`).appendTo(category),
                    counter  = 0;
                if (STUDIP.Search.getActiveCategory() == name) {
                    resultsInCategory = true;
                }

                // Create header name
                $('<a href="#">').text(value.name)
                    .wrap('<h1 class="search-category">')
                    .parent() // Element is now the wrapper
                    .data('category', name)
                    .appendTo(header);

                if (value.more) {
                    $('<a href="#">').text(allResultsText)
                        .wrap(`<div id="show-all-categories-${name}" class="search-more-results">`)
                        .parent() // Element is now the wrapper
                        .appendTo(header)
                        .hide();
                }

                // Process results and create corresponding entries.
                $.each(value.content, function (index, result) {
                    // Create single result entry.
                    var single      = $('<section>'),
                        data        = $('<div class="search-result-data">'),
                        details     = $('<div class="search-result-details">'),
                        information = $('<div class="search-result-information">');

                    if (counter >= resultsPerType) {
                        single.addClass('search-extended-result');
                    }

                    var link = $(`<a href="${result.url}">`)
                        .appendTo(single);

                    // Optional image...
                    if (result.img !== null) {
                        $(`<img src="${result.img}">`)
                            .wrap('<div class="search-result-img">')
                            .parent() // Element is now the wrapper
                            .appendTo(link);
                    }

                    link.append(data);

                    // Name/title
                    $('<div class="search-result-title">')
                        .html($.parseHTML(result.name))
                        .appendTo(data);

                    if (result.number !== null) {
                        $('<div class="search-result-number">')
                            .html($.parseHTML(result.number))
                            .appendTo(details);
                    }

                    // Details: Descriptional text
                    if (result.description !== null) {
                        $('<div class="search-result-description">')
                            .html($.parseHTML(result.description))
                            .appendTo(details);
                    }

                    if (result.dates !== null) {
                        $('<div class="search-result-dates">')
                            .html($.parseHTML(result.dates))
                            .appendTo(details);
                    }

                    data.append(details);

                    // Date/Time of entry
                    if (result.date !== null) {
                        $('<div class="search-result-time">')
                            .html($.parseHTML(result.date))
                            .appendTo(information);
                    }

                    // Details: Additional information
                    var additional = $('<div class="search-result-additional">');
                    if (result.additional !== null) {
                        additional.html($.parseHTML(result.additional));

                        // "Expand" attribute for further, result-related search
                        // (e.g. search in course of found forum entry)
                        if (result.expand !== null && result.expand !== value.fullsearch) {
                            additional.wrapInner(`<a href="${result.expand}" title="${result.expandtext}">`);
                        }
                        additional.appendTo(information);
                    }

                    link.append(information);

                    categoryBodyDiv.append(single);

                    counter += 1;
                });
                resultsDiv.append(category);
                $(`a#search_category_${name}`)
                    .removeClass('no-result')
                    .text(`${value.name}  (${counter})`);

                // We have more search results than shown, provide link to
                // full search if available.
                if (value.more) {
                    var footer = $('<footer class="search-more-results">');
                    $(`<a id="link_all_results_${name}" href="#">`).text(`alle ${counter} ${value.name} anzeigen`)
                        .click(function() {
                            STUDIP.Search.toggleLinkText(name);
                            STUDIP.Search.expandCategory(name);
                            STUDIP.Search.setActiveCategory(name);
                        })
                        .appendTo(footer);
                    $(`<a id="link_results_${name}" href="#">`).text(allResultsText).hide()
                        // .wrap(footer)
                        // .parent() // Element is now the wrapper
                        .click(function() {
                            STUDIP.Search.toggleLinkText(name);
                            STUDIP.Search.showAllCategories(name);
                            STUDIP.Search.setActiveCategory(name);
                        })
                        .appendTo(footer);
                    footer.appendTo(category);
                    if (STUDIP.Search.getActiveCategory() != 'show_all_categories') {
                        console.log(STUDIP.Search.getActiveCategory());
                        $(`a#link_all_results_${STUDIP.Search.getActiveCategory()}`).hide();
                        $(`a#link_results_${STUDIP.Search.getActiveCategory()}`).show();
                        $(`div#show-all-categories-${STUDIP.Search.getActiveCategory()}`).show();
                    }
                }

            });
            if (STUDIP.Search.getActiveCategory() != 'show_all_categories') {
                if (!resultsInCategory) {
                    STUDIP.Search.showAllCategories(STUDIP.Search.getActiveCategory());
                    STUDIP.Search.setActiveCategory('show_all_categories');
                } else {
                    STUDIP.Search.expandCategory(STUDIP.Search.getActiveCategory());
                    // STUDIP.Search.toggleLinkText(STUDIP.Search.getActiveCategory());
                }
            }

            wrapper.removeClass('is-searching');
        }).fail(function (xhr, status, error) {
            window.alert(error);
        });
    },

    /**
     * Clear search term and category from the cache,
     * reload the page and reset the active category.
     */
    resetSearch: function () {
        var cache = STUDIP.Cache.getInstance();
        STUDIP.Search.lastSearch = null;
        cache.remove('searchterm');
        cache.remove('search_category');
        // reload without parameters
        if (location.href.includes('?')) {
            location = location.href.split('?')[0];
        } else {
            location.reload();
        }
        STUDIP.Search.setActiveCategory('show_all_categories');
    },

    /**
     * Show all possible categories in the sidebar without result numbers.
     */
    resetSearchCategories: function () {
        $('a').filter(function () {
            return  this.id.match(/search_category_*/);
        }).each(function () {
            var category = $(this).text();
            if (category.includes('(')) {
                category = category.substr(0, category.indexOf('(') - 1);
                $(this).text(category);
            }
        }).show();
    },

    /**
     * Grey out all categories in the sidebar with no results.
     */
    greyOutSearchCategories: function () {
        $('a').filter(function () {
            return  this.id.match(/search_category_*/);
        }).addClass('no-result');
    },

    /**
     * Hide all select filters in the sidebar.
     */
    hideAllFilters: function () {
        $('div').filter(function () {
            return  this.id.match(/.*_filter/);
        }).hide();
    },

    /**
     * Show the select filters for a given category in the sidebar. Default: semester filter.
     *
     * @param {string} category Given category for which specific select filters should be shown.
     */
    showFilter: function (category) {
        var filters = $('#search-results').data('filters');
        STUDIP.Search.hideAllFilters();
        var active_filters = filters[category];
        if (active_filters  && category != 'show_all_categories') {
            for (let i = 0; i < active_filters.length; i++) {
                $(`#${active_filters[i]}_filter`).show();
            }
        } else if (category === 'show_all_categories') {
            $('#semester_filter').show();
        }
    },

    /**
     * Set the specified category active (highlighted) in the sidebar.
     * <li class="active">
     *
     * @param {string} category Given category which should be highlighted in the sidebar.
     */
    setActiveCategory: function (category) {
        var cache = STUDIP.Cache.getInstance();
        cache.set('search_category', category);
        // remove all active classes
        $('#show_all_categories').closest('li').removeClass('active');
        $('a').filter(function () {
            return  this.id.match(/search_category_*/);
        }).closest('li').removeClass('active');

        // set clicked class active
        if (category == 'show_all_categories') {
            $('#show_all_categories').closest('li').addClass('active');
        } else {
            $(`#search_category_${category}`).closest('li').addClass('active');
        }
        STUDIP.Search.showFilter(category);
    },

    /**
     * Get the current values from the filter selects in the sidebar that are relevant.
     *
     * @return {Object} filter object with the filter values set by the user.
     */
    getFilter: function () {
        var filters = $('#search-results').data('filters');
        var category = STUDIP.Search.getActiveCategory();
        var filter = {category: category};
        var active_filters = filters[category];
        $('select').filter(function () {
            return this.id.match(/.*_select/);
        }).each(function () {
            var selected = this.id.substr(0, this.id.lastIndexOf('_'));
            if ($.inArray(selected, active_filters) !== -1) {
                filter[selected] = $('option:selected', this).val();
            }
        });
        return filter;
    },

    /**
     * Set a specific sidebar filter select to the given value.
     *
     * @param {string} filter filter that should be set.
     * @param {string} value value that the filter should be set to.
     */
    setFilter: function (filter, value) {
        $(`#${filter}_select`).val(value);
    },

    /**
     * Getter for the selected (active) category.
     *
     * @return {string} The active (currently selected) category in the sidebar widget.
     */
    getActiveCategory: function () {
        var cache = STUDIP.Cache.getInstance();
        return cache.get('search_category');
    },

    /**
     * Toggle the link text for 'show all' results of one category and 'show all categories'
     * with max. 3 results each.
     *
     * @param {string} category Category for which the link text should be toggled
     */
    toggleLinkText: function (category) {
        if ($(`a#link_all_results_${category}`).is(':visible')) {
            $(`a#link_all_results_${category}`).hide();
            $(`a#link_results_${category}`).show();
            $(`div#show-all-categories-${category}`).show();
        } else {
            $(`a#link_all_results_${category}`).show();
            $(`a#link_results_${category}`).hide();
            $(`div#show-all-categories-${category}`).hide();
        }
    },

    /**
     * Expand a single category, showing more results, and hide other categories.
     *
     * @param {string} category Category that should be expanded.
     * @returns {boolean} false
     */
    expandCategory: function (category) {
        // Hide other categories.
        $(`#search-results article:not([id="search-${category}"])`).hide();
        // Show all results.
        $(`#search-${category} section.search-extended-result`)
            .removeClass('search-extended-result');
        // Reassign category click to closing extended view.
        var selector = [
            `#search-results article#search-${category} header a`,
            `#link_all_results_${category}`,
            `#link_results_${category}`,
            `#show-all-categories-${category}`
        ].join(',');
        $(selector).off('click').on('click', function () {
            STUDIP.Search.toggleLinkText(category);
            STUDIP.Search.showAllCategories(category);
            return false;
        });
        return false;
    },

    /**
     * Close expanded view of a single category, showing normal view with
     * all categories again.
     *
     * @param {string} currentCategory Category that was previously selected.
     * @return {boolean} false
     */
    showAllCategories: function (currentCategory) {
        var selector = [
            `#search-results article#search-${currentCategory} header a`,
            `#link_all_results_${currentCategory}`,
            `#link_results_${currentCategory}`
        ].join(',');
        $(selector).off('click').on('click', function () {
            STUDIP.Search.toggleLinkText(currentCategory);
            STUDIP.Search.expandCategory(currentCategory);
            STUDIP.Search.setActiveCategory(currentCategory);
            return false;
        });
        var resultCount = $('#search-results').data('results-per-type') - 1;
        $(`#search-${currentCategory} section:gt(${resultCount})`)
            .addClass('search-extended-result');
        $('#search-results').children(`article:not([id="search-${currentCategory}"])`).show();
        STUDIP.Search.setActiveCategory('show_all_categories');
        return false;
    }
};

export default Search;