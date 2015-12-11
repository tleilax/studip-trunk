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

class MessageProvider implements ActivityProvider
{
    public function getActivities($observer_id, Context $context, Filter $filter)
    {
        $activities = array();

        $now = time();
        $chdate = $now - (24 * 60 * 60 * 260);

        $messages_data = \DBManager::get()->prepare("
            SELECT message.*
            FROM message_user
                INNER JOIN message ON (message_user.message_id = message.message_id)
            WHERE message_user.user_id = ?
                AND message_user.deleted = 0
                AND snd_rec = 'snd'
                AND message_user.mkdate >= ?
            ORDER BY message_user.mkdate DESC
        ");

        $messages_data->execute(array($observer_id, $chdate));

        while ($msg = $messages_data->fetch()) {
            $url = \URLHelper::getLink('dispatch.php/messages/read/' . $msg['message_id']);

            $activities[] = new Activity(
                'message_provider',
                array(                                  // the description and summaray of the performed activity
                    'title'   => $msg['subject'],
                    'content' => $msg['message']
                ),
                'user',                                 // who initiated the activity?
                $msg['autor_id'],                       // id of initiator
                'created',                              // the type if the activity
                'message',                              // type of activity object
                array(                                  // url to entity in Stud.IP
                    $url => _('Zur Nachricht')
                ),
                'http://example.com/route',             // url to entity as rest-route
                $msg['mkdate']
            );
        }

        return $activities;
    }

}
