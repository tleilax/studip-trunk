<?php
/*
institut_main.php - Die Eingangsseite fuer ein Institut
Copyright (C) 200 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>

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
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once "$ABSOLUTE_PATH_STUDIP/dates.inc.php"; //Funktionen zur Anzeige der Terminstruktur
require_once "$ABSOLUTE_PATH_STUDIP/datei.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/config.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php"; 
require_once "$ABSOLUTE_PATH_STUDIP/functions.php"; 
require_once "$ABSOLUTE_PATH_STUDIP/lib/classes/DataFields.class.php"; 

if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php";
	if ($_REQUEST['kill_chat']){
		chat_kill_chat($_REQUEST['kill_chat']);
	}
	
}
if ($GLOBALS['VOTE_ENABLE']) {
	include_once ("$ABSOLUTE_PATH_STUDIP/show_vote.php");
}


// hier muessen Seiten-Initialisierungen passieren
if (isset($auswahl) && $auswahl!="") {
	//just opened Einrichtung... here follows the init
	openInst ($auswahl);
} else {
	$auswahl=$SessSemName[1];
}

	// gibt es eine Anweisung zur Umleitung?
	if(isset($redirect_to) && $redirect_to != "") {
		$take_it = 0;

		for ($i = 0; $i < count($i_query); $i++) { // alle Parameter durchwandern
			$parts = explode('=',$i_query[$i]);
			if ($parts[0] == "redirect_to") {
				// aha, wir haben die erste interessante Angabe gefunden
				$new_query = $parts[1];
				$take_it ++;
			} elseif ($take_it) {
				// alle weiteren Parameter mit einsammeln
				if ($take_it == 1) { // hier kommt der erste
					$new_query .= '?';
				} else { // hier kommen alle weiteren
					$new_query .= '&';
				}
				$new_query .= $i_query[$i];
				$take_it ++;
			}

		}
		unset($redirect_to);
		page_close();
		header("Location: $new_query");
		die;
	}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

checkObject();

include "links_openobject.inc.php";
include "show_news.php";
  	
$sess->register("institut_main_data");
$DataFields = new DataFields($SessSemName[1]);
  	
//Auf und Zuklappen News
if ($nopen)
	$institut_main_data["nopen"]=$nopen;
        
if ($nclose)
	$institut_main_data["nopen"]='';
        
?>

<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="topic" colspan=2><b>&nbsp; <? echo $SessSemName["header_line"]. " - " . _("Kurzinfo"); ?>
</b></td></tr>
<tr><td class="blank">
<br><blockquote><?
$db->query ("SELECT a.*, b.Name AS fakultaet_name  FROM Institute a LEFT JOIN Institute b ON (b.Institut_id = a.fakultaets_id) WHERE a.Institut_id='$auswahl'");
$db->next_record();

if ($db->f("Strasse")) {
	echo "<font size=\"-1\"><b>" . _("Stra�e:") . " </b>"; echo htmlReady($db->f("Strasse")); echo"<br></font>";
}
	
if ($db->f("Plz")) {
	echo "<font size=\"-1\"><b>" . _("Ort:") . " </b>"; echo htmlReady($db->f("Plz")); echo"<br></font>";
}

if ($db->f("telefon")) {
	echo "<font size=\"-1\"><b>" . _("Tel.:") . " </b>"; echo htmlReady($db->f("telefon")); echo"<br></font>";
}

if ($db->f("fax")) {
	echo "<font size=\"-1\"><b>" . _("Fax:") . " </b>"; echo htmlReady($db->f("fax")); echo"<br></font>";
}

if ($db->f("url")) {
	echo "<font size=\"-1\"><b>" . _("Homepage:") . " </b>"; echo formatReady($db->f("url")); echo"<br></font>";
}

if ($db->f("email")) {
	echo "<font size=\"-1\"><b>" . _("E-Mail:") . " </b>"; echo formatReady($db->f("email")); echo"<br></font>";
}

if ($db->f("fakultaet_name")) {
	echo "<font size=\"-1\"><b>" . _("Fakult&auml;t:") . " </b>"; echo htmlReady($db->f("fakultaet_name")); echo"<br></font>";
}

$localFields = $DataFields->getLocalFields();

foreach ($localFields as $val) {
	echo "<font size=\"-1\"><b>" .htmlReady($val["name"]) . ": </b>"; echo htmlReady($val["content"]); echo"<br>";
}

	
?>
</blockquote>
</td>
<td class="blank" align="right" valign="top">
	<img src="pictures/blank.gif" height="10" width="5" /><br />
	<img src="pictures/einrichtungen.jpg" border="0"><img src="pictures/blank.gif" height="10" width="10" /><br />
	<img src="pictures/blank.gif" height="10" width="5" />
</td>
</tr>
</table>
<br />
<?php

// Anzeige von News
($rechte) ? $show_admin=TRUE : $show_admin=FALSE;
if (show_news($auswahl,$show_admin, 0, $institut_main_data["nopen"], "100%", $loginfilelast[$SessSemName[1]]))
	echo"<br>";
	
//show chat info
if (($GLOBALS['CHAT_ENABLE']) && ($modules["chat"])){
	if (chat_show_info($auswahl))
		echo "<br>";
}

// include and show votes and tests
if ($GLOBALS['VOTE_ENABLE']) {
	show_votes ($auswahl, $auth->auth["uid"], $perm, YES);
}

?>
</body>
</html>
<?php
  // Save data back to database.
  page_close()
 ?>
<!-- $Id$ -->
