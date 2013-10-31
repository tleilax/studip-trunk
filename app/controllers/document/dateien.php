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
  public function before_filter(&$action, &$args)
    {    
     parent::before_filter($action, $args);    
     Navigation::activateItem('/document/dateien');

     $user_id = $GLOBALS['auth'] -> auth['uid'];
     $api = new StudipDocumentAPI();
    
     // TODO ...
   
     PageLayout::addStylesheet('./assets/stylesheets/jquery-ui-1.9.1.custom.css');
     PageLayout::addScript('./assets/javascripts/jquery/jquery-ui.1.9.1.custom.js');
     $this -> set_layout($GLOBALS['template_factory'] -> open('layouts/base'));
    }
  
 
    public function index_action() {
        //Configurations for the Documentarea for this user 
        $this->userConfig = DocUsergroupConfig::getUserConfig($GLOBALS['user']->user_id);
    }
 }
 
