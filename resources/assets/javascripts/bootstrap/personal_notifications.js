$(document).on('click', '#notification_list .mark_as_read', STUDIP.PersonalNotifications.markAsRead);
$(document).on('mouseenter', '#notification_list', STUDIP.PersonalNotifications.setSeen);

$(document).ready(function() {
    STUDIP.PersonalNotifications.initialize();
    $('#notification_container .mark-all-as-read').click(STUDIP.PersonalNotifications.markAllAsRead);
});
