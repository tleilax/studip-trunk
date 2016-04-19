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

require_once 'public/plugins_packages/core/Blubber/models/BlubberPosting.class.php';

class BlubberProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the acitivty to fill with details, passed by reference
     */
    public function getActivityDetails(&$activity)
    {
        ## TODO: if entry does not exist, clear out activity...
        $blubb = \BlubberPosting::find($activity->object_id);

        $activity->content = formatReady($blubb->description);

        $url = \PluginEngine::getURL('Blubber', array(), 'streams/thread/'.$activity->object_id);

        $route = \URLHelper::getURL('api.php/blubber/posting/' . $activity->object_id, NULL, true);

        $activity->object_url = array(
            $url => _('Zum Blubberstream')
        );

        $activity->object_route = $route;
    }

    public static function postActivity($event, $blubb)
    {
        ## TODO: switch on $blubb['context']

        foreach($blubb->getRelatedUsers() as $context_id)  {    // context: private
            $activity = Activity::get(
                array(
                    'provider'     => 'blubber',
                    'context'      => 'user',
                    'context_id'   => $context_id,
                    'content'      => NULL,
                    'actor_type'   => 'user',                                       // who initiated the activity?
                    'actor_id'     => $blubb['user_id'],                            // id of initiator
                    'verb'         => 'created',                                    // the activity type
                    'object_id'    => $blubb['topic_id'],                           // the id of the referenced object
                    'object_type'  => 'blubber',                                    // type of activity object
                    'mkdate'       => $blubb['chdate']
                )
            );

            $activity->store();
        }
    }
}
