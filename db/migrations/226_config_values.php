<?php

class ConfigValues extends Migration
{
    function description()
    {
        return 'add database table for generic config values';
    }

    function up()
    {
        $db = DBManager::get();

        // fix up missing config entries with is_default = 1
        $db->exec('UPDATE config LEFT JOIN config c ON config.field = c.field AND c.is_default = 1
                   SET config.is_default = 1 WHERE config.is_default = 0 AND c.config_id IS NULL');

        // fix up missing user_config entries in config
        $db->exec("INSERT IGNORE INTO config (config_id, field, value, is_default, type, `range`)
                   SELECT DISTINCT MD5(user_config.field), user_config.field, '', 1, 'string', 'user'
                   FROM user_config LEFT JOIN config ON user_config.field = config.field AND is_default = 1
                   WHERE config_id IS NULL");

        // drop unused fields and update range
        $db->exec("ALTER TABLE config DROP parent_id, DROP position, DROP message_template,
                   CHANGE `range` `range` enum('global', 'user', 'course') COLLATE latin1_bin NOT NULL DEFAULT 'global'");

        // create new table and migrate all settings
        $db->exec('CREATE TABLE config_values (
                   field varchar(255) COLLATE latin1_bin NOT NULL,
                   range_id varchar(32) COLLATE latin1_bin NOT NULL,
                   value text NOT NULL,
                   comment text NOT NULL,
                   mkdate int(11) NOT NULL,
                   chdate int(11) NOT NULL,
                   PRIMARY KEY (field, range_id),
                   KEY range_id (range_id)
                   ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC');

        $db->exec("INSERT INTO config_values (field, range_id, value, comment, mkdate, chdate)
                   SELECT field, 'studip', value, comment, mkdate, chdate
                   FROM config WHERE is_default = 0");

        $db->exec('INSERT INTO config_values (field, range_id, value, comment, mkdate, chdate)
                   SELECT field, user_id, user_config.value, user_config.comment, user_config.mkdate, user_config.chdate
                   FROM user_config LEFT JOIN config USING(field) WHERE is_default = 1');

        // $db->exec('DELETE FROM config WHERE is_default = 0');
        // $db->exec('DROP TABLE user_config');

        // Config::get()->create('TEST', array(
        //     'value' => 'Stud.IP',
        //     'is_default' => '1',
        //     'type' => 'string',
        //     'range' => 'course',
        //     'description' => 'Testeintrag Veranstaltung'
        // ));

        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        $db = DBManager::get();

        // Config::get()->delete('TEST');

        // FIXME restore old settings
        // $db->exec("INSERT INTO config (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description, comment)
        //            SELECT MD5(CONCAT(field, range_id), field, config_values.value, 0, config.type, config.`range`, section, user_config.mkdate, user_config.chdate, description, user_config.comment
        //            FROM config_values JOIN config USING(field)");

        $db->exec('DROP TABLE config_values');

        $db->exec("ALTER TABLE config
                   CHANGE `range` `range` enum('global', 'user') COLLATE latin1_bin NOT NULL DEFAULT 'global',
                   ADD parent_id varchar(32) COLLATE latin1_bin NOT NULL DEFAULT '' AFTER config_id,
                   ADD position int(11) NOT NULL DEFAULT 0 AFTER section,
                   ADD message_template varchar(255) NOT NULL DEFAULT '' AFTER comment");

        SimpleORMap::expireTableScheme();
    }
}
