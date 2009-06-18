<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/*
literatur.php - Literaturanzeige von Stud.IP
Copyright (C) 2000 Andr� Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

require_once('lib/classes/StudipLitList.class.php');
// -- here you have to put initialisations for the current page

mark_public_course();

$HELP_KEYWORD="Basis.Literatur";
$CURRENT_PAGE = $SessSemName["header_line"]. " - " . _("Literatur");

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

checkObject(); // do we have an open object?
checkObjectModule("literature");
object_set_visit_module("literature");

include ('lib/include/links_openobject.inc.php');
?>
<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
	<td class="blank" width="99%" align="left" valign="top">
	<table width="100%" border="0" cellpadding="20" cellspacing="0">
		<tr><td align="left" class="blank">
<?
if ( ($list = StudipLitList::GetFormattedListsByRange($SessSemName[1], object_get_visit($SessSemName[1], "literature"))) ){
	echo $list;
} else {
	echo _("Es wurde noch keine Literatur erfasst");
}
?>
		</td></tr>
	</table>
</td>
<td class="blank" align="center" valign="top">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="blank" width="270" align="right" valign="top">
<?
$infobox[0] = array ("kategorie" => _("Information:"),
					"eintrag" =>	array(
									array("icon" => "ausruf_small.gif","text"  =>	_("Hier sehen sie Literaturlisten.")),
									)
					);
$infobox[1] = array ("kategorie" => _("Aktionen:"));
$infobox[1]["eintrag"][] = array("icon" => "blank.gif","text"  =>  _("Sie k&ouml;nnen jede dieser Listen in ihren pers&ouml;nlichen Literaturbereich kopieren, um erweiterte Informationen �ber die Eintr&auml;ge zu erhalten.") );

print_infobox ($infobox,"literaturelist.jpg");
?>
</td>
</tr>
</table>
</td>
</tr>
<tr><td class="blank" colspan="2">&nbsp;</td></tr>
</table>
<?
include ('lib/include/html_end.inc.php');
// Save data back to database.
page_close();
// <!-- $Id$ -->
?>
