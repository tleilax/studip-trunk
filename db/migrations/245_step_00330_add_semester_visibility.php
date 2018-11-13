<?php


//@author Timo Hartge <hartge@data-quest.de>


class StEP00330AddSemesterVisibility extends Migration
{
    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "ALTER TABLE `semester_data` ADD `visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1' AFTER `vorles_ende`;"
        );

        $db = null;
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `semester_data` DROP ` visible `;");

        $db = null;
    }
}
