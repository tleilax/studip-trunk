<?php
/**
 * MessageFolder.class.php
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
 * Class MessageFolder
 *
 * This is a FolderType implementation for message folders.
 * A message folder contains the attachments of a Stud.IP message.
 */
class MessageFolder implements FolderType
{
    protected $folder;
    protected $message; //folders of this type can be associated with a message object


    /**
     * @param Folder|null folder The folder object for this FolderType
     */
    public function __construct($folder = null)
    {
        if($folder instanceof Folder) {
            $this->folder = $folder;
            $this->message = Message::find($folder->range_id);
        } else {
            $this->folder = new Folder();
            $this->message = null;
        }

    }

    /**
     * Retrieves or creates the top folder for a message.
     *
     * Creating top folders for messages is a special task since
     * message attachments can be stored when the message wasn't sent yet.
     * This means that message attachments of an unsent message are stored
     * in a top folder with a range-ID that doesn't belong to a message
     * table entry (yet). Therefore we must create the top folder
     * manually when we can't find the top folder by the method
     * Folder::getTopFolder.
     *
     * @param string $message_id The message-ID of the message whose top folder shall be returned
     *
     * @return MessageFolder|null The top folder of the message identified by $message_id. If the folder can't be retrieved, null is returned.
     */
    static public function findMessageTopFolder($message_id = null, $user_id = null)
    {
        if(!$message_id) {
            //if no message-ID or no user-ID is given we can't look for a top folder!
            return null;
        }

        //try to find the top folder:
        $folder = Folder::findTopFolder($message_id);

        //check if that was successful:
        if(!$folder) {
            if(!$user_id) {
                //we need the user-ID to create a new top folder!
                return null;
            }
            //no, it wasn't successful: create the folder manually
            $folder = new Folder();
            $folder->user_id = $user_id;
            $folder->range_id = $message_id;
            $folder->range_type = 'message';
            $folder->folder_type = 'MessageFolder';
            $folder->store();
        }

        return new MessageFolder($folder);
    }


    static public function getNumMessageAttachments($message_id = null)
    {
        if(!$message_id) {
            return 0;
        }

        $message_top_folder = self::findMessageTopFolder($message_id);
        if(!$message_top_folder) {
            return 0;
        }

        //return the amount of file references that are logically inside the
        //top folder. This is the amount of message attachments.

        $num_file_ref = FileRef::countBySql(
            'folder_id = :folder_id',
            [
                'folder_id' => $message_top_folder->getId()
            ]
        );

        return $num_file_ref;
    }


    static public function getTypeName()
    {
        return _('Nachrichtenordner');
    }


    public function getIcon($role)
    {
        return Icon::create('folder-message', $role);
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
        return ($user_id == $this->folder->user_id);
    }


    /**
     * A message folder is readable if the user (specified by User-ID)
     * is either the sender or the receiver of the message.
     */
    public function isReadable($user_id)
    {
        if(!$user_id) {
            //How can we check for read permissions when we don't have
            //a user-ID?
            return false;
        }


        if(!$this->message) {
            //We can check the user's permissions without looking at the
            //message as a fallback solution:
            //If the user is the owner of the folder he has read permissions:
            return ($user_id == $this->folder->user_id);
        }


        $read_permission = false;
        //Check if the user_id is the ID of the sender
        //or of one of the receivers. If so, the user has read permissions:

        //If there is at least one entry with the message-ID of this message
        //and the user-ID specified in $user_id, we can grant the user
        //read permissions:
        return (MessageUser::countBySql(
                '(message_id = :message_id) AND (user_id = :user_id)',
                [
                    'message_id' => $this->message->id,
                    'user_id' => $user_id
                ]
            ) > 0);
    }


    public function isWritable($user_id)
    {
        return ($user_id == $this->folder->user_id);
    }

    public function isEditable($user_id)
    {
        return false;
    }


    public function isSubfolderAllowed($user_id)
    {
        if(!$this->folder) {
            //if we haven't got a folder object we can't create a subfolder in it!
            return false;
        }

        if(!$this->message) {
            //if the message object isn't set then a subfolder can't be created
            //since it would result in a folder without a range ID that can't
            //be found!
            return false;
        }

        //if findSendedByMessageId returns something else than null
        //the message was already sent and therefore changing it is not an
        //option!
        if(MessageUser::findSendedByMessageId($this->message->id)) {
            //message was sent!
            return false;
        }

        //check if the user given by $user_id is the owner of this folder.
        //If so, creating a subfolder is permitted!
        return ($user_id == $this->folder->user_id);
    }


    public function getDescriptionTemplate()
    {
        return '';
    }


    public function getSubfolders()
    {
        $subfolders = [];
        if($this->folder) {
            foreach($this->folder->subfolders as $subfolder) {
                $subfolders[] = $subfolder->getTypedFolder();
            }
        }
        return $subfolders;
    }

    public function getFiles()
    {
        if($this->folder) {
            return $this->folder->file_refs->getArrayCopy();
        } else {
            return [];
        }
    }
    
    
    public function getParent()
    {
        if(!$this->folder) {
            return null;
        }
        
        $parent_folder = $this->folder->parentfolder;
        if(!$parent_folder) {
            return null;
        }
        
        return $parent_folder->getTypedFolder();
    }


    public function getEditTemplate()
    {
        if($this->folder) {
            return [
                'parent_id' => $this->folder->parent_id,
                'range_id' => $this->folder->range_id,
                'name' => $this->folder->name,
                'description' => $this->folder->description
            ];
        } else {
            return [];
        }
    }

    public function setDataFromEditTemplate($edit_template)
    {
        //IMPORTANT NOTICE: The attribute name of the folder MUST NOT be editable
        //if the folder is the top folder of a message!
        //This is because the top folder name is set to the message ID
        //(because folders must have a name)
        //and the message's topic is displayed as the top folder's name.
        //Therefore it doesn't make any sense to change the folder name, too.

        $data_changed = false;

        if($this->folder) {
            if(array_key_exists('parent_id', $edit_template)) {
                if($edit_template['parent_id'] &&
                    $this->folder->parent_id != $edit_template['parent_id']) {
                    $data_changed = true;
                    $this->folder->parent_id = $edit_template['parent_id'];
                }
            }

            if(array_key_exists('range_id', $edit_template)) {
                if($edit_template['range_id'] &&
                    $this->folder->range_id != $edit_template['range_id']) {
                    $data_changed = true;
                    $this->folder->range_id = $edit_template['range_id'];
                }
            }


            if(array_key_exists('description', $edit_template)) {
                //description is optional so it can be empty.
                if($this->folder->description != $edit_template['description']) {
                    $data_changed = true;
                    $this->folder->description = $edit_template['description'];
                }
            }

            if($data_changed) {
                //we only want to store data if the folder data were changed:
                $this->folder->store();
            }
        }
    }


    public function validateUpload($uploaded_file, $user_id)
    {
        $status = $GLOBALS['perm']->get_perm($user_id);
        $upload_type = $GLOBALS['UPLOAD_TYPES']['attachments'];
        
        if ($upload_type["file_sizes"][$status] < $uploaded_file['size']) {
            return sprintf(_("Die maximale Größe für einen Upload (%s) wurde überschritten."), relsize($upload_type["file_sizes"][$status]));
        }

        $extension = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
        $types = array_map('strtolower', $upload_type['file_types']);
        if (!in_array($extension, $types) && $upload_type['type'] == 'deny') {
            return sprintf(_("Sie dürfen nur die Dateitypen %s hochladen!"), join(',', $upload_type['file_types']));
        }
        if (in_array($extension, $types) && $upload_type['type'] == 'allow') {
            return sprintf(_("Sie dürfen den Dateityp %s nicht hochladen!"), $extension);
        }
    }


    public function createFile($file)
    {
        if(!$this->folder) {
            return MessageBox::error(_('Datei kann nicht erstellt werden, da kein Ordner angegeben wurde, in dem diese erstellt werden kann!'));
        }

        $new_file = null;
        if (!is_a($file, "File")) {
            $new_file = new File();
            $new_file->name = $file['name'];
            $new_file->mime_type = $file['type'];
            $new_file->size = $file['size'];
            $new_file->storage = 'disk';
            $new_file->id = $new_file->getNewId();
            $new_file->connectWithDataFile($file['tmp_path']);
            $file_ref_data['description'] = $file['description'];
            $file_ref_data['license'] = $file['license'];
            $file_ref_data['content_terms_of_use_id'] = $file['content_terms_of_use_id'];
        } else {
            $new_file = $file;
            $file_ref_data = [];
        }
        if ($new_file->isNew()) {
            $new_file->store();
        }
        $file_ref = $this->folder->linkFile($new_file, array_filter($file_ref_data));
        return $file_ref;
    }


    public function deleteFile($file_ref_id)
    {
        $file_refs = $this->folderdata->file_refs;

        if($file_refs) {
            foreach($file_refs as $file_ref) {
                if($file_ref->id == $file_ref_id) {
                    //we found the FileRef that shall be deleted
                    return $file_ref->delete();
                }
            }
        }

        //if no file refs are present or the file ref can't be found
        //we return false:
        return false;
    }

    public function store()
    {

    }

    public function createSubfolder(FolderType $folderdata)
    {
        $result = [];
        if(array_key_exists($folderdata['name'])) {
            $result = FileManager::createSubfolder(
                $this,
                $this->folder->owner,
                'MessageFolder',
                $folderdata['name'],
                $folderdata['description']
            );
        } else {
            $result = [_('Es wurde kein Ordnername angegeben!')];
        }

        if(is_array($result)) {
            //there were errors during the creation of the subfolder:
            return MessageBox::error(_('Fehler beim Erstellen eines Unterordners!', $result));
        }

        //no errors, we have received a FolderType object:
        return $result;
    }


    public function deleteSubfolder($subfolder_id)
    {
        $folder = Folder::find($subfolder_id);
        if(!$folder) {
            return MessageBox::error(_('Ordner nicht gefunden!'));
        }

        $folder = $folder->getTypedFolder();

        $result = FileManager::deleteFolder(
            $folder,
            $this->folder->owner
        );

        if(!empty($result)) {
            return false;
        }

        return true;
    }


    public function delete()
    {
        return $this->folder->delete();
    }


    public function isFileDownloadable($file_ref_id, $user_id)
    {
        //we have to check if the user ID is the sender
        //or one of the receivers of the message.

        if(!$this->message) {
            //no message? then we can't check if the file is downloadable
            return false;
        }

        //$user_belongs_to_message contains either true or false (see the > 0 below)
        $user_belongs_to_message = MessageUser::countBySql(
                '(message_id = :message_id) AND (user_id = :user_id)',
                [
                    'message_id' => $this->message->id,
                    'user_id' => $user_id
                ]
            ) > 0;

        return $user_belongs_to_message;
    }


    public function isFileEditable($file_ref_id, $user_id)
    {
        //message attachments are never editable!
        return false;
    }


    public function isFileWritable($file_ref_id, $user_id)
    {
        //message attachments are never writable!
        return false;
    }

}