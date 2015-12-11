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

class SystemContext implements Context
{
    function __construct()
    {
    }

    public function getActivities($observer_id, Filter $filter)
    {
        $self = $this; // oy vey

        $providers = $this->getProviders();

        $activities = array_map(
            function ($provider) use($self, $filter, $observer_id) {
                return $provider->getActivities($observer_id, $self, $filter);
            },
            $providers);

        return array_flatten($activities);
    }

    private function getProviders()
    {
        // system context only knows global news
        return array(
            new NewsProvider(),
            new MessageProvider()
        );
    }
}
