<?php

/*
dates.inc.php - basale Routinen zur Terminveraltung.
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, Andr� Noack <anoack@mcis.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require_once $ABSOLUTE_PATH_STUDIP."datei.inc.php";  // ben�tigt zum L�schen von Dokumenten
require_once $ABSOLUTE_PATH_STUDIP."config.inc.php";  //Daten 
require_once $ABSOLUTE_PATH_STUDIP."functions.php";  //Daten 
require_once ("$RELATIVE_PATH_CALENDAR/calendar_func.inc.php");

/**
* This function creates the assigned room name for range_id
*
* @param		string	the id of the Veranstaltung or date
* @retunr		string	the name of the room
*
*/

function getRoom ($range_id, $link=TRUE) {
	global $RESOURCES_ENABLE, $RELATIVE_PATH_RESOURCES, $TERMIN_TYP;
	
	if ($RESOURCES_ENABLE)	
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
	
	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	
	switch (get_object_type($range_id)) {
		case ("sem"):
			$query = sprintf ("SELECT metadata_dates, ort FROM seminare WHERE Seminar_id = '%s'", $range_id);
			$db->query($query);
			if ($db->next_record()) {
				//get the metatdata array
				$term_data=unserialize($db->f("metadata_dates"));
				$i=0;
				if (is_array($term_data["turnus_data"])) {
					foreach ($term_data["turnus_data"] as $data) {
						if ($i)
							$ret.=", ";
						if (sizeof($term_data["turnus_data"]) > 1)
							switch ($data["day"]) {
								case "1": $ret .=_("Mo.: "); break;
								case "2": $ret .=_("Di.: "); break;
								case "3": $ret .=_("Mi.: "); break;
								case "4": $ret .=_("Do.: "); break;
								case "5": $ret .=_("Fr.: "); break;
								case "6": $ret .=_("Sa.: "); break;
								case "7": $ret .=_("So.: "); break;
							}
						if (($RESOURCES_ENABLE) && ($data["resource_id"])) {
							if ($link)
								$ret .= sprintf ("<a href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav\">%s</a>", $data["resource_id"], htmlReady(getResourceObjectName($data["resource_id"])));
							else
								$ret .= getResourceObjectName($data["resource_id"]);
						}
						elseif ((!$data["room"]) && (sizeof($term_data["turnus_data"]) >1))
							$ret .=_("n. A.");
						else
							$ret .= htmlReady($data["room"]);
						$i++;
					}
					if ($ret)
						return $ret;
					else
						return _("nicht angegeben");
				} else {
					//Load all TERMIN_TYPs that are "Sitzungstermine" and build query-clause
					$i=0;
					$typ_clause = "(";
					foreach ($TERMIN_TYP as $key=>$val) {
						if ($val["sitzung"]) {
							if ($i)
								$typ_clause .= ", ";
							$typ_clause .= "'".$key."' ";
							$i++;
						}
					}
					$typ_clause .= ")";	
					
					$query = sprintf ("SELECT termin_id, date, raum FROM termine WHERE date_typ IN $typ_clause AND range_id='%s' ORDER BY date", $range_id);
					$db->query($query);
					$i=0;
					while ($db->next_record()) {
						$tmp_room='';
						if ($RESOURCES_ENABLE) {
							if (getDateAssigenedRoom($db->f("termin_id")))
								if ($link) 
									$tmp_room .= sprintf ("<a href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav\">%s</a>", getDateAssigenedRoom($db->f("termin_id")), htmlReady(getResourceObjectName(getDateAssigenedRoom($db->f("termin_id")))));
								else
									$tmp_room .= getResourceObjectName(getDateAssigenedRoom($range_id));
						}
						if (($tmp_room) || ($db->f("raum"))) {
							if ($i)
								$ret .= ", ";
							$i++;
						}
						if ($tmp_room)
							$ret .= date ("d.m", $db->f("date")).": ".$tmp_room;
						elseif ($db->f("raum"))
							$ret .= date ("d.m", $db->f("date")).": ".htmlReady($db->f("raum"));
					}
					if ($ret)
						return $ret;
					else
						return _("nicht angegeben");
				}
			} else
				return FALSE;
		break;
		case ("date");
			$query = sprintf ("SELECT termin_id, date, raum FROM termine WHERE termin_id='%s' ", $range_id);
			$db->query($query);
			$db->next_record();

			if ($RESOURCES_ENABLE) {
				if (getDateAssigenedRoom($range_id))
					if ($link)
						$tmp_room .= sprintf ("<a href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav\">%s</a>", getDateAssigenedRoom($range_id), htmlReady(getResourceObjectName(getDateAssigenedRoom($range_id))));
					else
						$tmp_room .= getResourceObjectName(getDateAssigenedRoom($range_id));
			}
			if ($tmp_room)
				$ret .= $tmp_room;
			else
				$ret .= htmlReady($db->f("raum"));
			return $ret;
		break;
	}
}


/*
Die Funktion veranstaltung_beginn errechnet den ersten Seminartermin aus dem Turnus Daten.
Zurueckgegeben wird ein String oder Timestamp. je nach return_mode (TRUE = Timestamp)
Evtl. Ergaenzungen werden im Stringmodus mit ausgegeben.
Die Funktion kann mit einer Seminar_id aufgerufen werden, dann werden saemtliche gespeicherten Daten 
beruecksichtigt. Im 'ad hoc' Modus koennen der Funktion auch die eizelnen Variabeln des Metadaten-Arrays
uebergeben werden. Dann werden konkrete Termine nur mit berruecksichtigt, sofern sie schon angelegt wurden.
*/

function veranstaltung_beginn ($seminar_id='', $art='', $semester_start_time='', $start_woche='', $start_termin='', $turnus_data='', $return_mode='') {
	global $SEMESTER, $TERMIN_TYP;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;	
	
	if ((func_num_args()==1) || (func_num_args()==2)){
		$seminar_id=func_get_arg(0);
		if (func_num_args()==2)
			$return_mode=func_get_arg(1);
		$db->query("SELECT metadata_dates, start_time, duration_time FROM seminare WHERE seminar_id='$seminar_id'");
		$db->next_record();
		$term_data=unserialize($db->f("metadata_dates"));
		$term_data["start_time"]=$db->f("start_time");
	} else {
		$term_data["art"]=func_get_arg(0);
		$term_data["start_time"]=func_get_arg(1);
		$term_data["start_woche"]=func_get_arg(2);
		$term_data["start_termin"]=func_get_arg(3);
		$term_data["turnus_data"]=func_get_arg(4);
		if (func_num_args()==6)
			$return_mode=func_get_arg(1);
	}
	//Regelmaessige Termine. also Turnus aus Metadaten
	if ($term_data["art"]==0)
		{
		if (($term_data["start_woche"] ==0) || ($term_data["start_woche"] ==1)) // Startzeitpunkt 1. oder 2. Semesterwoche
			if (sizeof($term_data["turnus_data"])) {
				foreach ($SEMESTER as $sem)
					if (($term_data["start_time"] >= $sem["beginn"]) AND ($term_data["start_time"] <= $sem["ende"]))
						$vorles_beginn=$sem["vorles_beginn"];
				$start_termin=$vorles_beginn+(($term_data["turnus_data"][0]["day"]-1)*24*60*60)+($term_data["turnus_data"][0]["start_stunde"]*60*60)+($term_data["turnus_data"][0]["start_minute"]*60) + ($term_data["start_woche"] * 7 * 24 * 60 *60);
				$end_termin=$vorles_beginn+(($term_data["turnus_data"][0]["day"]-1)*24*60*60)+($term_data["turnus_data"][0]["end_stunde"]*60*60)+($term_data["turnus_data"][0]["end_minute"]*60) + ($term_data["start_woche"] * 7 * 24 * 60 *60);;
				$return_string=date ("d.m.Y, G:i", $start_termin);
				$return_int=$start_termin;
				if ($start_termin != $end_termin) 
					$return_string.=" - ".date ("G:i", $end_termin);
				}
			else {
				$return_string="nicht angegeben";
				$return_int=-1; 
			}
		//anderer Startzeitpunkt gewaehlt
		else {
			//kein gueltiger Termin bekannt
			if ($term_data["start_termin"]<1) {
				$return_string.= _("nicht angegeben");
				$return_int=-1;
			//gueltiger Termin bekannt
			} else {
				$return_string.=date ("d.m.Y", $term_data["start_termin"]);
				$return_int=$term_data["start_termin"];
			}
			if (is_array($term_data["turnus_data"]))
				foreach ($term_data["turnus_data"] as $val) {
					$dow = $val["day"];
					if ($dow == 7)
						$dow=0;
					if ($dow == date("w", $term_data["start_termin"])) {
						if ($val["start_stunde"]) {
							$return_string.=", ". $val["start_stunde"]. ":"; 
							if (($val["start_minute"] > 0)  &&  ($val["start_minute"] < 10))
								$return_string.="0". $val["start_minute"];
							elseif ($val["start_minute"] > 10)
								$return_string.=$val["start_minute"];
							if (!$val["start_minute"])
								$return_string.="00";
							if (!(($val["end_stunde"] != $val["start_stunde"]) && ($val["end_minute"] !=$val["start_minute"]))) {
								$return_string.= " - ". $val["end_stunde"]. ":";
								if (($val["end_minute"] > 0)  &&  ($val["end_minute"] < 10))
									$return_string.="0".$val["end_minute"];
								elseif ($val["end_minute"] > 10)
									$return_string.=$val["end_minute"];
								if (!$val["end_minute"])
									$return_string.="00";
								}	
							}
						break;
						}
					}
				}
		}
	//Unregelmaessige Termine, also konkrete Termine aus Termintabelle
	else {
	
		//Load all TERMIN_TYPs that are "Sitzungstermine" and build query-clause
		$i=0;
		$typ_clause = "(";
		foreach ($TERMIN_TYP as $key=>$val) {
			if ($val["sitzung"]) {
				if ($i)
					$typ_clause .= ", ";
				$typ_clause .= "'".$key."' ";
				$i++;
			}
		}
		$typ_clause .= ")";	
		
		$db2->query("SELECT date, end_time FROM termine WHERE date_typ IN $typ_clause AND range_id='$seminar_id' ORDER BY date");
		$db2->next_record();
		if ($db->affected_rows()) {
			$return_string=date ("d.m.Y, G:i", $db2->f("date"))." - ".date ("G:i",  $db2->f("end_time"));
			$return_int=$db2->f("date");
		} else {
			$return_string.= _("nicht angegeben");
			$return_int=-1;
		
		}
	}

	if ($return_mode)
		return $return_int;	
	else
		return $return_string;	
	}

/*
Die Funktion view_turnus zeigt in einer kompakten Ansicht den Turnus eines Seminars an.
Angezeigt werden bei unregelmaessigen Veranstaltungen gruppierte Termine,
wenn mehrere gleiche Termine an aufeinanderfolgenden Tagen liegen.
Der Parameter short verkuerzt die Ansicht nochmals (fuer besonders platzsparende Ausgabe).
Bei regelmaessigen Veranstaltungen werden die einzelen Zeiten ausgegeben, bei zweiwoechentlichem
Turnus mit dem enstprechenden Zusatz. Short verkuerzt die Ansicht nochmals.
*/

function view_turnus ($seminar_id, $short = FALSE) {
	global $TERMIN_TYP;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	
	
	$db->query("SELECT metadata_dates FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();
	
	$term_data=unserialize($db->f("metadata_dates"));
	
	if ($term_data["art"] == 1)
		{
		//Load all TERMIN_TYPs that are "Sitzungstermine" and build query-clause
		$i=0;
		$typ_clause = "(";
		foreach ($TERMIN_TYP as $key=>$val) {
			if ($val["sitzung"]) {
				if ($i)
					$typ_clause .= ", ";
				$typ_clause .= "'".$key."' ";
				$i++;
			}
		}
		$typ_clause .= ")";
		
		$db2->query("SELECT * FROM termine WHERE range_id='$seminar_id' AND date_typ IN $typ_clause ORDER BY date");
		if ($db2->affected_rows() == 0)
			{
			if ($short)
				$return_string= _("Termin: n. A.");
			else
				$return_string= _("unregelm�ssige Veranstaltung oder Blockveranstaltung. Die Termine stehen nicht fest.") . " ";
			}
		else
			if ($short)
				$return_string= _("Termine am") . " ";
			else
				$return_string= _("unregelm�ssige Veranstaltung oder Blockveranstaltung am") . " ";

			while ($db2->next_record())
				$dates[]=array("start_time"=>$db2->f("date"), "end_time"=>$db2->f("end_time"), "conjuncted"=>FALSE, "time_match"=>FALSE);
			
			for ($i=1; $i<sizeof($dates); $i++)
				{
				if (((date("G", $dates[$i-1]["start_time"])) == date("G", $dates[$i]["start_time"])) && ((date("i", $dates[$i-1]["start_time"])) == date("i", $dates[$i]["start_time"])) && ((date("G", $dates[$i-1]["end_time"])) == date("G", $dates[$i]["end_time"])) && ((date("i", $dates[$i-1]["end_time"])) == date("i", $dates[$i]["end_time"])))
					$dates[$i]["time_match"]=TRUE;
					
				if (((date ("z", $dates[$i]["start_time"])-1) == date ("z", $dates[$i-1]["start_time"])) || ((date ("z", $dates[$i]["start_time"]) == 0) && (date ("j", $dates[$i-1]["start_time"]) == 0)))
					if ($dates[$i]["time_match"])
						$dates[$i]["conjuncted"]=TRUE;
				}
			
			for ($i=0; $i<sizeof($dates); $i++)
				{
				if (!$dates[$i]["conjuncted"])
					$conjuncted=FALSE;				
					
				if ((!$dates[$i]["conjuncted"]) || (!$dates[$i+1]["conjuncted"]))
					$return_string.=date (" j.n.", $dates[$i]["start_time"]);
				
				if ((!$conjuncted) && ($dates[$i+1]["conjuncted"]))
					{
					$return_string.=" -";	
					$conjuncted=TRUE;
					}
				elseif ((!$dates[$i+1]["conjuncted"]) && ($dates[$i+1]["time_match"]))
					$return_string.=",";
					
				if (!$dates[$i+1]["time_match"])
					{
					$return_string.=" ".date("G:i", $dates[$i]["start_time"]);
					if (date("G:i", $dates[$i]["start_time"]) != date("G:i", $dates[$i]["end_time"])) 
						$return_string.=" - ".date("G:i", $dates[$i]["end_time"]);
					if ($i+1 != sizeof ($dates))
						$return_string.=",";
					}
				}
		}
	else
		{
		if ($short)
			if (sizeof($term_data["turnus_data"])) {
				$k=0;
				foreach ($term_data["turnus_data"] as $data)
					{
					if ($k) 
						$return_string.=", ";
					$k++;
					switch ($data["day"])
						{
						case "1": $return_string.= _("Mo."); break;
						case "2": $return_string.= _("Di."); break;
						case "3": $return_string.= _("Mi."); break;
						case "4": $return_string.= _("Do."); break;
						case "5": $return_string.= _("Fr."); break;
						case "6": $return_string.= _("Sa."); break;
						case "7": $return_string.= _("So."); break;
						}
					$return_string.=" ".$data["start_stunde"].":";
					if (!$data["start_minute"])
						$return_string.="00";
					elseif (($data["start_minute"] <10) && ($data["start_minute"] >0))
						$return_string.="0".$data["start_minute"];
					else
						$return_string.=$data["start_minute"];
					if (!(($data["end_stunde"] == $data["start_stunde"]) && ($data["end_minute"] == $data["start_minute"])))
						{
						$return_string.=" - ".$data["end_stunde"].":";
						if (!$data["end_minute"])
							$return_string.="00";
						elseif (($data["end_minute"] <10) && ($data["end_minute"] >0))
							$return_string.="0".$data["end_minute"];
						else
							$return_string.=$data["end_minute"];
						}
					else
						$return_string.=" ";
					}
				}
			else {
				$return_string= _("Zeiten: n. A.");
				}
		else
			if (sizeof($term_data["turnus_data"])) {
				$k=0;
				foreach ($term_data["turnus_data"] as $data)
					{
					if ($k) 
						$return_string.=", ";
					$k++;
					switch ($data["day"])
						{
						case "1": $return_string.= _("Montag"); break;
						case "2": $return_string.= _("Dienstag"); break;
						case "3": $return_string.= _("Mittwoch"); break;
						case "4": $return_string.= _("Donnerstag"); break;
						case "5": $return_string.= _("Freitag"); break;
						case "6": $return_string.= _("Samstag"); break;
						case "7": $return_string.= _("Sonntag"); break;
						}
					$return_string.=" ".$data["start_stunde"].":";
					if (!$data["start_minute"])
						$return_string.="00";
					elseif (($data["start_minute"] <10) && ($data["start_minute"] >0))
						$return_string.="0".$data["start_minute"];
					else
						$return_string.=$data["start_minute"];
					if (!(($data["end_stunde"] == $data["start_stunde"]) && ($data["end_minute"] == $data["start_minute"])))
						{
						$return_string.=" - ".$data["end_stunde"].":";
						if (!$data["end_minute"])
							$return_string.="00";
						elseif (($data["end_minute"] <10) && ($data["end_minute"] >0))
							$return_string.="0".$data["end_minute"];
						else
							$return_string.=$data["end_minute"];
						}
					else
						$return_string.=" ";
					}
				}
			else {
				$return_string= _("Die Zeiten der Veranstaltung stehen nicht fest.");
				}			
			if ($term_data["turnus"] == 1)
				$return_string.= " " . _("(zweiw�chentlich)");
		}
	return $return_string;
	}


/*
Die Funktion Vorbesprechung ueberpueft, ob es eine Vorbesprechung gibt und gibt in diesem
Falle den entsprechenden Timestamp zurueck. Ansonsten wird FALSE zurueckgegeben.
*/

function vorbesprechung ($seminar_id)
	{
	$db=new DB_Seminar;
	$db->query("SELECT * FROM termine WHERE range_id='$seminar_id' AND date_typ='2' ORDER by date");
	if ($db->next_record())
		$return_string=date ("d.m.Y, G:i", $db->f("date"))." - ".date ("G:i", $db->f("end_time"));
	if ($db->f("raum"))
		$return_string.=", Ort: ".$db->f("raum");
	return $return_string;		
	}

/*
Die Funktion next_date errechnet den naechsten Seminartermin. Dabei wird zuerst geprueft, ob es spezielle
Termine in der Termintabelle gibt. Wenn nicht wird der naechste Seminartermin aus dem Turnusdaten ermittelt.
*/

function next_date ($seminar_id)
	{
	}
	
/*
Die Funktion get_sem_name gibt den Namen eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/


function get_sem_name ($time) {
	global $SEMESTER;
	foreach ($SEMESTER as $key=>$val)
		if (($time >= $val["beginn"]) AND ($time <= $val["ende"]))
			return $val["name"];

}

/*
Die Funktion get_sem_num gibt die Nummer eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/

function get_sem_num ($time) {
	global $SEMESTER;
	foreach ($SEMESTER as $key=>$val)
		if (($time >= $val["beginn"]) AND ($time <= $val["ende"]))
			return $key;

}

function get_sem_num_sem_browse () {
	global $SEMESTER;
	$time = time();
	$ret = false;
	foreach ($SEMESTER as $key=>$val){
		if ($ret && ($val["vorles_ende"] >= $time)){
			$ret = $key;
			break;
		}
		if ($time >= $val["vorles_ende"]){
			$ret = true;
		}
	}
	return $ret;
}

/*
Die Funktion get_semester gibt den oder die Semester einer speziellen Veranstaltung aus.
*/

function get_semester($seminar_id, $start_sem_only=FALSE)
	{
	$db=new DB_Seminar;
	$db->query("SELECT metadata_dates, start_time, duration_time FROM seminare WHERE seminar_id='$seminar_id'");
	$db->next_record();
	
	$return_string=get_sem_name($db->f("start_time"));
	if (!$start_sem_only) {
		if ($db->f("duration_time")>0)
			$return_string.=" - ".get_sem_name($db->f("start_time") + $db->f("duration_time"));
		if ($db->f("duration_time")==-1)				
			$return_string.=" bis unbegrenzt";
		}
	return $return_string;		
	}


/*
Die Funktion edit_dates veraendert den zu der Uebergebenen termin_id passenden Termin.
Dazu wird die Beschreibung des Ordners angepasst, falls es einen gibt.
Dabei werden die Beschriftungen der Ordner im Forensystem und im Dateisystem aktualisiert.
*/


function edit_dates($stunde,$minute,$monat,$tag,$jahr,$end_stunde, $end_minute, $termin_id,$art,$titel,$description,$topic_id,$raum,$resource_id,$range_id,$term_data='') {
	global $user,$auth, $SEMESTER, $TERMIN_TYP, $RESOURCES_ENABLE, $RELATIVE_PATH_RESOURCES;

	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
	}
	
	$do=TRUE;
	if (!checkdate($monat,$tag,$jahr)) {
		$do=FALSE;
		$result="error�Bitte geben Sie ein g&uuml;ltiges Datum ein!";
	}

	if ($do)		
		if ((!$stunde) && (!end_stunde)) {
			$do=FALSE;	
			$result.="error�Bitte geben Sie eine g&uuml;eltige Start- und Endzeit an!";
		}
	
	$start_time = mktime($stunde,$minute,0,$monat,$tag,$jahr);
	$end_time = mktime($end_stunde,$end_minute,0,$monat,$tag,$jahr);
	
	if ($do)		
		if ($start_time > $end_time) {
			$do=FALSE;	
			$result.="error�Der Endzeitpunkt muss nach dem Startzeitpunkt liegen!";
		}
				
	//Check auf Konsistenz mt Metadaten, Semestercheck
	if (($do) && ($TERMIN_TYP[$art]["sitzung"]==1) && (is_array($term_data ["turnus_data"]))) {
		foreach ($SEMESTER as $a) {
			if (($term_data["start_time"] >= $a["beginn"]) && ($term_data["start_time"] <= $a["ende"]))  {
				$sem_beginn=$a["beginn"];
				$sem_ende=$a["ende"];
			}
			if (($term_data["duration_time"] > 0) && ((($term_data["start_time"] + $term_data["duration_time"]) >= $a["beginn"]) && (($term_data["start_time"] + $term_data["duration_time"]) < $a["ende"])))
				$sem_ende=$a["ende"];
			}
			
		if (($start_time < $sem_beginn) || ($start_time > $sem_ende))
			$add_result.="info�Sie haben einen oder mehrere Termine eingegeben, der ausserhalb des Semesters liegt, in dem die Veranstaltung stattfindet. Es wird empfohlen, diese Termine anzupassen.�";
		
		//Und dann noch auf regelmaessige Termine checken, wenn dieser Typ gewsehlt ist
		if (!$term_data["art"]) {
			foreach ($term_data ["turnus_data"] as $a) {
				if ($a["day"] == 7) 
					$tmp_day=0;
				else
					$tmp_day=$a["day"];
				if ($tmp_day == date("w", $start_time)) {
					$tmp_start_time=mktime (date("G", $start_time), date("i", $start_time), 0, 8, 1, 2001);
					$tmp_end_time=mktime (date("G", $end_time), date("i", $end_time), 0, 8, 1, 2001);
					$tmp_turnus_start=mktime ($a["start_stunde"], $a["start_minute"], 0, 8, 1, 2001);
					$tmp_turnus_end=mktime ($a["end_stunde"], $a["end_minute"], 0, 8, 1, 2001);
					if (($tmp_start_time >= $tmp_turnus_start) && ($tmp_end_time <= $tmp_turnus_end))
						$ok=TRUE;
				}
			}
			if (!$ok)
				$add_result.="info�Sie haben einen oder mehrere Termine eingegeben, der nicht zu den allgemeinen Veranstaltungszeiten stattfindet. Es wird empfohlen, Sitzungstermine von regelm&auml;&szlig;igen Veranstaltungen nur zu den allgemeinen Zeiten stattfinden zu lassen.�";
		}
	}
		
	if ($result) 
		$result.="<br> Der Termin <b>\"$titel\"</b> konnte nicht ge&auml;ndert werden.�";
	
	if ($do) {
		$db=new DB_Seminar;
		$db2=new DB_Seminar;
		$db3=new DB_Seminar;
		$db4=new DB_Seminar;

		$author = get_fullname();

		$titel=$titel;
		$description=$description; 

		//if we have a resource_id, we take the room name from resource_id
		if ($resource_id)
			$raum=getResourceObjectName($resource_id);
		
		$db->query("UPDATE  termine SET autor_id='$user->id', content='$titel', date= '$start_time', end_time='$end_time', date_typ='$art', raum='$raum', description='$description'  WHERE termin_id='$termin_id'");
		if ($db->affected_rows()) {
			$db->query ("UPDATE termine SET chdate='".time()."' WHERE termin_id='$termin_id'"); //Nur wenn Daten geaendert wurden, schreiben wir auch ein chdate
		}
			
		//Workaround fuer Forenbug. Dies ist keine Loesung, sondern Schadensvermeidung!!
		$db3->query("SELECT Seminar_id FROM px_topics WHERE topic_id ='$topic_id'");
		$db3->next_record();
			
		$db4->query("SELECT range_id FROM termine WHERE termin_id ='$termin_id'");
		$db4->next_record();
			
		if ($db3->f("Seminar_id") == $db4->f("range_id")) {
		//WA Ende
			
			if ($topic_id) 
				$db->query("UPDATE px_topics SET name='".$TERMIN_TYP[$art]["name"].": $titel am ".date("d.m.Y ", $start_time)."', author='$author', user_id='".$user->id."' WHERE topic_id='$topic_id'");		
			if ($db->affected_rows())
				$db2->query("UPDATE px_topics SET chdate='".time()."' WHERE topic_id='$topic_id'");
			
		//WA Teil zwei, wo gefunden, so korrigieren
		} else {
			if ($topic_id) 
				$db->query("UPDATE termine SET topic_id=''  WHERE termin_id='$termin_id'");		
			}
		//WA Ende
			
		//Aendern des Titels des zugehoerigen Ordners
		$titel_f=$TERMIN_TYP[$art]["name"].": $titel";
		$titel_f.=" am ".date("d.m.Y ", $start_time);
		
		$db->query("SELECT folder_id FROM folder WHERE range_id ='$termin_id'");
		if ($db->num_rows() == 1) {
			$db->next_record();
			$db2->query ("UPDATE folder SET name='$titel_f' WHERE folder_id = '".$db->f("folder_id")."'");
			if ($db2->affected_rows())
				$db3->query("UPDATE folder SET chdate='".time()."' WHERE folder_id = '".$db->f("folder_id")."'");
		}
		
		//update assigned resources, if resource manangement activ
		if ($RESOURCES_ENABLE) {
			$updateAssign = new VeranstaltungResourcesAssign($range_id);
			if ($resource_id)
				$resources_result=$updateAssign->changeDateAssign($termin_id, $resource_id);
			else
				$updateAssign->killDateAssign($termin_id);
		}
	}

	$result_a["msg"]=$result;
	$result_a["add_msg"]=$add_result;
	$result_a["resources_result"]=$resources_result;

	return ($result_a);
}

/*
Die Funktion delete_topic l�scht rekursiv alle Postings ab der �bergebenen topic_id, der zweite Parameter
muss(!) eine Variable sein, diese wird f�r jedes gel�schte Posting um eins erh�ht
*/

function delete_topic($topic_id, &$deleted)  //rekursives l�schen von topics VORSICHT!
{

	$db=new DB_Seminar;
	// echo "gel�scht $topic_id<br>";
	$db->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
	if ($db->num_rows()) {
		while ($db->next_record()) {
			$next_topic=$db->f("topic_id");
			delete_topic($next_topic,$deleted);
		}
	}
 	$db->query("DELETE FROM px_topics WHERE topic_id='$topic_id'");
 	$deleted++;
	
	// gehoerte dieses Posting zu einem Termin?
	// dann Verknuepfung loesen...
	$db->query("UPDATE termine SET topic_id = '' WHERE topic_id = '$topic_id'");
	
 	return;
}

/*
Die function delete_date l�scht einen Termin und verschiebt daran haegende
Ordner in den allgemeinen Ordner.
Der erste Parameter ist die termin_id des zu l�schenden Termins.
Der zweite Parameter topic_id gibt an, ob auch die zu diesem Termin gehoerenden
Postings im Forensystem geloescht werden sollen.
0 bzw. FALSE : keine Topics loeschen
> 0 : rekursives loeschen von topic_id
Der dritte Parameter gibt analog an, ob auch die zu diesem Terminen gehoerenden
Folder im Ordnersystem geloescht werden sollen.
Der R�ckgabewert der Funktion ist die Anzahl der insgesamt gel�schten Items.
-1 bedeutet einen Fehler beim Loeschen des Termins.
Ausgabe wird keine produziert.
Es erfolgt keine �berpr�fung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_date ($termin_id, $topic_id = FALSE, $folder_move=FALSE, $sem_id=0) {
	global $RESOURCES_ENABLE, $RELATIVE_PATH_RESOURCES;
	
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	}
	
	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$db3 = new DB_Seminar;	

	## Eventuell rekursiv Postings loeschen
	if ($topic_id) {
		delete_topic($topic_id,$count);
	}
	
	if (!$folder_move) {
		## Dateiordner muessen weg!
		recursiv_folder_delete ($termin_id);
		}
	else {
		## Dateiordner werden verschoben, wenn Ordner nicht leer, ansonsten auch weg
		if (!doc_count($termin_id, 0))
			recursiv_folder_delete ($termin_id);		
		else {
			$db->query("SELECT folder_id FROM folder WHERE range_id ='$sem_id'");
			$db->next_record();
			$db2->query("SELECT folder_id FROM folder WHERE range_id = '$termin_id'");
			while ($db2->next_record()) {
				move_item ($db2->f("folder_id"), $db->f("folder_id"));
				$db3->query ("UPDATE folder SET name='Dateiordner zu gel�schtem Termin', description='Dieser Ordner enth�lt Dokumente und Termine eines gel�schten Termins' WHERE folder_id='".$db2->f("folder_id")."'");
				}
			}
		}

	## Und den Termin selbst loeschen
	$query = "DELETE FROM termine WHERE termin_id='$termin_id'";
	$db->query($query);
	if ($db->affected_rows() && $RESOURCES_ENABLE) {
		$insertAssign = new VeranstaltungResourcesAssign($sem_id);
		$insertAssign->killDateAssign($termin_id);
	}
}

/*
Die function delete_range_of_dates l�scht Termine mit allen daran haengenden Items.
Der erste Parameter ist die range_id der zu l�schenden Termine.
Es koennen also mit einem Aufruf alle Termine eines Seminares,
eines Institutes oder persoenliche Termine eines Benutzers aus der Datenbank entfernt werden.
Dokumente und Literatur an diesen Terminen werden auf jeden Fall gel�scht.
Der zweite Parameter topics gibt an, ob auch die zu diesen Terminen gehoerenden
Postings im Forensystem geloescht werden sollen.
0 bzw. FALSE : keine Topics loeschen
1 bzw. TURE : rekursives Loeschen der Postings
Der R�ckgabewert der Funktion ist die Anzahl der gel�schten Termine.
Ausgabe wird keine produziert.
Es erfolgt keine �berpr�fung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_range_of_dates ($range_id, $topics = FALSE) {

	$db = new DB_Seminar;
	$count = 0;

	## Termine finden...
	$query = "SELECT termin_id, topic_id FROM termine WHERE range_id='$range_id'";
	$db->query($query);
	while ($db->next_record()) {       // ...und nacheinander...
		if ($topics)
			delete_date($db->f("termin_id"), $db->f("topic_id"));  // ...mit topics loeschen
		else
			delete_date($db->f("termin_id"), FALSE);  // ...ohne topics loeschen
		$count ++;
	}

	return $count;
}



function dateAssi ($sem_id, $mode="update", $topic=FALSE, $folder=FALSE, $full = FALSE, $old_turnus = FALSE) {
	global $RESOURCES_ENABLE, $RELATIVE_PATH_RESOURCES, $SEMESTER, $HOLIDAY, $TERMIN_TYP, $user;
	
	if ($RESOURCES_ENABLE)	{
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		$insertAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
	}
	 	
	$hash_secret = "blubbelsupp";
	$date_typ=1; //type to use for new dates
	$author = get_fullname();

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;

	//load data of the Veranstaltung
	$query = sprintf("SELECT start_time, duration_time, metadata_dates FROM seminare WHERE Seminar_id = '%s'", $sem_id);
	$db->query($query);
	$db->next_record();

	$term_data = unserialize ($db->f("metadata_dates"));
	$veranstaltung_start_time = $db->f("start_time");
	$veranstaltung_duration_time = $db->f("duration_time");
	
	//load the ids from already created dates
	if (($mode == "update") && (is_array($old_turnus))) {
		$i=0;
		$clause=" (";
		foreach ($old_turnus as $val) {
			if ($i)
				$clause.=" OR";

			if ($val["day"] == 7)
				$t_day = 1;
			else
				$t_day = $val["day"] + 1;
				
			$clause.="(";
			$clause.="DAYOFWEEK(FROM_UNIXTIME(date)) = '".$t_day."' AND ";
			$clause.="HOUR(FROM_UNIXTIME(date)) = '".$val["start_stunde"]."' AND ";
			$clause.="MINUTE(FROM_UNIXTIME(date)) = '".$val["start_minute"]."' AND ";
			$clause.="HOUR(FROM_UNIXTIME(end_time)) = '".$val["end_stunde"]."' AND ";
			$clause.="MINUTE(FROM_UNIXTIME(end_time)) = '".$val["end_minute"]."' ";
			$clause.=")";
			
			$i++;
		}
		$clause.=" )";
		
		$query = sprintf("SELECT termin_id FROM termine WHERE range_id='%s' AND %s ORDER BY date", $sem_id, $clause);
		$db->query($query);
		while ($db->next_record())
			$saved_dates[] = $db->f("termin_id");
	}

	//determine first day of the start-week as sem_begin
	if ($term_data["start_woche"] >= 0) {
		foreach ($SEMESTER as $val)
			if (($veranstaltung_start_time >= $val["beginn"]) AND ($veranstaltung_start_time <= $val["ende"])) {
				$sem_begin = mktime(0, 0, 0, date("n",$val["vorles_beginn"]), date("j",$val["vorles_beginn"])+($term_data["start_woche"] * 7),  date("Y",$val["vorles_beginn"]));
				$sem_end = $val["vorles_ende"];
			}
	} else  {
		$dow = date("w", $term_data["start_termin"]);
		//calculate corrector to get first day of the week
		if ($dow <= 5)
			$corr = ($dow -1) * -1;
		elseif ($dow == 6)
			$corr = 2;
		elseif ($dow == 0)
			$corr = 1;
		else
			$corr = 0;
		
		$sem_begin = mktime(0, 0, 0, date("n",$term_data["start_termin"]), date("j",$term_data["start_termin"])+$corr,  date("Y",$term_data["start_termin"]));
		foreach ($SEMESTER as $val)
			if (($veranstaltung_start_time >= $val["beginn"]) AND ($veranstaltung_start_time <= $val["ende"])) {
				$sem_end = $val["vorles_ende"];
			}
	}

	//determine the last day as sem_end
	if ($full)
		foreach ($SEMESTER as $val)
			if  ((($veranstaltung_start_time + $veranstaltung_duration_time + 1) >= $val["beginn"]) AND (($veranstaltung_start_time + $veranstaltung_duration_time +1) <= $val["ende"]))
				$sem_end=$val["vorles_ende"];
	
	$interval = $term_data["turnus"] + 1;

	//create the dates
	$affected_dates=0;
	if (is_array($term_data["turnus_data"]))
		do {
			foreach ($term_data["turnus_data"] as $val) {
				$do = TRUE;

				//create new dates
				$start_time = mktime ($val["start_stunde"], $val["start_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1) + ($week * 7), date("Y", $sem_begin));
				$end_time = mktime ($val["end_stunde"], $val["end_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1) + ($week * 7), date("Y", $sem_begin));

				//check for HOLIDAY from config.inc.php. You should use it only for special holidays
				foreach ($HOLIDAY as $val2)
					if (($val2["beginn"] <= $start_time) && ($start_time <=$val2["ende"]))
						$do = FALSE;
		
				//check for calculatable holidays
				if ($do) {
					$holy_type = holiday($start_time);
					if ($holy_type == 3)
						$do = FALSE;
				}

				if (($do) && ($end_time <$sem_end)){
					//ids
					$date_id=md5(uniqid("lisa"));
					$folder_id=md5(uniqid("alexandra"));
					$aktuell=time();

					//if we have a resource_id, we take the room name from resource_id
					if (($val["resource_id"]) && ($RESOURCES_ENABLE))	
						$room = getResourceObjectName($val["resource_id"]);
					else
						$room = $val["room"];
						
					//create topic
					if (($topic) && (!$saved_dates[$affected_dates]))
						$topic_id=CreateTopic($TERMIN_TYP[$date_typ]["name"]." am ".date("d.m.Y", $start_time), $author, "Hier kann zu diesem Termin diskutiert werden", 0, 0, $sem_id);
		
					//create folder
					if (($folder) && (!$saved_dates[$affected_dates])) {
						$titel = sprintf ("%s am %s", $TERMIN_TYP[$date_typ]["name"], date("d.m.Y", $start_time));
						$description="Ablage f�r Ordner und Dokumente zu diesem Termin";
						$db2->query("INSERT INTO folder SET folder_id='$folder_id', range_id='$date_id', description='$description', user_id='$user->id', name='$titel', mkdate='$aktuell', chdate='$aktuell'");
					} else
						$folder_id='';
					
					//insert/update dates
					if ($saved_dates[$affected_dates]) 
						$query2 = "UPDATE termine SET autor_id='$user->id', date='$start_time', chdate='$aktuell', end_time='$end_time', raum='$room' WHERE termin_id = '".$saved_dates[$affected_dates]."' ";
					else
						$query2 = "INSERT INTO termine SET termin_id='$date_id', range_id='$sem_id', autor_id='$user->id', content='Kein Titel', date='$start_time', mkdate='$aktuell', chdate='$aktuell', date_typ='$date_typ', topic_id='$topic_id', end_time='$end_time', raum='$room' ";
					$db2->query($query2);
					if ($db2->affected_rows()) {
						//insert a entry for the linked resource, if resource management activ
						if ($RESOURCES_ENABLE) {
							if ($saved_dates[$affected_dates]) 
								$resources_result = array_merge($resources_result, $insertAssign->changeDateAssign($saved_dates[$affected_dates], $val["resource_id"]));
							else 
								$resources_result = array_merge($resources_result, $insertAssign->insertDateAssign($date_id, $val["resource_id"]));
						}
						$affected_dates++;
					}
					
					//update topic & folder
					if ($saved_dates[$affected_dates-1]) {
						//load topic- and folder_id
						$db->query("SELECT topic_id, content FROM termine WHERE termin_id = '".$saved_dates[$affected_dates-1]."' ");
						$db->next_record();
						
						//change topic
						$db2->query("UPDATE px_topics SET name='".$TERMIN_TYP[$date_typ]["name"].": ".$db->f("content")." am ".date("d.m.Y ", $start_time)."', author='$author', user_id='".$user->id."', chdate='$aktuell' WHERE topic_id='".$db->f("topic_id")." '");
					
						//change folder
						$titel = sprintf ("%s: %s am %s", $TERMIN_TYP[$date_typ]["name"], $db->f("content"), date("d.m.Y", $start_time));
						$db2->query("UPDATE folder SET user_id='$user->id', name='$titel', chdate='$aktuell' WHERE range_id = '".$saved_dates[$affected_dates-1]."' ");
					}
				}
			}
			//inc the week
			$week = $week + $interval;			
		} while ($end_time <$sem_end);

		//kill dates
		if (sizeof($saved_dates) >$affected_dates)
			for ($i=$affected_dates; $i<sizeof($saved_dates); $i++) {
				$query2 = "SELECT topic_id FROM termine WHERE termin_id = '".$saved_dates[$i]."' ";
				$db2->query($query2);
				$db2->next_record();
				delete_date ($saved_dates[$i], $db2->f("topic_id"), TRUE, $range_id);
			}

	$result_a["changed"]=$affected_dates;
	$result_a["resources_result"]=$resources_result;

	return ($result_a);
}

function isSchedule ($sem_id) {
	$db = new DB_Seminar;
	
	$query = sprintf ("SELECT metadata_dates FROM seminare WHERE Seminar_id = '%s'", $sem_id);
	
	$db->query($query);
	$db->next_record();
	
	$term_metadata=unserialize($db->f("metadata_dates"));

	//load the ids from already created dates
	if (is_array($term_metadata["turnus_data"])) {
		$i=0;
		$clause=" (";
		foreach ($term_metadata["turnus_data"] as $val) {
			if ($i)
				$clause.=" OR";

			if ($val["day"] == 7)
				$t_day = 1;
			else
				$t_day = $val["day"] + 1;
				
			$clause.="(";
			$clause.="DAYOFWEEK(FROM_UNIXTIME(date)) = '".$t_day."' AND ";
			$clause.="HOUR(FROM_UNIXTIME(date)) = '".$val["start_stunde"]."' AND ";
			$clause.="MINUTE(FROM_UNIXTIME(date)) = '".$val["start_minute"]."' AND ";
			$clause.="HOUR(FROM_UNIXTIME(end_time)) = '".$val["end_stunde"]."' AND ";
			$clause.="MINUTE(FROM_UNIXTIME(end_time)) = '".$val["end_minute"]."' ";
			$clause.=")";
			
			$i++;
		}
		$clause.=" )";
		
		$query = sprintf("SELECT termin_id FROM termine WHERE range_id='%s' AND %s ORDER BY date", $sem_id, $clause);
		$db->query($query);
		
		if ($db->num_rows())
			return TRUE;
		else
			return FALSE;
	}
}
?>
