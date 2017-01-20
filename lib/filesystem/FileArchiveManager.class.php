<?php
/**
 * FileArchiveManager.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


class FileArchiveManagerException extends Exception
{
    
}


/**
 * The FileArchiveManager class gives programmers a simple way to handle
 * file archives by providing different methods for packing and unpacking
 * file archives in a simple manner.
 */
class FileArchiveManager
{
    //ARCHIVE HELPER METHODS
    
    
    /**
     * Adds a FileRef to a Zip archive.
     * 
     * @param ZipArchive $archive The Zip archive where the FileRef shall be added to.
     * @param FileRef $file_ref The FileRef which shall be added to the zip archive.
     * @param string $user_id The user who wishes to add the FileRef to the archive.
     * @param string $archive_fs_path The path of the file inside the archive's file system.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $skip_check_for_user_permissions Set to true, if a file
     *     which has no download restrictions shall be included 
     *     and the user-specific download condition check shall be ignored.
     *     If this parameter is set to true, the user_id parameter is irrelevant.
     *     The default for this parameter is false.
     * @return bool True on success, false on failure.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function addFileRefToArchive(
        ZipArchive $archive,
        FileRef $file_ref,
        $user_id = null,
        $archive_fs_path = '',
        $do_user_permission_checks = true,
        $skip_check_for_user_permissions = false
    ) {
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1048576; //1048576 bytes = 1 Mebibyte
        
        //For FileRef objects we first have to do permission checks
        //using the FileRef's folder object.
        
        $adding_allowed = false;
        
        if($do_user_permission_checks) {
            $folder = $file_ref->folder;
            if(!$folder) {
                return false;
            }
            $folder = $folder->getTypedFolder();
            if(!$folder) {
                return false;
            }
            if($folder->isReadable($user_id) and $folder->isFileDownloadable($file_ref->id, $user_id)) {
                //FileRef is readable and downloadable for the user (identified by $user_id).
                $adding_allowed = true;
            }
        } else {
            if($skip_check_for_user_permissions == true) {
                //we have to check the download condition by looking at the
                //terms of use object of the FileRef:
                if($file_ref->terms_of_use) {
                    if($file_ref->terms_of_use->download_condition == 0) {
                        $adding_allowed = true;
                    }
                }
            } else {
                //Totally skip permission checks:
                $adding_allowed = true;
            }
        }
        
        if($adding_allowed) {
            //Adding the FileRef is allowed:
            //Get the file's path (if the file exists) and add the file to the archive:
            $file = $file_ref->file;
            if($file) {
                //Check if the FileRef references a file
                //in the file system or a link:
                if(!$file_ref->isLink()) {
                    //The FileRef references a file:
                    $file_path = $file->getPath();
                    if($file_path and file_exists($file_path)) {
                        $archive->addFile($file_path, $archive_fs_path . $file_ref->name);
                        
                        //The archive file may not exist if it is empty!
                        if(file_exists($archive->filename)) {
                            if(filesize($archive->filename) > $archive_max_size) {
                                throw new FileArchiveManagerException(
                                    "Zip archive is too big! Limit is $archive_max_size bytes!"
                                );
                            }
                        }
                        
                        return true;
                    }
                } else {
                    //The FileRef references a link:
                    //TODO: put the URL into a file ending with .url
                    return true;
                }
            }
        }
        
        //Something must have gone wrong:
        return false;
    }
        
    
    /**
     * Adds a FolderType instance to a Zip archive.
     * 
     * @param ZipArchive $archive The Zip archive where the FileRef shall be added to.
     * @param FileRef $file_ref The FileRef which shall be added to the zip archive.
     * @param string $user_id The user who wishes to add the FileRef to the archive.
     * @param string $archive_fs_path The path of the folder inside the archive's file system.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the folder hierarchy shall be kept.
     *     False, if the folder hierarchy shall be flattened.
     * @param bool $skip_check_for_user_permissions Set to true, if a folder
     *     of type StandardFolder shall be included without checking
     *     if the user (identified by user_id) can read it.
     * @return bool True on success, false on failure.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function addFolderToArchive(
        ZipArchive $archive,
        FolderType $folder,
        $user_id = null,
        $archive_fs_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true,
        $skip_check_for_user_permissions = false
    ) {
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1048576; //1048576 bytes = 1 Mebibyte
        
        if($do_user_permission_checks) {
            //Check if the folder is readable for the user (identified by $user_id):
            if(!$folder->isReadable($user_id)) {
                //Folder is not readable:
                return false;
            }
        } else {
            //If user permissions shall be skipped the folder must be
            //an instance of StandardFolder and the folder's range type
            //must be course or institute since we can only be sure
            //that StandardFolder instances in courses or institutes
            //are readable by everyone.
            if($skip_check_for_user_permissions 
                and !($folder instanceof StandardFolder)
                and (($folder->range_type == 'course')
                    or ($folder->range_type == 'institute'))) {
                return false;
            }
        }
        
        $folder_zip_path = $archive_fs_path;
        if($keep_hierarchy) {
            $folder_zip_path .= $folder->name;
            $archive->addEmptyDir($folder_zip_path);
        }
        foreach($folder->getFiles() as $file_ref) {
            if($keep_hierarchy) {
                //keep hierarchy in zip file (files and subdirectories)
                self::addFileRefToArchive(
                    $archive,
                    $file_ref,
                    $user_id,
                    $folder_zip_path . '/',
                    $do_user_permission_checks,
                    $skip_check_for_user_permissions
                );
            } else {
                //don't keep hierarchy (files only)
                self::addFileRefToArchive(
                    $archive,
                    $file_ref,
                    $user_id,
                    '',
                    $do_user_permission_checks,
                    $skip_check_for_user_permissions
                );
            }
        }
        
        foreach($folder->getSubfolders() as $subfolder) {
            if($keep_hierarchy) {
                self::addFolderToArchive(
                    $archive,
                    $subfolder,
                    $user_id,
                    $folder_zip_path . '/',
                    $do_user_permission_checks,
                    $keep_hierarchy,
                    $skip_check_for_user_permissions
                );
            } else {
                //don't keep hierarchy (files of subfolder only)
                self::addFolderToArchive(
                    $archive,
                    $subfolder,
                    $user_id,
                    '',
                    $do_user_permission_checks,
                    $keep_hierarchy,
                    $skip_check_for_user_permissions
                );
            }
        }
        
        if(file_exists($archive->filename)) {
            if(filesize($archive->filename) > $archive_max_size) {
                throw new FileArchiveManagerException(
                    "Zip archive is too big! Limit is $archive_max_size bytes!"
                );
            }
        }
        
        return true;
    }
    
    
    //ARCHIVE CREATION METHODS
    
    
    /**
     * General method for creating file archives.
     * 
     * This method is a generalisation for all archive creation methods.
     * For easier archive creation you may use the other archive creation
     * methods which work with less arguments.
     * 
     * @param Array $file_area_objects Array of FileRef, FileURL, Folder or FolderType objects.
     *     $file_area_objects may contain a mix between those object types.
     * @param string $user_id The user who wishes to pack files.
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if individual 
     *     reading/downloading permissions shall be checked. False otherwise.
     *     Default is true.
     * @param bool $keep_hierarchy True, if the folder hierarchy shall be kept.
     *     False, if the folder hierarchy shall be flattened. Default is true.
     * @param bool $skip_check_for_user_permissions Set to true, if all files
     *     which have no download restrictions and all folders which are of type
     *     StandardFolder shall be included and the user-specific
     *     download condition check shall be ignored.
     *     If this parameter is set to true, the user_id parameter is irrelevant.
     *     The default for this parameter is false.
     * 
     * @return bool True, if the archive file was created and saved successfully 
     *     at $archive_file_path, false otherwise.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function createArchive(
        $file_area_objects = [],
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true,
        $skip_check_for_user_permissions = false
    ) {
        
        $archive_max_num_files = Config::get()->ZIP_DOWNLOAD_MAX_FILES;
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1048576; //1048576 bytes = 1 Mebibyte
        
        //check if archive path is set:
        if(!$archive_file_path) {
            throw new FileArchiveManagerException(
                'Destination path for file archive is not set!'
            );
        }
        
        //We can create the Zip archive now since its path exists in the file system.
        
        $archive = new ZipArchive();
        if(!$archive->open($archive_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new FileArchiveManagerException(
                'Error opening new ZIP archive!'
            );
        }
        
        //$file_area_objects must be an array!
        //Otherwise we return an empty Zip archive.
        if(!is_array($file_area_objects)) {
            return $archive;
        }
        
        //If there are no File area objects, we can stop here
        //and return an empty Zip archive.
        if(empty($file_area_objects)) {
            return $archive;
        }
        
        
        //Check if more file area objects than allowed shall be packed:
        //In that case, stop here.
        if($archive_max_num_files and (count($file_area_objects) > $archive_max_num_files)) {
            throw new FileArchiveManagerException(
                "Too many file area objects! Limit is $archive_max_num_files!"
            );
        }
        
        
        foreach($file_area_objects as $file_area_object) {
            if($file_area_object instanceof FileRef) {
                self::addFileRefToArchive(
                    $archive,
                    $file_area_object,
                    $user_id,
                    '',
                    $do_user_permission_checks,
                    $skip_check_for_user_permissions
                );
            } elseif(($file_area_object instanceof Folder) or
                ($file_area_object instanceof FolderType)) {
                $folder = $file_area_object;
                if($folder instanceof Folder) {
                    //We use FolderType instances here.
                    $folder = $folder->getTypedFolder();
                }
                
                self::addFolderToArchive(
                    $archive,
                    $folder,
                    $user_id,
                    '',
                    $do_user_permission_checks,
                    $keep_hierarchy,
                    $skip_check_for_user_permissions
                );
            }
        }
        
        if($archive->numFiles > 0) {
            return $archive->close();
        } else {
            //empty archive
            return false;
        }
        
    }
    
    
    
    /**
     * Puts files (identified by their file refs) into one file archive.
     * 
     * @param FileRef[] $file_refs Array of FileRef objects.
     * @param User $user The user who wishes to pack files.
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * 
     * @return bool True, if the archive file was created and saved successfully 
     *     at $archive_file_path, false otherwise.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromFileRefs(
        $file_refs = [],
        User $user,
        $archive_file_path = '',
        $do_user_permission_checks = true
        ){
        
        $archive_max_num_files = Config::get()->ZIP_DOWNLOAD_MAX_FILES;
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE;
        
        if(!$archive_file_path) {
            throw new FileArchiveManagerException(
                'Destination path for file archive is not set!'
            );
        }
        
        
        //We must now collect all the files from these FileRefs and copy them
        //into the new archive file.
        
        return self::createArchive(
            $file_refs,
            $user->id,
            $archive_file_path,
            $do_user_permission_checks,
            false //do not keep the file hierarchy
        );
    }
    
    
    
    /**
     * Creates an archive that contains all files of a course the given user
     * is allowed to download.
     * 
     * @param FolderType $folder The folder whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the course's files into an archive
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     *
     * @return bool True, if the archive file was created and saved successfully 
     *     at $archive_file_path, false otherwise.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromFolder(
        FolderType $folder,
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true)
    {
        $folder_children = [];
        foreach($folder->subfolders as $subfolder) {
            $folder_children[] = $subfolder;
        }
        foreach($folder->file_refs as $file_ref) {
            $folder_children[] = $file_ref;
        }
        
        return self::createArchive(
            $folder_children,
            $archive_file_path,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }
    
    
    
    /**
     * Creates an archive that contains all files of a course the given user
     * is allowed to download.
     * 
     * @param string $course_id The ID of the course whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the course's files into an archive
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     * 
     * @return bool True, if the archive file was created and saved successfully 
     *     at $archive_file_path, false otherwise.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromCourse(
        $course_id = null,
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true)
    {
        $folder = Folder::findTopFolder($course_id);
        if(!$folder) {
            return null;
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            return null;
        }
        
        $folder_children = [];
        foreach($folder->subfolders as $subfolder) {
            $folder_children[] = $subfolder;
        }
        foreach($folder->file_refs as $file_ref) {
            $folder_children[] = $file_ref;
        }
        
        return self::createArchive(
            $folder_children,
            $user_id,
            $archive_file_path,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }
    
    
    /**
     * Creates an archive that contains all files of an institute the given user
     * is allowed to download.
     * 
     * @param string $institute_id The ID of the institute whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the institute's files into an archive
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     * 
     * @return bool True, if the archive file was created and saved successfully 
     *     at $archive_file_path, false otherwise.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromInstitute(
        $institute_id = null,
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true)
    {
        $folder = Folder::findTopFolder($institute_id);
        if(!$folder) {
            return null;
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            return null;
        }
        
        $folder_children = [];
        foreach($folder->subfolders as $subfolder) {
            $folder_children[] = $subfolder;
        }
        foreach($folder->file_refs as $file_ref) {
            $folder_children[] = $file_ref;
        }
        
        return self::createArchive(
            $folder_children,
            $archive_file_path,
            $archive_file_name,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }
    
    
    /**
     * Creates an archive that contains all files of a user, if the current
     * user has root permissions to do this.
     * 
     * @param string $user_id The ID of the user whose files shall be put inside an archive.
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     * 
     * @return bool True, if the archive file was created and saved successfully 
     *     at $archive_file_path, false otherwise.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromUser(
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true
    ) {
        $folder = Folder::findTopFolder($user_id);
        if(!$folder) {
            return null;
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            return null;
        }
        
        $folder_children = [];
        foreach($folder->subfolders as $subfolder) {
            $folder_children[] = $subfolder;
        }
        foreach($folder->file_refs as $file_ref) {
            $folder_children[] = $file_ref;
        }
        
        return self::createArchive(
            $folder_children,
            $archive_file_path,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }
    
    
    /**
     * This method creates an archive with the content of a physical folder
     * (A folder inside the operating system's file system).
     * 
     * @param string $folder_path The path to the physical folder 
     *     which content shall be added to a file archive.
     * @param string $archive_file_path The path to the archive file which
     *     shall be created.
     * 
     * @return True, if all files were added successfully, false otherwise.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs 
     *     a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromPhysicalFolder(
        $folder_path = null,
        $archive_file_path = null
    ) {
        
        if(!$folder_path or !$archive_file_path) {
            //we can't work with empty paths!
            return false;
        }
        
        if(!file_exists($folder_path)) {
            //path to physical folder does not exist!
            throw new FileArchiveManagerException(
                'Physical folder does not exist!'
            );
        }
        
        //Put all the content of the folder inside an archive:
        $archive = Studip\ZipArchive::create($archive_file_path);
        $result = $archive->addFromPath($folder_path);
        $archive->close();
        return $result;
    }
    
    
    
    //ARCHIVE EXTRACTION METHODS
    
    
    /**
     * This is a helper method that builds a subfolder hierarchy inside 
     * a folder by looking at a string representing a file system path.
     * 
     * The variable $path contains a hierarchy of subfolders that shall be created
     * inside the given folder. If $path contains "folder1/folder2/folder3" then
     * the given folder will get a subfolder named "folder1". The folder
     * "folder1" itself will get a subfolder named "folder2" and so on.
     * 
     * @param FolderType $folder The folder where a subfolder path shall be created.
     * @param User $user The user who wishes to create the path.
     * @param string $path The path which shall be created inside $folder.
     * 
     * @return FolderType[] An array with FolderType objects representing
     *     each element of $path.
     */
    public static function createFolderPath(
        FolderType $folder,
        User $user,
        $path = '')
    {
        $folder_path = [];
        
        //first let's check if $path is empty:
        if(!$path) {
            //Empty path means we don't have to create any folder:
            return [];
        }
        
        //now we strip leading and trailing slashes, whitespaces and other characters:
        $path = trim($path);
        $path = trim($path, '/');
        
        //then we convert path into an array of strings:
        $path = explode('/', $path);
        
        
        //now we loop through path and build subfolders:
        $current_folder = $folder;
        foreach($path as $new_folder_name) {
            //first we check if the folder already exists:
            foreach($current_folder->getSubfolders() as $subfolder) {
                if($subfolder->name = $new_folder_name) {
                    //We have found a folder that has the name $new_folder_name:
                    //No need to create a new folder, we can use that folder
                    //and continue with it:
                    $current_folder = $subfolder;
                    $folder_path[] = $subfolder;
                    
                    //start next iteration of the outer foreach loop:
                    continue 2;
                }
            }
            //If code execution has reached this point we have looped
            //throug all subfolders of the current folder and couldn't find
            //any subfolder that matches the name given in $new_folder_name.
            //Therefore we must create a new folder here, if possible:
            
            //Check the user's permissions first:
            if($current_folder->isSubfolderAllowed($user->id)) {
                //Create a subfolder:
                $result = FileManager::createSubfolder(
                    $current_folder,
                    $user,
                    $current_folder->getTypeName(),
                    $new_folder_name
                );
                
                if($result instanceof FolderType) {
                    $folder_path[] = $result;
                }
            }
        }
        return $folder_path;
    }
    
    
    
    /**
     * Extracts one file from an opened archive and stores it in a folder.
     * 
     * TODO: description!!
     * 
     * @return FileRef|null FileRef instance on success, null otherwise.
     */
    public static function extractFileFromArchive(
        ZipArchive $archive,
        $archive_path = null,
        FolderType $target_folder,
        User $user)
    {
        $file_resource = $archive->getStream($archive_path);
        $file_info = $archive->statName($archive_path);
        
        if(!$file_resource) {
            return null;
        }
        
        $file = new File();
        $file->user_id = $user_id;
        $file->name = basename($archive_path);
        $file->mime_type = get_mime_type($file->name);
        $file->size = $file_info['size'];
        $file->storage = 'disk';
        $file->store();
        
        //Ok, we have a file object in the database. Now we must connect
        //it with the data file by extracting the data file into
        //the place, where the file's content has to be placed.
        
        $file_path = pathinfo($file->getPath(), PATHINFO_DIRNAME);
        
        //Create the directory for the file, if necessary:
        if (!is_dir($file_path)) {
            mkdir($file_path);
        }
        
        //Ok, now we read all data from $file_resource and put it into
        //the file's path:
        
        if(file_put_contents($file->getPath(), $file_resource) === false) {
            //Something went wrong: abort and clean up!
            unlink($file->getPath());
            $file->delete();
            return null;
        }
        
        //Ok, we now must create a FileRef:
        
        $file_ref = new FileRef();
        $file_ref->file_id = $file->id;
        $file_ref->folder_id = $target_folder->getId();
        $file_ref->user_id = $user->id;
        $file_ref->name = $file->name;
        if($file_ref->store()) {
            return $file_ref;
        } else {
            //Something went wrong: abort and clean up!
            unlink($file->getPath());
            $file_ref->delete();
            return null;
        }
    }
    
    
    
    /**
     * Extracts an archive into a folder inside the Stud.IP file area.
     * 
     * @param FileRef $archive_file_ref The archive file which shall be extracted.
     * @param FolderType $folder The folder where the archive shall be extracted.
     * @param string $user_id The ID of the user who wants to extract the archive.
     * 
     * @return FileRef[] Array with extracted files, represented as FileRef objects.
     */
    public static function extractArchiveFileToFolder(
        FileRef $archive_file_ref,
        FolderType $folder,
        $user_id = null)
    {
        global $TMP_PATH;
        
        if(!$user_id) {
            return [];
        }
        
        $user = User::find($user_id);
        
        if(!$user) {
            return [];
        }
        
        
        //Determine, if the folder is writable for the user identified by $user_id:
        if(!$folder->isWritable($user_id)) {
            return [];
        }
        
        //Determine if we can keep the zip archive's folder hierarchy:
        $keep_hierarchy = $folder->isSubfolderAllowed($user_id);
        //$keep_hierarchy = false;
        
        $archive = new ZipArchive();
        $archive->open($archive_file_ref->file->getPath());
        
        //loop over all entries in the zip archive and put each entry
        //in the current folder or one of its subfolders:
        
        $file_refs = [];
        
        for($i = 0; $i < $archive->numFiles; $i++) {
            $file_info = $archive->statIndex($i);
            
            $extracted_file_folder = $folder;
            
            if($keep_hierarchy) {
                //Extract the path from the Archive's file entry.
                //We also have to trim any . at the begin of the string to
                //avoid a path named '.';
                $file_archive_path = ltrim(pathinfo($file_info['name'], PATHINFO_DIRNAME), '.');
                
                if(basename($file_archive_path) != '') {
                    //The file doesn't lie in the "top folder" of the archive:
                    //Pass the path to createFolderPath and let it generate
                    //a folder path before extracting the file:
                    $folder_path = self::createFolderPath(
                        $folder,
                        $user,
                        $file_archive_path
                    );
                    
                    //Get the last element of $folder_path:
                    $last_folder_path = array_pop($folder_path);
                    
                    //Compare $extracted_file_folder's name with the name of the
                    //last path item in $file_archive_path. Only if they are equal
                    //we can use that folder to store the file. Otherwise
                    //we must continue with the next file entry in the archive:
                    if($last_folder_path->name == basename($file_archive_path)) {
                        $extracted_file_folder = $last_folder_path;
                    } else {
                        continue;
                    }
                }
            }
            $file_ref = self::extractFileFromArchive(
                $archive,
                $file_info['name'],
                $extracted_file_folder,
                $user
            );
            
            if($file_ref instanceof FileRef) {
                $file_refs[] = $file_ref;
            }
        }
        
        return $file_refs;
    }
}
