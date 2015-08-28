/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _, STUDIP */

jQuery(function ($) {

    $(document).on('ready dialog-open dialog-update', function() {
        $('#block_appointments_days input').click(function () {
            var clicked_id = parseInt(this.id.split('_').pop(), 10);
            if (clicked_id === 0 || clicked_id === 1) {
                $('#block_appointments_days input:checkbox').prop('checked', function (i) {
                    return i === clicked_id;
                });
            } else {
                $('#block_appointments_days_0').prop('checked', false);
                $('#block_appointments_days_1').prop('checked', false);
            }
        });
    });

    $(document).on('click', 'a.bookable_rooms_action', function (event) {
        var select = $(this).prev('select')[0];
        var me = $(this);
        if (select !== null && select !== undefined) {
            if (me.data('state') === 'enabled') {
                STUDIP.Raumzeit.disableBookableRooms(me);
            } else {
                if (me.data('options') === undefined) {
                    me.data('options', $(select).children('option').clone(true));
                } else {
                    $(select).empty().append(me.data('options').clone(true));
                }
                $.ajax({
                    type: 'POST',
                    url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/resources/helpers/bookable_rooms',
                    data: {
                        rooms: _.pluck(select.options, 'value'),
                        selected_dates : _.pluck($('input[name="singledate[]"]:checked'), 'value'),
                        singleDateID: $('input[name=singleDateID]').attr('value'),
                        new_date: _.map($('#startDate,#start_stunde,#start_minute,#end_stunde,#end_minute'), function (v) {
                            return { name: v.id, value: v.value };
                        })
                    },
                    success: function (result) {
                        if ($.isArray(result)) {
                            if (result.length) {
                                var not_bookable_rooms = _.map(result, function (v) {
                                    return $(select).children('option[value=' + v + ']').text().trim();
                                });
                                select.title = 'Nicht buchbare R�ume:'.toLocaleString() + ' ' + not_bookable_rooms.join(', ');
                            } else {
                                select.title = '';
                            }
                            _.each(result, function (v) {
                                $(select).children('option[value=' + v + ']').prop('disabled', true);
                            });
                        } else {
                            select.title = '';
                        }
                        me.attr('title', 'Alle R�ume anzeigen'.toLocaleString());
                        me.data('state', 'enabled');
                    }
                });
            }
        }
        event.preventDefault();
    });
    $('a.bookable_rooms_action').show();

    $(document).on('change', 'input[name="singledate[]"]', function () {
        STUDIP.Raumzeit.disableBookableRooms($('a.bookable_rooms_action'));
    });
});

STUDIP.Raumzeit = {
    toggleCheckboxes: function (cycle_id) {
        var checked = false;
        jQuery('table[data-cycleid=' + cycle_id + '] input[name^=singledate]').each(function () {
            if (jQuery(this).prop('checked')) {
                checked = true;
            }
        });

        jQuery('table[data-cycleid=' + cycle_id + '] input[name*=singledate]').prop('checked', !checked);
    },

    addLecturer: function () {
        jQuery('select[name=teachers] option:selected').each(function () {
            var lecturer_id = jQuery(this).val();
            if (lecturer_id === 'none') {
                return;
            }

            jQuery('li[data-lecturerid=' + lecturer_id + ']').show();
            jQuery('select[name=teachers] option[value=' + lecturer_id + ']').hide();
            jQuery('select[name=teachers] option[value=none]').prop('selected', true);
        });

        STUDIP.Raumzeit.addFormLecturers();
    },

    removeLecturer: function (lecturer_id) {
        if (jQuery('ul.teachers li:visible').size() > 1) {
            jQuery('li[data-lecturerid=' + lecturer_id + ']').hide();
            //jQuery('li[data-lecturerid=' + lecturer_id + '] input').val('0');
            jQuery('select[name=teachers] option[value=' + lecturer_id + ']').show();
        } else {
            if (jQuery('div.at_least_one_teacher').size() === 0) {
                jQuery('ul.teachers').before('<div class="at_least_one_teacher" style="display: none"><i>' + 'Jeder Termin muss mindestens eine Person haben, die ihn durchf�hrt!'.toLocaleString() + '</i><div>');
                jQuery('div.at_least_one_teacher').slideDown().delay(3000).fadeOut(400, function () {
                    jQuery(this).remove();
                });
                jQuery('li[data-lecturerid=' + lecturer_id + ']').effect('shake', 100);
            }
        }

        STUDIP.Raumzeit.addFormLecturers();
    },

    addFormLecturers: function () {
        var data = [];

        jQuery('ul.teachers li:visible').each(function () {
            data.push(jQuery(this).data('lecturerid'));
        });

        jQuery('input[name=related_teachers]').val(data.join(','));
    },
    addFormGroups: function () {
        var data = [];
        jQuery('ul.groups li:visible').each(function () {
            data.push(jQuery(this).data('groupid'));
        });
        jQuery('input[name=related_statusgruppen]').val(data.join(','));
    },
    addGroup: function () {
        jQuery('select[name=groups] option:selected').each(function () {
            var statusgruppe_id = jQuery(this).val();
            if (statusgruppe_id === 'none') {
                return;
            }

            jQuery('li[data-groupid=' + statusgruppe_id + ']').show();
            jQuery('select[name=groups] option[value=' + statusgruppe_id + ']').hide();
            jQuery('select[name=groups] option[value=none]').prop('selected', true);
        });

        STUDIP.Raumzeit.addFormGroups();
    },

    removeGroup: function (statusgruppe_id) {
        jQuery('li[data-groupid=' + statusgruppe_id + ']').hide();
        jQuery('select[name=groups] option[value=' + statusgruppe_id + ']').show();

        STUDIP.Raumzeit.addFormGroups();
    },

    disableBookableRooms: function (icon) {
        var select = $(icon).prev('select')[0];
        var me = $(icon);
        select.title = '';
        $(select).children('option').each(function () {
            $(this).prop('disabled', false);
        });

        me.data('state', false);
        me.attr('title', 'Nur buchbare R�ume anzeigen'.toLocaleString());
    }
};
