<?
/**
* ExternElementLinkInternSimple.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementLinkInternSimple
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementLinkInternSimple.class.php
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

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternElement.class.php");

class ExternElementLinkInternSimple extends ExternElement {

	var $attributes = array("a_class", "a_style", "config", "srilink");
	var $link_module_type;

	/**
	* Constructor
	*
	* @param array config
	*/
	function ExternElementLinkInternSimple ($config = "") {
		if ($config)
			$this->config = $config;
		
		$this->name = "LinkInternSimple";
		$this->real_name = _("Links");
		$this->description = _("Formatierung des Links.");
		$this->headlines = array(_("Linkformatierung"), _("Verlinkung zum Modul"));
	}
	
	/**
	* 
	*/
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
		global $EXTERN_MODULE_TYPES;
		$out = "";
		$table = "";
		if ($edit_form == "")
			$edit_form =& new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $edit_form->editElementHeadline($this->real_name,
				$this->config->getName(), $this->config->getId(), TRUE, $anker);
		
		$attributes = array("a_class", "a_style");
		$headlines = array("a" => $this->headlines[0]);
		$content_table = $edit_form->getEditFormContent($attributes, $headlines);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline($this->headlines[1]);
		
		$title = _("Konfiguration:");
		$info = _("Der Link ruft das Modul mit der gew�hlten Konfiguration auf. W�hlen Sie \"Standard\", um die von Ihnen gesetzte Standardkonfiguration zu benutzen. Ist f�r das aufgerufene Modul noch keine Konfiguration erstellt worden, wird die Stud.IP-Default-Konfiguration verwendet.");
		if ($configs = get_all_configurations($this->config->range_id, $this->link_module_type)) {
			$module_name = $EXTERN_MODULE_TYPES[$this->link_module_type]["module"];
			$values = array_keys($configs[$module_name]);
			unset($names);
			foreach ($configs[$module_name] as $config)
				$names[] = $config["name"];
		}
		else {
			$values = array();
			$names = array();
		}
		array_unshift($values, "");
		array_unshift($names, _("Standardkonfiguration"));
		$table = $edit_form->editOptionGeneric("config", $title, $info, $values, $names);
		
		$title = _("SRI-Link:");
		$info = _("Wenn Sie die SRI-Schnittstelle benutzen, m�ssen Sie hier die vollst�ndige URL (mit http://) der Seite angeben, in der das Modul, das durch den Link aufgerufen wird, eingebunden ist. Lassen Sie dieses Feld unbedingt leer, falls Sie die SRI-Schnittstelle nicht nutzen.");
		$table .= $edit_form->editTextfieldGeneric("srilink", $title, $info, 50, 250);
		
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
