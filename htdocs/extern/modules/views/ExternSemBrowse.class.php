<?
global $ABSOLUTE_PATH_STUDIP;
global $RELATIVE_PATH_CALENDAR;
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/SemBrowse.class.php");

class ExternSemBrowse extends SemBrowse {
	
	var $config;
	
	function ExternSemBrowse (&$config, $start_item_id) {
		
		global $SEM_TYPE,$SEM_CLASS,$SEMESTER;
		
		$this->group_by_fields = array(	array('name' => _("Semester"), 'group_field' => 'sem_number'),
										array('name' => _("Bereich"), 'group_field' => 'bereich'),
										array('name' => _("DozentIn"), 'group_field' => 'fullname', 'unique_field' => 'username'),
										array('name' => _("Typ"), 'group_field' => 'status'),
										array('name' => _("Einrichtung"), 'group_field' => 'Institut', 'unique_field' => 'Institut_id'));

		$this->config = $config;
		$this->sem_browse_data["group_by"] = $this->config->getValue("Main", "grouping");
		$this->sem_dates = $GLOBALS['SEMESTER'];
		$this->sem_dates[0] = array("name" => sprintf(_("vor dem %s"),$this->sem_dates[1]['name']));
		
		// reorganize the $SEM_TYPE-array
		foreach ($SEM_CLASS as $key_class => $class) {
			$i = 0;
			foreach ($SEM_TYPE as $key_type => $type) {
				if ($type["class"] == $key_class) {
					$i++;
					$this->sem_types_position[$key_type] = $i;
				}
			}
		}
		
		// current semester
		$now = time();
		foreach ($SEMESTER as $key => $sem) {
			if ($sem["beginn"] >= $now)
				break;
		}
		$key--;
		$semrange = $this->config->getValue("Main", "semrange");
		switch ($semrange) {
			case "current":
			$this->sem_number[0] = $key;
			break;
			case "three":
			if ($key == 1){
				$this->sem_number[0] = 2;
				$this->sem_number[1] = 1;
			} elseif($key == count($SEMESTER)-1) {
				$this->sem_number[0] = $key;
				$this->sem_number[1] = $key-1;
			} else {
				$this->sem_number[0] = $key+1;
				$this->sem_number[1] = $key;
				$this->sem_number[2] = $key-1;
			}
			break;
			case "all":
			$this->sem_number = false;
			break;
		}
		
		$semclasses = $this->config->getValue("Main", "semclasses");
		foreach ($SEM_TYPE as $key => $type) {
			if (in_array($type["class"], $semclasses))
				$this->sem_browse_data['sem_status'][] = $key;
		}
		
		$this->get_sem_range_tree($start_item_id, true);
				
	}
	
	function print_result () {
		global $_fullname_sql,$_views,$PHP_SELF,$SEM_TYPE,$SEM_CLASS,$SEMESTER;
		
		$sem_link = ExternModule::getModuleLink("Lecturedetails",
			$this->config->getValue("SemLink", "config"), $this->config->getValue("SemLink", "srilink"));
		
		$lecturer_link = ExternModule::getModuleLink("Persondetails",
			$this->config->getValue("LecturerLink", "config"), $this->config->getValue("LecturerLink", "srilink"));
			
		if (is_array($this->sem_browse_data['search_result']) && count($this->sem_browse_data['search_result'])) {
			
			if ($this->sem_browse_data['group_by'] == 1){
				if (!is_object($this->sem_tree)){
					$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
				} else {
					$the_tree =& $this->sem_tree->tree;
				}
			}
			
			if (!$this->config->getValue("Main", "allseminars")){
				$sem_inst_query = " AND seminar_inst.Institut_id='{$this->config->range_id}' ";
			}
			
			$query = ("SELECT seminare.Seminar_id, seminare.status, seminare.Name, seminare.metadata_dates 
				, Institute.Name AS Institut,Institute.Institut_id,
				seminar_sem_tree.sem_tree_id AS bereich, " . $_fullname_sql['no_title_short'] ." AS fullname, auth_user_md5.username,
				" . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminare 
				LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent') 
				LEFT JOIN auth_user_md5 USING (user_id) 
				LEFT JOIN user_info USING (user_id) 
				LEFT JOIN seminar_sem_tree ON (seminare.Seminar_id = seminar_sem_tree.seminar_id)
				LEFT JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.Seminar_id $sem_inst_query) 
				LEFT JOIN Institute USING (Institut_id) 
				WHERE seminare.Seminar_id IN('" . join("','", array_keys($this->sem_browse_data['search_result'])) . "')");
			
			$db = new DB_Seminar($query);
			$snap = new DbSnapShot($db);
			$group_field = $this->group_by_fields[$this->sem_browse_data['group_by']]['group_field'];
			$data_fields[0] = "Seminar_id";
			if ($this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field']){
				$data_fields[1] = $this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field'];
			}
			$group_by_data = $snap->getGroupedResult($group_field, $data_fields);
			$sem_data = $snap->getGroupedResult("Seminar_id");
			if ($this->sem_browse_data['group_by'] == 0){
				$group_by_duration = $snap->getGroupedResult("sem_number_end", array("sem_number","Seminar_id"));
				foreach ($group_by_duration as $sem_number_end => $detail){
					if ($sem_number_end != -1 && ($detail['sem_number'][$sem_number_end] && count($detail['sem_number']) == 1)){
						continue;
					} else {
						foreach ($detail['Seminar_id'] as $seminar_id => $foo){
							$start_sem = key($sem_data[$seminar_id]["sem_number"]);
							if ($sem_number_end == -1){
								if (is_array($this->sem_number)){
									$sem_number_end = $this->sem_number[0];
								} else {
									$sem_number_end = count($this->sem_dates)-1;
								}
							}
							for ($i = $start_sem; $i <= $sem_number_end; ++$i){
								if ($this->sem_number === false || (is_array($this->sem_number) && in_array($i,$this->sem_number))){
									if ($group_by_data[$i] && !$tmp_group_by_data[$i]){
										foreach($group_by_data[$i]['Seminar_id'] as $id => $bar){
											$tmp_group_by_data[$i]['Seminar_id'][$id] = true;
										}
									}
									$tmp_group_by_data[$i]['Seminar_id'][$seminar_id] = true;
								}
							}
						}
					}
				}
				if (is_array($tmp_group_by_data)){
					if ($this->sem_number !== false){
						unset($group_by_data);
					}
					foreach ($tmp_group_by_data as $start_sem => $detail){
						$group_by_data[$start_sem] = $detail;
					}
				}
			}
			
			foreach ($group_by_data as $group_field => $sem_ids){
				foreach ($sem_ids['Seminar_id'] as $seminar_id => $foo){
					$name = strtolower(key($sem_data[$seminar_id]["Name"]));
					$name = str_replace("�","ae",$name);
					$name = str_replace("�","oe",$name);
					$name = str_replace("�","ue",$name);
					$group_by_data[$group_field]['Seminar_id'][$seminar_id] = $name;
				}
				uasort($group_by_data[$group_field]['Seminar_id'], 'strnatcmp');
			}
			
			switch ($this->sem_browse_data["group_by"]){
					case 0:
					krsort($group_by_data, SORT_NUMERIC);
					break;
					
					case 1:
					uksort($group_by_data, create_function('$a,$b',
							'$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
							return (int)($the_tree->tree_data[$a]["index"] - $the_tree->tree_data[$b]["index"]);
							'));
					break;
					
					case 3:
					uksort($group_by_data, create_function('$a,$b',
							'global $SEM_CLASS,$SEM_TYPE;
							return strnatcasecmp($SEM_TYPE[$a]["name"]." (". $SEM_CLASS[$SEM_TYPE[$a]["class"]]["name"].")",
												$SEM_TYPE[$b]["name"]." (". $SEM_CLASS[$SEM_TYPE[$b]["class"]]["name"].")");'));
					break;
					default:
					uksort($group_by_data, 'strnatcasecmp');
					break;
					
			}
			
			$show_time = $this->config->getValue("Main", "time");
			$show_lecturer = $this->config->getValue("Main", "lecturer");
			if ($show_time && $show_lecturer) {
				if (!$td2width = $this->config->getValue("LecturesInnerTable", "td2width"))
					$td2width = 50;
				$colspan = " colspan=\"2\"";
				$td_time = $this->config->getAttributes("LecturesInnerTable", "td2");
				$td_time .= " width=\"$td2width%\"";
				$td_lecturer = " align=\"" . $this->config->getValue("LecturesInnerTable", "td3_align");
				$td_lecturer .= "\" valign=\"" . $this->config->getValue("LecturesInnerTable", "td2_valign");
				$td_lecturer .= "\" width=\"" . (100 - $td2width) . "%\"";
			}
			else {
				$colspan = "";
				$td_time = $this->config->getAttributes("LecturesInnerTable", "td2") . " width=\"100%\"";
				$td_lecturer = " align=\"" . $this->config->getValue("LecturesInnerTable", "td3_align");
				$td_lecturer .= "\" valign=\"" . $this->config->getValue("LecturesInnerTable", "td2_valign");
				$td_lecturer .= " width=\"100%\"";
			}
			
			echo "\n<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
			if ($this->config->getValue("Main", "addinfo")) {
				echo "\n<tr" . $this->config->getAttributes("InfoCountSem", "tr") . ">";
				echo "<td" . $this->config->getAttributes("InfoCountSem", "td") . ">";
				echo "<font" . $this->config->getAttributes("InfoCountSem", "font") . ">&nbsp;";
				echo count($sem_data) ." ";
				echo $this->config->getValue("Main", "textlectures");
				echo ", " . $this->config->getValue("Main", "textgrouping");
				$group_by_name = $this->config->getValue("Main", "aliasesgrouping");
				echo $group_by_name[$this->sem_browse_data['group_by']];
				echo "</font></td></tr>";
			}
			
			foreach ($group_by_data as $group_field => $sem_ids) {
				echo "\n<tr" . $this->config->getAttributes("Grouping", "tr") . ">";
				echo "<td" . $this->config->getAttributes("Grouping", "td") . ">";
				echo "<font" . $this->config->getAttributes("Grouping", "font") . ">";
				switch ($this->sem_browse_data["group_by"]){
					case 0:
					echo $this->sem_dates[$group_field]['name'];
					break;
					
					case 1:
					$range_path_new = NULL;
					if ($the_tree->tree_data[$group_field]) {
						$range_path = explode(" > ", $the_tree->getShortPath($group_field));
						$range_path_level = $this->config->getValue("Main", "rangepathlevel");
						if ($range_path_level > sizeof($range_path))
							$range_path_level = sizeof($range_path);
						for ($i = $range_path_level - 1; $i < sizeof($range_path); $i++)
							$range_path_new[] = $range_path[$i];
						echo htmlReady(implode(" > ", $range_path_new));
					}
					else
						echo $this->config->getValue("Main", "textnogroups");
					
					break;
					
					case 2:
					echo htmlReady($group_field);
					break;
					
					case 3:
					$aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
							"class_{$SEM_TYPE[$group_field]['class']}");
					if ($aliases_sem_type[$this->sem_types_position[$group_field] - 1])
						echo $aliases_sem_type[$this->sem_types_position[$group_field] - 1];
					else {
						echo htmlReady($SEM_TYPE[$group_field]["name"]
								." (". $SEM_CLASS[$SEM_TYPE[$group_field]["class"]]["name"].")");
					}
					break;
					
					case 4:
					echo htmlReady($group_field);
					break;
					
				}
				echo "</font></td></tr>";
				if (is_array($sem_ids['Seminar_id'])) {
					while (list($seminar_id,) = each($sem_ids['Seminar_id'])) {
						$sem_name = key($sem_data[$seminar_id]["Name"]);
						$sem_number_start = key($sem_data[$seminar_id]["sem_number"]);
						$sem_number_end = key($sem_data[$seminar_id]["sem_number_end"]);
						if ($sem_number_start != $sem_number_end){
							$sem_name .= " (" . $this->sem_dates[$sem_number_start]['name'] . " - ";
							$sem_name .= (($sem_number_end == -1) ? _("unbegrenzt") : $this->sem_dates[$sem_number_end]['name']) . ")";
						}
						echo "\n</td></tr>\n<tr" . $this->config->getAttributes("LecturesInnerTable", "tr").">";
						echo "<td width=\"100%\"" . $this->config->getAttributes("LecturesInnerTable", "td")."\">\n";
						echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
						echo "<tr" . $this->config->getAttributes("LecturesInnerTable", "tr1") . ">";
						echo "<td$colspan" . $this->config->getAttributes("LecturesInnerTable", "td1") . ">";
						echo "<font" . $this->config->getAttributes("LecturesInnerTable", "font1") . ">";
						echo "<a href=\"$sem_link&seminar_id=$seminar_id\"";
						echo $this->config->getAttributes("SemLink", "a") . ">";
						echo htmlReady($sem_name) . "</a></font></td></tr>\n";
						//create Turnus field
						$temp_turnus_string = view_turnus($seminar_id, TRUE ,key($sem_data[$seminar_id]["metadata_dates"]));
						//Shorten, if string too long (add link for details.php)
						if (strlen($temp_turnus_string) >70) {
							$temp_turnus_string = substr($temp_turnus_string, 0, strpos(substr($temp_turnus_string, 70, strlen($temp_turnus_string)), ",") +71);
							$temp_turnus_string .= "...";
						}
						if ($show_time || $show_lecturer) {
							echo "\n<tr" . $this->config->getAttributes("LecturesInnerTable", "tr2") . ">";
							if ($show_time) {
								echo "<td$td_time>";
								echo "<font" . $this->config->getAttributes("LecturesInnerTable", "font2") . ">";
								echo $temp_turnus_string . "</font></td>\n";
							}
							if ($show_lecturer) {
								echo "<td$td_lecturer>";
								echo "<font" . $this->config->getAttributes("LecturesInnerTable", "font2") . ">(";
								$doz_name = array_keys($sem_data[$seminar_id]['fullname']);
								$doz_uname = array_keys($sem_data[$seminar_id]['username']);
								if (is_array($doz_name)){
									uasort($doz_name, 'strnatcasecmp');
									$i = 0;
									foreach ($doz_name as $index => $value) {
										echo "<a href=\"$lecturer_link&username={$doz_uname[$index]}\"";
										echo $this->config->getAttributes("LecturerLink", "a") . ">";
										echo htmlReady($value) . "</a>";
										if ($i != count($doz_name) - 1) {
											echo ", ";
										}
										if ($i == 3) {
											echo "...";
											break;
										}
										++$i;
									}
									echo ") ";
								}
								echo "</font></td>";
							}
							echo "</tr>";
						}
						echo "</table></td></tr>\n";
					}
				}
			}
			echo "</table>";
		}
	}
	
}
?>
