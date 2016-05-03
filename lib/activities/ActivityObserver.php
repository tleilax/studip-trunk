<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
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
        \NotificationCenter::addObserver('\Studip\Activity\NewsProvider', 'postActivity','NewsDidUpdate');

        //Notifications for WikiProvider
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidCreate');
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidDelete');
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidUpdate');

        //Notifications for ScheduleProvider (Course)
        \NotificationCenter::addObserver('\Studip\Activity\ScheduleProvider', 'postActivity','CourseDidChangeSchedule');
    }
}
