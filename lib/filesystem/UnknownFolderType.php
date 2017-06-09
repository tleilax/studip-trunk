<?php
/**
 * UnknownFolderType.php
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
class UnknownFolderType implements FolderType
{
    /**
     * @var Folder
     */
    protected $folderdata;

    /**
     * StandardFolder constructor.
     * @param Folder|null $folderdata
     */
    public function __construct($folderdata)
    {
        if ($folderdata instanceof Folder) {
            $this->folderdata = $folderdata;
        } else {
            $this->folderdata = new Folder();
        }
    }

    /**
     * @return string
     */
    public static function getTypeName()
    {
        return _('Unbekannter Ordner Typ');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    /**
     * @return Icon
     */
    public function getIcon($role = 'info')
    {
        return Icon::create('folder-broken', $role);
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
        if ($attribute === 'name') {
            return $this->folderdata['name'] . sprintf(
                _(' (unbekannter Typ: %s)'),
                $this->folderdata['folder_type']
            );
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
    public function isEditable($user_id)
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
        return [];
    }

    /**
     * @return FileRef[]
     */
    public function getFiles()
    {
        return [];
    }

    /**
     * Returns the parent-folder as a StandardFolder
     * @return FolderType
     */
    public function getParent()
    {
        return $this->folderdata->parentfolder
             ? $this->folderdata->parentfolder->getTypedFolder()
             : null;
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

    public function createSubfolder(FolderType $folderdata)
    {

    }

    public function deleteSubfolder($subfolder_id)
    {
        return false;
    }

    public function delete()
    {
        return false;
    }

    public function store()
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
