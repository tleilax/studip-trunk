-- phpMyAdmin SQL Dump
-- version 2.7.0-pl1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 17. November 2006 um 12:27
-- Server Version: 5.0.26
-- PHP-Version: 4.4.4-0.dotdeb.1
-- 
-- Datenbank: `studip15`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `Institute`
-- 

DROP TABLE IF EXISTS `Institute`;
CREATE TABLE IF NOT EXISTS `Institute` (
  `Institut_id` varchar(32) NOT NULL default '',
  `Name` varchar(255) NOT NULL default '',
  `fakultaets_id` varchar(32) NOT NULL default '',
  `Strasse` varchar(255) NOT NULL default '',
  `Plz` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default 'http://www.studip.de',
  `telefon` varchar(32) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `fax` varchar(32) NOT NULL default '',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `modules` int(10) unsigned default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `lit_plugin_name` varchar(255) default NULL,
  `srienabled` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`Institut_id`),
  KEY `fakultaets_id` (`fakultaets_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `admission_seminar_studiengang`
-- 

DROP TABLE IF EXISTS `admission_seminar_studiengang`;
CREATE TABLE IF NOT EXISTS `admission_seminar_studiengang` (
  `seminar_id` varchar(32) NOT NULL default '',
  `studiengang_id` varchar(32) NOT NULL default '',
  `quota` int(3) NOT NULL default '0',
  PRIMARY KEY  (`seminar_id`,`studiengang_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `admission_seminar_user`
-- 

DROP TABLE IF EXISTS `admission_seminar_user`;
CREATE TABLE IF NOT EXISTS `admission_seminar_user` (
  `user_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '',
  `studiengang_id` varchar(32) NOT NULL default '',
  `status` varchar(16) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `position` int(5) default NULL,
  `comment` tinytext,
  `visible` enum('yes','no','unknown') NOT NULL default 'unknown',
  PRIMARY KEY  (`user_id`,`seminar_id`,`studiengang_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `archiv`
-- 

DROP TABLE IF EXISTS `archiv`;
CREATE TABLE IF NOT EXISTS `archiv` (
  `seminar_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `untertitel` varchar(255) NOT NULL default '',
  `beschreibung` text NOT NULL,
  `start_time` int(20) NOT NULL default '0',
  `semester` varchar(16) NOT NULL default '',
  `heimat_inst_id` varchar(32) NOT NULL default '',
  `institute` varchar(255) NOT NULL default '',
  `dozenten` varchar(255) NOT NULL default '',
  `fakultaet` varchar(255) NOT NULL default '',
  `dump` mediumtext NOT NULL,
  `archiv_file_id` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `forumdump` longtext NOT NULL,
  `wikidump` longtext,
  `studienbereiche` text NOT NULL,
  `VeranstaltungsNummer` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`seminar_id`),
  KEY `heimat_inst_id` (`heimat_inst_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `archiv_user`
-- 

DROP TABLE IF EXISTS `archiv_user`;
CREATE TABLE IF NOT EXISTS `archiv_user` (
  `seminar_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `status` enum('user','autor','tutor','dozent') NOT NULL default 'user',
  PRIMARY KEY  (`seminar_id`,`user_id`),
  KEY `user_id` (`user_id`,`status`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `auth_extern`
-- 

DROP TABLE IF EXISTS `auth_extern`;
CREATE TABLE IF NOT EXISTS `auth_extern` (
  `studip_user_id` varchar(32) NOT NULL default '',
  `external_user_id` varchar(32) NOT NULL default '',
  `external_user_name` varchar(64) NOT NULL default '',
  `external_user_password` varchar(32) NOT NULL default '',
  `external_user_category` varchar(32) NOT NULL default '',
  `external_user_system_type` varchar(32) NOT NULL default '',
  `external_user_type` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`studip_user_id`,`external_user_system_type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `auth_user_md5`
-- 

DROP TABLE IF EXISTS `auth_user_md5`;
CREATE TABLE IF NOT EXISTS `auth_user_md5` (
  `user_id` varchar(32) NOT NULL default '',
  `username` varchar(64) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `perms` enum('user','autor','tutor','dozent','admin','root') NOT NULL default 'user',
  `Vorname` varchar(64) default NULL,
  `Nachname` varchar(64) default NULL,
  `Email` varchar(64) default NULL,
  `auth_plugin` varchar(64) default NULL,
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `lock_comment` varchar(255) default NULL,
  `locked_by` varchar(32) default NULL,
  `visible` enum('always','yes','unknown','no','never') NOT NULL default 'unknown',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `k_username` (`username`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `banner_ads`
-- 

DROP TABLE IF EXISTS `banner_ads`;
CREATE TABLE IF NOT EXISTS `banner_ads` (
  `ad_id` varchar(32) NOT NULL default '',
  `banner_path` varchar(255) NOT NULL default '',
  `description` varchar(255) default NULL,
  `alttext` varchar(255) default NULL,
  `target_type` enum('url','seminar','inst','user','none') NOT NULL default 'url',
  `target` varchar(255) NOT NULL default '',
  `startdate` int(20) NOT NULL default '0',
  `enddate` int(20) NOT NULL default '0',
  `priority` int(4) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `clicks` int(11) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`ad_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `calendar_events`
-- 

DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE IF NOT EXISTS `calendar_events` (
  `event_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `autor_id` varchar(32) NOT NULL default '',
  `uid` varchar(255) NOT NULL default '',
  `start` int(10) unsigned NOT NULL default '0',
  `end` int(10) unsigned NOT NULL default '0',
  `summary` varchar(255) NOT NULL default '',
  `description` text,
  `class` enum('PUBLIC','PRIVATE','CONFIDENTIAL') NOT NULL default 'PUBLIC',
  `categories` tinytext,
  `category_intern` tinyint(3) unsigned NOT NULL default '0',
  `priority` tinyint(3) unsigned NOT NULL default '0',
  `location` tinytext,
  `ts` int(10) unsigned NOT NULL default '0',
  `linterval` smallint(5) unsigned default NULL,
  `sinterval` smallint(5) unsigned default NULL,
  `wdays` varchar(7) default NULL,
  `month` tinyint(3) unsigned default NULL,
  `day` tinyint(3) unsigned default NULL,
  `rtype` enum('SINGLE','DAILY','WEEKLY','MONTHLY','YEARLY') NOT NULL default 'SINGLE',
  `duration` smallint(5) unsigned NOT NULL default '0',
  `count` smallint(5) default '0',
  `expire` int(10) unsigned NOT NULL default '0',
  `exceptions` text,
  `mkdate` int(10) unsigned NOT NULL default '0',
  `chdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`event_id`),
  UNIQUE KEY `uid_range` (`uid`,`range_id`),
  KEY `autor_id` (`autor_id`),
  KEY `range_id` (`range_id`,`class`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `chat_data`
-- 

DROP TABLE IF EXISTS `chat_data`;
CREATE TABLE IF NOT EXISTS `chat_data` (
  `id` int(11) NOT NULL default '0',
  `data` mediumblob NOT NULL,
  `tstamp` timestamp NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `comments`
-- 

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` varchar(32) NOT NULL default '',
  `object_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `content` text NOT NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`comment_id`),
  KEY `object_id` (`object_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `config`
-- 

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `config_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `field` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `is_default` tinyint(4) NOT NULL default '0',
  `type` enum('boolean','integer','string') NOT NULL default 'boolean',
  `range` enum('global','user') NOT NULL default 'global',
  `section` varchar(255) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `description` varchar(255) NOT NULL default '',
  `comment` text NOT NULL,
  `message_template` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`config_id`),
  KEY `parent_id` (`parent_id`),
  KEY `field` (`field`,`range`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `contact`
-- 

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `contact_id` varchar(32) NOT NULL default '',
  `owner_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `buddy` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`contact_id`),
  KEY `owner_id` (`owner_id`,`buddy`,`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `contact_userinfo`
-- 

DROP TABLE IF EXISTS `contact_userinfo`;
CREATE TABLE IF NOT EXISTS `contact_userinfo` (
  `userinfo_id` varchar(32) NOT NULL default '',
  `contact_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `priority` int(11) NOT NULL default '0',
  PRIMARY KEY  (`userinfo_id`),
  KEY `contact_id` (`contact_id`),
  KEY `priority` (`priority`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `datafields`
-- 

DROP TABLE IF EXISTS `datafields`;
CREATE TABLE IF NOT EXISTS `datafields` (
  `datafield_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `object_type` enum('sem','inst','user') default NULL,
  `object_class` varchar(10) default NULL,
  `edit_perms` enum('user','autor','tutor','dozent','admin','root') default NULL,
  `view_perms` enum('all','user','autor','tutor','dozent','admin','root') NOT NULL default 'all',
  `priority` tinyint(3) unsigned NOT NULL default '0',
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`datafield_id`),
  KEY `object_type` (`object_type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `datafields_entries`
-- 

DROP TABLE IF EXISTS `datafields_entries`;
CREATE TABLE IF NOT EXISTS `datafields_entries` (
  `datafield_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `content` text,
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`datafield_id`,`range_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `dokumente`
-- 

DROP TABLE IF EXISTS `dokumente`;
CREATE TABLE IF NOT EXISTS `dokumente` (
  `dokument_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `description` text NOT NULL,
  `filename` varchar(255) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `filesize` int(20) NOT NULL default '0',
  `autor_host` varchar(20) NOT NULL default '',
  `downloads` int(20) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `protected` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`dokument_id`),
  KEY `range_id` (`range_id`),
  KEY `seminar_id` (`seminar_id`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`),
  KEY `mkdate` (`mkdate`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `eval`
-- 

DROP TABLE IF EXISTS `eval`;
CREATE TABLE IF NOT EXISTS `eval` (
  `eval_id` varchar(32) NOT NULL default '',
  `author_id` varchar(32) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `startdate` int(20) default NULL,
  `stopdate` int(20) default NULL,
  `timespan` int(20) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `anonymous` tinyint(1) NOT NULL default '1',
  `visible` tinyint(1) NOT NULL default '1',
  `shared` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`eval_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `eval_group_template`
-- 

DROP TABLE IF EXISTS `eval_group_template`;
CREATE TABLE IF NOT EXISTS `eval_group_template` (
  `evalgroup_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `group_type` varchar(250) NOT NULL default 'normal',
  PRIMARY KEY  (`evalgroup_id`,`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `eval_range`
-- 

DROP TABLE IF EXISTS `eval_range`;
CREATE TABLE IF NOT EXISTS `eval_range` (
  `eval_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`eval_id`,`range_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `eval_templates`
-- 

DROP TABLE IF EXISTS `eval_templates`;
CREATE TABLE IF NOT EXISTS `eval_templates` (
  `template_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) default NULL,
  `institution_id` varchar(32) default NULL,
  `name` varchar(255) NOT NULL default '',
  `show_questions` tinyint(1) NOT NULL default '1',
  `show_total_stats` tinyint(1) NOT NULL default '1',
  `show_graphics` tinyint(1) NOT NULL default '1',
  `show_questionblock_headline` tinyint(1) NOT NULL default '1',
  `show_group_headline` tinyint(1) NOT NULL default '1',
  `polscale_gfx_type` varchar(255) NOT NULL default 'bars',
  `likertscale_gfx_type` varchar(255) NOT NULL default 'bars',
  `mchoice_scale_gfx_type` varchar(255) NOT NULL default 'bars',
  `kurzbeschreibung` varchar(255) default NULL,
  PRIMARY KEY  (`template_id`),
  KEY `user_id` (`user_id`,`institution_id`,`name`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `eval_templates_eval`
-- 

DROP TABLE IF EXISTS `eval_templates_eval`;
CREATE TABLE IF NOT EXISTS `eval_templates_eval` (
  `eval_id` varchar(32) NOT NULL default '',
  `template_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`eval_id`),
  KEY `eval_id` (`eval_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `eval_templates_user`
-- 

DROP TABLE IF EXISTS `eval_templates_user`;
CREATE TABLE IF NOT EXISTS `eval_templates_user` (
  `eval_id` varchar(32) NOT NULL default '',
  `template_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  KEY `eval_id` (`eval_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `eval_user`
-- 

DROP TABLE IF EXISTS `eval_user`;
CREATE TABLE IF NOT EXISTS `eval_user` (
  `eval_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`eval_id`,`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `evalanswer`
-- 

DROP TABLE IF EXISTS `evalanswer`;
CREATE TABLE IF NOT EXISTS `evalanswer` (
  `evalanswer_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  `text` text NOT NULL,
  `value` int(11) NOT NULL default '0',
  `rows` tinyint(4) NOT NULL default '0',
  `counter` int(11) NOT NULL default '0',
  `residual` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`evalanswer_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `evalanswer_user`
-- 

DROP TABLE IF EXISTS `evalanswer_user`;
CREATE TABLE IF NOT EXISTS `evalanswer_user` (
  `evalanswer_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`evalanswer_id`,`user_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `evalgroup`
-- 

DROP TABLE IF EXISTS `evalgroup`;
CREATE TABLE IF NOT EXISTS `evalgroup` (
  `evalgroup_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `position` int(11) NOT NULL default '0',
  `child_type` enum('EvaluationGroup','EvaluationQuestion') NOT NULL default 'EvaluationGroup',
  `mandatory` tinyint(1) NOT NULL default '0',
  `template_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`evalgroup_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `evalquestion`
-- 

DROP TABLE IF EXISTS `evalquestion`;
CREATE TABLE IF NOT EXISTS `evalquestion` (
  `evalquestion_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `type` enum('likertskala','multiplechoice','polskala') NOT NULL default 'multiplechoice',
  `position` int(11) NOT NULL default '0',
  `text` text NOT NULL,
  `multiplechoice` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`evalquestion_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `extern_config`
-- 

DROP TABLE IF EXISTS `extern_config`;
CREATE TABLE IF NOT EXISTS `extern_config` (
  `config_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `config_type` int(4) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `is_standard` int(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`config_id`,`range_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `folder`
-- 

DROP TABLE IF EXISTS `folder`;
CREATE TABLE IF NOT EXISTS `folder` (
  `folder_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text,
  `permission` tinyint(3) unsigned NOT NULL default '7',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`folder_id`),
  KEY `user_id` (`user_id`),
  KEY `range_id` (`range_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `guestbook`
-- 

DROP TABLE IF EXISTS `guestbook`;
CREATE TABLE IF NOT EXISTS `guestbook` (
  `post_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `content` text NOT NULL,
  PRIMARY KEY  (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `range_id` (`range_id`,`mkdate`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `kategorien`
-- 

DROP TABLE IF EXISTS `kategorien`;
CREATE TABLE IF NOT EXISTS `kategorien` (
  `kategorie_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `hidden` tinyint(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `priority` int(11) NOT NULL default '0',
  PRIMARY KEY  (`kategorie_id`),
  KEY `priority` (`priority`),
  KEY `range_id` (`range_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `lit_catalog`
-- 

DROP TABLE IF EXISTS `lit_catalog`;
CREATE TABLE IF NOT EXISTS `lit_catalog` (
  `catalog_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(11) NOT NULL default '0',
  `chdate` int(11) NOT NULL default '0',
  `lit_plugin` varchar(100) NOT NULL default 'Studip',
  `accession_number` varchar(100) default NULL,
  `dc_title` varchar(255) NOT NULL default '',
  `dc_creator` varchar(255) NOT NULL default '',
  `dc_subject` varchar(255) default NULL,
  `dc_description` text,
  `dc_publisher` varchar(255) default NULL,
  `dc_contributor` varchar(255) default NULL,
  `dc_date` date default NULL,
  `dc_type` varchar(100) default NULL,
  `dc_format` varchar(100) default NULL,
  `dc_identifier` varchar(255) default NULL,
  `dc_source` varchar(255) default NULL,
  `dc_language` varchar(10) default NULL,
  `dc_relation` varchar(255) default NULL,
  `dc_coverage` varchar(255) default NULL,
  `dc_rights` varchar(255) default NULL,
  PRIMARY KEY  (`catalog_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `lit_list`
-- 

DROP TABLE IF EXISTS `lit_list`;
CREATE TABLE IF NOT EXISTS `lit_list` (
  `list_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `format` varchar(255) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(11) NOT NULL default '0',
  `chdate` int(11) NOT NULL default '0',
  `priority` smallint(6) NOT NULL default '0',
  `visibility` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`list_id`),
  KEY `range_id` (`range_id`),
  KEY `priority` (`priority`),
  KEY `visibility` (`visibility`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `lit_list_content`
-- 

DROP TABLE IF EXISTS `lit_list_content`;
CREATE TABLE IF NOT EXISTS `lit_list_content` (
  `list_element_id` varchar(32) NOT NULL default '',
  `list_id` varchar(32) NOT NULL default '',
  `catalog_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(11) NOT NULL default '0',
  `chdate` int(11) NOT NULL default '0',
  `note` varchar(255) default NULL,
  `priority` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`list_element_id`),
  KEY `list_id` (`list_id`),
  KEY `catalog_id` (`catalog_id`),
  KEY `priority` (`priority`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `log_actions`
-- 

DROP TABLE IF EXISTS `log_actions`;
CREATE TABLE IF NOT EXISTS `log_actions` (
  `action_id` varchar(32) NOT NULL default '',
  `name` varchar(128) NOT NULL default '',
  `description` varchar(64) default NULL,
  `info_template` text,
  `active` tinyint(1) NOT NULL default '1',
  `expires` int(20) default NULL,
  PRIMARY KEY  (`action_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `log_events`
-- 

DROP TABLE IF EXISTS `log_events`;
CREATE TABLE IF NOT EXISTS `log_events` (
  `event_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `action_id` varchar(32) NOT NULL default '',
  `affected_range_id` varchar(32) default NULL,
  `coaffected_range_id` varchar(32) default NULL,
  `info` text,
  `dbg_info` text,
  `mkdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`event_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `message`
-- 

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `message_id` varchar(32) NOT NULL default '',
  `chat_id` varchar(32) default NULL,
  `autor_id` varchar(32) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `mkdate` int(20) NOT NULL default '0',
  `readed` tinyint(1) NOT NULL default '0',
  `reading_confirmation` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `chat_id` (`chat_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `message_user`
-- 

DROP TABLE IF EXISTS `message_user`;
CREATE TABLE IF NOT EXISTS `message_user` (
  `user_id` char(32) NOT NULL default '',
  `message_id` char(32) NOT NULL default '',
  `readed` tinyint(1) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `snd_rec` enum('rec','snd') NOT NULL default 'rec',
  `dont_delete` tinyint(1) NOT NULL default '0',
  `folder` tinyint(4) NOT NULL default '0',
  `confirmed_read` tinyint(1) NOT NULL default '0',
  `answered` tinyint(1) NOT NULL default '0',
  `mkdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`,`snd_rec`,`user_id`),
  KEY `user_id` (`user_id`,`snd_rec`,`deleted`,`readed`,`mkdate`),
  KEY `user_id_2` (`user_id`,`snd_rec`,`deleted`,`folder`,`mkdate`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `news`
-- 

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `news_id` varchar(32) NOT NULL default '',
  `topic` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `author` varchar(255) NOT NULL default '',
  `date` int(11) NOT NULL default '0',
  `user_id` varchar(32) NOT NULL default '',
  `expire` int(11) NOT NULL default '0',
  `allow_comments` tinyint(1) NOT NULL default '0',
  `chdate` int(10) unsigned NOT NULL default '0',
  `chdate_uid` varchar(32) NOT NULL default '',
  `mkdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`news_id`),
  KEY `date` (`date`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `news_range`
-- 

DROP TABLE IF EXISTS `news_range`;
CREATE TABLE IF NOT EXISTS `news_range` (
  `news_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`news_id`,`range_id`),
  KEY `range_id` (`range_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `news_rss_range`
-- 

DROP TABLE IF EXISTS `news_rss_range`;
CREATE TABLE IF NOT EXISTS `news_rss_range` (
  `range_id` char(32) NOT NULL default '',
  `rss_id` char(32) NOT NULL default '',
  `range_type` enum('user','sem','inst','global') NOT NULL default 'user',
  PRIMARY KEY  (`range_id`),
  KEY `rss_id` (`rss_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `object_contentmodules`
-- 

DROP TABLE IF EXISTS `object_contentmodules`;
CREATE TABLE IF NOT EXISTS `object_contentmodules` (
  `object_id` varchar(32) NOT NULL default '',
  `module_id` varchar(32) NOT NULL default '',
  `system_type` varchar(32) NOT NULL default '',
  `module_type` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`module_id`,`system_type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `object_rate`
-- 

DROP TABLE IF EXISTS `object_rate`;
CREATE TABLE IF NOT EXISTS `object_rate` (
  `object_id` varchar(32) NOT NULL default '',
  `rate` int(10) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  KEY `object_id` (`object_id`),
  KEY `rate` (`rate`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `object_user`
-- 

DROP TABLE IF EXISTS `object_user`;
CREATE TABLE IF NOT EXISTS `object_user` (
  `object_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `flag` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`user_id`,`flag`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `object_user_visits`
-- 

DROP TABLE IF EXISTS `object_user_visits`;
CREATE TABLE IF NOT EXISTS `object_user_visits` (
  `object_id` char(32) NOT NULL default '',
  `user_id` char(32) NOT NULL default '',
  `type` enum('vote','documents','forum','literature','schedule','scm','sem','wiki','news','eval','inst','ilias_connect','elearning_interface') NOT NULL default 'vote',
  `visitdate` int(20) NOT NULL default '0',
  `last_visitdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`user_id`,`type`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `object_views`
-- 

DROP TABLE IF EXISTS `object_views`;
CREATE TABLE IF NOT EXISTS `object_views` (
  `object_id` varchar(32) NOT NULL default '',
  `views` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`object_id`),
  KEY `views` (`views`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `plugins`
-- 

DROP TABLE IF EXISTS `plugins`;
CREATE TABLE IF NOT EXISTS `plugins` (
  `pluginid` int(10) unsigned NOT NULL auto_increment,
  `pluginclassname` varchar(255) NOT NULL default '',
  `pluginpath` varchar(255) NOT NULL default '',
  `pluginname` varchar(45) NOT NULL default '',
  `plugindesc` varchar(45) NOT NULL default '',
  `plugintype` enum('Standard','Administration','System','Homepage','Portal','Core') NOT NULL default 'Standard',
  `enabled` enum('yes','no') NOT NULL default 'no',
  `navigationpos` int(10) unsigned NOT NULL default '4294967295',
  `dependentonid` int(10) unsigned default NULL,
  PRIMARY KEY  (`pluginid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `plugins_activated`
-- 

DROP TABLE IF EXISTS `plugins_activated`;
CREATE TABLE IF NOT EXISTS `plugins_activated` (
  `pluginid` int(10) unsigned NOT NULL default '0',
  `poiid` varchar(255) NOT NULL default '',
  `state` enum('on','off') NOT NULL default 'on',
  PRIMARY KEY  (`pluginid`,`poiid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `plugins_default_activations`
-- 

DROP TABLE IF EXISTS `plugins_default_activations`;
CREATE TABLE IF NOT EXISTS `plugins_default_activations` (
  `pluginid` int(10) unsigned NOT NULL default '0',
  `institutid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`pluginid`,`institutid`)
) TYPE=MyISAM COMMENT='default activations of standard plugins';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `px_topics`
-- 

DROP TABLE IF EXISTS `px_topics`;
CREATE TABLE IF NOT EXISTS `px_topics` (
  `topic_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `root_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `description` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `author` varchar(255) default NULL,
  `author_host` varchar(255) default NULL,
  `Seminar_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `read_only` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  KEY `root_id` (`root_id`),
  KEY `Seminar_id` (`Seminar_id`),
  KEY `parent_id` (`parent_id`),
  KEY `chdate` (`chdate`),
  KEY `mkdate` (`mkdate`),
  KEY `user_id` (`user_id`,`Seminar_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `range_tree`
-- 

DROP TABLE IF EXISTS `range_tree`;
CREATE TABLE IF NOT EXISTS `range_tree` (
  `item_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `level` int(11) NOT NULL default '0',
  `priority` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `studip_object` varchar(10) default NULL,
  `studip_object_id` varchar(32) default NULL,
  PRIMARY KEY  (`item_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_assign`
-- 

DROP TABLE IF EXISTS `resources_assign`;
CREATE TABLE IF NOT EXISTS `resources_assign` (
  `assign_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `assign_user_id` varchar(32) default NULL,
  `user_free_name` varchar(255) default NULL,
  `begin` int(20) NOT NULL default '0',
  `end` int(20) NOT NULL default '0',
  `repeat_end` int(20) default NULL,
  `repeat_quantity` int(2) default NULL,
  `repeat_interval` int(2) default NULL,
  `repeat_month_of_year` int(2) default NULL,
  `repeat_day_of_month` int(2) default NULL,
  `repeat_week_of_month` int(2) default NULL,
  `repeat_day_of_week` int(2) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`assign_id`),
  KEY `resource_id` (`resource_id`),
  KEY `assign_user_id` (`assign_user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_categories`
-- 

DROP TABLE IF EXISTS `resources_categories`;
CREATE TABLE IF NOT EXISTS `resources_categories` (
  `category_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `system` tinyint(4) NOT NULL default '0',
  `is_room` tinyint(4) NOT NULL default '0',
  `iconnr` int(3) default '1',
  PRIMARY KEY  (`category_id`),
  KEY `is_room` (`is_room`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_categories_properties`
-- 

DROP TABLE IF EXISTS `resources_categories_properties`;
CREATE TABLE IF NOT EXISTS `resources_categories_properties` (
  `category_id` varchar(32) NOT NULL default '',
  `property_id` varchar(32) NOT NULL default '',
  `requestable` tinyint(4) NOT NULL default '0',
  `system` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`property_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_locks`
-- 

DROP TABLE IF EXISTS `resources_locks`;
CREATE TABLE IF NOT EXISTS `resources_locks` (
  `lock_id` varchar(32) NOT NULL default '',
  `lock_begin` int(20) unsigned default NULL,
  `lock_end` int(20) unsigned default NULL,
  `type` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`lock_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_objects`
-- 

DROP TABLE IF EXISTS `resources_objects`;
CREATE TABLE IF NOT EXISTS `resources_objects` (
  `resource_id` varchar(32) NOT NULL default '',
  `root_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `category_id` varchar(32) NOT NULL default '',
  `owner_id` varchar(32) NOT NULL default '',
  `institut_id` varchar(32) NOT NULL default '',
  `level` int(4) default NULL,
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `lockable` tinyint(4) default NULL,
  `multiple_assign` tinyint(4) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`resource_id`),
  KEY `institut_id` (`institut_id`),
  KEY `root_id` (`root_id`),
  KEY `parent_id` (`parent_id`),
  KEY `category_id` (`category_id`),
  KEY `owner_id` (`owner_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_objects_properties`
-- 

DROP TABLE IF EXISTS `resources_objects_properties`;
CREATE TABLE IF NOT EXISTS `resources_objects_properties` (
  `resource_id` varchar(32) NOT NULL default '',
  `property_id` varchar(32) NOT NULL default '',
  `state` text NOT NULL,
  PRIMARY KEY  (`resource_id`,`property_id`),
  KEY `property_id` (`property_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_properties`
-- 

DROP TABLE IF EXISTS `resources_properties`;
CREATE TABLE IF NOT EXISTS `resources_properties` (
  `property_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `type` set('bool','text','num','select') NOT NULL default 'bool',
  `options` text NOT NULL,
  `system` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`property_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_requests`
-- 

DROP TABLE IF EXISTS `resources_requests`;
CREATE TABLE IF NOT EXISTS `resources_requests` (
  `request_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '',
  `termin_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `category_id` varchar(32) NOT NULL default '',
  `comment` text,
  `closed` tinyint(3) unsigned default NULL,
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`request_id`),
  KEY `termin_id` (`termin_id`),
  KEY `seminar_id` (`seminar_id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`),
  KEY `category_id` (`category_id`),
  KEY `closed` (`closed`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_requests_properties`
-- 

DROP TABLE IF EXISTS `resources_requests_properties`;
CREATE TABLE IF NOT EXISTS `resources_requests_properties` (
  `request_id` varchar(32) NOT NULL default '',
  `property_id` varchar(32) NOT NULL default '',
  `state` text,
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`request_id`,`property_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_temporary_events`
-- 

DROP TABLE IF EXISTS `resources_temporary_events`;
CREATE TABLE IF NOT EXISTS `resources_temporary_events` (
  `event_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `assign_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '',
  `termin_id` varchar(32) NOT NULL default '',
  `begin` int(20) NOT NULL default '0',
  `end` int(20) NOT NULL default '0',
  `type` varchar(15) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`event_id`),
  KEY `resource_id` (`resource_id`),
  KEY `assign_object_id` (`assign_id`)
) TYPE=MEMORY;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `resources_user_resources`
-- 

DROP TABLE IF EXISTS `resources_user_resources`;
CREATE TABLE IF NOT EXISTS `resources_user_resources` (
  `user_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `perms` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`resource_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `roles`
-- 

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `roleid` int(10) unsigned NOT NULL auto_increment,
  `rolename` varchar(80) NOT NULL default '',
  `system` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`roleid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `roles_plugins`
-- 

DROP TABLE IF EXISTS `roles_plugins`;
CREATE TABLE IF NOT EXISTS `roles_plugins` (
  `roleid` int(10) unsigned NOT NULL default '0',
  `pluginid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`roleid`,`pluginid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `roles_studipperms`
-- 

DROP TABLE IF EXISTS `roles_studipperms`;
CREATE TABLE IF NOT EXISTS `roles_studipperms` (
  `roleid` int(10) unsigned NOT NULL default '0',
  `permname` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`roleid`,`permname`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `roles_user`
-- 

DROP TABLE IF EXISTS `roles_user`;
CREATE TABLE IF NOT EXISTS `roles_user` (
  `roleid` int(10) unsigned NOT NULL default '0',
  `userid` char(32) NOT NULL default '',
  PRIMARY KEY  (`roleid`,`userid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `rss_feeds`
-- 

DROP TABLE IF EXISTS `rss_feeds`;
CREATE TABLE IF NOT EXISTS `rss_feeds` (
  `feed_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `url` text NOT NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `priority` int(11) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `fetch_title` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`feed_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `scm`
-- 

DROP TABLE IF EXISTS `scm`;
CREATE TABLE IF NOT EXISTS `scm` (
  `scm_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `tab_name` varchar(20) NOT NULL default 'Info',
  `content` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`scm_id`),
  UNIQUE KEY `range_id` (`range_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `sem_tree`
-- 

DROP TABLE IF EXISTS `sem_tree`;
CREATE TABLE IF NOT EXISTS `sem_tree` (
  `sem_tree_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `priority` tinyint(4) NOT NULL default '0',
  `info` text NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `studip_object_id` varchar(32) default NULL,
  PRIMARY KEY  (`sem_tree_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `semester_data`
-- 

DROP TABLE IF EXISTS `semester_data`;
CREATE TABLE IF NOT EXISTS `semester_data` (
  `semester_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `semester_token` varchar(10) NOT NULL default '',
  `beginn` int(20) unsigned default NULL,
  `ende` int(20) unsigned default NULL,
  `vorles_beginn` int(20) unsigned default NULL,
  `vorles_ende` int(20) unsigned default NULL,
  PRIMARY KEY  (`semester_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `semester_holiday`
-- 

DROP TABLE IF EXISTS `semester_holiday`;
CREATE TABLE IF NOT EXISTS `semester_holiday` (
  `holiday_id` varchar(32) NOT NULL default '',
  `semester_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `beginn` int(20) unsigned default NULL,
  `ende` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`holiday_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `seminar_inst`
-- 

DROP TABLE IF EXISTS `seminar_inst`;
CREATE TABLE IF NOT EXISTS `seminar_inst` (
  `seminar_id` varchar(32) NOT NULL default '',
  `institut_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`seminar_id`,`institut_id`),
  KEY `institut_id` (`institut_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `seminar_lernmodul`
-- 

DROP TABLE IF EXISTS `seminar_lernmodul`;
CREATE TABLE IF NOT EXISTS `seminar_lernmodul` (
  `seminar_id` varchar(32) NOT NULL default '',
  `co_inst` int(11) unsigned NOT NULL default '0',
  `co_id` int(11) unsigned NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`seminar_id`,`co_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `seminar_sem_tree`
-- 

DROP TABLE IF EXISTS `seminar_sem_tree`;
CREATE TABLE IF NOT EXISTS `seminar_sem_tree` (
  `seminar_id` varchar(32) NOT NULL default '',
  `sem_tree_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`seminar_id`,`sem_tree_id`),
  KEY `sem_tree_id` (`sem_tree_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `seminar_user`
-- 

DROP TABLE IF EXISTS `seminar_user`;
CREATE TABLE IF NOT EXISTS `seminar_user` (
  `Seminar_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `status` enum('user','autor','tutor','dozent') NOT NULL default 'user',
  `gruppe` tinyint(4) NOT NULL default '0',
  `admission_studiengang_id` varchar(32) NOT NULL default '',
  `notification` int(10) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `visible` enum('yes','no','unknown') NOT NULL default 'unknown',
  PRIMARY KEY  (`Seminar_id`,`user_id`),
  KEY `status` (`status`,`Seminar_id`),
  KEY `user_id` (`user_id`,`status`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `seminare`
-- 

DROP TABLE IF EXISTS `seminare`;
CREATE TABLE IF NOT EXISTS `seminare` (
  `Seminar_id` varchar(32) NOT NULL default '0',
  `VeranstaltungsNummer` varchar(100) default NULL,
  `Institut_id` varchar(32) NOT NULL default '0',
  `Name` varchar(255) NOT NULL default '',
  `Untertitel` varchar(255) default NULL,
  `status` tinyint(4) unsigned NOT NULL default '1',
  `Beschreibung` text NOT NULL,
  `Ort` varchar(255) default NULL,
  `Sonstiges` text,
  `Passwort` varchar(32) default NULL,
  `Lesezugriff` tinyint(4) NOT NULL default '0',
  `Schreibzugriff` tinyint(4) NOT NULL default '0',
  `start_time` int(20) default '0',
  `duration_time` int(20) default NULL,
  `art` varchar(255) default NULL,
  `teilnehmer` text,
  `vorrausetzungen` text,
  `lernorga` text,
  `leistungsnachweis` text,
  `metadata_dates` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `ects` varchar(32) default NULL,
  `admission_endtime` int(20) default NULL,
  `admission_turnout` int(5) default NULL,
  `admission_binding` tinyint(4) default NULL,
  `admission_type` int(3) NOT NULL default '0',
  `admission_selection_take_place` tinyint(4) default '0',
  `admission_group` varchar(32) default NULL,
  `admission_prelim` tinyint(4) unsigned NOT NULL default '0',
  `admission_prelim_txt` text,
  `admission_starttime` int(20) NOT NULL default '-1',
  `admission_endtime_sem` int(20) NOT NULL default '-1',
  `visible` tinyint(2) unsigned NOT NULL default '1',
  `showscore` tinyint(3) default '0',
  `modules` int(10) unsigned default NULL,
  PRIMARY KEY  (`Seminar_id`),
  KEY `Institut_id` (`Institut_id`),
  KEY `visible` (`visible`),
  KEY `status` (`status`,`Seminar_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `session_data`
-- 

DROP TABLE IF EXISTS `session_data`;
CREATE TABLE IF NOT EXISTS `session_data` (
  `sid` varchar(32) NOT NULL default '',
  `val` mediumtext NOT NULL,
  `changed` timestamp NOT NULL,
  PRIMARY KEY  (`sid`),
  KEY `changed` (`changed`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `smiley`
-- 

DROP TABLE IF EXISTS `smiley`;
CREATE TABLE IF NOT EXISTS `smiley` (
  `smiley_id` int(11) unsigned NOT NULL auto_increment,
  `smiley_name` varchar(50) NOT NULL default '',
  `smiley_width` int(11) NOT NULL default '0',
  `smiley_height` int(11) NOT NULL default '0',
  `short_name` varchar(50) NOT NULL default '',
  `smiley_counter` int(11) unsigned NOT NULL default '0',
  `short_counter` int(11) unsigned NOT NULL default '0',
  `fav_counter` int(11) unsigned NOT NULL default '0',
  `mkdate` int(10) unsigned default NULL,
  `chdate` int(10) unsigned default NULL,
  PRIMARY KEY  (`smiley_id`),
  UNIQUE KEY `name` (`smiley_name`),
  KEY `short` (`short_name`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `statusgruppe_user`
-- 

DROP TABLE IF EXISTS `statusgruppe_user`;
CREATE TABLE IF NOT EXISTS `statusgruppe_user` (
  `statusgruppe_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`statusgruppe_id`,`user_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `statusgruppen`
-- 

DROP TABLE IF EXISTS `statusgruppen`;
CREATE TABLE IF NOT EXISTS `statusgruppen` (
  `statusgruppe_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `position` int(20) NOT NULL default '0',
  `size` int(20) NOT NULL default '0',
  `selfassign` tinyint(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`statusgruppe_id`),
  KEY `range_id` (`range_id`),
  KEY `position` (`position`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `studiengaenge`
-- 

DROP TABLE IF EXISTS `studiengaenge`;
CREATE TABLE IF NOT EXISTS `studiengaenge` (
  `studiengang_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `beschreibung` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`studiengang_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `studip_ilias`
-- 

DROP TABLE IF EXISTS `studip_ilias`;
CREATE TABLE IF NOT EXISTS `studip_ilias` (
  `studip_user_id` varchar(32) NOT NULL default '',
  `ilias_user_id` int(11) unsigned NOT NULL default '0',
  `is_created` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`studip_user_id`,`ilias_user_id`),
  KEY `is_created` (`is_created`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `support_contract`
-- 

DROP TABLE IF EXISTS `support_contract`;
CREATE TABLE IF NOT EXISTS `support_contract` (
  `contract_id` varchar(32) NOT NULL default '',
  `institut_id` varchar(32) default NULL,
  `range_id` varchar(32) default NULL,
  `given_points` int(20) unsigned NOT NULL default '0',
  `contract_begin` int(20) unsigned NOT NULL default '0',
  `contract_end` int(20) unsigned NOT NULL default '0',
  `mkdate` int(20) unsigned NOT NULL default '0',
  `chdate` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`contract_id`),
  KEY `contract_id` (`contract_id`,`institut_id`,`range_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `support_event`
-- 

DROP TABLE IF EXISTS `support_event`;
CREATE TABLE IF NOT EXISTS `support_event` (
  `event_id` varchar(32) NOT NULL default '',
  `request_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `begin` int(20) unsigned NOT NULL default '0',
  `end` int(20) unsigned NOT NULL default '0',
  `used_points` int(4) unsigned NOT NULL default '0',
  `mkdate` int(20) unsigned NOT NULL default '0',
  `chdate` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`event_id`),
  KEY `event_id` (`event_id`,`user_id`,`mkdate`,`request_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `support_request`
-- 

DROP TABLE IF EXISTS `support_request`;
CREATE TABLE IF NOT EXISTS `support_request` (
  `request_id` varchar(32) NOT NULL default '',
  `contract_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `date` int(20) unsigned NOT NULL default '0',
  `user_id` varchar(32) NOT NULL default '0',
  `channel` tinyint(3) unsigned default '0',
  `topic_id` varchar(32) NOT NULL default '',
  `mkdate` int(20) unsigned NOT NULL default '0',
  `chdate` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`request_id`),
  KEY `contract_id` (`contract_id`,`topic_id`,`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `termine`
-- 

DROP TABLE IF EXISTS `termine`;
CREATE TABLE IF NOT EXISTS `termine` (
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
  `raum` varchar(255) default NULL,
  PRIMARY KEY  (`termin_id`),
  KEY `autor_id` (`autor_id`),
  KEY `range_id` (`range_id`,`topic_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `user_config`
-- 

DROP TABLE IF EXISTS `user_config`;
CREATE TABLE IF NOT EXISTS `user_config` (
  `userconfig_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) default NULL,
  `user_id` varchar(32) NOT NULL default '',
  `field` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `mkdate` int(11) NOT NULL default '0',
  `chdate` int(11) NOT NULL default '0',
  `comment` text NOT NULL,
  PRIMARY KEY  (`userconfig_id`),
  KEY `user_id` (`user_id`,`field`,`value`(5))
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `user_data`
-- 

DROP TABLE IF EXISTS `user_data`;
CREATE TABLE IF NOT EXISTS `user_data` (
  `sid` varchar(32) NOT NULL default '',
  `val` mediumtext NOT NULL,
  `changed` timestamp NOT NULL,
  PRIMARY KEY  (`sid`),
  KEY `changed` (`changed`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `user_info`
-- 

DROP TABLE IF EXISTS `user_info`;
CREATE TABLE IF NOT EXISTS `user_info` (
  `user_id` varchar(32) NOT NULL default '',
  `hobby` varchar(255) NOT NULL default '',
  `lebenslauf` text,
  `publi` text NOT NULL,
  `schwerp` text NOT NULL,
  `Home` varchar(200) NOT NULL default '',
  `privatnr` varchar(32) NOT NULL default '',
  `privatcell` varchar(32) NOT NULL default '',
  `privadr` varchar(64) NOT NULL default '',
  `score` int(11) unsigned NOT NULL default '0',
  `geschlecht` tinyint(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `title_front` varchar(64) NOT NULL default '',
  `title_rear` varchar(64) NOT NULL default '',
  `preferred_language` varchar(6) default NULL,
  `smsforward_copy` tinyint(1) NOT NULL default '1',
  `smsforward_rec` varchar(32) NOT NULL default '',
  `guestbook` tinyint(4) NOT NULL default '0',
  `email_forward` tinyint(4) NOT NULL default '0',
  `smiley_favorite` varchar(255) NOT NULL default '',
  `smiley_favorite_publish` tinyint(1) NOT NULL default '0',
  `motto` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  KEY `score` (`score`,`guestbook`),
  KEY `guestbook` (`user_id`,`score`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `user_inst`
-- 

DROP TABLE IF EXISTS `user_inst`;
CREATE TABLE IF NOT EXISTS `user_inst` (
  `user_id` varchar(32) NOT NULL default '0',
  `Institut_id` varchar(32) NOT NULL default '0',
  `inst_perms` enum('user','autor','tutor','dozent','admin') NOT NULL default 'user',
  `sprechzeiten` varchar(200) NOT NULL default '',
  `raum` varchar(200) NOT NULL default '',
  `Telefon` varchar(32) NOT NULL default '',
  `Fax` varchar(32) NOT NULL default '',
  `externdefault` tinyint(3) unsigned NOT NULL default '0',
  `priority` tinyint(3) unsigned NOT NULL default '0',
  `visible` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`Institut_id`,`user_id`),
  KEY `inst_perms` (`inst_perms`,`Institut_id`),
  KEY `user_id` (`user_id`,`inst_perms`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `user_studiengang`
-- 

DROP TABLE IF EXISTS `user_studiengang`;
CREATE TABLE IF NOT EXISTS `user_studiengang` (
  `user_id` varchar(32) NOT NULL default '',
  `studiengang_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`studiengang_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `vote`
-- 

DROP TABLE IF EXISTS `vote`;
CREATE TABLE IF NOT EXISTS `vote` (
  `vote_id` varchar(32) NOT NULL default '',
  `author_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `type` enum('vote','test') NOT NULL default 'vote',
  `title` varchar(100) NOT NULL default '',
  `question` text NOT NULL,
  `state` enum('new','active','stopvis','stopinvis') NOT NULL default 'new',
  `startdate` int(20) default NULL,
  `stopdate` int(20) default NULL,
  `timespan` int(20) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `resultvisibility` enum('ever','delivery','end','never') NOT NULL default 'ever',
  `multiplechoice` tinyint(1) NOT NULL default '0',
  `anonymous` tinyint(1) NOT NULL default '1',
  `changeable` tinyint(1) NOT NULL default '0',
  `co_visibility` tinyint(1) default NULL,
  `namesvisibility` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  KEY `range_id` (`range_id`),
  KEY `state` (`state`),
  KEY `startdate` (`startdate`),
  KEY `stopdate` (`stopdate`),
  KEY `resultvisibility` (`resultvisibility`),
  KEY `chdate` (`chdate`),
  KEY `author_id` (`author_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `vote_user`
-- 

DROP TABLE IF EXISTS `vote_user`;
CREATE TABLE IF NOT EXISTS `vote_user` (
  `vote_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `votedate` int(20) default NULL,
  PRIMARY KEY  (`vote_id`,`user_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `voteanswers`
-- 

DROP TABLE IF EXISTS `voteanswers`;
CREATE TABLE IF NOT EXISTS `voteanswers` (
  `answer_id` varchar(32) NOT NULL default '',
  `vote_id` varchar(32) NOT NULL default '',
  `answer` varchar(255) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  `counter` int(11) NOT NULL default '0',
  `correct` tinyint(1) default NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `vote_id` (`vote_id`),
  KEY `position` (`position`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `voteanswers_user`
-- 

DROP TABLE IF EXISTS `voteanswers_user`;
CREATE TABLE IF NOT EXISTS `voteanswers_user` (
  `answer_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `votedate` int(20) default NULL,
  PRIMARY KEY  (`answer_id`,`user_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `wap_sessions`
-- 

DROP TABLE IF EXISTS `wap_sessions`;
CREATE TABLE IF NOT EXISTS `wap_sessions` (
  `user_id` char(32) NOT NULL default '',
  `session_id` char(32) NOT NULL default '',
  `creation_time` datetime default NULL,
  PRIMARY KEY  (`session_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `wiki`
-- 

DROP TABLE IF EXISTS `wiki`;
CREATE TABLE IF NOT EXISTS `wiki` (
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) default NULL,
  `keyword` varchar(128) binary NOT NULL default '',
  `body` text,
  `chdate` int(11) default NULL,
  `version` int(11) NOT NULL default '0',
  PRIMARY KEY  (`range_id`,`keyword`,`version`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `wiki_links`
-- 

DROP TABLE IF EXISTS `wiki_links`;
CREATE TABLE IF NOT EXISTS `wiki_links` (
  `range_id` char(32) NOT NULL default '',
  `from_keyword` char(128) binary NOT NULL default '',
  `to_keyword` char(128) binary NOT NULL default '',
  PRIMARY KEY  (`range_id`,`to_keyword`,`from_keyword`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `wiki_locks`
-- 

DROP TABLE IF EXISTS `wiki_locks`;
CREATE TABLE IF NOT EXISTS `wiki_locks` (
  `user_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `keyword` varchar(128) binary NOT NULL default '',
  `chdate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`range_id`,`user_id`,`keyword`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM;

