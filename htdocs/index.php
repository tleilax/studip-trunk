<?php
/*
index.php - Startseite von Stud.IP (anhaengig vom Status)
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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

// database object
$db=new DB_Seminar;

// evaluate language clicks
// has to be done before seminar_open to get switching back to german (no init of i18n at all))
if (isset($set_language)) {
	$sess->register("forced_language");
	$forced_language = $set_language;
	$_language = $set_language;
}

// store and restore user-specific language preference
if ($auth->is_authenticated() && $user->id != "nobody") {
	// store last language click
	if (isset($forced_language)) {
		$db->query("UPDATE user_info SET preferred_language = '$forced_language' WHERE user_id='$user->id'");
		$_language = $forced_language;
		$sess->unregister("forced_language");
	// restore user-setting
	} else {
		$db->query("SELECT preferred_language FROM user_info WHERE user_id='$user->id'");
		if ($db->next_record()) {
			if ($db->f("preferred_language") != NULL && $db->f("preferred_language") != "") {
				// we found a stored setting for preferred language
				$_language = $db->f("preferred_language");
			}
		}
	}
}

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");

// -- hier muessen Seiten-Initialisierungen passieren --

// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

$sess->register("index_data");
		
//Auf und Zuklappen News
if ($nopen)
	$index_data["nopen"]=$nopen;
       
if ($nclose)
	$index_data["nopen"]='';
	
// Auf- und Zuklappen Termine
if ($dopen)
	$index_data["dopen"]=$dopen;
       
if ($dclose)
	$index_data["dopen"]='';
	
// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");

if (($my_messaging_settings["start_messenger_at_startup"]) && ($auth->auth["jscript"]) && (!$index_data["im_loaded"])) {
	?>
	<script language="Javascript">
	{fenster=window.open("studipim.php","im_<?=$user->id?>","scrollbars=yes,width=400,height=300","resizable=no");}
	</script>
	<?
	$index_data["im_loaded"]=TRUE;
}


//Anzeigemodul fuer persoenliche Startseite (nur wenn man angemeldet und nicht global root oder admin ist!)
IF ($auth->is_authenticated() && $user->id != "nobody" && !$perm->have_perm("dozent"))
	{
?>

<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0 >
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b>&nbsp;Ihre pers&ouml;nliche Startseite bei Stud.IP</b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="meine_seminare.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Meine Veranstaltungen</a><br />&nbsp; &nbsp; <font size="-1"><a href="sem_portal.php">Veranstaltung hinzuf&uuml;gen</a></td></tr>
		<tr><td class="blank"><a href="calendar.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Terminkalender</a><br />&nbsp; &nbsp; <font size="-1"><a href="mein_stundenplan.php">pers&ouml;nlicher Stundenplan</a></td></tr>
		<tr><td class="blank"><a href="about.php"><img src="pictures/forumrot.gif" border=0>&nbsp;pers&ouml;nliche Homepage</a><br />&nbsp; &nbsp; <font size="-1"><a href="edit_about.php?view=Daten">Benutzerdaten</a></td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Suchen</a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php">Personensuche</a>&nbsp;/&nbsp;<font size="-1"><a href="sem_portal.php">Veranstaltungsuche</a></td></tr>
		<tr><td class="blank"><a href="help/index.php" target="_new"><img src="pictures/forumrot.gif" border=0>&nbsp;Hilfe</a></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<?
	include ('show_news.php');
	if (show_news("studip", FALSE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";
	
	include("show_dates.inc.php");
	$start = time();
	$end = $start + 60 * 60 * 24 * 7;
	show_all_dates($start, $end, FALSE, FALSE, $index_data["dopen"]);
}

//Anzeigemodul fuer nobody)
ELSEIF (!$perm->have_perm("dozent")){

?>

<table class="blank" width="600" border=0 cellpadding=0 cellspacing=0 align=center>
<tr><td colspan=3 class="topic">&nbsp;<b><? echo $UNI_NAME;?></b></td></tr>
<tr><td class="blank" colspan=3><img src="./locale/<?=$_language_path?>/LC_PICTURES/startseite.jpg" alt="Stud.IP - <?=$UNI_NAME?>" width="669" height="320" usemap="#Map" border="0">
<map name="Map">
  <area shape="rect" coords="23,43,291,92" href="index.php?again=yes">
  <area shape="rect" coords="22,103,291,152" href="register1.php">
  <area shape="rect" coords="22,187,290,236" href="freie.php">
  <area shape="rect" coords="23,246,290,299" href="help/index.php" target="_new">
</map>

</td></tr>

<tr><td class="blank" colspan="3" align="right">
<?

// choose language
foreach ($INSTALLED_LANGUAGES as $temp_language_key => $temp_language) {
	printf ("&nbsp;&nbsp;<a href=\"%s?set_language=%s\"><img src=\"pictures/languages/%s\" %s border=\"0\"></a>", $PHP_SELF, $temp_language_key, $temp_language["picture"], tooltip($temp_language["name"]));
}
unset($temp_language_key); unset($temp_language);

?>
&nbsp;&nbsp;</td></tr>

<tr>
<td class="blank" nowrap align=left valign=bottom>
	&nbsp; <a href="index.php?again=yes"><font size=2 color="#6699CC">Login</font></a>&nbsp; 
	<a href="register1.php"><font size=2 color="#6699CC">Registrieren</font></a>&nbsp; 
</td>
<td class="blank" align=center><a href="http://www.studip.de"><img src="pictures/logoklein.gif" border=0 alt="Zur Portalseite"></a></td>
<td class="blank" align=right nowrap valign=bottom>
	<a href="freie.php"><font size=2 color="#6699CC">Freier Zugang</font></a>&nbsp; 
	<a href="help/index.php" target="_new"><font size=2 color="#6699CC">Hilfe</font></a>&nbsp;
</td></tr></table>
<DIV align=center>
<?php
		
}
ELSEIF ($auth->auth["perm"]=="dozent"){

//Startseite fuer Dozenten 
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0 >
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b>&nbsp;Startseite f&uuml;r DozentInnen bei Stud.IP</b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="meine_seminare.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Meine Veranstaltungen</a></td></tr>
		<tr><td class="blank"><a href="adminarea_start.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;Verwaltung von Veranstaltungen</a><br />&nbsp; &nbsp; <font size="-1"><a href="admin_seminare_assi.php?new_session=TRUE">neue Veranstaltung anlegen</a></font></td></tr>
		<tr><td class="blank"><a href="calendar.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Terminkalender</a><br />&nbsp; &nbsp; <font size="-1"><a href="mein_stundenplan.php">pers&ouml;nlicher Stundenplan</a></font></td></tr>
		<tr><td class="blank"><a href="about.php"><img src="pictures/forumrot.gif" border=0>&nbsp;pers&ouml;nliche Homepage</a><br />&nbsp; &nbsp; <font size="-1"><a href="edit_about.php?view=Daten">Benutzerdaten</a></font></td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Suchen</a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php">Personensuche</a>&nbsp;/&nbsp;<font size="-1"><a href="sem_portal.php">Veranstaltungsuche</a></font></td></tr>
		<tr><td class="blank"><a href="help/index.php" target="_new"><img src="pictures/forumrot.gif" border=0>&nbsp;Hilfe</a></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<div align="center">
<?
	include ('show_news.php');
	if (show_news("studip", FALSE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";
	
	include("show_dates.inc.php");
	$start = time();
	$end = $start + 60 * 60 * 24 * 7;
	show_all_dates($start, $end, FALSE, FALSE, $index_data["dopen"]);
}


ELSEIF ($auth->auth["perm"]=="admin"){

//Startseite fuer Inst-Admins
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0 >
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b>&nbsp;Startseite f&uuml;r Administratoren bei Stud.IP</b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="meine_seminare.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Veranstaltungen an meinen Einrichtungen</a></td></tr>
		<tr><td class="blank"><a href="adminarea_start.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;Verwaltung von Veranstaltungen</a></td></tr>
		<tr><td class="blank"><a href="admin_institut.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;Verwaltung von Einrichtungen</a></td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Suchen</a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php">Personensuche</a>&nbsp;/&nbsp;<a href="sem_portal.php">Veranstaltungsuche</a></font></td></tr>
		<tr><td class="blank"><a href="new_user_md5.php"><img src="pictures/forumrot.gif" border=0>&nbsp;globale Benutzerverwaltung</a></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<div align="center">
<?
	include ('show_news.php');
	if (show_news("studip", FALSE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";
}


ELSEIF ($perm->have_perm("root")){

//Startseite fuer root
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b><b>&nbsp;Startseite f&uuml;r root bei Stud.IP</b></b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="meine_seminare.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Veranstaltungs-&Uuml;bersicht</a></td></tr>
		<tr><td class="blank"><a href="adminarea_start.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;Verwaltung von Veranstaltungen</a></td></tr>
		<tr><td class="blank"><a href="admin_institut.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;Verwaltung von Einrichtungen</a></td></tr>
		<tr><td class="blank"><a href="new_user_md5.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Verwaltung globaler Einstellungen</a></td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;Suchen</a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php">Personensuche</a>&nbsp;/&nbsp;<a href="sem_portal.php">Veranstaltungsuche</a></font></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<?
	include ('show_news.php');
	if (show_news("studip", TRUE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";
}

?>
</td></tr></table>
</body>
</html>
<?php
  // Save data back to database.
  page_close();
 ?>
<!-- $Id$ -->
