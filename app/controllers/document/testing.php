<?
/**
 * Document_testingController
 *
 * @author      Stefan Osterloh s.osterloh@uni-oldenburg.de
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// administration.php
//
// Copyright (C) 2013 s.osterloh@uni-oldenburg.de
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

require_once 'app/controllers/authenticated_controller.php';

class Document_testingController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
    }

    public function index_action() {
        if (Request::submitted('create')) {
            $file = StudipDirectory::create('testdatei', 0);
            echo '<pre>';
            var_dump($file);
            echo '</pre>';
            //die;
            //$this->test = $_FILES['datei']['tmp_name'];
        }
        if (Request::submitted('mkdir')) {
            $dir = StudipDirectory::mkdir('testordner', 0);
            echo '<pre>';
            var_dump($dir);
            echo '</pre>';
        }
        if (Request::submitted('list')) {
            $list = new StudipDirectory('f0c49ec506dfdccb6041223845d49342');
            echo '<pre>';
            var_dump($list);
            echo '</pre>';
        }
        if (Request::submitted('test')) {

            $root = StudipDirectory::getRootDirectory(md5('foo'));
            $test = $root->getEntry('subfolder2');
            // create a test folder and file
            echo '<pre>';
            echo 'root-directory';
            var_dump($root);
            echo 'testtesttest';
            var_dump($test);
            //$folder_entry = $root->mkdir('folder');
            //$folder_entry->setDescription('test folder');
            //echo 'neuer Ordner directory';
            //var_dump($folder_entry);
            die;
            echo 'directory';
            //$folder = $folder_entry->getFile();
            //var_dump($folder);
            $newRoot = StudipDirectory::getRootDirectory('2b44c4f8baa5c774c0b15e30b142cfeb');
            var_dump($newRoot);
            //$newFolderEntry = $newRoot->mkdir('subfolder2');
            //$newFolderEntry->setDescription('new new supfolder');
            
            
            
            /*
            $file_entry = $folder->create('file');
            $file_entry->setDescription('test file');
            $file = $file_entry->getFile();

            $stream = $file->open('wb');
            fputs($stream, "Hello, world!\n");
            fclose($stream);
*/
/* create a copy of the file
            $folder->copy($file, 'copy');

// print content of the test file
            $folder_entry = $root->getEntry('folder');
            var_dump($folder_entry);
            $folder = $folder_entry->getFile();

            $file_entry = $folder->getEntry('file');
            var_dump($file_entry);
            $file = $file_entry->getFile();

            $stream = $file->open('rb');
            fpassthru($stream);
            fclose($stream);

/* print content of the copy
            $file_entry = $folder->getEntry('copy');
            var_dump($file_entry);
            $file = $file_entry->getFile();

            $stream = $file->open('rb');
            fpassthru($stream);
            fclose($stream);

// remove the test folder and file
            $entries = $root->listFiles();

            foreach ($entries as $entry) {
                $root->unlink($entry->getName());
            }
     */   
            echo '</pre>';
     }
    }

}