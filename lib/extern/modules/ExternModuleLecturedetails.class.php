<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
* ExternModuleLecturedetails.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access		public
* @modulegroup	extern
* @module		ExternModuleLecturedetails
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleLecturedetails.class.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require_once($GLOBALS["RELATIVE_PATH_EXTERN"].'/lib/ExternModule.class.php');
require_once($GLOBALS["RELATIVE_PATH_EXTERN"].'/views/extern_html_templates.inc.php');
require_once($GLOBALS["RELATIVE_PATH_EXTERN"].'/modules/views/ExternSemBrowse.class.php');
require_once($GLOBALS["RELATIVE_PATH_EXTERN"].'/lib/extern_functions.inc.php');

require_once('lib/classes/DataFieldEntry.class.php');
require_once('lib/language.inc.php');
require_once('lib/visual.inc.php');
require_once('lib/dates.inc.php');
require_once 'lib/functions.php';

class ExternModuleLecturedetails extends ExternModule {

	// private
	var $db;
	// private
	var $seminar_id;

	/**
	*
	*/
	function ExternModuleLecturedetails ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
		$this->data_fields = array('subtitle', 'lecturer', 'art', 'status', 'description',
			'location', 'semester', 'time', 'number', 'teilnehmer', 'requirements',
			'lernorga', 'leistung', 'range_path', 'misc', 'ects');
		$this->registered_elements = array(
			'ReplaceTextSemType',
			'Body',
			'TableHeader',
			'SemName' => 'TableParagraphText',
			'Headline' => 'TableParagraphText',
			'Content' => 'TableParagraphText',
			'LinkInternSimple' => 'LinkIntern',
			'StudipInfo',
			'StudipLink');
		$this->args = array('seminar_id');
		$this->field_names = array(
				_("Untertitel"),
				_("DozentIn"),
				_("Veranstaltungsart"),
				_("Veranstaltungstyp"),
				_("Beschreibung"),
				_("Ort"),
				_("Semester"),
				_("Zeiten"),
				_("Veranstaltungsnummer"),
				_("TeilnehmerInnen"),
				_("Voraussetzungen"),
				_("Lernorganisation"),
				_("Leistungsnachweis"),
				_("Bereichseinordnung"),
				_("Sonstiges"),
				_("ECTS-Kreditpunkte"));
		parent::ExternModule($range_id, $module_name, $config_id, $set_config, $global_id);
	}
	
	function setup () {
		// extend $data_fields if generic datafields are set
		$config_datafields = $this->config->getValue("Main", "genericdatafields");
		$this->data_fields = array_merge((array)$this->data_fields, (array)$config_datafields);
		
		// setup module properties
		$this->elements["SemName"]->real_name = _("Name der Veranstaltung");
		$this->elements["Headline"]->real_name = _("&Uuml;berschriften");
		$this->elements["Content"]->real_name = _("Abs&auml;tze");
		$this->elements["LinkInternSimple"]->link_module_type = 2;
		$this->elements["LinkInternSimple"]->real_name = _("Link zum Modul Mitarbeiterdetails");
	}
	
	function printout ($args) {
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_header($this->config);
		
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		echo $this->toString($args);
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_header($this->config);
		
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		include($GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/lecturedetails_preview.inc.php");
				
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function toString ($args) {
		$out = "";
		$this->seminar_id = $args["seminar_id"];
		$this->db =& new DB_Seminar();
		$query = "SELECT * FROM seminare WHERE Seminar_id='$this->seminar_id'";
		$this->db->query($query);
		$visible = $this->config->getValue("Main", "visible");
		
		$j = -1;
		if ($this->db->next_record()) {
		
			$data["name"] = htmlReady($this->db->f("Name"));
			
			if ($visible[++$j] && $this->db->f("Untertitel"))
				$data["subtitle"] = htmlReady($this->db->f("Untertitel"));
			
			if ($visible[++$j]) {
				if (!$name_sql = $this->config->getValue("Main", "nameformat"))
					$name_sql = "full";
				$name_sql = $GLOBALS['_fullname_sql'][$name_sql];
				$db_lecturer =& new DB_Seminar();
				$db_lecturer->query("SELECT $name_sql AS name, username, position FROM seminar_user su LEFT JOIN
						auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id)
						WHERE su.Seminar_id='{$this->seminar_id}' AND su.status='dozent' ORDER BY position, username");
				while ($db_lecturer->next_record()) {
					$data["lecturer"][] = $this->elements["LinkInternSimple"]->toString(
							array("module" => "Persondetails",
							"link_args" => "username=" . $db_lecturer->f("username")
							. "&seminar_id=" . $this->seminar_id,
							"content" => $db_lecturer->f("name")));
				}
				if (is_array($data["lecturer"]))
					$data["lecturer"] = implode(", ", $data["lecturer"]);
			}
			
			if ($visible[++$j] && $this->db->f("art"))
				$data["art"] = htmlReady($this->db->f("art"));
			
			if ($visible[++$j]) {
				// reorganize the $SEM_TYPE-array
				foreach ($GLOBALS["SEM_CLASS"] as $key_class => $class) {
					$i = 0;
					foreach ($GLOBALS["SEM_TYPE"] as $key_type => $type) {
						if ($type["class"] == $key_class) {
							$i++;
							$sem_types_position[$key_type] = $i;
						}
					}
				}
				
				$aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
						"class_" . $GLOBALS["SEM_TYPE"][$this->db->f("status")]['class']);
				if ($aliases_sem_type[$sem_types_position[$this->db->f("status")] - 1])
					$data["status"] =  $aliases_sem_type[$sem_types_position[$this->db->f("status")] - 1];
				else
					$data["status"] = htmlReady($GLOBALS["SEM_TYPE"][$this->db->f("status")]["name"]);
			}
			
			if ($visible[++$j] && $this->db->f("Beschreibung"))
				$data["description"] = FixLinks(htmlReady($this->db->f("Beschreibung")), TRUE, TRUE, FALSE, TRUE);
			
			if ($visible[++$j])
				$data["location"] = getRoom($this->seminar_id, FALSE);
			
			if ($visible[++$j])
				$data["semester"] = get_semester($this->seminar_id);
			
			if ($visible[++$j]) {
				$data["time"] = htmlReady(view_turnus($this->seminar_id, FALSE, FALSE));
				if ($first_app = vorbesprechung($this->seminar_id)) {
					$data["time"] .= "<br>" . $this->config->getValue("Main", "aliaspredisc") . $first_app;
				}
				if ($begin = veranstaltung_beginn($this->seminar_id)) {
					$data["time"] .= "<br>" . $this->config->getValue("Main", "aliasfirstmeeting") . $begin;
				}
			}
			
			if ($visible[++$j] && $this->db->f("VeranstaltungsNummer"))
				$data["number"] = htmlReady($this->db->f("VeranstaltungsNummer"));
			
			if ($visible[++$j] && $this->db->f("teilnehmer"))
				$data["teilnehmer"] = htmlReady($this->db->f("teilnehmer"));
			
			if ($visible[++$j] && $this->db->f("vorrausetzungen"))
				$data["requirements"] = htmlReady($this->db->f("vorrausetzungen"));
			
			if ($visible[++$j] && $this->db->f("lernorga"))
				$data["lernorga"] = htmlReady($this->db->f("lernorga"));
			
			if ($visible[++$j] && $this->db->f("leistungsnachweis"))
				$data["leistung"] = htmlReady($this->db->f("leistungsnachweis"));
			
			if ($visible[++$j]) {
				$range_path_level = $this->config->getValue("Main", "rangepathlevel");
				$pathes = get_sem_tree_path($this->seminar_id, $range_path_level);
				if (is_array($pathes)) {
					$data["range_path"] = htmlReady(implode("\n", array_values($pathes)),true,true);
				}
			}
			
			if ($visible[++$j] && $this->db->f("Sonstiges"))
				$data["misc"] = FixLinks(htmlReady($this->db->f("Sonstiges")), TRUE, TRUE, FALSE, TRUE);
			
			if ($visible[++$j] && $this->db->f("ects"))
				$data["ects"] = htmlReady($this->db->f("ects"));
			
			// generic data fields
			if ($generic_datafields = $this->config->getValue("Main", "genericdatafields")) {
//				$datafields_obj =& new DataFields($this->seminar_id);
//				$datafields = $datafields_obj->getLocalFields($this->seminar_id);
				$localEntries = DataFieldEntry::getDataFieldEntries($this->seminar_id);
				foreach ($generic_datafields as $id) {
					if ($visible[++$j] && isset($localEntries[$id]) && is_object($localEntries[$id])) {
						$data[$id] = $localEntries[$id]->getDisplayValue();
					}
				}
			}
			$out = $this->toStringMainTable($data, FALSE); 
		}
		
		return $out;
	}
	
	function toStringPreview ($args) {		
		// reorganize the $SEM_TYPE-array
		foreach ($GLOBALS["SEM_CLASS"] as $key_class => $class) {
			$i = 0;
			foreach ($GLOBALS["SEM_TYPE"] as $key_type => $type) {
				if ($type["class"] == $key_class) {
					$i++;
					$sem_types_position[$key_type] = $i;
				}
			}
		}
		
		$data_sem["name"] = _("Name der Veranstaltung");
		$data_sem["subtitle"] = _("Untertitel der Veranstaltung");
		switch ($this->config->getValue("Main", "nameformat")) {
			case "no_title_short" :
				$data_sem["lecturer"] = _("Meyer, P.");
				break;
			case "no_title" :
				$data_sem["lecturer"] = _("Peter Meyer");
				break;
			case "no_title_rev" :
				$data_sem["lecturer"] = _("Meyer Peter");
				break;
			case "full" :
				$data_sem["lecturer"] = _("Dr. Peter Meyer");
				break;
			case "full_rev" :
				$data_sem["lecturer"] = _("Meyer, Peter, Dr.");
				break;
			default :
				$data_sem["lecturer"] = _("Dr. Peter Meyer");
		}
		$data_sem["art"] = _("Testveranstaltung");
		$data_sem["semtype"] = 1;
		$data_sem["description"] = str_repeat(_("Beschreibung") . " ", 10);
		$data_sem["location"] = _("A 123, 1. Stock");
		$data_sem["semester"] = "WS 2003/2004";
		$data_sem["time"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
		$data_sem["number"] = "1234";
		$data_sem["teilnehmer"] = str_repeat(_("Teilnehmer") . " ", 6);
		$data_sem["requirements"] = str_repeat(_("Voraussetzungen") . " ", 6);
		$data_sem["lernorga"] = str_repeat(_("Lernorganisation") . " ", 6);
		$data_sem["leistung"] = str_repeat(_("Leistungsnachweis") . " ", 6);
		$data_sem["range_path"] = _("Fakult&auml;t &gt; Studiengang &gt; Bereich");
		$data_sem["misc"] = str_repeat(_("Sonstiges") . " ", 6);
		$data_sem["ects"] = "4";
		
		
		setlocale(LC_TIME, $this->config->getValue("Main", "timelocale"));
		$order = $this->config->getValue("Main", "order");
		$visible = $this->config->getValue("Main", "visible");
		$aliases = $this->config->getValue("Main", "aliases");
		$j = -1;
		
		$data["name"] = $data_sem["name"];
		
		if ($visible[++$j])
			$data["subtitle"] = $data_sem["subtitle"];
		
		if ($visible[++$j]) {
			$data["lecturer"][] = sprintf("<a href=\"\"%s>%s</a>",
					$this->config->getAttributes("LinkInternSimple", "a"),
					$data_sem["lecturer"]);
			if (is_array($data["lecturer"]))
				$data["lecturer"] = implode(", ", $data["lecturer"]);
		}
		
		if ($visible[++$j])
			$data["art"] = $data_sem["art"];
		
		if ($visible[++$j]) {
			$aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
					"class_{$SEM_TYPE[$data_sem['semtype']]['class']}");
			if ($aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1])
				$data["status"] = $aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1];
			else {
				$data["status"] = htmlReady($SEM_TYPE[$data_sem['semtype']]["name"]
						." (". $SEM_CLASS[$SEM_TYPE[$data_sem['semtype']]["class"]]["name"].")");
			}
		}
		
		if ($visible[++$j])
			$data["description"] = $data_sem["description"];
		
		if ($visible[++$j])
			$data["location"] = $data_sem["location"];
		
		if ($visible[++$j])
			$data["semester"] = $data_sem["semester"];
		
		if ($visible[++$j])
			$data["time"] = $data_sem["time"];
		
		if ($visible[++$j])
			$data["number"] = $data_sem["number"];
		
		if ($visible[++$j])
			$data["teilnehmer"] = $data_sem["teilnehmer"];
		
		if ($visible[++$j])
			$data["requirements"] = $data_sem["requirements"];
		
		if ($visible[++$j])
			$data["lernorga"] = $data_sem["lernorga"];
		
		if ($visible[++$j])
			$data["leistung"] = $data_sem["leistung"];
		
		if ($visible[++$j]) {
			$pathes = array($data_sem["range_path"]);
			if (is_array($pathes)) {
				$pathes_values = array_values($pathes);
				if ($this->config->getValue("Main", "range") == "long")
					$data["range_path"] = $pathes_values;
				else {
					foreach ($pathes_values as $path)
						$data["range_path"][] = array_pop(explode("&gt;", $path));
				}
				$data["range_path"] = array_filter($data["range_path"], "htmlReady");
				$data["range_path"] = implode("<br>", $data["range_path"]);
			}
		}
		
		if ($visible[++$j])
			$data["misc"] = $data_sem["misc"];
		
		if ($visible[++$j])
			$data["ects"] = $data_sem["ects"];
				
		return $this->toStringMainTable($data, TRUE);
	}

	
	// private
	function toStringMainTable ($data, $preview) {
		$order = $this->config->getValue("Main", "order");
		$visible = $this->config->getValue("Main", "visible");
		$aliases = $this->config->getValue("Main", "aliases");
		
		if ($this->config->getValue("Main", "studiplink")) {
			$out .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"";
			if ($studiplink_width = $this->config->getValue("TableHeader", "table_width"))
				$out .= " width=\"$studiplink_width\"";
			if ($studiplink_align = $this->config->getValue("TableHeader", "table_align"))
				$out .= " align=\"$studiplink_align\">\n";
			
			if ($preview)
				$studip_link = "";
			else {
				if ($this->config->getValue("Main", "studiplinktarget") != "signin") {
					$studip_link = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'seminar_main.php?auswahl=';
					$studip_link .= $this->seminar_id;
					$studip_link .= "&again=1&redirect_to=admin_seminare1.php&login=true&new_sem=TRUE";
				}
				else {
					$studip_link = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'details.php?again=1&sem_id=';
					$studip_link .= $this->seminar_id;
				}
			}
			if ($this->config->getValue("Main", "studiplink") == "top") {
				$args = array("width" => "100%", "height" => "40", "link" => $studip_link);
				$out .= "<tr><td width=\"100%\">\n";
				$out .= $this->elements["StudipLink"]->toString($args);
				$out .= "</td></tr>";
			}
			$table_attr = $this->config->getAttributes("TableHeader", "table");
			$pattern = array("/width=\"[0-9%]+\"/", "/align=\"[a-z]+\"/");
			$replace = array("width=\"100%\"", "");
			$table_attr = preg_replace($pattern, $replace, $table_attr);
			$out .= "<tr><td width=\"100%\">\n<table$table_attr>";
		}
		else
			$out .= "<table" . $this->config->getAttributes("TableHeader", "table") . ">";
		
		$out .= $this->elements["SemName"]->toString(array("content" => $data["name"]));
		
		if ($this->config->getValue("Main", "headlinerow")) {
			foreach ($order as $position) {
				if ($visible[$position] && $data[$this->data_fields[$position]]) {
					$out .= $this->elements["Headline"]->toString(
							array("content" => $aliases[$position]));
					$out .= $this->elements["Content"]->toString(
							array("content" => $data[$this->data_fields[$position]]));
				}
			}
		}
		else {
			foreach ($order as $position) {
				if ($visible[$position] && $data[$this->data_fields[$position]]) {
					$out .= $this->elements["Content"]->toString(array("content" =>
							$this->config->getTag("Headline", "font") . $aliases[$position] .
							"</font>" . $data[$this->data_fields[$position]]));
				}
			}
		}
		
		if ($this->config->getValue("Main", "studipinfo")) {
			$out .= $this->elements["Headline"]->toString(array("content" =>
					$this->config->getValue("StudipInfo", "headline")));
			$out .= $this->toStringStudipInfo($preview);
		}
		
		$out .= "</table>\n";
		
		if ($this->config->getValue("Main", "studiplink")) {
			if ($this->config->getValue("Main", "studiplink") == "bottom") {
				$args = array("width" => "100%", "height" => "40", "link" => $studip_link);
				$out .= "</td></tr>\n<tr><td width=\"100%\">\n";
				$out .= $this->elements["StudipLink"]->toString($args);
			}
			$out .= "</td></tr></table>\n";
		}
		
		return $out;
	}
	
	// private
	function toStringStudipInfo ($preview) {
		if ($preview) {
			$studip_info = $this->elements["StudipInfo"]->toString(array("content" =>
					$this->config->getValue("StudipInfo", "homeinst") . "&nbsp;"));
			$studip_info .= sprintf("<a href=\"\"%s>%s</a><br>\n",
					$this->config->getAttributes("LinkInternSimple", "a"),
					_("Heimatinstitut"));			
			
			$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
					$this->config->getValue("StudipInfo", "involvedinst") . "&nbsp;"));
			$studip_info .= str_repeat(_("Beteiligte Institute") . " ", 5) . "<br>\n";
			
			$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
					$this->config->getValue("StudipInfo", "countuser") . "&nbsp;"));
			$studip_info .= "23<br>\n";
			
			$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
					$this->config->getValue("StudipInfo", "countpostings") . "&nbsp;"));
			$studip_info .= "42<br>\n";
		
			$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
					$this->config->getValue("StudipInfo", "countdocuments") . "&nbsp;"));
			$studip_info .= "7<br>\n";
		}
		else {
			$this->db->query("SELECT i.Institut_id, i.Name, i.url FROM seminare LEFT JOIN Institute i
									USING(institut_id) WHERE Seminar_id='{$this->seminar_id}'");
			$this->db->next_record();
			$own_inst = $this->db->f("Institut_id");
			
			$studip_info = $this->elements["StudipInfo"]->toString(array("content" =>
					$this->config->getValue("StudipInfo", "homeinst") . "&nbsp;"));
			
			if ($this->db->f("url")) {
				$link_inst = htmlReady($this->db->f("url"));
				if (!preg_match('{^https?://.+$}', $link_inst))
					$link_inst = "http://$link_inst";
				$studip_info .= sprintf("<a href=\"%s\"%s target=\"_blank\">%s</a>", $link_inst,
						$this->config->getAttributes("LinkInternSimple", "a"),
						htmlReady($this->db->f("Name")));
			}
			else
				$studip_info .= htmlReady($this->db->f("Name"));
			$studip_info .= "<br>\n";
			
			$this->db->query("SELECT Name, url FROM seminar_inst LEFT JOIN Institute i USING(institut_id)
									WHERE seminar_id='{$this->seminar_id}' AND i.institut_id!='$own_inst'");
			$involved_insts = NULL;
			while ($this->db->next_record()) {
				if ($this->db->f("url")) {
					$link_inst = htmlReady($this->db->f("url"));
					if (!preg_match('{^https?://.+$}', $link_inst))
						$link_inst = "http://$link_inst";
					$involved_insts[] = sprintf("<a href=\"%s\"%s target=\"_blank\">%s</a>",
							$link_inst, $this->config->getAttributes("LinkInternSimple", "a"),
							htmlReady($this->db->f("Name")));
				}
				else
					$involved_insts[] = $this->db->f("Name");
			}
			
			if ($involved_insts) {
				$involved_insts = implode(", ", $involved_insts);
				$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
						$this->config->getValue("StudipInfo", "involvedinst") . "&nbsp;"));
				$studip_info .= $involved_insts . "<br>\n";
			}
			
			$this->db->query("SELECT count(*) as count_user FROM seminar_user WHERE Seminar_id='{$this->seminar_id}'");
			$this->db->next_record();
			
			if ($this->db->f("count_user")) {
				$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
							$this->config->getValue("StudipInfo", "countuser") . "&nbsp;"));
				$studip_info .= $this->db->f("count_user") . "<br>\n";
			}
			
			$this->db->query("SELECT count(*) as count_postings FROM px_topics WHERE Seminar_id='{$this->seminar_id}'");
			$this->db->next_record();
			
			if ($this->db->f("count_postings")) {
				$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
							$this->config->getValue("StudipInfo", "countpostings") . "&nbsp;"));
				$studip_info .= $this->db->f("count_postings") . "<br>\n";
			}
			
			$this->db->query("SELECT count(*) as count_documents FROM dokumente WHERE seminar_id='{$this->seminar_id}'");
			$this->db->next_record();
			
			if ($this->db->f("count_documents")) {
				$studip_info .= $this->elements["StudipInfo"]->toString(array("content" =>
							$this->config->getValue("StudipInfo", "countdocuments") . "&nbsp;"));
				$studip_info .= $this->db->f("count_documents") . "\n";
			}
		}
		
		return $this->elements["Content"]->toString(array("content" => $studip_info));
	}
	
}

?>
