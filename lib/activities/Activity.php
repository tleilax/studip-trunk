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
    protected $actor;
    protected $description;
    protected $provider;
    protected $object;
    protected $verb;
    protected $mkdate;
    
    static $allowed_verbs = array(
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

    public static function get($provider, $description, $actor_type, $actor_id, $verb, $object_type, $object_url, $object_route, $mkdate)
    {
        $activity = new Activity();

        $activity->setProvider($provider);
        $activity->setDescription($description);
        $activity->setActor($actor_type, $actor_id);
        $activity->setVerb($verb);
        $activity->setObject($object_type, $object_url, $object_route);
        $activity->setMkdate($mkdate);

        return $activity;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function getDescription()
    {
        return $this->description;
    }

    function __toString()
    {
        return $this->description;
    }

    function setActor($objectType, $id)
    {
        $this->actor = array(
            'objectType' => $objectType,
            'id'         => $id
        );
    }

    function getActor()
    {
        return $this->actor;
    }

    function setVerb($verb)
    {
        if (!in_array($verb, self::$allowed_verbs)) {
            throw new \InvalidArgumentException("That verb is not allowed.");
        }

        $this->verb = $verb;
    }

    function getVerb()
    {
        return $this->verb;
    }

    function setObject($object_type, $url, $route)
    {
        $this->object = array('objectType' => $object_type, 'url' => $url, 'route' => $route);
    }

    function getObject()
    {
        return $this->object;
    }

    function setProvider($provider)
    {
        $this->provider = $provider;
    }

    function getProvider()
    {
        return $this->provider;
    }

    function setMkdate($mkdate)
    {
       $this->mkdate = $mkdate;
    }
    
    function getMkdate()
    {
        return $this->mkdate;
    }

    /**
     * Add a url to the list of urls
     *
     * @param type $url
     * @param type $name
     */
    function addUrl($url, $name)
    {
        $this->object['url'][$url] = $name;
    }

    function asArray()
    {
        return array(
            'actor'       => $this->actor,
            'description' => $this->description,
            'provider'    => $this->dprovider,
            'object'      => $this->object,
            'verb'        => $this->verb,
            'mkdate'      => $this->mkdate
        );
    }
}
