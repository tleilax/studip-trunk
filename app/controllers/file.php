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
    
    /**
     * This is a helper method that decides where a redirect shall be made
     * in case of error or success after an action was executed.
     */
    private function redirectToFolder(Folder $folder, $message = null)
    {
        if($message instanceof MessageBox) {
            if(Request::isDialog()) {
                $this->render_text($message);
            } else {
                PageLayout::postMessage($message);
            }
        }
        
        if(!Request::isDialog()) {
            //we only need to redirect when we're not in a dialog!
            
            $dest_range = $folder->range_id;
    
            switch ($folder->range_type) {
                case 'course':
                case 'institute':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $folder->id . '?cid=' . $dest_range));                            
                case 'user':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/' . $folder->id));
                default:
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $folder->id));
            }
        }
    }
    
    
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
    
    
    public function download_action($file_id)
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
    
    
    /**
     * The action for editing a file reference.
     */
    public function edit_action($file_ref_id)
    {
        $file_ref = FileRef::find($file_ref_id);
        if ($file_ref) {
            $this->file_ref_id = $file_ref->id;
            $this->folder_id = $file_ref->folder_id;
            $this->description = $file_ref->description;
        } else {
            if(Request::isDialog()) {
                $this->render_text(
                    MessageBox::error(_('Die zu bearbeitende Datei wurde nicht gefunden!'))
                );
            } else {
                PageLayout::postError(_('Die zu bearbeitende Datei wurde nicht gefunden!'));
            }
        }
        
        if (Request::submitted('save')) {
            //form was sent
            $this->name = Request::get('name');
            $this->description = Request::get('description');
            $this->license = Request::get('licence');
            
            $errors = FileManager::editFileRef($file_ref, $this->name, $this->description, $this->license);
            if (empty($errors)) {
                $this->redirectToFolder(
                    $file_ref->folder,
                    MessageBox::success(_('Änderungen gespeichert!'))
                );
            } else {
                $this->redirectToFolder(
                    $file_ref->folder,
                    MessageBox::error(_('Fehler beim Speichern der Änderungen!'), $errors)
                );
            }
        } else {
            //load default data:
            $this->name = $file_ref->name;
            $this->description = $file_ref->description;
            $this->license = $file_ref->license;
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
    
    
    /**
     * This action handles copying file references to another folder.
     */
    public function copy_action($file_ref_id)
    {
        $destinationFolderId = Request::get('destinationId');
        
        if(!$file_ref_id) {
            PageLayout::postError(_('Datei-ID nicht gesetzt!'));
            return;
        }
        
        $this->file_ref = FileRef::find($file_ref_id);
        if(!$this->file_ref) {
            PageLayout::postError(_('Datei nicht gefunden!'));
            return;
        }
        
        if($destinationFolderId) {
            //form was sent
            $this->destination_folder = Folder::find($destinationFolderId);
            
            if(!$this->destination_folder) {
                PageLayout::postError(_('Zielordner nicht gefunden!'));
                return;
            }
            
            $errors = FileManager::copyFileRef($this->file_ref, $this->destination_folder, User::findCurrent());
            
            if(empty($errors)) {
                PageLayout::postSuccess(_('Die Datei wurde kopiert.'));
                } else {
                    PageLayout::postError(_('Fehler beim Kopieren der Datei.'), $errors);
            }
        }
    }
    
    
    /**
     * The action for moving a file reference.
     */
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
    
    
    /**
     * The action for deleting a file reference.
     */
    public function delete_action($file_ref_id)
    {
        $folder = null;
        
        if($file_ref_id) {
            $file_ref = FileRef::find($file_ref_id);
            if($file_ref) {
                $folder = $file_ref->folder;
                $file_ref->delete();
                return $this->redirectToFolder(
                    $folder,
                    MessageBox::success(_('Datei wurde gelöscht!'))
                );
            } else {
                //file not found
                PageLayout::postError(_('Datei nicht gefunden!'));
                return;
            }
        } else {
            //you can't delete things you don't know
            PageLayout::postError(_('Datei-ID nicht angegeben!'));
            return;
        }
    }
}
