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
    
    
    
    
    public function index_action()
    {
        if(Navigation::hasItem('/course/files_new')) {
            Navigation::activateItem('/course/files_new');
        }
        
        $course = Course::find(Request::get('cid'));
        if(!$course) {
            //TODO: throw exception
            
            return; //DEVELOPMENT STAGE CODE!
        }
        
        PageLayout::setTitle($course->name . ' - ' . _('Dateien'));
        
        $this->buildSidebar();
        
        //get all files of the course
        $this->files = null;
        
        $path = Request::get('path', '/'); //file system hierarchy path
        
        //for early development stage we MUST check which class is available:
        if(class_exists('StudipDocument')) {
            $this->files = StudipDocument::findByCourseId($course->id);
        } elseif(class_exists('File')) {
            //TO BE DESIGNED
        } else {
        
        }
    }
}
