<?

function year_restore(&$this){
	$db = new DB_Seminar();
	$end = $this->getEnd();
	$start = $this->getStart();

		$query = sprintf("SELECT termin_id,content,date,end_time,date_typ,expire,repeat,color,priority,raum"
	       . " FROM termine WHERE range_id='%s' AND autor_id='%s' AND (date BETWEEN %s AND %s"
				 . " OR (date <= %s AND expire > %s AND repeat NOT LIKE '%%SINGLE%%') OR (%s BETWEEN date AND end_time))"
				 . " ORDER BY date ASC"
				 , $this->user_id, $this->user_id, $start, $end, $end, $start, $start);
	$db->query($query);
	
	$year = $this->year;
	$month = 1;
	
	while($db->next_record()){
		list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
	     $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $db->f("repeat"));
		if($rep["duration"] == "#")
			$duration = 1;
		else
			$duration = $rep["duration"];
		
		switch($rep["type"]){
			// Einzeltermin
			case "SINGLE" :
				$adate = $rep["ts"];
				while($duration-- && $adate <= $end){
					$this->apdays["$adate"]++;
					$adate += 86400;
				}
				break;
			
			// t�gliche Wiederholung
			case "DAYLY" :
				if($rep["ts"] < $start){
					// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
					$adate = $this->ts + ($rep["lintervall"] - (($this->ts - $rep["ts"]) / 86400) % $rep["lintervall"]) * 86400;
					// Wie oft muss ein mehrt�giger Termin eingetragen werden, dessen
					// Startzeit vor Jahresbeginn liegt?
					if(($xdate = $adate - ($rep["lintervall"] - $duration + 1) * 86400) > $start){
						$duration_first = ($xdate - $this->ts) / 86400 + 1;
						$md_date = $this->ts;
						while($duration_first-- && $md_date <= $end && $md_date <= $db->f("expire")){
							$this->apdays["$md_date"]++;
							$md_date += 86400;
						}
					}
				}
				else
					$adate = $rep["ts"];
				
				while($duration--){
					$md_date = $adate;
					while($md_date <= $db->f("expire") && $md_date <= $end){
						$this->apdays["$md_date"]++;
						$md_date += 86400 * $rep["lintervall"];
					}
					$adate += 86400;
				}
				break;
			
			// w�chentliche Wiederholung
			case "WEEKLY" :
				if($db->f("date") > $start){
					$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
					if($rep["ts"] != $adate){
						$md_date = $adate;
						$count = $duration;
						while($count-- && $md_date <= $end && $md_date <= $db->f("expire")){
							$this->apdays["$md_date"]++;
							$md_date += 86400;
						}
					}
						
					$aday = strftime("%u", $adate) - 1;
					for($i = 0;$i < strlen($rep["wdays"]);$i++){
						$awday = (int) substr($rep["wdays"], $i, 1) - 1;
						if($awday > $aday){
							$wdate = $adate + ($awday - $aday) * 86400;
							$count = $duration;
							while($count--){
								if($wdate > $end || $wdate > $db->f("expire"))
									break 2;
								$this->apdays["$wdate"]++;
								$wdate += 86400;
							}
						}
					}
				}
				
				if($rep["ts"] < $start){
					// Brauche Montag der "angefangenen" Woche
					$adate = $this->ts - (strftime("%u",$start) - 1) * 86400;
					$adate += (($rep["lintervall"] - (($adate - $rep["ts"]) / 604800) % $rep["lintervall"]) % $rep["lintervall"]) * 604800;
				}
				else
					$adate = $rep["ts"];
					
				while($adate <= $db->f("expire") && $adate <= $end){
					// Termin kann innerhalb der Woche an verschiedenen Wochentagen wiederholt werden
					for($i = 0;$i < strlen($rep["wdays"]);$i++){
						$awday = (int) substr($rep["wdays"], $i, 1) - 1;
						$wdate = $adate + $awday * 86400;
						$count = $duration;
						while($count--){
							if($wdate > $end || $wdate > $db->f("expire"))
								break 3;
							$this->apdays["$wdate"]++;
							$wdate += 86400;
						}
					}
					$adate += 604800 * $rep["lintervall"];
				}
				break;
			
			// monatliche Wiederholung
			case "MONTHLY" :
				if($db->f("date") > $start){
					$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
					$count = $duration;
					while($count-- && $adate <= $end && $adate <= $db->f("expire")){
						$this->apdays["$adate"]++;
						$adate += 86400;
					}
				}
				
				if($rep["sintervall"] == 5)
					$cor = 0;
				else
					$cor = 1;
				
				if($rep["ts"] < $start){
					// brauche ersten Monat in dem der Termin wiederholt wird
			  	$amonth = ($rep["lintervall"] - ((($year - date("Y",$rep["ts"])) * 12) - date("n",$rep["ts"])) % $rep["lintervall"]) % $rep["lintervall"];
					// ist Wiederholung am X. Wochentag des X. Monats...
					if($rep["day"] == ""){
						$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sintervall"] == 5){
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
					$adate = $rep["ts"];// + ($rep["day"]?($rep["day"] - 1) * 86400:0);
					$amonth = date("n", $rep["ts"]);
				}
				
				// Termine, die die Jahresgrenze �berbr�cken
				if($duration > 1 && $rep["ts"] < $this->ts){
					if($rep["day"] == ""){
						$xdate = mktime(12,0,0,$amonth - $rep["lintervall"],1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
						$aday = strftime("%u",$xdate);
						$xdate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sintervall"] == 5){
							if(date("j",$xdate) < 10)
								$xdate -= 604800;
							if(date("n",$xdate) == date("n",$xdate + 604800))
								$xdate += 604800;
						}
						else
							if($aday > $rep["wdays"])
								$xdate += 604800;
						$xdate += $duration * 86400;
					}
					else
						$xdate = mktime(12,0,0,date("n",$adate) - $rep["lintervall"],date("j",$adate) + $duration,date("Y",$adate),0);
					
					$xdate++;
					$md_date = $this->ts;
					while($md_date < $xdate && $md_date <= $db->f("expire")){
						$this->apdays["$md_date"]++;
						$md_date += 86400;
					}
				}
				
				while($adate <= $db->f("expire") && $adate <= $end){
					$md_date = $adate;
					$count = $duration;
					while($count--){
						// verhindert die Anzeige an Tagen, die au�erhalb des Monats liegen (am 29. bis 31.)
						if($rep["wdays"] == ""?date("j", $adate) == $rep["day"]:TRUE
							&& $md_date <= $db->f("expire") && $md_date <= $end)
								$this->apdays["$md_date"]++;
						$md_date += 86400;
					}
					$amonth += $rep["lintervall"];
					// wenn Termin am X. Wochentag des X. Monats, dann Berechnung hier wiederholen
					if($rep["day"] == ""){
						$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sintervall"] == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
								$adate += 604800;
						}
						else
							if($aday > $rep["wdays"])
								$adate += 604800;
					}
					else
						$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
				}
				break;
			
			// j�hrliche Wiederholung
			case "YEARLY" :
				if($db->f("date") > $start -1 && $db->f("start") < $end + 1){
					$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
					if($rep["ts"] != $adate){
						$count = $duration;
						while($count-- && $adate <= $end && $adate <= $db->f("expire")){
							$this->apdays["$adate"]++;
							$adate += 86400;
						}
					}
				}
				
				if($rep["ts"] > $start -1 && $rep["ts"] < $end + 1){
					$adate = $rep["ts"];
					$count = $duration;
					while($count-- && $adate <= $end && $adate <= $db->f("expire")){
						$this->apdays["$adate"]++;
						$adate += 86400;
					}
				}
				
				if($rep["sintervall"] == 5)
					$cor = 0;
				else
					$cor = 1;
				
				if($rep["ts"] < $start){
					if($rep["day"] == ""){
						$adate = mktime(12,0,0,$rep["month"],1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sintervall"] == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
								$adate += 604800;
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
								
				// Termine, die die Jahresgrenze �berbr�cken
				if($duration > 1 && $rep["ts"] < $this->ts){
					if($rep["day"] == ""){
						$xdate = mktime(12,0,0,$rep["month"],1,$year - 1,0) + ($rep["sintervall"] - $cor) * 604800;
						$aday = strftime("%u",$xdate);
						$xdate -= ($aday - $rep["wdays"]) * 86400;
						if($rep["sintervall"] == 5){
							if(date("j",$xdate) < 10)
								$xdate -= 604800;
							if(date("n",$xdate) == date("n",$xdate + 604800))
								$xdate += 604800;
						}
						else
							if($aday > $rep["wdays"])
								$xdate += 604800;
						$xdate += $duration * 86400;
					}
					else
						$xdate = mktime(12,0,0,date("n",$adate),date("j",$adate) + $duration - 1,date("Y",$adate) - 1,0);
					
					$xdate++;
					$md_date = $this->ts;
					while($md_date < $xdate && $md_date <= $db->f("expire")){
						$this->apdays["$md_date"]++;
						$md_date += 86400;
					}
				}
				
				if($adate > $db->f("date"))
					while($duration-- && $adate <= $db->f("expire") && $adate <= $end){
						$this->apdays["$adate"]++;
						$adate += 86400;
					}
				break;
				
		}
	}
}

?>
