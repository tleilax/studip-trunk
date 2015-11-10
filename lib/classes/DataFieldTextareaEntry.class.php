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

class DataFieldTextareaEntry extends DataFieldEntry
{
    protected $template = 'textarea.php';

    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return htmlReady($this->getValue(), true, true);
        }

        return $this->getValue();
    }
}
