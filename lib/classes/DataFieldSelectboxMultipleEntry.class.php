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
class DataFieldSelectboxMultipleEntry extends DataFieldSelectboxEntry
{
    const SEPARATOR = '|';

    protected function init()
    {
        if ($this->getValue() === null) {
            $this->setValue('');
        }
    }

    public function getHTML($name = '', $variables = array())
    {
        return parent::getHTML($name, array(
            'multiple' => true,
        ));
    }

    public function getDisplayValue($entities = true)
    {
        $value = $this->getValue();
        if ($value) {
            $type_param = $this->type_param;

            $mapper = 'trim';
            if ($this->is_assoc_param) {
                $mapper = function ($a) use ($type_param) {
                    $a = trim($a);
                    return $type_param[$a];
                };
            }

            $value = explode(self::SEPARATOR, $value);
            $value = array_map($mapper, $value);
            $value = implode('; ', $value);
        }
        return $entities
            ? htmlReady($value)
            : $value;
    }

    public function setValueFromSubmit($value)
    {
        if (is_array($value)) {
            $value = array_map('trim', $value);
            $value = array_filter($value);
            $value = array_unique($value);
            $value = implode(self::SEPARATOR, $value);
        } else {
            $value = '';
        }

        parent::setValueFromSubmit($value);
    }
}
