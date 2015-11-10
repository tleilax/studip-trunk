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
class DataFieldPhoneEntry extends DataFieldEntry
{
    protected $template = 'phone.php';

    public function numberOfHTMLFields()
    {
        return 3;
    }

    public function setValueFromSubmit($value)
    {
        if (is_array($value)) {
            $value = array_slice($value, 0, 3);
            $value = implode("\n", $value);
            $value = str_replace(' ', '', $value);

            parent::setValueFromSubmit($value);
        }
    }

    public function getDisplayValue($entities = true)
    {
        list($country, $area, $phone) = $this->getNumberParts();

        if ($country || $area || $phone) {
            $number = '';

            if ($country) {
                $number .= "+$country";
            }
            if ($area) {
                $area = "(0)$area";
                if ($phone) {
                    $area .= '/';
                }
                $number .= " $area";
            }
            $number .= $phone;

            return $number;
        }

        return '';
    }

    public function getHTML($name = '', $variables = array())
    {
        return parent::getHTML($name, array(
            'values' => $this->getNumberParts(),
        ));
    }

    public function isValid()
    {
        $value = trim($this->value);

        if (!$this->value) {
            return  parent::isValid();
        }

        return parent::isValid()
            && preg_match('/^([1-9]\d*)?\n[1-9]\d+\n[1-9]\d+(-\d+)?$/', $this->value);
    }

    protected function getNumberParts()
    {
        $values = explode("\n", $this->value);

        // pad values array to a size of 3 by inserting empty values from left
        while (count($values) < 3) {
            array_unshift($values, '');
        }

        return $values;
    }
}

