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
