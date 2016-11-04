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
    protected $folderdata;
    protected $range_id;
    protected $range_type;


    public function __construct($folderdata)
    {
        $this->folderdata = Folder::buildExisting($folderdata);
    }


    static public function getTypeName()
    {
        return _("Ordner");
    }

    static public function getIconShape()
    {
        return 'folder';
    }

    static public function creatableInStandardFolder($range_type)
    {
        return true;
    }

    public function getId()
    {
        return $this->folderdata->getId();
    }

    public function __get($attribute)
    {
        return $this->folderdata[$attribute];
    }


    public function isVisible($user_id)
    {
        return true;
    }

    public function isReadable($user_id)
    {
        return true;
    }

    public function isWritable($user_id)
    {
        return true;
    }

    public function isSubfolderAllowed($user_id)
    {
        return true;
    }

    public function getDescriptionTemplate()
    {

    }

    public function getEditTemplate()
    {

    }

    public function setDataFromEditTemplate($request)
    {
        foreach ($this->folderdata as $name => $value) {
            $this->folderdata[$name] = $request[$name];
        }
        return $this->folderdata->store();
    }

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

    public function getSubfolders()
    {
        $subfolders = array();
        foreach ($this->folderdata->subfolders as $subfolder) {
            //check FolderType of subfolder
            $subfolders[] = new StandardFolder($subfolder);
        }
        return $subfolders;
    }

    public function getFiles()
    {
        return $this->folderdata->file_refs;
    }

    /**
     * Returns the parent-folder as a StandardFolder
     * @return StandardFolder
     */
    public function getParent()
    {
        return FolderFactory::get()->init($this->folderdata->parentfolder);
    }

    public function createFile($file)
    {
        $this->folderdata->linkFile($file);
    }

    public function isFileDownloadable($file_id)
    {
        return true;
    }

    public function isFileEditable($file_id)
    {
        return true;
    }

    public function isFileWritable($file_id)
    {
        return true;
    }


}