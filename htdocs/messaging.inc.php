<?php
/*
mesaging.inc.php - Funktionen fuer das Messaging
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

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

require_once "ChatShmServer.class.php"; //wird f�r Nachrichten im chat ben�tigt

class messaging {
	var $db;							//Datenbankanbindung
	var $sig_string;					//String, der Signaturen vom eigentlichen Text abgrenut


//Konstruktor, testet, ob es gesetzte Buddies ueberhaupt noch gibt
function messaging () {
	global $my_buddies;
	$this->sig_string="\n \n -- \n";
		
	$this->db = new DB_Seminar;
	if ($my_buddies) {
		foreach ($my_buddies as $a)
			$this->db->query("SELECT username FROM auth_user_md5 WHERE username = '".$a["username"]."' ");
				if (!$this->db->next_record())
				unset ($my_buddies[$a["username"]]);
		}
	}

//alle Nachrichten loeschen
function delete_all_sms  ($user_id, $delete_unread) { 
	global $LastLogin, $user;
	$db=new DB_Seminar;
	
	if (!$user_id)
		$user_id = $user->id;
	
	$db->query ("SELECT username FROM auth_user_md5 WHERE user_id = '".$user_id."' ");
	$db->next_record();
	$tmp_sms_username=$db->f("username");
	
	if (!$delete_unread)
		$db->query("DELETE FROM globalmessages WHERE user_id_rec = '$tmp_sms_username' ");
	else
		$db->query("DELETE FROM globalmessages WHERE mkdate < ".$LastLogin ." AND user_id_rec = '$tmp_sms_username' ");

	return $db->affected_rows();
	}

//Nachricht loeschen
function delete_sms ($message_id) {
	global $user;
	$db=new DB_Seminar;

	$db->query ("SELECT username FROM auth_user_md5 WHERE user_id = '".$user->id."' ");
	$db->next_record();
	$tmp_sms_username=$db->f("username");
		
	$db->query("DELETE FROM globalmessages WHERE message_id = '$message_id' AND user_id_rec = '$tmp_sms_username' ");
	if ($db->affected_rows())
		return TRUE;
	else
		return FALSE;
	}
 
//Geschriebene Nachricht einfuegen
function insert_sms ($rec_uname, $message, $user_id='') {
	global $user, $my_messaging_settings;

	if (!$this->sig_string)
		$this->sig_string="\n \n -- \n";

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;

	if (!$user_id)
		$user_id = $user->id;

	if (!empty($message)) {
		$db->query ("SELECT username,CONCAT(Vorname,' ',Nachname) AS fullname FROM auth_user_md5 WHERE user_id = '".$user_id."' ");
		$db->next_record();

		$db2->query ("SELECT user_id FROM auth_user_md5 WHERE username = '".$rec_uname."' ");
		$db2->next_record();

		if ($db2->num_rows()){
		$m_id=md5(uniqid("voyeurism"));
		if ($my_messaging_settings["sms_sig"])
			$message.=$this->sig_string.$my_messaging_settings["sms_sig"];
		$db3->query("INSERT INTO globalmessages SET message_id='$m_id', user_id_rec='$rec_uname', user_id_snd='".$db->f("username")."', mkdate='".time()."', message='$message' ");
		
//Benachrichtigung in alle Chatr�ume schicken
          $chatServer=new ChatShmServer;
          $myUser=$chatServer->chatUser[$db2->f("user_id")];
          $chatMsg="Du hast eine SMS von <b>".$db->f("fullname")." (".$db->f("username").")</b> erhalten!<br></i>";
          $chatMsg.=formatReady(stripslashes($message))."<i>";
          if (is_array($myUser))
          	foreach($myUser as $chatid =>$wert)
	               if ($chatid["action"])
        	            $chatServer->addMsg("system:".$db2->f("user_id"),$chatid,$chatMsg);

		
		return $db3->affected_rows();
        	}
        	else return false;
        }
        
        else
		return -1;
	}

//Chateinladung absetzen
function insert_chatinv ($rec_uname, $user_id='') {
	global $user;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;

	if (!$user_id)
		$user_id = $user->id;

	$db->query ("SELECT username,CONCAT(Vorname,' ',Nachname) AS fullname FROM auth_user_md5 WHERE user_id = '".$user_id."' ");
	$db->next_record();

	$db2->query ("SELECT user_id FROM auth_user_md5 WHERE username = '".$rec_uname."' ");
	$db2->next_record();

	$m_id=md5(uniqid("voyeurism"));
	$db3->query ("INSERT INTO globalmessages SET message_id='$m_id', user_id_rec='$rec_uname', user_id_snd='".$db->f("username")."', mkdate='".time()."', message='chat_with_me' ");

     //Benachrichtigung in alle Chatr�ume schicken, noch nicht so sinnvoll :)
          $chatServer=new ChatShmServer;
          $myUser=$chatServer->chatUser[$db2->f("user_id")];
          $chatMsg="Du wurdest von <b>".$db->f("fullname")." (".$db->f("username").")</b> in den Chat eingeladen !";
          if (is_array($myUser))        
	          foreach($myUser as $chatid=>$wert)
        	       if ($chatid["action"])
                	    $chatServer->addMsg("system:".$db2->f("user_id"),$chatid,$chatMsg);


	if ($db3->affected_rows())
		return TRUE;
	else
		return FALSE;
        }

function delete_chatinv($username){
    $this->db->query("DELETE FROM globalmessages WHERE user_id_rec='$username' AND message LIKE '%chat_with_me%'");
    return $this->db->affected_rows();
}


//Buddy aus der Buddyliste loeschen        
function delete_buddy ($username) {
	global $my_buddies;

	unset ($my_buddies[$username]);
	if ((count($my_buddies)) == 0)
		$my_buddies=FALSE;
	return TRUE;
	}

//Buddy zur Buddyliste hinzufuegen
function add_buddy ($username, $group) {
	global $my_buddies;

	if (!$my_buddies[$username]) {
		$my_buddies[$username]=array("username"=>$username, "group"=>$group);
		return TRUE;
		}
	else
		return FALSE;
	}
}
?>
