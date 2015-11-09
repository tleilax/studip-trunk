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

class NewsProvider implements ActivityProvider
{
    public function getActivities($observer_id, Context $context, Filter $filter)
    {
        $range_id = $this->contextToRangeId($context);

        if (!\StudipNews::haveRangePermission('view', $range_id, $observer_id)) {
            return array();
        }

        $observer_may_edit = \StudipNews::haveRangePermission('edit', $range_id, $observer_id);

        $news = \StudipNews::GetNewsByRange($range_id, !$observer_may_edit, true);

        // TODO: hier muss irgendwo noch das maxage gefilter werden.

        return $this->wrapNews($news);
    }

    private function contextToRangeId(Context $context)
    {
        if ($context instanceof CourseContext) {
            $range_id = $context->getSeminarId();
        }

        else if ($context instanceof InstituteContext) {
            $range_id = $context->getInstituteId();
        }

        else if ($context instanceof SystemContext) {
            $range_id = 'studip';
        }

        else if ($context instanceof UserContext) {
            $range_id = $context->getUserId();
        }

        return $range_id;
    }


    private function wrapNews($news)
    {
        return array_map(function ($n) {
            $description = sprintf(_("%s hat eine Ankündigung geschrieben"), $n->author);
            return new Activity('news_provider', $description, 'user', $n->user_id, 'created', 'news', 'http://example.com/url', 'http://example.com/route', $n->mkdate);
        }, $news);
    }
}
