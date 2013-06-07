<?php
class Step00240CourseSets extends Migration
{

    function description()
    {
        return 'add tables needed for storing new admission related data';
    }

    function up()
    {
        $db = DBManager::get();

        // assign conditions to admission rules
        $db->exec("CREATE TABLE IF NOT EXISTS `admission_condition` (
                `rule_id` VARCHAR(32) NOT NULL ,
                `condition_id` VARCHAR(32) NOT NULL ,
                `mkdate` INT NULL ,
            PRIMARY KEY (`rule_id`, `condition_id`) )
            ENGINE = MyISAM");

        // "chance adjustment" in seat distribution
        $db->exec("CREATE TABLE IF NOT EXISTS `admissionfactor` (
                `list_id` VARCHAR(32) NOT NULL ,
                `name` VARCHAR(255) NULL ,
                `factor` DECIMAL(5,2) NULL ,
                `owner_id` VARCHAR(32) NOT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`list_id`) )
            ENGINE = MyISAM");

        // admission rules specifying conditions for access
        $db->exec("CREATE TABLE IF NOT EXISTS `conditionaladmissions` (
                `rule_id` VARCHAR(32) NOT NULL ,
                `message` TEXT NULL ,
                `mkdate` INT NULL ,
                `conditions_stopped` TINYINT NOT NULL DEFAULT 0 ,
                `chdate` INT NULL ,
            PRIMARY KEY (`rule_id`) )
            ENGINE = MyISAM");

        // several fields form a condition
        $db->exec("CREATE TABLE IF NOT EXISTS `conditionfields` (
                `field_id` VARCHAR(32) NOT NULL ,
                `condition_id` VARCHAR(32) NULL ,
                `type` VARCHAR(255) NULL ,
                `value` VARCHAR(255) NULL ,
                `compare_op` VARCHAR(255) NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`field_id`) )
            ENGINE = MyISAM;");

        // conditions for admission
        $db->exec("CREATE TABLE IF NOT EXISTS `conditions` (
                `condition_id` VARCHAR(32) NOT NULL ,
                `start_time` INT NULL ,
                `end_time` INT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`condition_id`) )
            ENGINE = MyISAM");

        // assign course sets to factor lists
        $db->exec("CREATE TABLE IF NOT EXISTS `courseset_factorlist` (
                `set_id` VARCHAR(32) NOT NULL ,
                `factorlist_id` VARCHAR(32) NOT NULL ,
                `mkdate` INT NULL ,
            PRIMARY KEY (`set_id`, `factorlist_id`) )
            ENGINE = MyISAM");

        // assign admission rules to course sets
        $db->exec("CREATE TABLE IF NOT EXISTS `courseset_rule` (
                `set_id` VARCHAR(32) NOT NULL ,
                `rule_id` VARCHAR(32) NOT NULL ,
                `type` VARCHAR(255) NULL ,
                `mkdate` INT NULL ,
            PRIMARY KEY (`set_id`, `rule_id`) )
            ENGINE = MyISAM");

        // sets of courses with common admission rules
        $db->exec("CREATE TABLE IF NOT EXISTS `coursesets` (
                `set_id` VARCHAR(32) NOT NULL ,
                `user_id` VARCHAR(32) NOT NULL ,
                `institut_id` VARCHAR(32) NOT NULL ,
                `name` VARCHAR(255) NOT NULL ,
                `infotext` TEXT NOT NULL ,
                `algorithm` VARCHAR(255) NOT NULL ,
                `algorithm_run` TINYINT NULL DEFAULT 0 ,
                `invalidate_rules` TINYINT NOT NULL DEFAULT 0 ,
                `mkdate` INT NOT NULL ,
                `chdate` INT NOT NULL ,
            PRIMARY KEY (`set_id`) ,
            INDEX `set_user` (`set_id` ASC, `user_id` ASC) ,
            INDEX `set_institut` (`set_id` ASC, `institut_id` ASC))
            ENGINE MyISAM");

        // admission rules with max number of courses to register for
        $db->exec("CREATE TABLE IF NOT EXISTS `limitedadmissions` (
                `rule_id` VARCHAR(32) NOT NULL ,
                `message` TEXT NULL ,
                `maxnumber` INT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`rule_id`) )
            ENGINE = MyISAM");

        // admission rules that completely lock access to courses
        $db->exec("CREATE TABLE IF NOT EXISTS `lockedadmissions` (
                `rule_id` VARCHAR(32) NOT NULL ,
                `message` TEXT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`rule_id`) )
            ENGINE = MyISAM");

        // admission rules that specify a password for course access
        $db->exec("CREATE TABLE IF NOT EXISTS `passwordadmissions` (
                `rule_id` VARCHAR(32) NOT NULL ,
                `message` TEXT NULL ,
                `password` VARCHAR(255) NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`rule_id`) )
            ENGINE = MyISAM");

        // priorities for course assignment
        $db->exec("CREATE TABLE IF NOT EXISTS `priorities` (
                `user_id` VARCHAR(32) NOT NULL ,
                `set_id` VARCHAR(32) NOT NULL ,
                `seminar_id` VARCHAR(32) NOT NULL ,
                `priority` INT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`user_id`, `set_id`, `seminar_id`) ,
            INDEX `user_rule_priority` (`user_id` ASC, `priority` ASC, `set_id` ASC) )
            ENGINE = MyISAM");

        // assign courses to course sets
        $db->exec("CREATE TABLE IF NOT EXISTS `seminar_courseset` (
                `set_id` VARCHAR(32) NOT NULL ,
                `seminar_id` VARCHAR(32) NOT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`set_id`, `seminar_id`) )
            ENGINE = MyISAM");

        // admission rules concerning time
        $db->exec("CREATE TABLE IF NOT EXISTS `timedadmissions` (
                `rule_id` VARCHAR(32) NOT NULL ,
                `message` TEXT NULL ,
                `start_time` INT NULL ,
                `distribution_time` INT NULL ,
                `end_time` INT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`rule_id`) ,
            INDEX `start_time` (`start_time` ASC) ,
            INDEX `end_time` (`end_time` ASC) ,
            INDEX `dist_time` (`distribution_time` ASC) ,
            INDEX `start_end` (`start_time` ASC, `end_time` ASC) )
            ENGINE = MyISAM;");

        // assign users to lists with different factor in seat distribution
        $db->exec("CREATE TABLE IF NOT EXISTS `user_factorlist` (
                `list_id` VARCHAR(32) NULL ,
                `user_id` VARCHAR(32) NULL ,
                `mkdate` INT NULL ,
            PRIMARY KEY (`list_id`, `user_id`) )
            ENGINE = MyISAM");

        // user defined max number of courses to register for
        $db->exec("CREATE TABLE IF NOT EXISTS `userlimits` (
                `rule_id` VARCHAR(32) NOT NULL ,
                `user_id` VARCHAR(32) NOT NULL ,
                `maxnumber` INT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`rule_id`, `user_id`) )
            ENGINE = MyISAM");

        // waiting lists at courses
        $db->exec("CREATE TABLE IF NOT EXISTS `waitinglist_config` (
                `list_id` VARCHAR(32) NOT NULL ,
                `seminar_id` VARCHAR(32) NOT NULL ,
                `set_id` VARCHAR(32) NULL ,
                `max_users` INT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
            PRIMARY KEY (`list_id`) )
            ENGINE = MyISAM");

        // assign users to waiting lists
        $db->exec("CREATE TABLE IF NOT EXISTS `waitinglist_user` (
                `list_id` VARCHAR(32) NOT NULL ,
                `user_id` VARCHAR(32) NOT NULL ,
                `position` INT NOT NULL ,
                `mkdate` INT NULL ,
            PRIMARY KEY (`user_id`, `list_id`) )
            ENGINE = MyISAM");
        
        $db->exec("CREATE TABLE IF NOT EXISTS `courseset_institute` (
                `set_id` VARCHAR(32) NOT NULL ,
                `institute_id` VARCHAR(32) NOT NULL ,
                `mkdate` INT NULL ,
                `chdate` INT NULL ,
                PRIMARY KEY (`set_id`, `institute_id`) )
                ENGINE = MyISAM");
    }

    function down()
    {
        $db = DBManager::get();
        // delete all tables related with new admission structure
        $db->exec("DROP TABLE admission_condition`, `admissionfactor`,
            `conditionaladmissions`, `conditionfields`, `conditions`,
            `courseset_factorlist`, `courseset_rule`, `coursesets`,
            `limitedadmissions`, `lockedadmissions`, `priorities`,
            `seminar_courseset`, `timedadmissions`, `user_factorlist`,
            `userlimits`, `waitinglist_config`, `waitinglist_user`");
    }
}
?>
