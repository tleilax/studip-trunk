<?
/* vim: noexpandtab */
/**
* several functions used for the systeminternal messages
*
* @author				Nils K. Windisch <studip@nkwindisch.de>
* @access				public
* @modulegroup	Messaging
* @module				sms_functions.inc.php
* @package			Stud.IP Core
*/
// $Id$
/*
sms_functions.inc.php -
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Nils K. Windisch <info@nkwindisch.de>

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

require_once 'lib/classes/Avatar.class.php';

/**
 * returns the key from a val
 *
 * @author			Nils K. Windisch <studip@nkwindisch.de>
 * @access			private
 */

function return_key_from_val($array, $val) {
	return array_search($val, $array);
}

/**
 * returns the val from a key
 *
 *
 * @author			Nils K. Windisch <studip@nkwindisch.de>
 * @access			private
 */

function return_val_from_key($array, $key) {
	return $array[$key];
}

function MessageIcon($message_hovericon) {
	global $my_messaging_settings, $PHP_SELF, $auth, $forum;
	if ($auth->auth["jscript"] AND $message_hovericon["content"]!="" && $message_hovericon["openclose"]=="close" &&  $forum["jshover"] == "1") {
		$hovericon = "<a href=\"javascript:void(0);\" "
			."onMouseOver=\"return overlib('"
			.JSReady($message_hovericon["content"], "forum")
			."', CAPTION, '&nbsp;"
			.JSReady($message_hovericon["titel"])
			."', NOCLOSE, CSSOFF)\" "
			." onMouseOut=\"nd();\"><img src=\"".$GLOBALS['ASSETS_URL']."images/".$message_hovericon["picture"]."\" border=0></a>";
	} else {
		$hovericon = "<a href=\"".$message_hovericon['link']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/".$message_hovericon["picture"]."\" border=0></a>";
	}
	return $hovericon;
}

function count_x_messages_from_user($snd_rec, $folder, $where="") {
	global $user;
	$db = new DB_Seminar();
	if ($snd_rec == "in" || $snd_rec == "out") {
		if ($snd_rec == "in") {
			$tmp_snd_rec = "rec";
		} else {
			$tmp_snd_rec = "snd";
		}
	} else {
		$tmp_snd_rec = $snd_rec;
	}
	$user_id = $user->id;
	if ($folder == "all") {
		$folder_query = "";
	} else {
		$folder_query = " AND message_user.folder = " . $folder;
	}
	$query = "SELECT COUNT(*)
		FROM message_user
		WHERE message_user.snd_rec = '".$tmp_snd_rec."'
			AND message_user.user_id = '".$user_id."'
			AND message_user.deleted = 0
			".$folder_query . $where;
	$db->query($query);
	$db->next_record();
	return $db->f(0);
}

function count_messages_from_user($snd_rec, $where="") {
	global  $user;
	$db = new DB_Seminar();
	if ($snd_rec == "in" || $snd_rec == "out") {
		if ($snd_rec == "in") {
			$tmp_snd_rec = "rec";
		} else {
			$tmp_snd_rec = "snd";
		}
	} else {
		$tmp_snd_rec = $snd_rec;
	}
	$user_id = $user->id;
	$query = "SELECT COUNT(*)
		FROM message_user
		WHERE snd_rec = '".$tmp_snd_rec."'
			AND user_id = '".$user_id."'
			AND deleted = 0
			".$where;
	$db->query($query);
	$db->next_record();
	return $db->f(0);

}


function show_icon($sms_show, $value) {
	if ($sms_show == $value) {
		$x = "forum_indikator_gelb2.gif";
	} else {
		$x = "blank.gif";
	}
	return $x;
}

function showfoldericon($tmp, $count) {
	global $sms_show, $sms_data, $PHP_SELF;
	if ($count == "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "close") {
		$picture = "cont_folder2.gif";
	} else if ($count == "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "open") {
		$picture = "cont_folder4.gif";
	} else if ($count != "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "close") {
		$picture = "cont_folder.gif";
	} else if ($count != "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "open") {
		$picture = "cont_folder3.gif";
	}
	return $picture;
}

function folder_makelink($tmp) {
	global $sms_show, $sms_data, $PHP_SELF;
	if (folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "open") {
		$link = $PHP_SELF."?show_folder=close";
	} else {
		$link = $PHP_SELF."?show_folder=".$tmp;
	}
	return $link;
}

function folder_openclose($folder, $x) {
	if ($folder == $x) {
		$tmp = "open";
	} else {
		$tmp = "close";
	}
	return $tmp;
}

// print_snd_message
function print_snd_message($psm) {
	global $n, $LastLogin, $my_messaging_settings, $cmd, $db7, $PHP_SELF, $msging, $cmd_show, $sms_data, $_fullname_sql, $user;

	// open?!
	if ($sms_data["open"] == $psm['message_id']) {
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($cmd_show == "openall" || $my_messaging_settings["openall"] == "1") {
		$open = "open";
		$link = $PHP_SELF."?mopen=".$psm['message_id']."#".$psm['message_id'];
	} else {
		$open = "close";
		$link = $PHP_SELF."?mopen=".$psm['message_id']."#".$psm['message_id'];
	}

	// make message_header
	$x = $psm['num_rec']; // how many receivers are there?
	if ($psm['dont_delete'] == "1") { // disable the checkbox if message is locked
		$tmp_cmd = "open_selected";
		$tmp_picture = "closelock2";
		$tmp_tooltip = tooltip(_("L�schschutz deaktivieren."));
		$trash = "<img src=\"".$GLOBALS['ASSETS_URL']."images/trash2no.gif\" border=0 ".tooltip(_("Diese Nachricht kann momentan nicht gel�scht werden.")).">";
	} else {
		$tmp_cmd = "safe_selected";
		$tmp_picture = "openlock2";
		$tmp_tooltip = tooltip(_("L�schschutz f�r diese Nachricht aktivieren."));
		$trash = "<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_sms[1]=".$psm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash2.gif\" border=0 ".tooltip(_("Diese Nachricht l�schen."))."></a>";
	}

	$zusatz = "<font size=-1>";
	if ($x == 1) { // if only one receiver
		$zusatz .= sprintf(_("an %s, %s"), "</font><a href=\"about.php?username=".$psm['rec_uname']."\"><font size=-1 color=\"#333399\">".htmlReady($psm['rec_vorname'])."&nbsp;".htmlReady($psm['rec_nachname'])."</font></a><font size=-1>", date("d.m.y, H:i",$psm['mkdate']));
		$zusatz .= "&nbsp;";
		if (have_msgfolder($sms_data['view']) == TRUE) {
			$zusatz .= "<a href=\"".$PHP_SELF."?move_to_folder[1]=".$psm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/cont_folder_sms_move.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei w�hlbaren Ordner verschieben."))."></a>";
		}
		$zusatz .= "<a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$psm['message_id']."#".$psm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"2\">".$trash."<input type=\"checkbox\" name=\"sel_sms[]\" value=\"".$psm['message_id']."\" ".CheckChecked($cmd, "select_all").">";
	} else if ($x >= "2") { // if more than one receiver
		$zusatz .= sprintf(_("an %s Empf&auml;nger, %s"), $x, date("d.m.y, H:i",$psm['mkdate']));
		$zusatz .= "&nbsp;";
		if (have_msgfolder($sms_data['view']) == TRUE) {
			$zusatz .= "<a href=\"".$PHP_SELF."?move_to_folder[1]=".$psm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/cont_folder_sms_move.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei w�hlbaren Ordner verschieben."))."></a>";
		}
		$zusatz .= "<a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$psm['message_id']."#".$psm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"2\">".$trash."<input type=\"checkbox\" name=\"sel_sms[]\" value=\"".$psm['message_id']."\" ".CheckChecked($cmd, "select_all").">";
	}
	$zusatz .= "</font>";

	if ($open == "open") {
		$content = quotes_decode(formatReady($psm['message']));
		if ($x >= 2) { // if more than one receiver add appendix
			$content .= "<br><br>--<br>"._("gesendet an:")."<br>";
			$query = "
			SELECT  auth_user_md5.username, " .$_fullname_sql['full'] ." AS fullname
				FROM message_user
				LEFT JOIN auth_user_md5 USING(user_id)
				LEFT JOIN user_info USING(user_id)
				WHERE message_user.message_id = '".$psm['message_id']."'
				AND message_user.snd_rec = 'rec'";
			$db7->query($query);
			$i = 0;
			while ($db7->next_record()) {
				if ($db7->f("user_id") != $user->id && $db7->f("username") != "") {
					if ($i > "0") {
						$content .= ",&nbsp;";
					}
					$content .= "<a href=\"about.php?username=".$db7->f("username")."\"><font size=-1 color=\"#333399\">".htmlReady($db7->f("fullname"))."</font></a>";
					++$i;
				} else {
					$msg_sndnote = _("und an Sie selbst");
				}
			}
			if ($msg_sndnote) {
				$content .= "&nbsp;".$msg_sndnote;
				unset($msg_sndnote);
			}
		}

		// buttons
		$edit = "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_sms[1]=".$psm['message_id']."\" ".tooltip(_("Diese Nachricht l�schen.")).">".makeButton("loeschen", "img")."</a>&nbsp;";
		if (have_msgfolder($sms_data['view']) == TRUE) {
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder[1]=".$psm['message_id']."\" ".tooltip(_("Diese Nachricht in einen frei w�hlbaren Ordner verschieben.")).">".makeButton("verschieben", "img")."</a><br><br>";
		}
	}

	$titel = "<a name=".$psm['message_id']."><a href=\"$link\" class=\"tree\" >".htmlready($psm['message_subject'])."</a></a>";
	$message_hovericon['titel'] = $psm['message_subject'];
	// (hover) icon
	$message_hovericon['openclose'] = $open;
	$message_hovericon['content'] = $psm['message'];
	$message_hovericon['id'] = $psm['message_id'];
	$message_hovericon["picture"] = "cont_nachricht.gif";
	$icon = MessageIcon($message_hovericon);
	// print message_header
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	if ($psm['count'] == "0" || sizeof($sms_data['tmp']['move_to_folder']) == "1" || $psm['count_2'] == "0") {
		$tmp_line1 = "forumstrich2.gif";
		$tmp_line2 = "blank.gif";
	} else {
		$tmp_line1 = "forumstrich3.gif";
		$tmp_line2 = "forumstrich.gif";
	}
	echo "<td class=\"blank\"><img src=\"".$GLOBALS['ASSETS_URL']."images/".$tmp_line1."\"></td>";
	printhead(0, 0, $link, $open, FALSE, $icon, $titel, $zusatz, $psm['mkdate']);
	echo "</tr></table>	";
	// print content
	if (($open == "open") || ($psm['sms_data_open'] == $psm['message_id'])) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
		echo "<td class=\"blank\" background=\"".$GLOBALS['ASSETS_URL']."images/".$tmp_line2."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" height=\"100%\" width=\"10px\"></td>";
		printcontent("99%",0, $content, $edit);
		echo "</tr></table>	";
	}
	return $n++;
}

// print_rec_message
function print_rec_message($prm) {
	global $n, $LastLogin, $my_messaging_settings, $cmd, $PHP_SELF, $msging, $cmd_show, $sms_show, $sms_data, $user;
	// build
	if ($prm['readed'] != "1" && $my_messaging_settings["opennew"] == "1") { // open if unread
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($sms_data["open"] == $prm['message_id']) {
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($cmd_show == "openall" || $my_messaging_settings["openall"] == "1") {
		$open = "open";
		$link = $PHP_SELF."?mopen=".$prm['message_id']."#".$prm['message_id'];
	} else {
		$open = "close";
		$link = $PHP_SELF."?mopen=".$prm['message_id']."#".$prm['message_id'];
	}
	if ($prm['readed'] == "1") { // unread=new ... is message new? if new and opened=set readed
		$red = FALSE;
		if ($prm['answered'] == 1) {
			$picture = "cont_nachricht_pfeil.gif";
		} else {
			$picture = "cont_nachricht.gif";
		}
	} else {
		$red = TRUE;
		$picture = "cont_nachricht_rot.gif";
		if ($open == "open") $msging->set_read_message($prm['message_id']);
	}
	if ($prm['dont_delete'] == "1") { // disable the checkbox if message is locked
		$tmp_cmd = "open_selected";
		$tmp_picture = "closelock2";
		$tmp_tooltip = tooltip(_("L�schschutz deaktivieren."));
		$trash = "<img src=\"".$GLOBALS['ASSETS_URL']."images/trash2no.gif\" border=0 ".tooltip(_("Diese Nachricht kann momentan nicht gel�scht werden.")).">";
	} else {
		$tmp_cmd = "safe_selected";
		$tmp_picture = "openlock2";
		$tmp_tooltip = tooltip(_("L�schschutz f�r diese Nachricht aktivieren."));
		$trash = "<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_sms[1]=".$prm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash2.gif\" border=0 ".tooltip(_("Diese Nachricht l�schen."))."></a>";
	}
	// zusatz
	if (have_msgfolder($sms_data['view']) == TRUE) {
		$move_option = "<a href=\"".$PHP_SELF."?move_to_folder[1]=".$prm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/cont_folder_sms_move.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei w�hlbaren Ordner verschieben."))."></a>";
	}
	$zusatz = "<font size=-1>";
	if ($prm['user_id_snd'] == "____%system%____") {
		$zusatz .= _("automatische Systemnachricht, ");
	} else {
		$zusatz .= sprintf(_("von %s, "), "</font><a href=\"about.php?username=".$prm['uname_snd']."\"><font size=-1 color=\"#333399\">".htmlReady($prm['vorname'])."&nbsp;".htmlReady($prm['nachname'])."</font></a><font size=-1>");
	}
	$zusatz .= date("d.m.y, H:i", $prm['mkdate']);
	$zusatz .= "&nbsp;".$move_option."<a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$prm['message_id']."#".$prm['message_id']."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"2\">".$trash."<input type=\"checkbox\" name=\"sel_sms[]\" value=\"".$prm['message_id']."\" ".CheckChecked($cmd, "select_all").">";
	$zusatz .= "</font>";

	$titel = "<a name=".$prm['message_id']."><a href=\"$link\" class=\"tree\" >".htmlready($prm['message_subject'])."</a></a>";

	if ($open == 'open'){
		$content = quotes_decode(formatReady($prm['message']));
		if ($my_messaging_settings["confirm_reading"] != 1 && $prm['message_reading_confirmation'] == 1) { // yeah i'm interested in readingconfirmations and the message has a readingrequested
			if ($my_messaging_settings["confirm_reading"] == 3 && $prm['confirmed_read'] != 1) { // let me decided what to do
				$content .= "<br>--<br>"._("Der Absender / Die Absenderin hat eine Lesebest�tigung angefordert.");
				$content .= "<br><a href=\"".$PHP_SELF."?readingconfirmation=".$prm['message_id']."&uname_snd=".$prm['uname_snd']."#".$prm['message_id']."\">"._("Klicken Sie hier um das Lesen der Nachricht zu best�tigen")."</a>";
			} else if ($my_messaging_settings["confirm_reading"] == 2 && $prm['confirmed_read'] != 1) { // automatic confirm my reading and don't nag me
				$dbX = new DB_Seminar;
				$user_id = $user->id;
				$user_fullname = get_fullname($user_id);
				$query = "
					UPDATE message_user SET
						confirmed_read = '1'
						WHERE message_id = '".$prm['message_id']."'
							AND user_id = '".$user_id."'";
				if($dbX->query($query)) {
					$subject = sprintf (_("Lesebest�tigung von %s"), $user_fullname);
					$message = sprintf (_("Ihre Nachricht an %s mit dem Betreff: %s vom %s wurde gelesen."), "%%".$user_fullname."%%", "%%".$prm['message_subject']."%%", "%%".date("d.m.y, H:i", $prm['mkdate'])."%%");
					$msging->insert_message(mysql_escape_string($message), $prm['uname_snd'], "____%system%____", FALSE, FALSE, 1, FALSE, mysql_escape_string($subject));
				}
			}
		}

		if ($my_messaging_settings['show_sndpicture'] == 1) {
			$tmp_snd_id = get_userid($prm['uname_snd']);
			if ($prm['user_id_snd'] != '____%system%____') {
				$content = "<table width=\"100%\" cellpadding=0 cellmargin=0><tr><td valign=\"top\" width=\"99%\"><font size=\"-1\">".$content."</font><td>";
				$content .= "<td align=\"right\" style=\"border-left: 1px dotted black;\">&nbsp;";
				$avatar = new Avatar($tmp_snd_id);
				$content .= $avatar->getImageTag(Avatar::MEDIUM);
				$content .= "&nbsp;</td></tr></table>";
			}
		}

		// mk buttons
		$edit = "";
		if ($prm['user_id_snd'] != "____%system%____") {
			$edit .= "<a href=\"sms_send.php?cmd=write&answer_to=".$prm['message_id']."\">".makeButton("antworten", "img")."</a>";
			$edit .= "&nbsp;<a href=\"sms_send.php?cmd=write&quote=".$prm['message_id']."&answer_to=".$prm['message_id']."\">".makeButton("zitieren", "img")."</a>";
		}
		$edit.= "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_sms[1]=".$prm['message_id']."\">".makeButton("loeschen", "img")."</a>";
		if (have_msgfolder($sms_data['view']) == TRUE) {
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder[1]=".$prm['message_id']."\" ".tooltip(_("Diese Nachricht in einen frei w�hlbaren Ordner verschieben.")).">".makeButton("verschieben", "img")."</a><br><br>";
		}
	}
	// (hover) icon
	$message_hovericon['titel'] = $prm['message_subject'];
	$message_hovericon['openclose'] = $open;
	$message_hovericon['content'] = $prm['message'];
	$message_hovericon['id'] = $prm['message_id'];
	$message_hovericon['link'] = $link;
	$message_hovericon["picture"] = $picture;
	$icon = MessageIcon($message_hovericon);
	// print message_header
	if ($prm['count'] <= "0" || sizeof($sms_data['tmp']['move_to_folder'])== "1" || $prm['count_2'] == "0") {
		$tmp_line1 = "forumstrich2.gif";
		$tmp_line2 = "blank.gif";
	} else {
		$tmp_line1 = "forumstrich3.gif";
		$tmp_line2 = "forumstrich.gif";
	}
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	echo "<td class=\"blank\"><img src=\"".$GLOBALS['ASSETS_URL']."images/".$tmp_line1."\"></td>";
	printhead(0, 0, $link, $open, $red, $icon, $titel, $zusatz, $prm['mkdate']);
	echo "</tr></table>	";
	// print message content
	if (($open == "open") || ($sms_data["open"] == $prm['message_id'])) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
		echo "<td class=\"blank\" background=\"".$GLOBALS['ASSETS_URL']."images/".$tmp_line2."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" height=\"100%\" width=\"10px\"></td>";
		printcontent("99%", 0, $content, $edit);
		echo "</tr></table>	";
	}
	return $n++;
}

function print_messages() {
	global $user, $my_messaging_settings, $PHP_SELF ,$sms_data, $sms_show, $query_showfolder, $query_time_sort, $query_movetofolder, $query_time, $_fullname_sql, $srch_result, $no_message_text, $n, $count, $count_timefilter;
	$db = new DB_Seminar();
	if ($query_time) $count = $count_timefilter;
	$n = 0;
	$user_id = $user->id;
	if ($sms_data['view'] == "in") { // postbox in
		$query = "SELECT message.*, folder,confirmed_read,answered,message_user.readed,dont_delete,Vorname,Nachname,username FROM message_user
				LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id)
				WHERE message_user.user_id = '".$user_id."' AND message_user.snd_rec = 'rec'
				AND message_user.deleted = 0 ".$query_movetofolder." ".$query_showfolder." ".$query_time;
		$db->query($query);
		$tmp_move_to_folder = sizeof($sms_data['tmp']['move_to_folder']);
		while ($db->next_record()) {
			--$count;
			$prm['count'] = $count;
			$prm['count_2'] = $tmp_move_to_folder - ($n+1);
			$prm['user_id_snd'] = $db->f("autor_id");
			$prm['folder'] = $my_messaging_settings['folder']['active']['in'];
			$prm['mkdate'] = $db->f("mkdate");
			$prm['message_id'] = $db->f("message_id");
			$prm['message_subject'] = $db->f("subject");
			$prm['message_reading_confirmation'] = $db->f("reading_confirmation");
			$prm['confirmed_read'] = $db->f("confirmed_read");
			$prm['answered'] = $db->f("answered");
			$prm['message'] = $db->f("message");
			$prm['vorname'] = $db->f("Vorname");
			$prm['nachname'] = $db->f("Nachname");
			$prm['readed'] = $db->f("readed");
			$prm['dont_delete'] = $db->f("dont_delete");
			$prm['uname_snd'] = $db->f("username");
			ob_start();
			print_rec_message($prm);
			ob_end_flush();
		}
	} else if ($sms_data['view'] == "out") { // postbox out
		$db->query("SELECT message. * , message_user.folder,message_user.dont_delete , auth_user_md5.user_id AS rec_uid,
					auth_user_md5.vorname AS rec_vorname, auth_user_md5.nachname AS rec_nachname,
					auth_user_md5.username AS rec_uname, count( mu.message_id )  AS num_rec
					FROM message_user
					LEFT  JOIN message_user AS mu ON ( message_user.message_id = mu.message_id AND mu.snd_rec =  'rec'  )
					LEFT  JOIN message ON ( message.message_id = message_user.message_id )
					LEFT  JOIN auth_user_md5 ON ( mu.user_id = auth_user_md5.user_id )
					WHERE message_user.user_id = '".$user_id."'
					AND message_user.snd_rec = 'snd' AND message_user.deleted = 0 "
					.$query_movetofolder." ".$query_showfolder. $query_time_sort . " GROUP BY (message_user.message_id) ORDER BY message_user.mkdate DESC");
		$tmp_move_to_folder = sizeof($sms_data['tmp']['move_to_folder']);
		while ($db->next_record()) {
			--$count;
			$psm['count'] = $count;
			$psm['count_2'] = $tmp_move_to_folder - ($n+1);
			$psm['mkdate'] = $db->f("mkdate");
			$psm['folder'] = $my_messaging_settings['folder']['active']['out'];
			$psm['message_id'] = $db->f("message_id");
			$psm['message_subject'] = $db->f("subject");
			$psm['message'] = $db->f("message");
			$psm['dont_delete'] = $db->f("dont_delete");
			$psm['rec_uid'] = $db->f("rec_uid");
			$psm['rec_vorname'] = $db->f("rec_vorname");
			$psm['rec_nachname'] = $db->f("rec_nachname");
			$psm['rec_uname'] = $db->f("rec_uname");
			$psm['num_rec'] = $db->f("num_rec");
			ob_start();
			print_snd_message($psm);
			ob_end_flush();
		}
	}
	if (!$n) { // wenn keine nachrichten zum anzeigen
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\">";
		$srch_result = "info�<font size=-1><b>".$no_message_text."</b></font>";
		parse_msg ($srch_result, "�", "steel1", 2, FALSE);
		echo "</td></tr></table>";
	}
}

function show_nachrichtencount($count, $count_timefilter) {
	if ($count == "0") {
		$zusatz = _("keine Nachrichten");
	} else {
		$zusatz = sprintf(_("%s von %s Nachrichten"), $count_timefilter, $count);
	}
	return $zusatz;
}

function have_msgfolder($view) {
	global $my_messaging_settings;
	static $have_folder = null;
	if (isset($have_folder[$view])) return $have_folder[$view];
	$dummies = array_unique($my_messaging_settings["folder"][$view]);
	if (sizeof($dummies) == 1 && $dummies[0] == 'dummy') {
		return ($have_folder[$view] = false);
	} else {
		return ($have_folder[$view] = true);
	}
}

// checkt ob alle adressbuchmitglieder in der empaengerliste stehen
function CheckAllAdded($adresses_array, $rec_array) {

	$x = sizeof($adresses_array);
	if (!empty($rec_array)) {
		foreach ($rec_array as $a) {
			if (in_array($a, $adresses_array)) {
				$x = ($x-1);
			}
		}
	}
	if ($x != "0") {
		return FALSE;
	} else {
		return TRUE;
	}

}

///////////////////////////////////////////////////////////////////////

function show_precform() {

	global $PHP_SELF, $sms_data, $user, $my_messaging_settings;

	if ($my_messaging_settings["send_view"] == "1") {
		$tmp_01 = sizeof($sms_data["p_rec"]);
		if (sizeof($sms_data["p_rec"]) >= "12") { $tmp_01 = "12"; }
	} else {
		$tmp_01 = "5";
	}

	$tmp =  "";

	if (sizeof($sms_data["p_rec"]) == "0") {
		$tmp .= "<font size=\"-1\">"._("Bitte w&auml;hlen Sie mindestens einen Empf&auml;nger aus.")."</font>";
	} else {
		$tmp .= "<select size=\"$tmp_01\" name=\"del_receiver[]\" multiple style=\"width: 250\">";
		if ($sms_data["p_rec"]) {
			foreach ($sms_data["p_rec"] as $a) {
				$tmp .= "<option value=\"$a\">".get_fullname_from_uname($a,'full',true)."</option>";
			}
		}
		$tmp .= "</select><br>";
		$tmp .= "<input type=\"image\" name=\"del_receiver_button\" src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("l�scht alle ausgew�hlten Empf�ngerInnen"))." border=\"0\">";
		$tmp .= " <font size=\"-1\">"._("ausgew&auml;hlte l&ouml;schen")."</font><br>";
		$tmp .= "<input type=\"image\" name=\"del_allreceiver_button\" src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("Empf&auml;ngerliste leeren"))." border=\"0\">";
		$tmp .= " <font size=\"-1\">"._("Empf&auml;ngerliste leeren")."</font>";
	}

	return $tmp;

}


function show_addrform() {

	global $PHP_SELF, $sms_data, $user, $db, $_fullname_sql, $adresses_array, $search_exp, $my_messaging_settings;

	if ($my_messaging_settings["send_view"] == "1") {
		$picture = "move_up.gif";
	} else {
		$picture = "move_left.gif";
	}

	// list of adresses
	$query_for_adresses = "SELECT contact.user_id, username, ".$_fullname_sql['full_rev']." AS fullname FROM contact LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id) WHERE owner_id = '".$user->id."' ORDER BY Nachname ASC";
	$db->query($query_for_adresses);
	while ($db->next_record()) {
		$adresses_array[] = $db->f("username");
	}

	$tmp = "<b><font size=\"-1\">"._("Adressbuch-Liste:")."</font></b><br>";

	if (empty($adresses_array)) { // user with no adress-members at all

		$tmp .= sprintf("<font size=\"-1\">"._("Sie haben noch keine Personen in ihrem Adressbuch. %s Klicken sie %s hier %s um dorthin zu gelangen.")."</font>", "<br>", "<a href=\"contact.php\">", "</a>");

	} else if (!empty($adresses_array)) { // test if all adresses are added?

		if (CheckAllAdded($adresses_array, $sms_data["p_rec"]) == TRUE) { // all adresses already added
			$tmp .= sprintf("<font size=\"-1\">"._("Bereits alle Personen des Adressbuchs hinzugef&uuml;gt!")."</font>");
		} else { // show adresses-select
			$tmp_count = "0";
			$db->query($query_for_adresses);
			while ($db->next_record()) {
				if (empty($sms_data["p_rec"])) {
					$tmp_02 .= "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
					$tmp_count = ($tmp_count+1);
				} else {
					if (!in_array($db->f("username"), $sms_data["p_rec"])) {
						$tmp_02 .= "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
						$tmp_count = ($tmp_count+1);
					}
				}
			}

			if ($my_messaging_settings["send_view"] == "1") {
				$tmp_01 = $tmp_count;
				if ($tmp_count >= "12") { $tmp_01 = "12"; }
			} else {
				$tmp_01 = "3";
			}
			$tmp .= "<select size=\"".$tmp_01."\" name=\"add_receiver[]\" multiple style=\"width: 250\">";
			$tmp .= $tmp_02;
			$tmp .= "</select><br>";
			$tmp .= "<input type=\"image\" name=\"add_receiver_button\" src=\"".$GLOBALS['ASSETS_URL']."images/".$picture."\" border=\"0\" ".tooltip(_("f�gt alle ausgew�htlen Personen der Empf�ngerInnenliste hinzu")).">";
			$tmp .= "&nbsp;<font size=\"-1\">"._("ausgew&auml;hlte hinzuf�gen")."";
			$tmp .= "&nbsp;<br><input type=\"image\" name=\"add_allreceiver_button\" src=\"".$GLOBALS['ASSETS_URL']."images/".$picture."\" border=\"0\" ".tooltip(_("f�gt alle Personen der Empf�ngerInnenliste hinzu")).">";
			$tmp .= "&nbsp;<font size=\"-1\">"._("alle hinzuf&uuml;gen")."</font>";

		}

	}

	// free search
	$tmp .= "<br><br><font size=\"-1\"><b>"._("Freie Suche:")."</b></font><br>";
	if ($search_exp != "" && strlen($search_exp) >= "3") {
		$search_exp = str_replace("%", "\%", $search_exp);
		$search_exp = str_replace("_", "\_", $search_exp);
		$query = "SELECT username, ".$_fullname_sql['full_rev']." AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE (username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ORDER BY Nachname ASC";
		$db->query($query); //
		if (!$db->num_rows()) {
			$tmp .= "&nbsp;<input type=\"image\" name=\"reset_freesearch\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zur�ck")).">";
			$tmp .= "&nbsp;<font size=\"-1\">"._("keine Treffer")."</font>";
		} else {
			$c = 0;
			$tmp2 .= "<input type=\"image\" name=\"add_freesearch\" ".tooltip(_("zu Empf�ngerliste hinzuf�gen"))." value=\""._("zu Empf&auml;ngerliste hinzuf&uuml;gen")."\" src=\"".$GLOBALS['ASSETS_URL']."images/".$picture."\" border=\"0\">&nbsp;";
			$tmp2 .= "<select size=\"1\" width=\"80\" name=\"freesearch[]\">";
			while ($db->next_record()) {
				if (get_visibility_by_username($db->f("username"))) {
					$c++;
					if (empty($sms_data["p_rec"])) {
						$tmp2 .= "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
					} else {
						if (!in_array($db->f("username"), $sms_data["p_rec"])) {
							$tmp2 .= "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
						}
					}
				}
			}
			$tmp2 .= "</select>";
			$tmp2 .= "<input type=\"image\" name=\"reset_freesearch\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zur�ck")).">";
			if ($c > 0) {
				$tmp .= $tmp2;
			} else {
				$tmp .= "&nbsp;<input type=\"image\" name=\"reset_freesearch\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zur�ck")).">";
				$tmp .= "&nbsp;<font size=\"-1\">"._("keine Treffer")."</font>";
			}
		}
	} else {
		ob_start();
		?>
		<input id="addressee" type="text" name="search_exp" size="30">
		<div id="addressee_choices" class="autocomplete"></div>

		<input type="image" name="" src="<?= Assets::url('images/suchen.gif') ?>" border="0">

		<script type="text/javascript">
			Event.observe(window, 'load', function() {
			  new Ajax.Autocompleter('addressee',
			                         'addressee_choices',
			                         'dispatch.php/autocomplete/person/family',
			                         {
			                           minChars: 3,
			                           paramName: 'value',
			                           method: 'get'
			                         });
			});
		</script>
		<?
		$tmp .= ob_get_clean();
	}
	return $tmp;
}

function show_msgform() {

	global $PHP_SELF, $sms_data, $user, $quote, $tmp_sms_content, $quote_username, $message, $messagesubject, $cmd;

	$tmp = "&nbsp;<font size=\"-1\"><b>"._("Betreff:")."</b></font>";
	$tmp .= "<div align=\"center\"><input type=\"text\" ". ($cmd == "write_chatinv" ? "disabled" : "") ." name=\"messagesubject\" value=\"".trim(htmlready(stripslashes($messagesubject)))."\"style=\"width: 99%\"></div>";

	$tmp .= "<br>&nbsp;<font size=\"-1\"><b>"._("Nachricht:")."</b></font>";
	$tmp .= "<div align=\"center\"><textarea name=\"message\" style=\"width: 99%\" cols=80 rows=10 wrap=\"virtual\">\n";
	if ($quote) { $tmp .= quotes_encode(htmlReady($tmp_sms_content), get_fullname_from_uname($quote_username)); }
	if ($message) { $tmp .= htmlReady(stripslashes($message)); }
	$tmp .= "</textarea>\n<br><br>";
	// send/ break-button
	if (sizeof($sms_data["p_rec"]) > "0") { $tmp .= "<input type=\"image\" ".makeButton("abschicken", "src")." name=\"cmd_insert\" border=0 align=\"absmiddle\">"; }
	$tmp .= "&nbsp;<a href=\"sms_box.php\">".makeButton("abbrechen", "img")."</a>&nbsp;";
	$tmp .= "<input type=\"image\" ".makeButton("vorschau", "src")." name=\"cmd\" border=0 align=\"absmiddle\">";
	$tmp .= "<br><br>";
	$tmp .= "</div>";
	return $tmp;

}

function show_previewform() {

	global $sms_data, $message, $signature, $my_messaging_settings, $messagesubject;

	$tmp = "<input type=\"image\" name=\"refresh_message\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind3.gif\" border=\"0\" ".tooltip(_("aktualisiert die Vorschau der aktuellen Nachricht.")).">&nbsp;"._("Vorschau erneuern.")."<br><br>";
	$tmp .= "<b>"._("Betreff:")."</b><br>".htmlready(stripslashes($messagesubject));
	$tmp .= "<br><br><b>"._("Nachricht:")."</b><br>";
	$tmp .= quotes_decode(formatReady(stripslashes($message)));
	if ($sms_data["sig"] == "1") {
		$tmp .= "<br><br>--<br>";
		if ($signature) {
			$tmp .= quotes_decode(formatReady(stripslashes($signature)));
		} else {
			$tmp .= quotes_decode(formatReady(stripslashes($my_messaging_settings["sms_sig"])));
		}
	}

	return $tmp;

}

function show_sigform() {

	global $sms_data, $signature, $my_messaging_settings;

	if ($sms_data["sig"] == "1") {
			$tmp =  "<font size=\"-1\">";
			$tmp .= _("Dieser Nachricht wird eine Signatur angeh�ngt");
			$tmp .= "<br><input type=\"image\" name=\"rmv_sig_button\" src=\"".$GLOBALS['ASSETS_URL']."images/rmv_sig.gif\" border=\"0\" ".tooltip(_("entfernt die Signatur von der aktuellen Nachricht.")).">&nbsp;"._("Signatur entfernen.");
			$tmp .= "</font><br>";
			$tmp .= "<textarea name=\"signature\" style=\"width: 250px\" cols=20 rows=7 wrap=\"virtual\">\n";
			if (!$signature) {
				$tmp .= htmlready(stripslashes($my_messaging_settings["sms_sig"]));
			} else {
				$tmp .= htmlready(stripslashes($signature));
			}
			$tmp .= "</textarea>\n";
	} else {
		$tmp =  "<font size=\"-1\">";
		$tmp .=  _("Dieser Nachricht wird keine Signatur angeh�ngt");
			$tmp .= "<br><input type=\"image\" name=\"add_sig_button\" src=\"".$GLOBALS['ASSETS_URL']."images/add_sig.gif\" border=\"0\" ".tooltip(_("f�gt der aktuellen Nachricht eine Signatur an.")).">&nbsp;"._("Signatur anh�ngen.");
		$tmp .= "</font>";
	}

	$tmp = "<font size=\"-1\">".$tmp."</font>";
	return $tmp;

}

function show_msgsaveoptionsform() {

	global $sms_data, $my_messaging_settings;

	if($sms_data["tmpsavesnd"] == 1) {

		$tmp .= "<input type=\"image\" name=\"rmv_tmpsavesnd_button\" src=\"".$GLOBALS['ASSETS_URL']."images/smssave_red.gif\" border=\"0\" ".tooltip(_("Klicken Sie hier um die Nachricht nicht zu speichern.")).">&nbsp;"._("Klicken Sie das Icon um die Nachricht nicht zu speichern.");
		// do we have any personal folders? if, show them here
		if (have_msgfolder("out") == TRUE) {
			// walk throw personal folders
			$tmp .= "<br><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"5\" height=\"5\" border=0>";
			$tmp .= "<br>"._("in: ");
			$tmp .= "<select name=\"tmp_save_snd_folder\" style=\"vertical-align:middle; font-size:11pt; width: 180px\">";
			$tmp .=  "<option value=\"dummy\">"._("Postausgang")."</option>";
			for($x="0";$x<sizeof($my_messaging_settings["folder"]["out"]);$x++) {
				if (htmlready(stripslashes(return_val_from_key($my_messaging_settings["folder"]["out"], $x))) != "dummy") {
					$tmp .=  "<option value=\"".$x."\" ".CheckSelected($x, $sms_data["tmp_save_snd_folder"]).">".htmlready(stripslashes(return_val_from_key($my_messaging_settings["folder"]["out"], $x)))."</option>";
				}
			}
			$tmp .= "</select>";
		}

	} else {

		$tmp .= "<input type=\"image\" name=\"add_tmpsavesnd_button\" src=\"".$GLOBALS['ASSETS_URL']."images/smssave.gif\" border=\"0\" ".tooltip(_("Klicken Sie hier um die Nachricht zu speichern.")).">&nbsp;"._("Klicken Sie das Icon um die Nachricht zu speichern.");

	}

	$tmp = "<font size=\"-1\">".$tmp."</font>";
	return $tmp;

}

function show_msgemailoptionsform() {

	global $sms_data, $my_messaging_settings;

	if($sms_data["tmpemailsnd"] == 1) {
		$tmp .= "<input type=\"image\" name=\"rmv_tmpemailsnd_button\" src=\"".$GLOBALS['ASSETS_URL']."images/emailrequest_red.gif\" border=\"0\" ".tooltip(_("Klicken Sie hier um die Nachricht nicht (auch) als Email zu versenden.")).">&nbsp;"._("Klicken Sie das Icon um die Nachricht nicht (auch) als Email zu versenden.");
	} else {
		$tmp .= "<input type=\"image\" name=\"add_tmpemailsnd_button\" src=\"".$GLOBALS['ASSETS_URL']."images/emailrequest.gif\" border=\"0\" ".tooltip(_("Klicken Sie hier um die Nachricht (auch) als Email zu versenden.")).">&nbsp;"._("Klicken Sie das Icon um die Nachricht (auch) als Email zu versenden.");
	}

	$tmp = "<font size=\"-1\">".$tmp."</font>";
	return $tmp;

}

function show_msgreadconfirmoptionsform() {

	global $sms_data, $my_messaging_settings;

	if($sms_data["tmpreadsnd"] == 1) {
		$tmp .= "<input type=\"image\" name=\"rmv_tmpreadsnd_button\" src=\"".$GLOBALS['ASSETS_URL']."images/lesebst_red.gif\" border=\"0\" ".tooltip(_("Klicken Sie hier um f�r diese Nachricht keine Lesebest�tigung anzufordern.")).">&nbsp;"._("Klicken Sie das Icon um keine Lesebest�tigung anzufordern.");
	} else {
		$tmp .= "<input type=\"image\" name=\"add_tmpreadsnd_button\" src=\"".$GLOBALS['ASSETS_URL']."images/lesebst.gif\" border=\"0\" ".tooltip(_("Klicken Sie hier um f�r diese Nachricht eine Lesebest�tigung anzufordern.")).">&nbsp;"._("Klicken Sie das Icon um eine Lesebest�tigung anzufordern.");
	}

	$tmp = "<font size=\"-1\">".$tmp."</font>";
	return $tmp;

}

function show_chatselector() {

	global $admin_chats, $cmd;

	if ($cmd == "write_chatinv") {

		echo "<td class=\"steel1\" width=\"100%\" valign=\"left\"><div align=\"left\">";
		echo "<font size=\"-1\"><b>"._("Chatraum ausw&auml;hlen:")."</b>&nbsp;&nbsp;</font>";
		echo "<select name=\"chat_id\" style=\"vertical-align:middle;font-size:9pt;\">";
		foreach($admin_chats as $chat_id => $chat_name){
			echo "<option value=\"$chat_id\"";
			if ($_REQUEST['selected_chat_id'] == $chat_id){
				echo " selected ";
			}
			echo ">".htmlReady($chat_name)."</option>";
		}
		echo "</select>";
		echo "</div><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" height=\"6\" border=\"0\">";
		echo "</td></tr>";

	}

}

?>
