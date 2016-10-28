<?php
/**
 * FileManager.php
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
class FileManager
{

    public static function handleFileUpload(Array $uploadedfiles, FolderType $folder, $user_id)
    {
        $result = array();
        if (is_array($uploadedfiles['name'])) {
            $error = [];
            foreach ($uploadedfiles['name'] as $key => $filename) {
                if ($uploadedfiles['error'][$key] === UPLOAD_ERR_INI_SIZE) {
                    $error[] = _("Die maximale Dateigr��e wurde �berschritten.");
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