<?php
/*
admin_banner_ads.php - Werbebanner-Verwaltung von Stud.IP.
Copyright (C) 2003 Tobias Thelen <tthelen@uos.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("root");

// keep data copies for search etc.
$sess->register("save_banner_data");
$sess->register("banner_data");

if (!$BANNER_ADS_ENABLE) {
	print "<p>Module disabled.</p>";
	print "</body></html>";
	page_close();
	die;
}

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php"); //Funktionen fuer Nachrichtenmeldungen
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins

$cssSw=new cssClassSwitcher;

// Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;


function imaging($img, $img_size, $img_name) {
	global $banner_data;

	if (!$img_name) { //keine Datei ausgew�hlt!
		return "error�" . _("Sie haben keine Datei zum Hochladen ausgew�hlt!");
	}

	//Dateiendung bestimmen
	$dot = strrpos($img_name,".");
	if ($dot) {
		$l = strlen($img_name) - $dot;
		$ext = strtolower(substr($img_name,$dot+1,$l));
	}
	//passende Endung ?
	if ($ext != "jpg" && $ext != "gif" ) {
		$msg = "error�" . sprintf(_("Der Dateityp der Bilddatei ist falsch (%s).<br>Es sind nur die Dateiendungen .gif und .jpg erlaubt!"), $ext);
		return $msg;
	}

	//na dann kopieren wir mal...
	$uploaddir="./banner";
	$md5hash=md5($img_name+time());
	$newfile = $uploaddir . "/" . $md5hash . "." . $ext;
	$banner_data["banner_path"] = $md5hash . "." . $ext;
	if(!@copy($img,$newfile)) {
		$msg = "error�" . _("Es ist ein Fehler beim Kopieren der Datei aufgetreten. Das Bild wurde nicht hochgeladen!");
		return $msg;
	} else {
		$msg = "msg�" . _("Die Bilddatei wurde erfolgreich hochgeladen.");
	}
	return $msg;
}

//Anzeige der Bannerdaten
function wrap_table_row($category, $value) {
	global $cssSw;
	$x = "<tr>";
	$x .= "<td class=\"";
	$cssSw->switchClass(); 
	$x .= $cssSw->getClass() . "\">";
	$x .= $category;
	$x .= "</td>";
	$x .= "<td class=\"" . $cssSw->getClass() . "\">";
	$x .= $value . "</td></tr>";
	return $x;
}

function view_probability($prio) {
	static $computed=0, $sum=0;

	if ($prio==0) return "--";

	if (!$computed) {
		$db=new DB_Seminar;
		$q="SELECT priority FROM banner_ads WHERE priority>0";
		$result=$db->query($q);
		$sum=0;
		while ($db->next_record($result)) {
			$sum += pow(2,$db->f("priority"));
		}
		$computed=1;
	}
	return "1/" . (1/(pow(2,$prio)/$sum));
}
	

function show_banner_list($cssSw) {
	global $db;
	$q="SELECT * FROM banner_ads ORDER BY priority DESC";
	$result = $db->query($q);
	$count=0;
	while ($db->next_record($result)) {
		$count++;
		print wrap_table_row("Banner","<img src=\"./pictures/banner/".$db->f("banner_path")."\" alt=\"".$db->f("alttext")."\">");
		print wrap_table_row("Beschreibung",$db->f("description"));
		print wrap_table_row("Ziel","(".$db->f("target_type").") " . $db->f("target"));
		print wrap_table_row("Anzeigezeitraum", ($db->f("startdate") ? date("d.m.Y, h:i") : _("sofort")) . " " . _("bis") . " " . ($db->f("enddate") ? date("d.m.Y, h:i") : _("unbegrenzt")));
		print wrap_table_row("Views/Clicks/Clickrate", $db->f("views") . " / " . $db->f("clicks") . " / " . ($db->f("clicks") ? ($db->f("views")/$db->f("clicks")) : "-"));
		print wrap_table_row("Priorit�t (Wahrscheinlichkeit)", $db->f("priority") . " (" . view_probability($db->f("priority")) . ")");
		print wrap_table_row("", "<a href=\"$PHP_SELF?cmd=editdb&ad_id=".$db->f("ad_id")."\"><img " . makeButton("bearbeiten","src") . " border=0/></a> <a href=\"$PHP_SELF?cmd=delete&ad_id=".$db->f("ad_id")."\"><img " . makeButton("loeschen","src") . "\" border=0></a>");
		print "<tr><td colspan=2 class=blank>&nbsp;</td></tr>";
	}
	if ($count==0) {
		echo "<tr><td colspan=2 class=blank><h4>" . _("Keine Banner vorhanden.") . "</h4></td></tr>";
	}
}

function check_data($banner_data) {
	$msg="";
	function valid_date($h,$m,$d,$mo,$y) {
		if ($h==_("hh") && $m==_("mm") && $d==_("tt") && $mo==_("mm") && $y==_("jjjj")) {
			return 0; // 0= forever
		}
		// mktime return -1 if date is invalid (and does some strange
		// conversion which might be considered as a bug..)
		$x=mktime($h,$m,0,$mo,$d,$y);
		return $x;
	}

	if (!$banner_data["banner_path"]) 
		$msg .= "error�"._("Es wurde kein Bild ausgew�hlt.�");

	if (!$banner_data["target"] && $banner_data["target_type"]!="none") 
		$msg .= "error�"._("Es wurde kein Verweisziel angegeben.�");

	if ($x=valid_date($banner_data["start_hour"], $banner_data["start_minute"], $banner_data["start_day"], $banner_data["start_month"], $banner_data["start_year"])==-1) 
		$msg .= "error�Bitte geben Sie einen g�ltiges Startdatum ein.�";
	else 
		$banner_data["startdate"]=$x;

	if ($x=valid_date($banner_data["end_hour"], $banner_data["end_minute"], $banner_data["end_day"], $banner_data["end_month"], $banner_data["end_year"])==-1) 
		$msg .= "error�Bitte geben Sie einen g�ltiges Startdatum ein.�";
	else 
		$banner_data["enddate"]=$x;

	switch ($banner_data["target_type"]) {
		case "url":
			// if (!preg_match("#http://#", $banner_data["target"])) $msg .= "error�Das Verweisziel muss eine g�ltige URL sein.�";
			break;
		case "inst":
			$msg .= "error�Der Verweistyp \"Einrichtung\" wird in dieser Installation nicht unterst�tzt.�";
			break;
		case "user":
			$db=new DB_Seminar;
			$q="SELECT * FROM auth_user_md5 WHERE username='" . $banner_data["target"] . "'";
			$db->query($q);
			if (!$db->next_record()) {
				$msg .= "error�" . _("Der angegebene Benutzername existiert nicht.�");
			}
			break;
		case "seminar":
			$db=new DB_Seminar;
			$q="SELECT * FROM seminare WHERE Seminar_id='" . $banner_data["target"] . "'";
			$db->query($q);
			if (!$db->next_record()) {
				$msg .= "error�" . _("Die angegebene Veranstaltung existiert nicht.�");
			}
			break;
		case "special":
			$msg .= "error�Der Verweistyp \"speziell\" wird in dieser Installation nicht unterst�tzt.�";
			break;
		case "none":
			$banner_data["target"]="";
			break;
	}
	return $msg;
}

function write_data_to_db($banner_data) {
	global $db;

	if ($banner_data["ad_id"]) {
		$q = "UPDATE banner_ads SET ";
	} else {
		$md5hash=md5($banner_data["banner_path"]+time());
		$q = "INSERT INTO banner_ads SET ";
		$q .= "ad_id = '$md5hash', ";
		$q .= "clicks = '0', ";
		$q .= "views = '0', ";
		$q .= "mkdate = '". time() ."', ";
	}
	$q .= "banner_path = '$banner_data[banner_path]', ";
	$q .= "description = '$banner_data[description]', ";
	$q .= "alttext = '$banner_data[alttext]', ";
	$q .= "target_type = '$banner_data[target_type]', ";
	$q .= "target = '$banner_data[target]', ";
	$q .= "startdate = '$banner_data[startdate]', ";
	$q .= "enddate = '$banner_data[enddate]', ";
	$q .= "priority = '$banner_data[priority]', ";
	$q .= "chdate = '". time() ."' ";
	if ($banner_data["ad_id"]) {
		$q .= "WHERE ad_id='". $banner_data["ad_id"] . "'";
	}
	$db->query($q);
}

function edit_banner_pic($banner_data) {
	global $cssSw, $save_banner_data;

	print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" width=\"75%\" cellspacing=0 cellpadding=2>";
	$cssSw->switchClass();
	print "<tr><td colspan=2 class=\"" . $cssSw->getClass() . "\">";

	// save data for lower form
	$save_banner_data=$banner_data;

	print _("Aktuelles Banner:");
	if ($banner_data["banner_path"]) {
		print "<p><img src=\"pictures/banner/" . $banner_data["banner_path"] . "\"></p>";
	} else {
		print "<p>" . _("noch kein Bild hochgeladen") . "</p>";
	}
	print "</td></tr>";

	print "<form enctype=\"multipart/form-data\" action=\"$PHP_SELF?cmd=upload&view=edit\" method=\"POST\">";
	print wrap_table_row("1. Bilddatei ausw�hlen:", "<input name=\"imgfile\" type=\"file\" cols=45>");
	print wrap_table_row("2. Bilddatei hochladen:", "<input type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=\"" . _("absenden") . "\">");
	print "</form>";
	print "</table>";

}

function edit_banner_data($banner_data) {

	function select_option($name, $printname, $checkval) {
		$x = "<option value=\"$name\"";
		if ($checkval==$name) {
			$x .= " selected";
		}
		$x .= ">" . $printname . "</option>";
		return $x;
	}

	print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" width=\"75%\" cellspacing=0 cellpadding=2>";
	print "<form action=\"$PHP_SELF?cmd=edit&i_view=edit\" method=\"post\">";
	if ($banner_data["ad_id"]) {
		print "<input type=hidden name=\"ad_id\" value=\"" . $banner_data["ad_id"] . "\">";
	}
	if ($banner_data["banner_path"]) {
		$path_info = "<input type=hidden name=banner_path value=\"" . $banner_data["banner_path"] . "\">" . $banner_data["banner_path"];
	} else {
		$path_info = _("Noch kein Bild ausgew�hlt");
	}
	print wrap_table_row("Pfad:",$path_info);
	print wrap_table_row("Beschreibung","<input type=text name=\"description\" size=\"40\" maxlen=\"254\" value=\"" . $banner_data["description"] . "\">");
	print wrap_table_row("Alternativtext","<input type=text name=\"alttext\" size=\"40\" maxlen=\"254\" value=\"". $banner_data["alttext"] . "\">");
	$type_selector = "<select name=\"target_type\">";
	$type_selector .= select_option("url",_("URL"), $banner_data["target_type"]);
	$type_selector .= select_option("seminar",_("Veranstaltung"), $banner_data["target_type"]);
	$type_selector .= select_option("inst",_("Einrichtung"), $banner_data["target_type"]);
	$type_selector .= select_option("user",_("Benutzer"), $banner_data["target_type"]);
	$type_selector .= select_option("none",_("Kein Verweis"), $banner_data["target_type"]);
	//$type_selector .= select_option("special",_("speziell"), $banner_data["target_type"]);
	$type_selector .= "</select>";
	print wrap_table_row("Verweis-Typ",$type_selector);

	print wrap_table_row("Verweis-Ziel","<input type=text name=\"target\" size=40 maxlength=254 value=\"". $banner_data["target"] . "\">");

	$startdate_fields = "<input name=\"start_day\" value=\"tt\" size=2>. ";
	$startdate_fields .= "<input name=\"start_month\" value=\"mm\" size=2>. ";
	$startdate_fields .= "<input name=\"start_year\" value=\"jjjj\" size=4> &nbsp; &nbsp;";
	$startdate_fields .= "<input name=\"start_hour\" value=\"hh\" size=2>:";
	$startdate_fields .= "<input name=\"start_minute\" value=\"mm\" size=2> ";
	print wrap_table_row("Anzeigen ab:", $startdate_fields);

	$enddate_fields = "<input name=\"end_day\" value=\"tt\" size=2>. ";
	$enddate_fields .= "<input name=\"end_month\" value=\"mm\" size=2>. ";
	$enddate_fields .= "<input name=\"end_year\" value=\"jjjj\" size=4> &nbsp; &nbsp;";
	$enddate_fields .= "<input name=\"end_hour\" value=\"hh\" size=2>:";
	$enddate_fields .= "<input name=\"end_minute\" value=\"mm\" size=2> ";
	print wrap_table_row("Anzeigen bis:", $enddate_fields);

	$prio_selector = "<select name=\"priority\">";
	$prio_selector .= select_option("0", _("0 (nicht anzeigen)"), $banner_data[priority]);
	$prio_selector .= select_option("1", _("1 (sehr niedrig)"), $banner_data[priority]);
	$prio_selector .= select_option("2", _("2"), $banner_data[priority]);
	$prio_selector .= select_option("3", _("3"), $banner_data[priority]);
	$prio_selector .= select_option("4", _("4"), $banner_data[priority]);
	$prio_selector .= select_option("5", _("5"), $banner_data[priority]);
	$prio_selector .= select_option("6", _("6"), $banner_data[priority]);
	$prio_selector .= select_option("7", _("7"), $banner_data[priority]);
	$prio_selector .= select_option("8", _("8"), $banner_data[priority]);
	$prio_selector .= select_option("9", _("9"), $banner_data[priority]);
	$prio_selector .= select_option("10", _("10 (sehr hoch)"), $banner_data[priority]);
	$prio_selector .= "</select>";
	print wrap_table_row("Priorit�t:", $prio_selector);

	print wrap_table_row("", "<input type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=\"absenden\"> <a href=\"admin_banner_ads.php\"><img " . makeButton("abbrechen", "src") . " border=0></a>");

	print "</form>";
	print "</table>";

	print "</form>";
}


?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;<?=_("Verwaltung der Werbebanner")?></b></td>
</tr>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>
<tr><td class="blank" colspan=2><b><a href="<?echo $PHP_SELF?>?i_view=new">&nbsp;<?=_("Neues Banner anlegen")?></a><b><br><br></td></tr>

<tr><td class="blank" colspan=2>
<table border=0 bgcolor="#eeeeee" align="center" width="75%" cellspacing=0 cellpadding=2>
<?

$banner_data=array();

if ($cmd=="upload") {
	$msg=imaging($imgfile,$imgfile_size,$imgfile_name);
	parse_msg($msg);
	parse_msg("msg�" . _("Die Daten wurden noch nicht in die Datenbank geschrieben."));
	$banner_path=$banner_data["banner_path"];
	$banner_data=$save_banner_data;
	$banner_data["banner_path"]=$banner_path;
	$i_view="edit";
} elseif ($cmd=="delete") {
	$q="DELETE FROM banner_ads WHERE ad_id='".$ad_id."'";
	$db->query($q);
	parse_msg("msg�". _("Banner gel�scht"));
	$i_view="list";
} elseif ($cmd=="editdb") {
	$q="SELECT * FROM banner_ads WHERE ad_id='" . $ad_id . "'";
	$result = $db->query($q);
	if ($db->next_record($result)) {
		$banner_data["ad_id"]=$db->f("ad_id");
		$banner_data["target"]=$db->f("target");
		$banner_data["target_type"]=$db->f("target_type");
		$banner_data["description"]=$db->f("description");
		$banner_data["alttext"]=$db->f("alttext");
		$banner_data["banner_path"]=$db->f("banner_path");
		$starttime=$db->f("startdate");
		$banner_data["start_minute"] = date("i", $starttime);
		$banner_data["start_hour"] = date("h", $starttime);
		$banner_data["start_day"] = date("d", $starttime);
		$banner_data["start_month"] = date("m", $starttime);
		$banner_data["start_year"] = date("Y", $starttime);
		$endtime = $db->f("enddate");
		$banner_data["end_minute"] = date("i", $endtime);
		$banner_data["end_hour"] = date("h", $endtime);
		$banner_data["end_day"] = date("d", $endtime);
		$banner_data["end_month"] = date("m", $endtime);
		$banner_data["end_year"] = date("Y", $endtime);
		$banner_data["priority"]= $db->f("priority");

		$i_view="edit";
	} else {
		parse_msg("error�" . _("Ung�ltige Banner-ID"));
	}
} elseif ($cmd=="edit") {
	if ($ad_id) {
		$banner_data["ad_id"]=$ad_id;
	}
	$banner_data["target"]=$target;
	$banner_data["target_type"]=$target_type;
	$banner_data["description"]=$description;
	$banner_data["alttext"]=$alttext;
	$banner_data["banner_path"]=$banner_path;
	$banner_data["start_minute"]=$start_minute;
	$banner_data["start_hour"]=$start_hour;
	$banner_data["start_day"]=$start_day;
	$banner_data["start_month"]=$start_month;
	$banner_data["start_year"]=$start_year;
	$banner_data["end_minute"]=$end_minute;
	$banner_data["end_hour"]=$end_hour;
	$banner_data["end_day"]=$end_day;
	$banner_data["end_month"]=$end_month;
	$banner_data["end_year"]=$end_year;
	$banner_data["priority"]=$priority;
	$msg=check_data($banner_data);
	if ($msg) {
		parse_msg($msg);
		$i_view="edit";
	} else {
		write_data_to_db($banner_data);
		parse_msg("msg�Die Daten wurden erfolgreich in die Datenbank geschrieben.");
		$i_view="list";
	}
}

if ($i_view=="new") {
	$banner_data["target"]="";
	$banner_data["target_type"]="url";
	$banner_data["description"]="";
	$banner_data["alttext"]="";
	if (!$banner_data["banner_path"]) {
		$banner_data["banner_path"]="";
	}
	$banner_data["start_minute"]=_("mm");
	$banner_data["start_hour"]=_("hh");
	$banner_data["start_day"]=_("tt");
	$banner_data["start_month"]=_("mm");
	$banner_data["start_year"]=_("jjjj");
	$banner_data["end_minute"]=_("mm");
	$banner_data["end_hour"]=_("hh");
	$banner_data["end_day"]=_("tt");
	$banner_data["end_month"]=_("mm");
	$banner_data["end_year"]=_("jjjj");
	$banner_data["priority"]="1";
	edit_banner_pic($banner_data["banner_path"]);
	print "<p>&nbsp;</p>";
	edit_banner_data($banner_data, $cssSw);
} else if ($i_view=="edit") {
	edit_banner_pic($banner_data);
	print "<p>&nbsp;</p>";
	edit_banner_data($banner_data, $cssSw);
} else {
	show_banner_list($cssSw);
}

?>
</table>
</table>

<?
page_close();
?>
</body>
</html>
<!-- $Id$ -->
