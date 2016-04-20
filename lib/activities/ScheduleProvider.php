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

class ScheduleProvider implements ActivityProvider
{


    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails(&$activity)
    {


        $activity->content = $activity->content;

        //todo fix url and route
        $url = \URLHelper::getUrl("dispatch.php/course/dates?cid={$activity->context_id}");
        $route = \URLHelper::getURL('api.php/course/' . $activity->context_id . '/events', NULL, true);

        $activity->object_url = array(
            $url => _('Zum Ablaufplan der Veranstaltung')
        );

        $activity->object_route = $route;
    }


    public function postActivity($event, $info)
    {

        $info = $info->toArray();

        $range_id = $info['seminar_id'];

        //todo info to store in acitvity

        $type     = get_object_type($range_id);
        if($type == 'sem') {
            $course = \Course::find($range_id);
        }


        $user_id = $GLOBALS['user']->id;
        $mkdate = strtotime('now');

       if($event == 'CourseDidChangeSchedule') {
            $verb = 'edited';
            $summary = _('Der Ablaufplan in der Veranstaltung "%s" von %s aktualisiert.');
            $summary = sprintf($summary, $course->name, get_fullname($user_id));
        }



        $activity = Activity::get(
            array(
                'provider'     => 'schedule',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $range_id,
                'content'      => $summary,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $user_id,                                     // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $range_id,                                   // the id of the referenced object
                'object_type'  => 'schedule',                                  // type of activity object
                'mkdate'       =>  $mkdate
            )
        );

        $activity->store();
    }
}
