<?php
/**
 * DataFieldBoolEntry.class.php - <short-description>
 *
 * Copyright (C) 2005 - Martin Gieseking  <mgieseki@uos.de>
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */
class DataFieldBoolEntry extends DataFieldEntry
{
    protected $template = 'bool.php';

    public function getDisplayValue($entities = true)
    {
        return $this->getValue() ? _('Ja') : _('Nein');
    }

    public function setValueFromSubmit($submitted_value)
    {
        $this->setValue((int) $submitted_value);
    }
}
