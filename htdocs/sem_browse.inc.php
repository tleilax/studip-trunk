<?
/*
sem_browse.inc.php - Universeller Seminarbrowser zum Includen, Stud.IP
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>

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

//Settings for the Script

/*If you want to switch the colors of the header of each different group
(e.g. Semester or Dozent) set this to TRUE. If it it not set, the color is
always a light red */
$sem_browse_switch_headers=FALSE;

//includes
require_once "$ABSOLUTE_PATH_STUDIP/config.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/config_tools_semester.inc.php"; 
require_once "$ABSOLUTE_PATH_STUDIP/dates.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/functions.php";

//init classes
$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$cssSw=new cssClassSwitcher; // Klasse f�r Zebra-Design
$cssSw->enableHover();

$sess->register("sem_browse_data");

//Alle frisch reingekommenen Variablen in Sessionvariable uebernehmen
if ($send) {	
	$sem_browse_data["qs_string"]=stripslashes($qs_string);
	$sem_browse_data["s_titel"]=stripslashes($s_titel);
	$sem_browse_data["s_art"]=$s_art;
	$sem_browse_data["s_dozent"]=stripslashes($s_dozent);
	$sem_browse_data["s_sem"]=$s_sem;
	$sem_browse_data["s_untert"]=stripslashes($s_untert);
	$sem_browse_data["s_bereich"]=$s_bereich;
	$sem_browse_data["s_class"]=$s_class;	
	$sem_browse_data["s_kommentar"]=stripslashes($s_kommentar);
	$sem_browse_data["s_bool"]=$s_bool;
	}
if ($s_range) $sem_browse_data["s_range"]=$s_range;
if ($id) $sem_browse_data["id"]=$id;
if ($oid) $sem_browse_data["oid"]=$oid;
if ($oid2) $sem_browse_data["oid2"]=$oid2;
if ($extern) $sem_browse_data["extern"]=$extern;
if ($extend) $sem_browse_data["extend"]=$extend;
if ($sset) $sem_browse_data["sset"]=$sset;
if ($cmd) $sem_browse_data["cmd"]=$cmd;
if ($level) $sem_browse_data["level"]=$level;
if ($group_by) $sem_browse_data["group_by"]=$group_by;

if ((!$sem_browse_data["cmd"]) && ($root_mode))
	$sem_browse_data["cmd"]=xts;

//default group_by mode
if (!$sem_browse_data["group_by"])
	$sem_browse_data["group_by"]="semester";

if (($i_page=="show_bereich.php") && (!$extern))
	$sem_browse_data["extern"]=TRUE;
elseif (!$extern)
	$sem_browse_data["extern"]=FALSE;

//Zuruecksetzen
if (($reset_all) || ($level == "O")){
	$tmp_cmd=$sem_browse_data["cmd"];	
	$sem_browse_data='';
	$sem_browse_data["cmd"]=$tmp_cmd;	
	}

if (!isset ($sem_browse_data["level"])) $sem_browse_data["level"]="f";
if ($level==0) $sem_browse_data["extern"] == FALSE;


//We want to show the search forms only in non-browsing mode
if ((!$sem_browse_data["extern"]) && ($sem_browse_data["sset"] || $sem_browse_data["level"]=="f")) {
	
	//Quicksort Formular... fuer die eiligen oder die DAUs....
	if (($sem_browse_data["cmd"]=="qs") || ($sem_browse_data["cmd"]=="") || (!isset($sem_browse_data["cmd"]))) {
		echo "<form action=\"$PHP_SELF?send=yes&sset=qs\" method=\"post\">\n";
		echo "<table border=0 align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
		echo "<tr><td class=\"steel1\" align=\"center\"  colspan=2>Schnellsuche:&nbsp;<select name=s_range size=1>";
		if ($sem_browse_data["s_range"] == "alles")
			echo "<option selected>alles</option>";
		else
			echo "<option>alles</option>";
		if ($sem_browse_data["s_range"] == "Titel")
			echo "<option selected>Titel</option>";
		else
			echo "<option>Titel</option>";
		if ($sem_browse_data["s_range"] == "Dozent")
			echo "<option selected>Dozent</option>";
		else
			echo "<option>Dozent</option>";
		
		if ($sem_browse_data["s_range"] == "Kommentar")
			echo "<option selected>Kommentar</option>";
		else
			echo "<option>Kommentar</option>";
		echo "</select>&nbsp;<input name=qs_string type=textarea size=20 maxlength=255 value=\"".htmlReady($sem_browse_data["qs_string"])."\">\n";
		echo "<input type=\"IMAGE\" src=\"pictures/buttons/suchestarten-button.gif\"  border=0 value=\"Suche starten\">&nbsp;<a href=\"$PHP_SELF?reset_all=true\"><img src=\"pictures/buttons/neuesuche-button.gif\" border=0></a>\n";
		echo "<a href=\"$PHP_SELF?cmd=xts";
		if (isset($sem_browse_data["sset"])) echo "&sset=", $sem_browse_data["sset"];
		echo "\"><img src=\"pictures/buttons/erweitertesuche-button.gif\" border=0></a></td></tr>";
		echo "<tr><td class=\"steel1\" colspan=2 align=\"center\"><hr></td></tr>";
		echo "</table>\n";
		}
	
	//Extended Sortformular, fuer Leute mit mehr GRiPS...
	if (($sem_browse_data["cmd"]=="xts"))
		{
		echo "<form action=\"$PHP_SELF?send=yes&sset=xts\" method=\"post\">\n";
		echo "<table border=0 align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">Titel: </td><td class=\"steel1\" align=\"left\" width=\"35%\"><input name=s_titel type=textarea size=40 maxlength=255 value=\"".htmlReady($sem_browse_data["s_titel"])."\"></td><td class=\"steel1\" align=\"right\" width=\"15%\">Typ:</td><td class=\"steel1\" align=\"left\" width=\"35%\"><select name=s_art size=1>";
		if ($sem_browse_data["s_art"]=="alle")
			echo "<option selected>alle</option>";
		else
			echo "<option>alle</option>";
		for ($i=1; $i <= sizeof($SEM_TYPE); $i++)
			{
			if (($show_class) && ($show_class == $SEM_TYPE[$i]["class"])) {
				echo "<option";
				if ($sem_browse_data["s_art"]==$i)
					echo" selected";
				echo " value=\"$i\">", my_substr($SEM_TYPE[$i]["name"], 0, 30), "</option>";
				}
			elseif (!$show_class) {
				echo "<option";
				if ($sem_browse_data["s_art"]==$i)
					echo" selected";
				echo " value=\"$i\">", my_substr($SEM_TYPE[$i]["name"], 0, 30), " (", $SEM_CLASS[$SEM_TYPE[$i]["class"]]["name"],")</option>";
				}
			}
		echo "</select></td></tr>\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">Untertitel: </td><td  class=\"steel1\" align=\"left\" width=\"35%\"><input name=s_untert type=textarea size=40 maxlength=255 value=\"".htmlReady($sem_browse_data["s_untert"])."\"></td><td class=\"steel1\" align=\"right\" width=\"15%\">Semester: </td><td class=\"steel1\" align=\"left\" width=\"35%\"><select name=s_sem size=1>";
			$i=1;
			for ($i; $i <= sizeof($SEMESTER); $i++)
				{
				echo "<option";			
				if (($sem_browse_data["s_sem"]==$SEMESTER[$i]["name"]) || 
					((!$sem_browse_data["s_sem"]) && ((($SEMESTER[$i]["name"] == $SEM_NAME_NEXT) && ($VORLES_ENDE < time())) ||  (($SEMESTER[$i]["name"] == $SEM_NAME) && ($VORLES_ENDE > time())))))
					echo" selected";
				echo ">", $SEMESTER[$i]["name"], "</option>";
				}
		if ($sem_browse_data["s_sem"]=="alle")
			echo "<option selected>alle</option>";
		else
			echo "<option>alle</option>";
		echo "</select>";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">Kommentar: </td><td class=\"steel1\" align=\"left\" width=\"35%\"><input name=s_kommentar type=textarea size=40 maxlength=255 value=\"".htmlReady($sem_browse_data["s_kommentar"])."\"></td><td class=\"steel1\" align=\"right\" width=\"15%\">&nbsp;</td><td class=\"steel1\" align=\"left\" width=\"35%\">&nbsp; </td></tr>\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">DozentIn: </td><td class=\"steel1\" align=\"left\" width=\"35%\"><input name=s_dozent type=textarea size=20 maxlength=255 value=\"".htmlReady($sem_browse_data["s_dozent"])."\"></td><td class=\"steel1\" align=\"right\" width=\"15%\">Verkn&uuml;pfung: </td><td class=\"steel1\" align=\"left\" width=\"35%\"><select name=s_bool size=1><option selected>UND<option>ODER</select></td></tr>\n";
	
		$tmp_cs=4;
		echo "<tr>";
	
		if (!$hide_bereich) {
			$tmp_cs=2;
			echo "<td class=\"steel1\" align=\"right\" width=\"15%\">Bereich: </td><td class=\"steel1\" align=\"left\" width\"35%\"><select name=s_bereich>";
	
			if ($sem_browse_data["s_bereich"]=="alle")
				echo "<option selected>alle</option>";
			else
				echo "<option>alle</option>";

			//bauen des Bereichspulldownfeld
			$fachtmp="0";
			$db->query("SELECT a.bereich_id, a.name, b.fach_id, c.name AS fachname FROM bereiche a LEFT JOIN bereich_fach b USING(bereich_id) LEFT JOIN faecher c USING (fach_id) ORDER BY c.fach_id,a.name");
			while ($db->next_record()) 
				{
				if ($fachtmp != $db->f("fach_id"))
					{
					// Hier werden die Faecherueberschriften ausgegeben 
					$fachtmp = $db->f("fach_id");
					echo "<option value = nix>------------------------------------------------------------</option>";
					echo "<option value = nix>".htmlReady(my_substr($db->f("fachname"), 0,30))."</option>";
					echo "<option value = nix>------------------------------------------------------------</option>";
					}
				$bereichtmp =  $db->f("bereich_id");
				if($sem_browse_data["s_bereich"]==$db->f("bereich_id"))
				echo "<option selected VALUE=\"".$db->f("bereich_id")."\">&nbsp;".htmlReady(my_substr($db->f("name"), 0, 30))."</option>";
			else
				echo "<option  VALUE=\"".$db->f("bereich_id")."\">&nbsp;".htmlReady(my_substr($db->f("name"), 0, 30))."</option>";
			  $fachtmp = $db->f("fach_id");
		  	}
			echo "</select></td>";
		}
		if (!$show_class) {
			echo "<td class=\"steel1\" align =\"right\"width =\"15%\">Kategorie: </td><td class=\"steel1\" width=\"35%\"><select name=\"s_class\"><option";
			if (!$sem_browse_data["s_class"])
				echo " selected";
			echo " value=0>alle</option>";
			$i=1;
			foreach ($SEM_CLASS as $a) {
				echo "<option value=$i";
				if ($sem_browse_data["s_class"]==$i)
					echo " selected";
				echo ">",$a["name"], "</option>";
				$i++;
				}
			echo "</select>";
			}
		else
			echo "<td class=\"steel1\" width =\"100%\" colspan=$tmp_cs>&nbsp; </td>";
		echo "</tr>";
		echo "<tr><td class=\"steel1\">&nbsp</td><td class=\"steel1\" align=\"left\"><input  type=\"IMAGE\" src=\"pictures/buttons/suchestarten-button.gif\" border=0 value=\"Suche starten\">&nbsp;<a href=\"$PHP_SELF?reset_all=true\"><img src=\"pictures/buttons/neuesuche-button.gif\" border=0></a></td><td class=\"steel1\">&nbsp;</td><td class=\"steel1\"><a href=\"$PHP_SELF?cmd=qs";
			if (isset($sem_browse_data["sset"])) echo "&sset=", $sem_browse_data["sset"];
		echo  "\"><img src=\"pictures/buttons/schnellsuche-button.gif\" border=0></a></td></tr>\n";
		echo "<tr><td class=\"steel1\" colspan=4 align=\"center\"><hr></td></tr>";
		echo "</table>\n";
	}

//header to reset (start a new) search
} elseif (!$sem_browse_data["extern"]) {
	echo "<form action=\"$PHP_SELF\" method=\"POST\"><table border=0 align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
	echo "<tr><td class=\"steel1\" align=\"center\">";
	echo "<a href=\"$PHP_SELF?reset_all=true\">".makeButton("neuesuche")."</a>\n";
	echo "</table>\n";
} else
	echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";

//Parser zur Auswertung des Suchstrings
if (!$sem_browse_data["extern"]) {

if ($sem_browse_data["sset"])
	{
	$sem_browse_data["level"]="s";

	if ($sem_browse_data["sset"]=="qs")
		{

		//Empty searchstring?	
		if (!$sem_browse_data["qs_string"])
			$dont_search=TRUE;
		else {

		if ($sem_browse_data["s_range"]=="alles")
			{
			$sql_where_query_seminare="(seminare.Name LIKE '%".$sem_browse_data["qs_string"]."%' OR seminare.Untertitel LIKE '%".$sem_browse_data["qs_string"]."%' OR seminare.Beschreibung LIKE '%".$sem_browse_data["qs_string"]."%' OR auth_user_md5.Nachname LIKE '%".$sem_browse_data["qs_string"]."%'";
			}
		elseif ($sem_browse_data["s_range"]=="Dozent")
			{
			$sql_where_query_seminare="(auth_user_md5.Nachname LIKE '%".$sem_browse_data["qs_string"]."%'";
			}
		elseif ($sem_browse_data["s_range"]=="Titel")
			{
			$sql_where_query_seminare="(seminare.Name LIKE '%".$sem_browse_data["qs_string"]."%' OR seminare.Untertitel LIKE '%".$sem_browse_data["qs_string"]."%'";
			}
		elseif ($sem_browse_data["s_range"]=="Kommentar")
			{
			$sql_where_query_seminare="(seminare.Beschreibung LIKE '%".$sem_browse_data["qs_string"]."%'";
			}
		/* Uncomment to let the quicksearch search only in the current semester
		if (!$all_sem) {
			if ($VORLES_ENDE <= time())
				$sql_where_query_seminare=$sql_where_query_seminare.") AND seminare.start_time <=".$SEM_BEGINN_NEXT." AND (".$SEM_BEGINN_NEXT." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1)";
			else
				$sql_where_query_seminare=$sql_where_query_seminare.") AND seminare.start_time <=".$SEM_BEGINN." AND (".$SEM_BEGINN." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1)";
			}
		else*/
		$sql_where_query_seminare=$sql_where_query_seminare. ") ";
		}
		}
	elseif ($sem_browse_data["sset"]=="xts")
		{
		if ($sem_browse_data["s_bool"]=="UND")
			{
			$sql_where_query_seminare="(seminare.Name LIKE '%".$sem_browse_data["s_titel"]."%' AND seminare.Untertitel LIKE '%".$sem_browse_data["s_untert"]."%' AND seminare.Beschreibung LIKE '%".$sem_browse_data["s_kommentar"]."%' AND auth_user_md5.Nachname LIKE '%".$sem_browse_data["s_dozent"]."%'";
			if ($sem_browse_data["s_sem"] !="alle")
				{
				$i=0;
				for ($i; $i <=sizeof($SEMESTER); $i++)
					{
					if ($SEMESTER[$i]["name"] == $sem_browse_data["s_sem"])
						{
						$sql_where_query_seminare=$sql_where_query_seminare."AND seminare.start_time <=".$SEMESTER[$i]["beginn"]." AND (".$SEMESTER[$i]["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1)";
						}
					}
				}
			if ($sem_browse_data["s_art"] != "alle")
				{
				$sql_where_query_seminare=$sql_where_query_seminare." AND seminare.status LIKE '%".$sem_browse_data["s_art"]."%'";
				}
			if (!$hide_bereich)
				if (($sem_browse_data["s_bereich"] !="nix") && ($sem_browse_data["s_bereich"] !="alle"))
					{
					$sql_where_query_seminare=$sql_where_query_seminare." AND seminar_bereich.bereich_id = '".$sem_browse_data["s_bereich"]."'";
					}
			}
		else
			{
			$sql_where_query_seminare="(seminare.Name LIKE '%".$sem_browse_data["s_titel"]."%' OR seminare.Untertitel LIKE '%".$sem_browse_data["s_untert"]."%' OR seminare.Beschreibung LIKE '%".$sem_browse_data["s_kommentar"]."%' OR auth_user_md5.Nachname LIKE '%".$sem_browse_data["s_dozent"]."%' OR seminare.status LIKE '%".$sem_browse_data["s_art"]."%'";
			if ($sem_browse_data["s_sem"] !="alle")
				{
				$i=0;
				for ($i; $i <=sizeof($SEMESTER); $i++)
					{
					if ($SEMESTER[$i]["name"] == $sem_browse_data["s_sem"])
						{
						$sql_where_query_seminare=$sql_where_query_seminare." AND seminare.start_time <=".$SEMESTER[$i]["beginn"]." AND (".$SEMESTER[$i]["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1)";
						}
					}
				}
			if ($sem_browse_data["s_art"] != "alle")
				{
				$sql_where_query_seminare=$sql_where_query_seminare." AND seminare.status LIKE '%".$sem_browse_data["s_art"]."%'";
				}
			}
		$sql_where_query_seminare.=")";
		}
	}
	}
	
//Erweiterung des query um Klasseneingrenzung
if (($show_class) || ($sem_browse_data["s_class"])){
	if ($sem_browse_data["s_class"])
		$show_class=$sem_browse_data["s_class"];
	$tmp_classes='';
	$i=1;
	foreach ($SEM_TYPE as $a) {
		if ($a["class"] == $show_class)
			$tmp_classes[]=$i;
		$i++;
		}
	$class_query.=" seminare.status in (";
	$i=0;
	foreach ($tmp_classes as $a) {
		if ($i)
			$class_query.=", ";
		$class_query.="'$a'";
		$i++;
		}
	$class_query.=" )";
	if ($sql_where_query_seminare)
		$sql_where_query_seminare.=" AND";
	$sql_where_query_seminare.=$class_query;
	$class_query =" AND".$class_query;
	}

//Expressions for grouping
switch ($sem_browse_data["group_by"]) {
	case "einrichtung":
		$order_by_exp="institut ASC";
		$select_add=", Institute.Name AS Institut";
	break;
	case "bereich":
		$order_by_exp="bereich ASC";
		$select_add=", bereiche.name AS bereich";		
	break;
	case "typ":
		$order_by_exp="status ASC";
	break;
	case "dozent":
		$order_by_exp="Nachname ASC";
		$select_add=", " . $_fullname_sql['full_rev'] ." AS fullname, auth_user_md5.Username";
	break;
	case "semester":
		$order_by_exp="start_time DESC";
	break;
	default:
		$order_by_exp="start_time DESC";
	break;
}

//calculate colspans
$rows = ($sem_browse_data["extend"] == "yes") ? 8 : 5;
$rightspan = ($sem_browse_data["extend"] == "yes") ? 3 : 2;
$leftspan = $rows-$rightspan; 
if (($sem_browse_data["group_by"] == "einrichtung" || $sem_browse_data["group_by"] == "semester" || $sem_browse_data["group_by"] == "dozent")
	|| ($sem_browse_data["group_by"] == "einrichtung" && $sem_browse_data["estend"] == "yes")) {
	$leftspan--;
	$rows--;
}

ob_start(); //Outputbuffering start

echo "<table border=0 align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";

//Anzeige der Hierarchiezeile
if ((!isset($sem_browse_data["sset"])) && (!$hide_bereich))
	{
	echo "<tr><td class=\"steel1\" ";
	if (($sem_browse_data["level"]<>"s") && ($sem_browse_data["level"]<>"sbb"))
		echo "colspan=2";
	else
		echo "colspan=$rows";
	echo ">";
	
	if ($sem_browse_data["extern"]<>"yes")
	{
	switch ($sem_browse_data["level"])
		{
		case "f":
			echo "<font size=-1>> <a href=\"$UNI_URL\" target=\"_new\">$UNI_NAME</a> > Fakult&auml;ten</font>";
			break;
		case "sbi":
			echo "<font size=-1>> <a href=\"$UNI_URL\" target=\"_new\">$UNI_NAME</a> > <a href=\"$PHP_SELF?level=f\">Fakult&auml;ten</a> > Einrichtungen/Studienf&auml;cher</font>";
			break;
		case "sb":
			$db->query("SELECT Name FROM Institute WHERE Institut_id='".$sem_browse_data["id"]."' AND fakultaets_id='".$sem_browse_data["id"]."'");
			$db->next_record();
			echo "<font size=-1>> <a href=\"$UNI_URL\" target=\"_new\">$UNI_NAME</a> > <a href=\"$PHP_SELF?level=f\">Fakult&auml;ten</a> > <a href=\"$PHP_SELF?level=sbi&id=".$sem_browse_data["id"]."\">Einrichtungen/Studienf&auml;cher</a> > Studienf&auml;cher</font>";
			break;
		case "i":
			$db->query("SELECT Name FROM Institute WHERE Institut_id='".$sem_browse_data["id"]."' AND fakultaets_id='".$sem_browse_data["id"]."'");
			$db->next_record();
			echo "<font size=-1>> <a href=\"$UNI_URL\" target=\"_new\">$UNI_NAME</a> > <a href=\"$PHP_SELF?level=f\">Fakult&auml;ten</a> > <a href=\"$PHP_SELF?level=sbi&id=".$sem_browse_data["id"]."\">Einrichtungen/Studienf&auml;cher</a> > Einrichtungen </font>";
			break;
		case "s";
			$db->query("SELECT Name FROM Institute WHERE Institut_id='".$sem_browse_data["oid"]."' AND fakultaets_id='".$sem_browse_data["oid"]."'");
			$db2->query("SELECT Name, Institut_id FROM Institute WHERE Institut_id ='".$sem_browse_data["id"]."'");
			$db->next_record();
			$db2->next_record();
			echo "<font size=-1>> <a href=\"$UNI_URL\" target=\"_new\">$UNI_NAME</a> > <a href=\"$PHP_SELF?level=f\">Fakult&auml;ten</a> > <a href=\"$PHP_SELF?level=sbi&id=".$sem_browse_data["oid"]."\">Einrichtungen/Studienf&auml;cher</a> > <a href=\"$PHP_SELF?level=i&id=".$sem_browse_data["oid"]."\">Einrichtungen</a> > ", htmlReady($db2->f("Name")), "</font>";
			break;
		case "b";
			$db2->query("SELECT name FROM faecher WHERE fach_id ='".$sem_browse_data["id"]."'");
			$db2->next_record();
			echo "<font size=-1>> <a href=\"$UNI_URL\" target=\"_new\">$UNI_NAME</a> > <a href=\"$PHP_SELF?level=f\">Fakult&auml;ten</a> > <a href=\"$PHP_SELF?level=sbi&id=".$sem_browse_data["oid"]."\">Einrichtungen/Studienf&auml;cher</a> > <a href=\"$PHP_SELF?level=sb&id=".$sem_browse_data["oid"]."\">Studienf&auml;cher</a> > ", htmlReady($db2->f("name")), "</font>";
			break;
		case "sbb";
			$db2->query("SELECT name FROM faecher WHERE fach_id ='".$sem_browse_data["oid"]."'");
			$db3->query("SELECT name FROM bereiche WHERE bereich_id='".$sem_browse_data["id"]."'");			
			$db2->next_record();
			$db3->next_record();
			echo "<font size=-1>> <a href=\"$UNI_URL\" target=\"_new\">$UNI_NAME</a> > <a href=\"$PHP_SELF?level=f\">Fakult&auml;ten</a> > <a href=\"$PHP_SELF?level=sbi&id=".$sem_browse_data["oid2"]."\">Einrichtungen/Studienf&auml;cher</a> > <a href=\"$PHP_SELF?level=sb&id=".$sem_browse_data["oid2"]."\">Studienf&auml;cher</a> > <a href=\"$PHP_SELF?level=b&id=".$sem_browse_data["oid"]."&oid=".$sem_browse_data["oid2"]."\">", htmlReady($db2->f("name")), "</a> > ", htmlReady($db3->f("name")), "</font>";
			break;
		}
	}
	echo"</td></tr>";
}

//Query for view by Bereich or Einrichtung
if (($sem_browse_data["level"]=="s") && ((!isset($sem_browse_data["sset"])) || $sem_browse_data["extern"]))
	$sql_where_query_seminare="seminar_inst.institut_id = '".$sem_browse_data["id"]."' $class_query";

if (($sem_browse_data["level"]=="sbb") && ((!isset($sem_browse_data["sset"])) || $sem_browse_data["extern"]))
	$sql_where_query_seminare="seminar_bereich.bereich_id = '".$sem_browse_data["id"]."' $class_query";
	
//the basic search SQL
if (!$dont_search)
	$query = ("SELECT DISTINCT seminare.Seminar_id, seminare.status, seminare.Name, seminare.start_time, seminare.metadata_dates, seminare.duration_time, seminare.Schreibzugriff, seminare.Lesezugriff $select_add  FROM seminare 
				LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent') 
				LEFT JOIN seminar_bereich ON (seminare.Seminar_id = seminar_bereich.Seminar_id) 
				LEFT JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.Seminar_id) 
				LEFT JOIN auth_user_md5 ON (seminar_user.user_id = auth_user_md5.user_id) 
				LEFT JOIN user_info USING (user_id) 
				LEFT JOIN bereiche ON (bereiche.bereich_id=seminar_bereich.bereich_id) 
				LEFT JOIN Institute ON (seminar_inst.Institut_id = Institute.Institut_id) 
				WHERE $sql_where_query_seminare ORDER BY $order_by_exp, seminare.Name ");

//Anzeige des Suchergebnis (=Seminarebene)
if (($sem_browse_data["level"]=="s") || ($sem_browse_data["level"]=="sbb")) {
	$db->query($query);

	if ($db->num_rows()) {
		if (($sem_browse_data["sset"]) || ($sem_browse_data["extern"]) ||  $sem_browse_data["level"] !="f") {
			printf ("<tr><td nowrap class=\"steel1\" colspan=\"%s\">", $leftspan);
			//Change/view the group method
			print ("<font size=-1><b>Gruppierung:</b>&nbsp;<select name=\"group_by\">");
			printf ("<option %s value=\"semester\">Semester</option>", (($sem_browse_data["group_by"]=="semester") || (!$sem_browse_data["group_by"])) ? "selected" : "");
			printf ("<option %s value=\"bereich\">Bereich</option>", ($sem_browse_data["group_by"]=="bereich") ? "selected" : "");
			printf ("<option %s value=\"dozent\">DozentIn</option>", ($sem_browse_data["group_by"]=="dozent") ? "selected" : "");
			printf ("<option %s value=\"typ\">Typ</option>", ($sem_browse_data["group_by"]=="typ") ? "selected" : "");
			printf ("<option %s value=\"einrichtung\">Einrichtung</option>", ($sem_browse_data["group_by"]=="einrichtung") ? "selected" : "");
			print ("</select>&nbsp; <input type=\"IMAGE\" border=0 src=\"./pictures/buttons/uebernehmen-button.gif\" /></font>");
			echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <font size=-1>&nbsp;<b>", $db->num_rows(), " </b>Veranstaltungen gefunden. </font> ";
			print ("</td>");
			//Show how many items were found
			printf ("<td class=\"steel1\" nowrap align=\"right\" colspan=%s>", $rightspan);
			echo"<a href=\"", $PHP_SELF; if ($sem_browse_data["extend"]<>"yes") { 
				echo "?extend=yes\"><img src=\"pictures/buttons/erweiterteansicht-button.gif\" border=0>"; 
			} else {
				echo "?extend=no\"><img src=\"pictures/buttons/normaleansicht-button.gif\" border=0>"; 
			} 
			echo "</a></font></td></tr>";
			}
	
		ob_end_flush();
		ob_start();

		//init the cols
		if ($sem_browse_data["extend"]=="yes") {
		?>
		<colgroup>
				<col width="30%">
				<col width="15%">
				<col width="10%">
				<col width="15%">
				<?
				if ($sem_browse_data["group_by"] != "semester") print "<col width=\"10%\">";
				?>
				<col width="10%">
				<col width="10%">
			</colgroup>
		<?
		} else {
		?>
		<colgroup>
				<col width="40%">
				<col width="15%">
				<?
				if ($sem_browse_data["group_by"] != "einrichtung") print "<col width=\"20%\">";
				if ($sem_browse_data["group_by"] != "dozent") print "<col width=\"20%\">";				
				if ($sem_browse_data["group_by"] != "semester") print "<col width=\"5%\">";
				?>
			</colgroup>
		<?
		}
		?> 
			<tr align="center">
				<td class="steel" align="left"><font size="-1">
					<img src="pictures/blank.gif" width="1" height="20" valign="top">
					<b>Name</b></font>
				</td>
				<td class="steel" valign="bottom">
					<font size="-1"><b>Zeit</b></font>
				</td>
				<?
				if ($sem_browse_data["group_by"] != "einrichtung") {
				?>
				<td class="steel" valign="bottom">
					<font size="-1"><b>Einrichtungen</b></font>
				</td>
				<?
				}
				if ($sem_browse_data["group_by"] != "dozent") {
				?>
				<td class="steel" valign="bottom">
					<font size="-1"><b>DozentInnen</b></font>
				</td>
				<?
				}
				if ($sem_browse_data["group_by"] != "semester") {
				?>
				<td class="steel" valign="bottom">
					<font size="-1"><b>Semester</b></font>
				</td>
				<?
				}
		if ($sem_browse_data["extend"]=="yes") {
			if ($sem_browse_data["group_by"] != "typ") {
				?>		
				<td class="steel" valign="bottom">
					<font size=-1><b>Typ</b></font>
				</td>
				<?
				}
				?>
				<td class="steel" valign="bottom">
					<font size=-1><b>Lesen</b> / <b><font size=-1>Schreiben</b></font>
				</td>
				<td class="steel" valign="bottom">
					<font size=-1><b>Mein Status</b></font>
				</td>
				<?
		}
		echo "</tr>";
	} else {
		if ($dont_search)
			echo "<tr><td class=\"blank\" colspan=2><br /><font size=-1><b>Es wurden keine Veranstaltungen gefunden, da Sie keinen Suchbegriff angegeben haben.</b></font>";
		else
			echo "<tr><td class=\"blank\" colspan=2><font size=-1><b>Es wurden keine Veranstaltungen gefunden.</b></font>";		
	}
	
	$group=1;
	if (!$dont_search)
	while ($db->next_record()) {
		$cssSw->switchClass();		
		
		if ($group==8)
			$group=1;
		
		//Create the group headers
		switch ($sem_browse_data["group_by"]) {
			case "semester":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_sem"] == "alle")) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					$tmp_sem=get_semester($db->f("Seminar_id"), TRUE);
					if ($last_sem != $tmp_sem) {
						$group_header_name=$tmp_sem;
						$group_header_class=$group;
						$group++;
					} else
						$group_header_name=FALSE;
					$last_sem=$tmp_sem;
				}
			break;
			case "bereich":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_bereich"] == "alle")) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					$tmp_bereich=$db->f("bereich");
					if ($last_bereich != $db->f("bereich")) {
						$group_header_name=$db->f("bereich");
						$group_header_class=$group;
						$group++;
					} else
						$group_header_name=FALSE;
					$last_bereich=$db->f("bereich");
				}
			break;
			case "typ":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_status"] == "alle")) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					$tmp_status=$db->f("status");
					if ($last_status != $db->f("status")) {
						$group_header_name=$SEM_TYPE[$db->f("status")]["name"]." (". $SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"].")";
						$group_header_class=$group;
						$group++;
					} else
						$group_header_name=FALSE;
					$last_status=$db->f("status");
				}
			break;
			case "dozent":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_dozent"])) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					$tmp_dozent=$db->f("fullname");
					if ($last_dozent != $db->f("fullname")) {
						$group_header_name=$db->f("fullname");
						$group_header_class=$group;
						$group++;
					} else
						$group_header_name=FALSE;
					$last_dozent=$db->f("fullname");
				}
			break;
			case "einrichtung":
				if (($sem_browse_data["cmd"] != "qs") || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					$tmp_institut=$db->f("Institut");
					if ($last_institut != $db->f("Institut")) {
						$group_header_name=$db->f("Institut");
						$group_header_class=$group;
						$group++;
					} else
						$group_header_name=FALSE;
				$last_institut=$db->f("Institut");
				}
			break;
		}
		
		//Put group_by headers
		if ($group_header_name)
			printf ("<tr> <td class=\"steelgroup%s\" colspan=%s><font size=-1><b>&nbsp;%s</b></font></td></tr>", ($sem_browse_switch_headers) ? $group_header_class : "1", $rows, $group_header_name);
			
		//create name-field	
		echo"<tr ".$cssSw->getHover()."><font size=-1>";
		echo"<td class=\"".$cssSw->getClass()."\"><font size=-1><a href=\"", $target_url, "?", $target_id, "=", $db->f("Seminar_id"), "&send_from_search=true&send_from_search_page=$PHP_SELF\">", htmlReady($db->f("Name")), "</a></font></td>";
		
		//----------------------
		
		//create Turnus field
		$temp_turnus_string=view_turnus($db->f("Seminar_id"), TRUE);
		
		//Shorten, if string too long (add link for details.php)
		if (strlen($temp_turnus_string) >70) {
			$temp_turnus_string=substr($temp_turnus_string, 0, strpos(substr($temp_turnus_string, 70, strlen($temp_turnus_string)), ",") +71);
			$temp_turnus_string.="...&nbsp;<a href=\"".$target_url."?".$target_id."=".$db->f("Seminar_id")."&send_from_search=true&send_from_search_page=$PHP_SELF\">(mehr) </a>";
		}
		echo"<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>".$temp_turnus_string."</font></td>";

		//----------------------
				
		//create the Einrichtungen Colummn
		if ($sem_browse_data["group_by"] != "einrichtung") {			
			$sem_id=$db->f("Seminar_id");
			$einrichtungen ="";
			$i=0;
			$db2->query("SELECT a.institut_id ,Name FROM seminar_inst a LEFT JOIN Institute USING (institut_id) WHERE Seminar_id = '$sem_id' AND Name NOT LIKE '%- - -%' ORDER BY Name");
			while (($db2->next_record()) && ($i<=3)) {
				if ($i) $einrichtungen .= ", ";
				$einrichtungen .= "<a href=\"institut_main.php?auswahl=".$db2->f("institut_id")."\">".htmlReady($db2->f("Name"))."</a>";
				//more than 2 Einrichtungen are two much, link to the details.php for more info
				if ($i==2)
					$einrichtungen .= ",...&nbsp;<a href=\"".$target_url."?".$target_id."=".$db->f("Seminar_id")."&send_from_search=true&send_from_search_page=$PHP_SELF\">(mehr) </a>";
				$i++;
			}
			if ($einrichtungen == "") $einrichtungen = "- - -";
		echo"<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>",$einrichtungen,"&nbsp;</font></td>";
		}
	
		//----------------------
			
		//create the Dozenten Colummn
		if ($sem_browse_data["group_by"] != "dozent") {		
			$sem_id=$db->f("Seminar_id");
			$dozname ="";
			$i=0;
			$db2->query("SELECT username, ". $_fullname_sql['full'] ." AS fullname FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE Seminar_id = '$sem_id' AND status = 'dozent' ORDER BY Nachname");
			while (($db2->next_record()) && ($i<=3)) {
				if ($i) $dozname .= ", ";
				$dozname .= "<a href=\"about.php?username=".$db2->f("username")."\">".htmlReady($db2->f("fullname"))."</a>";
				//more than 3 Dozenten are two much, link to the details.php for more info
				if ($i==3)
					$dozname .= ",...&nbsp;<a href=\"".$target_url."?".$target_id."=".$db->f("Seminar_id")."&send_from_search=true&send_from_search_page=$PHP_SELF\">(mehr) </a>";
				$i++;
			}
			if ($dozname == "") $dozname = "- - -";
		echo"<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>",$dozname,"&nbsp;</font></td>";
		}
		
		//----------------------
		
		//create the Semester colummn
		if ($sem_browse_data["group_by"] != "semester")
			echo "<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>".get_semester($db->f("Seminar_id"), TRUE)."</font></td>";
		
		//----------------------
				
		//create extended fields
		if ($sem_browse_data["extend"]=="yes") {
			//Typ
			if ($sem_browse_data["group_by"] != "typ")
				echo "<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>", $SEM_TYPE[$db->f("status")]["name"]." <br>(Kategorie ", $SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"],")</font></td>";
	
			//Meinen Status ermitteln
			$user_id = $auth->auth["uid"];
			$db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$sem_id' AND user_id = '$user_id'");
			if ($db2->next_record() ){
				$mein_status = $db2->f("status");
			} else {
				unset ($mein_status);
			}
			
			//Ampel-Schaltung
			if ($mein_status) { // wenn ich im Seminar schon drin bin, darf ich auf jeden Fall lesen
				echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">&nbsp; ";
			} else {
				switch($db->f("Lesezugriff")){
					case 0 : 
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">&nbsp; ";
					break;
					case 1 :
						if ($perm->have_perm("autor"))
							echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">&nbsp; ";
						else
							echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp; ";
					break;
					case 2 :
						if ($perm->have_perm("autor"))
							echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp; ";
						else
							echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp; ";
					break;
					case 3:
						if ($perm->have_perm("autor"))
							echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp; ";
						else
							echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp; ";
				}
			}

			if ($mein_status == "dozent" || $mein_status == "tutor" || $mein_status == "autor") { // in den F�llen darf ich auf jeden Fall schreiben
				echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\"></td>";
			} else {
				switch($db->f("Schreibzugriff")){
					case 0 : 
						echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\"></td>";
					break;
					case 1 :
							if ($perm->have_perm("autor"))
							echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\"></td>";
						else
							echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\"></td>";
					break;
					case 2 :
						if ($perm->have_perm("autor"))
							echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\"></td>";
						else
							echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\"></td>";
					break;
					case 3:
						if ($perm->have_perm("autor"))
							echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\"></td>";
						else
							echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\"></td>";
				}
			}
			echo "<td class=\"".$cssSw->getClass()."\" align=\"center\"><font size=-1>";
			//Meinen Status ausgeben
			if ($mein_status) {
				echo $mein_status;
			} else {
				echo "&nbsp;";
			}
			echo "</font></td>";
			}
			echo"</tr>";
		}

	
	if ((!$sem_browse_data["sset"]) && ($sem_browse_data["extern"] <> "yes")) {
		echo "<tr><td class=\"steel1\" ";
		echo "colspan=$rows"; 
		echo " align=\"center\"><font size=-1>&nbsp;";
		if ($sem_browse_data["level"]=="s")
			echo"<a href=\"$PHP_SELF?level=i&id=".$sem_browse_data["oid"]."\">eine Ebene zur&uuml;ck</a>";
		else
			echo"<a href=\"$PHP_SELF?level=b&id=".$sem_browse_data["oid"]."&oid=".$sem_browse_data["oid2"]."\">eine Ebene zur&uuml;ck</a>";
	}

	echo"</font></td></tr><tr><td class=\"blank\">&nbsp;<br></td></tr>";
	ob_end_flush();
}

//Uebersicht ueber die Institute einer Fakultaet
if (($sem_browse_data["level"]=="i") && (!$hide_bereich))
	{
	$db->query("SELECT Institute.Name, Institute.Institut_id, count(seminare.Seminar_id) AS number_seminare FROM Institute LEFT JOIN seminar_inst USING(Institut_id) LEFT JOIN seminare USING (Seminar_id) WHERE Institute.Name NOT LIKE '%- - -%' AND Institute.fakultaets_id = '".$sem_browse_data["id"]."' $class_query GROUP BY Institute.Institut_id ORDER BY number_seminare DESC");
	
	$i=0;
	//echo "<tr><td class=\"blank\">&nbsp;<br></tr></td>";

	while ($db->next_record())
		if ($db->f("number_seminare") !="0")
			if ($i % 2 == 0)
				{
				echo "<tr><td class=\"blank\" width=\"50%\" valign=\"_top\">";
				echo "<a href=\"$PHP_SELF?level=s&id=", $db->f("Institut_id"), "&oid=".$sem_browse_data["id"]."\"><b>", htmlReady($db->f("Name")), "</b></a>&nbsp;<font size=-1>(", $db->f("number_seminare"), ")</font><br>";
				$i++;
				echo "</td>\n";
				}
			else
				{
				echo "<td class=\"blank\" width=\"50%\" valign=\"_top\">";
				echo "<a href=\"$PHP_SELF?level=s&id=", $db->f("Institut_id"), "&oid=".$sem_browse_data["id"]."\"><b>", htmlReady($db->f("Name")), "</b></a>&nbsp;<font size=-1>(", $db->f("number_seminare"), ")</font><br>";
				$i++;
				echo "</td></tr>\n";
				}
			
	echo "<tr><td class=\"steel1\" colspan=2 align=\"center\"><font size=-1>";
	if (!$sem_browse_data["extern"]=="yes") echo "<a href=\"$PHP_SELF?level=sbi&id=".$sem_browse_data["id"]."\">eine Ebene zur&uuml;ck</a>";
	echo "<br>Es werden alle Einrichtungen angezeigt, die Veranstaltungen in Stud.IP anbieten.</font></td></tr><tr><td class=\"blank\">&nbsp;<br></td></tr>";
	}

//Uerbersicht ueber die Studienbereiche eines Studienfaches
if (($sem_browse_data["level"]=="b")  && (!$hide_bereich))
	{
	
	$db->query("SELECT bereiche.name, bereiche.bereich_id, count(seminare.Seminar_id) AS number_seminare FROM bereich_fach 
				LEFT JOIN bereiche USING (bereich_id) LEFT JOIN seminar_bereich USING (bereich_id)LEFT JOIN seminare USING (Seminar_id)
				WHERE bereich_fach.fach_id = '".$sem_browse_data["id"]."' $class_query GROUP BY bereiche.bereich_id HAVING number_seminare > 0 ORDER BY number_seminare DESC");
	
	$i=0;
	//echo "<tr><td class=\"blank\">&nbsp;<br></tr></td>";

	while ($db->next_record())
		if ($db->f("number_seminare") !="0")
			if ($i % 2 == 0)
				{
				echo "<tr><td class=\"blank\" width=\"50%\" valign=\"_top\">";
				echo "<a href=\"$PHP_SELF?level=sbb&id=", $db->f("bereich_id"), "&oid=".$sem_browse_data["id"]."&oid2=".$sem_browse_data["oid"]."\"><b>", htmlReady($db->f("name")), "</b></a>&nbsp;<font size=-1>(", $db->f("number_seminare"), ")</font><br>";
				$i++;
				echo "</td>\n";
				}
			else
				{
				echo "<td class=\"blank\" width=\"50%\" valign=\"_top\">";
				echo "<a href=\"$PHP_SELF?level=sbb&id=", $db->f("bereich_id"), "&oid=".$sem_browse_data["id"]."&oid2=".$sem_browse_data["oid"]."\"><b>", htmlReady($db->f("name")), "</b></a>&nbsp;<font size=-1>(", $db->f("number_seminare"), ")</font><br>";
				$i++;
				echo "</td></tr>\n";
				}
			
	echo "<tr><td class=\"steel1\" colspan=2 align=\"center\"><font size=-1>";
	if (!$sem_browse_data["extern"]=="yes") echo "<a href=\"$PHP_SELF?level=sb&id=".$sem_browse_data["oid"]."\">eine Ebene zur&uuml;ck</a>";
	echo "<br>Es werden nur Studienbereiche angezeigt, zu denen Veranstaltungen in Stud.IP existieren.</font></td></tr><tr><td class=\"blank\">&nbsp;<br></td></tr>";
	}

//Uebersucht ueber die Studienfaecher einer Fakult&auml;t
if (($sem_browse_data["level"]=="sb") && (!$hide_bereich))
	{
	
	//$db->query("SELECT faecher.name, faecher.fach_id, count(seminare.Seminar_id) AS number_seminare FROM Institute  LEFT JOIN fach_inst USING (Institut_id) LEFT JOIN faecher USING (fach_id) LEFT JOIN bereich_fach USING (fach_id) LEFT JOIN seminar_bereich USING (bereich_id) 
	//			LEFT JOIN seminare USING (Seminar_id) WHERE Institute.Fakultaets_id = '".$sem_browse_data["id"]."' $class_query GROUP BY faecher.fach_id HAVING number_seminare > 0 ORDER BY number_seminare DESC");
	
	$db->query("SELECT fach_id FROM Institute  LEFT JOIN fach_inst USING (Institut_id) WHERE Institute.Fakultaets_id = '".$sem_browse_data["id"]."' AND NOT ISNULL(fach_id) GROUP BY fach_id");
	while ($db->next_record()){
		$_fach_list[] = $db->f('fach_id');
	}
	if (count($_fach_list)){
		$db->query("SELECT faecher.name, faecher.fach_id, count(seminare.Seminar_id) AS number_seminare FROM faecher 
					LEFT JOIN bereich_fach USING (fach_id) LEFT JOIN seminar_bereich USING (bereich_id) 
					LEFT JOIN seminare USING (Seminar_id) WHERE faecher.fach_id IN('".join("','",$_fach_list)."')
					GROUP BY faecher.fach_id HAVING number_seminare > 0 ORDER BY number_seminare DESC");
		
		$i=0;
		//echo "<tr><td class=\"blank\">&nbsp;<br></tr></td>";
		
		while ($db->next_record()){
			if ($db->f("number_seminare") !="0"){
				if ($i % 2 == 0){
					echo "<tr><td class=\"blank\" width=\"50%\" valign=\"_top\">";
					echo "<a href=\"$PHP_SELF?level=b&id=", $db->f("fach_id"), "&oid=".$sem_browse_data["id"]."\"><b>", htmlReady($db->f("name")), "</b></a>&nbsp;<font size=-1>(", $db->f("number_seminare"), ")</font><br>";
					$i++;
					echo "</td>\n";
				} else {
					echo "<td class=\"blank\" width=\"50%\" valign=\"_top\">";
					echo "<a href=\"$PHP_SELF?level=b&id=", $db->f("fach_id"), "&oid=".$sem_browse_data["id"]."\"><b>", htmlReady($db->f("name")), "	</b></a>&nbsp;<font size=-1>(", $db->f("number_seminare"), ")</font><br>";
					$i++;
					echo "</td></tr>\n";
				}
			}
		}
	}
	echo "<tr><td class=\"steel1\" colspan=2 align=\"center\"><font size=-1>";
	if (!$sem_browse_data["extern"]=="yes") echo "<a href=\"$PHP_SELF?level=sbi&id=".$sem_browse_data["id"]."\">eine Ebene zur&uuml;ck</a>";
	echo "<br>Es werden nur Studienf&auml;cher angezeigt, zu denen Veranstaltungen in Stud.IP existieren.</font></td></tr><tr><td class=\"blank\">&nbsp;<br></td></tr>";
	}

//Uebersicht Studienbereichebereiche/Institute
if (($sem_browse_data["level"]=="sbi")  && (!$hide_bereich))
	{
	echo "<tr><td class=\"blank\" width=\"50%\" valign=\"_top\">";
	echo "<a href=\"$PHP_SELF?level=i&id=", $sem_browse_data["id"], "\"><b>Einrichtungen</b></a><br></td>";
	echo "<td class=\"blank\" width=\"50%\" valign=\"_top\">";
	if ((($SEM_CLASS[$sem_browse_data["s_class"]]["bereiche"]) || ($SEM_CLASS[$show_class]["bereiche"])) || ((!$sem_browse_data["s_class"]) && (!$show_class)))
		echo "<a href=\"$PHP_SELF?level=sb&id=",$sem_browse_data["id"], "\"><b>Studienf&auml;cher</b></a><br>";
	else
		echo "<font size=-1>(keine Studienf&auml;cher vorhanden.)</font><br>";
	echo "</td></tr>\n";
	echo "<tr><td class=\"steel1\" colspan=2 align=\"center\"><font size=-1><a href=\"$PHP_SELF?level=f\">eine Ebene zur&uuml;ck</a></font></td></tr><tr><td class=\"blank\">&nbsp;</td></tr>";
	}



//Uebersicht ueber alle Fakultaeten mit einigen Instituten
if ((($sem_browse_data["level"]=="f") || (!isset($sem_browse_data["level"])))  && (!$hide_bereich))
	{
	$db->query("SELECT a.Name, a.Institut_id AS Fakultaets_id, count(seminare.Seminar_id) AS number_seminare FROM Institute a 
				LEFT JOIN Institute b ON (b.fakultaets_id=a.Institut_id) 
				LEFT JOIN seminar_inst c USING (Institut_id) 
				LEFT JOIN seminare  USING (Seminar_id) 
				WHERE a.Name NOT LIKE '%- - -%' AND a.Institut_id=b.fakultaets_id $class_query GROUP BY a.Institut_id ORDER BY number_seminare DESC");
	$db2->query("SELECT Institute.Name, Institute.Institut_id, Institute.fakultaets_id, count(seminare.Seminar_id) AS number_seminare 
				FROM Institute LEFT JOIN seminar_inst USING(Institut_id) 
				LEFT JOIN seminare USING (Seminar_id) 
				WHERE Institute.Name NOT LIKE '%- - -%' AND Institute.Institut_id!=fakultaets_id  $class_query GROUP BY Institute.Institut_id ORDER BY number_seminare DESC");
	
	$i=0;
	
	while ($db->next_record())
		if ($db->f("number_seminare") !="0")
			if ($i % 2 == 0)
				{
				echo "<tr><td class=\"blank\" width=\"50%\" valign=\"_top\">";
				echo "<a href=\"$PHP_SELF?level=sbi&id=", $db->f("Fakultaets_id"), "\"><b>", htmlReady($db->f("Name")), "</b></a><br><font size=-1>";
				$i++;
				$db2->seek(0);
				$db2->next_record();
				$k=0;
				do
					{
					if (($db2->f("number_seminare") !="0") && ($db->f("Fakultaets_id") == $db2->f("fakultaets_id")) && ($k>=3))
						{
						echo ", ...";
						$k++;
						}
					if ($k>=4) break;
					if (($db2->f("number_seminare") !="0") && ($db->f("Fakultaets_id") == $db2->f("fakultaets_id")))
						{
						if ($k >=1) echo ", ";
						echo "<a href=\"$PHP_SELF?level=s&id=", $db2->f("Institut_id"), "&oid=",$db->f("Fakultaets_id"),"\">", htmlReady($db2->f("Name")), "</a>";
						$k++;
						}
					}
				while ($db2->next_record());			
				echo "</font></td>\n";
				}
			else
				{
				echo "<td class=\"blank\" width=\"50%\" valign=\"_top\">";
				echo "<a href=\"$PHP_SELF?level=sbi&id=", $db->f("Fakultaets_id"), "\"><b>", htmlReady($db->f("Name")),"</b></a><br><font size=-1>";
				$i++;
				$db2->seek(0);
				$db2->next_record();
				$k=0;
				$k=0;
				do
					{
					if (($db2->f("number_seminare") !="0") && ($db->f("Fakultaets_id") == $db2->f("fakultaets_id")) && ($k>=3))
						{
						echo ", ...";
						$k++;
						}
					if ($k>=4) break;
					if (($db2->f("number_seminare") !="0") && ($db->f("Fakultaets_id") == $db2->f("fakultaets_id")))
						{
						if ($k >=1) echo ", ";
						echo "<a href=\"$PHP_SELF?level=s&id=", $db2->f("Institut_id"), "&oid=",$db->f("Fakultaets_id"),"\">", htmlReady($db2->f("Name")), "</a>";
						$k++;
						}
					}
				while ($db2->next_record());	
				echo "</font></td></tr>\n";
				}
	echo "<tr><td class=\"steel1\" colspan=2 align=\"center\"><font size=-1>Es werden alle Einrichtungen und Fakult&auml;ten angezeigt, die Veranstaltungen in Stud.IP anbieten.</font></td></tr><tr><td class=\"blank\">&nbsp;<br></td></tr>";
	}
	
			
echo "</table></form>";
?>

