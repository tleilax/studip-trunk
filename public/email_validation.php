<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");
$perm->check("user");
// nobody hat hier nix zu suchen...

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once('config.inc.php');
require_once 'lib/functions.php';
require_once('lib/classes/UserManagement.class.php');

// -- here you have to put initialisations for the current page

$magic     = "dsdfjhgretha";  // Challenge seed.
// MUSS IDENTISCH ZU DEM IN SEMINAR_REGISTER_AUTH IN LOCAL.INC SEIN!

$hash = md5("$user->id:$magic");
// hier wird noch mal berechnet, welches secret in der Bestaetigungsmail uebergeben wurde

$HELP_KEYWORD="Basis.AnmeldungMail";
$CURRENT_PAGE= _("Aktivierung");

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

?>
<div class="topic"><b><?=_("Best�tigung der E-Mail-Adresse")?></b></div>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
    <tr>
        <td class="blank"><br>
<?php
	//user bereits vorhanden
    if ($perm->have_perm("autor")) {
	    echo Messagebox::error(sprintf(_("Sie haben schon den Status <b>%s</b> im System.
	    Eine Aktivierung des Accounts ist nicht mehr n&ouml;tig, um Schreibrechte zu bekommen"), $auth->auth["perm"]),
	    array("<a href=\"index.php\">&nbsp;" . _("zur&uuml;ck zur Startseite") . "</a>"));
	}

    //	So, wer bis hier hin gekommen ist gehoert zur Zielgruppe...
    // Volltrottel (oder abuse)
	elseif (!isset($secret) || $secret == "") {
		echo Messagebox::error(_("Sie m�ssen den vollst&�ndigen Link aus der Best�tigungsmail in die Adresszeile Ihres Browsers kopieren."));
	}

	// abuse (oder Volltrottel)
	elseif ($secret != $hash) {
	    echo Messagebox::error(_("Der �bergebene <em>Secret-Code</em> ist nicht korrekt."),
	    array(_("Sie m�ssen unter dem Benutzernamen eingeloggt sein, f�r den Sie die Best�tigungsmail erhalten haben."),
	    _("Und Sie m�ssen den vollst�ndigen Link aus der Best�tigungsmail in die Adresszeile Ihres Browsers kopieren.")));

        // Mail an abuse
		$smtp=new studip_smtp_class;
		$REMOTE_ADDR=getenv("REMOTE_ADDR");
		$Zeit=date("H:i:s, d.m.Y",time());
		$from="wwwrun@".$smtp->localhost;
		$to="abuse@".$smtp->localhost;
		$username = $auth->auth["uname"];
		$smtp->SendMessage(
				$to, "",
				$smtp->abuse, "",
				"Validation", "Secret falsch\n\nUser: $username\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
	}

	// alles paletti, Status �ndern
    elseif ($secret == $hash) {
        $db = new DB_Seminar;
        $query = "update auth_user_md5 set perms='autor' where user_id='$user->id'";
        $db->query($query);
        if ($db->affected_rows() == 0) {
            echo Messagebox::error(_("Fehler! Bitte wenden Sie sich an den Systemadministrator."), array($query));
        } else {
            echo Messagebox::success(_("Ihr Status wurde erfolgreich auf <em>autor</em> gesetzt.<br>
            Damit d�rfen Sie in den meisten Veranstaltungen schreiben,<br>f�r die Sie sich anmelden."),
            array(_("Einige Veranstaltungen erfordern allerdings bei der Anmeldung die Eingabe eines Passwortes.
            Dieses Passwort erfahren Sie von der Dozentin oder dem Dozenten der Veranstaltung.")));

            // Auto-Eintrag in Boards
            $UserManagement = new UserManagement($user->id);
            $UserManagement->autoInsertSem('user');

            $auth->logout();	// einen Logout durchf�hren, um erneuten Login zu erzwingen
            echo Messagebox::info(sprintf(_("Die Status�nderung wird erst nach einem erneuten %sLogin%s wirksam!<br>
            Deshalb wurden Sie jetzt automatisch ausgeloggt."), "<a href=\"index.php?again=yes\"><em>", "</em></a>"));
        }
    }
?>
        </td>
    </tr>
</table>

<?php
    include ('lib/include/html_end.inc.php');
    page_close();
