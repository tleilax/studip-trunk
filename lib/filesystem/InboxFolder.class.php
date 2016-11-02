<?php
/**
 * InboxFolder.class.php
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
 * This is a FolderType implementation especially for message attachments.
 * 
 * Message attachments of Stud.IP messages are covered by the file system.
 * So they have to be stored somewhere in the file system.
 * The InboxFolder folder type exists for this special purpose.
 */
class InboxFolder implements FolderType
{
    public $folder = null;
    
    
    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }
    
    
    static public function getTypeName()
    {
        return 'InboxFolder';
    }
    
    
    static public function getIconShape()
    {
        return 'inbox';
    }
    
    
    static public function creatableInStandardFolder($range_type)
    {
        return ($range_type == 'user');
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
        //folders of this type are visible only for the folder's owner
        if($this->folder) {
            return ($this->folder->user_id == $user_id);
        } else {
            //a non-existant folder isn't visible!
            return false;
        }
    }
    
    
    public function isReadable($user_id)
    {
        //folders of this type are readable only for the folder's owner
        global $perm;
        
        if($this->folder) {
            return ($this->folder->user_id == $user_id);
        } else {
            //a non-existant folder isn't readable!
            return false;
        }
    }
    
    
    public function isWritable($user_id)
    {
        //folders of this type are writable only for the folder's owner
        if($this->folder) {
            return ($this->folder->user_id == $user_id);
        } else {
            //a non-existant folder isn't writable!
            return false;
        }
    }
    
    
    public function isSubfolderAllowed($user_id)
    {
        //no subfolders allowed (This is an inbox, not a standard folder)
        return false;
    }
    
    
    public function getDescriptionTemplate()
    {
        if($this->folder) {
            $user = User::find($this->folder->user_id);
            if($user) {
                return sprintf(
                    _('Nachrichtenanhänge von %s'),
                    $user->getFullName()
                );
            } else {
                return _('Nachrichtenanhänge');
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
}
