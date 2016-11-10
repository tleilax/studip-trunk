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

    /**
     * Returns the ID of the folder that is managed from this FolderType instance.
     * @return string ID of the folder.
     */
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
     * Determines if a user may read the content of the folder.
     * @param $user_id The user who wishes to read the folder's content.
     * @return boolean True, if the user is permitted to read the folder, false otherwise.
     */
    public function isReadable($user_id);

    /**
     * Determines if a user may have write permissions for the folder.
     * @param $user_id The user who wishes to write into the folder.
     * @return boolean True, if the user is permitted to write into the folder, false otherwise.
     */
    public function isWritable($user_id);

    /**
     * Determines if a user may create a subfolder in this folder.
     * @param $user_id The user who wishes to create a subfolder.
     * @return boolean True, if the user is permitted to create a subfolder, false otherwise.
     */
    public function isSubfolderAllowed($user_id);

    /**
     * 
     * @return string
     */
    public function getDescriptionTemplate();

    /**
     * Returns a list of subfolders of this folder.
     * @return Array List of folder objects
     */
    public function getSubfolders();

    
    /**
     * Returns a list of files of this folder.
     * @return Array List of FileRef objects
     */
    public function getFiles();

    public function getEditTemplate();

    /**
     * @param ArrayAccess|Array $folderdata
     * @return FolderType|MessageBox : FolderType of the edited folder or MessageBox if an error occured
     */
    public function setDataFromEditTemplate($folderdata);

    /**
     * Validates a file upload.
     * 
     * @param mixed file The file to be validated.
     * @param string user_id The ID of the user who uploaded the file.
     */
    public function validateUpload($file, $user_id);

    /**
     * Must create the file and return the FileRef object.
     * @param ArrayAccess|Array $file
     * @return File|MessageBox : File of the created file or MessageBox if an error occured
     */
    public function createFile($file);

    /**
     * @param ArrayAccess|Array $folderdata
     * @return FolderType|MessageBox : FolderType of the created folder or MessageBox if an error occured
     */
    public function createSubfolder($folderdata);

    /**
     * Determines if a user may download the file.
     * @param $file_id The file that shall be downloaded.
     * @param $user_id The user who wishes to download the file.
     * @return boolean True, if the user is permitted to download the file, false otherwise.
     */
    public function isFileDownloadable($file_id, $user_id);

    /**
     * Determines if a user may edit the file.
     * @param $file_id The file that shall be edited.
     * @param $user_id The user who wishes to edit the file.
     * @return boolean True, if the user is permitted to edit the file, false otherwise.
     */
    public function isFileEditable($file_id, $user_id);

    /**
     * Determines if a user may write to the file.
     * @param $file_id The file that shall be written.
     * @param $user_id The user who wishes to write to the file.
     * @return boolean True, if the user is permitted to write to the file, false otherwise.
     */
    public function isFileWritable($file_id, $user_id);

}