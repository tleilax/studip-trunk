<?
/**
* AssignObject.class.php
* 
* class for an assign-object
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		AssignObject.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// AssignObject.class.php
// zentrale Klasse fuer ein Belegungsobjekt
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

require_once($ABSOLUTE_PATH_STUDIP."/lib/classes/SemesterData.class.php");

/*****************************************************************************
AssignObject, zentrale Klasse der Objekte der Belegung
/*****************************************************************************/
class AssignObject {
	var $db;					//Datenbankanbindung;
	var $id;					//Id des Belegungs-Objects
	var $resource_id;			//resource_id des verknuepten Objects;
	var $assign_user_id;		//id des verknuepten Benutzers der Ressource
	var $user_free_name;		//freier Name fuer Belegung
	var $begin;				//Timestamp der Startzeit
	var $end;					//Timestamp der Endzeit
	var $repeat_end;			//Timestamp der Endzeit der Belegung (expire)
	var $repeat_quantity;		//Anzahl der Wiederholungen
	var $repeat_interval;		//Intervall der Wiederholungen
	var $repeat_month_of_year ;	//Wiederholungen an bestimmten Monat des Jahres
	var $repeat_day_of_month;	//Wiederholungen an bestimmten Tag des Monats
	var $repeat_week_of_month;	//Wiederholungen immer in dieser Woche des Monats
	var $repeat_day_of_week;	//Wiederholungen immer an diesem Wochentag

	//Konstruktor
	function AssignObject($id='', $resource_id='', $assign_user_id='', $user_free_name='', $begin='', $end='', 
						$repeat_end='', $repeat_quantity='', $repeat_interval='', $repeat_month_of_year='', $repeat_day_of_month='', 
						$repeat_week_of_month='', $repeat_day_of_week='') {
		global $RELATIVE_PATH_RESOURCES;
		
	 	require_once ($RELATIVE_PATH_RESOURCES."/lib/list_assign.inc.php");
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");

		global $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;

		if(func_num_args() == 1) {
			$this->id = func_get_arg(0);
			if (!$this->restore($this->id))
				$this->isNewObject =TRUE;
		} elseif(func_num_args() == 13) {
			$this->id=func_get_arg(0);
			$this->resource_id = func_get_arg(1);
			$this->assign_user_id = func_get_arg(2);
			$this->user_free_name = func_get_arg(3);
			$this->begin = func_get_arg(4);
			$this->end = func_get_arg(5);
			$this->repeat_end = func_get_arg(6);
			$this->repeat_quantity = func_get_arg(7);
			$this->repeat_interval = func_get_arg(8);
			$this->repeat_month_of_year  = func_get_arg(9);
			$this->repeat_day_of_month = func_get_arg(10);
			$this->repeat_week_of_month = func_get_arg(11);
			$this->repeat_day_of_week = func_get_arg(12);
			if (!$this->id)
				$this->createId();
			$this->isNewObject =TRUE;
		} 	
	}

	function createId() {
		$this->id = md5(uniqid("BartSimpson",1));
	}
	
	function create() {
		$query = sprintf("SELECT assign_id FROM resources_assign WHERE assign_id ='%s' ", $this->id);
		$this->db->query($query);
		if ($this->db->nf()) {
			$this->chng_flag=TRUE;
			return $this->store();
		} else
			return $this->store(TRUE);
	}

	function getId() {
		return $this->id;
	}
	
	function getAssignUserId() {
		return $this->assign_user_id;
	}

	function getOwnerName($explain=FALSE, $id='') {
		global $TERMIN_TYP;

		if (!$id)
			$id=$this->assign_user_id;
			
		switch (get_object_type($id)) {
			case "user";
				if (!$explain)
					return get_fullname($id);
				else
					return get_fullname($id)." ("._("NutzerIn").")";
			break;
			case "inst":
			case "fak":
				$query = sprintf("SELECT Name FROM Institute WHERE Institut_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." ("._("Einrichtung").")";
			break;
			case "sem":
				$query = sprintf("SELECT Name FROM seminare WHERE Seminar_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name"). " ("._("Veranstaltung").")";	
			break;
			case "date":
				$query = sprintf("SELECT Name, content, date_typ FROM termine LEFT JOIN seminare ON (seminar_id = range_id) WHERE termin_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." (".$TERMIN_TYP[$this->db->f("date_typ")]["name"].")";	
			break;
			case "global":
			default:
				return "unbekannt";
			break;
		}
	}
	

	function getUsername($use_free_name=TRUE) {
		if ($this->assign_user_id) 
			return $this->getOwnerName(TRUE, $this->assign_user_id);
		elseif ($use_free_name)
			return $this->getUserFreeName();
		else 
			return FALSE;
	}
	
	function getOwnerType() {
		$type = get_object_type($this->getAssignUserId());
		return $type == "fak" ? "inst" : $type;
	}

	function getResourceId() {
		return $this->resource_id;
	}

	function getUserFreeName() {
		return $this->user_free_name;
	}

	function getBegin() {
		if (!$this->begin)
			return time();
		else
			return $this->begin;
	}

	function getEnd() {
		if (!$this->end)
			return time()+3600;
		else
			return $this->end;
	}

	function getRepeatEnd() {
		if (!$this->repeat_end)
			return $this->end;
		else
			return $this->repeat_end;
	}

	function getRepeatQuantity() {
		return $this->repeat_quantity;
	}

	function getRepeatInterval() {
		return $this->repeat_interval;
	}

	function getRepeatMonthOfYear() {
		return $this->repeat_month_of_year ;
	}

	function getRepeatDayOfMonth() {
		return $this->repeat_day_of_month;
	}

	function getRepeatWeekOfMonth() {
		return $this->repeat_week_of_month;
	}

	function getRepeatDayOfWeek() {
		return $this->repeat_day_of_week;
	}
	
	function getRepeatMode() {
		if ((!$this->repeat_month_of_year) && (!$this->repeat_week_of_month) && (!$this->repeat_day_of_month) && (!$this->repeat_day_of_week) && (!$this->repeat_quantity)) {
			if ((date("j", $this->repeat_end) != date("j", $this->begin)) && ($this->repeat_end))
				return "sd";
			else
				return "na";
		} elseif ($this->repeat_month_of_year)
			return "y";
		elseif ($this->repeat_week_of_moth || $this->repeat_day_of_month)
			return "m";
		elseif ($this->repeat_day_of_week)
			return "w";
		else
			return "d";
	}
	
	function getRepeatEndByQuantity() {
		create_assigns($this, $this, -1, -1);
		
		$max_date = 0;
		foreach ($this->events as $val) {
			if ($val->getEnd() > $max_date)
				$max_date = $val->getEnd();
		}
		return $max_date;
	}
	
	function getEvents() {
		create_assigns($this, $this);
		return $this->events;
	}
	
	function isNew() {
		return $this->isNewObject;
	}
	
	function isRepeatEndSemEnd() {
		$semester = new SemesterData; 
		$all_semester = $semester->getAllSemesterData(); 

		foreach ($all_semester as $a)	
			if (($this->begin >= $a["beginn"]) &&($this->begin <= $a["ende"]))
				if ($this->repeat_end==$a["vorles_ende"])
					return true;
		return false;
	}
	
	function checkOverlap() {
		$resObject = new ResourceObject($this->resource_id);
		if (!$resObject->getMultipleAssign()) { //when multiple assigns are allowed, we need no check...
			//we check overlaps always for a whole day
			$start = mktime (0,0,0, date("n", $this->begin), date("j", $this->begin), date("Y", $this->begin));
			if ($this->repeat_end)
				$end = mktime (23,59,59, date("n", $this->repeat_end), date("j", $this->repeat_end), date("Y", $this->repeat_end));
			else
				$end = mktime (23,59,59, date("n", $this->end), date("j", $this->end), date("Y", $this->end));
				
			//load the existing assigns for the given resource...
			list_restore_assign($this, $this->resource_id, $start, $end);
		
			//...and add the actual assign to perform the checks ...
			create_assigns($this, $this);
			
			//..so we have a "virtual" set of assign-events in the given resource. Now we can check...
			if (is_array($this->events))
				$keys=array_keys($this->events);
			$my_id = $this->getId();
			//ok, a very heavy algorythmus to detect the overlaps...
			$count_this_events = count($this->events);
			for ($i1=0; $i1<$count_this_events; $i1++) {
				$val_id = $this->events[$keys[$i1]]->getId();
				$val_begin = $this->events[$keys[$i1]]->getBegin();
				$val_end = $this->events[$keys[$i1]]->getEnd();
				$val_assign_id = $this->events[$keys[$i1]]->getAssignId();
				for ($i2=0; $i2<$count_this_events; $i2++) {
					$val2_id = $this->events[$keys[$i2]]->getId();
					if ($val2_id != $val_id) {
						$val2_begin = $this->events[$keys[$i2]]->getBegin();
						$val2_end = $this->events[$keys[$i2]]->getEnd();
						$val2_assign_id = $this->events[$keys[$i2]]->getAssignId();
						if ((($val_end > $val2_begin) && ($val_end < $val2_end))
						|| (($val_begin > $val2_begin) && ($val_begin < $val2_end))
						|| (($val2_end > $val_begin) && ($val2_end < $val_end))
						|| (($val2_begin > $val_begin) && ($val2_begin < $val_end))
						|| (($val_begin == $val2_begin) && ($val_end == $val2_end))) {
							if (($val2_assign_id  != $my_id) && ($val_assign_id  == $my_id )) {
								$overlaps[$val2_assign_id] = array("begin" =>$val_begin, "end"=>$val_end);
							}
						}
					}
				}
			}
			return $overlaps;
		} else
			return FALSE;
	}
	
	function getFormattedShortInfo() {
		$info = strftime("%A", $this->begin);
		$info.= ", ".date("d.m.Y", $this->begin);
		if ((date("d", $this->begin) != date("d", $this->repeat_end)) &&
			(date("m", $this->begin) != date("m", $this->repeat_end)) &&
			(date("Y", $this->begin) != date("Y", $this->repeat_end)))
			$info.= " - ". date("d.m.Y", $this->repeat_end);
		$info.=", ".date("H:i", $this->begin)." - ".date("H:i", $this->end);
		if (($this->getRepeatMode() != "na") && ($this->getRepeatMode() != "sd"))
			$info.=", ".$this->getFormattedRepeatMode();
		return $info;
	}
	
	function getFormattedRepeatMode() {
		switch ($this->getRepeatMode()) {
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
		return $str[$this->getRepeatInterval()];	
	}

	function setResourceId($value) {
		$this->resource_id=$value;
		$this->chng_flag=TRUE;
	}

	function setUserFreeName($value) {
		$this->user_free_name=$value;
		$this->chng_flag=TRUE;
	}
	
	function setAssignUserId($value) {
		$this->assign_user_id=$value;
		$this->chng_flag=TRUE;
	}

	function setBegin($value) {
		$this->begin=$value;
		$this->chng_flag=TRUE;
	}

	function setEnd($value) {
		$this->end=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatEnd($value) {
		$this->repeat_end=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatQuantity($value) {
		$this->repeat_quantity=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatInterval($value) {
		$this->repeat_interval=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatMonthOfYear($value) {
		$this->repeat_month_of_year=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatDayOfMonth($value) {
		$this->repeat_day_of_month=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatWeekOfMonth($value) {
		$this->repeat_week_of_month=$value;
		$this->chng_flag=TRUE;
	}
	
	function setRepeatDayOfWeek($value) {
		$this->repeat_day_of_week=$value;
		$this->chng_flag=TRUE;
	}

	function restore($id='') {
		if(func_num_args() == 1){
			if (!$id){
				return false;
			}
		} else {
			if (!$this->id){
				return false;
			}
			$id = $this->id;
		}
		$query = sprintf("SELECT * FROM resources_assign WHERE assign_id='%s' ",$id);
		$this->db->query($query);
		
		if($this->db->next_record()) {
			$this->id = $id;
			$this->resource_id = $this->db->f("resource_id");
			$this->assign_user_id = $this->db->f("assign_user_id");
			$this->user_free_name = $this->db->f("user_free_name");
			$this->begin =$this->db->f("begin");
			$this->end = $this->db->f("end");
			$this->repeat_end = $this->db->f("repeat_end");
			$this->repeat_quantity = $this->db->f("repeat_quantity");
			$this->repeat_interval = $this->db->f("repeat_interval");
			$this->repeat_month_of_year  =$this->db->f("repeat_month_of_year");
			$this->repeat_day_of_month =$this->db->f("repeat_day_of_month");
			$this->repeat_month = $this->db->f("repeat_month");
			$this->repeat_week_of_month = $this->db->f("repeat_week_of_month");
			$this->repeat_day_of_week = $this->db->f("repeat_day_of_week");
			$this->repeat_week = $this->db->f("repeat_week");
			return TRUE;
		}
		return FALSE;
	}

	function store($create=''){
		// save only, if changes were made or the object is new and a assign_user_id or a user_free_name is given
		if ((($this->chng_flag) || ($create)) && (($this->assign_user_id) || ($this->user_free_name))) {
			$chdate = time();
			$mkdate = time();
			
			//insert NULL instead of nothing
			if (!$this->assign_user_id)
				$tmp_assign_user_id = "NULL";
			else
				$tmp_assign_user_id = "'$this->assign_user_id'";
				
			if($create) {
				$query = sprintf("INSERT INTO resources_assign SET assign_id='%s', resource_id='%s', " 
					."assign_user_id=%s, user_free_name='%s', begin='%s', end='%s', repeat_end='%s', "
					."repeat_quantity='%s', repeat_interval='%s', repeat_month_of_year='%s', repeat_day_of_month='%s',  "
					."repeat_week_of_month='%s', repeat_day_of_week='%s', mkdate='%s' "
							 , $this->id, $this->resource_id, $tmp_assign_user_id, $this->user_free_name, $this->begin
							 , $this->end, $this->repeat_end, $this->repeat_quantity, $this->repeat_interval
							 , $this->repeat_month_of_year, $this->repeat_day_of_month, $this->repeat_week_of_month
							 , $this->repeat_day_of_week, $mkdate);
			} else {
				$query = sprintf("UPDATE resources_assign SET resource_id='%s', " 
					."assign_user_id=%s, user_free_name='%s', begin='%s', end='%s', repeat_end='%s', "
					."repeat_quantity='%s', repeat_interval='%s', repeat_month_of_year='%s', repeat_day_of_month='%s',  "
					."repeat_week_of_month='%s', repeat_day_of_week='%s' WHERE assign_id='%s' "
							 , $this->resource_id, $tmp_assign_user_id, $this->user_free_name, $this->begin 
							 , $this->end, $this->repeat_end, $this->repeat_quantity, $this->repeat_interval
							 , $this->repeat_month_of_year, $this->repeat_day_of_month, $this->repeat_week_of_month
							 , $this->repeat_day_of_week, $this->id);
			}
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE resources_assign SET chdate='%s' WHERE assign_id='%s' ", $chdate, $this->id);
				$this->db->query($query);
				return TRUE;
			} else
				return FALSE;
		}
		return FALSE;
	}

	function delete() {
		/*
		NOTE: this feature isn't used at the moment. I could be useful, if a functionality to delete assings from
		Veranstaltungen by an resources admin will be implemented. So - we keep it for the future...
	
		//update the owner in the case it is a Veranstaltung (delete resource_id from the metadata array)
		if ($this->getOwnerType() == "sem") {
			$query = sprintf ("SELECT metadata_dates FROM seminare WHERE Seminar_id = '%s' ", $this->assign_user_id);
			$this->db->query($query);
			$this->db->next_record();
			
			$metadata_termin = unserialize ($this->db->f("metadata_dates"));
			
			foreach ($metadata_termin["turnus_data"] as $key =>$val)
				if ($val["resource_id"] == $this->resource_id) {
					$metadata_termin["turnus_data"][$key]["resource_id"]=FALSE;
				}
			
			$serialized_metadata = serialize($metadata_termin);
			$query = sprintf ("UPDATE seminare SET metadata_dates ='%s' WHERE Seminar_id = '%s' ", $serialized_metadata, $this->assign_user_id);
			$this->db->query($query);
		}
		*/
		
		$query = sprintf("DELETE FROM resources_assign WHERE assign_id='%s'", $this->id);
		if($this->db->query($query))
			return TRUE;
		return FALSE;
	}

}
