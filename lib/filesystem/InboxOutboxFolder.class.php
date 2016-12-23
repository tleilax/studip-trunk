<?php
/**
 * InboxOutboxFolder.class.php
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
 * Class InboxOutboxFolder
 *
 * This is a common FolderType implementation for inbox and outbox folders.
 * It it not meant to be used directly! Instead use the InboxFolder and
 * OutboxFolder extensions of this class!
 */
class InboxOutboxFolder implements FolderType
{
    protected $user;
    protected $folder;

    public function __construct($folder)
    {
        if($folder instanceof Folder) {
            $this->folder = $folder;
            $this->user = User::find($folder->user_id);
        } else {
            $this->folder = new Folder();
            $this->user = null;
        }
    }


    public function __get($attribute)
    {
        return $this->folder[$attribute];
    }


    static public function getTypeName()
    {
        return _('InboxOutboxFolder');
    }


    public function getIcon($role)
    {
        return Icon::create(count($this->getFiles()) ? 'folder-full' : 'folder-empty', $role);
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
        if($this->user) {
            return ($user_id == $this->user->id);
        } else {
            return false;
        }
    }

    public function isReadable($user_id)
    {
        if($this->user) {
            return ($user_id == $this->user->id);
        } else {
            return false;
        }
    }

    public function isWritable($user_id)
    {
        return false;
    }

    public function isEditable($user_id)
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
        //this folder type is not meant to be used directly, so no files
        //are returned:
        return [];
    }

    public function getEditTemplate()
    {
        return [];
    }

    public function setDataFromEditTemplate($folderdata)
    {
        return MessageBox::error(_('InboxOutbox-Ordner können nicht bearbeitet werden!'));
    }
    public function store()
    {

    }

    public function validateUpload($file, $user_id)
    {
        //no uploads allowed
        return false;
    }

    public function createFile($file)
    {
        return MessageBox::error(_('In InboxOutbox-Ordnern können keine Dateien erzeugt werden!'));
    }

    public function deleteFile($file_ref_id)
    {
        return false;
    }

    public function createSubfolder(FolderType $folderdata)
    {
        throw new UnexpectedValueException(_('In InboxOutbox-Ordnern können keine nutzerdefinierten Unterordner erzeugt werden!'));
    }

    public function deleteSubfolder($subfolder_id)
    {
        //there are no subfolders, so they can't be deleted:
        return false;
    }


    public function delete()
    {
        return $this->folder->delete();
    }


    public function isFileDownloadable($file_ref_id, $user_id)
    {
        if($this->user) {
            return ($user_id == $this->user->id);
        } else {
            return false;
        }
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
