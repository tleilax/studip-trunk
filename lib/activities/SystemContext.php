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

class SystemContext extends Context
{
    public function getActivities($observer_id, Filter $filter)
    {
        $self = $this;

        $providers = $this->filterProvider($this->getProvider(), $filter);

        $activities = array_map(
            function ($provider) use($self, $filter, $observer_id) {
                return $provider->getActivities($observer_id, $self, $filter);
            },
            $providers);

        return array_flatten($activities);
    }

    private function getProvider()
    {
        $this->addProvider('news');
        $this->addProvider('message');
        $this->addProvider('blubber');

        return $this->provider;
    }
}
