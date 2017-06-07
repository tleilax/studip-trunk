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
    /**
     * MaterialFolder constructor.
     */
    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);

        // you need to be at least tutor to have the right to do more then read
        $this->must_have_perm = 'tutor';
    }

    /**
     * The type of folder are always visible
     *
     * @return bool True
     */
    public function isVisible($user_id)
    {
        return true;
    }

    /**
     * The type of folder are always readable
     *
     * @return bool True
     */
    public function isReadable($user_id)
    {
        return true;
    }

    /**
     * This folder is only writable if the user is the lecturer or his tutor
     *
     * @param null $user_id
     * @return bool True, if the user is the lecturer or his tutor
     */
    public function isWritable($user_id)
    {
        return $this->checkPermission($user_id);
    }

    /**
     * This folder is only writable if the user is the lecturer or his tutor
     *
     * @param null $user_id
     * @return bool True, if the user is the lecturer or his tutor
     */
    public function isSubfolderAllowed($user_id)
    {
        return $this->checkPermission($user_id);
    }

    /**
     * This function returns the suitable Icon for this folder type (GroupFolder)
     *
     * @return Icon The icon object for this folder type
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create('download', $role);
    }

    /**
     * Returns the name of the MaterialFolder type.
     *
     * @return string the name of the MaterialFolder type
     */
    static public function getTypeName()
    {
        return _('Materialordner zum Anbieten von Inhalten zum Download');
    }

    /**
     * There is nothing special you can edit at this folder
     */
    public function getEditTemplate()
    {
        return null;
    }

    /**
     * This method check the studip-permission for a given user. The User need to be at
     * least a tutor (or higher) in this course
     *
     * @param $user_id The User-ID
     * @return bool True if user have permission, False otherwise
     */
    protected function checkPermission($perm, $user_id = null)
    {
        return $user_id
            && is_object($GLOBALS['perm'])
            && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id);
    }

    /**
     * Returns the description template for a instance of a MaterialFolder type
     *
     * @return mixed A description template for a instance of the type MaterialFolder
     */
    public function getDescriptionTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/material_folder/description.php');

        $template->type   = self::getTypeName();
        $template->folder = $this;

        return $template;
    }
}
