<?php
/**
 * FileManager.php
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
class FileManager
{

    public static function handleFileUpload(Array $uploadedfiles, FolderType $folder, $user_id)
    {
        $result = array();
        if (is_array($uploadedfiles['name'])) {
            $error = [];
            foreach ($uploadedfiles['name'] as $key => $filename) {
                if ($uploadedfiles['error'][$key] === UPLOAD_ERR_INI_SIZE) {
                    $error[] = _("Die maximale Dateigröße wurde überschritten.");
                    continue;
                }
                if ($uploadedfiles['error'][$key] > 0) {
                    $error[] = _("Ein Systemfehler ist beim Upload aufgetreten. Fehlercode: " . $uploadedfiles['error'][$key]);
                    continue;
                }
                $filetype = $uploadedfiles['type'][$key] ?: get_mime_type($filename);
                $tmpname = $uploadedfiles['tmp_name'][$key];
                $size = $uploadedfiles['size'][$key];
                if ($folder_error = $folder->validateUpload(['name' => $filename, 'size' => $size], $user_id)) {
                    $error[] = $folder_error;
                    continue;
                }
                $file = new File();
                $file->user_id = $user_id;
                $file->name = $filename;
                $file->mime_type = $filetype;
                $file->size = $size;
                $file->storage = 'disk';
                $file->id = $file->getNewId();
                if ($file->connectWithDataFile($tmpname)) {
                    $result['files'][] = $file;
                } else {
                    $error[] = _("Ein Systemfehler ist beim Upload aufgetreten.");
                }
            }
        }
        return array_merge($result, ['error' => $error]);
    }
    
    
    /**
        Handles the sub folder creation routine.
        
        @param folder The folder where the subfolder shall be created.
        @param user The user who wants to create the subfolder.
        @param subFolder The subfolder that shall be linked.
        
        @returns array with error messages
        
    **/
    public static function createSubFolder(Folder $folder, User $user, Folder $subFolder)
    {
        $errorMessages = [];
        
        //check if subFolder is new:
        if(!$subFolder->isNew()) {
            $errorMessages[] = _('Ein bereits erstellter Ordner kann nicht neu erzeugt werden!');
        }
        
        
        //check if user is owner of parent folder:
        $folderType = $folder->getTypedFolder();
        
        if(!$folderType->isSubfolderAllowed($user->id)) {
            $errorMessages[] = _('Sie sind nicht dazu berechtigt, einen Unterordner zu erstellen!');
        }
        
        //check if folder name is unique and change it, if it isn't:
        $subFolder->name = $folder->getUniqueName($subFolder->name);
        
        //we can return here if we have found errors:
        if($errorMessages) {
            return $errorMessages;
        }
        
        
        //check if all necessary attributes of the sub folder are set 
        //and if they aren't set, set them here:
        
        $subFolder->user_id = $user->id;
        
        $subFolder->range_id = $folder->range_id;
        
        $subFolder->parent_id = $folder->id;
        
        $subFolder->range_type = $folder->range_type;
        
        $subFolder->folder_type = $folder->folder_type;
        
        $subFolder->store();
                
    }
    
    
    /**
        This method handles copying a file to a new folder.
        
        @param source The file reference for the file that shall be copied.
        @param destination_folder The destination folder.
        
        @returns Array with error messages: Empty array on success, filled array on failure.
    **/
    public static function copyFileRef(FileRef $source, Folder $destination_folder)
    {
        return ['Not yet implemented!'];
    }
    
    
    /**
        This method handles copying folders, including
        copying the subfolders and files recursively.
        
        @param source_folder The folder that shall be copied.
        @param destination_folder The destination folder.
        
        @returns Array with error messages: Empty array on success, filled array on failure.
    **/
    public static function copyFolder(Folder $source_folder, Folder $destination_folder)
    {
        $errors = [];
        
        
        $new_folder = Folder();
        $new_folder->user_id = $source_folder->user_id;
        $new_folder->parent_id = $destination_folder->id;
        $new_folder->range_id = $destination_folder->range_id;
        $new_folder->range_type = $destination_folder->range_type;
        $new_folder->folder_type = $source_folder->folder_type;
        $new_folder->name = $source_folder->name;
        $new_folder->data_content = $source_folder->data_content;
        $new_folder->description = $source_folder->description;
        //folder is copied, we can store it:
        $new_folder->store();
        
        
        //now we go through all subfolders and copy them:
        foreach($source_folder->subfolders as $sub_folder) {
            $errors[] = self::copyFolder($sub_folder, $new_folder);
            if($errors) {
                return $errors;
            }
        }
        
        //now go through all files and copy them, too:
        foreach($source_folder->file_refs as $file_ref) {
            $errors[] = self::copyFileRef($file_ref, $new_folder);
            
            if($errors) {
                return $errors;
            }
        }
        
        return $errors;
    }
    
    
    public static function getFolderTypes($range_type = null)
    {
        $result = array();
        foreach (scandir(dirname(__FILE__)) as $filename) {
            $path = pathinfo($filename);
            if ($path['extension'] == 'php') {
                class_exists($path['filename']);
            }
        }
        foreach (get_declared_classes() as $declared_class) {
            if (is_a($declared_class, 'FolderType', true)) {
                $folder = new $declared_class([]);
                foreach ($folder->getAllowedRangeTypes() as $type) {
                    $result[$type][] = $declared_class;
                }
            }
        }
        if ($range_type) {
            return @$result[$range_type] ?: [];
        } else {
            return $result;
        }
    }
}