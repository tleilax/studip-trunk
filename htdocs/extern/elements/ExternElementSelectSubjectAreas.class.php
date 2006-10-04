<?
/**
* ExternElementSelectSubjectAreas.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementReplaceTextSemType
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementReplaceTextSemType.class.php
// 
// Copyright (C) 2006 Peter Thienel <thienel@data-quest.de>,
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
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/lib/ExternElement.class.php");
require_once($ABSOLUTE_PATH_STUDIP."/lib/classes/StudipSemTreeSearch.class.php");

class ExternElementSelectSubjectAreas extends ExternElement {

	var $attributes = array();
	var $selector;
	var $all_ranges = array();

	/**
	* Constructor
	*
	* @param array config
	*/
	function ExternElementSelectSubjectAreas ($config = '') {
		if ($config != '')
			$this->config = $config;
		
		$this->name = "SelectSubjectAreas";
		$this->real_name = _("Auswahl der anzuzeigenden Studienbereiche");
		$this->description = _("Sie k&ouml;nnen hier die Studienbereiche ausw&auml;hlen, die auf der externen Seite ausgegeben werden sollen.");
		$this->attributes = array('subjectareasselected', 'selectallsubjectareas');
		
		$this->selector =& new StudipSemTreeSearch('dummy', 'SelectSubjectAreas', FALSE);
		if ($this->config->range_id) {
			$this->selector->institut_id = $this->config->range_id;
			foreach ($this->selector->sem_tree_ranges as $range_path) {
				$this->all_ranges = array_merge((array)$this->all_ranges, (array)$range_path);
			}
		}
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		
		$config['subjectareasselected'] = '';
		$config['subjectareasselected'] .= '|' . implode('|', $this->all_ranges);
		$config['selectallsubjectareas'] = '1';
		
		return $config;
	}
	
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
							
		if ($faulty_values == '')
			$faulty_values = array();	
		$out = '';
		$table = '';
		if ($edit_form == '')
			$edit_form =& new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $this->getEditFormHeadline($edit_form);
		
		$title = _("Alle Studienbereiche anzeigen:");
		$info = _("W�hlen Sie diese Option, wenn alle Veranstaltungen aus allen Studienbereichen angezeigt werden sollen. Unabh�ngig von unten vorgenommener Auswahl.");
		$values = '1';
		$names = '';
		$table = $edit_form->editCheckboxGeneric('selectallsubjectareas', $title, $info, $values, $names);
		$table .= $edit_form->editSelectSubjectAreas($this->selector);
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($content_table, $submit);
		$out .= $edit_form->editBlank();
		
		return  $element_headline . $out;
	}
	
	function executeCommand ($command, $value = "") {

		if ($command == 'do_search_x') {
			$GLOBALS['com'] = 'edit';
			$this->config->setValue($this->name, 'subjectareasselected',
					$_POST['SelectSubjectAreas_subjectareasselected']);
		}
		return TRUE;
	}
			
	
	function checkValue ($attribute, $value) {
		if ($attribute == 'selectallsubjectareas') {
			// This is necessary for checkbox-values. If there is no checkbox
			// checked, the variable is not declared and it is necessary to set the
			// variable to "".
			if (!isset($GLOBALS['HTTP_POST_VARS'][$this->name . '_' . $attribute])) {
				$GLOBALS['HTTP_POST_VARS'][$this->name . '_' . $attribute] = '';
				return FALSE;
			}
			return !($value == '1' || $value == '');
		}
		if ($attribute == 'subjectareasselected' && sizeof($GLOBALS['HTTP_POST_VARS'][$this->name . '_selectallsubjectareas'])) {
			return ($value == '0');
		}

		return FALSE;
	}
	
}

?>
