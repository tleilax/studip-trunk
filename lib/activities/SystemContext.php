<?php

/**
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
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
        $this->addProvider('news');
        $this->addProvider('blubber');

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
