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

class Filter
{
    var $age, $type;

    function setMaxAge($age) {
        $this->age = $age;
    }

    function getMaxAge() {
        return $this->age;
    }

    function setType($type) {
        $this->type = $type;
    }

    function getType() {
        return $this->type;
    }
}
