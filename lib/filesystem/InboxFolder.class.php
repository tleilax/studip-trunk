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
 * Class InboxFolder
 * 
 * This is a FolderType implementation for file attachments of messages
 * that were received by a user. It is a read-only folder.
 */
class InboxFolder extends InboxOutboxFolder
{
    /*
    //attributes and constructor of InboxOutboxFolder (just for your convenience):
    protected $user;
    protected $folder;
    
    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
        $this->user = User::find($folder->user_id);
    }
    */
    
    
    static public function getTypeName()
    {
        return _('Ein Ordner für Anhänge eingegangener Nachrichten');
    }
    
    
    public function getIcon($role)
    {
        return Icon::create(count($this->getFiles()) ? 'folder-inbox-full' : 'folder-inbox-empty', $role);
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
            message_user.snd_rec = 'rec'",
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
    
    
    public function setDataFromEditTemplate($folderdata)
    {
        return MessageBox::error(_('Inbox-Ordner können nicht bearbeitet werden!'));
    }
    
    
    public function createFile($file)
    {
        return MessageBox::error(_('In Inbox-Ordnern können keine Dateien erzeugt werden!'));
    }
    
    
    public function createSubfolder($folderdata)
    {
        return MessageBox::error(_('In Inbox-Ordnern können keine nutzerdefinierten Unterordner erzeugt werden!'));
    }
}
