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


class Document_FilesController extends AuthenticatedController
{
    private $download_handle = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

		// Lock context to user id
		$this->context_id = $GLOBALS['user']->id;

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


        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
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

                if ($env_dir == $this->context_id) {
                    $user_dir = new RootDirectory($this->context_id);
                } else if ($env_dir != $this->context_id) {
                    $dirEntry = new DirectoryEntry($env_dir);
                    $user_dir = StudipDirectory::get($dirEntry->file_id);
                }

                $fileEntry = $user_dir->getEntry($upfile);

                // TODO: Refactor this
                $i = 1;
                while (!is_null($fileEntry)) {
                    $pos = strrpos($name, '.');

                    if ($pos !== false) {
                        $pre = substr($name, 0, $pos);
                        $post = substr($name, $pos);
                        $newname = $pre. '('. $i. ')';
                        $upfile = $ext. $post;
                    }
                    else {
                        $newname = $upfile. '('. $i. ')';
                        $upfile = $newname;
                    }
                    $i++;
                }

                $new_file = $user_dir->create($upfile);
                $new_file->rename($_POST['title']);
                $new_file->setDescription($_POST['description']);
                $handle = $new_file->getFile();
                $handle->setMimeType($type);
                $handle->size = $size;

                if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $handle->getStoragePath())) {
                    //PageLayout::postMessage(MessageBox::error(_('Upload-Fehler')));
                    $handle->delete();
                } else {
                    $handle->update();
                }
            }
            $this->redirect("document/files/index/$env_dir");
        }

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

        $response = $this->response;

        if ($_SERVER['HTTPS'] === 'on') {
            $response->add_header('Pragma', 'public');
            $response->add_header('Cache-Control', 'private');
        } else {
            $response->add_header('Pragma', 'no-cache');
            $response->add_header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        $dispositon = sprintf('%s;filename="%s"',
                              $inline ? 'inline' : 'attachment',
                              urlencode($file->getFilename()));
        $response->add_header('Content-Disposition', $dispositon);
        $response->add_header('Content-Description', 'File Transfer');
        $response->add_header('Content-Transfer-Encoding' , 'binary');
        $response->add_header('Content-Type', $file->getMimeType());
        $response->add_header('Content-Length', $file->getSize());

        $this->render_nothing();

        $this->download_handle = $storage->open('r');
    }

    public function after_filter($action, $args)
    {
        parent::after_filter($action, $args);

        if ($this->download_handle) {
            fpassthru($this->download_handle);
            fclose($this->download_handle);
        }
    }

    /**
     * @todo This needs to use StudipDirectory::unlink()
     */
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
            $entry->getFile()->delete();
            PageLayout::postMessage(MessageBox::success(_('Die Datei wurde gelöscht.')));
        }
        $this->redirect('document/files/index/' . $parent_id);
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
                               $this->url_for('document/files/remove/all'),
                               _('Dateibereich leeren'));
        $this->addToInfobox(_('Aktionen:'),
                            $delete_link,
                            'icons/16/black/trash.png');

        $this->addToInfobox(_('Export:'), _('Dateibereich herunterladen'), 'icons/16/black/download.png');
    }

	public function getIcon($mime_type)
	{
		if (strpos($mime_type, 'image/') === 0) {
			return 'file-pic.png';
		}
		if (strpos($mime_type, 'audio/') === 0) {
			return 'file-audio.png';
		}
		if (strpos($mime_type, 'video/') === 0) {
			return 'file-video.png';
		}
		if ($mime_type === 'application/pdf') {
			return 'file-pdf.png';
		}
		if ($mime_type === 'application/vnd.ms-powerpoint') {
			return 'file-presentation.png';
		}

		$parts = explode('/', $mime_type);
		if (reset($parts) === 'application' && in_array(end($parts), words('vnd.ms-excel msexcel x-msexcel x-ms-excel x-excel x-dos_ms_excel xls x-xls'))) {
			return 'file-xls.png';
		}
		if (reset($parts) === 'application' && in_array(end($parts), words('7z arj rar zip'))) {
			return 'file-archive.png';
		}
		return 'file-generic.png';
	}
}
