<?php
/**
 * file.php - controller to display files in a course
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.0
 */


/**
    This controller contains actions related to single files.
**/
class FileController extends AuthenticatedController
{
    public function upload_action()
    {
        if (Request::isPost() and is_array($_FILES)) {
            $folder = Folder::find(Request::option('folder_id'));
            CSRFProtection::verifyUnsafeRequest();
            $validatedFiles = FileManager::handleFileUpload(
                $_FILES['file'],
                $folder->getTypedFolder(),
                $GLOBALS['user']->id
            );
            if (count($validatedFiles['error'])) {
                //error during upload: display error message:
                PageLayout::postError(
                    _('Beim Upload ist ein Fehler aufgetreten ', 
                    array_map('htmlready', $validated_files['error'])
                    )
                );
            } else {
                //all files were uploaded successfully:
                foreach($validatedFiles['files'] as $file) {
                    if ($file->store() && $folder->linkFile($file, Request::get('description'))) {
                        $storedFiles[] = $file->name;
                    }
                }
                if (count($storedFiles)) {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Es wurden %s Dateien hochgeladen'),
                            count($storedFiles)
                        ),
                        array_map('htmlready', $storedFiles)
                    );
                }
                
                //DEVELOPMENT STAGE ONLY:
                return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/tree/'));
            }
        }
        $this->folder_id = Request::option('topfolder');
    }
    
    
    public function download_action()
    {
        $fileId = Request::get('fileId');
        if($fileId) {
            $file = File::find($fileId);
            if($file) {
                $dataPath = $file->getPath();
                
                //STUB! Change this to support big files!
                $data = file_get_contents($dataPath);
                
                if($file->mime_type) {
                    $this->set_content_type($file->mime_type);
                }
                
                //TODO: send data
                
            } else {
                //send 404 not found
            }
        } else {
            //send 400 bad request
        }
    }
    
    
    public function edit_action()
    {
        $fileId = Request::get('fileId');
        $fileName = Request::get('fileName');
        
        //TODO: implement updating the file's data
        //(handle uploaded files)
        
        if($fileId and $fileName) {
            $file = File::find($fileId);
            if($file) {
                $file->filename = $fileName;
                $file->store();
            } else {
                //file not found
            }
        } else {
            //file ID not set
        }
    }
    
    
    public function link_action()
    {
        $fileId = Request::get('fileId');
        $targetFolderId = Request::get('folderId');
        $description = Request::get('description', '');
        $license = Request::get('license', 'UnknownLicense');
        
        if($fileId and $targetFolderId) {
            $folder = Folder::find($folderId);
            if($folder) {
                $folder->linkFile($fileId, $description, $license);
                //maybe it is useful to redirect to the folder from here...
            } else {
                //file or folder not found: can't link non-existing files
                //or link existing files to non-existing folders!
            }
        } else {
            //file-ID and folder-ID not given: can't build link
        }
    }
    
    
    public function move_action()
    {
        $fileId = Reuqest::get('fileId');
        $sourceFolderId = Request::get('sourceId');
        $destinationFolderId = Request::get('destinationId');
        
        if($fileId and $sourceFolderId and $destinationFolderId) {
            $file = File::find($fileId);
            $sourceFolder = Folder::find($sourceFolderId);
            $destinationFolder = Folder::find($destinationFolderId);
            if($file and $sourceFolder and $destinationFolder) {
                //ok, we can move the file
                
            } else {
                //either the file or the source folder or the
                //destination folder is missing: we can't move the file!
            }
        } else {
            //at least one of the required parameters is not set:
            //TODO: show some error message
        }
            
    }
    
    
    public function delete_action()
    {
        $fileId = Request::get('fileId');
        
        if($fileId) {
            $file = File::find($fileId);
            if($file) {
                $file->deleteDataFile();
                $file->delete();
            } else {
                //file not found
            }
        } else {
            //you can't delete things you don't know
        }
    }
}
