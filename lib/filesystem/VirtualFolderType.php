<?php
class VirtualFolderType implements FolderType
{
    protected $folderdata;
    protected $plugin_id;

    protected $files      = [];
    protected $subfolders = [];

    public function __construct($folderdata = [], $plugin_id = null)
    {
        $this->folderdata = $folderdata;
        $this->plugin_id  = $plugin_id;
    }

    public static function getTypeName()
    {
        return _('Virtueller Ordner');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    public function getIcon($role = 'info')
    {
        return Icon::create('folder-empty', $role);
    }

    public function getId()
    {
        return $this->folderdata['id'];
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

    public function isEditable($user_id)
    {
        return false;
    }

    public function isSubfolderAllowed($user_id)
    {
        return false;
    }

    public function getDescriptionTemplate()
    {
        return null;
    }

    public function getEditTemplate()
    {
        return null;
    }

    public function setDataFromEditTemplate($request)
    {
    }

    public function validateUpload($uploadedfile, $user_id)
    {
        return false;
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
        if (!$this->folderdata['parent_id']) {
            return null;
        }

        if ($this->plugin_id) {
            return PluginManager::getInstance()->getPluginById($this->plugin_id)->getFolder($this->folderdata->parent_id);
        }

        return $this->folderdata->parent_id
             ? $this->folderdata->parentfolder->getTypedFolder()
             : null;
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
        return true;
    }

    public function createSubfolder(FolderType $folderdata)
    {
        $this->subfolders[] = $folderdata;
        return $folderdata;
    }

    public function deleteSubfolder($subfolder_id)
    {
        return true;
    }

    public function delete()
    {
        return true;
    }

    public function store()
    {
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
