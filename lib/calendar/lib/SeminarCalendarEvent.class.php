<?

/*
SeminarEvent.class.php
Klassen fuer Persoenlichen Terminkalender in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthienel@web.de>

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

//****************************************************************************

require_once $RELATIVE_PATH_CALENDAR . '/lib/CalendarEvent.class.php';


class SeminarCalendarEvent extends CalendarEvent {

	var $sem_id = "";
	var $sem_write_perm = FALSE;
	var $driver;
	
	function SeminarCalendarEvent ($properties = NULL, $id = '', $sem_id = "", $permission = NULL) {
		global $auth;
		
		if ($id && is_null($properties)) {
			$this->id = $id;
			$this->driver =& CalendarDriver::getInstance($auth->auth['uid']);
			// get event out of database...
			$this->restore();
		} elseif (!is_null($properties)) {
			parent::CalendarEvent($properties, $id, '', $permission);
		//	$this->id = $id;
			$this->sem_id = $sem_id;
		}
		
		$this->properties['UID'] = $this->getUid();
	}
	
	/**
	* Changes the category of this event.
	*
	* See config.inc.php for further information and values.<br>
	* <br>
	* After calling this method, the method isModified() returns TRUE.
	*
	* @access public
	* @param int $category a valid integer representation of a category (see 
	* config.inc.php)
	* @return boolean TRUE if the value of $category is valid, otherwise FALSE
	*/
/*	function setCategory ($category) {
		global $TERMIN_TYP;
		
		if(is_array($TERMIN_TYP[$category])){
			$this->properties['STUDIP_CATEGORY'] = $category;
			$this->chng_flag = TRUE;
			return TRUE;
		}
		return FALSE;
	}
	*/
	
	/**
	* Returns the name of the category.
	*
	* @access public
	* @return String the name of the category
	*/
	/*function toStringCategories () {
		global $TERMIN_TYP;
		
		return $TERMIN_TYP[$this->getProperty('STUDIP_CATEGORY') - 1]['name'];
	}
	*/
	// public
	function getSeminarId () {
		return $this->sem_id;
	}
	/*
	// public
	function createRepeat () {
		$rep = array('ts' => 0, 'linterval' => 0, 'sinterval' => 0, 'wdays' => '',
				'month' => 0, 'day' => 0, 'rtype' => 'SINGLE', 'duration' => 1);
		return $rep;
	}
	*/
	/*
	function getRepeat ($index = '') {
		if (!is_array($this->properties['RRULE']))
			$this->properties['UID'] = SeminarEvent::createRepeat();
		
		return $index ? $this->properties['RRULE'][$index] : $this->properties['RRULE'];
	}
	*/
	// public
	function setSeminarId ($id) {
		$this->sem_id = $id;
	}
	
	function restore ($id = '') {
		global $auth;
		
		if ($id == '')
			$id = $this->id;
		else
			$this->id = $id;
		
		if (!is_object($this->driver)) {
			$this->driver = CalendarDriver::getInstance($auth->auth['uid']);
		}
			
		$this->driver->openDatabaseGetSingleObject($id, 'SEMINAR_CALENDAR_EVENTS');
		
		if (!$event =& $this->driver->nextObject()) {
			return FALSE;
		}
		
		$this->properties = $event->properties;
		$this->id = $event->id;
		$this->sem_id = $event->sem_id;
		$this->sem_write_perm = $event->sem_write_perm;
		
		return TRUE;
	}
		
		/*
		if ($id == "")
			$id = $this->id;
		else
			$this->id = $id;
		$db =& new DB_Seminar();
		$query = "SELECT t.*, su.*, s.Seminar_id, s.Name "
						.	"FROM termine t LEFT JOIN seminar_user su ON (t.range_id=su.Seminar_id) "
						. "LEFT JOIN seminare s USING(Seminar_id) WHERE t.termin_id='{$this->id}' "
						. "AND su.user_id='{$user->id}'";
		$db->query($query);
		if ($db->num_rows() == 1 && $db->next_record()) {
			$this->setProperty('SUMMARY',         $db->f('content'));
			$this->setProperty('DTSTART',         $db->f('date'));
			$this->setProperty('DTEND',           $db->f('end_time'));
			$this->setProperty('LOCATION',        $db->f('raum'));
			$this->setProperty('DESCRIPTION',     $db->f('description'));
			$this->setProperty('CLASS',           'PRIVATE');
			$this->setProperty('SEMNAME',         $db->f('Name'));
			$this->setProperty('UID',             $this->getUid($this->id));
			$this->setProperty('CREATED',         $db->f('mkdate'));
			$this->setProperty('LAST-MODIFIED',   $db->f('chdate'));
			$this->setProperty('RRULE',           $this->getRepeat());
			$this->setProperty('STUDIP_CATEGORY', $db->f('date_typ'));
			$this->sem_id   = $db->f('Seminar_id');
			if ($db->f('status') == 'tutor' || $db->f('status') == 'dozent')
				$this->setWritePermission(TRUE);
			
			return TRUE;
		}
		*/
	
	function getSemName () {
		return $this->properties["SEMNAME"];
	}
	
	function setSemName ($name) {
		$this->properties["SEMNAME"] = $name;
	}
	
	function getType () {
		return 1;
	}
	
	function getPermission () {
		switch ($GLOBALS['perm']->get_studip_perm($this->sem_id)) {
			case 'user' :
			case 'autor' :
				return CALENDAR_EVENT_PERM_READABLE;
			case 'tutor' :
			case 'dozent' :
			case 'admin' :
			case 'root' :
				return CALENDAR_EVENT_PERM_WRITABLE;
			default : 
				return CALENDAR_EVENT_PERM_FOBIDDEN;
		}
	}
	
	function havePermission ($permission) {
		return ($this->getPermission() >= $permission);
	}
	
	function setWritePermission ($perm) {
		$this->sem_write_perm = $perm;
	}
	
	function haveWritePermission () {
		return $this->sem_write_perm;
	}
	
	function getUid () {
		if ($this->properties['UID'] == '')
			$this->properties['UID'] = SeminarEvent::createUid($this->id);
			
		return $this->properties['UID'];
	}
	
	// static
	function createUid ($sem_id) {
		return "Stud.IP-SEM-$sem_id-{$this->id}@{$_SERVER['SERVER_NAME']}";
	}
	/*
	function getCategory () {
		if ($this->permission == CALENDAR_EVENT_PERM_CONFIDENTIAL) {
			return 255;
		}
		
		return $this->properties['STUDIP_CATEGORY'];
	}
	*/
	/*
	function getCategoryStyle ($image_size = 'small') {
		global $TERMIN_TYP, $CANONICAL_RELATIVE_PATH_STUDIP; $PERS_TERMIN_KAT;
		
		$index = $this->getCategory();
		if ($index == 255) {
			return array('image' => $image_size == 'small' ?
				"{$CANONICAL_RELATIVE_PATH_STUDIP}calendar/pictures/category{$index}_small.jpg" :
				"{$CANONICAL_RELATIVE_PATH_STUDIP}calendar/pictures/category{$index}.jpg",
				'color' => $PERS_TERMIN_KAT[$index]['color']);
		}
		
		return array('image' => $image_size == 'small' ?
					"{$CANONICAL_RELATIVE_PATH_STUDIP}calendar/pictures/category_sem"
							. ($index - 1) . "_small.jpg" :
					"{$CANONICAL_RELATIVE_PATH_STUDIP}calendar/pictures/category_sem"
					. ($index - 1) . ".jpg",
					'color' => $TERMIN_TYP[$index - 1]['color']);
	}
	*/
	
} // class SeminarEvent

?>
