<?php


class PublicFolder implements FolderType
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
        return _('Ein Ordner für öffentlich zugängliche Daten');
    }
    
    
    static public function getIconShape()
    {
        return 'folder-full';
    }
    
    
    public function getId()
    {
        return 1;
    }
    
    
    static public function creatableInStandardFolder($range_type)
    {
        return true;
    }
    
    
    static public function getAllowedRangeTypes()
    {
        return ['user'];
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
        return $user_id === $GLOBALS['user']->id;
    }

    
    public function isSubfolderAllowed($user_id)
    {
        return $user_id === $GLOBALS['user']->id;
    }
    
    
    public function getDescriptionTemplate()
    {
        return _("Öffentlich sichtbar für alle.");
    }


    public function getSubfolders()
    {
        //to be implemented
    }
    
    
    public function getFiles()
    {
        //to be implemented
    }
    
    
    public function getEditTemplate()
    {
        //to be implemented
    }
    
    
    public function setDataFromEditTemplate($request)
    {
        //to be implemented
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
    
    
    public function createFile($file)
    {
        //to be implemented
    }
    
    
    public function isFileDownloadable($file_id, $user_id)
    {
        //to be implemented
    }
    
    
    public function isFileEditable($file_id, $user_id)
    {
        //to be implemented
    }
    
    
    public function isFileWritable($file_id, $user_id)
    {
        //to be implemented
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



    public function getName()
    {
        return $this->folderdata['name'];
    }

    public function getIcon()
    {
        return Icon::create('folder');
    }


    public function setData($request)
    {
    }


}