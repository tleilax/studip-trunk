<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
* ShowSchedules.class.php
*
* view schedule/assigns for a ressource-object
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access		public
* @modulegroup		resources
* @module		ShowSchedules.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowSchedule.class.php
// stellt Assign/graphische Uebersicht der Belegungen dar
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

require_once ($GLOBALS['RELATIVE_PATH_RESOURCES'].'/lib/AssignEventList.class.php');
require_once ($GLOBALS['RELATIVE_PATH_RESOURCES'].'/views/ScheduleWeek.class.php');
require_once ('lib/classes/cssClassSwitcher.inc.php');

$cssSw = new cssClassSwitcher;

/*****************************************************************************
ShowSchedules - schedule view
/*****************************************************************************/

class ShowSchedules {
	var $ressource_id;		//viewed ressource object
	var $user_id;			//viewed user
	var $range_id;			//viewed range
	var $start_time;		//time to start
	var $end_time;			//time to end
	var $length_factor;		//the used length factor for calculations, only used for viewing
	var $length_unit;		//the used length unit for calculations, only used for viewing
	var $week_offset;		//offset for the week view
	var $used_view;			//the used view, submitted to the sub classes


	//Konstruktor
	function ShowSchedules ($resource_id='', $user_id='', $range_id='') {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
		$this->resource_id=$resource_id;
		$this->user_id=$user_id;
		$this->range_id=$range_id;
	}

	function setLengthFactor ($value) {
		$this->length_factor = $value;
	}

	function setLengthUnit ($value) {
		$this->length_unit = $value;
	}

	function setStartTime ($value) {
		$this->start_time = $value;
	}

	function setEndTime ($value) {
		$this->end_time = $value;
	}

	function setWeekOffset ($value) {
		$this->week_offset = $value;
	}

	function setUsedView($value) {
		$this->used_view = $value;
	}

	function navigator () {
		global $cssSw, $view_mode, $PHP_SELF;

	 	//match start_time & end_time for a whole week
	 	$dow = date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) >1)
	 		$offset = 1 - date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) <1)
		 	$offset = -6;

 		$start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
 		$end_time = mktime (23, 59, 0, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));

		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?navigate=TRUE&quick_view=view_schedule&quick_view_mode=<?=$view_mode?>">
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan="2"><font size=-1><b><?=_("Zeitraum:")?></b></font>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%" rowspan="2">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="30%" rowspan="2" valign="top"><font size=-1>
					<font size=-1>Beginn:&nbsp;
					<input type="text" name="schedule_begin_day" size=2 maxlength=2 value="<? if (!$start_time) echo date("d",time()); else echo date("d",$start_time); ?>">.
					<input type="text" name="schedule_begin_month" size=2 maxlength=2 value="<? if (!$start_time) echo date("m",time()); else echo date("m",$start_time); ?>">.
					<input type="text" name="schedule_begin_year" size=4 maxlength=4 value="<? if (!$start_time) echo date("Y",time()); else echo date("Y",$start_time); ?>"><br />
					&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; <input type="IMAGE" name="jump" border="0"<? echo makeButton("auswaehlen", "src") ?> /><br />
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="66%" valign="top"><font size=-1>
					<input type="text" name="schedule_length_factor" size=2 maxlength=2 / value="<? if (!$this->length_factor) echo "1"; else echo $this->length_factor; ?>">
					&nbsp; <select name="schedule_length_unit">
						<option <? if ($this->length_unit  == "d") echo "selected" ?> value="d"><?=_("Tag(e)")?></option>
						<option <? if ($this->length_unit  == "w") echo "selected" ?> value="w"><?=_("Woche(n)")?></option>
						<option <? if ($this->length_unit  == "m") echo "selected" ?> value="m"><?=_("Monat(e)")?></option>
						<option <? if ($this->length_unit  == "y") echo "selected" ?> value="y"><?=_("Jahre(e)")?></option>
					</select>
					&nbsp;<?=_("als Liste ausgeben")?>
					&nbsp; <input type="IMAGE" name="start_list" <?=makeButton("ausgeben", "src") ?> border=0 vallue="<?=_("ausgeben")?>" /><br />
				</td>
			</tr>
			<tr>
					<td class="<? echo $cssSw->getClass() ?>" width="66%" valign="top"><font size=-1>
					<?=_("<i>oder</i> eine Woche grafisch ausgeben")?>
					&nbsp; <input type="IMAGE" name="start_graphical" <?=makeButton("ausgeben", "src") ?> border=0 vallue="<?=_("ausgeben")?>" /><br />&nbsp;
				</td>
			</tr>
		</table>
	<?
	}

	function showScheduleList($print_view = false) {
		global $PHP_SELF, $cssSw, $view_mode;

		 //select view to jump from the schedule
		 if ($this->used_view == "openobject_schedule")
		 	$view = "openobject_assign";
		 else
			$view = "edit_object_assign";

		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" align="center">
				<b>
				<?
				if ($print_view){
					$room =& ResourceObject::Factory($this->resource_id);
					echo htmlReady($room->getName().' - ' .$this->semester['name']);
				} else {
					if ($this->semester){
						printf(_("Anzeige des Semesters: %s"), htmlReady($this->semester['name']));
					} else {
						echo _("Anzeige des Zeitraums:");
					}
				}
				echo '<br>' . date ("d.m.Y", $this->start_time), " - ", date ("d.m.Y", $this->end_time);
				?>
				</b>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%">
					<?
					$assign_events=new AssignEventList ($this->start_time, $this->end_time, $this->resource_id, '', '', TRUE);
					echo "<br /><font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font>";
					echo "<br /><br />";
					$num = 1;
					while ($event = $assign_events->nextEvent()) {
						$add_info = '';
						if (in_array($event->getOwnerType(), array('sem','date'))){
							$sem_doz_names = array();
							if ($event->getOwnerType() == 'sem'){
								$sem_obj =& Seminar::GetInstance($event->getAssignUserId());
							} else {
								$sem_obj =& Seminar::GetInstance(Seminar::GetSemIdByDateId($event->getAssignUserId()));
							}
							foreach($sem_obj->getMembers('dozent') as $dozent){
								$sem_doz_names[] = $dozent['Nachname'];
								if (++$c > 2) break;
							}
							$add_info = ', (' . join(', ' , $sem_doz_names) . ')';
						}
						if (!$print_view){
							echo "<a href=\"$PHP_SELF?quick_view=".$view."&quick_view_mode=".$quick_view_mode."&edit_assign_object=".$event->getAssignId()."\">".makeButton("eigenschaften")."</a><font size=-1>";
						} else {
						 echo '<font size=-1>' . sprintf("%02d" , $num++) . '.';
						}
						printf ("&nbsp;"
								._("Belegung ist von <b>%s</b> bis <b>%s</b>, belegt von <b>%s</b>")
								."</font><br />", strftime("%A, %d.%m.%Y %H:%M", $event->getBegin())
								, strftime("%A, %d.%m.%Y %H:%M", $event->getEnd())
								, $event->getName(get_config('RESOURCES_SCHEDULE_EXPLAIN_USER_NAME')) . $add_info);
					}
					?>
				</td>
			</tr>
		</table>
		</form>
		<br /><br />
	<?
	}

	function showScheduleGraphical() {
		global $RELATIVE_PATH_RESOURCES, $PHP_SELF, $cssSw, $view_mode, $resources_data, $ActualObjectPerms;

		$categories["na"] = 4;
	 	$categories["sd"] = 4;
	 	$categories["y"] = 3;
	 	$categories["m"] = 3;
	 	$categories["w"] = 0;
	 	$categories["d"] = 2;

	 	//an assign for a date corresponding to a (seminar-)metadate
	 	$categories["meta"] = 1;

	 	//match start_time & end_time for a whole week
	 	$dow = date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) >1)
	 		$offset = 1 - date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) <1)
		 	$offset = -6;

		 //select view to jump from the schedule
		 if ($this->used_view == "openobject_schedule")
		 	$view = "openobject_assign";
		 else
			$view = "edit_object_assign";

 		$start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
 		$end_time = mktime (23, 59, 59, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));

 		if ($resources_data["schedule_time_range"] == -1) {
 			$start_hour = 0;
 			$end_hour = 12;
 		} elseif ($resources_data["schedule_time_range"] == 1) {
 			$start_hour = 12;
 			$end_hour = 23;
 		} else {
 		 	$start_hour = 8;
 			$end_hour = 22;
		}

	 	$schedule=new ScheduleWeek($start_hour, $end_hour, FALSE, $start_time, true);

	 	if ($ActualObjectPerms->havePerm("autor"))
		 	$schedule->add_link = "resources.php?cancel_edit_assign=1&quick_view=$view&quick_view_mode=".$view_mode."&add_ts=";

		//fill the schedule
		$assign_events=new AssignEventList ($start_time, $end_time, $this->resource_id, '', '', TRUE, $resources_data["show_repeat_mode"]);
		while ($event=$assign_events->nextEvent()) {
			$repeat_mode = $event->getRepeatMode(TRUE);
			$add_info = '';
			if (in_array($event->getOwnerType(), array('sem','date'))){
				$sem_doz_names = array();
				if ($event->getOwnerType() == 'sem'){
					$sem_obj =& Seminar::GetInstance($event->getAssignUserId());
				} else {
					$sem_obj =& Seminar::GetInstance(Seminar::GetSemIdByDateId($event->getAssignUserId()));
				}
				foreach($sem_obj->getMembers('dozent') as $dozent){
					$sem_doz_names[] = $dozent['Nachname'];
					if (++$c > 2) break;
				}
				$add_info = '(' . join(', ' , $sem_doz_names) . ')';
			}
			$schedule->addEvent($event->getName(get_config('RESOURCES_SCHEDULE_EXPLAIN_USER_NAME')), $event->getBegin(), $event->getEnd(),
						"$PHP_SELF?cancel_edit_assign=1&quick_view=$view&quick_view_mode=".$view_mode."&edit_assign_object=".$event->getAssignId(), $add_info, $categories[$repeat_mode]);
		}
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="35" border="0"/>
				</td>
				<td class="<? echo $cssSw->getClass() ?>"  width="10%" align="left">&nbsp;
					<a href="<? echo $PHP_SELF ?>?quick_view=<?=$this->used_view?>&quick_view_mode=<?=$view_mode?>&previous_week=TRUE"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/calendar_previous.gif" <? echo tooltip (_("Vorherige Woche anzeigen")) ?>border="0" /></a>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="76%" align="center" style="font-weight:bold">
					<? printf(_("Anzeige der Woche vom %s bis %s (KW %s)"), strftime("%x", $start_time), strftime("%x", $end_time),strftime("%V", $start_time));?>
					<br>
					<?php
					$this->showSemWeekNumber($start_time);
					?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="10%" align="center">&nbsp;
					<a href="<? echo $PHP_SELF ?>?quick_view=<?=$this->used_view?>&quick_view_mode=<?=$view_mode?>&next_week=TRUE"><img  valign="middle"  src="<?= $GLOBALS['ASSETS_URL'] ?>images/calendar_next.gif" <? echo tooltip (_("N�chste Woche anzeigen")) ?>border="0" /></a>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%" align="center" valign="bottom">&nbsp;
					<?
					if ((!$resources_data["schedule_time_range"]) || ($resources_data["schedule_time_range"] == 1))
						printf ("<a href=\"%s?quick_view=%s&quick_view_mode=%s&time_range=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/calendar_up.gif\" %sborder=\"0\" /></a>", $PHP_SELF, $this->used_view, $view_mode, ($resources_data["schedule_time_range"]) ? "FALSE" : -1, tooltip (_("Fr�here Belegungen anzeigen")));
					?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="76%" colspan="2">
					<?
					echo "&nbsp;<font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font><br />&nbsp;";
					?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="20%" nowrap>
					<?
					print "<select style=\"font-size:10px;\" name=\"show_repeat_mode\">";
					printf ("<option style=\"font-size:10px;\" %s value=\"all\">"._("alle Belegungen")."</option>", ($resources_data["show_repeat_mode"] == "all") ? "selected" : "");
					printf ("<option %s style=\"font-size:10px;\" value=\"single\">"._("nur Einzeltermine")."</option>", ($resources_data["show_repeat_mode"] == "single") ? "selected" : "");
					printf ("<option %s style=\"font-size:10px;\" value=\"repeated\">"._("nur Wiederholungstermine")."</option>", ($resources_data["show_repeat_mode"] == "repeated") ? "selected" : "");
					print "</select>";
					print "&nbsp;<input type=\"IMAGE\" name=\"send_schedule_repeat_mode\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" border=\"0\" ".tooltip(_("Ansicht umschalten"))." />";
					?>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan="3">
					<?
					$schedule->showSchedule("html", $print_view);
					?>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%" align="center" valign="bottom">&nbsp;
					<?
					if ((!$resources_data["schedule_time_range"]) || ($resources_data["schedule_time_range"] == -1))
						printf ("<a href=\"%s?quick_view=%s&quick_view_mode=%s&time_range=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/calendar_down.gif\" %sborder=\"0\" /></a>", $PHP_SELF, $this->used_view, $view_mode, ($resources_data["schedule_time_range"]) ? "FALSE" : 1, tooltip (_("Sp�tere Belegungen anzeigen")));
					?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="20%" nowrap colspan="3">
				&nbsp;
				</td>
			</tr>

		</table>
		</form>
	<?
	}
	
	function showSemWeekNumber($start_time){
		$semester = SemesterData::getInstance()->getSemesterDataByDate($start_time);
		echo htmlready($semester['name']) . ' - ';
		if(is_int($semester['sem_week_number'])){
			printf(_("%s. Vorlesungswoche"), $semester['sem_week_number']); 
		} else {
			echo _("vorlesungsfreie Zeit");
		}
	}
}
?>
