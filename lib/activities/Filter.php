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
    private
        $start_date,
        $end_date,
        $type;



    function setStartDate($start_date){

        $this->start_date = $start_date;
    }

    function getStartDate(){
        return $this->start_date;
    }

    function setEndDate($end_date){
        $this->end_date = $end_date;
    }

    function getEndDate(){
        return $this->end_date;
    }

    function setType($type) {
        $this->type = $type;
    }

    function getType() {
        return $this->type;
    }
}
