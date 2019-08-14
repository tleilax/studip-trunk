function searchMoreFiles(button) {
    var table = $(button).closest('table'),
        loading = $('<div class="loading" style="padding: 10px">').html(
            $('<img>')
                .attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg')
                .css('width', '24')
                .css('height', '24')
        );

    $(button).replaceWith(loading);

    $.get(button.href).done(function(output) {
        table.find('tbody').append($('tbody tr', output));
        table.find('tfoot').replaceWith($('tfoot', output));
    });

    return false;
}

STUDIP.domReady(() => {
    $('form.drag-and-drop.files').on('dragover dragleave', function(event) {
        $(this).toggleClass('hovered', event.type === 'dragover');
        return false;
    });
    $('form.drag-and-drop.files').on('drop', function(event) {
        event.preventDefault();

        var filelist = event.originalEvent.dataTransfer.files || {};
        STUDIP.Files.upload(filelist);
    });
    $('form.drag-and-drop.files').on('click', function() {
        $('.file_selector input[type=file]')
            .first()
            .click();
    });

    // workaround to wait for tables.js to be executed first
    STUDIP.domReady(() => {
        if ($.fn.hasOwnProperty('filterTable')) {
            $('table.documents.flat').filterTable({
                highlightClass: 'filter-match',
                ignoreColumns: [0, 1, 3, 5, 6],
                inputSelector: '.sidebar .tablesorterfilter',
                minChars: 1,
                minRows: 1
            });
        }

        $(document).trigger('refresh-handlers');
    });

    $(document).on('click', '#file_license_chooser_1 > input[type=radio]', STUDIP.Files.updateTermsOfUseDescription);

    $(document).on('click', '.files-search-more', function(event) {
        event.preventDefault();
        return searchMoreFiles(this);
    });
});

jQuery(document).on('ajaxComplete', function (event, xhr) {
    if (!xhr.getResponseHeader('X-Filesystem-Changes')) {
        return;
    }

    var changes = JSON.parse(xhr.getResponseHeader('X-Filesystem-Changes'));
    var payload = false;

    function process(key, handler) {
        if (!changes.hasOwnProperty(key)) {
            return;
        }

        var values = changes[key];
        if (values === null && xhr.getResponseHeader('Content-Type').match(/json/)) {
            try {
                if (payload === false) {
                    payload = JSON.parse(xhr.responseText);
                }
                if (payload.hasOwnProperty(key)) {
                    values = payload[key];
                }
            } catch (e) {
            }
        }

        handler(values);
    }

    process('removed_files', function (id) {
        if (!Array.isArray(id)) {
            id = [id];
        }
        id.forEach(STUDIP.Files.removeFileDisplay);
    });
    process('added_files', STUDIP.Files.addFileDisplay);
    process('added_folders', STUDIP.Files.addFolderDisplay);
    process('redirect', STUDIP.Dialog.fromURL);
    process('message', function (html) {
        $('.file_upload_window .uploadbar')
            .hide()
            .parent()
            .append(html);
    });
    process('close_dialog', STUDIP.Dialog.close);

});
