<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

namespace Studip\Activity;

class WikiProvider implements ActivityProvider
{


    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails(&$activity)
    {


        $activity->content = $activity->content;

        $url = \URLHelper::getUrl("wiki.php?cid={$activity->context_id}&keyword={$activity->object_id}");
        $route = \URLHelper::getURL('api.php/course/' . $activity->context_id . '/wiki/' . $activity->object_id, NULL, true);

        $activity->object_url = array(
            $url => _('Zum Wiki der Veranstaltung')
        );

        $activity->object_route = $route;
    }


    public function postActivity($event, $info)
    {

        $range_id = $info[0];
        $keyword = $info[1];

        $type     = get_object_type($range_id);
        if($type == 'sem') {
            $course = \Course::find($range_id);
        }


        $user_id = $GLOBALS['user']->id;
        $mkdate = strtotime('now');

        if($event == 'WikiPageDidCreate') {
            $verb = 'created';
            $summary = _('Die WikiSeite %s wurde von %s in der Veranstaltung "%s" angelegt.');
            $summary = sprintf($summary,$keyword, get_fullname($user_id), $course->name);

        } elseif($event == 'WikiPageDidUpdate') {
            $verb = 'edited';
            $summary = _('Die WikiSeite %s wurde von %s  in der Veranstaltung "%s" aktualisiert.');
            $summary = sprintf($summary,$keyword, get_fullname($user_id), $course->name);
        } elseif($event == 'WikiPageDidDelete') {
            $verb = 'voided';
            $summary = _('Die WikiSeite %s wurde von %s in der Veranstaltung "%s" gel�scht.');
            $summary = sprintf($summary,$keyword, get_fullname($user_id), $course->name);
        }



        $activity = Activity::get(
            array(
                'provider'     => 'wiki',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $range_id,
                'content'      => $summary,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $user_id,                                     // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $keyword,                                   // the id of the referenced object
                'object_type'  => 'wiki',                                  // type of activity object
                'mkdate'       =>  $mkdate
            )
        );

        $activity->store();
    }
}
