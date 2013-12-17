<?php

/**
 * dateien.php 
 * 
 * Der Controller stellt angemeldeten Benutzer/innen ein Dateimanagement-
 * system fuer einen persoenlichen Dateibereich im Stud.IP zur Verfuegung.   
 *
 *
 * TODO:
 * 
 * - Lifter 010: Unterstuetzung einer barrierefreien Nutzung
 * 
 *
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @version     3.0
 */


require_once 'app/controllers/authenticated_controller.php';
require_once 'lib/classes/document/StudipDocumentAPI.class.php';


class Document_DateienController extends AuthenticatedController
 {  
   private $userConfig, $quota;
   
   
   public function before_filter(&$action, &$args)
    {    
     parent::before_filter($action, $args);    
     Navigation::activateItem('/document/dateien');
     
     $user_id = $GLOBALS['auth'] -> auth['uid'];
       
     //Configurations for the Documentarea for this user 
     $this -> userConfig = DocUsergroupConfig::getUserConfig($GLOBALS['user'] -> user_id);
     
     if (!empty($this -> userConfig))
      {
       $measure = $this -> userConfig['quota'];
       $this -> quota = $this -> formatiere($measure);
      }
      
     $api = new StudipDocumentAPI();
     $user_exists = $api -> authEntity($user_id, 'DB');
     
     if (! $user_exists)
      $api -> initEntity($user_id, 'DB');
     
     PageLayout::setTitle(_('Dateiverwaltung'));
     PageLayout::setHelpKeyword('Basis.Dateien');      
     PageLayout::addStylesheet('/stylesheets/jquery-ui-studip.css');
     
     $this -> set_layout($GLOBALS['template_factory'] -> open('layouts/base'));
    }
  
 
  public function index_action()
   {                          
    $this -> redirect("document/dateien/list");
   }
   
 
  public function list_action()
   {
    $inhalt[0][0] = 0;
    $inhalt[0][1] = "id1";
    $inhalt[0][2] = "Ordner";
    $inhalt[0][3] = "Test";
    $inhalt[0][4] = "unlocked";
    $inhalt[0][5] = "Martin Mustermann";
    $inhalt[0][6] = "16.10.2013";

    $inhalt[1][0] = 1;
    $inhalt[1][1] = "id2";
    $inhalt[1][2] = "Datei";
    $inhalt[1][3] = "Hausarbeit.pdf";
    $inhalt[1][4] = "locked";
    $inhalt[1][5] = "Martin Mustermann";
    $inhalt[1][6] = "17.10.2013";
    
    $this -> flash['count'] = 1;
    $this -> flash['inhalt'] = $inhalt;
    
    $this -> flash['quota'] = $this -> quota;
    $this -> flash['closed'] = $this -> userConfig['area_close'];
    $this -> flash['notification'] = $this -> userConfig['area_close_text'];
    
    $this -> render_action('index');
   }
   

  public function oeffnen_action($type)
   { 
    //
   }
   
  
  public function teilen_action($type)
   { 
    $this -> flash['share'] = $type;
    $this -> redirect("document/dateien/list");
   }
   
  
  public function bearbeiten_action($type)
   { 
    $this -> flash['workOn'] = $type;
    $this -> redirect("document/dateien/list");
   }
   
   
  public function verwalten_action($type)
   { 
    $this -> flash['admin'] = $type;
    $this -> redirect("document/dateien/list");
   }
   
   
  public function loeschen_action($type)
   { 
    $this -> flash['delete'] = $type;
    $this -> redirect("document/dateien/list");
   }
     
   
  public function hochladen_action()
   {
    //
   }
   
   
  public function erstellen_action()
   {
    //
   }
   
  
  public function konfigurieren_action()
   {
    //
   }
   
   
  private function formatiere($bytes)
   {
    if ($bytes >= 1073741824)
      {
       $groesse = number_format($bytes / 1073741824, 2) . ' GB';
      }
     elseif ($bytes >= 1048576)
      {
       $groesse = number_format($bytes / 1048576, 2) . ' MB';
      }
     elseif ($bytes >= 1024)
      {
       $groesse = number_format($bytes / 1024, 2) . ' KB';
      }
     elseif ($bytes > 1)
      {
       $groesse = $bytes . ' Bytes';
      }
     elseif ($bytes == 1)
      {
       $groesse = $bytes . ' Byte';
      }
     else
      {
       $groesse = '0 Byte';
      }
 
    return $groesse;
   }
 }
 
