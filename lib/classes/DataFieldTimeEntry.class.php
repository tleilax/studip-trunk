<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/*
* DataFieldEntry.class.php - <short-description>
*
* Copyright (C) 2005 - Martin Gieseking  <mgieseki@uos.de>
* Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of
* the License, or (at your option) any later version.
*/
class DataFieldTimeEntry extends DataFieldEntry
{
    protected $template = 'time.php';

    public function numberOfHTMLFields()
    {
        return 2;
    }

    public function setValueFromSubmit($value)
    {
        if (is_array($value) && count($value) === 2) {
            $value = implode(':', $value);
            parent::setValueFromSubmit($value);
        }
    }

    public function getHTML($name = '', $variables = array())
    {
        return parent::getHTML($name, array(
            'values' => explode(':', $this->value),
        ));
    }

    public function isValid()
    {
        $parts = explode(':', $this->value);

        return parent::isValid()
            && $parts[0] >= 0 && $parts[0] <= 24
            && $parts[1] >= 0 && $parts[1] <= 59;
    }
}
