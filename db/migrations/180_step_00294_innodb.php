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

        // Tables to ignore on engine conversion.
        $ignore_tables = array();

        // Get version of database system (MySQL/MariaDB/Percona)
        $data = DBManager::get()->fetchFirst("SELECT VERSION() AS version");
        $version = $data[0];

        // lit_catalog has fulltext indices which InnoDB doesn't support in older versions.
        if (version_compare($version, '5.6', '<')) {
            $ignore_tables[] = 'lit_catalog';
        }

        // Generate necessary conversion SQL queries.
        $query = "SELECT CONCAT('ALTER TABLE `" . $DB_STUDIP_DATABASE . "`.`', TABLE_NAME, '`";
        // Use Barracuda format if database supports it (5.5 upwards).
        if (version_compare($version, '5.5', '>=')) {
            // Get innodb_file_per_table setting
            $data = DBManager::get()->fetchFirst("SHOW VARIABLES LIKE 'innodb_file_per_table'");
            $file_per_table = $data[0];
            if (in_array(strtolower($file_per_table), array('on', '1'))) {
                // Check if Barracuda file format is enabled
                $data = DBManager::get()->fetchFirst("SHOW VARIABLES LIKE 'innodb_file_format'");
                $file_format = $data[0];
                // All prerequisites fulfilled, use Barracuda format
                if ($file_format == 'Barracuda') {
                    $query .= " ROW_FORMAT=COMPACT";
                }
            }
        }
        $query .= " ENGINE=InnoDB;') AS query
            FROM `information_schema`.TABLES WHERE TABLE_SCHEMA='" . $DB_STUDIP_DATABASE . "'
                AND ENGINE='MyISAM'
                AND TABLE_NAME NOT IN (?)";
        $sql = DBManager::get()->fetchAll($query, array($ignore_tables));

        // Now execute the generated queries.
        foreach ($sql as $q) {
            DBManager::get()->execute($q['query']);
        }

    }

    /**
     * Convert all databases back to MyISAM engine.
     */
    public function down()
    {
        // Generate necessary conversion SQL queries.
        $query = "SELECT CONCAT('ALTER TABLE `" . $DB_STUDIP_DATABASE . "`.`', TABLE_NAME, '` ENGINE=MyISAM;') AS query
            FROM `information_schema`.TABLES WHERE TABLE_SCHEMA='" . $DB_STUDIP_DATABASE . "'
                AND ENGINE='InnoDB'";
        $sql = DBManager::get()->fetchAll($query);

        // Now execute the generated queries.
        foreach ($sql as $q) {
            DBManager::get()->execute($q['query']);
        }
    }
}
