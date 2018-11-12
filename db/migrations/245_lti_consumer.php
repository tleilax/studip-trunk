<?php
/**
 * LtiConsumer - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class LtiConsumer extends Migration
{
    function description()
    {
        return 'initial database setup for LTI consumer API';
    }

    function up()
    {
        $db = DBManager::get();

        $sql = "CREATE TABLE lti_data (
                id int(11) NOT NULL AUTO_INCREMENT,
                position int(11) NOT NULL default 0,
                course_id char(32) COLLATE latin1_bin NOT NULL,
                title varchar(255) NOT NULL default '',
                description text NOT NULL default '',
                tool_id int(11) NOT NULL default 0,
                launch_url varchar(255) NOT NULL default '',
                mkdate int(11) NOT NULL default 0,
                chdate int(11) NOT NULL default 0,
                options text NULL,
                PRIMARY KEY (id),
                KEY course_id (course_id))";
        $db->exec($sql);

        $sql = "CREATE TABLE lti_tool (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL default '',
                launch_url varchar(255) NOT NULL default '',
                consumer_key varchar(255) NOT NULL default '',
                consumer_secret varchar(255) NOT NULL default '',
                custom_parameters text NOT NULL,
                allow_custom_url tinyint(1) NOT NULL default 0,
                deep_linking tinyint(1) NOT NULL default 0,
                send_lis_person tinyint(1) NOT NULL default 0,
                mkdate int(11) NOT NULL default 0,
                chdate int(11) NOT NULL default 0,
                PRIMARY KEY (id))";
        $db->exec($sql);

        $sql = "CREATE TABLE lti_grade (
                link_id int(11) NOT NULL default 0,
                user_id char(32) COLLATE latin1_bin NOT NULL,
                score float NOT NULL default 0,
                mkdate int(11) NOT NULL default 0,
                chdate int(11) NOT NULL default 0,
                PRIMARY KEY (link_id, user_id))";
        $db->exec($sql);

        // install as core plugin
        $sql = "INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos)
                VALUES ('LtiToolModule', 'LTI-Tool', 'StandardPlugin,SystemPlugin', 'yes', 1)";
        $db->exec($sql);

        $sql = "INSERT INTO roles_plugins (roleid, pluginid) SELECT roleid, ? FROM roles WHERE system = 'y'";
        $db->execute($sql, [$db->lastInsertId()]);

        // install config settings
        $stmt = $db->prepare('INSERT INTO config (field, value, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute(array(
            'name'        => 'LTI_TOOL_TITLE',
            'description' => 'Voreinstellung für den Titel des Reiters "LTI-Tool" im Kurs.',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => 'LTI-Tool'
        ));

        // migrate data from alija plugin
        $result = $db->query("SHOW TABLES LIKE 'alija_grade'");

        if ($result->rowCount() > 0) {
            $db->exec("INSERT INTO lti_data SELECT id, position, course_id, title, description, tool_id,
                            launch_url, mkdate, chdate, options FROM alija_data");
            $db->exec('INSERT INTO lti_tool SELECT * FROM alija_tool');
            $db->exec('INSERT INTO lti_grade SELECT * FROM alija_grade');
        }
    }

    function down()
    {
        $db = DBManager::get();

        $db->exec("DELETE config, config_values FROM config LEFT JOIN config_values USING(field)
                   WHERE field = 'LTI_TOOL_TITLE'");

        $db->exec("DELETE plugins, roles_plugins FROM plugins LEFT JOIN roles_plugins USING(pluginid)
                   WHERE pluginclassname = 'LtiToolModule'");

        $db->exec('DROP table lti_grade, lti_tool, lti_data');

        SimpleORMap::expireTableScheme();
    }
}
