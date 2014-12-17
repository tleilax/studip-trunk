/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

jQuery(function ($) {
    if (!window.matchMedia) {
        return;
    }

    var media_query = window.matchMedia('(max-width: 768px)');

    function responsify (mq) {
        media_query.removeListener(responsify);

        addMenu();

        if ($('#layout-sidebar').length > 0) {
            $('<li id="sidebar-menu">').on('click', function () {
                $('#hamburgerChecker').prop('checked', false);
                $('#layout-sidebar').toggleClass('visible-sidebar');
            }).appendTo('#barBottomright ul');

            $('#hamburgerChecker').on('change', function () {
                $('#layout-sidebar').removeClass('visible-sidebar');
            });
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
    };

    function addMenu () {
        var wrapper = $('<div id="responsive-navigation">'),
            menu    = buildMenu(STUDIP.Navigation, 'resp', 'hamburgerNavigation');
        
        $('<label for="hamburgerChecker" class="hamburger">').appendTo(wrapper);
        $('<input type="checkbox" id="hamburgerChecker">').appendTo(wrapper);
        wrapper.append(menu);
        
        wrapper.appendTo('#barBottomLeft');
    };

    function buildMenu (navigation, path, id) {
        var list = $('<ul>'),
            menu;

        if (id) {
            list.attr('id', id);
        }

        _.forEach(navigation, function (nav, node) {
            var subpath = path + '_' + node,
                li      = $('<li>'),
                item    = $('<div class="navigation_item">').appendTo(li),
                title   = $('<div class="nav_title">').appendTo(item),
                label   = $('<label>').attr('for', subpath).html(nav.title).appendTo(title);
            
            if (nav.image) {
                $('<img class="icon">').attr('src', STUDIP.ASSETS_URL + nav.image).prependTo(label);
            }
            
            $('<a class="nav_link">').attr('href', STUDIP.ABSOLUTE_URI_STUDIP + nav.url).appendTo(item);
            
            if (nav.children) {
                $('<input type="checkbox">').attr('id', subpath).prop('checked', nav.active).appendTo(li);
                li.append(buildMenu(nav.children, subpath));
            }

            list.append(li);
        });
        
        return list;
    };

    if (media_query.matches) {
        responsify();
    } else {
        media_query.addListener(responsify);
    }
});