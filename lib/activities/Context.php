<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

namespace Studip\Activity;

abstract class Context
{
    protected
        $provider;

    abstract protected function getProvider();

    public function getActivities($observer_id, Filter $filter)
    {
        $providers = $this->filterProvider($this->getProvider(), $filter);

        $activities = array_map(
            function ($provider) use($observer_id, $filter) {
                return $provider->getActivities($observer_id, $this, $filter);
            },
            $providers);

        return array_flatten($activities);

    }

    protected function addProvider($provider)
    {
        $class_name = 'Studip\Activity\\' . ucfirst($provider) . 'Provider';

        $reflectionClass = new \ReflectionClass($class_name);
        $this->provider[] =  $reflectionClass->newInstanceArgs();
    }

    protected function filterProvider($providers, Filter $filter)
    {
        $filtered_providers = array();

        if (is_null($filter->getType())) {
            $filtered_providers = $providers;
        } else {
            foreach($providers as $provider) {
                $filtered_class = 'Studip\Activity\\' . ucfirst($filter->getType()) . 'Provider';

                if ($provider instanceof $filtered_class) {
                    $filtered_providers[] =  $provider;
                }
            }
        }

        return $filtered_providers;

    }
}
