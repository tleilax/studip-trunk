<?
/**
* ExternElementMain.class.php
* 
*  
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementMain
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMain.class.php
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

class ExternElementMain extends ExternElement {

	var $attributes = array();
	var $edit_function = "";
	var $data_fields;
	var $field_names;

	/**
	* Constructor
	*
	*/
	function ExternElementMain ($module_name, $data_fields,
			$field_names, &$config) {
			
		$this->real_name = _("Grundeinstellungen");
		$this->description = _("In den Grundeinstellungen k&ouml;nnen Sie allgemeine Daten des Elements �ndern.");
		
		if ($module_name != "") {
			$main_class_name = "ExternElementMain" . $module_name;
			require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "extern/elements/$main_class_name.class.php");
			$this = new $main_class_name();
		}
		$this->name = "Main";
		$this->config =& $config;
		$this->data_fields = $data_fields;
		$this->field_names = $field_names;
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {}
	
	/**
	* 
	*/
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
		
		$out = "";
		$table = "";
		if ($edit_form == "")
			$edit_form =& new ExternEdit($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $edit_form->editElementHeadline($this->real_name,
				$this->config->getName(), $this->config->getId(), TRUE, $anker);
		
		if ($faulty_values = "")
			$faulty_values = array();
		
		$edit_function = $this->edit_function;
		$table = $edit_form->$edit_function($this->field_names);

		$content_table = $edit_form->editContentTable($tag_headline, $table);
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($content_table, $submit);
		$out .= $edit_form->editBlank();
		
		return $element_headline . $out;
	}
	
	/**
	* 
	*/
	function mainCommand ($command, $value = "") {
		switch ($command) {
			case "show" :
				$visible = $this->config->getValue("Main", "visible");
				if ($value >= 0 || $value < sizeof($visible)) {
					$visible[$value] = "TRUE";
					$this->config->setValue("Main", "visible", $visible);
					$this->config->store();
				}
				break;
				
			case "hide" :
				$visible = $this->config->getValue("Main", "visible");
				if ($value >= 0 || $value < sizeof($visible)) {
					$visible[$value] = "FALSE";
					$this->config->setValue("Main", "visible", $visible);
					$this->config->store();
				}
				break;
				
			case "move_left" :
				$order = $this->config->getValue("Main", "order");
				if ($value >= 0 || $value < sizeof($order)) {
					$a = $order[$value];
					if (($value - 1) < 0) {
						$b = array_pop($order);
						array_push($order, $a);
						$order[0] = $b;
					}
					else {
						$b = $order[$value - 1];
						$order[$value - 1] = $a;
						$order[$value] = $b;
					}
					$this->config->setValue("Main", "order", $order);
					$this->config->store();
				}
				break;
				
			case "move_right" :
				$order = $this->config->getValue("Main", "order");
				if ($value >= 0 || $value < sizeof($order)) {
					$a = $order[$value];
					if (($value + 1) >= sizeof($order)) {
						$b = $order[0];
						$order[0] = $a;
						$order[$value] = $b;
					}
					else {
						$b = $order[$value + 1];
						$order[$value + 1] = $a;
						$order[$value] = $b;
					}
					$this->config->setValue("Main", "order", $order);
					$this->config->store();
				}
				break;
				
			default :
				return FALSE;
		}
		
		return TRUE;
	}
	
}

?>
