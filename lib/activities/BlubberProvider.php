<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
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

    private static function doPostActivity($context, $context_id, $blubb)
    {
        $activity = Activity::get(
            array(
                'provider'     => 'blubber',
                'context'      => $context,
                'context_id'   => $context_id,
                'title'        => '',
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

    public static function postActivity($event, $blubb)
    {
        switch($blubb['context_type']) {
            case 'private':
                foreach($blubb->getRelatedUsers() as $context_id) {
                    self::doPostActivity('user', $context_id, $blubb);
                }
            break;

            case 'course':
                self::doPostActivity('course', $blubb['Seminar_id'], $blubb);
            break;

            case 'public':
                self::doPostActivity('system', 'system', $blubb);
            break;
        }
    }

    public static function getLexicalField()
    {
        return _('eine Blubbernachricht');
    }

}
