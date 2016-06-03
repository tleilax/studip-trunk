<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class Stream implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private $activities;

    /**
     * creates a stream representing the activities for the passed contexts,
     * filter by time (if any)
     *
     * @param array $contexts All contexts that need to be considered
     * @param \Studip\Activity\Filter $filter
     *
     * @throws \InvalidArgumentException
     */
    function __construct($contexts, Filter $filter)
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


        $activities = array_flatten(array_map(
            function ($context) use ($filter) {
                return $context->getActivities($filter);
            }, $contexts)
        );


        $new_activities = array();

        foreach ($activities as $key => $activity) {
            // generate an id for the activity, considering some basic object parameters
            $id = md5($activity->provider . $activity->content .
                    $activity->verb . $activity->object_type . $activity->mkdate);

            if ($new_activities[$id]) {
                list($url, $name) = each($activity->object_url);
                $new_activities[$id]->addUrl($url, $name);
            } else {
                $new_activities[$id] = $activity;
            }
        }

        // sort activites by their mkdate
        usort($new_activities, function($a, $b) {
            if ($a->mkdate == $b->mkdate) {
                return 0;
            }

            return ($a->mkdate > $b->mkdate) ? -1 : 1;
        });


        // finally sort the activite-list by day
        ksort($new_activities, SORT_NATURAL);

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

    /**
     * return representation of the current stream as an array
     *
     * @return array
     */
    public function asArray()
    {
        $activities = array();

        foreach ($this as $key => $activity) {
            $activities[$key] = $activity->asArray();

            // add i18n auto generated title prefix
            ## TODO: switch between actor-types
            $title = '';

            $class       = '\\Studip\\Activity\\' . ucfirst($activity->provider) . 'Provider';
            $object_text = $class::getLexicalField();

            if (in_array($activity->actor_id, array('____%system%____', 'system')) !== false) {
                $actor = _('Stud.IP');
            } else {
                $actor = get_fullname($activity->actor_id);
            }

            switch ($activity->context) {
                case 'course':
                    $obj = get_object_name($activity->context_id, 'sem');

                    $title = $actor .' '
                        . sprintf($activity->verbToText(),
                            $object_text . sprintf(_(' im Kurs "%s"'), $obj['name'])
                        );
                break;

                case 'institute':
                    $obj = get_object_name($activity->context_id, 'inst');

                    $title = $actor .' '
                        . sprintf($activity->verbToText(),
                            $object_text . sprintf(_(' in der Einrichtung "%s"'), $obj['name'])
                        );
                break;

                case 'system':
                    $title = $actor .' '
                        . sprintf($activity->verbToText(), _('allen')) .' '
                        . $object_text;
                break;

                case 'user':
                    $title = $actor .' '
                        . sprintf($activity->verbToText(), get_fullname($activity->context_id)) .' '
                        . $object_text;
                break;

            }

            $activities[$key]['title'] = $title;
        }

        return $activities;
    }
}
