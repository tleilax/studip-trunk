<?php
/*
admin_news.php - �ndern der News von Stud.IP
Copyright (C) 2001	Andr� Noack <andre.noack@gmx.net>

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
//$Id$
page_open(array("sess"=> "Seminar_Session", "auth" =>"Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");
$perm->check("autor");

require_once "messaging.inc.php";
require_once "visual.inc.php";
require_once 'lib/functions.php';
require_once "lib/classes/AdminNewsController.class.php";


include ("seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

$HELP_KEYWORD="Basis.News";

// Start of Output
include ("html_head.inc.php"); // Output of html head
include ("header.php");   // Output of Stud.IP head



echo "\n" . cssClassSwitcher::GetHoverJSFunction() . "\n";

if(!$news_range_id) {
	$sess->register("news_range_id");
	$sess->register("news_range_name");
}

if ($range_id == 'self') {
	$range_id = $user->id;
}

if ($range_id){
	$news_range_id = $range_id;
}

if ($SessSemName[1] && ($list || $view)) {
	$news_range_id = $SessSemName[1];
	$news_range_name = $SessSemName[0];
}

$news = new AdminNewsController();


if ($list || $view || (($news_range_id != $user->id) && ($news_range_id != 'studip')) ){
		include ("links_admin.inc.php");	//Linkleiste fuer admins
} else {
		include ("links_about.inc.php"); //Linkliste pers�nlicher Bereich
}



?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td class="topic"><b>&nbsp;
<?=_("Newsverwaltung")?></b> <font size="-1">(<?=_("gew&auml;hlter Bereich:")?> <b><?=htmlReady($news_range_name)?></b>)</font></td></tr>
<?

if ($perm->have_perm("admin"))	{
	if ($cmd=="search") {
		if (!$search) {
			$news->msg .= "error�" . _("Sie haben keinen Suchbegriff eingegeben!") . "�";
			$cmd="";
		} else {
			$news->search_range($search);
			if (is_array($news->search_result) && !count($news->search_result))
				$news->msg.="info�" . _("Die Suche ergab keine Treffer!") . "�";
			$cmd="";
		}
	}
}

if ($cmd == 'news_edit'){
	if (isset($_REQUEST['news_submit_x'])) $cmd = 'news_submit';
	if (isset($_REQUEST['news_range_search_x'])){
		$cmd = 'edit';
		$edit_news = $_REQUEST['news_id'];
	}
}

if ($cmd=="news_submit") {
	if (!trim(stripslashes($topic)) && trim(stripslashes($body))) $topic = addslashes(substr(trim(stripslashes($body)),0,30) . '...');
	if ($topic != "") {
		$edit_news = $news->update_news($news_id, $author, $topic, $body, $user_id, $date, $expire, $add_range, $allow_comments);
		if ($edit_news) $cmd = "edit";
		else $cmd = "";
	} else {
		$cmd = "edit";
		$news->msg .= "error�"._("Leere News k&ouml;nnen nicht gespeichert werden! Geben Sie immer &Uuml;berschrift oder Inhalt an!")."�";
	}
}
if ($cmd=="new_entry" &&
	isset($_REQUEST['change_rss_x']) &&
	get_config('NEWS_RSS_EXPORT_ENABLE') &&
	$news->get_news_range_perm($news_range_id) > 1){
		if (StudipNews::GetRssIdFromRangeId($news_range_id)){
			StudipNews::UnSetRssId($news_range_id);
			$news->msg .= "info�" . _("Der RSS Export wurde f�r diesen Bereich ausgeschaltet!") . "�";
		} else {
			StudipNews::SetRssId($news_range_id);
			$news->msg .= "info�" . _("Der RSS Export wurde f�r diesen Bereich eingeschaltet!") 
						. '<br>' . _("Bitte beachten Sie, dass damit die News dieses Bereiches auch von Personen die nicht im Stud.IP angemeldet sind abgerufen werden k&ouml;nnen!") . "�";
		}
		$cmd = '';
}

if ($news->msg) {
	echo "<tr><td class=\"blank\"><br />";
	parse_msg($news->msg,"�","blank","1");
	echo "</td></tr>";
}
$news->msg="";

if ($cmd=="edit") {
	if ($perm->have_perm("admin") && $search) {
		if ($search)
			$news->search_range($search);
			if (is_array($news->search_result) && !count($news->search_result)) {
			echo "<tr><td class=\"blank\"><br />";
			parse_msg("info�" . _("Die Suche ergab keine Treffer!") . "�","�","blank","1",FALSE);
			echo "</td></tr>";
		}
	}
	if ($auth->auth["perm"]=="dozent" OR $auth->auth["perm"]=="tutor")
		$news->search_range();

	$news->edit_news($edit_news);
}

if ($cmd=="kill") {
	$news->kill_news($kill_news);
	$cmd="";
}

if ($news->msg) {
	echo "<tr><td class=\"blank\"><br />";
	parse_msg($news->msg,"�","blank","1");
	echo "</td></tr>";
}
$news->msg="";

if ($cmd=="new_entry") {
	if ($auth->auth["perm"]=="dozent" OR $auth->auth["perm"]=="tutor") $news->search_range();
	$news->edit_news();

}

if (!$cmd OR $cmd=="show") {
	if ($news->sms)
		$news->send_sms();
	if ($perm->have_perm("tutor")) {
		if ($perm->have_perm("admin")) {
			echo"\n<tr><td class=\"blank\"><blockquote><br /><b>" . _("Bereichsauswahl") . "</b><br />&nbsp; </blockquote></td></tr>\n";
			echo "<tr><td class=\"blank\"><blockquote>";
			echo "<table width=\"50%\" cellspacing=0 cellpadding=2 border=0>";
			echo "<form action=\"".$news->p_self("cmd=search")."\" method=\"POST\">";
			echo "<tr><td class=\"steel1\">";
			echo "&nbsp; <font size=-1>" . _("Geben Sie einen Suchbegriff ein, um weitere Bereiche zu finden!") . "</font><br /><br />";
			echo "&nbsp; <INPUT TYPE=\"TEXT\" style=\"vertical-align:middle;\" name=\"search\" size=\"20\">&nbsp;&nbsp;";
			echo "<input type=\"IMAGE\" style=\"vertical-align:middle;\" name=\"submit\" " . makeButton("suchestarten","src") . tooltip( _("Suche starten")) ." border=\"0\">";
			echo "</td></tr></form></table>\n";
			echo "</blockquote>";
			echo "</td></tr>";
		} else
			$news->search_range("blah");
		echo "\n<tr><td class=\"blank\"><blockquote>";
		if ($perm->have_perm("admin"))
		echo "<hr>";
		echo "<br /><b>" . _("verf&uuml;gbare Bereiche");
		echo "</b></blockquote></td></tr>\n ";
		$typen = array("user"=>_("Benutzer"),"sem"=>_("Veranstaltung"),"inst"=>_("Einrichtung"),"fak"=>_("Fakult&auml;t"));
		$my_cols=3;
		if ($perm->have_perm("tutor")){
			echo "\n<tr><td class=\"blank\"><blockquote>";
			echo "<font size=\"-1\" style=\"vertical-align:middle;\">" . _("Sie k&ouml;nnen&nbsp; <b>Pers&ouml;nliche News</b> bearbeiten") . "</font>&nbsp;";
			echo "<a href=\"".$news->p_self("range_id=$user->id")."\">&nbsp; <img style=\"vertical-align:middle;\" " . makeButton("bearbeiten","src") . tooltip(_("Pers�nliche News bearbeiten")) ." border=\"0\"></a>";
		}
		if ($perm->have_perm("root")) {
			$my_cols=4;
			echo "<font size=\"-1\" style=\"vertical-align:middle;\">&nbsp; " . _("<i>oder</i> <b>Systemweite News</b> bearbeiten") . "</font>&nbsp;";
			echo "<a href=\"".$news->p_self("range_id=studip")."\">&nbsp;<img style=\"vertical-align:middle;\" " . makeButton("bearbeiten","src") . tooltip(_("Systemweite News bearbeiten")) ." border=\"0\"></a>";
		}
		if ($news->search_result)
			echo "<br><br><font size=\"-1\" style=\"vertical-align:middle;\">" . _("<i>oder</i> <b>hier</b> einen der gefundenen Bereiche ausw&auml;hlen:") . "&nbsp;</font>";

		if ($perm->have_perm("tutor"))
			echo "</blockquote></td></tr>";

		if ($news->search_result) {
			echo "\n<tr><td width=\"100%\" class=\"blank\"><blockquote>";
			echo "<table width=\"".round(0.88*$news->xres)."\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">";
			$css = new CssClassSwitcher(array("steel1","steel1"));
			$css->hoverenabled = TRUE;
			$css->switchClass();
			while (list($typen_key,$typen_value)=each ($typen)) {
				if (!$perm->have_perm("root") AND $typen_key=="user")
					continue;
				echo "\n<td class=\"steel1\" width=\"".floor(100/$my_cols)."%\" align=\"center\" valign=\"top\"><b>$typen_value</b><br><font size=\"-1\">";
				reset($news->search_result);
				while (list ($range,$details) = each ($news->search_result)) {
					if ($details["type"]==$typen_key) {
						echo "\n<div ".$css->getHover()."><a href=\"".$news->p_self("range_id=$range&view_mode=$typen_key")."\">".htmlReady($details["name"]);
						echo ($details["anzahl"]) ? " (".$details["anzahl"].")" : " (0)";
						echo "</a></div>";
					}
				}
				echo "\n</font></td>";
			}
			echo"\n</table></blockquote></td></tr>";
		}
	}
	echo "\n<tr><td class=\"blank\"><br /><blockquote>";
	echo "<form action=\"".$news->p_self("cmd=new_entry&range_id=$news_range_id&view_mode=$view_mode")."\" method=\"POST\">";
	echo "<hr width=\"100%\"><br /><b>" . _("gew&auml;hlter Bereich:") . " </b>".htmlReady($news_range_name). "<br /><br />";
	if (get_config('NEWS_RSS_EXPORT_ENABLE') && $news->get_news_range_perm($news_range_id) > 1){
		echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/rss.gif\" border=\"0\" align=\"absmiddle\">&nbsp;";
		echo "\n<font size=\"-1\" style=\"vertical-align:middle;\">" . _("Die News des gew&auml;hlten Bereiches als RSS-feed zur Verf&uuml;gung stellen") . "</font>&nbsp;";
		vprintf("\n<input type=\"image\" src=\"".$GLOBALS['ASSETS_URL']."images/%s\" %s border=\"0\" name=\"change_rss\" align=\"absmiddle\"/>",
				(StudipNews::GetRssIdFromRangeId($news_range_id) ? array('haken.gif',tooltip(_("RSS Export ist eingeschaltet"))) : array('x2.gif',tooltip(_("RSS Export ist ausgeschaltet")))));
		echo "\n<br><br>";
	}
	echo "\n<font size=\"-1\" style=\"vertical-align:middle;\">" . _("Eine neue News im gew&auml;hlten Bereich erstellen") . "</font>&nbsp;";
	echo makeButton('erstellen', 'input', _("Eine neue News erstellen"), 'new_entry');
	echo "</b>\n</blockquote>\n</form>\n</td>\n</tr>\n ";
	if (!$news->show_news($news_range_id)) {
		echo "\n<tr><td class=\"blank\"><blockquote>";
		echo "<font size=\"-1\" style=\"vertical-align:middle;\">" . _("Im gew&auml;hlten Bereich sind keine News vorhanden!") . "<br><br>";
		echo "</blockquote></td></tr>";
	}
}
echo"\n</table>";
include ('html_end.inc.php');
page_close();
?>