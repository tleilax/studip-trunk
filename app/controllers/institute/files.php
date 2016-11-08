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


class Institute_FilesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        checkObject();
        checkObjectModule('documents');
        $this->institute = Institute::findCurrent();
        object_set_visit_module('documents');

        PageLayout::addSqueezePackage('tablesorterfork');
        PageLayout::setHelpKeyword("Basis.Dateien");
        PageLayout::setTitle($this->institute->getFullname() . " - " . _("Dateien"));

        $this->last_visitdate = object_get_visit($this->institute->id, 'documents');

    }


    private function buildSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $actions = new ActionsWidget();

        if ($this->topFolder && $this->topFolder->isSubfolderAllowed($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl('dispatch.php/folder/new',
                    array('context' => 'institute', 'rangeId' => $this->institute->id, 'parent_folder_id' => $this->topFolder->getId())),
                Icon::create('folder-empty+add', 'clickable'),
                array('data-dialog' => 'size=auto')
            );

        }
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
    public function index_action($topFolderId = '')
    {


        Navigation::activateItem('/course/files_new/tree');

        $this->marked_element_ids = [];

        if (!$topFolderId) {
            $folder = Folder::findTopFolder($this->institute->id);
        } else {
            $folder = Folder::find($topFolderId);
        }

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        if (!empty($this->topFolder->parent_id)) {
            $this->isRoot = false;
            $this->parent_id = $this->topFolder->parent_id;
            $this->parent_page = 1;
        } else {
            $this->isRoot = true;
        }

        $this->marked = array();
        $this->filecount = count($this->topFolder->getSubfolders());
        $this->filecount += count($this->topFolder->getFiles());

        $this->dir_id = $this->topFolder->getId();

        $this->buildSidebar();

        $this->render_template('files/index.php', $GLOBALS['template_factory']->open('layouts/base'));
    }


    private function getFolderFiles($folder)
    {
        $this->folders[$folder->getId()] = $folder;
        foreach ($folder->getSubFolders() as $subfolder) {
            $this->getFolderFiles($subfolder);
        }
        foreach ($folder->getFiles() as $file) {
            $this->files[] = $file;
        }
    }


    /**
    Displays the files in flat view
     **/
    public function flat_action()
    {

        Navigation::activateItem('/course/files_new/flat');

        $this->marked_element_ids = [];

        $filePreselector = Request::get('select', null);

        $folder = Folder::findTopFolder($this->institute->id);

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        //find all files in all subdirectories:
        $this->files = [];
        $this->folders = [];
        $this->getFolderFiles($this->topFolder);


        $this->render_template('files/flat.php', $GLOBALS['template_factory']->open('layouts/base'));
    }
}
