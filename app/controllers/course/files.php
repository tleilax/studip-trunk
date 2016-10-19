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
                URLHelper::getUrl('dispatch.php/folder/new'),
                Icon::create('folder-empty+add', 'clickable'),
                array('data-dialog' => 'size=auto')
            );

        }
        $actions->addLink(
            _('Neue Datei'),
            URLHelper::getUrl('dispatch.php/file/upload', ['topfolder' => $this->topFolder->id]),
            Icon::create('file+add', 'clickable'),
            array('data-dialog' => 'size=auto')
        );

        $sidebar->addWidget($actions);
    }



    /**
        Displays the files in tree view
    **/
    public function index_action($topFolder = '', $page = 1)
    {
        if(Navigation::hasItem('/course/files_new')) {
            Navigation::activateItem('/course/files_new');
        }
        if(Navigation::hasItem('/course/files_new/tree')) {
            Navigation::activateItem('/course/files_new/tree');
        }

        $course = Course::findCurrent();
        $institute = null;
        if(!$course) {
            $institute = Institute::findCurrent();
        }
        if (!$topFolder) {
            if($course) {
                $this->topFolder = Folder::findTopFolder($course->id);
            } else {
                $this->topFolder = Folder::findTopFolder($institute->id);
            }
            
        } else {
            $this->topFolder = Folder::find($topFolder);
        }
        
        if(!$this->topFolder) {
            //create top folder:
            $this->topFolder = new Folder();
            //$this->topFolder->user_id = ;
            if($course) {
                $this->topFolder->range_id = $course->id;
            } elseif($institute) {
                $this->topFolder->range_id = $institute->id;
            } else {
                PageLayout::postError(_('Fehler beim Erstellen des Hauptordners: Zugehöriges Datenbankobjekt nicht gefunden!'));
                return;
            }
            $this->topFolder->store();
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
        
        $limit = 2;        
        $start_index = ($page-1) * $limit;
        
        $this->limit = $limit;
        $this->page = $page;
        $this->dir_id = $this->topFolder->id;
                
        $this->buildSidebar();
        if($course) {
            PageLayout::setTitle($course->getFullname() . ' - ' . _('Dateien'));
        } elseif($institute) {
            PageLayout::setTitle($institute->getFullname() . ' - ' . _('Dateien'));
        }

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
    }

}
