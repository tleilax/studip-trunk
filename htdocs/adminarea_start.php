<?php

/*
adminarea_start.php - Dummy zum Einstieg in Adminbereich
Copyright (C) 2001 Cornelis Kater <ckater@gwdg.de>

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

// -- here you have to put initialisations for the current page

// Start of Output
include "$ABSOLUTE_PATH_STUDIP/html_head.inc.php"; // Output of html head
include "$ABSOLUTE_PATH_STUDIP/header.php";   // Output of Stud.IP head
include "$ABSOLUTE_PATH_STUDIP/links_admin.inc.php"; //Output the nav

require_once"$ABSOLUTE_PATH_STUDIP/visual.inc.php";
	
if ($SessSemName[1]) {
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="topic" colspan=2><img src="pictures/blank.gif" width="5" height="5" border="0"><b><?=_("Veranstaltung vorgew&auml;hlt")?></b></td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2>
	<blockquote>
	<?
	if ($links_admin_data["referred_from"] == "sem") {
		printf(_("Sie k&ouml;nnen hier direkt die Daten der Veranstaltung <b>%s</b> bearbeiten.") . "<br>", htmlReady($SessSemName[0]));
		print(_("Wenn Sie die Daten einer anderen Veranstaltung bearbeiten wollen, klicken Sie bitte auf das Schl&uuml;sselsymbol.") . "<br />&nbsp;"); 
	} else {
		printf(_("Sie haben die Veranstaltung <b>%s</b> vorgew&auml;hlt. Sie k&ouml;nnen nun direkt die einzelnen Bereiche dieser Veranstaltung bearbeiten, in dem Sie die entsprechenden Menupunkte w&auml;hlen.") . "<br>", htmlReady($SessSemName[0]));
		print(_("Wenn Sie eine andere Veranstaltung bearbeiten wollen, klicken Sie bitte auf das Schl&uuml;sselsymbol.") . "<br />&nbsp;"); 
	}
	?>
	</blockquote>
	</td></tr>
	</table>
<?		
}
page_close();
?>
</body>
</html>
