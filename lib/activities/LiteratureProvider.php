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

class LiteratureProvider implements ActivityProvider
{
    public function getActivities($observer_id, Context $context, Filter $filter) {

        $range_id = $this->contextToRangeId($context);

        if (!\StudipNews::haveRangePermission('view', $range_id, $observer_id)) {
            return array();
        }

        //TODO: that shouldn't be fix
        $now = time();
        $chdate = $now - 24 * 60 * 60 * 260;

        $course = \Course::find($range_id);
        $sem_class = $course->getSemClass();
        $module = $sem_class->getModule('literature');
        $notifications = $module->getNotificationObjects($range_id, $chdate, $observer_id);

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
            return new Activity('participants_provider', $n->getSummary(), 'user', $n->getDate(), 'created', 'participants', $n->getUrl(), 'http://example.com/route', $n->getDate());
        }, $notifications);

    }
}
