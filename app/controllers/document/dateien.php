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
    
    private $realname, $userConfig, $quota;
   
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
            $user_root = new RootDirectory($GLOBALS['user']->id);
            $dir = $user_root->listFiles();
               
            $i = 0;
            foreach ($dir as $entry) {
                $item = File::get($entry->file_id);
                $inhalt[$i]['ord'] = $i;
                $inhalt[$i]['id'] = $entry->id;          
                $inhalt[$i]['type'] = $item->getEntryType();
                $inhalt[$i]['name'] = $entry->getName();
                $inhalt[$i]['lock'] = 'locked';
                $inhalt[$i]['autor'] = $this->realname;
                $timestamp = $item->getModificationTime();                
                $inhalt[$i]['date'] = $this->transformDate($timestamp);
                $i++;  
            }
                   
            if (empty($dir))
                $this->flash['count'] = -1;
            else 
                $this->flash['count'] = --$i;

            $this->flash['up_dir'] = 'user_root';   
        }
        else {
            $sub_dir = new DirectoryEntry($dir_id);
            $user_dir = StudipDirectory::get($sub_dir->file_id);
            $dir = $user_dir->listFiles();
                    
            $i = 0;
            foreach ($dir as $entry) {
                $item = File::get($entry->file_id);
                $inhalt[$i]['ord'] = $i;
                $inhalt[$i]['id'] = $entry->id;          
                $inhalt[$i]['type'] = $item->getEntryType();
                $inhalt[$i]['name'] = $entry->getName();
                $inhalt[$i]['lock'] = 'locked';
                $inhalt[$i]['autor'] = $this->realname;
                $timestamp = $item->getModificationTime();                
                $inhalt[$i]['date'] = $this->transformDate($timestamp);
                $i++;  
            }
                   
            if (empty($dir))
                $this->flash['count'] = -1;
            else 
                $this->flash['count'] = --$i;
                
            $this->flash['up_dir'] = $sub_dir->getParent()->id ?: $GLOBALS['user']->id;       
        }
        
        $this->flash['env'] = $dir_id;
        $this->flash['inhalt'] = $inhalt;
        $this->flash['quota'] = $this->quota;
        $this->flash['closed'] = $this->userConfig['area_close'];
        $this->flash['notification'] = $this->userConfig['area_close_text'];
        $this -> render_action('index');
    }
    
    public function openDir_action($sub_dir) {
        $this->flash['id'] = $sub_dir;
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
                    $user_root = new RootDirectory($GLOBALS['user']->id);
                    $new_dir = $user_root->mkdir($dir_name);
                    $new_dir->setDescription($_POST['description']);
                }
                else {
                    $dirEntry = new DirectoryEntry($env_dir);
                    $sub_dir = StudipDirectory::get($dirEntry->file_id);
                    $new_dir = $sub_dir->mkdir($dir_name);
                    $new_dir->setDescription($_POST['description']);
                }
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
                    $user_root = new RootDirectory($GLOBALS['user']->id);
                    $new_file = $user_root->create($upfile, $type); 
                    $new_file->setDescription($_POST['description']);
                } 
                else if ($env_dir != $GLOBALS['user']->id) {
                    $dirEntry = new DirectoryEntry($env_dir);
                    $sub_dir = StudipDirectory::get($dirEntry->file_id);
                    $new_file = $sub_dir->create($upfile, $type);
                    $new_file->setDescription($_POST['description']);
                }
            }
            else {
                PageLayout::postMessage(MessageBox::error(_('FEHLER')));
            }
        
            $newEntry = File::get($new_file->file_id);
            $storage = $newEntry->getStoragePath(); 
            
           if (move_uploaded_file($_FILES['upfile']['tmp_name'], $storage. $upfile)) {
               PageLayout::postMessage(MessageBox::success(_('Datei erfolgreich hochgeladen')));
           }
           else {
               PageLayout::postMessage(MessageBox::error(_('Upload-Fehler')));
               $newEntry->delete();
           } 
        } 
        $this->redirect("document/dateien/list/$env_dir");
    }
     
    public function edit_action($id, $parent_id) { 
        if (Request::submitted('edit')) {
            
            /*
            $directory = new DirectoryEntry($id);
            $directory->setDescription($_POST['description']);
            */
        }
        $this->redirect('document/dateien/list');
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
    
    public function remove_action($file_id, $parent_id = NULL) {    
        /* 
        if (Request::submitted('remove')) {
            $entry = File::get($file_id);
            $entry->delete();
        
            if (isset($parent_id)) {
                $this->redirect('document/dateien/list/'. $parent_id);
            else
                $this->redirect('document/dateien/list');
            }
        }*/
    }
    
    public function download_action($item, $name) {
        
        /* 
        switch ($item) {
            case "datei":
                chdir($dir); 
                $handle = opendir($dir);
        
                if (file_exists($dname)) {                  
                    header('Content-Type: application/unknown');
                    header("Content-Disposition: attachment; filename = $dname");
                    readfile($dname);
                }
         
                closedir($handle);
                break;
       
            case "verz":
                $zipDatei = $this -> zipEintrag("verzeichnis", $dir, $dname);
                chdir($verz); 
                $handle = opendir($verz);
       
                if (file_exists($zipDatei)) {        
                    if ($zip_name != "err") {
                        header('Content-Type: application/zip');
                        header("Content-Disposition: attachment; filename = $zipDatei");
                        readfile($zipDatei);
                    }
          
                unlink($zipDatei);
                }
       
                closedir($handle);
                break;
         }
         */
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
       /*
       $eintrag = $envDir. "/". $dname;
    
       if (file_exists($eintrag))
          return "eintrag vorhanden";
       else
          return "ok";
      
       //Datei-Kontrollfunktionen:
       // -Quota
       // -Dateigroesse
       // -Dateityp
       */
   }
}
