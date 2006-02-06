<?
/**
* admin_modules.php
* 
* switch the modules (defines in Modules.class.php) on/off for Institutes or Veranstaltungen
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @module		admin_modules.php
* @modulegroup		admin
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_modules.php
// Module fuer Veranstaltungen und Einrichtungen (definiert in Modules.class.php) an/abschalten
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$perm->check("tutor");

include ($ABSOLUTE_PATH_STUDIP."seminar_open.php"); // initialise Stud.IP-Session
require_once($ABSOLUTE_PATH_STUDIP."msg.inc.php");	//Ausgaben
require_once($ABSOLUTE_PATH_STUDIP."config.inc.php");	//Settings....
require_once($ABSOLUTE_PATH_STUDIP."functions.php");	//basale Funktionen
require_once($ABSOLUTE_PATH_STUDIP."visual.inc.php");	//Darstellungsfunktionen
require_once($ABSOLUTE_PATH_STUDIP."messaging.inc.php");	//Nachrichtenfunktionen
require_once($ABSOLUTE_PATH_STUDIP."lib/classes/AdminModules.class.php");	//Nachrichtenfunktionen

$db=new DB_Seminar;
$db2=new DB_Seminar;
$cssSw=new cssClassSwitcher;
$sess->register("admin_modules_data");
$messaging=new messaging;
$amodules=new AdminModules;
if ($PLUGINS_ENABLE){
	$plugins = $amodules->pluginengine->getAllEnabledPlugins(); // get all installed and enabled plugins
}

if ($perm->have_studip_perm("tutor", $admin_modules_data["range_id"])) {
	//Sicherheitscheck ob ueberhaupt was zum Bearbeiten gewaehlt ist.
	if ($default_x) {
		$admin_modules_data["changed_bin"] = $amodules->getDefaultBinValue($admin_modules_data["range_id"]);
	}
	
	//consistency: kill objects
	foreach ($amodules->registered_modules as $key => $val) {
		$moduleXxDeactivate = "module".$key."Deactivate";
		$delete_xx = "delete_".$key;
	
		if (($$delete_xx) && (method_exists($amodules,$moduleXxDeactivate))) {
			$amodules->$moduleXxDeactivate($admin_modules_data["range_id"]);
			$amodules->clearBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
			unset($admin_modules_data["conflicts"][$key]);
			$resolve_comflicts = TRUE;
		}
	}

	//consitency: cancel kill objects
	foreach ($amodules->registered_modules as $key => $val) {
		$cancel_xx = "cancel_".$key;
		
		if (($$cancel_xx) && (method_exists($amodules,$moduleXxDeactivate))) {
			$amodules->setBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
			unset($admin_modules_data["conflicts"][$key]);
			$resolve_comflicts = TRUE;
		}
	}
	
	if (($uebernehmen_x) || ($retry)) {
		$msg='';

		if ($uebernehmen_x)
			foreach ($amodules->registered_modules as $key => $val) {
				//after sending, set all "conflicts" to TRUE (we check them later)
				$admin_modules_data["conflicts"][$key] = TRUE;
				
				$tmp_key = $key."_value";
				if ($$tmp_key == "TRUE")
					$$tmp_key = TRUE;
				else
					$$tmp_key = FALSE;

				if ($$tmp_key) {
					$amodules->setBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
				} else {
					$amodules->clearBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
				}
			}
			if ($PLUGINS_ENABLE){
				foreach ($plugins as $plugin){
					$key = "plugin_".$plugin->getPluginId();
					if ($$key == "TRUE"){
						$plugin->setActivated(true);
					}
					else {
						$plugin->setActivated(false);
					}				
					$amodules->pluginengine->savePlugin($plugin);
				}
				$plugins = $amodules->pluginengine->getAllEnabledPlugins();
			}
			
		//consistency checks
		foreach ($amodules->registered_modules as $key => $val) {
			$delete_xx = "delete_".$key;
			$cancel_xx = "cancel_".$key;
			
			//checks for deactivating a module
			$getModuleXxExistingItems = "getModule".$key."ExistingItems";
	
			if (method_exists($amodules,$getModuleXxExistingItems)) {
				if (($amodules->isBit($admin_modules_data["orig_bin"],  $amodules->registered_modules[$key]["id"])) &&
					(!$amodules->isBit($admin_modules_data["changed_bin"],  $amodules->registered_modules[$key]["id"])) &&
					($amodules->$getModuleXxExistingItems($admin_modules_data["range_id"])) &&
					($admin_modules_data["conflicts"][$key])) {
					
					$msg.="info�".$amodules->registered_modules[$key]["msg_warning"];
					$msg.="<br /><a href=\"".$PHP_SELF."?delete_$key=TRUE&retry=TRUE\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
					$msg.="<a href=\"".$PHP_SELF."?cancel_$key=TRUE&retry=TRUE\">" . makeButton("nein", "img") . "</a>\n�";
				} else
					unset($admin_modules_data["conflicts"][$key]);
			} else
				unset($admin_modules_data["conflicts"][$key]);
				
			//checks for activating a module
			$moduleXxActivate = "module".$key."Activate";
	
			if (method_exists($amodules,$moduleXxActivate)) {
				if ((!$amodules->isBit($admin_modules_data["orig_bin"],  $amodules->registered_modules[$key]["id"])) &&
					($amodules->isBit($admin_modules_data["changed_bin"],  $amodules->registered_modules[$key]["id"]))) {
					
					$amodules->$moduleXxActivate($admin_modules_data["range_id"]);
				}
			}
		}
	}
	
	if ((!count($admin_modules_data["conflicts"])) && ($admin_modules_data["orig_bin"] != $admin_modules_data["changed_bin"])) {
		$amodules->writeBin($admin_modules_data["range_id"], $admin_modules_data["changed_bin"]);
		$admin_modules_data["orig_bin"] = $admin_modules_data["changed_bin"];
		$admin_modules_data["modules_list"] = $amodules->getLocalModules($admin_modules_data["range_id"]);
		$msg.= "msg�Die ver&auml;nderte Modulkonfiguration wurde &uuml;bernommen";
	}
}

// Start of Output
include ($ABSOLUTE_PATH_STUDIP."html_head.inc.php"); // Output of html head
include ($ABSOLUTE_PATH_STUDIP."header.php");   // Output of Stud.IP head
include ($ABSOLUTE_PATH_STUDIP."links_admin.inc.php");	//hier wird das Reiter- und Suchsystem des Adminbereichs eingebunden

//get ID
if ($SessSemName[1])
	$range_id=$SessSemName[1]; 

if (!$admin_modules_data["conflicts"])
	$admin_modules_data["conflicts"] = array();
	
//wenn wir frisch reinkommen, werden benoetigte Daten eingelesen
if (($range_id) && (!$uebernehmen_x) && (!$delete_forum) && (!$delete_documents) && ((!count($admin_modules_data["conflicts"]) && (is_array($admin_modules_data["conflicts"]))))) {
	$admin_modules_data["modules_list"] = $amodules->getLocalModules($range_id);
	$admin_modules_data["orig_bin"] = $amodules->getBin($range_id);
	$admin_modules_data["changed_bin"] = $amodules->getBin($range_id);
	$admin_modules_data["range_id"] = $range_id;
} else {
	//Sicherheitscheck ob ueberhaupt was zum Bearbeiten gewaehlt ist.
	if (!$admin_modules_data["range_id"]) {
		echo "</tr></td></table>";
		die;
	}
}

if ($admin_modules_data["range_id"]) {

?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2>&nbsp; <b>
		<?
		echo getHeaderLine($admin_modules_data["range_id"])." -  "._("Module konfigurieren");
		?>
		</td>		
	</tr>
 	<tr>
		<td class="blank" valign="top">
			<?
			if (isset($msg)) {
			?>
				<table border="0">
				<tr><td>&nbsp;</td></tr>
				<?parse_msg($msg);?>
				</table>
			<? } ?>
			<br />
			<blockquote>
			<b><?=_("Module konfigurieren") ?></b><br /><br />
			<?=_("Sie k&ouml;nnen hier einzelne Module nachtr&auml;glich aktivieren oder deaktivieren.")?> <br />
			<?=_("Mit &raquo;zur&uuml;cksetzten&laquo; k&ouml;nnen Sie die Ausgangs-Modulkonfiguration wieder herstellen.")?> <br /><br />
			</blockqoute>
		</td>
		<td class="blank" align="right" valign="top"><img src="pictures/blank.gif" height="10" width="5" /><br />
			<img src="pictures/modules.jpg" border="0"><img src="pictures/blank.gif" height="10" width="10" />
		</td>		
	</tr>
	<tr>
	<td class="blank" colspan=2>
	<form method="POST" name="modules" action="<? echo $PHP_SELF ?>">
		<table width="99%" border=0 cellpadding=2 cellspacing=0 align="center">
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" align="center" colspan="4">		
				<input type="IMAGE" name="uebernehmen" <?=makeButton("uebernehmen", "src")?> border=0 value="uebernehmen">
				&nbsp;<input type="IMAGE" name="default" <?=makeButton("zuruecksetzen", "src")?> border=0 value="uebernehmen">
				<? if ($admin_modules_data["orig_bin"] != $admin_modules_data["changed_bin"]) {
					?> <br /><img src="pictures/ausruf_small2.gif" align="absmiddle" />&nbsp;<font size=-1><?=_("Diese Daten sind noch nicht gespeichert.")?></font><br /> <?
					}
				?>
			</td>
		</tr>
		<? 
		foreach ($amodules->registered_modules as $key => $val) {
			if ($amodules->isEnableable($key, $admin_modules_data["range_id"])) { ?>
			<tr <? $cssSw->switchClass() ?> rowspan=2>
				<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
					&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>"  width="10%" align="left">
					<font size=-1><b><?=$val["name"]?></b><br /></font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="16%">
					<input type="RADIO" name="<?=$key?>_value" value="TRUE" <?=($amodules->isBit($admin_modules_data["changed_bin"], $val["id"])) ? "checked" : "" ?>>
					<font size=-1><?=_("an")?></font>
					<input type="RADIO" name="<?=$key?>_value" value="FALSE" <?=($amodules->isBit($admin_modules_data["changed_bin"], $val["id"])) ? "" : "checked" ?>>
					<font size=-1><?=_("aus")?><br /></font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="70%">
					<font size=-1><?
					$getModuleXxExistingItems = "getModule".$key."ExistingItems";
					
					if (method_exists($amodules,$getModuleXxExistingItems)) {
						if (($amodules->$getModuleXxExistingItems($admin_modules_data["range_id"])) && ($admin_modules_data["modules_list"][$key]))
							printf ("<font color=\"red\">".$amodules->registered_modules[$key]["msg_pre_warning"]."</font>", $amodules->$getModuleXxExistingItems($admin_modules_data["range_id"]));
						else
							print ($admin_modules_data["modules_list"][$key]) ? $amodules->registered_modules[$key]["msg_deactivate"] : $amodules->registered_modules[$key]["msg_activate"];
					} else
						print ($admin_modules_data["modules_list"][$key]) ? $amodules->registered_modules[$key]["msg_deactivate"] : $amodules->registered_modules[$key]["msg_activate"];
					?></font>
				</td>
			</tr>
			<? }
			
		}
		if ($PLUGINS_ENABLE){
			$plugins = $amodules->pluginengine->getAllEnabledPlugins();
					
			if ($plugins == null){
				$plugins = array();
			}
			foreach ($plugins as $plugin){			
				?>
				<tr <? $cssSw->switchClass() ?> rowspan=2>
				<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
						&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>"  width="10%" align="left">
						<font size=-1><b><?=$plugin->getPluginname()?></b><br /></font>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="16%">
						<input type="RADIO" name="plugin_<?=$plugin->getPluginid()?>" value="TRUE" <?= ($plugin->isActivated()==true) ? "checked" : "" ?>>
						<!-- mark old state -->
						<font size=-1><?=_("an")?></font>
						<input type="RADIO" name="plugin_<?=$plugin->getPluginid()?>" value="FALSE" <?= ($plugin->isActivated()==true) ? "" : "checked" ?>>
						<font size=-1><?=_("aus")?><br /></font>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">
						<font size=-1><?
						$admininfo = $plugin->getPluginAdminInfo();
						if (!is_null($admininfo)){
							if ($plugin->isActivated() || $globalactivated){
								// TODO: Fallunterscheidung, welches AdminInfo angezeigt werden muss.
								if ($globalactivated){
									print ("(per Voreinstellung aktiviert) <font color=\"red\">".$admininfo->getWarningBeforeDeactivation()."</font>");
								}
								else {
									print ("<font color=\"red\">".$admininfo->getWarningBeforeDeactivation()."</font>");
								}
							}
							else {
								print ($admininfo->getWarningBeforeActivation());
							}
						}
						else {
							// kein AdminInfo vorhanden, also nichts ausgeben
							print (_("Dieses Plugin hat keinen Hilfetext bereitgestellt."));
						}
						?></font>
					</td>
				</tr>
				<?php
			}
		}
		?>
		<tr>
			<td class="blank" colspan=3>&nbsp; 
			</td>
		</tr>
		<?
	page_close();		
	}
else
	die;

?>
	</table>
</td>
</tr>
</table>
</body>
</html>
