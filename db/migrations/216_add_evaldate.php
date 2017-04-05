<?php

class AddEvaldate extends Migration
{
    function description()
    {
        return 'add column "evaldate" to "evalanswer_user" table';
    }

    function up()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE evalanswer_user ADD evaldate int(11) NOT NULL default 0');
        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE evalanswer_user DROP evaldate');
        SimpleORMap::expireTableScheme();
    }
}