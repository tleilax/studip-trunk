<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

namespace Studip\Activity;

class ActivityObserver
{
    public static function initialize()
    {
        \NotificationCenter::addObserver('Studip\Activity\MessageProvider', 'postActivity','MessageDidSend');
        \NotificationCenter::addObserver('Studip\Activity\BlubberProvider', 'postActivity', 'PostingHasSaved');

        // Notifications for ParticipantsProvider
        \NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','UserDidEnterCourse');
        \NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','UserDidLeaveCourse');
        \NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','CourseDidGetMember');

        //Notifications for DocumentsProvider
        \NotificationCenter::addObserver('\Studip\Activity\DocumentsProvider', 'postActivity','DocumentDidCreate');
        \NotificationCenter::addObserver('\Studip\Activity\DocumentsProvider', 'postActivity','DocumentDidUpdate');
        \NotificationCenter::addObserver('\Studip\Activity\DocumentsProvider', 'postActivity','DocumentDidDelete');

        //Notifications for NewsProvider
        \NotificationCenter::addObserver('\Studip\Activity\NewsProvider', 'postActivity','NewsDidCreate');

        //Notifications for WikiProvider
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidCreate');
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidDelete');
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidUpdate');

        //Notifications for ScheduleProvider (Course)
        \NotificationCenter::addObserver('\Studip\Activity\ScheduleProvider', 'postActivity','CourseDidChangeSchedule');
    }
}
