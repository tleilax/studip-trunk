<?

/**
* frontend for message-transmission
*
* @author		Cornelis Kater <ckater@gwdg.de>, Nils K. Windisch <studip@nkwindisch.de>
* @version		$Id$
* @access		public
* @modulegroup	Messaging
* @module		sms_send.php
* @package		Stud.IP Core
*/

/*
sms_send.php - Verwaltung von systeminternen Kurznachrichten
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once 'lib/functions.php';
require_once ('lib/msg.inc.php');
require_once ('lib/visual.inc.php');
require_once ('lib/include/messagingSettings.inc.php');
require_once ('lib/messaging.inc.php');
require_once ('lib/statusgruppe.inc.php');
require_once ('lib/include/reiter.inc.php');
require_once ('lib/sms_functions.inc.php');
require_once ('lib/user_visible.inc.php');
if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT.'/chat_func_inc.php';
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
	$admin_chats = $chatServer->getAdminChats($auth->auth['uid']);

}

$sess->register("sms_data");
$msging=new messaging;

$db=new DB_Seminar;

check_messaging_default();

# ACTION
###########################################################

if($answer_to) {
	$query = "UPDATE message_user SET	answered = '1' WHERE message_id = '".$answer_to."' AND user_id='".$user->id."' AND snd_rec = 'rec'";
	$db->query ($query);
}

// write a chat-invitation, so predefine the messagesubject
if ($cmd == "write_chatinv" && !isset($messagesubject)) $messagesubject = _("Chateinladung");

// where do we save the message?
if($tmp_save_snd_folder) {

	if($tmp_save_snd_folder == "dummy") {
		unset($sms_data["tmp_save_snd_folder"]);
	} else {
		$sms_data["tmp_save_snd_folder"] = $tmp_save_snd_folder;
	}

}


// do we like save the transmitted sms?
if(!$sms_data["tmpsavesnd"]) {
	$sms_data["tmpsavesnd"] = $my_messaging_settings["save_snd"];
} else if($add_tmpsavesnd_button_x) {
	$sms_data["tmpsavesnd"] = 1;
} else if($rmv_tmpsavesnd_button_x) {
	$sms_data["tmpsavesnd"] = 2;
}

// email-forwarding?
if ($rmv_tmpemailsnd_button_x) $sms_data['tmpemailsnd'] = "";
if ($add_tmpemailsnd_button_x) $sms_data['tmpemailsnd'] = 1;

//reading-confirmation?
if ($rmv_tmpreadsnd_button_x) $sms_data["tmpreadsnd"] = "";
if ($add_tmpreadsnd_button_x) $sms_data["tmpreadsnd"] = 1;


// check if active chat avaiable
if (($cmd == "write_chatinv") && (!is_array($admin_chats))) $cmd='';

// send message
if ($cmd_insert_x) {

	if (!empty($sms_data["p_rec"])) {
		$time = date("U");
		$tmp_message_id = md5(uniqid("321losgehtes"));
		if ($chat_id) {
			$count = $msging->insert_chatinv($message, $sms_data["p_rec"], $chat_id);
		} else {
			$count = $msging->insert_message($message, $sms_data["p_rec"], FALSE, $time, $tmp_message_id, FALSE, $signature, $messagesubject);
		}
	}

	if ($count) {

		$msg = "msg�";
		if ($count == "1") $msg .= sprintf(_("Ihre Nachricht an %s wurde verschickt!"), get_fullname_from_uname($sms_data["p_rec"][0],'full',true))."<br />";
		if ($count >= "2") $msg .= sprintf(_("Ihre Nachricht wurde an %s Empf&auml;nger verschickt!"), $count)."<br />";
		unset($signature);
		unset($message);
		$sms_data["sig"] = $my_messaging_settings["addsignature"];

	}

	if ($count < 0) {
		$msg = "error�" . _("Ihre Nachricht konnte nicht gesendet werden. Die Nachricht enth&auml;lt keinen Text.");
	} else if ((!$count) && (!$group_count)) {
		$msg = "error�" . _("Ihre Nachricht konnte nicht gesendet werden.");
	}

	if (!preg_match('/^[a-zA-Z0-9_-]+\.php$/',$sms_source_page)) $sms_source_page = '';
	if ($sms_source_page) {
		$sess->register('sms_msg');
		$sms_msg = $msg;
		$sess->freeze();
		if ($sms_source_page == "about.php") {
			$header_info = "Location: ".$sms_source_page."?username=".$sms_data["p_rec"][0];
		} else {
			$header_info = "Location: ".$sms_source_page;
		}
		header ($header_info);
		die;

	}

	unset($sms_data["p_rec"]);
	unset($sms_data["tmp_save_snd_folder"]);
	unset($sms_data["tmpreadsnd"]);
	unset($sms_data["tmpemailsnd"]);
	unset($messagesubject);

	if($my_messaging_settings["save_snd"] == "1") $sms_data["tmpsavesnd"]  = "1";

}

// do we answer someone and did we came from somewhere != sms-page
if ($answer_to) {
	$query = "SELECT auth_user_md5.username as rec_uname, message.autor_id FROM message LEFT JOIN auth_user_md5 ON(message.autor_id = auth_user_md5.user_id) WHERE message.message_id = '".$answer_to."'";
	$db->query ($query);
	while ($db->next_record()) {
		if($quote) $quote_username = $db->f("rec_uname");
		$sms_data["p_rec"] = array($db->f("rec_uname"));
	}
	$sms_data["sig"] = $my_messaging_settings["addsignature"];
}

if (isset($rec_uname)) {
	if (!get_visibility_by_username($rec_uname)) {
		if ($perm->get_perm() == "dozent") {
			$dbv = new DB_Seminar("SELECT user_id FROM auth_user_md5 WHERE username = '$rec_uname'");
			$dbv->next_record();
			$the_user = $dbv->f("user_id");
			$dbv->query("SELECT * FROM seminar_user a, seminar_user b WHERE a.Seminar_id = b.Seminar_id AND a.user_id = '$user->id' AND a.status = 'dozent' AND b.user_id = '$the_user'");
			if ($dbv->num_rows() == 0) {
				$rec_uname = "";
				$sms_data["p_rec"] = "";
			}
		} else {
			$rec_uname = "";
			$sms_data["p_rec"] = "";
		}
	}
}

if ($msgid) {
	$dbv = new DB_Seminar;
	$dbv->query("SELECT auth_user_md5.username FROM auth_user_md5, message_user WHERE message_user.message_id = '$msgid' AND message_user.user_id = auth_user_md5.user_id AND snd_rec = 'snd'");
	$dbv->next_record();
	$rec_uname = $dbv->f("username");
	$sms_data["p_rec"] = "";
}


if ($rec_uname) {
	$sms_data["p_rec"] = array($rec_uname);
	$sms_data["sig"] = $my_messaging_settings["addsignature"];
}

if($rec_uname) {
	if(get_userid($rec_uname) != "") $sms_data["p_rec"] = array($rec_uname);
	unset($rec_uname);
}


// if send message at group (adressbook or groups in courses)
if ($group_id) {

	// be sure to send it as email
	if($emailrequest == 1) $sms_data['tmpemailsnd'] = 1;

	// predefine subject
	if($subject) $messagesubject = $subject;

	$query = sprintf("SELECT statusgruppe_user.user_id, username FROM statusgruppe_user LEFT JOIN auth_user_md5 USING (user_id) WHERE statusgruppe_id = '%s' ", $group_id);
	$db->query($query);
	while ($db->next_record()) {
		$add_group_members[] = $db->f("username");
	}

	$sms_data["p_rec"] = "";
	if (is_array($add_group_members)) {
		$sms_data["p_rec"] = array_add_value($add_group_members, $sms_data["p_rec"]);
	} else {
		$msg = "error�"._("Die gew�hlte Adressbuchgruppe enth�lt keine Mitglieder.");
		unset($sms_data["p_rec"]);
	}

	// append signature
	$sms_data["sig"] = $my_messaging_settings["addsignature"];

}

// if send message at course
if ($course_id && $perm->have_studip_perm('tutor', $course_id)) {

	// be sure to send it as email
	if($emailrequest == 1) $sms_data['tmpemailsnd'] = 1;

	// predefine subject
	if($subject) $messagesubject = $subject;
	$db = new DB_Seminar;
	if ($filter=="all") {
		$db->query ("SELECT username FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE Seminar_id = '".$course_id."'");
	} else if ($filter=="prelim") {
		$db->query ("SELECT username FROM admission_seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE seminar_id = '".$course_id."' AND status='accepted'");
	} else if ($filter=="waiting") {
		$db->query ("SELECT username FROM admission_seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE seminar_id = '".$course_id."' AND (status='awaiting' OR status='claiming')");
	}
	while ($db->next_record()) {
		$add_course_members[] = $db->f("username");
	}

	$sms_data["p_rec"] = "";
	$sms_data["p_rec"] = array_add_value($add_course_members, $sms_data["p_rec"]);

	// append signature
	$sms_data["sig"] = $my_messaging_settings["addsignature"];

}

// if send message at inst, only for admins
if ($inst_id && $perm->have_studip_perm('admin', $inst_id)) {

	// be sure to send it as email
	if($emailrequest == 1) $sms_data['tmpemailsnd'] = 1;

	// predefine subject
	if($subject) $messagesubject = $subject;
	$db = new DB_Seminar;
	$db->query ("SELECT username FROM user_inst LEFT JOIN auth_user_md5 USING(user_id) WHERE inst_perms!='user' AND Institut_id = '".$inst_id."'");
	while ($db->next_record()) {
		$add_course_members[] = $db->f("username");
	}

	$sms_data["p_rec"] = "";
	$sms_data["p_rec"] = array_add_value($add_course_members, $sms_data["p_rec"]);

	// append signature
	$sms_data["sig"] = $my_messaging_settings["addsignature"];

}

// attach signature
if (!isset($sms_data["sig"])) {
	$sms_data["sig"] = $my_messaging_settings["addsignature"];
} else if ($add_sig_button_x) {
	$sms_data["sig"] = "1";
} else if ($rmv_sig_button_x) {
	$sms_data["sig"] = "0";
}


// add a reciever from adress-members
if ($add_receiver_button_x && !empty($add_receiver)) { $sms_data["p_rec"] = array_add_value($add_receiver, $sms_data["p_rec"]); }


// add all reciever from adress-members
if ($add_allreceiver_button_x) {

	$query_for_adresses = "SELECT contact.user_id, username, ".$_fullname_sql['full_rev']." AS fullname 	FROM contact LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id) WHERE owner_id = '".$user->id."' ORDER BY Nachname ASC";
	$db->query($query_for_adresses);
	while ($db->next_record()) {
		if (empty($sms_data["p_rec"])) {
			$add_rec[] = $db->f("username");
		} else {
			if (!in_array($db->f("username"), $sms_data["p_rec"])) { $add_rec[] = $db->f("username"); }
		}
	}

	$sms_data["p_rec"] = array_add_value($add_rec, $sms_data["p_rec"]);
	unset($add_rec);

}


// add receiver from freesearch
if ($add_freesearch_x && !empty($freesearch)) { $sms_data["p_rec"] = array_add_value($freesearch, $sms_data["p_rec"]); }


// remove all from receiverlist
if ($del_allreceiver_button_x) { unset($sms_data["p_rec"]); }


// aus empfaengerliste loeschen
if ($del_receiver_button_x && !empty($del_receiver)) {
	foreach ($del_receiver as $a) {
		$sms_data["p_rec"] = array_delete_value($sms_data["p_rec"], $a);
	}
}


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
		$tmp .= "<input type=\"text\" name=\"search_exp\" size=\"30\">";
		$tmp .= "<input type=\"image\" name=\"\" src=\"".$GLOBALS['ASSETS_URL']."images/suchen.gif\" border=\"0\">";
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

# OUTPUT
###########################################################

if ($change_view) {
	$HELP_KEYWORD="Basis.MyStudIPMessaging";
} else {
	$HELP_KEYWORD="Basis.InteraktionNachrichten";
}

// includes
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_sms.inc.php'); // include reitersystem
check_messaging_default();

if (($change_view) || ($delete_user) || ($view=="Messaging")) {

	change_messaging_view();
	echo "</td></tr></table>";
	page_close();
	die;

}


$txt['001'] = _("aktuelle Empf&auml;ngerInnen");
$txt['002'] = _("m&ouml;gliche Empf&auml;ngerInnen");
$txt['003'] = _("Signatur");
$txt['004'] = _("Vorschau");
$txt['005'] = (($cmd=="write_chatinv") ? _("Chateinladung") : _("Nachricht"));
$txt['006'] = _("Nachricht speichern");
$txt['007'] = _("als Email senden");
$txt['008'] = _("Lesebest�tigung");


if ($send_view) {

	if ($send_view == "2") {
		unset($my_messaging_settings["send_view"]);
	} else if ($send_view == "1") {
		$my_messaging_settings["send_view"] = $send_view;
	}

}

?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="topic" colspan="2"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/nachricht1.gif" border="0" align="texttop"><b>&nbsp;<?=_("Systeminterne Nachricht schreiben")?></b></td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="blank" valign="top" align="center"> <?
	if ($msg) {
		print ("<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" width=\"99%\"><tr><td valign=\"top\">");
		parse_msg ($msg);
		print ("</td></tr></table>");
	}

	echo "<form action=\"".$PHP_SELF."\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"sms_source_page\" value=\"".$sms_source_page."\">";
	echo "<input type=\"hidden\" name=\"cmd\" value=\"".$cmd."\">";

	// we like to quote something
	if ($quote) {
		$db->query ("SELECT subject, message FROM message WHERE message_id = '$quote' ");
		$db->next_record();
		if(substr($db->f("subject"), 0, 3) != "RE:") {
			$messagesubject = "RE: ".$db->f("subject");
		} else {
			$messagesubject = $db->f("subject");
		}
		if (strpos($db->f("message"),$msging->sig_string)) {
			$tmp_sms_content = substr($db->f("message"), 0, strpos($db->f("message"),$msging->sig_string));
		} else {
			$tmp_sms_content = $db->f("message");
		}
	}
	// we simply answer, not more or less
	if ($answer_to && !$quote) {
		$db->query ("SELECT subject, message FROM message WHERE message_id = '$answer_to' ");
		$db->next_record();
		if(substr($db->f("subject"), 0, 3) != "RE:") {
			$messagesubject = "RE: ".$db->f("subject");
		} else {
			$messagesubject = $db->f("subject");
		}
	}

	if ($my_messaging_settings["send_view"] == "1") { ?>

		<table cellpadding="0" cellspacing="0" border="0" height="10" width="99%">
			<tr>
				<td colspan="2" valign="top" width="30%" height="10" class="blank" style="border-right: dotted 1px">

					<table cellpadding="5" cellspacing="0" border="0" height="10" width="100%">
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['001']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraulight">
								<?=show_precform()?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['002']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraulight">
								<?=show_addrform()?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['006']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraulight">
								<?=show_msgsaveoptionsform()?>
							</td>
						</tr>
						<? if ($GLOBALS["MESSAGING_FORWARD_AS_EMAIL"]) { ?>
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['007']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraulight">
								<?=show_msgemailoptionsform()?>
							</td>
						</tr>
						<? } ?>
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['008']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraulight">
								<?=show_msgreadconfirmoptionsform()?>
							</td>
						</tr>
					</table>

				</td>
				<td colspan="2" valign="top" width="70%" class="blank">

					<table cellpadding="5" cellspacing="0" border="0" height="10" width="100%">
						<?=show_chatselector()?>
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['005']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraulight">
								<?=show_msgform()?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['003']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="printcontent">
								<?=show_sigform()?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="steelgraudunkel">
								<font size="-1" color="#FFFFFF"><b><?=$txt['004']?></b></font>
							</td>
						</tr>
						<tr>
							<td valign="top" class="printcontent">
								<?=show_previewform()?>
							</td>
						</tr>
					</table>

				</td>
			</tr>
		</table> <?

	} else { ?>

		<table cellpadding="5" cellspacing="0" border="0" height="10" width="99%">
			<tr>
				<td colspan="2" valign="top" width="30%" height="10" class="steelgraudunkel">
					<font size="-1" color="#FFFFFF"><b><?=$txt['001']?></b></font>
				</td>
				<td colspan="2" valign="top" width="70%" class="steelgraudunkel">
					<font size="-1" color="#FFFFFF"><b><?=$txt['002']?></b></font>
				</td>
			</tr>
		</table>
		<table cellpadding="5" cellspacing="0" border="0" width="99%">
			<tr>
				<td colspan="2" valign="top" width="30%" class="steelgraulight">
					<?=show_precform()?>
					</td>
					<td class="printcontent" align="left" valign="top" width="70%">
					<?=show_addrform()?><br><br>
				</td>
			</tr>
		</table>
		<table cellpadding="5" cellspacing="0" border="0" width="99%">
			<tr>
				<td colspan="2" valign="top" width="80%" class="steelgraudunkel">
					<font size="-1" color="#FFFFFF"><b><?=$txt['005']?></b></font>
				</td>
			</tr>
		</table>
		<table border="0" cellpadding="5" cellspacing="0" width="99%" align="center">
			<?=show_chatselector()?>
			<tr>
				<td class="steelgraulight" width="80%" valign="middle">
					<?=show_msgform()?>
				</td>
			</tr>
		</table>
		<table border="0" cellpadding="5" cellspacing="0" width="99%" align="center">
			<tr>
				<td class="steelgraudunkel"  width="30%" valign="top">
					<font size="-1" color="#FFFFFF"><b><?=$txt['003']?></b></font>
				</td>
				<td class="steelgraudunkel"  width="70%" valign="top">
					<font size="-1" color="#FFFFFF"><b><?=$txt['004']?></b></font>
				</td>
			</tr>
			<tr>
				<td class="steelgraulight"  width="20%" valign="top">
					<?=show_sigform()?>
				</td>
				<td class="printcontent" width="20%" valign="top">
					<?=show_previewform()?>
				</td>
			</tr>
		</table>
	<?
	}


	if (!$my_messaging_settings["send_view"]) {
		$tmp_link_01 = "1";
		$tmp_link_02 = _("Experten-Ansicht");
	} else if ($my_messaging_settings["send_view"] == "1") {
		$tmp_link_01 = "2";
		$tmp_link_02 = _("Standard-Ansicht");
	}


	$switch_sendview = sprintf(_("W�hlen Sie hier zwischen Experten- und Standard-Ansicht."))."<br><img src=\"".$GLOBALS['ASSETS_URL']."images/link_intern.gif\" width=\"15\" height=\"15\" border=0 alt=\"\">&nbsp;<a href=\"".$PHP_SELF."?send_view=".$tmp_link_01."\">".$tmp_link_02."</a>";
	if($my_messaging_settings["send_view"] == FALSE) {
		$switch_sendview .= "<br>"._("In der Experten-Ansicht sind weitere Optionen wie z. B. Emailweiterleitung und Lesebest�tigung w�hlbar.");
	}


	if($GLOBALS["MESSAGING_FORWARD_AS_EMAIL"] == TRUE) {
		if($sms_data["tmpemailsnd"] == 1) {
			$emailforwardinfo = _("Die Nachricht wird auch als E-Mail weitergeleitet, sofern die Empf�ngerIn sich nicht ausdr�cklich gegen die E-Mail-Weiterleitung entschieden hat.");
		} else {
			$emailforwardinfo = _("Ihre Nachricht wird nicht auch als Email versand.");
		}
		if($tmp_link_01 == 1) $emailforwardinfo .= "<br>".sprintf(_("Nutzern Sie die <a href=\"%s?send_view=1\">Experten-Ansicht</a> um die Einstellung zu �ndern."), $PHP_SELF);
		$emailforwardinfo = array("kategorie" => _("Emailweiterleitung:"),"eintrag" => array(array("icon" => "nachricht1.gif", "text" => sprintf($emailforwardinfo))));
	}

	$smsinfos = "";

	// emailforwarding?!
	if($GLOBALS["MESSAGING_FORWARD_AS_EMAIL"] == TRUE) {
		if($sms_data["tmpemailsnd"] == 1) {
			$smsinfos .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_correct.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
		} else {
			$smsinfos = "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_wrong.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
		}
		$smsinfos .= "&nbsp;"._("Emailweiterleitung")."<br>";
	}

	// readingconfirmation?!
	if($sms_data["tmpreadsnd"] == 1) {
		$smsinfos .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_correct.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
	} else {
		$smsinfos .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_wrong.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
	}
	$smsinfos .= "&nbsp;"._("Lesebest�tigung")."<br>";

	// save the message?!
	if($sms_data["tmpsavesnd"] == 1) {
		$smsinfos .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_correct.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
	} else {
		$smsinfos .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_wrong.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
	}
	$smsinfos .= "&nbsp;"._("Speichern")."<br>";

	// signature?!
	if($sms_data["sig"] == 1) {
		$smsinfos .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_correct.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
	} else {
		$smsinfos .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/vote_answer_wrong.gif\" width=\"16\" height=\"16\" border=0 alt=\"\">";
	}
	$smsinfos .= "&nbsp;"._("Signatur");

	$smsinfos = array("kategorie" => _("�bersicht:"),"eintrag" => array(array("icon" => "einst.gif", "text" => sprintf($smsinfos))));

	echo"</form>\n";
	print "</td><td class=\"blank\" width=\"270\" align=\"right\" valign=\"top\">";

	if (get_config("EXTERNAL_HELP")) {
		$help_url_smil=format_help_url("Basis.VerschiedenesSmileys");
		$help_url_format=format_help_url("Basis.VerschiedenesFormat");
	} else {
		$help_url_smil="help/index.php?help_page=ix_forum7.htm";
		$help_url_format="help/index.php?help_page=ix_forum6.htm";
	}
	$infobox = array(
		array("kategorie" => _("Ansicht:"),"eintrag" => array(
			array("icon" => "admin.gif", "text" => $switch_sendview)
		)),
		$smsinfos,
		$emailforwardinfo,
		array("kategorie" => _("Smilies & Textformatierung:"),"eintrag" => array(
			array("icon" => "asmile.gif", "text" => sprintf(_("%s Liste mit allen Smilies %s Hilfe zu Smilies %s Hilfe zur Textformatierung %s"), "<a href=\"show_smiley.php\" target=\"_blank\">", "</a><br><a href=\"".$help_url_smil."\" target=\"_blank\">", "</a><br><a href=\"".$help_url_format."\" target=\"_blank\">", "</a>"))
		))
	);

	print_infobox($infobox,"sms3.jpg"); ?>

	</td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;
	</td>
</tr>
</table>
<?php
// Save data back to database.
include ('lib/include/html_end.inc.php');
page_close();
?>