<?
/**
* archiv_Assi.php - Archivierungs-Assistent von Stud.IP.
* Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, data-quest <info@data-quest.de>
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
// $Id$

require_once($ABSOLUTE_PATH_STUDIP . "dates.inc.php"); // Funktionen zum Loeschen von Terminen
require_once($ABSOLUTE_PATH_STUDIP . "datei.inc.php"); // Funktionen zum Loeschen von Dokumenten
require_once($ABSOLUTE_PATH_STUDIP . "archiv.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");
require_once($ABSOLUTE_PATH_STUDIP . "config_tools_semester.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "statusgruppe.inc.php"); //Enthaelt Funktionen fuer Statusgruppen
require_once($ABSOLUTE_PATH_STUDIP . "log_events.inc.php"); // Logging
require_once($ABSOLUTE_PATH_STUDIP . "lib/classes/DataFields.class.php"); //Enthaelt Funktionen fuer Statusgruppen
require_once($ABSOLUTE_PATH_STUDIP . "lib/classes/StudipLitList.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "lib/classes/StudipNews.class.php");


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");

$check_perm = (get_config('ALLOW_DOZENT_ARCHIV') ? 'dozent' : 'admin');

$perm->check($check_perm);

include ($ABSOLUTE_PATH_STUDIP . 'seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES . "/lib/DeleteResourcesUser.class.php");
} 
// # Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;

$sess->register("archiv_assi_data");
$cssSw = new cssClassSwitcher; 
// Start of Output
include ($ABSOLUTE_PATH_STUDIP.'html_head.inc.php'); // Output of html head
include ($ABSOLUTE_PATH_STUDIP.'header.php'); // Output of Stud.IP head
include ($ABSOLUTE_PATH_STUDIP.'links_admin.inc.php'); //Linkleiste fuer admins

// single delete (a Veranstaltung is open)
if ($SessSemName[1]) {
	$archiv_sem[] = "_id_" . $SessSemName[1];
	$archiv_sem[] = "on";
} 
// Handlings....
// Kill current list and stuff

if ($new_session)
	$archiv_assi_data = '';

// A list was sent
if (is_array($archiv_sem)) {
	unset($archiv_assi_data["sems"]);
	unset($archiv_assi_data["sem_check"]);
	$archiv_assi_data["pos"] = 0;
	foreach($archiv_sem as $key => $val) {
		if ((substr($val, 0, 4) == "_id_") && (substr($$archiv_sem[$key + 1], 0, 4) != "_id_"))
				if ($archiv_sem[$key + 1] == "on") {
					$archiv_assi_data["sems"][] = array("id" => substr($val, 4, strlen($val)), "succesful_archived" => FALSE);
					$archiv_assi_data["sem_check"][substr($val, 4, strlen($val))] = TRUE;
				} 
	} 
} 
// inc if we have lectures left in the upper
if ($inc)
	if ($archiv_assi_data["pos"] < sizeof($archiv_assi_data["sems"])-1) {
		$i = 1;
		while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $i]["id"]]) && ($archiv_assi_data["pos"] + $i < sizeof($archiv_assi_data["sems"])-1))
		$i++;
		if ((sizeof($archiv_assi_data["sem_check"]) > 1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $i]["id"]]))
			$archiv_assi_data["pos"] = $archiv_assi_data["pos"] + $i;
	} 

// dec if we have lectures left in the lower
if ($dec)
	if ($archiv_assi_data["pos"] > 0) {
		$d = -1;
		while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $d]["id"]]) && ($archiv_assi_data["pos"] + $d > 0))
		$d--;
		if ((sizeof($archiv_assi_data["sem_check"]) > 1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $d]["id"]]))
			$archiv_assi_data["pos"] = $archiv_assi_data["pos"] + $d;
	} 
	
// Delete (and archive) the lecture
if ($archive_kill) {
	$run = TRUE;
	$s_id = $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]; 
	// # Do we have permission to do so?

	if (!$perm->have_perm($check_perm)) {
		$msg .= "error�" . _("Sie haben keine Berechtigung zum archivieren von Veranstaltungen.") . "�";
		$run = FALSE;
	} 
	// Trotzdem nochmal nachsehen
	if (!$perm->have_studip_perm($check_perm , $s_id)) {
		$msg .= "error�" . _("Sie haben keine Berechtigung diese Veranstaltung zu archivieren.") . "�";
		$run = FALSE;
	} 

	if ($run) {
		// Bevor es wirklich weg ist. kommt das Seminar doch noch schnell ins Archiv
		in_archiv($s_id); 
		
		// Delete that Seminar.
		
		// Alle Benutzer aus dem Seminar rauswerfen.
		$query = "DELETE from seminar_user where Seminar_id='$s_id'";
		$db->query($query);
		if (($db_ar = $db->affected_rows()) > 0) {
			$liste .= "<li>" . sprintf(_("%s VeranstaltungsteilnehmerInnen, DozentenInnen oder TutorenInnen archiviert."), $db_ar) . "</li>";
		} 
		
		// Alle Benutzer aus Wartelisten rauswerfen
		$query = "DELETE from admission_seminar_user where seminar_id='$s_id'";
		$db->query($query); 
		
		// Alle Eintraege aus Zuordnungen zu Studiengaenge rauswerfen
		$query = "DELETE from admission_seminar_studiengang where seminar_id='$s_id'";
		$db->query($query); 
		
		// Alle beteiligten Institute rauswerfen
		$query = "DELETE FROM seminar_inst where Seminar_id='$s_id'";
		$db->query($query);
		if (($db_ar = $db->affected_rows()) > 0) {
			$liste .= "<li>" . sprintf(_("%s Zuordnungen zu Einrichtungen archiviert."), $db_ar) . "</li>";
		} 
		
		// user aus den Statusgruppen rauswerfen
		$count = DeleteAllStatusgruppen($s_id);
		if ($count > 0) {
			$liste .= "<li>" . _("Eintr&auml;ge aus Funktionen / Gruppen gel&ouml;scht.") . "</li>";
		} 
		
		// Alle Eintraege aus dem Vorlesungsverzeichnis rauswerfen
		$db_ar = StudipSemTree::DeleteSemEntries(null, $s_id);
		if ($db_ar > 0) {
			$liste .= "<li>" . sprintf(_("%s Zuordnungen zu Bereichen archiviert."), $db_ar) . "</li>";
		} 
		
		// Alle Termine mit allem was dranhaengt zu diesem Seminar loeschen.
		if (($db_ar = delete_range_of_dates($s_id, TRUE)) > 0) {
			$liste .= "<li>" . sprintf(_("%s Veranstaltungstermine archiviert."), $db_ar) . "</li>";
		} 
		
		// Alle weiteren Postings zu diesem Seminar loeschen.
		$query = "DELETE from px_topics where Seminar_id='$s_id'";
		$db->query($query);
		if (($db_ar = $db->affected_rows()) > 0) {
			$liste .= "<li>" . sprintf(_("%s Postings archiviert."), $db_ar) . "</li>";
		} 
		
		// Alle Dokumente zu diesem Seminar loeschen.
		if (($db_ar = delete_all_documents($s_id)) > 0) {
			$liste .= "<li>" . sprintf(_("%s Dokumente und Ordner archiviert."), $db_ar) . "</li>";
		} 
		
		// Freie Seite zu diesem Seminar l�schen
		$query = "DELETE FROM scm where range_id='$s_id'";
		$db->query($query);
		if (($db_ar = $db->affected_rows()) > 0) {
			$liste .= "<li>" . _("Freie Seite der Veranstaltung archiviert.") . "</li>";
		} 
		
		// delete literatur 
		$del_lit = StudipLitList::DeleteListsByRange($s_id);
		if ($del_lit) {
			$liste .= "<li>" . sprintf(_("%s Literaturlisten archiviert."),$del_lit['list'])  . "</li>";
		}
		
		// Alle News-Verweise auf dieses Seminar l�schen
		if ( ($db_ar = StudipNews::DeleteNewsRanges($s_id)) ) {
			$liste .= "<li>" . sprintf(_("%s News gel&ouml;scht."), $db_ar) . "</li>";
		} 

		//kill the datafields
		$DataFields = new DataFields($s_id);
		$DataFields->killAllEntries();
		
		//kill all wiki-pages
		$query = sprintf ("DELETE FROM wiki WHERE range_id='%s'", $s_id);
		$db->query($query);
		if (($db_wiki = $db->affected_rows()) > 0) {
			$liste .= "<li>" . sprintf(_("%s Wiki-Seiten archiviert."), $db_wiki) . "</li>";
		}

		$query = sprintf ("DELETE FROM wiki_links WHERE range_id='%s'", $s_id);
		$db->query($query);

		$query = sprintf ("DELETE FROM wiki_locks WHERE range_id='%s'", $s_id);
		$db->query($query);
		
		// kill all the ressources that are assigned to the Veranstaltung (and all the linked or subordinated stuff!)
		if ($RESOURCES_ENABLE) {
			$killAssign = new DeleteResourcesUser($s_id);
			$killAssign->delete();
		} 

		if ($liste)
			$msg .= "info�<font size=-1>$liste</font>�"; 
		
		//kill the object_user_vists for this seminar
		object_kill_visits(null, $s_id);

                // Logging...
                $query="SELECT seminare.name as name, seminare.VeranstaltungsNummer as number, semester_data.name as semester FROM seminare LEFT JOIN semester_data ON (seminare.start_time = semester_data.beginn) WHERE seminare.Seminar_id='$s_id'";
                $db->query($query);
                if ($db->next_record()) {
                        $semlogname=$db->f('number')." ".$db->f('name')." (".$db->f('semester').")";
                } else {
                        $semlogname="unknown sem_id: $s_id";
                }
                log_event("SEM_ARCHIVE",$s_id,NULL,$semlogname);
                // ...logged
								
		// und das Seminar loeschen.
		$query = "DELETE FROM seminare where Seminar_id= '$s_id'";
		$db->query($query);
		if ($db->affected_rows() == 0) {
			$msg .= "error�<b>" . _("Fehler beim L&ouml;schen der Veranstaltung") . "�";
			die;
		} 
		// Successful archived, if we are here
		$msg .= "msg�" . sprintf(_("Die Veranstaltung %s wurde erfolgreich archiviert und aus der Liste der aktiven Veranstaltungen gel&ouml;scht. Sie steht nun im Archiv zur Verf&uuml;gung."), "<b>" . htmlReady(stripslashes($tmp_name)) . "</b>") . "�"; 
		
		// unset the checker, lecture is now killed!
		unset($archiv_assi_data["sem_check"][$s_id]);
		
		// if there are lectures left....
		if (is_array($archiv_assi_data["sem_check"])) {
			if ($archiv_assi_data["pos"] < sizeof($archiv_assi_data["sems"])-1) { // ...inc the counter if possible..
				$i = 1;
				while ((! $archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $i]["id"]]) && ($archiv_assi_data["pos"] + $i < sizeof($archiv_assi_data["sems"])-1))
				$i++;
				$archiv_assi_data["pos"] = $archiv_assi_data["pos"] + $i;
			} else { // ...else dec the counter to find a unarchived lecture
				if ($archiv_assi_data["pos"] > 0)
					$d = -1;
				while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $d]["id"]]) && ($archiv_assi_data["pos"] + $d > 0))
				$d--;
				$archiv_assi_data["pos"] = $archiv_assi_data["pos"] + $d;
			} 
		} 
	} 
} 

// Outputs...
if (($archiv_assi_data["sems"]) && (sizeof($archiv_assi_data["sem_check"]) > 0)) {
	$db->query("SELECT * FROM seminare WHERE Seminar_id = '" . $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"] . "' ");
	$db->next_record();
	$msg .= "info�<font color=\"red\">" . _("Sie sind im Begriff, die untenstehende  Veranstaltung zu archivieren. Dieser Schritt kann nicht r&uuml;ckg&auml;ngig gemacht werden!") . "�"; 
	// check is Veranstaltung running
	if ($db->f("duration_time") == -1) {
		$msg .= "info�" . _("Das Archivieren k&ouml;nnte unter Umst&auml;nden nicht sinnvoll sein, da es sich um eine dauerhafte Veranstaltung handelt.") . "�";
	} elseif (time() < ($db->f("start_time") + $db->f("duration_time"))) {
		$msg .= "info�" . _("Das Archivieren k&ouml;nnte unter Umst&auml;nden nicht sinnvoll sein, da das oder die Semester, in denen die Veranstaltung stattfindet, noch nicht verstrichen sind.") . "�";
	} 
?>
<body>

<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><b>&nbsp;
		<?
		echo $SEM_TYPE[$db->f("status")]["name"], ": ", htmlReady(substr($db->f("Name"), 0, 60));
		if (strlen($db->f("Name")) > 60)
			echo "... ";
		echo " -  " . _("Archivieren der Veranstaltung");
		?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2><b>&nbsp;
		<table align="center" width="99%" border=0 cellpadding=2 cellspacing=0>
			<?
			parse_msg($msg, "�", "blank", 3);
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" colspan=3 valign="top" width="96%">
				<? 
					// Grunddaten des Seminars
					printf ("<b>%s</b>", htmlReady($db->f("Name"))); 
					// last activity
					$last_activity = lastActivity($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]);
					if ((time() - $last_activity) < (60 * 60 * 24 * 7 * 12))
						$activity_warning = TRUE;
					printf ("<br><font size=\"-1\" >" . _("letzte Ver&auml;nderung am:") . " %s%s%s </font>", ($activity_warning) ? "<font color=\"red\" >" : "", date("d.m.Y, G:i", $last_activity), ($activity_warning) ? "</font>" : "");
					?>
				</td>
			</tr>
			<? if ($db->f("Untertitel") != "") {

						?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" colspan=2 valign="top" width="96%">
				<? 
				// Grunddaten des Seminars
				printf ("<font size=-1><b>" . _("Untertitel:") . "</b></font><br /><font size=-1>%s</font>", htmlReady($db->f("Untertitel")));
				?>
				</td>
			</tr>
			<? } 
					?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>" . _("Zeit:") . "</b></font><br /><font size=-1>%s</font>", view_turnus($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"], FALSE));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>" . _("Semester:") . "</b></font><br /><font size=-1>%s</font>", get_semester($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]));
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>" . _("Erster Termin:") . "</b></font><br /><font size=-1>%s</font>", veranstaltung_beginn($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>" . _("Vorbesprechung:") . "</b></font><br /><font size=-1>%s</font>", (vorbesprechung($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])) ? vorbesprechung($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]) : _("keine"));
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Veranstaltungsort:") . "</b></font><br /><font size=-1>%s</font>", (getRoom($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])) ? getRoom($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"], FALSE) : "nicht angegeben");
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				if ($db->f("VeranstaltungsNummer"))
					printf ("<font size=-1><b>" . _("Veranstaltungsnummer:") . "</b></font><br /><font size=-1>%s</font>", htmlReady($db->f("VeranstaltungsNummer")));
				else
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<? 
				// wer macht den Dozenten?
				$db2->query ("SELECT " . $_fullname_sql['full'] . " AS fullname, seminar_user.user_id, username, status FROM seminar_user  LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '" . $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"] . "' AND status = 'dozent' ORDER BY Nachname");
				if ($db2->num_rows() > 1)
					printf ("<font size=-1><b>" . _("DozentInnen:") . "</b></font><br />");
				else
					printf ("<font size=-1><b>" . _("DozentIn:") . "</b></font><br />");
				while ($db2->next_record()) {
					if ($db2->num_rows() > 1)
						print "<li>";
					printf("<font size=-1><a href = about.php?username=%s>%s</a></font>", $db2->f("username"), htmlReady($db2->f("fullname")));
					if ($db2->num_rows() > 1)
						print "</li>";
				} 

				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<? 
				// und wer ist Tutor?
				$db2->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status FROM seminar_user  LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '" . $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"] . "' AND status = 'tutor' ORDER BY Nachname");
				if ($db2->num_rows() > 1)
					printf ("<font size=-1><b>" . _("TutorInnen:") . "</b></font><br />");
				elseif ($db2->num_rows() == 0)
					printf ("<font size=-1><b>" . _("TutorIn:") . "</b></font><br /><font size=-1>" . _("keine") . "</font>");
				else
					printf ("<font size=-1><b>" . _("TutorIn:") . "</b></font><br />");
				while ($db2->next_record()) {
					if ($db2->num_rows() > 1)
						print "<li>";
					printf("<font size=-1><a href = about.php?username=%s>%s</a></font>", $db2->f("username"), htmlReady($db2->f("fullname")));
					if ($db2->num_rows() > 1)
						print "</li>";
				} 

				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Veranstaltungstyp:") . "</b></font><br /><font size=-1>%s in der Kategorie %s</font>", $SEM_TYPE[$db->f("status")]["name"], $SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"]);
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				if ($db->f("art"))
					printf ("<font size=-1><b>" . _("Art/Form:") . "</b></font><br /><font size=-1>%s</font>", htmlReady($db->f("art")));
				else
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<? if ($db->f("Beschreibung") != "") {

						?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Kommentar/Beschreibung:") . "</b></font><br /><font size=-1>%s</font>", htmlReady($db->f("Beschreibung"), TRUE, TRUE));
				?>
				</td>
			</tr>	
			<?
			} 
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				$db2->query("SELECT Name, url, Institut_id FROM Institute WHERE Institut_id = '" . $db->f("Institut_id") . "' ");
				$db2->next_record();
				if ($db2->num_rows()) {
					printf("<font size=-1><b>" . _("Heimat-Einrichtung:") . "</b></font><br /><font size=-1><a href=\"institut_main.php?auswahl=%s\">%s</a></font>", $db2->f("Institut_id"), htmlReady($db2->f("Name")));
				} 

				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				$db2->query("SELECT Name, url, Institute.Institut_id FROM Institute LEFT JOIN seminar_inst USING (institut_id) WHERE seminar_id = '" . $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"] . "' AND Institute.institut_id != '" . $db->f("Institut_id") . "'");
				if ($db2->num_rows() == 1)
					printf ("<font size=-1><b>" . _("beteiligte Einrichtung:") . "</b></font><br />");
				elseif ($db2->num_rows() >= 2)
					printf ("<font size=-1><b>" . _("beteiligte Einrichtungen:") . "</b></font><br />");
				else
					print "&nbsp; ";
				while ($db2->next_record()) {
					if ($db2->num_rows() >= 2)
						print "<li>";
					printf("<font size=-1><a href=\"institut_main.php?auswahl=%s\">%s</a></font><br />", $db2->f("Institut_id"), htmlReady($db2->f("Name")));
					if ($db2->num_rows() > 2)
						print "</li>";
				} 

				?>
				</td>
			</tr>			
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="96%" valign="top" align="center">
				<? 
				// can we dec?
				if ($archiv_assi_data["pos"] > 0) {
					$d = -1;
					while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $d]["id"]]) && ($archiv_assi_data["pos"] + $d > 0))
					$d--;
					if ((sizeof($archiv_assi_data["sem_check"]) > 1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $d]["id"]]))
						$inc_possible = TRUE;
				} 
				if ($inc_possible) {
					print("&nbsp;<a href=\"$PHP_SELF?dec=TRUE\">" . makeButton("vorherige", "img") . "</a>");
				} 
				if (!$links_admin_data["sem_id"]) {
					echo '&nbsp;<a href="' .
					 (($SessSemName[1]) ?  $GLOBALS['ABSOLUTE_URI_STUDIP'].'admin_seminare1.php?list=TRUE' : $_SERVER['PHP_SELF'].'?list=TRUE&new_session=TRUE'). '">' . makeButton('abbrechen', 'img') . '</a>';
				} 
				print("&nbsp;<a href=\"$PHP_SELF?archive_kill=TRUE\">" . makeButton("archivieren", "img") . "</a>"); 
				// can we inc?
				if ($archiv_assi_data["pos"] < sizeof($archiv_assi_data["sems"])-1) {
					$i = 1;
					while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $i]["id"]]) && ($archiv_assi_data["pos"] + $i < sizeof($archiv_assi_data["sems"])-1))
					$i++;
					if ((sizeof($archiv_assi_data["sem_check"]) > 1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"] + $i]["id"]]))
						$dec_possible = TRUE;
				} 
				if ($dec_possible) {
					print("&nbsp;<a href=\"$PHP_SELF?inc=TRUE\">" . makeButton("naechster", "img") . "</a>");
				} 
				if (sizeof($archiv_assi_data["sems"]) > 1)
					printf ("<br /><font size=\"-1\">" . _("noch <b>%s</b> von <b>%s</b> Veranstaltungen zum Archivieren ausgew&auml;hlt.") . "</font>", sizeof($archiv_assi_data["sem_check"]), sizeof($archiv_assi_data["sems"]));
				?>
				</td>
			</tr>
		</table>
		<br />
	</td>
	</tr>
	</table>

	<?
	} elseif (($archiv_assi_data["sems"]) && (sizeof($archiv_assi_data["sem_check"]) == 0)) {
	?>

	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><b>&nbsp; <?=_("Die Veranstaltung wurde archiviert.")?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2><b>&nbsp;
		<?
		parse_msg($msg . "info�" . _("Sie haben alle ausgew&auml;hlten Veranstaltungen archiviert!"));
		?>
		</td>
	</tr>	
	</table>
	<?
	if ($links_admin_data["sem_id"] == $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])
		reset_all_data();
	} elseif (!$list) {
	if ($links_admin_data["sem_id"] == $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])
		reset_all_data();
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><b>&nbsp; <?=_("Keine Veranstaltung zum Archivieren gew&auml;hlt")?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2><b>&nbsp;
		<?
		if (!$links_admin_data["sem_id"])
			parse_msg("info�" . _("Sie haben keine Veranstaltung zum Archivieren gew&auml;hlt."));
		?>
		</td>
	</tr>
	</table>
	<?
	} 
	page_close();
	?>
</body>
</html>
