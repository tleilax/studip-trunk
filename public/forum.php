<?
# Lifter001: DONE
# Lifter002: TODO
# Lifter005: TODO
/*
forum.php - Anzeige und Verwaltung des Forensystems
Copyright (C) 2003 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
$txt = $message = $count = $verschoben = '';
$flatviewstartposting = (int)$flatviewstartposting;
(isset($view) && preg_match('/^[a-z]*$/', $view)) or $view = '';
(isset($open) && preg_match('/^[a-z0-9]{1,32}$/', $open)) or $open = '';

if ($forumsend && $forumsend!="bla") {
	$HELP_KEYWORD="Basis.ForumEinstellungen";
	$CURRENT_PAGE = _("Einstellungen des Forums anpassen");
} elseif(isset($neuesthema)) {
	$HELP_KEYWORD="Basis.ForumBeteiligen";
	$CURRENT_PAGE = $SessSemName["header_line"]. " - " . _("Forum");
} else {
	switch($view) {
		case "neue": 
			$HELP_KEYWORD="Basis.ForumNeu"; 
			break;
		case "flat": 
			$HELP_KEYWORD="Basis.Forumlast4"; 
			break;
		case "search": 
			$HELP_KEYWORD="Basis.ForumSuche"; 
			break;
		default:
			$HELP_KEYWORD="Basis.Forum";
	}
	$CURRENT_PAGE = $SessSemName["header_line"]. " - " . _("Forum");
}

// Start of Output
	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head

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

if ($auth->auth["jscript"]) { // JS an
	echo "<script language=\"JavaScript\">";
	echo "var ol_textfont = \"Arial\"";
	echo "</script>";
	echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
	echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$GLOBALS['ASSETS_URL']."javascripts/overlib.js\"></SCRIPT>";
}

require_once 'lib/functions.php';
require_once ('lib/visual.inc.php');
require_once ('lib/forum.inc.php');
require_once ('lib/object.inc.php');
require_once ('lib/msg.inc.php');
require_once ('lib/dates.inc.php');

checkObject();
checkObjectModule("forum");
object_set_visit_module("forum");

//////////////////////////////////////////////////////////////////////////////////
// Debug Funktion zur Zeitmessung
//////////////////////////////////////////////////////////////////////////////////

function getMsTime(){
	$microtime = explode(' ', microtime());
	return (double)($microtime[1].substr($microtime[0],1));
}

//$stoppuhr=getMsTime();


//////////////////////////////////////////////////////////////////////////////////
//Daten aus der Einstellungsseite verarbeiten
//////////////////////////////////////////////////////////////////////////////////

if ($forumsend) {
	if ($forumsend=="bla"){
		if ($presetview == "theme")
			$presetview = $themeview;
		$forum["neuauf"] = $neuauf;
		$forum["postingsperside"] = $postingsperside;
		$forum["flatallopen"] = $flatallopen;
		$forum["rateallopen"] = $rateallopen;
		$forum["showimages"] = $showimages;
		$forum["sortthemes"] = $sortthemes;
		$forum["themeview"] = $themeview;
		$forum["presetview"] = $presetview;
		$forum["shrink"] = $shrink*604800; // Anzahl der Sekunden pro Woche
		$forum["changed"] = "TRUE";
		$txt = _("Anpassungen durchgef�hrt.");
	} else
		include('lib/include/forumsettings.inc.php');
}

//////////////////////////////////////////////////////////////////////////////////
// Anzeige und View-Logik
//////////////////////////////////////////////////////////////////////////////////


if ($forum["view"]=="mixed" && $open) {
	$forum["flatfolder"] = $open;
}

if (!$forum["themeview"])
	$forum["themeview"]="tree";

if ($themeview) { // Umschaltung tree/flat �ber die Kopfleiste
	$forum["themeview"]=$themeview;
	if ($forum["presetview"]=="tree" || $forum["presetview"]=="mixed")
		$forum["presetview"] = $themeview;
}

if ($presetview) {
	if ($presetview == "theme")
		$forum["presetview"]=$forum["themeview"];
	else
		$forum["presetview"] = $presetview;
}

if (!$forum["presetview"])
	$forum ["presetview"] = $forum["themeview"];
if (!$forum["sortthemes"])
	$forum["sortthemes"] = "asc";

if ($view) {
	if ($view=="reset")
		$forum["view"] = $forum["presetview"];
	else
		$forum["view"] = $view;
}

if (!$forum["view"]) {
	$view = $forum["themeview"];
	$forum["view"] = $view;
}

$view = $forum["view"];

URLHelper::addLinkParam('view', $view);

///////////////////////////////////////////////////////////////////////////////////
// Reiterleiste einbinden
//////////////////////////////////////////////////////////////////////////////////

include 'lib/include/links_openobject.inc.php';

//////////////////////////////////////////////////////////////////////////////////
// Behandlung der Suche
//////////////////////////////////////////////////////////////////////////////////

if ($_REQUEST['suchbegriff'] != "") {
	$forum['searchstring'] = $_REQUEST['suchbegriff'];

	$search_words = array();
	$search_title = array();
	$search_author = array();

	foreach(explode(' ', str_replace('.', ' ', $_REQUEST['suchbegriff'])) as $item) {
		$item = str_replace('*', '%', $item);
		$item = str_replace('.', ',', $item);
		$item = str_replace('-', ',', $item);
		// ignore ',' in queries like "thema, neu"
		if(substr($item, -1) == ',') {
			$item = substr($item, 0, strlen($item) -1);
		}

		// ignore searches for single letters and multiple double spaces
		if(strlen($item) < 2) {
			continue;
		}

		if(substr($item, 0, 8) == 'intitle:') {
			foreach(explode(',', substr($item, 8)) as $i) {
				array_push($search_title, "x.name LIKE '%$i%'");
			}
		} else {
			foreach(explode(',', $item) as $i) {
				// handle "thema,neu"
				array_push($search_words, "(x.name LIKE '%$i%' OR x.description LIKE '%$i%')");
			}
		}
	}

	$forum['searchauthor'] = array();
	if(trim($_REQUEST['author']) != "") {
		$forum['searchauthor'] = explode(',', $_REQUEST['author']);
	}

	foreach($forum['searchauthor'] as $item) {
		$author = array();
		foreach(explode(' ', str_replace('.', ' ', $item)) as $a) {
			array_push($author, "x.author LIKE '%".trim($a)."%'");
		}
		array_push($search_author, implode(' AND ', $author));
	}

	if(count($search_words) > 0)
		$search_exp = '(' . implode(' AND ', $search_words) . ')';
	else
		$search_exp = '1';

	if(count($search_author) > 0)
		$search_exp.= ' AND '. implode(' OR ', $search_author);

	if(count($search_title) > 0)
		$search_exp.= ' AND '. implode(' AND ', $search_title);

	$forum["search"] = $search_exp;
	URLHelper::addLinkParam('suchbegriff', $_REQUEST['suchbegriff']);
	URLHelper::addLinkParam('author', $_REQUEST['author']);
}

if ($reset=="1")	// es wurde neue Suche aktiviert, also Suchbegriff l�schen
	$forum["search"] = "";

//////////////////////////////////////////////////////////////////////////////////
// verschiedene GUI-Konstanten werden gesetzt
//////////////////////////////////////////////////////////////////////////////////

if ($indikator) {
	$forum["indikator"] = $indikator;
	URLHelper::addLinkParam('indikator', $indikator);
}

if ($sort) {
	$forum["sort"] = $sort;
	URLHelper::addLinkParam('sort', $sort);
}
if (!$forum["sort"])
	$forum["sort"] = "age";

if (!$forum["indikator"])
	$forum["indikator"] = "age";

if ($toolbar=="open") {
	$forum["toolbar"] = "open";
	URLHelper::addLinkParam('toolbar', $toolbar);
}
if ($toolbar=="close")
	$forum["toolbar"] = "close";

$indexvars["age"]["name"]=_("Alter");
$indexvars["age"]["color"]="#FF0000";
$indexvars["viewcount"]["name"]=_("Views");
$indexvars["viewcount"]["color"]="#008800";
$indexvars["rating"]["name"]=_("Bewertung");
$indexvars["rating"]["color"]="#CC7700";
$indexvars["score"]["name"]=_("Relevanz");
$indexvars["score"]["color"]="#0000FF";

$openorig = $open;  // wird gebraucht f�r den open-Link wenn im Treeview $open �berschrieben wird

//////////////////////////////////////////////////////////////////////////////////
// Sind wir da wo wir hinwollen?
//////////////////////////////////////////////////////////////////////////////////
$sql_topic_id = false;
if ($topic_id AND !$update) {
	$sql_topic_id = $topic_id;
} elseif ($open AND !$update) {
	$sql_topic_id = $open;
} elseif ($answer_id) {
	$sql_topic_id = $answer_id;
}
if ($sql_topic_id) {
	$db=new DB_Seminar;
	$db->query('SELECT * FROM px_topics WHERE topic_id=\''.$sql_topic_id. '\' AND Seminar_id =\''.$SessSemName[1].'\'');
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
		echo '<br /><br />';
		parse_window ('error�' . _("Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.") . '<br /><font size="-1" color="black">' . _("Um unerw&uuml;nschte Effekte - wie falsch einsortierten Postings - zu vermeiden,<br>empfehlen wir, Stud.IP nur in einem Browserfenster zu verwenden.") . '</font>', '�',
				_("zuviele Browserfenster im Forenbereich!"),
				'');
		die;
	}
}

//////////////////////////////////////////////////////////////////////////////////
// loeschen von nicht zuende getippten Postings
//////////////////////////////////////////////////////////////////////////////////

if ($forum["lostposting"]!="" AND !isset($update)) {
	$writemode = $forum["lostposting"];
	$db=new DB_Seminar;
	$db->query("SELECT topic_id FROM px_topics WHERE topic_id='$writemode' AND mkdate=chdate+1");
	if ($db->num_rows()) {
		$count = 0;
		$result = forum_lonely(array('id'=>$writemode));
		if ($result['lonely']==TRUE) // nur l�schen wenn noch keine Antworten, sonst stehenlassen
			delete_topic($writemode,$count);
		unset($result);
	}
	$forum["lostposting"]="";
}

//////////////////////////////////////////////////////////////////////////////////
// Rekursives L�schen von Postings, Warnung
//////////////////////////////////////////////////////////////////////////////////

if ($delete_id) {
	$db=new DB_Seminar;
	$mutter = suche_kinder($delete_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	$db->query("SELECT *, IFNULL(ROUND(AVG(rate),1),99) as rating FROM px_topics LEFT JOIN object_rate ON(object_rate.object_id=topic_id) WHERE topic_id='$delete_id' AND Seminar_id ='$SessSemName[1]' GROUP BY topic_id");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		if ($rechte || (($db->f("user_id") == $user->id) && ($count == 0))) {  // noch mal checken ob alles o.k.
			$root = $db->f("root_id");
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
			$forumposting["rating"] = $db->f("rating");
			forum_draw_topicline();
			if ($forumposting["id"] == $forumposting["rootid"])
				$tmp_label = _("das untenstehende Thema");
			else
				$tmp_label = _("das untenstehende Posting");
			echo "\n\n<table class=\"blank\" cellspacing=0 cellpadding=5 border=0 width=\"100%\"><colgroup span=1></colgroup>\n";
			echo "<tr><td class=\"blank\"></td></tr>";
			$msg="info�" . sprintf(_("Wollen Sie %s %s von %s wirklich l�schen?"), $tmp_label, "<b>".htmlReady($db->f("name"))."</b>", "<b>".htmlReady($db->f("author"))."</b>") . "<br>\n";
			if ($count)
				$msg.= sprintf(_("Alle %s Antworten auf diesen Beitrag werden ebenfalls gel�scht!"), $count) . "<br />\n<br />\n";
			$msg.="<a href=\"".URLHelper::getLink("?really_kill=$delete_id&view=$view#anker")."\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
			$msg.="<a href=\"".URLHelper::getLink("?topic_id=$root&open=$topic_id&view=$view&mehr=$mehr#anker")."\">" . makeButton("nein", "img") . "</a>\n";
			parse_msg($msg, '�', 'blank', '1', FALSE);
			echo "</table>";

		// Darstellung des zu loeschenden Postings

			echo '<table width="100%" class="blank" border="0" cellpadding="0" cellspacing="0" align="center"><tr><td class="blank"><br /><br />';
			echo '<table width="80%" class="blank" border="0" cellpadding="0" cellspacing="0" align="center"><tr>';



			printposting($forumposting);

			echo "<br /></td></tr></table>\n<br /></td></tr></table>";
			page_close();
			die;
		}
	}
} else {
	$forumposting["buttons"] = "yes";
}

//////////////////////////////////////////////////////////////////////////////////
// Verschieben von Postings
//////////////////////////////////////////////////////////////////////////////////

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

//////////////////////////////////////////////////////////////////////////////////
// Rekursives L�schen von Postings, jetzt definitiv!
//////////////////////////////////////////////////////////////////////////////////

if ($really_kill) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$really_kill' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		$mutter = suche_kinder($really_kill);
		$mutter = explode (";",$mutter);
		$count = sizeof($mutter)-2;
		$open = $db->f("root_id");
		if ($rechte || (($db->f("user_id") == $user->id || $db->f("user_id") == "") && ($count == 0))) {  // noch mal checken ob alles o.k.
			$count = 0;
			delete_topic($really_kill, $count);
			$db->next_record();
			if ($nurneu!=1) { // es wurde wirklich was gel�scht und nicht nur ein Anlegen unterbrochen
				$message = "kill";
			}
			$forum["lostposting"]="";
		}
	}
}

//////////////////////////////////////////////////////////////////////////////////
// neuer Beitrag als Antwort wird eingeleitet
//////////////////////////////////////////////////////////////////////////////////

if ($answer_id) {
	$db=new DB_Seminar;
	$db->query("SELECT name, topic_id, root_id FROM px_topics WHERE topic_id = '$answer_id'");
	while($db->next_record()){
		$name = $db->f("name");
		if (substr($name,0,3)!="Re:")
			$name = "Re: ".$name; // Re: vor �berschriften bei Antworten
		$author = get_fullname();
		$postinginhalt = _("Dieser Beitrag wird gerade bearbeitet.");
		$edit_id = CreateNewTopic($name, $postinginhalt, $answer_id, $db->f("root_id"));
		$open = $edit_id;
		$forum["lostposting"] = $edit_id;
	}
}

//////////////////////////////////////////////////////////////////////////////////
// Update eines Beitrags
//////////////////////////////////////////////////////////////////////////////////

if ($update) {
	// check whether we should create a new posting or update an existing one
	if (isset($_REQUEST['parent_id'])) {
		$author = get_fullname();
		$parent_id = $_REQUEST['parent_id'];
		$root_id = $parent_id != "0" ? $_REQUEST['root_id'] : "0";
		$user_id = $auth->auth['uid'];
		$update = CreateTopic($titel, $author, $description, $parent_id, $root_id, 0, $user_id);
	} else {
		if (!ForumFreshPosting($update)) // editiert von nur dranh�ngen wenn nicht frisch erstellt
			$description = forum_append_edit($description);
		UpdateTopic ($titel, $update, $description);
	}
	$open = $update; //gerade bearbeiteten Beitrag aufklappen
	$forum["lostposting"] = "";
}

//////////////////////////////////////////////////////////////////////////////////
// Neues Thema wird angelegt
//////////////////////////////////////////////////////////////////////////////////

if ($neuesthema==TRUE && ($rechte || $SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["topic_create_autor"])) {			// es wird ein neues Thema angelegt
		$name = _("Name des Themas");
		$author = get_fullname();
		$edit_id = CreateNewTopic($name, "Beschreibung des Themas");
		$open = $edit_id;
		$forum["lostposting"] = $edit_id;
}

//////////////////////////////////////////////////////////////////////////////////
// weitere Konstanten setzen
//////////////////////////////////////////////////////////////////////////////////

if ($zitat==TRUE)
	$zitat = $answer_id;

if ($edit_id)
	$open = $edit_id;

if ($flatallopen=="TRUE")
	$forum["flatallopen"] = "TRUE";

if ($flatallopen=="FALSE")
	$forum["flatallopen"] = "FALSE";

if ($fav)
	$forum["anchor"] = $fav; // Anker auf Favoriten
else
	$forum["anchor"] = $open; // Anker setzen

if ($rate) { // Objekt bewerten
	while(list($key,$value) = each($rate)) {
		$txt = object_add_rate ($key, $value);
		$forum["anchor"] = $key;
	}
}

if ($fav)   // zu den Favoriten hinzuf�gen/entfernen
	$fav = object_switch_fav($fav);




//////////////////////////////////////////////////////////////////////////////////
//Anzeige des Kopfes mit Meldungen und Toolbar
//////////////////////////////////////////////////////////////////////////////////

if ($forumsend!="anpassen") {

	forum_draw_topicline();

	// Ausgabe f�r Zusatzinfos
	if ($message=="kill") echo parse_msg("msg�" . sprintf(_("%s Posting(s) gel&ouml;scht"), $count));
	if ($message=="move") echo parse_msg("msg�" . sprintf(_("%s Posting(s) verschoben."), $verschoben));
	if ($txt) echo parse_msg("msg�" . $txt);
	if ($cmd == "move" && $topic_id !="" && $rechte)
		forum_move_navi ($topic_id);

	if (!$cmd && !$reset) {
	}

	echo "\n</table>\n";
}

if (!$reset && $user->id != "nobody" && $cmd!="move")   // wenn Suche aufgerufen wird keine toolbar
	echo forum_print_toolbar($edit_id);
elseif ($user->id == "nobody" || $cmd=="move") {
	echo "\n<table width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"blank\"><br></td></tr>";
	if ($edit_id)
		echo "<form name=forumwrite onsubmit=\"return pruefe_name()\" method=post action=\"".URLHelper::getLink("#anker")."\">";
}
//////////////////////////////////////////////////////////////////////////////////
// Verzweigung zu den Anzeigemodi
//////////////////////////////////////////////////////////////////////////////////

if ($forum["view"]=="flat" || $forum["view"]=="neue" || $forum["view"]=="flatfolder" || $forum["view"]=="search")
 	flatview ($open, $mehr, $show, $edit_id, $name, $description, $zitat);
else
	DisplayFolders ($open, $edit_id, $zitat);

//////////////////////////////////////////////////////////////////////////////////
// Rest
//////////////////////////////////////////////////////////////////////////////////


// echo "Zeit:".(getMsTime()-$stoppuhr);
include ('lib/include/html_end.inc.php');
  // Save data back to database.
  page_close();
 ?>
