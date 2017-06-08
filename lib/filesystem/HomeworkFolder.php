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
class HomeworkFolder extends StandardFolder
{
    public $folderdata = null;

    /**
     * Returns a localised name of the HomeworkFolder type.
     *
     * @return string The localised name of this folder type.
     */
    public static function getTypeName()
    {
        return _('Ordner für Hausarbeiten');
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
     * @param string $range_type The range type where the creatable flag shall be checked.
     *
     * @return bool True, if the range type is 'course', false otherwise.
     */
    public static function creatableInStandardFolder($range_type)
    {
        return $range_type === 'course';
    }

    /**
     * HomeworkFolders are always visible.
     *
     * @return bool True
     */
    public function isVisible($user_id)
    {
        //folders of this type are visible for everyone
        return true;
    }

    /**
     * HomeworkFolders are always readable.
     *
     * @return bool True
     */
    public function isReadable($user_id)
    {
        //We need to enter this folder even as a student
        return true;
    }

    /**
     * Returns all files of the folder object.
     *
     * @return FileRef[] An array with all files of this folder.
     */
    public function getFiles()
    {
        return $this->folderdata->file_refs->getArrayCopy();
    }

    /**
     * A file is downloadable for the owner or if the user specified by $user_id
     * is a tutor in the course (specified by $this->range_id).
     *
     * @param mixed $fileref_or_id Either a FileRef object or an ID of a FileRef object.
     * @param string $user_id The user who wishes to download the file.
     *
     * @return True, if the file is downloadable, false otherwise.
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        return $fileref['user_id'] === $user_id
            || $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * A file is editable for the owner or if the user specified by $user_id
     * is a tutor in the course (specified by $this->range_id).
     *
     * @param mixed $fileref_or_id Either a FileRef object or an ID of a FileRef object.
     * @param string $user_id The user who wishes to edit the file.
     *
     * @return True, if the file is editable, false otherwise.
     */
    public function isFileEditable($fileref_or_id, $user_id)
    {
        return $this->isFileDownloadable($fileref_or_id, $user_id);
    }

    /**
     * A file is writeable for the owner or if the user specified by $user_id
     * is a tutor in the course (specified by $this->range_id).
     *
     * @param mixed $fileref_or_id Either a FileRef object or an ID of a FileRef object.
     * @param string $user_id The user who wishes to write to the file.
     *
     * @return True, if the file is writeable, false otherwise.
     */
    public function isFileWritable($fileref_or_id, $user_id)
    {
        return $this->isFileDownloadable($fileref_or_id, $user_id);
    }

    /**
     * Folders of this type are writable for users which have the author
     * permissions inside the course of this folder.
     *
     * @param string $user_id The user who wishes to do a write operation
     *     on this folder.
     *
     * @return True, if the folder is writeable, false otherwise.
     */
    public function isWritable($user_id)
    {
        // folders of this type are writable for users with permissions author
        if ($this->folderdata) {
            return $GLOBALS['perm']->have_studip_perm('autor', $this->folderdata->range_id, $user_id);
        }

        // a non-existant folder isn't writable!
        return false;
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
        if ($this->folderdata) {
            $course = Course::find($this->folderdata->range_id);
            if ($course) {
                return sprintf(
                    _('Hausarbeitenordner für %s'),
                    $course->getFullName()
                );
            }

            return _('Hausarbeitenordner');
        }
    }

    /**
     * Folders of this type don't have an edit template.
     *
     * @return null
     */
    public function getEditTemplate()
    {
        return '';
    }
}
