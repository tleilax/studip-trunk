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

class ActivityObserver
{
    public static function initialize()
    {
        NotificationCenter::addObserver('Studip\Activity\MessageProvider', 'postActivity','MessageDidSend');
        NotificationCenter::addObserver('Studip\Activity\BlubberProvider', 'postActivity', 'PostingHasSaved');

        // Notifications for ParticipantsProvider
        NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','UserDidEnterCourse');
        NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','UserDidLeaveCourse');
        NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','CourseDidGetMember');

    }
}
