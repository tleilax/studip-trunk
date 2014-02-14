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
#require_once 'lib/classes/document/UserToAPI.class.php';


class Document_DateienController extends AuthenticatedController
 {
   private $userConfig, $quota;


   public function before_filter(&$action, &$args)
    {
     global $user, $USER_DOC_PATH;

     parent::before_filter($action, $args);
     Navigation::activateItem('/document/dateien');

     $user_id = $user -> id;

     //Configurations for the Documentarea for this user
     $this -> userConfig = DocUsergroupConfig::getUserConfig($GLOBALS['user'] -> user_id);

     if (!empty($this -> userConfig))
      {
       $measure = $this -> userConfig['quota'];
       $this -> quota = $this -> formatiere($measure);
      }

#     $this -> userAPI = new UserToAPI();
#     $user_exists = $this -> userAPI -> authUser($user_id, 'DB');

#     if (!$user_exists)
#      $this -> userAPI -> initUser($user_id, 'DB');

     PageLayout::setTitle(_('Dateiverwaltung'));
     PageLayout::setHelpKeyword('Basis.Dateien');
     //PageLayout::addScript('./javascripts/application_diff.js');
     //PageLayout::addScript('./javascripts/jquery/jquery-ui.1.9.1.custom.js');

     $this -> set_layout($GLOBALS['template_factory'] -> open('layouts/base'));
    }


  public function index_action()
   {
    $this -> redirect("document/dateien/list");
   }


  public function list_action()
   {
    $this -> flash['count'] = 0;

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



  public function crazy_action()
   {
    $in = $_GET["test"];
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
