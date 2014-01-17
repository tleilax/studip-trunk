<?php

/**
 * StudipDocumentAPI.class.php 
 * 
 * Die Klasse stellt in Stud.IP eine zentrale Programmierschnittstelle zur 
 * Verwaltung von Dateien zur Verfuegung. 
 * 
 * @category    Stud.IP
 * @version     3.1
 * 
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0
 * @copyright   2014 Carl von Ossietzky Universitaet Oldenburg
 */


require_once 'lib/models/document/DBStorage.class.php';

//require_once 'lib/models/document/FileStorage.class.php';
//require_once 'lib/models/document/URLStorage.class.php';
//require_once 'lib/models/document/CloudStorage.class.php';
//require_once 'lib/models/document/WebDAVStorage.class.php';

//require_once 'lib/classes/document/ShareDocument.class.php';


class StudipDocumentAPI 
 {
  public static function authEntity($entity, $id, $storage)
   {
    switch ($storage)
     {
      case "DB":
       $exist = DBStorage::authRootDir($entity, $id);         
       break;
     }
    
    return $exist;
   }
   
   
  public static function initEntity($entity, $id, $storage)
   {
    switch ($storage)
     {
      case "DB":
       DBStorage::createRootDir($entity, $id); 
       break;
     }
   }
   
   
  public static function deleteEntity($entity, $id, $storage)
   {
    switch ($storage)
     {
      case "DB":
       $res = DBStorage::deleteRootDir($entity, $id);   
       break;
     }
     
    return $res;
   }
 }

 