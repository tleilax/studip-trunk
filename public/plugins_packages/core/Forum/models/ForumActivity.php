<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class ForumActivity
{
    /**
     * Post activity for new forum post
     *
     * @param type $topic_id
     * @param type $post
     */
    public static function newEntry($topic_id, $post)
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
    public static function updateEntry($topic_id, $post)
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
    public static function deleteEntry($topic_id, $post)
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

        $course_id = $post['seminar_id'];

        $obj = get_object_name($course_id, 'sem');
        
        $url = PluginEngine::getURL('CoreForum', array(), 'index/index/' . $post['topic_id']
                    .'?cid='. $course_id .'&highlight_topic='. $post['topic_id']
                    .'#'. $post['topic_id']);

        $activity = Studip\Activity\Activity::get(
            'forum_provider',
            array(                                                  // the description and summaray of the performed activity
                'title'   => sprintf($summary, get_fullname($post['user_id']), $obj['name']),
                'content' => NULL
            ),
            'user',                                                 // who initiated the activity?
            $post['user_id'],                                       // id of initiator
            $verb,                                                  // the type if the activity
            $post['topic_id'],                                      // the id of the referenced object
            'forum',                                                // type of activity object
            array(                                                  // url to entity in Stud.IP
                $url => _('Zum Forum der Veranstaltung')
            ),
            \URLHelper::getURL('api.php/forum_entry/' . $post['topic_id'], NULL, true),   // url to entity as rest-route
            $post['mkdate']
        );

        $activity->store();
    }
}
