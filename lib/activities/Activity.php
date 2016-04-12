<?php

/**
 * Activity.php - Activity Class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

namespace Studip\Activity;

class Activity extends \SimpleORMap
{
    public
        $object_url,
        $object_route;

    private static $allowed_verbs = array(
        'answered',
        'attempted',
        'attended',
        'completed',
        'created',
        'deleted',
        'edited',
        'experienced',
        'failed',
        'imported',
        'interacted',
        'passed',
        'shared',
        'voided'
    );

    protected static function configure($config = array())
    {
        $config['db_table'] = 'activities';

        parent::configure($config);
    }

    public static function get($data)
    {
        $activity = new Activity();
        $activity->setData($data);

        return $activity;
    }

    public function __toString()
    {
        return $this->title .', '. $this->content;
    }

    public function setVerb($verb)
    {
        if (!in_array($verb, self::$allowed_verbs)) {
            throw new \InvalidArgumentException("That verb is not allowed.");
        }

        $this->content['verb'] = $verb;
    }

    /**
     * Add a url to the list of urls
     *
     * @param type $url
     * @param type $name
     */
    public function addUrl($url, $name)
    {
        $this->object_url[$url] = $name;
    }

    public function asArray()
    {
        $data = $this->toArray();

        $data['object_url'] = $this->object_url;
        $data['object_route'] = $this->object_route;

        return $data;
    }
}
