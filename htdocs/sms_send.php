<?
/*
sms.php - Verwaltung von systeminternen Kurznachrichten
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
	
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/messagingSettings.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/reiter.inc.php");
if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php"; 
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
	$admin_chats = $chatServer->getAdminChats($auth->auth['uid']);
}

$sess->register("sms_data");
$msging=new messaging;

$db=new DB_Seminar;


//
function array_add_value($add, $array) {
	foreach ($add as $a) {
		if (!empty($array)) {
			if (!in_array($a, $array)) {
				$x = array_push($array, $a);
			}
		} else {
			$array = array($a);
		}
	}
	return $array;
}

//
function array_delete_value($array, $value) {
	for ($i=0;$i<count($array);$i++) {
		if ($array[$i] == $value) 
			array_splice($array, $i, 1);
		}
	return $array;
}

// check if active chat avaiable
if (($cmd == "write_chatinv") && (!is_array($admin_chats)))
	$cmd='';

// send message
if ($cmd_insert_x) {
	
	if (!empty($sms_data["p_rec"])) {
		$count = "";
		$time = date("U");
		$tmp_message_id = md5(uniqid("321losgehtes"));
		foreach ($sms_data["p_rec"] as $a) {
			if ($chat_id) {
				$count = ($count+$msging->insert_chatinv($message, $a, $chat_id));
			} else {
				$count = ($count+$msging->insert_message($message, $a, FALSE, $time, $tmp_message_id));
			}
		}
	}

	if ($count) {
		$msg = "msg�";
		if ($count == "1")	 {
			$msg .= sprintf(_("Ihre Nachricht an %s wurde verschickt!"), get_fullname_from_uname($sms_data["p_rec"][0]))."<br />";
		}
		if ($count >= "2") {
			$msg .= sprintf(_("Ihre Nachricht wurde an %s Empf&auml;nger verschickt!"), $count)."<br />";
		}
	}
	if ($count < 0) {
		$msg = "error�" . _("Ihre Nachricht konnte nicht gesendet werden. Die Nachricht enth&auml;lt keinen Text.");
	} else if ((!$count) && (!$group_count)) {
		$msg = "error�" . _("Ihre Nachricht konnte nicht gesendet werden.");
	}
		
	$sms_msg = rawurlencode ($msg);

	if ($sms_source_page) {
		if ($sms_source_page == "about.php") {
			$header_info = "Location: ".$sms_source_page."?username=".$sms_data["p_rec"][0]."&sms_msg=".$sms_msg;
		} else {
			$header_info = "Location: ".$sms_source_page."?sms_msg=".$sms_msg;
		}
		header ($header_info);
		die;
	}
}

// falls antwort
if ($rec_uname) {
	if (get_username($user->id) != $rec_uname) {
		$sms_data["p_rec"] = array($rec_uname);
	}
}

//
if ($group_id) {
	$query = sprintf("SELECT statusgruppe_user.user_id, username FROM statusgruppe_user LEFT JOIN auth_user_md5 USING (user_id) WHERE statusgruppe_id = '%s' ", $group_id);
	$db->query($query);
	while ($db->next_record()) {
		$add_group_members[] = $db->f("username");
	}
	$sms_data["p_rec"] = "";
	$sms_data["p_rec"] = array_add_value($add_group_members, $sms_data["p_rec"]);
}

// add a reciever from adress-members
if ($add_receiver_button_x && !empty($add_receiver)) {
	$sms_data["p_rec"] = array_add_value($add_receiver, $sms_data["p_rec"]);
}

// add receiver from freesearch
if ($add_freesearch_x && !empty($freesearch)) {
	$sms_data["p_rec"] = array_add_value($freesearch, $sms_data["p_rec"]);
}

// aus empfaengerliste loeschen
if ($del_receiver_button_x && !empty($del_receiver)) {
	foreach ($del_receiver as $a) {
		$sms_data["p_rec"] = array_delete_value($sms_data["p_rec"], $a);
	}
}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

include ("$ABSOLUTE_PATH_STUDIP/links_sms.inc.php");

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
	change_messaging_view();
	echo "</td></tr></table>";
	page_close();
	die;
} ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="topic" colspan="2"><img src="pictures/nachricht1.gif" border="0" align="texttop"><b>&nbsp;<?=_("Systeminterne Nachricht schreiben")?></b></td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;</td>
</tr>
<tr>	
	<td class="blank" valign="top" align="center"> <?
	if ($sms_msg) {
		print ("<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" width=\"99%\"><tr><td valign=\"top\">");
		parse_msg (rawurldecode($sms_msg));
		print ("</td></tr></table>");
	} ?>
	<table cellpadding="5" cellspacing="0" border="0" height="10" width="99%">
		<tr>
			<td colspan="2" valign="top" width="30%" height="10" class="steelgraudunkel"> <?
				echo "<font size=\"-1\" color=\"#FFFFFF\"><b>"._("aktuelle Empf&auml;ngerInnen")."</b></font>"; ?>
			</td>
			<td colspan="2" valign="top" width="70%" class="steelgraudunkel"> 
				<font size="-1" color="#FFFFFF"><b><?=_("m&ouml;gliche Empf&auml;ngerInnen")?></b></font> 
			</td>
		</tr>
	</table>
	<table cellpadding="5" cellspacing="0" border="0" width="99%">
		<tr>
			<td colspan="2" valign="top" width="30%" class="steelgraulight"><?
				// list of to-be-receiver 
				echo "<form action=\"".$PHP_SELF."\" method=\"post\">";
				if (sizeof($sms_data["p_rec"]) == "0") { 
					echo "<font size=\"-1\">"._("Bitte w&auml;hlen Sie mindestens einen Empf&auml;nger aus.");
					if (get_username($user->id) == $rec_uname) {
						echo "<br>"._("Nachrichten k&ouml;nnen nicht an sich selbst gesandt werden.");
					}
					echo "</font>";
				} else {
					echo "<select size=\"5\" name=\"del_receiver[]\" multiple style=\"width: 200\">";
					if ($sms_data["p_rec"]) {
						foreach ($sms_data["p_rec"] as $a) {
							echo "<option value=\"$a\">".get_fullname_from_uname($a)."</option>";
						}
					}
					echo "</select><br>";	
					echo "<input type=\"image\" name=\"del_receiver_button\" src=\"./pictures/trash.gif\" ".tooltip(_("l�scht alle ausgew�htlen Empf�ngerInnen"))." border=\"0\">";
					echo " <font size=\"-1\">"._("ausgew&auml;hlte l&ouml;schen")."</font>";
				}
				echo "</td><td class=\"printcontent\" align=\"left\" valign=\"top\" width=\"70%\">";
				// list of adresses
				$query_for_adresses = "
				SELECT contact.user_id, username, ".$_fullname_sql['full_rev']." AS fullname 
				FROM contact 
				LEFT JOIN auth_user_md5 USING(user_id) 
				LEFT JOIN user_info USING (user_id) 
				WHERE owner_id = '".$user->id."' 
				ORDER BY Nachname ASC";
				
				$db->query($query_for_adresses);

				while ($db->next_record()) {
					$adresses_array[] = $db->f("username");
				}

				echo "<b><font size=\"-1\">"._("Adressbuch-Liste:")."</font></b><br>";

				if (empty($adresses_array)) { // user with no adress-members at all
					echo sprintf("Sie haben noch keine Personen in ihrem Adressbuch. %s Klicken sie %s hier %s um dorthin zu gelangen.", "<br>", "<a href=\"contact.php\">", "</a>");
				} else if (!empty($adresses_array)) { // test if all adresses are added?
					$x = sizeof($adresses_array);
					if (!empty($sms_data["p_rec"])) {
						foreach ($sms_data["p_rec"] as $a) {
							if (in_array($a, $adresses_array)) {
								$x = ($x-1);
							}
						}
					}
					if ($x == "0") { // all adresses already added
						print("Bereits alle Personen des Adressbuchs hinzugef&uuml;gt!");
					} else { // show adresses-select
						echo "<select size=\"3\" name=\"add_receiver[]\" multiple style=\"width: 200\">";
						$db->query($query_for_adresses);
						while ($db->next_record()) {
							if (empty($sms_data["p_rec"])) {
								echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
							} else {
								if (!in_array($db->f("username"), $sms_data["p_rec"])) {
									echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
								}
							}
						}
						echo "</select><br><input type=\"image\" name=\"add_receiver_button\" src=\"./pictures/move_left.gif\" border=\"0\" ".tooltip(_("f�gt alle ausgew�htlen Personen der Empf�ngerInnenliste hinzu")).">&nbsp;<font size=\"-1\">"._("ausgew&auml;hlte hinzuf�gen")."</font>";
					}
				}
				// free search
				echo "<br><br><font size=\"-1\"><b>"._("Freie Suche:")."</b></font><br>";
				if ($search_exp != "") {
					$query = "
					SELECT username, ".$_fullname_sql['full_rev']." AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE (username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ORDER BY Nachname ASC";
					$db->query($query); //
					if (!$db->num_rows()) {
						echo "&nbsp;<input type=\"image\" name=\"reset_freesearch\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zur�ck")).">";
						echo "&nbsp;<font size=\"-1\">"._("keine Treffer")."</font>";
					} else {
						echo "<input type=\"image\" name=\"add_freesearch\" ".tooltip(_("zu Empf�ngerliste hinzuf�gen"))." value=\""._("zu Empf&auml;ngerliste hinzuf&uuml;gen")."\" src=\"./pictures/move_left.gif\" border=\"0\">&nbsp;";
						echo "<select size=\"1\" width=\"100\" name=\"freesearch[]\">";
						while ($db->next_record()) {
							if (empty($sms_data["p_rec"])) {
								if (get_username($user->id) != $db->f("username")) {
									echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
								}							
							} else {
								if (!in_array($db->f("username"), $sms_data["p_rec"]) && get_username($user->id) != $db->f("username")) {
									echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
								}
							}
						}
						echo "</select>";
						echo "<input type=\"image\" name=\"reset_freesearch\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zur�ck")).">";
					}
				} else {
					echo "<input type=\"text\" name=\"search_exp\" size=\"40\">";
					echo "<input type=\"image\" name=\"\" src=\"./pictures/suchen.gif\" border=\"0\">";
				}
		echo "<br><br></td></tr>";
	?></table>
	<table cellpadding="5" cellspacing="0" border="0" width="99%">
		<tr>
			<td colspan="2" valign="top" width="30%" class="steelgraudunkel">
			<?
			echo "<font size=\"-1\" color=\"#FFFFFF\"><b>".(($cmd=="write_chatinv") ? _("Chateinladung") : _("Nachricht"))."</b></font>";
			?>
			</td>
		</tr>
	</table><?
	echo "<table border=\"0\" cellpadding=\"5\" cellspacing=\"0\" width=\"99%\" align=\"center\">";
	if ($quote) {
		$db->query ("SELECT message FROM message WHERE message_id = '$quote' ");
		$db->next_record();
		if (strpos($db->f("message"),$msging->sig_string)) {
			$tmp_sms_content = substr($db->f("message"), 0, strpos($db->f("message"),$msging->sig_string));
		} else {
			$tmp_sms_content = $db->f("message");
		}
	}
	echo "<input type=\"hidden\" name=\"sms_source_page\" value=\"$sms_source_page\">";
	echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">";
	if ($cmd == "write_chatinv") {
		echo "<td class=\"steel1\" width=\"100%\" valign=\"left\"><div align=\"left\">";
		echo "<font size=\"-1\"><b>"._("Chatraum ausw&auml;hlen:")."</b>&nbsp;&nbsp;</font>";
		echo "<select name=\"chat_id\" style=\"vertical-align:middle;font-size:9pt;\">";
		foreach($admin_chats as $chat_id => $chat_name){
			echo "<option value=\"$chat_id\"";
			if ($_REQUEST['selected_chat_id'] == $chat_id){
				echo " selected ";
			}
			echo ">" . htmlReady($chat_name) . "</option>";
		}
		echo "</select>";
		echo "</div><img src=\"pictures/blank.gif\" height=\"6\" border=\"0\">";
		echo "</td></tr>";	
	}
	echo "<td class=\"steelgraulight\" width=\"100%\" valign=\"center\"><div align=\"center\">";
	echo "<textarea name=\"message\" style=\"width: 99%\" cols=80 rows=10 wrap=\"virtual\">";
	if ($quote) {
		echo quotes_encode($tmp_sms_content, get_fullname_from_uname($rec_uname));
	}
	if ($message) {
		echo stripslashes($message);
	}
	echo "</textarea><br><br>";	
	if (sizeof($sms_data["p_rec"]) > "0") {
		echo "<input type=\"image\" ".makeButton("abschicken", "src")." name=\"cmd_insert\" border=0 align=\"absmiddle\">";
	}
	echo "&nbsp;<a href=\"sms_box.php\">".makeButton("abbrechen", "img")."</a><br>&nbsp;";	
	echo "</div><img src=\"pictures/blank.gif\" height=\"6\" border=\"0\">";
	echo "</td>";	

	echo"</form>";

	echo "</td></tr></table>";
	print "</td><td class=\"blank\" width=\"270\" align=\"right\" valign=\"top\">";
	$infobox = array(
		array("kategorie" => _("Empf&auml;nger hinzuf&uuml;gen:"),"eintrag" => array(
			array("icon" => "pictures/nutzeronline.gif", "text" => sprintf(_("Nutzen Sie die Adressbuch-Liste oder freie Suche um Empf&auml;ngerInnen hinzuf&uuml;gen.")))
		)),
		array("kategorie" => _("Smilies & Textformatierung:"),"eintrag" => array(
			array("icon" => "pictures/smile/asmile.gif", "text" => sprintf(_("%s Liste mit allen Smilies %s Hilfe zu Smilies %s Hilfe zur Textformatierung %s"), "<a href=\"show_smiley.php\" target=\"_blank\">", "</a><br><a href=\"help/index.php?help_page=ix_forum7.htm\" target=\"_blank\">", "</a><br><a href=\"help/index.php?help_page=ix_forum6.htm\" target=\"_blank\">", "</a>"))
		))
	);
	print_infobox($infobox,"pictures/sms3.jpg"); ?>

	</td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;
	</td>
</tr>
</table> <?

// Save data back to database.
page_close() ?>

</body>
</html>
