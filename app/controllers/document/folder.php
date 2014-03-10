<?php

/**
 * files.php
 *
 * Der Controller stellt angemeldeten Benutzer/innen einen Dateimanager
 * fuer deren persoenlichen Dateibereich im Stud.IP zur Verfuegung.
 *
 *
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-3.0
 * @copyright   Stud.IP Core-Group
 * @since       3.1
 */

require_once 'document_controller.php';


class Document_FolderController extends DocumentController
{

    private $realname, $realnameConfig, $quota, $upload_quota;

    public function before_filter(&$action, &$args)
    {
        global $USER_DOC_PATH;

        parent::before_filter($action, $args);

        Navigation::activateItem('/document/files');

        //Setup the user's sub-directory in $USER_DOC_PATH
        $userdir = $USER_DOC_PATH.'/'.$GLOBALS['user']->id.'/';

        if (!file_exists($userdir)) {
            mkdir($userdir, 0755, true);
        }
    }

    public function create_action($parent_id)
    {
        $this->parent_id = $parent_id;

        if (Request::isPost()) {
            $name = Request::get('name');

            try {
                $entry = new DirectoryEntry($parent_id);
                $parent_dir = $entry->getFile();
            } catch (Exception $e) {
                $parent_dir = new RootDirectory($this->context_id);
            }

            do {
                $check = true;
                try {
                    $directory = $parent_dir->mkdir($name);
                } catch (Exception $e) {
                    $check = false;

                    $name = FileHelper::AdjustFilename($name);
                }
            } while (!$check);

            $directory->setDescription(Request::get('description', ''));
            $directory->getFile()->setFilename($name);

            PageLayout::postMessage(MessageBox::success(_('Der Ordner wurde erstellt.')));
            $this->redirect('document/files/index/' . $directory->id);
        }
    }
    
    public function edit_action($folder_id)
    {
        $folder    = new DirectoryEntry($folder_id);
        $parent_id = $this->getParentId($folder_id);
        
        if (Request::isPost()) {
            $folder->rename(Request::get('name'));
            $folder->setDescription(Request::get('description'));
            $folder->getFile()->setFilename(Request::get('name'));
            
            PageLayout::postMessage(MessageBox::success(_('Der Ordner wurde bearbeitet.')));
            $this->redirect('document/files/index/' . $parent_id);
        }
        
        if (Request::isXhr()) {
            header('X-Title: ' . _('Ordner bearbeiten'));
        }
        
        $this->folder_id = $folder_id;
        $this->folder    = $folder;
    }
    
    public function delete_action($folder_id)
    {
        $parent_id = $this->getParentId($folder_id);
        
        if (!Request::isPost()) {
            $message = $folder_id === 'all'
                     ? _('Soll der gesamte Dateibereich inklusive aller Order und Dateien wirklich gelöscht werden?')
                     : _('Soll der Ordner inklusive aller darin enthaltenen Dateien wirklich gelöscht werden?');
            $question = createQuestion2($message, array(), array(),
                                        $this->url_for('document/folder/delete/' . $folder_id));
            $this->flash['question'] = $question;
        } elseif (Request::isPost() && Request::submitted('yes')) {
            if ($folder_id === 'all') {
                $entry = new RootDirectory($this->context_id);
                foreach ($entry->listFiles() as $file) {
                    $entry->unlink($file->name);
                }
                PageLayout::postMessage(MessageBox::success(_('Der Dateibereich wurde geleert.')));
            } else {
                $entry = new DirectoryEntry($folder_id);
                File::get($parent_id)->unlink($entry->name);
                PageLayout::postMessage(MessageBox::success(_('Der Ordner wurde gelöscht.')));
            }
        }
        $this->redirect('document/files/index/' . $parent_id);
    }
    
    public function download_action($folder_id)
    {
        $entries = array();
        
        if ($folder_id === 'flashed') {
            $ids = $this->flash['download-ids'];
            foreach ($ids as $id) {
                $entry     = new DirectoryEntry($id);
                $entries[] = $entry->getFile();
            }
        } else {
            if ($folder_id === $this->context_id) {
                $entries[] = new RootDirectory($this->context_id);
            } else {
                $entry     = new DirectoryEntry($folder_id);
                $entries[] = $entry->getFile();
            }
        }
        $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');
        $zip = new ZipArchive();
        $open_result = $zip->open($tmp_file, ZipArchive::CREATE);
        if (true !== $open_result) {
            throw new Exception('Could not create zip file (' . $open_result . ')');
        }

        foreach ($entries as $entry) {
            $this->addToZip($zip, $entry, '', $remove);
        }
        if (true !== ($close_result = $zip->close())) {
            throw new Exception('Could not close zip file (' . $close_result . ')');
        }

        array_map('unlink', $remove);

        // TODO: swap "foobar" with a more appropriate name
        $this->initiateDownload(false, 'foobar.zip', 'application/zip', filesize($tmp_file), fopen($tmp_file, 'r'));
        $this->download_remove = $tmp_file;
    }
    
    protected function addToZip(&$zip, $entry, $path = '', &$remove = array())
    {
        $path = rtrim($path, '/');
        if ($entry instanceof StudipDirectory) {
            $path = ltrim($path . '/' . $entry->filename, '/');
            if ($path && true !== $zip->addEmptyDir($path)) {
                throw new Exception('Can not add dir "' . $path . '"');
            }
            foreach ($entry->listFiles() as $file) {
                $this->addToZip($zip, $file->getFile(), $path, $remove);
            }
        } else {
            $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'zip');
            $source      = $entry->open('r');
            $destination = fopen($tmp_file, 'w+');

            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);

            if (!file_exists($tmp_file) || !is_readable($tmp_file) || filesize($tmp_file) === 0) {
                throw new Exception('Empty file');
            }
            if (true !== $zip->addFile($tmp_file, $path . '/' . $entry->filename)) {
                throw new Exception('Could not add file');
            }
            $remove[] = $tmp_file;
        }
    }
}
