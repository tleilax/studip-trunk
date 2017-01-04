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
     * 
     * @param ZipArchive $archive The archive whose download URL shall be returned.
     * 
     * @return string The download URL of the archive. Empty string on failure.
     */
    public static function getArchiveUrl(ZipArchive $archive)
    {
        $zip_file_path = $archive->filename;
        
        if(!$zip_file_path) {
            //Archive does not exist or was closed.
            return '';
        }
        
        $zip_file_name = basename($zip_file_path);
        
        $archive_url = $GLOBALS['ABSOLUTE_URI_STUDIP'];
        
        $sendfile_mode = Config::get()->SENDFILE_LINK_MODE ?: 'normal';
        
        if($sendfile_mode == 'rewrite') {
            $archive_url .= 'zip/zip'. rawurlencode(prepareFilename($zip_file_name));
        } else {
            //normal mode (default):
            $archive_url .= 'sendfile.php?type=4&file_id=' .
                rawurlencode(prepareFilename($zip_file_name)) . 
                '&file_name='.rawurlencode(prepareFilename($zip_file_name));
        }
        
        return $archive_url;
    }
    
    
    /**
     * Adds a FileRef to a Zip archive.
     * 
     * @param ZipArchive $archive The Zip archive where the FileRef shall be added to.
     * @param FileRef $file_ref The FileRef which shall be added to the zip archive.
     * @param string $user_id The user who wishes to add the FileRef to the archive.
     * @param string $archive_path The path of the folder inside the archive.
     * 
     * @return bool True on success, false on failure.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function addFileRefToArchive(
        ZipArchive $archive,
        FileRef $file_ref,
        $user_id = null,
        $archive_path = ''
    ) {
        //For FileRef objects we first have to do permission checks
        //using the FileRef's folder object.
        $file_ref = $file_area_object;
        $folder = $file_ref->folder;
        if($folder) {
            $folder = $folder->getTypedFolder();
            if($folder) {
                if($folder->isReadable($user_id) and $folder->isFileDownloadable($file_ref->id, $user->id)) {
                    //FileRef is readable and downloadable for the user (identified by $user_id):
                    //Get the file's path (if the file exists) and add the file to the archive:
                    $file = $file_ref->file;
                    if($file) {
                        //Check if the FileRef references a file
                        //in the file system or a link:
                        if(!$file_ref->isLink()) {
                            //The FileRef references a file:
                            $file_path = $file->getPath();
                            if($file_path and file_exists($file_path)) {
                                $archive->addFile($file_path, $archive_path . $file_ref->name);
                                return true;
                            }
                        } else {
                            //The FileRef references a link:
                            //TODO: put the URL into a file ending with .url
                            return true;
                        }
                    }
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
     * @param bool $keep_hierarchy True, if the folder hierarchy shall be kept.
     *     False, if the folder hierarchy shall be flattened.
     * @param string $archive_path The path of the folder inside the archive.
     * 
     * @return bool True on success, false on failure.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function addFolderToArchive(
        ZipArchive $archive,
        FolderType $folder,
        $user_id = null,
        $keep_hierarchy = true,
        $archive_path = ''
    ) {
        //Check if the folder is readable for the user (identified by $user_id):
        if($folder->isReadable($user_id)) {
            $folder_zip_path = $archive_path;
            if($keep_hierarchy) {
                $folder_zip_path .= $folder->name;
                $archive->addEmptyDir($folder_zip_path);
            }
            foreach($folder->getFiles() as $file_ref) {
                if($keep_hierarchy) {
                    //keep hierarchy in zip file (files and subdirectories)
                    self::addFileRefToArchive($archive, $file_ref, $user_id, $folder_zip_path . '/');
                } else {
                    //don't keep hierarchy (files only)
                    self::addFileRefToArchive($archive, $file_ref, $user_id, $archive_path);
                }
            }
            
            foreach($folder->getSubfolders() as $subfolder) {
                self::addFolderToArchive($archive, $subfolder, $user_id, $keep_hierarchy, $folder_zip_path);
            }
            return true;
        }

        //Folder is not readable:
        return false;
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
     * @param User $user The user who wishes to pack files.
     * @param string $archive_path The path in which the archive shall be created.
     * @param string $archive_file_name An optional file name for the archive.
     *     If omitted, a random name is generated.
     * @param bool $keep_hierarchy True, if the folder hierarchy shall be kept.
     *     False, if the folder hierarchy shall be flattened.
     * 
     * @return ZipArchive A ZipArchive object for the created archive.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function createArchive(
        $file_area_objects = [],
        $range_id = null,
        $range_type = '',
        $user_id = null,
        $archive_path = '',
        $archive_file_name = '',
        $keep_hierarchy = true
    ) {
        
        $archive_max_num_files = Config::get()->ZIP_DOWNLOAD_MAX_FILES;
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE;
        
        //check if archive path is set:
        if(!$archive_path) {
            throw new FileArchiveManagerException(
                'Destination path for file archive is not set!'
            );
        }
        
        //check if archive path exists:
        if(!file_exists($archive_path)) {
            throw new FileArchiveManagerException(
                'Destination path for file archive does not exist!'
            );
        }
        
        if(!$archive_file_name) {
            $archive_file_name = md5(uniqid('FileArchiveManager::createArchiveFromFileRefs', true));
        }
        $archive_path = $archive_path . '/' . $archive_file_name;
        
        //We can create the Zip archive now since its path exists in the file system.
        
        $archive = new ZipArchive();
        if(!$archive->open($archive_path, ZipArchive::CREATE)) {
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
        
        
        $check_size = true;
        
        //check if the maximum number of files and maximum archive file size
        //is set. If not, we can't check for the file sizes.
        if(!$archive_max_num_files && !$archive_max_size) {
            $check_size = false;
        }
        
        //Check if more file area objects than allowed shall be packed:
        //In that case, stop here.
        if(count($file_area_objects) > $archive_max_num_files) {
            throw new FileArchiveManagerException(
                "Too many file area objects! Limit is $archive_max_num_files!"
            );
        }
        
        
        foreach($file_area_objects as $file_area_object) {
            if($file_area_object instanceof FileRef) {
                self::addFileRefToArchive(
                    $archive,
                    $file_area_object,
                    $user_id
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
                    $keep_hierarchy
                );
            }
        }
    }
    
    
    
    /**
     * Puts files (identified by their file refs) into one file archive.
     * 
     * @param FileRef[] $file_refs Array of FileRef objects.
     * @param User $user The user who wishes to pack files.
     * @param string $archive_path The path in which the archive shall be created.
     * @param string $archive_file_name An optional file name for the archive. If omitted, an 
     * 
     * @return ZipArchive The created ZipArchive object.
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromFileRefs(
        $file_refs = [],
        User $user,
        $archive_path = '',
        $archive_file_name = ''
        ){
        
        $archive_max_num_files = Config::get()->ZIP_DOWNLOAD_MAX_FILES;
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE;
        
        if(!$archive_path) {
            throw new FileArchiveManagerException(
                'Destination path for file archive is not set!'
            );
        }
        
        if(!$archive_file_name) {
            $archive_file_name = md5(uniqid('FileArchiveManager::createArchiveFromFileRefs', true));
        }
        $archive_path = $archive_path . '/' . $archive_file_name;
        
        $archive = new ZipArchive();
        if(!$archive->open($archive_path, ZipArchive::CREATE)) {
            throw new FileArchiveManagerException(
                'Error opening new ZIP archive!'
            );
        }
        
        //$file_ref_ids must be an array!
        if(!is_array($file_refs)) {
            return $archive;
        }
        
        //If there are no FileRef IDs, we can stop here.
        if(empty($file_refs)) {
            return $archive;
        }
        
        
        $check_size = true;
        
        //check if the maximum number of files and maximum archive file size
        //is set. If not, we can't check for the file sizes.
        if(!$archive_max_num_files && !$archive_max_size) {
            $check_size = false;
        }
        
        //check if more files than allowed shall be packed:
        //In that case, stop here.
        if(count($file_refs) > $archive_max_num_files) {
            return $archive;
        }
        
        //For each FileRef we must check if the user is allowed to download it.
        
        $downloadable_file_refs = [];
        $downloadable_files_total_size = 0;
        
        foreach($file_refs as $file_ref) {
            //we handle only FileRef instances here
            if($file_ref instanceof FileRef) {
                $folder = $file_ref->folder;
                if($folder) {
                    $folder = $folder->getTypedFolder();
                    if($folder) {
                        if($folder->isFileDownloadable($file_ref->id, $user->id)) {
                            $downloadable_file_refs[] = $file_ref;
                        }
                    }
                }
            }
        }
        
        //Ok, we have only those FileRefs that can be downloaded by the user.
        //Now we must check, if the size of those files exceed the maximum
        //allowed size for file archives, if we can check for that.
        
        if($check_size) {
            if($downloadable_files_total_size > $archive_max_size) {
                //The total size of all downloadable files exceeds the
                //maximum allowed size for file archives. We must stop here!
                return $archive;
            }
        }
        
        
        //We must now collect all the files from these FileRefs and copy them
        //into the new archive file.
        
        foreach($downloadable_file_refs as $file_ref) {
            $file = $file_ref->file;
            if($file) {
                $file_path = $file->getPath();
                if($file_path) {
                    $archive->addFile($file_path, $file_ref->name);
                }
            }
        }
        
        
        //Ok, archive file is finished. Return the ZipArchive object:
        return $archive;
    }
    
    
    
    /**
     * Creates an archive that contains all files of a course the given user
     * is allowed to download.
     * 
     * @param FolderType $folder The folder whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the course's files into an archive
     * @param string $archive_path The path where the archive shall be placed.
     * @param string $archive_file_name (optional) The file name of the archive.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     */
    public static function createArchiveFromFolder(
        FolderType $folder,
        $user_id = null,
        $archive_path = '',
        $archive_file_name = '',
        $keep_hierarchy = true)
    {
        //to be implemented
    }
    
    
    
    /**
     * Creates an archive that contains all files of a course the given user
     * is allowed to download.
     * 
     * @param string $course_id The ID of the course whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the course's files into an archive
     * @param string $archive_path The path where the archive shall be placed.
     * @param string $archive_file_name (optional) The file name of the archive.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     */
    public static function createArchiveFromCourse(
        $course_id = null,
        $user_id = null,
        $archive_path = '',
        $archive_file_name = '',
        $keep_hierarchy = true)
    {
        //to be implemented
    }
    
    
    /**
     * Creates an archive that contains all files of an institute the given user
     * is allowed to download.
     * 
     * @param string $institute_id The ID of the institute whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the institute's files into an archive
     * @param string $archive_path The path where the archive shall be placed.
     * @param string $archive_file_name (optional) The file name of the archive.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     */
    public static function createArchiveFromInstitute(
        $institute_id = null,
        $user_id = null,
        $archive_path = '',
        $archive_file_name = '',
        $keep_hierarchy = true)
    {
        //to be implemented
    }
    
    
    /**
     * Creates an archive that contains all files of a user, if the current
     * user has root permissions to do this.
     * 
     * @param string $user_id The ID of the user whose files shall be put inside an archive.
     * @param string $archive_path The path where the archive shall be placed.
     * @param string $archive_file_name (optional) The file name of the archive.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     */
    public static function createArchiveFromUser(
        $user_id = null,
        $archive_path = '',
        $archive_file_name = '',
        $keep_hierarchy = true)
    {
        //to be implemented
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
