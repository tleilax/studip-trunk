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
     * Returns the download URL of an archive file.
     * 
     * This is a replacement of the getDownloadLink function from datei.inc.php.
     * THIS METHOD MAY BE REPLACED BY A CLASS THAT REPLACES SENDFILE.PHP
     * AND THEREFORE THE NEED TO BUILD DOWNLOAD LINKS!
     * 
     * @param string $file_path The path to a file whose download URL shall be returned.
     * 
     * @return string The download URL of the file. Empty string on failure.
     * 
     * @throws FileArchiveManagerException If the file path is not set.
     */
    public static function getDownloadLink_TEMP($file_path)
    {
        if(!$file_path) {
            throw new FileArchiveManagerException(
                'Cannot retrieve file name: File path is not set!'
            );
        }
        
        $file_name = basename($file_path);
        
        $file_url = $GLOBALS['ABSOLUTE_URI_STUDIP'];
        
        $sendfile_mode = Config::get()->SENDFILE_LINK_MODE ?: 'normal';
        
        if($sendfile_mode == 'rewrite') {
            $file_url .= 'zip/zip'. rawurlencode(prepareFilename($file_name));
        } else {
            //normal mode (default):
            $file_url .= 'sendfile.php?type=4&file_id=' .
                rawurlencode(prepareFilename($file_name)) . 
                '&file_name='.rawurlencode(prepareFilename($file_name));
        }
        
        return $file_url;
    }
    
    
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
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1000000;
        
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
            if($folder->isReadable($user_id) and $folder->isFileDownloadable($file_ref->id, $user->id)) {
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
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1000000;
        
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
                    $do_user_permission_checks,
                    $skip_check_for_user_permissions
                );
            }
        }
        
        foreach($folder->getSubfolders() as $subfolder) {
            self::addFolderToArchive(
                $archive,
                $subfolder,
                $user_id,
                $folder_zip_path,
                $do_user_permission_checks,
                $keep_hierarchy,
                $skip_check_for_user_permissions
            );
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
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1000000;
        
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
     * Extracts an archive into a folder inside the Stud.IP file area.
     * 
     * @param ZipArchive $archive The archive which shall be extracted.
     * @param FolderType $folder The folder where the archive shall be extracted.
     * @param string $user_id The ID of the user who wants to extract the archive.
     */
    public static function extractArchiveToFolder(
        ZipArchive $archive,
        FolderType $folder,
        $user_id = null)
    {
        //to be implemented
    }
}
