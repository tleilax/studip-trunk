<?php


//@author Timo Hartge <hartge@data-quest.de>


class StEP00330 extends Migration
{
    public function up()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `semester_data` ADD `visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1' AFTER `vorles_ende`;");

        $stmt = $db->prepare('INSERT INTO config (field, value, type, section, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'ANONYMIZE_USERNAME',
            'description' => 'Wenn diese Einstellung gesetzt ist, wird beim Anonymisieren der Name der Person entfernt.',
            'section'     => 'privacy',
            'type'        => 'boolean',
            'value'       => 'true'
        ]);

        $db = null;
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `semester_data` DROP ` visible `;");

        $stmt = $db->prepare('DELETE config, config_values FROM config LEFT JOIN config_values USING(field) WHERE field = ?');
        $stmt->execute(['ANONYMIZE_USERNAME']);

        $db = null;
    }
}
