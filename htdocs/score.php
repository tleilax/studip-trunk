<?php
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/score.class.php");
?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><img src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;<?=_("Stud.IP-Score")?></td>
</tr>
<tr>
<td class="blank" align = left valign="top" width="60%"><br /><blockquote>
<?
echo _("Auf dieser Seite k�nnen Sie abrufen, wie weit Sie im Stud.IP-Score aufgestiegen sind. Je aktiver Sie sich im System verhalten, desto h�her klettern Sie!");

$cssSw=new cssClassSwitcher;
$score = new Score($user->id);

// schreiben des Wertes

IF ($cmd=="write") {
	$score->PublishScore();
}
	
IF ($cmd=="kill") {
	$score->KillScore();
}

// Angabe der eigenen Werte (immer)

echo "<br><br><b>" . _("Ihr Score:") . "&nbsp; ".$score->ReturnMyScore()."</b>";
echo "<br><b>" . _("Ihr Titel") . "</b> ;-)&nbsp; <b>".$score->ReturnMyTitle()."</b>";
if ($score->ReturnPublik())
	echo "<br><br><a href=\"score.php?cmd=kill\">" . _("Ihren Wert von der Liste l�schen") . "</a>";
else
	echo "<br><br><a href=\"score.php?cmd=write\">" . _("Diesen Wert auf der Liste ver�ffentlichen") . "</a>";
	
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
$db->query("SELECT a.user_id,username,score,geschlecht, " .$_fullname_sql['full'] ." AS fullname FROM user_info a LEFT JOIN auth_user_md5 b USING (user_id) WHERE score > 0 ORDER BY score DESC");
if ($db->num_rows()) {
	echo "<table width=\"99%\" align=\"center\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	while ($db->next_record()) {
		$kill = "";
		$cssSw->switchClass();
		if ($db->f("user_id")==$user->id) {
			$kill = "&nbsp; &nbsp; <a href=\"score.php?cmd=kill\">" . _("[l�schen]") . "</a>";
		}
		echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"1%\" nowrap align=\"right\">".$rang.".</td><td class=\"".$cssSw->getClass()."\" width=\"39%\" nowrap>"
		."&nbsp; &nbsp; <a href='about.php?username=".$db->f("username")."'>".$db->f("fullname")."</a></td>"
		."<td class=\"".$cssSw->getClass()."\" width=\"10%\">".$score->GetScoreContent($db->f("user_id"))."</td>"
		."<td class=\"".$cssSw->getClass()."\" width=\"20%\">".$db->f("score")."</td><td class=\"".$cssSw->getClass()."\" width=\"30%\">".$score->GetTitel($db->f("score"), $db->f("geschlecht"))
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
