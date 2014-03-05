<?php

/**
 * dateien.php 
 * 
 * Der Controller stellt angemeldeten Benutzer/innen einen Dateimanager
 * fuer deren persoenlichen Dateibereich im Stud.IP zur Verfuegung.   
 *
 *
 * TODO:
 * 
 * - Lifter 010: Unterstuetzung einer barrierefreien Nutzung
 * 
 *
 * @category    Stud.IP
 * @version     3.1
 * 
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0
 * @copyright   2014 Carl von Ossietzky Universitaet Oldenburg
 */


require_once 'app/controllers/authenticated_controller.php';


class Document_DateienController extends AuthenticatedController {  
    
    private $realname, $userConfig, $quota, $upload_quota;
   
    public function before_filter(&$action, &$args) {        
        global $USER_DOC_PATH;
     
        parent::before_filter($action, $args);    
        Navigation::activateItem('/document/dateien');
         
        //Setup the user's sub-directory in $USER_DOC_PATH
        $userdir = $USER_DOC_PATH.'/'.$GLOBALS['user']->id.'/';
        
        if (!file_exists($userdir))
            mkdir($userdir, 0744, true); 
                   
        //Configurations for the Documentarea for this user 
        $this->userConfig = DocUsergroupConfig::getUserConfig($GLOBALS['user']->id);
        
        if (!empty($this->userConfig)) {
            $measure = $this->userConfig['quota'];
            $this->quota = $this->resize($measure);
            $measure1 = $this->userConfig['upload_quota'];
            $this->upload_quota = $this->resize($measure1);
        }
        
        //Retrieve the user's realname
        $user = new StudipUser;
        $surname = $user->getSurname($GLOBALS['user']->id);
        $givenname = $user->getGivenname($GLOBALS['user']->id);
        $this->realname = $givenname. ' '. $surname;
        
        PageLayout::setTitle(_('Dateiverwaltung'));
        PageLayout::setHelpKeyword('Basis.Dateien');      
        PageLayout::addStylesheet('/stylesheets/jquery-ui-studip.css');
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }
   
    public function index_action() {
        $user_root = $GLOBALS['user']->id;
        $this->redirect("document/dateien/list/$user_root");
    }
    
    public function list_action($dir_id) {
        
        if ($dir_id == $GLOBALS['user']->id) {
            $user_dir = new RootDirectory($GLOBALS['user']->id);
            $this->flash['env_dirname'] = 'Root-Verzeichnis';
            $this->flash['up_dir'] = 'user_root';
        } 
        else {
            $sub_dir = new DirectoryEntry($dir_id);
            $user_dir = StudipDirectory::get($sub_dir->file_id);
            $handle = File::get($user_dir->file_id);
            $this->flash['env_dirname'] = $handle->filename;

            if ($sub_dir->parent_id === $GLOBALS['user']->id) {
                $this->flash['up_dir'] = $GLOBALS['user']->id;
            }
            else {
                $this->flash['up_dir'] = $sub_dir->getParent()->id;
            }
        }

        $files = $user_dir->listFiles();

        $i = 0;
        foreach ($files as $entry) {
            $item = File::get($entry->file_id);
            $inhalt[$i]['ord'] = $i;
            $inhalt[$i]['id'] = $entry->id;
            $inhalt[$i]['file_id'] = $entry->file_id;
            $inhalt[$i]['type'] = $item->getEntryType();
            $inhalt[$i]['name'] = $item->filename;
            $inhalt[$i]['lock'] = 'locked';
            $inhalt[$i]['autor'] = $this->realname;
            $timestamp = $item->getModificationTime();
            $inhalt[$i]['date'] = $this->transformDate($timestamp);
            $inhalt[$i]['title'] = $entry->name;    //$entry->title;
            $inhalt[$i]['script'] = $entry->description;
            $i++;
        }

        if (empty($inhalt))
            $this->flash['count'] = -1;
        else
            $this->flash['count'] = --$i;

        $this->flash['env'] = $dir_id;
        $this->flash['inhalt'] = $inhalt;
        
        $this->flash['realname'] = $this->realname;
        
        $this->flash['quota'] = $this->quota;
        $this->flash['upload_quota'] = $this->upload_quota;
        $this->flash['closed'] = $this->userConfig['area_close'];
        $this->flash['notification'] = $this->userConfig['area_close_text'];
       
        $this -> render_action('index');
    }
    
    public function openDir_action($sub_dir) {
        $this->redirect("document/dateien/list/$sub_dir"); 
    }
    
    public function up_action($up_dir) {
        $this->redirect("document/dateien/list/$up_dir");  
    }
    
    public function addDir_action($env_dir) {

        if(Request::submitted('mkdir')) {   
            $dir_name = $_POST['dirname'];
            $result = $this->verifyName($dir_name);

            if ($result == 'ok') {
                      
                if ($env_dir == $GLOBALS['user']->id) {
                    $user_dir = new RootDirectory($GLOBALS['user']->id);
                }
                else {
                    $dirEntry = new DirectoryEntry($env_dir);
                    $user_dir = StudipDirectory::get($dirEntry->file_id);    
                }      
                    
                $new_dir = $user_dir->mkdir($dir_name);
                    
                /*if (!$new_dir) { 
                    PageLayout::postMessage(MessageBox::error(_("Ein Ordner '". $dir_name. 
                    "' ist bereits vorhanden.")));
                }
                else {
                    $new_dir->setDescription($_POST['description']);
                }*/
                    
                $new_dir->setDescription($_POST['description']);
                $new_dir->rename($_POST['title']);    //$new_file->setTitle($_POST['title']);
                $handle = File::get($new_dir->file_id);
                $handle->setFilename($dir_name);
            }
        }
        $this->redirect("document/dateien/list/$env_dir");           
    }
    
    public function upload_action($env_dir) {
     
        if(Request::submitted('upload')) {
            
            if (isset ($_FILES['upfile']['tmp_name'])) {
                $upfile = $_FILES['upfile']['name'];
                $size = $_FILES['upfile']['size'];
                $type = $_FILES['upfile']['type'];
                $tmp_name = $_FILES['upfile']['tmp_name'];
                
                if ($env_dir == $GLOBALS['user']->id) {
                    $user_dir = new RootDirectory($GLOBALS['user']->id);
                }
                else if ($env_dir != $GLOBALS['user']->id) {
                    $dirEntry = new DirectoryEntry($env_dir);                    
                    $user_dir = StudipDirectory::get($dirEntry->file_id);
                } 
                   
                $exist = $user_dir->getEntry($upfile);    
                    
                //if (!is_null($exist)) {
                //    $newname = $this->nameFactory($env_dir, $upfile);
                //    $upfile = $newname;
                //}
                     
                $new_file = $user_dir->create($upfile);
                $new_file->rename($_POST['title']);    //$new_file->setTitle($_POST['title']);
                $new_file->setDescription($_POST['description']);
                $handle = File::get($new_file->file_id);
                $handle->setFilename($upfile);
                $handle->setMimeType($type); 
              
                $newEntry = File::get($new_file->file_id);
                //$file_path = $newEntry->getStoragePath();
             
                try {
                    $file_path = $newEntry->getStoragePath();
                }
                catch (Exception $e) {
                    echo 'Fehler: '. $e->getMessage(). '<br />';
                }
                
                if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $file_path)) {
                    //PageLayout::postMessage(MessageBox::error(_('Upload-Fehler')));
                    $newEntry->delete();
                }
            }
        } 
        $this->redirect("document/dateien/list/$env_dir");
    }
     
    public function edit_action($env_dir) { 
        
        if (Request::submitted('edit')) {
            $file_id = $_POST['editFileId'];
            $id = $_POST['editId'];  
            $entry = File::get($file_id);
            $entry->setFilename($_POST['editName']);      
            $entryRef = new DirectoryEntry($id);
            $entryRef->rename($_POST['editTitle']);
            $entryRef->setDescription($_POST['editScript']);
        }
        $this->redirect("document/dateien/list/$env_dir");
    }
    
    public function copyTo_action($id, $parent_id=NULL) {
        
        /*
        $directory = StudipDirectory::get($id);
        $directory->copy($_SESSION['document-copy']['file_refs'], $_SESSION['document']['files']->name);
        unset($_SESSION['document-copy']['file_refs']);
        unset($_SESSION['document-copy']['files']);
        */
     
        $this->redirect('document/dateien/list'. $parent_id);
    }
    
    public function moveTo_action($id, $parent_id) {
        
        /*
        $directory = new DirectoryEntry($id);
        $directory->move($_SESSION['document-copy']['file_refs']);
        unset($_SESSION['document-copy']['file_refs']);
        unset($_SESSION['document-copy']['files']);
        */
        
        $this->redirect('document/dateien/list'. $parent_id);
    }
    
    public function copy($id, $parent_id) {

        /*
        $_SESSION['document-copy']['files'] = new DirectoryEntry($id);
        $_SESSION['document-copy']['file_refs'] = File::get($_SESSION['document-copy']['files']->file_id);
        */
     
        $this->redirect('document/dateien/list'. $parent_id);
    }
    
    public function download_action($file_id, $id, $env_dir) {
        
        global $USER_DOC_PATH; 
        $path = $USER_DOC_PATH.'/'. $GLOBALS['user']->id. '/';
        
        $file = File::get($file_id);
        
        if($file->getEntryType() == "Datei") {
            chdir($path); 
            $handle = opendir($path);
            $storage_id = $file->storage_id;  
            $filename = $file->filename;    
            $entry = new DirectoryEntry($id);     
            $pos = strrpos($filename, ".");
    
            if ($pos !== false)
                $pre = substr($filename, 0, $pos);
           
            if (file_exists($storage_id)) {                  
                header('Content-Type: application/unknown');
                header("Content-Disposition: attachment; filename = $pre");
                readfile($storage_id);
            
                $count = $entry->getDownloadCount();
                
                echo '<pre>'; var_dump($count); die;
                
                $count++;
                $entry->setDownloadCount($count);
            }

            closedir($handle);
        }
        else
         $this->redirect("document/dateien/list/$env_dir");
    }

    public function remove_action($env_dir) {
     
        if (Request::submitted('remove')) {

            $file_id = $_POST["rm_item"];  
            $entry = File::get($file_id);
            
            if ($entry->getEntryType() == 'Datei') {
                $file_path = $entry->getStoragePath();
                unlink($file_path);
            }
               
            $entry->delete();                  
            $this->redirect("document/dateien/list/$env_dir");    
        }
    }
    
    private function resize($bytes) {
        if ($bytes >= 1073741824) {
            $groesse = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576) {
            $groesse = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024) {
            $groesse = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1) {
            $groesse = $bytes . ' Bytes';
        }
        elseif ($bytes == 1) {
            $groesse = $bytes . ' Byte';
        }
        else {
            $groesse = '0 Byte';
        }
        return $groesse;
    }
    
    private function transformDate($timestamp) {
         setlocale(LC_TIME, "de_DE");
         $day = strftime("%d.%m.%y", $timestamp);
         $time = strftime("%X", $timestamp);
         $new_form = $day. ' - '. $time;
         return $new_form;
    }
    
    private function verifyName($name) { 
       if (!(strpos($name, '/') === false)) {
           $ergebnis = "slash";
       }
       else if(!(strpos($name, '\\') === false)) {
           $ergebnis = "backslash";
       }
       //else if(!(strpos($name, '(') === false) || !(strpos($name, ')') === false)) {
       //    $ergebnis = "klammern";
       //}
       else if (strlen($name) > 256) {
           $ergebnis = "max";
       }
       else {
           $ergebnis = "ok";
       }
       return $ergebnis;
    }
    
   private function checkFile($file) {
       //$eintrag = $envDir. "/". $dname;
    
       //if (file_exists($eintrag))
       //   return "eintrag vorhanden";
       //else
       //   return "ok";
      
       //Datei-Kontrollfunktionen:
       // -Quota
       // -Dateigroesse
       // -Dateityp
   }
   
  private function nameFactory($env_dir, $name) {
      /* 
      if ($env_dir == $GLOBALS['user']->id) {
          $user_root = new RootDirectory($GLOBALS['user']->id);
          $dir = $user_root->listFiles();
      }
      else {
          $sub_dir = new DirectoryEntry($env_dir);        
          $user_dir = StudipDirectory::get($sub_dir->file_id);
          $dir = $user_dir->listFiles();
      }
   
      $max = 0;
      foreach ($dir as $entry) {
           $inhalt = $entry->getName($name);
           
           if (strrpos($name) !== false)
               $result[$i] = $inhalt;
           
           $max++;  
      }
      
      for ($i = 0; $i <= $max; $i++) {
       
      }
      
                   
     $pos = strrpos($name, '.');
    
     if ($pos !== false) {
         $pre = substr($name, 0, $pos);
         $post = substr($name, $pos);
           
         if (strrpos($pre, '(1)') === false) {
             $fact = $pre. '(1)'. $post;
         }
         else {
             $length = strlen($post);
             //$number = $post[$length-1];
             //$number++;
             //$post[$lenght-1] = $number;
             $fact1 = 'ok'; //$pre. $post;
         }
     }
     else if (strrpos($name, '(1)') !== false) {
          $fact = $name. '(1)';          
     }
     else {
         $length = $name;
         $number = $name[$length-1];
         $number++;
         $name[$length-1] = $number;
         $fact = $name; 
     } 
     
     echo '<pre>'; var_dump($name); die;
     return $fact;
     */
  }
}
