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

class ForumProvider implements ActivityProvider
{
    public function getActivities($observer_id, Context $context, Filter $filter) {

        $range_id = $this->contextToRangeId($context);

        if (!\StudipNews::haveRangePermission('view', $range_id, $observer_id)) {
            return array();
        }

        $course = \Course::find($range_id);
        $sem_class = $course->getSemClass();
        $module = $sem_class->getModule('forum');
        $notifications = $module->getNotificationObjects($range_id, $filter->getMaxAge(), $observer_id);

        return $this->wrapParticipantNotifications($notifications);
    }

    private function contextToRangeId(Context $context){
        if ($context instanceof CourseContext) {
            $range_id = $context->getSeminarId();
        }

        else if ($context instanceof InstituteContext) {
            $range_id = $context->getInstituteId();
        }

        return $range_id;
    }

    private function  wrapParticipantNotifications($notifications){
        return array_map(function ($n) {
            return new Activity(
                'forum_provider',
                array(                                  // the description and summaray of the performed activity
                    'title' => $n->getSummary(),
                    'content' => $n->getContent()
                ),
                'user',                                 // who initiated the activity?
                $n->getCreatorid(),                     // id of initiator
                'created',                              // the type if the activity
                'forum',                                // type of activity object
                array(                                  // url to entity in Stud.IP
                    $n->getUrl() => _('Zum Eintrag springen und weiterlesen...')
                ),
                'http://example.com/route',             // url to entity as rest-route
                $n->getDate()
            );
        }, $notifications);

    }


}
