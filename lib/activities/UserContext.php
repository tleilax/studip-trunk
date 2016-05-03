<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <gloeggler@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class UserContext extends Context
{
    private
        $user_id;

    function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    function getRangeId()
    {
        return $this->user_id;
    }

    protected function getProvider()
    {

        if (!$this->provider) {
            $this->addProvider('blubber'); // todo: check if active for given user
            ## $this->addProvider('news');
            $this->addProvider('message');

            if (get_config('LITERATURE_ENABLE')) {
                ## $this->addProvider('literature');
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

    protected function getContextType()
    {
        return 'user';
    }
}
