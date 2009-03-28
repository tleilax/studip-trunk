<?
/**
* Chat Functions
*
*
* @author		Andr� Noack <noack@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	chat_modules
* @module		chat_func_inc
* @package		Chat
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_func_inc.php
//
// Copyright (c) 2003 Andr� Noack <noack@data-quest.de>
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

require_once $GLOBALS['RELATIVE_PATH_CHAT'].'/ChatServer.class.php';
//Studip includes
require_once 'lib/visual.inc.php';
require_once 'lib/messaging.inc.php';
require_once 'lib/functions.php';
require_once 'lib/contact.inc.php';

function chat_kill_chat($chatid){
	if ($GLOBALS['CHAT_ENABLE']){
		if (chat_get_entry_level($chatid) == "admin"){
			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
			$chatServer->caching = false;
			$chatServer->removeChat($chatid);
			$chatServer->caching = true;
		}
	}
}

function chat_get_chat_icon($chatter,$chatinv,$is_active,$as_icon = false){
	if ($GLOBALS['CHAT_ENABLE']){
			$pic_prefix = ($as_icon) ? "icon-" : "";
			$pic_path = $GLOBALS['ASSETS_URL']."images/";
			$image = "<img border=\"0\" src=\"" . $pic_path . $pic_prefix;
			if (!$chatter){
				$image .= "chat1.gif\"" . tooltip(_("Dieser Chatraum ist leer"));
			} elseif ($chatinv){
				$image .= "chateinladung.gif\"" . tooltip(_("Sie haben eine g�ltige Einladung f�r diesen Chatraum")
					. " " . (($chatter == 1) ? _("Es ist eine Person in diesem Chatraum.") : sprintf(_("Es sind %s Personen in diesem Chatraum"),$chatter)));
			} elseif ($chatter == 1 && $is_active) {
				$image .= "chat3.gif\"" . tooltip(_("Sie sind alleine in diesem Chatraum"));
			} else {
				$image .= "chat2.gif\"" . tooltip(($chatter == 1) ? _("Es ist eine Person in diesem Chatraum.") : sprintf(_("Es sind %s Personen in diesem Chatraum"),$chatter));
			}
			$image .= "align=\"texttop\">";
			return $image;
	} else {
		return false;
	}
}

function chat_get_entry_level($chatid){
	global $perm,$user,$auth;
	$object_type = get_object_type($chatid);
	$chat_entry_level = false;
	if (!$perm->have_perm("root")){;
		switch($object_type){
			case "user":
			if ($chatid == $user->id){
				$chat_entry_level = "admin";
			} elseif (CheckBuddy($auth->auth['uname'], $chatid)){
				$chat_entry_level = "user";
			}
			break;

			case "sem" :
			if ($perm->have_studip_perm("tutor",$chatid)){
				$chat_entry_level = "admin";
			} elseif ($perm->have_studip_perm("user",$chatid)){
				$chat_entry_level = "user";
			}
			break;

			case "inst" :
			case "fak" :
			if ($perm->have_studip_perm("admin",$chatid)){
				$chat_entry_level = "admin";
			} elseif ($perm->have_studip_perm("autor",$chatid)){
				$chat_entry_level = "user";
			}
			break;

			default:
			if ($chatid == "studip"){
				$chat_entry_level = "user";
			}
		}
	} else {
		$chat_entry_level = "admin";
	}
	return $chat_entry_level;
}

function chat_get_name($chatid){
	$db = new DB_Seminar();
	if ($chatid != "studip"){
		$db->query("SELECT Name from seminare WHERE Seminar_id='$chatid'");
		if (!$db->next_record()){
			$db->query("SELECT Name from Institute WHERE Institut_id='$chatid'");
			if (!$db->next_record()){
				$db->query("SELECT " . $GLOBALS['_fullname_sql']['full'] ." AS Name FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE a.user_id='$chatid'");
				if (!$db->next_record()){
					return false;
				}
			}
		}
		return $db->f("Name");
	} else {
		return "Stud.IP Global Chat";
	}
}

function chat_show_info($chatid){
	global $auth;
		if ($GLOBALS['CHAT_ENABLE']){
			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
			$sms = new messaging();
			$chatter = $chatServer->isActiveChat($chatid);
			$chatinv = $sms->check_chatinv($chatid);
			$is_active = $chatServer->isActiveUser($auth->auth['uid'],$chatid);
			$chatname = ($chatter) ? $chatServer->chatDetail[$chatid]['name'] : chat_get_name($chatid);
			if (chat_get_entry_level($chatid) || $is_active || $chatinv){
				//Ausgabe der Kopfzeile
				chat_get_javascript();
				echo "\n<table border=\"0\" bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" width=\"100%\" >";
				echo "\n<tr><td class=\"topic\" colspan=\"2\" width=\"100%\">";
				echo "\n" . chat_get_chat_icon($chatter,$chatinv,$is_active);
				echo "\n<b>&nbsp;" . _("Chatraum:") . "&nbsp;" . htmlReady($chatname) . "</b></td></tr>";
				echo chat_get_content($chatid,$chatter,$chatinv,$chatServer->chatDetail[$chatid]['password'],$is_active,$chatServer->getUsers($chatid));
				echo "\n</table>";
				return true;
			}
		}
		return false;
}

function chat_get_content($chatid,$chatter,$chatinv,$password,$is_active,$chat_user){
	$pic_path = $GLOBALS['ASSETS_URL']."images/";
	$ret = "\n<tr><td class=\"steel1\" colspan=\"2\" width=\"100%\">&nbsp;</td></tr>";
	$ret .= "\n<tr><td class=\"steel1\" width=\"50%\" valign=\"center\"><blockquote><font size=\"-1\">";
	if (($entry_level = chat_get_entry_level($chatid)) || $chatinv){
		if (!$is_active){
			$ret .= "<a href=\"#\" onClick=\"javascript:return open_chat('$chatid');\">";
			$ret .= "<img border=\"0\" align=\"absmiddle\" src=\"$pic_path/chat1.gif\" " . tooltip(_("Diesen Chatraum betreten")) ." ></a>&nbsp;&nbsp;";
			$ret .= sprintf(_("Sie k&ouml;nnen diesen Chatraum %sbetreten%s."),"<a href=\"#\" onClick=\"javascript:return open_chat('$chatid');\">","</a>");
			if ($chatinv){
				$ret .= "&nbsp;" . _("(Sie wurden eingeladen.)");
			}
		} else {
			$ret .= "<img border=\"0\" align=\"absmiddle\" src=\"$pic_path/chat1.gif\" " . tooltip(_("Sie haben diesen Chatraum bereits betreten.")) ." >&nbsp;&nbsp;";
			$ret .= _("Sie haben diesen Chatraum bereits betreten.");
		}
		if ($password){
			$ret .= "<br><img border=\"0\" align=\"absmiddle\" src=\"$pic_path/closelock.gif\" >&nbsp;&nbsp;";
			$ret .= _("Dieser Chatraum ist mit einem Passwort gesichert.");
		}
		if ($chatter && $entry_level == "admin"){
			$ret .= "<br><a href=\"" . $GLOBALS['PHP_SELF'] . "?kill_chat=$chatid\">";
			$ret .= "<img border=\"0\" align=\"absmiddle\" src=\"$pic_path/trash.gif\" " . tooltip(_("Diesen Chatraum leeren")) ." ></a>&nbsp;&nbsp;";
			$ret .= sprintf(_("Diesen Chatraum %sleeren%s"),"<a href=\"" . $GLOBALS['PHP_SELF'] . "?kill_chat=$chatid\">","</a>");
		}
		if ($entry_level == "admin" && count($_SESSION['chat_logs'][$chatid])){
			$ret .= '<br>'._("Ihre gespeicherten Aufzeichnungen:");
			$ret .= '<ol style="margin:3px;padding:3px;">';
			foreach($_SESSION['chat_logs'][$chatid] as $log_id => $chat_log){
				$ret .= '<li style="list-style-image:url('.$pic_path.'file.gif);list-style-position:inside">';
				$ret .= '<a href="#" onclick="window.open(\'chat_dispatcher.php?target=chat_dummy.php&log_id='.$log_id.'&chatid='.$chatid.'\', \'chat_dummy\', \'scrollbars=no,width=100,height=100,resizable=no\');return false;">';
				$ret .= _("Start") . ': ' . strftime('%X', $chat_log['start']) . ', ' . (int)count($chat_log['msg']) . ' ' . _("Zeilen");
				$ret .= '</li>';
				$ret .= '</a>';
			}
			$ret .= '</ol>';
			
		}
	} else {
		$ret .= "<img border=\"0\" align=\"absmiddle\" src=\"$pic_path/nochat.gif\" >&nbsp;&nbsp;";
		$ret .= _("Um diesen Chatraum zu betreten, brauchen sie eine g&uuml;ltige Einladung.");
	}
	$ret .= "\n</font></blockquote></td><td class=\"steel1\" width=\"50%\" valign=\"center\"><blockquote><font size=\"-1\">";
	if (!$chatter){
		$ret .= _("Dieser Chatraum ist leer.");
	} else {
		$ret .= ($chatter == 1) ? _("Es ist eine Person in diesem Chatraum.") : sprintf(_("Es sind %s Personen in diesem Chatraum"),$chatter);
		$ret .= "<br>(";
		$c = 0;
		foreach ($chat_user as $chat_user_id => $detail){
			$ret .= "<a href=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}about.php?username={$detail['nick']}\">"
			. htmlReady($detail['fullname']) . "</a>";
			if (++$c != $chatter){
				$ret .= ", ";
			}
		}
		$ret .= ")";
	}
	$ret .= "</font></blockquote></td></tr>";
	$ret .= "\n<tr><td class=\"steel1\" colspan=\"2\" width=\"100%\">&nbsp;</td></tr>";
	return $ret;
}

function chat_get_online_icon($user_id = false, $username = false, $pref_chat_id = false){
	global $i_page;
	if ($GLOBALS['CHAT_ENABLE']) {
		if ($user_id && !$username){
			$username = get_username($user_id);
		}
		if (!$user_id && $username){
			$user_id = get_userid($username);
		}
		if (!$user_id && !$username){
			return false;
		}
		$pic_path = $GLOBALS['ASSETS_URL']."images/";
		$stud_path = $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'];
		$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
		$admin_chats = $chatServer->getAdminChats($GLOBALS['auth']->auth['uid']);
		if ($tmp_num_chats = $chatServer->chatUser[$user_id]) {
			$ret = "<a href=\"{$stud_path}chat_online.php?search_user={$user_id}\"><img src=\"{$pic_path}chat2.gif\""
			.tooltip(($tmp_num_chats == 1) ? _("Dieser User befindet sich in einem Chatraum.") : sprintf(_("Dieser User befindet sich in %s Chatr�umen"),$tmp_num_chats))
			." border=\"0\"></a>";
		} elseif (is_array($admin_chats)) {
			$ret = "<a href=\"{$stud_path}sms_send.php?sms_source_page=$i_page&cmd=write_chatinv&rec_uname=$username";
			if ($pref_chat_id && $admin_chats[$pref_chat_id]){
				$ret .= "&selected_chat_id=$pref_chat_id";
			}
			$ret .= "\"><img src=\"{$pic_path}chat1.gif\" ".tooltip(_("zum Chatten einladen"))." border=\"0\"></a>";
		} else {
			$ret = "<img src=\"{$pic_path}chat1.gif\" " . tooltip(_("Sie haben in keinem aktiven Chatraum die Berechtigung andere NutzerInnen einzuladen!")) . " border=\"0\">";
		}
		return $ret;
	} else {
		return "&nbsp;";
	}
}

function chat_get_javascript(){
	global $auth;
	echo "\t\t<script type=\"text/javascript\">\n";
	echo "\t\tfunction open_chat(chatid) {\n";
	echo "\t\t\tif(!chatid){\n";
	printf ("\t\t\t\talert('%s');\n", _("Sie sind bereits in diesem Chat angemeldet!"));
	echo "\t\t\t} else {\n\t\t\tfenster=window.open(\"chat_dispatcher.php?target=chat_login.php&chatid=\" + chatid,\"chat_\" + chatid + \"_".$auth->auth["uid"]."\",\"scrollbars=no,width=640,height=480,resizable=yes\");\n";
	echo "\t\t}\nreturn false;\n}\n";
	echo "\t\t</script>\n";
}

?>
