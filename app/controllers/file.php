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
    private function redirectToFolder($folder, $message = null)
    {
        if ($message instanceof MessageBox) {
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
                    echo "t1";
                    return $this->redirect(URLHelper::getUrl('dispatch.php/' . $folder->range_type . '/files/index/' . $folder->id . '?cid=' . $dest_range));
                case 'user':
                    echo "t2";
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/' . $folder->id));
                    die();
            }
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
                $tmp_folder = $GLOBALS['TMP_PATH']."/".md5(uniqid());
                mkdir($tmp_folder);
                extract_zip($this->file_ref->file->getPath(), $tmp_folder);
                var_dump($this->current_folder);
                $ref_ids = $this->recursivleyReferenceFiles(
                    $tmp_folder,
                    $this->current_folder,
                    $this->current_folder->isSubfolderAllowed($GLOBALS['user']->id)
                );
                $ref_ids = array_map(function ($fileref) { return $fileref->getId(); }, $ref_ids);
                rmdirr($tmp_folder);
                $this->file_ref->delete();
            } else {
                $ref_ids = array($this->file_ref->getId());
            }
            if (in_array($this->current_folder->range_type, array("course", "institute"))) {
                header("Location: ". URLHelper::getURL("dispatch.php/file/edit_license", array(
                    'file_refs' => $ref_ids
                )));
                $this->render_nothing();
            } else {
                $payload = array(
                    'html' => $this->render_template_as_string("files/_fileref_tr")
                );
                $payload = array("func" => "STUDIP.Files.addFile", 'payload' => $payload);
                $this->response->add_header("X-Dialog-Execute", json_encode(studip_utf8encode($payload)));
                $this->render_nothing();
            }
        }
    }

    private function recursivleyReferenceFiles($folder_path, $foldertype, $createfolder = true)
    {
        $filerefs = array();
        foreach (scandir($folder_path) as $file) {
            if ($file !== "." && $file !== "..") {
                if (is_dir($folder_path . "/" . $file)) {
                    //create folder
                    if ($createfolder) {
                        $subfolder = $foldertype->createSubfolder(array(
                            'name' => $file,
                            'folder_type' => "StandardFolder",
                            'user_id' => $GLOBALS['user']->id
                        ));
                    } else {
                        $subfolder = $foldertype;
                    }
                    $reflist = $this->recursivleyReferenceFiles(
                        $folder_path . "/" . $file,
                        $subfolder,
                        $createfolder
                    );
                    $filerefs = array_merge($filerefs, $reflist);
                } else {
                    $fileref = $foldertype->createFile(array(
                        'name' => $file,
                        'type' => get_mime_type($file),
                        'size' => filesize($folder_path . "/" . $file),
                        'tmp_path' => $folder_path . "/" . $file
                    ));
                    $filerefs[] = $fileref;
                }
            }
        }
        return $filerefs;
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
            $this->file_ref = $file_ref;
        } else {
            if(Request::isDialog()) {
                $this->render_text(
                    MessageBox::error(_('Die zu bearbeitende Datei wurde nicht gefunden!'))
                );
            } else {
                PageLayout::postError(_('Die zu bearbeitende Datei wurde nicht gefunden!'));
            }
        }

        $this->content_terms_of_use_entries = ContentTermsOfUse::findBySql('TRUE ORDER BY position ASC, id ASC');

        if (Request::submitted('save')) {
            //form was sent
            $this->name = Request::get('name');
            $this->description = Request::get('description');
            $this->content_terms_of_use_id = Request::get('content_terms_of_use_id');

            $result = FileManager::editFileRef($file_ref, User::findCurrent(), $this->name, $this->description, $this->content_terms_of_use_id);
            if ($result instanceof FileRef) {
                $this->redirectToFolder(
                    $file_ref->folder,
                    MessageBox::success(_('Änderungen gespeichert!'))
                );
            } else {
                $this->redirectToFolder(
                    $file_ref->folder,
                    MessageBox::error(_('Fehler beim Speichern der Änderungen!'), $result)
                );
            }
        } else {
            //load default data:
            $this->name = $file_ref->name;
            $this->description = $file_ref->description;
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

            if($file_ref_id && $folder_id) {

                $file_ref = FileRef::find($file_ref_id);
                $source_folder = Folder::find($file_ref->folder_id);
                $destination_folder = Folder::find($folder_id);

                if(!$destination_folder) {
                PageLayout::postError(_('Zielordner nicht gefunden!'));
                return;
                }

                $destination_folder = $destination_folder->getTypedFolder();
                if(!$destination_folder) {
                    PageLayout::postError(_('Ordnertyp des Zielordners konnte nicht ermittelt werden!'));
                    return;
                }

                if($source_folder && $destination_folder) {

                    $errors = [];

                    if($this->copymode == 'move') {
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
            $this->parent_folder = $first_ref->folder_id;
        } else {
            $folder = Folder::find($refs[0]);
            if ($folder) {
                $this->parent_folder = $folder->parent_id;
            }
        
        }

        $this->plugin = Request::get("to_plugin");
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
        $folder = null;

        if($file_ref_id) {
            $file_ref = FileRef::find($file_ref_id);
            if($file_ref) {
                $folder = $file_ref->folder;
                $file_ref->delete();
                if (Request::isAjax() && !Request::isDialog()) {
                    $this->render_nothing();
                } else {
                    return $this->redirectToFolder(
                        $folder,
                        MessageBox::success(_('Datei wurde gelöscht!'))
                    );
                }
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
                        $plugins = PlugineManager::get()->getPlugins("FileUploadHook");
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
                PageLayout::postMessage(MessageBox::error(_("Konnte die Datei nicht hinzufügen.", array($error))));
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
                $file_ref['content_terms_of_use_id'] = Request::option("license_id");
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

                    $plugins = PlugineManager::get()->getPlugins("FileUploadHook");
                    $redirects = array();
                    foreach ($plugins as $plugin) {
                        $url = $plugin->getAdditionalUploadWizardPage($this->file_ref);
                        if ($url) {
                            $redirects = $url;
                        }
                    }
                    $payload['html'] = $redirects[0];

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
                    $this->response->add_header('X-Dialog-Close');
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
     * @param string folder_id The ID of the folder that shall be edited.
     */
    public function edit_folder_action($folder_id)
    {
        //we need the folder-ID of the folder that is to be edited:
        if(!$folder_id) {
            $this->render_text(
                MessageBox::error(_('Ordner-ID nicht gefunden!'))
            );
            return;
        }

        $this->folder = Folder::find($folder_id);
        if(!$this->folder) {
            $this->render_text(
                MessageBox::error(_('Ordner nicht gefunden!'))
            );
            return;
        }

        $this->folder = $this->folder->getTypedFolder();
        if(!$this->folder) {
            $this->render_text(
                MessageBox::error(_('Ordnertyp des Ordners konnte nicht ermittelt werden!'))
            );
            return;
        }


        $this->folder_id = $this->folder->getId();
        $this->parent_folder_id = $this->folder->parent_id;

        $current_user = User::findCurrent();

        //permission check: is the current allowed to edit the folder?

        if($this->folder->isWritable($current_user->id)) {

            if(Request::submitted('edit')) {
                //update edited fields
                $this->name = Request::get('name');
                $this->description = Request::get('description');

                $result = FileManager::editFolder($this->folder, $current_user, $this->name, $this->description);

                if($result instanceof FolderType) {
                    return $this->redirectToFolder($this->folder, MessageBox::success(_('Ordner wurde bearbeitet!')));
                } else {
                    return $this->redirectToFolder($this->folder, MessageBox::error(_('Fehler beim Bearbeiten des Ordners!'), $result));
                }
            } else {
                //show current field values:

                $this->name = $this->folder->name;
                $this->description = $this->folder->description;
            }
        } else {
            //current user isn't permitted to change this folder:
            $error_message = MessageBox::error(_('Sie sind nicht dazu berechtigt, diesen Ordner zu bearbeiten!'));

            return $this->redirectToFolder($this->folder, $error_message);
        }

        if(Request::isDialog()) {
            $this->render_template('file/edit_folder.php');
        } else {
            $this->render_template('file/edit_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
    }
}
