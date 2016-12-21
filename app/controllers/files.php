<?php
/**
 * files.php - controller to display personal files of a user
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
 * The FilesController controller provides actions for the personal file area.
 */
class FilesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        $this->utf8decode_xhr = true;
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Meine Dateien'));
        PageLayout::setHelpKeyword('Basis.Dateien');
        PageLayout::addSqueezePackage('tablesorterfork');

        $this->user = User::findCurrent();
        $this->last_visitdate = time();
    }


    /**
     * Helper method for filling the sidebar with actions.
     */
    private function buildSidebar(FolderType $folder)
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $actions = new ActionsWidget();

        $config_urls = array();
        foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) {
            $url = $plugin->filesystemConfigurationURL();
            $navigation = $plugin->getFileSelectNavigation();
            if ($url) {
                $config_urls[] = array(
                    'name' => $navigation->getTitle(),
                    'icon' => $navigation->getImage(),
                    'url' => $url
                );
            }
        }
        if (count($config_urls)) {
            if (count($config_urls) > 1) {
                $actions->addLink(
                    _("Dateibereiche konfigurieren"),
                    URLHelper::getUrl('dispatch.php/files/configure'),
                    Icon::create("admin", "clickable"),
                    array('data-dialog' => 1)
                );
            } else {
                $actions->addLink(
                    sprintf(_("%s konfigurieren"), $config_urls[0]['name']),
                    $config_urls[0]['url'],
                    $config_urls[0]['icon'],
                    array('data-dialog' => 1)
                );
            }
        }

        if ($folder->isSubfolderAllowed($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl('dispatch.php/file/new_folder/' . $folder->getId()),
                Icon::create('folder-empty+add', 'clickable'), ['data-dialog' => 1]
            );
        }

        if ($folder->isWritable($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Datei hinzufügen'),
                "#",
                Icon::create('file+add', 'clickable'),
                array('onClick' => "STUDIP.Files.openAddFilesWindow(); return false;")
            );
        }

        $sidebar->addWidget($actions);
    }



    /**
     * Displays the files in tree view.
     */
    public function index_action($topFolderId = '')
    {
        Navigation::activateItem('/profile/files/tree');

        $this->marked_element_ids = [];

        if (!$topFolderId) {
            $folder = Folder::findTopFolder($this->user->id);
        } else {
            $folder = Folder::find($topFolderId);
        }

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        $this->buildSidebar($this->topFolder);

        //check for INBOX and OUTBOX folder:

        //first the INBOX folder:
        $inbox_folder = UserFileArea::getInboxFolder($this->user);
        if(!$inbox_folder) {
            //no inbox folder
            PageLayout::postWarning(_('Ordner für Anhänge eingegangener Nachrichten konnte nicht ermittelt werden!'));
        }

        //then the OUTBOX folder:
        $outbox_folder = UserFileArea::getOutboxFolder($this->user);
        if(!$outbox_folder) {
            //no inbox folder
            PageLayout::postWarning(_('Ordner für Anhänge gesendeter Nachrichten konnte nicht ermittelt werden!'));
        }
    }



    /**
    Displays the files in flat view
     **/
    public function flat_action()
    {
        Navigation::activateItem('/profile/files/flat');

        $this->marked_element_ids = [];

        $filePreselector = Request::get('select', null);

        $folder = Folder::findTopFolder($this->user->id);

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        //find all files in all subdirectories:
        list($this->files, $this->folders) = array_values(FileManager::getFolderFilesRecursive($this->topFolder, $GLOBALS['user']->id));
    }


    private function fillZipArchive(ZipArchive $zip, $zip_path = '', $filesystem_item = null, User $user)
    {
        if($filesystem_item instanceof FileRef) {
            //check permissions:
            $folder = $filesystem_item->folder;
            if(!$folder) {
                return;
            }

            $folder = $folder->getTypedFolder();
            if(!$folder) {
                return;
            }

            if($folder->isFileDownloadable($filesystem_item->id, $user->id)) {
                $zip->addFile($filesystem_item->file->getPath(), $zip_path . $filesystem_item->name);
                $filesystem_item->downloads += 1;
                $filesystem_item->store();
            }
        } elseif($filesystem_item instanceof FolderType) {
            //check permissions:
            if($filesystem_item->isReadable($user->id)) {
                //add directory:
                $zip->addEmptyDir($zip_path . $filesystem_item->name);

                //loop through all file_refs and subfolders:
                foreach($filesystem_item->getFiles() as $file_ref) {
                        $this->fillZipArchive($zip, $zip_path . $filesystem_item->name . '/', $file_ref, $user);
                }

                foreach($filesystem_item->getSubfolders() as $subfolder) {
                    $this->fillZipArchive($zip, $zip_path . $filesystem_item->name . '/', $subfolder, $user);
                }
            }
        }
    }



    /**
     * This action allows downloading, copying, moving and deleting files and folders in bulk.
     */
    public function bulk_action()
    {
        //check, if at least one ID was given:

        $parent_folder_id = Request::get('parent_folder_id', null);
        $ids = Request::getArray('ids');

        if(empty($ids)) {
            $this->redirect('files/index/' . $parent_folder_id);
        }

        //check, which action was chosen:

        if(Request::submitted('download')) {
            //bulk downloading:

            //loop through all ids, check if it refers to a file_ref or a folder
            //and zip them into one big archive:

            $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');
            $zip = new ZipArchive();

            $zip_open_result = $zip->open($tmp_file, ZipArchive::CREATE);
            if($zip_open_result !== true) {
                throw new Exception('Could not create zip file: ' . $zip_open_result);
            }

            $zip_path = '';

            $user = User::findCurrent();

            foreach($ids as $id) {
                //check if the ID references a FileRef:
                $filesystem_item = FileRef::find($id);
                if(!$filesystem_item) {
                    //check if the ID references a Folder:
                    $filesystem_item = Folder::find($id);
                    if($filesystem_item) {
                        $filesystem_item = $filesystem_item->getTypedFolder();
                    }
                }

                if(!$filesystem_item) {
                    //we can't find any file system item for this ID!
                    continue;
                }

                $this->fillZipArchive($zip, '', $filesystem_item, $user);

            }

            //finish writing:
            $zip->close();


            //Ok, we have a zip file now: We can send it to the user:
            //(The following code was taken from the old file area file
            //download.php)

            $dispositon = sprintf('%s;filename="%s"',
                              'inline',
                              urlencode(basename($tmp_file, '.zip'))
                            );
            $this->response->add_header('Content-Disposition', $dispositon);
            $this->response->add_header('Content-Description', 'File Transfer');
            $this->response->add_header('Content-Transfer-Encoding' , 'binary');
            $this->response->add_header('Content-Type', 'application/zip');
            $this->response->add_header('Content-Length', filesize($tmp_file));

            $this->render_nothing();
            $this->download_handle = fopen($tmp_file, 'r');
            $this->download_remove = $tmp_file;

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
                    $result = FileManager::deleteFolder($foldertype, $user);
                    if (!is_array($result)) {
                        $count_folders++;
                        $children = $this->countChildren($result);
                        $count_files += $children[0];
                        $count_folders += $children[1];
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


            $dest_range = Request::option('cid');
            $destination_folder = Folder::find($parent_folder_id);

            switch ($destination_folder->range_type) {
                case 'course':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $parent_folder_id . '?cid=' . $dest_range));
                case 'institute':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/' . $parent_folder_id . '?cid=' . $dest_range));
                case 'user':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/' . $parent_folder_id));
                default:
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $parent_folder_id));
            }

        }
    }

    /**
     * Action to configure the different FileSystem-plugins
     */
    public function configure_action()
    {
        $this->configure_urls = array();
        PageLayout::setTitle(_("Dateibereich zur Konfiguration auswählen"));
        foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) {
            $url = $plugin->filesystemConfigurationURL();
            $navigation = $plugin->getFileSelectNavigation();
            if ($url) {
                $this->configure_urls[] = array(
                    'name' => $navigation->getTitle(),
                    'icon' => $navigation->getImage(),
                    'url' => $url
                );
            }
        }
    }

    public function system_action($plugin_id, $folder_id = null)
    {
        $this->plugin = PluginManager::getInstance()->getPluginById($plugin_id);
        if (!$this->plugin->isPersonalFileArea()) {
            throw new Exception("Dieser Bereich ist nicht verfügbar.");
        }
        Navigation::activateItem('/profile/files/'.get_class($this->plugin));
        $this->topFolder = $this->plugin->getFolder($folder_id);
        $this->buildSidebar($this->topFolder);
        $this->controllerpath = "files/system/".$plugin_id;
        URLHelper::bindLinkParam("to_plugin", get_class($this->plugin));
        $this->render_template("files/index", $GLOBALS['template_factory']->open("layouts/base"));
    }

    public function copyhandler_action($destination_id)
    {
        $to_plugin = Request::get("to_plugin", null);
        $plugin = Request::get("plugin", null);

        $fileref_id = Request::get("fileref_id", null);
        $copymode = Request::get("copymode", null);

        $user = User::findCurrent();
        $destination_folder = Folder::find($destination_id)->getTypedFolder();

        $errors = array();
        $count_files = 0;
        $count_folders = 0;
        $count_dracula = 'vampire'; //sorry

        $filerefs = explode('-', $fileref_id);
        if (!empty($filerefs)) {
            foreach ($filerefs as $fileref) {

                if ($source = FileRef::find($fileref)) {
                    if ($copymode == 'move') {
                        $result = FileManager::moveFileRef($source, $destination_folder, $user);
                    } else {
                        $result = FileManager::copyFileRef($source, $destination_folder, $user);
                    }
                    if (!is_array($result)) $count_files++;
                } elseif ($source = Folder::find($fileref)) {
                    $source_folder = $source->getTypedFolder();
                    if ($copymode == 'move') {
                        $result = FileManager::moveFolder($source_folder, $destination_folder, $user);
                    } else {
                        $result = FileManager::copyFolder($source_folder, $destination_folder, $user);
                    }
                    if (!is_array($result)) {
                        $count_folders++;
                        $children = $this->countChildren($result);
                        $count_files += $children[0];
                        $count_folders += $children[1];
                    }
                }
                if (is_array($result)) {
                    $errors = array_merge($errors, $result);
                }
            }
        }

        $actiontext = ($copymode == 'copy') ? _('kopiert') : _('verschoben');

        if (empty($errors) || $count_files > 0 || $count_folders > 0) {

            if (count($filerefs) == 1) {
                if ($source_folder) {
                    PageLayout::postSuccess(sprintf(_('Der Ordner wurde %s!'), $actiontext));
                } else {
                    PageLayout::postSuccess(sprintf(_('Die Datei wurde %s!'), $actiontext));
                }
            } else {
                if ($count_files > 0 && $count_folders > 0) {
                    PageLayout::postSuccess(sprintf(_('Es wurden %s Ordner und %s Dateien %s!'), $count_folders, $count_files, $actiontext));
                } elseif ($count_files > 0) {
                    PageLayout::postSuccess(sprintf(_('Es wurden  %s Dateien %s!'), $count_files, $actiontext));
                } else {
                    PageLayout::postSuccess(sprintf(_('Es wurden %s Ordner %s!'), $count_folders, $actiontext));
                }
            }

        } else {
            PageLayout::postError(_('Es ist ein Fehler aufgetreten!'), $errors);
        }

        $dest_range = $destination_folder->range_id;

        switch ($destination_folder->range_type) {
            case 'course':
                return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $destination_folder->getId() . '?cid=' . $dest_range));
            case 'institute':
                return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/' . $destination_folder->getId() . '?cid=' . $dest_range));
            case 'user':
                return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/' . $destination_folder->getId()));
            default:
                return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $destination_folder->getId()));
        }

    }

    private function countChildren (FolderType $folder) {
        $file_count = count($folder->getFiles());
        $folder_count = count($folder->getSubfolders());
        if ($folder_count > 0) {
            foreach ($folder->getSubfolders() as $subfolder) {
                $subs = $this->countChildren($subfolder);
                $file_count += $subs[0];
                $folder_count += $subs[1];
            }
        }
        return array($file_count, $folder_count);
    }


    /**
     * The after_filter method of this controller transmits file contens
     * (if any) and removes temporary files that were created before
     * bulk downloading.
     *
     * @param String $action The executed action.
     * @param Array  $args Arguments that were passed to the executed action.
     */
    public function after_filter($action, $args)
    {
        //(The following code was taken from the old file area file
        //download.php)

        parent::after_filter($action, $args);

        //If the handle for the file to be downloaded is opened
        //we must send the file and then close it.
        if($this->download_handle && is_resource($this->download_handle)) {
            fpassthru($this->download_handle);
            fclose($this->download_handle);
        }

        //If a temporary file was created for the download
        //we must delete if afterwards.
        if($this->download_remove && file_exists($this->download_remove)) {
            unlink($this->download_remove);
        }
    }
}
