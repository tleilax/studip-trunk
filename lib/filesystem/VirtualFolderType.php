<?php

class VirtualFolderType implements FolderType
{

    protected $folderdata = array();
    protected $files = array();
    protected $subfolders = array();


    public function __construct($folderdata = array())
    {
        $this->folderdata = $folderdata;
    }

    static public function getTypeName()
    {
        return _("Virtueller Ordner");
    }

    public function getIcon($role = 'info')
    {
        return Icon::create('folder-empty', $role);
    }

    static public function creatableInStandardFolder($range_type)
    {
        return false;
    }

    public function getId()
    {
        return $this->folderdata->id;
    }

    public function __get($attribute)
    {
        return $this->folderdata[$attribute];
    }

    public function __set($attribute, $value)
    {
        $this->folderdata[$attribute] = $value;
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
        return false;
    }

    public function isSubfolderAllowed($user_id)
    {
        return false;
    }

    public function getDescriptionTemplate()
    {

    }

    public function getEditTemplate()
    {

    }

    public function setDataFromEditTemplate($request)
    {

    }

    public function validateUpload($uploadedfile, $user_id)
    {

    }

    public function getSubfolders()
    {
        return $this->subfolders;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getParent()
    {
        return $this->folderdata->parent_id ? $this->folderdata->parentfolder->getTypedFolder() : null;
    }

    public function createFile($filedata)
    {
        if (is_array($filedata)) {
            $filedata = (object) $filedata;
        }
        $this->files[] = $filedata;
    }
    
    
    public function deleteFile($file_ref_id)
    {
        //TODO
        return true;
    }
    

    public function createSubfolder($folderdata)
    {
        $this->subfolders[] = $folderdata;
        return $folderdata;
    }
    
    public function deleteSubfolder($subfolder_id)
    {
        //TODO
        return true;
    }
    
    
    public function delete()
    {
        //TODO
        return true;
    }
    

    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        return true;
    }

    public function isFileEditable($fileref_or_id, $user_id)
    {
        return false;
    }

    public function isFileWritable($fileref_or_id, $user_id)
    {
        return false;
    }
}