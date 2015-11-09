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

class UserContext implements Context
{
    private
        $user_id;

    function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    function getUserId()
    {
        return $this->user_id;
    }

    public function getActivities($observer_id, Filter $filter)
    {

    }

}
