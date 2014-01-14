<?php

/**
 * files.php
 *
 * Die Migration erzeugt alle Tabellen, die fuer den Betrieb des persoenlichen 
 * Dateibereichs noetig sind. Dies sind:
 * 
 * - die Kern-Tabellen zur Verwaltung der persoenlichen Verzeichnisse und 
 *   Dateien
 * 
 * - die Kern-Tabellen zur Verwaltung der vom Stud.IP-Systemadminstrator 
 *   vorgenommenen Konfiguration des persoenlichen Dateibereichs und
 *   
 * - die Kern-Tabelle zur Verwaltung der von einem Benutzer gewaehlten 
 *   Anzeigeoptionen fuer "seinen" Dateimanager.
 *   
 *  Zur physikalischen Speicherung persoenlicher Dateien auf dem lokalen Server 
 *  richtet files das in der config_local.inc.php in der globalen Variablen 
 *  "$USER_DOC_PATH" angegebene Verzeichnis ein.
 *  
 * @category    Stud.IP
 * @version     3.1
 * 
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @author      Stefan Osterloh <s.osterloh@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0
 * @copyright   2014 Carl von Ossietzky Universitaet Oldenburg 
 *  
 */


class files extends DBMigration 
 {
  public function description() 
   {
    return 'Modifies db-scheme for StEP00262 to provide an user-centered filemanager.';
   }


  public function up()
   {
    global $USER_DOC_PATH;
         
    $alluserdir = $USER_DOC_PATH;
    
    if (!file_exists($alluserdir))
     mkdir($alluserdir, 0744, true); 
    
    $db = DBManager::get();
    
    $db -> exec("CREATE TABLE IF NOT EXISTS doc_user
     ( 
      id VARCHAR(32) NOT NULL,
      user_id VARCHAR(32) NOT NULL,
      type ENUM('dir','file','link','unkown') NOT NULL,
      name VARCHAR(128) NOT NULL,       
      mimetype VARCHAR(16) NULL,
      author VARCHAR(128) NOT NULL,
      mkdate INT UNSIGNED NOT NULL, 
      chdate INT UNSIGNED NULL,
      size BIGINT UNSIGNED NOT NULL,
      env VARCHAR(32) NOT NULL,
      stage TINYINT UNSIGNED NOT NULL,
      share BOOLEAN DEFAULT FALSE,
      description TEXT NULL,
      elearning BOOLEAN DEFAULT FALSE,
      storage VARCHAR(32) NULL,
      PRIMARY KEY (id)
      )");   
    
    $db -> exec("CREATE TABLE IF NOT EXISTS doc_elearning
     ( 
      doc_user_id VARCHAR(32) NOT NULL,
      description TEXT NULL,
      lecturetype ENUM('leistung'),
      semester INT UNSIGNED NOT NULL, 
      feedback MEDIUMTEXT,
      timestamp INT UNSIGNED NULL,
      PRIMARY KEY (doc_user_id)
      )");
        
    $db -> exec("CREATE TABLE IF NOT EXISTS doc_entity
     ( 
      id VARCHAR(32) NOT NULL,
      PRIMARY KEY (id)
      )");
    
    $db -> exec("CREATE TABLE IF NOT EXISTS doc_share
     ( 
      doc_user_id VARCHAR(32) NOT NULL,
      doc_entity_id VARCHAR(32) NOT NULL,
      description TEXT NULL,
      read_perm BOOLEAN DEFAULT TRUE,
      write_perm BOOLEAN DEFAULT FALSE,
      start_date INT(10) UNSIGNED NOT NULL,
      end_date INT(10) UNSIGNED NOT NULL,
      PRIMARY KEY (doc_user_id, doc_entity_id)
      )");
    
    $db -> exec("CREATE TABLE IF NOT EXISTS doc_storage
     (
      id VARCHAR(32) NOT NULL,
      PRIMARY KEY (id)
      )");

    /*
     * Migration for the Admin-Area
     */
    
    DBManager::get() -> query("CREATE  TABLE IF NOT EXISTS `doc_filetype` 
     (
      `id` INT NOT NULL AUTO_INCREMENT ,
      `type` VARCHAR(45) NOT NULL ,
      `description` TEXT NULL ,
      PRIMARY KEY (`id`))
      ENGINE = MyISAM"
     );

    DBManager::get() -> query("CREATE  TABLE IF NOT EXISTS `doc_usergroup_config` 
     (
      `id` INT NOT NULL AUTO_INCREMENT ,
      `usergroup` VARCHAR(45) NOT NULL ,
      `upload_quota` TEXT NOT NULL ,
      `upload_unit` VARCHAR(45) NULL ,
      `quota` TEXT NULL ,
      `quota_unit` VARCHAR(45) NULL ,
      `upload_forbidden` INT NOT NULL DEFAULT 0 ,
      `area_close` INT NOT NULL DEFAULT 0 ,
      `area_close_text` TEXT NULL ,
      `is_group_config` INT NOT NULL DEFAULT 0 ,
      PRIMARY KEY (`id`, `usergroup`))
      ENGINE = MyISAM"
     );

    DBManager::get() -> query("CREATE  TABLE IF NOT EXISTS `doc_filetype_forbidden`
     (
      `id` INT NOT NULL AUTO_INCREMENT ,
      `usergroup` VARCHAR(45) NOT NULL ,
      `dateityp_id` INT NOT NULL ,
      PRIMARY KEY (`id`) ,
      INDEX `fk_dateityp_verbot_nutzerbereich_2_idx` (`dateityp_id` ASC) ,
      INDEX `fk_dateityp_verbot_nutzerbereich_1_idx` (`usergroup` ASC))
      ENGINE = MyISAM"
     );

    /*
     * Set the entry into the table "config" to enable or disable the Personal Document Area
     */

    $query = "INSERT IGNORE INTO `config`
                (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, 
                 `mkdate`, `chdate`, `description`)
                VALUES (:id, :field, :value, 1, :type, 'global', 'files', UNIX_TIMESTAMP(), 
                 UNIX_TIMESTAMP(), :description)";

    $statement = DBManager::get() -> prepare($query);

    $statement -> execute(array(
             ':id' =>  md5(uniqid('PERSONALDOCUMENT_ENABLE')),
             ':field' => 'PERSONALDOCUMENT_ENABLE',
             ':value' => (int) true,
             ':type' => 'boolean',
             ':description' => 'Aktiviert den persoenlichen Dateibereich',
         ));
        
    $queryTwo = "INSERT IGNORE INTO `doc_usergroup_config`
                  (`usergroup`, `upload_quota`, `upload_unit`,`quota`,`quota_unit`,`is_group_config`)
                  VALUES (:group, :uploadQuota, :uploadUnit, :quota, :quotaUnit, :isGroupConfig)";

    $statementTwo = DBManager::get()->prepare($queryTwo);

    $statementTwo -> execute(array(
            ':group' => 'default',
            ':uploadQuota' => '5242880',
            ':uploadUnit' => 'MB',
            ':quota' => '52428800',
            ':quotaUnit' => 'MB',
            ':isGroupConfig'=>'1'
        ));
       
    $queryThree=("INSERT IGNORE INTO `doc_filetype` 
                  (`type`) VALUES (:type)");

    $statementThree = DBManager::get() -> prepare($queryThree);
 
    $values = array('exe', 'com','pif','bat','scr');

    foreach($values as $value)
     $statementThree->execute (array(':type' => $value));
   }
   
   
  public function down()
   {
    global $USER_DOC_PATH;
    
    $alluserdir = $USER_DOC_PATH;

    foreach (scandir($alluserdir) as $item)
     {
      if ($item == '.' || $item == '..')
       continue 
       unlink($alluserdir . DIRECTORY_SEPARATOR . $item);
     }

    rmdir($alluserdir);

    $db = DBManager::get();

    $db -> exec("DROP TABLE IF EXISTS 
     (
      'doc_user', 
      'doc_elearning', 
      'doc_entity', 
      'doc_share', 
      'doc_storage'   
     )");
   
    /*
     * Down-Migration for Admin-Area
     */

    //DELETEs the config entry
    DBManager::get() -> query("DELETE FROM config WHERE field IN ('PERSONALDOCUMENT_ENABLE')");

    //DELETE the added Tables
    DBManager::get() -> query("DROP TABLE IF EXISTS `doc_usergroup_config`");
    DBManager::get() -> query("DROP TABLE IF EXISTS `doc_filetype`");
    DBManager::get() -> query("DROP TABLE IF EXISTS `mydb`.`doc_filetype_forbidden`");
   }
 }
