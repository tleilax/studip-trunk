<?php

class AddDedicatedAdminsRole extends Migration
{

    public function description()
    {
        return 'Non-admins may now be dedicated admins, which means they can see the admin-area with restricted rights.';
    }

    public function up()
    {        
        DBManager::get()->exec("
            INSERT INTO `roles` (`rolename`, `system`)
            VALUES ('DedicatedAdmin', 'n');
        ");
        StudipCacheFactory::getCache()->expire(RolePersistence::ROLES_CACHE_KEY);
    }

    public function down()
    {
        DBManager::get()->execute("
            DELETE FROM `roles` WHERE `rolename` = 'DedicatedAdmin' AND `system` = 'n'
        ");
        StudipCacheFactory::getCache()->expire(RolePersistence::ROLES_CACHE_KEY);
    }

}
