<?php
/**
 * files.php - controller to display files in a course
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


class Course_FilesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation

        PageLayout::addSqueezePackage('tablesorter');

        $this->cid = Request::option('cid');
        if ($action == 'index') {
            if (!empty($args)) {
                $this->currentFolder = $args[0];
            } else {                
                $this->currentFolder = Folder::findTopFolder($this->cid)->id;
            }
        }
    }
    
    
    /**
        Retrieves the permissions of the current user (identified by $userId).
    **/
    private function getPermissions($userId = null)
    {
        //STUB:
        return ['r' => true, 'w' => true, 'x' => true];
    }


    private function buildSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $userRights = $this->getPermissions(User::findCurrent()->id);

        $actions = new ActionsWidget();

        if($userRights['w'] and $userRights['x']) {
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl('dispatch.php/folder/new', 
                        array('context' => 'course', 'rangeId' => $this->cid, 'parentFolderId' => $this->currentFolder)),
                Icon::create('folder-empty+add', 'clickable'),
                array('data-dialog' => 'size=auto')
            );

        }
        $actions->addLink(
            _('Datei hochladen'),
            "#",
            Icon::create('file+add', 'clickable'),
            array('onClick' => "jQuery('#file_selector input[type=file]').click(); return false;")
        );
        $actions->addLink(
            _('Datei hinzufügen'),
            "#",
            Icon::create('file+add', 'clickable'),
            array('onClick' => "STUDIP.Dialog.show(jQuery('.source_selector').html(), { title: '"._("Datei hinzufügen")."'}); return false;")
        );

        $sidebar->addWidget($actions);
    }



    /**
        Displays the files in tree view
    **/
    public function index_action($topFolderId = '', $page = 1)
    {
        if(Navigation::hasItem('/course/files_new')) {
            Navigation::activateItem('/course/files_new');
        }
        if(Navigation::hasItem('/course/files_new/tree')) {
            Navigation::activateItem('/course/files_new/tree');
        }
        
        $this->markedElementIds = [];
        
        $course = Course::findCurrent();
        $institute = null;
        if(!$course) {
            $institute = Institute::findCurrent();
        }
        if (!$topFolderId) {
            if($course) {
                $this->topFolder = Folder::findTopFolder($course->id);
            } else {
                $this->topFolder = Folder::findTopFolder($institute->id);
            }
        } else {
            $this->topFolder = Folder::find($topFolderId);
        }
        
        if(!$this->topFolder) {
            //create top folder:
            if($course) {
                $this->topFolder = Folder::createTopFolder($course->id, 'course');
                
            } elseif($institute) {
                $this->topFolder = Folder::createTopFolder($institute->id, 'inst');
            } else {
                PageLayout::postError(_('Fehler beim Erstellen des Hauptordners: Zugehöriges Datenbankobjekt nicht gefunden!'));
                return;
            }
        }
        
        if(!$this->topFolder) {
            PageLayout::postError(_('Fehler beim Laden des Hauptordners!'));
            return;
        }
        
        if (!empty($this->topFolder['parent_id'])) {
            $this->isRoot = false;
            $this->parent_id = $this->topFolder['parent_id'];
            $this->parent_page = 1;
        } else {
            $this->isRoot = true;
        }
        
        $this->marked = array();        
        $this->filecount = count($this->topFolder->subfolders);
        $this->filecount += count($this->topFolder->file_refs);
        
        $limit = 20;  
        $remain_limit = $limit;
        $start_index = ($page-1) * $limit;        
        
        $partial_folders = array();
        $partial_frefs = array();
        
        if ($start_index < count($this->topFolder->subfolders)) {
            $partial_folders = $this->topFolder->subfolders->limit($start_index, $limit);
            $remain_limit -= count($partial_folders);
        }
        if (!empty($remain_limit)) {
            if(!empty($partial_folders)) {
                $start_index = $start_index - count($this->topFolder->subfolders) + count($partial_folders);
                $partial_frefs = $this->topFolder->file_refs->limit($start_index, $remain_limit);
            } else {
                $start_index = $start_index - count($this->topFolder->subfolders);
                $partial_frefs = $this->topFolder->file_refs->limit($start_index, $remain_limit);
            }            
        }
        
        $this->topFolder->subfolders = $partial_folders;
        $this->topFolder->file_refs = $partial_frefs;
        
        $this->limit = $limit;
        $this->page = $page;
        $this->dir_id = $this->topFolder->id;
                
        $this->buildSidebar();
        if($course) {
            PageLayout::setTitle($course->getFullname() . ' - ' . _('Dateien'));
        } elseif($institute) {
            PageLayout::setTitle($institute->getFullname() . ' - ' . _('Dateien'));
        }
        
        $this->render_template('files/index.php', $GLOBALS['template_factory']->open('layouts/base'));
    }
    
    
    private function getFolderFiles(Folder $folder)
    {
        $files = [];
        if($folder->file_refs) {
            foreach($folder->file_refs as $fileRef) {
                $files[] = $fileRef;
            }
            
            if($folder->subfolders) {
                foreach($folder->subfolders as $subFolder) {
                    $files = array_merge($files, $this->getFolderFiles($subFolder));
                }
            }
        }
        return $files;
    }
    
    
    /**
        Displays the files in flat view
    **/
    public function flat_action($topFolder = '')
    {
        if(Navigation::hasItem('/course/files_new')) {
            Navigation::activateItem('/course/files_new');
        }
        if(Navigation::hasItem('/course/files_new/flat')) {
            Navigation::activateItem('/course/files_new/flat');
        }
        
        $this->markedElementIds = [];
        
        $filePreselector = Request::get('select', null);
        
        
        $course = Course::find(Request::get('cid'));
        if(!$course) {
            //TODO: throw exception

            return; //DEVELOPMENT STAGE CODE!
        }
        
        //find top folder:
        
        if (!$topFolder) {
            $this->topFolder = Folder::findTopFolder($course->id);
        } else {
            $this->topFolder = Folder::find($topFolder);
        }
        
        if(!$this->topFolder) {
            //create top folder:
            $this->topFolder = new Folder();
            //$this->topFolder->user_id = $user->id;
            $this->topFolder->range_id = $course->id;
            $this->topFolder->store();
        }
        
        //find all files in all subdirectories:
        
        $this->files = $this->getFolderFiles($this->topFolder);
        
        
        $this->buildSidebar();
        PageLayout::setTitle($course->getFullname() . ' - ' . _('Dateien'));
        
        
        $this->render_template('files/flat.php', $GLOBALS['template_factory']->open('layouts/base'));
    }
    
    
    public function upload_action()
    {
        if (Request::isPost() && is_array($_FILES)) {
            $folder = Folder::find(Request::option('folder_id'));
            CSRFProtection::verifyUnsafeRequest();
            $validated_files = FileManager::handleFileUpload($_FILES['file'], $folder->getTypedFolder(), $GLOBALS['user']->id);
            if (count($validated_files['error'])) {
                PageLayout::postError(_('Beim Upload ist ein Fehler aufgetreten', array_map('htmlready', $validated_files['error'])));
            } else {
                foreach($validated_files['files'] as $one) {
                    if ($one->store() && $folder->linkFile($one, Request::get('description'))) {
                        $ok[] = $one->name;
                    }
                }
                if (count($ok)) {
                    PageLayout::postSuccess(sprintf(_('Es wurden %s Dateien hochgeladen'), count($ok)), array_map('htmlready', $ok));
                }
                return $this->redirect($this->url_for('/tree/' . $folder->id));
            }
        }
        $this->folder_id = Request::option('topfolder');
        
        if(Request::isDialog()) {
            $this->render_template('file/upload.php');
        } else {
            $this->render_template('files/index.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
    }

}
