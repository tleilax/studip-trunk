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
/*
if($answer_to) {
	$query = "UPDATE message_user SET	answered = '1' WHERE message_id = '".$answer_to."' AND user_id='".$user->id."' AND snd_rec = 'rec'";
	$db->query ($query);
}
*/
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
		if($_REQUEST['answer_to']) {
			$query = "UPDATE message_user SET answered = '1' WHERE message_id = '".$_REQUEST['answer_to']."' AND user_id='".$user->id."' AND snd_rec = 'rec'";
			$db->query ($query);
		}
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
if ($_REQUEST['answer_to']) {
	$query = "SELECT auth_user_md5.username as rec_uname, message.autor_id FROM message LEFT JOIN auth_user_md5 ON(message.autor_id = auth_user_md5.user_id) WHERE message.message_id = '".$_REQUEST['answer_to']."'";
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

	echo '<form action="'.$PHP_SELF.'" method="post">';
	if($_REQUEST['answer_to']) {
		 echo '<input type="hidden" name="answer_to" value="'. htmlReady($_REQUEST['answer_to']). '">';
	}
	echo '<input type="hidden" name="sms_source_page" value="'.$sms_source_page.'">';
	echo '<input type="hidden" name="cmd" value="'.$cmd.'">';

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
	if ($_REQUEST['answer_to'] && !$quote) {
		$db->query ("SELECT subject, message FROM message WHERE message_id = '". $_REQUEST['answer_to']. "' ");
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
			$emailforwardinfo = _("Ihre Nachricht wird nicht gleichzeitig als E-Mail weitergeleitet.");
		}
		if($tmp_link_01 == 1) $emailforwardinfo .= "<br>".sprintf(_("Nutzen Sie die <a href=\"%s?send_view=1\">Experten-Ansicht</a> um die Einstellung zu �ndern."), $PHP_SELF);
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