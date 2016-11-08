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
     * @param Folder $folderdata
     */
    public function __construct(Folder $folderdata)
    {
        $this->folderdata = $folderdata;
    }


    /**
     * @return string
     */
    static public function getTypeName()
    {
        return _("Ordner");
    }

    /**
     * @return string
     */
    static public function getIconShape()
    {
        return 'folder';
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
     * @param $user_id
     * @return bool
     */
    public function isVisible($user_id)
    {
        return true;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isReadable($user_id)
    {
        return true;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isWritable($user_id)
    {
        return ($this->range_type == 'user' && $GLOBALS['user']->id == $user_id) || Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isSubfolderAllowed($user_id)
    {
        return true;
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
     * @return bool|number
     */
    public function setDataFromEditTemplate($request)
    {
        foreach ($this->folderdata as $name => $value) {
            $this->folderdata[$name] = $request[$name];
        }
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
        return FolderFactory::get()->init($this->folderdata->parentfolder);
    }

    /**
     * @param $file
     */
    public function createFile($file)
    {
        $this->folderdata->linkFile($file);
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
                return $fileref->terms_of_use->isDownloadable($fileref, $user_id);
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