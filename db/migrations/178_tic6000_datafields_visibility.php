<?php
/**
 * Adds another visibility setting to datafields
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class Tic6000DatafieldsVisibility extends Migration
{
    public function description()
    {
        return 'Adds another visibility setting to datafields';
    }

    public function up()
    {
        $query = "ALTER TABLE `datafields`
                    ADD COLUMN `self_perms` ENUM('all','user','autor','tutor','dozent','admin','root')
                        NULL DEFAULT NULL AFTER `view_perms`";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `datafields` DROP COLUMN `self_perms`";
        DBManager::get()->exec($query);
    }
}
