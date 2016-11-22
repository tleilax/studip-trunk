<?php
/**
 * OutboxFolder.class.php
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
 * Class OutboxFolder
 * 
 * This is a FolderType implementation for file attachments of messages
 * that were sent by a user. It is a read-only folder.
 */
class OutboxFolder implements FolderType
{
    protected $user;
    protected $folder;
    
    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
        $this->user = User::find($folder->user_id);
    }
    
    public function __get($attribute)
    {
        return $this->folder[$attribute];
    }
    
    
    static public function getTypeName()
    {
        return _('Ein Ordner für Anhänge gesendeter Nachrichten');
    }
    
    
    public function getIcon($role)
    {
        //TODO: special icon for this folder type
        return Icon::create('folder-empty', $role);
    }
    
    public function getId()
    {
        return $this->folder->id;
    }
    
    static public function creatableInStandardFolder($range_type)
    {
        return ($range_type == 'user');
    }
    
    public function isVisible($user_id)
    {
        return ($user_id == $this->user->id);
    }
    
    public function isReadable($user_id)
    {
        return ($user_id == $this->user->id);
    }
    
    public function isWritable($user_id)
    {
        return false;
    }
    
    public function isSubfolderAllowed($user_id)
    {
        //this folder type does not allow subfolders!
        return false;
    }
    
    public function getDescriptionTemplate()
    {
        return [];
    }
    
    public function getParent()
    {
        if($this->folder->parentFolder) {
            return $this->folder->parentFolder->getTypedFolder();
        } else {
            return null;
        }
    }
    
    public function getSubfolders()
    {
        //no subfolders allowed!
        return [];
    }
    
    public function getFiles()
    {
        //get all folders of the user that belongs to a received message:
        $message_folders = Folder::findBySql(
            "INNER JOIN message_user ON folders.range_id = message_user.message_id
            WHERE
            folders.range_type = 'message'
            AND
            message_user.user_id = :user_id
            AND
            message_user.snd_rec = 'snd'",
            [
                'user_id' => $this->user->id
            ]
        );
        
        $files = [];
        
        foreach($message_folders as $folder) {
            $file_refs = FileRef::findBySql(
                'folder_id = :folder_id',
                [
                    'folder_id' => $folder->id
                ]
            );
            $files = array_merge($files, $file_refs);
        }
        
        return $files;
    }
    
    public function getEditTemplate()
    {
        return [];
    }
    
    public function setDataFromEditTemplate($folderdata)
    {
        return MessageBox::error(_('Outbox-Ordner können nicht bearbeitet werden!'));
    }
    
    public function validateUpload($file, $user_id)
    {
        //no uploads allowed
        return false;
    }
    
    public function createFile($file)
    {
        return MessageBox::error(_('In Outbox-Ordnern können keine Dateien erzeugt werden!'));
    }
    
    public function deleteFile($file_ref_id)
    {
        return false;
    }
    
    public function createSubfolder($folderdata)
    {
        return MessageBox::error(_('In Outbox-Ordnern können keine nutzerdefinierten Unterordner erzeugt werden!'));
    }
    
    public function deleteSubfolder($subfolder_id)
    {
        //subfolders must not be deleted!
        return false;
    }
    
    public function isFileDownloadable($file_ref_id, $user_id)
    {
        return ($user_id == $this->user->id);
    }
    
    public function isFileEditable($file_ref_id, $user_id)
    {
        //files shall be unchanged in here
        return false;
    }
    
    public function isFileWritable($file_ref_id, $user_id)
    {
        //files shall be unchanged in here
        return false;
    }
}
