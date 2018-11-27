<?php
class MigrationsReloaded extends Migration
{
    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `schema_versions` (
                    `domain` VARCHAR(255) NOT NULL DEFAULT '',
                    `version` BIGINT(20) UNSIGNED NOT NULL,
                    INDEX `domain` (`domain`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT";
        DBManager::get()->exec($query);

        $query = "SELECT `domain`, `version`
                  FROM `schema_version`
                  WHERE `version` > 0";
        $rows = DBManager::get()->query($query)->fetchAll(PDO::FETCH_NUM);

        $query = "INSERT INTO `schema_versions`
                  VALUES (:domain, :version)";
        $statement = DBManager::get()->prepare($query);
        foreach ($rows as list($domain, $version)) {
            $statement->bindValue(':domain', $domain);

            for ($i = 1; $i <= $version; $i += 1) {
                $statement->bindValue(':version', $i);
                $statement->execute();
            }
        }

//        $query = "DROP TABLE IF EXISTS `schema_version`";
//        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "CREATE TABLE IF NOT EXISTS `schema_version` (
                    `domain` VARCHAR(255) NOT NULL DEFAULT '',
                    `version` INT(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`domain`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `schema_version`
                  SELECT `domain`, MAX(`version`)
                  FROM `schema_versions`
                  GROUP BY `domain`";
        DBManager::get()->exec($query);

        $query = "DROP TABLE IF EXISTS `schema_versions`";
        DBManager::get()->exec($query);
    }
}
