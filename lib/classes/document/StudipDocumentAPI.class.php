<?php

/**
 * StudipDocumentAPI.class.php 
 * 
 * Die Klasse stellt eine zentrale Programmierschnittstelle zur Verwaltung von
 * Dateien aus verschiedenen sekundaeren Speichersystemen zur Verfuegung. 
 * 
 * 
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @version     3.0
 */


require_once 'lib/models/document/DBStorage.class.php';

//require_once 'lib/models/document/FileStorage.class.php';
//require_once 'lib/models/document/URLStorage.class.php';
//require_once 'lib/models/document/CloudStorage.class.php';
//require_once 'lib/models/document/WebDAVStorage.class.php';
//require_once 'Share.class.php';


class StudipDocumentAPI 
 {
  public function __construct()
   {
    $this -> DBStorage = new DBStorage();
   }
   
  // TODO ...   

 }

 