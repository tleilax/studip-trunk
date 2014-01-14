<?php

/**
 * UserToAPI.class.php 
 * 
 * Die Operationen der "StudipDocumentAPI.class" sind so allgemein formuliert, 
 * dass sie sich sowohl auf User- als auch auf Seminar- oder Institute-Documente
 * beziehen koennen. Zur Vereinfachung der Verwendung der API realisiert die 
 * Klasse "UserToAPI.class" eine Abstraktion der StudipDocumentAPI.class in Bezug 
 * auf User-Dokumente. 
 * 
 * Die Abstraktion erfolgt mit Hilfe eines Adapters mit Delegation ("Wrapper-Klasse") 
 * fuer Zugriffe auf die Operationen der API, welche sich auf die persoenlichen 
 * Dokumente eines Users beziehen.  
 * 
 * @category    Stud.IP
 * @version     3.1
 * 
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0
 * @copyright   2014 Carl von Ossietzky Universitaet Oldenburg
 */


require_once 'StudipDocumentAPI.class.php';


class UserToAPI
 {  
  public function authUser($id, $storage)
   {
    $auth = StudipDocumentAPI::authEntity('user', $id, $storage);
    return $auth;
   }
   
   
  public function initUser($id, $storage)
   {
    StudipDocumentAPI::initEntity('user', $id, $storage);
   }
 }