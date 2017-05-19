<?php

/**
 *  HiddenFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author Dominik Feldschnieders <dofeldsc@uos.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class HiddenFolder extends PermissionEnabledFolder
{
    protected $permission = 6;

    static public function creatableInStandardFolder($range_type)
    {
        return $range_type == 'course';
    }

    static public function getTypeName()
    {
        return _("Unsichtbarer Ordner mit Zugangsbeschränkung");
    }

    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) == 0 ? 'folder-lock-empty+visibility-invisible' : 'folder-lock-full+visibility-invisible';
        return Icon::create($shape, $role);
    }

    // ToDo für Testzwecke template erstmal noch gleich wie in parent (PermissionEnabledFolder)
    // Was soll ein Unsichtbarer Ordner können, nur gelesen werden wenn sichtbar odedr wie?
/*    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/hidden_folder/edit.php');
        $template->set_attribute('folder', $this);
        return $template;
    }
*/
    public function getDescriptionTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/hidden_folder/description.php');
        $template->set_attribute('type', self::getTypeName());
        $template->set_attribute('folder', $this);
        return $template;
    }

}