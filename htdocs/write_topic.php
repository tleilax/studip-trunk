<?php
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head

if  ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben, dazu auch JS Check auf Name
?>
<SCRIPT language="JavaScript">
<!--
function pruefe_name(){
 var re_nachname = /^([a-zA-Z���][^0-9"�'`\/\\\(\)\[\]]+)$/;
 var checked = true;
 if (re_nachname.test(document.forumwrite.nobodysname.value)==false) {
 	alert("<?=_("Bitte geben Sie Ihren tats�chlichen Namen an.")?>");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
  if (document.forumwrite.nobodysname.value=="unbekannt") {
 	alert("<?=_("Bitte geben Sie Ihren Namen an.")?>");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
 return checked;
}
// -->
</SCRIPT>
<?
}

	require_once("forum.inc.php");
	require_once("visual.inc.php");
	require_once("functions.php");
	require_once("msg.inc.php");

	checkObject();

	include "check_sem_entry.inc.php"; //hier wird der Zugang zum Seminar ueberprueft	

?>


<table class="blank" width="100%" cellspacing=0 border=0><tr>

<?

// Freies Seminar mit Schreibrecht fuer Nobody?

if ($user->id == "nobody"){
	$db=new DB_Seminar;
	$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id='$SessSemName[1]' AND Schreibzugriff=0");
	if ($db->num_rows())
		$pass=TRUE;
	else $pass=FALSE;
}

if (!(have_sem_write_perm()) OR $pass==TRUE) {
	if (!isset($Create)) {  // $Create != "abschicken"
		if (isset($topic_id)) {
			$db=new DB_Seminar;
			$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
			if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
				echo "</body></html>";
				page_close();
				die;
			}
			while ($db->next_record()) {
				$name = $db->f("name");
				echo"<td class=steel2 colspan=2>&nbsp; &nbsp; <b><font size=2>".htmlReady($name)."</font></b></td>";
				echo "\n</tr><tr>";
				$parent_description = formatReady($db->f("description"));
	  		if (ereg("\[quote",$parent_description) AND ereg("\[/quote\]",$parent_description))
					$parent_description = quotes_decode($parent_description);
				printcontent ("100%","",$parent_description,"");
				echo "\n</tr>";
				echo "	<tr>";
				echo "		<td colspan=2 class=steel1 align=center>";
				echo "			<a href=\"$PHP_SELF?write=1&root_id=$root_id&topic_id=$topic_id&quote=TRUE\">" . makeButton("zitieren", "img") . "</a>";
				echo "		</td>";
				echo "	</tr>";
				echo "<tr><td colspan=2 class=steel>&nbsp; </td></tr><tr><td colspan=2 class=steel1><blockquote>";
			
			}
			print "<br><b>" . _("Hierzu antworten:") . "</b><br><br>\n";
		} else {
			$topic_id = "0";
			$root_id = "0";
			$name = "";
		}

		if ($user->id == "nobody")
			echo "<form name=Create method=post action=\"write_topic.php?Create=TRUE\" onsubmit=\"return pruefe_name()\">"; // bei nobody mit namen pruefen
		else
			echo "<form name=Create method=post action=\"write_topic.php?Create=TRUE\">";

		echo "<input type=hidden name=\"parent_id\" value=\"$topic_id\">";
		echo "<input type=hidden name=\"root_id\" value=\"$root_id\">";
		print _("&Uuml;berschrift:") . " <br><input type=text name=name value=\"";

		if ($topic_id != "0" OR $topic_id > 0) {
			if (substr($name,0,3)=="Re:")
				print htmlReady($name);
			else
				print "Re: ".htmlReady($name);
		}
		print ("\" size=60>");
		print ("<input type=\"hidden\" name=\"author\" value = \"");
		$db=new DB_Seminar;
		$tmp = $auth->auth["uname"];
		echo get_fullname();
		print ("\" size=\"20\"><br><br>");
		if  ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben
			$description =	"<b>" . _("Ihr Name:") . "</b>&nbsp; <input type=text size=50 name=nobodysname onchange=\"return pruefe_name()\" value=\"" . _("unbekannt") . "\"><br><br><input type=hidden name=update value='".$write."'>";
		} 
		echo $description;
		echo _("Ihr Beitrag:");
		echo "<br><textarea name=\"description\" cols=60 rows=12>";
		if ($quote==TRUE) {  // es soll zitiert werden
			$zitat = quote($topic_id);
			echo htmlReady($zitat);
			echo "\n";
		}
		echo "</textarea><br>";
		echo "<input type=\"IMAGE\" " . makeButton("abschicken", "src") . " border=0>";
		echo "</form>";
	} else {
		if ($parent_id) {
			$db=new DB_Seminar;
			$db->query("SELECT * FROM px_topics WHERE topic_id='$parent_id' AND Seminar_id ='$SessSemName[1]'");
			if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
				echo "</body></html>";
		  	page_close();
				die;
			}
		}
		if ($nobodysname) $author = $nobodysname;
		$writeextern = TRUE;
		$topic_id = CreateTopic ($name, $author, $description, $parent_id, $root_id);
		parse_window( "msg�" . _("Ihr Beitrag wurde erfolgreich ins System &uuml;bernommen") . "�info�" . _("Sie k&ouml;nnen dieses Fenster jetzt schliessen.<br>Um Ihr neues Posting zu sehen, m&uuml;ssen Sie das Hauptfenster aktualisieren!") . "�", "�", "Schreiben erfolgreich", "&nbsp;");
	}
} else {
	$msg=have_sem_write_perm();
	parse_window($msg, "�", _("Schreiben nicht m&ouml;glich"), "&nbsp;");
}

  // Save data back to database.
  page_close()
?>
</blockquote></td></tr></table>
</body>
</html>
