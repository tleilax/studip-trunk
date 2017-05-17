<?php

/**
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class LiteratureProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $activity->content = \htmlReady($activity->content);

        if ($activity->context == "course") {

            $url = \URLHelper::getUrl("dispatch.php/course/literature?cid={$activity->context_id}&view=literatur_sem");
            $route = null;

            $activity->object_url = array(
                $url => _('Zur Literatur der Veranstaltung')
            );

            $activity->object_route = $route;

        } elseif ($activity->context == "institute") {
            $url = \URLHelper::getUrl("dispatch.php/course/literature?cid={$activity->context_id}&view=literatur_sem");
            $route= null;

            $activity->object_url = array(
                $url => _('Zur Literatur der Einrichtung')
            );

            $activity->object_route = $route;
        }

        return true;
    }


    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param Array  $info information which a relevant for the activity
     */
    public function postActivity($event, $info)
    {
        $range_id = $info['range_id'];
        $name = $info['name'];
        $type = get_object_type($range_id);
        $user_id = $GLOBALS['user']->id;
        $mkdate = time();

        if ($type == 'sem') {
            $course = \Course::find($range_id);
        } else {
            $course = \Institute::find($range_id);
        }

        if ($event == 'LitListDidUpdate') {
            $verb = 'edited';
            if ($type == 'sem') {
                $summary = _('Die Literaturliste %s wurde von %s in der Veranstaltung "%s" ge�ndert.');
            } else {
                $summary = _('Die Literaturliste %s wurde von %s in der Einrichtung "%s" ge�ndert.');
            }
        } elseif ($event == 'LitListDidCreate') {
            $verb = 'created';
            if ($type == 'sem') {
                $summary = _('Die Literaturliste %s wurde von %s in der Veranstaltung "%s" erstellt.');
            } else {
                $summary = _('Die Literaturliste %s wurde von %s in der Einrichtung "%s" erstellt.');
            }
        } elseif ($event == 'LitListDidDelete') {
            $verb = 'voided';
            if ($type == 'sem') {
                $summary = _('Die Literaturliste %s wurde von %s in der Veranstaltung "%s" entfernt.');
            } else {
                $summary = _('Die Literaturliste %s wurde von %s in der Einrichtung "%s" entfernt.');
            }
        } elseif ($event == 'LitListElementDidUpdate') {
            $verb = 'edited';
            if ($type == 'sem') {
                $summary = _('Es wurde %s von %s in eine Literaturliste in der Veranstaltung "%s" ge�ndert.');
            } else {
                $summary = _('Es wurde %s von %s in eine Literaturliste in der Einrichtung "%s" ge�ndert.');
            }
        } elseif ($event == 'LitListElementDidInsert') {
            $verb = 'created';
            if ($type == 'sem') {
                $summary = _('Es wurde %s von %s in eine Literaturliste in der Veranstaltung "%s" erstellt.');
            } else {
                $summary = _('Es wurde %s von %s in eine Literaturliste in der Einrichtung "%s" erstellt.');
            }
        } elseif ($event == 'LitListElementDidDelete') {
            $verb = 'voided';
            if ($type == 'sem') {
                $summary = _('Es wurde %s von %s aus einer Literaturliste in der Veranstaltung "%s" entfernt.');
            } else {
                $summary = _('Es wurde %s von %s aus einer Literaturliste in der Einrichtung "%s" entfernt.');
            }
        }

        $summary = sprintf($summary, $name, get_fullname($user_id), $course->name);

        if (isset($verb)) {
            $activity = Activity::create(
                array(
                    'provider'     => __CLASS__,
                    'context'      => ($type == 'sem') ? 'course' : 'institute',
                    'context_id'   => $range_id,
                    'content'      => $summary,
                    'actor_type'   => 'user',           // who initiated the activity?
                    'actor_id'     => $user_id,         // id of initiator
                    'verb'         => $verb,            // the activity type
                    'object_id'    => $name,            // the id of the referenced object
                    'object_type'  => 'literaturelist', // type of activity object
                    'mkdate'       =>  $mkdate
                )
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('einen Literatureintrag');
    }

}
