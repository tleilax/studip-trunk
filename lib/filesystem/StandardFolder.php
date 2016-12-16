<?php
/**
 * StandardFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class StandardFolder implements FolderType
{
    /**
     * @var Folder
     */
    protected $folderdata;

    /**
     * StandardFolder constructor.
     * @param Folder|null $folderdata
     */
    public function __construct($folderdata = null)
    {
        if ($folderdata instanceof Folder) {
            $this->folderdata = $folderdata;
        } else {
            $this->folderdata = Folder::build($folderdata);
        }
        $this->folderdata['folder_type'] = get_class($this);
    }


    /**
     * @return string
     */
    static public function getTypeName()
    {
        return _("Ordner ohne besondere Merkmale");
    }

    /**
     * @return Icon
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        if ($this->parent_id) {
            $shape = count($this->getSubfolders()) + count($this->getFiles()) == 0 ? 'folder-empty' : 'folder-full';
        } else {
            $shape = count($this->getSubfolders()) + count($this->getFiles()) == 0 ? 'folder-home-empty' : 'folder-home-full';
        }
        return Icon::create($shape, $role);
    }

    /**
     * @param string $range_type
     * @return bool
     */
    static public function creatableInStandardFolder($range_type)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->folderdata->getId();
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return $this->folderdata[$attribute];
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->folderdata[$name] = $value;
    }


    /**
     * @param $user_id
     * @return bool
     */
    public function isVisible($user_id)
    {
        return ($this->range_type == 'user' && $this->range_id == $user_id) || Seminar_Perm::get()->have_studip_perm('user', $this->range_id, $user_id);
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isReadable($user_id)
    {
        return ($this->range_type == 'user' && $this->range_id == $user_id) || Seminar_Perm::get()->have_studip_perm('user', $this->range_id, $user_id);
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isWritable($user_id)
    {
        return ($this->range_type == 'user' && $this->range_id == $user_id) || Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isSubfolderAllowed($user_id)
    {
        return ($this->range_type == 'user' && $this->range_id == $user_id) || Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     *
     */
    public function getDescriptionTemplate()
    {

    }

    /**
     *
     */
    public function getEditTemplate()
    {

    }

    /**
     * @param $request
     * @return FolderType|MessageBox
     */
    public function setDataFromEditTemplate($request)
    {
        if (!$request['name']) {
            return MessageBox::error(_("Die Bezeichnung des Ordners fehlt."));
        }
        $this->folderdata['name'] = $request['name'];
        if($request['description'] !== null) {
            //A database error occurs when description is null!
            $this->folderdata['description'] = $request['description'];
        }
        return $this;
    }

    public function store()
    {
        return $this->folderdata->store();
    }

    /**
     * @param $uploadedfile
     * @param $user_id
     * @return string
     */
    public function validateUpload($uploadedfile, $user_id)
    {
        if ($this->range_type == 'course') {
            $status = $GLOBALS['perm']->get_studip_perm($this->range_id, $user_id);
            $active_upload_type = Course::find($this->range_id)->status;
        } elseif ($this->range_type == 'institute') {
                $status = $GLOBALS['perm']->get_studip_perm($this->range_id, $user_id);
                $active_upload_type = 'institute';
        } else {
            $status = $GLOBALS['perm']->get_perm($user_id);
            $active_upload_type = "personalfiles";
        }
        if (!isset($GLOBALS['UPLOAD_TYPES'][$active_upload_type])) {
            $active_upload_type = 'default';
        }
        $upload_type = $GLOBALS['UPLOAD_TYPES'][$active_upload_type];
        if ($upload_type["file_sizes"][$status] < $uploadedfile['size']) {
            return sprintf(_("Die maximale Größe für einen Upload (%s) wurde überschritten."), relsize($upload_type["file_sizes"][$status]));
        }
        $ext = strtolower(pathinfo($uploadedfile['name'], PATHINFO_EXTENSION));
        $types = array_map('strtolower', $upload_type['file_types']);
        if (!in_array($ext, $types) && $upload_type['type'] == 'deny') {
            return sprintf(_("Sie dürfen nur die Dateitypen %s hochladen!"), join(',', $upload_type['file_types']));
        }
        if (in_array($ext, $types) && $upload_type['type'] == 'allow') {
            return sprintf(_("Sie dürfen den Dateityp %s nicht hochladen!"), $ext);
        }
    }

    /**
     * @return FolderType[]
     */
    public function getSubfolders()
    {
        $subfolders = array();
        foreach ($this->folderdata->subfolders as $subfolder) {
            //check FolderType of subfolder
            $subfolders[] = $subfolder->getTypedFolder();
        }
        return $subfolders;
    }

    /**
     * @return FileRef[]
     */
    public function getFiles()
    {
        return $this->folderdata->file_refs->getArrayCopy();
    }

    /**
     * Returns the parent-folder as a StandardFolder
     * @return FolderType
     */
    public function getParent()
    {
        return $this->folderdata->parentfolder ? $this->folderdata->parentfolder->getTypedFolder() : null;
    }

    /**
     * @param $file
     */
    public function createFile($file)
    {
        if (!is_a($file, "File")) {
            $newfile = new File();
            $newfile->name = $file['name'];
            $newfile->mime_type = $file['type'];
            $newfile->size = $file['size'];
            $newfile->storage = 'disk';
            $newfile->id = $newfile->getNewId();
            $newfile->connectWithDataFile($file['tmp_path']);
            $file_ref_data['description'] = $file['description'];
            $file_ref_data['content_terms_of_use_id'] = $file['content_terms_of_use_id'];
        } else {
            $newfile = $file;
            $file_ref_data = [];
        }
        if ($newfile->isNew()) {
            $newfile->store();
        }
        $file_ref = $this->folderdata->linkFile($newfile, array_filter($file_ref_data));
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


    public function createSubfolder(FolderType $foldertype)
    {
        $foldertype->range_id = $this->folderdata['range_id'];
        $foldertype->range_type = $this->folderdata['range_type'];
        $foldertype->parent_id = $this->folderdata['id'];
        return $foldertype->store();

    }


    public function deleteSubfolder($subfolder_id)
    {
        $subfolders = $this->folderdata->subfolders;

        if($subfolders) {
            foreach($subfolders as $subfolder) {
                if($subfolder->id == $subfolder_id) {
                    //we found the subfolder that shall be deleted
                    return $subfolder->delete();
                }
            }
        }

        //if no subfolders are present or the subfolder can't be found
        //we return false:
        return false;
    }


    public function delete()
    {
        return $this->folderdata->delete();
    }


    /**
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        if ($this->range_type === 'user') {
            return $user_id === $this->range_id;
        }
        if (in_array($this->range_type, ['course', 'institute'])) {
            if (is_object($fileref->terms_of_use)) {
                //terms of use are defined for this file!
                return $fileref->terms_of_use->fileIsDownloadable($fileref, $user_id);
            }
            return $GLOBALS['perm']->have_studip_perm('user', $this->range_id, $user_id);
        }

        return true;
    }

    /**
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileEditable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        return $fileref->user_id == $user_id ||
        $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * Checks if a user has write permissions to a file.
     *
     * For standard folders write permissions are granted
     * if the user is the owner of the file or if the user has at least
     * tutor permissions on the Stud.IP object specified by range_id
     * (such objects may be courses or institutes for example).
     *
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileWritable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        return $fileref->user_id == $user_id ||
        $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }

}
