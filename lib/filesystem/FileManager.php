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
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * The FileManager class contains methods that faciliate the management of files
 * and folders. Furthermore its methods perform necessary additional checks
 * so that files and folders are managed in a correct manner.
 *
 * It is recommended to use the methods of this class for file and folder
 * management instead of writing own methods.
 */
class FileManager
{
    
    //FILE HELPER METHODS
    
    /**
     * Removes special characters from the file name (and by that cleaning the file name)
     * so that the file name which is returned by this method works on every operating system.
     * 
     * @param string $file_name The file name that shall be "cleaned".
     * @param bool $shorten_name True, if the file name shall be shortened to 31 characters.
     *     False, if the full length shall be kept (default).
     * 
     * @return string The "cleaned" file name.
     */
    public static function cleanFileName($file_name = null, $shorten_name = false)
    {
        if(!$file_name) {
            //If you put an empty string in, you will get an empty string out!
            return $file_name;
        }
        
        $bad_characters = [':', chr(92), '/', '"', '>', '<', '*', '|', '?', ' ', '(', ')', '&', '[', ']', '#', chr(36), '\'', '*', ';', '^', '`', '{', '}', '|', '~', chr(255)];
        
        $replacement_characters = ['', '', '', '', '', '', '', '', '', '_', '', '', '+', '', '', '', '', '', '', '-', '', '', '', '', '-', '', ''];
        
        //All control characters shall be deleted:
        for($i = 0; $i < 0x20; $i++) {
            $bad_characters[] = chr($i);
            $replacement_characters[] = '';
        }
        
        $clean_file_name = str_replace($bad_characters, $replacement_characters, $file_name);
        
        if($clean_file_name[0] == '.') {
            $clean_file_name = mb_substr($clean_file_name, 1, mb_strlen($clean_file_name));
        }
        
        if($shorten_name === true) {
            //If we have to shorten the file name we have to split it up
            //into file name and extension.
            
            $tmp_file_name = pathinfo($clean_file_name, PATHINFO_FILENAME);
            $file_extension = pathinfo($clean_file_name, PATHINFO_EXTENSION);
            
            $clean_file_name = mb_substr($tmp_file_name, 0, 28)
                . '.'
                . $file_extension;
            
        }
        
        return $clean_file_name;
    }
    
    
    /**
     * Returns the icon name for a given mime type.
     *
     * @param string $mime_type The mime type whose icon is requested.
     * 
     * @return string The icon name for the mime type.
     */
    public static function getIconNameForMimeType($mime_type = null)
    {
        $application_category_icons = [
            'file-pdf' => ['pdf'],
            'file-ppt' => ['powerpoint','presentation'],
            'file-excel' => ['excel', 'spreadsheet', 'csv'],
            'file-word' => ['word', 'wordprocessingml', 'opendocument.text', 'rtf'],
            'file-archive' => ['zip', 'rar', 'arj', '7z' ]
        ];
        
        if(!$mime_type) {
            //No mime type given: We can only assume it is a generic file.
            return 'file-generic';
        }
        
        list($category, $type) = explode('/', $mime_type);
        
        
        switch($category) {
            case 'image': {
                return 'file-pic';
            } case 'audio': {
                return 'file-audio';
            } case 'video': {
                return 'file-video';
            } case 'text': {
                if($type == 'csv') {
                    //CSV files:
                    return 'file-excel';
                }
                //other text files:
                return 'file-text';
            } case 'application': {
                //loop through all application category icons
                //and return the icon name that matches the regular expression
                //for an application mime type:
                foreach($application_category_icons as $icon_name => $type_name) {
                    if(preg_match('/' . implode($type_name, '|') . '/i', $type)) {
                        return $icon_name;
                    }
                }
            }
        }
        
        //If code execution reaches this point, no special mime type icon
        //was detected.
        return 'file-generic';
    }
    
    
    
    //FILE METHODS

    /**
     * Handles uploading one or more files
     *
     * @param uploaded_files A two-dimensional array with file data for all uploaded files.
     *     The array has the following structure in the second dimension:
            [
                'name': The name of the file
                'error': An integer telling if there were errors. 0, if no errors occured.
                'type': The uploaded file's mime type.
                'tmp_name': Name of the temporary file that was created right after the upload.
                'size': Size of the uploaded file in bytes.
            ]
     * @param folder the folder where the files are inserted
     * @param user_id the ID of the user who wants to upload files
     *
     * @return mixed[] Array with the created file objects and error strings
     */
    public static function handleFileUpload(Array $uploaded_files, FolderType $folder, $user_id)
    {
        $result = array();
        $error = [];
        
        //check if user has write permissions for the folder:
        if(!$folder->isWritable($user_id)) {
            $error[] = _('Keine Schreibrechte für Zielordner!');
            return array_merge($result, ['error' => $error]);
        }
        
        //Check if uploaded files[name] is an array.
        //This check is necessary to find out, if $uploaded_files is a
        //two-dimensional array. Each index of the first dimension 
        //contains an array attribute for uploaded files, one entry per file.
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
                
                //validate the upload by looking at the folder where the
                //uploaded file shall be stored:
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
                    $file->store();
                    $result['files'][] = $file;
                } else {
                    $error[] = _("Ein Systemfehler ist beim Upload aufgetreten.");
                }
            }
        }
        return array_merge($result, ['error' => $error]);
    }


    /**
     * This method handles editing file reference attributes.
     *
     * Checks that have to be made during the editing of a file reference are placed
     * in this method so that a controller can simply call this method
     * to change attributes of a file reference.
     *
     * At least one of the three parameters name, description and license
     * must be set. Otherwise this method will do nothing.
     *
     * @param FileRef file_ref The file reference that shall be edited.
     * @param User user The user who wishes to edit the file reference.
     * @param string|null name The new name for the file reference
     * @param string|null description The new description for the file reference.
     * @param string|null content_terms_of_use_id The ID of the new ContentTermsOfUse object.
     * @param string|null license The new license description for the file reference.
     *
     * @return FileRef|string[] The edited FileRef object on success, string array with error messages on failure.
     */
    public static function editFileRef(FileRef $file_ref, User $user, $name = null, $description = null, $content_terms_of_use_id = null)
    {
        if(!$name && ($description == null) && ($content_terms_of_use_id == null)) {
            //nothing to do, no errors:
            return $file_ref;
        }

        if(!$file_ref->folder) {
            return [_('Dateireferenz ist keinem Ordner zugeordnet!')];
        }

        $folder_type = $file_ref->folder->getTypedFolder();

        if(!$folder_type) {
            return [_('Ordnertyp konnte nicht ermittelt werden!')];
        }

        if($folder_type->isFileEditable($file_ref, $user->id)) {

            //check if name is set and is different from the current name
            //of the file reference:
            if(($name !== null) && ($name != $file_ref->name)) {
                //name is special: we have to check if files/folders in
                //the file_ref's folder have the same name. If so, we must
                //make it unique.
                $folder = $file_ref->folder;

                if(!$folder) {
                    return [
                        sprintf(
                            _('Verzeichnis von Datei %s nicht gefunden!'),
                            $file_ref->name
                        )
                    ];
                }

                $file_ref->name = $name;
            }

            if($description !== null) {
                //description may be an empty string which is allowed here
                $file_ref->description = $description;
            }

            if($content_terms_of_use_id !== null) {
                $content_terms_of_use = ContentTermsOfUse::find($content_terms_of_use_id);
                if(!$content_terms_of_use) {
                    return [
                        sprintf(
                            _('Inhalts-Nutzungsbedingungen mit ID %s nicht gefunden!'),
                            $content_terms_of_use_id
                        )
                    ];
                }

                $file_ref->content_terms_of_use_id = $content_terms_of_use->id;
            }


            if($file_ref->store()) {
                //everything went fine
                return $file_ref;
            } else {
                //error while saving the changes!
                return [
                    sprintf(
                        _('Fehler beim Speichern der Änderungen bei Datei %s'),
                        $file_ref->name
                    )
                ];
            }
        } else {
            return [
                sprintf(
                        _('Ungenügende Berechtigungen zum Bearbeiten der Datei %s!'),
                        $file_ref->name
                    )
            ];
        }
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
     * @param FileRef $source The file reference for the file that shall be copied.
     * @param FolderType $destination_folder The destination folder for the file.
     * @param User $user The user who wishes to copy the file.
     *
     * @return FileRef|string[] The copied FileRef object on success or an array with error messages on failure.
     */
    public static function copyFileRef(FileRef $source, FolderType $destination_folder, User $user)
    {
        //first we have to make sure if the user has the permissions to read the source folder
        //and the permissions to write to the destination folder:
        $source_folder = $source->folder;

        if(!$source_folder) {
            return [_('Dateireferenz ist keinem Ordner zugeordnet!')];
        }

        $source_folder = $source->folder->getTypedFolder();

        if(!$source_folder) {
            return [_('Ordnertyp des Quellordners konnte nicht ermittelt werden!')];
        }


        if($source_folder->isReadable($user->id) && $destination_folder->isWritable($user->id)) {
            //user is permitted to copy a file, but is he the owner?
            if($source->user_id == $user->id) {
                //the user is the owner of the file: we can simply make a new reference to it

                $new_reference = new FileRef();
                $new_reference->file_id = $source->file_id;
                $new_reference->folder_id = $destination_folder->getId();
                $new_reference->name = $source->file->name;
                $new_reference->description = $source->description;
                $new_reference->content_terms_of_use_id = $source->content_terms_of_use_id;
                $new_reference->user_id = $user->id;

                if($new_reference->store()) {
                    return $new_reference;
                } else {
                    return[
                        _('Neue Referenz kann nicht erzeugt werden!')
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
                    
                    //first we must create a directory:
                    $destination_directory = pathinfo($file_copy->getPath(), PATHINFO_DIRNAME);
                    
                    if(!$destination_directory) {
                        return [_('Zielverzeichnis konnte nicht ermittelt werden!')];
                    }
                    
                    if(!is_dir($destination_directory)) {
                        //if the destination directory doesn't exist,
                        //we must create it first:
                        if(!mkdir($destination_directory)) {
                            return [_('Zielverzeichnis konnte nicht erstellt werden!')];
                        }
                    }
                    
                    
                    if(copy($source->file->getPath(), $file_copy->getPath())) {

                        //ok, create the file ref for the copied file:
                        $new_reference = new FileRef();
                        $new_reference->file_id = $file_copy->id;
                        $new_reference->folder_id = $destination_folder->id;
                        $new_reference->name = $file_copy->name;
                        $new_reference->description = $source->description;
                        $new_reference->content_terms_of_use_id = $source->content_terms_of_use_id;
                        $new_reference->user_id = $user->id;

                        if($new_reference->store()) {
                            return $new_reference;
                        } else {
                            //file reference can't be created!
                            return[
                                _('Neue Referenz kann nicht erzeugt werden!')
                            ];
                            //delete $file_copy
                            //(to avoid orphaned entries in the database)
                            $file_copy->delete();
                        }
                    } else {
                        //error while copying: delete $file_copy
                        //(to avoid orphaned entries in the database)
                        $file_copy->delete();
                        return[
                            _('Daten konnten nicht kopiert werden!')
                        ];
                    }
                }
            }
        } else {
            //the user is not permitted to read the source folder
            //or to write to the destination folder!
            return [
                sprintf(
                    _('Ungenügende Berechtigungen zum Kopieren der Datei %s in Ordner %s!'),
                    $source->name,
                    $destination_folder->name
                )
            ];
        }
    }


    /**
     * This method handles moving a file to a new folder.
     *
     * @param FileRef $source The file reference for the file that shall be moved.
     * @param FolderType $destination_folder The destination folder.
     * @param User $user The user who wishes to move the file.
     *
     * @returns FileRef|string[] $source FileRef object on success, Array with error messages on failure.
     */
    public static function moveFileRef(FileRef $source, FolderType $destination_folder, User $user)
    {
        $source_folder = Folder::find($source->folder_id);

        if(!$source_folder) {
            return [_('Dateireferenz ist keinem Ordner zugeordnet!')];
        }

        $source_folder = $source->folder->getTypedFolder();

        if(!$source_folder) {
            return [_('Ordnertyp des Quellordners konnte nicht ermittelt werden!')];
        }


        //the user must have the permissions to write into the source file,
        //to read the source folder and to write into the destination folder.
        if($source_folder->isFileWritable($user->id) &&
            $source_folder->isReadable($user->id) &&
            $destination_folder->isWritable($user->id)
            ) {

            $source->folder_id = $destination_folder->id;

            if($source->store()) {
                return $source;
            } else {
                return [_('Datei konnte nicht gespeichert werden.')];
            }
        } else {
            return [
                sprintf(
                    _('Ungenügende Berechtigungen zum Verschieben der Datei %s in Ordner %s!'),
                    $source->name,
                    $destination_folder->name
                )
            ];
        }
    }


    /**
     * This method handles deletign a file reference.
     *
     * @param FileRef file_ref The file reference that shall be deleted
     * @param User user The user who wishes to delete the file reference.
     *
     * @return FileRef|string[] The FileRef object that was deleted from the database on success
     * or an array with error messages on failure.
     */
    public static function deleteFileRef(FileRef $file_ref, User $user)
    {
        $folder = $file_ref->folder;

        if(!$folder) {
            return [_('Dateireferenz ist keinem Ordner zugeordnet!')];
        }

        $folder_type = $folder->getTypedFolder();

        if(!$folder_type) {
            return [_('Ordnertyp des Quellordners konnte nicht ermittelt werden!')];
        }


        if($folder_type->isFileWritable($file_ref->id, $user->id)) {

            if($file_ref->delete()) {
                return $file_ref;
            } else {
                return [_('Dateireferenz konnte nicht gelöscht werden.')];
            }
        } else {
            return [
                sprintf(
                    _('Ungenügende Berechtigungen zum Löschen der Datei %s in Ordner %s!'),
                    $file_ref->name
                )
            ];
        }
    }


    
    
    
    

    // FOLDER METHODS


    /**
     * Handles the sub folder creation routine.
     *
     * @param FolderType $destination_folder The folder where the subfolder shall be linked.
     * @param User $user The user who wishes to create the subfolder.
     * @param string $folder_type_class_name The FolderType class name for the new folder
     * @param string $name The name for the new folder
     * @param string $description The description of the new folder
     *
     * @returns FolderType|string[] Either the FolderType object of the new folder or an Array with error messages.
     *
     */
    public static function createSubFolder(
        FolderType $destination_folder,
        User $user,
        $folder_type_class_name = null,
        $name = null,
        $description = null)
    {
        $errors = [];


        if(!$folder_type_class_name) {
            //folder_type_class_name is not set: we can't create a folder!
            return [_('Es wurde kein Ordnertyp angegeben!')];
        }

        //check if folder_type_class_name has a valid class:
        if(!is_subclass_of($folder_type_class_name, 'FolderType')) {
            return [
                sprintf(
                    _('Die Klasse %s ist nicht von FolderType abgeleitet!'),
                    $folder_type_class_name
                )
            ];
        }

        if(!$name) {
            //name is not set: we can't create a folder!
            return [_('Es wurde kein Ordnername angegeben!')];
        }

        $sub_folder = new Folder();
        $sub_folder_type = new $folder_type_class_name($sub_folder);

        //set name and description of the new folder:
        $sub_folder->name = $name;

        if($description) {
            $sub_folder->description = $description;
        }


        //check if the sub folder type is creatable in a StandardFolder,
        //if the destination folder is a StandardFolder:
        if((get_class($destination_folder) == 'StandardFolder') &&
            !$sub_folder_type->creatableInStandardFolder($destination_folder->range_type)) {

            $errors[] = sprintf(
                _('Ein Ordner vom Typ %s kann nicht in einem Ordner vom Typ %s erzeugt werden!'),
                get_class($sub_folder_type),
                'StandardFolder'
            );
        }




        //check if destination_folder is a standard folder

        if((get_class($destination_folder) != 'StandardFolder') &&
            (get_class($sub_folder_type) != get_class($destination_folder))) {
            //we can't create a special folder in another special folder!
            $errors[] = sprintf(
                _('Ein Ordner vom Typ %s kann nicht in einem Ordner vom Typ %s erzeugt werden!'),
                get_class($sub_folder_type),
                get_class($destination_folder)
            );
        }


        if(!$destination_folder->isSubfolderAllowed($user->id)) {
            $errors[] = _('Sie sind nicht dazu berechtigt, einen Unterordner zu erstellen!');
        }

        //we can return here if we have found errors:
        if(!empty($errors)) {
            return $errors;
        }

        //check if all necessary attributes of the sub folder are set
        //and if they aren't set, set them here:
        
        //special case for inbox and outbox folders: these folder types
        //get a custom ID instead of a generic one, so it has to be set here!
        
        if($folder_type_class_name == 'InboxFolder') {
            $sub_folder->id = md5('INBOX_' . $user->id);
        } elseif($folder_type_class_name == 'OutboxFolder') {
            $sub_folder->id = md5('OUTBOX_' . $user->id);
        }
        
        $sub_folder->user_id = $user->id;

        $sub_folder->range_id = $destination_folder->range_id;

        $sub_folder->parent_id = $destination_folder->getId();

        $sub_folder->range_type = $destination_folder->range_type;

        $sub_folder->folder_type = get_class($sub_folder_type);

        $sub_folder->store();
        
        return $sub_folder_type; //no errors
    }


    /**
     * This method does all the checks that are necessary before editing a folder's data.
     * Note that either name or description has to be set. Otherwise this method
     * will do nothing.
     *
     * @param FolderType $folder The folder that shall be edited.
     * @param User $user The user who wants to edit the folder.
     * @param string|null $name The new name for the folder (can be left empty).
     * @param string|null $description The new description for the folder (can be left empty).
     *
     * @returns FolderType|string[] Returns the edited FolderType object success
     * or an array with error messages on failure.
     */
    public static function editFolder(FolderType $folder, User $user, $name = null, $description = null)
    {
        //Since name must not be empty we have to check if it validates to false
        //(which can happen with emtpy strings). Description on the other hand
        //can be null which means it shoudln't be changed.
        //If description is an empty string it shall be changed to an empty string
        //if it had a filled string as value.
        if(!$name && ($description == null)) {
            //neither name nor description are set: nothing to do, no error:
            return $folder;
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


        if($folder->isWritable($user->id)) {
            //ok, user has write permissions for this folder:
            //edit name or description or both

            $data = $folder->getEditTemplate();

            if($name) {
                //get the parent folder to check for duplicate names
                //and set the folder name to an unique name:
                $data['name'] = $name;
            }

            if($description != null) {
                $data['description'] = $description;
            }

            $folder->setDataFromEditTemplate($data);
            if($folder->store()) {
                //folder successfully edited
                return $folder;
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
     * @param FolderType $source_folder The folder that shall be copied.
     * @param FolderType $destination_folder The destination folder.
     * @param User $user The user who wishes to copy the folder.
     *
     * @return FolderType|string[] The copy of the source_folder FolderType object on success
     * or an array with error messages on failure.
     */
    public static function copyFolder(FolderType $source_folder, FolderType $destination_folder, User $user)
    {
        global $perm;

        $errors = [];
        $new_folder = null;

        if($destination_folder->isWritable($user->id)) {

            //we have to check, if the source folder is a folder from a course.
            //If so, then only users with status dozent or tutor (or root) in that course
            //may copy the folder!
            if($source_folder->isReadable($user->id)) {
                //the user has the permissions to copy the folder

                $unique_name = Folder::find($destination_folder->getId())->getUniqueName($source_folder->name);
                $folder_class_name = get_class($source_folder);

                $new_folder = new $folder_class_name(new Folder());                
                $data = $source_folder->getEditTemplate();
                $data['id'] = null;
                $data['user_id'] = $user->id;
                $data['parent_id'] = $destination_folder->id;
                $data['range_id'] = $destination_folder->range_id;
                $data['range_type'] = $destination_folder->range_type;
                $data['folder_type'] = $source_folder->folder_type;
                $data['name'] = $unique_name;
                $data['data_content'] = $source_folder->data_content;
                $data['description'] = $source_folder->description;

                //folder is copied, we can store it:
                $new_folder->setDataFromEditTemplate($data);

                //now we go through all subfolders and copy them:
                foreach($source_folder->getSubfolders() as $sub_folder) {
                    $result = self::copyFolder($sub_folder, $new_folder, $user);
                    if(!$result instanceof FolderType) {
                        return $result;
                    }
                }

                //now go through all files and copy them, too:
                foreach($source_folder->getFiles() as $file_ref) {
                    $result = self::copyFileRef($file_ref, $new_folder, $user);
                    if(!$result instanceof FileRef) {
                        return $result;
                    }
                }
            } else {
                //no permission to copy course folders!
                return [
                    sprintf(
                        _('Unzureichende Berechtigungen zum Kopieren von Veranstaltungsordner %s in Ordner %s!'),
                        $source_folder->name,
                        $destination_folder->name
                    )
                ];
            }
        } else {
            return [
                sprintf(
                    _('Unzureichende Berechtigungen zum Kopieren von Ordner %s in Ordner %s!'),
                    $source_folder->name,
                    $destination_folder->name
                )
            ];
        }
        return $new_folder;
    }


    /**
     * This method handles moving folders, including
     * subfolders and files.
     *
     * @param FolderType $source_folder The folder that shall be moved.
     * @param FolderType $destination_folder The destination folder.
     * @param User $user The user who wishes to move the folder.
     *
     * @return FolderType|string[] The moved folder's FolderType object on success
     * or an array with error messages on failure.
     */
    public static function moveFolder(FolderType $source_folder, FolderType $destination_folder, User $user)
    {
        if($destination_folder->isWritable($user->id)) {
            $source_data = $source_folder->getEditTemplate();
            $source_data['parent_id'] = $destination_folder->getId();
            $source_folder->setDataFromEditTemplate($source_data);
            return $source_folder;
        } else {
            return [
                sprintf(
                    _('Unzureichende Berechtigungen zum Verschieben von Ordner %s in Ordner %s!'),
                    $source_folder->name,
                    $destination_folder->name
                )
            ];
        }
    }


    /**
     * This method helps with deleting a folder.
     *
     * @param FolderType $folder The folder that shall be deleted.
     * @param User $user The user who wishes to delete the folder.
     *
     * @return FolderType|string[] The deleted folder's FolderType object on success
     * or an array with error messages on failure.
     */
    public static function deleteFolder(FolderType $folder, User $user)
    {
        if(!$folder->isWritable($user->id)) {
            return [
                sprintf(
                    _('Unzureichende Berechtigungen zum Löschen von Ordner %s!'),
                    $folder->name
                )
            ];
        }
        
        if($folder->delete()) {
            //everything went fine!
            return $folder;
        } else {
            //error occured!
            return [
                sprintf(
                    _('Fehler beim Löschvorgang von Ordner %s!'),
                    $folder->name
                )
            ];
        }
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
     * @return array with strings representing the class names of available folder types.
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
                if($range_type == null) {
                    foreach(['course', 'institute', 'message', 'user'] as $known_range_type) {
                        if($declared_class::creatableInStandardFolder($known_range_type)) {
                            if(!is_array($result[$known_range_type])) {
                                $result[$known_range_type] = [];
                            }
                            $result[$known_range_type][] = $declared_class;
                        }
                    }
                } else {
                    if($declared_class::creatableInStandardFolder($range_type)) {
                        if(!is_array($result[$range_type])) {
                            $result[$range_type] = [];
                        }
                        $result[$range_type][] = $declared_class;
                    }
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
     * Copies the content of a folder (files and subfolders) into a given
     * path in the operating system's file system.
     * 
     * @param FolderType folder The folder whose content shall be copied.
     * @param string path The path in the operating system's file system where the content shall be copied into.
     * @param string user_id The user who wishes to copy the content.
     * @param string min_perms If set, the selection of subfolders and files is limited to those
     *     which are visible for users having the minimum permissions.
     * @param bool ignore_perms If set to true, files are copied without checking
     *     the minimum permissions or the permissions of the user given by user_id.
     * @return bool True on success, false on error.
     */
    public static function copyFolderContentIntoPath(
        FolderType $folder,
        $path = null,
        $user_id = 'nobody',
        $min_perms = 'nobody',
        $ignore_perms = false
    ) {
        if(!$path) {
            return false;
        }
        
        global $perm;
        
        //loop through all subfolders, create a directory for each subfolder
        //and call this method recursively:
        foreach($folder->getSubfolders() as $subfolder) {
            //TODO: finalise permission check:
            if($subfolder->isReadable($user_id) /*and
                $perm->have_studip_perm($user_id, $folder->range_id)
                and $perm->have_perm($min_perms)*/
                or $ignore_perms) {
                //User has permissions to read the folder or permission checks
                //are ignored.
                
                $subfolder_path = $path . '/' . $subfolder->name;
                mkdir($subfolder_path, 0700);
                $success = self::copyFolderContentIntoPath(
                    $subfolder,
                    $subfolder_path,
                    $user_id,
                    $min_perms
                );
                if(!$success) {
                    return false;
                }
            }
        }
        
        //loop through all files and copy them to the folder path:
        foreach($folder->getFiles() as $file_ref) {
            if($folder->isFileDownloadable($file_ref, $user_id)
                or $ignore_perms) {
                //The user (given by user_id) has the required permissions
                //to download the file or the permission checks are
                //ignored.
                
                $file_path = $path . '/' . $file_ref->name;
                $success = copy($file_ref->file->getPath(), $file_path);
                if(!$success) {
                    return false;
                }
            }
        }
        
        //Everything went fine.
        return true;
    }
    
    
    /**
     * Returns a FolderType object for the public folder of the given user.
     *
     * @param User user The user whose public folder is requested.
     */
    public static function getPublicFolder(User $user)
    {

    }

    /**
     * @param FolderType $top_folder
     * @param string $user_id
     * @return array
     */
    public static function getFolderFilesRecursive(FolderType $top_folder, $user_id)
    {
        $files = [];
        $folders = [];
        $array_walker = function ($top_folder) use (&$array_walker, &$folders, &$files, $user_id) {
            if ($top_folder->isVisible($user_id) && $top_folder->isReadable($user_id)) {
                $folders[$top_folder->getId()] = $top_folder;
                $files = array_merge($files, $top_folder->getFiles());
                array_walk($top_folder->getSubFolders(), $array_walker);
            }
        };
        $top_folders = [$top_folder];
        array_walk($top_folders, $array_walker);
        return compact('files', 'folders');
    }

    /**
     * @param $id
     * @param null $pluginclass
     * @return FolderType
     */
    public static function getTypedFolder($id, $pluginclass = null)
    {
        if (!isset($pluginclass)) {
            $folder = Folder::find($id);
            if ($folder) {
                return $folder->getTypedFolder();
            }
        } else {
            $plugin = PluginManager::getInstance()->getPlugin($pluginclass);
            if ($plugin instanceof FilesystemPlugin) {
                $folder = $plugin->getFolder($id);
                if ($folder instanceof FolderType) {
                    return $folder;
                }
            }
        }
    }

    public static function fetchURLMetadata($url, $level = 0)
    {

        if ($level > 5) {
            return array('response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400);
        }

        $url_parts = @parse_url($url);
        //filter out localhost and reserved or private IPs
        if (mb_stripos($url_parts["host"], 'localhost') !== false
            || mb_stripos($url_parts["host"], 'loopback') !== false
            || (filter_var($url_parts["host"], FILTER_VALIDATE_IP) !== false
                && (mb_strpos($url_parts["host"], '127') === 0
                    || filter_var($url_parts["host"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)
            )
        ) {
            return array('response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400);
        }
        if ($url_parts['scheme'] === 'ftp') {

            if (preg_match('/[^a-z0-9_.-]/i', $url_parts['host'])) { // exists umlauts ?
                $IDN = new idna_convert();
                $out = $IDN->encode(utf8_encode($url_parts['host'])); // false by error
                $url_parts['host'] = ($out) ? $out : $url_parts['host'];
            }

            $ftp = @ftp_connect($url_parts['host'],$url_parts['port'] ?: 21, 10);
            if (!$ftp) {
                return array('response' => 'HTTP/1.0 502 Bad Gateway', 'response_code' => 502);
            }
            if (!$url_parts["user"]) {
                $url_parts["user"] = "anonymous";
            }
            if (!$url_parts["pass"]) {
                $mailclass = new StudipMail();
                $url_parts["pass"] = $mailclass->getSenderEmail();
            }
            if (!@ftp_login($ftp, $url_parts["user"], $url_parts["pass"])) {
                ftp_quit($ftp);
                return array('response' => 'HTTP/1.0 403 Forbidden', 'response_code' => 403);
            }
            $parsed_link["Content-Length"] = ftp_size($ftp, $url_parts['path']);
            ftp_quit($ftp);
            if ($parsed_link["Content-Length"] != "-1") {
                $parsed_link["HTTP/1.0 200 OK"] = "HTTP/1.0 200 OK";
                $parsed_link["response_code"] = 200;
            } else {
                return array('response' => 'HTTP/1.0 404 Not Found', 'response_code' => 404);
            }
            $parsed_link['filename'] = basename($url_parts['path']);
            $parsed_link['Content-Type'] = get_mime_type($parsed_link['filename']);
            return $parsed_link;

        } else {
            if (!empty($url_parts["path"])) {
                $documentpath = $url_parts["path"];
            } else {
                $documentpath = "/";
            }
            if (!empty($url_parts["query"])) {
                $documentpath .= "?" . $url_parts["query"];
            }
            $host = $url_parts["host"];
            $port = $url_parts["port"];
            $scheme = mb_strtolower($url_parts['scheme']);
            if (!in_array($scheme, words('http https'))) {
                return array('response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400);
            }
            if ($scheme == "https") {
                $ssl = true;
                if (empty($port)) {
                    $port = 443;
                }
            } else {
                $ssl = false;
            }
            if (empty($port)) {
                $port = "80";
            }
            if (preg_match('/[^a-z0-9_.-]/i', $host)) { // exists umlauts ?
                $IDN = new idna_convert();
                $out = $IDN->encode(utf8_encode($host)); // false by error
                $host = ($out) ? $out : $host;
            }
            $socket = @fsockopen(($ssl ? 'ssl://' : '') . $host, $port, $errno, $errstr, 10);
            if (!$socket) {
                return array('response' => 'HTTP/1.0 502 Bad Gateway', 'response_code' => 502);
            } else {
                $urlString = "GET " . $documentpath . " HTTP/1.0\r\nHost: $host\r\n";
                if ($url_parts["user"] && $url_parts["pass"]) {
                    $pass = $url_parts["pass"];
                    $user = $url_parts["user"];
                    $urlString .= "Authorization: Basic " . base64_encode("$user:$pass") . "\r\n";
                }
                $urlString .= sprintf("User-Agent: Stud.IP v%s File Crawler\r\n", $GLOBALS['SOFTWARE_VERSION']);
                $urlString .= "Connection: close\r\n\r\n";
                fputs($socket, $urlString);
                stream_set_timeout($socket, 5);
                $response = '';
                do {
                    $response .= fgets($socket, 128);
                    $info = stream_get_meta_data($socket);
                } while (!feof($socket) && !$info['timed_out'] && mb_strlen($response) < 1024);
                fclose($socket);
            }
            $header = array();
            $raw_header = explode("\n",trim($response));
            if (preg_match("|^HTTP/[^\s]*\s(.*?)\s|", $raw_header[0], $status)) {
                $header['response_code'] = (int)$status[1];
                $header['response'] = trim($raw_header[0]);
            } else {
                return array('response' => 'HTTP/1.0 502 Bad Gateway', 'response_code' => 502);
            }
            for($i = 0; $i < count($raw_header); ++$i){
                $parts = null;
                if(trim($raw_header[$i]) == "") break;
                $matches = preg_match('/^\S+:/', $raw_header[$i], $parts);
                if ($matches){
                    $key = trim(mb_substr($parts[0],0,-1));
                    $value = trim(mb_substr($raw_header[$i], mb_strlen($parts[0])));
                    $header[$key] = $value;
                } else {
                    $header[trim($raw_header[$i])] = trim($raw_header[$i]);
                }
            }

            // Anderer Dateiname?
            $disposition_header = $header['Content-Disposition']
                ?: $header['content-disposition'];
            if ($disposition_header) {
                $header_parts = explode(';', $disposition_header);
                foreach ($header_parts as $part) {
                    $part = trim($part);
                    list($key, $value) = explode('=', $part, 2);
                    if (mb_strtolower($key) === 'filename') {
                        $header['filename'] = trim($value, '"');
                    }
                }
            } else {
                $header['filename'] = basename($url_parts['path']);
            }
            // Weg über einen Locationheader:
            $location_header = $header["Location"]
                ?: $header["location"];
            if (in_array($header["response_code"], array(300, 301, 302, 303, 305, 307)) && $location_header) {
                if (mb_strpos($location_header, 'http') !== 0) {
                    $location_header = $url_parts['scheme'] . '://' . $url_parts['host'] . '/' . $location_header;
                }
                $header = self::fetchURLMetadata($location_header, $level + 1);
            }
            return $header;
        }

    }

}
