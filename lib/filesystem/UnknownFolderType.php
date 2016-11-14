<?php
/**
 * UnknownFolderType.php
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
class UnknownFolderType implements FolderType
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
        return _("Unbekannter Ordner Typ");
    }

    /**
     * @return Icon
     */
    public function getIcon($role = 'info')
    {
        return Icon::create('brokenfolder', $role);
    }

    /**
     * @param string $range_type
     * @return bool
     */
    static public function creatableInStandardFolder($range_type)
    {
        return false;
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
        if ($attribute == 'name') {
            return $this->folderdata['name'] . sprintf(' (unbekannter Typ: %s)', $this->folderdata['folder_type']);
        }
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
        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isWritable($user_id)
    {
        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isSubfolderAllowed($user_id)
    {
        return false;
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

    public function setDataFromEditTemplate($request)
    {

    }

    public function validateUpload($uploadedfile, $user_id)
    {

    }

    /**
     * @return FolderType[]
     */
    public function getSubfolders()
    {
        return array();
    }

    /**
     * @return FileRef[]
     */
    public function getFiles()
    {
        return array();
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

    }
    
    
    public function deleteFile($file_ref_id)
    {
        return false;
    }
    

    public function createSubfolder($folderdata)
    {

    }
    
    
    public function deleteSubfolder($subfolder_id)
    {
        return false;
    }
    

    /**
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        return false;
    }

    /**
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileEditable($fileref_or_id, $user_id)
    {
        return false;
    }

    /**
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileWritable($fileref_or_id, $user_id)
    {
        return false;
    }
}