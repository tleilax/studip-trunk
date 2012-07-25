/* ------------------------------------------------------------------------
 * calendar gui
 * ------------------------------------------------------------------------ */
STUDIP.Calendar = {
    cell_height: 20,
    the_entry_content: null,
    entry: null,
    click_start_hour: -1,
    click_entry: null,
    click_in_progress: false,

    day_names: [
        "Montag",
        "Dienstag",
        "Mittwoch",
        "Donnerstag",
        "Freitag",
        "Samstag",
        "Sonntag"
    ],

    /**
     * this function is called, whenever an existing entry in the
     * calendar is clicked. It calls the passed function with the
     * calculcate id of the clicked element
     *
     * @param  object  a function or a reference to a function
     * @param  object  the element in the dom, that has been clicked
     * @param  object  the click-event itself
     */
    clickEngine: function (func, target, event) {
        event.cancelBubble = true;
        var id = jQuery(target).parent()[0].id;
        id = id.substr(id.lastIndexOf("_") + 1);
        func(id);
    },


    /**
     * check, that the submited input-field cotains of a valid hour
     *
     * @param  object  the input-element to check
     */
    validateHour: function (element) {
        var hour = parseInt(jQuery(element).val(), 10);

        if (hour > 23) {
            hour = 23;
        }
        if (hour < 0 || isNaN(hour)) {
            hour = 0;
        }

        jQuery(element).val(hour);
    },

    /**
     * check, that the submited input-field cotains of a valid minute
     *
     * @param  object  the input-element to check
     */
    validateMinute: function (element) {
        var minute = parseInt(jQuery(element).val(), 10);

        if (minute > 59) {
            minute = 59;
        }
        if (minute < 0 || isNaN(minute)) {
            minute = 0;
        }

        jQuery(element).val(minute);
    },

    /**
     * check, that the submitted input-fields contain a valid time-range
     *
     * @param  object  the input-element to check (start-hour)
     * @param  object  the input-element to check (start-minute)
     * @param  object  the input-element to check (end-hour)
     * @param  object  the input-element to check (end-minute)
     *
     * @return: bool true if valid time-range, false otherwise
     */
    checkTimeslot: function (start_hour, start_minute, end_hour, end_minute) {
        if ((parseInt(start_hour.val(), 10) * 100) + parseInt(start_minute.val(), 10) >=
            (parseInt(end_hour.val(), 10) * 100) + parseInt(end_minute.val(), 10)) {
            return false;
        }

        return true;
    }
};
