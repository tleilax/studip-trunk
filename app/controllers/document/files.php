<?php

/**
 * files.php
 *
 * Der Controller stellt angemeldeten Benutzer/innen einen Dateimanager
 * fuer deren persoenlichen Dateibereich im Stud.IP zur Verfuegung.
 *
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0
 * @copyright   Stud.IP Core-Group
 * @since       3.1
 *
 * @todo        Remove user dir creation from this controller, it is storage type specific
 * @todo        Respect quotas - done in Rev. 29817
 * @todo        Respect file extension black list - done in Rev. 29817
 * @todo        Extends file extension black list to mime type black list?
 * @todo        Info page for # of downloads
 * @todo        Inline display of media
 * @todo        AJAX file upload
 * @todo        Admin/root handling needs to be improved
 * @todo        ZIP extract in local file space?
 * @todo        Test another storage type (DB? FTP?)
 * @todo        Drag and drop move operation
 * @todo        ?? Trash functionality (store deleted files in trash for X days)
 */

require_once 'document_controller.php';


class Document_FilesController extends DocumentController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        //Setup the user's sub-directory in $USER_DOC_PATH
        $userdir = $GLOBALS['USER_DOC_PATH'] . '/' . $this->context_id . '/';

        if (!file_exists($userdir)) {
            mkdir($userdir, 0755, true);
        }

        //Configurations for the Documentarea for this user
        $this->userConfig = DocUsergroupConfig::getUserConfig($GLOBALS['user']->id);
        if (!empty($this->userConfig)) {
            $measure = $this->userConfig['quota'];
            $this->quota = relsize($measure);
            $measure1 = $this->userConfig['upload_quota'];
            $this->upload_quota = relsize($measure1);
        }
        PageLayout::setTitle(_('Dateiverwaltung'));
        PageLayout::setHelpKeyword('Basis.Dateien');
        Navigation::activateItem('/document/files');
    }

    public function index_action($dir_id = null)
    {
        $dir_id = $dir_id ?: $this->context_id;
        $this->setupInfobox($dir_id);
        try {
            $directory = new DirectoryEntry($dir_id);
            $this->directory = $directory->getFile();
            $this->files     = $this->directory->listFiles();
        } catch (Exception $e) {
            $this->directory = new RootDirectory($this->context_id);
            $this->files     = $this->directory->listFiles();
            $this->parent_id = null;
        }

        if (isset($directory)) {
            try {
                //$this->parent_id = $directory->getParent()->id;
            } catch (Exception $e) {
                $this->parent_id = $this->context_id;
            }
        }

        $this->dir_id = $dir_id;
        $this->marked = $this->flash['marked-ids'] ?: array();
        $this->breadcrumbs = FileHelper::getBreadCrumbs($dir_id);
    }

    public function upload_action($folder_id)
    {
        $folder_id = $folder_id ?: $this->context_id;

        if (Request::isPost()) {
            if ($folder_id === $this->context_id) {
                $directory = new RootDirectory($this->context_id);
            } else {
                $dirEntry = new DirectoryEntry($folder_id);
                $directory = $dirEntry->getfile();
            }
            
            $title       = Request::get('title');
            $description = Request::get('description', '');
            $restricted  = Request::int('restricted', 0);

            $count = count($_FILES['file']['name']);

            $failed = array();
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['file']['error'][$i] !== 0) {
                    $failed[] = array($_FILES['file']['name'][$i], 'remote');
                    continue;
                }

                $filename = $_FILES['file']['name'][$i];
                $filesize = $_FILES['file']['size'][$i];
                $mimetype = $_FILES['file']['type'][$i];
                $tempname = $_FILES['file']['tmp_name'][$i];

                $fileExtension = explode('.', $filename);
                if(!empty($fileExtension) && !empty($this->userConfig['types'])){
                    foreach($this->userConfig['types'] as $typ){
                        if($typ['type']==$fileExtension[count($fileExtension)-1]){
                            $failed[] = array($_FILES['file']['name'][$i], 'forbidden_type');
                        }
                    }
                }

                $restQuota = ( (int)$this->userConfig['quota'] -
                        DiskFileStorage::getQuotaUsage($GLOBALS['user']->id));

                if ($filesize > $restQuota){
                    $failed[] = array($_FILES['file']['name'][$i], 'quota');

                } else if ($filesize > (int)$this->userConfig['upload_quota']){
                       $failed[] = array($_FILES['file']['name'][$i], 'upload_quota');

                } else {
                     while ($directory->getEntry($filename) !== null) {
                        $filename = FileHelper::AdjustFilename($filename);
                    }
                    $this_title = $title;
                    if ($count > 1) {
                        $this_title .= ' ' . sprintf(_('(%u von %u)'), $i + 1, $count);
                    }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       
                    $new_file = $directory->createFile($filename);
                    $new_file->rename($this_title);
                    $new_file->setNewDescription($description);
                    $handle = $new_file->getFile();
                    $handle->setNewRestricted($restricted);
                    $handle->setNewMimeType($mimetype);
                    //echo $filesize;die;
                    $handle->size = $filesize;
                        
                    // TODO: Check if storage path is writable
                    if (!move_uploaded_file($tempname, $handle->getStoragePath())) {
                        $failed[] = array($filename, 'local');
                        $handle->delete();
                    } else {
                        $handle->update();
                    }
                }
            }
            if (!empty($failed)) {
                $remote = array_map('reset', array_filter($failed, function ($item) {
                    return $item[1] === 'remote';
                }));
                if (!empty($remote)) {
                    $message = MessageBox::error(_('Folgende Dateien wurden fehlerhaft hochgeladen:'),
                                                 $remote);
                    PageLayout::postMessage($message);
                }

                $forbidden = array_map('reset', array_filter($failed, function($item) {
                    return $item[1] === 'forbidden_type' ;
                }));
                if (!empty($forbidden)){
                    $message = MessageBox::error(_('Der Upload folgender Dateien ist verboten:'),
                                                 $forbidden);
                    PageLayout::postMessage($message);
                }

                $quota = array_map('reset', array_filter($failed, function($item) {
                    return $item[1] === 'quota' ;
                }));
                if (!empty($quota)){
                    $message = MessageBox::error(_('Für folgende Dateien ist der verbleibende Speicherplatz zu klein:'),
                                                 $quota);
                    PageLayout::postMessage($message);
                }

                $upload = array_map('reset', array_filter($failed, function($item) {
                    return $item[1] === 'upload_quota' ;
                }));
                if (!empty($upload)){
                    $message = MessageBox::error(_('Folgende Dateien sind zu groß für den Upload:'),
                                                 $upload);
                    PageLayout::postMessage($message);
                }

                $local = array_map('reset', array_filter($failed, function ($item) {
                    return $item[1] === 'local';
                }));
                if (!empty($local)) {
                    $message = MessageBox::error(_('Folgende Dateien konnten nicht gespeichert werden:'),
                                                 $remote);
                    PageLayout::postMessage($message);
                }
            }
            if ($count - count($failed) > 0) {
                $message = sprintf(_('%u Dateien wurden erfolgreich hochgeladen.'), $count - count($failed));
                PageLayout::postMessage(MessageBox::success($message));
            }

            $this->redirect('document/files/index/' . $folder_id);
        }

        $this->folder_id = $folder_id;

        $this->setDialogLayout('icons/48/blue/upload.png');

        if (Request::isXhr()) {
            header('X-Title: ' . _('Datei hochladen'));
        }
    }

    private function setDialogLayout($icon = false)
    {
        $layout = $this->get_template_factory()->open('document/dialog-layout.php');
        $layout->icon = $icon;

        if (!Request::isXhr()) {
            $layout->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        $this->set_layout($layout);
    }

    public function edit_action($entry_id)
    {
        $entry = new DirectoryEntry($entry_id);

        if (Request::isPost()) {
            $entry->getFile()->setNewFilename(Request::get('filename'));
            $entry->getFile()->setNewRestricted(Request::int('restricted', 0));
            $entry->rename(Request::get('name'));
            $entry->setNewDescription(Request::get('description'));

            PageLayout::postMessage(MessageBox::success(_('Die Datei wurde bearbeitet.')));
            $this->redirect('document/files/index/' . $this->getParentId($entry_id));
            return;
        }

        $this->entry = $entry;

        if (Request::isXhr()) {
            header('X-Title: ' . _('Datei bearbeiten'));
        }
    }

    public function move_action($file_id, $source_id = null)
    {
        if (Request::isPost()) {
            $folder_id = Request::option('folder_id');

            if ($file_id === 'flashed') {
                $ids = Request::optionArray('file_id');
            } else {
                $ids = array($file_id);
            }

            foreach ($ids as $id) {
                $source_id = $source_id ?: $this->getParentId($file_id);

                $entry = new DirectoryEntry($id);
                $entry->move($folder_id);
            }

            $message = ngettext('Die Datei wurde erfolgreich verschoben', 'Die Dateien wurden erfolgreich verschoben', count($ids));
            PageLayout::postMessage(MessageBox::success($message));

            $this->redirect('document/files/index/' . $source_id);
            return;
        }

        $this->file_id  = $file_id;
        $this->dir_tree = FileHelper::getDirectoryTree($this->context_id);

        if ($file_id === 'flashed') {
            $this->flashed = $this->flash['move-ids'];
            $this->parent_id = $source_id;
        } else {
            $this->parent_id = $this->getParentId($file_id);
        }
        $this->active_folders = array_keys(FileHelper::getBreadCrumbs($this->parent_id));

        try {
            $parent = new DirectoryEntry($this->parent_id);
            $this->parent_file_id = $parent->getFile()->file_id;
        } catch (Exception $e) {
            $this->parent_file_id = $this->context_id;
        }
    }

    public function copy_action($file_id, $source_id = null)
    {
        
         if (Request::isPost()) {
            $folder_id = Request::option('folder_id');
            if ($file_id === 'flashed') {
                $ids = Request::optionArray('file_id');
            } else {
                $ids = array($file_id);
            }
            if ($this->checkCopyQuota($ids)) {
                foreach ($ids as $id) {
                    $source_id = $source_id ? : $this->getParentId($file_id);
                    $entry = new DirectoryEntry($id);
                    $folder = new StudipDirectory($folder_id);
                    $folder->copy(File::get($entry->file_id), $entry->name);
                }
                PageLayout::postMessage(MessageBox::success(_('Die ausgewählten Dateien wurden erfolgreich kopiert')));
            } else {
                PageLayout::postMessage(MessageBox::error(_('Der Kopiervorgang wurde abgebrochen, '.
                    'da Ihnen nicht genügend freier Speicherplatz zur Verfügung steht')));
            }
            
            $this->redirect('document/files/index/' . $source_id);
            return;
        }
        
        
        $this->file_id = $file_id;
        $this->dir_tree = FileHelper::getDirectoryTree($this->context_id);

        if ($file_id === 'flashed') {
            $this->flashed =  $this->flash['copy-ids'];
            $this->parent_id = $source_id;
        } else {
            $this->parent_id = $this->getParentId($file_id);
        }
        $this->active_folders = array_keys(FileHelper::getBreadCrumbs($this->parent_id));

        try {
            $parent = new DirectoryEntry($this->parent_id);
            $this->parent_file_id = $parent->getFile()->file_id;
        } catch (Exception $e) {
            $this->parent_file_id = $this->context_id;
        }
    }
    
    public function checkCopyQuota($ids, $size)
    {
        $copySize = $size;
        for($i = 0; $i<count($ids); $i++){
            $entry = new DirectoryEntry($ids[$i]);
            $file = $entry->getFile();
            if($file->storage_id == ''){
                $folderEntries = $file->listFiles();
                foreach($folderEntries as $entry){
                    $ids[]=$entry->id;
                }
            }else{
                $copySize = $copySize+$file->size;
            }
        }
         $restQuota = $this->userConfig['quota'] - 
                    DiskFileStorage::getQuotaUsage($GLOBALS['user']->id);
        if(($restQuota - $copySize) <= 0){
            return false;
        }
        return true;
    }
    
    public function download_action($entry_id, $inline = false)
    {
        $entry = new DirectoryEntry($entry_id);
        $file  = $entry->getFile();

        if ($file instanceof StudipDirectory) {
            throw new Exception('Cannot download directory');
        }

        $storage = $file->getStorageObject();
        if (!$storage->exists() || !$storage->isReadable()) {
            throw new Exception('Cannot access file');
        }

        $entry->setDownloadCount($entry->downloads + 1);

        $this->initiateDownload($inline, $file->getFilename(), $file->getMimeType(), $file->getSize(), $storage->open('r'));
    }

    public function delete_action($id)
    {
        $entry = new DirectoryEntry($id);
        $parent_id = $this->getParentId($id);

        if (!Request::isPost()) {
            $question = createQuestion2(_('Soll die Datei wirklich gelöscht werden?'),
                                        array(), array(),
                                        $this->url_for('document/files/delete/' . $id));
            $this->flash['question'] = $question;
        } elseif (Request::isPost() && Request::submitted('yes')) {
            File::get($parent_id)->unlink($entry->name);
            PageLayout::postMessage(MessageBox::success(_('Die Datei wurde gelöscht.')));
        }
        $this->redirect('document/files/index/' . $parent_id);
    }

    public function bulk_action($folder_id)
    {
        $ids = Request::optionArray('ids');

        if (empty($ids)) {
            $this->redirect('document/files/index/' . $folder_id);
        } else if (Request::submitted('download')) {
            $this->flash['download-ids'] = $ids;
            $this->redirect('document/folder/download/flashed');
        } else if (Request::submitted('move')) {
            $this->flash['move-ids'] = $ids;
            $this->redirect('document/files/move/flashed/' . $folder_id);
        } else if (Request::submitted('copy')) {
            $this->flash['copy-ids'] = $ids;
            $this->redirect('document/files/copy/flashed/' . $folder_id);
        } else if (Request::submitted('delete')) {
            if (Request::submitted('yes')) {
                if ($folder_id === $this->context_id) {
                    $dir = new RootDirectory($this->context_id);
                } else {
                    $entry = new DirectoryEntry($folder_id);
                    $dir   = $entry->getFile();
                }
                foreach ($ids as $id) {
                    $entry = new DirectoryEntry($id);
                    $dir->unlink($entry->name);
                }
                PageLayout::postMessage(MessageBox::success(_('Die Dateien wurden erfolgreich gelöscht.')));
            } elseif (!Request::submitted('no')) {
                $question = createQuestion2(_('Sollen die markierten Dateien wirklich gelöscht werden?'),
                                            array('delete' => 'true', 'ids' => $ids), array(),
                                            $this->url_for('document/files/bulk/' . $folder_id));
                $this->flash['question']   = $question;

                $this->flash['marked-ids'] = $ids;
            }

            $this->redirect('document/files/index/' . $folder_id);
        }
    }

    public function getParentId($entry_id)
    {
        try {
            $entry  = new DirectoryEntry($entry_id);
            $parent = $entry->getParent();
            $parent_id = $parent->id;
        } catch (Exception $e) {
            $parent_id = $this->context_id;
        }
        return $parent_id;
    }

    private function setupInfobox($current_dir)
    {
        $this->setInfoboxImage('sidebar/files-sidebar.png');
        if($this->userConfig['forbidden'] == 0){
            $upload_link = sprintf('<a href="%s" rel="lightbox">%s</a>',
                               $this->url_for('document/files/upload/' . $current_dir),
                               _('Datei hochladen'));
        } else {
            $upload_link = sprintf('<a class="tooltip">' .
                                   '%s<span>%s</span></a>',
                                   '<strike>'._('Datei hochladen').'</strike>',
                                   _('Ihre Upload-Funktion wurde geperrt. Wenden Sie sich an den Support: '));
        }

        $this->addToInfobox(_('Aktionen:'),
                            $upload_link,
                            'icons/16/black/upload.png');

        $add_dir_link = sprintf('<a href="%s" rel="lightbox">%s</a>',
                                $this->url_for('document/folder/create/' . $current_dir),
                                _('Neuen Ordner erstellen'));
        $this->addToInfobox(_('Aktionen:'),
                            $add_dir_link,
                            'icons/16/black/add/folder-empty.png');

        $delete_link = sprintf('<a href="%s">%s</a>',
                               $this->url_for('document/folder/delete/all'),
                               _('Dateibereich leeren'));
        $this->addToInfobox(_('Aktionen:'),
                            $delete_link,
                            'icons/16/black/trash.png');

        $export_link = sprintf('<a href="%s">%s</a>',
                               $this->url_for('document/folder/download/' . $this->context_id),
                               _('Dateibereich herunterladen'));
        $this->addToInfobox(_('Export:'), $export_link, 'icons/16/black/download.png');

        $this->addToInfobox(_('Speicherplatz:'),
                ((int)((DiskFileStorage::getQuotaUsage($GLOBALS['user']->id)/
                        (int)$this->userConfig['quota'])*100)) . sprintf('%s',  '% belegt') .
                        ' (' . relsize(DiskFileStorage::getQuotaUsage($GLOBALS['user']->id), false) .
                        '/' . relsize($this->userConfig['quota'], false) . ')',
                'icons/16/black/stat.png');
    }
}
