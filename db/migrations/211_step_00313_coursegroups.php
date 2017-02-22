<?php
class StEP00313Coursegroups extends Migration
{
    public function up()
    {
        DBManager::get()->exec("ALTER TABLE `sem_classes` ADD `is_group` TINYINT(1) NOT NULL DEFAULT '0' AFTER `show_raumzeit`");
        DBManager::get()->exec("ALTER TABLE `seminare` ADD `parent_course` VARCHAR(32) NULL DEFAULT NULL AFTER `public_topics`");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `sem_classes` DROP `is_group`");
        DBManager::get()->exec("ALTER TABLE `seminare` DROP `parent_course`");

        SimpleORMap::expireTableScheme();
    }
}
