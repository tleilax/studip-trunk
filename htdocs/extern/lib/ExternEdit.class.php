<?
/**
* ExternEdit.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternEdit
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternEdit.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "cssClassSwitcher.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "language.inc.php");

class ExternEdit {

	var $css;
	var $config;
	var $form_values = array();
	var $faulty_values = array();
	var $element_name = "main";
	var $is_post_vars = FALSE;
	var $edit_element;
	var $width_1 = " width=\"20%\"";
	var $width_2 = " width=\"80%\"";
	var $error_sign = "<font size=\"4\" color=\"ff0000\">&nbsp; &nbsp;<b>*</b></font>";
	
	function ExternEdit (&$config, $form_values = "", $faulty_values = "",
			 $edit_element = "") {
			 
		$this->css = new CssClassSwitcher("", "topic");
		$this->config =& $config;
		$this->form_values = $form_values;
		$this->edit_element = $edit_element;
		
		if (is_array($form_values))
			$this->is_post_vars = TRUE;
		
		if ($faulty_values != "")
			$this->faulty_values = $faulty_values;
	}
	
	function setElementName ($element_name) {
		$this->element_name = $element_name;
	}
	
	function getValue ($attribute) {
		if ($this->is_post_vars && ($this->edit_element == $this->element_name)) {
			$form_name = $this->element_name . "_" . $attribute;
			$value = $this->form_values[$form_name];
			
			if ($value != "" || $this->faulty_values[$form_name]) {
			if (is_array($value)) {
				// sort the array by keys and fit the values for output in a form
				for ($i = 0; $i < sizeof($value); $i++)
					$val_tmp[] = htmlentities(stripslashes($value[$i]), ENT_QUOTES);
				$value = $val_tmp;
			}
			else
				$value = htmlentities(stripslashes($value), ENT_QUOTES);
			
			return $value;
			}
		}
		
		return $this->config->getValue($this->element_name, $attribute);
	}
	
	function editHeader () {
		$out = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" ";
		$out .= "width=\"95%\" align=\"left\">\n";
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the height of a table-row
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editFooter () {
		$out = "</table>\n";
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the thickness of a table border
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editSubmit ($module_name, $config_id, $element_name = "") {
		$this->css->resetClass();
		$this->css->switchClass();
		
		$out = "<tr><td align=\"center\" colspan=\"2\" nowrap=\"nowrap\"";
		$out .= $this->css->getFullClass() . ">&nbsp;";
		$out .= "<a href=\"{$GLOBALS['PHP_SELF']}?list=TRUE&view=extern_inst\">";
		$out .= "<img " . makeButton("abbrechen", "src");
		$out .= " border=\"0\" valign=\"absmiddle\"></a>&nbsp; &nbsp; &nbsp;";
		$out .= "<input type=\"image\" name=\"submit\" ";
		$out .= makeButton("uebernehmen", "src") . ">";
		$out .= "<input type=\"hidden\" name=\"config_id\" value=\"$config_id\">";
		$out .= "<input type=\"hidden\" name=\"mod\" value=\"$module_name\">";
		if ($element_name)
			$out .= "<input type=\"hidden\" name=\"edit\" value=\"$element_name\">";
		$out .= "</td></tr>";
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the thickness of a table border
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editHeadline ($headline) {
		$headline = "&nbsp; $headline";
		
		$out = "<table class=\"blank\" width=\"100%\" cellpadding=\"0\" ";
		$out .= "cellspacing=\"0\" border=\"0\">\n<tr><td class=\"" . $this->css->getHeaderClass();
		$out .= "\" width=\"100%\"><font size=\"2\"><b>$headline</b></font>";
		$out .= "</td></tr>\n</table>\n";
		
		$this->css->resetClass();
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the thickness of a table border
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editTagHeadline ($tag) {
		$headline = "&nbsp; " . sprintf(_("Angaben zum HTML-Tag %s"), "&lt;$tag&gt;");
		
		$out = "<table class=\"blank\" width=\"100%\" cellpadding=\"0\" ";
		$out .= "cellspacing=\"0\" border=\"0\">\n<tr><td class=\"" . $this->css->getHeaderClass();
		$out .= "\" width=\"100%\"><font size=\"2\"><b>$headline</b></font>";
		$out .= "</td></tr>\n</table>\n";
		
		$this->css->resetClass();
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the thickness of a table border
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editElementHeadline ($element_real_name, $module_name, $config_id,
			$open = TRUE, $anker = "") {
			
		$titel = sprintf(_("Angaben zum Element %s"), "&quot;$element_real_name&quot;");
		$icon = "<img src=\"{$GLOBALS['CANONICAL_RELATIV_PATH_STUDIP']}pictures/";
		$icon .= "txt-icon.gif\" border=\"0\">";
		
		if ($this->element_name == $anker)
			$titel .= "<a name=\"anker\">&nbsp;</a>";
		
		if ($open) {
			$link = $GLOBALS["PHP_SELF"] . "?com=close&mod=$module_name&edit=";
			$link .= $this->element_name . "&config_id=$config_id#anker";
			$open = "open";
		}
		else {
			$link = $GLOBALS["PHP_SELF"] . "?com=open&mod=$module_name&edit=";
			$link .= $this->element_name . "&config_id=$config_id#anker";
			$open = "close";
		}
		
		$out = "<tr><td class=\"blank\" width=\"100%\">\n";
		$out .= "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		$out .= "<tr>\n";
		$out .= printhead(0, "", $link, $open, TRUE, $icon, $titel, "", 0, FALSE);
		$out .= "</tr></table>\n</td></tr>\n";
				
		return $out;
	}
	
	function editContentTable ($header, $body) {
		$out = "\n<!-- BEGIN ContentTable -->\n";
		$out .= "<table width=\"90%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		$out .= "<tr><td class=\"blank\" width=\"100%\">\n";
		$out .= "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		$out .= "<tr><td class=\"blank\" width=\"100%\">\n" . $header;
		$out .= "</td></tr>\n</table>\n";
	//	$out .= "<form action=\"{$GLOBALS['PHP_SELF']}j?com=store\" method=\"post\">\n";
		$out .= "<table width=\"100%\" style=\"border-style:solid; border-width:1px; ";
		$out .= "border-color:#000000;\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		$out .= $body . "</table>\n</td></tr>\n</table>\n";
		$out .= "<!-- END ContentTable -->\n";
		
		$this->css->resetClass();
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the thickness of a table border
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editContent ($content, $submit) {
		$out = "\n<!-- BEGIN Content -->\n";
		$out .= "<tr><td class=\"blank\" width=\"100%\" align=\"left\">\n";
		$out .= "<form action=\"{$GLOBALS['PHP_SELF']}?com=store#anker\" method=\"post\">\n";
		$out .= "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";
		$out .= "<tr>" . printcontent("100%", FALSE, $content, "", FALSE) . "</tr>";
		$out .= "$submit</table>\n</form>\n</td></tr>\n";
		$out .= "<!-- END Content -->\n";
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the thickness of a table border
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editBlankContent ($class = "") {
		if (!$class) {
			$this->css->resetClass();
			$this->css->switchClass();
			$class = $this->css->getClass();
		}
		
		$out = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		$out .= "<tr><td class=\"$class\">&nbsp;</td></tr>\n";
		$out .= "</table>\n";
		
		return $out;
	}
	
	/**
	* Prints out a text field for entering the thickness of a table border
	*
	* @param string name the name of the text field
	* @param string value the value for the text pre-emption
	*/
	
	function editBlankContentTable ($class = "") {
		if (!$class) {
			$this->css->resetClass();
			$this->css->switchClass();
			$class = $this->css->getClass();
		}
		
		$out = "<tr><td>\n<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		$out .= "<tr><td class=\"$class\">&nbsp;</td></tr>\n";
		$out .= "</table>\n</td></tr>\n";
		
		return $out;
	}
	
	function editBlank ($class = "") {
		if (!$class) {
			$this->css->resetClass();
			$this->css->switchClass();
			$class = $this->css->getClass();
		}
		
		$out = "<tr><td class=\"$class\" colspan=\"2\">&nbsp;</td></tr>\n";
		$out .= "</td></tr>\n";
		
		return $out;
	}
}
	
?>
	
