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
class ForumProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the acitivty to fill with details, passed by reference
     */
    public static function getActivityDetails(&$activity)
    {
        if ($activity->provider != 'forum_provider') {
            throw new InvalidArgumentException('the passed activity is not a forum activity!');
        }

        $post = ForumEntry::getEntry($activity->object_id);

        $activity->content = $post['content'];

        $url = PluginEngine::getURL('CoreForum', array(), 'index/index/' . $post['topic_id']
                    .'?cid='. $post['seminar_id'] .'&highlight_topic='. $post['topic_id']
                    .'#'. $post['topic_id']);

        $route = URLHelper::getURL('api.php/forum_entry/' . $post['topic_id'], NULL, true);

        $activity->object_url = array(
            $url => _('Zum Forum der Veranstaltung')
        );

        $activity->object_route = $route;
    }
}
