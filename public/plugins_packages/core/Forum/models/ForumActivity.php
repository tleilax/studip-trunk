<?php
/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class ForumActivity implements Studip\Activity\ActivityProvider
{
    /**
     * Post activity for new forum post
     *
     * @param type $topic_id
     * @param type $post
     */
    public static function newEntry($event, $topic_id, $post)
    {
        $verb = ($post['depth'] == 3)  ? 'answered' : 'created';

        if ($verb == 'created') {
            if ($post['depth'] == 1) {
                $summary = _('%s hat im Forum der Veranstaltung "%s" einen Bereich erstellt.');
            } else {
                $summary = _('%s hat im Forum der Veranstaltung "%s" ein Thema erstellt.');
            }
        } else {
            $summary = _('%s hat im Forum der Veranstaltung "%s" auf ein Thema geantwortet.');
        }

        self::post($post, $verb, $summary);
    }

    /**
     * Post activity for updating a forum post
     *
     * @param type $topic_id
     * @param type $post
     */
    public static function updateEntry($event, $topic_id, $post)
    {
        $summary = _('%s hat im Forum der Veranstaltung "%s" einen Beitrag editiert.');
        
        if ($post['user_id'] == $GLOBALS['user']->id) {
            $content = sprintf(_('%s hat seinen eigenen Beitrag vom %s editiert.'),
                    get_fullname($post['user_id']), date('d.m.y, H:i', $post['mkdate']));
        } else {
            $content = sprintf(_('%s hat den Beitrag von %s vom %s editiert.'),
                    get_fullname($post['user_id']), get_fullname($GLOBALS['user']->id),
                    date('d.m.y, H:i', $post['mkdate']));
        }

        self::post($post, 'edited', $summary, $content);
    }

    /**
     * Post activity for deleting a forum post
     *
     * @param type $topic_id
     * @param type $post
     */
    public static function deleteEntry($event, $topic_id, $post)
    {
        $summary = _('%s hat im Forum der Veranstaltung "%s" einen Beitrag gelöscht.');

        if ($post['user_id'] == $GLOBALS['user']->id) {
            $content = sprintf(_('%s hat seinen Beitrag vom %s gelöscht.'),
                    get_fullname($GLOBALS['user']->id),
                    date('d.m.y, H:i', $post['mkdate']));
        } else {
            $content = sprintf(_('%s hat den Beitrag von %s vom %s gelöscht.'),
                    get_fullname($post['user_id']), get_fullname($GLOBALS['user']->id),
                    date('d.m.y, H:i', $post['mkdate']));
        }

        self::post($post, 'deleted', $summary, $content);
    }

    private static function post($post, $verb, $summary, $content = null)
    {
        // skip system-created entries like "Allgemeine Diskussionen"
        if (!$post['user_id']) return;

        $range_id = $post['seminar_id'];
        $type     = get_object_type($range_id);

        $obj = get_object_name($range_id, $type);
        
        $url = PluginEngine::getURL('CoreForum', array(), 'index/index/' . $post['topic_id']
                    .'?cid='. $course_id .'&highlight_topic='. $post['topic_id']
                    .'#'. $post['topic_id']);

        $activity = Studip\Activity\Activity::get(
            array(
                'provider'     => 'CoreForum',
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'title'        => sprintf($summary, get_fullname($post['user_id']), $obj['name']),
                'content'      => NULL,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $post['user_id'],                             // id of initiator
                'verb'         => $verb,                                        // the activity type
                'object_id'    => $post['topic_id'],                            // the id of the referenced object
                'object_type'  => 'forum',                                      // type of activity object
                /*'object_url'   => array(                                        // url to entity in Stud.IP
                    $url => _('Zum Forum der Veranstaltung')
                ),
                'object_route' => \URLHelper::getURL('api.php/forum_entry/' . $post['topic_id'], NULL, true),   // url to entity as rest-route
                 * 
                 */
                'mkdate'       => $post['mkdate']
            )
        );

        $activity->store();
    }


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
