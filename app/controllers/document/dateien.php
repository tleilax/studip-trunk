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
    
    private $userConfig, $quota;
   
    public function before_filter(&$action, &$args) {        
        global $USER_DOC_PATH;
     
        parent::before_filter($action, $args);    
        Navigation::activateItem('/document/dateien');
                   
        //Configurations for the Documentarea for this user 
        $this->userConfig = DocUsergroupConfig::getUserConfig($GLOBALS['user']->id);
     
        if (!empty($this->userConfig)) {
            $measure = $this->userConfig['quota'];
            $this->quota = $this->formatiere($measure);
        }
        PageLayout::setTitle(_('Dateiverwaltung'));
        PageLayout::setHelpKeyword('Basis.Dateien');      
        PageLayout::addStylesheet('/stylesheets/jquery-ui-studip.css');
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }
   
    public function index_action() {
    
        $this->redirect("document/dateien/list");
    }
    
    public function list_action() {     
        $user_root = StudipDirectory::getRootDirectory($GLOBALS['user']->id);
        $dir_list = $user_root->listFiles();
        
        $count = 0;
        foreach ($dir_list as $key => $entry) {
            $merge_result[$key] = array_merge((array) $entry, (array) File::get($entry->file_id));
            $count++; 
        }
        $dir_list = $merge_result;
        
        if (empty($dir_list)) {
            $this->flash['count'] = -1;
        }
        else { 
            foreach ($dir_list as $entry)
                $inhalt[] = (array) $entry;
            $this->flash['count'] = $count;
        }
           
        $this->flash['inhalt'] = $inhalt;
        $this->flash['quota'] = $this->quota;
        $this->flash['closed'] = $this->userConfig['area_close'];
        $this->flash['notification'] = $this->userConfig['area_close_text'];
        $this -> render_action('index');
    }
    
    public function addDir_action() {
        if(Request::submitted('mkdir')) {   
            $newDir = $_POST['dirname'];
            $result = $this->verifyName($newDir);

            //test
            $user_root = new RootDirectory($GLOBALS['user']->id);
            $entry = $user_root->mkdir($_POST['dirname']);
            $entry->setDescription($_POST['description']);
            
            /*
            if ($result == 'ok') {
             
                if (!isset($dir_id) {
                    $user_root = new RootDirectory($GLOBALS['user']->id);
                    $entry = $user_root->mkdir($_POST['dirname']);
                    $entry->setDescription($_POST['description']);
                }
                else {
                    $dirEntry = new DirectoryEntry($id);
                    $dir = StudipDirectory::get($dirEntry->file_id);
                    $entry = $dir->mkdir($_POST['dirname']);
                    $entry->setDescription($_POST['description']);
                }
            }
            */
        }
        $this->redirect('document/dateien/index');           
    }
    
    public function upload_action() {
        if(Request::submitted('upload')) {

            /*
            if (!isset($id) && isset($_FILES['upfile']['tmp_name'])) {
                $user_root = new RootDirectory($GLOBALS['user']->id);
                $entry = $user_root->create($_POST['dirname']);
                $entry->setDescription($_POST['description']); 
            }
            else if (isset($id) && isset($_FILES['upfile']['tmp_name'])) {
                $stud = StudipDirectory::get($dirEntry->file_id);
                $file = $stud->create($_POST['dateiname']);
                $file->setDescription($_POST['description']);
            else {
                PageLayout::postMessage(MessageBox::error(_('FEHLER')));
            }
        
            $fileEntry = File::get($file->file_id);
            $fileEntry->storage;
            //$storage = $fileEntry->test(); 
            
            $upfile = $_FILES['upfile']['name'];
            $groesse = $_FILES['upfile']['size'];
            $typ = $_FILES['upfile']['type'];
            $tmp_name = $_FILES['upfile']['tmp_name'];

            $ergebnis = $this -> kontrolliereDatei($env, $upfile); //$groesse, $typ); 

            switch ($ergebnis) {
                case "eintrag vorhanden":
                    $upfile = $this -> renameEintrag($env, $upfile);
                break;
           
                case "datei zu gross":
                break;
           
                case "unerlaubter datei-typ":
                break;
           
                case "quota-ueberschreitung":
                break;
            }
         
           if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $ziel_verz. $upfile))
               $this -> $ergebnis = "Upload_Fehler"; 
           
           //if ($ergebnis == "ok" && $typ == "application/zip")
           // $ergebnis = $this -> unzipEintrag($ziel_verz, $upfile);
         
           $this -> flash['addFile_MessageFlag'] = $ergebnis;
           */
       }
      
      $this -> redirect('document/dateien/list');
     }
     
    public function edit_action($id, $parent_id) { 
        if (Request::submitted('edit')) {
            
            /*
            $directory = new DirectoryEntry($id);
            $directory->setDescription($_POST['description']);
            */
        }
        $this -> redirect('document/dateien/list');
    }
    
    public function copyTo_action($id, $parent_id=NULL) {
        
        /*
        $directory = StudipDirectory::get($id);
        $directory->copy($_SESSION['document-copy']['file_refs'], $_SESSION['document']['files']->name);
        unset($_SESSION['document-copy']['file_refs']);
        unset($_SESSION['document-copy']['files']);
        */
     
        $this_redirect('document/dateien/list'. $parent_id);
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
        }
        */
    }
    
    private function formatiere($bytes) {
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
    
    private function verifyName($name) { 
       if (!(strpos($name, '/') === false)) {
           $ergebnis = "slash";
       }
       else if(!(strpos($name, '\\') === false)) {
           $ergebnis = "backslash";
       }
       else if(!(strpos($name, '(') === false) || !(strpos($name, ')') === false)) {
           $ergebnis = "klammern";
       }
       else if(strlen($name) == 0) {
           $ergebnis = "leer";
       }
       else if (strlen($name) > 256) {
           $ergebnis = "max";
       }
       else {
           $ergebnis = "ok";
       }
       return $ergebnis;
    }
}
