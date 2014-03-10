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
                $this->parent_id = $directory->getParent()->id;
            } catch (Exception $e) {
                $this->parent_id = $this->context_id;
            }
        }

        $this->dir_id = $dir_id;
        $this->marked = $this->flash['marked-ids'] ?: array();
    }

    public function upload_action($env_dir)
    {
        $env_dir = $env_dir ?: $this->context_id;

        if (Request::isPost()) {

            if (isset ($_FILES['upfile']['tmp_name'])) {
                $upfile = $_FILES['upfile']['name'];
                $size = $_FILES['upfile']['size'];
                $type = $_FILES['upfile']['type'];
                $tmp_name = $_FILES['upfile']['tmp_name'];

                if ($env_dir === $this->context_id) {
                    $user_dir = new RootDirectory($this->context_id);
                } else {
                    $dirEntry = new DirectoryEntry($env_dir);
                    $user_dir = $dirEntry->getfile();
                }

                while ($user_dir->getEntry($upfile) !== null) {
                    $upfile = FileHelper::AdjustFilename($upfile);
                }

                $new_file = $user_dir->create($upfile);
                $new_file->rename(Request::get('name'));
                $new_file->setDescription(Request::get('description', ''));
                $handle = $new_file->getFile();
                $handle->setRestricted(Request::int('restricted'));
                $handle->setMimeType($type);
                $handle->size = $size;

                // TODO: Check if storage path is writable
                if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $handle->getStoragePath())) {
                    PageLayout::postMessage(MessageBox::error(_('Upload-Fehler')));
                    $handle->delete();
                } else {
                    $handle->update();
                }
            }
            $this->redirect('document/files/index/' . $env_dir);
        }

        $this->env_dir = $env_dir;

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
            $entry->getFile()->setFilename(Request::get('filename'));
            $entry->getFile()->setRestricted(Request::int('restricted', 0));
            $entry->rename(Request::get('name'));
            $entry->setDescription(Request::get('description'));

            PageLayout::postMessage(MessageBox::success(_('Die Datei wurde bearbeitet.')));
            $this->redirect('document/files/index/' . $this->getParentId($entry_id));
            return;
        }

        $this->entry = $entry;

        if (Request::isXhr()) {
            header('X-Title: ' . _('Datei bearbeiten'));
        }
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
            $question = createQuestion2(_('Soll die Datei wirklich gel�scht werden?'),
                                        array(), array(),
                                        $this->url_for('document/files/delete/' . $id));
            $this->flash['question'] = $question;
        } elseif (Request::isPost() && Request::submitted('yes')) {
            File::get($parent_id)->unlink($entry->name);
            PageLayout::postMessage(MessageBox::success(_('Die Datei wurde gel�scht.')));
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
                PageLayout::postMessage(MessageBox::success(_('Die Dateien wurden erfolgreich gel�scht.')));
            } elseif (!Request::submitted('no')) {
                $question = createQuestion2(_('Sollen die markierten Dateien wirklich gel�scht werden?'),
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

    public function getBreadCrumbs($entry_id)
    {
        $crumbs = array();

        do {
            try {
                $entry = new DirectoryEntry($entry_id);
                $crumbs[] = array(
                    'id'   => $entry_id,
                    'name' => $entry->getFile()->filename,
                );
                $entry_id = $this->getParentId($entry_id);
            } catch (Exception $e) {
            }
        } while ($entry_id !== $this->context_id);

        $crumbs[] = array(
            'id'   => $this->context_id,
            'name' => _('Hauptverzeichnis'),
        );

        return array_reverse($crumbs);
    }

    private function setupInfobox($current_dir)
    {
        $this->setInfoboxImage('infobox/folders.jpg');

        $upload_link = sprintf('<a href="%s" rel="lightbox">%s</a>',
                               $this->url_for('document/files/upload/' . $current_dir),
                               _('Datei hochladen'));
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
    }
}
