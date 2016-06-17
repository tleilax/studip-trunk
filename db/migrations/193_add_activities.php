<?php

class AddActivities extends Migration
{

    public function description() 
    {
        return 'add table for activities';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE `activities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `object_id` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `context` enum('system','course','institute','user') COLLATE latin1_german1_ci NOT NULL,
            `context_id` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `provider` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `actor_type` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `actor_id` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `verb` enum('answered','attempted','attended','completed','created','deleted','edited','experienced','failed','imported','interacted','passed','shared','sent','voided') COLLATE latin1_german1_ci NOT NULL DEFAULT 'experienced',
            `content` text COLLATE latin1_german1_ci NULL,
            `object_type` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `mkdate` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        )");
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE `activities`");
    }
}
