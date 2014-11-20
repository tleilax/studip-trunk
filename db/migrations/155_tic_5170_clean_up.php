<?php

class Tic5170CleanUp extends Migration {

    function description()
    {
        return 'cleans up a bit.';
    }

    function up()
    {
        $db = DbManager::get();
        $db->exec("ALTER TABLE `message` DROP `chat_id`,`readed`");
        $db->exec("ALTER TABLE `message_user` DROP `confirmed_read`,`answered`");
        $db->exec("ALTER TABLE `user_info` DROP `guestbook`");
        $db->exec("DROP TABLE object_rate");
        $db->exec("DROP TABLE object_user");
        $db->exec("DROP TABLE px_topics");
        $db->exec("DROP TABLE rss_feeds");
        SimpleORMap::expireTableScheme();
    }

    function down()
    {

    }

}

