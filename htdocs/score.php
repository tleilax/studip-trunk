<?php
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><img src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;<?=_("Stud.IP-Score")?></td>
</tr>
<tr>
<td class="blank" align = left valign="top" width="60%"><br /><blockquote>
<?
echo _("Auf dieser Seite k&ouml;nnen Sie abrufen, wie weit Sie im Stud.IP-Score aufgestiegen sind. Je aktiver Sie sich im System verhalten, desto h&ouml;her klettern Sie!");

$score = getscore();
$user_id=$user->id; //damit keiner schummelt...

// schreiben des Wertes
$db=new DB_Seminar;
$cssSw=new cssClassSwitcher;

IF ($cmd=="write") {
	$query = "UPDATE user_info "
		." SET score = $score"
		." WHERE user_id = '$user_id'";
		$db->query($query);
	}
	
IF ($cmd=="kill") {
	$db=new DB_Seminar;
	$query = "UPDATE user_info "
		." SET score = 0"
		." WHERE user_id = '$user_id'";
		$db->query($query);
	}

// Angabe der eigenen Werte (immer)
echo "<br><br><b>" . _("Ihr Score:") . "&nbsp; ".$score."</b>";
echo "<br><b>" . _("Ihr Titel") . "</b> ;-)&nbsp; <b>".gettitel($score)."</b>";
echo "<br><br><a href=\"score.php?cmd=write\">" . _("Diesen Wert hier ver&ouml;ffentlichen") . "</a>";
?>

</blockquote></td>
<td class="blank" align = right><img src="pictures/board2.jpg" border="0"></td>
</tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td class=blank>
<br><blockquote>
<?=_("Hier sehen Sie den Score der NutzerInnen, die Ihre Werte ver&ouml;ffentlicht haben:")?>
</blockquote>&nbsp; </td></tr>
<tr><td class="blank">
<?

// Liste aller die mutig (oder eitel?) genug sind

$rang = 1;
$db=new DB_Seminar;
$db->query("SELECT a.user_id,username,score, " .$_fullname_sql['full'] ." AS fullname FROM user_info a LEFT JOIN auth_user_md5 b USING (user_id) WHERE score > 0 ORDER BY score DESC");
if ($db->num_rows()) {
	echo "<table width=\"99%\" align=\"center\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	while ($db->next_record()) {
		$kill = "";
		$cssSw->switchClass();
		if ($db->f("user_id")==$user_id) {
			$kill = "&nbsp; &nbsp; <a href=\"score.php?cmd=kill\">" . _("[l&ouml;schen]") . "</a>";
		}
		echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"1%\" nowrap align=\"right\">".$rang.".</td><td class=\"".$cssSw->getClass()."\" width=\"39%\" nowrap>"
		."&nbsp; &nbsp; <a href='about.php?username=".$db->f("username")."'>".$db->f("fullname")."</a></td>"
		."<td class=\"".$cssSw->getClass()."\" width=\"30%\">".$db->f("score")."</td><td class=\"".$cssSw->getClass()."\" width=\"30%\">".gettitel($db->f("score"))
		.$kill
		."</td></tr>\n";
		$rang++;
	}
	echo "</table>\n";
	}

page_close()
?>
</td></tr></table>
</body>
</html>
