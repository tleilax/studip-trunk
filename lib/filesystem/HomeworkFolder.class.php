<?php
/**
 * HomeworkFolder.class.php
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * This is a FolderType implementation for homework folders.
 * 
 * A homework folder in Stud.IP can be writeable by all course members
 * but is only readable by teachers.
 */
class HomeworkFolder extends StandardFolder
{
    public $folderdata = null;
    
    
    static public function getTypeName()
    {
        return _('Ordner für Hausarbeiten');
    }


    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) == 0 ? 'folder-lock-empty' : 'folder-lock-full';
        return Icon::create($shape, $role);
    }
    
    
    static public function creatableInStandardFolder($range_type)
    {
        return ($range_type == 'course');
    }
    
    
    public function isVisible($user_id)
    {
        //folders of this type are visible for everyone
        return true;
    }
    
    
    public function isReadable($user_id)
    {
        //We need to enter this folder even as a student
        return true;
    }

    public function getFiles()
    {
        return $this->folderdata->file_refs->getArrayCopy();
    }

    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        return $fileref['user_id'] === $user_id || $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    public function isFileEditable($fileref_or_id, $user_id)
    {
        return $this->isFileDownloadable($fileref_or_id, $user_id);
    }

    public function isFileWritable($fileref_or_id, $user_id)
    {
        return $this->isFileDownloadable($fileref_or_id, $user_id);
    }


    public function isWritable($user_id)
    {
        //folders of this type are writable for users with permissions author
        if ($this->folderdata) {
            return $GLOBALS['perm']->have_studip_perm('autor', $this->folderdata->range_id, $user_id);
        } else {
            //a non-existant folder isn't writable!
            return false;
        }
    }
    
    
    public function isSubfolderAllowed($user_id)
    {
        //no subfolders allowed (I'm a homework folder, not a home folder backup!)
        return false;
    }
    
    
    public function getDescriptionTemplate()
    {
        if($this->folder) {
            $course = Course::find($this->folderdata->range_id);
            if($course) {
                return sprintf(
                    _('Hausarbeitenordner für %s'),
                    $course->getFullName()
                );
            } else {
                return _('Hausarbeitenordner');
            }
        }
    }
    
    
    public function getEditTemplate()
    {
        return null; //STUB
    }
    
    
    public function setData($request)
    {
        return null; //STUB
    }
}
