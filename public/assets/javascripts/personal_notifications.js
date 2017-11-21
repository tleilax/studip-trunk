/*jslint browser: true, nomen: true, unparam: true, newcap: true */
/*global Notification, jQuery, STUDIP, _, Notificon */

(function ($) {
    'use strict';

    var stack = {},
        originalTitle,
        favicon_url,
        audio_notification = false,
        directlydeleted = [];

    // Wrapper function that creates a desktop notification from given data
    function create_desktop_notification(data) {
        var notification = new Notification(STUDIP.STUDIP_SHORT_NAME, {
            body: data.text,
            icon: data.avatar,
            tag: data.id
        });
        notification.addEventListener('click', function () {
            location.href = STUDIP.URLHelper.getURL('dispatch.php/jsupdater/mark_notification_read/' + this.tag);
        });
    }

    // Handler for all notifications received by an ajax request
    function process_notifications(notifications) {
        var cache = STUDIP.Cache.getInstance('desktop.notifications'),
            ul        = $('<ul/>'),
            changed   = false,
            new_stack = {};

        $.each(notifications, function (index, notification) {
            if ($.inArray(notification.personal_notification_id, directlydeleted) === -1) {
                ul.append(notification.html);

                var id = $('.notification:last', ul).data().id;
                new_stack[id] = notification;
                if (notification.html_id) {
                    $("#" + notification.html_id).on("mouseenter", STUDIP.PersonalNotifications.isVisited);
                }

                changed = (changed || !stack.hasOwnProperty(id));

                // Check if notifications should be sent (depends on the
                // Notification itself and session storage)
                if (!window.hasOwnProperty('Notification') || Notification.permission === 'denied' || cache.has(notification.id)) {
                    return;
                }

                // If it's okay let's create a notification
                if (Notification.permission === 'granted') {
                    create_desktop_notification(notification);
                } else {
                    Notification.requestPermission(function (permission) {
                        if (permission === 'granted') {
                            create_desktop_notification(notification);
                        }
                    });
                }

                cache.set(id, true);
            }
        });

        if (changed || _.values(stack).length !== _.values(new_stack).length) {
            stack = new_stack;
            $('#notification_list > ul').replaceWith(ul);
        }
        STUDIP.PersonalNotifications.update();
        directlydeleted = [];
    }

    STUDIP.PersonalNotifications = {
        newNotifications: function () {},
        markAsRead: function (event) {
            var notification = $(this).closest('.notification'),
                id = notification.data().id;
            STUDIP.PersonalNotifications.sendReadInfo(id, notification);
            return false;
        },
        markAllAsRead: function (event) {
            var notifications = $(this).parent().find('.notification');
            STUDIP.PersonalNotifications.sendReadInfo('all', notifications);
            return false;
        },
        sendReadInfo: function (id, notification) {
            $.get(STUDIP.URLHelper.getURL('dispatch.php/jsupdater/mark_notification_read/' + id))
                .done(function () {
                    if (notification) {
                        var count = notification.length;
                        notification.toggle('blind', 'fast', function () {
                            var data = $(this).data();
                            delete stack[data.id];
                            $(this).remove();

                            count -= 1;
                            if (count === 0) {
                                STUDIP.PersonalNotifications.update();
                            }
                        });
                    }
                });
        },
        update: function () {
            var count      = _.values(stack).length,
                old_count  = parseInt($('#notification_marker').text(), 10),
                really_new = 0;
            $('#notification_list > ul > li').each(function () {
                if (parseInt($(this).data("timestamp"), 10) > parseInt($('#notification_marker').data("lastvisit"), 10)) {
                    really_new += 1;
                }
            });
            if (really_new > 0) {
                $("#notification_marker").data('seen', false).addClass("alert");
                window.document.title = "(!) " + originalTitle;
            } else {
                $("#notification_marker").removeClass("alert");
                window.document.title = originalTitle;
            }
            if (count) {
                $("#notification_container").addClass("hoverable");
                if (count > old_count && audio_notification !== false) {
                    audio_notification.play();
                }
            } else {
                $("#notification_container").removeClass("hoverable");
            }
            if (old_count !== count) {
                $('#notification_marker').text(count);
                Notificon(count || '', {favicon: favicon_url});
                $('#notification_container .mark-all-as-read').toggleClass('hidden', count < 2);
            }
        },
        isVisited: function () {
            var id = this.id;
            $.each(stack, function (index, notification) {
                if (notification.html_id === id) {
                    STUDIP.PersonalNotifications.sendReadInfo(notification.personal_notification_id);
                    delete stack[index];
                    jQuery(".notification[data-id=" + notification.personal_notification_id + "]")
                        .fadeOut(function () { jQuery(this).remove(); });
                    directlydeleted.push(notification.personal_notification_id);
                    STUDIP.PersonalNotifications.update();
                }
            });
        },
        setSeen: function () {
            if ($('#notification_marker').data('seen')) {
                return;
            }
            $('#notification_marker').data('seen', true);

            $.get(STUDIP.URLHelper.getURL('dispatch.php/jsupdater/notifications_seen'))
                .then(function (time) {
                    $("#notification_marker").removeClass("alert").data("lastvisit", time);
                });
        }
    };

    $(document).on('click', '#notification_list .mark_as_read', STUDIP.PersonalNotifications.markAsRead);
    $(document).on('mouseenter', '#notification_list', STUDIP.PersonalNotifications.setSeen);

    $(document).ready(function () {
        if ($("#notification_marker").length > 0) {
            $('#notification_list .notification').map(function () {
                var data = $(this).data();
                stack[data.id] = data;
            });

            originalTitle = window.document.title;
            favicon_url = $('link[rel="shortcut icon"]').attr('href');
            STUDIP.PersonalNotifications.newNotifications = process_notifications;

            if ($('#audio_notification').length > 0) {
                audio_notification = $('#audio_notification').get(0);
                audio_notification.load();
            }
        }

        $('#notification_container .mark-all-as-read').click(STUDIP.PersonalNotifications.markAllAsRead);
    });

}(jQuery));
