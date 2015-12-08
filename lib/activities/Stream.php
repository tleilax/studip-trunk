<?php

/**
 * File - description
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

class Stream implements \ArrayAccess, \Countable, \IteratorAggregate
{

    // TODO
    private $activities;

    function __construct($observer_id, $contexts, Filter $filter)
    {
        if (!is_array($contexts)) {
            $contexts = array($contexts);
        }

        foreach ($contexts as $context) {
            if (!$context instanceof Context) {
                throw new \InvalidArgumentException();
            }
        }

        if (!$filter instanceof Filter) {
            throw new \InvalidArgumentException();
        }


        // load all activities
        $this->activities = array_flatten(array_map(
                                              function ($context) use($observer_id, $filter) {
                                                  return $context->getActivities($observer_id, $filter);
                                              }, $contexts));

        // deduplizieren + sortieren nach mkdate (immer wg. Timeline!)
        // TODO: deduplizieren fehlt noch

        usort($this->activities, function($a, $b) {
            if ($a->getMkdate() == $b->getMkdate()) {
                return 0;
            }

            return ($a->getMkdate() > $b->getMkdate()) ? -1 : 1;
        });

        foreach ($this->activities as $key => $activity) {
            // generate an id for the activity, considering some basic object parameters
            $object = $activity->getObject();
            $id = md5($activity->getProvider() . serialize($activity->getDescription()) . $activity->getVerb() . $object['objectType'] . $activity->getMkdate());

            if ($new_activities[$id]) {
                list($url, $name) = each($object['url']);
                $new_activities[$id]->addUrl($url, $name);
            } else {
                $new_activities[$id] = $activity;
            }
        }

        $this->activities = $new_activities;
    }

 /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {

    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {

    }
    /**
     * ArrayAccess: unset the value at the given offset (not applicable)
     */
    public function offsetUnset($offset)
    {

    }
    /**
     * IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->activities);
    }
    /**
     * Countable
     */
    public function count()
    {
        return 2;
    }

}
