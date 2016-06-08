/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

(function ($, STUDIP) {
    'use strict';

    STUDIP.ActionMenu = {

    };

    $(document).ready(function () {
        // Open submenu trigger.
        $('ul.actionmenu').find('li > a, li > img, li > svg').on('click', function(e) {
            var el = $(this).parent();
            // Hide all other open actionmenus.
            $('ul.actionmenu').not(el.parents('ul.actionmenu')).find('li.active').removeClass('active');
            $('ul.actionmenu').removeClass('positioncorrected');
            $('ul.actionmenu').css('left', '');
            el.toggleClass('active');
            var parent = el.parent();


            // First menu level is a bit different from others.
            if (parent.hasClass('actionmenu')) {
                var td = parent.closest('td');
                // Adjust menu top item width to match the children.
                if (el.hasClass('active')) {
                    // Get width of child li elements.
                    var itemWidth = el.children('ul').children('li').width();

                    el.children('div.action-title').width(itemWidth - 15);

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
            } else {
                // Get width of child li elements.
                var itemWidth = el.closest('li.active').width();

                if (el.hasClass('active')) {
                    el.children('ul').css('right', (itemWidth + 10) + 'px');
                } else {
                    el.children('ul').css('right', '');
                }
            }
        });
    });

}(jQuery, STUDIP));
