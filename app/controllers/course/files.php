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
 * @since       3.6
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
                URLHelper::getLink('dispatch.php/folder/create/' . Request::get('cid') . '/sem'),
                Icon::create('folder-empty+add', 'clickable'),
                array('data-dialog' => 'size=auto')
            );
            
        }
        
        $sidebar->addWidget($actions);
    }
    
    
    private function loadFiles($courseId = null, $flat = false)
    {
        if(!$courseId) {
            return;
        }
        
        //get all files of the course
        $this->files = null;
        
        $path = Request::get('path', '/'); //file system hierarchy path
        
        //for early development stage we MUST check which class is available:
        if(class_exists('StudipDocument')) {
            $this->files = StudipDocument::findByCourseId($courseId);
        } elseif(class_exists('File')) {
            //TO BE DESIGNED
        } else {
        
        }
    }
    
    
    /**
        Displays the files in tree view
    **/
    public function tree_action()
    {
        if(Navigation::hasItem('/course/files_new')) {
            Navigation::activateItem('/course/files_new');
        }
        if(Navigation::hasItem('/course/files_new/tree')) {
            Navigation::activateItem('/course/files_new/tree');
        }
        
        $course = Course::find(Request::get('cid'));
        if(!$course) {
            //TODO: throw exception
            
            return; //DEVELOPMENT STAGE CODE!
        }
        
        $this->buildSidebar();
        PageLayout::setTitle($course->name . ' - ' . _('Dateien'));
        
        $this->loadFiles($course->id);
    }
    
    
    /**
        Displays the files in flat view
    **/
    public function flat_action()
    {
        if(Navigation::hasItem('/course/files_new')) {
            Navigation::activateItem('/course/files_new');
        }
        if(Navigation::hasItem('/course/files_new/flat')) {
            Navigation::activateItem('/course/files_new/flat');
        }
        
        $course = Course::find(Request::get('cid'));
        if(!$course) {
            //TODO: throw exception
            
            return; //DEVELOPMENT STAGE CODE!
        }
        
        $this->buildSidebar();
        PageLayout::setTitle($course->name . ' - ' . _('Dateien'));
        
        $this->loadFiles($course->id, true);
    }
    
    
    
    public function index_action()
    {
        $this->redirect('course/files/tree');
    }
}
