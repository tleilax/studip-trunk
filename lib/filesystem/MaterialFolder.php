<?php
/**
 * HiddenFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Dominik Feldschnieders <dofeldsc@uos.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class MaterialFolder extends PermissionEnabledFolder
{
    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);

        $this->must_have_perm = 'tutor';
    }

    public function isVisible($user_id)
    {
        return true;
    }

    public function isReadable($user_id)
    {
        return true;
    }

    public function isWritable($user_id)
    {
        return $this->checkPermission($user_id);
    }

    public function isSubfolderAllowed($user_id)
    {
        return $this->checkPermission($user_id);
    }

    // ToDo Icon anpassen
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create('download', $role);
    }

    static public function getTypeName()
    {
        return _('Materialordner zum Anbieten von Inhalten zum Download');
    }

    public function getEditTemplate()
    {
        return null;
    }

    protected function checkPermission($perm, $user_id = null)
    {
        return $user_id
            && is_object($GLOBALS['perm'])
            && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id);
    }

    public function getDescriptionTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/material_folder/description.php');

        $template->type   = self::getTypeName();
        $template->folder = $this;

        return $template;
    }
}
