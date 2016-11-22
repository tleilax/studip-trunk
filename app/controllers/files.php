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
    }


    /**
     * Helper method for filling the sidebar with actions.
     */
    private function buildSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $actions = new ActionsWidget();

        if ($this->topFolder && $this->topFolder->isSubfolderAllowed($GLOBALS['user']->id)) {
            //standard dialog version:
            /*$actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl('dispatch.php/folder/new',
                    array('context' => 'user', 'rangeId' => $this->user->id, 'parent_folder_id' => $this->topFolder->getId())),
                Icon::create('folder-empty+add', 'clickable'),
                array('data-dialog' => 'size=auto')
            );
            */

            //AJAX version:
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl(
                    'dispatch.php/folder/new',
                    [
                        'context' => 'user',
                        'rangeId' => $this->user->id,
                        'parent_folder_id' => $this->topFolder->getId()
                    ]
                ),
                Icon::create('folder-empty+add', 'clickable'),
                [
                    'onclick' => 'STUDIP.Folders.openAddFoldersWindow(\''. $this->topFolder->getId() . '\', \'' . $this->user->id . '\'); return false;'
                ]
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

        $this->buildSidebar();
        
        //check for INBOX and OUTBOX folder:
        
        //first the INBOX folder:
        
        $inbox_folder = UserFileArea::getInboxFolder($this->user);
        if(!$inbox_folder) {
            //no inbox folder
            PageLayout::postWarning(_('Ordner für Nachrichteneingänge konnte nicht ermittelt werden!'));
        }
        
        //then the OUTBOX folder:
        
        //TODO
        
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
        //find all files in all subdirectories:
        list($this->files, $this->folders) = array_values(FileManager::getFolderFilesRecursive($this->topFolder, $GLOBALS['user']->id));
    }



}
