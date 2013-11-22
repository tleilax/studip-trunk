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
                    id varchar(32) NOT NULL,
                    user_id varchar(32) NOT NULL,
                    mime_type varchar(64) NOT NULL,
                    size int NOT NULL,
                    restricted int NOT NULL,
                    storage varchar(32) NOT NULL DEFAULT 'DiskFileStorage',
                    storage_id varchar(32) NOT NULL,
                    mkdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    chdate timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (id)) ENGINE=MyISAM");

        $db->exec("CREATE TABLE file_refs (
                    id varchar(32) NOT NULL,
                    file_id varchar(32) NOT NULL,
                    parent_id varchar(32) NOT NULL,
                    name varchar(255) NOT NULL,
                    description text NOT NULL,
                    downloads int NOT NULL DEFAULT 0,
                    PRIMARY KEY (id)) ENGINE=MyISAM");
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
