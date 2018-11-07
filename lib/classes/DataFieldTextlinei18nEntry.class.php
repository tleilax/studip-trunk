<?php
/**
 * DataFieldTextlinei18nEntry.php
 * Representation of datafields of type textline with i18n support.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 *
 */
class DataFieldTextlinei18nEntry extends DataFieldI18NEntry
{

    /**
     * Returns the input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = [])
    {
        $variables['id'] = $name . '_' . $this->model->id;

        if ($this->isRequired()) {
            $variables['required'] = true;
        }

        $variables['locale_names'] = $this->getLocaleNames($name);

        return I18N::input($name, $this->getValue(), $variables);
    }

}
