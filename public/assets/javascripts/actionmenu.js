/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

(function ($, STUDIP) {
    'use strict';

    STUDIP.ActionMenu = {
        
    };

    $(document).ready(function () {
        // Open submenu trigger.
        $('ul.actionmenu').find('li').on('click', function(e) {
            // Hide all other open actionmenus.
            $('ul.actionmenu').not($(this).parents('ul.actionmenu')).children('li').removeClass('active');
            $('ul.actionmenu').removeClass('positioncorrected');
            $('ul.actionmenu').css('left', '');
            $(this).toggleClass('active');
            var parent = $(this).parent();
            if (parent.hasClass('actionmenu')) {
                var td = parent.closest('td');

                // Adjust menu top item width.
                if ($(this).hasClass('active')) {
                    var itemWidth = $(this).children('ul').children('li').width();
                    $(this).children('div.action-title').width(itemWidth - 15);

                    /*
                     * We need to check if we are inside a table because
                     * then the positioning must be changed if we do not
                     * want the td to grow along with the menu.
                     */
                    if (td.length > 0) {
                        td.css('position', 'relative');
                        td.css('height', '35px');
                        parent.addClass('positioncorrected');
                        parent.css('left', '-' + (itemWidth - 55) + 'px');
                    }
                }
            }
        });
    });

}(jQuery, STUDIP));
