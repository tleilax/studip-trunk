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
}
