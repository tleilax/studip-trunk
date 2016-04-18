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
    /**
     * get the details for the passed activity
     *
     * @param object $activity the acitivty to fill with details, passed by reference
     */
    public function getActivityDetails(&$activity)
    {
        ## TODO: if entry does not exist, clear out activity...
        $message = \Message::find($activity->object_id);

        $activity->content = formatReady($message->message);

        $url = \URLHelper::getUrl("dispatch.php/messages/read/{$message->id}", array('cid' => null));

        $route = \URLHelper::getURL('api.php/message/' . $message->id, NULL, true);

        $activity->object_url = array(
            $url => _('Zur Nachricht')
        );

        $activity->object_route = $route;
    }


    public static function postActivity($event, $message_id, $data)
    {
        // var_Dump($data);die;
        $username     = get_username($data['user_id']);

        // activity for sender
        /*
        $activity = Activity::get(
            array(
                'provider'     => 'message',
                'context'      => 'user',
                'context_id'   => $data['user_id'],
                'title'        => sprintf('Sie haben eine Nachricht verschickt.'),   ## TODO: list all recipients??
                'content'      => NULL,
                'actor_type'   => 'user',                                       // who initiated the activity?
                'actor_id'     => $data['user_id'],                             // id of initiator
                'verb'         => 'created',                                    // the activity type
                'object_id'    => $message_id,                                  // the id of the referenced object
                'object_type'  => 'message',                                    // type of activity object
                'mkdate'       => time()
            )
        );

        $activity->store();
         *
         */

        foreach ($data['rec_id'] as $rec_id) {
            # $rec_username = get_username($rec_id);

            // activity for receipent
            $activity = Activity::get(
                array(
                    'provider'     => 'message',
                    'context'      => 'user',
                    'context_id'   => $rec_id,
                    'title'        => sprintf('Sie haben eine Nachricht von %s erhalten.', $username),
                    'content'      => NULL,
                    'actor_type'   => 'user',                                   // who initiated the activity?
                    'actor_id'     => $data['user_id'],                         // id of initiator
                    'verb'         => 'created',                                // the activity type
                    'object_id'    => $message_id,                              // the id of the referenced object
                    'object_type'  => 'message',                                // type of activity object
                    'mkdate'       => time()
                )
            );
        }

        $activity->store();
    }

}
