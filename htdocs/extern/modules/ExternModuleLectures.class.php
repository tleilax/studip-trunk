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

global $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_EXTERN;
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/lib/ExternModule.class.php");
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/views/extern_html_templates.inc.php");
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/modules/views/ExternSemBrowse.class.php");
require_once($ABSOLUTE_PATH_STUDIP."language.inc.php");

class ExternModuleLectures extends ExternModule {

	var $field_names = array();
	var $data_fields = array();
	var $registered_elements = array(
			"ReplaceTextSemType",
			"Body",
			"TableHeader",
			"InfoCountSem" => "TableGroup",
			"Grouping" => "TableGroup",
			"LecturesInnerTable",
			"SemLink" => "LinkInternSimple",
			"LecturerLink" => "LinkInternSimple");
	var $args = array();

	/**
	*
	*/
	function ExternModuleLectures () {}
	
	function setup () {
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
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		$start_item_id = get_start_item_id($this->config->range_id);
		$browser =& new ExternSemBrowse($this->config, $start_item_id);
		$browser->print_result();
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"));
		}
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/lectures_preview.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
}
?>
