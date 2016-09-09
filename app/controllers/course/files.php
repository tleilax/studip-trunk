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
                $this->link_for('/createfolder'),
                Icon::create('folder-empty+add', 'clickable'),
                array('data-dialog' => 'size=auto')
            );

        }
        $actions->addLink(
            _('Neue Datei'),
            $this->link_for('/upload', ['topfolder' => $this->topfolder->id]),
            Icon::create('file+add', 'clickable'),
            array('data-dialog' => 'size=auto')
        );

        $sidebar->addWidget($actions);
    }



    /**
        Displays the files in tree view
    **/
    public function tree_action($topfolder = '')
    {
        if(Navigation::hasItem('/course/files_new')) {
            Navigation::activateItem('/course/files_new');
        }
        if(Navigation::hasItem('/course/files_new/tree')) {
            Navigation::activateItem('/course/files_new/tree');
        }

        $course = Course::findCurrent();
        if(!$course) {
            //TODO: throw exception

            return; //DEVELOPMENT STAGE CODE!
        }
        if (!$topfolder) {
            $this->topfolder = Folder::findTopFolder($course->id);
        } else {
            $this->topfolder = Folder::find($topfolder);
        }
        $this->buildSidebar();
        PageLayout::setTitle($course->getFullname() . ' - ' . _('Dateien'));

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
