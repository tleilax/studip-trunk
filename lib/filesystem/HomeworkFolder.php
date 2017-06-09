<?php
/**
 * HomeworkFolder.class.php
 *
 * This is a FolderType implementation for homework folders.
 *
 * A homework folder in Stud.IP can be writeable by all course members
 * but is only readable by teachers.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Moritz Strohm <strohm@data-quest.de>
 * @copyright 2016 data-quest
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class HomeworkFolder extends PermissionEnabledFolder
{

    /**
     * Returns a localised name of the HomeworkFolder type.
     *
     * @return string The localised name of this folder type.
     */
    public static function getTypeName()
    {
        return _('Ordner für Hausarbeiten');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id);
        }
    }

    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);
        $this->permission = 3;
    }

    /**
     * Returns the Icon object for the HomeworkFolder type.
     *
     * @return Icon An icon object with the icon for this folder type.
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
            ? 'folder-lock-empty'
            : 'folder-lock-full';
        return Icon::create($shape, $role);
    }


    /**
     * Homework folders don't allow subfolders.
     *
     * @return bool False
     */
    public function isSubfolderAllowed($user_id)
    {
        //no subfolders allowed (I'm a homework folder, not a home folder backup!)
        return false;
    }

    /**
     * Returns the description template for a HomeworkFolder instance.
     *
     * @return string A description for the HomeworkFolder instance.
     */
    public function getDescriptionTemplate()
    {
            return _('Hausarbeitenordner');
    }

    /**
     * Folders of this type don't have an edit template.
     *
     * @return string
     */
    public function getEditTemplate()
    {
        return '';
    }
}
