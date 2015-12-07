<?php
/**
 * Migration for StEP00294
 *
 * @author  Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 *
 * @see https://develop.studip.de/trac/ticket/5415
 */
class StEP00294InnoDB extends Migration
{
    function description()
    {
        return 'Converts the Stud.IP database tables to InnoDB engine';
    }

    /**
     * Convert all tables to InnoDB engine, using Barracuda format if supported.
     */
    public function up()
    {
        global $DB_STUDIP_DATABASE;

        // Unset max_execution_time, this migration could take a while.
        ini_set('max_execution_time', 0);

        // Tables to ignore on engine conversion.
        $ignore_tables = array();

        // Get version of database system (MySQL/MariaDB/Percona)
        $data = DBManager::get()->fetchFirst("SELECT VERSION() AS version");
        $version = $data[0];

        // lit_catalog has fulltext indices which InnoDB doesn't support in older versions.
        if (version_compare($version, '5.6', '<')) {
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
            // Get innodb_file_per_table setting
            $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_per_table'");
            $file_per_table = $data['Value'];

            // Check if Barracuda file format is enabled
            $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_format'");
            $file_format = $data['Value'];

            // All settings ok, use Barracuda.
            if (strtolower($file_per_table) == 'on' && strtolower($file_format) == 'barracuda') {
                $rowformat = 'DYNAMIC';
            // Barracuda cannot be enabled, use Antelope format.
            } else {
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
            $stmt->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
            $stmt->execute();
        }

        /*
         * On MySQL 5.6 and up, lit_catalog was converted to InnoDB. In order
         * to keep the literature search working, we now need several fulltext
         * indices on this table.
         */
        if (version_compare($version, '5.6', '>=')) {
            DBManager::get()->exec("ALTER TABLE `lit_catalog`
                ADD FULLTEXT(`dc_title`,`dc_creator`,`dc_contributor`,`dc_subject`),
                ADD FULLTEXT(`dc_title`),
                ADD FULLTEXT(`dc_creator`,`dc_contributor`),
                ADD FULLTEXT(`dc_subject`),
                ADD FULLTEXT(`dc_description`),
                ADD FULLTEXT(`dc_publisher`),
                ADD FULLTEXT(`dc_identifier`)");
        }

    }

    /**
     * Convert all databases back to MyISAM engine.
     */
    public function down()
    {
        global $DB_STUDIP_DATABASE;

        // Fetch all tables that need to be converted.
        $tables = DBManager::get()->fetchFirst("SELECT TABLE_NAME
            FROM `information_schema`.TABLES
            WHERE TABLE_SCHEMA=:database AND ENGINE=:oldengine
            ORDER BY TABLE_NAME",
            array(
                ':database' => $DB_STUDIP_DATABASE,
                ':oldengine' => 'InnoDB'
            ));

        // Prepare query for table conversion.
        $stmt = DBManager::get()->prepare("ALTER TABLE :database.:table ENGINE=:newengine");
        $stmt->bindParam(':database', $DB_STUDIP_DATABASE, StudipPDO::PARAM_COLUMN);
        $newengine = 'MyISAM';
        $stmt->bindParam(':newengine', $newengine, StudipPDO::PARAM_COLUMN);

        // Now convert the found tables.
        foreach ($tables as $t) {
            $stmt->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
            $stmt->execute();
        }

    }

}
