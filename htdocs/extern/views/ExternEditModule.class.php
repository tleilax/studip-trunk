<?
/**
* ExternEditModule.class.php
* 
* basic functions for the extern interfaces
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternEditModule
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternEditModule.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/views/ExternEditHtml.class.php");

class ExternEditModule extends ExternEditHtml {
	
	function ExternEditModule (&$config, $form_values = "", $faulty_values = "",
			 $edit_element = "") {
		ExternEdit::ExternEdit(&$config, $form_values, $faulty_values, $edit_element);
	}
	
	function editMainSettings ($field_names, $hide_fields = "", $hide = "") {
		// these two values are always necessary, even there is an error in the users inputs, so
		// there arent transfered via HTTP_POST_VARS
		$this->form_values[$this->element_name . "_order"]
				= $this->config->getValue($this->element_name, "order");
		$this->form_values[$this->element_name . "_visible"]
				= $this->config->getValue($this->element_name, "visible");
		
		$order = $this->getValue("order");
		$aliases = $this->getValue("aliases");
		$visible = $this->getValue("visible");
		$widths = $this->getValue("width");
		$sort = $this->getValue("sort");
		if (!is_array($hide_fields["sort"]))
			$hide_fields["sort"] = array();
		if (!is_array($hide_fields["aliases"]))
			$hide_fields["aliases"] = array();
		if (!is_array($hide))
			$hide = array();
		
		$this->css->resetClass();
		$this->css->switchClass();
		
		$out = "<tr><td><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
		$out .= "<tr" . $this->css->getFullClass() . ">\n";
		$out .= "<td><font size=\"2\"><b>" . _("Datenfeld") . "</b></font></td>\n";
		$out .= "<td><font size=\"2\"><b>" . _("&Uuml;berschrift") . "</b></font></td>\n";
		if (!in_array("width", $hide))
			$out .= "<td><font size=\"2\"><b>" . _("Breite") . "</b></font></td>\n";
		if (!in_array("sort", $hide))
			$out .= "<td><font size=\"2\"><b>" . _("Sortierung") . "</b></font></td>\n";
		if (!in_array("visible", $hide))
			$out .= "<td><font size=\"2\"><b>" . _("Reihenfolge/<br>Sichtbarkeit") . "</b></font></td>\n";
		$out .= "</tr>\n";
		$this->css->switchClass();
		
		for ($i = 0; $i < sizeof($field_names); $i++) {
			
			// name of column
			$out .= "<tr" . $this->css->getFullClass() . ">\n";
			$out .= "<td><font size=\"2\">&nbsp;{$field_names[$order[$i]]}</font></td>";
			
			// column headline
			if (!in_array($order[$i], $hide_fields["aliases"])) {
				$out .= "<td><input type=\"text\" name=\"{$this->element_name}_aliases[$order[$i]]\"";
				$out .= "\" size=\"12\" maxlength=\"50\" value=\"";
				$out .= $aliases[$order[$i]] . "\">";
				if ($this->faulty_values[$this->element_name . "_aliases"][$order[$i]])
					$out .= $this->error_sign;
				$out .= "</td>\n";
			}
			else {
				$out .= "<td>&nbsp;</td>\n";
				$out .= "<input type=\"hidden\" name=\"{$this->element_name}_aliases[$order[$i]]\" ";
				$out .= "value=\"\">";
			}
			
			// width
			if (!in_array("width", $hide)) {
				$width = str_replace("%", "", $widths[$order[$i]]);
				$out .= "<td><input type=\"text\" name=\"{$this->element_name}_width[$order[$i]]";
				$out .= "\" size=\"3\" maxlength=\"3\" value=\"$width\">";
				if ($this->faulty_values[$this->element_name . "_width"][$order[$i]])
					$out .= $this->error_sign;
				$out .= "</td>\n";
			}
			
			// sort
			if (!in_array("sort", $hide)) {
				if (!in_array($order[$i], $hide_fields["sort"])) {
					$out .= "<td><select name=\"{$this->element_name}_sort[$order[$i]]\" ";
					$out .= "size=\"1\">\n";
					$out .= "<option value=\"0\"" . ($sort[$order[$i]] == 1 ? " selected" : "")
							. ">" . _("keine") . "</option>";
					for ($j = 1; $j <= (sizeof($order) - sizeof($hide_fields["sort"])); $j++) {
						if ($sort[$order[$i]] == $j)
							$selected = " selected";
						else
							$selected = "";
						$out .= "<option value=\"$j\"$selected>$j</option>";
					}
					$out .= "\n</select>\n</td>\n";
				}
				else {
					$out .= "<td>&nbsp;</td>\n";
					$out .= "<input type=\"hidden\" name=\"{$this->element_name}_sort[$order[$i]]\" ";
					$out .= "value=\"0\">\n";
				}
			}
			
			if (!in_array("visible", $hide)) {
				// move left
				$out .= "<td valign=\"top\" nowrap=\"nowrap\">";
				$out .= "<input type=\"image\" name=\"{$this->element_name}_move_left[$i]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/move_up.gif\"";
				$out .= tooltip(_("Datenfeld verschieben"));
				$out .= "border=\"0\" align=\"bottom\">\n";
				
				// move right
				$out .= "<input type=\"image\" name=\"{$this->element_name}_move_right[$i]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/move_down.gif\"";
				$out .= tooltip(_("Datenfeld verschieben"));
				$out .= "border=\"0\" align=\"bottom\">\n&nbsp;";
				
				// visible
				if ($visible[$order[$i]]) {
					$out .= "<input type=\"image\" name=\"{$this->element_name}_hide[{$order[$i]}]\" ";
					$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/on_small.gif\"";
					$out .= tooltip(_("Datenfeld ausblenden"));
					$out .= "border=\"0\" align=\"middle\">\n";
				}
				else {
					$out .= "<input type=\"image\" name=\"{$this->element_name}_show[{$order[$i]}]\" ";
					$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/off_small_blank.gif\"";
					$out .= tooltip(_("Datenfeld anzeigen"));
					$out .= "border=\"0\" align=\"middle\">\n</td>\n";
				}
			}
			
			$out .= "</tr>\n";
			$this->css->switchClass();
		}
		
		// width in pixels or percent
		if (!in_array("widthpp", $hide)) {
			$colspan = 4 - sizeof($hide);
			$title = _("Breite in:");
			$info = _("W�hlen Sie hier, ob die Breiten der Tabellenspalten als Prozentwerte oder Pixel interpretiert werden sollen.");
			$width_values = array("%", "");
			$width_names = array(_("Prozent"), _("Pixel"));
			$out .= "<tr" . $this->css->getFullClass() . ">\n";
			$out .= "<td><font size=\"2\">&nbsp;$title</font></td>";
			$out .= "<td colspan=\"$colspan\"><input type=\"radio\" name=\"{$this->element_name}_widthpp\" value=\"%\"";
			if (substr($widths[0], -1) == "%")
				$out .= " checked=\"checked\"";
			$out .= " /><font size=\"2\">" . _("Prozent") . "&nbsp; &nbsp;</font><input type=\"radio\" name=\"";
			$out .= "{$this->element_name}_widthpp\" value=\"\"";
			if (substr($widths[0], -1) != "%")
				$out .= " checked=\"checked\"";
			$out .= " /><font size=\"2\">" . _("Pixel") . "&nbsp; &nbsp;</font>\n";
			$out .= "<img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/info.gif\"";
			$out .= tooltip($info, TRUE, TRUE) . ">$error_sign</td></tr>\n";
		}
		
		$out .= "</table>\n</td></tr>\n";
		
		return $out;
	}
	
	function editName ($attribute) {
		$info = _("Geben Sie den Namen der Konfiguration an.");
		
		return $this->editTextfieldGeneric($attribute, "", $info, 40, 40);
	}
	
	function editGroups () {
		$groups_db = get_statusgruppen_by_name($this->config->range_id, "''", TRUE);
		
		if (!$groups_db)
			return FALSE;
		
		$title = _("Gruppen ausw�hlen:");
		$info = _("W�hlen sie die Statusgruppen aus, die ausgegeben werden sollen.");
		$groups_config = $this->getValue("groups");
		
		// this value is always necessary, even there is an error in the users inputs, so
		// it isn't transfered via HTTP_POST_VARS
		$this->form_values[$this->element_name . "_groupsvisible"]
				= $this->config->getValue($this->element_name, "groupsvisible");
		
		// initialize groups if this value isn't set in the config file
		if (!$groups_config)
			$groups_config = array_keys($groups_db);
			
		$groups_aliases = $this->getValue("groupsalias");
		$groups_visible = $this->getValue("groupsvisible");
		if (!$groups_visible)
			$groups_visible = array();
		
		for ($i = 0; $i < sizeof($groups_config); $i++)
			$groups[$groups_config[$i]] = $groups_aliases[$i];

		$this->css->resetClass();
		$this->css->switchClass();
		$out = "<tr><td><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
		$out .= "<tr" . $this->css->getFullClass() . ">\n";
		$out .= "<td width=\"42%\"><font size=\"2\"><b>" . _("Gruppenname") . "</b></font></td>\n";
		$out .= "<td width=\"48%\"><font size=\"2\"><b>" . _("alternativer Gruppenname") . "</b></font></td>\n";
		$out .= "<td width=\"1%\"><font size=\"2\"><b>" . _("Sichtbarkeit") . "</b></font></td>\n";
		$out .= "<td width=\"9%\"><font size=\"2\">&nbsp;</font></td>\n";
		$out .= "</tr>\n";
		$this->css->switchClass();
		$i = 0;
		foreach ($groups_db as $id => $name) {
		
			// name of group
			if (strlen($name) > 30)
				$name = substr($name, 0, 14) . "[...]" . substr($name, -10);
			$out .= "<tr" . $this->css->getFullClass() . ">\n";
			$out .= "<td nowrap=\"nowrap\"><font size=\"2\">&nbsp;" . htmlReady($name) . "</font></td>";
			
			// column headline
			$out .= "<td nowrap=\"nowrap\"><input type=\"text\" name=\"{$this->element_name}_groupsalias[]\"";
			$out .= "\" size=\"25\" maxlength=\"150\" value=\"";
			$out .= $groups[$id] . "\">";
			if ($this->faulty_values[$this->element_name . "_groupsalias"][$i])
					$out .= $this->error_sign;
			$out .= "</td>\n";
			
			// visible
			if (in_array($id, $groups_visible)) {
				$out .= "<td align=\"center\"><input type=\"image\" name=\"{$this->element_name}_hide_group[$id]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/on_small.gif\"";
				$out .= tooltip(_("Spalte ausblenden"));
				$out .= "border=\"0\" align=\"middle\">\n</td>\n";
			}
			else {
				$out .= "<td align=\"center\"><input type=\"image\" name=\"{$this->element_name}_show_group[$id]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/off_small_blank.gif\"";
				$out .= tooltip(_("Spalte einblenden"));
				$out .= "border=\"0\" align=\"middle\">\n</td>\n";
			}
			$out .= "<td>&nbsp;</td></tr>\n";
			$this->css->switchClass();
			$i++;
		}
			
		$out .= "</table>\n</td></tr>\n";
		
		return $out;
	}
	
	function editSemTypes () {
		global $SEM_TYPE, $SEM_CLASS;
		// these two values are always necessary, even there is an error in the users inputs, so
		// there arent transfered via HTTP_POST_VARS
		$this->form_values[$this->element_name . "_order"]
				= $this->config->getValue($this->element_name, "order");
		
		$order = $this->getValue("order");
		
		if (!is_array($order))
			$order = array_keys($SEM_TYPE);
			
		$this->css->resetClass();
		$this->css->switchClass();
		
		$out = "<tr><td><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
		$out .= "<tr" . $this->css->getFullClass() . ">\n";
		$out .= "<td><font size=\"2\"><b>" . _("Datenfeld") . "</b></font></td>\n";
		$out .= "<td><font size=\"2\"><b>" . _("&Uuml;berschrift") . "</b></font></td>\n";
		$out .= "<td><font size=\"2\"><b>" . _("Reihenfolge") . "</b></font></td>\n";
		$out .= "</tr>\n";
		$this->css->switchClass();
			
		foreach ($SEM_CLASS as $class_index => $foo) {
			$i = 0;
			foreach ($SEM_TYPE as $type_index => $type) {
				if ($type["class"] == $class_index)
					$mapping[$type_index] = $i++;
			}
			$classes[$class_index] = $this->getValue("class_$class_index");
		}
		
		for ($i = 0; $i < sizeof($order); $i++) {
			// name of column
			$out .= "<tr" . $this->css->getFullClass() . ">\n";
			$out .= "<td><font size=\"2\">&nbsp;";
			if (strlen($SEM_TYPE[$order[$i]]["name"]) > 25) {
				$out .= htmlReady(substr($SEM_TYPE[$order[$i]]["name"], 0, 22)
						. "... ({$SEM_CLASS[$SEM_TYPE[$order[$i]]['class']]['name']})");
			}
			else {
				$out .= htmlReady($SEM_TYPE[$order[$i]]["name"]
						. " ({$SEM_CLASS[$SEM_TYPE[$order[$i]]['class']]['name']})");
			}
			$out .= "</font></td>";
			
			// column headline
			$out .= "<td><input type=\"text\" name=\"{$this->element_name}_class_";
			$out .= $SEM_TYPE[$order[$i]]['class'] . "[{$mapping[$order[$i]]}]\"";
			$out .= "\" size=\"20\" maxlength=\"100\" value=\"";
			if (isset($classes[$SEM_TYPE[$order[$i]]['class']][$mapping[$order[$i]]]))
				$out .= $classes[$SEM_TYPE[$order[$i]]['class']][$mapping[$order[$i]]] . "\">";
			else {
				$out .= $SEM_TYPE[$order[$i]]["name"]
						. " ({$SEM_CLASS[$SEM_TYPE[$order[$i]]['class']]['name']})\">";
			}
			if ($this->faulty_values[$this->element_name
					. "_class_{$SEM_TYPE[$order[$i]]['class']}"][$mapping[$order[$i]]]) {
				$out .= $this->error_sign;
			}
			$out .= "</td>\n";
						
				// move left
			$out .= "<td valign=\"top\" align=\"center\" nowrap=\"nowrap\">";
			$out .= "<input type=\"image\" name=\"{$this->element_name}_move_left[$i]\" ";
			$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/move_up.gif\"";
			$out .= tooltip(_("Datenfeld verschieben"));
			$out .= "border=\"0\" align=\"bottom\">\n";
			
			// move right
			$out .= "<input type=\"image\" name=\"{$this->element_name}_move_right[$i]\" ";
			$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/move_down.gif\"";
			$out .= tooltip(_("Datenfeld verschieben"));
			$out .= "border=\"0\" align=\"bottom\">\n&nbsp;";
			
			$out .= "</tr>\n";
			$this->css->switchClass();
		}
		
		$out .= "</table>\n</td></tr>\n";
		
		return $out;
	}
}
	
?>
