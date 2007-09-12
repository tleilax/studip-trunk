<?
class Step25RaumzeitMigrations extends DBMigration
{
    function description ()
    {
        return 'modify db schema for StEP00025; see logfile in $TMP_PATH';
    }

    function up ()
    {
        // open log file
        $logfile_handle = fopen( $GLOBALS["TMP_PATH"] ."/Stud.IP_date_conversion.log", "ab");
        if(!$logfile_handle) {
            throw new Exception ("Can't open logfile ".$GLOBALS["TMP_PATH"]."/Stud.IP_date_conversion.log");
        }
        
        $this->write( get_class($this)." - Creating db schema...");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `themen` (
              `issue_id` varchar(32) NOT NULL default '',
              `seminar_id` varchar(32) NOT NULL default '',
              `author_id` varchar(32) NOT NULL default '',
              `title` varchar(255) NOT NULL default '',
              `description` mediumtext NOT NULL,
              `priority` int(11) NOT NULL default '0',
              `mkdate` int(11) NOT NULL default '0',
              `chdate` int(11) NOT NULL default '0',
              PRIMARY KEY (`issue_id`)
            );
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ex_termine` (
              `termin_id` varchar(32) NOT NULL default '',
              `range_id` varchar(32) NOT NULL default '',
              `autor_id` varchar(32) NOT NULL default '',
              `content` varchar(255) NOT NULL default '',
              `description` text,
              `date` int(20) NOT NULL default '0',
              `end_time` int(20) NOT NULL default '0',
              `mkdate` int(20) NOT NULL default '0',
              `chdate` int(20) NOT NULL default '0',
              `date_typ` tinyint(4) NOT NULL default '0',
              `topic_id` varchar(32) default NULL,
              `expire` int(20) default NULL,
              `repeat` varchar(128) default NULL,
              `color` varchar(20) default NULL,
              `priority` tinyint(4) default NULL,
              `raum` varchar(255) default NULL,
              `metadate_id` varchar(32) default NULL,
              `resource_id` varchar(32) NOT NULL default '',
              PRIMARY KEY  (`termin_id`),
              KEY `range_id` (`range_id`),
              KEY `autor_id` (`autor_id`),
                            KEY `metadate_id` (`metadate_id`),
                            KEY `date` (`date`)
            ) TYPE=MyISAM PACK_KEYS=1;
        ");
            
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `themen_termine` (
              `issue_id` varchar(32) NOT NULL default '',
              `termin_id` varchar(32) NOT NULL default '',
              PRIMARY KEY  (`issue_id`,`termin_id`)
            ) TYPE=MyISAM;
        ");
            
        $this->db->query("
            ALTER TABLE `termine` ADD `metadate_id` VARCHAR( 32 );
        ");

        $this->db->query("
            ALTER TABLE `termine` ADD INDEX ( `metadate_id` );
        ");
            
        $this->db->query("
            ALTER TABLE `termine` ADD INDEX ( `date` );
        ");
            
        $this->db->query("
            ALTER TABLE `resources_requests` ADD `reply_comment` TEXT AFTER `comment`;        
        ");
        
        // move "RESOURCES_ENABLE" from config_local.inc.php to config table:
        if( $GLOBALS["RESOURCES_ENABLE"] ){
            // if "true", insert this as a local customization
            $this->db->query("
                INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
                VALUES ( 'dade8ea9ac4ec346e796ab9449d35b0e' , '', 'RESOURCES_ENABLE', '1', '0', 'boolean', 'global', '', '0', '0', '0', 'Enable the Stud.IP resource management module', '', '');
            ");
        }

        // RESOURCES_ENABLE default value (=false)
        $this->db->query("
            INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES ( '06cdb765fb8f0853e3ebe08f51c3596e' , '', 'RESOURCES_ENABLE', '0', '1', 'boolean', 'global', '', '0', '0', '0', 'Enable the Stud.IP resource management module', '', '');
        ");
        
        $this->db->query("
            INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES ( '93da66ca9e2d17df5bc61bd56406add7' , '', 'RESOURCES_ROOM_REQUEST_DEFAULT_ACTION', 'NO_ROOM_INFO_ACTION', '1', 'string', 'global', '', '0', '0', '0', 'Designates the pre-selected action for the room request dialog', 'Valid values are: NO_ROOM_INFO_ACTION, ROOM_REQUEST_ACTION, BOOKING_OF_ROOM_ACTION, FREETEXT_ROOM_ACTION', '');
        ");
        
        $this->db->query("
            INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
            VALUES ('0d3f84ed4dd6b7147b504ffb5b6fbc2c', '', 'RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW', '0', 1, 'boolean', 'global', '', 0, 12, 12, 'Enables the expert view of the course schedules', '', '');
        ");

        $this->db->query("
            INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES ( 'bc3004618b17b29dc65e10e89be9a7a0', '', 'RESOURCES_ENABLE_BOOKINGSTATUS_COLORING', '1', '1', 'boolean', 'global', '', '0', '0', '0', 'Enable the colored presentation of the room booking status of a date', '', '');
        ");
        
        $this->write( get_class($this).": Finished with creating db schema.");
        
        // close logfile
        fclose($logfile_handle);        
    }
}
?>
