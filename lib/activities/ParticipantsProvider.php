<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class ParticipantsProvider implements ActivityProvider
{

    public function postActivity($event, $course_id, $user_id)
    {

        $course = \Course::find($course_id);

        if($event == 'UserDidEnterCourse') {
            $verb = 'created';
            $summary = _('%s wurde in die Veranstaltung "%s" eingetragen.');
            $summary = sprintf($summary, get_fullname($user_id), $course->name);
        } elseif($event == 'UserDidLeaveCourse') {
            $verb = 'experienced';
            $summary = _('%s wurde aus der Veranstaltung "%s" ausgetragen.');
            $summary = sprintf($summary, get_fullname($user_id), $course->name);
        } elseif ($event = "CourseDidGetMember") {
            $verb = 'experienced';
            $summary = _('%s wurde in die Veranstaltung "%s" eingetragen.');
            $summary = sprintf($summary, get_fullname($user_id), $course->name);
        }

        $type     = get_object_type($course_id);

        $activity = Activity::get(
            array(
                'provider'     => 'participants',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $course_id,
                'content'      => $summary,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $user_id,                                     // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $course_id,                                   // the id of the referenced object
                'object_type'  => 'participants',                               // type of activity object
                'mkdate'       =>  strtotime('now')
            )
        );

        $activity->store();
    }


    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails(&$activity)
    {


        $activity->content = $activity->content;

        $url = \URLHelper::getUrl("dispatch.php/course/members/index?cid=/{$activity->context_id}", array('cid' => null));

        $route = \URLHelper::getURL('api.php/course/' . $activity->context_id, NULL, true);

        $activity->object_url = array(
            $url => _('Zur Veranstaltung')
        );

        $activity->object_route = $route;
    }

    public static function getLexicalField()
    {
        return _('eine/n Teilnehmer/in');
    }

}


