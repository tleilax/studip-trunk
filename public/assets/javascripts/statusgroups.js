/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

    var ajax_endpoint = false;

    STUDIP.Statusgroups = {
        apply: function () {
            $('.movable tbody').sortable({
                axis: 'y',
                handle: '.dragHandle',
                helper: function (event, ui) {
                    ui.children().each(function () {
                        $(this).width($(this).width());
                    });
                    return ui;
                },
                start: function (event, ui) {
                    $(this).closest('table').addClass('nohover');
                },
                stop: function (event, ui) {
                    var table    = $(this).closest('table'),
                        group    = table.attr('id'),
                        user     = ui.item.data('userid'),
                        position = $(ui.item).prevAll().length;

                    table.removeClass('nohover');

                    $.ajax({
                        type: 'POST',
                        url: ajax_endpoint,
                        dataType: 'html',
                        data: {group: group, user: user, pos: position},
                        async: false
                    }).done(function (data) {
                        $('tbody', table).html(data);
                        STUDIP.Statusgroups.apply();
                    });
                }
            });
        },

        initInputs: function () {
            //$('input[name="selfassign_start"]').datetimepicker();
            if (!$('input[name="selfassign"]').attr('checked')) {
                $('input[name="exclusive"]').closest($('section')).hide();
                $('input[name="selfassign_start"]').closest($('section')).hide();
                $('input[name="selfassign_end"]').closest($('section')).hide();
            }
            //$('input[name="selfassign_end"]').datetimepicker();
            $('input[name="selfassign"]').on('click', function () {
                $('input[name="exclusive"]').closest($('section')).toggle();
                $('input[name="selfassign_start"]').closest($('section')).toggle();
                $('input[name="selfassign_end"]').closest($('section')).toggle();
            });

            $('input[name="numbering_type"]').on('click', function () {
                var type     = $('input[name="numbering_type"]:checked').val(),
                    disabled = parseInt(type, 10) === 2;

                $('input[name="startnumber"]')
                    .prop('disabled', disabled)
                    .toggle(!disabled);
            });
        }

    };

    $(document).ready(function () {
        ajax_endpoint = $('meta[name="statusgroups-ajax-movable-endpoint"]').attr('content');
        STUDIP.Statusgroups.apply();

        $('.nestable').each(function () {
            $(this).nestable({
                rootClass: 'nestable',
                maxDepth: $(this).data('max-depth') || 5
            });
        });

        $('a.get-group-members').on('click', function () {
            var dataEl = $('article#group-members-' + $(this).data('group-id')),
                url;
            if ($.trim(dataEl.html()).length === 0) {
                url = $(this).data('get-members-url');

                dataEl.html($('<img>').attr({
                    width: 32,
                    height: 32,
                    src: STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg'
                }));

                $.get(url).done(function (html) {
                    dataEl.html(html);
                });
            }
        });

        var index_before = null;
        $('.course-statusgroups[data-sortable]').disableSelection().sortable({
            axis: 'y',
            containment: 'parent',
            forcePlaceholderSize: true,
            items: '> .draggable',
            placeholder: 'sortable-placeholder',
            start: function (event, ui) {
                index_before = ui.item.index();
            },
            stop: function (event, ui) {
                if (index_before === ui.item.index()) {
                    return;
                }

                var url = $(this).data('sortable');
                $.post(url, {
                    id: ui.item.attr('id'),
                    index: ui.item.index() - 1
                });
            }
        });

    }).on('dialog-open dialog-update', function () {
        $('.nestable').each(function () {
            $(this).nestable({
                rootClass: 'nestable',
                maxDepth: $(this).data('max-depth') || 5
            });
        });
    }).on('submit', '#order_form', function () {
        var structure = $('.nestable').nestable('serialize'),
            json_data = JSON.stringify(structure);
        $('#ordering').val(json_data);
    });

}(jQuery, STUDIP));
