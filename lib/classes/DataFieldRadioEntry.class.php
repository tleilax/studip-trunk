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
class DataFieldRadioEntry extends DataFieldSelectboxEntry
{
    protected $template = 'radio.php';

    public function numberOfHTMLFields()
    {
        return count($this->type_param);
    }

    public function getHTML($name = '', $variables = array())
    {
        return parent::getHTML($name, array(
            'type_param' => $this->type_param,
            'is_assoc'   => $this->is_assoc_param,
        ));
    }
}
