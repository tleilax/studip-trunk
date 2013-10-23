<?php

/**
 * SetupDocument.php
 *
 * Die Migration "SetupDocument" erzeugt alle Tabellen, die für den Betrieb des
 * persoenlichen Dateibereichs noetig sind. Dies sind:
 * 
 * - die Kern-Tabellen zur Verwaltung der persoenlichen Verzeichnisse und Dateien
 * 
 * - die Kern-Tabelle zur Verwaltung der von einem Benutzer gewaehlten Anzeigeoptionen 
 *   fuer "seinen" Dateimanager und 
 *   
 * - die Kern-Tabelle zur Verwaltung der vom Stud.IP-Systemadminstrator vorgenommenen
 *   Konfiguration des persoenlichen Dateibereichs.
 *   
 *  Zur physikalischen Speicherung der persoenlichen Dateien richtet SetupDocument das 
 *  im Rahmen der config_local.inc.php in der globalen Variablen "$USER_DOC_PATH" 
 *  angegebene Verzeichnis ein.   
 */


class SetupDocument extends DBMigration 
 {
  public function description() 
   {
    return 'Erzeugt alle Tabellen zur Verwaltung und ein Verzeichnis zur Speicherung 
     persönlicher Dateien';
   }


  public function up()
   {
    $alluserdir = $USER_DOC_PATH;
    
    if (!file_exists($alluserdir))
     mkdir($alluserdir, 0744, true); 
    
    $db = DBManager::get();

    $db -> exec("CREATE TABLE IF NOT EXISTS files
      (
       id VARCHAR(64) NOT NULL,
       user_id VARCHAR(32) NOT NULL,
       type ENUM('file','dir','link','block','char','fifo','unknown'),
       size BIGINT UNSIGNED NOT NULL,
       mimetype VARCHAR(16) NOT NULL,
       uploader_ip VARCHAR(40) NULL,
       uploader_name VARCHAR(256) NOT NULL,
       backend_type VARCHAR(32) NOT NULL,
       backend_id INT UNSIGNED NOT NULL,
       chdate INT UNSIGNED NULL,
       mkdate INT UNSIGNED NOT NULL,
       PRIMARY KEY (id)
       )");
   
    $db -> exec("CREATE TABLE IF NOT EXISTS files_layout
      (
       id VARCHAR(32) NOT NULL,
       parent_id VARCHAR (32) NOT NULL,
       files_id VARCHAR(64) NOT NULL,
       name VARCHAR(256) NOT NULL,
       description MEDIUMTEXT NULL,
       downloads INT UNSIGNED NULL,
       PRIMARY KEY (id)
       )");
    
    $db -> exec("CREATE TABLE IF NOT EXISTS files_backend_studip
      (
       id INT UNSIGNED NOT NULL, 
       files_id VARCHAR(64) NOT NULL,
       path VARCHAR(256) NOT NULL,
       PRIMARY KEY (id)
       )");
    
    $db -> exec("CREATE TABLE IF NOT EXISTS files_backend_url
      (
       id INT UNSIGNED NOT NULL,
       files_id VARCHAR(64) NOT NULL,
       url VARCHAR(256) NOT NULL,
       PRIMARY KEY (id)
       )");
    
    $db -> exec("CREATE TABLE IF NOT EXISTS files_share
      (
       files_id VARCHAR(64) NOT NULL,
       entity_id VARCHAR(32) NOT NULL,
       description MEDIUMTEXT NULL,
       read_perm BOOLEAN DEFAULT FALSE,
       write_perm BOOLEAN DEFAULT FALSE,
       start_date INT UNSIGNED NOT NULL,
       end_date INT UNSIGNED NOT NULL,
       PRIMARY KEY (files_id, entity_id)
       )");
    
    $db -> exec("CREATE TABLE IF NOT EXISTS entity
      (
       id VARCHAR(32) NOT NULL,
       aktiv BOOLEAN NULL,
       PRIMARY KEY (id)
       )");
   }

   
  public function down() 
   {
    $alluserdir = $USER_DOC_PATH;
    
    foreach (scandir($alluserdir) as $item)
     {
      if ($item == '.' || $item == '..') continue
       unlink($alluserdir.DIRECTORY_SEPARATOR.$item);
     }
     
    rmdir($alluserdir);
    
    $db = DBManager::get();
    
    $db -> exec("DROP TABLE IF EXISTS 
     (
      'files', 
      'files_layout', 
      'files_backend_studip', 
      'files_backend_url', 
      'files_share', 
      'entity'      
     )");
   }
 }
