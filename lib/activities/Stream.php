<?php

/**
 * Stream.php - represents a set of activities
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

namespace Studip\Activity;

class Stream implements \ArrayAccess, \Countable, \IteratorAggregate
{
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

        $cached_activities = self::getCachedActivities($observer_id, $filter, $contexts);


        //var_dump($cached_activities);

        $new_activities = array();

        foreach ( $cached_activities as $activities) {

            if(!empty($activities)) {
                foreach ( $activities as $key => $activity) {
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
            }

        }


        $this->activities = $new_activities;
    }

    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return isset($this->activities[$offset]);
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this->activities[$offset];
    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
        $this->activities[$offset] = $value;
    }

    /**
     * ArrayAccess: unset the value at the given offset (not applicable)
     */
    public function offsetUnset($offset)
    {
        unset($this->activities[$offset]);
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
        return sizeof($this->activities);
    }

    public function asArray()
    {
        $activities = array();

        foreach ($this as $key => $activity) {
            $activities[$key] = $activity->asArray();
        }

        return $activities;
    }


    static function getCachedActivities($observer_id, $filter, $contexts) {

        $cache =\StudipCacheFactory::getCache();


        $end_date = new \DateTime();
        $end_date->setTimestamp(($filter->getEndDate()-(24*3600)));

        $start_date = new \DateTime();
        $start_date->setTimestamp($filter->getStartDate());

        $diff = $end_date->diff($start_date);
        $ret = array();

        for($i=1; $i<=$diff->days; $i++) {
            date_sub($end_date, date_interval_create_from_date_string('1 day'));
            $cachekey = 'activities_' . $observer_id . '_' . $end_date->format('Y-m-d');

            $tmp_stmp = $end_date->getTimestamp();

            $cached_activities = unserialize($cache->read($cachekey));

            if ($cached_activities) {

                $ret[$cachekey] = $cached_activities;

            } else {

                $filter2 = new Filter();
                $filter2->setStartDate($tmp_stmp);
                $filter2->setEndDate($tmp_stmp + ((23 * 3600) + 35999));


                // load all activities
                $activities = array_flatten(array_map(
                    function ($context) use ($observer_id, $filter2) {
                        return $context->getActivities($observer_id, $filter2);
                    }, $contexts));


                $new_activities = array();

                foreach ($activities as $key => $activity) {
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

                $cache->write($cachekey, serialize($new_activities));
                $ret[$cachekey] = $new_activities;
            }
        }
        return $ret;
    }
}
