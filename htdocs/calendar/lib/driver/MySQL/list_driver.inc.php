<?

function list_restore(&$this){
	$db = new DB_Seminar();
	$end = $this->getEnd();
	$start = $this->getStart();
	$query = "SELECT * FROM calendar_events WHERE range_id='" . $this->range_id . "' AND ";
	if (!$this->show_private)
		$query .= "class = 'PUBLIC' AND ";
	$query .= "(((start BETWEEN $start AND $end) OR (end BETWEEN $start AND $end)) "
					. "OR (start <= $end AND expire > $start AND rtype != 'SINGLE')) "
					. "ORDER BY start ASC";

	$db->query($query);
	
	$year = date("Y", $start);
	$month = date("n", $start);
	
	while ($db->next_record()) {
		
		$rep = array(
				"ts"        => $db->f("ts"),
				"linterval" => $db->f("linterval"),
				"sinterval" => $db->f("sinterval"),
				"wdays"     => $db->f("wdays"),
				"month"     => $db->f("month"),
				"day"       => $db->f("day"),
				"rtype"     => $db->f("rtype"),
				"duration"  => $db->f("duration"));
		
		$duration = $rep["duration"];
		$expire = $db->f("expire");
		
		switch($rep["rtype"]){
			// Einzeltermin (die hat die Datenbank schon gefiltert)
			case "SINGLE" :
				$adate = $rep["ts"];
				$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
				new_event($this, $db, $real_date);
				break;
			
			// t�gliche Wiederholung
			case "DAILY" :
				if($rep["ts"] < $start)
					// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
					$adate = $this->ts + (($rep["linterval"]-(ceil(($start - $rep["ts"]) / 86400) % $rep["linterval"]) - 1) * 86400);
				else
					$adate = $rep["ts"];
					
				while($adate <= $expire && $adate <= $end && $adate >= $start){
					$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
					new_event($this, $db, $real_date);
					$adate += 86400 * $rep["linterval"];
				}
				break;
			
			// w�chentliche Wiederholung
			case "WEEKLY" :
				if($db->f("start") >= $start){
					$adate = mktime(12,0,0,date("n",$db->f("start")),date("j",$db->f("start")),date("Y",$db->f("start")),0);
					if($rep["ts"] != $adate){
						$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
						new_event($this, $db, $real_date);
					}
					$aday = strftime("%u", $adate) - 1;
					for($i = 0;$i < strlen($rep["wdays"]);$i++){
						$awday = (int) substr($rep["wdays"], $i, 1) - 1;
						if($awday > $aday){
							$wdate = $adate + ($awday - $aday) * 86400;
								if($wdate > $expire)
									break 2;
							$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
							new_event($this, $db, $real_date);
						}
					}
				}
				if($rep["ts"] < $start){
					// Brauche den Montag der angefangenen Woche
					$adate = $this->ts - (strftime("%u", $this->ts) - 1) * 86400;
					$adate += (($rep["linterval"] - (($adate - $rep["ts"]) / 604800) % $rep["linterval"]) % $rep["linterval"]) * 604800;
				}
				else
					$adate = $rep["ts"];
				
				while($adate <= $expire && $adate <= $end){
					// Termin kann innerhalb der Woche an verschiedenen Wochentagen wiederholt werden
					for($i = 0;$i < strlen($rep["wdays"]);$i++){
						$awday = (int) substr($rep["wdays"], $i, 1) - 1;
						$wdate = $adate + $awday * 86400;
						$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
						if($real_date > $end || $wdate > $expire)
							break 2;
						if($real_date < $start)
							continue;
						new_event($this, $db, $real_date);
					}
					$adate += 604800 * $rep["linterval"];
				}
				break;
			
			// monatliche Wiederholung
			case "MONTHLY" :
				if($db->f("start") > $start){
					$adate = mktime(12, 0, 0, date("n", $db->f("start")), date("j", $db->f("start")), date("Y", $db->f("start")), 0);
					if($rep["ts"] != $adate){ 
						$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")), 0, date("n", $adate), date("j", $adate), date("Y", $adate));
						new_event($this, $db, $real_date);
					}
				}
				
				if($rep["sinterval"] == 5)
					$cor = 0;
				else
					$cor = 1;
				
				if($rep["ts"] < $end){
					// brauche ersten Monat nach $start in dem der Termin wiederholt wird
					$amonth = $month + (abs($month - date("n", $rep["ts"])) % $rep["linterval"]);
					// ist Wiederholung am X. Wochentag des X. Monats...
					if($rep["day"] == ""){
						$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sinterval"] - $cor) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sinterval"] == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
								$adate += 604800;
						}
						else{
							if($aday > $rep["wdays"])
								$adate += 604800;
						}
					}
					else
						// oder am X. Tag des Monats ?
						$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
				}
				else{
					// handelt es sich um "X. Wochentag des X. Monats" kommt nichts hinzu
					$adate = $rep["ts"] + ($rep["day"] ? ($rep["day"] - 1) * 86400 : 0);
					$amonth = date("n", $rep["ts"]);
				}
					
				while($adate <= $expire && $adate <= $end && $adate >= $start){
					// verhindert die Anzeige an Tagen, die au�erhalb des Monats liegen (am 29. bis 31.)
					if($rep["wdays"] == "" ? date("j", $adate) == $rep["day"] : TRUE){
						$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
						new_event($this, $db, $real_date);
					}
					
					$amonth += $rep["linterval"];
					// wenn Termin am X. Wochentag des X. Monats, dann Berechnung hier wiederholen
					if($rep["day"] == ""){
						$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sinterval"] - 1) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sinterval"] == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
								$adate += 604800;
						}
						else{
							if($aday > $rep["wdays"])
								$adate += 604800;
						}
					}
					else
						$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
				}
				break;
			
			// j�hrliche Wiederholung
			case "YEARLY" :
				if($db->f("start") > $start){
					$wdate = mktime(12,0,0,date("n",$db->f("start")),date("j",$db->f("start")),date("Y",$db->f("start")),0);
					if($rep["ts"] != $wdate){
						$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
						new_event($this, $db, $real_date);
					}
				}
				
				if($rep["sinterval"] == 5)
					$cor = 0;
				else
					$cor = 1;
				
				if($rep["ts"] < $start){
					if($rep["day"] == ""){
						$adate = mktime(12,0,0,$rep["month"],1,$year,0) + ($rep["sinterval"] - $cor) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sinterval"] == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
						}
						else
							if($aday > $rep["wdays"])
								$adate += 604800;
					}
					else
						$adate = mktime(12,0,0,$rep["month"],$rep["day"],$year,0);
				}
				else
					$adate = $rep["ts"];
				
				if($duration > 1){
					if($rep["day"] == ""){
						$xdate = mktime(12,0,0,$rep["month"],1,$year - 1,0) + ($rep["sinterval"] - $cor) * 604800;
						$aday = strftime("%u",$xdate);
						$xdate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sinterval"] == 5){
							if(date("j",$xdate) < 10)
								$xdate -= 604800;
						}
						else
							if($aday > $rep["wdays"])
								$xdate += 604800;
						$duration_first = $duration - (date("z",mktime(12,0,0,12,31,$year-1,0)) - date("z",$xdate)) - 1;
					}
					else{
						$xdate = mktime(12,0,0,date("n",$adate),date("j",$adate),date("Y",$adate) - 1,0)
										+ ($duration - 1) * 86400;
						$duration_first = ($xdate - $this->ts) / 86400 + 1;
					}
					if($xdate <= $end && $xdate >= $start && $xdate <= $expire){
						$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $md_date),date("j", $md_date),date("Y", $md_date));
						new_event($this, $db, $real_date);
					}
				}
				
				if($adate <= $end && $adate >= $start && $adate <= $expire){
					$real_date = mktime(date("G", $db->f("start")), date("i", $db->f("start")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
					new_event($this, $db, $real_date);
				}
				break;
		}
	}
}

function new_event (&$this, $db, $real_date) {
	$event = new CalendarEvent(array(
			"DTSTART"       => $real_date,
			"DTEND"         => $db->f("end") - $db->f("start") + $real_date,
			"SUMMARY"       => $db->f("summary"),
			"DESCRIPTION"   => $db->f("description"),
			"PRIORITY"      => $db->f("prority"),
			"LOCATION"      => $db->f("location"),
			"CATEGORIES"    => $db->f("categories"),
			"UID"           => $db->f("uid"),
			"DTSTAMP"       => $db->f("mkdate"),
			"LAST-MODIFIED" => $db->f("chdate"),
			"RRULE"         => array(
				 "ts"         => $db->f("ts"),
				 "linterval"  => $db->f("linterval"),
				 "sinterval"  => $db->f("sinterval"),
				 "wdays"      => $db->f("wdays"),
				 "month"      => $db->f("month"),
				 "day"        => $db->f("day"),
				 "rtype"      => $db->f("rtype"),
				 "duration"   => $db->f("duration"),
				 "expire"     => $db->f("expire"))),
			$db->f("event_id"));
	
	$this->events[] = $event;
}
	
?>
