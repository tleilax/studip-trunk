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
class DataFieldDateEntry extends DataFieldEntry
{
    protected $template = 'date.php';

    public function numberOfHTMLFields()
    {
        return 3;
    }

    public function setValueFromSubmit($value)
    {
        if (is_array($value) && count(array_filter($value)) === 3) {
            $value = implode('-', array_reverse($value));
            parent::setValueFromSubmit($value);
        }
    }

    public function getDisplayValue($entries = true)
    {
        if ($this->isValid()) {
            $value = trim($this->value);
            $value = explode('-', $value);
            $value = array_reverse($value);
            $value = implode('.', $value);
            return $value;
        }

        return '';
    }

    public function getHTML($name = '', $variables = array())
    {
        return parent::getHTML($name, array(
            'timestamp' => strtotime(trim($this->value)),
        ));
    }

    public function isValid()
    {
        $value = trim($this->value);

        if (!$value) {
            return parent::isValid();
        }

        return parent::isValid() && strtotime($value) !== false;
    }
}
