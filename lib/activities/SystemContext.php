<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class SystemContext extends Context
{
    protected function getProvider()
    {
        $this->addProvider('news');
        $this->addProvider('blubber');

        return $this->provider;
    }

    public function getRangeId() {
        return 'system';
    }

    protected function getContextType()
    {
        return 'system';
    }
}
