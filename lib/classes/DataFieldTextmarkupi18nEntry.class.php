<?php
/**
 * DataFieldTextmarkup18nEntry.php
 * Representation of datafields of type textmarkup with i18n support.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <pthienel@data-quest.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 * 
 */

class DataFieldTextmarkupi18nEntry extends DataFieldTextareai18nEntry
{
    
    /**
     * Returns the input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = array())
    {
        $attributes['input_attributes']['id'] = $name . '_' . $this->model->id;
        if ($this->isRequired()) {
            $attributes['input_attributes']['required'] = '';
        }
        $attributes['input_attributes']['class'] = 'wysiwyg';
        $attributes['datafield_id'] = $this->model->id;
        return I18N::inputTmpl('datafields/textarea_i18n.php', $name,
                $this->getValue(), $attributes);
    }

    public function setValueFromSubmit($submitted_value)
    {
        array_walk($submitted_value, 'Studip\Markup::purifyHtml');
        parent::setValueFromSubmit($submitted_value);
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return formatReady($this->getValue());
        }

        return $this->getValue();
    }
}
