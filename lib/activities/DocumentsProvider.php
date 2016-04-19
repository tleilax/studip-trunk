<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

namespace Studip\Activity;

class DocumentsProvider implements ActivityProvider
{

    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails(&$activity)
    {


        $activity->content = $activity->content;


        //TODO Fix URLs
        $url = \URLHelper::getUrl("dispatch.php/course/members/index?cid=/{$activity->context_id}", array('cid' => null));
        $route = \URLHelper::getURL('api.php/course/' . $activity->context_id, NULL, true);

        $activity->object_url = array(
            $url => _('Zur Veranstaltung')
        );

        $activity->object_route = $route;
    }


    public function postActivity($event, $document)
    {

        $document_info = $document->toArray();
        
        $user_id = $document_info['user_id'];
        $file_name = $document_info['name'];
        $course_id = $document_info['seminar_id'];

        $course = \Course::find($course_id);

        if($event == 'DocumentDidCreate') {
            $verb = 'created';
            $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" hochgeladen.');
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $course->name);
            $mkdate = $document_info['mkdate'];
        } elseif($event == 'DocumentDidUpdate') {
            $verb = 'edited';
            $summary = _('Die Datei %s wurde von %s  in der Veranstaltung "%s" aktualisiert.');
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $course->name);
            $mkdate = $document_info['chdate'];
        } elseif($event == 'DocumentDidDelete') {
            $verb = 'voided';
            $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" gelöscht.');
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $course->name);
            $mkdate = $document_info['chdate'];
        }

        $type     = get_object_type($course_id);

        $activity = Activity::get(
            array(
                'provider'     => 'documents',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $course_id,
                'content'      => $summary,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $user_id,                                     // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $course_id,                                   // the id of the referenced object
                'object_type'  => 'documents',                                  // type of activity object
                'mkdate'       =>  $mkdate
            )
        );

        $activity->store();
    }

}
