<?php
class PerformanceTic3759 extends Migration
{
    function description()
    {
        return 'changes index for plugins_activated';
    }

    function up()
    {
        DBManager::get()->exec("ALTER TABLE  `plugins_activated` CHANGE  `poiid`  `poiid` VARCHAR( 36 ) NOT NULL DEFAULT  ''");
        DBManager::get()->exec("ALTER TABLE  `plugins_activated` ADD UNIQUE  `poiid` (  `poiid` ,  `pluginid` ,  `state` )");
        }

    function down()
    {
    }
}
