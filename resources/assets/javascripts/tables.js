/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    jQuery(function ($) {

        if (window.MutationObserver !== undefined) {
            var observer = new window.MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName === "class") {
                        if ($(mutation.target).attr("class").indexOf("open") !== -1) {
                            $(mutation.target).next().find("td").slideDown().find(".detailscontainer").hide().slideDown();
                        } else {
                            $(mutation.target).next().show().find("td").slideUp().find(".detailscontainer").slideUp();
                        }
                    }
                });
            });
            $("table.withdetails > tbody > tr:not(.details)").each(function (index, element) {
                observer.observe(element, { attributes: true });
            });
        }

        function enhanceSortableTable(table) {
            var headers = {};
            $('thead tr:last th', table).each(function (index, element) {
                headers[index] = {
                    sorter: $(element).data().sort || false
                };
            });

            if ($('tbody tr[data-sort-fixed]', table).length > 0) {
                $('tbody tr[data-sort-fixed]', table).each(function () {
                    var index = $(this).index();
                    $(this).data('sort-fixed', index);
                });
                $(table).on('sortStart', function () {
                    $('tbody tr[data-sort-fixed]', table).each(function () {
                        var hidden = $(this).is(':hidden');
                        $(this).data('sort-hidden', hidden);
                    });
                }).on('sortEnd', function () {
                    $('tbody tr[data-sort-fixed]', table).detach().each(function () {
                        var index  = $(this).data('sort-fixed');
                        if ($('tbody tr', table).length === 0) {
                            $('tbody:first', table).append(this);
                        } else {
                            $('tbody tr:eq(' + index + ')', table).before(this);
                        }

                        if ($(this).data('sort-hidden')) {
                            setTimeout(function () {
                                $(this).hide();
                            }.bind(this), 100);
                        }
                    });
                });
            }

            $(table).tablesorter({
                headers: headers
            });
        }

        STUDIP.Table = {
            enhanceSortableTable: function (table) {
                // async load the tablesorter, then enhance
                import(/* webpackChunkName: "tablesorter" */ './tablesorter')
                    .then(() => {
                        enhanceSortableTable(table)
                    })
                    .catch(error => {
                        console.log('An error occurred while loading the tablesorter component', error)
                    })
            }
        };

        if ($('table.sortable-table').length) {
            $('table.sortable-table').each(function (index, element) {
                STUDIP.Table.enhanceSortableTable(element);
            });
        }
    });

}(jQuery, STUDIP));
