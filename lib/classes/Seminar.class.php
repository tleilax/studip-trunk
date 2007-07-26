<?
/**
* Seminar.class.php
*
* the seminar main-class
*
*
* @author		Till Gl�ggler <tgloeggl@uni-osnabrueck.de>; Stefan Suchi <suchi@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		Seminar.class.php
* @package		raumzeit
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Seminar.class.php
// zentrale Veranstaltungsklasse
// Copyright (C) 2004 Cornelis Kater <kater@data-quest>, data-quest GmbH <info@data-quest.de>
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

require_once ('lib/functions.php');
require_once ('lib/admission.inc.php');
require_once ('lib/classes/Modules.class.php');
require_once ('lib/dates.inc.php');
require_once ('lib/raumzeit/MetaDate.class.php');
require_once ('lib/raumzeit/SeminarDB.class.php');
require_once ('lib/raumzeit/Issue.class.php');
require_once ('lib/raumzeit/SingleDate.class.php');
require_once ('lib/classes/SemesterData.class.php');
require_once ('lib/log_events.inc.php');
require_once ($GLOBALS['RELATIVE_PATH_RESOURCES'].'/lib/ResourceObject.class.php');

class Seminar {

	var $id = null;						// ID of the seminar
	var $issues = null;					// Array of Issue
	var $irregularSingleDates = null;	// Array of SingleDates
	var $metadate = null;				// MetaDate
	var $db;							// DB_Seminar
	var $db2;    						// unsere Datenbankverbindung
	var $messages = array();			// occured errors, infos, and warnings
	var $semester = null;
	var $filterStart = 0;
	var $filterEnd = 0;
	var $hasDatesOutOfDuration = -1;

	var $user_number = 0;

	function &GetInstance($id = false, $refresh_cache = false){

		static $seminar_object_pool;

		if ($id){
			if ($refresh_cache){
				$seminar_object_pool[$id] = null;
			}
			if (is_object($seminar_object_pool[$id]) && $seminar_object_pool[$id]->getId() == $id){
				return $seminar_object_pool[$id];
			} else {
				$seminar_object_pool[$id] = new Seminar($id);
				return $seminar_object_pool[$id];
			}
		} else {
			return new Seminar(false);
		}
	}
	/**
	* Constructor
	*
	* Pass nothing to create a seminar, or the seminar_id from an existing seminar to change or delete
	* @access	public
	* @param	string	$seminar_id	the seminar to be retrieved
	*/
	function Seminar($id = FALSE) {
		$this->db  = new DB_Seminar();
		$this->db2 = new DB_Seminar();
		$this->semester = new SemesterData();

		if ($id) {
			$this->id = $id;
			$this->restore();
		}
		if (!$this->id) {
			$this->id = $this->createId();
			$this->is_new = TRUE;
		}

	}

	function GetSemIdByDateId($date_id){
		$db = new DB_Seminar("SELECT range_id FROM termine WHERE termin_id = '$date_id'");
		$db->next_record();
		return $db->f(0);
	}

	/**
	*
	* creates an new id for this object
	* @access	private
	* @return	string	the unique id
	*/
	function createId() {
		return md5(uniqid("Seminar"));
	}

	function getMembers($status = 'dozent'){
		if (!isset($this->members[$status])){
			$this->restoreMembers($status);
		}
		return $this->members[$status];
	}

	function restoreMembers($status = 'dozent'){
		$this->members[$status] = array();
		$this->db->query("SELECT su.user_id,username,Vorname,Nachname,
						".$GLOBALS['_fullname_sql']['full']." as fullname
							FROM seminar_user su INNER JOIN auth_user_md5 USING(user_id)
							LEFT JOIN user_info USING(user_id)
							WHERE status='$status' AND su.seminar_id='".$this->getId()."' ORDER BY su.position");
		while($this->db->next_record()){
			$this->members[$status][$this->db->f('user_id')] = $this->db->Record;
		}
		return $this->db->num_rows();
	}

	function getId() {
		return $this->id;
	}

	function getName() {
		return $this->name;
	}

	function getInstitutId() {
		return $this->institut_id;
	}

	function getSemesterStartTime() {
		return $this->semester_start_time;
	}

	function getSemesterDurationTime() {
		return $this->semester_duration_time;
	}

	function getMetaDateType () {
		return $this->metadate->getArt();
	}

	function getSerializedMetadata() {
		return $this->metadate->getSerializedMetaData();
	}

	function formatDate($return_mode = 'string', $termin) {
		switch ($return_mode) {
			case 'int':
				return $termin->getStartTime();
				break;

			case 'export':
				$ret = $termin->toString();
				if ($termin->getResourceID()) {
					$ret .= ', '._("Raum:").' ';
					$resObj =& ResourceObject::Factory($termin->getResourceID());
					$ret .= $resObj->getName();
				}
				return $ret;
			break;

			case 'string':
			default:
				$ret = $termin->toString();
				if ($termin->getResourceID()) {
					$ret .= ', '._("Raum:").' ';
					$resObj =& ResourceObject::Factory($termin->getResourceID());
					$ret .= $resObj->getFormattedLink(TRUE, TRUE, TRUE);
				}
				return $ret;
			break;

		}
		return FALSE;
	}

	function getNextDate($return_mode = 'string') {
		if ($return_mode == 'int') {
			echo __class__.'::'.__function__.', line '.__line__.', return_mode "int" ist not supported by this function!';die;
		}

		if (!$termine = SeminarDB::getNextDate($this->id)) return FALSE;
		if ($termine['termin']) {
			$termin = new SingleDate($termine['termin']);
			$next_date = $this->formatDate($return_mode, $termin);
		}
		if ($termine['ex_termin']) {
			$ex_termin = new SingleDate($termine['ex_termin']);
			$missing_date  = '<div style="{border:1px solid black;background:#FFFFDD}">';
			$missing_date .= sprintf(_("Der Termin am %s findet nicht statt."), $this->formatDate($return_mode, $ex_termin));
			$missing_date .= '<br/>Kommentar: '.$ex_termin->getComment();
			$missing_date .= '</div>';
			if ($termine['termin']) {
				if ($ex_termin->getStartTime() < $termin->getStartTime()) {
					return $next_date.'<br/>'.$missing_date;
				}
			} else {
				return $missing_date;
			}
		} else {
			return $next_date;
		}

		return false;
	}

	function getFirstDate($return_mode = 'string') {
		if (!$termin_id = SeminarDB::getFirstDate($this->id)) return FALSE;
		$termin = new SingleDate($termin_id);
		return $this->formatDate($return_mode, $termin);
	}

	function getUndecoratedData() {
		$cycles = $this->metadate->getCycleData();
		$dates = $this->getSingleDates();
		// besser wieder mit direktem Query statt Objekten
		if (is_array($cycles) && (sizeof($cycles) == 0)) {
			$cycles = FALSE;
		}

		$ret['regular']['turnus_data'] = $cycles;
		$ret['regular']['art'] = $this->metadate->art;
		$ret['regular']['start_woche'] = $this->metadate->start_woche;
		$ret['regular']['turnus'] = $this->metadate->turnus;

		foreach ($dates as $val) {
			$zw = array(
					'metadate_id' => $val->getMetaDateID(),
					'termin_id' => $val->getTerminID(),
					'date_typ' => $val->getDateType(),
					'start_time' => $val->getStartTime(),
					'end_time' => $val->getEndTime(),
					'mkdate' => $val->getMkDate(),
					'chdate' => $val-> getMkDate(),
					'ex_termin' => $val->isExTermin(),
					'orig_ex' => $val->isExTermin(),
					'range_id' => $val->getRangeID(),
					'author_id' => $val->getAuthorID(),
					'resource_id' => $val->getResourceID(),
					'raum' => $val->getFreeRoomText(),
					'typ' => $val->getDateType()
				);

			$ret['irregular'][$val->getTerminID()] = $zw;
		}
		return $ret;
	}

	function getFormattedTurnus($short = FALSE) {
		// activate this with StEP 00077
		/* $cache = Cache::instance();
		 * $cache_key = "formatted_turnus".$this->id;
		 * if (! $return_string = $cache->read($cache_key))
		 * {
		 */
		$turnus = $this->metadate->cycles;
		$irregular = $this->getSingleDates();
		// TODO: use one query instead of objectS

		if (is_array($turnus) && (sizeof($turnus) == 0)) {
			$turnus = FALSE;
		}

		if (!$turnus && !$irregular) {
			return _("Die Zeiten der Veranstaltung stehen nicht fest.");
		}

		if ($turnus) {
			$first = TRUE;
			foreach ($turnus as $val) {
				$return_string .= (($first) ? '' : ', ') . $val->toString($short);
				$first = FALSE;
			}
			if ($this->getTurnus() == 1) {
				$return_string.= " " . _("(zweiw�chentlich)");
			}
		}

		if ($irregular) {
			foreach ($irregular as $sid => $termin) {
				foreach (getPresenceTypes() as $tp) {
					if ($termin->getDateType() == $tp) {
						$dates[] = array('start_time' => $termin->getStartTime(), 'end_time' => $termin->getEndTime(), 'conjuncted' => FALSE, 'time_match' => FALSE);
					}
				}
			}

			if (sizeof($dates) > 0) {
				if ($turnus) {
					$return_string .= ', ';
				}
				$return_string .= _("Termine am"). " ";
			}
			
			$return_string .= join('', shrink_dates($dates));
		}
		// activate this with StEP 00077
		// $cache->write($cache_key, $return_string, 60*60);
		// }
		return $return_string;
	}

	function getFormattedTurnusDates($short = FALSE) {
		if ($cycles = $this->metadate->getCycleData()) {
			foreach ($cycles as $key=>$val) {
				if ($short)
					switch ($val["day"]) {
						case "0": $return_string[$key]= _("So."); break;
						case "1": $return_string[$key]= _("Mo."); break;
						case "2": $return_string[$key]= _("Di."); break;
						case "3": $return_string[$key]= _("Mi."); break;
						case "4": $return_string[$key]= _("Do."); break;
						case "5": $return_string[$key]= _("Fr."); break;
						case "6": $return_string[$key]= _("Sa."); break;
					}
				else
					switch ($val["day"]) {
						case "0": $return_string[$key]= _("Sonntag"); break;
						case "1": $return_string[$key]= _("Montag"); break;
						case "2": $return_string[$key]= _("Dienstag"); break;
						case "3": $return_string[$key]= _("Mittwoch"); break;
						case "4": $return_string[$key]= _("Donnerstag"); break;
						case "5": $return_string[$key]= _("Freitag"); break;
						case "6": $return_string[$key]= _("Samstag"); break;
					}
				$return_string[$key].=", ".$val["start_hour"].":";

				$return_string[$key] .= leadingZero($val['start_minute']);

				if (!(($val["end_hour"] == $val["start_hour"]) && ($val["end_minute"] == $val["start_minute"]))) {
					$return_string[$key].=" - ".$val["end_hour"].":";

					$return_string[$key] .= leadingZero($val['end_minute']);
				}
				if ($val['desc']){
					$return_string[$key].= ' ('. htmlReady($val['desc']) .')';
				}
			}
			return $return_string;
		} else
			return FALSE;
	}

	function getMetaDateCount() {
		return sizeof($this->metadate->cycles);
	}

	/* depreceated */
	function getMetaDates() {
		if ($this->metadate) {
			return $this->metadate->getCycleData();
		} else {
			return FALSE;
		}
	}

	function getMetaDatesKey($begin, $end){
		$ret = null;
		$day_of_week = date("w", $begin);
		$day_of_week = ($day_of_week == 0 ? 7 : $day_of_week);
		if (is_array($meta_dates = $this->getMetaDates())){
			foreach($meta_dates as $key => $value){
				if (($value['day'] == $day_of_week)
				&& ($value['start_hour'] == date('G', $begin))
				&& ($value['start_minute'] == date('i', $begin))
				&& ($value['end_hour'] == date('G', $end))
				&& ($value['end_minute'] == date('i', $end))){
					$ret = $key;
					break;
				}
			}
		}
		return $ret;
	}

	function getMetaDateValue($key, $value_name) {
		return $this->metadate->cycles[$key]->$value_name;
	}

	/* depreceated */
	function setMetaDateValue($key, $value_name, $value) {
		$this->metadate->cycles[$key]->$value_name = $value;
	}

	/**
	* restore the data
	*
	* the complete data of the object will be loaded from the db
	* @access	public
	* @return	boolean	succesfull restore?
	*/
	function restore() {

		$this->irregularSingleDates = null;
		$this->issues = null;

		$query = sprintf("SELECT * FROM seminare WHERE Seminar_id='%s' ",$this->id);
		$this->db->query($query);
		if ($this->db->num_rows() == 0) {
			echo 'Fehler: Konnte das Seminar mit der ID '.$this->id.' nicht finden!<br/>';
			die;
		}

		if ($this->db->next_record()) {
			$this->seminar_number = $this->db->f("VeranstaltungsNummer");
			$this->institut_id = $this->db->f("Institut_id");
			$this->name = $this->db->f("Name");
			$this->subtitle = $this->db->f("Untertitel");
			$this->status = $this->db->f("status");
			$this->description = $this->db->f("Beschreibung");
			$this->location = $this->db->f("Ort");
			$this->misc = $this->db->f("Sonstiges");
			$this->password = $this->db->f("Passwort");
			$this->read_level = $this->db->f("Lesezugriff");
			$this->write_level = $this->db->f("Schreibzugriff");
			$this->semester_start_time = $this->db->f("start_time");
			$this->semester_duration_time = $this->db->f("duration_time");
			$this->form = $this->db->f("art");
			$this->participants = $this->db->f("teilnehmer");
			$this->requirements = $this->db->f("vorrausetzungen");
			$this->orga = $this->db->f("lernorga");
			$this->leistungsnachweis = $this->db->f("leistungsnachweis");

			$this->metadate = new MetaDate();
			$this->metadate->createMetaDateFromArray(unserialize($this->db->f('metadata_dates')));
			$this->metadate->setSeminarStartTime($this->db->f('start_time'));
			$this->metadate->setSeminarDurationTime($this->db->f('duration_time'));
			$this->metadate->seminar_id = $this->id;

			$this->mkdate = $this->db->f("mkdate");
			$this->chdate = $this->db->f("chdate");
			$this->ects = $this->db->f("ects");
			$this->admission_endtime = $this->db->f("admission_endtime");
			$this->admission_turnout = $this->db->f("admission_turnout");
			$this->admission_binding = $this->db->f("admission_binding");
			$this->admission_type = $this->db->f("admission_type");
			$this->admission_selection_take_place = $this->db->f("admission_selection_take_place");
			$this->admission_group = $this->db->f("admission_group");
			$this->admission_prelim = $this->db->f("admission_prelim");
			$this->admission_prelim_txt = $this->db->f("admission_prelim_txt");
			$this->admission_starttime = $this->db->f("admission_starttime");
			$this->admission_endtime_sem = $this->db->f("admission_endtime_sem");
			$this->visible = $this->db->f("visible");
			$this->showscore = $this->db->f("showscore");
			$this->modules = $this->db->f("modules");
			$this->is_new = false;
			$this->members = array();
			return TRUE;

		}
		return FALSE;
	}

	function store() {

		// activate this with StEP 00077
		// $cache = Cache::instance();
		// $cache->expire("formatted_turnus".$this->id);

    //check for security consistency
		if ($this->read_level < $this->write_level) // hier wusste ein Dozent nicht, was er tat
			$this->write_level = $this->read_level;

		if ($this->irregularSingleDates) {
			foreach ($this->irregularSingleDates as $val) {
				$val->store();
			}
		}

		if ($this->issues) {
			foreach ($this->issues as $val) {
				$val->store();
			}
		}

		if ($this->is_new) {
			$query = "INSERT INTO seminare SET
				Seminar_id = '".			$this->id."',
				VeranstaltungsNummer = '".		mysql_escape_string($this->seminar_number)."',
				Institut_id = '".			$this->institut_id."',
				Name = '".				mysql_escape_string($this->name)."',
				Untertitel = '".			mysql_escape_string($this->subtitle)."',
				status = '".				$this->status."',
				Beschreibung = '".			mysql_escape_string($this->description)."',
				Ort = '".				mysql_escape_string($this->location)."',
				Sonstiges = '".				mysql_escape_string($this->misc)."',
				Passwort= '".				$this->password."',
				Lesezugriff = '".			$this->read_level."',
				Schreibzugriff = '".			$this->write_level."',
				start_time = '".			$this->semester_start_time."',
				duration_time = '".			$this->semester_duration_time."',
				art = '".				mysql_escape_string($this->form)."',
				teilnehmer = '".			mysql_escape_string($this->participants)."',
				vorrausetzungen = '".			mysql_escape_string($this->requirements)."',
				lernorga = '".				mysql_escape_string($this->orga)."',
				leistungsnachweis = '".			mysql_escape_string($this->leistungsnachweis)."',
				metadata_dates= '".			mysql_escape_string($this->getSerializedMetaData())."',
				mkdate = '".				time()."',
				chdate = '".				time()."',
				ects = '".				mysql_escape_string($this->ects)."',
				admission_endtime = '".			$this->admission_endtime."',
				admission_turnout = '".			$this->admission_turnout."',
				admission_binding = 			NULL ,
				admission_type = '".			$this->admission_type."',
				admission_selection_take_place = 	'0',
				admission_group = 			NULL ,
				admission_prelim = '".			$this->admission_prelim."',
				admission_prelim_txt = '".		mysql_escape_string($this->admission_prelim_txt)."',
				admission_starttime = '".		$this->admission_starttime."',
				admission_endtime_sem = '".		$this->admission_endtime_sem."',
				visible =  				'".		$this->visible."',
				showscore =				'0',
				modules = NULL";

			//write the default module-config
			$Modules = new Modules;
			$Modules->writeDefaultStatus($this->id);
		} else {
			$query = "UPDATE seminare SET
				VeranstaltungsNummer = '".		mysql_escape_string($this->seminar_number)."',
				Institut_id = '".			$this->institut_id."',
				Name = '".				mysql_escape_string($this->name)."',
				Untertitel = '".			mysql_escape_string($this->subtitle)."',
				status = '".				$this->status."',
				Beschreibung = '".			mysql_escape_string($this->description)."',
				Ort = '".				mysql_escape_string($this->location)."',
				Sonstiges = '".				mysql_escape_string($this->misc)."',
				Passwort= '".				$this->password."',
				Lesezugriff = '".			$this->read_level."',
				Schreibzugriff = '".			$this->write_level."',
				start_time = '".			$this->semester_start_time."',
				duration_time = '".			$this->semester_duration_time."',
				art = '".				mysql_escape_string($this->form)."',
				teilnehmer = '".			mysql_escape_string($this->participants)."',
				vorrausetzungen = '".			mysql_escape_string($this->requirements)."',
				lernorga = '".				mysql_escape_string($this->orga)."',
				leistungsnachweis = '".			mysql_escape_string($this->leistungsnachweis)."',
				metadata_dates= '".			mysql_escape_string($this->getSerializedMetadata())."',
				ects = '".				mysql_escape_string($this->ects)."',
				admission_endtime = '".			$this->admission_endtime."',
				admission_turnout = '".			$this->admission_turnout."',
				admission_binding = '".			$this->admission_binding."',
				admission_type = '".			$this->admission_type."',
				admission_selection_take_place ='". 	$this->admission_selection_take_place."',
				admission_group = '".			$this->admission_group."' ,
				admission_prelim = '".			$this->admission_prelim."',
				admission_prelim_txt = '".		mysql_escape_string($this->admission_prelim_txt)."',
				admission_starttime = '".		$this->admission_starttime."',
				admission_endtime_sem = '".		$this->admission_endtime_sem."',
				visible = '". 				$this->visible."',
				showscore ='".				$this->showscore."',
				modules = ".(($this->modules == NULL) ? 'NULL' : "'".$this->modules."'")."
				WHERE Seminar_id = '".			$this->id."'";
		}
		$this->db->query($query);

		if ($this->db->affected_rows()) {
			$query = sprintf("UPDATE seminare SET chdate='%s' WHERE Seminar_id='%s' ", time(), $this->id);
			$this->db->query($query);
			return TRUE;
		} else
			return FALSE;
	}

	function setStartSemester($start) {
		global $perm;
		if ($perm->have_perm('tutor') && $start != $this->semester_start_time) {
			// logging >>>>>>
			log_event("SEM_SET_STARTSEMESTER", $this->getId(), $start);
			// logging <<<<<<
			$this->semester_start_time = $start;
			$this->metadate->setSeminarStartTime($start);
			SeminarDB::removeOutRangedSingleDates($this->semester_start_time, $this->getEndSemesterVorlesEnde(), $this->id);
			$this->createMessage(_("Das Startsemester wurde ge�ndert."));
			$this->createInfo(_("Beachten Sie, dass Termine, die nicht mit den Einstellungen der regelm��igen Zeit �bereinstimmen (z.B. auf Grund einer Verschiebung der regelm��igen Zeit), teilweise gel�scht sein k�nnten!"));
			foreach ($this->metadate->cycles as $key => $val) {
				$this->metadate->createSingleDates($key);
				$this->metadate->cycles[$key]->termine = NULL;
			}
			return TRUE;
		}
		return FALSE;
	}

	function getStartSemester() {
		return $this->semester_start_time;
	}

	/*
	 * setEndSemester
	 * @param	end	integer	0 (one Semester), -1 (eternal), or timestamp of last happening semester
	 * @returns	TRUE on success, FALSE on failure
	 */
	function setEndSemester($end) {
		global $perm;

		$previousEndSemester = $this->getEndSemester();		// save the end-semester before it is changed, so we can choose lateron in which semesters we need to be rebuilt the SingleDates

		if ($end != $this->getEndSemester()) {	// only change Duration if it differs from the current one
			if ($end > ($this->getStartSemester() + strtotime('+8 month', 0)) && !$perm->have_perm('admin')) {
				$this->createError(_("Nur Admin oder h�her kann die Dauer einer Veranstaltung auf l�nger als 2 Semester setzen!"));
				return FALSE;
			}

			if ($end == 0) {					// the seminar takes place just in the selected start-semester
				$this->semester_duration_time = 0;
				$this->metadate->setSeminarDurationTime(0);
				SeminarDB::removeOutRangedSingleDates($this->semester_start_time, $this->getEndSemesterVorlesEnde(), $this->id);
				// logging >>>>>>
				log_event("SEM_SET_ENDSEMESTER", $this->getId(), $end, 'Laufzeit: 1 Semester');
				// logging <<<<<<
			} else if ($end == -1) {	// the seminar takes place in every semester above and including the start-semester
				if ($perm->have_perm("admin")) {	// only admin or higher may choose eternal duration
					// logging >>>>>>
					log_event("SEM_SET_ENDSEMESTER", $this->getId(), $end, 'Laufzeit: unbegrenzt');
					// logging <<<<<<
					$this->semester_duration_time = -1;
					$this->metadate->setSeminarDurationTime(-1);
					SeminarDB::removeOutRangedSingleDates($this->semester_start_time, $this->getEndSemesterVorlesEnde(), $this->id);
				} else {
					$this->createError(_("Nur Admin oder h�her kann die Dauer einer Veranstaltung auf \"ungebrenzt\" setzen!"));
					return FALSE;
				}
			} else {									// the seminar takes place  between the selected start~ and end-semester
				// logging >>>>>>
				log_event("SEM_SET_ENDSEMESTER", $this->getId(), $end);
				// logging <<<<<<
				$this->semester_duration_time = $end - $this->semester_start_time;	// the duration is stored, not the real end-point
				$this->metadate->setSeminarDurationTime($this->semester_duration_time);
				SeminarDB::removeOutRangedSingleDates($this->semester_start_time, $this->getEndSemesterVorlesEnde(), $this->id);	// delete obsolete SingleDates
			}

			$this->createMessage(_("Die Dauer wurde ge�ndert."));

			/*
			 * If the duration has been changed, we have to create new SingleDates
			 * if the new duration is longer than the previous one
			 */
			if ( ($previousEndSemester != -1) && ( ($previousEndSemester < $this->getEndSemester()) || (($previousSemester == 0) && ($this->getEndSemester() == -1) ) )) {
				// if the previous duration was unlimited, the only option choosable is
				// a shorter duration then 'ever', so there cannot be any new SingleDates

				// special case: if the previous selection was 'one semester' and the new one is 'eternal',
				// than we have to find out the end of the only semester, the start-semester
				if ($previousEndSemester == 0) {
					$all_semester = $this->semester->getAllSemesterData();
					foreach ($all_semester as $val) {
						if ($val['beginn'] == $this->getStartSemester()) {
							$startAfterTimeStamp = $val['ende'];
							break;
						}
					}
				} else {
					$startAfterTimeStamp = $previousEndSemester;
				}

				foreach ($this->metadate->cycles as $key => $val) {
					$this->metadate->createSingleDates(array('metadate_id' => $key, 'startAfterTimeStamp' => $startAfterTimeStamp));
					$this->metadate->cycles[$key]->termine = NULL;	// emtpy the SingleDates for each cycle, so that SingleDates, which were not in the current view, are not loaded and therefore should not be visible
					$this->metadate->cycles[$key]->readSingleDates($this->filterStart, $this->filterEnd);	// load the SingleDates with the appropriate filter
				}
			}
		}

		return TRUE;
	}

	/*
	 * getEndSemester
	 * @returns	0 (one Semester), -1 (eternal), or TimeStamp of last Semester for this Seminar
	 */
	function getEndSemester() {
		if ($this->semester_duration_time == 0) return 0;										// seminar takes place only in the start-semester
		if ($this->semester_duration_time == -1) return -1;									// seminar takes place eternally
		return $this->semester_start_time + $this->semester_duration_time;	// seminar takes place between start~ and end-semester
	}

	function getEndSemesterVorlesEnde() {
		if ($this->semester_duration_time == 0) {
			$all_semester = $this->semester->getAllSemesterData();
			foreach ($all_semester as $val) {
				if ($val['beginn'] == $this->semester_start_time) {
					return $val['vorles_ende'];
				}
			}
		} else if ($this->semester_duration_time == -1) {
			$all_semester = $this->semester->getAllSemesterData();
			foreach ($all_semester as $val) {
				$ende = $val['vorles_ende'];
			}
			return $ende;
		} else {
			$ende = $this->semester_start_time + $this->semester_duration_time;
			$all_semester = $this->semester->getAllSemesterData();
			foreach ($all_semester as $val) {
				if (($ende >= $val['beginn']) && ($ende <= $val['ende'])) {
					return $val['vorles_ende'];
				}
			}
		}
	}

	function readSingleDatesForCycle($metadate_id){
		return $this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
	}

	function readSingleDates($force = FALSE, $filter = FALSE) {
		if (!$force) {
			if (is_array($this->irregularSingleDates)) {
				return TRUE;
			}
		}
		$this->irregularSingleDates = array();

		if ($filter) {
			$data = SeminarDB::getSingleDates($this->id, $this->filterStart, $this->filterEnd);
		} else {
			$data = SeminarDB::getSingleDates($this->id);
		}

		foreach ($data as $val) {
			unset($termin);
			$termin = new SingleDate($this->id);
			$termin->fillValuesFromArray($val);
			$this->irregularSingleDates[$val['termin_id']] =& $termin;
		}
	}

	function &getSingleDate($singleDateID, $cycle_id = '') {
		if ($cycle_id == '') {
			$this->readSingleDates();
			return $this->irregularSingleDates[$singleDateID];
		} else {
			$data =& $this->metadate->getSingleDates($cycle_id, $this->filterStart, $this->filterEnd);
			return $data[$singleDateID];
		}
	}

	function &getSingleDates($filter = FALSE) {
		$this->readSingleDates(FALSE, $filter);
		return $this->irregularSingleDates;
	}

	function &getSingleDatesForCycle($metadate_id) {
		if (!$this->metadate->cycles[$metadate_id]->termine) {
			$this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
			if (!$this->metadate->cycles[$metadate_id]->termine) {
				$this->readSingleDates();
				$this->metadate->createSingleDates($metadate_id, $this->irregularSingleDates);
				$this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
			}
			//$this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
		}

		return $this->metadate->getSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
	}

	function readIssues($force = false) {
		if (!is_array($this->issues) || $force) {
			$data = SeminarDB::getIssues($this->id);

			foreach ($data as $val) {
				unset($issue);
				$issue = new Issue();
				$issue->fillValuesFromArray($val);
				$this->issues[$val['issue_id']] =& $issue;
			}
		}
	}

	function addSingleDate(&$singledate) {
		// logging >>>>>>
		log_event("SEM_ADD_SINGLEDATE", $this->getId(), $singledate->toString(), 'SingleDateID: '.$singledate->getTerminID());
		// logging <<<<<<

		$this->readSingleDates();
		$this->irregularSingleDates[$singledate->getSingleDateID()] =& $singledate;
		return TRUE;
	}

	function addIssue(&$issue) {
		$this->readIssues();
		if (get_class($issue) != 'issue') {
			return FALSE;
		} else {
			$max = -1;
			if (is_array($this->issues)) foreach ($this->issues as $val) {
				if ($val->getPriority() > $max) {
					$max = $val->getPriority();
				}
			}
			$max++;
			$issue->setPriority($max);
			$this->issues[$issue->getIssueID()] =& $issue;
			return TRUE;
		}
	}

	function deleteSingleDate($date_id, $cycle_id = '') {
		$this->readSingleDates();
		// logging >>>>>>
		log_event("SEM_DELETE_SINGLEDATE",$date_id, $this->getId(), 'Cycle_id: '.$cycle_id);
		// logging <<<<<<
		if ($cycle_id == '') {
			$this->irregularSingleDates[$date_id]->setExTermin(true);
			$this->irregularSingleDates[$date_id]->store();
			unset ($this->irregularSingleDates[$date_id]);
			return TRUE;
		} else {
			$this->metadate->deleteSingleDate($cycle_id, $date_id, $this->filterStart, $this->filterEnd);
			return TRUE;
		}
	}

	function unDeleteSingleDate($date_id, $cycle_id = '') {
		// logging >>>>>>
		log_event("SEM_UNDELETE_SINGLEDATE",$date_id, $this->getId(), 'Cycle_id: '.$cycle_id);
		// logging <<<<<<
		if ($cycle_id == '') {
			$this->readSingleDates();
			$this->irregularSingleDates[$date_id]->setExTermin(false);
			return TRUE;
		} else {
			$this->metadate->unDeleteSingleDate($cycle_id, $date_id, $this->filterStart, $this->filterEnd);
			return TRUE;
		}
	}

	function getNextMessage() {
		if ($this->messages[0]) {
			$ret = $this->messages[0];
			unset ($this->messages[0]);
			sort($this->messages);
			return $ret;
		}
		return FALSE;
	}

	function createError($text) {
		$this->messages[] = 'error�'.$text.'�';
	}

	function createInfo($text) {
		$this->messages[] = 'info�'.$text.'�';
	}

	function createMessage($text) {
		$this->messages[] = 'msg�'.$text.'�';
	}

	function appendMessages($messages) {
		if (!is_array($messages)) return FALSE;
		$this->messages = array_merge($this->messages, $messages);
		return TRUE;
	}

	function addCycle($data = array()) {
		$new_id = $this->metadate->addCycle($data);
		// logging >>>>>>
		$cycle_info = $this->metadate->cycles[$new_id]->toString();
		log_event("SEM_ADD_CYCLE", $this->getId(), $cycle_info, '<pre>'.print_r($data,true).'</pre>');
		// logging <<<<<<
		return $new_id;
	}

	function editCycle($data = array()) {
		$cycle = $this->metadate->cycles[$data['cycle_id']];
		$new_start = mktime($data['start_stunde'], $data['start_minute']);
		$new_end = mktime($data['end_stunde'], $data['end_minute']);
		$old_start = mktime($cycle->getStartStunde(),$cycle->getStartMinute());
		$old_end = mktime($cycle->getEndStunde(), $cycle->getEndMinute());
		$do_changes = false;

		if (($new_start < $old_start) || ($new_end > $old_end) || ($data['day'] != $this->metadate->cycles[$data['cycle_id']]->day) ) {
			if (!$data['really_change']) {
				$link = 'raumzeit.php?editCycle_x=1&editCycle_y=1&cycle_id='.$data['cycle_id'].'&start_stunde='.$data['start_stunde'].'&start_minute='.$data['start_minute'].'&end_stunde='.$data['end_stunde'].'&end_minute='.$data['end_minute'].'&day='.$data['day'].'&really_change=true';
				$this->createQuestion(sprintf(_('Wenn Sie die regelm��ige Zeit auf %s �ndern, verlieren Sie die Raumbuchungen f�r alle in der Zukunft liegenden Termine!<BR/>Sind Sie sicher, dass die regelm��ige Zeit �ndern m�chten?'), '<B>'.getWeekday($data['day'], FALSE).', '.$data['start_stunde'].':'.$data['start_minute'].' - '.$data['end_stunde'].':'.$data['end_minute'].'</B>'),$link);
			} else {
				$do_changes = true;
			}
		} else {
			$do_changes = true;
		}

		$messages = false;
		$same_time = false;

		if ($data['description'] != $cycle->getDescription()) {
			$this->createMessage(_("Die Beschreibung des regelm��igen Eintrags wurde ge�ndert."));
			$message = true;
			$do_changes = true;
		}

		if ($old_start == $new_start && $old_end == $new_end) {
			$same_time = true;
		}

		if ($do_changes) {
			if ($this->metadate->editCycle($data)) {
				if (!$same_time) {
					// logging >>>>>>
					log_event("SEM_CHANGE_TURNUS", $this->getId(), $cycle->toString());
					// logging <<<<<<
					$this->createMessage(sprintf(_("Die regelm��ige Veranstaltungszeit \"%s\" wurde f�r alle in der Zukunft liegenden Termine ge�ndert!"), '<b>'.$cycle->toString().'</b>'));
					$message = true;
				}
			} else {
				if (!$same_time) {
					$this->createInfo(sprintf(_("Die regelm��ige Veranstaltungszeit \"%s\" wurde ge�ndert, jedoch gab es keine Termine die davon betroffen waren."), '<b>'.$cycle->toString().'</b>'));
					$message = true;
				}
			}
		}

		if (!$message) {
			$this->createInfo("Sie haben keine �nderungen vorgenommen!");
		}

	}

	function deleteCycle($cycle_id) {
		// logging >>>>>>
		$cycle_info = $this->metadate->cycles[$cycle_id]->toString();
		log_event("SEM_DELETE_CYCLE", $this->getId(), $cycle_info);
		// logging <<<<<<
		return $this->metadate->deleteCycle($cycle_id);
	}

	function setTurnus($turnus) {
		if ($this->metadate->getTurnus() != $turnus) {
			$this->metadate->setTurnus($turnus);
			foreach ($this->metadate->cycles as $key => $val) {
				$this->metadate->createSingleDates($key);
				$this->metadate->cycles[$key]->termine = null;
			}
			$this->createMessage(_("Der Turnus wurde ge�ndert."));
		}
		return TRUE;
	}

	function getTurnus() {
		return $this->metadate->getTurnus();
	}

	function bookRoomForSingleDate($singleDateID, $roomID, $cycle_id = '', $append_messages = true) {
		if ($roomID == '') {
			//$this->createError('Seminar::bookRoomForSingleDate: missing roomID!');
			return FALSE;
		}
		if ($roomID == 'nochange') return FALSE;
		if ($cycle_id != '') {	// SingleDate of an MetaDate
			$this->readSingleDatesForCycle($cycle_id, $this->filterStart, $this->filterEnd);	// Let the cycle-object read in all of his single dates

			if ($roomID == 'retreat' || $roomID == 'nothing') {	// remove room bookment
				if (isset($this->metadate->cycles[$cycle_id]->termine[$singleDateID])) {	// check, if the specified singleDate exists
					$this->metadate->cycles[$cycle_id]->termine[$singleDateID]->killAssign();	// delete bookment for this singledate
				} else {
					return FALSE;		// otherwise return FALSE, meaning : 'No Success'; optional could be placed an error message here
					//$this->createError(sprintf(_("Es existiert kein Termin mit der SingleDateID %s in der CycleDataID %s"), $singleDateID, $cycle_id));
				}
				return TRUE;
			}

			if (isset($this->metadate->cycles[$cycle_id]->termine[$singleDateID])) {
				if (!$this->metadate->cycles[$cycle_id]->termine[$singleDateID]->bookRoom($roomID)) {
					$this->appendMessages($this->metadate->cycles[$cycle_id]->termine[$singleDateID]->getMessages());
					return FALSE;
				}
				/*if ($append_messages)
					$this->appendMessages($this->metadate->cycles[$cycle_id]->termine[$singleDateID]->getMessages());*/
			}
		} else {	// an irregular SingleDate
			$this->readSingleDates();
			if ($roomID == 'retreat' || $roomID == 'nothing') {
				if (isset($this->irregularSingleDates[$singleDateID])) {
					$this->irregularSingleDates[$singleDateID]->killAssign();
				}
				return TRUE;
			}

			if (isset($this->irregularSingleDates[$singleDateID])) {
				if (!$this->irregularSingleDates[$singleDateID]->bookRoom($roomID)) {
					$this->appendMessages($this->irregularSingleDates[$singleDateID]->getMessages());
					return FALSE;
				}
				/*if ($append_messages)
					$this->appendMessages($this->irregularSingleDates[$singleDateID]->getMessages());*/
			}
		}
		return TRUE;
	}

	function getStatOfNotBookedRooms($cycle_id) {
		if (!isset($this->BookedRoomsStatTemp[$cycle_id])) {
			$this->BookedRoomsStatTemp[$cycle_id] = SeminarDB::getStatOfNotBookedRooms($cycle_id, $this->id, $this->filterStart, $this->filterEnd);
		}
		return $this->BookedRoomsStatTemp[$cycle_id];
		/* get StatOfNotBookedRooms returns an array:
		 * open:        number of rooms with no booking
		 * all:         number of singleDates, which can have a booking
		 * open_rooms:  array of singleDates which have no booking
		*/
	}

	function getBookedRoomsTooltip($cycle_id) {
		$stat = $this->getStatOfNotBookedRooms($cycle_id);

		if (($stat['open'] > 0) && ($stat['open'] == $stat['all'])) {
			//$return = _("Keiner der Termine hat eine Raumbuchung!");
			$return = '';
		} else if ($stat['open'] > 0) {
			$return = _("Folgende Termine haben keine Raumbuchung:").'\n\n';
			foreach ($stat['open_rooms'] as $aSingleDate) {
				$return .= getWeekday(date('w',$aSingleDate['date'])).', '.date('d.m.Y', $aSingleDate['date']).', '.date('H:i', $aSingleDate['date']).' - '.date('H:i', $aSingleDate['end_time']).'\n';
			}
		}

		return $return;
	}

	function getRequestsInfo($cycle_id) {
		$zahl =  SeminarDB::countRequestsForSingleDates($cycle_id, $this->id, $this->filterStart, $this->filterEnd);
		if ($zahl == 0) {
			return 'keine offen';
		} else {
			return $zahl.' noch offen';
		}
	}

	function getCycleColorClass($cycle_id) {
		$stat = $this->getStatOfNotBookedRooms($cycle_id);
		if ($GLOBALS['RESOURCES_ENABLE']) {
			if (!$this->metadate->hasDates($cycle_id, $this->filterStart, $this->filterEnd)) {
				$return = 'steelred';
			} else {
				if (($stat['open'] > 0) && ($stat['open'] == $stat['all'])) {
					$return = 'steelred';
				} else if ($stat['open'] > 0) {
					$return = 'steelgelb';
				} else {
					$return = 'steelgreen';
				}
			}
		} else {
			$return = 'printhead';
		}

		return $return;
	}

	function &getIssues($force = false) {
		$this->readIssues($force);
		$this->renumberIssuePrioritys();
		if (is_array($this->issues)) {
			uasort($this->issues, 'myIssueSort');
		}
		return $this->issues;
	}

	function deleteIssue($issue_id) {
		$this->issues[$issue_id]->delete();
		unset($this->issues[$issue_id]);
		return TRUE;
	}

	function &getIssue($issue_id) {
		$this->readIssues();
		return $this->issues[$issue_id];
	}

	/*
	 * changeIssuePriority
	 *
	 * changes an issue with an given id to a new priority
	 *
	 * @param
	 * issue_id				the issue_id of the issue to be changed
	 * new_priority		the new priority
	 */
	function changeIssuePriority($issue_id, $new_priority) {
		/* REMARK:
		 * This function only works, when an issue is moved ONE slote higher or lower
		 * It does NOT work with ARBITRARY movements!
		 */
		$this->readIssues();
		$old_priority = $this->issues[$issue_id]->getPriority();	// get old priority, so we can just exchange prioritys of two issues
		foreach ($this->issues as $id => $issue) {								// search for the concuring issue
			if ($issue->getPriority() == $new_priority) {
				$this->issues[$id]->setPriority($old_priority);				// the concuring issue gets the old id of the changed issue
				$this->issues[$id]->store();													// ###store_problem###
			}
		}

		$this->issues[$issue_id]->setPriority($new_priority);			// changed issue gets the new priority
		$this->issues[$issue_id]->store();												// ###store_problem###

	}

	function renumberIssuePrioritys() {
		if (is_array($this->issues)) {
			$sorter = array();
			foreach ($this->issues as $id => $issue) {
				$sorter[$id] = $issue->getPriority();
			}
			asort($sorter);
			$i = 0;
			foreach ($sorter as $id => $old_priority) {
				$this->issues[$id]->setPriority($i);
				$i++;
			}
		}
	}

	function autoAssignIssues($themen, $cycle_id) {
		$this->metadate->cycles[$cycle_id]->autoAssignIssues($themen, $this->filterStart, $this->filterEnd);
	}

	function hasRoomRequest() {
		if (!$this->request_id) {
			$this->request_id = getSeminarRoomRequest($this->id);
			if (!$this->request_id) return FALSE;

			$rD =& new RoomRequest($this->request_id);
			if ($rD->getClosed() != 0) {
				return FALSE;
			}
		}

		return TRUE;
	}

	function applyTimeFilter($start, $end) {
		$this->filterStart = $start;
		$this->filterEnd = $end;
	}

	function createQuestion($question, $approvalCmd) {
		$msg = $question;
		$msg .= "<br/><br/><a href=\"$approvalCmd\">";
		$msg .= '<img '.makebutton('ja2', 'src').' border="0"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$msg .= "<a href=\"$PHP_SELF?\">";
		$msg .= '<img '.makebutton('nein', 'src').' border="0"></a>';

		$this->createInfo($msg);
	}

	function registerCommand($command, $function) {
		$this->commands[$command] = $function;
	}

	function processCommands() {
		global $_REQUEST, $_LOCKED, $cmd;

		if (!isset($cmd) && isset($_REQUEST['cmd'])) $cmd = $_REQUEST['cmd'];
		if (!isset($cmd)) return FALSE;

		if ($_LOCKED) {
			if (($cmd == 'open') || ($cmd == 'close')) {
				if (isset($this->commands[$cmd])) {
					call_user_func($this->commands[$cmd]);
				}
			}
		} else {
			if (isset($this->commands[$cmd])) {
				call_user_func($this->commands[$cmd]);
			}
		}
	}

	function getFreeTextPredominantRoom($cycle_id) {
		if (!($room = $this->metadate->cycles[$cycle_id]->getFreeTextPredominantRoom($this->filterStart, $this->filterEnd))) {
			return FALSE;
		}
		return $room;
	}

	function getPredominantRoom($cycle_id, $list = FALSE) {
		if (!($rooms = $this->metadate->cycles[$cycle_id]->getPredominantRoom($this->filterStart, $this->filterEnd))) {
			return FALSE;
		}
		if ($list) {
			return $rooms;
		} else {
			return $rooms[0];
		}
	}

	function getFormattedPredominantRooms($cycle_id, $link = true, $show = 3) {
		if (!($rooms = $this->metadate->cycles[$cycle_id]->getPredominantRoom($this->filterStart, $this->filterEnd))) {
			return FALSE;
		}

		$roominfo = '';

		foreach ($rooms as $key => $val) {
			// get string-representation of predominant booked rooms
			if ($key >= $show) {
				if ($show > 1) {
					$roominfo .= ', '.sprintf(_("und %s weitere"), (sizeof($rooms)-$show));
				}
				break;
			} else {
				if ($key > 0) {
					$roominfo .= ', ';
				}
				$resObj =& ResourceObject::Factory($val);
				if ($link) {
					$roominfo .= $resObj->getFormattedLink(TRUE, TRUE, TRUE);
				} else {
					$roominfo .= $resObj->getName();
				}
				unset($resObj);
			}
		}
		return $roominfo;
	}

	function checkFilter() {
		global $raumzeitFilter, $cmd, $_REQUEST, $semester;
		if (isset($cmd) && ($cmd == 'applyFilter')) {
			$raumzeitFilter = $_REQUEST['newFilter'];
		}

		if ($this->getEndSemester() == 0 && !$this->hasDatesOutOfDuration()) {
			$raumzeitFilter = $this->getStartSemester();
		}

		/* Zeitfilter anwenden */
		if ($raumzeitFilter == '') {
			$raumzeitFilter = $semester->getCurrentSemesterData();
			$raumzeitFilter = $raumzeitFilter['beginn'];
		}

		if ($raumzeitFilter != 'all') {
			if (($raumzeitFilter < $this->getStartSemester()) || ($raumzeitFilter > $this->getEndSemesterVorlesEnde())) {
				$raumzeitFilter = $this->getStartSemester();
			}
			$filterSemester = $semester->getSemesterDataByDate($raumzeitFilter);
			$this->applyTimeFilter($filterSemester['beginn'], $filterSemester['ende']);
		}

	}

	function removeRequest($singledate_id,  $cycle_id = '') {
		if ($cycle_id == '') {
			$this->irregularSingleDates[$singledate_id]->removeRequest();
		} else {
			$this->metadate->cycles[$cycle_id]->removeRequest($singledate_id, $this->filterStart, $this->filterEnd);
		}
		$this->createMessage(_("Die Raumanfrage wurde zur&uuml;ckgezogen!"));
		return TRUE;
	}

	function hasDatesOutOfDuration() {
		if ($this->hasDatesOutOfDuration == -1) {
			$this->hasDatesOutOfDuration = SeminarDB::hasDatesOutOfDuration($this->getStartSemester(), $this->getEndSemesterVorlesEnde(), $this->id);
		}
		return $this->hasDatesOutOfDuration;
	}

	function getStartWeek() {
		return $this->metadate->getStartWoche();
	}

	function setStartWeek($week) {
		if ($this->metadate->getStartWoche() == $week) {
			return FALSE;
		} else {
			$this->metadate->setStartWoche($week);
			$this->createMessage(_("Die Startwoche wurde ge&auml;ndert."));
			foreach ($this->metadate->cycles as $key => $val) {
				$this->metadate->createSingleDates($key);
				$this->metadate->cycles[$key]->termine = null;
			}
		}
	}

	// Funktion fuer die Ressourcenverwaltung
	function getGroupedDates($singledate = '') {
		$i = 0;
		$first_event = FALSE;
		$semesterData = new SemesterData();
		$all_semester = $semesterData->getAllSemesterData();

		if ($GLOBALS['RESOURCES_HIDE_PAST_SINGLE_DATES']) {
			// filtering
			foreach ($all_semester as $semester) {
				if ($semester['ende'] > time()) {
					$new_as[] = $semester;
				}
			}
			$all_semester = $new_as;
		}

		if (!$singledate) {
			foreach ($all_semester as $semester) {
				foreach ($this->metadate->cycles as $metadate_id => $cycle) {
					$group = $cycle->getSingleDates();
					$metadate_has_termine = 0;
					$single = true;
					foreach ($group as $termin) {
						if (!$termin->isExTermin() && $termin->getStartTime() >= $semester['beginn'] && $termin->getStartTime() <= $semester['ende'] && (!$GLOBALS['RESOURCES_HIDE_PAST_SINGLE_DATES'] || $termin->getStartTime() >= time())) {
							if (empty($first_event)) {
								$first_event = $termin->getStartTime();
							}
							$groups[$i]["termin_ids"][$termin->getSingleDateId()] = TRUE;
							$metadate_has_termine = 1;

							if (empty($info[$i]['raum'])) {
								$info[$i]['raum'] = $termin->resource_id;
							} else if ($info[$i]['raum'] != $termin->resource_id) {
								$single = false;
							}
						}
					}
					if ($metadate_has_termine) {
						$info[$i]['name'] = $cycle->toString().' ('.$semester['name'].')';
						$this->applyTimeFilter($semester['beginn'], $semester['ende']);
						$raum = $this->getFormattedPredominantRooms($metadate_id);
						if ($raum) {
							$info[$i]['name'] .= '<BR/>&nbsp;&nbsp;&nbsp;&nbsp;'.$raum;
						}
						if (!$single) unset($info[$i]['raum']);
						$i++;
					}
				}
			}

			$irreg = $this->getSingleDates();

			if ($GLOBALS['RESOURCES_HIDE_PAST_SINGLE_DATES']) {
				$anzahl = 0;
				foreach ($irreg as $termin_id => $termin) {
					if ($termin->getStartTime() > (time() - 3600)) {
						$anzahl++;
					}
				}
			} else {
				$anzahl = sizeof($irreg);
			}

			if ($anzahl > $GLOBALS["RESOURCES_ALLOW_SINGLE_DATE_GROUPING"]) {
				$single = true;
				$first = true;
				foreach ($irreg as $termin_id => $termin) {
					if (!$GLOBALS['RESOURCES_HIDE_PAST_SINGLE_DATES'] ||  $termin->getStartTime() > (time() - 3600)) {
						if (empty($first_event)) {
							$first_event = $termin->getStartTime();
						}
						$groups[$i]["termin_ids"][$termin->getSingleDateId()] = TRUE;
						if (!$first) $info[$i]['name'] .= '<BR/>&nbsp;&nbsp;&nbsp;&nbsp;';
						$info[$i]['name'] .= $termin->toString();
						$resObj =& ResourceObject::Factory($termin->resource_id);

						if ($link = $resObj->getFormattedLink($termin->getStartTime())) {
							$info[$i]['name'] .= '<BR/>&nbsp;&nbsp;&nbsp;&nbsp;'.$link;
							if (empty($info[$i]['raum'])) {
								$info[$i]['raum'] = $termin->resource_id;
							} else if ($info[$i]['raum'] != $termin->resource_id) {
								$single = false;
							}
						}
						$first = false;
					}
				}
				if (!$single) unset($info[$i]['raum']);
			} else {
				foreach ($irreg as $termin_id => $termin) {
					if (!$GLOBALS['RESOURCES_HIDE_PAST_SINGLE_DATES'] ||  $termin->getStartTime() > (time() - 3600)) {
						if (empty($first_event)) {
							$first_event = $termin->getStartTime();
						}
						$groups[$i]["termin_ids"][$termin->getSingleDateId()] = TRUE;
						$info[$i]['name'] = $termin->toString();
						$resObj =& ResourceObject::Factory($termin->resource_id);

						if ($link = $resObj->getFormattedLink($termin->getStartTime())) {
							$info[$i]['name'] .= '<BR/>&nbsp;&nbsp;&nbsp;&nbsp;'.$link;
							$info[$i]['raum'] = $termin->resource_id;
						}
						$i++;
					}
				}
			}
		} else {	// we have a single date
			$termin = new SingleDate($singledate);
			$groups[0]['termin_ids'][$termin->getSingleDateID()] = TRUE;
			$info[0]['name'] = $termin->toString();
			$info[0]['raum'] = $termin->resource_id;
			$first_event = $termin->getStartTime();
		}


		return array('first_event' => $first_event, 'groups' => $groups, 'info' => $info);
	}

	function getRoomRequestInfo() {
		if ($this->hasRoomRequest()) {
			if (!$this->requestData) {
				$rD =& new RoomRequest($this->request_id);
				$resObject =& ResourceObject::Factory($rD->resource_id);
				$this->requestData .= 'Raum: '.$resObject->getName().'\n';
				$this->requestData .= 'verantworlich: '.$resObject->getOwnerName().'\n\n';
				foreach ($rD->getProperties() as $val) {
					$this->requestData .= $val['name'].': ';
					if ($val['type'] == 'bool') {
						if ($val['state'] == 'on') {
							$this->requestData .= 'vorhanden\n';
						} else {
							$this->requestData .= 'nicht vorhanden\n';
						}
					} else {
						$this->requestData .= $val['state'].'\n';
					}
				}
				if  ($rD->getClosed() == 0) {
					$txt = _("Die Anfrage wurde noch nicht bearbeitet.");
				} else if ($rD->getClosed() == 3) {
					$txt = _("Die Anfrage wurde bearbeitet und abgelehnt.");
				} else {
					$txt = _("Die Anfrage wurde bearbeitet.");
				}

				$this->requestData .= '\nStatus: '.$txt.'\n';

				$this->requestData .= '\nNachricht an den Raumadministrator:\n';
				$this->requestData .= str_replace("\r", '', str_replace("\n", '\n', $rD->getComment()));

			}

			return $this->requestData;
		} else {
			return FALSE;
		}
	}

	function removeSeminarRequest() {
		// logging >>>>>>
		log_event("SEM_DELETE_REQUEST", $this->getId());
		// logging <<<<<<
		return SeminarDB::deleteRequest($this->id);
	}

	/**
	 * instance method
	 *
	 * returns number of participants for each usergroup in seminar,
	 * total, lecturers, tutors, authors, users
	 *
	 * @param string (optional) return count only for given usergroup
	 *
	 * @return array <description>
	 */

	function getNumberOfParticipants()
	{
		$args = func_get_args();
		array_unshift($args, $this->id);
		return call_user_func_array(array("Seminar", "getNumberOfParticipantsBySeminarId"), $args);
	}

	/**
	 * class method
	 *
	 * returns number of participants for each usergroup in given seminar,
	 * total, lecturers, tutors, authors, users
	 *
	 * @param string seminar_id
	 *
	 * @param string (optional) return count only for given usergroup
	 *
	 * @return array <description>
	 */

	function getNumberOfParticipantsBySeminarId($sem_id)
	{
		$params = func_get_args();

		// init vars
		$db1 = new DB_Seminar();
		$db2 = new DB_Seminar();
		$count = 0;
		$participant_count = array();

		$db1->query($query = " SELECT
				COUNT(Seminar_id) AS anzahl,
				COUNT(IF(status='dozent',Seminar_id,NULL)) AS anz_dozent,
				COUNT(IF(status='tutor',Seminar_id,NULL)) AS anz_tutor,
				COUNT(IF(status='autor',Seminar_id,NULL)) AS anz_autor,
				COUNT(IF(status='user',Seminar_id,NULL)) AS anz_user
				FROM seminar_user
				WHERE Seminar_id = '{$sem_id}'
				GROUP BY Seminar_id");

		$db1->next_record();

		$db2->query(" SELECT COUNT(*) as anzahl
				FROM admission_seminar_user
				WHERE seminar_id = '$sem_id'
				AND status = 'accepted'");

		$db2->next_record();

		if ($db1->f("anzahl")) $count += $db1->f("anzahl");
		if ($db2->f("anzahl")) $count += $db2->f("anzahl");

		$participant_count['total'] = $count ? $count : 0;
		$participant_count['lecturers'] = $db1->f('anz_dozent') ? (int)$db1->f('anz_dozent') : 0;
		$participant_count['tutors'] = $db1->f('anz_tutor') ? (int)$db1->f('anz_tutor') : 0;
		$participant_count['authors'] = $db1->f('anz_autor') ? (int)$db1->f('anz_autor') : 0;
		$participant_count['users'] = $db1->f('anz_user') ? (int)$db1->f('anz_user') : 0;

		// return specific parameter if
		if (sizeof($params) > 1)
		{
			if (in_array($params[1], array_keys($participant_count)))
			{
				return $participant_count[$params[1]];
			} else
			{
				trigger_error(get_class($this)."::__getParticipantInfos - unknown parameter requested");
			}
		}

		return $participant_count;
	}

}
?>
