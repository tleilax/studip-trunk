<?
/**
* EditResourceData.class.php
* 
* shows the forms to edit the object
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		EditResourceData.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// EditResourceData.class.php
// stellt die forms zur Bearbeitung eines Ressourcen-Objekts zur Verfuegung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignObject.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignObjectPerms.class.php");

require_once ($ABSOLUTE_PATH_STUDIP."/cssClassSwitcher.inc.php");

$cssSw = new cssClassSwitcher;


/*****************************************************************************
EditResourceData, Darstellung der unterschiedlichen Forms zur 
Bearbeitung eines Objects
/*****************************************************************************/
class EditResourceData {
	var $resObject;		//Das Oject an dem gearbeitet wird
	var $used_view;		//the used view
	
	//Konstruktor
	function EditResourceData ($resource_id) {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
		$this->resObject=new ResourceObject($resource_id);
	}
	
	function setUsedView ($value) {
		$this->used_view = $value;
	}
	
	//private
	function selectCategories($select_rooms = TRUE) {
		if (!$select_rooms)
			$this->db->query("SELECT * FROM resources_categories WHERE is_room = 0");
		else
			$this->db->query("SELECT * FROM resources_categories");
	}

	//private
	function selectProperties() {
		$this->db->query ("SELECT resources_properties.name, resources_properties.description, resources_properties.type, resources_properties.options, resources_properties.system, resources_properties.property_id  FROM resources_properties LEFT JOIN resources_categories_properties USING (property_id) LEFT JOIN resources_objects USING (category_id) WHERE resources_objects.resource_id = '".$this->resObject->getId()."' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	//private
	function selectFacultys($only_fak = TRUE) {
		$this->db->query ("SELECT Name, Institut_id, fakultaets_id  FROM Institute WHERE fakultaets_id = Institut_id ORDER BY name");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}
	
	//private
	function selectInstitutes($fak_id) {
		$this->db2->query ( "SELECT Name, Institut_id FROM Institute WHERE fakultaets_id = '$fak_id' AND  fakultaets_id != Institut_id ORDER BY name");
		if (!$this->db2->affected_rows())
			return FALSE;
		else
			return TRUE;
	}
	

	//private
	function selectPerms() {
		$this->db->query ("SELECT *  FROM resources_user_resources WHERE resource_id = '".$this->resObject->getId()."' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	function showScheduleForms($assign_id='') {
		global $PHP_SELF, $perm, $resources_data, $new_assign_object, $search_user, $search_string_search_user,
			$CANONICAl_RELATIVE_PATH_STUDIP, $RELATIVE_PATH_RESOURCES, $cssSw, $view_mode, $add_ts;

		$killButton = TRUE;
		
		if ($new_assign_object)
			$resAssign = unserialize($new_assign_object);
		else
			$resAssign=new AssignObject($assign_id);
		
		if (($add_ts) && ($resAssign->isNew())) {
			$resAssign->setBegin($add_ts);
			$resAssign->setEnd($add_ts + (60 * 60));
		}
		
		//it is not allowed to edit or kill assigns for rooms here
		if (($resAssign->getOwnerType() == "sem") || ($resAssign->getOwnerType() == "date")) {
			$resObject=new ResourceObject ($resAssign->getResourceId());
			if ($resObject->isRoom()) {
				$lockedAssign=TRUE;
				$killButton = FALSE;
			}
		}

		//load the object perms
		$ObjectPerms = new ResourceObjectPerms($resAssign->getResourceId());
		
		//in some case, we load the perms from the assign object, if it has an owner
		if (($ObjectPerms->getUserPerm() != "admin") && (!$resAssign->isNew()) && (!$new_assign_object)) {
			//load the assign-object perms of a saved object
			$SavedStateAssignObject = new AssignObject($resAssign->getId());
			if ($SavedStateAssignObject->getAssignUserId())
				$ObjectPerms = new AssignObjectPerms($resAssign->getId());
		}

		if ((!$ObjectPerms->havePerm("tutor")) && (!$resAssign->isNew()) && (!$new_assign_object)) {
			$killButton = FALSE;
			$lockedAssign = TRUE;
		}

		if ($resAssign->isNew())
			$killButton = FALSE;
		
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_schedules=<? printf ("%s", ($resAssign->getId()) ?  $resAssign->getId() : "NEW"); ?>">
			<input type="HIDDEN" name="quick_view" value="<?=$this->used_view ?>" />
			<input type="HIDDEN" name="quick_view_mode" value="<?=$view_mode ?>" />
			<input type="HIDDEN" name="change_schedule_resource_id" value="<? printf ("%s", ($assign_id) ? $resAssign->getResourceId() : $resources_data["actual_object"]); ?>" />			
			<input type="HIDDEN" name="change_schedule_repeat_month_of_year" value="<? echo $resAssign->getRepeatMonthOfYear() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_day_of_month" value="<? echo $resAssign->getRepeatDayOfMonth() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_week_of_month" value="<? echo $resAssign->getRepeatWeekOfMonth() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_day_of_week" value="<? echo $resAssign->getRepeatDayOfWeek() ?>" />
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 align="center"> 				
				<?
				if (!$lockedAssign) {
				?>
					<br />&nbsp;
					<input type="IMAGE" align="absmiddle"  <?=makeButton("uebernehmen", "src") ?> border=0 name="submit" value="&Uuml;bernehmen">
					&nbsp;<a href="<?=$PHP_SELF."?quick_view=".$this->used_view."&quick_view_mode=".$view_mode ?>"><?=makeButton("abbrechen", "img") ?></a>
				<?
				}
				if ($killButton) {
					?>&nbsp;<input type="IMAGE" align="absmiddle" <?=makeButton("loeschen", "src") ?> border=0 name="kill_assign" value="<?=_("l&ouml;schen")?>"><?
				}
				if  (!$resAssign->getId()) 
					print "<br /><img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1>"._("Sie erstellen eine neue Belegung")."</font>";
				elseif (!$lockedAssign)
					print "<br />&nbsp;";
				if ($lockedAssign) {
					if ($resAssign->getOwnerType() == "sem") {
						$query = sprintf("SELECT Name, Seminar_id FROM seminare WHERE Seminar_id='%s' ",$resAssign->getAssignUserId());
						$this->db->query($query);
						$this->db->next_record();
					} elseif ($resAssign->getOwnerType() == "date") {
						$query = sprintf("SELECT Name, Seminar_id FROM termine LEFT JOIN seminare ON (termine.range_id = seminare.Seminar_id) WHERE termin_id='%s' ",$resAssign->getAssignUserId());									
						$this->db->query($query);
						$this->db->next_record();
					}
					if ($resAssign->getOwnerType() == "sem") {
						print "<img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1>";
						printf (_("Diese Belegung ist ein regelm&auml;&szlig;iger Termin der Veranstaltung %s, die in diesem Raum stattfindet."), 
							($perm->have_studip_perm("user", $this->db->f("Seminar_id"))) ? 
								"<a href=\"seminar_main.php?auswahl=".$this->db->f("Seminar_id")."\">".htmlReady($this->db->f("Name"))."</a>" : 
								"<a href=\"details.php?&sem_id=".$this->db->f("Seminar_id")."\">".htmlReady($this->db->f("Name"))."</a>");
						if ($perm->have_studip_perm("tutor", $this->db->f("Seminar_id")))
							printf ("<br />"._("Um die Belegung zu ver&auml;ndern, &auml;ndern Sie die %sZeiten%s der Veranstaltung"), "<img src=\"pictures/link_intern.gif\" border=\"0\"/>&nbsp;<a href=\"admin_metadates.php?seminar_id=".$this->db->f("Seminar_id")."\">", "</a>");
						print "</font>";
					} elseif ($resAssign->getOwnerType() == "date") {
						print "<img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1>";
						printf (_("Diese Belegung ist ein Einzeltermin der Veranstaltung %s, die in diesem Raum stattfindet."), 
							($perm->have_studip_perm("user", $this->db->f("Seminar_id"))) ? 
								"<a href=\"seminar_main.php?auswahl=".$this->db->f("Seminar_id")."\">".htmlReady($this->db->f("Name"))."</a>" : 
								"<a href=\"details.php?&sem_id=".$this->db->f("Seminar_id")."\">".htmlReady($this->db->f("Name"))."</a>");
						if ($perm->have_studip_perm("tutor", $this->db->f("Seminar_id")))
							printf ("<br />"._("Um die Belegung zu ver&auml;ndern, &auml;ndern Sie bitte den Termin im %sAblaufplan%s der Veranstaltung"), "<img src=\"pictures/link_intern.gif\" border=\"0\"/>&nbsp;<a href=\"admin_dates.php?range_id=".$this->db->f("Seminar_id")."\">", "</a>");
						print "</font>";
					} elseif (!$lockedAssign) {
						print "<br /><img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1>";
						printf (_("Sie haben nicht die Berechtigung, diese Belegung zu bearbeiten."));
					}
				}
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top"><font size=-1><?=_("Datum/erster Termin:")?></font><br />
				<font size=-1>
				<?
				if ($lockedAssign) {
					echo "<b>".date("d.m.Y",$resAssign->getBegin())."</b>";
				} else {
				?>
					<input name="change_schedule_day" value="<? echo date("d",$resAssign->getBegin()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_month" value="<? echo date("m",$resAssign->getBegin()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_year" value="<? echo date("Y",$resAssign->getBegin()); ?>" size=4 maxlength="4" />
				<?
				}
				?>
				</font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="40%"><font size=-1><?=_("Art der Wiederholung:")?></font><br />
				<font size=-1>
				<?
				if ($lockedAssign) {
					if ($resAssign->getRepeatMode()=="w")
						if ($resAssign->getRepeatInterval() == 2)
							echo "<b>"._("zweiw&ouml;chentlich")."</b>";
						else
							echo "<b>"._("w&ouml;chentlich")."</b>";
					else
						if ($resAssign->getOwnerType() == "date") {
							if (isMetadateCorrespondingDate($resAssign->getAssignUserId()))
								echo "<b>"._("Einzeltermin zu regelm&auml;&szlig;igen Veranstaltungszeiten")."</b>";
						} else
							echo "<b>"._("keine Wiederholung (Einzeltermin)")."</b>";
				} else {
				?>				
					<input type="IMAGE" name="change_schedule_repeat_none" <?=makeButton("keine".(($resAssign->getRepeatMode()=="na") ? "2" :""), "src") ?> border=0 />&nbsp;&nbsp;
					&nbsp;<input type="IMAGE" name="change_schedule_repeat_day" <?=makeButton("taeglich".(($resAssign->getRepeatMode()=="d") ? "2" :""), "src") ?> border=0 />
					&nbsp;<input type="IMAGE" name="change_schedule_repeat_week" <?=makeButton("woechentlich".(($resAssign->getRepeatMode()=="w") ? "2" :""), "src") ?> border=0 /><br />
					<input type="IMAGE" name="change_schedule_repeat_severaldays" <?=makeButton("mehrtaegig".(($resAssign->getRepeatMode()=="sd") ? "2" :""), "src") ?> border=0 />&nbsp;&nbsp;
					&nbsp;<input type="IMAGE" name="change_schedule_repeat_month" <?=makeButton("monatlich".(($resAssign->getRepeatMode()=="m") ? "2" :""), "src") ?> border=0 />
					&nbsp;<input type="IMAGE" name="change_schedule_repeat_year" <?=makeButton("jaehrlich".(($resAssign->getRepeatMode()=="y") ? "2" :""), "src") ?> border=0 />
				<?
				}
				?>
				</font>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top"><font size=-1><?=_("Beginn/Ende:")?></font><br />
				<font size=-1>
				<?
				if ($lockedAssign) {
					echo "<b>".date("G:i",$resAssign->getBegin())." - ".date("G:i",$resAssign->getEnd())." </b>";
				} else {
				?>
					<input name="change_schedule_start_hour" value="<? echo date("G",$resAssign->getBegin()); ?>" size=2 maxlength="2" />
					:<input name="change_schedule_start_minute" value="<? echo date("i",$resAssign->getBegin()); ?>" size=2 maxlength="2" /><?=_("Uhr")?>
					&nbsp; &nbsp; <input name="change_schedule_end_hour"  value="<? echo date("G",$resAssign->getEnd()); ?>" size=2 maxlength="2" />
					:<input name="change_schedule_end_minute" value="<? echo date("i",$resAssign->getEnd()); ?>" size=2 maxlength="2" /><?=_("Uhr")?>
				<?
				}
				?>
				</font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="40%" valign="top">
				<? if ($resAssign->getRepeatMode() != "na") { ?>
				<font size=-1><?if ($resAssign->getRepeatMode() != "sd") print _("Wiederholung bis sp&auml;testens:"); else print _("Letzter Termin:"); ?></font><br />				
				<font size=-1>
				<?
				if ($lockedAssign) {
					echo "<b>".date("d.m.Y",$resAssign->getRepeatEnd())."</b>";
				} else {
				?>
					<input name="change_schedule_repeat_end_day" value="<? echo date("d",$resAssign->getRepeatEnd()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_repeat_end_month" value="<? echo date("m",$resAssign->getRepeatEnd()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_repeat_end_year" value="<? echo date("Y",$resAssign->getRepeatEnd()); ?>" size=4 maxlength="4" />
					<? if (($resAssign->getRepeatMode() != "y") && ($resAssign->getRepeatMode() != "sd")) { ?>
						<input type="CHECKBOX" <? printf ("%s", ($resAssign->isRepeatEndSemEnd()) ? "checked" : "") ?> name="change_schedule_repeat_sem_end" /> <?=_("Ende der Vorlesungszeit")?>
					<? }
				}
				?>
				</font>
				<? 
				} else { 
				?> &nbsp;  
				<? } ?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top"><font size=-1><?=_("eingetragen f&uuml;r die Belegung:")?></font><br />
				<font size=-1>
					<? 
					$user_name=$resAssign->getUsername(FALSE);
					if ($user_name)
						echo "<b>$user_name&nbsp;</b></font>";
					else
						echo "<b>-- "._("keinE Stud.IP NutzerIn eingetragen")." -- &nbsp;</b></font>";
					if (!$lockedAssign) {
						?><br /><br /><font size=-1><? 
						 if ($user_name) 
						 	print _("einen anderen User (NutzerIn oder Einrichtung) eintragen:");
						 else
							print _("einen Nutzer (Person oder Einrichtung) eintragen:");						 
						?><br /></font><font size=-1>
						<? showSearchForm("search_user", $search_string_search_user, FALSE, TRUE, FALSE, FALSE, FALSE, "up") ?> <br/>
						<?=_("freie Eingabe zur Belegung:")?><br /></font>
						<input name="change_schedule_user_free_name" value="<?= htmlReady($resAssign->getUserFreeName()); ?>" size=40 maxlength="255" />
						<br /><font size=-1><?=_("<b>Beachten Sie:</b> Wenn Sie einen NutzerIn oder eine Einrichtung eintragen, kann diese NutzerIn oder berechtigte Personen die Belegung selbstst&auml;ndig aufheben. Sie k&ouml;nnen die Belegung aber auch frei eingeben.")?></font>
						<input type ="HIDDEN" name="change_schedule_assign_user_id" value="<? echo $resAssign->getAssignUserId(); ?>" />
						<input type ="HIDDEN" name="change_schedule_repeat_mode" value="<? echo $resAssign->getRepeatMode(); ?>" />
					<?
					}
					?>
				</font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top">
				<? if (($resAssign->getRepeatMode() != "na") && ($resAssign->getRepeatMode() != "sd") && ($resAssign->getOwnerType() != "sem") && ($resAssign->getOwnerType() != "date")) {?>
				<font size=-1><?=_("Wiederholungsturnus:")?></font><br />				
				<font size=-1>
					<?
					if (!$lockedAssign) {
					?>				
					<select name="change_schedule_repeat_interval"> value="<? echo $resAssign->getRepeatInterval(); ?>" size=2 maxlength="2" />
					<?
					}
					switch ($resAssign->getRepeatMode()) {
						case "d": 
							$str[1]= _("jeden Tag");
							$str[2]= _("jeden zweiten Tag");
							$str[3]= _("jeden dritten Tag");
							$str[4]= _("jeden vierten Tag");
							$str[5]= _("jeden f&uuml;nften Tag");
							$str[6]= _("jeden sechsten Tag");
							$max=6;
						break;
						case "w": 
							$str[1]= _("jede Woche");
							$str[2]= _("jede zweite Woche");
							$str[3]= _("jede dritte Woche");
							$max=3;
						break;
						case "m": 
							$str[1]= _("jeden Monat");
							$str[2]= _("jeden zweiten Monat");
							$str[3]= _("jeden dritten Monat");
							$str[4]= _("jeden vierten Monat");
							$str[5]= _("jeden f&uuml;nften Monat");
							$str[6]= _("jeden sechsten Monat");
							$str[7]= _("jeden siebten Monat");
							$str[8]= _("jeden achten Monat");
							$str[9]= _("jeden neunten Monat");
							$str[10]= _("jeden zehnten Monat");
							$str[11]= _("jeden elften Monat");
							$max=11;
						break;
						case "y": 
							$str[1]= _("jedes Jahr");
							$str[2]= _("jedes zweite Jahr");
							$str[3]= _("jedes dritte Jahr");
							$str[4]= _("jedes vierte Jahr");
							$str[5]= _("jedes f&uuml;nfte Jahr");
							$max=5;
						break;
					}
					if (!$lockedAssign) {
						for ($i=1; $i<=$max; $i++) {
							if ($resAssign->getRepeatInterval() == $i)
								printf ("<option value=\"%s\" selected>%s</option>", $i, $str[$i]);
							else
								printf ("<option value=\"%s\">%s</option>", $i, $str[$i]);
						}
						print "</select>";
					} else
						print "<b>".$str[$resAssign->getRepeatInterval()]."</b>";
					?>
					<br />
					</font>
					<font size=-1><?=_("begrenzte Anzahl der Wiederholungen:")?></font><br />
					<font size=-1>
					<?
					if (!$lockedAssign) {
						printf (_("max. %s Mal wiederholen"), "&nbsp;<input name=\"change_schedule_repeat_quantity\" value=\"".(($resAssign->getRepeatQuantity() != -1) ? $resAssign->getRepeatQuantity() : "")."\" size=\"2\" maxlength=\"2\" />&nbsp;");
						if ($resAssign->getRepeatQuantity() == -1) 
							{ ?> <input type="HIDDEN" name="change_schedule_repeat_quantity_infinity" value="TRUE" /> <? }
					} elseif ($resAssign->getRepeatQuantity() != -1) 
						printf ("<b>"._("max. %s Mal wiederholen")." </b>",$resAssign->getRepeatQuantity());
					else
						print ("<b>"._("unbegrenzt")."</b>");
					?>
					</font>
				</font>
				<? 
				} else { 
				?> &nbsp;  
				<? } ?>
				</td>
			</tr>
			<?
			if (!$lockedAssign) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 align="center"><br />&nbsp; 
					<input type="IMAGE" align="absmiddle" <?=makeButton("uebernehmen", "src") ?> border=0 name="submit" value="<?=_("&Uuml;bernehmen")?>">
					&nbsp;<a href="<?=$PHP_SELF."?quick_view=".$this->used_view."&quick_view_mode=".$view_mode?>"><?=makeButton("abbrechen", "img") ?></a>
				<?
				if ($killButton) {
					?>&nbsp;<input type="IMAGE" align="absmiddle" <?=makeButton("loeschen", "src") ?> border=0 name="kill_assign" value="<?=_("l&ouml;schen")?>"><?
				}
				?>
				<br />&nbsp; 
				</td>
			</tr>
			<?
			}
			?>
			</form>
		</table>
		<?
	}	


	function showPropertiesForms() {
		global $PHP_SELF, $cssSw;
		
		$ObjectPerms = new ResourceObjectPerms($this->resObject->getId());
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_properties=<? echo $this->resObject->getId() ?>">
			<input type="HIDDEN" name="view" value="edit_object_properties" />
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>"><font size=-1><?=_("Name:")?></font><br />
				<font size=-1><input name="change_name" value="<? echo htmlReady($this->resObject->getName()) ?>" size=60 maxlength="255" />
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="40%"><font size=-1><?=_("Typ des Objektes:")?></font><br />
				<font size=-1>
					<?
					if (!checkAssigns($this->resObject->getId())) {
						?>
						<select name="change_category_id">
						<?
						$this->selectCategories(allowCreateRooms());
						if (!$this->resObject->getCategoryId())
							echo "<option select value=\"\">"._("nicht zugeordnet")."</option>";
						while ($this->db->next_record()) {
							if ($this->db->f("category_id")==$this->resObject->getCategoryId()) {
								echo "<option selected value=\"".$this->db->f("category_id")."\">".htmlReady($this->db->f("name"))."</option>";
							} else
								echo "<option value=\"".$this->db->f("category_id")."\">".htmlReady($this->db->f("name"))."</option>";
						}
						?>
						</select><img src="./pictures/pfeiltransparent.gif" border=0><input type="IMAGE" name="assign" <?=makeButton("zuweisen", "src")?> value="<?=_("Zuweisen")?>" border=0>
					<?
					} else {
						print "<b>".htmlReady($this->resObject->getCategoryName())."</b>";
						printf ("<input type=\"HIDDEN\" name=\"change_category_id\" value=\"%s\" />", $this->resObject->getCategoryId());
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>"><font size=-1><?=_("Beschreibung:")?></font><br />
			
				<font size=-1><textarea name="change_description" rows=3 cols=60><? echo htmlReady($this->resObject->getDescription()) ?></textarea>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="40%" valign="top"><font size=-1><?=_("verantwortlich:")?></font><br />
				<font size=-1><a href="<? echo $this->resObject->getOwnerLink()?>"><? echo $this->resObject->getOwnerName(TRUE) ?></a></font>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2><font size=-1><b><?=_("Eigenschaften")?></b></font><br />
				</td>
			</tr>
			<?
			if (($this->resObject->isRoom()) && (get_config("RESOURCES_ENABLE_ORGA_CLASSIFY"))) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" ><font size=-1><?=_("organisatorische Einordnung:")?></font><br />
				<font size=-1><?echo ($this->resObject->getInstitutId()) ? "<a href=\"".$this->resObject->getOrgaLink()."\">" : ""; echo ($this->resObject->getInstitutId()) ? $this->resObject->getOrgaName(TRUE)."</a>" : _("keine Zuordnung") ?></font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="40%">
				<?
				if ($ObjectPerms->havePerm("admin")){
					?>
					<font size=-1 color="red"></font><br />
					<select name="change_institut_id">
						<option value="0">&lt;<?=_("keine Zuordnung")?>&gt;</option>
						<?
						$this->selectFacultys();
						while ($this->db->next_record()) {
							printf ("<option style=\"font-weight:bold;\" value=\"%s\" %s>%s</option>", $this->db->f("Institut_id"), ($this->db->f("Institut_id") == $this->resObject->getInstitutId()) ? "selected" : "", my_substr($this->db->f("Name"),0,50));
							$this->selectInstitutes($this->db->f("fakultaets_id"));
								print_r ($this->db2);
								while ($this->db2->next_record()) {
									printf ("<option value=\"%s\" %s>&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $this->db2->f("Institut_id"), ($this->db2->f("Institut_id") == $this->resObject->getInstitutId()) ? "selected" : "", my_substr($this->db2->f("Name"),0,50));
								}
						}
						?>
					</select>
				<?
				} else
					print "<img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1><font size=\"-1\"> "._("Sie k&ouml;nnen die Einordnung in die Orga-Struktur nicht &auml;ndern.")."</font>";
				?>
				</td>
			</tr>			
			<?
			}
			/*
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>">&nbsp; 
				<td class="<? echo $cssSw->getClass() ?>" width="40%" valign="top"><font size=-1><?=_("Vererbte Belegung:")?></font><br />
				<font size=-1><input type="CHECKBOX" name="change_parent_bind" <? if ($this->resObject->getParentBind()) echo "checked" ?> >
				<?=_("Objekt &uuml;bernimmt Belegung von &uuml;bergeordnetem Objekt")?></font>
				</td>
			</tr>
			*/
			?>
			<? if ($this->resObject->getCategoryId()) {?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 align="center">
				</td>
			</tr>
				<?
				$this->selectProperties();
				while ($this->db->next_record()) {
					?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>">
					<font size=-1><? echo htmlReady($this->db->f("name")); ?></font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="40%">
				<font size=-1>
				<?
					$this->db2->query("SELECT * FROM resources_objects_properties WHERE resource_id = '".$this->resObject->getId()."' AND property_id = '".$this->db->f("property_id")."' ");
					$this->db2->next_record();
					printf ("<input type=\"HIDDEN\" name=\"change_property_val[]\" value=\"%s\" />", "_id_".$this->db->f("property_id"));
					switch ($this->db->f("type")) {
						case "bool":
							printf ("<input type=\"CHECKBOX\" name=\"change_property_val[]\" %s /><font size=-1>&nbsp;%s</font>", ($this->db2->f("state")) ? "checked":"", htmlReady($this->db->f("options")));
						break;
						case "num":
							if ($this->db->f("system") == 2)
								printf ("<input type=\"TEXT\" name=\"change_property_val[]\" value=\"%s\" size=5 maxlength=10 />", htmlReady($this->db2->f("state")));
							else
								printf ("<input type=\"TEXT\" name=\"change_property_val[]\" value=\"%s\" size=30 maxlength=255 />", htmlReady($this->db2->f("state")));
						break;
						case "text";
							printf ("<textarea name=\"change_property_val[]\" cols=30 rows=2 >%s</textarea>", htmlReady($this->db2->f("state")));
						break;
						case "select";
							$options=explode (";",$this->db->f("options"));
							printf ("<select name=\"change_property_val[]\">");
							foreach ($options as $a) {
								printf ("<option %s value=\"%s\">%s</option>", ($this->db2->f("state") == $a) ? "selected":"", $a, htmlReady($a));
							}
							printf ("</select>");
						break;
					}
				?></td>
			</tr><?
				}
			} else { ?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2>
				<font size=-1 color="red"><?=_("Das Objekt wurde noch keinem Typ zugewiesen. Um Eigenschaften bearbeiten zu k&ouml;nnen, m&uuml;ssen Sie vorher einen Typ festlegen!")?></font>
				</td>
			</tr>
			<? } 
			if ((getGlobalPerms($user->id) == "admin") && ($this->resObject->getCategoryId())) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				<td class="<? echo $cssSw->getClass() ?>" >
					<font size=-1><b><?=_("gleichzeitige Belegung")?></b><br><br />
					<?=_("Die Ressource darf mehrfach zur gleichen Zeit belegt werden - <br />&Uuml;berschneidungschecks finden <u>nicht</u> statt!")?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="40%">
					<input type="CHECKBOX" name="change_multiple_assign" <?=($this->resObject->getMultipleAssign()) ? "checked" : "" ?> /> <br />
				</td>
				</tr>
			<?
			}
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 align="center"><br />&nbsp; <input type="IMAGE" align="absmiddle" <?=makeButton("uebernehmen", "src")?> border=0 name="submit" value="<?=_("Zuweisen")?>">
			 	<?
				if ($this->resObject->isUnchanged())
					print "&nbsp;<a href=\"$PHP_SELF?cancel_edit=".$this->resObject->id."\">".makeButton("abbrechen", "img")."</a>";
			 	?>
				<br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
		<br /><br />
		<?
	}	

	function showPermsForms() {
		global $PHP_SELF, $search_owner, $search_perm_user, $search_string_search_perm_user, $search_string_search_owner,
			$cssSw;
		
		$ObjectPerms = new ResourceObjectPerms($this->resObject->getId());
		
		$owner_perms = checkObjektAdministrablePerms ($this->resObject->getOwnerId());

		if ($owner_perms)
			$admin_perms = TRUE;
		else
			$admin_perms = ($ObjectPerms->havePerm("admin")) ? TRUE : FALSE;
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_perms=<? echo $this->resObject->getId() ?>">
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2><font size=-1><?=_("verantwortlich:")?></font><br />
				<font size=-1><a href="<? echo $this->resObject->getOwnerLink()?>"><? echo $this->resObject->getOwnerName(TRUE) ?></a></font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="50%">
				<?
				if ($owner_perms){
					?>
					<font size=-1><?=_("verantworlicheN NutzerIn &auml;ndern:") ?></font><font size=-1 color="red"></font><br />
					<? showSearchForm("search_owner", $search_string_search_owner, FALSE,TRUE);
				} else
					print "<img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1><font size=\"-1\"> "._("Sie k&ouml;nnen den/die verantwortlicheN NutzerIn nicht &auml;ndern.")."</font>";
				?>
				</td>
			</tr>
			<tr>

				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 valign="top"><font size=-1><?=_("Berechtigungen:")?></font><br />
				<td class="<? echo $cssSw->getClass() ?>" width="50%" valign="top"><font size=-1<?=_("Berechtigung hinzuf&uuml;gen")?></font><br />
				<? showSearchForm("search_perm_user", $search_string_search_perm_user, FALSE, FALSE, FALSE, TRUE) ?>
				</td>
			</tr>
			<?
			$i=0;
			if ($this->selectPerms())
				while ($this->db->next_record()) {
				?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<input type="HIDDEN" name="change_user_id[]" value="<? echo $this->db->f("user_id")?>" />
					<font size=-1><a href="<? echo $this->resObject->getOwnerLink($this->db->f("user_id"))?>"><? echo htmlReady($this->resObject->getOwnerName(TRUE, $this->db->f("user_id"))) ?></a></font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="*" >
					<font size=-1>&nbsp; 
					<?
					//admin-perms
					if (($this->resObject->getOwnerType($this->db->f("user_id")) == "user") && ($owner_perms))
						printf ("<input type=\"RADIO\" name=\"change_user_perms[%s]\" value=\"admin\" %s />admin", $i, ($this->db->f("perms") == "admin") ? "checked" : "");
					else
						printf ("<input type=\"RADIO\" disabled name=\"FALSE\" %s /><font color=\"#888888\">admin</font>", ($this->db->f("perms") == "admin") ? "checked" : "");

					//tutor-perms
					if (($this->resObject->getOwnerType($this->db->f("user_id")) == "user") && ($admin_perms) && ((($this->db->f("perms") == "tutor") || ($owner_perms))))
						printf ("<input type=\"RADIO\" name=\"change_user_perms[%s]\" value=\"tutor\" %s />tutor", $i, ($this->db->f("perms") == "tutor") ? "checked" : "");
					else
						printf ("<input type=\"RADIO\" disabled name=\"FALSE\" %s /><font color=\"#888888\">tutor</font>", ($this->db->f("perms") == "tutor") ? "checked" : "");

					//autor-perms
					if (($admin_perms) && ((($this->db->f("perms") == "autor") || ($owner_perms))))
						printf ("<input type=\"RADIO\" name=\"change_user_perms[%s]\" value=\"autor\" %s />autor", $i, ($this->db->f("perms") == "autor") ? "checked" : "");
					else
						printf ("<input type=\"RADIO\" disabled name=\"FALSE\" %s /><font color=\"#888888\">autor</font>", ($this->db->f("perms") == "autor") ? "checked" : "");
					
					//trash
					if (($owner_perms) || (($admin_perms) && ($this->db->f("perms") == "autor")))
						printf ("&nbsp; <a href=\"%s?change_object_perms=%s&delete_user_perms=%s\"><img src=\"pictures/trash.gif\" ".tooltip(_("Berechtigung l�schen"))." border=0></a>", $PHP_SELF, $this->resObject->getId(), $this->db->f("user_id"));
					else
						print "&nbsp; <img src=\"pictures/lighttrash.gif\" ".tooltip(_("Sie d&uuml;rfen diese Berechtigung leider nicht l�schen"))." border=0>";
						
					?>
					</font>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="50%">
					<font size=-1>
					<?

					switch ($this->db->f("perms")) {
						case "admin":
							print _("Nutzer ist <b>Admin</b> und kann s&auml;mtliche Belegungen und Eigenschaften &auml;ndern und Rechte vergeben.");
						break;
						case "tutor":
							print _("Nutzer ist <b>Tutor</b> und kann s&auml;mtliche Belegungen &auml;ndern.");
						break;
						case "autor":
							print _("Nutzer ist <b>Autor</b> und kann nur eigene Belegungen &auml;ndern.");
						break;
					}					
					?>
					</font>
				</td>
			</tr>
				<?
				$i++;  
				}
			else {
				?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=3>
					<font size=-1><img src="pictures/ausruf_small2.gif" align="absmiddle" />&nbsp;<font size=-1><font size="-1"><?=_("Es sind keine weiteren Berechtigungen eingetragen")?></font>
				</td>
			</tr>
			<? } 
			if ((getGlobalPerms($user->id) == "admin") && ($this->resObject->isRoom())) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				<td class="<? echo $cssSw->getClass() ?>" colspan="3">
					<font size=-1><?=_("Blockierung:")?><br>
					<?=_("Diesen Raum bei globaler Blockierung gegen eine Bearbeitung durch lokale Administratoren und andere Personen sperren:")?>
					<input type="CHECKBOX" name="change_lockable" <?=($this->resObject->isLockable()) ? "checked" : "" ?> /> <br />
					<?print _("<b>aktueller Zustand</b>:")." "; print ($this->resObject->isLockable()) ? _("Raum <u>kann</u> blockiert werden") : _("Raum kann <u>nicht</u> blockiert werden") ?>
				</td>
				</tr>
			<?
			}
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=3 align="center"><br />&nbsp; <input type="IMAGE" <?=makeButton("uebernehmen", "src")?> border=0 name="submit" value="<?=_("Zuweisen")?>"><br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
		<?
	}	
}