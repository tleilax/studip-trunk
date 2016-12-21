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
    
}
