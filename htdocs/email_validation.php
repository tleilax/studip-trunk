<?php
/*
email_validation.php - Hochstufung eines user auf Status autor, wenn erfolgreich per Mail zurueckgemeldet
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>

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
$auth->login_if($auth->auth["uid"] == "nobody");
$perm->check("user");
// nobody hat hier nix zu suchen...

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

$magic     = "dsdfjhgretha";  // Challenge seed.
// MUSS IDENTISCH ZU DEM IN SEMINAR_REGISTER_AUTH IN LOCAL.INC SEIN!

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php"); 

?>
<br>

<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><b>&nbsp;<?=_("Best&auml;tigung der Email-Adresse")?></b></td>
</tr>
<tr><td class="blank" colspan=2 width="100%">&nbsp;</td></tr>

<?
	if ($perm->have_perm("autor")) {
		my_error(sprintf(_("Sie haben schon den Status <b>%s</b> im System. Eine Aktivierung des Accounts ist nicht mehr n&ouml;tig, um Schreibrechte zu bekommen"), $auth->auth["perm"]) . "\n");
		print "<tr><td class=\"blank\" colspan=2 width=\"100%\"><a href=\"index.php\">&nbsp;" . _("zur&uuml;ck zur Startseite") . "</a><br><br>\n";
		print "</td></tr></table>";
		page_close();
		die;
	}

//	So, wer bis hier hin gekommen ist gehoert zur Zielgruppe...

	if (!isset($secret) || $secret == "") {   // Volltrottel (oder abuse)
		my_error(_("Sie m&uuml;ssen den vollst&auml;ndigen Link aus der Best&auml;tigungsmail<br>in die Zeile <b>Location</b> oder <b>URL</b> Ihres Browsers kopieren.") . "\n");
		print "<tr><td class=\"blank\" colspan=2 width=\"100%\"><b>&nbsp;" . _("Versuchen Sie es noch einmal!") . "</b><br><br>\n";
		print "</td></tr></table>";
		page_close();
		die;
	}

	$hash = md5("$user->id:$magic");
	// hier wird noch mal berechnet, welches secret in der Bestaetigungsmail uebergeben wurde

	if ($secret != $hash) {   // abuse (oder Volltrottel)
		my_error(_("Der &uuml;bergebene <b>Secret-Code</b> ist nicht korrekt.") . "\n");
		my_info(_("Sie m&uuml;ssen unter dem Benutzernamen eingeloggt sein,<br>f&uuml;r den Sie die Best&auml;tigungsmail erhalten haben.") . "\n");
		my_info(_("Und Sie m&uuml;ssen den vollst&auml;ndigen Link aus der Best&auml;tigungsmail<br>in die Zeile <b>Location</b> oder <b>URL</b> Ihres Browsers kopieren.") . "\n");
		print "<tr><td class=\"blank\" colspan=2 width=\"100%\"><b>&nbsp;" . _("Versuchen Sie es noch einmal!") . "</b><br><br>\n";
		print "</td></tr></table>";
    // Mail an abuse
		$smtp=new studip_smtp_class;
		$REMOTE_ADDR=getenv("REMOTE_ADDR");
		$Zeit=date("H:i:s, d.m.Y",time());
		$from="wwwrun@".$smtp->localhost;
		$to="abuse@".$smtp->localhost;
		$username = $auth->auth["uname"];
		$smtp->SendMessage(
		$from, array($to),
		array("From: $from", "To: $to", "Subject: Validation"),
		"Secret falsch\n\nUser: $username\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
		page_close();
		die;
	}

	if ($secret == $hash) {   // alles paletti, Status �ndern
		$db = new DB_Seminar;
	   $query = "update auth_user_md5 set perms='autor' where user_id='$user->id'";
	   $db->query($query);
	   if ($db->affected_rows() == 0) {
	     my_error("<b>Changes failed:</b> $query");
	     break;
	   }

		my_msg(_("Ihr Status wurde erfolgreich auf <b>autor</b> gesetzt.<br>Damit d&uuml;rfen Sie in den meisten Veranstaltungen schreiben,<br>f&uuml;r die Sie sich anmelden.") . "\n");
		my_info(_("Einige Veranstaltungen erfordern allerdings bei der Anmeldung<br>die Eingabe eines Passwortes.<br>Dieses Passwort erfahren Sie von dem Dozenten der Veranstaltung.") . "\n");

		// Auto-Eintrag in Boards
		if (is_array($AUTO_INSERT_SEM)){
			foreach ($AUTO_INSERT_SEM as $a) {
				$db->query("SELECT Name, Schreibzugriff FROM seminare WHERE Seminar_id = '$a'");
				if ($db->num_rows()) {
					$db->next_record();
					if ($db->f("Schreibzugriff") < 2) { // es gibt das Seminar und es ist kein Passwort gesetzt
						$db2 = new DB_Seminar;
						$db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$a' AND user_id='$user->id'");
						if ($db2->num_rows()) { // Benutzer ist schon eingetragen
							$db2->next_record();
							if ($db2->f("status") == "user") { // wir k�nnen ihn hochstufen
								$db2->query("UPDATE seminar_user SET status = 'autor' WHERE Seminar_id = '$a' AND user_id='$user->id'");	
								my_msg(sprintf(_("Ihnen wurden Schreibrechte in der Veranstaltung <b>%s</b> erteilt."), $db->f("Name")) . "\n");
							}
						} else {  // Benutzer ist noch nicht eingetragen
							$db2->query("INSERT into seminar_user (Seminar_id, user_id, status, gruppe) values ('$a', '$user->id', 'autor', '0')");
							my_msg(sprintf(_("Sie wurden automatisch in die Veranstaltung <b>%s</b> eingetragen."), $db->f("Name")) . "\n");
						}
					}
				}
			}
		}
		$auth->logout();	// einen Logout durchf�hren, um erneuten Login zu erzwingen
		my_info(sprintf(_("Die Status-&Auml;nderung wird erst nach einem erneuten %sLogin%s wirksam!<br>Deshalb wurden Sie jetzt automatisch ausgeloggt."), "<a href=\"index.php?again=yes\"><b>", "</b></a>") . "\n");
		print "";
	} else {
		; // hier sollten wir nie hinkommen
	}

  page_close();
?>
</body>
</html>
<!-- $Id$ -->
