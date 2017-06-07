/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

    $(document).ready(function () {
        STUDIP.i18n.init();
    });

    $(document).on('dialog-update', function (event, data) {
        STUDIP.i18n.init(data.dialog);
    });

    STUDIP.i18n = {
        init: function (root) {
            $('.i18n_group', root).each(function () {
                var languages = $(this).children('.i18n'),
                    select    = $('<select tabindex="-1">').addClass('i18n').css('background-image', $(languages).first().data('icon'));
                select.change(function () {
                    var opt   = $(this).find('option:selected'),
                        index = opt.index();
                    languages.not(':eq(' + index + ')').hide();
                    languages.eq(index).show().focus();
                    $(this).css('background-image', opt.css('background-image'));
                });
                languages.each(function (id, lang) {
                    select.append($('<option>', {text: $(lang).data('lang')}).css('background-image', $(lang).data('icon')));
                });
                $(this).append(select);
                languages.not(':eq(0)').hide();

                $('div.i18n textarea[required]', this).on('invalid', function () {
                    var element = $(this).closest('.i18n');
                    if (element.parent().find('.i18n:has(textarea:visible:invalid)').length === 0) {
                        element.siblings('div').hide();
                        element.siblings('select').val($(element).data('lang')).change();
                    }
                });
            });
        }
    };

}(jQuery, STUDIP));
