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

    /**
     * Returns the input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = array())
    {
        $variables['id']    = $name . '_' . $this->model->id;
        $variables['model'] = $this->model;

        if ($this->isRequired()) {
            $variables['required'] = true;
        }

        $variables['locale_names'] = $this->getLocaleNames($name);

        return I18N::textarea($name, $this->getValue(), $variables);
    }

}
