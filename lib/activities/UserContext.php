<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <gloeggler@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class UserContext extends Context
{
    private $user_id;

    /**
     * create new user-context
     *
     * @param string $user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRangeId()
    {
        return $this->user_id;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {

        if (!$this->provider) {
            $this->addProvider('Studip\Activity\NewsProvider');
            $this->addProvider('Studip\Activity\BlubberProvider');
            $this->addProvider('Studip\Activity\MessageProvider');

            if (get_config('LITERATURE_ENABLE')) {
                $this->addProvider('Studip\Activity\LiteratureProvider');
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

    /**
     * {@inheritdoc}
     */
    protected function getContextType()
    {
        return 'user';
    }
}
