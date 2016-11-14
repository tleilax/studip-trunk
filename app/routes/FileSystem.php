<?php
/**
 * @author Moritz Strohm <strohm@data-quest.de>
 * @license GNU General Public License Version 2 or later
 * 
 * Partially based upon the Files.php source code from Jan-Hendrik Willms
 * (tleilax+studip@gmail.com) and mluzena@uos.de which is also
 * licensed under the terms of the GNU General Public License Version 2
 * or later.
 */

namespace RESTAPI\Routes;


/**
 * This class implements REST routes for the new Stud.IP file system.
 */
class FileSystem extends \RESTAPI\RouteMap
{
    
    
    // FILE REFERENCE AND FILE ROUTES:
    
    
    /**
     * Get a file reference object (metadata)
     * @get /file/:file_ref_id
     */
     public function getFileRef($file_ref_id)
     {
        //check if the file_id references a file reference object:
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        $user_id = \User::findCurrent()->id;
        
        //check if the file reference is placed inside a folder.
        //(must be present to check for permissions)
        if(!$file_ref->folder) {
            $this->halt(500, 'File reference has no folder!');
        }
        
        $folder_type = $file_ref->folder->getTypedFolder();
        if(!$folder_type) {
            $this->halt(500, "File reference's folder has no folder type!");
        }
        
        //check if the current user has the permissions to read this file reference:
        if(!$folder_type->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this file reference!');
        }
        
        $extended_data = \Request::get('extended', 0);
        //the current user may read the file reference:
        $result = $file_ref->toRawArray();
        
        
        //add permissions for the user who called this REST route to the result:
        
        //this code wouldn't be executed if the FileRef wasn't readable (see above):
        $result['is_readable'] = true;
        
        $result['is_downloadable'] = $folder_type->isFileDownloadable($file_ref->id, $user_id);
        $result['is_editable'] = $folder_type->isFileEditable($file_ref->id, $user_id);
        $result['is_writable'] = $folder_type->isFileWritable($file_ref->id, $user_id);
        
        //maybe the user wants not just only the FileRef object's data
        //but also data from related objects:
        if($extended_data == 1) {
            //In case more data are requested we can add a File,
            //Folder and ContentTermsOfUse object
            
            //folder does exist (since we checked for its existence above)
            $result['folder'] = $file_ref->folder->toRawArray();
            
            //file and owner might not exist, so we have to check that:
            if($file_ref->file) {
                $result['file'] = $file_ref->file->toRawArray();
            }
            if($file_ref->owner) {
                $result['owner'] = $file_ref->owner->toRawArray();
            }
            
            //$result['license'] = $file_ref->license; //to be activated when licenses are defined
            
            if($file_ref->terms_of_use) {
                $result['terms_of_use'] = 
                    $file_ref->terms_of_use->toRawArray();
            }
        }
        
        return $result;
    }
    
    
    /**
     * Get the data of a file by the ID of an associated FileRef object
     * 
     * @get /file/:file_ref_id/download
     */
    public function getFileRefData($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        if(!$file_ref->folder) {
            $this->halt(500, 'File reference is not bound to a folder!');
        }
        
        $folder_type = $file_ref->folder->getTypedFolder();
        if(!$folder_type) {
            $this->halt(500, "Cannot find folder type of the file reference's folder!");
            return;
        }
        
        
        $user_id = \User::findCurrent()->id;
        
        //check if the current user has the permissions to read this file reference:
        if($folder_type->isReadable($user_id)) {
            //if this code is executed we can read the file's data
            
            //check if file exists:
            if(!$file_ref->file) {
                $this->halt(500, 'File reference has no associated file object!');
            }
            
            $data_path = $file_ref->file->getPath();
            if(!file_exists($data_path)) {
                $this->halt(404, "File was not found in the operating system's file system!");
            }
            
            $this->lastModified($file_ref->file->chdate);
            $this->sendFile($data_path, ['filename' => $file_ref->name]);
        }
    }
    
    
    /**
     * Upload file data using a FileReference to it.
     * 
     * @put /file/:file_ref_id/upload
     */
    public function uploadFileData($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        $user_id = \User::findCurrent()->id;
        
        //check if the current user has the permissions to upload
        //data for the file reference's file object:
        if($file_ref->folder) {
            if($file_ref->folder->isEditable($user_id)) {
                if(!$file_ref->file) {
                    $this->halt(500, 'File reference has no associated file object!');
                }
                //if this code is executed the user can
                //upload new data to the associated file object
                $data_path = $file_ref->file->getPath();
                if(!file_exists($data_path)) {
                    $this->halt(404, "File was not found in the operating system's file system!");
                }
                
                //TODO
            }
        } else {
            $this->halt(500, 'File reference has no associated folder object!');
        }
    }
    
    
    /**
     * Edit a file reference.
     * 
     * @put /file/:file_ref_id
     */
    public function editFileRef($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        $user = \User::findCurrent();
        
        $errors = \FileManager::editFileRef(
            $file_ref,
            $user,
            $this->data['name'],
            $this->data['description'],
            $this->data['content_term_of_use_id'],
            $this->data['license']
        );
        
        if(!empty($errors)) {
            $this->halt(500, 'Error while editing a file reference: ' . implode(' ', $errors));
        }
        
        return $file_ref->toRawArray();
    }
    
    
    /**
     * Copies a file reference.
     * 
     * @post /file/:file_ref_id/copy/:destination_folder_id
     */
    public function copyFileRef($file_ref_id, $destination_folder_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        $destination_folder = \Folder::find($destination_folder_id);
        if(!$destination_folder) {
            $this->halt(404, 'Destination folder not found!');
        }
        
        $destination_folder = $destination_folder->getTypedFolder();
        if(!$destination_folder) {
            $this->halt(500, 'Cannot find folder type of destination folder!');
            return;
        }
            
        
        $user = \User::findCurrent();
        
        $errors = \FileManager::copyFileRef($file_ref, $destination_folder, $user);
        
        if(!empty($errors)) {
            $this->halt(500, 'Error while copying a file reference: ' . implode(' ', $errors));
        }
    
        return $file_ref->toRawArray();
        
    }
    
    
    /**
     * Moves a file reference.
     * 
     * @post /file/:file_ref_id/move/:destination_folder_id
     */
    public function moveFileRef($file_ref_id, $destination_folder_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        $destination_folder = \Folder::find($destination_folder_id);
        if(!$destination_folder) {
            $this->halt(404, 'Destination folder not found!');
        }
        
        $destination_folder = $destination_folder->getTypedFolder();
        if(!$destination_folder) {
            $this->halt(500, 'Cannot find folder type of destination folder!');
            return;
        }
        
        
        $user = \User::findCurrent();
        
        $errors = \FileManager::moveFileRef($file_ref, $destination_folder, $user);
        
        if(!empty($errors)) {
            $this->halt(500, 'Error while moving a file reference: ' . implode(' ', $errors));
        }
    
        return $file_ref->toRawArray();
    }
    
    
    /**
     * Deletes a file reference.
     * 
     * @delete /file/:file_ref_id
     */
    public function deleteFileRef($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        $user = \User::findCurrent();
        
        $errors = \FileManager::deleteFileRef(
            $file_ref,
            $user
        );
            
        if(!empty($errors)) {
            $this->halt(500, 'Error while deleting a file reference: ' . implode(' ', $errors));
        }
        
        $this->halt(200, 'OK');
    }
    
    
    // FOLDER ROUTES:
    
    
    /**
     * Returns a list of defined folder types, separated by range type.
     * @get /studip/file_system/folder_types
     */
    public function getDefinedFolderTypes()
    {
        return \FileManager::getFolderTypes();
    }
    
    
    /**
     * Get a folder object with its file references, subdirectories and the permissions for the user who has made the API call.
     * @get /folder/:folder_id
     */
    public function getFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if(!$folder) {
            $this->halt(404, 'Folder not found!');
        }
        
        $folder_type = $folder->getTypedFolder();
        if(!$folder_type) {
            $this->halt(500, 'Folder type not found!');
        }
        
        $user_id = \User::findCurrent()->id;
        
        $result['is_visible'] = $folder_type->isVisible($user_id);
        $result['is_readable'] = $folder_type->isReadable($user_id);
        $result['is_writable'] = $folder_type->isWritable($user_id);
        
        //If the folder isn't readable by the user (given by user_id)
        //the result parameter is_readable is set to false.
        
        if($result['is_readable']) {
            
            $result = $folder->toRawArray();
            
            $subfolders = $folder_type->getSubfolders();
            
            if($subfolders) {
                $result['subfolders'] = [];
                foreach($subfolders as $subfolder) {
                    $result['subfolders'][] = $subfolder->getEditTemplate();
                }
            }
            
            $file_refs = $folder_type->getFiles();
            
            if($file_refs) {
                $result['file_refs'] = [];
                foreach($file_refs as $file_ref) {
                    $result['file_refs'][] = $file_ref->toRawArray();
                }
            }
            
        }
        
        return $result;
    }
    
    
    /**
     * Creates a new folder inside of another folder and returns the new object on success.
     * @post /folder/:parent_folder_id/new_folder
     */
    public function createNewFolder($parent_folder_id)
    {
        $parent_folder = \Folder::find($parent_folder_id);
        if(!$parent_folder) {
            $this->halt(404, 'Parent folder not found!');
        }
        
        $parent_folder = $parent_folder->getTypedFolder();
        if(!$parent_folder) {
            $this->halt(500, 'Parent folder has an invalid folder type!');
        }
        
        $user = \User::findCurrent();
        
        if(!$parent_folder->isWritable($user->id)) {
            $this->halt(403, 'You are not permitted to create a subfolder in the parent folder!');
        }
        
        
        $folder_type = 'StandardFolder'; //to be extended
        
        $result = \FileManager::createSubFolder(
            $parent_folder,
            $user,
            $folder_type,
            $this->data['name'],
            $this->data['description']
        );
        
        if(is_array($errors)) {
            $this->halt(500, 'Error when creating a subfolder: ' . implode(' ', $errors));
        }
        
        return $result->getEditTemplate();
    }
    
    
    /**
     * Get a list with all FileRef objects of a folder.
     * @get /folder/:folder_id/files
     */
    public function getFileRefsOfFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if(!$folder) {
            $this->halt(404, 'Folder not found!');
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            $this->halt(500, 'Folder type not found!');
        }
        
        if(!$folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this folder!');
        }
        
        $user_id = \User::findCurrent()->id;
        
        $result = [];
        
        $file_refs = \FileRef::findBySql(
            'folder_id = :folder_id ORDER BY name ASC LIMIT :limit OFFSET :offset',
            [
                'folder_id' => $folder->id,
                'limit' => $this->limit,
                'offset' => $this->offset
            ]
        );
        
        $num_file_refs = \FileRef::countBySql(
            'folder_id = :folder_id',
            [
                'folder_id' => $folder->id
            ]
        );
        
        
        if($file_refs) {
            foreach($file_refs as $file_ref) {
                $result[] = $file_ref->toRawArray();
            }
        }
        
        return $this->paginated($result, $num_file_refs, ['folder_id' => $folder->id]);
    }
    
    
    /**
     * Get a list with all FileRef objects of a folder.
     * @get /folder/:folder_id/subfolders
     */
    public function getSubfoldersOfFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if(!$folder) {
            $this->halt(404, 'Folder not found!');
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            $this->halt(500, 'Folder type not found!');
        }
        
        if(!$folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this folder!');
        }
        
        
        $user_id = \User::findCurrent()->id;
        
        $result = [];
        
        $subfolders = \Folder::findBySql(
            'parent_id = :parent_id ORDER BY name ASC LIMIT :limit OFFSET :offset',
            [
                'parent_id' => $folder->id,
                'limit' => $this->limit,
                'offset' => $this->offset
            ]
        );
        
        $num_subfolders = \Folder::countBySql(
            'parent_id = :parent_id',
            [
                'parent_id' => $folder->id
            ]
        );
        
        
        
        if($subfolders) {
            foreach($subfolders as $subfolder) {
                $result[] = $subfolder->toRawArray();
            }
        }
        
        return $this->paginated($result, $num_subfolders, ['folder_id' => $folder->id]);
    }
    
    
    /**
     * Get a list with permissions a user (or the current user) has for a folder.
     * @get /folder/:folder_id/permissions
     * @get /folder/:folder_id/permissions/:user_id
     */
    public function getFolderPermissions($folder_id, $user_id = null)
    {
        $folder = \Folder::find($folder_id);
        if(!$folder) {
            $this->halt(404, 'Folder not found!');
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            $this->halt(500, 'Folder type not found!');
        }
        
        if(!$user_id) {
            //user_id is not set: use the ID of the current user
            $user_id = \User::findCurrent()->id;
        }
        
        //read permissions of the user and return them:
        
        return [
            'folder_id' => $folder->id,
            'user_id' => $user_id,
            'is_visible' => $folder->isVisible($user_id),
            'is_readable' => $folder->isReadable($user_id),
            'is_writable' => $folder->isWritable($user_id),
        ];
    }
    
    
    /**
     * Allows editing the name or the description (or both) of a folder.
     * 
     * @put /folder/:folder_id
     */
    public function editFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if(!$folder) {
            $this->halt(404, 'Folder not found!');
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            $this->halt(500, 'Folder has an invalid folder type!');
        }
        
        $errors = \FileManager::editFolder($folder, \User::findCurrent(), $this->data['name'], $this->data['description']);
        
        if(!empty($errors)) {
            $this->halt(500, 'Error while editing a folder: ' . implode(' ', $errors));
        }
        
    }
    
    
    /**
     * Copies a folder into another folder.
     * 
     * @post /folder/:folder_id/copy/:destination_folder_id
     */
    public function copyFolder($folder_id, $destination_folder_id)
    {
        $folder = \Folder::find($folder_id);
        $destination_folder = \Folder::find($destination_folder_id);
        
        if(!$folder || !$destination_folder) {
            if(!$folder) {
                $this->halt(404, 'Source folder not found!');
            } else {
                $this->halt(404, 'Destination folder not found!');
            }
        }
        
        $folder_type = $folder->getTypedFolder();
        if(!$folder_type) {
            $this->halt(500, 'Source folder type not found!');
        }
        
        $destination_folder_type = $folder->getTypedFolder();
        if(!$destination_folder_type) {
            $this->halt(500, 'Destination folder type not found!');
        }
        
        
        $user = \User::findCurrent();
        
        $errors = \FileManager::copyFolder_OLD($folder, $destination_folder, $user);
        
        if(!empty($errors)) {
            $this->halt(500, 'Error while copying a folder: ' . implode(' ', $errors));
        }
        
        return $destination_folder->toRawArray();
    }
    
    
    /**
     * Move a folder into another folder.
     * @post /folder/:folder_id/move/:destination_folder_id
     */
    public function moveFolder($folder_id, $destination_folder_id)
    {
        $folder = \Folder::find($folder_id);
        $destination_folder = \Folder::find($destination_folder_id);
        
        if(!$folder || !$destination_folder) {
            if(!$folder) {
                $this->halt(400, 'Source folder not found!');
            } else {
                $this->halt(400, 'Destination folder not found!');
            }
        }
        
        $folder = $folder->getTypedFolder();
        if(!$folder) {
            $this->halt(500, 'Folder has an invalid folder type!');
        }
        
        $destination_folder = $destination_folder->getTypedFolder();
        if(!$destination_folder) {
            $this->halt(500, 'Destination folder has an invalid folder type!');
        }
        
        
        $user = \User::findCurrent();
        
        $errors = \FileManager::moveFolder($folder, $destination_folder, $user);
        
        if(!empty($errors)) {
            $this->halt(500, 'Error while moving a folder: ' . implode(' ', $errors));
        }
        
        return $folder->toRawArray();
    }
    
    
    /**
     * Deletes a folder.
     * 
     * @delete /folder/:folder_id
     */
    public function deleteFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        
        if(!$folder) {
            $this->halt(404, 'Folder not found!');
        }
        
        $folder_type = $folder->getTypedFolder();
        
        if(!$folder_type) {
            $this->halt(500, 'Folder type of folder not found!');
        }
        
        $user = \User::findCurrent();
        
        $errors = \FileManager::deleteFolder($folder_type, $user);
        
        if(!empty($errors)) {
            $this->halt(500, 'Error while deleting a folder: ' . implode(' ', $errors));
        }
        
        return $folder->toRawArray();
    }
    
    
    // RELATED OBJECT ROUTES:
    
    
    /**
     * Get a collection of all ContentTermsOfUse objects
     * 
     * @get /studip/content_terms_of_use_list
     */
     public function getContentTermsOfUseList()
     {
        $content_terms_of_use_objects = \ContentTermsOfUse::findBySql(
            'TRUE ORDER BY name ASC LIMIT :limit OFFSET :offset',
            [
                'limit' => $this->limit,
                'offset' => $this->offset
            ]
        );
        
        $num_content_terms_of_use_objects = \ContentTermsOfUse::countBySql('TRUE');
        
        $result = [];
        if($content_terms_of_use_objects) {
            foreach($content_terms_of_use_objects as $object) {
                $result[] = $object->toRawArray();
            }
        }
        
        return $this->paginated($result, $num_content_terms_of_use_objects);
     }
     
     
}