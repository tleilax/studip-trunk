<?
/**
* ExternModuleLecturestable.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleLecturestable
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleLecturestable.class.php
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
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/modules/views/ExternSemBrowseTable.class.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"]."language.inc.php");

class ExternModuleLecturestable extends ExternModule {

	var $field_names = array();
	var $data_fields = array("VeranstaltungsNummer", "Name", "Untertitel", "status", "Ort",
			"art", "zeiten", "dozent");
	var $registered_elements = array(
			"ReplaceTextSemType",
			"Body",
			"TableHeader",
			"InfoCountSem" => "TableGroup",
			"Grouping" => "TableGroup",
			"TableHeadrow",
			"TableRow",
			"SemLink" => "LinkInternSimple",
			"LecturerLink" => "LinkInternSimple");
	var $args = array();

	/**
	*
	*/
	function ExternModuleLecturestable () {
		$this->field_names = array(
				_("Veranstaltungsnummer"),
				_("Name"),
				_("Untertitel"),
				_("Status"),
				_("Ort"),
				_("Art"),
				_("Zeiten"),
				_("DozentIn")
		);
	}
	
	function setup () {
		// extend $data_fields if generic datafields are set
	//	$config_datafields = $this->config->getValue("Main", "genericdatafields");
	//	$this->data_fields = array_merge($this->data_fields, $config_datafields);
		
		// setup module properties
		$this->elements["InfoCountSem"]->real_name = _("Anzahl Veranstaltungen/Gruppierung");
		$this->elements["SemLink"]->link_module_type = 4;
		$this->elements["SemLink"]->real_name = _("Link zum Modul Veranstaltungsdetails");
		$this->elements["LecturerLink"]->link_module_type = 2;
		$this->elements["LecturerLink"]->real_name = _("Link zum Modul MitarbeiterInnendetails");
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
		$browser =& new ExternSemBrowseTable($this, $start_item_id);
		$browser->print_result();
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"));
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/lectures_table_preview.inc.php");
				
		echo html_footer();
	}
	
}
?>
