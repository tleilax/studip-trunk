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
class HomeworkFolder implements FolderType
{
    public $folder = null;
    
    
    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }
    
    
    static public function getTypeName()
    {
        return _('Ordner für Hausarbeiten');
    }
    
    
    static public function getIconShape()
    {
        return 'folder-empty';
    }
    
    
    static public function creatableInStandardFolder($range_type)
    {
        return ($range_type == 'course');
    }
    
    
    public function getName()
    {
        if($this->folder) {
            return $this->folder->name;
        } else {
            return null;
        }
    }
    
    
    public function isVisible($user_id)
    {
        //folders of this type are visible for everyone
        return true;
    }
    
    
    public function isReadable($user_id)
    {
        //folders of this type are readable only for course teachers
        global $perm;
        
        if($this->folder) {
            return $perm->have_studip_perm('dozent', $this->folder->range_id, $user_id);
        } else {
            //a non-existant folder isn't readable!
            return false;
        }
    }
    
    
    public function isWritable($user_id)
    {
        //folders of this type are writable for users with permissions author
        
        global $perm;
        
        if($this->folder) {
            return $perm->have_studip_perm('autor', $this->folder->range_id, $user_id);
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
            $course = Course::find($this->folder->range_id);
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
    
    
    public function validateUpload($file, $user_id)
    {
        return true; //STUB
    }

    public function createSubfolder($folderdata)
    {

    }
}
