<?
/**
* ExternElementPersondetailsLectures.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementPersondetailsLectures
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementPersondetailsLectures.class.php
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

require_once($RELATIVE_PATH_EXTERN."/lib/ExternElement.class.php");
require_once("dates.inc.php");
require_once("lib/classes/SemesterData.class.php");

class ExternElementPersondetailsLectures extends ExternElement {

	var $attributes = array("semstart", "semrange", "semswitch", "aliaswise",
			"aliassose", "aslist");
	
	/**
	* Constructor
	*
	* @param array config
	*/
	function ExternElementPersondetailsLectures ($config = "") {
		if ($config)
			$this->config = $config;
		
		$this->name = "PersondetailsLectures";
		$this->real_name = _("Lehrveranstaltungen");
		$this->description = _("Angaben zur Ausgabe von Lehrveranstaltungen.");
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		
		$config = array(
			"semstart" => "",
			"semrange" => "",
			"semswitch" => "",
			"aliaswise" => _("Wintersemester"),
			"aliassose" => _("Sommersemester"),
			"aslist" => "1"
		);
		
		return $config;
	}
	
	/**
	* 
	*/
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
		
		// get semester data
		$semester =& new SemesterData();
		$semester_data = $semester->getAllSemesterData();
		
		$out = "";
		$table = "";
		if ($edit_form == "")
			$edit_form =& new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $edit_form->editElementHeadline($this->real_name,
				$this->config->getName(), $this->config->getId(), TRUE, $anker);
		
		$headline = $edit_form->editHeadline(_("Allgemeine Angaben"));
		
		$title = _("Startsemester:");
		$info = _("Geben Sie das erste anzuzeigende Semester an. Die Angaben \"vorheriges\", \"aktuelles\" und \"n�chstes\" beziehen sich immer auf das laufende Semester und werden automatisch angepasst.");
		$current_sem = get_sem_num_sem_browse();
		if ($current_sem === FALSE) {
			$names = array(_("keine Auswahl"), _("aktuelles"), _("n&auml;chstes"));
			$values = array("", "current", "next");
		}
		else if ($current_sem === TRUE) {
			$names = array(_("keine Auswahl"), _("vorheriges"), _("aktuelles"));
			$values = array("", "previous", "current");
		}
		else {
			$names = array(_("keine Auswahl"), _("vorheriges"), _("aktuelles"), "n&auml;chstes");
			$values = array("", "previous", "current", "next");
		}
		foreach ($semester_data as $sem_num => $sem) {
			$names[] = $sem["name"];
			$values[] = $sem_num + 1;
		}
		$table = $edit_form->editOptionGeneric("semstart", $title, $info, $values, $names);
		
		$title = _("Anzahl der anzuzeigenden Semester:");
		$info = _("Geben Sie an, wieviele Semester (ab o.a. Startsemester) angezeigt werden sollen.");
		$names = array(_("keine Auswahl"));
		$values = array("");
		$i = 1;
		foreach ($semester_data as $sem_num => $sem) {
			$names[] = $i++;
			$values[] = $sem_num + 1;
		}
		$table .= $edit_form->editOptionGeneric("semrange", $title, $info, $values, $names);
		
		$title = _("Umschalten des aktuellen Semesters:");
		$info = _("Geben Sie an, wieviele Wochen vor Semesterende automatisch auf das n�chste Semester umgeschaltet werden soll.");
		$names = array(_("keine Auswahl"), _("am Semesterende"), _("1 Woche vor Semesterende"));
		for ($i = 2; $i < 13; $i++)
			$names[] = sprintf(_("%s Wochen vor Semesterende"), $i);
		$values = array("", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12");
		$table .= $edit_form->editOptionGeneric("semswitch", $title, $info, $values, $names);
		
		$title = _("Bezeichnung Sommersemester:");
		$info = _("Alternative Bezeichnung f�r den Begriff \"Sommersemester\".");
		$table .= $edit_form->editTextfieldGeneric("aliassose", $title, $info, 40, 80);
		
		$title = _("Bezeichnung Wintersemester:");
		$info = _("Alternative Bezeichnung f�r den Begriff \"Wintersemester\".");
		$table .= $edit_form->editTextfieldGeneric("aliaswise", $title, $info, 40, 80);
		
		$title = _("Darstellungsart:");
		$info = _("W�hlen Sie zwischen Listendarstellung und reiner Textdarstellung.");
		$names = array(_("Liste"), _("nur Text"));
		$values = array("1", "0");
		$table .= $edit_form->editRadioGeneric("aslist", $title, $info, $values, $names);
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($content_table, $submit);
		$out .= $edit_form->editBlank();
		
		return $element_headline . $out;
	}
	
}

?>
