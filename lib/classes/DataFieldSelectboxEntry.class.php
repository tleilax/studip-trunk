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
class DataFieldSelectboxEntry extends DataFieldEntry
{
    protected $template = 'selectbox.php';

    public function __construct($struct, $range_id, $value)
    {
        parent::__construct($struct, $range_id, $value);

        list($values, $is_assoc) = $this->getParams();
        $this->is_assoc_param = $is_assoc;
        $this->type_param     = $values;

        $this->init();
    }

    protected function init()
    {
        $is_assoc = $this->is_assoc_param;
        $values   = $this->type_param;

        reset($values);

        if ($this->getValue() === null) {
            if ($is_assoc) {
                $this->setValue((string)key($values));
            } else {
                $this->setValue(current($values)); // first selectbox entry is default
            }
        }
    }

    public function getHTML($name = '', $variables = array())
    {
        $variables = array_merge(array(
            'multiple'   => false,
            'type_param' => $this->type_param,
            'is_assoc'   => $this->is_assoc_param,
        ), $variables);

        return parent::getHTML($name, $variables);
    }

    protected function getParams()
    {
        $params = explode("\n", $this->structure->getTypeParam());
        $params = array_map('trim', $params);

        $ret = array();
        $is_assoc = false;

        foreach ($params as $i => $p) {
            if (strpos($p, '=>') !== false) {
                $is_assoc = true;

                list($key, $value) = array_map('trim', explode('=>', $p, 2));
                $ret[$key] = $value;
            } else {
                $ret[$i] = $p;
            }
        }
        return array($ret, $is_assoc);
    }

    public function getDisplayValue($entities = true)
    {
        $value = $this->is_assoc_param
               ? $this->type_param[$this->getValue()]
               : $this->getValue();
        return $entities
            ? htmlReady($value)
            : $value;
    }
}
