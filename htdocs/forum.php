<?
/*
forum.php - Anzeige und Verwaltung des Forensystems
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

	if ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben, dazu auch JS Check auf Name
?>
<SCRIPT language="JavaScript">
<!--
function pruefe_name(){
 var re_nachname = /^([a-zA-Z���][^0-9"�'`\/\\\(\)\[\]]+)$/;
 var checked = true;
 if (re_nachname.test(document.forumwrite.nobodysname.value)==false) {
 	alert("Bitte geben Sie Ihren tats�chlichen Namen an.");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
  if (document.forumwrite.nobodysname.value=="unbekannt") {
 	alert("Bitte geben Sie Ihren Namen an.");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
 return checked;
}
// -->
</SCRIPT>
<?
}

	if ($forum["jshover"]==1 AND $auth->auth["jscript"]) { // JS an und erwuenscht?
		echo "<script language=\"JavaScript\">";
		echo "var ol_textfont = \"Arial\"";
		echo "</script>";
		echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
		echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"overlib.js\"></SCRIPT>";
	}

	require_once "functions.php";
	require_once "visual.inc.php";
	require_once "forum.inc.php";
	require_once "object.inc.php";
	require_once "msg.inc.php";
	require_once "dates.inc.php"; 

	checkObject();

function getMsTime(){
	$microtime = explode(' ', microtime());
	return (double)($microtime[1].substr($microtime[0],1)); 
}

$stoppuhr=getMsTime();

if ($view) {
	if ($view=="reset") {
		if ($forum["view"]=="tree" || $forum["view"]=="mixed")
			$view = $forum["view"];
		elseif ($forum["view"]=="flatfolder") {
			$view = "mixed";
			$forum["view"]="mixed";
		} else {
			$view = "tree";
			$forum["view"]="tree";
		}
	} else
		$forum["view"] = $view;
}
if (!$forum["view"]) {
	$view = "tree";
	$forum["view"] = $view;
}
if ($forum["view"]=="flatfolder" && $view)
	$forum["flatfolder"] = $open;
$view = $forum["view"];

	include "links_openobject.inc.php";

// Behandlung der Suche


if ($suchbegriff!="") {
	if($check_author) 
		$search_exp="x.author LIKE '%$suchbegriff%'";
	if ($check_name) {
		if ($search_exp)
			$search_exp.=" OR";
		$search_exp.=" x.name LIKE '%$suchbegriff%'";
	}
	if ($check_cont) {
		if ($search_exp)
			$search_exp.=" OR";
		$search_exp.=" x.description LIKE '%$suchbegriff%'";
	}
	$forum["search"] = $search_exp;	
}

if ($reset=="1")
	$forum["search"] = "";	

if ($indikator)
	$forum["indikator"] = $indikator;
	
if (!$forum["indikator"])
	$forum["indikator"] = "age";
	
if ($toolbar=="open")
	$forum["toolbar"] = "open";
if ($toolbar=="close")
	$forum["toolbar"] = "close";

// Sind wir da wo wir hinwollen?

if ($topic_id AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
		echo "<br><br>";
		parse_window ("error�" . _("Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.") . "<br /><font size=-1 color=black>" . _("Um unerw&uuml;nschte Effekte - wie falsch einsortierten Postings - zu vermeiden,<br>empfehlen wir, Stud.IP nur in einem Browserfenster zu verwenden.") . "</font>", "�",
				_("zuviele Browserfenster im Forenbereich!"), 
				"");
		die;
	}
} elseif ($open AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$open' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
		echo "<br><br>";
		parse_window ("error�" . _("Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.") . "<br /><font size=-1 color=black>" . _("Um unerw&uuml;nschte Effekte - wie falsch einsortierten Postings - zu vermeiden,<br>empfehlen wir, Stud.IP nur in einem Browserfenster zu verwenden.") . "</font>", "�",
				_("zuviele Browserfenster im Forenbereich!"), 
				"");
		die;
	}
}

// loeschen von nicht zuende getippten Postings

if ($forum["lostposting"]!="" AND !isset($update)) {
	$writemode = $forum["lostposting"];
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$writemode' and (description = 'Dieser Beitrag wird gerade bearbeitet.' OR description = 'Beschreibung des Themas')");
	if ($db->num_rows()) { 
		$count = 0;
		if (leer($writemode)==TRUE) // nur l�schen wenn noch keine Antworten, sonst stehenlassen
			delete_topic($writemode,$count);
		$forum["lostposting"]="";
	}
}

// Rekursives L�schen von Postings, Warnung
if ($delete_id) {
	$db=new DB_Seminar;
	$mutter = suche_kinder($delete_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$delete_id' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		if ($rechte || (($db->f("user_id") == $user->id) && ($count == 0))) {  // noch mal checken ob alles o.k.
			$root = $db->f("root_id");
			echo "\n\n<table class=\"blank\" cellspacing=0 cellpadding=5 border=0 width=\"100%\"><colgroup span=1></colgroup>\n";
			$msg="info�" . sprintf(_("Wollen Sie das untenstehende Posting %s von %s wirklich l&ouml;schen?"), "<b>".htmlReady($db->f("name"))."</b>", "<b>".$db->f("author")."</b>") . "<br>\n";
			if ($count)
				$msg.= sprintf(_("Alle %s Antworten auf diesen Beitrag werden ebenfalls gel&ouml;scht!"), $count) . "<br />\n<br />\n";
			$msg.="<a href=\"".$PHP_SELF."?really_kill=$delete_id&view=$view#anker\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
			$msg.="<a href=\"".$PHP_SELF."?topic_id=$root&open=$topic_id&view=$view&mehr=$mehr#anker\">" . makeButton("nein", "img") . "</a>\n";
			parse_msg($msg, '�', 'blank', '1', FALSE);
			echo "</table>";

		// Darstellung des zu loeschenden Postings
	
			echo "<table width=\"100%\" class=blank border=0 cellpadding=0 cellspacing=0 align=center><tr><td class=blank><br><br>";	
			echo "<table width=\"80%\" class=blank border=0 cellpadding=0 cellspacing=0 align=center><tr>";	
	
			$forumposting["id"] = $db->f("topic_id");
			$forumposting["name"] = $db->f("name");
			$forumposting["description"] = $db->f("description");
			$forumposting["author"] = $db->f("author");
			$forumposting["username"] = $db->f("username");
			$forumposting["rootid"] = $db->f("root_id");
			$forumposting["rootname"] = $db->f("root_name");
			$forumposting["mkdate"] = $db->f("mkdate");
			$forumposting["chdate"] = $db->f("chdate");
			$forumposting["buttons"] = "no";
			printposting($forumposting);
			echo "<br></td></tr></table>\n<br></td></tr></table>";	
			page_close();
			die;
		}
	}
} else {
	$forumposting["buttons"] = "yes";
}


if ($target =="Seminar"){ //Es soll in ein anderes Seminar verschoben werden 
	$verschoben = 0;
	move_topic($topic_id,$sem_id,$topic_id,$verschoben);
	$message = "move";
}
	
if ($target =="Institut"){ //Es soll in ein Institut verschoben werden 
	$verschoben = 0;
	move_topic($topic_id,$inst_id,$topic_id,$verschoben);
	$message = "move";
}

if ($target =="Thema"){ //Es soll in ein anderes Thema verschoben werden 
	$verschoben = 0;
	move_topic2($move_id,$move_id,$verschoben,$parent_id);
	$message = "move";
}


// Rekursives L�schen von Postings, jetzt definitiv!

if ($really_kill) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$really_kill' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		$mutter = suche_kinder($really_kill);
		$mutter = explode (";",$mutter);
		$count = sizeof($mutter)-2;
		$open = $db->f("root_id");
		if ($rechte || (($db->f("user_id") == $user->id) && ($count == 0))) {  // noch mal checken ob alles o.k.
			$count = 0;
			delete_topic($really_kill, $count);
			$db->next_record();
			if ($nurneu!=1) { // es wurde wirklich was gel&ouml;scht und nicht nur ein Anlegen unterbrochen
				$message = "kill";
			}
			$forum["lostposting"]="";
		}		
	}
}

// neuer Beitrag als Antwort wird eingeleitet

if ($answer_id) {
	$db=new DB_Seminar;
	$db->query("SELECT name, topic_id, root_id FROM px_topics WHERE topic_id = '$answer_id'");
	while($db->next_record()){
		$name = $db->f("name");
		if (substr($name,0,3)!="Re:")
			$name = "Re: ".$name; // Re: vor �berschriften bei Antworten
		$author = get_fullname();
		$postinginhalt = _("Dieser Beitrag wird gerade bearbeitet.");
		$edit_id = CreateTopic (addslashes($name), $author, $postinginhalt, $answer_id, $db->f("root_id"));
		$open = $edit_id;
		$forum["lostposting"] = $edit_id;
	}
}

if ($zitat==TRUE)
	$zitat = $answer_id;
	
if ($edit_id) 
	$open = $edit_id;

if ($update) {
	$author = get_fullname();
	$now = date ("d.m.y - H:i", time());
	if (ereg("%%\[editiert von",$description)) { // wurde schon mal editiert
		$postmp = strpos($description,"editiert von");
		$description = substr_replace($description,"editiert von ".$author." am ".$now."]%%",$postmp);
	} else {
		if (ForumFreshPosting($update)==FALSE) // editiert von nur dranh�ngen wenn nicht frisch erstellt
			$description.="\n\n%%[editiert von ".$author." am ".$now."]%%";
	}
	UpdateTopic ($titel, $update, $description);
	$open = $update; //gerade bearbeiteten Beitrag aufklappen
	$forum["lostposting"] = "";
}

if ($neuesthema==TRUE && $rechte) {			// es wird ein neues Thema angelegt
		$name = _("Name des Themas");
		$author = get_fullname();
		$edit_id = CreateTopic ($name, $author, "Beschreibung des Themas", "0", "0");
		$open = $edit_id;
		$forum["lostposting"] = $edit_id;
}

// Anker setzen

	$forum["anchor"] = $open;

//Titel-Zeile
if (!$forumsend=="anpassen") {
	echo "\n<table width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "<tr><td class=\"topic\" width=\"50%\"><b>&nbsp;<img src='pictures/icon-posting.gif' align=absmiddle>&nbsp; ". $SessSemName["header_line"] ." - " . _("Forum") . "</b></td><td class=\"topic\" width=\"50%\" align=\"right\"><a href='forum.php?forumsend=anpassen'><img src='pictures/pfeillink.gif' border=0 " . tooltip(_("Look & Feel anpassen")) . ">&nbsp;</a></td></tr>";
	
	// Ausgabe f�r Zusatzinfos
	if ($message=="kill") echo parse_msg("msg�" . sprintf(_("%s Posting(s) gel&ouml;scht"), $count));
	if ($message=="move") echo parse_msg("msg�" . sprintf(_("%s Posting(s) verschoben."), $verschoben));
	if ($cmd == "move" && $topic_id !="" && $rechte)
		forum_move_navi ($topic_id);
		
	if (!$cmd && !$reset) {
		if ($forum["toolbar"] == "open") {
			echo "<tr><td class=\"blank\" colspan=\"2\"><br>";
			?>
			<table cellpadding="0" cellspacing="0" border="0" class="blank"><tr><td class="steelkante"><img src="pictures/blank.gif" height="22" width="5"></td>
			<td class="steelkante"><font size=-1>Indikator:&nbsp;
			<?
			if ($forum["indikator"] == "age")
				echo "</td><td class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forumrot_indikator.gif\" align=\"middle\"><font size=\"-1\">"._("Alter")." &nbsp;";
			else
				echo "</td><td class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=age\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Alter")."</a> &nbsp;";
			if ($forum["indikator"] == "views")
				echo "</td><td class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forum_indikator_gruen.gif\" align=\"middle\"><font size=\"-1\">"._("Views")." &nbsp;";
			else
				echo "</td><td class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=views\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Views")."</a> &nbsp;";
			if ($forum["indikator"] == "rate")
				echo "</td><td class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forumrot_indikator.gif\" align=\"middle\"><font size=\"-1\">"._("Bewertung")." &nbsp;";
			else
				echo "</td><td class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=rate\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Bewertung")."</a> &nbsp;";
			if ($forum["indikator"] == "score")
				echo "</td><td class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forumrot_indikator.gif\" align=\"middle\"><font size=\"-1\">"._("Relevanz")." &nbsp;";
			else
				echo "</td><td class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=score\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Relevanz")."</a> &nbsp;";
			?>
			</td><td class="steelkante" valign="bottom">&nbsp;|&nbsp;&nbsp;<font size="-1">Sortierung:&nbsp;&nbsp;</font>
			<select name="username" size="1">
			<option value="" size="-1">Alter
			<option value="">Views
			<option value="">Bewertungen
			<option value="">Relevanz
			</select>&nbsp;&nbsp;
			<?
			echo "<img src=\"pictures/vote_answer_correct.gif\" align=\"middle\">&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&toolbar=close&open=$open\" ".tooltip(_("Toolbar einfahren"))."><img src=\"pictures/forumgrau3.gif\" align=\"middle\" border=\"0\"></a>&nbsp;";
			echo "</td></tr>";
		} else {
			echo "<tr><td class=\"blank\" colspan=\"2\"><br>";
			echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"blank\"><tr><td class=\"steelkante\"><img src=\"pictures/blank.gif\" height=\"22\" width=\"5\"></td>";
			echo "<td class=\"steelkante\"><font size=\"-1\"><a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&toolbar=open&open=$open\"><img src=\"pictures/forum_indikator_grau.gif\" align=\"middle\" border=\"0\"".tooltip(_("Toolbar ausfahren"))."></a>";
			echo "</td></tr>";
		}
	}	
	echo "\n</table>\n";
}

if ($forumsend) {
	if ($forumsend=="bla"){
		$forum=array(
			"jshover"=>$jshover, 
			"neuauf"=>$neuauf,
			"changed"=>"TRUE"			
			);
	} ELSE
		include("forumsettings.inc.php");
}

// Verzweigung zu den drei Anzeigemodi 

if ($flatallopen=="TRUE")
	$forum["flatallopen"] = "TRUE";
if ($flatallopen=="FALSE")
	$forum["flatallopen"] = "FALSE";

if ($forum["view"]=="flat" || $forum["view"]=="neue" || $forum["view"]=="flatfolder" || $forum["view"]=="search")
 	flatview ($open, $mehr, $show, $edit_id, $name, $description, $zitat);
else
	DisplayFolders ($open, $edit_id, $zitat);

echo "Zeit:".(getMsTime()-$stoppuhr);

  // Save data back to database.
  page_close()
 ?>
</body>
</html>