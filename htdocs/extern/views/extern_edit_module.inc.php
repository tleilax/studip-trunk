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

echo "<table class=\"blank\" border=\"0\" width=\"100%\" ";
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
				echo "</a>\n</td></tr>\n</table>\n";
				print_footer();
				page_close();
				exit;
			}
		}
	}
}
else {
	foreach ($EXTERN_MODULE_TYPES as $type) {
		if ($type["module"] == $mod) {
			// Vorl�ufiger Bugfix
			$class_name = "ExternModule" . $mod;
			require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/modules/$class_name.class.php");
			$module =& new ExternModule($range_id, $mod, $config_id);
		}
	}
}

if (!$module)
	die("Unknown module type");

// execute commands they modify attributes of given element
if ($execute_command)
	$module->executeCommand($edit, $execute_command, $pos);

$elements = $module->getAllElements();
	
// the first parameter of printOutEdit() has to be an array, because it is
// possible to open more than one element form
$edit_open = "";
$l=0;
foreach ($elements as $element) {
	if ($edit == $element->getName())
		$edit_open = array("$edit" => ($com != "close"));
}
if ($com == "new" || $com == "edit" || $com == "open" || $com == "close") {
	echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
	$module->printoutEdit($edit_open, $HTTP_POST_VARS, "", $edit);
}

if ($com == "store") {

	$faulty_values = $module->checkFormValues($edit);
	$fault = FALSE;
	foreach ($faulty_values as $faulty) {
		if (in_array(TRUE, $faulty)) {
			$message = sprintf(_("Bitte korrigieren Sie die mit %s gekennzeichneten Werte!"),
					"<font color=\"#ff0000\" size=\"+1\"><b>*</b></font>");
			my_info($message);
			echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
			$module->printoutEdit($edit_open, $HTTP_POST_VARS,
					$faulty_values, $edit);
			$fault = TRUE;
			break;
		}
	}
	if (!$fault) {
		// This is the right place to trigger some functions by special 
		// POST_VARS-values. At the moment there is only one: If the name of the
		// configuration was changed, setup the extern_config table.
		if ($edit == "Main" && $HTTP_POST_VARS["Main_name"] != $module->config->config_name) {
			if (!change_config_name($module->config->range_id, $module->getType(), $module->config->getId(),
					$module->config->config_name, $HTTP_POST_VARS["Main_name"])) {
				$message = _("Der Konfigurationsname wurde bereits f�r eine Konfiguration dieses Moduls vergeben. Bitte geben Sie einen anderen Namen ein.");
				my_error($message, "blank", 1);
				echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
				$module->printoutEdit($edit_open, "$HTTP_POST_VARS", "", $edit);
			}
			$module->store($edit, $HTTP_POST_VARS);
			$message = _("Die eingegebenen Werte wurden �bernommen und der Name der Konfiguration ge�ndert.");
			my_msg($message, "blank", 1);
			echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
			$module->printoutEdit($edit_open, "", "", $edit);
		}
		else {
			$module->store($edit, $HTTP_POST_VARS);
			$message = _("Die eingegebenen Werte wurden �bernommen.");
			my_msg($message, "blank", 1);
			echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
			$module->printoutEdit($edit_open, "", "", $edit);
		}
	}
}

echo "</td></tr>\n";
if (!$edit_open[$edit]) {
	echo "<tr><td class=\"blank\">&nbsp;</td></tr>\n";
	echo "<tr><td class=\"blank\" align=\"center\">";
	echo "<a href=\"{$GLOBALS['PHP_SELF']}?list=TRUE&view=extern_inst\">";
	echo "<img " . makeButton("zurueck", "src");
	echo " border=\"0\" align=\"absmiddle\"></a>\n</td></tr>\n";
}
echo "</table></td></tr></table>\n</td>\n<td width=\"10%\" class=\"blank\" valign=\"top\">\n";
echo "<table class=\"blank\" border=\"0\" width=\"100%\" ";
echo "align=\"left\" cellspacing=\"0\" cellpadding=\"5\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";

$info_edit_element = _("Um die Werte eines einzelnen Elements zu &auml;ndern, klicken Sie bitte den &quot;&Uuml;bernehmen&quot;-Button innerhalb des jeweiligen Elements.");
// the type of this module is not Global
if ($module->getType() != 0) {
	$info_preview = _("Um eine Vorschau der Seite zu erhalten, klicken Sie bitte hier:");
	$info_preview .= "<br>&nbsp;<div align=\"center\">";
	$info_preview .= "<a target=\"_blank\" href=\"{$GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"]}extern.php";
	$info_preview .= "?module=" . $module->getName() . "&range_id=" . $module->config->range_id;
	$info_preview .= "&preview=1&config_id=" . $module->config->getId();
	if ($global_config = get_global_config($module->config->range_id))
		$info_preview .= "&global_id=$global_config";
	$info_preview .= "\">";
	$info_preview .= makeButton("vorschau") . "</a></div><br>";
	$info_preview .= _("Die Vorschau wird in einem neuen Fenster ge&ouml;ffnet.") . "<br>";
	$info_preview .= _("Es werden eventuell nicht alle Einstellungen in der Vorschau angezeigt.");

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
}
// the type is Global -> no preview
else {
	$info_content = array(	
									array("kategorie" => "Information:",
												"eintrag" => array(	
													array("icon" => "pictures/ausruf_small.gif",
																"text" => $info_edit_element
													)
									)));
}

print_infobox($info_content, "pictures/einrichtungen.jpg");
?>
