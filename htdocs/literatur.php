<?php

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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

checkObject(); // do we have an open object?

include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
	

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><b>&nbsp;<img src="pictures/icon-lit.gif" align=absmiddle>&nbsp; <? echo $SessSemName["header_line"]; ?> - <?=_("Literatur und Links")?></b></td>
</tr>
	<td class="blank" width="100%"><blockquote><? printf ("%s", ($SessSemName["class"]=="inst") ? _("Hier finden Sie n&uuml;tzliche Literatur und Links zu der Einrichtung.") : _("Hier finden Sie die Literatur- und Linkliste der Veranstaltung."));?></td>
	<td class="blank" align = right><img src="pictures/literatur.jpg" border="0"></td>
</tr>
<tr>
	<td class="blank" colspan=2>

<?
$db=new DB_Seminar;
$db2=new DB_Seminar;

$db->query("SELECT * FROM literatur WHERE range_id='$SessSemName[1]'");
echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr><td class=\"blank\">";

if ($db->num_rows()) {
	$db->next_record();
	$literatur=$db->f("literatur");
	$links=$db->f("links");
	
	$zusatz="<font size=-1>" . sprintf(_("Zuletzt ge&auml;ndert von %s am %s"), "</font><a href=\"about.php?username=".get_username ($db->f("user_id"))."\"><font size=-1 color=\"#333399\">".get_fullname ($db->f("user_id"))."</font></a><font size=-1>", date("d.m.Y, H:i",$db->f("chdate"))."<font size=-1>&nbsp;"."</font>");				
	$icon="&nbsp;<img src=\"pictures/cont_lit.gif\">";
	
	//Literatur
	if ($literatur) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printhead(0, 0, $link, "open", FALSE, $icon, _("Literatur"), $zusatz);
		echo "</tr></table>	";
	
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent(0,0, FixLinks(htmlReady($literatur)), FALSE);	
		echo "</tr></table>	";		
		}

	//Links
	if ($links) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printhead(0, 0, $link, "open", FALSE, $icon, _("Links"), $zusatz);
		echo "</tr></table>	";
	
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent(0,0, FixLinks(htmlReady($links)), FALSE);	
		echo "</tr></table>	";		
		}
	}
	if ((!$literatur) && (!$links)) {
		parse_msg("info�<font size=-1><b>" . sprintf(_("In dieser %s wurden keine Literatur oder Links erfasst"), $SessSemName["art_generic"]) . "</b></font>", "�", "steel1", 
		2, FALSE);
		}
	echo "</td></tr></table></td></tr></table>";
?>
</td></tr></table>
</body>
</html>
<?php
  // Save data back to database.
  page_close()
 ?>
<!-- $Id$ -->
