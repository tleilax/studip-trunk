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
    
    //FILE METHODS
    
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
        This method handles copying a file to a new folder.
        
        If the user (given by $user) is the owner of the file (by looking at the user_id
        in the file reference) we can just make a new reference to that file.
        Else, we must copy the file and its content.
        
        @param source The file reference for the file that shall be copied.
        @param destination_folder The destination folder for the file.
        @param user The user who wishes to copy the file.
        
        @returns Array with error messages: Empty array on success, filled array on failure.
    **/
    public static function copyFileRef(FileRef $source, Folder $destination_folder, User $user)
    {
        //first we have to make sure if the user has the permissions to read the source folder
        //and the permissions to write to the destination folder:
        if($source->isReadable($user->id) && $destination_folder->isEditable($user->id)) {
            //user is permitted to copy a file, but is he the owner?
            if($source->user_id == $user->id) {
                //the user is the owner of the file: we can simply make a new reference to it
                
                $new_reference = new FileRef();
                $new_reference->file_id = $source->file_id;
                $new_reference->folder_id = $destination_folder->id;
                $new_reference->description = $source->description;
                $new_reference->license = $source->license;
                
                if($new_reference->store()) {
                    return [];
                } else {
                    return[
                        _('Fehler beim Kopieren: Neue Referenz kann nicht erzeugt werden!')
                    ];
                }
            } else {
                //the user is not the owner of the file: we must copy the file object, too!
                
                $file_copy = new File();
                $file_copy->user_id = $user->id;
                $file_copy->mime_type = $source->file->mime_type;
                $file_copy->name = $source->file->name;
                $file_copy->size = $source->file->size;
                $file_copy->storage = $source->file->storage;
                $file_copy->author_name = $source->file->author_name;
                
                if($file_copy->store()) {
                    //ok, file is stored, now we need to copy the real data:
                    
                    if(copy($source->getPath(), $file_copy->getPath())) {
                        
                        //ok, create the file ref for the copied file:
                        $new_reference = new FileRef();
                        $new_reference->file_id = $file_copy->file_id;
                        $new_reference->folder_id = $destination_folder->id;
                        $new_reference->description = $source->description;
                        $new_reference->license = $source->license;
                        
                        if($new_reference->store()) {
                            return [];
                        } else {
                            //file reference can't be created!
                            return[
                                _('Fehler beim Kopieren: Neue Referenz kann nicht erzeugt werden!')
                            ];
                            //delete $file_copy
                            //(to avoid orphaned entries in the database)
                            $file_copy->delete();
                        }
                    } else {
                        //error while copying: delete $file_copy
                        //(to avoid orphaned entries in the database)
                        $file_copy->delete();
                    }                
                }
            }
        } else {
            //the user is not permitted to read the source folder
            //or to write to the destination folder!
            return [
                sprintf(
                    _('Ungenügende Berechtigungen zum Kopieren der Datei %s in Ordner %s!'),
                    $source->file->name,
                    $destination_folder->name
                )
            ];
        }
    }
    
    /**
        This method handles moving a file to a new folder.
        
        @param source The file reference for the file that shall be moved.
        @param destination_folder The destination folder.
        @param user The user who wishes to move the file.
        
        @returns Array with error messages: Empty array on success, filled array on failure.
    **/
    public static function moveFileRef(FileRef $source, Folder $destination_folder, User $user)
    {
        if($source->isReadable($user->id) && $destination_folder->isEditable($user->id)) {
            
            $source->folder_id = $destination_folder->id;
            if($source->store()) {
                return [];
            } else {
                return [_('Datei konnte nicht gespeichert werden.')];
            }
        } else {
            return [
                sprintf(
                    _('Ungenügende Berechtigungen zum Verschieben der Datei %s in Ordner %s!'),
                    $source->file->name,
                    $destination_folder->name
                )
            ];
        }
    }
    
    
    
    
    // FOLDER METHODS
    
    
    /**
        Handles the sub folder creation routine.
        
        @param folder The folder where the subfolder shall be created.
        @param subFolder The subfolder that shall be linked.
        @param user The user who wishes to create the subfolder.
        
        @returns array with error messages
        
    **/
    public static function createSubFolder(Folder $folder, Folder $subFolder, User $user)
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
        This method handles copying folders, including
        copying the subfolders and files recursively.
        
        @param source_folder The folder that shall be copied.
        @param destination_folder The destination folder.
        @param user The user who wishes to copy the folder.
        
        @returns Array with error messages: Empty array on success, filled array on failure.
    **/
    public static function copyFolder(Folder $source_folder, Folder $destination_folder, User $user)
    {
        $errors = [];
        
        $destination_folder_type = $destination_folder->getTypedFolder();
        if($destination_folder_type->isWritable($user->id)) {
            
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
                $errors[] = self::copyFolder($sub_folder, $new_folder, $user);
                if($errors) {
                    return $errors;
                }
            }
            
            //now go through all files and copy them, too:
            foreach($source_folder->file_refs as $file_ref) {
                $errors[] = self::copyFileRef($file_ref, $new_folder, $user);
                
                if($errors) {
                    return $errors;
                }
            }
        } else {
            $errors[] = sprintf(
                _('Unzureichende Berechtigungen zum Kopieren von Ordner %s in Ordner %s!'),
                $source_folder->name,
                $destination_folder->name
            );
        }
        return $errors;
    }
    
    
    /**
        This method handles moving folders, including
        subfolders and files.
        
        @param source_folder The folder that shall be moved.
        @param destination_folder The destination folder.
        @param user The user who wishes to move the folder.
        
        @returns Array with error messages: Empty array on success, filled array on failure.
    **/
    public static function moveFolder(Folder $source_folder, Folder $destination_folder, User $user)
    {
        $destination_folder_type = $destination_folder->getTypedFolder();
        if($destination_folder_type->isWritable($user->id)) {
            $source_folder->parent_id = $destination_folder->id;
            $source_folder->store();
        } else {
            $errors[] = sprintf(
                _('Unzureichende Berechtigungen zum Verschieben von Ordner %s in Ordner %s!'),
                $source_folder->name,
                $destination_folder->name
            );
        }
        return [];
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