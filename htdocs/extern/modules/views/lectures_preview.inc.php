<?
global $ABSOLUTE_PATH_STUDIP;
global $RELATIVE_PATH_CALENDAR;
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/SemBrowse.class.php");

global $SEM_TYPE,$SEM_CLASS, $SEMESTER;

// reorganize the $SEM_TYPE-array
foreach ($SEM_CLASS as $key_class => $class) {
	$i = 0;
	foreach ($SEM_TYPE as $key_type => $type) {
		if ($type["class"] == $key_class) {
			$i++;
			$sem_types_position[$key_type] = $i;
		}
	}
}

// current semester
$now = time();
foreach ($SEMESTER as $key => $sem) {
	if ($sem["beginn"] >= $now)
		break;
}

$data_sem[0]["group"] = 1;
$data_sem[1]["group"] = 2;
$data_sem[0]["name"] = _("Name der Veranstaltung 1");
$data_sem[1]["name"] = _("Name der Veranstaltung 2");
$data_sem[0]["time"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
$data_sem[1]["time"] = _("Termine am 31.7. 14:00 - 16:00, 17.8. 11:00 - 14:30, 6.9. 14:00 - 16:00,...");
switch ($this->config->getValue("Main", "nameformat")) {
	case "no_title_short" :
		$data_sem[0]["lecturer"] = _("Meyer, P.");
		break;
	case "no_title" :
		$data_sem[0]["lecturer"] = _("Peter Meyer");
		break;
	case "no_title_rev" :
		$data_sem[0]["lecturer"] = _("Meyer Peter");
		break;
	case "full" :
		$data_sem[0]["lecturer"] = _("Dr. Peter Meyer");
		break;
	case "full_rev" :
		$data_sem[0]["lecturer"] = _("Meyer, Peter, Dr.");
		break;
}
$data_sem[1]["lecturer"] = $data_sem[0]["lecturer"];

$show_time = $this->config->getValue("Main", "time");
$show_lecturer = $this->config->getValue("Main", "lecturer");
if ($show_time && $show_lecturer)
	$colspan = " colspan=\"2\"";
else
	$colspan = "";

echo "\n<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
if ($this->config->getValue("Main", "addinfo")) {
	echo "\n<tr" . $this->config->getAttributes("InfoCountSem", "tr") . ">";
	echo "<td$colspan" . $this->config->getAttributes("InfoCountSem", "td") . ">";
	echo "<font" . $this->config->getAttributes("InfoCountSem", "font") . ">&nbsp;";
	echo "2 ";
	echo $this->config->getValue("Main", "textlectures");
	echo ", " . $this->config->getValue("Main", "textgrouping");
	$group_by_name = $this->config->getValue("Main", "aliasesgrouping");
	echo $group_by_name[3];
	echo "</font></td></tr>";
}

foreach ($data_sem as $dat) {
	echo "\n<tr" . $this->config->getAttributes("Grouping", "tr") . ">";
	echo "<td$colspan" . $this->config->getAttributes("Grouping", "td") . ">";
	echo "<font" . $this->config->getAttributes("Grouping", "font") . ">";
	
	$aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
			"class_{$SEM_TYPE[$dat['group']]['class']}");
	if ($aliases_sem_type[$sem_types_position[$dat['group']] - 1])
		echo $aliases_sem_type[$sem_types_position[$dat['group']] - 1];
	else {
		echo htmlReady($SEM_TYPE[$dat['group']]["name"]
				." (". $SEM_CLASS[$SEM_TYPE[$dat['group']]["class"]]["name"].")");
	}
	
	echo "</font></td></tr>";
	echo "<tr" . $this->config->getAttributes("SemName", "tr") . ">";
	echo "<td$colspan" . $this->config->getAttributes("SemName", "td") . ">";
	echo "<font" . $this->config->getAttributes("SemName", "font") . ">";
	echo "<a href=\"\"";
	echo $this->config->getAttributes("SemLink", "a") . ">";
	echo $dat["name"] . "</a></font></td></tr>\n";
	
	if ($show_time || $show_lecturer) {
		echo "<tr" . $this->config->getAttributes("TimeLecturer", "tr") . ">";
		if ($show_time) {
			echo "<td" . $this->config->getAttributes("TimeLecturer", "td1") . ">";
			echo "<font" . $this->config->getAttributes("TimeLecturer", "font1") . ">";
			echo $dat["time"] . "</font></td>\n";
		}
		if ($show_lecturer) {
			echo "<td" . $this->config->getAttributes("TimeLecturer", "td2") . ">";
			echo "<font" . $this->config->getAttributes("TimeLecturer", "font2") . ">(";
			echo "<a href=\"\"";
			echo $this->config->getAttributes("LecturerLink", "a") . ">";
			echo $dat["lecturer"] . "</a>";
			echo ") </font></td></tr>";
		}
	}
}
echo "</table>";
?>
