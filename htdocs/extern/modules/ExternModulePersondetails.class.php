<?
/**
* ExternModulePersondetail.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModulePersondetail
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModulePersondetail.class.php
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

global $RELATIVE_PATH_CALENDAR, $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_EXTERN;

require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/lib/ExternModule.class.php");
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/views/extern_html_templates.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/DataFields.class.php");
require_once($ABSOLUTE_PATH_STUDIP."language.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "dates.inc.php");

class ExternModulePersondetails extends ExternModule {

	var $field_names = array();
	var $data_fields = array("contact" => array("raum", "Telefon", "Fax", "Email",
			"Home", "sprechzeiten"), "content" => array("head", "lebenslauf", "schwerp", "lehre",
			"news", "termine", "publi", "kategorien"));
	var $registered_elements = array("Body", "TableHeader", "PersondetailsHeader", "Contact",
			"PersondetailsLectures", "TableParagraph", "TableParagraphHeadline",
			"TableParagraphSubHeadline", "TableParagraphText", "List", "LinkIntern", "StudipLink");
	var $args = array("username", "seminar_id");

	/**
	*
	*/
	function ExternModulePersondetails () {
		$this->field_names = array
		(
			"contact" => array
			(
				_("Raum"),
				_("Telefon"),
				_("Fax"),
				_("Email"),
				_("Homepage"),
				_("Sprechzeiten")
			),
			"content" => array
			(
				_("Name, Anschrift, Kontakt"),
				_("Lebenslauf"),
				_("Schwerpunkte"),
				_("Lehrveranstaltungen"),
				_("News"),
				_("Termine"),
				_("Publikationen"),
				_("eigene Kategorien")
			)
		);
		
	}
	
	function setup () {
		// extend $data_fields if generic datafields are set
		$config_datafields = $this->config->getValue("Main", "genericdatafields");
		$this->data_fields["content"] = array_merge($this->data_fields["content"], $config_datafields);
		
		// setup module properties
		$this->elements["LinkIntern"]->link_module_type = 4;
		$this->elements["LinkIntern"]->real_name = _("Link zum Modul Veranstaltungsdetails");
		$this->elements["TableHeader"]->real_name = _("Umschlie�ende Tabelle");
	}
	
	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);
		
		if ($range == "inst" || $range == "fak")
			return TRUE;
			
		return FALSE;
	}
	
	function printout ($args) {
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"),
					$this->config->getValue("Main", "copyright"),
					$this->config->getValue("Main", "author"));
		}
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/persondetails.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"),
					$this->config->getValue("Main", "copyright"),
					$this->config->getValue("Main", "author"));
		}
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/persondetails_preview.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
}
?> 
