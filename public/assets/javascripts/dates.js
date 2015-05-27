/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global jQuery, STUDIP */

(function ($, STUDIP) {

    STUDIP.Dates = {
        addTopic: function () {
            var topic_name = $('#new_topic').val(),
                termin_id  = $('#new_topic').closest('table[data-termin_id]').data('termin_id');
            $.ajax({
                url: STUDIP.URLHelper.getURL('dispatch.php/course/dates/add_topic'),
                data: {
                    title: topic_name,
                    termin_id: termin_id
                },
                dataType: 'json',
                type: 'post'
            }).done(function (response) {
                $('#new_topic').closest('table').find('.themen_list').append(response.li);
                $('#date_' + termin_id).find('.themen_list').append(response.li);
            });

            $('#new_topic').val('');
        },
        removeTopic: function () {
            var topic_id  = $(this).closest('li').data('issue_id'),
                termin_id = $('#new_topic').closest('table[data-termin_id]').data('termin_id');
            $.ajax({
                url: STUDIP.URLHelper.getURL('dispatch.php/course/dates/remove_topic'),
                data: {
                    issue_id: topic_id,
                    termin_id: termin_id
                },
                dataType: 'json',
                type: 'post'
            }).done(function () {
                $('.topic_' + topic_id).remove();
            });
        }
    };

    $(document).on('click', '.remove_topic', STUDIP.Dates.removeTopic);

    $(document).ready(function () {
        $('#course-dates-index .dates').tablesorter({
            textExtraction: function(node) {
                var $node = $(node);
                return String($node.data('timestamp') || $node.text()).trim();
            },
            cssAsc: 'sortasc',
            cssDesc: 'sortdesc',
            sortList: [[0, 0]]
        });
    });

    // Drag and drop support for topics in date list
    function createDraggable() {
        $('.themen_list li > a:not(.draggable-topic)').each(function () {
            var table_id = $(this).closest('table').data().tableId;

            // jQuery' addClass does not work on svgs so we need to set the
            // handle class by hand
            // (see http://stackoverflow.com/a/29525743/982902)
            $(this).children().each(function () {
                this.classList.add('draggable-topic-handle');
            });

            $(this).addClass('draggable-topic').attr('data-table-id', table_id).draggable({
                axis: 'y',
                containment: $(this).closest('tbody'),
                handle: '.draggable-topic-handle',
                revert: true
            });
        });
    }

    $(document).ready(function () {
        if ($('body#course-dates-index').length === 0) {
            return;
        }

        $(document).ajaxComplete(createDraggable);

        $('.themen_list').each(function () {
            var table_id = $(this).closest('table').data().tableId;
            $(this).closest('td').addClass('topic-droppable').droppable({
                accept: '.draggable-topic[data-table-id="' + table_id + '"]',
                activeClass: 'active',
                hoverClass: 'hovered',
                drop: function (event, ui) {
                    var context = $(ui.draggable.context),
                        topic   = context.closest('li').data().issue_id,
                        source  = context.closest('tr').data().dateId,
                        target  = $(this).closest('tr').data().dateId,
                        path    = ['dispatch.php/course/dates/move_topic', topic, source, target].join('/'),
                        url     = STUDIP.URLHelper.getURL(path),
                        cell    = $(this);

                    if (source === target) {
                        return;
                    }

                    ui.draggable.draggable('option', 'revert', false);

                    $.post(url).done(function (response) {
                        ui.draggable.draggable('destroy').closest('li').remove();
                        $('ul', cell).append(response);
                    });
                }
            });
        });

        createDraggable();
    });

}(jQuery, STUDIP));