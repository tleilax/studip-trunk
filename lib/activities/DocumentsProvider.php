<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
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
        if($activity->context == "course") {
            $url = \URLHelper::getUrl("folder.php?cid={$activity->context_id}&cmd=tree&open={$activity->object_id}");
            $route = \URLHelper::getURL('api.php/file/' . $activity->object_id, NULL, true);

            $activity->object_url = array(
                $url => _('Zum Dateibereich der Veranstaltung')
            );



        } elseif($activity->context == "institute") {
            $url = \URLHelper::getUrl("folder.php?cid={$activity->context_id}&cmd=tree&open={$activity->object_id}");
            $route= null;

            $activity->object_url = array(
                $url => _('Zum Dateibereich der Einrichtung')
            );
        }

        $activity->object_route = $route;
    }


    public function postActivity($event, $document)
    {
        $document_info = $document->toArray();

        $user_id = $document_info['user_id'];
        $file_name = $document_info['name'];
        $course_id = $document_info['seminar_id'];
        $file_id = $document_info['dokument_id'];

        $type     = get_object_type($course_id);
        if($type == 'sem') {
            $course = \Course::find($course_id );
        } else {
            $course = \Institute::find($course_id );
        }


        $context_clean = ($type == 'sem') ? _("Veranstaltung") : _("Einrichtung");



        if($event == 'DocumentDidCreate') {
            $verb = 'created';
            $summary = _('Die Datei %s wurde von %s in der %s "%s" hochgeladen.');
            $summary = sprintf($summary,$file_name, get_fullname($user_id),$context_clean ,$course->name);
            $mkdate = $document_info['mkdate'];
        } elseif($event == 'DocumentDidUpdate') {
            $verb = 'edited';
            $summary = _('Die Datei %s wurde von %s  in der %s "%s" aktualisiert.');
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $context_clean ,$course->name);
            $mkdate = $document_info['chdate'];
        } elseif($event == 'DocumentDidDelete') {
            $verb = 'voided';
            $summary = _('Die Datei %s wurde von %s in der %s "%s" gelöscht.');
            $summary = sprintf($summary,$file_name, get_fullname($user_id),$context_clean ,$course->name);
            $mkdate = $document_info['chdate'];
        }


        $activity = Activity::get(
            array(
                'provider'     => 'documents',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $course_id,
                'content'      => $summary,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $user_id,                                     // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $file_id,                                   // the id of the referenced object
                'object_type'  => 'documents',                                  // type of activity object
                'mkdate'       =>  $mkdate
            )
        );

        $activity->store();
    }

    public static function getLexicalField()
    {
        return _('eine Datei');
    }

}
