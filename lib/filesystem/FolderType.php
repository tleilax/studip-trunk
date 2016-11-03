<?php
/*
 * FolderType.php
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

/**
 * Interface FolderType
 * This interface is to be implemented by all file-folders. It is like the god-class-interface thingy
 * for all workflows containing folders, may they be standard-folder, homework-folders or virtual owncloud-folder that don't
 * even exist in the database.
 */
interface FolderType
{
    /**
     * Returns the name of the FolderType (not the folder)
     * @return string
     */
    static public function getTypeName();

    /**
     * Returns the name of the icon shape that shall be used with this folder type.
     * 
     * @return string Name of icon shape
     */
    static public function getIconShape();

    public function getId();

    /**
     * @param string $range_type : "course", "user", "institute"
     * @return boolean
     */
    static public function creatableInStandardFolder($range_type);

    /**
     * @param $user_id
     * @return boolean
     */
    public function isVisible($user_id);

    /**
     * @param $user_id
     * @return boolean
     */
    public function isReadable($user_id);

    /**
     * @param $user_id
     * @return boolean
     */
    public function isWritable($user_id);

    /**
     * @param $user_id
     * @return boolean
     */
    public function isSubfolderAllowed($user_id);

    /**
     * @return string
     */
    public function getDescriptionTemplate();

    public function getSubfolders();

    public function getFiles();

    public function getEditTemplate();

    public function setDataFromEditTemplate($request);

    public function validateUpload($file, $user_id);

    public function createFile($file);

    public function isFileDownloadable($file_id);

    public function isFileEditable($file_id);

    public function isFileWritable($file_id);

}