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
    public function upload_action($folder_id)
    {
        if (Request::isPost() && is_array($_FILES)) {

            $folder = Folder::find($folder_id);
            if(!$folder) {
                PageLayout::postError(
                    _('Zielordner für Dateiupload nicht gefunden!')
                );
                return;
            }

            //CSRFProtection::verifyUnsafeRequest();
            $validatedFiles = FileManager::handleFileUpload(
                $_FILES['file'],
                $folder->getTypedFolder(),
                $GLOBALS['user']->id
            );

            if (count($validatedFiles['error'])) {
                //error during upload: display error message:
                PageLayout::postError(
                    _('Beim Upload ist ein Fehler aufgetreten ', 
                    array_map('htmlready', $validatedFiles['error'])
                    )
                );
            } else {
                //all files were uploaded successfully:
                foreach($validatedFiles['files'] as $file) {
                    if ($file->store() && ($fileref = $folder->linkFile($file, Request::get('description', "")))) {
                        $storedFiles[] = $fileref;
                    }
                }
                if (count($storedFiles) && !Request::isAjax()) {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Es wurden %s Dateien hochgeladen'),
                            count($storedFiles)
                        ),
                        array_map('htmlready', $storedFiles)
                    );
                }
                if (Request::isAjax()) {
                    $output = array(
                        "new_html" => array()
                    );
                    foreach ($storedFiles as $fileref) {
                        $this->fileref = $fileref;
                        $this->controller = $this;
                    }
                    $this->render_json($output);
                }
                
            }
        }
        $this->folder_id = $folder_id;
    }
    
    
    public function download_action($fileId)
    {
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
    
    
    public function edit_action($fileRef_id)
    {
        /*$fileName = Request::get('fileName');
        
        //TODO: implement updating the file's data
        //(handle uploaded files)
        
        if($fileId && $fileName) {
            $file = File::find($fileId);
            if($file) {
                $file->filename = $fileName;
                $file->store();
            } else {
                //file not found
            }
        } else {
            //file ID not set
        }*/
        
        
        
        $fileref = FileRef::find($fileRef_id);
        if ($fileref) {
            $this->fileref_id = $fileref->id;
            $this->folder_id = $fileref->folder_id;
            $this->description = $fileref->description;
        }
        
        if (Request::submitted('save')) {
            $fileref = FileRef::find(Request::option('fileref_id'));
            if ($fileref) {
                //$fileref->licence = Request::get('licence');
                $fileref->description = Request::get('description');
                if ($fileref->store()) {
                    PageLayout::postSuccess(_('Änderungen gespeichert.'));
                } else {
                    PageLayout::postError(_('Fehler beim Speichern der Änderungen.'));
                }
                return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'));
            }
        }
        
    }
    
    
    public function link_action($fileId)
    {
        $targetFolderId = Request::get('folderId');
        $description = Request::get('description', '');
        $license = Request::get('license', 'UnknownLicense');
        
        if($fileId && $targetFolderId) {
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
    
    
    public function copy_action($fileId)
    {
        $destinationFolderId = Request::get('destinationId');
        
        if(!$fileId) {
            PageLayout::postError(_('Datei-ID nicht gesetzt!'));
            return;
        }
        
        $this->file = File::find($fileId);
        if(!$this->file) {
            PageLayout::postError(_('Datei nicht gefunden!'));
            return;
        }
        
        if($destinationFolderId) {
            //form was sent
            $this->destinationFolder = Folder::find($destinationFolderId);
            
            if(!$this->destinationFolder) {
                PageLayout::postError(_('Zielordner nicht gefunden!'));
                return;
            }
            
            //destination folder is present. We now have to check,
            //if the current user is the owner of the file (by looking at the folder
            //where the file resides).
            //If so, we just make a new reference to that file.
            //If the current usern isn't the owner of the file we must copy the file
            //and its content.
            
            global $perm;
            
            //TODO: get folder of file
            
            //TODO: get file contents if the file is not owned by the current user
            
            //TODO: link file if the file is owned by the current user
            
        }
    }
    
    public function move_action($file_ref_id)
    {

        global $perm;
        $user = User::findCurrent();
        
        if (Request::submitted("do_move")) {
        
            $folder_id = Request::get('dest_folder');
            
            if($file_ref_id && $folder_id) {
                
                $file_ref = FileRef::find($file_ref_id);                
                $source_folder = Folder::find($file_ref->folder_id);
                $destination_folder = Folder::find($folder_id);
                
                if($source_folder && $destination_folder) {
                    
                    $errors = [];
                    
                    if (Request::get("copymode", 'move') == 'move') {
                        $errors = FileManager::moveFileRef($file_ref, $destination_folder, $user);
                    } else {
                        $errors = FileManager::copyFileRef($file_ref, $destination_folder, $user);
                    }
                    
                    if(empty($errors)){
                        PageLayout::postSuccess(_('Die Datei wurde kopiert.'));
                    } else {
                        PageLayout::postError(_('Fehler beim Kopieren der Datei.'), $errors);
                    }
                    
                    
                    $dest_range = $destination_folder->range_id;
                    
                    switch ($destination_folder->range_type) {
                        case 'course':
                        case 'inst':
                        case 'institute':
                            return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$folder_id. '?cid=' . $dest_range));                            
                        case 'user':
                            return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/'.$folder_id));
                        default:
                            return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$folder_id));
                    }
                }
            }
            
        } else {
            
            
            
            if ($perm->have_perm('root')) {
                $inst_sql =  "SELECT DISTINCT Institute.Institut_id, Institute.Name " .
                    "FROM Institute " .
                    "LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) " .
                    "WHERE Institute.Name LIKE :input " .
                    "OR Institute.Strasse LIKE :input " .
                    "OR Institute.email LIKE :input " .
                    "OR range_tree.name LIKE :input " .
                    "ORDER BY Institute.Name";
                
                $parameters = array(
                    'semtypes' => studygroup_sem_types() ?: array(),
                    'exclude' => array()
                );
                
                
            /*} else if ($perm->have_perm('admin')) {
                
                
                $parameters = array(
                    'semtypes' => studygroup_sem_types() ?: array(),
                    'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                    }, Institute::getMyInstitutes()),
                    'exclude' => array()
                    );
            */
            } else {
                
                $inst_sql =  "SELECT DISTINCT Institute.Institut_id, Institute.Name " .
                    "FROM Institute " .
                    "LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) " .
                    "LEFT JOIN user_inst ON (user_inst.Institut_id = Institute.Institut_id)" .
                    "WHERE user_inst.user_id = '" . $user_id . "' " .
                    "AND Institute.Name LIKE :input " .
                    "OR Institute.Strasse LIKE :input " .
                    "OR Institute.email LIKE :input " .
                    "OR range_tree.name LIKE :input " .
                    "ORDER BY Institute.Name";
                
                
                $parameters = array(
                    'userid' => $GLOBALS['user']->id,
                    'semtypes' => studygroup_sem_types() ?: array(),
                    'exclude' => array()
                );
            }
                        
            $coursesearch = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);
            //$instsearch = StandardSearch::get('Institut_id');
            $instsearch = SQLSearch::get($inst_sql, _("Einrichtung suchen"), 'Institut_id');
            $this->search = QuickSearch::get('course_id', $coursesearch)
            ->setInputStyle('width:100%')
            ->fireJSFunctionOnSelect('function(){STUDIP.Files.getFolders();}')
            ->withButton()
            ->render();
            $this->inst_search = QuickSearch::get('Institut_id', $instsearch)
            ->setInputStyle('width:100%')
            ->fireJSFunctionOnSelect('function(){STUDIP.Files.getFolders();}')
            ->withButton()
            ->render();
            
            
            $this->move_copy = Request::get("copymode", 'move');
            $this->user_id = $user_id;
            $this->file_ref = $file_ref_id;
        }
    }
    
    public function getFolders_action()
    {
        $rangeId = Request::get("range");        
        $folders = Folder::findBySQL("range_id=?", array($rangeId));
        $folderray = array();
        foreach ($folders as $folder) {
            $folderray[][ $folder->getPath()] = $folder->id;
        }
        sort($folderray);
        
        if (Request::isAjax()) {
            echo json_encode($folderray);
            die();
        } else {
            $this->render_nothing();
        }
    }
    
    public function delete_action($fileRefId)
    {
       if($fileRefId) {
            $fileRef = FileRef::find($fileRefId);           
            if($fileRef) {                
                $fileRef->delete();
            } else {
                //file not found
            }
        } else {
            //you can't delete things you don't know
        }
        
        $folderId = Request::option('folder_id', Folder::findTopFolder(Request::get("cid"))->id);
        //DEVELOPMENT STAGE ONLY:
        return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$folderId));
    }
}
