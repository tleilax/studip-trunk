<?php

class Files extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'add database tables for flexible files';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE files (
                    file_id CHAR(32) NOT NULL,
                    user_id CHAR(32) NOT NULL,
                    mime_type VARCHAR(64) NOT NULL,
                    size BIGINT UNSIGNED NOT NULL,
                    restricted TINYINT(1) NOT NULL DEFAULT 0,
                    storage VARCHAR(32) NOT NULL DEFAULT 'DiskFileStorage',
                    storage_id VARCHAR(32) NOT NULL,
                    mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    chdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    PRIMARY KEY (file_id))");

        $db->exec("CREATE TABLE file_refs (
                    id CHAR(32) NOT NULL,
                    file_id CHAR(32) NOT NULL,
                    parent_id CHAR(32) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    downloads INT NOT NULL DEFAULT 0,
                    PRIMARY KEY (id))");
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE files');
        $db->exec('DROP TABLE file_refs');
    }
}
