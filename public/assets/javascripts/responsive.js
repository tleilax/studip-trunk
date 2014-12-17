/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

jQuery(function ($) {
    if (!window.matchMedia) {
        return;
    }

    var media_query = window.matchMedia('(max-width: 768px)');

    function responsify(mq) {
        media_query.removeListener(responsify);

        if ($('#layout-sidebar').length > 0) {
            $('<li id="sidebar-menu">').on('click', function () {
                $('#hamburgerChecker').prop('checked', false);
                $('#layout-sidebar').toggleClass('visible-sidebar');
            }).appendTo('#barBottomright ul');
        }


        $('#hamburgerNavigation :checkbox').on('change', function () {
            var li = $(this).closest('li');
            if ($(this).is(':checked')) {
                li.siblings(':not(#hamburgerNavigation > li)').slideUp();
                if (li.is('#hamburgerNavigation > li')) {
                    li.siblings().find(':checkbox:checked').prop('checked', false);
                }
            } else {
                $(this).closest('li').siblings().slideDown();
            }
        }).trigger('change');

        $('.hamburger').on('click', function () {
            $('#layout-sidebar').removeClass('visible-sidebar');
        });
    }

    if (media_query.matches) {
        responsify();
    } else {
        media_query.addListener(responsify);
    }
});