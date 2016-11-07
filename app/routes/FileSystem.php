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
     * @get /file_ref/:file_ref_id
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
        
        //check if the current user has the permissions to read this file reference:
        if(!$file_ref->folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this file reference!');
        }
        
        $extended_data = \Request::get('extended', 0);
        //the current user may read the file reference:
        $result = $file_ref->toRawArray();
        
        
        //add permissions for the user who called this REST route to the result:
        
        //this code wouldn't be executed if the FileRef wasn't readable (see above):
        $result['is_readable'] = true;
        
        $result['is_downloadable'] = $file_ref->isDownloadable($user_id);
        $result['is_editable'] = $file_ref->isEditable($user_id);
        $result['is_deletable'] = $file_ref->isDeletable($user_id);
        
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
     * @get /file_ref/:file_ref_id/file_data
     */
    public function getFileRefData($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if(!$file_ref) {
            $this->halt(404, 'File reference not found!');
        }
        
        //check if the current user has the permissions to read this file reference:
        if($file_ref->folder) {
            if($file_ref->folder->isReadable($user_id)) {
                if(!$file_ref->file) {
                    $this->halt(500, 'File reference has no associated file object!');
                }
                //if this code is executed we can read the file's data
                //TODO
            }
        } else {
            $this->halt(500, 'File reference has no associated folder object!');
        }
        
        $user_id = \User::findCurrent()->id;
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
        
        $user_id = \User::findCurrent()->id;
        
        if(!$folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this folder!');
        }
        
        //if the code below is executed the user is permitted to read the folder
        
        $result = $folder->toRawArray();
        
        if($folder->subfolders) {
            $result['subfolders'] = [];
            foreach($folder->subfolders as $subfolder) {
                $result['subfolders'][] = $subfolder->toRawArray();
            }
        }
        
        if($folder->file_refs) {
            $result['file_refs'] = [];
            foreach($folder->file_refs as $file_ref) {
                $result['file_refs'][] = $file_ref->toRawArray();
            }
        }
        
        $result['is_readable'] = $folder->isReadable($user_id);
        $result['is_editable'] = $folder->isEditable($user_id);
        $result['is_deletable'] = $folder->isDeletable($user_id);
        
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
            $this->halt(404, 'Folder not found!');
        }
        
        $user = \User::findCurrent();
        
        if(!$parent_folder->isEditable($user->id)) {
            $this->halt(403, 'You are not permitted to create a subfolder in the parent folder!');
        }
        
        $folder = new \Folder();
        $folder->name = $name;
        $folder->description = $description;
        
        $folder_type = 'StandardFolder'; //to be extended
        
        $errors = \FileManager::createSubFolder($folder, $parent_folder, $user, $folder_type);
        if(!empty($errors)) {
            $this->halt(500, 'Error when creating a subfolder: ' . implode(' ', $errors));
        }
        
        return $folder;
    }
    
    
    /**
     * Get a list with all FileRef objects of a folder.
     * @get /folder/:folder_id/file_refs
     */
    public function getFileRefsOfFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if(!$folder) {
            $this->halt(404, 'Folder not found!');
        }
        
        $user_id = \User::findCurrent()->id;
        
        if(!$folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this folder!');
        }
        
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
        
        $user_id = \User::findCurrent()->id;
        
        if(!$folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this folder!');
        }
        
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
        
        if(!$user_id) {
            //user_id is not set: use the ID of the current user
            $user_id = \User::findCurrent()->id;
        }
        
        //read permissions of the user and return them:
        
        return [
            'folder_id' => $folder->id,
            'user_id' => $user_id,
            'is_readable' => $folder->isReadable($user_id),
            'is_editable' => $folder->isEditable($user_id),
            'is_deletable' => $folder->isDeletable($user_id),
        ];
    }
    
    
    /**
     * 
     */
}