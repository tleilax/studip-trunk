STUDIP.domReady(() => {
    // Test if the header is actually present
    if ($('#barBottomContainer').length > 0) {
        STUDIP.HeaderMagic.enable();
    }
});
$(document).on('click', '#avatar-arrow', function (event) {
    event.stopPropagation();
    $('#header_avatar_menu .action-menu-icon').trigger('mousedown');
});
