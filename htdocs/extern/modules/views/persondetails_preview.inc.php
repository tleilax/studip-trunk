<?

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "config.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "/lib/classes/SemesterData.class.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
		. "/lib/extern_functions.inc.php");
global $_fullname_sql;

$attr_subheadline_td = preg_replace('/width\="[^"]+"/i',
		$this->config->getAttributes("TableParagraphSubHeadline", "td"),
		$this->config->getValue("TableParagraph", "margin"));


$aliases_content = $this->config->getValue("Main", "aliases");
$visible_content = $this->config->getValue("Main", "visible");

if ($margin = $this->config->getValue("TableParagraphText", "margin")) {
	$text_div = "<div style=\"margin-left:$margin;\">";
	$text_div_end = "</div>";
}
else {
	$text_div = "";
	$text_div_end = "";
}

$first_loop = TRUE;
$order = $this->config->getValue("Main", "order");
foreach ($order as $position) {
	
	$data_field = $this->data_fields["content"][$position];

	if ($visible_content[$position]) {
		$data = NULL;
		switch ($data_field) {
			case "lebenslauf" :
				$data["content"] = str_repeat(_("Das ist mein Lebenslauf.") . " &nbsp;", 15);
				break;
			case "schwerp" :
				$data["content"] = str_repeat(_("Das sind meine Arbeitsschwerpunkte.") . " &nbsp;", 15);
				break;
			case "publi" :
				$data["content"] = str_repeat(_("Das sind meine Publikationen.") . " &nbsp;", 15);
				break;
			case "news" :
				$data[0]["topic"] = _("Das ist News Nr. 1");
				$data[0]["body"] = str_repeat(_("News Nr. 1") . " &nbsp;", 10);
				$data[1]["topic"] = _("Das ist News Nr. 2");
				$data[1]["body"] = str_repeat(_("News Nr. 2") . " &nbsp;", 10);
				$data[2]["topic"] = _("Das ist News Nr. 3");
				$data[2]["body"] = str_repeat(_("News Nr. 3") . " &nbsp;", 10);
				break;
			case "termine" :
				$now = time();
				for ($i = 0; $i < 3; $i++) {
					$data[$i]["start"] = $now + 19710329 * ($i + 1);
					$data[$i]["end"] = $data[$i]["start"] + 1000 * ($i + 1);
				}
				$data[0]["title"] = _("Das ist der erste Termin");
				$data[1]["title"] = _("Das ist der zweite Termin");
				$data[2]["title"] = _("Das ist der dritte Termin");
				$data[0]["content"] = str_repeat(_("Erster Termin") . " &nbsp;", 10);
				$data[1]["content"] = str_repeat(_("Zweiter Termin") . " &nbsp;", 10);
				$data[2]["content"] = str_repeat(_("dritter Termin ") . " &nbsp;", 10);
				break;
			case "kategorien" :
				$data["headline"] = _("Eigene Kategorie");
				$data["content"] = str_repeat(_("Eigene Kategorie") . " &nbsp;", 10);
				break;
			case "lehre" :
				$now = time();
				$data[0]["start_time"] = $now - 164160000;
				$data[1]["start_time"] = $now;
				$data[2]["start_time"] = $now + 164160000;
				$data[0]["name"] = _("Veranstaltung 1");
				$data[1]["name"] = _("Veranstaltung 2");
				$data[2]["name"] = _("Veranstaltung 3");
				$data[0]["untertitel"] = _("Untertitel der Veranstaltung 1");
				$data[1]["untertitel"] = _("Untertitel der Veranstaltung 2");
				$data[2]["untertitel"] = _("Untertitel der Veranstaltung 3");
				break;
			case "head" :
				$nameformat = $this->config->getValue("Main", "nameformat");
				switch ($nameformat) {
					case "no_title_short" :
						$data["fullname"] = _("Meyer, P.");
						break;
					case "no_title" :
						$data["fullname"] = _("Peter Meyer");
						break;
					case "no_title_rev" :
						$data["fullname"] = _("Meyer Peter");
						break;
					case "full" :
						$data["fullname"] = _("Dr. Peter Meyer");
						break;
					case "full_rev" :
						$data["fullname"] = _("Meyer, Peter, Dr.");
						break;
					default :
						$data["fullname"] = _("Dr. Peter Meyer");
						break;
				}
				$data["Name"] = _("Mustereinrichtung");
				$data["Strasse"] = _("Musterstra&szlig;e");
				$data["Plz"] = _("12345 Musterstadt");
				$data["raum"] = "A 123";
				$data["Telefon"] = "213 - 237 192";
				$data["Fax"] = "213 - 237 191";
				$data["Email"] = "email@email.org";
				$data["Home"] = "http://www.studip.de";
				$data["sprechzeiten"] = _("Mo. und Do. 12.00 - 13.00");
				break;
		}
			
		if ($first_loop) {
			echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
			if ($this->config->getValue("Main", "studiplink") == "top") {
				$args = array("width" => "100%", "height" => "40", "link" => "");
				echo "<tr><td width=\"100%\">\n";
				$this->elements["StudipLink"]->printout($args);
				echo "</td></tr>";
			}
			$first_loop = FALSE;
		}
		
		switch ($data_field) {
			case "lebenslauf" :
			case "schwerp" :
			case "publi" :
				echo "\n<tr><td width=\"100%\">\n";
				echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
				echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
				echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
				echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
				echo $aliases_content[$position] . "</font></td></tr>\n";
				echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
				echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
				echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
				echo $data["content"];
				echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
				break;
			case "news" :
			case "termine" :
			case "kategorien" :
			case "lehre" :
			case "head" :
				$data_field($this, $data, $aliases_content[$position], $text_div, $text_div_end);
		}
	}
}

// fit size of image
if ($pic_max_width && $pic_max_height) {
	$pic_size = @getimagesize($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . "user/"
			. $db->f("user_id") . ".jpg");
	
	if ($pic_size[0] > $pic_max_width || $pic_size[1] > $pic_max_height) {
		$fak_width = $pic_size[0] / $pic_max_width;
		$fak_height = $pic_size[1] / $pic_max_height;
		if ($fak_width > $fak_height) {
			$pic_width = (int) ($pic_size[0] / $fak_width);
			$pic_height = (int) ($pic_size[1] / $fak_width);
		}
		else {
			$pic_height = (int) ($pic_size[1] / $fak_height);
			$pic_width = (int) ($pic_size[0] / $fak_height);
		}
	}
	else {
		$pic_width = $pic_size[0];
		$pic_height = $pic_size[1];
	}
	$pic_max_width = $pic_width;
	$pic_max_height = $pic_height;
}
else {
	$pic_max_width = "";
	$pic_max_height = "";
}

if ($this->config->getValue("Main", "studiplink") == "bottom") {
	$args = array("width" => "100%", "height" => "40", "link" => "");
	echo "<tr><td width=\"100%\">\n";
	$this->elements["StudipLink"]->printout($args);
	echo "</td></tr>";
}

echo "</table>\n";

function news (&$this, $data, $alias_content, $text_div, $text_div_end) {
	if ($margin = $this->config->getValue("TableParagraphSubHeadline", "margin")) {
		$subheadline_div = "<div style=\"margin-left:$margin;\">";
		$subheadline_div_end = "</div>";
	}
	else {
		$subheadline_div = "";
		$subheadline_div_end = "";
	}
		
	echo "<tr><td width=\"100%\">\n";
	echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
	echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
	echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
	echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
	echo "$alias_content</font></td></tr>\n";
	foreach ($data as $dat) {
		echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
		echo $subheadline_div;
		echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
		echo $dat["topic"];
		echo "</font>$subheadline_div_end</td></tr>\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
		list ($content, $admin_msg) = explode("<admin_msg>", $dat["body"]);
		echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
		echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
		echo $content;
		echo "</font>$text_div_end</td></tr>\n";
	}
	echo "</table>\n</td></tr>\n";
}

function termine (&$this, $data, $alias_content, $text_div, $text_div_end) {
	if ($GLOBALS["CALENDAR_ENABLE"]) {
		if ($margin = $this->config->getValue("TableParagraphSubHeadline", "margin")) {
			$subheadline_div = "<div style=\"margin-left:$margin;\">";
			$subheadline_div_end = "</div>";
		}
		else {
			$subheadline_div = "";
			$subheadline_div_end = "";
		}
	
		echo "<tr><td width=\"100%\">\n";
		echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		echo "$alias_content</font></td></tr>\n";
		
		foreach ($data as $dat) {
			echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
			echo $subheadline_div;
			echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
			echo strftime($this->config->getValue("Main", "dateformat") . " %H.%m", $dat["start"]);
			if (date("dmY", $dat["start"]) == date("dmY", $dat["end"]))
				echo strftime(" - %H.%m", $dat["end"]);
			else
				echo strftime(" - " . $this->config->getValue("Main", "dateformat") . " %H.%m", $dat["end"]);
			echo " &nbsp;" . $dat["title"];
			echo "</font>$subheadline_div_end</td></tr>\n";
			echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
			echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
			echo $dat["content"];
			echo "</font>$text_div_end</td></tr>\n";
		} 
		echo "</table>\n</td></tr>\n";
	}
}

function kategorien (&$this, $data, $alias_content, $text_div, $text_div_end) {
	echo "<tr><td width=\"100%\">\n";
	echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
	echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
	echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
	echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
	echo $data["headline"];
	echo "</font></td></tr>\n";
	echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
	echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
	echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
	echo $data["content"];
	echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
}

function lehre (&$this, $data, $alias_content, $text_div, $text_div_end) {
	global $attr_text_td;
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	
	if ($margin = $this->config->getValue("TableParagraphSubHeadline", "margin")) {
		$subheadline_div = "<div style=\"margin-left:$margin;\">";
		$subheadline_div_end = "</div>";
	}
	else {
		$subheadline_div = "";
		$subheadline_div_end = "";
	}
	if ($margin = $this->config->getValue("List", "margin")) {
		$list_div = "<div style=\"margin-left:$margin;\">";
		$list_div_end = "</div>";
	}
	else {
		$list_div = "";
		$list_div_end = "";
	}
	// sem-types in class 1 (Lehre)
	foreach ($GLOBALS["SEM_TYPE"] as $key => $type) {
		if ($type["class"] == 1)
			$types[] = $key;
	}
	$types = implode("','", $types);
	
	
	$switch_time = mktime(0, 0, 0, date("m"),
			date("d") + 7 * $this->config->getValue("PersondetailsLectures", "semswitch"), date("Y"));
	// get current semester
	$current_sem = get_sem_num($switch_time);
	
	switch ($this->config->getValue("PersondetailsLectures", "semstart")) {
		case "previous" :
			if (isset($all_semester[$current_sem - 1]))
				$current_sem--;
			break;
		case "next" :
			if (isset($all_semester[$current_sem + 1]))
				$current_sem++;
			break;
		case "current" :
			break;
		default :
			if (isset($all_semester[$this->config->getValue("PersondetailsLectures", "semstart")]))
				$current_sem = $this->config->getValue("PersondetailsLectures", "semstart");
	}
	
	$last_sem = $current_sem + $this->config->getValue("PersondetailsLectures", "semrange") - 1;
	if ($last_sem < $current_sem)
		$last_sem = $current_sem;
	if (!isset($all_semester[$last_sem]))
		$last_sem = sizeof($all_semester);
	
	$out = "";
	for (;$current_sem - 1 < $last_sem; $last_sem--) {			
		$out .= "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
		$out .= "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
		if (!($this->config->getValue("PersondetailsLectures", "semstart") == "current"
				&& $this->config->getValue("PersondetailsLectures", "semrange") == 1)) {
			$out .= $subheadline_div;
			$out .= "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
			$month = date("n", $all_semester[$last_sem]["beginn"]);
			if($month > 9) {
				$out .= $this->config->getValue("PersondetailsLectures", "aliaswise");
				$out .= date(" Y/", $all_semester[$last_sem]["beginn"]) . date("y", $all_semester[$last_sem]["ende"]);
			}
			else if($month > 3 && $month < 10) {
				$out .= $this->config->getValue("PersondetailsLectures", "aliassose");
				$out .= date(" Y", $all_semester[$last_sem]["beginn"]);
			}
			$out .= "</font>$subheadline_div_end</td></tr>\n";
		}
		
		$out .= "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
		$out .= "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
		
		if ($this->config->getValue("PersondetailsLectures", "aslist")) {
			$out .= "$list_div<ul" . $this->config->getAttributes("List", "ul") . ">\n";
			foreach ($data as $dat) {
				$out .= "<li" . $this->config->getAttributes("List", "li") . ">";
				$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
				$out .= "<a href=\"\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
				$out .= $dat["name"] . "</a></font>\n";
				$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . "><br>";
				$out .= $dat["untertitel"] . "</font>\n";
			}
			$out .= "</ul>$list_div_end";
		}
		else {
			$out .= $text_div;
			$j = 0;
			foreach ($data as $dat) {
				if ($j) $out .= "<br><br>";
				$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
				$out .= "<a href=\"$lnk\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
				$out .= $dat["name"] . "</a></font>\n";
				$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
				$out .= "<br>" . $dat["untertitel"] . "</font>\n";
				$j = 1;
			}
			$out .= $text_div_end;
		}
		$out .= "</td></tr>\n";
	}
	
	if ($out) {
		$out_title = "<tr><td width=\"100%\">\n";
		$out_title .= "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		$out_title .= "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		$out_title .= "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		$out_title .= "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		$out_title .= $alias_content . "</font></td></tr>\n";
		echo $out_title . $out . "</table>\n</td></tr>\n";
	}
}

function head (&$this, $data, $a) {
	if ($this->config->getValue("Main", "showcontact")
			&& $this->config->getValue("Main", "showimage"))
		$colspan = " colspan=\"2\"";
	else
		$colspan = "";
	
	echo "<tr><td width=\"100%\">\n";
	echo "<table" . $this->config->getAttributes("PersondetailsHeader", "table") . ">\n";
	echo "<tr" . $this->config->getAttributes("PersondetailsHeader", "tr") . ">";
	echo "<td$colspan width=\"100%\"";
	echo $this->config->getAttributes("PersondetailsHeader", "headlinetd") . ">";
	echo "<font" . $this->config->getAttributes("PersondetailsHeader", "font") . ">";
	echo $data["fullname"];
	echo "</font></td></tr>\n";
	
	if ($this->config->getValue("Main", "showimage")
			|| $this->config->getValue("Main", "showcontact")) {
		echo "<tr>";
		if ($this->config->getValue("Main", "showcontact")
				&& ($this->config->getValue("Main", "showimage") == "right"
				|| !$this->config->getValue("Main", "showimage"))) {
				echo "<td" . $this->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
				echo kontakt($this, $data) . "</td>\n";
		}
		
		if ($this->config->getValue("Main", "showimage")) {
			echo "<td" . $this->config->getAttributes("PersondetailsHeader", "picturetd") . ">";
			if (file_exists("{$GLOBALS['ABSOLUTE_PATH_STUDIP']}/user/nobody.jpg")) {
				echo "<img src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}user/";
				echo "nobody.jpg\" alt=\"Foto " . $data["fullname"] . "\"";
				echo $this->config->getAttributes("PersondetailsHeader", "img") . ">";
			}
			else
				echo "&nbsp;";
		}
		
		if ($this->config->getValue("Main", "showcontact")
				&& $this->config->getValue("Main", "showimage") == "left") {
			echo "<td" . $this->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
			echo kontakt($this, $data) . "</td>\n";
		}
		
		echo "</tr>\n";
	}
	
	echo "</table>\n</td></tr>\n";
}

function kontakt (&$this, $data) {
	$attr_table = $this->config->getAttributes("Contact", "table");
	$attr_tr = $this->config->getAttributes("Contact", "table");
	$attr_td = $this->config->getAttributes("Contact", "td");
	$attr_fonttitle = $this->config->getAttributes("Contact", "fonttitle");
	$attr_fontcontent = $this->config->getAttributes("Contact", "fontcontent");
	
	$out = "<table$attr_table>\n";
	$out .= "<tr$attr_tr>";
	$out .= "<td colspan=\"2\"$attr_td>";
	$out .= "<font$attr_fonttitle>";
	if ($headline = $this->config->getValue("Contact", "headline"))
		$out .= "$headline<br><br></font>\n";
	else
		$out .= "<br></font>\n";
	
	$out .= "<font$attr_fontcontent>";
	$out .= $data["fullname"] . "<br>\n";
	
	$out .= "<br>";
	$out .= "<a href=\"\">";
	$out .= $data["Name"] . "</a>";
	if ($this->config->getValue("Contact", "adradd"))
		$out .= "<br>" . $this->config->getValue("Contact", "adradd");
	
	$out .= "<br><br>" . $data["Strasse"];
	$out .= "<br>" . $data["Plz"];
	
  $out .= "<br><br></font></td></tr>\n";
	
	$order = $this->config->getValue("Contact", "order");
	$visible = $this->config->getValue("Contact", "visible");
	$alias_contact = $this->config->getValue("Contact", "aliases");
	foreach ($order as $position) {
		if (!$visible[$position])
			continue;
		$data_field = $this->data_fields["contact"][$position];
	  if($data_field == "raum"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
	    $out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
   	  $out .= $data["raum"] . "</font></td></tr>\n";
    }

	  if($data_field == "Telefon"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
   	  $out .= $data["Telefon"] . "</font></td></tr>\n";
	  }
    
   	if($data_field == "Fax"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= $data["Fax"] . "</font></td></tr>\n";
	  }
           	
	  if($data_field == "Email"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= "<a href=\"mailto:$mail\">{$data['Email']}</a></font></td></tr>\n";
		}
        	
		if($data_field == "Home"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= $data["home"] . "</font></td></tr>\n";
		}
			
		if($data_field == "sprechzeiten"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= $data["sprechzeiten"] . "</font></td></tr>\n";
		}
	}
	$out .= "</table>\n";
	
	return $out;
}				

?>
