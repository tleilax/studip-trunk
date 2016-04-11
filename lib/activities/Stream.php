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
        // TODO: validate that the filter object has only timestamp at 00:00:00 o'clock
        // ----> Do not filter here, filter in Activity-Plugin to allow calls precise to one second via internal API
        // TODO: validate that the filter object has dates in a correct order and does not exceed a certain range

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

        ## TODO: filter context as well
        $this->activities = Activity::findBySQL('mkdate >= ? AND mkdate <= ? ORDER BY mkdate DESC',
                array($filter->getStartDate(), $filter->getEndDate()));

        
        foreach ($this->activities as $activity) {
            \ForumActivity::getAtivityDetails($activity);
            // call_user_func($activity->provider .'::getActivityDetails', $activity);

            print_r($activity->asArray());
        }

        die;

        echo json_encode($this->activities);die;

        /*
        $cached_activities = self::getCachedActivities($observer_id, $filter, $contexts);

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
         *
         */
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
            $activities[$key] = $activity->toArray();
        }

        return $activities;
    }

    /*
    static function getCachedActivities($observer_id, $filter, $contexts) {

        $cache =\StudipCacheFactory::getCache();


        $end_date = new \DateTime();
        $end_date->setTimestamp($filter->getEndDate());

        $start_date = new \DateTime();
        $start_date->setTimestamp($filter->getStartDate());

        $diff = $end_date->diff($start_date);
        $ret = array();

        $ranges = array();

        // step 1 - detect ranges to speed up collection of data
        for($i = 1; $i <= $diff->days; $i++) {
            $cachekey = 'activities_' . $observer_id . '_' . $end_date->format('Y-m-d');

            $tmp_stmp = $end_date->getTimestamp();

            $cached_activities = unserialize($cache->read($cachekey));

            // if there are cache entries and it is NOT the current day, return cached actvities
            if ($cached_activities !== false && date('Y-m-d') !=  $end_date->format('Y-m-d')) {
                $ret[$cachekey] = $cached_activities;
            } else {
                $end_date_timestamp = $end_date->getTimestamp();

                if ($ranges[$previous_date]) {
                    $ranges[$end_date_timestamp] = $ranges[$previous_date];
                    unset($ranges[$previous_date]);
                } else {
                    $ranges[$end_date_timestamp] = $end_date_timestamp;
                }
            }

            // go to the day before the current end_date and set it as new end_date
            $previous_date = $end_date->getTimestamp();
            date_sub($end_date, date_interval_create_from_date_string('1 day'));
        }

        // step 2 - get data for ranges
        foreach ($ranges as $from => $to) {
            $filter2 = new Filter();
            $filter2->setStartDate($from);
            $filter2->setEndDate($to + 86399); // 23 hours, 59 minutes and 59 seconds

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
                    $new_activities[date('Y-m-d', $activity->getMkdate())][$id]->addUrl($url, $name);
                } else {
                    $new_activities[date('Y-m-d', $activity->getMkdate())][$id] = $activity;
                }
            }

            foreach ($new_activities as $date => $tmp_activities) {
                $cachekey = 'activities_' . $observer_id . '_' . $date;

                // sort activites by their mkdate
                usort($tmp_activities, function($a, $b) {
                    if ($a->getMkdate() == $b->getMkdate()) {
                        return 0;
                    }

                    return ($a->getMkdate() > $b->getMkdate()) ? -1 : 1;
                });

                // write activites to cache
                $cache->write($cachekey, serialize($tmp_activities));
                $ret[$cachekey] = $tmp_activities;
            }
        }

        // finally sort the activite-list by day
        ksort($ret, SORT_NATURAL);

        // after ksort the array is in the wrong order
        return array_reverse($ret);
    }
     * 
     */
}
