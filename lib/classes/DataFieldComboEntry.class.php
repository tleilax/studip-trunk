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
class DataFieldComboEntry extends DataFieldEntry
{
    protected $template = 'combo.php';

    public function __construct($struct, $range_id, $value)
    {
        parent::__construct($struct, $range_id, $value);

        if ($this->getValue() === null) {
            $parameters = $this->getParameters();
            $this->setValue($values[0]); // first selectbox entry is default
        }
    }

    public function numberOfHTMLFields()
    {
        return 2;
    }

    public function setValueFromSubmit($value)
    {
        $index = $value['combo'];
        $value = $value[$index];
        parent::setValueFromSubmit($value);
    }

    public function getHTML($name = '', $variables = array())
    {
        return parent::getHTML($name, array(
            'values' => $this->getParameters(),
        ));
    }

    protected function getParameters()
    {
        $parameters = explode("\n", $this->model->typeparam);
        $parameters = array_map('trim', $parameters);
        return $parameters;
    }
}
