<?
/**
* ExternElementMainLectures.class.php
* 
*  
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementMainLectures
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainLectures.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternElementMain.class.php");

class ExternElementMainLectures extends ExternElementMain {

	var $attributes = array("name", "grouping", "semrange", "addinfo", "time", "lecturer",
			"semclasses", "textlectures", "textgrouping", "textnogroups", "aliasesgrouping",
			"wholesite", "nameformat", "language", "urlcss", "title");
	var $edit_function = "editMainSettings";
	
	/**
	* Constructor
	*
	*/
	function ExternElementMainLectures () {
		$this->real_name = _("Grundeinstellungen");
		$this->description = _("In den Grundeinstellungen k&ouml;nnen Sie allgemeine Daten des Moduls &auml;ndern.");
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		
		$config = array(
			"name" => "",
			"grouping" => "3",
			"semrange" => "three",
			"addinfo" => "1",
			"time" => "1",
			"lecturer" => "1",
			"semclasses" => "|1",
			"textlectures" => _("Veranstaltungen"),
			"textgrouping" => _("Gruppierung:"),
			"textnogroups" => _("keine Studienbereiche eingetragen"),
			"aliasesgrouping" => "|"._("Semester")."|"._("Bereich")."|"._("DozentIn")."|"
					._("Typ")."|"._("Einrichtung"),
			"wholesite" => "0",
			"nameformat" => "no_title_short",
			"language" => "de_DE",
			"urlcss" => "",
			"title" => _("Lehrveranstaltungen")
		);
		
		return $config;
	}
	
	/**
	* 
	*/
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
		
		$out = "";
		$table = "";
		if ($edit_form == "")
			$edit_form =& new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $edit_form->editElementHeadline($this->real_name,
				$this->config->getName(), $this->config->getId(), TRUE, $anker);
		
		$headline = $edit_form->editHeadline(_("Name der Konfiguration"));
		$table = $edit_form->editName("name");
		
		$content_table = $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Allgemeine Angaben Seitenaufbau"));
		
		$title = _("Gruppierung:");
		$info = _("W�hlen Sie, wie die Veranstaltungen gruppiert werden sollen.");
		$values = array("0", "1", "2", "3", "4");
		$names = array(_("Semester"), _("Bereich"), _("DozentIn"),
				_("Typ"), _("Einrichtung"));
		$table = $edit_form->editOptionGeneric("grouping", $title, $info, $values, $names);
		
		$title = _("Semesterumfang:");
		$info = _("Geben Sie an, aus welchen Semestern Lehrveranstaltungen angezeigt werden sollen.");
		$names = array(_("nur aktuelles"), _("vorheriges, aktuelles, n&auml;chstes"), _("alle"));
		$values = array("current", "three", "all");
		$table .= $edit_form->editRadioGeneric("semrange", $title, $info, $values, $names);
		
		$title = _("Anzahl Veranstaltungen/Gruppierung anzeigen:");
		$info = _("W�hlen Sie diese Option, wenn die Anzahl der Veranstaltungen und die gew�hlte Gruppierungsart angezeigt werden sollen.");
		$values = "1";
		$names = "";
		$table .= $edit_form->editCheckboxGeneric("addinfo", $title, $info, $values, $names);
		
		$title = _("Termine/Zeiten anzeigen:");
		$info = _("W�hlen Sie diese Option, wenn Termine und Zeiten der Veranstaltung unter dem Veranstaltungsnamen angezeigt werden sollen.");
		$values = "1";
		$names = "";
		$table .= $edit_form->editCheckboxGeneric("time", $title, $info, $values, $names);
		
		$title = _("Dozenten anzeigen:");
		$info = _("W�hlen Sie diese Option, wenn die Namen der Dozenten der Veranstaltung angezeigt werden sollen.");
		$values = "1";
		$names = "";
		$table .= $edit_form->editCheckboxGeneric("lecturer", $title, $info, $values, $names);
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Ausgabe bestimmter Veranstaltungsklassen"));
		
		$table = "";
		unset($names);
		unset($values);
		$info = _("W�hlen Sie die anzuzeigenden Veranstaltungsklassen aus.");
		
		foreach ($GLOBALS["SEM_CLASS"] as $key => $class) {
			$values[] = $key;
			$names[] = $class["name"];
		}
		$table = $edit_form->editCheckboxGeneric("semclasses", $names, $info, $values, "");
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Textersetzungen"));
		
		$title = _("Anzahl Veranstaltungen:");
		$info = _("Geben Sie einen Text ein, der nach der Anzahl der Veranstaltungen steht. Nur wirksam, wenn die Ausgabe der Anzahl der Veranstaltungen und der Gruppierung aktiviert wurde.");
		$table = $edit_form->editTextfieldGeneric("textlectures", $title, $info, 40, 150);
		
		$title = _("Gruppierungsinformation:");
		$info = _("Geben Sie einen Text ein, der vor der Gruppierungsart steht. Nur wirksam, wenn die Ausgabe der Anzahl der Veranstaltungen und der Gruppierung aktiviert wurde.");
		$table .= $edit_form->editTextfieldGeneric("textgrouping", $title, $info, 40, 150);
		
		$title = _("&quot;Keine Studienbereiche&quot;:");
		$info = _("Geben Sie einen Text ein, der Angezeigt wird, wenn Lehrveranstaltungen vorliegen, die keinem Bereich zugeordnet sind. Nur wirksam in Gruppierung nach Bereich.");
		$table .= $edit_form->editTextfieldGeneric("textnogroups", $title, $info, 40, 150);
		
		$titles = array(_("Semester"), _("Bereich"), _("DozentIn"), _("Typ"), _("Einrichtung"));
		$info = _("Geben Sie eine Bezeichnung f�r die entsprechende Gruppierungsart ein.");
		$table .= $edit_form->editTextfieldGeneric("aliasesgrouping", $titles, $info, 40, 150);
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Weitere Angaben"));
		
		$title = _("Namensformat:");
		$info = _("W�hlen Sie, wie Personennamen formatiert werden sollen.");
		$values = array("no_title_short", "no_title", "no_title_rev", "full", "full_rev");
		$names = array(_("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
				_("Dr. Peter Meyer"), _("Meyer, Peter, Dr."));
		$table = $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);
		
		$title = _("Sprache");
		$info = _("W�hlen Sie eine Sprache f�r die Datumsangaben aus.");
		$values = array("de_DE", "en_GB");
		$names = array(_("Deutsch"), _("Englisch"));
		$table .= $edit_form->editOptionGeneric("language", $title, $info, $values, $names);
		
		$title = _("HTML-Header/Footer:");
		$info = _("Anw�hlen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
		$values = "1";
		$names = "";
		$table .= $edit_form->editCheckboxGeneric("wholesite", $title, $info, $values, $names);
		
		$title = _("Stylesheet-Datei:");
		$info = _("Geben Sie hier die URL Ihrer Stylesheet-Datei an.");
		$table .= $edit_form->editTextfieldGeneric("urlcss", $title, $info, 50, 200);
		
		$title = _("Seitentitel:");
		$info = _("Geben Sie hier den Titel der Seite ein. Der Titel wird bei der Anzeige im Web-Browser in der Titelzeile des Anzeigefensters angezeigt.");
		$table .= $edit_form->editTextfieldGeneric("title", $title, $info, 50, 200);
		
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
