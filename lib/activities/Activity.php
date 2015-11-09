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

class Activity
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
            'experienced',
            'failed',
            'imported',
            'interacted',
            'passed',
            'shared',
            'voided');


    function __construct($provider, $description, $actor_type, $actor_id, $verb, $object_type, $object_url, $object_route, $mkdate)
    {
        $this->setProvider($provider);
        $this->setDescription($description);
        $this->setActor($actor_type, $actor_id);
        $this->setVerb($verb);
        $this->setObject($object_type, $object_url, $object_route);
        $this->setMkdate($mkdate);
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

    /*
     * todo
     */
    
    function setMkdate($mkdate)
    {
       $this->mkdate = $mkdate;
    }
    
    function getMkdate()
    {
        return time();
    }
}
