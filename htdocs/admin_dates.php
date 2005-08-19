<?php
/*
admin_dates.php - Terminverwaltung von Stud.IP
Copyright (C) 2000 Andr� Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("tutor");
	
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); 
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/forum.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/calendar_functions.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/Modules.class.php");

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;	
$db4=new DB_Seminar;
$Modules=new Modules;
$semester=new SemesterData; 

//Defaults, die fuer DAUS (<admin) gesetzt werden
$default_description= _("Bitte geben Sie hier nur weiterf�hrende Angaben (genauere Terminbeschreibung, Referatsthemen usw.) ein.");
$default_titel= _("Kurztitel, bitte ausf�llen!");
if (!$perm->have_perm ("admin")) {
	$temp_default[1]= _("tt");
	$temp_default[2]= _("mm");
	$temp_default[3]= _("jjjj");
	$temp_default[4]= _("hh");
	$temp_default[5]= _("mm");
	$temp_default[6]= _("hh");
	$temp_default[7]= _("mm");
}

//Load all TERMIN_TYPs that are "Sitzungstermine" and build query-clause
$i=0;
$typ_clause = "(";
foreach ($TERMIN_TYP as $key=>$val) {
	if ($val["sitzung"]) {
		if ($i)
			$typ_clause .= ", ";
		$typ_clause .= "'".$key."' ";
		$i++;
	}
}
$typ_clause .= ")";

$sess->register("term_data");
$sess->register("admin_dates_data");

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
	$resList = new ResourcesUserRoomsList($user->id, TRUE, FALSE, TRUE);
}


// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");

checkObjectModule("schedule");

if ($SessSemName[1])
	$admin_dates_data["range_id"]=$SessSemName[1]; 
elseif ($range_id)
	$admin_dates_data["range_id"]=$range_id; 

if (!$admin_dates_data["range_id"]) {
	echo "</tr></td></table>";
	die;
}

//load module-config
$modules = $Modules->getLocalModules($admin_dates_data["range_id"]);

//Einpflegen neu angekommender Daten/Schalter
if ($assi) 
	$admin_dates_data["assi"]=$assi;
if ($list) //note: we re-use the $list switch here, because it indicates that we are defenitely not coming from the assi.
	$admin_dates_data["assi"]=FALSE;
	
if ($show_id) 
	$admin_dates_data["show_id"]=$show_id;
if ($show_all) 
	$admin_dates_data["show_all"]=TRUE;
if ($show_nall) 
	$admin_dates_data["show_all"]=FALSE;
	
if (($edit_x) && (!$admin_dates_data["show_all"]))
	$admin_dates_data["show_id"]='';


//single kill
if ($kill_single_date) {
	$kill_date[] = $kill_single_date;
	$kill_x = TRUE;
}

//save the edit-arrays (if sent from from) in a persistent varaiable
if ((is_array ($termin_id)) || (is_array ($kill_date))){
	$admin_dates_data["form_data"] = '';
	if (is_array ($termin_id)) foreach ($termin_id as $key=>$val) {
		$admin_dates_data["form_data"][$val]["termin_id"] = $termin_id[$key];
		$admin_dates_data["form_data"][$val]["topic_id"] = $topic_id[$key];
		$admin_dates_data["form_data"][$val]["tag"] = $tag[$key];
		$admin_dates_data["form_data"][$val]["monat"] = $monat[$key];
		$admin_dates_data["form_data"][$val]["jahr"] = $jahr[$key];
		$admin_dates_data["form_data"][$val]["stunde"] = $stunde[$key];
		$admin_dates_data["form_data"][$val]["minute"] = $minute[$key];
		$admin_dates_data["form_data"][$val]["end_stunde"] = $end_stunde[$key];
		$admin_dates_data["form_data"][$val]["end_minute"] = $end_minute[$key];
		$admin_dates_data["form_data"][$val]["t_titel"] = $titel[$key];
		$admin_dates_data["form_data"][$val]["description"] = $description[$key];
		$admin_dates_data["form_data"][$val]["insert_topic"] = $insert_topic[$key];
		$admin_dates_data["form_data"][$val]["insert_folder"] = $insert_folder[$key];
		$admin_dates_data["form_data"][$val]["art"] = $art[$key];
		$admin_dates_data["form_data"][$val]["resource_id"] = $resource_id[$key];
		$admin_dates_data["form_data"][$val]["raum"] = $raum[$key];
	}
	if (is_array ($kill_date)) foreach ($kill_date as $key=>$val) {
		$admin_dates_data["form_data"][$val]["kill_date"] = TRUE;
	}
}

if ($reset_edit)
	$admin_dates_data["form_data"] = '';

if ($insert_new) {
	$hash_secret = "blubbelsupp";
	$t_id=md5(uniqid($hash_secret));   //termin_id erzeugen
	//Insert Modus AN
	$admin_dates_data["insert_id"]=$t_id;
} else {
	$admin_dates_data["insert_id"]=FALSE;
}

//maximale spaltenzahl berechnen
if ($auth->auth["jscript"])
	$max_col = round($auth->auth["xres"] / 10 );
else
	$max_col =  64 ; //default f�r 640x480

if ($admin_dates_data["range_id"] && !$perm->have_perm("root")) {
	//Sicherheitscheck
	$range_perm=get_perm($admin_dates_data["range_id"]);
	if ($range_perm!="admin" && $range_perm!="dozent" && $range_perm!="tutor")
		die;
}

//Bevor wir einen Termin loeschen uebernehmen wir Aenderungen, dann ist gleichzeitiges Aendern und Loeschen moeglich
if ($kill_x)
	$edit_x=TRUE;

//Assistent zum automatischen generieren eines Ablaufplans
if ($make_dates_x) {
	//kill saved regularly dates
 	if ($RESOURCES_ENABLE) {
		$insertAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
		$insertAssign->deleteAssignedRooms();
	}	
	
	$resultAssi = dateAssi ($admin_dates_data["range_id"], "insert", $pfad, $folder, $full, FALSE, FALSE);
	$result="msg�" . sprintf(_("Der Ablaufplan wurde erstellt. Es wurden %s Termine erstellt."), $resultAssi["changed"]) . "�";
	$resources_result = $resultAssi["resources_result"];
}

if ($new) {
	$do=TRUE;
	
	if ($resource_id == "FALSE")
		$resource_id = FALSE;
	
	if (!checkdate($monat,$tag,$jahr)) {
		$do=FALSE;
		$result="error�" . _("Bitte geben Sie ein g&uuml;ltiges Datum ein!") . "�";
	}

	if (($stunde == "") || ($stunde == $temp_default[4]) || ($end_stunde == "") || ($end_stunde == $temp_default[6])) {
		$do=FALSE;	
		$result.="error�" . _("Bitte geben Sie eine g&uuml;ltige Start- und Endzeit an!") . "�";
	}
		
	$start_time = mktime($stunde,$minute,0,$monat,$tag,$jahr);
	$end_time = mktime($end_stunde,$end_minute,0,$monat,$tag,$jahr);
	
	if ($do && $start_time > $end_time) {
		$do=FALSE;	
		$result.="error�" . _("Der Endzeitpunkt muss nach dem Startzeitpunkt liegen!") . "�";
	}
	
	//check, if a single date should be created when it is forbidden (no single dates corresponding to metadates are allowed when using resources, only a whole schedule creating with date-assi is fine...!)
	if ($GLOBALS["RESOURCES_ENABLE"]) {
		if ((isMetadateCorrespondingDate($termin_id, $start_time, $end_time, $admin_dates_data["range_id"])) && (!$term_data["art"]) && (!isSchedule($admin_dates_data["range_id"]))) {
			$do = FALSE;
				if ($TERMIN_TYP[$art]["sitzung"])
					$result .= "info�" . sprintf(_("Sie wollen einen Sitzungstermin zu den regelm&auml;&szlig;igen Veranstaltungszeiten anlegen oder &auml;ndern. Bitte verwenden daf&uuml;r zun&auml;chst den Ablaufplanassistenten, um die entsprechenden Termine f&uuml;r den gesamten Veranstaltungszeitraum anzulegen.")) . "�";
				else
					$result .= "info�" . sprintf(_("Sie wollen einen Sondertermin (%s) zu den regelm&auml;&szlig;igen Veranstaltungszeiten anlegen. Bitte verwenden zun&auml;chst den Ablaufplanassistenten und &auml;ndern dann die Terminart f&uuml;r den gew&uuml;nschten Termin in %s "), $TERMIN_TYP[$art]["name"], $TERMIN_TYP[$art]["name"]) . "�";

		} elseif ($GLOBALS["RESOURCES_ALLOW_ROOM_REQUESTS"]) {
			$resObjPrm =& ResourceObjectPerms::Factory($resource_id);
			if (!$resObjPrm->havePerm("autor"))
				$result .= "info�" . sprintf(_("Sie haben einen neuen Termin angelegt. Um f&uuml;r diesen Termin einen Raum zu buchen, m&uuml;ssen Sie f&uuml;r diesen Termin eine %sRaumanfrage%s an den zust&auml;ndigen Raumadministrator stellen."), "<a href=\"admin_room_requests.php?seminar_id=".$admin_dates_data["range_id"]."&termin_id=".$termin_id."\">", "</a>") . "�";
		}
	}		
	
	//Check auf Konsistenz mt Metadaten, Semestercheck
	if (($do) && ($art == 1) && (is_array($term_data ["turnus_data"]))) {
		$all_semester = $semester->getAllSemesterData();		
		foreach ($all_semester as $a) {
			if (($term_data["start_time"] >= $a["beginn"]) && ($term_data["start_time"] <= $a["ende"])) {
				$sem_beginn=$a["beginn"];
				$sem_ende=$a["ende"];
			}
			if (($term_data["duration_time"] > 0) && ((($term_data["start_time"] + $term_data["duration_time"]) >= $a["beginn"]) && (($term_data["start_time"] + $term_data["duration_time"]) < $a["ende"])))
				$sem_ende=$a["ende"];
		}
			
		if (($start_time < $sem_beginn) || ($start_time > $sem_ende))
			$result.="info�" . _("Der eingegebene Termine liegt au&szlig;erhalb des Semesters, in dem die Veranstaltung stattfindet. Es wird empfohlen, den Termin anzupassen.") . "�";
		
		//Und dann noch auf regelmaessige Termine checken, wenn dieser Typ gewaehlt ist
		if ((!$term_data["art"]) && (!isMetadateCorrespondingDate($t_id, $start_time, $end_time, $admin_dates_data["range_id"]))) {
			$add_result .= "info�" . _("Sie haben einen oder mehrere Termine eingegeben, der nicht zu den allgemeinen Veranstaltungszeiten stattfindet. Es wird empfohlen, Sitzungstermine von regelm&auml;&szlig;igen Veranstaltungen nur zu den allgemeinen Zeiten stattfinden zu lassen.") . "�";
		}
	}

	
	
	if ($do) {
		$hash_secret = "blubbelsupp";
		$t_id=$termin_id;
		$f_id=md5(uniqid($hash_secret));   //folder_id erzeugen		
		$aktuell=time();

		$tmp = $auth->auth["uname"];
		$author=get_fullname();

		if ($titel==$default_titel)
			$tmp_titel=_("Kein Titel");
		else
			$tmp_titel=$titel;
		
		if ($description==$default_description)
			$description='';
			
		//if we have a resource_id, we take the room name from resource_id (deprecated at the moment)
		/*if (($resource_id) && ($RESOURCES_ENABLE))
			$raum=getResourceObjectName($resource_id);*/


		if ($topic)  //Forumseintrag erzeugen
			$topic_id=CreateTopic($TERMIN_TYP[$art]["name"].": ".$tmp_titel." " . _("am") . " ".date("d.m.Y ", $start_time), $author, _("Hier kann zu diesem Termin diskutiert werden"), 0, 0, $admin_dates_data["range_id"]);
		if ($folder) { //Dateiordner erzeugen
			$titel_f=$TERMIN_TYP[$art]["name"].": $tmp_titel";
			$titel_f.=" " . _("am") . " ".date("d.m.Y ", $start_time);
			$description_f=_("Ablage f�r Ordner und Dokumente zu diesem Termin");		
			$db3->query("INSERT INTO folder SET folder_id='$f_id', range_id='$t_id', description='$description_f', user_id='$user->id', name='$titel_f', mkdate='$aktuell', chdate='$aktuell'");
		} else
			$f_id='';
	
		$db->query("REPLACE INTO termine SET termin_id='$t_id', range_id='".$admin_dates_data["range_id"]."', autor_id='$user->id', content='$tmp_titel', date='$start_time', mkdate='$aktuell', chdate='$aktuell', date_typ='$art', topic_id='$topic_id', end_time='$end_time', raum='$raum', description='$description'");
		if ($db->affected_rows()) {
			//insert a entry for the linked resource, if resource management activ
			if (($RESOURCES_ENABLE) && ($resource_id)){
				$insertAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
				$resources_result = $insertAssign->insertDateAssign($t_id, $resource_id);
				$insertAssign->updateAssign(false); 
			}
		
			$result.="msg�" . _("Ihr Termin wurde eingef&uuml;gt!") . "�";
			$admin_dates_data["termin_id"]=FALSE;
		}
	} else
		$result.="error�" . _("Der Termin wurde <u>nicht</u> eingef&uuml;gt!"). "�";
}  // end if ($new)

//reload the temporaray data, if user want to resent (and create a request)
if (($save_changes_with_request) || ($delete_confirm)) {
	foreach ($admin_dates_data["form_data"] as $key=>$val) {
		$termin_id [] = $key;
		$tag[] = $val["tag"];
		$monat[] = $val["monat"];
		$jahr[] = $val["jahr"];
		$stunde[] = $val["stunde"];
		$minute[] = $val["minute"];
		$end_stunde[] = $val["end_stunde"];
		$end_minute[] = $val["end_minute"];
		$titel[] = $val["t_titel"];
		$description[] = $val["description"];
		$insert_topic[] = $val["insert_topic"];
		$insert_folder[] = $val["insert_folder"];
		$art[] = $val["art"];
		$raum[] = $val["raum"];
		$kill_date[] = $key;
	}
}

if ((($edit_x) || ($save_changes_with_request)) && (!$admin_dates_data["termin_id"])) {
	if (is_array($termin_id)) {
		for ($i=0; $i < sizeof($termin_id); $i++) {
		 	$t_id=$termin_id[$i];
			$f_id=md5(uniqid($hash_secret));
			
			$tmp_result=edit_dates($stunde[$i],$minute[$i],$monat[$i], $tag[$i], $jahr[$i], $end_stunde[$i], $end_minute[$i], $t_id, $art[$i], $titel[$i],$description[$i], $topic_id[$i],$raum[$i], $resource_id[$i], $admin_dates_data["range_id"], $save_changes_with_request);
		 	$result.=$tmp_result["msg"];

		 	$resources_result = array_merge ($resources_result, $tmp_result["resources_result"]);

			$aktuell=time();

			$tmp = $auth->auth["uname"];
			$author=get_fullname();
			
			if ($tag[$i]<10)
				$tag[$i]="0".$tag[$i];
			if ($monat[$i]<10)
				$monat[$i]="0".$monat[$i];
			$tmp_datum=$tag[$i].".".$monat[$i].".".$jahr[$i];

			
 			if ($titel[$i]==$default_titel)
				$tmp_titel="Kein Titel";
			else
				$tmp_titel=$titel[$i];

		 	//nachtraegliches Anlegen von Ordner vornehmen
		 	if ($insert_topic[$i]) {
				$tmp_topic_id=CreateTopic($TERMIN_TYP[$art[$i]]["name"].": ".$tmp_titel." " . _("am") . " $tmp_datum", $author, _("Hier kann zu diesem Termin diskutiert werden"), 0, 0, $admin_dates_data["range_id"]);
				$db3->query ("UPDATE termine SET topic_id = '$tmp_topic_id' WHERE termin_id = '$t_id'");
			} else
				$tmp_topic_id='';
			if ($insert_folder[$i]) { 
				$titel_f=$TERMIN_TYP[$art[$i]]["name"].": $tmp_titel";
				$titel_f.=" " . _("am") . " $tmp_datum";
				$description_f= _("Ablage f�r Ordner und Dokumente zu diesem Termin");		
				$db3->query("INSERT INTO folder SET folder_id='$f_id', range_id='$t_id', description='$description_f', user_id='$user->id', name='$titel_f', mkdate='$aktuell', chdate='$aktuell'");
			}
		 	
		 	if (!$add_result) //Hinweisnachrichten nur einmal anzeigen
		 		$add_result=$tmp_result["add_msg"];
		 	
		 	//delete the temporary data (actual data will be loaded from db while showing the date)
		 	if ($tmp_result["changed"]) {
		 		$admin_dates_data["form_data"][$t_id] = '';
		 	}
		}
		$result=$add_result.$result;
	}

	//after every change, we have to do this check (and we create the msgs...)
	if ($RESOURCES_ENABLE) {
		$updateAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
		$resources_result = array_merge ($resources_result, $updateAssign->updateAssign(false)); //do not check locks, we are only editing real dates
	}
}  // end if ($edit_x)

if ($kill_date)
	$kill_termin = $kill_date;
if ((($kill_x) || ($delete_confirm)) && ($admin_dates_data["range_id"])) {
	if (is_array($kill_date)) {
		for ($i=0; $i < count($kill_date); $i++) {		
			$do_delete = TRUE;
			if (($RESOURCES_ENABLE) && (!$delete_confirm)){
				if ($assigned_room = getDateAssigenedRoom($kill_date[$i])) {
					$resObjPrm =& ResourceObjectPerms::Factory($assigned_room);
					if (!$resObjPrm->havePerm("autor")) {
						if (!$just_informed) {
							$result .= "info�" . sprintf(_("Sie wollen einen oder mehrere Termine l&ouml;schen, denen bereits ein Raum zugewiesen ist. Beim L&ouml;schen verlieren Sie diese Raumbuchungen. Wollen Sie diese Termine wirklich l&ouml;schen?"));
							$result .= "<br /><a href=\"$PHP_SELF?delete_confirm=1\">".makeButton("ja2")."</a>&nbsp;<a href=\"$PHP_SELF?reset_edit=1\">".makeButton("nein")."</a>�";
						}
						$do_delete = FALSE;
						$just_informed = TRUE;
					} 				
				}
			}
			if ($do_delete) {
		 		delete_date($kill_date[$i], TRUE, TRUE, $admin_dates_data["range_id"]);
		 		$del_count++;
		 	}
		}
	}

	//after every change, we have to do this check (and we create the msgs...)
	if ($RESOURCES_ENABLE) {
		$updateAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
		$resources_result = array_merge ($resources_result, $updateAssign->updateAssign(false));
		if ($updateAssign->turnus_cleared && $RESOURCES_ALLOW_ROOM_REQUESTS){
			$request_id = getSeminarRoomRequest($admin_dates_data["range_id"]);
			if ($request_id){		//reactivate room request if there is one
				$room_request = new RoomRequest($request_id);
				if ($room_request->getClosed()){
					$room_request->setClosed(0);
					$room_request->store();
					$result .= sprintf ("info�"._("Die Raumanfrage zu den regelm&auml;&szlig;igen Zeiten der Veranstaltung wurde reaktiviert. Um die Anfrage einzusehen oder zu bearbeiten, gehen Sie auf %sRaumanfragen%s.")."�", "<a href=\"admin_room_requests.php?seminar_id=\"".$admin_dates_data["range_id"]."\">", "</a>");
				}
			} else {
				//there is no room request, maybe the user should know that...
				$result .= sprintf ("info�"._("Um R&auml;ume f&uuml;r Ihre Veranstaltung zu bekommen, m&uuml;ssen Sie eine %sRaumanfrage%s erstellen.")."�", "<a href=\"admin_room_requests.php?seminar_id=\"".$admin_dates_data["range_id"]."\">", "</a>");
 			}
		}
	}
	
	if ($del_count)
		if ($del_count == 1)
			$result.="msg�" . _("1 Termin wurde gel&ouml;scht!"). "�";
		else
			$result.="msg�" . sprintf(_("%s Termine wurden gel&ouml;scht!"), $del_count). "�";
	$beschreibung='';


}  // end if ($kill_x)

//result from the resource management
if (($RESOURCES_ENABLE) && ($resources_result)) {
	$result.=getFormattedResult($resources_result, "booth");
	}
	
//Bereich wurde ausgewaehlt (aus linksadmin) oder wir kommen aus dem Seminar Assistenten
$db->query("SELECT metadata_dates, Name, start_time, duration_time, status, Seminar_id FROM seminare WHERE Seminar_id = '".$admin_dates_data["range_id"]."'");
$db->next_record();
if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME) 	
	$tmp_typ = _("Veranstaltung"); 
else
	$tmp_typ = $SEM_TYPE[$db->f("status")]["name"];
	
$term_data=unserialize($db->f("metadata_dates"));
$term_data["start_time"]=$db->f("start_time");
$term_data["duration_time"]=$db->f("duration_time");
						

$db2->query("SELECT count(*) AS anzahl FROM termine WHERE range_id='".$admin_dates_data["range_id"]."' AND date_typ IN $typ_clause");
$db2->next_record();

//some messages
if (($db2->f("anzahl") == 0) && (!$admin_dates_data["insert_id"])){
	$result.= sprintf ("info�"._("Es sind keine Termine vorhanden.")."<br>"._("Einen neuen Termin %s")."�", "<a href=\"$PHP_SELF?insert_new=TRUE#anchor\">".makeButton("anlegen")."</a>");
}

if ($admin_dates_data["assi"]) {
	$result.= "info�"._("Sie haben die M&ouml;glichkeit, diesen Schritt des Veranstaltungs-Assistenten jederzeit nachzuholen.")."&nbsp;<a href=\"admin_seminare1.php\">".makeButton("abbrechen")."</a>�";
}

if (!sizeof($term_data["turnus_data"])) {
	$result.= "info�"._("Sie haben bislang noch keine <b>allgemeinen</b> (regelm&auml;&szlig;ige) Veranstaltungszeiten eingegeben. Sie k&ouml;nnen an dieser Stelle den Ablaufplan-Assisten benutzen, wenn Sie f&uuml;r die Veranstaltung einen regelm&auml;&szlig;igen Turnus festlegen.")."�";
}
						


//Ab hier Ausgaben....
?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2>&nbsp;<b>
		<?
		if ($admin_dates_data["assi"]) {
			printf(_("Schritt 8: Ablaufplan und Termine der Veranstaltung: %s"),htmlReady(substr($db->f("Name"), 0, 40)));
		if (strlen($db->f("Name")) > 40)
			echo "... ";
		} else
			echo  getHeaderLine($db->f("Seminar_id"))." - " . _("Ablaufplan und Termine");
		?>
		</b></td>
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp; 
 		</td>
 	</tr>
 	<tr>
		<td class="blank" valign="top">
			<table width="100%" border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td valign="top">
					<table border="0" cellpadding="2" cellspacing="0" width="99%" align="center">
					<?
					if ($result) {
						parse_msg($result, "�", "blank", 1, FALSE);
						print "<a href=\"anchor\"></a>";
					}
					?>
					</table>				
				
					<?

					print("<form method=\"POST\" action=\"$PHP_SELF\">");
					//Fenster zum Starten des Terminassistenten einblenden
					if ((!$term_data["art"]) && (!isSchedule($admin_dates_data["range_id"], TRUE, TRUE))) {
						if (sizeof($term_data["turnus_data"])) { //Ablaufplanassistent nur wenn allgemeine Zeiten vorhanden moeglich
						?>
						<table border="0" cellpadding="6" cellspacing="0" width="99%" align="center">
						<tr>
							<td class="rahmen_steel">
								<font size="-1"><b><?=_("Ablaufplan-Assistent")?></b><br /><br /></font>
								<?
								if (($modules["forum"]) || ($modules["documents"]) || ($db->f("duration_time") != 0)) {
									?>
									<font size="-1"><?=_("generieren Sie Sitzungstermine zu den Veranstaltungszeiten mit folgenden Einstellungen:")?><br /></font>
									<?
									//only, if the forum is active
									if ($modules["forum"]) { 
										?>
										&nbsp; &nbsp; <font size="-1"><input type="checkbox" name="pfad"> <?=_("Zu jedem Termin einen Themenordner im Forum der Veranstaltung anlegen.")?></font><br>
										<? 
									} 
									//only, if the documents-folder is active
									if ($modules["documents"]) { 
										?>
										&nbsp; &nbsp; <font size="-1"><input type="checkbox" name="folder"> <?=_("Zu jedem Termin einen Dateiordner f&uuml;r Dokumente anlegen.")?> </font>
										<?
									} 
									if ($db->f("duration_time") != 0) {
										?>
										<br />&nbsp; &nbsp; <font size="-1"><input type="checkbox" name="full"> <?=_("Ablaufplan f&uuml;r alle Semester anlegen (wenn nicht gesetzt: nur f&uuml;r das erste Semester)")?> </font>
										<?
									}
								}
								echo "<br /><br /><font size=\"-1\">";
								printf(_("Assistent %s"), "<input type=\"IMAGE\" align=\"absmiddle\" name=\"make_dates\" " . makeButton("starten", "src") . " border=\"0\" value=\"" . _("Ablaufplan-Assistenten ausf&uuml;hren") . "\">");
								?>
								</font>
								&nbsp; <img  src="./pictures/info.gif"
									<?=tooltip(_("Der Ablaufplan-Assistent erstellt automatisch die zu den Veranstaltungszeiten passenden Termine f�r ein oder mehrere Semester. Dabei werden - soweit wie m�glich - Feiertage und Ferienzeiten �bersprungen. Anschlie�end k�nnen Sie jedem Termin einen Titel und eine Beschreibung geben."), TRUE, TRUE)?>>
								<br /><br />
								</td>
							</tr>
						</table>
						<?
						}
					} 
					?>
					</form>
					<?
					//Vorhandene Termine holen und anzeigen und nach Bedarf bearbeiten
					$db->query("SELECT * FROM termine WHERE range_id='".$admin_dates_data["range_id"]."' ORDER BY date");
					if ($db->num_rows() || $admin_dates_data["insert_id"]) {
						?>
						<table border="0" cellpadding="2" cellspacing="0" width="99%" align="center">
						<tr align="left" height="22">
							<td width="5%" class="steelgraulight" align="left">
								<a href="admin_dates.php?insert_new=TRUE#anchor"><img src="pictures/add_sheet.gif" <?=tooltip(_("Einen neuen Termin anlegen"))?> border=0></a>
							</td>
							<td width="80%" class="steelgraulight" align="center">
							<?
							if (!$show_all) {
								?>
								<a href="<? echo $PHP_SELF, "?range_id=".$admin_dates_data["range_id"]."&show_all=TRUE"; ?>"><img src="pictures/open_all.gif" <?=tooltip(_("Alle Termine aufklappen"))?> border=0></a>
								<?
							} else {
								?>
								<a href="<? echo $PHP_SELF, "?range_id=".$admin_dates_data["range_id"]."&show_nall=TRUE"; ?>"><img src="pictures/close_all.gif" <?=tooltip(_("Alle Termine zuklappen"))?> border=0></a>
								<?
							}
								?>
							</td>
							<form method="POST" name="Formular" action="<? echo $PHP_SELF; ?>#anchor">
					
							<?
							if (!$admin_dates_data["insert_id"]) {
							?>
							<td class="steelgraulight" align="right" nowrap>
							<?
								if (!$show_all) {
								?>
								<input type="IMAGE" name="mark_all" border=0 <?=makeButton("alleauswaehlen", "src")?> value="ausw�hlen">&nbsp;
								<input type="IMAGE" name="kill" border=0 <?=makeButton("loeschen", "src")?> value="l�schen">&nbsp; 
								<?
								}
							}
							if ($show_all) {
							?>
							<input type="IMAGE" name="edit" border=0 <?=makeButton("allesuebernehmen", "src")?> value="ver�ndern">&nbsp; &nbsp; 
							<?
							}
							?>
							<input type="HIDDEN" name="show_id" value="<? echo $db->f("termin_id");?>">
							<input type="HIDDEN" name="show_id" value="<? echo $show_id ?>">
							<input type="HIDDEN" name="show_all" value="<? echo $show_all ?>">
							</td>
						</tr>
						</table>
						<?	
			
						//Wenn insert gesetzt, neuen Anlegen...
						if ($admin_dates_data["insert_id"]) {
							$kill_date = '';
									
							//Titel erstellen
							$titel = '';
							$titel .= "&nbsp;<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"tag\" maxlength=2 size=2 value=\"".$temp_default[1]."\"><font style=\"font-size:10 pt;vertical-align:bottom\">.</font>";
							$titel .= "<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"monat\" maxlength=2 size=2 value=\"".$temp_default[2]."\"><font style=\"font-size:10 pt;vertical-align:bottom\">.</font>";
							$titel .= "<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"jahr\" maxlength=4 size=4  value=\"".$temp_default[3]."\"><font style=\"font-size:10 pt;vertical-align:bottom\">&nbsp;" . _("von") . "&nbsp;</font>";
							$titel .= "<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"stunde\" maxlength=2 size=2 value=\"".$temp_default[4]."\"><font style=\"font-size:10 pt;vertical-align:bottom\"> :</font>";
							$titel .= "<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"minute\" maxlength=2 size=2 value=\"".$temp_default[5]."\"><font style=\"font-size:10 pt;vertical-align:bottom\">&nbsp;" . _("bis") . "&nbsp;</font>";
							$titel .= "<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"end_stunde\" maxlength=2 size=2 value=\"".$temp_default[6]."\"><font style=\"font-size:10 pt;vertical-align:bottom\"> :</font>";
							$titel .= "<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"end_minute\" maxlength=2 size=2 value=\"".$temp_default[7]."\"><font style=\"font-size:10 pt;vertical-align:bottom\"> " . _("Uhr") . ".</font>";
							$titel .= "<input type=\"HIDDEN\" name=\"termin_id\" value=\"".$admin_dates_data["insert_id"]."\">";
							$titel .= Termin_Eingabe_javascript(1,0,0);
							$icon = "&nbsp;<img src=\"./pictures/termin-icon.gif\" border=0>";
							$link = $PHP_SELF."?cancel=TRUE";
						
							echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
							printhead(0, 0, $link, "open", TRUE, $icon, $titel, $zusatz);
							echo "</tr></table>";
							
							//Contentbereich
							$content='';
						
							$content.="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"left\" width=\"100%\"><tr>\n";
							$content.="<td class=\"steel1\" width=\"70%\" valign=\"top\" colspan=\"2\">\n";
						    	$content.="<input type=\"HIDDEN\" name=\"new\" value=\"TRUE\">";
							$content.="<font size=-1><b>" . _("Titel:") . "</b></font><br /><input type=\"TEXT\" name=\"titel\" style=\"width:98%\" maxlength=255 size=".round($max_col*0.45)." value=\"";
							if (!$perm->have_perm ("admin"))
								$content.=$default_titel;
							$content.="\"><br />";
							$content.="<font size=-1><b>" . _("Beschreibung:") . "</b><br></font><textarea style=\"width:98%\" cols=\"". round($max_col*0.45)."\" rows=3 name=\"description\"  wrap=\"virtual\">";
							if (!$perm->have_perm ("admin"))
								$content.=$default_description;
							$content.="</textarea>";
							$content.="</td>\n";
							
							//room
							$content.="<td class=\"steel1\" width=\"30%\" valign=\"top\" rowspan=\"2\">\n";
							$content.="<font size=-1><b>" . _("Raum:") . "</b></font>";
							//load the default from metatdata (regular times) (deprecated at the moment)
							/*if ((is_array($term_data["turnus_data"])) && (sizeof($term_data["turnus_data"]) == 1)) {
								$new_date_resource_id = $term_data["turnus_data"][0]["resource_id"];
								$new_date_room = $term_data["turnus_data"][0]["room"];
							}*/
							if ($RESOURCES_ENABLE) {
								$resList -> reset();
								if ($resList->numberOfRooms()) {
									$content.= "<br /><font size=-1><select name=\"resource_id\"></font>";
									$content.= ("<option value=\"FALSE\">[" . _("ausw&auml;hlen oder wie Eingabe") . "]</option>");
									while ($res = $resList->next())
										$content.= sprintf("<option %s value=\"%s\">%s</option>", ($new_date_resource_id && $new_date_resource_id == $res["resource_id"]) ? "selected" : "", $res["resource_id"], htmlReady(my_substr($res["name"], 0, 25)));
									$content.= "</select></font>";
								}
							}
							$content.="<br /><input type=\"TEXT\" name=\"raum\" style=\"width:98%\" maxlength=255 size=30 value=\"".htmlReady($new_date_room)."\"><br>\n";
							$content.="</td></tr>";
							
							
							//select type
							$content.="<tr><td class=\"steel1\" width=\"20%\" valign=\"top\" nowrap>\n";							
							$content.="<font size=-1><b>" . _("Art:") . "</b></font><br><select name=\"art\">\n";
							for ($i=1; $i<=sizeof($TERMIN_TYP); $i++)
								if ($db->f("date_typ") == $i)
									$content.= "<option value=$i selected>".$TERMIN_TYP[$i]["name"]."</option>";
								else
									$content.= "<option value=$i>".$TERMIN_TYP[$i]["name"]."</option>";
							$content.="</select>&nbsp;&nbsp;&nbsp;<br><br></td>\n";							
							
							
							//linked forum and folder
							$content.="<td class=\"steel1\" width=\"50%\" valign=\"top\" align=\"left\">\n";							
							if (($modules["forum"]) || ($modules["documents"]))
								$content.="<font size=-1><b>" . _("Verkn&uuml;pfungen mit diesem Termin:") . "</b></font><br />";
						
							//only, if the forum is active
							if ($modules["forum"])
								$content.="<input type=\"CHECKBOX\" name=\"topic\"/><font size=-1>" . _("Thema im Forum anlegen") . "</font><br />\n";
							//only, if the docuements-folder is active
							if ($modules["documents"])
								$content.="<input type=\"CHECKBOX\" name=\"folder\"/><font size=-1>" . _("Dateiordner anlegen") . "</font>\n";
						
							$content.="<br>&nbsp;</td>\n";
						
							$content.="</tr></td></table></td></tr>\n<tr><td class=\"steel1\" align=\"center\" colspan=2>";
							$content.="<input type=\"IMAGE\" name=\"send\" border=0 " . makeButton("uebernehmen", "src") . " align=\"absmiddle\" value=\"speichern\">&nbsp;";
							$content.="<a href=\"$PHP_SELF?cancel=TRUE\">" . makeButton("abbrechen", "img") . "</a><br />";
						 	$content.= "<a name=\"anchor\"></a>";
						
						
							echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
							printcontent(0,1, $content, '');
							echo "</tr></table>";
						
							?>
							</form>
							<?
						}
						
						//..und alte Bearbeiten
						$c=0;
						$resource_id = FALSE;
						while ($db->next_record()) {
							//if persistent data exists, we use this...
							if (is_array ($admin_dates_data["form_data"][$db->f("termin_id")])) {
								$topic_id =  $db->f("topic_id");
								$tag = $admin_dates_data["form_data"][$db->f("termin_id")]["tag"];
								$monat = $admin_dates_data["form_data"][$db->f("termin_id")]["monat"];
								$jahr = $admin_dates_data["form_data"][$db->f("termin_id")]["jahr"];
								$stunde = $admin_dates_data["form_data"][$db->f("termin_id")]["stunde"];
								$minute = $admin_dates_data["form_data"][$db->f("termin_id")]["minute"];
								$end_stunde = $admin_dates_data["form_data"][$db->f("termin_id")]["end_stunde"];
								$end_minute = $admin_dates_data["form_data"][$db->f("termin_id")]["end_minute"];
								$t_titel = stripslashes($admin_dates_data["form_data"][$db->f("termin_id")]["t_titel"]);
								$description = stripslashes($admin_dates_data["form_data"][$db->f("termin_id")]["description"]);
								$insert_topic = $admin_dates_data["form_data"][$db->f("termin_id")]["insert_topic"];
								$insert_folder = $admin_dates_data["form_data"][$db->f("termin_id")]["insert_folder"];
								$art = $admin_dates_data["form_data"][$db->f("termin_id")]["art"];
								if ($RESOURCES_ENABLE)
									$resource_id = getDateAssigenedRoom($db->f("termin_id"));
								$raum = $admin_dates_data["form_data"][$db->f("termin_id")]["raum"];
								$kill_selected = $admin_dates_data["form_data"][$db->f("termin_id")]["kill_date"];
							//otherwise, we use the saved state
							} else {
								$topic_id = $db->f("topic_id");
								$tag = date ("j", $db->f("date"));
								$monat =date ("n", $db->f("date"));
								$jahr = date ("Y", $db->f("date"));
								$stunde = date ("G", $db->f("date"));
								$minute = date ("i", $db->f("date"));
								$end_stunde = date ("G", $db->f("end_time"));
								$end_minute = date ("i", $db->f("end_time"));
								$t_titel = $db->f("content");
								$description = $db->f("description");
								$art = $db->f("date_typ");
								if ($RESOURCES_ENABLE)
									$resource_id = getDateAssigenedRoom($db->f("termin_id"));
								$raum = $db->f("raum");
								$kill_selected = FALSE;
							}
						
							//Ermitteln, ob Ordner an diesem Termin haengt
							$db2->query("SELECT folder_id FROM folder WHERE range_id='".$db->f("termin_id")."'");
							if ($db2->num_rows())
								$folder=TRUE;
							else
								$folder=FALSE;
						
							if (($show_id  == $db->f("termin_id")) || ($show_all)) {
								print "<a name=\"#anchor\"></a>";
								$edit=TRUE;
							} else
								$edit=FALSE;
						
							//Zusatz erstellen
							if ((!$admin_dates_data["insert_id"]) && ($show_id  != $db->f("termin_id")) && (!$show_all))
								$zusatz="<input type=\"CHECKBOX\" ".((($mark_all_x) || ($kill_selected)) ? "checked" : "")." name=\"kill_date[]\" value=\"". $db->f("termin_id")."\"><a href=\"$PHP_SELF?kill_single_date=".$db->f("termin_id")."\"><img src=\"pictures/trash.gif\" border=\"0\" style=\"style=\"vertical-align:bottom\"\"/></a>";
							else
								$zusatz='';
						
							//Link erstellen
							if (($show_id  == $db->f("termin_id")) || ($show_all))
								$link=$PHP_SELF."?range_id=".$admin_dates_data["range_id"]."&show_id=";
							else
								$link=$PHP_SELF."?range_id=".$admin_dates_data["range_id"]."&show_id=".$db->f("termin_id")."#anchor";
						
							//Titel erstellen
							$titel='';
							if ($edit) {
								$titel.="&nbsp;<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"tag[$c]\" maxlength=2 size=2 value=\"".sprintf('%02d',$tag)."\"><font style=\"font-size:10 pt;vertical-align:bottom\">.</font>";
								$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"monat[$c]\" maxlength=2 size=2 value=\"".sprintf('%02d',$monat)."\"><font style=\"font-size:10 pt;vertical-align:bottom\">.</font>";
								$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"jahr[$c]\" maxlength=4 size=4  value=\"".sprintf('%04d',$jahr)."\"><font style=\"font-size:10 pt;vertical-align:bottom\">&nbsp;" . _("von") . "&nbsp;</font>";
								$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"stunde[$c]\" maxlength=2 size=2 value=\"".sprintf('%02d',$stunde)."\"><font style=\"font-size:10 pt;vertical-align:bottom\"> :</font>";
								$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"minute[$c]\" maxlength=2 size=2 value=\"".sprintf('%02d',$minute)."\"><font style=\"font-size:10 pt;vertical-align:bottom\">&nbsp;" . _("bis") . "&nbsp;</font>";
								$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"end_stunde[$c]\" maxlength=2 size=2 value=\"".sprintf('%02d',$end_stunde)."\"><font style=\"font-size:10 pt;vertical-align:bottom\"> :</font>";
								$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;vertical-align:bottom\" name=\"end_minute[$c]\" maxlength=2 size=2 value=\"".sprintf('%02d',$end_minute)."\"><font style=\"font-size:10 pt;vertical-align:bottom\"> " . _("Uhr") . ".</font>";
							    	$titel.="<input type=\"HIDDEN\" name=\"termin_id[$c]\" value=\"".$db->f("termin_id")."\">";
								$titel.="<input type=\"HIDDEN\" name=\"topic_id[$c]\" value=\"".$topic_id."\">";
							} else {
								$titel .= substr(strftime("%a",$db->f("date")),0,2);
								$titel.= date (". d.m.Y, H:i", $db->f("date"));
								if ($db->f("date") <$db->f("end_time"))
									$titel.= " - ".date ("H:i", $db->f("end_time"));
								if ($db->f("content")) {
									$tmp_titel=htmlReady(mila($db->f("content"))); //Beschneiden des Titels
									$titel.=", ".$tmp_titel;
								}
								$titel = "<a class=\"tree\" href=\"".$link."\">".$titel."</a>";
							}
							if (($show_id  == $db->f("termin_id")) && (!$result))
							 	$titel.= "<a name=\"anchor\"></a>";
						
							//Icon erstellen
							$icon="&nbsp;<img src=\"./pictures/termin-icon.gif\" border=0>";
						
							if ($db->f("chdate") > object_get_visit($SessSemName[1], "schedule"))
								$neuer_termin=TRUE;
							else
								$neuer_termin=FALSE;
						
						
							echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
						
							if (($show_id  == $db->f("termin_id")) || ($show_all)) {
								if ($edit) {
									$at = mktime(0,0,0,$monat,$tag,$jahr);
									$titel .= Termin_Eingabe_javascript(2, $c, $at, $stunde, $minute, $end_stunde, $end_minute);
								}
								printhead(0, 0, $link, "open", $neuer_termin, $icon, $titel, $zusatz, $db->f("mkdate"));
							} else {
								printhead(0, 0, $link, "close", $neuer_termin, $icon, $titel, $zusatz, $db->f("mkdate"));
							}
							echo "</tr></table>";
							//Contentbereich
							if (($show_id  == $db->f("termin_id")) || ($show_all)) {
								$content='';
								if ($edit) {
									$content .= '<table border="0" cellpadding="0" cellspacing="0" align="left"width="100%"><tr>' . "\n";
									$content .= '<td class="steel1" width="70%" valign="top" colspan="2">' . "\n";
						
									if (!$show_all) {
										$content.="<input type=\"HIDDEN\" name=\"show_id\" value=\"". $db->f("termin_id")."\">";
									}
						
									$content.="<font size=-1><b>" . _("Titel:") . "</b><br /><input type=\"TEXT\" name=\"titel[$c]\" style=\"width:98%\" maxlength=255 size=".round($max_col*0.45)." value=\"".htmlReady($t_titel)."\"><br />";
									$content.="<b>"._("Beschreibung:") . "</b><br><textarea style=\"width:98%\" cols=\"". round($max_col*0.45)."\" rows=3 name=\"description[$c]\"  wrap=\"virtual\">".htmlReady($description)."</textarea></font>\n";
									$content.="</td>";
									
									//room
									$content.="<td class=\"steel1\" width=\"30%\" valign=\"top\" rowspan=\"2\">\n";
									$content.="<font size=-1><b>" . _("Raum:")."</b><br />";
						
									//show list of rooms, the user has perms on
									if ($RESOURCES_ENABLE) {
										$resList -> reset();
										if ($resList->numberOfRooms()) {
											$content.= "<select name=\"resource_id[$c]\">";
											$content.= sprintf("<option %s value=\"NULL\">[" . (($resource_id) ? _("gebuchter Raum oder ausw&auml;hlen") : _("ausw&auml;hlen oder wie Eingabe")) . "]</option>", (!$resource_id) ? "selected" : "");
											if ($resource_id)
												$content.= "<option value=\"FALSE\">["._("kein gebuchter Raum") ."]</option>";
											while ($res = $resList->next())
												$content.= sprintf("<option value=\"%s\">%s</option>", $res["resource_id"], htmlReady(my_substr($res["name"], 0, 25)));
											$content.= "</select><br>";
										} else
											$content.="<input type=\"HIDDEN\" name=\"resource_id[$c]\" value=\"NULL\">";
									}
						
									//the free field for room-information
									$content.="<br /><input type=\"TEXT\" style=\"width:98%\" name=\"raum[$c]\" maxlength=255 size=30 value=\"". htmlReady($raum)."\">\n";
						
									//show the booked room
									if ($RESOURCES_ENABLE) {
										$content.= "<br /><br /><b>"._("gebuchter Raum:")."</b><br /> ";
										if ($resource_id) {
											$resObj =& ResourceObject::Factory($resource_id);
											$content.= $resObj->getFormattedLink(TRUE, TRUE, TRUE)."\n";
										} else
											$content.= _("kein gebuchter Raum")."\n";
									}
						
									//link to admin_room_requests.php
									if ($RESOURCES_ENABLE && $RESOURCES_ALLOW_ROOM_REQUESTS) {
										if (getDateRoomRequest($db->f("termin_id")))
											$content.="<br /><br /><img src=\"pictures/link_intern.gif\" border=\"0\" />&nbsp;<a href=\"admin_room_requests.php?seminar_id=".$admin_dates_data["range_id"]."&termin_id=".$db->f("termin_id")."\">"._("Raumanfrage <b>bearbeiten</b>")."</a><br>\n";
										else
											$content.="<br /><br /><img src=\"pictures/link_intern.gif\" border=\"0\" />&nbsp;<a href=\"admin_room_requests.php?seminar_id=".$admin_dates_data["range_id"]."&termin_id=".$db->f("termin_id")."\">"._("Raumanfrage <b>erstellen</b>")."</a><br>\n";
									}
						
									$content.="</font></td></tr>";									
									
									
									//type of date
									$content .= "<tr><td class=\"steel1\" width=\"20%\" align\"left\" valign=\"top\" nowrap>" . "\n";
									$content.="<font size=-1><b>" . _("Art:") . "</b><br><select name=\"art[$c]\">\n";
									for ($i=1; $i<=sizeof($TERMIN_TYP); $i++)
										if ($art == $i)
											$content.= "<option value=$i selected>".$TERMIN_TYP[$i]["name"]."</option>";
										else
											$content.= "<option value=$i>".$TERMIN_TYP[$i]["name"]."</option>";
									$content.="</select>&nbsp;&nbsp;&nbsp;<br><br></td>\n";									
									
									
									//Liked Forum and Folder
									$content .= '<td class="steel1" width="50%" valign="top">' . "\n";
									if (($modules["forum"]) || ($modules["documents"]))
										$content.="<font size=-1><b>" . _("Verkn&uuml;pfungen mit diesem Termin:") . "</b><br />";
						
									//only, if the forum is active
									if ($modules["forum"]) {
										if ($topic_id)
											$content.= _("Forenthema vorhanden") . "<br>";
										else
											$content.= sprintf ("<input type=\"CHECKBOX\" name=\"insert_topic[$c] %s \"/>" . _("Thema im Forum anlegen") . "<br />\n", ($insert_topic) ? TRUE : FALSE);
									}
						
									//only, if the documents-folder is active
									if ($modules["documents"]) {
										if ($folder)
											$content.= _("Dateiordner vorhanden");
										else
											$content.= sprintf ("<input type=\"CHECKBOX\" name=\"insert_folder[$c] %s \"/>" . _("Dateiordner anlegen") . "\n", ($insert_folder) ? TRUE : FALSE);
									}
						
									$content.="<br />&nbsp;</font></td>\n";
															
									//end content
									$content.="</td></tr></table></td></tr>\n<tr><td class=\"steel1\" align=\"center\" colspan=2>";
						
									echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
						
									if (!$show_all)
										$content.="<input type=\"IMAGE\" name=\"edit\" border=0 " . makeButton("uebernehmen", "src") . " align=\"absmiddle\" value=\"ver�ndern\">&nbsp;";
									$content.="<a href=\"$PHP_SELF?kill_single_date=$show_id\">".makeButton("loeschen")."</a><br />";
									printcontent(0,1, $content, '');
									echo "</tr></table>";
									$c++;
								}
							}
						}
						?>
					</td>
				</tr>
				<?
				if ((!$admin_dates_data["insert_id"]) && (($show_all) || ($c>10))) {
				?>
				<tr align="left" height="22">
					<td width="100%" class="steelgraulight" align="right" colspan="3" nowrap>
					<?
					if (!$show_all) {
						?>
						<input type="IMAGE" name="mark_all" border=0 <?=makeButton("alleauswaehlen", "src")?> value="ausw�hlen">&nbsp;
						<input type="IMAGE" name="kill" border=0 <?=makeButton("loeschen", "src")?> value="l�schen">&nbsp; 
						<?
					} else {
						?>
						<input type="IMAGE" name="edit" border=0 <?=makeButton("allesuebernehmen", "src")?> value="ver�ndern">&nbsp; &nbsp;
						<?
					}
					?>
					</td>
				</tr>
				<?
				}
				?>
			</table>
		</td>
		<?
		}
		//create an infobox

		//information about regularly times
		if ($term_data["art"] == 1) {
			$times_inf = _("<b>Typ:</b> unregelm&auml;&szlig;ige Veranstaltung");
			if (get_semester($admin_dates_data["range_id"])) {
				$times_inf .= "<br /><b>" . _("Semester:") . "</b> ". get_semester($admin_dates_data["range_id"]);
			}
		} else {
			$times_inf =  _("<b>Typ:</b> regelm&auml;&szlig;ige Veranstaltung");
			if (view_turnus($admin_dates_data["range_id"]))
				$times_inf .= "<br>".trim(view_turnus($admin_dates_data["range_id"]));
			if (veranstaltung_beginn($admin_dates_data["range_id"]))
				$times_inf .= "<br><b>" . _("Erster Termin:") . "</b> ". veranstaltung_beginn($admin_dates_data["range_id"]);
			if (get_semester($admin_dates_data["range_id"]))
				$times_inf .= "<br /><b>" . _("Semester:") . "</b> ". get_semester($admin_dates_data["range_id"]);
		}

		//the box
		$infobox[0]["kategorie"] = _("Informationen:");
		$infobox[1]["kategorie"] = _("Aktionen:");

		$infobox[0]["eintrag"][] = array ("icon" => "pictures/ausruf_small.gif",
			"text"  => ($admin_dates_data["assi"]) ? _("Sie k&ouml;nnen nun den Ablaufplan und weitere Termine f&uuml;r die neu angelegte Veranstaltung eingeben.") : _("Hier k&ouml;nnen Sie den Ablaufplan und weitere Termine der Veranstaltung ver&auml;ndern."));
		$infobox[0]["eintrag"][] = array ("icon" => "pictures/blank.gif",
			"text"  => $times_inf);
		$infobox[1]["eintrag"][] = array ("icon" => "pictures/link_intern.gif",
			"text"  =>"<a href=\"admin_dates.php?insert_new=TRUE#anchor\">"._("Einen neuen Termin anlegen")."</a>");
		$infobox[1]["eintrag"][] = array ("icon" => "pictures/link_intern.gif",
			"text"  => sprintf(_("Um die allgemeinen Zeiten der Veranstaltung zu &auml;ndern, nutzen Sie bitte den Men&uuml;punkt %s Zeiten %s"), "<a href=\"admin_metadates.php?seminar_id=".$admin_dates_data["range_id"]."\">", "</a>"));
		?>
		<td class="blank" width="1%" valign="top">
							<? print_infobox ($infobox, ($admin_dates_data["assi"]) ? "./locale/$_language_path/LC_PICTURES/hands08.jpg" : "pictures/schedules.jpg"); ?>
			<img src="pictures/blank.gif" width="270" height="1"/>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp;
		</td>
	</tr>
</table>
</form>
<?	
page_close();
?>
</td></tr></table>
</body>
</html>
