<?php

class I18nContent extends Migration
{
    public function description()
    {
        return 'Add database table for multi-language content';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('CREATE TABLE `i18n` (
                   `object_id` varchar(32) NOT NULL,
                   `table` varchar(255) NOT NULL,
                   `field` varchar(255) NOT NULL,
                   `lang` varchar(32) NOT NULL,
                   `value` text,
                   PRIMARY KEY (`object_id`,`table`,`field`,`lang`)
                   )');
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE i18n');
    }
}
