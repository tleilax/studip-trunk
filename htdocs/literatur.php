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
<body bgcolor="#ffffff">


<?php
        include "seminar_open.php"; //hier werden die sessions initialisiert

// hier muessen Seiten-Initialisierungen passieren


        include "header.php";   //hier wird der "Kopf" nachgeladen

?>
<body>

<?
IF ($SessSemName[1] =="")
	{
	parse_window ("error�Sie haben keine Veranstaltung gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher eine Veranstaltung gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich l�nger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zur�ck zur Anmeldung zu gelangen. </font>", "�",
				"Keine Veranstaltung gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
	}
ELSE
        {
        include "links1.php";
        include "links2.php";
        require_once "functions.php";
        require_once "visual.inc.php";
        



?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="blank" colspan="2" width="100%">&nbsp;</td></tr>
<tr>
        <td class="topic" colspan=2><b>&nbsp;<img src="pictures/icon-lit.gif" align=absmiddle>&nbsp; <? echo htmlReady($SessSemName["art"]) .": ". htmlReady($SessSemName[0]); ?> - Literatur und Links</b></td>
</tr>
	<td class="blank" width="100%"><blockquote>Hier finden Sie die Literatur- und Linkliste der Veranstaltung.</td>
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
	
	$zusatz="<font size=-1>Zuletzt ge&auml;ndert von </font><a href=\"about.php?username=".get_username ($db->f("user_id"))."\"><font size=-1 color=\"#333399\">".get_fullname ($db->f("user_id"))."</font></a><font size=-1> am ".date("d.m.Y, H:i",$db->f("chdate"))."<font size=-1>&nbsp;"."</font>";				
	$icon="&nbsp;<img src=\"pictures/cont_lit.gif\">";
	
	//Literatur
	if ($literatur) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printhead(0, 0, $link, "open", FALSE, $icon, "Literatur", $zusatz);
		echo "</tr></table>	";
	
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent(0,0, FixLinks(htmlReady($literatur)), FALSE);	
		echo "</tr></table>	";		
		}

	//Links
	if ($links) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printhead(0, 0, $link, "open", FALSE, $icon, "Links", $zusatz);
		echo "</tr></table>	";
	
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent(0,0, FixLinks(htmlReady($links)), FALSE);	
		echo "</tr></table>	";		
		}
	}
	if ((!$literatur) && (!$links)) {
		parse_msg("info�<font size=-1><b>In dieser Veranstaltung wurde keine Literatur oder Links erfasst</b></font>", "�", "steel1", 
		2, FALSE);
		}
	echo "</td></tr></table></td></tr></table>";
?>
</td></tr></table>
</body>
</html>
<?php
}
  // Save data back to database.
  page_close()
 ?>
<!-- $Id$ -->