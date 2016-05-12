<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class LiteratureProvider implements ActivityProvider
{


    public function getActivityDetails(&$activity)
    {
        if($activity->context == "course") {

            $url = \URLHelper::getUrl("dispatch.php/course/literature?cid={$activity->context_id}&view=literatur_sem");
            $route = null;

            $activity->object_url = array(
                $url => _('Zur Literatur der Veranstaltung')
            );

            $activity->object_route = $route;

        } elseif($activity->context == "institute") {
            $url = \URLHelper::getUrl("dispatch.php/course/literature?cid={$activity->context_id}&view=literatur_sem");
            $route= null;

            $activity->object_url = array(
                $url => _('Zur Literatur der Einrichtung')
            );

            $activity->object_route = $route;

        }
        
    }

    public function postActivity($event, $info)
    {

        $range_id = $info['range_id'];
        $name = $info['name'];
        $type     = get_object_type($range_id);
        $user_id = $GLOBALS['user']->id;
        $mkdate = strtotime('now');


        if($type == 'sem') {
            $course = \Course::find($range_id);
        } else {
            $course = \Institute::find($range_id);
        }

        $context_clean = ($type == 'sem') ? _("Veranstaltung") : _("Einrichtung");


        if($event == 'LitListDidUpdate') {
            $verb = 'edited';
            $summary = _('Die Literaturliste %s wurde von %s in der %s "%s" geändert.');
        } elseif($event == 'LitListDidInsert') {
            $verb = 'created';
            $summary = _('Die Literaturliste %s wurde von %s in der %s "%s" erstellt.');
        } elseif($event == 'LitListDidDelete') {
            $verb = 'voided';
            $summary = _('Die Literaturliste %s wurde von %s in der %s "%s" entfernt.');
        } elseif($event == 'LitListElementDidUpdate') {
            $verb = 'edited';
            $summary = _('Es wurde %s von %s in eine Literaturliste in der %s "%s" geändert.');
        } elseif($event == 'LitListElementDidInsert') {
            $verb = 'created';
            $summary = _('Es wurde %s von %s in eine Literaturliste der %s "%s" erstellt.');
        } elseif($event == 'LitListElementDidDelete') {
            $verb = 'voided';
            $summary = _('Es wurde %s von %s aus einer Literaturliste in der %s "%s" entfernt.');
        }

        $summary = sprintf($summary, $name, get_fullname($user_id), $context_clean , $course->name);

        $activity = Activity::get(
            array(
                'provider'     => 'literature',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $range_id,
                'content'      => $summary,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $user_id,                                     // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $name,                                        // the id of the referenced object
                'object_type'  => 'literaturelist',                             // type of activity object
                'mkdate'       =>  $mkdate
            )
        );

        $activity->store();
    }


    public static function getLexicalField()
    {
        _('einen Literatureintrag');
    }

}
