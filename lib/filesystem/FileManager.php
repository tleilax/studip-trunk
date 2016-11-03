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
    
    /**
     * Handles uploading one or more files
     * 
     * @param uploaded_files An array with file data for all uploaded files
     * @param folder the folder where the files are inserted
     * @param user_id the ID of the user who wants to upload files
     * 
     * @return mixed[] Array with the created file objects and error strings
     */
    public static function handleFileUpload(Array $uploaded_files, FolderType $folder, $user_id)
    {
        $result = array();
        if (is_array($uploaded_files['name'])) {
            $error = [];
            foreach ($uploaded_files['name'] as $key => $filename) {
                if ($uploaded_files['error'][$key] === UPLOAD_ERR_INI_SIZE) {
                    $error[] = _("Die maximale Dateigröße wurde überschritten.");
                    continue;
                }
                if ($uploaded_files['error'][$key] > 0) {
                    $error[] = _("Ein Systemfehler ist beim Upload aufgetreten. Fehlercode: " . $uploaded_files['error'][$key]);
                    continue;
                }
                $filetype = $uploaded_files['type'][$key] ?: get_mime_type($filename);
                $tmpname = $uploaded_files['tmp_name'][$key];
                $size = $uploaded_files['size'][$key];
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
     * This method handles copying a file to a new folder.
     * 
     * If the user (given by $user) is the owner of the file (by looking at the user_id
     * in the file reference) we can just make a new reference to that file.
     * Else, we must copy the file and its content.
     *
     * The file name is altered when a file with the identical name exists in
     * the destination folder. In that case, only the name in the FileRef object
     * of the file is altered and the File object's name is unchanged.
     * 
     * @param source The file reference for the file that shall be copied.
     * @param destination_folder The destination folder for the file.
     * @param user The user who wishes to copy the file.
     * 
     * @return string[] Array with error messages: Empty array on success, filled array on failure.
     */
    public static function copyFileRef(FileRef $source, Folder $destination_folder, User $user)
    {
        //first we have to make sure if the user has the permissions to read the source folder
        //and the permissions to write to the destination folder:        
        $source_folder = Folder::find($source->folder_id);
        
        if($source_folder->isReadable($user->id) && $destination_folder->isEditable($user->id)) {
            //user is permitted to copy a file, but is he the owner?
            if($source->user_id == $user->id) {
                //the user is the owner of the file: we can simply make a new reference to it
                
                $new_reference = new FileRef();
                $new_reference->file_id = $source->file_id;
                $new_reference->folder_id = $destination_folder->id;
                $new_reference->name = $destination_folder->getUniqueName($source->file->name);
                $new_reference->description = $source->description;
                $new_reference->license = $source->license;
                $new_reference->user_id = $user->id;
                
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
                
                //The File object's name is unchanged here.
                //It must only be unique for the file reference (see below).
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
                        
                        //Create an unique name for the file reference:
                        $new_reference->name = $destination_folder->getUniqueName($file_copy->name);
                        
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
     * This method handles moving a file to a new folder.
     * 
     * @param source The file reference for the file that shall be moved.
     * @param destination_folder The destination folder.
     * @param user The user who wishes to move the file.
     * 
     * @returns string[] Array with error messages: Empty array on success, filled array on failure.
     */
    public static function moveFileRef(FileRef $source, Folder $destination_folder, User $user)
    {
        $source_folder = Folder::find($source->folder_id);
        
        if($source_folder->isReadable($user->id) && $destination_folder->isEditable($user->id)) {
            
            $source->folder_id = $destination_folder->id;
            $source->name = $destination_folder->getUniqueName($source->name);
            
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
     * Handles the sub folder creation routine.
     * 
     * @param sub_folder The subfolder that shall be linked with $destination_folder
     * @param destination_folder The folder where the subfolder shall be linked.
     * @param user The user who wishes to create the subfolder.
     * 
     * @returns string[] Array with error messages: Empty array on success, filled array on failure.
     * 
     */
    public static function createSubFolder(Folder $sub_folder, Folder $destination_folder, User $user)
    {
        $errors = [];
        
        //check if subFolder is new:
        if(!$sub_folder->isNew()) {
            $errors[] = _('Ein bereits erstellter Ordner kann nicht neu erzeugt werden!');
        }
        
        
        //check if user is owner of parent folder:
        $folder_type = $destination_folder->getTypedFolder();
        
        if(!$folder_type->isSubfolderAllowed($user->id)) {
            $errors[] = _('Sie sind nicht dazu berechtigt, einen Unterordner zu erstellen!');
        }
        
        //check if folder name is unique and change it, if it isn't:
        $sub_folder->name = $destination_folder->getUniqueName($sub_folder->name);
        
        //we can return here if we have found errors:
        if($errors) {
            return $errors;
        }
        
        
        //check if all necessary attributes of the sub folder are set 
        //and if they aren't set, set them here:
        
        $sub_folder->user_id = $user->id;
        
        $sub_folder->range_id = $destination_folder->range_id;
        
        $sub_folder->parent_id = $destination_folder->id;
        
        $sub_folder->range_type = $destination_folder->range_type;
        
        $sub_folder->folder_type = $destination_folder->folder_type;
        
        $sub_folder->store();
        
        return []; //no errors
    }
    
    
    /**
     * This method does all the checks that are necessary before editing a folder's data.
     * Note that either name or description has to be set. Otherwise this method
     * will do nothing.
     * 
     * @param folder The folder that shall be edited.
     * @param user The user who wants to edit the folder.
     * @param name The new name for the folder (can be left empty).
     * @param description The new description for the folder (can be left empty).
     * 
     * @returns string[] Array with error messages: Empty array on success, filled array on failure.
     */
    public static function editFolder(Folder $folder, User $user, $name = null, $description = null)
    {
        //Since name must not be empty we have to check if it validates to false
        //(which can happen with emtpy strings). Description on the other hand
        //can be null which means it shoudln't be changed.
        //If description is an empty string it shall be changed to an empty string
        //if it had a filled string as value.
        if(!$name && ($description == null)) {
            //neither name nor description are set: we can't do anything.
            return [_('Keine Änderungen angegeben!')];
        }
        
        //check if folder is not a top folder:
        if(!$folder->parent_id) {
            //folder is a top folder which cannot be edited!
            return [
                sprintf(
                    _('Ordner %s ist ein Hauptordner, der nicht bearbeitet werden kann!'),
                    $folder->name
                )
            ];
        }
        
        
        $folder_type = $folder->getTypedFolder();
        
        if($folder_type->isWritable($user->id)) {
            //ok, user has write permissions for this folder:
            //edit name or description or both
            
            if($name) {
                //get the parent folder to check for duplicate names
                //and set the folder name to an unique name:
                
                $folder->name = $folder->parentfolder->getUniqueName($name);
            }
            
            if($description != null) {
                $folder->description = $description;
            }
            
            if($folder->store()) {
                //folder successfully edited
                return [];
            } else {
                return [
                    sprintf(
                        _('Fehler beim Speichern des Ordners %s'),
                        $folder->name
                    )
                ];
            }
            
        } else {
            return [
                sprintf(
                    _('Unzureichende Berechtigungen zum Bearbeiten des Ordners %s'),
                    $folder->name
                )
            ];
        }
    }
    
    
    /**
     * This method handles copying folders, including
     * copying the subfolders and files recursively.
     * 
     * @param source_folder The folder that shall be copied.
     * @param destination_folder The destination folder.
     * @param user The user who wishes to copy the folder.
     * 
     * @return string[] Array with error messages: Empty array on success, filled array on failure.
     */
    public static function copyFolder(Folder $source_folder, Folder $destination_folder, User $user)
    {
        global $perm;
        
        $errors = [];
        
        $destination_folder_type = $destination_folder->getTypedFolder();
        if($destination_folder_type->isWritable($user->id)) {
            
            $source_folder_type = Folder::findRangeTypeById($source_folder->range_id);
            
            //we have to check, if the source folder is a folder from a course.
            //If so, then only users with status dozent or tutor (or root) in that course
            //may copy the folder!
            if($source_folder_type == 'course' &&
                $perm->have_studip_perm('tutor', $source_folder->range_id, $user->id)
                || ($source_folder_type != 'course')
            ) {
                //the if-query above returns true if the folder type is not course
                //or if the user has the permissions to copy a course folder
                
                $new_folder = new Folder();
                $new_folder->user_id = $source_folder->user_id;
                $new_folder->parent_id = $destination_folder->id;
                $new_folder->range_id = $destination_folder->range_id;
                $new_folder->range_type = $destination_folder->range_type;
                $new_folder->folder_type = $source_folder->folder_type;
                $new_folder->name = $destination_folder->getUniqueName($source_folder->name);
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
                //no permission to copy course folders!
                $errors[] = sprintf(
                    _('Unzureichende Berechtigungen zum Kopieren von Veranstaltungsordner %s in Ordner %s!'),
                    $source_folder->name,
                    $destination_folder->name
                );
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
     * This method handles moving folders, including
     * subfolders and files.
     * 
     * @param source_folder The folder that shall be moved.
     * @param destination_folder The destination folder.
     * @param user The user who wishes to move the folder.
     * 
     * @returns Array with error messages: Empty array on success, filled array on failure.
     */
    public static function moveFolder(Folder $source_folder, Folder $destination_folder, User $user)
    {
        $destination_folder_type = $destination_folder->getTypedFolder();
        if($destination_folder_type->isWritable($user->id)) {
            $source_folder->parent_id = $destination_folder->id;
            $source_folder->name = $destination_folder->getUniqueName($source_folder->name);
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
    
    
    /**
     * returns the available folder types, sorted (and at your option selected) by range type
     * 
     * There are several types of folders in Stud.IP. This method returns
     * all available folder types. If the parameter range_type is set then only
     * the folder types allowed in that range are returned.
     * 
     * @param range_type the range type: "course", "institute", "user", ...
     * 
     * @return Array with strings representing the class names of available folder types.
     * 
     */
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

    
    /**
     * Returns a FolderType object for the inbox folder of the given user.
     * 
     * @param User user The user whose inbox folder is requested.
     */
    public static function getInboxFolder(User $user)
    {
        
    }
    
    /**
     * Returns a FolderType object for the outbox folder of the given user.
     * 
     * @param User user The user whose outbox folder is requested.
     */
    public static function getOutboxFolder(User $user)
    {
        
    }
    
    /**
     * Returns a FolderType object for the homework folder of the given user.
     * 
     * @param User user The user whose homework folder is requested.
     */
    public static function getHomeworkFolder(User $user)
    {
        
    }
    
    /**
     * Returns a FolderType object for the public folder of the given user.
     * 
     * @param User user The user whose public folder is requested.
     */
    public static function getPublicFolder(User $user)
    {
        
    }
    
}
