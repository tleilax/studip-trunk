<?php
/*
auswahl_suche.php - Uebersicht ueber die Suchfunktion von Stud.IP
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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	
?>
<table width="70%" border=0 cellpadding=0 cellspacing=0 align="center">

<tr>
	<td class="topic" colspan=2><img src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;<?=_("Suchen in Stud.IP")?></b></td>
</tr>
<tr>
<td class="blank" width="100%">
<blockquote>
	<br><a href="browse.php"><b><?=_("Suchen nach Personen")?></b></a></br>
	<font size=-1><?=_("Hier k&ouml;nnen sie nach ihren in Stud.IP angemeldeten Kommilitonen und Dozenten suchen.")?></font>
	<br>
	<br>
	<? if (!$perm->have_perm("root"))
		print "<a href=\"sem_portal.php\">";
	else
		print "<a href=\"meine_seminare.php\">";
	?>
	<b><?=_("Suchen nach Veranstaltungen")?></b></a></br>
	<font size=-1><?=_("Hier finden sie alle Veranstaltungen in Stud.IP.")?></font>
	<br>
	<br><a href="institut_browse.php"><b><?=_("Suchen nach Einrichtungen")?></b></a></br>
	<font size=-1><?=_("Hier finden sie alle Einrichtungen in Stud.IP.")?></font>
	<br>
	<?
	if ($RESOURCES_ENABLE) {
	?>
	<br><a href="resources.php?view=search&view_mode=no_nav&new_search=TRUE"><b><?=_("Suchen nach Ressourcen")?></b></a></br>
	<font size=-1><?=_("Hier finden Ressourcen wie etwa R&auml;ume, Geb&auml;ude oder Ger&auml;te.")?></font>
	<br>
	<?
	}
	?>
	<br><a href="archiv.php"><b><?=_("Suchen im Archiv")?></b></a></br>
	<font size=-1><?=_("Hier finden sie alle archivierten Veranstaltungen vergangener Semester.")?></font>
	<br>
	<br>
</td>
<td class="blank" align="right" valign="top"><img src="pictures/suche.jpg" border="0"></td>
</tr>

<?  // Save data back to database.
  page_close()
 ?>
</table>
</body>
</html>