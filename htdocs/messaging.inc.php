<?php
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
require_once ("$ABSOLUTE_PATH_STUDIP/contact.inc.php");
if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php"; //wird f�r Nachrichten im chat ben�tigt
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
function delete_message($message_id, $user_id = FALSE) {
	global $user;
	
	if (!$user_id) {
		$user_id = $user->id;
	}	
	
	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	$db->query("UPDATE message_user SET deleted = '1' WHERE message_id = '".$message_id."' AND user_id = '".$user_id."'");
	$db2->query("SELECT message_id FROM message_user WHERE message_id = '".$message_id."' AND deleted = '0'");
	if (!$db2->num_rows()) {
		$db2->query("DELETE FROM message WHERE message_id = '".$message_id."'");
		$db2->query("DELETE FROM message_user WHERE message_id = '".$message_id."'");
	}
	if ($db->affected_rows()) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// delete all messages from user
function delete_all_messages($user_id = FALSE) {
	global $user;
	$db=new DB_Seminar;
	
	if (!$user_id) {
		$user_id = $user->id;
	}
	
	$query = "SELECT message_id FROM message_user WHERE user_id = '".$user_id."' AND deleted='0'";
	$db->query("$query");
	while ($db->next_record()) {
		$this->delete_message($db->f("message_id"));
	}
}

function insert_message($message, $rec_uname, $user_id='', $time='', $tmp_message_id='', $set_deleted='') {
	global $_fullname_sql, $user, $my_messaging_settings;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	
	if (!$time) {
		$time = time();
	}

	if (!$tmp_message_id) {
		$tmp_message_id = md5(uniqid("321losgehtes"));
	}

	if (!$user_id) {
		$user_id = $user->id;
	}
	
	$db4->query("SELECT user_id FROM auth_user_md5 WHERE username = '".$rec_uname."'");
	$db4->next_record();
		
	if (!empty($message)) {
		if ($user_id != "____%system%____") {
			$snd_user_id = $user_id;
		} else {
			$snd_user_id = "____%system%____";			
		}
		if ($user_id != "____%system%____")  {
			if ($my_messaging_settings["sms_sig"]) {
				$message .= $this->sig_string.$my_messaging_settings["sms_sig"];
			}
		} else {
			setTempLanguage($db4->f("user_id"));
			$message .= $this->sig_string. _("Diese Nachricht wurde automatisch vom Stud.IP-System generiert. Sie k�nnen darauf nicht antworten.");
			restoreLanguage();
		}
		$db3->query("INSERT IGNORE message SET message_id='".$tmp_message_id."', mkdate='".$time."', message='".$message."', autor_id='".$snd_user_id."'");
		if (!$set_deleted) {
			$db3->query("INSERT IGNORE message_user SET message_id='".$tmp_message_id."', user_id='".$snd_user_id."', snd_rec='snd' ");
		} else {
			$db3->query("INSERT IGNORE message_user SET message_id='".$tmp_message_id."', user_id='".$snd_user_id."', snd_rec='snd', deleted='1'");
		}
		
		$db3->query("INSERT IGNORE message_user SET message_id='".$tmp_message_id."', user_id='".$db4->f("user_id")."', snd_rec='rec' ");
		//Benachrichtigung in alle Chatr�ume schicken	 
		$snd_name = ($user_id != "____%system%____") ? get_fullname($user_id) . " (" . get_username($user_id). ")" : "Stud.IP-System";
		if ($GLOBALS['CHAT_ENABLE']) {	 
			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);	 
			setTempLanguage($db4->f("user_id"));	 
			$chatMsg = sprintf(_("Sie haben eine Nachricht von <b>%s</b> erhalten!"),htmlReady($snd_name));	 
			restoreLanguage();	 
			$chatMsg .= "<br></i>" . quotes_decode(formatReady(stripslashes($message)))."<i>";	 
			foreach($chatServer->chatDetail as $chatid => $wert) {	 
				if ($wert['users'][$db4->f("user_id")]) {	 
					$chatServer->addMsg("system:".$db4->f("user_id"),$chatid,$chatMsg);	 
				}	 
			}	 
		}
		return 1;
	} else {
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
		$db2=new DB_Seminar;
		$db3=new DB_Seminar;
		
		if (!$user_id) {
			$user_id = $user->id;
		}
		$chat_uniqid = $chatServer->chatDetail[$chat_id]['id'];
		if (!$chat_uniqid) {
			return false;	//no active chat
		}
		$db->query ("SELECT username," . $_fullname_sql['full'] ." AS fullname FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE a.user_id = '".$user_id."' ");
		$db->next_record();
		
		$db2->query ("SELECT user_id FROM auth_user_md5 WHERE username = '".$rec_uname."' ");
		if (!$db2->next_record()){
			return false;	//no user found
		}
		setTempLanguage($db2->f("user_id"));
		$message = sprintf(_("Sie wurden von %s in den Chatraum %s eingeladen!"),$db->f("fullname")." (".$db->f("username").")",$chatServer->chatDetail[$chat_id]['name']) 
			. "\n - - - \n" . stripslashes($msg);
		
		$m_id = md5(uniqid("voyeurism"));
		
		$db3->query ("INSERT INTO message SET message_id='$m_id', autor_id='".$user_id."', mkdate='".time()."', message='".mysql_escape_string($message)."', chat_id='$chat_uniqid' ");
		
		$db3->query ("INSERT IGNORE INTO message_user SET message_id='$m_id', user_id='".get_userid($rec_uname)."', snd_rec='rec'");
		$db3->query ("INSERT IGNORE INTO message_user SET message_id='$m_id', user_id='".get_userid($rec_uname)."', snd_rec='snd', deleted='1'");
		
		//Benachrichtigung in alle Chatr�ume schicken
		$chatMsg = sprintf(_("Sie wurden von <b>%s</b> in den Chatraum <b>%s</b> eingeladen!"),htmlReady($db->f("fullname")." (".$db->f("username").")"),htmlReady($chatServer->chatDetail[$chat_id]['name']));
		$chatMsg .= "<br></i>" . formatReady(stripslashes($msg))."<i>";
		restoreLanguage();
		foreach($chatServer->chatDetail as $chatid => $wert){
			if ($wert['users'][$db2->f("user_id")]){
				$chatServer->addMsg("system:".$db2->f("user_id"),$chatid,$chatMsg);
			}
		}

		return TRUE;
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
		$this->db->query("SELECT message.message_id FROM message_user LEFT JOIN message USING (message_id) WHERE message_user.user_id = '$user_id' AND snd_rec = 'rec' AND chat_id IS NOT NULL" . $clause);
		
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
		$this->db->query("SELECT message.message_id FROM message_user LEFT JOIN message USING (message_id) WHERE message_user.user_id = '$user_id' AND snd_rec = 'rec' AND chat_id='$chat_uniqid' LIMIT 1");
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
		$this->db->query("SELECT DISTINCT chat_id FROM message_user LEFT JOIN message USING (message_id) WHERE user_id='$user_id' AND snd_rec = 'rec' AND chat_id IN('" . join("','",$chat_uniqids)."')");
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
}
?>
