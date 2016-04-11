<?php

class AddActivities extends Migration {

    function description() {
        return 'add table for activities';
    }

    function up() {
        $db = DBManager::get();

        $db->exec("CREATE TABLE `activities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `object_id` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `context` enum('system','course','institute','user') COLLATE latin1_german1_ci NOT NULL,
            `provider` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `actor_type` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `actor_id` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `verb` enum('answered','attempted','attended','completed','created','deleted','edited','experienced','failed','imported','interacted','passed','shared','voided') COLLATE latin1_german1_ci NOT NULL,
            `title` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `content` text COLLATE latin1_german1_ci NOT NULL,
            `object_type` int(11) NOT NULL,
            `object_url` text COLLATE latin1_german1_ci NOT NULL COMMENT 'json',
            `object_route` text COLLATE latin1_german1_ci NOT NULL COMMENT 'json',
            `mkdate` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        )");
    }

    function down() {
        $db = DBManager::get();

        $db->exec("DROP TABLE `activities`");
    }
}
