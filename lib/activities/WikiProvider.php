<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
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
        if($activity->context == "course") {
            $url = \URLHelper::getUrl("wiki.php?cid={$activity->context_id}&keyword={$activity->object_id}");
            $route = \URLHelper::getURL('api.php/course/' . $activity->context_id . '/wiki/' . $activity->object_id, NULL, true);

            $activity->object_url = array(
                $url => _('Zum Wiki der Veranstaltung')
            );

            $activity->object_route = $route;

        } elseif($activity->context == "institute") {
            $url = \URLHelper::getUrl("wiki.php?cid={$activity->context_id}&keyword={$activity->object_id}");
            $route= null;

            $activity->object_url = array(
                $url => _('Zum Wiki der Einrichtung')
            );

            $activity->object_route = $route;

        }
    }

    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param Array  $info information which a relevant for the activity
     */
    public function postActivity($event, $info)
    {

        $range_id = $info[0];
        $keyword = $info[1];

        $type     = get_object_type($range_id);
        if($type == 'sem') {
            $course = \Course::find($range_id);
        } else {
            $course = \Institute::find($range_id);
        }


        $user_id = $GLOBALS['user']->id;
        $mkdate = strtotime('now');
        $context_clean = ($type == 'sem') ? _("Veranstaltung") : _("Einrichtung");


        if($event == 'WikiPageDidCreate') {
            $verb = 'created';
            $summary = _('Die Wiki Seite %s wurde von %s in der %s "%s" angelegt.');
        } elseif($event == 'WikiPageDidUpdate') {
            $verb = 'edited';
            $summary = _('Die Wiki Seite %s wurde von %s  in der %s "%s" aktualisiert.');
        } elseif($event == 'WikiPageDidDelete') {
            $verb = 'voided';
            $summary = _('Die Wiki Seite %s wurde von %s in der %s "%s" gelöscht.');
        }

        $summary = sprintf($summary, $keyword, get_fullname($user_id), $context_clean , $course->name);
        

        $activity = Activity::get(
            array(
                'provider'     => 'wiki',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $range_id,
                'content'      => $summary,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $user_id,                                     // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $keyword,                                     // the id of the referenced object
                'object_type'  => 'wiki',                                       // type of activity object
                'mkdate'       =>  $mkdate
            )
        );

        $activity->store();
    }

    public static function getLexicalField()
    {
        return _('eine Wikiseite');
    }

}
