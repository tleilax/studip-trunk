<?
/**
* extern_edit_module.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		extern_edit_module
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_edit_module.inc.php
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


require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_EXTERN/lib/ExternModule.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "msg.inc.php");

$sess->register("EXTERN_SESSION_OPEN_ELEMENTS");

echo "<tr><td class=\"blank\" width=\"99%\" valign=\"top\">\n";
echo "<table class=\"blank\" border=\"0\" width=\"95%\" ";
echo "align=\"left\" cellspacing=\"0\" cellpadding=\"0\">\n";

// it's forbidden to use the command "new" with a given config_id
if ($com == "new")
	$config_id = "";

$module = FALSE;
if ($com == "new") {
	foreach ($EXTERN_MODULE_TYPES as $key => $type) {
		if ($type["module"] == $mod) {
			$configurations = get_all_configurations($range_id, $key);
			if (sizeof($configurations[$type["module"]]) < $EXTERN_MAX_CONFIGURATIONS)
				$module =& new ExternModule($range_id, $mod, "", "NEW");
			else {
				$message = sprintf(_("Es wurden bereits %s Konfigurationen angelegt. Sie k&ouml;nnen f&uuml;r dieses Module keine weiteren Konfigurationen anlegen.")
						, $EXTERN_MAX_CONFIGURATIONS);
				my_error($message, "blank", 1);
				echo "<tr><td class=\"blank\" align=\"center\">\n";
				echo "<a href=\"$PHP_SELF??list=TRUE&view=extern_inst\">";
				echo makeButton("zurueck");
				echo "</a>\n</td></tr>\n";
				print_footer();
				page_close();
				exit;
			}
		}
	}
}
else {
	foreach ($EXTERN_MODULE_TYPES as $type) {
		if ($type["module"] == $mod)
			$module =& new ExternModule($range_id, $mod, $config_id);
	}
}

if (!$module)
	die("Unknown module type");

// execute commands they modify attributes of the main element
if ($main_element_command)
	$module->mainCommand($main_element_command, $pos);

//if ($com == "" || $com == "new" || $com == "edit" || $com == "open" || $com == "close") {

$elements = $module->getAllElements();
	
// the first parameter of printOutEdit() has to be an array, because it is
// possible to open more than one element form
foreach ($elements as $element) {
	if ($edit == $element->getName())
	//	$EXTERN_SESSION_OPEN_ELEMENTS[$element->getName()] = ($com == "open");
		$edit_open = array("$edit" => ($com != "close"));
}
if ($com == "new" || $com == "edit" || $com == "open" || $com == "close") {
	echo "<tr><td class=\"blank\" width=\"99%\" valign=\"top\">\n";
	$module->printoutEdit($edit_open, $HTTP_POST_VARS, "", $edit);
}

if ($com == "store") {

	$faulty_values = $module->checkFormValues($edit);

	if ($faulty_values) {
		$message = sprintf(_("Bitte korrigieren Sie die mit %s gekennzeichneten Werte!"),
				"<font color=\"#ff0000\" size=\"+1\"><b>*</b></font>");
		my_info($message);
		echo "<tr><td class=\"blank\" width=\"99%\" valign=\"top\">\n";
		$module->printoutEdit($edit_open, $HTTP_POST_VARS,
				$faulty_values, $edit);
	}
	else {
		// This is the right place to trigger some functions by special 
		// POST_VARS-values. At the moment there is only one: If the name of the
		// configuration was changed, setup the extern_config table.
		if ($edit == "Main" && $HTTP_POST_VARS["Main_name"] != $module->config->config_name) {
			if (!change_config_name($module->config->range_id, $module->getType(), $module->config->getId(),
					$module->config->config_name, $HTTP_POST_VARS["Main_name"])) {
				$message = _("Der Konfigurationsname wurde bereits f�r eine Konfiguration dieses Moduls vergeben. Bitte geben Sie einen anderen Namen ein.");
				my_error($message, "blank", 1);
				echo "<tr><td class=\"blank\" width=\"99%\" valign=\"top\">\n";
				$module->printoutEdit($edit_open, "$HTTP_POST_VARS", "", $edit);
			}
			$module->store($edit, $HTTP_POST_VARS);
			$message = _("Die eingegebenen Werte wurden �bernommen und der Name der Konfiguration ge�ndert.");
			my_msg($message, "blank", 1);
			echo "<tr><td class=\"blank\" width=\"99%\" valign=\"top\">\n";
			$module->printoutEdit($edit_open, "", "", $edit);
		}
		else {
			$module->store($edit, $HTTP_POST_VARS);
			$message = _("Die eingegebenen Werte wurden �bernommen.");
			my_msg($message, "blank", 1);
			echo "<tr><td class=\"blank\" width=\"99%\" valign=\"top\">\n";
			$module->printoutEdit($edit_open, "", "", $edit);
		}
	}
}

echo "</td></tr></table>\n<td class=\"blank\" width=\"1%\" valign=\"top\">\n";

$info_edit_element = _("Um die Werte eines einzelnen Elements zu &auml;ndern, klicken Sie bitte den &quot;&Uuml;bernehmen&quot;-Button innerhalb des jeweiligen Elements.");
$info_preview = _("Um eine Vorschau der Seite zu erhalten, klicken Sie bitte hier:");
$info_preview .= "<br>&nbsp;<div align=\"center\">";
$info_preview .= "<a target=\"_blank\" href=\"{$GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"]}extern.php";
$info_preview .= "?module=" . $module->getName() . "&range_id=" . $module->config->range_id;
$info_preview .= "&preview=1&config_id=" . $module->config->getId() . "\">";
$info_preview .= makeButton("vorschau") . "</a></div><br>";
$info_preview .= _("Die Vorschau wird in einem neuen Fenster ge&ouml;ffnet.") . "<br>";
$info_preview .= _("Es werden eventuell nicht alle Einstellungen in der Vorschau angezeigt.");
/*echo "<br>";
 print_r($EXTERN_SESSION_OPEN_ELEMENTS);
 echo "<br>";
//$open_elements = $EXTERN_SESSION_OPEN_ELEMENTS;
if ($EXTERN_SESSION_OPEN_ELEMENTS["Main"]) {
	$info_name = _("Verwenden Sie f�r den Namen der Konfiguration keine Sonderzeichen oder Umlaute.");
	$info_content = array(	
									array("kategorie" => "Information:",
												"eintrag" => array(	
													array("icon" => "pictures/ausruf_small.gif",
																"text" => $info_edit_element
													),
													"eintrag" => array(	
													array("icon" => "pictures/ausruf_small.gif",
																"text" => $info_name
													)
									))),
									array("kategorie" => "Aktion:",
		   									"eintrag" => array(	
													array("icon" => "pictures/ausruf_small.gif",
																"text" => $info_preview,
													)
									)));
}
else {*/
	$info_content = array(	
									array("kategorie" => "Information:",
												"eintrag" => array(	
													array("icon" => "pictures/ausruf_small.gif",
																"text" => $info_edit_element
													)
									)),
									array("kategorie" => "Aktion:",
		   									"eintrag" => array(	
													array("icon" => "pictures/ausruf_small.gif",
																"text" => $info_preview,
													)
									)));
//}

print_infobox($info_content, "pictures/einrichtungen.jpg");

?>
