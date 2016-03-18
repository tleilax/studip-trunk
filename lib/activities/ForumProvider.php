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

class ForumProvider implements ActivityProvider
{
    public function getActivities($observer_id, Context $context, Filter $filter)
    {
        // TODO: forum provider should only react to contextes where a forum is allowed
        if ($course = \Course::find($context->getRangeId())) {
            $sem_class = $course->getSemClass();
            $module = $sem_class->getModule('forum');
            return $module->getActivityObjects($range_id, $observer_id, $filter);
        }

        return array();
    }
}
