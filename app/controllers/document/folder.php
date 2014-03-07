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

require_once 'app/controllers/authenticated_controller.php';


class Document_FolderController extends AuthenticatedController
{

    private $realname, $userConfig, $quota, $upload_quota;

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

        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
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
                $parent_dir = new RootDirectory($GLOBALS['user']->id);
            }
            $directory = $parent_dir->mkdir($name);
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

    public function getParentId($entry_id)
    {
        try {
            $entry  = new DirectoryEntry($entry_id);
            $parent = $entry->getParent();
            $parent_id = $parent->id;
        } catch (Exception $e) {
            $parent_id = $GLOBALS['user']->id;
        }
        return $parent_id;
    }
}
