<?php
/**
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
 *
 * The FolderType interface defines methods for Folder objects
 * and other kinds of Folder like objects regarding permissions and access to
 * subfolders and files.
 *
 * Using the folder_type attribute of a Folder object it is possible to
 * associate a FolderType object to a Folder object. A FolderType object
 * provides information about permissions for the Folder object.
 *
 * By separating permission information from the Folder class and defining them
 * in FolderType implementations it it possible to define a lot of different
 * FolderType implementations that can use the same table in the database
 * (using the Folder SORM class).
 *
 * Furthermore it is possible to use other kinds of Folder-like objects that
 * aren't stored in the database but which are accessible via the Stud.IP
 * file system with this interface. These folders probably would have their own
 * FolderType implementation which then can be integrated into the Stud.IP
 * file system like ordinary Stud.IP folders. This means that sources
 * for files and folders outside the Stud.IP system can become accessible
 * in Stud.IP and that they can be used like Stud.IP file system objects.
 */
interface FolderType
{
    /**
     * Returns a human-friendly representation of the FolderType's name.
     *
     * @return string A human-friendly name for the FolderType implementation.
     */
    static public function getTypeName();

    /**
     * Returns the name of the icon shape that shall be used with the FolderType implementation.
     *
     * @return string Name of the icon shape for the FolderType implementation.
     */
    public function getIcon($role);

    public function getId();

    /**
     * This method tells if the FolderType implementation can be created in a specific range type.
     *
     * Some FolderType implementations aren't useful in conjunction with a
     * specific range type. An FolderType implementation therefore must provide
     * this method so that other parts of the file area system can easily check
     * if folders of the FolderType implementation can be placed inside
     * standard folders.
     *
     * @param string $range_type : "course", "user", "institute", "message"
     * @return boolean True, if creatable in standard folder, false otherwise.
     */
    static public function creatableInStandardFolder($range_type);


    /**
     * Determines if a user may see the folder.
     *
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

    /**
     * Must create the file and return the FileRef object.
     * @param $file
     * @return mixed
     */
    public function createFile($file);

    public function isFileDownloadable($file_id, $user_id);

    public function isFileEditable($file_id, $user_id);

    public function isFileWritable($file_id, $user_id);

}