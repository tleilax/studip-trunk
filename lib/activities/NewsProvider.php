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
    private function getUrlForContext($news, $context)
    {
        switch ($context) {
            case 'course':
                return array(
                    \URLHelper::getUrl('dispatch.php/course/details/?sem_id=' . $context->getSeminarId()) => _('News im Kurs')
                );
            break;

            case 'institute':
                return array(
                    \URLHelper::getUrl('dispatch.php/institute/overview?auswahl=' . $context->getInstituteId()) => _('News in der Einrichtung')
                );
            break;

            case 'system':
                return array(
                    \URLHelper::getUrl('dispatch.php/start?contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('News auf der Startseite')
                );
            break;

            case 'user':
                return array(
                    \URLHelper::getUrl('dispatch.php/profile?contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('News auf der Profilseite')
                );
            break;
        }
    }

    public function postActivity($event, $news_id)
    {
        $news = new \StudipNews($news_id);

        // var_dump($news->news_ranges);die;

        foreach ($news->news_ranges as $range) {
            #var_dump($range->toArray());

            switch ($range->type) {
                case 'user':   $context = 'user';break;
                case 'inst':   $context = 'institute';break;
                case 'sem':    $context = 'course';break;
                case 'global': $context = 'system';break;
            }

            $context_id = $range->range_id;

            $activity = Activity::get(
                array(
                    'provider'     => 'news',
                    'context'      => 'system',
                    'context_id'   => 'system',
                    'content'      => NULL,
                    'actor_type'   => 'user',                                       // who initiated the activity?
                    'actor_id'     => $news['user_id'],                             // id of initiator
                    'verb'         => 'created',                                    // the activity type
                    'object_id'    => $news->id,                                     // the id of the referenced object
                    'object_type'  => 'news',                                       // type of activity object
                    'mkdate'       => $mkdate
                )
            );

            $activity->store();
        }
    }

    public function getActivityDetails(&$activity)
    {
        $news = new \StudipNews($activity->object_id);

        $activity->content = '<b>' . htmlReady($news->topic)
            .'</b><br>'. formatReady($news->body);

        $url = self::getUrlForContext($news, $activity->context);
        $route = \URLHelper::getURL('api.php/news/' . $news->id, NULL, true);

        $activity->object_url = $url;
        $activity->object_route = $route;
    }

    public static function getLexicalField()
    {
        return _('eine Neuigkeit');
    }

}
