<?

function event_get_description ($id) {
	global $user;
		
	$db =& new DB_Seminar;
	$query = sprintf("SELECT event_id, description FROM calendar_events WHERE event_id='%s'"
				. " AND range_id='%s'", $id, $user->id);
	$db->query($query);
	if($db->next_record())
		return $db->f("description");
	return FALSE;
}

function event_save (&$this) {
	// Natuerlich nur Speichern, wenn sich was geaendert hat
	// und es sich um einen persoenlichen Termin handelt
	if($this->isModified()){
		$db =& new DB_Seminar();
		
		$query = "REPLACE calendar_events (event_id,range_id,autor_id,uid,summary,description,"
		    	  . "start,end,class,categories,priority,location,ts,linterval,sinterval,wdays,"
						. "month,day,rtype,duration,expire,exceptions,mkdate,chdate) VALUES ";
		
		$query .= sprintf("('%s','%s','%s','%s','%s','%s',%s,%s,'%s','%s',%s,'%s',%s,%s,%s,
				'%s',%s,%s,'%s',%s,%s,'%s',%s,%s)",
				$this->getId(), $this->getUserId(), $this->getUserId(),
				$this->properties["UID"],
				$this->properties["SUMMARY"],
				$this->properties["DESCRIPTION"],
				$this->properties["DTSTART"],
				$this->properties["DTEND"],
				$this->properties["CLASS"],
				$this->properties["CATEGORIES"],
				$this->properties["PRIORITY"],
				$this->properties["LOCATION"],
				$this->properties["RRULE"]["ts"],
				$this->properties["RRULE"]["linterval"],
				$this->properties["RRULE"]["sinterval"],
				$this->properties["RRULE"]["wdays"],
				$this->properties["RRULE"]["month"],
				$this->properties["RRULE"]["day"],
				$this->properties["RRULE"]["rtype"],
				$this->properties["RRULE"]["duration"],
				$this->properties["RRULE"]["expire"],
				$this->properties["EXCEPTIONS"],
				$this->getMakeDate(), $this->getChangeDate());
		
	/*		sprintf("REPLACE termine (termin_id,range_id,autor_id,content,description,"
		         . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES"
			       . " ('%s','%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')",
						 $this->id, $this->user_id, $this->user_id,
						 $this->getProperty("SUMMARY"),
						 $this->getProperty("DESCRIPTION"),
						 $this->getProperty("DTSTART"),
						 $this->getProperty("DTEND"),
						 $mkdate, $chdate,
						 $this->getProperty("CLASS"),
						 $this->getProperty("EXPIRE"),
						 $this->getProperty("RRULE"),
						 $this->getProperty("CATEGORIES"),
						 $this->getProperty("PRIORITY"),
						 $this->getProperty("LOCATION"));
	*/	
		if($db->query($query)){
			$this->chng_flag = FALSE;
			return TRUE;
		}
		return FALSE;
	}
	return FALSE;
}

function event_delete ($event_id, $user_id) {
	$db = new DB_Seminar;
	$query = sprintf("DELETE FROM calendar_events WHERE event_id='%s' AND range_id='%s'", $event_id, $user_id);
	if($db->query($query))
		return TRUE;
	return FALSE;
}

function event_restore ($id, &$this) {
	$db =& new DB_Seminar();

	$query = sprintf("SELECT * FROM calendar_events "
									. "WHERE range_id='%s' AND event_id='%s'"
									, $this->getUserId(), $id);
	$db->query($query);
	
	if ($db->next_record()) {
		$this->setId($id);
		$this->setProperty("UID",         $db->f("uid"));
		$this->setProperty("SUMMARY",     $db->f("summary"));
		$this->setProperty("DTSTART",     $db->f("start"));
		$this->setProperty("CLASS",       $db->f("class"));
		$this->setProperty("DTEND",       $db->f("end"));
		$this->setProperty("CATEGORIES",  $db->f("categories"));
		$this->setProperty("DESCRIPTION", $db->f("description"));
		$this->setProperty("PRIORITY",    $db->f("priority"));
		$this->setProperty("LOCATION",    $db->f("location"));
		$this->setProperty("RRULE", array(
				"ts"        => $db->f("ts"),
				"linterval" => $db->f("linterval"),
				"sinterval" => $db->f("sinterval"),
				"wdays"     => $db->f("wdays"),
				"month"     => $db->f("month"),
				"day"       => $db->f("day"),
				"rtype"     => $db->f("rtype"),
				"duration"  => $db->f("duration"),
				"expire"    => $db->f("expire")));
		$this->setMakeDate($db->f("mkdate"));
		$this->setChangeDate($db->f("chdate"));
		$this->chng_flag = FALSE;
		
		return TRUE;
	}
	return FALSE;
}
