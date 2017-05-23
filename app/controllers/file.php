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
 * This controller contains actions related to single files.
 */
class FileController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        $this->utf8decode_xhr = true;
        parent::before_filter($action, $args);
    }

    /**
     * This is a helper method that decides where a redirect shall be made
     * in case of error or success after an action was executed.
     */
    private function redirectToFolder($folder)
    {
        $params = [];
        switch ($folder->range_type) {
                case 'course':
                case 'institute':
                    $this->relocate($folder->range_type . '/files/index/' . $folder->getId(), ['cid' => $folder->range_id]);
                   break;
                case 'user':
                    $this->relocate('files/index/' . $folder->getId(), ['cid' => null]);
                    break;
            }
    }


    public function upload_action($folder_id)
    {
        $folder = FileManager::getTypedFolder($folder_id, Request::get("to_plugin"));
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$folder || !$folder->isWritable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        if (Request::isPost() && is_array($_FILES['file'])) {
            //CSRFProtection::verifyUnsafeRequest();
            $validatedFiles = FileManager::handleFileUpload(
                Request::isXhr() ? studip_utf8decode($_FILES['file']) : $_FILES['file'],
                $folder,
                $GLOBALS['user']->id
            );

            if (count($validatedFiles['error'])) {
                //error during upload: display error message:
               $this->render_json(['message' => MessageBox::error(
                   _('Beim Upload ist ein Fehler aufgetreten '),
                   array_map('htmlready', $validatedFiles['error'])
               )]);

                return;
            } else {
                //all files were uploaded successfully:
                foreach($validatedFiles['files'] as $file) {
                    if ($fileref = $folder->createFile($file)) {
                        $storedFiles[] = $fileref;
                    } else {
                        $this->render_json([
                            'message' => MessageBox::error(
                                _('Die hochgeladene Datei konnte nicht dem Ordner zugeordnet werden!')
                            )
                        ]);
                        return;
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
                    if (count($storedFiles) === 1 && $storedFiles[0]['mime_type'] === "application/zip") {
                        $ref_ids = array();
                        foreach ($storedFiles as $file_ref) {
                            $ref_ids[] = $file_ref->getId();
                        }
                        $output['redirect'] = URLHelper::getURL("dispatch.php/file/unzipquestion", array(
                            'file_refs' => $ref_ids
                        ));
                    } elseif (in_array($folder->range_type, array("course", "institute"))) {
                        $ref_ids = array();
                        foreach ($storedFiles as $file_ref) {
                            $ref_ids[] = $file_ref->getId();
                        }
                        $output['redirect'] = URLHelper::getURL("dispatch.php/file/edit_license", array(
                            'file_refs' => $ref_ids
                        ));
                    }

                    if ($storedFiles) {
                        foreach ($storedFiles as $fileref) {
                            $this->file_ref = $fileref;
                            $this->current_folder = $folder;
                            $this->marked_element_ids = array();
                            $output['new_html'][] = $this->render_template_as_string("files/_fileref_tr");
                        }
                    }
                    $this->render_json($output);
                }

            }
        }
        $this->folder_id = $folder_id;
    }

    
    public function unzipquestion_action()
    {
        $this->file_refs = FileRef::findMany(Request::getArray("file_refs"));
        $this->file_ref = $this->file_refs[0];
        $this->current_folder = $this->file_ref->folder->getTypedFolder();
        if (Request::isPost()) {
            if (Request::submitted("unzip")) {
                //unzip!


                $file_refs = FileArchiveManager::extractArchiveFileToFolder(
                    $this->file_ref,
                    $this->current_folder,
                    $GLOBALS['user']->id
                );

                $ref_ids = [];

                foreach($file_refs as $file_ref) {
                    $ref_ids[] = $file_ref->id;
                }

                //Delete the original zip file:
                $this->file_ref->delete();
            } else {
                $ref_ids = array($this->file_ref->getId());
            }

            header('Location: ' . URLHelper::getURL(
                'dispatch.php/file/edit_license',
                [
                    'file_refs' => $ref_ids
                ]
            ));
            $this->render_nothing();
        }
    }


    /**
     * Displays details about a file or a folder.
     *
     * @param string $file_area_object_id A file area object like a Folder or a FileRef.
     */
    public function details_action($file_area_object_id = null)
    {

        //check if the file area object is a FileRef:

        if ($this->file_ref = FileRef::find($file_area_object_id)) {
            //file system object is a FileRef
            PageLayout::setTitle($this->file_ref->name);

            //Check if file is downloadable for the current user:

            $this->show_preview = false;

            $this->is_downloadable = false;

            //NOTE: The following can only work properly for folders which are
            //stored in the database, since remote folders
            //(for example owncloud/nextcloud folders) are not stored in the database.
            $folder = $this->file_ref->folder->getTypedFolder();
            if (!$folder->isVisible(User::findCurrent()->id)) {
                throw new AccessDeniedException();
            }
            $this->is_downloadable = $folder->isFileDownloadable($this->file_ref->id, User::findCurrent()->id);
            $this->is_editable = $folder->isFileEditable($this->file_ref->id, User::findCurrent()->id);


            //load the previous and next file in the folder,
            //if the folder is of type FolderType.


            foreach ($folder->getFiles() as $folder_file_ref) {
                $last_file_ref_id = $current_file_ref_id;
                $current_file_ref_id = $folder_file_ref->id;

                if ($folder_file_ref->id == $this->file_ref->id) {
                    $this->previous_file_ref_id = $last_file_ref_id;
                }

                if ($last_file_ref_id == $this->file_ref->id) {
                    $this->next_file_ref_id = $folder_file_ref->id;
                    //at this point we have the ID of the previous
                    //and the next file ref so that we can exit
                    //the foreach loop:
                    break;
                }

            }


            $this->render_template('file/file_details');
        } else {
            //file area object is not a FileRef: maybe it's a folder:
            $this->folder = FileManager::getTypedFolder($file_area_object_id);
            if (!$this->folder || !$this->folder->isVisible($GLOBALS['user']->id)) {
                throw new AccessDeniedException();
            }
            //file system object is a Folder
            PageLayout::setTitle($this->folder->name);
            $this->render_template('file/folder_details');
        }
    }


    /**
     * The action for editing a file reference.
     */
    public function edit_action($file_ref_id)
    {
        $file_ref = FileRef::find($file_ref_id);
        $folder = FileManager::getTypedFolder($file_ref->folder_id);
        if (!$folder || !$folder->isFileEditable($file_ref->id, $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->content_terms_of_use_entries = ContentTermsOfUse::findAll();
        $this->file_ref = $file_ref;
        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();
            //form was sent
            $file_ref->name = trim(Request::get('name'));
            $file_ref->description = Request::get('description');
            $file_ref->content_terms_of_use_id = Request::get('content_terms_of_use_id');
            if ($file_ref->name) {
                if ($file_ref->store()) {
                    PageLayout::postSuccess(_('Änderungen gespeichert!'));
                } else {
                    PageLayout::postError(_('Fehler beim Speichern der Änderungen!'));
                }
                $this->redirectToFolder($folder);
            } else {
                PageLayout::postError(_('Bitte geben Sie einen Namen für die Datei ein!'));
            }
        }

    }


    /**
     * This action is responsible for updating a file reference.
     */
    public function update_action($file_ref_id)
    {
        $file_ref = FileRef::find($file_ref_id);
        $folder = FileManager::getTypedFolder($file_ref->folder_id);
        if (!$folder || !$folder->isFileEditable($file_ref->id, $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        
        $this->file_ref = $file_ref;
        $this->errors = [];
        
        if (Request::submitted('confirm')) {
            $update_filename = (bool) Request::get('update_filename', false);
            $update_all_instances = (bool) Request::get('update_all_instances', false);
            CSRFProtection::verifyUnsafeRequest();
            
            //Form was sent
            if (Request::isPost() && is_array($_FILES['file'])) {
                
                $result = FileManager::updateFileRef(
                    $this->file_ref,
                    User::findCurrent(),
                    $_FILES['file'],
                    $update_filename,
                    $update_all_instances
                );
                
                if (!$result instanceof FileRef) {
                    $this->errors = array_merge($this->errors, $result);
                }
                
            } else {
                $this->errors[] = _('Es wurde keine neue Dateiversion gewählt!');
            }
            
            if ($this->errors) {
                PageLayout::postError(
                    sprintf(
                        _('Fehler beim Aktualisieren der Datei %s!'),
                        $this->file_ref->name
                    ),
                    $this->errors
                );
            } else {
                PageLayout::postSuccess(
                    sprintf(
                        _('Datei %s wurde aktualisiert!'),
                        $this->file_ref->name
                    )
                );
            }
            $this->redirectToFolder($folder);
        }
    }
    
    
    /**
     * This action handles copying file references to another folder.
     */
    public function copy_action($file_ref_id)
    {
        $destination_folder_id = Request::get('dest_folder');

        if(!$file_ref_id) {
            PageLayout::postError(_('Datei-ID nicht gesetzt!'));
            return;
        }

        $this->file_ref = FileRef::find($file_ref_id);
        if(!$this->file_ref) {
            PageLayout::postError(_('Datei nicht gefunden!'));
            return;
        }

        if($destination_folder_id) {
            //form was sent
            $destination_folder = Folder::find($destination_folder_id);

            if(!$destination_folder) {
                PageLayout::postError(_('Zielordner nicht gefunden!'));
                return;
            }

            $this->destination_folder = $destination_folder->getTypedFolder();
            if(!$this->destination_folder) {
                PageLayout::postError(_('Ordnertyp des Zielordners konnte nicht ermittelt werden!'));
                return;
            }


            $result = FileManager::copyFileRef($this->file_ref, $this->destination_folder, User::findCurrent());

            if($result instanceof FileRef) {
                PageLayout::postSuccess(_('Die Datei wurde kopiert.'));
            } else {
                PageLayout::postError(_('Fehler beim Kopieren der Datei.'), $result);
            }

            $dest_range = $destination_folder->range_id;

            switch ($destination_folder->range_type) {
                case 'course':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$destination_folder_id . '?cid=' . $dest_range));
                case 'institute':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/'.$destination_folder_id . '?cid=' . $dest_range));
                case 'user':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/' . $destination_folder_id));
                default:
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $destination_folder_id));
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

        $this->copymode = Request::get("copymode", 'move');

        if (Request::submitted("do_move")) {

            $folder_id = Request::get('dest_folder');

            if ($file_ref_id && $folder_id) {

                $file_ref = FileRef::find($file_ref_id);
                $source_folder = Folder::find($file_ref->folder_id);
                $destination_folder = Folder::find($folder_id);

                if (!$destination_folder) {
                    PageLayout::postError(_('Zielordner nicht gefunden!'));
                    return;
                }

                $destination_folder = $destination_folder->getTypedFolder();
                if (!$destination_folder) {
                    PageLayout::postError(_('Ordnertyp des Zielordners konnte nicht ermittelt werden!'));
                    return;
                }

                if ($source_folder && $destination_folder) {
                    $errors = [];

                    if ($this->copymode == 'move') {
                        $result = FileManager::moveFileRef($file_ref, $destination_folder, $user);

                        if($result instanceof FileRef) {
                            PageLayout::postSuccess(_('Die Datei wurde verschoben.'));
                        } else {
                            PageLayout::postError(_('Fehler beim Verschieben der Datei.'), $errors);
                        }
                    } else {
                        $result = FileManager::copyFileRef($file_ref, $destination_folder, $user);

                        if($result instanceof FileRef) {
                            PageLayout::postSuccess(_('Die Datei wurde kopiert.'));
                        } else {
                            PageLayout::postError(_('Fehler beim Kopieren der Datei.'), $result);
                        }
                    }


                    $dest_range = $destination_folder->range_id;

                    switch ($destination_folder->range_type) {
                        case 'course':
                            return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$folder_id. '?cid=' . $dest_range));
                        case 'institute':
                            return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/'.$folder_id. '?cid=' . $dest_range));
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
            } else {
                $inst_sql =  "SELECT DISTINCT Institute.Institut_id, Institute.Name " .
                    "FROM Institute " .
                    "LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) " .
                    "LEFT JOIN user_inst ON (user_inst.Institut_id = Institute.Institut_id)" .
                    "WHERE user_inst.user_id = '" . $user->id . "' " .
                    "AND Institute.Name LIKE :input " .
                    "OR Institute.Strasse LIKE :input " .
                    "OR Institute.email LIKE :input " .
                    "OR range_tree.name LIKE :input " .
                    "ORDER BY Institute.Name";


                $parameters = array(
                    'userid' => $user->id,
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

            $this->user_id = $user->id;
            $this->file_ref = $file_ref_id;
        }
    }


    public function choose_destination_action($fileref_id, $copymode = null)
    {
        if ($copymode) {
            $this->copymode = $copymode;
        }
        $this->fileref_id = $fileref_id;

        $refs = explode('-', $fileref_id);
        $first_ref = FileRef::find($refs[0]);
        if ($first_ref) {
            $this->parent_folder = Folder::find($first_ref->folder_id);
        } else {
            $folder = Folder::find($refs[0]);
            if ($folder) {
                $this->parent_folder = Folder::find($folder->parent_id);
            }
        }

        $this->plugin = Request::get("to_plugin");
    }


    public function download_folder_action($folder_id)
    {
        
        $user = User::findCurrent();
        
        $folder = Folder::find($folder_id);
        
        if ($folder) {
            $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');
            
            $folder = $folder->getTypedFolder();
            
            $result = FileArchiveManager::createArchive(
                [$folder],
                $user->id,
                $tmp_file
            );
            
            if ($result) {
                //ZIP file was created successfully
                $this->redirect(
                    FileManager::getDownloadURLForTemporaryFile(
                        basename($tmp_file),
                        basename($tmp_file) . '.zip'
                    )
                );
            } else {
                throw new Exception('Error while creating ZIP archive!');
            }
        } else {
            throw new Exception('Folder not found in database!');
        }
    }
    
    
    
    
    
    
    public function choose_folder_from_course_action()
    {
        if (Request::get("course_id")) {
            $folder = Folder::findTopFolder(Request::get("course_id"));

            header("Location: ". URLHelper::getURL("dispatch.php/file/choose_folder/".$folder->getId(), array(
                    'to_plugin' => Request::get("to_plugin"),
                    'fileref_id' => Request::get("fileref_id"),
                    'copymode' => Request::get("copymode"),
                    'isfolder' => Request::get("isfolder")
            )));
        }


        $this->plugin = Request::get("to_plugin");
        if (!$GLOBALS['perm']->have_perm("admin")) {
            $statement = DBManager::get()->prepare("
                SELECT seminare.*
                FROM seminare
                    INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                WHERE seminar_user.user_id = :user_id
                ORDER BY seminare.duration_time = -1, seminare.start_time DESC, seminare.name ASC
            ");
            $statement->execute(array('user_id' => $GLOBALS['user']->id));
            $this->courses = array();
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $coursedata) {
                $this->courses[] = Course::buildExisting($coursedata);
            }
        }
    }

    public function choose_folder_from_institute_action()
    {
        if (Request::get("Institut_id")) {
            $folder = Folder::findTopFolder(Request::get("Institut_id"));
            header("Location: ". URLHelper::getURL("dispatch.php/file/choose_folder/".$folder->getId(), array(
                    'to_plugin' => Request::get("to_plugin"),
                    'fileref_id' => Request::get("fileref_id"),
                    'copymode' => Request::get("copymode"),
                    'isfolder' => Request::get("isfolder")
            )));
        }

        if ($GLOBALS['perm']->have_perm('root')) {
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
        } else {
            $inst_sql =  "SELECT DISTINCT Institute.Institut_id, Institute.Name " .
                    "FROM Institute " .
                    "LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) " .
                    "LEFT JOIN user_inst ON (user_inst.Institut_id = Institute.Institut_id)" .
                    "WHERE user_inst.user_id = '" . $user->id . "' " .
                    "AND Institute.Name LIKE :input " .
                    "OR Institute.Strasse LIKE :input " .
                    "OR Institute.email LIKE :input " .
                    "OR range_tree.name LIKE :input " .
                    "ORDER BY Institute.Name";
            $parameters = array(
                    'userid' => $user->id,
                    'semtypes' => studygroup_sem_types() ?: array(),
                    'exclude' => array()
            );
        }
        $this->instsearch = SQLSearch::get($inst_sql, _("Einrichtung suchen"), 'Institut_id');

        $this->plugin = Request::get("to_plugin");
        /*if (!$GLOBALS['perm']->have_perm("admin")) {
            $statement = DBManager::get()->prepare("
                SELECT seminare.*
                FROM seminare
                    INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                WHERE seminar_user.user_id = :user_id
                ORDER BY seminare.duration_time = -1, seminare.start_time DESC, seminare.name ASC
            ");
            $statement->execute(array('user_id' => $GLOBALS['user']->id));
            $this->courses = array();
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $coursedata) {
                $this->courses[] = Course::buildExisting($coursedata);
            }
        }*/

    }

    public function choose_folder_action($folder_id = null)
    {
        /*
        if (Request::get("to_plugin")) {
            $to_plugin = PluginManager::getInstance()->getPlugin(Request::get("to_plugin"));
            $this->to_folder_type = $to_plugin->getFolder(Request::get("to_folder_id"));
        } else {
            $folder = new Folder(Request::option("to_folder_id"));
            $this->to_folder_type = new StandardFolder($folder);
        }*/

        if (Request::isPost()) {
            //copy

            if (Request::get("plugin")) {
                $plugin = PluginManager::getInstance()->getPlugin(Request::get("plugin"));
                //$file = $plugin->getPreparedFile(Request::get("file_id"));
            } else {
                $folder = new Folder($folder_id);
                $this->to_folder_type = new StandardFolder($folder);
            }
        }
        if (Request::get("plugin")) {
            $this->filesystemplugin = PluginManager::getInstance()->getPlugin(Request::get("plugin"));
            if (Request::get("search") && $this->filesystemplugin->hasSearch()) {
                $this->top_folder = $this->filesystemplugin->search(Request::get("search"), Request::getArray("parameter"));
            } else {
                $this->top_folder = $this->filesystemplugin->getFolder($folder_id, true);
                if (is_a($this->top_folder, "Flexi_Template")) {
                    $this->top_folder->set_attribute("select", true);
                    $this->top_folder->set_attribute("to_folder", $this->to_folder);
                    $this->render_text($this->top_folder);
                }
            }
        } else {
            $this->top_folder = new StandardFolder(new Folder($folder_id));
            if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
                throw new AccessException();
            }
        }
    }

    public function getFolders_action()
    {
        $rangeId = Request::get("range");
        $folders = Folder::findBySQL("range_id=?", array($rangeId));
        $folderray = array();
        $pathes = array();
        foreach ($folders as $folder) {
            $pathes[] = $folder->getPath();
            $folderray[][$folder->getPath()] = $folder->id;
        }
        array_multisort($pathes, SORT_ASC, SORT_STRING, $folderray);

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
        CSRFProtection::verifyUnsafeRequest();
        $file_ref = FileRef::find($file_ref_id);
        if ($file_ref) {
            $folder = $file_ref->folder->getTypedFolder();
            if (!$folder || !$folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)) {
                throw new AccessDeniedException();
            }
            if ($folder->deleteFile($file_ref->id)) {
                PageLayout::postSuccess(_('Datei wurde gelöscht.'));
            } else {
                PageLayout::postError(_('Datei konnte nicht gelöscht werden.'));
            }
            $this->redirectToFolder($folder);
        } else {
            throw new Trails_Exception(404, _('Datei nicht gefunden.'));
        }
    }

    public function upload_window_action()
    {
    }

    public function add_files_window_action($folder_id)
    {
        $this->folder_id = $folder_id;
        $this->plugin = Request::get("to_plugin");
    }

    public function choose_file_from_course_action($folder_id)
    {
        if (Request::get("course_id")) {
            $folder = Folder::findTopFolder(Request::get("course_id"));
            header("Location: ". URLHelper::getURL("dispatch.php/file/choose_file/".$folder->getId(), array(
                    'to_plugin' => Request::get("to_plugin"),
                    'to_folder_id' => $folder_id
                )));
        }
        $this->folder_id = $folder_id;
        $this->plugin = Request::get("to_plugin");
        if (!$GLOBALS['perm']->have_perm("admin")) {
            $statement = DBManager::get()->prepare("
                SELECT seminare.*
                FROM seminare
                    INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                WHERE seminar_user.user_id = :user_id
                ORDER BY seminare.duration_time = -1, seminare.start_time DESC, seminare.name ASC
            ");
            $statement->execute(array('user_id' => $GLOBALS['user']->id));
            $this->courses = array();
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $coursedata) {
                $this->courses[] = Course::buildExisting($coursedata);
            }
        }
    }

    public function choose_file_action($folder_id = null)
    {
        if (!Request::get("to_folder_id")) {
            throw new Exception("target folder_id must be set.");
        }
        if (Request::get("to_plugin")) {
            $to_plugin = PluginManager::getInstance()->getPlugin(Request::get("to_plugin"));
            $this->to_folder_type = $to_plugin->getFolder(Request::get("to_folder_id"));
        } else {
            $folder = new Folder(Request::option("to_folder_id"));
            $this->to_folder_type = new StandardFolder($folder);
        }

        if (Request::isPost()) {
            //copy
            if (Request::get("plugin")) {
                $plugin = PluginManager::getInstance()->getPlugin(Request::get("plugin"));
                $filedata = $file = $plugin->getPreparedFile(Request::get("file_id"));
                if (!$file['tmp_path'] && $file['url']) {
                    $fileobject = new File();
                    $fileobject->url = $file['url'];
                    $fileobject->url_access_type = $file['url_access_type'] ?: "redirect";
                    $fileobject->name = $file['name'];
                    $meta = FileManager::fetchURLMetadata($file['url']);
                    if ($meta['response_code'] === 200) {
                        if (!$fileobject->name) {
                            $fileobject->name = $meta['filename'] ?: 'unknown';
                        }
                        $fileobject->mime_type = strstr($meta['Content-Type'], ';', true);
                        $fileobject->size = $meta['Content-Length'];
                    }
                    $file = $fileobject;
                }
            } else {
                $file = FileRef::find(Request::get("file_id"))->file;
            }

            $error = $this->to_folder_type->validateUpload($file, $GLOBALS['user']->id);
            if (!$error) {
                //do the copy
                $file_ref = $this->to_folder_type->createFile($file);
                if ($filedata['content_terms_of_use_id']) {
                    $file_ref['content_terms_of_use_id'] = $filedata['content_terms_of_use_id'];
                }
                if (in_array($this->to_folder_type->range_type, array("course", "institute"))) {
                    header("Location: ". URLHelper::getURL("dispatch.php/file/edit_license", array(
                        'file_refs' => array($file_ref->getId())
                    )));
                    $this->render_nothing();
                } else {
                    if (Request::isAjax()) {
                        $this->file_ref = $file_ref;
                        $this->current_folder = $this->to_folder_type;
                        $this->marked_element_ids = array();
                        $plugins = PluginManager::getInstance()->getPlugins("FileUploadHook");
                        $redirects = array();
                        foreach ($plugins as $plugin) {
                            $url = $plugin->getAdditionalUploadWizardPage($file_ref);
                            if ($url) {
                                $redirects = $url;
                            }
                        }
                        $payload = array(
                            'html' => $this->render_template_as_string("files/_fileref_tr"),
                            'redirect' => $redirects[0]
                        );

                        $payload = array("func" => "STUDIP.Files.addFile", 'payload' => $payload);
                        $this->response->add_header("X-Dialog-Execute", json_encode(studip_utf8encode($payload)));
                        $this->render_nothing();
                    } else {
                        PageLayout::postMessage(MessageBox::success(_("Datei wurde hinzugefügt.")));
                        $this->redirect(($this->to_folder_type->range_type === "course" ? "course/" : "")."files/index/".$folder_id);
                    }
                }
            } else {
                PageLayout::postMessage(MessageBox::error(_("Konnte die Datei nicht hinzufügen."), array($error)));
            }
        }
        if (Request::get("plugin")) {
            $this->filesystemplugin = PluginManager::getInstance()->getPlugin(Request::get("plugin"));
            PageLayout::setTitle(_("Datei hinzufügen von")." ".$this->filesystemplugin->getPluginName());
            if (Request::get("search") && $this->filesystemplugin->hasSearch()) {
                $this->top_folder = $this->filesystemplugin->search(Request::get("search"), Request::getArray("parameter"));
            } else {
                $this->top_folder = $this->filesystemplugin->getFolder($folder_id, true);
                if (is_a($this->top_folder, "Flexi_Template")) {
                    $this->top_folder->set_attribute("select", true);
                    $this->top_folder->set_attribute("to_folder", $this->to_folder);
                    $this->render_text($this->top_folder);
                }
            }
        } else {
            $this->top_folder = new StandardFolder(new Folder($folder_id));
            if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
                throw new AccessException();
            }
        }
    }

    public function edit_license_action()
    {
        $this->file_refs = FileRef::findMany(Request::getArray("file_refs"));
        if (Request::isPost()) {
            foreach ($this->file_refs as $file_ref) {
                $file_ref['content_terms_of_use_id'] = Request::option("content_terms_of_use_id");
                $file_ref->store();
            }
            if (Request::isAjax()) {
                $payload = array();

                foreach ($this->file_refs as $file_ref) {
                    $this->file_ref = $file_ref;
                    $this->current_folder = $file_ref->folder->getTypedFolder();
                    $this->marked_element_ids = array();
                    $payload['html'][] = $this->render_template_as_string("files/_fileref_tr");
                }


                $plugins = PluginManager::getInstance()->getPlugins("FileUploadHook");
                $redirect = null;
                foreach ($plugins as $plugin) {
                    $url = $plugin->getAdditionalUploadWizardPage($file_ref);
                    if ($url) {
                        $redirect = $url;
                        break;
                    }
                }

                if (count($redirect)) {
                    $payload['redirect'] = $redirect;
                    $this->redirect($redirect);
                    return;
                }

                $payload = array("func" => "STUDIP.Files.addFile", 'payload' => $payload);
                $this->response->add_header("X-Dialog-Execute", json_encode(studip_utf8encode($payload)));
            } else {
                PageLayout::postMessage(MessageBox::success(_("Datei wurde bearbeitet.")));
                //redirect:
            }
        }
        $this->licenses = ContentTermsOfUse::findBySQL("TRUE ORDER BY position ASC, id ASC");
    }

    public function add_url_action($folder_id)
    {
        $this->top_folder = FileManager::getTypedFolder($folder_id, Request::get("to_plugin"));
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$this->top_folder || !$this->top_folder->isWritable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $url = trim(Request::get('url'));
            $url_parts = parse_url($url);
            if (filter_var($url, FILTER_VALIDATE_URL) !== false && in_array($url_parts['scheme'], ['http', 'https','ftp'])) {
                if (Request::get('access_type') == 'redirect') {
                    if (in_array($url_parts['scheme'], ['http', 'https'])) {
                        $file = new File();
                        $file->url = $url;
                        $file->url_access_type = 'redirect';
                        $file->name = Request::get('name');
                        if (!$file->name) {
                            $meta = FileManager::fetchURLMetadata($url);
                            if ($meta['response_code'] === 200) {
                                $file->name = $meta['filename'] ?: 'unknown';
                                $file->mime_type = strstr($meta['Content-Type'], ';', true);
                            }
                        } else {
                            $file->mime_type = get_mime_type($file->name);
                        }
                    } else {
                        PageLayout::postError(_("Die angegebene URL muss mit http(s) beginnen."));
                    }
                }
                if (Request::get('access_type') == 'proxy') {
                    $meta = FileManager::fetchURLMetadata($url);
                    if ($meta['response_code'] === 200) {
                        $file = new File();
                        $file->url = $url;
                        $file->url_access_type = 'proxy';
                        $file->name = Request::get('name');
                        if (!$file->name) {
                            $file->name = $meta['filename'] ?: 'unknown';
                        }
                        $file->mime_type = $meta['Content-Type'] ? strstr($meta['Content-Type'], ';', true) : get_mime_type($file->name);
                        $file->size = $meta['Content-Length'];
                    } else {
                        PageLayout::postError(_("Die angegebene URL kann nicht abgerufen werden."), [_('Fehlercode') . ':' . $meta['response_code']]);
                    }
                }
                if ($file) {
                    $file['user_id'] = $GLOBALS['user']->id;

                    $this->file_ref = $this->top_folder->createFile($file);
                    $payload = array();

                    $this->current_folder = $this->top_folder;
                    $this->marked_element_ids = array();
                    $payload['html'][] = $this->render_template_as_string("files/_fileref_tr");

                    $plugins = PluginManager::getInstance()->getPlugins("FileUploadHook");
                    $redirects = array();
                    foreach ($plugins as $plugin) {
                        $url = $plugin->getAdditionalUploadWizardPage($this->file_ref);
                        if ($url) {
                            $redirects = $url;
                        }
                    }
                    if (count($redirects)) {
                        $payload['html'] = $redirects[0];
                    }

                    $payload = array("func" => "STUDIP.Files.addFile", 'payload' => $payload);
                    $this->response->add_header("X-Dialog-Execute", json_encode(studip_utf8encode($payload)));
                    $this->render_nothing();
                }
            } else {
                PageLayout::postError(_("Die angegebene URL ist ungültig."));
            }
        }

    }

    /**
     * Action for creating a new folder.
     */
    public function new_folder_action($folder_id)
    {

        $parent_folder = FileManager::getTypedFolder($folder_id, Request::get("to_plugin"));
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$parent_folder || !$parent_folder->isWritable($GLOBALS['user']->id)|| !$parent_folder->isSubfolderAllowed($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->parent_folder_id = $parent_folder->getId();

        $folder_types = FileManager::getFolderTypes($parent_folder->range_type);

        $this->current_folder_type = Request::get('folder_type', 'StandardFolder');
        $this->name = Request::get('name');
        $this->description = Request::get('description');
        if (!is_subclass_of($this->current_folder_type, 'FolderType') || !class_exists($this->current_folder_type)) {
            throw new InvalidArgumentException(_('Unbekannter Ordnertyp!'));
        }

        $new_folder = new $this->current_folder_type;
        $this->folder_template = $new_folder->getEditTemplate();

        $this->folder_types = [];

        foreach($folder_types as $folder_type) {
            $folder_type_instance = new $folder_type(new Folder());
            $this->folder_types[] = [
                'class' => $folder_type,
                'name' => $folder_type::getTypeName(),
                'icon' => $folder_type_instance->getIcon('clickable')
            ];
        }

        if (Request::submitted('create')) {
            CSRFProtection::verifyUnsafeRequest();
            $ok = $new_folder->setDataFromEditTemplate(Request::getInstance());
            if ($ok instanceof FolderType) {
                $new_folder->user_id = User::findCurrent()->id;
                if ($parent_folder->createSubfolder($new_folder)) {
                    PageLayout::postSuccess(_('Der Ordner wurde angelegt.'));
                    $this->response->add_header('X-Dialog-Close', '1');
                    $this->render_nothing();
                    return;
                }
            } else {
                PageLayout::postMessage($ok);
            }
        }
    }


    /**
     * Action for editing an existing folder, referenced by its ID.
     *
     * @param $folder_id string The ID of the folder that shall be edited.
     */
    public function edit_folder_action($folder_id)
    {
        $folder = FileManager::getTypedFolder($folder_id, Request::get("to_plugin"));
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$folder || !$folder->isEditable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        $parent_folder = $folder->getParent();
        $folder_types = FileManager::getFolderTypes($parent_folder->range_type);

        $this->current_folder_type = Request::get('folder_type', get_class($folder));
        $this->name = Request::get('name', $folder->name);
        $this->description = Request::get('description', $folder->description);
        if (!is_subclass_of($this->current_folder_type, 'FolderType') || !class_exists($this->current_folder_type)) {
            throw new InvalidArgumentException(_('Unbekannter Ordnertyp!'));
        }
        if ($this->current_folder_type != get_class($folder)) {
            $folder = new $this->current_folder_type($folder);
        }
        $this->folder = $folder;
        $this->folder_template = $folder->getEditTemplate();

        $this->folder_types = [];

        foreach ($folder_types as $folder_type) {
            $folder_type_instance = new $folder_type(new Folder());
            $this->folder_types[] = [
                'class' => $folder_type,
                'name'  => $folder_type::getTypeName(),
                'icon'  => $folder_type_instance->getIcon('clickable')
            ];
        }


        if (Request::submitted('edit')) {
            CSRFProtection::verifyUnsafeRequest();
            $ok = $folder->setDataFromEditTemplate(Request::getInstance());
            if ($ok instanceof FolderType) {
                if ($folder->store()) {
                    PageLayout::postSuccess(_('Der Ordner wurde bearbeitet.'));
                    $this->response->add_header('X-Dialog-Close', '1');
                    $this->render_nothing();
                    return;
                }
            } else {
                PageLayout::postMessage($ok);
            }
        }
    }

    public function delete_folder_action($folder_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $folder = FileManager::getTypedFolder($folder_id, Request::get("to_plugin"));
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$folder || !$folder->isEditable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $parent_folder = $folder->getParent();

        if ($folder->delete()) {
            PageLayout::postSuccess(_('Ordner wurde gelöscht!'));
        } else {
            PageLayout::postError(_('Ordner konnte nicht gelöscht werden!'));
        }
        $this->redirectToFolder($parent_folder);
    }

    /**
     * This action allows downloading, copying, moving and deleting files and folders in bulk.
     */
    public function bulk_action($folder_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $parent_folder = FileManager::getTypedFolder($folder_id, Request::get("to_plugin"));
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$parent_folder || !$parent_folder->isReadable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        //check, if at least one ID was given:
        $ids = Request::getArray('ids');

        if (empty($ids)) {
            $this->redirectToFolder($parent_folder);
            return;
        }

        //check, which action was chosen:

        if (Request::submitted('download')) {
            //bulk downloading:

            $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');

            $user = User::findCurrent();

            //collect file area objects by looking at their IDs:

            $file_area_objects = [];
            foreach ($ids as $id) {
                //check if the ID references a FileRef:
                $filesystem_item = FileRef::find($id);
                if(!$filesystem_item) {
                    //check if the ID references a Folder:
                    $filesystem_item = Folder::find($id);
                    if($filesystem_item) {
                        $file_area_objects[] = $filesystem_item->getTypedFolder();
                    }
                } else {
                    $file_area_objects[] = $filesystem_item;
                }
            }

            //create a ZIP archive:
            $result = FileArchiveManager::createArchive(
                $file_area_objects,
                $user->id,
                $tmp_file
            );

            if($result) {
                //ZIP file was created successfully
                $this->redirect(
                    FileManager::getDownloadURLForTemporaryFile(
                        basename($tmp_file),
                        basename($tmp_file) . '.zip'
                    )
                );
            } else {
                throw new Exception('Error while creating ZIP archive!');
            }

        } elseif(Request::submitted('copy')) {
            //bulk copying
            $selected_elements = Request::getArray('ids');
            $this->redirect('file/choose_destination/' . implode('-', $selected_elements) . '/copy');
            return;

        } elseif(Request::submitted('move')) {
            //bulk moving
            $selected_elements = Request::getArray('ids');
            $this->redirect('file/choose_destination/' . implode('-', $selected_elements) . '/move');
            return;

        } elseif(Request::submitted('delete')) {
            //bulk deleting
            $errors = array();
            $count_files = 0;
            $count_folders = 0;

            $user = User::findCurrent();
            $selected_elements = Request::getArray('ids');
            foreach ($selected_elements as $element) {
                if ($file_ref = FileRef::find($element)) {
                    $parent_folder_id = $file_ref->folder_id;
                    $result = FileManager::deleteFileRef($file_ref, $user);
                    if (!is_array($result)) $count_files++;
                } elseif ($folder = Folder::find($element)) {
                    $parent_folder_id = $folder->parent_id;
                    $foldertype = $folder->getTypedFolder();
                    $folder_files = count($foldertype->getFiles());
                    $folder_subfolders = count($foldertype->getSubfolders());
                    $result = FileManager::deleteFolder($foldertype, $user);
                    if (!is_array($result)) {
                        $count_folders++;
                        $count_files += $folder_files;
                        $count_folders += $folder_subfolders;
                    }
                }
                if (is_array($result)) {
                    $errors = array_merge($errors, $result);
                }
            }

            if (empty($errors) || $count_files > 0 || $count_folders > 0) {

                if (count($filerefs) == 1) {
                    if ($source_folder) {
                        PageLayout::postSuccess(_('Der Ordner wurde gelöscht!'));
                    } else {
                        PageLayout::postSuccess(_('Die Datei wurde gelöscht!'));
                    }
                } else {
                    if ($count_files > 0 && $count_folders > 0) {
                        PageLayout::postSuccess(sprintf(_('Es wurden %s Ordner und %s Dateien gelöscht!'), $count_folders, $count_files));
                    } elseif ($count_files > 0) {
                        PageLayout::postSuccess(sprintf(_('Es wurden  %s Dateien gelöscht!'), $count_files));
                    } else {
                        PageLayout::postSuccess(sprintf(_('Es wurden %s Ordner gelöscht!'), $count_folders));
                    }
                }

            } else {
                PageLayout::postError(_('Es ist ein Fehler aufgetreten!'), $errors);
            }


            $this->redirectToFolder($parent_folder);
        }
    }

    public function open_folder_action($folder_id)
    {
        $folder = FileManager::getTypedFolder($folder_id, Request::get("to_plugin"));
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$folder || !$folder->isVisible($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        $this->redirectToFolder($folder);
    }
}
