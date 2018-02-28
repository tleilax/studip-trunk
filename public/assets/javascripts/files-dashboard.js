/*jslint browser: true, unparam: true, todo: true */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    STUDIP.FilesDashboard = {
        /**
         * Diese Methode wird aufgerufen, sobald ein Dashboard-Widget
         * maximiert wurde. Die dort enthaltene Tabelle wird dann
         * sortierbar gemacht.
         * Die `elementId` bezieht sich auf die widget_element_id des Widgets.
         */
        enhanceList: function (elementId) {
            $(document).on("dialog-open", function () {
                $('.ui-dialog table[data-element-id="' + elementId + '"]')
                    .each(function (index, element) {
                        STUDIP.Table.enhanceSortableTable(element)
                    })
            })
        }
    }
}(jQuery, STUDIP));
