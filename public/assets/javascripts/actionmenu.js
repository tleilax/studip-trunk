/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

(function ($, STUDIP) {
    'use strict';

    STUDIP.ActionMenu = {
        
    };

    $(document).ready(function () {
        // Open submenu trigger.
        $('ul.actionmenu').children('li').on('click', function(e) {
            // Hide all other open actionmenus.
            $('ul.actionmenu').not($(this).parents('ul.actionmenu')).children('li').removeClass('active');
            $(this).toggleClass('active');
        });
    });

}(jQuery, STUDIP));
