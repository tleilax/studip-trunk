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

class InstituteContext implements Context
{
    private
        $institute_id;

    function __construct($institute_id)
    {
        $this->institute_id = $institute_id;
    }

    function getInstituteId()
    {
        return $this->institute_id;
    }

    public function getActivities($observer_id, Filter $filter)
    {

    }

}
