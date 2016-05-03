<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class Filter
{
    private
        $start_date,
        $end_date,
        $type,
        $verb;

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

    function getVerb()
    {
        return $this->verb;
    }

    function setVerb($verb)
    {
        $this->verb = $verb;
    }
}
