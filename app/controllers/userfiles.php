<?php
/**
 * userfiles.php - controller to display personal files of a user
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


class UserfilesController extends AuthenticatedController
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
    public function index_action($topFolder = '')
    {
        if(Navigation::hasItem('/profile/files')) {
            Navigation::activateItem('/profile/files');
        }
        if(Navigation::hasItem('/profile/files/tree')) {
            Navigation::activateItem('/profile/files/tree');
        }

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
        
        $this->render_template('course/files/index.php', $GLOBALS['template_factory']->open('layouts/base'));
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
        
        $this->render_template('course/files/flat.php', $GLOBALS['template_factory']->open('layouts/base'));
    }
    
}
