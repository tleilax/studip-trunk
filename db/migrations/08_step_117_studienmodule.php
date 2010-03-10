<?
class Step117StudienModule extends DBMigration
{
    var $sql_up = 
"
-- 
-- Tabellenstruktur f�r Tabelle `his_abschl`
-- 

CREATE TABLE `his_abschl` (
  `abint` char(2) default NULL,
  `aikz` char(1) default NULL,
  `ktxt` char(10) default NULL,
  `dtxt` char(25) default NULL,
  `ltxt` char(100) default NULL,
  `astat` char(2) default NULL,
  `hrst` char(10) default NULL,
  `part` char(2) default NULL,
  `anzstg` smallint(6) default NULL,
  `kzfaarray` char(10) default NULL,
  `mag_laa` char(1) default NULL,
  `sortkz1` char(2) default NULL,
  `anzstgmin` smallint(6) default NULL,
  `sprache` char(3) default NULL,
  `refabint` char(2) default NULL,
  `efh` char(4) default NULL,
  PRIMARY KEY  (`abint`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `his_abstgv`
-- 

CREATE TABLE `his_abstgv` (
  `ktxt` varchar(50) default NULL,
  `dtxt` varchar(50) default NULL,
  `ltxt` varchar(100) default NULL,
  `fb` char(2) default NULL,
  `kzfa` char(1) NOT NULL default '',
  `kzfaarray` char(3) default NULL,
  `abschl` char(2) NOT NULL default '',
  `stg` char(3) NOT NULL default '',
  `pversion` int(11) NOT NULL default '0',
  `regelstz` tinyint(2) default NULL,
  `login_part` char(2) default NULL,
  `studip_studiengang` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`abschl`,`stg`,`kzfa`,`pversion`),
  KEY `studip_studiengang` (`studip_studiengang`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `his_pvers`
-- 

CREATE TABLE `his_pvers` (
  `pvers` smallint(6) default NULL,
  `aikz` char(1) default NULL,
  `ktxt` char(10) default NULL,
  `dtxt` char(25) default NULL,
  `ltxt` char(50) default NULL,
  `sprache` char(3) default NULL,
  `refpvers` smallint(6) default NULL,
  PRIMARY KEY  (`pvers`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `his_stg`
-- 

CREATE TABLE `his_stg` (
  `stg` char(3) NOT NULL default '',
  `ktxt` varchar(10) default NULL,
  `dtxt` varchar(25) default NULL,
  `ltxt` varchar(100) default NULL,
  `fb` char(2) default NULL,
  PRIMARY KEY  (`stg`)
) TYPE=MyISAM COMMENT='Studienfaecher aus der HIS DB';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_abstract`
-- 

CREATE TABLE `stm_abstract` (
  `stm_abstr_id` varchar(32) NOT NULL default '',
  `id_number` varchar(10) default NULL COMMENT 'alphanummerische Identifikationsnummer f�r das Modul',
  `duration` varchar(155) default NULL,
  `credits` tinyint(3) unsigned default NULL COMMENT 'Anzahl der Leistungspunkte/Kreditpunkte',
  `workload` smallint(6) unsigned default NULL COMMENT 'Studentischer Arbeitsaufwand in Stunden',
  `turnus` tinyint(1) default NULL COMMENT '(optional) Angebotsturnus - Modulbeginn',
  `mkdate` int(20) default NULL COMMENT 'Erstellungdatum',
  `chdate` int(20) default NULL COMMENT 'Datum der letzten Aenderung',
  `homeinst` varchar(32) default NULL,
  PRIMARY KEY  (`stm_abstr_id`)
) TYPE=MyISAM COMMENT='abstrakte Module';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_abstract_assign`
-- 

CREATE TABLE `stm_abstract_assign` (
  `stm_abstr_id` varchar(32) NOT NULL default '',
  `stm_type_id` varchar(32) NOT NULL default '' COMMENT 'ID eines Modultyps',
  `abschl` char(3) NOT NULL default '' COMMENT 'ID eines Studienabschlusses',
  `stg` char(3) NOT NULL default '' COMMENT 'ID eines Studienprogramms/-fachs',
  `pversion` varchar(8) NOT NULL default '' COMMENT 'Version der Pr�fungsordnung',
  `earliest` tinyint(4) default NULL COMMENT 'fr�hrester Zeitpunkt (Semester)',
  `latest` tinyint(4) default NULL COMMENT 'sp�tester Zpkt.',
  `recommed` tinyint(4) default NULL COMMENT 'empfohlener Zpkt.',
  PRIMARY KEY  (`stm_abstr_id`,`abschl`,`stg`,`pversion`),
  KEY `studycourse` (`abschl`,`stg`)
) TYPE=MyISAM COMMENT='Zuordnung abstrakte Module <-> Studienprogramme';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_abstract_elements`
-- 

CREATE TABLE `stm_abstract_elements` (
  `element_id` varchar(32) NOT NULL default '' COMMENT 'ID eines abstrakten Modulbestandzeiles',
  `stm_abstr_id` varchar(32) NOT NULL default '' COMMENT 'ID eines abstrakten Studienmodules',
  `element_type_id` varchar(32) NOT NULL default '' COMMENT 'um welche Art von Element handelt es sich',
  `custom_name` varchar(50) default NULL COMMENT 'selbstgew�hlter Name',
  `sws` tinyint(4) NOT NULL default '0' COMMENT 'Semesterwochenstunden f�r den Bestandteil',
  `workload` int(4) NOT NULL default '0',
  `semester` tinyint(1) default NULL COMMENT 'Sommer od. Winter (Sommer = 1; Winter = 2)',
  `elementgroup` tinyint(4) NOT NULL default '0' COMMENT 'Kombinationsvariante',
  `position` tinyint(4) NOT NULL default '0' COMMENT 'Reihenfolge ',
  PRIMARY KEY  (`element_id`),
  UNIQUE KEY `elem_integr` (`stm_abstr_id`,`elementgroup`,`position`)
) TYPE=MyISAM COMMENT='Bestandteile eines Abstrakten Moduls (Elemente)';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_abstract_text`
-- 

CREATE TABLE `stm_abstract_text` (
  `stm_abstr_id` varchar(32) NOT NULL default '' COMMENT 'ID des abstrakten Studienmodules',
  `lang_id` varchar(32) NOT NULL default '' COMMENT 'ID der verwendeten Sprache',
  `title` varchar(155) NOT NULL default '' COMMENT 'Allgemeiner Modultitel (Name des Moduls)',
  `subtitle` varchar(155) default NULL COMMENT 'optionaler Untertitel',
  `topics` text NOT NULL COMMENT 'Inhalte (behandelte Themen etc.)',
  `aims` text NOT NULL COMMENT 'Lernziele',
  `hints` text,
  PRIMARY KEY  (`stm_abstr_id`,`lang_id`)
) TYPE=MyISAM COMMENT='(mehrsprachige) Texte der abstrakten Module';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_abstract_types`
-- 

CREATE TABLE `stm_abstract_types` (
  `stm_type_id` varchar(32) NOT NULL default '' COMMENT 'ID eines Modultyps',
  `lang_id` varchar(32) NOT NULL default '' COMMENT 'ID der verwendeten Sprache',
  `abbrev` varchar(5) NOT NULL default '' COMMENT 'Abkuerzung',
  `name` varchar(25) NOT NULL default '' COMMENT 'vollstaendige Bezeichnung',
  PRIMARY KEY  (`stm_type_id`,`lang_id`)
) TYPE=MyISAM COMMENT='Typen abstrakter Module';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_element_types`
-- 

CREATE TABLE `stm_element_types` (
  `element_type_id` varchar(32) NOT NULL default '' COMMENT 'ID des Modulbestandteils',
  `lang_id` varchar(32) NOT NULL default '' COMMENT 'ID der verwendeten Sprache',
  `abbrev` varchar(5) default NULL COMMENT 'Kurzname',
  `name` varchar(50) NOT NULL default '' COMMENT 'Name',
  PRIMARY KEY  (`element_type_id`,`lang_id`)
) TYPE=MyISAM COMMENT='Typen von m�glichen Bestandteilen eines abstrakten Moduls';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_instances`
-- 

CREATE TABLE `stm_instances` (
  `stm_instance_id` varchar(32) NOT NULL default '' COMMENT 'ID eines konkreten Studienmodules',
  `stm_abstr_id` varchar(32) NOT NULL default '' COMMENT 'ID eines abstrakten Studienmodules',
  `semester_id` varchar(32) NOT NULL default '' COMMENT 'ID des ersten Semesters in dem die Instanz stattfindet',
  `lang_id` varchar(32) NOT NULL default '' COMMENT 'ID der Sprache in der das Modul angeboten wird',
  `homeinst` varchar(32) default NULL COMMENT 'ID des anbietenden Institutes',
  `creator` varchar(32) NOT NULL,
  `responsible` varchar(32) default NULL COMMENT 'ID des Modulverantwortlichen Dozenten',
  `complete` tinyint(1) NOT NULL default '0' COMMENT 'Erfassung komplett (0=FALSE)',
  PRIMARY KEY  (`stm_instance_id`)
) TYPE=MyISAM COMMENT='Instanzen der abstrakten Module';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_instances_elements`
-- 

CREATE TABLE `stm_instances_elements` (
  `stm_instance_id` varchar(32) NOT NULL default '' COMMENT 'ID eines konkreten Studienmodules',
  `element_id` varchar(32) NOT NULL default '' COMMENT 'ID des abstrakten Modulbestandteils',
  `sem_id` varchar(32) NOT NULL default '' COMMENT 'ID der konkreten Veranstaltung',
  PRIMARY KEY  (`stm_instance_id`,`element_id`,`sem_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f�r Tabelle `stm_instances_text`
-- 

CREATE TABLE `stm_instances_text` (
  `stm_instance_id` varchar(32) NOT NULL default '' COMMENT 'ID eines konkreten Studienmodules',
  `lang_id` varchar(32) NOT NULL default '' COMMENT 'ID der verwendeten Sprache',
  `title` varchar(155) NOT NULL default '' COMMENT 'Allgemeiner Modultitel',
  `subtitle` varchar(155) default NULL COMMENT 'optionaler Untertitel',
  `topics` text NOT NULL COMMENT 'Inhalte',
  `hints` text,
  PRIMARY KEY  (`stm_instance_id`,`lang_id`)
) TYPE=MyISAM COMMENT='(mehrsprachige) Texte der instanziierten abstrakten Module';
";

    var $sql_down = "DROP TABLE IF EXISTS `his_abschl`, `his_abstgv`, `his_pvers`, `his_stg`, `stm_abstract`, `stm_abstract_assign`, `stm_abstract_elements`, `stm_abstract_text`, `stm_abstract_types`, `stm_element_types`, `stm_instances`, `stm_instances_elements`, `stm_instances_text`;";
    
    function description ()
    {
        return 'modify db schema StEP00117 Studienmodulstrukturen; ';
    }
    
    function up ()
    {
        $this->announce(get_class($this) . ": Creating db schema...");
        $statements = split(";[[:space:]]*\n", $this->sql_up);
        foreach($statements as $sqlstatement) {
            $this->db->query($sqlstatement);    
        }
    }
    
    function down ()
    {
        $this->announce(get_class($this) . ": Deleting db schema...");
        $statements = split(";[[:space:]]*\n", $this->sql_down);
        foreach($statements as $sqlstatement) {
            $this->db->query($sqlstatement);    
        }
    }

}
?>
