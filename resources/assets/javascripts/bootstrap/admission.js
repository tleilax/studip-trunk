/* ------------------------------------------------------------------------
 * Anmeldeverfahren und -sets
 * ------------------------------------------------------------------------ */

jQuery(document).ready(function($) {
    $(document).on('change', 'tr.course input', function(i) {
        STUDIP.Admission.toggleNotSavedAlert();
    });

    $('a.userlist-delete-user').on('click', function(event) {
        $(this)
            .closest('tr')
            .remove();
        return false;
    });
});
