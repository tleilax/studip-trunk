<?php
# Lifter002: TODO
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("root");

// keep data copies for search etc.
$sess->register("save_banner_data");
$sess->register("banner_data");

if (!$BANNER_ADS_ENABLE) {
	echo '<p>', _("Banner-Modul abgeschaltet."), "</p>\n";
	include ('lib/include/html_end.inc.php');
	page_close();
	die;
}

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ('lib/msg.inc.php'); //Funktionen fuer Nachrichtenmeldungen
require_once ('lib/visual.inc.php');
require_once ('config.inc.php');
require_once('lib/classes/Table.class.php');
require_once('lib/classes/ZebraTable.class.php');

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins

// Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;


function imaging($img, $img_size, $img_name) {
	global $banner_data;
	$msg = '';
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
	if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
		$msg = "error�" . sprintf(_("Der Dateityp der Bilddatei ist falsch (%s).<br>Es sind nur die Dateiendungen .gif, .png und .jpg erlaubt!"), $ext);
		return $msg;
	}

	//na dann kopieren wir mal...
	$uploaddir = $GLOBALS['DYNAMIC_CONTENT_PATH'] . '/banner';
	$md5hash = md5($img_name+time());
	$newfile = $uploaddir . '/' . $md5hash . '.' . $ext;
	$banner_data["banner_path"] = $md5hash . '.' . $ext;
	if(!@move_uploaded_file($img,$newfile)) {
		$msg = "error�" . _("Es ist ein Fehler beim Kopieren der Datei aufgetreten. Das Bild wurde nicht hochgeladen!");
		return $msg;
	} else {
		$msg = "msg�" . _("Die Bilddatei wurde erfolgreich hochgeladen.");
		chmod($newfile, 0666 & ~umask());       // set permissions for uploaded file
	}
	return $msg;
}

//Anzeige der Bannerdaten

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


function show_banner_list($table) {
	global $db;
	$q="SELECT * FROM banner_ads ORDER BY priority DESC";
	$result = $db->query($q);
	$count=0;
	while ($db->next_record($result)) {
		$count++;
		print $table->row(array(_("Banner"),"<img src=\"".$GLOBALS['DYNAMIC_CONTENT_URL']."/banner/".$db->f("banner_path")."\" alt=\"".$db->f("alttext")."\">"),"",1);
		print $table->row(array(_("Beschreibung"),$db->f("description")),"",0);
		print $table->row(array(_("Ziel"),"(".$db->f("target_type").") " . $db->f("target")),"",0);
		print $table->row(array(_("Anzeigezeitraum"), ($db->f("startdate") ? date("d.m.Y, H:i",$db->f("startdate")) : _("sofort")) . " " . _("bis") . " " . ($db->f("enddate") ? date("d.m.Y, H:i",$db->f("enddate")) : _("unbegrenzt"))),"",0);
		print $table->row(array(_("Views"), $db->f("views")),"",0);
		print $table->row(array(_("Priorit�t (Wahrscheinlichkeit)"), $db->f("priority") . " (" . view_probability($db->f("priority")) . ")"),"",0);
		print $table->row(array("", "<a href=\"$PHP_SELF?cmd=editdb&ad_id=".$db->f("ad_id")."\"><img " . makeButton("bearbeiten","src") . " border=0/></a> <a href=\"$PHP_SELF?cmd=delete&ad_id=".$db->f("ad_id")."\"><img " . makeButton("loeschen","src") . "\" border=0></a>"),"",0);
		print $table->row(array("&nbsp;","&nbsp"),array("class"=>"blank", "bgcolor"=>"white"),0);
	}
	if ($count==0) {
		print $table->row(array("<h4>" . _("Keine Banner vorhanden.") . "</h4>"), array("colspan"=>2, "class"=>"blank"));
	}
}

function check_data(&$banner_data) {
	$msg = '';
	$db = new DB_Seminar;

	function valid_date($h,$m,$d,$mo,$y) {
		if (($h==_("hh") && $m==_("mm") && $d==_("tt") && $mo==_("mm") && $y==_("jjjj"))|| ($h+$m+$d+$mo+$y == 0)) {
			return 0; // 0= forever
		}
		// mktime return -1 if date is invalid (and does some strange
		// conversion which might be considered as a bug..)
		$x=mktime($h,$m,0,$mo,$d,$y);
		return $x;
	}

	if (!$banner_data['banner_path'])
		$msg .= 'error�' . _("Es wurde kein Bild ausgew�hlt.") . '�';

	if (!$banner_data['target'] && $banner_data['target_type'] != 'none')
		$msg .= 'error�' . _("Es wurde kein Verweisziel angegeben.") . '�';

	if (($x=valid_date($banner_data['start_hour'], $banner_data['start_minute'], $banner_data['start_day'], $banner_data['start_month'], $banner_data['start_year']))==-1)
		$msg .= 'error�' . _("Bitte geben Sie einen g�ltiges Startdatum ein.") . '�';
	else
		$banner_data['startdate']=$x;

	if (($x=valid_date($banner_data["end_hour"], $banner_data["end_minute"], $banner_data["end_day"], $banner_data["end_month"], $banner_data["end_year"]))==-1)
		$msg .= 'error�' . _("Bitte geben Sie einen g�ltiges Enddatum ein.") . '�';
	else
		$banner_data['enddate']=$x;

	switch ($banner_data['target_type']) {
		case 'url':
			 if (!eregi('^(https?)|(ftp)://', $banner_data['target'])) $msg .= "error�" . _("Das Verweisziel muss eine g�ltige URL sein (incl. http://).") . "�";
			break;
		case 'inst':
			$q = "SELECT * FROM Institute WHERE Institut_id='" . $banner_data['target'] . "'";
			$db->query($q);
			if (!$db->next_record()) {
				$msg .= "error�" . _("Die angegebene Einrichtung existiert nicht. Bitte geben Sie eine g�ltige Einrichtungs-ID ein.") .'�';
			}
			break;
		case 'user':
			$q = "SELECT * FROM auth_user_md5 WHERE username='" . $banner_data['target'] . "'";
			$db->query($q);
			if (!$db->next_record()) {
				$msg .= "error�" . _("Der angegebene Benutzername existiert nicht.") ."�";
			}
			break;
		case 'seminar':
			$q = "SELECT * FROM seminare WHERE Seminar_id='" . $banner_data["target"] . "'";
			$db->query($q);
			if (!$db->next_record()) {
				$msg .= "error�" . _("Die angegebene Veranstaltung existiert nicht. Bitte geben Sie eine g�ltige Veranstaltungs-ID ein.") . "�";
			}
			break;
		case "special":
			$msg .= 'error�' . _("Der Verweistyp \"speziell\" wird in dieser Installation nicht unterst�tzt.") . '�';
			break;
		case "none":
			$banner_data['target'] = '';
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
	global $save_banner_data;

	$table=new ZebraTable(array("bgcolor"=>"#eeeeee", "align"=>"center", "width"=>"75%", "padding"=>"2"));
	echo $table->open();
	echo $table->openRow();
	echo $table->openCell();

	// save data for lower form
	$save_banner_data=$banner_data;

	print _("Aktuelles Banner:");
	if ($banner_data["banner_path"]) {
		print "<p><img src=\"".$GLOBALS['DYNAMIC_CONTENT_URL']."/banner/" . $banner_data["banner_path"] . "\"></p>";
	} else {
		print "<p>" . _("noch kein Bild hochgeladen") . "</p>";
	}
	print "</td></tr>";
	echo $table->closeRow();

	print "<form enctype=\"multipart/form-data\" action=\"$PHP_SELF?cmd=upload&view=edit\" method=\"POST\">";
	print $table->row(array(_("1. Bilddatei ausw�hlen:")." <input name=\"imgfile\" type=\"file\" cols=45>"),"",0);
	print $table->row(array(_("2. Bilddatei hochladen:")." <input type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=\"absenden\">"),"",0);
	print "</form>";
	echo $table->close();

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
	$table=new ZebraTable(array("bgcolor"=>"#eeeeee", "align"=>"center", "width"=>"75%", "padding"=>"2"));
	echo $table->open();

	print "<form action=\"$PHP_SELF?cmd=edit&i_view=edit\" method=\"post\">";
	if ($banner_data["ad_id"]) {
		print "<input type=hidden name=\"ad_id\" value=\"" . $banner_data["ad_id"] . "\">";
	}
	if ($banner_data["banner_path"]) {
		$path_info = "<input type=hidden name=banner_path value=\"" . $banner_data["banner_path"] . "\">" . $banner_data["banner_path"];
	} else {
		$path_info = _("Noch kein Bild ausgew�hlt");
	}
	print $table->row(array(_("Pfad:"),$path_info),0);
	print $table->row(array(_("Beschreibung"),"<input type=text name=\"description\" size=\"40\" maxlen=\"254\" value=\"" . $banner_data["description"] . "\">"),0);
	print $table->row(array(_("Alternativtext"),"<input type=text name=\"alttext\" size=\"40\" maxlen=\"254\" value=\"". $banner_data["alttext"] . "\">"),0);
	$type_selector = "<select name=\"target_type\">";
	$type_selector .= select_option("url",_("URL"), $banner_data["target_type"]);
	$type_selector .= select_option("seminar",_("Veranstaltung"), $banner_data["target_type"]);
	$type_selector .= select_option("inst",_("Einrichtung"), $banner_data["target_type"]);
	$type_selector .= select_option("user",_("Benutzer"), $banner_data["target_type"]);
	$type_selector .= select_option("none",_("Kein Verweis"), $banner_data["target_type"]);
	//$type_selector .= select_option("special",_("speziell"), $banner_data["target_type"]);
	$type_selector .= "</select>";
	print $table->row(array(_("Verweis-Typ"),$type_selector),0);

	print $table->row(array(_("Verweis-Ziel"),"<input type=text name=\"target\" size=40 maxlength=254 value=\"". $banner_data["target"] . "\">"),0);

	$startdate_fields = "<input name=\"start_day\" value=\"$banner_data[start_day]\" size=2 maxlength=2>. ";
	$startdate_fields .= "<input name=\"start_month\" value=\"$banner_data[start_month]\" size=2 maxlength=2>. ";
	$startdate_fields .= "<input name=\"start_year\" value=\"$banner_data[start_year]\" size=4 maxlength=4> &nbsp; &nbsp;";
	$startdate_fields .= "<input name=\"start_hour\" value=\"$banner_data[start_hour]\" size=2 maxlength=2>:";
	$startdate_fields .= "<input name=\"start_minute\" value=\"$banner_data[start_minute]\" size=2 maxlength=2> ";
	print $table->row(array(_("Anzeigen ab:"), $startdate_fields),0);

	$enddate_fields = "<input name=\"end_day\" value=\"$banner_data[end_day]\" size=2 maxlength=2>. ";
	$enddate_fields .= "<input name=\"end_month\" value=\"$banner_data[end_month]\" size=2 maxlength=2>. ";
	$enddate_fields .= "<input name=\"end_year\" value=\"$banner_data[end_year]\" size=4 maxlength=4> &nbsp; &nbsp;";
	$enddate_fields .= "<input name=\"end_hour\" value=\"$banner_data[end_hour]\" size=2 maxlength=2>:";
	$enddate_fields .= "<input name=\"end_minute\" value=\"$banner_data[end_minute]\" size=2 maxlength=2> ";
	print $table->row(array(_("Anzeigen bis:"), $enddate_fields),0);

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
	print $table->row(array("Priorit�t:", $prio_selector),0);

	print $table->row(array("", "<input type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=\"absenden\"> <a href=\"admin_banner_ads.php\"><img " . makeButton("abbrechen", "src") . " border=0></a>"),0);

	print "</form>";
	$table->close();
}

//
// Start output
//
$container=new ContainerTable();
echo $container->headerRow("<b>&nbsp;"._("Verwaltung der Werbebanner")."</b>");
echo $container->openCell();

$content=new ContentTable();
echo $content->open();
echo $content->openRow();
echo $content->cell("<b><a href=\"$PHP_SELF?i_view=new\">&nbsp;"._("Neues Banner anlegen")."</a><b><br><br>", array("colspan"=>"2"));
echo $content->openRow();
echo $content->openCell(array("colspan"=>"2"));

$banner_data=array();

if ($cmd=="upload") {
	$msg=imaging($imgfile,$imgfile_size,$imgfile_name);
	parse_msg($msg);
	parse_msg("info�" . _("Die Daten wurden noch nicht in die Datenbank geschrieben."));
	$banner_path = $banner_data["banner_path"];
	$banner_data = $save_banner_data;
	if ($banner_path != '' ) $banner_data["banner_path"] = $banner_path;
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
		$banner_data["start_minute"] = ($starttime == 0)? _("mm"):date("i", $starttime);
		$banner_data["start_hour"]   = ($starttime == 0)? _("hh"):date("H", $starttime);
		$banner_data["start_day"]    = ($starttime == 0)? _("tt"):date("d", $starttime);
		$banner_data["start_month"]  = ($starttime == 0)? _("mm"):date("m", $starttime);
		$banner_data["start_year"]   = ($starttime == 0)? _("jjjj"):date("Y", $starttime);
		$endtime = $db->f("enddate");
		$banner_data["end_minute"] = ($endtime == 0)? _("mm"):date("i", $endtime);
		$banner_data["end_hour"]   = ($endtime == 0)? _("hh"):date("H", $endtime);
		$banner_data["end_day"]    = ($endtime == 0)? _("tt"):date("d", $endtime);
		$banner_data["end_month"]  = ($endtime == 0)? _("mm"):date("m", $endtime);
		$banner_data["end_year"]   = ($endtime == 0)? _("jjjj"):date("Y", $endtime);
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
		parse_msg("msg�" . _("Die Daten wurden erfolgreich in die Datenbank geschrieben."));
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
	edit_banner_pic($banner_data);
	print "<p>&nbsp;</p>";
	edit_banner_data($banner_data);
} else if ($i_view=="edit") {
	edit_banner_pic($banner_data);
	print "<p>&nbsp;</p>";
	edit_banner_data($banner_data);
} else {
	$table=new ZebraTable(array("bgcolor"=>"#eeeeee", "align"=>"center", "width"=>"75%", "padding"=>"2"));
	echo $table->open();
	show_banner_list($table);
	echo $table->close();
}

echo $content->close();
echo $container->blankRow();
echo $container->close();
include ('lib/include/html_end.inc.php');
page_close();
// <!-- $Id$ -->
?>
