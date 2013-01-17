(function ($, STUDIP) {

$(':checkbox[data-proxyfor]').live('change', function () {
    var proxied = $(this).data().proxyfor,
        state   = !!$(this).attr('checked');
    $(proxied).attr('checked', state);
});

$('a[rel~="lightbox"]').live('click', function (event) {
    var $that     = $(this),
        href      = $that.attr('href'),
        container = $('<div/>');
    
    container.load(href, function (response, status, xhr) {
        var width   = $('body').width() * 2 / 3,
            height  = $('body').height() * 2 / 3,
            buttons = {},
            title   = xhr.getResponseHeader('X-Title') || '';

        $('a[rel~="option"]', this).remove().each(function () {
            var label = $(this).text(),
                href  = $(this).attr('href');
            buttons[label] = function () { location.href = href; };
        });
        buttons["Schliessen".toLocaleString()] = function () { $(this).dialog('close'); };

        $(this).dialog({
            width :  width,
            height:  height,
            buttons: buttons,
            title:   title,
            modal:   true
        });
    });
    
    event.preventDefault();
});

$('a[data-behaviour~="ajax-toggle"]').live('click', function (event) {
    var $that = $(this),
        href  = $that.attr('href'),
        id    = $that.closest('tr').attr('id');

    $that.attr('disabled', true).addClass('ajaxing');
    $.get(href, function (response) {
        var row = $('#' + id, response);
        $that.closest('tr').replaceWith(row);
    });

    event.preventDefault();
});

}(jQuery, STUDIP));