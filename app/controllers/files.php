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


class FilesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
    
        PageLayout::addSqueezePackage('tablesorter');
    }
    
    private function buildSidebar($top_folder_id = null)
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $actions = new ActionsWidget();
        
        $actionParams = [];
        if($top_folder_id) {
            $actionParams['parent_folder_id'] = $top_folder_id;
        }
        
        $actions->addLink(
            _('Neuer Ordner'),
            URLHelper::getUrl(
                'dispatch.php/folder/new',
                $actionParams
            ),
            Icon::create('folder-empty+add', 'clickable'),
            array('data-dialog' => 'reload-on-close;size=auto')
        );

        $actions->addLink(
            _('Datei hinzufügen'),
            "#",
            Icon::create('file+add', 'clickable'),
            array('onClick' => "STUDIP.Files.openAddFilesWindow(); return false;")
        );
        
        $sidebar->addWidget($actions);
    }



    /**
        Displays the files in tree view
    **/
    public function index_action($folder_id = '')
    {
        if(Navigation::hasItem('/profile/files')) {
            Navigation::activateItem('/profile/files');
        }
        if(Navigation::hasItem('/profile/files/tree')) {
            Navigation::activateItem('/profile/files/tree');
        }
        
        $this->marked_element_ids = [];
        
        $user = User::findCurrent();
        if(!$user) {
            //TODO: throw exception

            return; //DEVELOPMENT STAGE CODE!
        }
        if (!$folder_id) {
            $this->topFolder = new StandardFolder(Folder::findTopFolder($user->id));
        } else {
            $this->topFolder = new StandardFolder(Folder::find($folder_id));
        }
        
        if(!$this->topFolder) {
            //create top folder:
            $this->topFolder = new StandardFolder(Folder::createTopFolder($user->id, 'user'));
        }
        
        $this->buildSidebar($this->topFolder->getId());
        PageLayout::setTitle($user->getFullname() . ' - ' . _('Dateien'));
        
        //$this->render_template('files/index.php', $GLOBALS['template_factory']->open('layouts/base'));
    }


    /**
        Displays the files in flat view
    **/
    public function flat_action($topFolder = '')
    {
        if(Navigation::hasItem('/profile/files')) {
            Navigation::activateItem('/profile/files');
        }
        if(Navigation::hasItem('/profile/files/flat')) {
            Navigation::activateItem('/profile/files/flat');
        }
        
        $this->marked_element_ids = [];
        
        $user = User::findCurrent();
        if(!$user) {
            //TODO: throw exception
            
            return; //DEVELOPMENT STAGE CODE!
        }
        
        if (!$topFolder) {
            $this->topFolder = Folder::findTopFolder($user->id);
        } else {
            $this->topFolder = Folder::find($topFolder);
        }
        
        if(!$this->topFolder) {
            //create top folder:
            $this->topFolder = Folder::createTopFolder($user->id, 'user');
        }
        
        $this->buildSidebar();
        PageLayout::setTitle($user->getFullname() . ' - ' . _('Dateien'));
        
        $this->render_template('files/flat.php', $GLOBALS['template_factory']->open('layouts/base'));
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
            header("Location: ". URLHelper::getURL("dispatch.php/files/choose_file/".$folder->getId(), array(
                'to_plugin' => Request::get("to_plugin"),
                'to_folder_id' => $folder_id
            )));
            /*$this->redirect("files/choose_file/".$folder->getId(), array(
                'to_plugin' => Request::get("to_plugin"),
                'to_folder_id' => $folder_id
            ));*/
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
                $file = $plugin->getPreparedFile(Request::get("file_id"));
            } else {
                $file = FileRef::find(Request::get("file_id"))->file;
            }

            $error = $this->to_folder_type->validateUpload($file, $GLOBALS['user']->id);
            if (!$error) {
                //do the copy
                //$this->to_folder_type->createFile($file, $GLOBALS['user']->id);
                $this->to_folder_type->getFolderData()->linkFile($file);
                $this->response->add_header("X-Dialog-Execute", "STUDIP.Files.reloadPage");
                $this->render_text(MessageBox::success(_("Datei wurde hinzugefügt.")));
            } else {
                PageLayout::postMessage(MessageBox::error(_("Konnte die Datei nicht hinzufügen.", array($error))));
            }
        }
        if (Request::get("plugin")) {
            $this->plugin = PluginManager::getInstance()->getPlugin(Request::get("plugin"));
            if (Request::get("search") && $this->plugin->hasSearch()) {
                $this->top_folder = $this->plugin->search(Request::get("search"), Request::getArray("parameter"));
            } else {
                $this->top_folder = $this->plugin->getFolder($folder_id, true);
                if (is_a($this->top_folder, "Flexi_Template")) {
                    $this->top_folder->set_attribute("select", true);
                    $this->top_folder->set_attribute("to_folder", $this->to_folder);
                    $this->render_text($this->top_folder);
                }
            }
        } else {
            $this->top_folder = new Folder($folder_id);
            if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
                throw new AccessException();
            }
        }
    }
    
    
    
}
