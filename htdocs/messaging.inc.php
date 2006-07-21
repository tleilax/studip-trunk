<?php

/**
* several functions and classes used for the systeminternal messages
* 
* @author				Nils K. Windisch <studip@nkwindisch.de>, Cornelis Kater <ckater@gwdg.de>
* @access				public
* @modulegroup	Messaging
* @module				messaging.inc.php
* @package			Stud.IP Core
*/

/*
mesaging.inc.php - Funktionen fuer das Messaging
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

require_once ("$ABSOLUTE_PATH_STUDIP/language.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/user_visible.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/contact.inc.php");
if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php"; //wird f�r Nachrichten im chat ben�tigt
}

// 
function CheckChecked($a, $b) {
	if ($a == $b) {
		return "checked";
	} else {
		return FALSE;
	}
}

// 
function CheckSelected($a, $b) {
	if ($a == $b) {
		return "selected";
	} else {
		return FALSE;
	}
}

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

class messaging {
	var $db;	//Datenbankanbindung
	var $sig_string; //String, der Signaturen vom eigentlichen Text abgrenzt


	//Konstruktor
	function messaging () {
		$this->sig_string="\n \n -- \n";
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
	}

	//Nachricht loeschen
	function delete_message($message_id, $user_id = FALSE, $force = FALSE) {
		global $user;
		
		if (!$user_id) {
			$user_id = $user->id;
		}	

		$query = "UPDATE message_user SET deleted = '1' WHERE message_id = '".$message_id."' AND user_id = '".$user_id."' AND deleted='0'";

		if(!$force) {
			$query .= " AND dont_delete='0'";
		}

		$db=new DB_Seminar;
		$db2=new DB_Seminar;

		$db->query($query);
		if ($db->affected_rows()) {
			$db2->query("SELECT message_id FROM message_user WHERE message_id = '".$message_id."' AND deleted = '0'");
			if (!$db2->num_rows()) {
				$db2->query("DELETE FROM message WHERE message_id = '".$message_id."'");
				$db2->query("DELETE FROM message_user WHERE message_id = '".$message_id."'");
			}
			return TRUE;
		} else {
			return FALSE;
		}

	}


	// delete all messages from user
	function delete_all_messages($user_id = FALSE, $force = FALSE) {
		global $user;
		$db=new DB_Seminar;
		
		if (!$user_id) {
			$user_id = $user->id;
		}
		
		$query = "SELECT message_id FROM message_user WHERE user_id = '".$user_id."' AND deleted='0'";
		$db->query("$query");
		while ($db->next_record()) {
			$this->delete_message($db->f("message_id"), $user_id, $force);
		}
	}


	// update messages as readed
	function set_read_message($message_id) {
		global $user;
		$db=new DB_Seminar;
		$user_id = $user->id;
		$query = "UPDATE IGNORE message_user SET readed=1 WHERE user_id = '$user_id' AND message_id = '$message_id'";
		$db->query($query);
	}

	// delete all messages from user
	function set_read_all_messages() {
		global $user;
		$db=new DB_Seminar;
		
		$user_id = $user->id;
		
		$query = "SELECT message_id FROM message_user WHERE readed = '0' AND deleted='0' and user_id = '".$user_id."' AND snd_rec = 'rec'";
		$db->query("$query");
		while ($db->next_record()) {
			$this->set_read_message($db->f("message_id"));
		}
	}

	function user_wants_email($userid) {

		$db = new DB_Seminar("SELECT email_forward FROM user_info WHERE user_id = '".$userid."'");
		#$db = new DB_Seminar("SELECT email_forward FROM user_info a, auth_user_md5 b WHERE a.user_id = b.user_id AND (b.username = '$userid' OR b.user_id = '$userid')");
		$db->next_record();
		switch ($db->f("email_forward")) {
			case 1:
				return FALSE;
				break;

			case 2:
				return 2;
				break;

			case 3:
				return 3;
				break;

			default:
				return $GLOBALS["MESSAGING_FORWARD_DEFAULT"];
				break;
		}

	}

	function sendingEmail($rec_uname, $snd_user_id, $message, $subject) {
		
		global $user;

		$db4 = new DB_Seminar("SELECT user_id, Email FROM auth_user_md5 WHERE username = '$rec_uname' OR user_id = '$rec_uname';");
		$db4->next_record();
		$to = $db4->f("Email");				
		$rec_fullname = get_fullname($db4->f("user_id"));
			
		$smtp = new studip_smtp_class;
			
		setTempLanguage($db4->f("user_id"));	
			
		$title = "[Stud.IP - " . $GLOBALS['UNI_NAME_CLEAN'] . "] ".stripslashes(kill_format($subject));
				
		if ($snd_user_id != "____%system%____") {
			$snd_fullname = get_fullname($snd_user_id);
			$db4->query("SELECT Email FROM auth_user_md5 WHERE user_id = '$user->id'");
			$db4->next_record();
			$reply_to = "\"".$smtp->QuotedPrintableEncode($snd_fullname,1)."\" <".$db4->f("Email").">";
		} else {
			$snd_fullname = 'Stud.IP - ' . $GLOBALS['UNI_NAME_CLEAN'];
			$reply_to = $GLOBALS["UNI_CONTACT"];
		}

		$title = $smtp->QuotedPrintableEncode($title, 1);
		// Generate "Header" of the message
		$mailmessage = _("Von: ")."$snd_fullname\n";
		$mailmessage .= _("An: ")."$rec_fullname\n";
		$mailmessage .= _("Betreff: ")."".stripslashes(kill_format($subject))."\n";
		$mailmessage .= _("Datum: ").date("d.m. Y, H:i",time())."\n\n";
		$mailmessage .= kill_format($message)."\n-- \n";
				
		// generate signature of the message
		$mailmessage .= sprintf(_("Diese E-Mail ist eine Kopie einer systeminternen Nachricht, die in Stud.IP an %s versendet wurde."), $rec_fullname)."\n";
		$mailmessage .= sprintf(_("Antworten Sie nicht auf diese E-Mail, sondern benutzen Sie Stud.IP unter %s"), $smtp->url);
		//rescue escaped newlines if mysql_escape_string() was used
		$mailmessage = str_replace('\n', "\n", $mailmessage);
		$mailmessage = stripslashes($mailmessage);
			
		restoreLanguage();
				
		// Now, let us send the message
		$smtp->SendMessage($smtp->env_from, array($to), array("From: ".$smtp->from, "To: \"".$smtp->QuotedPrintableEncode($rec_fullname,1)."\" <$to>", "Reply-To: $reply_to", "Subject: $title"), $mailmessage);

	}

	function get_forward_id($id) {
		
		$db = new DB_Seminar;

		$db->query("SELECT smsforward_rec FROM user_info WHERE user_id='".$id."'");
		$db->next_record();
		$db->f("smsforward_rec");
		$forward_id = $db->f("smsforward_rec");

		return $forward_id;
	}

	function get_forward_copy($id) {
		
		$db = new DB_Seminar;

		$db->query("SELECT smsforward_copy FROM user_info WHERE user_id='".$id."'");
		$db->next_record();
		$db->f("smsforward_rec");
		$forward_copy = $db->f("smsforward_copy");

		return $forward_copy;
	}

	function insert_message($message, $rec_uname, $user_id='', $time='', $tmp_message_id='', $set_deleted='', $signature='', $subject='', $force_email='') {

		global $_fullname_sql, $user, $my_messaging_settings, $sms_data;

		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		$db3 = new DB_Seminar;
		$db4 = new DB_Seminar;
		$db5 = new DB_Seminar;
		
		// wenn kein subject uebergeben
		if(!$subject) $subject = _("Ohne Betreff");
		
		if($sms_data['tmpreadsnd'] == 1) {
			$reading_confirmation = 1;
		}

		if($sms_data['tmpemailsnd'] == 1) {
			$email_request = 1;
		}

		// wenn keine zeit uebergeben
		if (!$time) $time = time();
		
		// wenn keine id uebergeben
		if (!$tmp_message_id) $tmp_message_id = md5(uniqid("321losgehtes"));

		// wenn keine user_id uebergeben
		if (!$user_id) $user_id = $user->id;


		if (!empty($message)) { // wenn $message nicht empty
		
			if ($user_id != "____%system%____")  { // real-user message
				
				$snd_user_id = $user_id;
				if ($sms_data["tmpsavesnd"] != "1") { // don't save save sms in outbox
					$set_deleted = "1";
				}

				// personal-signatur
				if ($sms_data["sig"] == "1") { 
					if(!$signature) {
						$signature = $my_messaging_settings["sms_sig"];
					}
					$message .= $this->sig_string.$signature;
				}

			} else { // system-message

				// system-signatur
				$snd_user_id = "____%system%____";		
				setTempLanguage();
				$message .= $this->sig_string. _("Diese Nachricht wurde automatisch vom Stud.IP-System generiert. Sie k�nnen darauf nicht antworten.");
				restoreLanguage();

			}	

			
			// insert message
			$db3->query("INSERT INTO message SET message_id = '".$tmp_message_id."', mkdate = '".$time."', message = '".$message."', autor_id = '".$snd_user_id."', subject = '".$subject."', reading_confirmation = '".$reading_confirmation."'");
				
			// insert snd
			if (!$set_deleted) { // safe message
				if($sms_data["tmp_save_snd_folder"]) { // safe in specific folder (sender)
					$db3->query("INSERT INTO message_user SET message_id='".$tmp_message_id."',mkdate = '".$time."', user_id='".$snd_user_id."', snd_rec='snd', folder='".$sms_data["tmp_save_snd_folder"]."'");
				} else { // don't safe message in specific folder
					$db3->query("INSERT INTO message_user SET message_id='".$tmp_message_id."',mkdate = '".$time."', user_id='".$snd_user_id."', snd_rec='snd'");
				}
			} else { // save as deleted
				$db3->query("INSERT INTO message_user SET message_id='".$tmp_message_id."',mkdate = '".$time."', user_id='".$snd_user_id."', snd_rec='snd', deleted='1'");
			}

			// heben wir kein array bekommen, machen wir einfach eins ...
			if(!is_array($rec_uname)) {
				$rec_uname = array($rec_uname);
			}
				
			// wir bastelen ein neues array, das die user_id statt des user_name enthaelt
			for($x=0; $x<sizeof($rec_uname); $x++) {
				$rec_id[$x] = get_userid($rec_uname[$x]);
			}
			// wir gehen das eben erstellt array durch und schauen, ob irgendwer was weiterleiten moechte. diese user_id schreiben wir in ein tempraeres array
			for($x=0; $x<sizeof($rec_id); $x++) {
				$tmp_forward_id = $this->get_forward_id($rec_id[$x]);
				if($tmp_forward_id) {
					$tmp_forward_copy = $this->get_forward_copy($rec_id[$x]);
					$rec_id_tmp[] = $tmp_forward_id;	
				}
				
			}

			// wir mergen die eben erstellten arrays und entfernen doppelte eintraege
			$rec_id = array_merge($rec_id, $rec_id_tmp);
			$rec_id = array_unique($rec_id);

		
			// hier gehen wir alle empfaenger durch, schreiben das in die db und schicken eine mail
			for($x=0; $x<sizeof($rec_id); $x++) {
				$db3->query("INSERT message_user SET message_id='".$tmp_message_id."',mkdate = '".$time."', user_id='".$rec_id[$x]."', snd_rec='rec'");
				if ($GLOBALS["MESSAGING_FORWARD_AS_EMAIL"]) {	
					// mail to original receiver
					$mailstatus_original = $this->user_wants_email($rec_id[$x]);
					if($mailstatus_original == 2 || ($mailstatus_original == 3 && $email_request == 1) || $force_email == TRUE) { 
						$this->sendingEmail($rec_id[$x], $snd_user_id, $message, $subject);
					}
				}
				//Benachrichtigung in alle Chatr�ume schicken	 
				$snd_name = ($user_id != "____%system%____") ? get_fullname($user_id) . " (" . get_username($user_id). ")" : "Stud.IP-System";
				if ($GLOBALS['CHAT_ENABLE']) {	 
					$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);	 
					setTempLanguage($rec_id[$x]);	 
					$chatMsg = sprintf(_("Sie haben eine Nachricht von <b>%s</b> erhalten!"), htmlReady($snd_name));	 
					restoreLanguage();	 
					$chatMsg .= "<br></i>".quotes_decode(formatReady(stripslashes($message)))."<i>";	 
					foreach($chatServer->chatDetail as $chatid => $wert) {	 
						if ($wert['users'][$rec_id[$x]]) {	 
							$chatServer->addMsg("system:".$rec_id[$x], $chatid, $chatMsg);	 
						}	 
					}	 
				}
			}

			return sizeof($rec_id);

		} else { // wenn $message empty

			return 0;

		}
	}

	function buddy_chatinv ($message, $chat_id) {
		global $user;
		$this->db->query("SELECT contact.user_id, username FROM contact LEFT JOIN auth_user_md5 USING (user_id) WHERE owner_id = '$user->id' AND buddy = '1' ");
		while ($this->db->next_record()) {
			$count += $this->insert_chatinv($message, $this->db->f("username"), $chat_id);
		}
		return $count;
	}

	//Chateinladung absetzen
	function insert_chatinv($msg, $rec_uname, $chat_id, $user_id = false) {

		global $user,$_fullname_sql,$CHAT_ENABLE;
		
		if ($CHAT_ENABLE){

			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
			$db=new DB_Seminar;
			$snd = 0;
			if (!is_array($rec_uname)) $rec_uname = array($rec_uname);
			if (!$user_id) {
				$user_id = $user->id;
			}
			$username = get_username($user_id);
			$fullname = get_fullname($user_id);
			$chat_uniqid = $chatServer->chatDetail[$chat_id]['id'];
			if (!$chat_uniqid) {
				return false;	//no active chat
			}
			foreach ($rec_uname as $one_rec){
				$one_rec_id = get_userid($one_rec);
				if (!$one_rec_id) break;	//no user found
				if (!get_visibility_by_id($one_rec_id)) break; // only invite visible users
				setTempLanguage($one_rec_id);
				$subject = sprintf(_("Chateinladung von %s"), $fullname);
				$message = sprintf(_("Sie wurden von %s in den Chatraum %s eingeladen!"),$fullname ." (".$username.")",$chatServer->chatDetail[$chat_id]['name']) . "\n - - - \n" . stripslashes($msg);
				$m_id = md5(uniqid("voyeurism",1));
				$query = "
				INSERT INTO message SET 
					message_id = '$m_id', 
					autor_id = '".$user_id."', 
					mkdate = '".time()."', 
					subject = '".mysql_escape_string($subject)."', 
					message = '".mysql_escape_string($message)."', 
					chat_id = '$chat_uniqid'";
				$db->query($query);
				$snd += $db->affected_rows();
				$query = "
				INSERT INTO message_user SET 
					message_id='$m_id', 
					mkdate = '".time()."',
					user_id='".$one_rec_id."',
					snd_rec='rec'";
				$db->query($query);
				$query = "
				INSERT IGNORE INTO message_user SET 
					message_id='$m_id', 
					mkdate = '".time()."',
					user_id='".$user_id."', 
					snd_rec='snd',
					deleted='1'";
				$db->query($query);
				//Benachrichtigung in alle Chatr�ume schicken
				$chatMsg = sprintf(_("Sie wurden von <b>%s</b> in den Chatraum <b>%s</b> eingeladen!"),htmlReady($fullname ." (".$username.")"),htmlReady($chatServer->chatDetail[$chat_id]['name']));
				$chatMsg .= "<br></i>" . formatReady(stripslashes($msg))."<i>";
				foreach($chatServer->chatDetail as $chatid => $wert){
					if ($wert['users'][$one_rec_id]){
						$chatServer->addMsg("system:".$one_rec_id,$chatid,$chatMsg);
					}
				}
			}
			restoreLanguage();
			return $snd;
		} else {
			return FALSE;
		}
	}

	function delete_chatinv($user_id = false){
		global $user;

		if ($GLOBALS['CHAT_ENABLE']){
			if (!$user_id)
				$user_id = $user->id;	
				
			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
			foreach($chatServer->chatDetail as $chatid => $wert){
				$active_chats[] = $wert['id'];
			}
			if (is_array($active_chats)){
				$clause = " AND chat_id NOT IN('" . join("','",$active_chats) . "')";
			}
			$this->db->query("SELECT STRAIGHT_JOIN message.message_id FROM message  LEFT JOIN message_user USING (message_id) WHERE message_user.user_id = '$user_id' AND snd_rec = 'rec' AND chat_id IS NOT NULL" . $clause);
			
			while ($this->db->next_record()) {
				$this->db2->query ("DELETE FROM message_user WHERE message_id ='".$this->db->f("message_id")."' ");
				$this->db2->query ("DELETE FROM message WHERE message_id ='".$this->db->f("message_id")."' ");
			}

			return $this->db2->affected_rows();
		} else {
			return false;
		}
	}

	function check_chatinv($chat_id, $user_id = false){
		global $user;
		
		if ($GLOBALS['CHAT_ENABLE']){
			if (!$user_id)
				$user_id = $user->id;
				
			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
			$chat_uniqid = $chatServer->chatDetail[$chat_id]['id'];
			if (!$chat_uniqid){
				return false;	//no active chat
			}
			$this->db->query("SELECT message.message_id FROM message  LEFT JOIN message_user USING (message_id) WHERE message_user.user_id = '$user_id' AND snd_rec = 'rec' AND chat_id='$chat_uniqid' LIMIT 1");
			return $this->db->next_record();
		} else {
			return false;
		}
	}

	function check_list_of_chatinv($chat_uniqids, $user_id = false){
		global $user;
		
		if ($GLOBALS['CHAT_ENABLE']){
			if (!$user_id)
				$user_id = $user->id;
				
			if (!is_array($chat_uniqids)){
				return false;	//no active chat
			}
			$ret = false;
			$this->db->query("SELECT DISTINCT chat_id FROM message  LEFT JOIN message_user USING (message_id) WHERE user_id='$user_id' AND snd_rec = 'rec' AND chat_id IN('" . join("','",$chat_uniqids)."')");
			while ($this->db->next_record()){
				$ret[$this->db->f("chat_id")] = true;
			}
			return $ret;
		} else {
			return false;
		}
	}

	//Buddy aus der Buddyliste loeschen        
	function delete_buddy ($username) {
		RemoveBuddy($username);
	}

	//Buddy zur Buddyliste hinzufuegen
	function add_buddy ($username) {
		AddNewContact (get_userid($username));
		AddBuddy($username);
	}

	function check_newmsgfoldername($foldername) {
		if ($foldername == "new" || $foldername == "all" || $foldername == "free" || $foldername == "dummy") {
			return FALSE;
		} else {
			return TRUE;	
		}	
	}

}
?>
