<?php
/*
details.php - Detail-Uebersicht und Statistik fuer ein Seminar
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

?>

<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
  <title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
 </head>
<body>


<?php
	include "$ABSLOUTE_PATH_STUDIP/seminar_open.php"; //hier werden die sessions initialisiert
	include "$ABSLOUTE_PATH_STUDIP/header.php";   //hier wird der "Kopf" nachgeladen
	
	require_once "$ABSLOUTE_PATH_STUDIP/msg.inc.php";
	require_once "$ABSLOUTE_PATH_STUDIP/dates.inc.php"; //Funktionen zum Anzeigen der Terminstruktur
	require_once "$ABSLOUTE_PATH_STUDIP/config.inc.php";
	require_once "$ABSLOUTE_PATH_STUDIP/visual.inc.php"; // wir brauchen htmlReady
	require_once "$ABSLOUTE_PATH_STUDIP/admission.inc.php"; // wir brauchen htmlReady

?>
<body>
<?

//Inits
$cssSw=new cssClassSwitcher;
$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;


//wenn kein Seminar gesetzt und auch kein externer Aufruf raus....
IF (($SessSemName[1] =="") && (!isset($sem_id)))
	{
	parse_window ("error�Sie haben kein Objekt gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt (Veranstaltung oder Einrichtung) gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich l�nger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zur�ck zur Anmeldung zu gelangen. </font>", "�",
				"Keine Veranstaltung gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
	}
	
//wenn Seminar gesetzt und kein externer Aufruf uebernahme der SessionVariable
if (($SessSemName[1] <>"") && (!isset($sem_id)))
	{
	include "links1.php";

	$sem_id=$SessSemName[1];
	
	}

// nachfragen, ob das Seminar abonniert werden soll
if (($sem_id) && (!$perm->have_perm("admin"))) {
	if ($perm->have_perm("user")) { //Add lecture only if logged in	
		$db->query("SELECT status FROM seminar_user WHERE user_id ='$user->id' AND Seminar_id = '$sem_id'");
		$db->next_record();
		if (!$db->num_rows()) {
			$db->query("SELECT status FROM admission_seminar_user WHERE user_id ='$user->id' AND seminar_id = '$sem_id'");
			if (!$db->num_rows())
				$abo_msg="Tragen Sie sich hier ein";
		} else {
			if ($db->f("status") == "user") {
				$abo_msg="Schrebrechte aktivieren";
			}
		}
	}
}

if ($send_from_search)
    	$back_msg.="Zur&uuml;ck zur letzten Auswahl";

//Namen holen
$db2->query("SELECT * FROM seminare WHERE Seminar_id = '$sem_id'");
$db2->next_record();
	
//In dieser Datei nehmen wir die Art direkt, nicht aus Session, da die Datei auch ausserhalb von Seminaren aufgerufen wird
if ($SEM_TYPE[$db2->f("status")]["name"] == $SEM_TYPE_MISC_NAME) //Typ fuer Sonstiges
	$art = "Veranstaltung"; 
else
	$art = $SEM_TYPE[$db2->f("status")]["name"];

	
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr><td class="topic" colspan=2><b>&nbsp;<? echo $art,": ",htmlReady($db2->f("Name"))." - Details"; ?>
	</b></td></tr>
	<?

	if ($msg)
		{
		echo "<tr><td class=\"blank\" colspan=2>&nbsp;</td></tr>";
		parse_msg($msg);
		}
	?>
	<tr><td class="blank">
		&nbsp; <br />
		<table align="center" width="99%" border=0 cellpadding=2 cellspacing=0>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" colspan=2 valign="top" width="70%">
				<?
				//Titel und Untertitel der Veranstaltung
				printf ("<b>%s</b><br /> ",htmlReady($db2->f("Name")));
				printf ("<font size=-1>%s</font>",htmlReady($db2->f("Untertitel")));
				?>
				</td>
				<td class="angemeldet" width="26%" rowspan=4  valign="top">
					<table "center" width="99%" border=0 cellpadding=2 cellspacing=0>
					<tr>
						<td width="100%" colspan=2>
							<font size=-1><b><? print "Pers&ouml;nlicher Status" ?>:</b></font>
							<?
							// Meinen Status ermitteln
							$user_id = $auth->auth["uid"];
							$db3->query("SELECT status FROM seminar_user WHERE Seminar_id = '$sem_id' AND user_id = '$user_id'");
							if ($db3->next_record() ){
							$mein_status = $db3->f("status");
							} else {
								unset ($mein_status);
							}
							//Status als Wartender ermitteln
							$db3->query("SELECT status FROM admission_seminar_user WHERE seminar_id = '$sem_id' AND user_id = '$user_id'");
							if ($db3->next_record() ){
							$admission_status = $db3->f("status");
							} else {
								unset ($admission_status);
							}
							?>
						</td>
					</tr>
					<tr>
						<td width="1%" valign="top">
							<?printf ("%s", (($mein_status) || ($admission_status))? "<img src=\"./pictures/haken.gif\" border=0>" : "<img src=\"./pictures/x2.gif\" border=0>");?>
						</td>
						<td width="99%">
						<?
						if (($mein_status) || ($admission_status)) {
							if ($mein_status)
								$tmp_text="Sie sind als Teilnehmer der Veranstaltung eingetragen";
							elseif ($admission_status)
								$tmp_text=sprintf ("Sie sind in die %s der Veranstaltung eingetragen", ($admission_status=="claiming")  ? "Anmeldeliste" : "Warteliste");
						} elseif (!$perm->have_perm("admin"))
							$tmp_text="Sie sind nicht als Teilnehmer der Veranstaltung eingetragen.";
						else
							$tmp_text="Sie sind Administrator und k&ouml;nnen die Veranstaltung nicht abonnieren.";
						printf ("<font size=-1 %s>%s</font>", ((!$mein_status) && (!$admission_status)) ? " color=\"red\"" : "",$tmp_text);
						?>
						</td>
					</tr>
					<?
					if ($abo_msg) {
					?>
					<tr>
						<td width="1%" valign="top">
							<? echo "<a href=\"sem_verify.php?id=".$sem_id."&send_from_search=&send_from_search_page=$send_from_search_page\"><img src=\"./pictures/meinesem.gif\" border=0></a>"; ?>
						</td>
						<td width="99%">
							<font size=-1><? echo "<a href=\"sem_verify.php?id=".$sem_id."&send_from_search=$send_from_search&send_from_search_page=$send_from_search_page\">",$abo_msg, "</a>"; ?></font>
						</td>					
					</tr>
					<? } 
					if ($back_msg) {
					?>
					<tr>
						<td width="1%" valign="top">&nbsp; 
						</td>
						<td width="99%">
							<font size=-1><? echo "<a href=\"$send_from_search_page\">",$back_msg, "</a>"; ?></font>
						</td>					
					</tr>
					<? }
					if ($db2->f("admission_binding")) {
					?>
					<tr>
						<td width="1%" valign="top">
							<img src="./pictures/ausruf_small.gif" border=0>
						</td>
						<td width="99%">
							<font size=-1>Das Abonnement dieser Veranstaltung ist <u>bindend</u>!</font>
						</td>					
					</tr>
					<? } ?>
					
					<tr>
						<td width="100%" colspan=2>
							<font size=-1><b><? print  "Berechtigungen" ?>:</b><br /></font> 
						</td>
					</tr>
					<tr>
						<td width="1%">
						</td>
						<td width="99%">
						<?
						// Ampel-Schaltung
						printf ("<font size=-1>Lesen:&nbsp;</font>");
						get_ampel_read($mein_status, $admission_status, $db2->f("Lesezugriff"));
					
						printf ("<font size=-1><br />Schreiben:&nbsp;</font>");
						get_ampel_write($mein_status, $admission_status, $db2->f("Schreibzugriff"));
						?>
						</td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="35%">
				<?
				printf ("<font size=-1><b>Zeit:</b></font><br /><font size=-1>%s</font>",htmlReady(view_turnus($sem_id, FALSE)));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="35%">
				<?
				printf ("<font size=-1><b>Semester:</b></font><br /><font size=-1>%s</font>",get_semester($sem_id));
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="35%">
				<?
				printf ("<font size=-1><b>Erster Termin:</b></font><br /><font size=-1>%s</font>",veranstaltung_beginn($sem_id));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="35%">
				<?
				printf ("<font size=-1><b>Vorbesprechung:</b></font><br /><font size=-1>%s</font>", (vorbesprechung($sem_id)) ? vorbesprechung($sem_id) : "keine");
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="35%" valign="top">
				<?
				printf ("<font size=-1><b>Veranstaltungsort:</b></font><br /><font size=-1>%s</font>",($db2->f("Ort")) ? htmlReady($db2->f("Ort")) : "nicht angegeben");
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="35%"  align="top">
				<?
				if ($db2->f("VeranstaltungsNummer"))
					printf ("<font size=-1><b>Veranstaltungsnummer:</b></font><br /><font size=-1>%s</font>",$db2->f("VeranstaltungsNummer"));
				else	
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="35%" valign="top">
				<?		
			//wer macht den Dozenten?
				$db->query ("SELECT Vorname, Nachname, seminar_user.user_id, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'dozent' ORDER BY Nachname");
				if ($db->num_rows() > 1)
					printf ("<font size=-1><b>DozentInnen:</b></font><br />");
				elseif ($db->num_rows() == 1)
					printf ("<font size=-1><b>DozentIn:</b></font><br />");
				else	
					print "&nbsp; ";
				while ($db->next_record()) {
					if ($db->num_rows() > 1)
						print "<li>";
					printf( "<font size=-1><a href = about.php?username=%s>%s</a></font>",$db->f("username"), htmlReady($db->f("Vorname"))." ".htmlReady($db->f("Nachname")) );
					if ($db->num_rows() > 1)
						print "</li>";
				}
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>"width="61%" colspan=2 valign="top">
				<?		
				//und wer ist Tutor?
				$db->query ("SELECT seminar_user.user_id, Vorname, Nachname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'tutor' ORDER BY Nachname");
				if ($db->num_rows() > 1)
					printf ("<font size=-1><b>TutorInnen:</b></font><br />");
				elseif ($db->num_rows() == 1)
					printf ("<font size=-1><b>TutorIn:</b></font><br />");
				else	
					print "&nbsp; ";
				while ($db->next_record()) {
					if ($db->num_rows() > 1)
						print "<li>";
					printf( "<font size=-1><a href = about.php?username=%s>%s</a></font>",$db->f("username"), htmlReady($db->f("Vorname"))." ".htmlReady($db->f("Nachname")) );
					if ($db->num_rows() > 1)
						print "</li>";
				}
				?>
				</td>
			</tr>
		</table>
		<table align="center" width="99%" border=0 cellpadding=2 cellspacing=0>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
				<?
				printf ("<font size=-1><b>Veranstaltungstyp:</b></font><br /><font size=-1>%s in der Kategorie %s</font>",$SEM_TYPE[$db2->f("status")]["name"], $SEM_CLASS[$SEM_TYPE[$db2->f("status")]["class"]]["name"]);
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
				<?
				if ($db2->f("art"))
					printf ("<font size=-1><b>Art/Form:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("art")));
				else	
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<? if ($db2->f("Beschreibung") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>Kommentar/Beschreibung:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("Beschreibung"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("teilnehmer") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>Teilnehmer:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("teilnehmer"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("vorrausetzungen") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>Vorausetzungen:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("vorrausetzungen"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("lernorga") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>Lernorganisation:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("lernorga"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("leistungsnachweis") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>Leistungsnachweis:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("leistungsnachweis"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("Sonstiges") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>Sonstiges:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("Sonstiges"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("ects") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>ECTS-Kreditpunkte:</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("ects"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			// Anzeige der Bereiche  
			if ($SEM_CLASS[$SEM_TYPE[$db2->f("status")]["class"]]["bereiche"]) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="96%" valign="top">
				<?
				$db3->query("SELECT bereiche.* FROM bereiche LEFT JOIN seminar_bereich USING(bereich_id) WHERE seminar_id = '$sem_id'");
				if ($db3->num_rows() ==1)
					printf ("<font size=-1><b>Studienbereich:</b></font><br />");
				elseif ($db3->num_rows() >=2)
					printf ("<font size=-1><b>Studienbereiche:</b></font><br />");
				while ($db3->next_record()) {
					if ($db3->num_rows() >= 2)
						print "<li>";
						printf ("<font size=-1><a href=\"show_bereich.php?level=sbb&id=%s\">%s</a></font>",$db3->f('bereich_id'), htmlReady($db3->f("name")));
					if ($db3->num_rows() > 2)
						print "</li>";
				}
				?>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
				<?
				$db3->query("SELECT Name, url, Institut_id FROM Institute WHERE Institut_id = '".$db2->f("Institut_id")."' ");
				$db3->next_record();
				if ($db3->num_rows()) {
				printf("<font size=-1><b>Heimat-Einrichtung:</b></font><br /><font size=-1><a href=\"institut_main.php?auswahl=%s\">%s</a></font>", $db3->f("Institut_id"), htmlReady($db3->f("Name")));
				}
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
				<?
				$db3->query("SELECT Name, url, Institute.Institut_id FROM Institute LEFT JOIN seminar_inst USING (institut_id) WHERE seminar_id = '$sem_id' AND Institute.institut_id != '".$db2->f("Institut_id")."'");
				if ($db3->num_rows() ==1)
					printf ("<font size=-1><b>beteiligte Einrichtung:</b></font><br />");
				elseif ($db3->num_rows() >=2)
					printf ("<font size=-1><b>beteiligte Einrichtungen:</b></font><br />");
				else	
					print "&nbsp; ";
				while ($db3->next_record()) {
					if ($db3->num_rows() >= 2)
						print "<li>";
					printf("<font size=-1><a href=\"institut_main.php?auswahl=%s\">%s</a></font><br />", $db3->f("Institut_id"), htmlReady($db3->f("Name")));
					if ($db3->num_rows() > 2)
						print "</li>";
				}				
				?>
				</td>
			</tr>
			<?
			if ($db2->f("admission_type")) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
				<font size=-1><b>Anmeldeverfahren:</b></font><br />				
				<?
				if ($db2->f("admission_selection_take_place") == 1) {
					if ($db2->f("admission_type") == 1)
						printf ("<font size=-1>Die Teilnehmerauswahl wurde nach dem Losverfahren am %s Uhr festgelegt. Weitere Teilnehmer k&ouml;nnen per Warteliste einen Platz bekommen.</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
					else
						printf ("<font size=-1>Die Teilnehmerauswahl erfolgt in der Reihenfolge der Anmeldung. Die Kontingentierung wurd am %s aufgehoben.Weitere Pl&auml;tze k&ouml;nnen noch &uuml;ber Wartelisten vergeben werden.</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
				} else {
					if ($db2->f("admission_type") == 1)
						printf ("<font size=-1>Die Teilnehmerauswahl erfolgt nach dem Losverfahren am %s Uhr.</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
					else
						printf ("<font size=-1>Die Teilnehmerauswahl erfolgt in der Reihenfolge der Anmeldung. Die Kontingentierung wird am %s aufgehoben.</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
				}
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
					<font size=-1><b>Kontingente:</b></font><br />
					<?
					$db3->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id = '$sem_id' "); //Alle  moeglichen Studiengaenge anziegen
					while ($db3->next_record()) {
						if ($db3->f("studiengang_id") == "all")
							$tmp_details_quota=get_all_quota($sem_id);
						else
							$tmp_details_quota=round ($db2->f("admission_turnout") * ($db3->f("quota") / 100));
						printf ("<font size=-1>Kontingent f&uuml;r %s (%s Pl&auml;tze)</font>",  ($db3->f("studiengang_id") == "all") ? "alle Studieng&auml;nge" : $db3->f("name"), $tmp_details_quota);
						print "<br />";
					}
				       ?>
				</td>				
			</tr>
			<? } ?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="24%" valign="top">
				<?
				//Statistikfunktionen
				$db3->query("SELECT count(*) as anzahl FROM seminar_user WHERE Seminar_id = '$sem_id'");
				$db3->next_record();
				printf ("<font size=-1><b>Angemeldete Teilnehmer:&nbsp;</b></font><font size=-1>%s </font>", ($db3->f("anzahl")) ? $db3->f("anzahl") : "keine");
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="24%" valign="top">
				<?
				if ($db2->f("admission_turnout"))
					printf ("<font size=-1><b>%s Teilnehmerzahl:&nbsp;</b></font><font size=-1>%s </font>", ($db2->f("admission_type")) ? "max. " : "erw.", $db2->f("admission_turnout"));
				else
					print "&nbsp; ";
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="24%" valign="top">
				<?
				$db3->query("SELECT count(*) as anzahl FROM px_topics WHERE Seminar_id = '$sem_id'");
				$db3->next_record();
				printf ("<font size=-1><b>Postings:&nbsp;</b></font><font size=-1>%s </font>", ($db3->f("anzahl")) ? $db3->f("anzahl") :"keine");
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				$db3->query("SELECT count(*) as anzahl FROM dokumente WHERE Seminar_id = '$sem_id'");
				$db3->next_record();
				printf ("<font size=-1><b>Dokumente:&nbsp;</b></font><font size=-1>%s </font>", ($db3->f("anzahl")) ? $db3->f("anzahl") : "keine");
				?>
				</td>
				
			</tr>
		</table>
		<br />&nbsp; 
	</td>
	</tr>
	</table>
</td>
</tr>
</table>
</body>
</html>
<?php
	
// Save data back to database.
page_close();
 ?>
<!-- $Id$ -->