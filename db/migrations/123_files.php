<?php

class Files extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'add database tables for flexible files';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE IF NOT EXISTS files (
                    file_id CHAR(32) NOT NULL,
                    user_id CHAR(32) NOT NULL,
                    mime_type VARCHAR(64) NOT NULL,
                    size BIGINT UNSIGNED NOT NULL,
                    restricted TINYINT(1) NOT NULL DEFAULT 0,
                    storage VARCHAR(32) NOT NULL DEFAULT 'DiskFileStorage',
                    storage_id VARCHAR(32) NOT NULL,
                    mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    chdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    PRIMARY KEY (file_id))");

        $db->exec("CREATE TABLE IF NOT EXISTS file_refs (
                    id CHAR(32) NOT NULL,
                    file_id CHAR(32) NOT NULL,
                    parent_id CHAR(32) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    downloads INT NOT NULL DEFAULT 0,
                    PRIMARY KEY (id))");
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
    /*
     * Migration for the Admin-Area
     */
    
        DBManager::get()->query("CREATE  TABLE IF NOT EXISTS `doc_filetype` (
                                `id` INT NOT NULL AUTO_INCREMENT ,
                                `type` VARCHAR(45) NOT NULL ,
                                `description` TEXT NULL ,
                                PRIMARY KEY (`id`) )
                                ENGINE = MyISAM");

        DBManager::get()->query("CREATE  TABLE IF NOT EXISTS `doc_usergroup_config` (
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
                                PRIMARY KEY (`id`, `usergroup`) )
                                ENGINE = MyISAM");

        DBManager::get()->query("CREATE  TABLE IF NOT EXISTS `doc_filetype_forbidden` (
                                `id` INT NOT NULL AUTO_INCREMENT ,
                                `usergroup` VARCHAR(45) NOT NULL ,
                                `dateityp_id` INT NOT NULL ,
                                PRIMARY KEY (`id`) ,
                                INDEX `fk_dateityp_verbot_nutzerbereich_2_idx` (`dateityp_id` ASC) ,
                                INDEX `fk_dateityp_verbot_nutzerbereich_1_idx` (`usergroup` ASC) )
                                ENGINE = MyISAM");



        /*
         * Set the entry into the table "config" to enable or disable the Personal Document Area
         */
        $query = "INSERT IGNORE INTO `config`
	                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`,
	                     `mkdate`, `chdate`, `description`)
	                  VALUES (:id, :field, :value, 1, :type, 'global', 'files',
	                          UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);

        $statement->execute(array(
            ':id' =>  md5(uniqid('PERSONALDOCUMENT_ENABLE')),
            ':field' => 'PERSONALDOCUMENT_ENABLE',
            ':value' => (int) true,
            ':type' => 'boolean',
            ':description' => 'Aktiviert den persoenlichen Dateibereich',
        ));
        $queryTwo = "INSERT IGNORE INTO `doc_usergroup_config`
                        (`id`, `usergroup`, `upload_quota`, `upload_unit`,`quota`,`quota_unit`,`is_group_config`)
                        VALUES (:id,:group, :uploadQuota, :uploadUnit, :quota, :quotaUnit, :isGroupConfig)";
        $statementTwo = DBManager::get()->prepare($queryTwo);

        $statementTwo->execute(array(
            ':id' => '1',
            ':group' => 'default',
            ':uploadQuota' => '5242880',
            ':uploadUnit' => 'MB',
            ':quota' => '52428800',
            ':quotaUnit' => 'MB',
            ':isGroupConfig'=>'1'
        ));
       
        $queryThree=("INSERT IGNORE INTO `doc_filetype` 
           (`type`) VALUES (:type)");
        $statementThree = DBManager::get()->prepare($queryThree);
        $values = array('exe', 'com','pif','bat','scr');
        foreach($values as $value)
            $statementThree->execute (array(
                ':type' =>$value
            ));
   }

   
   public function down() {
        $alluserdir = $USER_DOC_PATH;

        foreach (scandir($alluserdir) as $item) {
            if ($item == '.' || $item == '..')
                continue
                unlink($alluserdir . DIRECTORY_SEPARATOR . $item);
        }

        rmdir($alluserdir);

        $db = DBManager::get();

        $db->exec("DROP TABLE IF EXISTS 
     (
      'files', 
      'files_layout', 
      'files_backend_studip', 
      'files_backend_url', 
      'files_share', 
      'entity'
     )");
        /*
         * Down-Migration for Admin-Area
         */
        //DELETEs the config entry
        DBManager::get()->query("DELETE FROM config WHERE field IN ('PERSONALDOCUMENT_ENABLE')");
        //DELETE the added Tables
        DBManager::get()->query("DROP TABLE IF EXISTS `doc_usergroup_config`");
        DBManager::get()->query("DROP TABLE IF EXISTS `doc_filetype`");
        DBManager::get()->query("DROP TABLE IF EXISTS `doc_filetype_forbidden`");
    }
 }
