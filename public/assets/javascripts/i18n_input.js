$(document).ready(function() {
        STUDIP.i18n.init();
    });

STUDIP.i18n = {
    init: function() {
        $('div.i18n_group:not(.single_lang)').each(function() {
            var languages = $(this).find('input, textarea');
            var select = $('<select>').addClass('i18n').css('background-image', $(languages).first().css('background-image'));
            select.change(function() {
                var opt = $(this).find('option:selected')
                var index = opt.index();
                languages.not(':eq('+index+')').hide();
                languages.eq(index).show();
                $(this).css('background-image', opt.css('background-image'));
            });
            languages.each(function(id, lang) {
                select.append($('<option>', {text: $(lang).data().lang_desc}).css('background-image', $(lang).css('background-image')));
            });
            $(this).append(select);
            languages.css('background-image', '').not(':eq(0)').hide();
        });
    }
}