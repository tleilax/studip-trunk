<?
/**
* ExternModuleLectures.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleLectures
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleLectures.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternModule.class.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/views/extern_html_templates.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/modules/views/ExternSemBrowse.class.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"]."language.inc.php");

class ExternModuleLectures extends ExternModule {

	var $field_names = array();
	var $data_fields = array();
	var $registered_elements = array(
			"ReplaceTextSemType",
			"Body",
			"TableHeader",
			"InfoCountSem" => "TableGroup",
			"Grouping" => "TableGroup",
			"SemName" => "TableGroup",
			"TimeLecturer" => "TableRowTwoColumns",
			"SemLink" => "LinkInternSimple",
			"LecturerLink" => "LinkInternSimple");
	var $args = array();

	/**
	*
	*/
	function ExternModuleLectures () {}
	
	function setup () {
		$this->elements["TimeLecturer"]->real_name = _("Zeile Zeiten(Termine)/Dozenten");
		$this->elements["SemName"]->real_name = _("Zeile Veranstaltungsname");
		$this->elements["InfoCountSem"]->real_name = _("Anzahl Veranstaltungen/Gruppierung");
		$this->elements["TimeLecturer"]->headlines = array(_("Angaben zum HTML-Tag &lt;tr&gt;"),
				_("Spalte mit Terminen/Zeiten &lt;td&gt;"),	_("Spalte mit Terminen/Zeiten &lt;font&gt;"),
				_("Spalte mit Dozentennamen &lt;td&gt;"), _("Spalte mit Dozentennamen &lt;font&gt;"));
		$this->elements["SemLink"]->link_module_type = 6;
		$this->elements["SemLink"]->real_name = _("Link zum Modul Mitarbeiterdetails");
		$this->elements["LecturerLink"]->link_module_type = 7;
		$this->elements["LecturerLink"]->real_name = _("Link zum Modul Veranstaltungsdetails");
	}
	
	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);
		
		if ($range == "inst" || $range == "fak")
			return TRUE;
			
		return FALSE;
	}
	
	function printout ($args) {
		global $ABSOLUTE_PATH_STUDIP;
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"));
		}
		
		init_i18n($this->config->getValue("Main", "language"));
		
		$start_item_id = get_start_item_id($this->config->range_id);
		$group_by = $this->config->getValue("Main", "grouping");
		$sem_browse_data = array("start_item_id" => $start_item_id, "level" => "ev",
		"cmd" => "qs", "show_class" => "all", "group_by" => $group_by, "default_sem" => "all",
		"search_result" => Array(), "show_entries" => "level", "sem_status" => "", "sset" => "");
		$browser =& new ExternSemBrowse($this->config, $sem_browse_data);
		$browser->print_result();
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"));
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/lectures_preview.inc.php");
				
		echo html_footer();
	}
	
}
?>
