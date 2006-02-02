<?
/**
* my_rss_feed.inc.php
*
* RSSFeed configuration
*
*
* @author               Jan Kulmann <jankul@tzi.de>
* @version              $Id$
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RSSFeed.class.php
// Copyright (C) 2005 Jan Kulmann <jankul@tzi.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

require_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . "lib/classes/RSSFeed.class.php";

function print_rss($username) {	
	
	global $view,$PHP_SELF,$auth, $ABSOLUTE_PATH_STUDIP;
	
	$db=new DB_Seminar;
	$cssSw=new cssClassSwitcher;

	$cssSw->switchClass();
	
	$db->query(sprintf("SELECT * FROM auth_user_md5 a, rss_feeds r WHERE a.username='%s' AND a.user_id=r.user_id ORDER BY r.priority",$username));
	echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>";
	echo _("Hier k�nnen Sie beliebige eigene RSS Feeds einbinden. Diese RSS Feeds erscheinen auf Ihrer pers&ouml;nlichen Startseite. Mit den Pfeilsymbolen k&ouml;nnen sie die Reihenfolge, in der die RSS Feeds angezeigt werden, ver&auml;ndern.");
	echo "<br>\n";
	echo _("<B>Achtung:</B> Je mehr RSS Feeds Sie definieren, desto l&auml;nger ist die Ladezeit der Startseite f&uuml;r Sie!");
	echo "<br>\n";

	echo "\n<br></blockquote></td></tr>\n<tr><td class=blank><table width=100% class=blank border=0 cellpadding=0 cellspacing=0>";
	echo "<form action=\"$PHP_SELF?rss=update_rss&username=$username&view=$view&show_rss_bsp=$show_rss_bsp\" method=\"POST\" name=\"edit_rss\">";
	if (!$db->num_rows())
		echo "<tr><td class=\"".$cssSw->getClass()."\"><font size=-1><b><blockquote>" . _("Es existieren zur Zeit keine eigenen RSS Feeds.") . "</blockquote></b></font></blockquote></td></tr>\n";
	echo "<tr><td class=\"".$cssSw->getClass()."\"><blockquote>" . _("RSS Feed") . "&nbsp; <a href='$PHP_SELF?rss=create_rss&view=$view&username=$username&show_rss_bsp=$show_rss_bsp'>" . makeButton("neuanlegen") . "</a></blockquote></td></tr>";
	$count = 0;
	while ($db->next_record() ){
		
			$cssSw->switchClass();
			$id = $db->f("feed_id");
			echo "<tr><td class=\"".$cssSw->getClass()."\">";
			if ($count)
				echo "<br />";
			echo "<input type=\"hidden\" name=\"rss_id[]\" value=\"".$db->f("feed_id")."\">\n";
			echo "<blockquote>Name:<BR><input type='text' name='rss_name[]' style=\"width: 50%\" value='".htmlReady($db->f("name"))."' size=40>";
			echo "&nbsp; &nbsp; &nbsp; <input type=checkbox name='rss_fetch_title[$count]' value='1'";
            IF ($db->f("fetch_title")=='1') echo " checked";
            echo ">" . _("Name des Feeds holen") . "&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;";
			if ($count){
				echo "\n<a href=\"$PHP_SELF?rss=order_rss&direction=up&username=$username&view=$view&cat_id=" . $db->f("feed_id")
				. "&show_rss_bsp=$show_rss_bsp\"><img src=\"pictures/move_up.gif\" hspace=\"4\" width=\"13\" height=\"11\" border=\"0\" " 
				. tooltip(_("RSS Feed nach oben verschieben")) ."></a>";
			}
			if ($count != ($db->num_rows()-1) ){
				echo "\n<a href=\"$PHP_SELF?rss=order_rss&direction=down&username=$username&view=$view&cat_id=" . $db->f("feed_id")
				. "&show_rss_bsp=$show_rss_bsp\"><img src=\"pictures/move_down.gif\" hspace=\"4\" width=\"13\" height=\"11\" border=\"0\" " 
				. tooltip(_("Kategorie nach unten verschieben")) ."></a>";
			}
			echo "<br>&nbsp;</td></tr>";
			echo "<tr><td class=\"".$cssSw->getClass()."\"><blockquote>URL:<BR><input type='text' name='rss_url[]' style=\"width: 50%\" value='".htmlReady($db->f("url"))."' size=40>";
			echo "&nbsp; &nbsp; &nbsp; <input type=checkbox name='rss_secret[$count]' value='1'";
            IF ($db->f("hidden")=='1') echo " checked";
            echo ">" . _("unsichtbar") . "&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<br><br><input type='IMAGE' name='update' border=0 align=\"absmiddle\"" . makeButton("uebernehmen", "src") . " value='" . _("ver&auml;ndern") . "'>";
			echo "&nbsp;<a href='$PHP_SELF?rss=delete_rss&rss_id=$id&view=$view&username=$username&show_rss_bsp=$show_rss_bsp'>";
			echo makeButton("loeschen") . "</a><br />&nbsp; </td></tr>";
			$count++;
	}
	echo "</form></td></tr></table></td></tr>";
}

function create_rss() {
	global $username;

	$db=new DB_Seminar;
	$now = time();
	$feed_id=md5(uniqid("blablubburegds4"));
	$db->query ("SELECT user_id FROM auth_user_md5 WHERE username = '$username'");
	$db->next_record();
	$user_id = $db->f("user_id");
	$db->query("UPDATE rss_feeds SET priority=priority+1 WHERE user_id='$user_id'");
	$db->query("INSERT INTO rss_feeds (feed_id,name, url, mkdate, chdate, user_id,priority,fetch_title) VALUES ('$feed_id','" . _("neuer Feed") . "','" . _("URL") . "','$now','$now','$user_id',0,1)");
	if ($db->affected_rows() == 0) {
		parse_msg ("info�" . _("Anlegen fehlgeschlagen"));
		die;
	}
}

function delete_rss($rss_id) {
	global $username;
	
	$db=new DB_Seminar;
	$db->query ("SELECT * FROM rss_feeds LEFT JOIN auth_user_md5 USING (user_id) WHERE username = '$username' and feed_id='$rss_id'");
	if (!$db->next_record()) { //hier wollte jemand schummeln
		parse_msg ("info�" . _("Netter Versuch, vielleicht beim n&auml;chsten Mal!"));
		die;
	} else {
		$db->query("DELETE FROM rss_feeds WHERE feed_id='$rss_id'");
		if ($db->affected_rows() == 1) {
			parse_msg ("msg�" . _("RSS Feed gel&ouml;scht!"));
		}
	}
}

function update_rss() {
	global $rss_id,$rss_name,$rss_url, $rss_secret, $rss_fetch_title;
	check_rss();
	$max = sizeof($rss_id);
	FOR ($i=0;$i<$max;$i++) {
		if (trim($rss_name[$i])!="" && trim($rss_url[$i])) {
			$now = time();
			$db=new DB_Seminar;
			$name = $rss_name[$i];
			$url = $rss_url[$i];
			$secret=$rss_secret[$i];
			$id = $rss_id[$i];
			$fetch_title = $rss_fetch_title[$i];
			$db->query("UPDATE rss_feeds SET name='$name', url='$url', hidden='$secret',fetch_title='$fetch_title', chdate='$now' WHERE feed_id='$id'");
		}
	}
	//parse_msg ("msg�" . _("RSS Feeds ge&auml;ndert!"));
	$msg[] = array('msg', ("RSS Feeds ge&auml;ndert!"));
	parse_msg_array ($msg,'blank',2,0,1);
}

function check_rss() {
	global $rss_id,$rss_name,$rss_url, $rss_secret, $rss_fetch_title;
	define('MAGPIE_CACHE_AGE',1);
	$max = sizeof($rss_id);
	$msg = array();
	FOR ($i=0;$i<$max;$i++) {
		if (trim($rss_url[$i])) {
			$feed = new RSSFeed($rss_url[$i]);
			if ($feed->ausgabe->feed_type){
				if($rss_fetch_title[$i] && $feed->ausgabe->channel['title']) $rss_name[$i] = addslashes($feed->ausgabe->channel['title']);
				$msg[] = array('msg', sprintf(_("Feed: <b>%s</b> (Typ: %s) erreicht."), htmlReady($rss_url[$i]), htmlReady($feed->ausgabe->feed_type)));
			} else {
				$rss_secret[$i] = 1;
				$msg[] = array('error', sprintf(_("Feed: <b>%s</b> nicht erreicht, oder Typ nicht erkannt."), htmlReady($rss_url[$i])));
			}
		}
	}
	parse_msg_array ($msg,'blank',2,0,1);
}

function order_rss($cat_id,$direction,$username){
	$items_to_order = array();
	$user_id = get_userid($username);
	$db = new DB_Seminar("SELECT feed_id FROM rss_feeds WHERE user_id='$user_id' ORDER BY priority");
	while($db->next_record()) {
		$items_to_order[] = $db->f("feed_id");
	}
	for ($i = 0; $i < count($items_to_order); ++$i) {
		if ($cat_id == $items_to_order[$i])
			break;
	}
	if ($direction == "up" && isset($items_to_order[$i-1])) {
		$items_to_order[$i] = $items_to_order[$i-1];
		$items_to_order[$i-1] = $cat_id;
	} elseif (isset($items_to_order[$i+1])) {
		$items_to_order[$i] = $items_to_order[$i+1];
		$items_to_order[$i+1] = $cat_id;
	}
	for ($i = 0; $i < count($items_to_order); ++$i) {
		$db->query("UPDATE rss_feeds SET priority=$i WHERE feed_id='$items_to_order[$i]'");
	}
	//parse_msg("msg�" . _("RSS Feeds wurden neu geordnet"));
	$msg[] = array('msg', ("RSS Feeds wurden neu geordnet"));
	parse_msg_array ($msg,'blank',2,0,1);
}

?>
