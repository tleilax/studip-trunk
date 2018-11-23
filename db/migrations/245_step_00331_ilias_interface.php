<?php
class StEP00331IliasInterface extends Migration
{

    function description()
    {
        return 'Adds new ilias interface module to Stud.IP';
    }

    function up()
    {
        $this->ilias_interface_config = array(
                        'moduletitle' => _('ILIAS'),
                        'edit_moduletitle' => false,
                        'search_active' => true,
                        'show_offline' => false,
                        'cache' => true
        );
        Config::get()->create('ILIAS_INTERFACE_BASIC_SETTINGS', array('type' => 'array', 'value' => json_encode($this->ilias_interface_config), 'range' => 'global', 'section' => 'modules'));
        Config::get()->create('ILIAS_INTERFACE_SETTINGS', array('type' => 'array', 'value' => json_encode(array()), 'range' => 'global', 'section' => 'modules'));
        Config::get()->create('ILIAS_INTERFACE_ENABLE', array('type' => 'boolean', 'value' => false, 'range' => 'global', 'section' => 'modules'));
        
        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` DROP PRIMARY KEY");
        $stmt->execute([]);
        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` ADD PRIMARY KEY (`studip_user_id`, `external_user_system_type`, `external_user_type`)");
        $stmt->execute([]);

        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` ADD `external_user_token` VARCHAR(32) NOT NULL DEFAULT '' AFTER `external_user_password`;");
        $stmt->execute([]);
        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` ADD `external_user_token_valid_until` INT(11) NOT NULL DEFAULT '0' AFTER `external_user_token`;");
        $stmt->execute([]);

        $stmt = DBManager::get()->prepare("ALTER TABLE `sem_classes` ADD `ilias_interface` VARCHAR(64) NULL DEFAULT NULL AFTER `elearning_interface`;");
        $stmt->execute([]);
        $stmt = DBManager::get()->prepare("UPDATE `sem_classes` SET `ilias_interface` = 'CoreIliasInterface' WHERE `sem_classes`.`id` = ?;");
        $stmt->execute([1]);
    
        $stmt = DBManager::get()->prepare("ALTER TABLE `object_user_visits` CHANGE `type` `type` ENUM('vote','documents','forum','literature','schedule','scm','sem','wiki','news','eval','inst','ilias_connect','elearning_interface','ilias_interface','participants') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'vote';");
        $stmt->execute([]);

    }

    function down()
    {
        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` DROP PRIMARY KEY");
        $stmt->execute([]);
        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` ADD PRIMARY KEY (`studip_user_id`, `external_user_system_type`)");
        $stmt->execute([]);
        
        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` DROP `external_user_token`");
        $stmt->execute([]);
        $stmt = DBManager::get()->prepare("ALTER TABLE `auth_extern` DROP `external_user_token_valid_until` ");
        $stmt->execute([]);
        
        $stmt = DBManager::get()->prepare("ALTER TABLE `sem_classes` DROP `ilias_interface`");
        $stmt->execute([]);
    }
}