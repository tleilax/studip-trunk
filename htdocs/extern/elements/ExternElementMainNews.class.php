<?
/**
* ExternElementMainNews.class.php
* 
*  
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementMainDownload
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainNews.class.php
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

class ExternElementMainNews extends ExternElementMain {

	var $attributes = array("name", "order", "visible", "aliases", "width",
			"width_pp", "sort", "studiplink", "wholesite", "nameformat", "dateformat", "language",
			"urlcss", "title", "nodatatext");
	var $edit_function = "editMainSettings";
	
	/**
	* Constructor
	*
	*/
	function ExternElementMainNews () {
		$this->real_name = _("Grundeinstellungen");
		$this->description = _("In den Grundeinstellungen k&ouml;nnen Sie allgemeine Daten des Moduls &auml;ndern.");
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		
		$config = array(
			"name" => "",
			"order" => "|0|1",
			"visible" => "|1|1",
			"aliases" => "|"._("Datum")."|"._("Nachricht"),
			"width" => "|10%|90%",
			"widthpp" => "",
			"sort" => "|1|0",
			"wholesite" => "",
			"studiplink" => "top",
			"nameformat" => "no_title",
			"dateformat" => "%d. %b. %Y",
			"language" => "de_DE",
			"urlcss" => "",
			"title" => _("News"),
			"nodatatext" => _("Keine aktuellen News")
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
		
		$headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Tabellenaufbau"));
		
		$edit_function = $this->edit_function;
		$table = $edit_form->$edit_function($this->field_names, array());
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Weitere Angaben"));
		
		$title = _("Namensformat:");
		$info = _("W�hlen Sie, wie Personennamen formatiert werden sollen.");
		$values = array("no_title_short", "no_title", "no_title_rev", "full", "full_rev", "last");
		$names = array(_("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
				_("Dr. Peter Meyer"), _("Meyer, Peter, Dr."), _("Meyer"));
		$table = $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);
		
		$title = _("Datumsformat:");
		$info = _("W�hlen Sie, wie Datumsangaben formatiert werden sollen.");
		$nametitle_values = array("%d. %b. %Y", "%d.%m.%Y", "%d.%m.%y", "%d. %B %Y", "%m/%d/%y");
		$nametitle_names = array(_("25. Nov. 2003"), "25.11.2003", "25.11.03",
				_("25. November 2003"), "11/25/03");
		$table .= $edit_form->editOptionGeneric("dateformat", $title, $info, $nametitle_values, $nametitle_names);
		
		$title = _("Sprache:");
		$info = _("W�hlen Sie eine Sprache f�r die Datumsangaben aus.");
		$values = array("de_DE", "en_GB");
		$names = array(_("Deutsch"), _("Englisch"));
		$table .= $edit_form->editOptionGeneric("language", $title, $info, $values, $names);
		
		$title = _("Stud.IP-Link:");
		$info = _("Ausgabe eines Links, der direkt zum Stud.IP-Administrationsbereich verweist.");
		$value = array("top", "bottom", "0");
		$names = array(_("oberhalb"), _("unterhalb der Tabelle"), _("ausblenden"));
		$table .= $edit_form->editRadioGeneric("studiplink", $title, $info, $value, $names);
		
		$title = _("HTML-Header/Footer:");
		$info = _("Anw�hlen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
		$wholesite_values = "1";
		$wholesite_names = "";
		$table .= $edit_form->editCheckboxGeneric("wholesite", $title, $info, $wholesite_values, $wholesite_names);
		
		$title = _("Stylesheet-Datei:");
		$info = _("Geben Sie hier die URL Ihrer Stylesheet-Datei an.");
		$table .= $edit_form->editTextfieldGeneric("urlcss", $title, $info, 50, 200);
		
		$title = _("Seitentitel:");
		$info = _("Geben Sie hier den Titel der Seite ein. Der Titel wird bei der Anzeige im Web-Browser in der Titelzeile des Anzeigefensters angezeigt.");
		$table .= $edit_form->editTextfieldGeneric("title", $title, $info, 50, 200);
		
		$title = _("Keine Dateien:");
		$info = _("Dieser Text wird an Stelle der Tabelle ausgegeben, wenn keine Dateien zum Download verf�gbar sind.");
		$table .= $edit_form->editTextareaGeneric("nodatatext", $title, $info, 3, 50);
		
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
