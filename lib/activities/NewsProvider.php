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

        return $this->wrapNews($news, $context);
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

    private function getUrlForContext($news, $context)
    {
        if ($context instanceof CourseContext) {
            return array(
                \URLHelper::getUrl('dispatch.php/course/details/?sem_id=' . $context->getSeminarId()) => _('News im Kurs')
            );
        }

        else if ($context instanceof InstituteContext) {
            return array(
                \URLHelper::getUrl('dispatch.php/institute/overview?auswahl=' . $context->getInstituteId()) => _('News in der Einrichtung')
            );
        }

        else if ($context instanceof SystemContext) {
            return array(
                \URLHelper::getUrl('dispatch.php/start?contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('News auf der Startseite')
            );
        }

        else if ($context instanceof UserContext) {
            #$range_id = $context->getUserId();
            return array(
                \URLHelper::getUrl('dispatch.php/profile?contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('News auf der Profilseite')
            );
        }
    }

    private function wrapNews($news, $context)
    {
        return array_map(function ($n) use ($context) {
            $description = array(
                'title'   => sprintf(_("%s hat eine Ankündigung geschrieben."), $n->author),
                'content' => formatReady($n->body)
            );

            return new Activity(
                'news_provider',
                $description,                           // the description and summaray of the performed activity
                'user',                                 // who initiated the activity?
                $n->user_id,                            // id of initiator
                'created',                              // the type if the activity
                'news',                                 // type of activity object
                $this->getUrlForContext($n, $context),  // url to entity in Stud.IP
                'http://example.com/route',             // url to entity as rest-route
                $n->mkdate
            );
        }, $news);
    }
}
