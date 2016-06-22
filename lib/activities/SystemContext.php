<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class SystemContext extends Context
{
    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        $this->addProvider('Studip\Activity\NewsProvider');
        $this->addProvider('Studip\Activity\BlubberProvider');

        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getRangeId()
    {
        return 'system';
    }

    /**
     * {@inheritdoc}
     */
    protected function getContextType()
    {
        return 'system';
    }
}
