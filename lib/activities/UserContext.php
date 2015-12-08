<?php

/**
 * UserContext - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @author      Till Gl�ggler <gloeggler@elan-ev.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

namespace Studip\Activity;

class UserContext implements Context
{
    private
        $user_id,
        $provider;

    function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    function getUserId()
    {
        return $this->user_id;
    }

    private function addProvider($provider){
        $class_name = 'Studip\Activity\\' . ucfirst($provider) . 'Provider';
        $reflectionClass = new \ReflectionClass($class_name);
        $this->provider[] =  $reflectionClass->newInstanceArgs();
    }

    private function getProvider(){

        if (!$this->provider) {


            $this->addProvider('blubber'); // todo: check if active for given user

            if (get_config('LITERATURE_ENABLE')) {
                $this->addProvider('literature');
            }

            $homepage_plugins = \PluginEngine::getPlugins('HomepagePlugin');
            foreach ($homepage_plugins as $plugin) {
                if ($plugin->isActivated($this->user_id, 'user')) {
                    if ($plugin instanceof \Studip\ActivityProvider) {
                        $this->provider[] = $plugin;
                    }
                }
            }
        }

        return $this->provider;

    }

    public function getActivities($observer_id, Filter $filter)
    {
        $providers = $this->getProvider();

        $activities = array_map(
            function ($provider) use($observer_id, $filter) {
                return $provider->getActivities($observer_id, $this, $filter);
            },
            $providers);

        return array_flatten($activities);

    }

}
