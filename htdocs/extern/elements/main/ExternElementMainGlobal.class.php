<?
/**
* ExternElementMainPersons.class.php
* 
*  
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementMainPersons
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainPersons.class.php
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

require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/lib/ExternElementMain.class.php");
require_once($ABSOLUTE_PATH_STUDIP."dates.inc.php");
require_once($ABSOLUTE_PATH_STUDIP."lib/classes/SemesterData.class.php");

class ExternElementMainGlobal extends ExternElementMain {

	var $attributes = array("name", "semstart", "semrange", "semswitch",
			"nameformat", "language", "wholesite", "urlcss", 'copyright', 'author');
	
	/**
	* Constructor
	*
	*/
	function ExternElementMainGlobal () {
		$this->real_name = _("Grundeinstellungen");
		$this->description = _("In den Grundeinstellungen k&ouml;nnen Sie allgemeine Daten des Moduls &auml;ndern.");
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		
		$config = array(
			"name" => "",
			"semstart" => "",
			"semrange" => "",
			"semswitch" => "",
			"wholesite" => "",
			"nameformat" => "",
			"language" => "",
			"urlcss" => "",
			"copyright" => htmlentities($GLOBALS['UNI_NAME_CLEAN']
					. " ({$GLOBALS['UNI_CONTACT']})", ENT_QUOTES),
			"author" => ''
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
		
		$headline = $edit_form->editHeadline(_("Name der globalen Konfiguration"));
		$table = $edit_form->editName("name");
		
		$content_table = $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Anzuzeigende Lehrveranstaltungen"));
		
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
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Weitere Angaben"));
		
		$title = _("Namensformat:");
		$info = _("W�hlen Sie, wie Personennamen formatiert werden sollen.");
		$values = array("", "no_title_short", "no_title", "no_title_rev", "full", "full_rev");
		$names = array(_("keine Auswahl"), _("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
				_("Dr. Peter Meyer"), _("Meyer, Peter, Dr."));
		$table = $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);
		
		$title = _("Sprache:");
		$info = _("W�hlen Sie eine Sprache f�r die Datumsangaben aus.");
		$values = array("", "de_DE", "en_GB");
		$names = array(_("keine Auswahl"), _("Deutsch"), _("Englisch"));
		$table .= $edit_form->editOptionGeneric("language", $title, $info, $values, $names);
		
		$title = _("HTML-Header/Footer:");
		$info = _("Anw�hlen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
		$values = "1";
		$names = "";
		$table .= $edit_form->editCheckboxGeneric("wholesite", $title, $info, $values, $names);
		
		$title = _("Stylesheet-Datei:");
		$info = _("Geben Sie hier die URL Ihrer Stylesheet-Datei an.");
		$table .= $edit_form->editTextfieldGeneric("urlcss", $title, $info, 50, 200);
		
		$title = _("Copyright:");
		$info = _("Geben Sie hier einen Copyright-Vermerk an. Dieser wird im Meta-Tag \"copyright\" ausgegeben, wenn Sie die Option \"HTML-Header/Footer\" angew�hlt haben.");
		$table .= $edit_form->editTextfieldGeneric("copyright", $title, $info, 50, 200);
		
		$title = _("Autor:");
		$info = _("Geben Sie hier den Namen des Seitenautors an. Dieser wird im Meta-Tag \"author\" ausgegeben, wenn Sie die Option \"HTML-Header/Footer\" angew�hlt haben.");
		$table .= $edit_form->editTextfieldGeneric("author", $title, $info, 50, 200);
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($content_table, $submit);
		$out .= $edit_form->editBlank();
		
		return $element_headline . $out;
	}
	
	function checkFormValues () {
		global $HTTP_POST_VARS;
				
		if ($fault = parent::checkFormValues()) {
		
			if ($HTTP_POST_VARS["Main_nameformat"] == ""
					&& $fault["Main_nameformat"][0] == TRUE) {
				$fault["Main_nameformat"][0] = FALSE;
			}
			
			return $fault;
		}
		
		return FALSE;
	}	
	
}

?>
