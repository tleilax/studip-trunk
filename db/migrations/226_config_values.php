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
        $db->exec("ALTER TABLE user_config
                   RENAME TO config_values,
                   DROP userconfig_id,
                   DROP parent_id,
                   CHANGE user_id range_id varchar(32) COLLATE latin1_bin NOT NULL AFTER field,
                   ADD PRIMARY KEY (field, range_id),
                   ADD KEY range_id (range_id),
                   DROP KEY user_id,
                   DROP KEY field");

        $db->exec("INSERT INTO config_values (field, range_id, value, comment, mkdate, chdate)
                   SELECT field, 'studip', value, comment, mkdate, chdate
                   FROM config WHERE is_default = 0");

        $db->exec('DELETE FROM config WHERE is_default = 0');

        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        $db = DBManager::get();

        // delete no longer supported values
        $db->exec("DELETE config, config_values FROM config JOIN config_values USING(field) WHERE `range` = 'course'");

        // restore user_config and old settings
        $db->exec("ALTER TABLE config_values
                   RENAME TO user_config,
                   ADD userconfig_id varchar(32) COLLATE latin1_bin NOT NULL DEFAULT '' FIRST,
                   ADD parent_id varchar(32) COLLATE latin1_bin DEFAULT NULL AFTER userconfig_id,
                   CHANGE range_id user_id varchar(32) COLLATE latin1_bin NOT NULL AFTER parent_id");

        $db->exec('UPDATE user_config SET userconfig_id = MD5(CONCAT(field, user_id))');

        $db->exec("ALTER TABLE user_config
                   DROP PRIMARY KEY,
                   DROP KEY range_id,
                   ADD PRIMARY KEY (userconfig_id),
                   ADD KEY user_id (user_id, field, value(5)),
                   ADD KEY field (field, value(10))");

        $db->exec("INSERT INTO config (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description, comment)
                   SELECT userconfig_id, field, user_config.value, 0, type, `range`, section,
                          user_config.mkdate, user_config.chdate, description, user_config.comment
                   FROM user_config JOIN config USING(field) WHERE user_id = 'studip'");

        $db->exec("DELETE FROM user_config WHERE user_id = 'studip'");

        // restore unused fields and update range
        $db->exec("ALTER TABLE config
                   CHANGE `range` `range` enum('global', 'user') COLLATE latin1_bin NOT NULL DEFAULT 'global',
                   ADD parent_id varchar(32) COLLATE latin1_bin NOT NULL DEFAULT '' AFTER config_id,
                   ADD position int(11) NOT NULL DEFAULT 0 AFTER section,
                   ADD message_template varchar(255) NOT NULL DEFAULT '' AFTER comment");

        SimpleORMap::expireTableScheme();
    }
}
