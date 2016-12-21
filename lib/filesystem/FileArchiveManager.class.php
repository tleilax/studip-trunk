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
    /**
     * Puts files (identified by their file refs) into one file archive.
     * 
     * @param Array $file_ref_ids List of FileRef object IDs.
     * @param User $user The user who wishes to pack files.
     * @param string $archive_path The path in which the archive shall be created.
     * @param string $archive_file_name An optional file name for the archive. If omitted, an 
     * @param string $format The archive format: 'zip' or 'targz'
     * 
     * @return PharData The created PharData object
     * 
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromFileRefs(
        $file_ref_ids = [],
        User $user,
        $archive_path = ''){
        
        $archive_max_num_files = Config::get()->ZIP_DOWNLOAD_MAX_FILES;
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE;
        
        //$file_ref_ids must be an array!
        if(!is_array($file_ref_ids)) {
            return null; //TODO: return PharData
        }
        
        //If there are no FileRef IDs, we can stop here.
        if(empty($file_ref_ids)) {
            return null; //TODO: return PharData
        }
        
        if(!$archive_path) {
            return null; //TODO: return PharData
        }
        
        $check_size = true;
        
        //check if the maximum number of files and maximum archive file size
        //is set. If not, we can't check for the file sizes.
        if(!$archive_max_num_files && !$archive_max_size) {
            $check_size = false;
        }
        
        //check if more files than allowed shall be packed:
        //In that case, stop here.
        if(count($file_ref_ids) > $archive_max_num_files) {
            return null; //TODO: return PharData
        }
        
        $file_refs = FileRef::findMany($file_ref_ids);
        
        //For each FileRef we must check if the user is allowed to download it.
        
        $downloadable_file_refs = [];
        $downloadable_files_total_size = 0;
        
        foreach($file_refs as $file_ref) {
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
        
        //Ok, we have only those FileRefs that can be downloaded by the user.
        //Now we must check, if the size of those files exceed the maximum
        //allowed size for file archives, if we can check for that.
        
        if($check_size) {
            if($downloadable_files_total_size > $archive_max_size) {
                //The total size of all downloadable files exceeds the
                //maximum allowed size for file archives. We must stop here!
                return null; //TODO: return PharData
            }
        }
        
        
        //We must now collect all the files from these FileRefs and copy them
        //into the new archive file.
        
        if(!$archive_file_name) {
            $archive_file_name = md5(uniqid('FileArchiveManager::createArchiveFromFileRefs', true));
        }
        $archive_path = $archive_path . '/' . $archive_file_name;
        
        $archive = null;
        
        $format = trim(strtolower($format)); //convert all to lowercase letters and trim whitespaces
        
        if($format == 'zip') {
            $archive = new PharData(
                $archive_path,
                Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME,
                '',
                Phar::ZIP
            );
        } elseif($format == 'targz') {
            $archive = new PharData(
                $archive_path,
                Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME,
                '',
                Phar::TAR
            );
        }
        
        if($archive === null) {
            throw FileArchiveManagerException(
                "Archive format $format is not recognized!"
            );
        }
        
        foreach($downloadable_file_refs as $file_ref) {
            $file = $file_ref->file;
            if($file) {
                $file_path = $file->getPath();
                if($file_path) {
                    $archive->addFile($file_path, $file_ref->name);
                }
            }
        }
        
        if($format == 'targz') {
            $archive = $archive->compress(Phar::GZ, '');
        }
        
        //Ok, archive file is finished. Return the PharData object:
        return $archive;
    }
}
