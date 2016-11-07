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
    
    /**
     * Helper method to convert a File object into an associative array.
     */
    private function fileToArray(\File $file)
    {
        return [
            'id' => $file->id,
            'user_id' => $file->user_id,
            'mime_type' => $file->mime_type,
            'name' => $file->name,
            'size' => $file->size,
            'storage' => $file->storage,
            'author_name' => $file->author_name,
            'mkdate' => $file->mkdate,
            'chdate' => $file->chdate
        ];
    }
    
    
    /**
     * Helper method to convert a FileRef object into an associative array.
     */
    private function fileRefToArray(\FileRef $file_ref)
    {
        return [
            'id' => $file_ref->id,
            'file_id' => $file_ref->file_id,
            'folder_id' => $file_ref->folder_id,
            'user_id' => $file_ref->user_id,
            'name' => $file_ref->name,
            'downloads' => $file_ref->downloads,
            'description' => $file_ref->description,
            'license' => $file_ref->license,
            'content_terms_of_use_id' => $file_ref->content_terms_of_use_id,
            'mkdate' => $file_ref->mkdate,
            'chdate' => $file_ref->chdate
        ];
    }
    
    
    /**
     * Helper method to convert a ContentTermsOfUse object into an associative array.
     */
    private function contentTermsOfUseToArray(\ContentTermsOfUse $content_terms_of_use)
    {
         return [
            'id' => $content_terms_of_use->id,
            'name' => $content_terms_of_use->name,
            'internal_name' => $content_terms_of_use->internal_name,
            'description' => $content_terms_of_use->description,
            'download_condition' => $content_terms_of_use->download_condition
        ];
    }
    
    
    /**
     * Helper method to convert a Folder object into an associative array.
     */
    private function folderToArray(\Folder $folder)
    {
        return [
            'id' => $folder->id,
            'user_id' => $folder->user_id,
            'parent_id' => $folder->parent_id,
            'range_id' => $folder->range_id,
            'range_type' => $folder->range_type,
            'folder_type' => $folder->folder_type,
            'name' => $folder->name,
            'data_content' => $folder->data_content,
            'description' => $folder->description,
            'mkdate' => $folder->mkdate,
            'chdate' => $folder->chdate
        ];
    }
    
    
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
     * Get the data of a file by the ID of an associated FileRef object (shortcut route)
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
    
    
    /**
     * Get a folder object
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
        
        return $result;
    }
    
    
    
    
}