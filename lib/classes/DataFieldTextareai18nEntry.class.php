<?php
/**
 * DataFieldTextareai18nEntry.php
 * Representation of datafields of type textarea with i18n support.
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

class DataFieldTextareai18nEntry extends DataFieldI18NEntry
{
    
    public function getHTML($name = '', $variables = array())
    {
        $attributes['input_attributes']['id'] = $name . '_' . $this->model->id;
        if ($this->isRequired()) {
            $attributes['input_attributes']['required'] = '';
        }
        $attributes['datafield_id'] = $this->model->id;
        return I18N::inputTmpl('datafields/textarea_i18n.php', $name,
                $this->getValue(), $attributes);
    }
    
}
