<?php
/**
 * StandardFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Andr� Noack <noack@data-quest.de>
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
        $this->setFolderData($folderdata);
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
    
    public function getName()
    {
        return $this->folderdata['name'];
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
    

    public function getFolderData()
    {
        return $this->folderdata;
    }

    public function setFolderData($folderdata)
    {
        $this->folderdata = $folderdata;
        $this->range_id = $folderdata['range_id'];
        $this->range_type = $folderdata['range_type'];
    }



    public function getDescriptionTemplate()
    {

    }

    public function getEditTemplate()
    {

    }

    public function setData($request)
    {
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
            return sprintf(_("Die maximale Gr��e f�r einen Upload (%s) wurde �berschritten."), relsize($upload_type["file_sizes"][$status]));
        }
        $ext = strtolower(pathinfo($uploadedfile['name'], PATHINFO_EXTENSION));
        $types = array_map('strtolower', $upload_type['file_types']);
        if (!in_array($ext, $types) && $upload_type['type'] == 'deny') {
            return sprintf(_("Sie d�rfen nur die Dateitypen %s hochladen!"), join(',', $upload_type['file_types']));
        }
        if (in_array($ext, $types) && $upload_type['type'] == 'allow') {
            return sprintf(_("Sie d�rfen den Dateityp %s nicht hochladen!"), $ext);
        }
    }

}
