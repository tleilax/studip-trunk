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
    private function buildSidebar(FolderType $folder)
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $actions = new ActionsWidget();

        if ($folder->isSubfolderAllowed($GLOBALS['user']->id)) {
            //standard dialog version:
            /*$actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl('dispatch.php/folder/new',
                    array('context' => 'user', 'rangeId' => $this->user->id, 'parent_folder_id' => $folder->getId())),
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
                        'parent_folder_id' => $folder->getId()
                    ]
                ),
                Icon::create('folder-empty+add', 'clickable'),
                [
                    'onclick' => 'STUDIP.Folders.openAddFoldersWindow(\''. $folder->getId() . '\', \'' . $this->user->id . '\'); return false;'
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

    
    private function fillZipArchive(ZipArchive $zip, $zip_path = '', $filesystem_item = null)
    {
        if($filesystem_item instanceof FileRef) {
            $zip->addFile($filesystem_item->file->getPath(), $zip_path . $filesystem_item->name);
            $filesystem_item->downloads += 1;
            $filesystem_item->store();
        } elseif($filesystem_item instanceof FolderType) {
            $zip->addEmptyDir($filesystem_item->name);
            
            //loop through all file_refs and subfolders:
            foreach($filesystem_item->getFiles() as $file_ref) {
                $this->fillZipArchive($zip, $zip_path . $filesystem_item->name . '/', $file_ref);
            }
            
            foreach($filesystem_item->getSubfolders() as $subfolder) {
                $this->fillZipArchive($zip, $zip_path . $filesystem_item->name . '/', $subfolder);
            }
        }
    }
    
    
    
    /**
     * This action allows downloading, copying, moving and deleting files and folders in bulk.
     */
    public function bulk_action()
    {
        //check, if at least one ID was given:
        
        $parent_folder_id = Request::get('parent_folder_id', null);
        $ids = Request::getArray('ids');
        
        if(empty($ids)) {
            $this->redirect('files/index/' . $parent_folder_id);
        }
        
        //check, which action was chosen:
        
        if(Request::submitted('download')) {
            //bulk downloading:
            
            //loop through all ids, check if it refers to a file_ref or a folder
            //and zip them into one big archive:
            
            $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');
            $zip = new ZipArchive();
            
            $zip_open_result = $zip->open($tmp_file, ZipArchive::CREATE);
            if($zip_open_result !== true) {
                throw new Exception('Could not create zip file: ' . $zip_open_result);
            }
            
            $zip_path = '';
            
            foreach($ids as $id) {
                //check if the ID references a FileRef:
                $filesystem_item = FileRef::find($id);
                if(!$filesystem_item) {
                    //check if the ID references a Folder:
                    $filesystem_item = Folder::find($id);
                    if($filesystem_item) {
                        $filesystem_item = $filesystem_item->getTypedFolder();
                    }
                }
                
                if(!$filesystem_item) {
                    //we can't find any file system item for this ID!
                    continue;
                }
                
                $this->fillZipArchive($zip, '', $filesystem_item);
                
            }
            
            //finish writing:
            $zip->close();
            
            
            //Ok, we have a zip file now: We can send it to the user:
            //(The following code was taken from the old file area file
            //download.php)
            
            $dispositon = sprintf('%s;filename="%s"',
                              'inline',
                              urlencode(basename($tmp_file, '.zip'))
                            );
            $this->response->add_header('Content-Disposition', $dispositon);
            $this->response->add_header('Content-Description', 'File Transfer');
            $this->response->add_header('Content-Transfer-Encoding' , 'binary');
            $this->response->add_header('Content-Type', 'application/zip');
            $this->response->add_header('Content-Length', filesize($tmp_file));
            
            $this->render_nothing();
            $this->download_handle = fopen($tmp_file, 'r');
            $this->download_remove = $tmp_file;
            
        } elseif(Request::submitted('copy')) {
            //bulk copying
        } elseif(Request::submitted('move')) {
            //bulk moving
        } elseif(Request::submitted('delete')) {
            //bulk deleting
        }
    }
    
    /**
     * The after_filter method of this controller transmits file contens
     * (if any) and removes temporary files that were created before
     * bulk downloading.
     *
     * @param String $action The executed action.
     * @param Array  $args Arguments that were passed to the executed action.
     */
    public function after_filter($action, $args)
    {
        //(The following code was taken from the old file area file
        //download.php)
        
        parent::after_filter($action, $args);
        
        //If the handle for the file to be downloaded is opened
        //we must send the file and then close it.
        if($this->download_handle && is_resource($this->download_handle)) {
            fpassthru($this->download_handle);
            fclose($this->download_handle);
        }
        
        //If a temporary file was created for the download
        //we must delete if afterwards.
        if($this->download_remove && file_exists($this->download_remove)) {
            unlink($this->download_remove);
        }
    }
}
