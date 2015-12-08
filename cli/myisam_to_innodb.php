#!/usr/bin/env php
<?php
require_once(__DIR__.'/studip_cli_env.inc.php');

echo 'Migration starting at '.date('d.m.Y H:i:s').".\n";
$start = microtime(true);

global $DB_STUDIP_DATABASE;

// Tables to ignore on engine conversion.
$ignore_tables = array();

// Check if InnoDB is enabled in database server.
$engines = DBManager::get()->fetchAll("SHOW ENGINES");
$innodb = false;
foreach ($engines as $e) {
    // InnoDB is found and enabled.
    if ($e['Engine'] == 'InnoDB' && in_array($e['Support'], array('DEFAULT', 'YES'))) {
        $innodb = true;
        break;
    }
}

if ($innodb) {
    // Get version of database system (MySQL/MariaDB/Percona)
    $data = DBManager::get()->fetchFirst("SELECT VERSION() AS version");
    $version = $data[0];

    // lit_catalog has fulltext indices which InnoDB doesn't support in older versions.
    if (version_compare($version, '5.6', '<')) {
        echo "\tFound MySQL in version < 5.6, lit_catalog will NOT be converted to InnoDB.\n";
        $ignore_tables[] = 'lit_catalog';
    }

    // Fetch all tables that need to be converted.
    $tables = DBManager::get()->fetchFirst("SELECT TABLE_NAME
        FROM `information_schema`.TABLES
        WHERE TABLE_SCHEMA=:database AND ENGINE=:oldengine
            AND TABLE_NAME NOT IN (:ignore)
        ORDER BY TABLE_NAME",
        array(
            ':database' => $DB_STUDIP_DATABASE,
            ':oldengine' => 'MyISAM',
            ':ignore' => $ignore_tables
        ));

    // Use Barracuda format if database supports it (5.5 upwards).
    if (version_compare($version, '5.5', '>=')) {
        echo "\tFound MySQL in version >= 5.5, checking if Barracuda file format is supported...";
        // Get innodb_file_per_table setting
        $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_per_table'");
        $file_per_table = $data['Value'];

        // Check if Barracuda file format is enabled
        $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_format'");
        $file_format = $data['Value'];

        if (strtolower($file_per_table) == 'on' && strtolower($file_format) == 'barracuda') {
            echo " yes.\n";
            $rowformat = 'DYNAMIC';
        } else {
            echo " no:\n";
            if (strtolower($file_per_table) != 'on') {
                echo "\t- file_per_table not set\n";
            }
            if (strtolower($file_format) != 'barracuda') {
                echo "\t- file_format not set to Barracuda (but to " . $file_format . ")\n";
            }
            $rowformat = 'COMPACT';
        }
    }

    // Prepare query for table conversion.
    $stmt = DBManager::get()->prepare("ALTER TABLE :database.:table ROW_FORMAT=:rowformat ENGINE=:newengine");
    $stmt->bindParam(':database', $DB_STUDIP_DATABASE, StudipPDO::PARAM_COLUMN);
    $stmt->bindParam(':rowformat', $rowformat, StudipPDO::PARAM_COLUMN);
    $newengine = 'InnoDB';
    $stmt->bindParam(':newengine', $newengine, StudipPDO::PARAM_COLUMN);

    // Now convert the found tables.
    foreach ($tables as $t) {
        $local_start = microtime(true);
        $stmt->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
        $stmt->execute();
        $local_end = microtime(true);
        $local_duration = $local_end - $local_start;
        $human_local_duration = sprintf("%02d:%02d:%02d",
            ($local_duration / 60 / 60) % 24, ($local_duration / 60) % 60, $local_duration % 60);

        echo "\tConversion of table " . $t . " took " . $human_local_duration . ".\n";
    }

    /*
     * On MySQL 5.6 and up, lit_catalog was converted to InnoDB. In order
     * to keep the literature search working, we now need several fulltext
     * indices on this table.
     */
    if (version_compare($version, '5.6', '>=')) {
        DBManager::get()->exec("ALTER TABLE `lit_catalog`
            ADD FULLTEXT(`dc_title`,`dc_creator`,`dc_contributor`,`dc_subject`)");
        DBManager::get()->exec("ALTER TABLE `lit_catalog`
            ADD FULLTEXT(`dc_title`)");
        DBManager::get()->exec("ALTER TABLE `lit_catalog`
            ADD FULLTEXT(`dc_creator`,`dc_contributor`)");
        DBManager::get()->exec("ALTER TABLE `lit_catalog`
            ADD FULLTEXT(`dc_subject`)");
        DBManager::get()->exec("ALTER TABLE `lit_catalog`
            ADD FULLTEXT(`dc_description`)");
        DBManager::get()->exec("ALTER TABLE `lit_catalog`
            ADD FULLTEXT(`dc_publisher`)");
        DBManager::get()->exec("ALTER TABLE `lit_catalog`
            ADD FULLTEXT(`dc_identifier`)");
    }

    $end = microtime(true);

    $duration = $end - $start;
    $human_duration = sprintf("%02d:%02d:%02d",
        ($duration / 60 / 60) % 24, ($duration / 60) % 60, $duration % 60);

    echo 'Migration finished at ' . date('d.m.Y H:i:s') . ', duration ' . $human_duration . ".\n";
} else {
    echo "The storage engine InnoDB is not enabled in your ".
        "database installation, tables cannot be converted.\n";
}
