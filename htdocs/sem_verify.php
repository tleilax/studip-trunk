<?
/**
* sem_verify.php
*
* checks the entry to a Veranstaltung an insert user to the seminar_user table
*
*
* @author		Andr� Noack <noack@data-quest.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @module		sem_verify.php
* @modulegroup	misc
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sem_verify.php
// Ueberprueft Zutrittsvorausetzungen fuer Veranstaltungen und traegt Nutzer in die Tabelle seminar_user ein
// Copyright (C) 2002 Andr� Noack <noack@data-quest.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

/*
 * This functions is used for printing a message, that the user can decide whether really to sign in to the seminar or not
 * @param	$sem_id		Seminar_id
 * @param	$sem_name	Seminar-name
 * @param	$user_id		User-Id
 */
function temporaly_accepted($sem_name, $user_id, $sem_id, $ask = "TRUE", $studiengang_id, $url) {
 	global $pass, $hashpass;
	$db = new DB_Seminar;

	if ($ask == "TRUE") {
		$db->query("SELECT admission_prelim_txt FROM seminare WHERE Seminar_id = '$sem_id'");
		$db->next_record();
		echo "<tr><td class=\"blank\">&nbsp;&nbsp;</td><td class=\"blank\">";
		printf (_("Um endg&uuml;ltig in die Veranstaltung <b>%s</b> aufgenommen zu werden, m&uuml;ssen Sie noch weitere Voraussetzungen erf&uuml;llen."),htmlReady($sem_name));
		if ($db->f("admission_prelim_txt")) {
			print " "._("Lesen Sie bitte folgenden Hinweistext:")."<br />";
			echo "<br/><table width=90%><tr><td>\n";
			echo formatReady($db->f("admission_prelim_txt"));
			echo "</td></tr></table><br/>\n";
		} else {
			print " "._("Bitte erkundigen Sie sich bei dem Dozenten oder der Dozentin der Veranstaltung nach weiteren Teilnahmevoraussetzungen.");
		}
		printf (_("Wenn Sie auf \"eintragen\" klicken, werden Sie vorl&auml;ufig f&uuml;r diese Veranstaltung eingetragen. Erf&uuml;llen Sie die Anforderungen, um von der DozentIn fest in die Veranstaltung <b>%s</b> eingetragen zu werden."), htmlReady($sem_name));
		echo "<br/><br/>\n";

		printf("<form action=\"%s\" method=\"post\">\n",$url);
		printf("<input type=\"hidden\" name=\"pass\" value=\"$pass\">");
		printf("<input type=\"hidden\" name=\"hashpass\" value=\"$hashpass\">");
		printf("<input %s %s type=\"image\" border=\"0\" style=\"vertical-align:middle;\">\n", makeButton("eintragen","src"),tooltip(_("In diese Veranstaltung eintragen")));
		print("<input type=\"hidden\" name=\"ask\" value=\"FALSE\">\n");
		printf ("<input type=\"HIDDEN\" name=\"sem_verify_suggest_studg\" value=\"%s\">\n", $studiengang_id);
		printf("<a href=\"details.php?sem_id=%s\"><img %s %s type=\"image\" border=\"0\" style=\"vertical-align:middle;\"></a>\n",$sem_id,makeButton("abbrechen","src"),tooltip(_("Nicht in diese Veranstaltung eintragen")));
		print("</form>");
		print("</td></tr><tr><td class=\"blank\" colspan=2>&nbsp;</td></tr></table>");
		page_close();
		die;

	} else {
		$db->query("INSERT INTO admission_seminar_user SET user_id = '$user_id', seminar_id = '$sem_id', studiengang_id = '$studiengang_id', status = 'accepted', mkdate = '".time()."', position = NULL");
		parse_msg (sprintf("msg�"._("Sie wurden mit dem Status <b>vorl&auml;ufig akzeptiert</b> in die Veranstaltung <b>%s</b> eingetragen. Damit haben Sie einen Platz sicher. F&uuml;r weitere Informationen lesen Sie den Abschnitt 'Anmeldeverfahren' in der &Uuml;bersichtsseite zu dieser Veranstaltung."),htmlReady($sem_name)));
		echo "<tr><td class=\"blank\" colspan=2>";
	}
}

/**
* This function checks, if a given seminar has the admission: temporarily accepted
*
* @param		string	seminar_id
* @param		string	user_id
* @return		boolean
*
*/
function seminar_preliminary($seminar_id,$user_id=NULL) {
	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	$db->query("SELECT Name,admission_prelim FROM seminare WHERE Seminar_id='$seminar_id'");
	$db->next_record();
	if ($db->f("admission_prelim") == 1) {
		if ($user_id) {
			$db2->query("SELECT user_id FROM admission_seminar_user WHERE user_id='$user_id' AND seminar_id='$seminar_id'");
			if ($db2->next_record()) {
				echo "<tr><td class=\"blank\" colspan=2>";
				parse_msg (sprintf("msg�"._("Sie sind f�r die Veranstaltung **%s** bereits vorl�ufig eingetragen!"),htmlReady($db->f("Name"))));
				echo "</td></tr>";
				page_close();
				die;
			}
		}
		return TRUE;
	} else {
		return FALSE;
	}
}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");
?>
<script type="text/javascript" language="javascript" src="md5.js"></script>
<script type="text/javascript" language="javascript">
  <!--
  function verifySeminar() {
      document.details.hashpass.value = MD5(document.details.pass.value);
      document.details.pass.value = "";
  }
  // -->
</script>

<?php
require_once "msg.inc.php";
require_once "functions.php";
require_once "admission.inc.php";

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$db4=new DB_Seminar;
$db5=new DB_Seminar;
$db6=new DB_Seminar;

?>
<body>

	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr><td class="topic" colspan=2>&nbsp;<b><?=_("Veranstaltungsfreischaltung")?></b></td></tr>
	<tr><td class="blank" colspan=2>&nbsp;<br></td></tr>
<?

	// temporaly accepted, if $ask is not set, then it we assume, that it must be true

	if (!isset($ask)) $ask = "TRUE";
	$temp_url = $sess->self_url();

	// admins und roots haben hier nix verloren
	if ($perm->have_perm("admin")) {
	    parse_msg ("info�"._("Sie sind einE <b>AdministratorIn</b> und k&ouml;nnen sich daher nicht f&uuml;r einzelne Veranstaltungen anmelden."));
	    echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
	    if ($send_from_search)
	    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
	    echo "<br><br></td></tr></table>";
	    page_close();
	    die;
	    }

	 //Gruppe auswaehlen, falls wir den User eintragen
	if ($SemIDtemp)
		$t_id=$SemIDtemp;
	else
		$t_id=$id;
	$db->query("SELECT start_time FROM seminare WHERE Seminar_id = '$t_id'");
	$db->next_record();
	$group = select_group ($db->f("start_time"), $user->id);

	 //check stuff for admission
	 check_admission();

	 if ($sem_verify_selection_send && !$sem_verify_suggest_studg)
	 	parse_msg ("error�"._("Bitte w&auml;hlen Sie einen Studiengang zur Anmeldung f&uuml;r diese Veranstaltung aus!"));

	//check if entry is allowed

	// Check the start and end-times of the current seminar and print an adequate message
	$db2->query("SELECT admission_starttime, admission_endtime_sem FROM seminare WHERE Seminar_id = '$t_id'");
	$db2->next_record();
	if ($db2->f("admission_starttime") > time()) {
		echo"<tr><td class=\"blank\">&nbsp;&nbsp;&nbsp;&nbsp;</td><td class=\"blank\">";
		echo "<font color=\"#FF0000\">";
		printf(_("Der Anmeldezeitraum dieser Veranstaltung startet erst am %s um %s Uhr."),date("d.m.Y",$db2->f("admission_starttime")), date("G:i",$db2->f("admission_starttime")));;
		echo "</font>";
		echo "<br /><br /></td></tr></table>";
		page_close();
		die;
	}
	if (($db2->f("admission_endtime_sem") < time()) && ($db2->f("admission_endtime_sem") != -1)) {
		echo"<tr><td class=\"blank\">&nbsp;&nbsp;&nbsp;&nbsp;</td><td class=\"blank\">";
		echo "<font color=\"#FF0000\">";
		printf(_("Der Anmeldezeitraum dieser Veranstaltung endete am %s um %s Uhr."),date("d.m.Y",$db2->f("admission_endtime_sem")), date("G:i",$db2->f("admission_endtime_sem")));;
		echo "</font>";
		echo "<br /><br /></td></tr></table>";
		page_close();
		die;
	}

	//check if seminar is grouped
	$db6->query("SELECT studiengang_id FROM user_studiengang WHERE user_id = '$user->id' "); //Hat der Studie ueberhaupt Studiengaenge angegeben?
	if ($db6->num_rows()) { // yes, he did.
		$db6->query("SELECT Seminar_id,admission_group FROM seminare WHERE Seminar_id='$t_id'");
		$db6->next_record();
		$admission_group = $db6->f("admission_group");
		$db6->free();
		if ($admission_group) {
			//get some infos about the current status of the seminar (admission-list, user-list, seminar-name)
			$db5->query("SELECT * FROM seminar_user, seminare WHERE seminare.admission_group='$admission_group' AND seminare.Seminar_id = seminar_user.Seminar_id AND seminar_user.user_id='$user->id'");
			$db6->query("SELECT * FROM admission_seminar_user, seminare WHERE seminare.admission_group='$admission_group' AND seminare.Seminar_id = admission_seminar_user.seminar_id AND admission_seminar_user.user_id='$user->id'");
			$db4->query("SELECT Name FROM seminare WHERE Seminar_id='$t_id'");
			$sem_id = $t_id;
			$db4->next_record();
			$current_name = $db4->f("Name");
			$db4->free();
			if ($db5->next_record()) {
				$seminar = 1;
				$seminar_name = $db5->f("Name");
				$seminar_id = $db5->f("Seminar_id");
			}
			if ($db6->next_record()) {
				$warteliste = 1;
				$warteliste_name = $db6->f("Name");
				$warte_id = $db6->f("seminar_id");
			}
			$db5->free();
			$db6->free();
			$db6->query("SELECT count(*) as anzahl FROM seminar_user WHERE Seminar_id = '$sem_id'");
			$db6->next_record();
			if (get_free_admission($t_id)) $platz = 1;
			$db6->free();

			/* now we know the following:
			 *  - Is the user already subscribed to another seminar? ($seminar)
			 *  - Is the user already awaiting in another seminar?   ($warteliste)
			 *  - Is there a place left in the seminar, the user wants to subsribe to? ($platz)
			 *
			 * The only thing that we have to do now, is to check for all possible constellations.
			 */

			$as_info = 1;	// Should the message be printed as an info via parse_message? (1 = yes, 0 = no)

			if ($as_info)
				echo"<tr><td class=\"blank\">&nbsp;&nbsp;&nbsp;&nbsp;</td><td class=\"blank\">";
			else
				echo"<tr>";

			if (!$seminar && !$warteliste && $platz) {
				$meldung  = sprintf(_("Sie bekommen einen Platz in der Veranstaltung %s."), "<br/>&nbsp;<b>$current_name (". htmlReady(view_turnus($sem_id)) .")</b>");
				$meldung .= _("<p>Falls dies nicht ihre pr�ferierte Veranstaltung dieser Gruppe ist, tragen Sie sich bitte ebenfalls f�r ihre pr�ferierte Veranstaltung ein.</p>");
				$meldung .= _("<p>Wenn Sie dort �ber die Warteliste nachr�cken, wird ihre Eintragung in dieser Veranstaltung automatisch gel�scht.</p>");
				if ($as_info) {
					parse_msg("info�".$meldung, "�", "blank",3);
				} else {
					print $meldung;
				}
			}

			if (!$seminar && !$warteliste && !$platz) {
				$meldung  = "<font color=\"#FF0000\">".  sprintf(_("Sie bekommen im Moment keinen Platz in der Veranstaltung %s."), "<br/>&nbsp;<b>$current_name (". htmlReady(view_turnus($sem_id)) .")</b><br/>");
				$meldung .= _("Sie wurden jedoch auf die Warteliste gesetzt.")."</font>";
				$meldung .= _("<p>Um sicher zu gehen, dass Sie einen Platz in einer Veranstaltung dieser Gruppe bekommen, sollten Sie sich zus�tzlich in einer weiteren Veranstaltung fest eintragen.</p>");
				$meldung .= _("<p>Sobald Sie in dieser Veranstaltung von der Warteliste aufr�cken, wird ihre dortige Eintragung automatisch gel�scht.");
				if ($as_info) {
					parse_msg("info&".$meldung, "�", "blank",3);
				} else {
					print $meldung;
				}
			}

			if ($seminar && !$warteliste && $platz) {
				$meldung  = sprintf(_("In dieser Veranstaltung sind noch Pl�tze frei. Sie haben jedoch bereits einen Platz in der Veranstaltung %s."), "<br>&nbsp;<b>$seminar_name (".htmlReady(view_turnus($seminar_id)).")</b><br/>");
				$meldung .= sprintf(_("Um sich f�r die Veranstaltung %s fest anzumelden, l�schen Sie bitte erst Ihre dortige Eintragung."), "<br>&nbsp;<b>$current_name (".htmlReady(view_turnus($sem_id)). ")</b><br>");
				if ($as_info) {
					parse_msg("info�$meldung", "�", "blank",3);
					echo "</td></tr></table>";
				} else {
					print $meldung;
					echo "<br/><br/></td></tr></table>";
				}

				page_close();
				die;
			}

			if ($seminar && !$warteliste && !$platz) {
				$meldung  = "<font color=\"#FF0000\">";
				$meldung .= sprintf(_("Sie sind bereits in der Veranstaltung %s in dieser Gruppe eingetragen."), "<br/>&nbsp;<b>$seminar_name (". htmlReady(view_turnus($seminar_id)) .")</b><br/>");
				$meldung .= "<p>" . sprintf(_("Sie wurden f�r die Veranstaltung %s auf die Warteliste gesetzt."), "<br/>&nbsp;<b>$current_name (". htmlReady(view_turnus($sem_id)) .")</b><br/>") . "</p></font>";
				$meldung .= _("Sobald Sie hier nachr�cken, wird ihre andere Anmeldung automatisch gel�scht.");
				if ($as_info) {
					parse_msg("info�".$meldung, "�", "blank",3);
				} else {
					print $meldung;
				}
			}

			if (!$seminar && $warteliste && $platz) {
				$meldung  = sprintf(_("Sie stehen bereits f�r die Veranstaltung %s auf der Warteliste. Ihre Anmeldung f�r die Veranstaltung %s wird automatisch gel�scht, wenn Sie dort �ber die Warteliste aufr�cken."), "<br/>&nbsp;<b>$warteliste_name (". htmlReady(view_turnus($warte_id)) .")</b><br/>", "<br/>&nbsp;<b>$current_name (". htmlReady(view_turnus($sem_id)) .")</b><br/>");
				$meldung .= "<p>". sprintf(_("Wenn Sie sich hier fest eintragen m�chten, l�schen Sie bitte erst ihren Eintrag in der Warteliste f�r die Veranstaltung %s."), "<br/>&nbsp;<b>$warteliste_name (". htmlReady(view_turnus($warte_id)) .")</b><br/>") . "</p>";
				if ($as_info) {
					parse_msg("info�".$meldung, "�", "blank",3);
				} else {
					print $meldung;
					echo "<br/><br/></td></tr></table>";
				}

			}

			if (!$seminar && $warteliste && !$platz) {
				$meldung = sprintf(_("Sie stehen bereits f�r die Veranstaltung %s auf der Warteliste. Wenn Sie sich in die Warteliste der Veranstaltung %s eintragen m�chten, l�schen Sie bitte erst Ihre andere Eintragung."), "<br/>&nbsp;<b>$warteliste_name (". htmlReady(view_turnus($warte_id)) .")</b><br/>", "<br/>&nbsp;<b>$current_name (". htmlReady(view_turnus($sem_id)) .")</b><br/>");
				if ($as_info) {
					parse_msg("info�".$meldung, "�", "blank",3);
					echo "</td></tr></table>";

				} else {
					print $meldung;
					echo "<br/><br/></td></tr></table>";
				}

				page_close();
				die;
			}

			if ($seminar && $warteliste && $platz) {
				$meldung  = sprintf(_("Sie stehen bereits f�r die Veranstaltung %s auf der Warteliste und sind in die Veranstaltung %s eingetragen."), "<br/>&nbsp;<b>$warteliste_name (". htmlReady(view_turnus($warte_id)) .")</b><br/>", "<br/>&nbsp;<b>$seminar_name (". htmlReady(view_turnus($seminar_id)) .")</b><br/>");
				$meldung .= "<p>" . sprintf(_("Sie k�nnen sich hier erst eintragen, wenn sie ihr Abonnement der Veranstaltung %s l�schen."), "<br/>&nbsp;<b>$seminar_name (". htmlReady(view_turnus($seminar_id)) .")</b><br/>");
				if ($as_info) {
					parse_msg("info�" . $meldung, "�", "blank",3);
					echo "</td></tr></table>";
				} else {
					print $meldung;
					echo "<br/><br/></td></tr></table>";
				}

				page_close();
				die;
			}

			if ($seminar && $warteliste && !$platz) {
				$meldung  = sprintf(_("Sie stehen bereits f�r die Veranstaltung %s auf der Warteliste und sind in die Veranstaltung %s eingetragen."), "<br/>&nbsp;<b>$warteliste_name (". htmlReady(view_turnus($warte_id)) .")</b><br/>", "<br/>&nbsp;<b>$seminar_name (". htmlReady(view_turnus($seminar_id)) .")</b><br/>");
				$meldung .= "<p>". sprintf(_("Sie k�nnen sich hier erst eintragen, wenn sie sich von der Warteliste der Veranstaltung $s l�schen."), "<br/>&nbsp;<b>$seminar_name (". htmlReady(view_turnus($seminar_id)) .")</b><br/>");
				if ($as_info) {
					parse_msg("info�" . $meldung, "�", "blank",3);
					echo "</td></tr></table>";
				} else {
					print $meldung;
					echo "<br/><br/></td></tr></table>";
				}
				page_close();
				die;
			}
			if (!$as_info) echo "<br/><br/>";
			echo "</td></tr>";
		}
	}

	//nobody darf sogar durch (wird spaeter schon abgefangen)
	if ($perm->have_perm("user")) {

		//Sonderfall, Passwort fuer Schreiben nicht eingegeben, Lesen aber erlaubt
		if ($EntryMode == "read_only"){
			$db->query("SELECT Lesezugriff, Name FROM seminare WHERE Seminar_id LIKE '$id'");
			$db->next_record();
			if ($db->f("Lesezugriff") <= 1 && $perm->have_perm("autor")) {
				if (!seminar_preliminary($id,$user->id)) {  // we have to change behaviour, depending on preliminary
					$db->query("INSERT INTO seminar_user SET Seminar_id = '$id', user_id = '$user->id', status = 'user', gruppe = '$group', mkdate = '".time()."'");
					parse_msg (sprintf("msg�"._("Sie wurden mit dem Status <b>Leser</b> in die Veranstaltung <b>%s</b> eingetragen."), htmlReady($db->f("Name"))));
					echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
				} else {
					parse_msg (sprintf("msg�"._("Die Veranstaltung **%s** ist teilnahmebeschr�nkt. Sie k�nnen sich nicht als Leser eintragen lassen."),htmlReady($db->f("Name"))));
				}
				if ($send_from_search)
			    		echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
			}
	 		page_close();
			die;
		}

		//wenn eine Sessionvariable gesetzt ist, nehmen wir besser die
		if (!isset($id)) if (isset($SessSemName[1]))
			$id=$SessSemName[1];

		//laden von benoetigten Informationen
		$db=new DB_Seminar;
		$db->query("SELECT Lesezugriff, Schreibzugriff, Passwort, Name FROM seminare WHERE Seminar_id LIKE '$id'");
		while ($db->next_record()) {
			$SemSecLevelRead=$db->f("Lesezugriff");
			$SemSecLevelWrite=$db->f("Schreibzugriff");
			$SemSecPass=$db->f("Passwort");
			$SeminarName=htmlReady($db->f("Name"));
		}
		$db->query("SELECT status FROM seminar_user WHERE Seminar_id LIKE '$id' AND user_id LIKE '$user->id'");
		$db->next_record();
		$SemUserStatus=$db->f("status");

		//Ueberpruefung auf korrektes Passwort
		if ((isset($pass) && $pass!="" && (md5($pass)==$SemSecPass))  ||  (isset($hashpass) && $hashpass!="" && $hashpass==$SemSecPass)) {
			if (($SemUserStatus=="user") && ($perm->have_perm("autor"))){
				$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$user->id'");
				parse_msg (sprintf("msg�"._("Sie wurden in der Veranstaltung <b>%s</b> auf den Status <b> Autor </b> hochgestuft."), $SeminarName));
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
			    	if ($send_from_search)
				    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
			elseif ($perm->have_perm("autor")) {
				if (!seminar_preliminary($id,$user->id)) {
					$db->query("INSERT INTO seminar_user SET Seminar_id = '$id', user_id = '$user->id', status = 'autor', gruppe = '$group', mkdate = '".time()."'");
					parse_msg (sprintf("msg�"._("Sie wurden mit dem Status <b>Autor</b> in die Veranstaltung <b>%s</b> eingetragen."), $SeminarName));
					echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
					if ($send_from_search) echo "&nbsp; |";
				} else {
					temporaly_accepted($SeminarName, $user->id, $id, $ask, $sem_verify_suggest_studg, $temp_url);
				}
			  	if ($send_from_search)
				    	echo "&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
		}
 elseif ((isset($pass) && $pass!="") || (isset($hashpass) && $hashpass!="")) {
		    parse_msg ("error�Ung&uuml;ltiges Passwort eingegeben, bitte nocheinmal versuchen !");
	}

	//Die eigentliche Ueberpruefung verschiedener Rechtesachen
	//User schon in der Seminar_user vorhanden? Und was macht er da eigentlich?
		if ($SemUserStatus) {
			if ($SemUserStatus=="user") { //Nur user? Dann muessen wir noch mal puefen
				if ($SemSecLevelWrite==2) { //Schreiben nur per Passwort, der User darf es eingeben
					if ($perm->have_perm("autor")) { //nur globale Autoren duerfen sich hochstufen!
						printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Bitte geben Sie das Passwort f&uuml;r die Veranstaltung <b>%s</b> ein.") . "<br><br></td></tr>", $SeminarName);
						?>
						</td></tr>
						<tr><td class="blank" colspan=2>
						<form name="details" action="<? echo $sess->pself_url(); ?>" method="POST" onSubmit="verifySeminar();return true;">
						&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
						<input type="HIDDEN" name="id" value="<? echo $id;?>">
						<input type="HIDDEN" name="hashpass" value="">
						<input type="IMAGE" <?=makeButton("abschicken", "src")?> border="0" value="<?=_("abschicken") ?>">
						</form>
						</td></tr>
						<?
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
					    	if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br>";
						?>
						</td></tr></table>
						<?
					} else {
						parse_msg (sprintf("info�"._("Um in der Veranstaltung <b>%s</b> schreiben zu d&uuml;rfen, m&uuml;ssen Sie zumindest auf die Registrierungsmail reagiert haben!"), $SeminarName));
	   					echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
  						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br></td></tr></table>";
					}
					page_close();
					die;
				}
			  	elseif ($SemSecLevelWrite==1){//Hat sich der globale Status in der Zwischenzeit geaendert? Dann hochstufen
					if ($perm->have_perm("autor")) {
						$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$user->id'");
						parse_msg(sprintf("info�"._("Sie wurden in der Veranstaltung <b>%s</b> hochgestuft auf den Status <b>Autor</b>."), $SeminarName));
						echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br></td></tr></table>";
						page_close();
						die;
					} else {//wenn nicht, informieren
						parse_msg(sprintf("info�"._("Sie sind nur mit der Berechtigung <b>Leser</b> f&uuml;r die Veranstaltung <b>%s</b> freigeschaltet. Wenn Sie auf die Registrierungsmail antworten, k&ouml;nnen Sie in dieser Veranstaltung auch schreiben."), $SeminarName));
						echo"<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; <a href=\"seminar_main.php?auswahl=$id\">"._("Hier kommen Sie zu der Veranstaltung")."</a>";
						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br></td></tr></table>";
						page_close();
						die;
					}
				}
			} else { //User ist schon Autor oder hoeher, soll den Quatsch mal lassen und weiter ins Seminar
				parse_msg(sprintf("info�"._("Sie sind schon mit der Berechtigung <b>%s</b> f&uuml;r die Veranstaltung <b>%s</b> freigeschaltet."), $SemUserStatus, $SeminarName));
				echo"<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; <a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
				if ($send_from_search)
					echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
		} else {//User ist noch nicht eingetragen in seminar_user
			if ($perm->have_perm("autor")) { //User ist global 'Autor'also normaler User
				if (($SemSecLevelWrite==3) && ($SemSecLevelRead==3)) {//Teilnehmerbeschraenkte Veranstaltung, naehere Uberpruefungen erforderlich
					if ($auth->auth["perm"]=="dozent") { //Dozenten duerfen sich nicht fuer Anmeldebeschraenkte Veranstaltungen anmelden
						parse_msg (sprintf("info�"._("Sie d&uuml;rfen sich mit dem Status Dozent nicht f&uuml;r die teilnahmebeschr&auml;nkte Veranstaltung <b>%s</b> anmelden.<br />Wenn Sie dennoch eingetragen werden m&ouml;chten, wenden Sie sich bitte direkt an die Dozentin oder den Dozenten der Veranstaltung."), $SeminarName));
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
						if ($send_from_search)
							echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br	><br></td></tr></table>";
						page_close();
						die;
						}
					$db->query("SELECT studiengang_id FROM user_studiengang WHERE user_id = '$user->id' "); //Hat der Studie ueberhaupt Studiengaenge angegeben?
					if (!$db->num_rows()) { //Es sind gar keine vorhanden! Hinweis wie man das eintragen kann
						parse_msg (sprintf("info�"._("Die Veranstaltung <b>%s</b> ist teilnahmebeschr&auml;nkt. Um sich f&uuml;r teilnahmebeschr&auml;nkte Veranstaltungen eintragen zu k&ouml;nnen, m&uuml;ssen Sie einmalig Ihre Studieng&auml;nge angeben! <br> Bitte tragen Sie ihre Studieng&auml;nge auf ihrer <a href=\"edit_about.php?view=Karriere#studiengaenge\">pers&ouml;nlichen Homepage</a> ein!"), $SeminarName));
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
						if ($send_from_search)
							echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br	><br></td></tr></table>";
						page_close();
						die;
						}
					//Wurden wir evtl. schon in die Veranstaltung als Wartender eingetragen?
					$db->query("SELECT user_id FROM admission_seminar_user WHERE user_id = '$user->id' AND seminar_id = '$id' "); //Bin ich eingetragen?
					if ($db->num_rows()) { //Es gibt einen Eintrag, da darf ich also nicht mehr rein
						parse_msg (sprintf("info�"._("Sie stehen schon auf der Anmelde- bzw. Warteliste der Veranstaltung <b>%s</b>. <br />Wenn Sie sich neu oder f&uuml;r ein anderes Kontingent eintragen wollen, dann l&ouml;schen Sie bitte vorher die Zuordnug auf der der &Uuml;bersicht ihrer Veranstaltungen."), $SeminarName));
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
						if ($send_from_search)
							echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br	><br></td></tr></table>";
						page_close();
						die;
						}
					//Ok, es gibt also Studiengaenge und wir stehen noch nicht in der admission_seminar_user
					$db2->query("SELECT admission_endtime, admission_turnout, admission_type, admission_selection_take_place FROM seminare WHERE Seminar_id LIKE '$id'"); //Wir brauchen in diesem Fall mehr Daten
					$db2->next_record();
					if (!$sem_verify_suggest_studg) {//Wir wissen noch nicht mit welchem Studiengang der User rein will
						$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id) LEFT JOIN user_studiengang USING (studiengang_id) WHERE seminar_id LIKE '$id' AND (user_id = '$user->id' OR admission_seminar_studiengang.studiengang_id = 'all')"); //Hat der Studi passende Studiengaenge ausgewaehlt?
						if ($db->num_rows() == 1) {//Nur einen passenden gefunden? Dann nehmen wir einfach mal diesen...
							$db->next_record();
							$sem_verify_suggest_studg=$db->f("studiengang_id");
						} elseif ($db->num_rows() >1) { //Mehrere gefunden, fragen welcher es denn sein soll
							printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Die Veranstaltung <b>%s</b> ist teilnahmebeschr&auml;nkt.")."<br><br></td></tr>", $SeminarName);
							print "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Sie k&ouml;nnen sich f&uuml;r <b>eines</b> der m&ouml;glichen Kontingente anmelden.")."<br/><br />&nbsp; &nbsp; "._("Bitte w&auml;hlen Sie das f&uuml;r Sie am besten geeignete Kontingent aus:")." <br><br></td></tr>";
							$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id = '$id' ORDER BY name"); //Alle theoretisch moeglichen auswaehlen
							?>
							<tr><td class="blank" colspan=2>
							<form action="<? echo $sess->pself_url(); ?>" method="POST" >
								<input type="HIDDEN" name="sem_verify_selection_send" value="TRUE" />
							       <?
								while ($db->next_record()) {
									$db3->query("SELECT studiengang_id FROM user_studiengang WHERE studiengang_id = '".$db->f("studiengang_id")."' AND user_id = '$user->id' "); // Darf ich diesen auswaehlen?
									$db3->next_record();
									if ($db3->f("studiengang_id") == "all")
										$tmp_sem_verify_quota=get_all_quota($id);
									else
										$tmp_sem_verify_quota=round ($db2->f("admission_turnout") * ($db->f("quota") / 100));
									if (($db3->num_rows()) || ($db->f("studiengang_id") == "all"))
										printf ("&nbsp; &nbsp; <input type=\"RADIO\" name=\"sem_verify_suggest_studg\" value=\"%s\">&nbsp; <font size=-1><b>"._("Kontingent f&uuml;r %s (%s Pl&auml;tze)")."</b></font><br />", $db->f("studiengang_id"), ($db->f("studiengang_id") == "all") ? _("alle Studieng&auml;nge") : $db->f("name"), $tmp_sem_verify_quota);
									else
										printf ("&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<font size=-1 color=\"#888888\">"._("Kontingent f&uuml;r %s (%s Pl&auml;tze)")."</font><br />", ($db->f("studiengang_id") == "all") ? _("alle Studieng&auml;nge") : $db->f("name"), $tmp_sem_verify_quota);
									}
							       ?>
							<br />&nbsp; &nbsp; <input type="IMAGE" <?=makeButton("ok", "src")?> border=0 value="abschicken">
							</form>
							</td></tr>
							<?
							echo "<tr><td class=\"blank\" colspan=\"2\">";
							if ($db2->f("admission_type") == 1) {
								if ($db2->f("admission_selection_take_place"))
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgte nach dem Losverfahren am %s Uhr.")." "._("Weitere Pl&auml;tze k&ouml;nnen evtl. &uuml;ber die Warteliste vergeben werden.")." <br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ", date("d.m.Y, G:i", $db2->f("admission_endtime")));
								else
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgt nach dem Losverfahren am %s Uhr.")." <br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ", date("d.m.Y, G:i", $db2->f("admission_endtime")));
							} else {
								if ($db2->f("admission_selection_take_place"))
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgte in der Reihenfolge der Anmeldung.")." "._(" Weitere Pl&auml;tze k&ouml;nnen evtl. &uuml;ber die Warteliste vergeben werden.")."<br />&nbsp; &nbsp;"._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ");
								else
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgt in der Reihenfolge der Anmeldung.")."<br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ");
							}
							echo "</td></tr>";
							echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
						    	if ($send_from_search)
					    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
							echo "<br><br>";
							?>
							</td></tr></table>
							<?
							page_close();
							die;
						} else { //Keinen passenden Studiengaenge gefunden, abbruch
							$db->query("SELECT studiengang_id FROM user_studiengang WHERE user_id = '$user->id' "); //Hat der Studie ueberhaupt Studiengaenge angegeben?
							if ($db->num_rows() >=1) { //Es waren nur die falschen
								parse_msg (sprintf("info�"._("Sie belegen leider keinen passenden Studiengang, um an der teilnahmebeschr&auml;nkten Veranstaltung <b>%s</b> teilnehmen zu k&ouml;nnen."), $SeminarName));
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							}
						}
					}
					if ($sem_verify_suggest_studg) { //User hat einen Studiengang angegeben oeder wir haben genau einen passenden gefunden, mit dem er jetzt rein will/kann
						if ($db2->f("admission_selection_take_place") == 1) { //Variante Eintragen nach Lostermin oder Enddatum der Kontigentierrung. Wenn noch Platz ist fuellen wir einfach auf, ansonsten Warteliste
							if (get_free_admission($id)) { //Wir koennen einfach eintragen, Platz ist noch
								if (!seminar_preliminary($id,$user->id)) {
								 	$db4->query("INSERT INTO seminar_user SET user_id = '$user->id', Seminar_id = '$id', admission_studiengang_id = '$sem_verify_suggest_studg', status='autor', gruppe='$group', mkdate='".time()."' ");
									parse_msg (sprintf("msg�"._("Sie wurden mit dem Status <b>Autor</b> in die Veranstaltung <b>%s</b> eingetragen. Damit sind Sie zugelassen."), $SeminarName));
									echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
									if ($send_from_search) echo "&nbsp; |";
								} else {
									temporaly_accepted($SeminarName, $user->id, $id, $ask, $sem_verify_suggest_studg, $temp_url);
								}
								if ($send_from_search)
					    				echo "&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							} else { //Auf die Warteliste
								$db5->query("SELECT position FROM admission_seminar_user WHERE seminar_id= '$id' AND status != 'accepted' ORDER BY position DESC");//letzte hoechste Position herausfinden
								$db5->next_record();
								$position=$db5->f("position")+1;
							 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='awaiting', mkdate='".time()."', position='".$position."'  ");
								parse_msg (sprintf("info�"._("Es gibt zur Zeit keinen freien Platz in der teilnahmebeschr&auml;nkten Veranstaltung <b>%s</b>. Sie wurden jedoch auf Platz %s der Warteliste gesetzt.")." <br /> "._("Sie werden automatisch eingetragen, sobald ein Platz f&uuml;r Sie frei wird."), $SeminarName, $position));
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
								if ($send_from_search)
					    				echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							}
						} else { //noch nicht gelost oder Enddatum, also Kontingentierung noch aktiv
							$db3->query("SELECT name, quota, admission_seminar_studiengang.studiengang_id FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id LIKE '$id' AND admission_seminar_studiengang.studiengang_id = '$sem_verify_suggest_studg' "); //Nochmal die Daten des quotas fuer diese Veranstaltung
							$db3->next_record();
							if ($db2->f("admission_type") == 1) { //Variante Losverfahren
								$db5->query("SELECT position FROM admission_seminar_user ORDER BY position DESC");//letzte hoechste Position herausfinden
								$db5->next_record();
							 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='claiming', mkdate='".time()."', position='' ");
								parse_msg (sprintf("info�"._("Sie wurden auf die Anmeldeliste der Veranstaltung <b>%s</b> gesetzt.")." <br />"._("Teilnehmer der Veranstaltung <b>%s</b> werden Sie, falls Sie im Losverfahren am %s Uhr ausgelost werden. Sollten Sie nicht ausgelost werden, werden Sie auf die Warteliste gesetzt und werden vom System automatisch als Teilnehmer eingetragen, sobald ein Platz f&uuml;r Sie frei wird."), $SeminarName, $SeminarName, date("d.m.Y, G:i", $db2->f("admission_endtime"))));
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							} else { //Variante chronologisches Anmelden
								$db->query("SELECT user_id FROM seminar_user WHERE Seminar_id = '$id' AND admission_studiengang_id = '$sem_verify_suggest_studg'"); //Wieviel user sind schon in diesem Kontingent eingetragen
								$db4->query("SELECT user_id FROM admission_seminar_user WHERE seminar_id = '$id' AND studiengang_id = '$sem_verify_suggest_studg' AND status = 'accepted'"); //the same for temporarily accepted
								if ($db3->f("studiengang_id") == "all")
									$tmp_sem_verify_quota=get_all_quota($id);
								else
									$tmp_sem_verify_quota=round ($db2->f("admission_turnout") * ($db3->f("quota") / 100));
								if (($db->num_rows() + $db4->num_rows()) < $tmp_sem_verify_quota) {//noch Platz in dem Kontingent --> direkt in seminar_user
								 	if (!seminar_preliminary($id,$user->id)) {
										$db4->query("INSERT INTO seminar_user SET user_id = '$user->id', Seminar_id = '$id', status='autor', gruppe='$group', admission_studiengang_id = '$sem_verify_suggest_studg', mkdate='".time()."' ");
										parse_msg (sprintf("msg�"._("Sie wurden mit dem Status <b>Autor</b> in die Veranstaltung <b>%s</b> eingetragen. Damit sind Sie zugelassen."), $SeminarName));
										echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
										if ($send_from_search) echo "&nbsp; |";
									} else {
										temporaly_accepted($SeminarName, $user->id, $id, $ask, $sem_verify_suggest_studg, $temp_url);
									}
									if ($send_from_search)
									    	echo "&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
									echo "<br><br></td></tr></table>";
									page_close();
									die;
								} else { //kein Platz mehr im Kontingent --> auf Warteposition in admission_seminar_user
									$db5->query("SELECT position FROM admission_seminar_user WHERE seminar_id= '$id' AND status != 'accepted' ORDER BY position DESC");//letzte hoechste Position herausfinden
									$db5->next_record();
									$position = $db5->f("position")+1;
								 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='awaiting', mkdate='".time()."', position='".$position."'  ");
									parse_msg (sprintf("info�"._("Es gibt zur Zeit keinen freien Platz in der teilnahmebeschr&auml;nkten Veranstaltung <b>%s</b>. Sie wurden jedoch auf Platz %s der Warteliste gesetzt.")." <br /> "._("Sie werden automatisch eingetragen, sobald ein Platz f&uuml;r Sie frei wird."), $SeminarName, $position));
									echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
									if ($send_from_search)
						    				echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
									echo "<br	><br></td></tr></table>";
									page_close();
									die;
								}
							}
						}
					}
				}
				elseif (($SemSecLevelWrite==2) && ($SemSecLevelRead==2)) {//Paswort auf jeden Fall erforderlich, also her damit
					printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp;"._("Bitte geben Sie das Passwort f&uuml;r die Veranstaltung <b>%s</b> ein.")."<br><br></td></tr>", $SeminarName);
					?>
					</td></tr>
					<tr><td class="blank" colspan=2>
					<form name="details" action="<? echo $sess->pself_url(); ?>" method="POST" onSubmit="verifySeminar();return true;">
					&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
					<input type="HIDDEN" name="id" value="<? echo $id;?>">
					<input type="HIDDEN" name="hashpass" value="">
					<input type="IMAGE" <?=makeButton("abschicken", "src")?> border="0" value="<?=_("abschicken") ?>">
					</form>
					</td></tr>
					<?
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
				    	if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
					echo "<br><br>";
					?>
					</td></tr></table>
					<?
					page_close();
					die;
				}
				elseif ($SemSecLevelWrite==2) {//nur passwort fuer Schreiben, User koennte ohne Passwort als 'User' in das Seminar
					print "<form name=\"details\" action=\"".$sess->self_url()."\" method=\"POST\" onSubmit=\"verifySeminar();return true;\">";
					print "<tr><td class=\"blank\" colspan=\"2\">";
					print "<table width=\"97%\" align=\"center\" border=\"0\" cellapdding=\"2\" cellspacing=\"0\">";
					print "<tr><td width=\"48%\" class=\"blank\">";
					print _("Wenn Sie mit Lese- und Schreibberechtigung an der Veranstaltung teilnehmen wollen, geben Sie hier bitte das Passwort f&uuml;r diese Veranstaltung ein:");
					print "</td><td width=\"4%\" class=\"blank\">&nbsp;";
					print "</td><td width=\"48%\" class=\"blank\" >";
					print _("Sie k&ouml;nnen auch ohne Eingabe eines Passwortes an der Veranstaltung teilnehmen. Sie haben in diesem Fall jedoch nur Leseberechtigung.");
					print "</td></tr>";	
					print "<tr><td width=\"48%\" class=\"blank\" valign=\"top\">";
					print "<br /><input type=\"RADIO\" name=\"EntryMode\" checked value=\"pass\">&nbsp;"._("Ich kenne das Passwort dieser Veranstaltung");
					print "</td><td width=\"4%\" class=\"blank\">&nbsp;";
					print "</td><td width=\"48%\" class=\"blank\" valign=\"top\">";
					print "<br /><input type=\"RADIO\" name=\"EntryMode\" value=\"read_only\">&nbsp;"._("Ich m&ouml;chte an der Veranstaltung nur mit Leseberechtigung teilnehmen.");
					print "<br />&nbsp;</td></tr>";	
					?>
					<tr><td class="blank">
					<font size="-1"><?=_("Bitte geben Sie hier das Passwort ein:")?></font><br />
					<input type="PASSWORD" name="pass" size="20">
					<input type="HIDDEN" name="id" value="<? echo $id;?>">
					<input type="HIDDEN" name="hashpass" value="">
					</td>
					<td class="blank">&nbsp;</td>
					<td class="blank" valign="top">
						<font size="-1">
							<?=_("(Sie k&ouml;nnen das Passwort sp&auml;ter unter &raquo;Details&laquo; innerhalb der Veranstaltung eingeben.)") ?>
							</font>
						</td>
					</tr>
					<tr><td class="blank" colspan="3" align="center">
					<input type="IMAGE" <?=makeButton("ok", "src")?> border="0" value="<?=_("abschicken") ?>"><br />&nbsp;
					</td></tr></table>
					</form>
					<?
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
				    	if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
					echo "<br><br>";
					?>
					</td></tr></table>
					<?
					page_close();
					die;
				} else {//kein Passwortschutz, also wird der Kerl auf jeden Fall autor im Seminar
					$InsertStatus="autor";
				}
			} else {//der User ist auch global 'User'
				if ($SemSecLevelRead>0) {//Lesen duerfen nur Autoren, also wech hier
					parse_msg (sprintf("info�"._("Um an der Veranstaltung <b>%s</b> teilnehmen zu k&ouml;nnen, m&uuml;ssen Sie zumindest auf die Registrierungsmail geantwortet haben!"), $SeminarName));
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
					if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
					echo "<br><br></td></tr></table>";
					page_close();
					die;
				} else {//Lesen mit Berechtigung 'User' geht
					if ($SemSecLevelWrite==0) {//Wenn Schreiben auch mit Berechtigung 'user' geht, darf es sogar als 'autor' rein (auch wenn es gegen das Grundprizip verstoesst (keine hoeheren Rechte als globale Rechte). Das geht nur, wenn in der config.inc Nobody write=TRUE fuer Veranstaltungsklasse ist
						$InsertStatus="autor";
					} else { //sonst bleibt es bei 'user'
						$InsertStatus="user";
					}
				}
			}
		}

		if (isset($InsertStatus)) {//Status reinschreiben
			if (!seminar_preliminary($id,$user->id)) {
				$db->query("INSERT INTO seminar_user SET seminar_id = '$id', user_id = '$user->id', status = '$InsertStatus', gruppe = '$group', mkdate = '".time()."'");
				parse_msg (sprintf("msg�"._("Sie wurden mit dem Status <b>%s</b> in die Veranstaltung <b>%s</b> eingetragen."), $InsertStatus, $SeminarName));
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
				if ($send_from_search) echo "&nbsp; |";
			} else {
				temporaly_accepted($SeminarName, $user->id, $id, $ask, $sem_verify_suggest_studg, $temp_url);
			}
			if ($send_from_search)
			    	echo "&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
			echo "<br><br></td></tr></table>";
			page_close();
			die;
		}
	}

  if ($SemSecLevelRead==0) {//nur wenn das Seminar wirklich frei ist geht's hier weiter
	printf("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Die Veranstaltung <b>%s</b> erfordert keine Anmeldung. %sHier kommen Sie zu der Veranstaltung</a>!")."<br><br></td></tr></table>", $SeminarName, "<a href=\"seminar_main.php?auswahl=$id\">");
  }	else {//keine Rechte f&uuml;r das Seminar
		parse_msg (sprintf("error�"._("Sie haben nicht die erforderlichen Rechte, um an der Veranstaltung <b>%s</b> teilnehmen zu d&uuml;rfen!"), $SeminarName));
		echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("Zur&uuml;ck zur Startseite")."</a>";
		if ($send_from_search)
	    		echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("Zur&uuml;ck zur letzten Auswahl")."</a>";
		echo "<br><br></td></tr></table>";
	}
	page_close();
?>
</body>
</html>

