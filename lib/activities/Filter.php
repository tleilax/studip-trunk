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

    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }

    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    public function getEndDate()
    {
        return $this->end_date;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getVerb()
    {
        return $this->verb;
    }

    public function setVerb($verb)
    {
        $this->verb = $verb;
    }
}
