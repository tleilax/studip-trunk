<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ChatShmServer.class.php
// class definfition for the chat server
// Copyright (c) 2002 Andr� Noack <andre.noack@gmx.net>
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
require_once $ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CHAT."/chat_config.php";
require_once $ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CHAT."/ChatFileServer.class.php";
require_once $ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CHAT."/ChatShmServer.class.php";
require_once $ABSOLUTE_PATH_STUDIP . "/visual.inc.php";
/**
*  Chat Server Klasse
* 
*
* @access	public	
* @author	Andr� Noack <andre.noack@gmx.net>
* @version	$Id$
* @package	Chat
*/
class ChatServer {

	var $that; // Container Objekt
	var $chatUser = array();
	var $chatDetail = array();
	var $caching = FALSE;
	
	function &GetInstance($class_name){
		static $object_instance;
		if (!is_object($object_instance[$class_name])){
			$object_instance[$class_name] = new $class_name();
		}
		$object_instance[$class_name]->restore();
		return $object_instance[$class_name];
	}
	
	function ChatServer(){
		$this->restore();
	}
	
	function restore(){
		if ($this->caching) return;
		$this->that->restore(&$this->chatDetail,CHAT_DETAIL_KEY);
		if (!is_array($this->chatDetail))
			$this->chatDetail=array();
	}
	
	function store(){
		$this->that->store(&$this->chatDetail,CHAT_DETAIL_KEY);
	}
	
	function addChat($rangeid, $chatname = "StudIP Global Chat",$password = false){
		if ($this->isActiveChat($rangeid)){
			return false;
		}
		$this->chatDetail[$rangeid]["name"] = $chatname;
		$this->chatDetail[$rangeid]["messages"] = array();
		$this->chatDetail[$rangeid]["password"] = $password;
		$this->chatDetail[$rangeid]["users"] = array();
		$this->store();
		return true;
	}
	
	function removeChat($rangeid){
		unset($this->chatDetail[$rangeid]);
		$this->store();
		return true;
	}
	
	function isActiveChat($rangeid){
		$this->restore();
		return $this->chatDetail[$rangeid]["name"];
	}
	
	function getActiveUsers($rangeid){
		$chat_users = $this->getUsers($rangeid);
		$a_time = time();
		$anzahl = 0;
		foreach ($chat_users as $userid => $detail){
			if ((!$detail["perm"] && ($a_time-$detail["action"]) > CHAT_IDLE_TIMEOUT) ||
				($detail["perm"] && ($a_time-$detail["action"]) > CHAT_ADMIN_IDLE_TIMEOUT)){
				$this->removeUser($userid,$rangeid); 
			}
			else 
				++$anzahl;
		}
		return $anzahl;
	}
	
	function getUsers($rangeid){
		$this->restore();
		return (is_array($this->chatDetail[$rangeid]['users'])) ? $this->chatDetail[$rangeid]['users'] : array();
	}
	
	function getIdFromNick($rangeid,$nick){
		$this->restore();
		foreach($this->chatDetail[$rangeid]['users'] as $userid => $detail){
			if ($detail["nick"] == $nick)
				return $userid;
		}
		return false;
	}

	function addUser($userid,$rangeid,$nick,$fullname,$chatperm,$color = "black"){
		if ($this->isActiveUser($userid,$rangeid))
			return false;
		$this->chatDetail[$rangeid]["users"][$userid]["action"] = time();
		$this->chatDetail[$rangeid]["users"][$userid]["nick"] = $nick;
		$this->chatDetail[$rangeid]["users"][$userid]["fullname"] = $fullname;
		$this->chatDetail[$rangeid]["users"][$userid]["perm"] = $chatperm;
		if (!$this->chatDetail[$rangeid]["users"][$userid]["color"])
			$this->chatDetail[$rangeid]["users"][$userid]["color"] = $color;
		$this->addMsg("system",$rangeid, sprintf(_("%s hat den Chat betreten!"),htmlReady($fullname." (".$nick.")")));
		$this->store();
		return true;
	}
	
	function getFullname($userid,$rangeid){
		return $this->chatDetail[$rangeid]["users"][$userid]["fullname"];
	}
	
	function getNick($userid,$rangeid){
		return $this->chatDetail[$rangeid]["users"][$userid]["nick"];
	}
	
	function getPerm($userid,$rangeid){
		return $this->chatDetail[$rangeid]["users"][$userid]["perm"];
	}
	
	function getAction($userid,$rangeid){
		return $this->chatDetail[$rangeid]["users"][$userid]["action"];
	}
	
	function removeUser($userid,$rangeid){
		if (!$this->isActiveUser($userid,$rangeid))
			return false;
		$this->removeCmdMsg($userid,$rangeid);
		$this->addMsg("system",$rangeid,sprintf(_("%s hat den Chat verlassen!"),htmlReady($this->getFullname($userid,$rangeid) ." (" . $this->getNick($userid,$rangeid) .")")));
		unset($this->chatDetail[$rangeid]["users"][$userid]);
		$this->store();
		return true;
	}

	function isActiveUser($userid,$rangeid){
		$this->restore();
		return $this->getAction($userid,$rangeid);
	}

	function addMsg($userid,$rangeid,$msg){
		$this->restore();
		$anzahl = count($this->chatDetail[$rangeid]["messages"]);
		if ($anzahl > CHAT_MAX_MSG) {
			array_shift($this->chatDetail[$rangeid]["messages"]);
			--$anzahl;
		}
		$this->chatDetail[$rangeid]["messages"][$anzahl] = array($userid,$msg,$this->getMsTime());
		if (substr($userid,0,6)!="system"){
			$this->chatDetail[$rangeid]["users"][$userid]["action"] = time();
		}
		$this->store();
	}
	
	function getMsg($rangeid,$msStamp=0){
		$this->restore();
		if (is_array($this->chatDetail[$rangeid]["messages"])){
			if ($msStamp) {
				$anzahl = count($this->chatDetail[$rangeid]["messages"]);
				for ($i = 0; $i < $anzahl; ++$i){
					if ($this->chatDetail[$rangeid]["messages"][$i][2] > $msStamp)
					break;
				}
			} else {
				$i = 0;
			}
			if ($i == $anzahl && $i != 0){
				return false;
			} else {
				return array_slice($this->chatDetail[$rangeid]["messages"],$i);
			}
		}
		return false;
	}
	
	function removeCmdMsg($userid,$rangeid){
		$this->restore();
		$anzahl = count($this->chatDetail[$rangeid]["messages"]);
		for ($i = 0;$i < $anzahl; ++$i){
			if ($this->chatDetail[$rangeid]["messages"][$i][0] == $userid){
				if (substr($this->chatDetail[$rangeid]["messages"][$i][1],0,1) == "/") {
					$this->chatDetail[$rangeid]["messages"][$i]=array("system:system"," ",0);
				}
			}
		}
		$this->store();
	}

	function logoutUser($userid){
		if(is_array($this->chatDetail)){
			foreach($this->chatDetail as $chatid => $detail)
				if ($this->removeUser($userid,$chatid))
					$this->addMsg("system",$chatid,sprintf(_("%s hat sich aus StudIP ausgeloggt!"),htmlReady($this->getFullname($userid,$chatid) . " (".$this->getNick($userid,$chatid).")")));
		}
		return true;
	}
	
	function getMsTime(){
			$microtime = explode(' ', microtime());
			return (double)($microtime[1].substr($microtime[0],1));
	}


}
?>
