<?php

/**
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class NewsProvider implements ActivityProvider
{
    private function getUrlForContext($news, $activity)
    {
        switch ($activity->context) {
            case 'course':
                return array(
                    \URLHelper::getUrl('dispatch.php/course/details/?sem_id=' . $activity->object_id) => _('News im Kurs')
                );
            break;

            case 'institute':
                return array(
                    \URLHelper::getUrl('dispatch.php/institute/overview?auswahl=' . $activity->object_id) => _('News in der Einrichtung')
                );
            break;

            case 'system':
                return array(
                    \URLHelper::getUrl('dispatch.php/start?contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('News auf der Startseite')
                );
            break;

            case 'user':
                return array(
                    \URLHelper::getUrl('dispatch.php/profile/?username='. get_username($activity->object_id)
                        . '&contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('News auf der Profilseite')
                );
            break;
        }
    }

    public function postActivity($event, $news_id)
    {
        $news = new \StudipNews($news_id);

        // delete any old activities for this id
        $activities = Activity::findBySql('object_id = ?', array($news->id));

        foreach ($activities as $activity) {
            $activity->delete();
        }

        // iterate over every news-range and create approbriate activity
        foreach ($news->news_ranges as $range) {
            $context_id = $range->range_id;

            switch ($range->type) {
                case 'user':   $context = 'user';break;
                case 'inst':   $context = 'institute';break;
                case 'sem':    $context = 'course';break;
                case 'global': $context = 'system'; $context_id = 'system';break;
            }

            $activity = Activity::get(
                array(
                    'provider'     => 'news',
                    'context'      => $context,
                    'context_id'   => $context_id,
                    'content'      => NULL,
                    'actor_type'   => 'user',                                   // who initiated the activity?
                    'actor_id'     => $news->user_id,                           // id of initiator
                    'verb'         => 'created',                                // the activity type
                    'object_id'    => $news->id,                                // the id of the referenced object
                    'object_type'  => 'news',                                   // type of activity object
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

        $url = self::getUrlForContext($news, $activity);
        $route = \URLHelper::getURL('api.php/news/' . $news->id, NULL, true);

        $activity->object_url = $url;
        $activity->object_route = $route;
    }

    public static function getLexicalField()
    {
        return _('eine Neuigkeit');
    }

}
