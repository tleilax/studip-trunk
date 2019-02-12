jQuery(function ($) {
    var cache = STUDIP.Search.getCache();
    // initially hide all filters except for the semester filter
    $('#reset-search').hide();
    STUDIP.Search.hideAllFilters();
    $('div#semester_filter').show();

    // searchterm and category can be passed by URL parameters (e.g. through the quicksearch)
    var searchterm = $('#search-results').data('searchterm');
    var category = $('#search-results').data('category');
    if(searchterm && category) {
        cache.set('searchterm', searchterm);
        STUDIP.Search.setActiveCategory(category);
        STUDIP.Search.setFilter('semester', '');
    }

    // Clear search term
    $('#reset-search').on('click', function () {
        STUDIP.Search.resetSearch();
        return false;
    });

    // Start search on Enter
    $('#search-input').on('keypress', function (e) {
        if (e.which === 13) {
            STUDIP.Search.doSearch(STUDIP.Search.getFilter());
            return false;
        }
    });

    // Delegate events on sidebar categories so we don't have to bind them
    // one by one (probably needs some work...) TODO refactor
    $('a').filter(function(){
        return  this.id.match(/search_category_*/);
    }).on('click', function () {
        var category = this.id.substr(this.id.lastIndexOf('_') + 1, this.id.length);
        var old_category = cache.get('search_category');
        STUDIP.Search.showAllCategories(old_category);
        STUDIP.Search.toggleLinkText(old_category);
        cache.set('search_category', category);
        STUDIP.Search.showAllCategories(category);
        STUDIP.Search.expandCategory(category);
        STUDIP.Search.toggleLinkText(category);
        STUDIP.Search.setActiveCategory(category);
        return false;
    });

    // click on 'Alle Ergebnisse'
    $('a#show_all_categories').on('click', function() {
        var category = cache.get('search_category');
        STUDIP.Search.toggleLinkText(category);
        STUDIP.Search.showAllCategories(category);
        if (!STUDIP.Search.resultsInCategory) {
            STUDIP.Search.resetFilters();
        }
    });

    // perform a new search when another filter is selected by the user
    $('select').filter(function(){
        return  this.id.match(/.*_select/);
    }).on('change', function () {
        STUDIP.Search.doSearch(STUDIP.Search.getFilter());
        return false;
    }).closest('form').on('submit', function(e) {
        e.preventDefault();
    });

    // set main search bar if a searchterm was typed in before
    $('#search-input').val(function() {
        if (cache.get('searchterm')) {
            STUDIP.Search.doSearch(STUDIP.Search.getFilter());
            if (cache.get('search_category')) {
                STUDIP.Search.setActiveCategory(cache.get('search_category'));
            }
        }
        return cache.get('searchterm');
    });

    // Delegate events on result container so we don't have to bind them
    // one by one
    $('#search-results').on('click', '.search-category a', function () {
        var category = $(this).closest('.search-category').data('category');
        STUDIP.Search.toggleLinkText(category);
        STUDIP.Search.expandCategory(category);
        STUDIP.Search.setActiveCategory(category);
        return false;
    });

    // Start searching 500 ms after user stopped typing.
    $('#search-input').keyup(_.debounce(function () {
        STUDIP.Search.doSearch(STUDIP.Search.getFilter());
    }, 500));

});
