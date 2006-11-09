<?php
/**
* chat server functions for AJAX communication
*
* 
*
* @author		Andr� Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup		chat_modules
* @module		sajax_chat_functions
* @package		Chat
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sajax_chat_functions.php
// 
// Copyright (c) 2006 Andr� Noack <andre.noack@gmx.net>
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

if (!function_exists("ob_get_clean")) {
   function ob_get_clean() {
       $ob_contents = ob_get_contents();
       ob_end_clean();
       return $ob_contents;
   }
}

if (!is_object($chatServer)){
	page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
	$perm->check("user");
	require_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . 'visual.inc.php';
	require_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . 'functions.php';
	require_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . 'messaging.inc.php';
	require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php";

	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

	//chat eingeschaltet?
	if (!$CHAT_ENABLE) {
		page_close();
		die;
	}

	$chatServer =& ChatServer::GetInstance($CHAT_SERVER_NAME);
	$chatServer->caching = true;
} else {
	$sess->register('chat_logs');
	$sess->register('last_msg_times');
	$chat_logs[$chatid] = null;
	$last_msg_times[$chatid] = $chatServer->getMsTime();
	--$last_msg_times[$chatid][1];
}

$userQuit = false;

require("Sajax.php");

function get_chat_status($chatid){
	global $user, $chatServer, $chat_logs, $CANONICAL_RELATIVE_PATH_STUDIP;
	if (!$chatServer->isActiveUser($user->id,$chatid)) return ;
	ob_start();
	?>
	<table width="98%" border="0" bgcolor="white" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td width="80%" align="left" class="topic">
			<?
			if ($chatServer->getPerm($user->id,$chatid)){
				?>
				<a href="javascript:<?=(($chatServer->chatDetail[$chatid]['password']) ? "doUnlock();" : "doLock();")?>">
				<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/<?=(($chatServer->chatDetail[$chatid]['password']) ? "closelock.gif" : "openlock.gif")?>"
					border="0" align="absmiddle"
					<?=tooltip(($chatServer->chatDetail[$chatid]['password']) ? _("Zugangsschutz f�r diesen Chat aufheben") : _("Diesen Chat absichern"))?>>
				</a>
				<?
			} else {
				?>
				<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/<?=(($chatServer->chatDetail[$chatid]['password']) ? "closelock.gif" : "openlock.gif")?>"
					border="0" align="absmiddle"
					<?=tooltip(($chatServer->chatDetail[$chatid]['password']) ? _("Dieser Chat ist zugangsbeschr�nkt.") : _("Dieser Chat ist nicht zugangsbeschr�nkt."))?>>
				<?
			}
			if (count($chatServer->chatDetail[$chatid]['log'])){
				?>
				<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/logging.gif" border="0" align="absmiddle"
					<?=tooltip(_("Dieser Chat wird aufgezeichnet."))?>>
				<?
			}
			?>
			<b>Chat - <?=htmlReady($chatServer->chatDetail[$chatid]["name"])?></b>
			</td>
			<td width="20%" align="right" class="topic" >
			<?
			if ($chatServer->getPerm($user->id,$chatid)){
				if (!$chatServer->chatDetail[$chatid]['log'][$user->id] && is_array($chat_logs[$chatid]) && count($chat_logs[$chatid])){
					?>
					<a href="javascript:doLogSend();">
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/file.gif"
						border="0" hspace="5" align="absmiddle"
						<?=tooltip(_("Download des letzten Chatlogs"))?>>
					</a>
					<?
				}
				?>
				<a href="javascript:<?=(($chatServer->chatDetail[$chatid]['log'][$user->id]) ? "doLogStop();" : "doLogStart();")?>">
				<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/<?=(($chatServer->chatDetail[$chatid]['log'][$user->id]) ? "stop_log.gif" : "start_log.gif")?>"
					border="0" hspace="5" align="absmiddle"
					<?=tooltip(($chatServer->chatDetail[$chatid]['log'][$user->id]) ? _("Die Aufzeichnung f�r diesen Chat beenden.") : _("Eine Aufzeichnung f�r diesen Chat starten."))?>>
				</a>
				<?
			}
			?>
			<a href="javascript:printhelp();">
			<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/hilfe.gif" border=0 hspace="5" align="texttop" <?=tooltip(_("Chat Kommandos einblenden"))?>>
			</a>
			<a href="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>show_smiley.php" target=new>
			<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/asmile.gif" hspace="5" border=0 align="absmiddle" <?=tooltip(_("Alle verf�gbaren Smileys anzeigen"))?>>
			</a></td>
		</tr>
	</table>
	<?
	return ob_get_clean();
}

function get_chat_nicklist($chatid){
	global $user, $chatServer, $CANONICAL_RELATIVE_PATH_STUDIP;
	if (!$chatServer->isActiveUser($user->id,$chatid)) return ;
	ob_start();
	?>
	<table align="center" border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="2"  width="95%">
	<tr>
		<td align="center">
			<table align="center" border="0" cellpadding="1" cellspacing="1" width="100%">
				<tr>
					<td class="topic" align="center"><b>Nicklist</b></td>
				</tr>
				<?
				$is_admin = $chatServer->getPerm($user->id,$chatid);
				$chat_users = $chatServer->getUsers($chatid);
				foreach ($chat_users as $chatUserId => $chatUserDetail){
						if ($chatUserDetail["action"]){
							echo "\n<tr><td><span style=\"font-size:10pt\">";
							if ($chatUserDetail["perm"])  echo "<b>";
							echo "<a href=\"#\" ". tooltip(_("Homepage aufrufen"),false) 
								. "onClick=\"return coming_home('{$CANONICAL_RELATIVE_PATH_STUDIP}about.php?username=".$chatUserDetail["nick"]."')\">"
								. htmlReady($chatUserDetail["fullname"])."</a><br>";
							if ($chatUserId != $user->id){
								if ($is_admin){
									echo "\n<a href=\"#\" " . tooltip(_("diesen Nutzer / diese Nutzerin aus dem Chat werfen"),false) 
								. "onClick=\"document.inputform.chatInput.value='/kick "
								. $chatUserDetail["nick"] . " ';doSubmit();return false;\">#</a>&nbsp;";
								}
								echo "\n<a href=\"#\" " . tooltip(_("diesem Nutzer / dieser Nutzerin eine private Botschaft senden"),false) 
								. "onClick=\"document.inputform.chatInput.value='/private "
								. $chatUserDetail["nick"] . " ';document.inputform.chatInput.focus();return false;\">@</a>&nbsp;";
							}
							echo "(".$chatUserDetail["nick"].")";
							if ($chatUserDetail["perm"])  echo "</b>";
							echo "</span></td></tr>";
						}
				}
				?>
			</table>
		</td>
	</tr>
	</table>
	<?
	return ob_get_clean();
}

function get_chat_color_chooser($chatid){
	global $user, $chatServer, $chatColors;
	if (!$chatServer->isActiveUser($user->id,$chatid)) return ;
	ob_start();
	echo '<select name="chatColor" onChange="doColorChange();">';
	foreach($chatColors as $c){
		print "<option style=\"color:$c;\" value=\"$c\" ";
		if ($chatServer->chatDetail[$chatid]["users"][$user->id]["color"] == $c){
			$selected = true;
			print " selected ";
		}
		print ">$c</option>\n";
	}
	if (!$selected) {
		print "<option style=\"color:" . $chatServer->chatDetail[$chatid]["users"][$user->id]["color"].";\" 
		value=\"".$chatServer->chatDetail[$chatid]["users"][$user->id]["color"] . "\" selected>user</option>\n";
	}
	echo '</select>';
	return ob_get_clean();
}

function do_logout($chatid){
	global $user, $chatServer,$chat_logs,$last_msg_time,$do_page_close;
	if (!$chatServer->isActiveUser($user->id,$chatid)) return ;
	if ($chatid){
		$chatServer->caching = false;
		$chatServer->removeUser($user->id,$chatid);
		$chatServer->isActiveChat($chatid);
		unset($chat_logs[$chatid]);
		unset($last_msg_time[$chatid]);
		$user->set_last_action();
	}
}

function insert_message($chatid, $msg){
		global $user, $chatServer, $do_page_close;
		if (!$chatServer->isActiveUser($user->id,$chatid)) return ;
		if ($chatid){
			$chatServer->addMsg($user->id,$chatid,stripslashes($msg));
			$user->set_last_action();
		}
		return;
}

function check_and_get_messages($chatid){
	global $user,$chatServer,$last_msg_times, $chat_logs, $userQuit;
	
	$lastMsgTime =& $last_msg_times[$chatid];
	$chat_log =& $chat_logs[$chatid];

	//Gibt es neue Nachrichten ?
	$newMsg = $chatServer->getMsg($chatid,$lastMsgTime);
	if ($newMsg) {
		$output = "";
		foreach($newMsg as $msg){
			$system = '';
			if (substr($msg[0],0,6) == "system") {
				$system = chatSystemMsg($msg);
				if ($system){
					$output .= $system;
					if ($chatServer->chatDetail[$chatid]['log'][$user->id]){
						$chat_log[] = strftime("%H:%M:%S",$msg[2][1])." [chatbot] $msg[1]";
					}
				} else {
					continue;
				}
			} elseif (substr($msg[1],0,1) == "/") {
				if ($msg[0] == $user->id){
					$output .= chatCommand($msg, $chatid);
				}
				continue;
			}
			if (!$system){
				$output .= "<font color=\"".$chatServer->chatDetail[$chatid]['users'][$msg[0]]["color"]."\">"
				. strftime("%H:%M:%S",$msg[2][1])." [".fullNick($msg[0],$chatid)."] "
				. formatReady($msg[1])."</font><br>";
				if ($chatServer->chatDetail[$chatid]['log'][$user->id]){
					$chat_log[] = strftime("%H:%M:%S",$msg[2][1])." [".fullNick($msg[0],$chatid)."] " . $msg[1];
				}
			}
		}
		$lastMsgTime = $msg[2];
	}
	
	//wurden wir zwischenzeitlich gekickt?
	if (!$userQuit){
		if (!$chatServer->isActiveUser($user->id,$chatid)){
			$output .= '<!--<logout>-->' . _("Sie mussten den Chat verlassen...") ."<br>";
			$output .=  sprintf(_("%sHier%s k&ouml;nnen Sie versuchen wieder einzusteigen."),"<a href=\"javascript:location.reload();\">",'</a>').'<br>';
		} elseif ((!$chatServer->getPerm($user->id,$chatid) && (time()-$chatServer->getAction($user->id,$chatid)) > CHAT_IDLE_TIMEOUT) ||
		($chatServer->getPerm($user->id,$chatid) && (time()-$chatServer->getAction($user->id,$chatid)) > CHAT_ADMIN_IDLE_TIMEOUT)){
			$output .=  '<!--<logout>-->' . sprintf(_("%sIDLE TIMEOUT%s - Sie wurden aus dem Chat entfernt!"),'<b>','</b>').'<br>';
			$chatServer->removeUser($user->id,$chatid);
			$output .=  sprintf(_("%sHier%s k&ouml;nnen Sie versuchen wieder einzusteigen."),"<a href=\"javascript:location.reload();\">",'</a>'). '<br>';
		}
	}

	$chatServer->setHeartbeat($user->id, $chatid);

	return $output;
}

//Hilfsfunktion, druckt script tags
function printJs($code){
	echo "<script type=\"text/Javascript\">$code</script>\n";
}

function fullNick($userid, $chatid) {
	global $chatServer;
	return $chatServer->getNick($userid,$chatid);
}

//Hilfsfunktion, unterscheidet zwischen �ffentlichen und privaten System Nachrichten
function chatSystemMsg($msg){
	global $user,$chatServer;
	$id = substr(strrchr ($msg[0],":"),1);
	if (!$id) {
		$output = strftime("%H:%M:%S",$msg[2][1])."<!--<reload>--><i> [chatbot] $msg[1]</i><br>";
	} elseif ($user->id == $id){
		$output = strftime("%H:%M:%S",$msg[2][1])."<i> [chatbot] $msg[1]</i><br>";
	}
	return $output;
}

//Die Funktionen f�r die Chatkommandos, f�r jedes Kommando in $chatCmd muss es eine Funktion geben
function chatCommand_color($msgStr, $chatid){
	global $user,$chatServer;
	if (!$msgStr || $msgStr == "\n" || $msgStr == "\r")
		return;
	$chatServer->chatDetail[$chatid]['users'][$user->id]["color"] = htmlReady($msgStr);
	$chatServer->store();
	$chatServer->addMsg("system:$user->id",$chatid,'<!--<colorchange>-->'.sprintf(_("Ihre %sSchriftfarbe%s wurde ge&auml;ndert!"),"<font color=\"".htmlReady($msgStr)."\">", '</font>'));
	return;
}

function chatCommand_quit($msgStr, $chatid){
	global $user,$chatServer,$userQuit,$chat_logs;
	$full_nick = fullNick($user->id, $chatid);
	if ($chatServer->chatDetail[$chatid]['log'][$user->id]){
			chatCommand_log("stop", $chatid);
	}
	$chatServer->addMsg("system",$chatid,sprintf(_("%s verl&auml;sst den Chat und sagt: %s"),htmlReady($full_nick),formatReady($msgStr)));
	echo '<!--<logout>-->' . _("Sie haben den Chat verlassen!") . "<br>";
	if (is_array($chat_logs[$chatid])){
		echo '<!--<sendlog>-->' ._("Ihre letzte Aufzeichnung wird noch einmal zum Download angeboten.<br>Nachdem sie dieses Fenster schlie&szlig;en wird diese Aufzeichnung gel&ouml;scht.");
	} else {
		echo '<!--<close>-->' . _("Das Chatfenster wird in 3 Sekunden geschlossen!") . "<br>";
	} 
	$chatServer->removeUser($user->id,$chatid);
	$userQuit = true;
}

function chatCommand_me($msgStr, $chatid){
	global $user,$chatServer;
	$chatServer->addMsg("system",$chatid,"<b>".htmlReady(fullNick($user->id,$chatid))." ".formatReady($msgStr)."</b>");
}

function chatCommand_help($msgStr, $chatid){
	global $user,$chatServer,$chatCmd;
	$str = _("M&ouml;gliche Chat-Kommandos:");
	foreach($chatCmd as $cmd => $text)
		$str .= "<br><b>/$cmd</b>" . htmlReady($text);
	$chatServer->addMsg("system:$user->id",$chatid,$str);
}

function chatCommand_private($msgStr, $chatid){
	global $user,$chatServer;
	$recnick = trim(substr($msgStr." ",0,strpos($msgStr," ")));
	$recid = $chatServer->getIdFromNick($chatid,$recnick);
	$privMsgStr = trim(strstr($msgStr," "));
	if ($chatServer->isActiveUser($recid,$chatid)){
		$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Ihre Botschaft an %s wurde verschickt."),htmlReady(fullNick($recid,$chatid)))
			.":<br></i><font color=\"".$chatServer->chatDetail[$chatid]['users'][$user->id]["color"]."\"> " . formatReady($privMsgStr)
			."</font>");
		$chatServer->addMsg("system:$recid",$chatid,sprintf(_("Eine geheime Botschaft von %s"),htmlReady(fullNick($user->id,$chatid)))
			.":<br></i><font color=\"".$chatServer->chatDetail[$chatid]['users'][$user->id]["color"]."\"> " . formatReady($privMsgStr)
			."</font>");
	} elseif ($recnick) {
		$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("%s ist in diesem Chat nicht bekannt."),'<b>'.$recnick.'</b>'));
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Falsche Kommandosyntax!"));
	}
}

function chatCommand_kick($msgStr, $chatid){
	global $user,$chatServer;
	//$kicknick = trim(substr($msgStr." ",0,strpos($msgStr," ")-1));
	$kicknick = $msgStr;
	if ($chatServer->getPerm($user->id,$chatid) && $kicknick){
		$chat_users = $chatServer->getUsers($chatid);
		if ($kicknick != "all") {
			$kickid = $chatServer->getIdFromNick($chatid,$kicknick);
			if ($kickid){
				$kickids[$kickid] = $chat_users[$kickid];
			}
		} else {
			$kickids = $chat_users;
		}
		if (is_array($kickids)){
			foreach ($kickids as $kickid => $detail){
				if ($chatServer->getPerm($kickid,$chatid)){
					unset($kickids[$kickid]);
				}
			}
		}
		if (is_array($kickids) && count($kickids)){
			foreach ($kickids as $kickid => $detail){
				if ($chatServer->removeUser($kickid,$chatid)){
					$chatServer->addMsg("system",$chatid,sprintf(_("%s wurde von %s aus dem Chat geworfen!"),htmlReady($detail['nick']),htmlReady(fullNick($user->id,$chatid))));
				}
			}
		} else {
			$chatServer->addMsg("system:$user->id",$chatid,_("Kein(e) Nutzer(in) zum entfernen gefunden."));
		}
	} elseif (!$kicknick){
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Falsche Kommandosyntax!"));
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Sie d&uuml;rfen hier niemanden rauswerfen!"));
	}
}

function chatCommand_sms($msgStr, $chatid){
	global $user,$chatServer;
	$recUserName = trim(substr($msgStr." ",0,strpos($msgStr," ")));
	$smsMsgStr = trim(strstr($msgStr," "));
	if (!$recUserName || !$smsMsgStr){
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Falsche Kommandosyntax!"));
		return;
	}
	$msging = new messaging();
	if ($recUserName != get_username($user->id)) {
		if (get_visibility_by_username($recUserName) && $msging->insert_message(addslashes($smsMsgStr), $recUserName))
			$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Ihre Nachricht an %s wurde verschickt."),'<b>'.$recUserName.'</b>'));
		else
			$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Ihre Nachricht konnte nicht verschickt werden!"));
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Es macht keinen Sinn, sich selbst Nachrichten zu schicken!"));
	}
}

function chatCommand_invite($msgStr, $chatid){
	global $user,$chatServer;
	if ($chatServer->getPerm($user->id,$chatid)){
		$recUserName = trim(substr($msgStr." ",0,strpos($msgStr." "," ")));
		$smsMsgStr = trim(strstr($msgStr," "));
		if (!$recUserName){
			$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Falsche Kommandosyntax!"));
			return;
		}
		$msging = new messaging();
		if ($recUserName != get_username($user->id)) {
			if ($msging->insert_chatinv(addslashes($smsMsgStr), $recUserName, $chatid)) {
				$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Ihre Einladung an %s wurde verschickt."),'<b>'.$recUserName.'</b>'));
			} else {
				$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Ihre Einladung konnte nicht verschickt werden!"));
			}
		} else {
			$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Es macht keinen Sinn, sich selbst in den Chat einzuladen!"));
		}
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Sie d&uuml;rfen f&uuml;r diesen Chat keine Einladungen verschicken!"));
	}
}

function chatCommand_password($msgStr, $chatid){
	global $user,$chatServer;
	$password = $msgStr;
	if ($chatServer->getPerm($user->id,$chatid)){
		if ($password){
			$chatServer->addMsg("system",$chatid,sprintf(_("Dieser Chat wurde soeben von %s mit einem Passwort gesichert."),'<b>' . htmlReady(fullNick($user->id,$chatid)).'</b>'));
			$chatServer->chatDetail[$chatid]['password'] = $password;
			$chatServer->store();
		} elseif ($chatServer->chatDetail[$chatid]['password']){
			$chatServer->addMsg("system",$chatid,sprintf(_("Der Passwortschutz f&uuml;r diesen Chat wurde soeben von %s aufgehoben."),'<b>'.htmlReady(fullNick($user->id,$chatid)).'</b>'));
			$chatServer->chatDetail[$chatid]['password'] = false;
			$chatServer->store();
		} else {
			$chatServer->addMsg("system:$user->id",$chatid,_("Dieser Chat ist nicht mit einem Passwort gesichert."));
		}
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Sie d&uuml;rfen hier kein Passwort setzen!"));
	}
	
}

function chatCommand_lock($msgStr, $chatid){
	global $user,$chatServer;
	if ($chatServer->getPerm($user->id,$chatid)){
		chatCommand_password(md5($chatServer->chatDetail[$chatid]['id'] . ":" . uniqid("blablubb",1)), $chatid);
		chatCommand_kick("all", $chatid);
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Sie d&uuml;rfen diesen Chat nicht absichern!"));
	}
}

function chatCommand_unlock($msgStr, $chatid){
	global $user,$chatServer;
	if ($chatServer->getPerm($user->id,$chatid)){
		chatCommand_password("", $chatid);
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Sie d&uuml;rfen diesen Chat nicht entsichern!"));
	}
}

function chatCommand_log($msgStr, $chatid){
	global $user,$chatServer,$chat_logs;
	$chat_log =& $chat_logs[$chatid];
	$cmd = $msgStr;
	if ($chatServer->getPerm($user->id,$chatid)){
		if ($cmd == "start"){
			if ($chatServer->chatDetail[$chatid]['log'][$user->id]){
				$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Sie lassen bereits seit %s eine Aufzeichnung laufen."),date("H:i",$chatServer->chatDetail[$chatid]['log'][$user->id])));
			} else {
				$chatServer->addMsg("system",$chatid,sprintf(_("Es wurde soeben von %s eine Aufzeichnung gestartet."),'<b>'.htmlReady(fullNick($user->id,$chatid)).'</b>'));
				$chatServer->chatDetail[$chatid]['log'][$user->id] = time();
				$chatServer->store();
				$chat_log = array();
			}
		} elseif ($cmd == "stop"){
			if ($chatServer->chatDetail[$chatid]['log'][$user->id]){
				$chatServer->addMsg("system",$chatid,sprintf(_("Die Aufzeichnung von %s wurde beendet."),'<b>'.htmlReady(fullNick($user->id,$chatid)).'</b>'));
				$chatServer->addMsg("system:$user->id",$chatid, '<!--<sendlog>-->' . _("Ihre Aufzeichnug wurde beendet und wird zu Ihrem Browser geschickt."));
				$chat_log[] = $chatServer->chatDetail[$chatid]['log'][$user->id];
				$chat_log[] = time();
				//$chatServer->chatDetail[$chatid]['users'][$user->id]['log'] = $chat_log;
				unset($chatServer->chatDetail[$chatid]['log'][$user->id]);
				$chatServer->store();
			} else {
				$chatServer->addMsg("system:$user->id",$chatid,_("Sie haben keine Aufzeichnung gestartet."));
			}
		} elseif ($cmd == "send"){
			if ($chat_log){
				$chatServer->addMsg("system:$user->id",$chatid, '<!--<sendlog>-->' . _("Ihre Aufzeichnung wird zu Ihrem Browser geschickt."));
			} else {
				$chatServer->addMsg("system:$user->id",$chatid,_("Sie haben keine gespeicherte Aufzeichnung."));
			}
		} else {
			$chatServer->addMsg("system:$user->id",$chatid,_("Fehler: Falsche Kommandosyntax!"));
		}
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Sie d&uuml;rfen hier keine Aufzeichnung starten!"));
	}
}


//Simpler Kommandoparser
function chatCommand($msg, $chatid){
	global $user,$chatServer,$chatCmd;
	$cmdStr = trim(substr($msg[1]." ",1,strpos($msg[1]," ")-1));
	$msgStr = trim(strstr($msg[1]," "));
	if (!$chatCmd[$cmdStr]) {
		$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Unbekanntes Kommando: %s"),'<b>'.htmlReady($cmdStr).'</b>'));
		return;
	}
	$chatFunc = "chatCommand_" . $cmdStr;
	ob_start();
	$chatFunc($msgStr, $chatid);       //variabler Funktionsaufruf!
	return ob_get_clean();
}
$GLOBALS['sajax_remote_uri'] = 'sajax_chat_functions.php';
$GLOBALS['sajax_request_type'] = 'POST';
$GLOBALS['sajax_debug_mode'] = 0;
sajax_init();
sajax_export("get_chat_status", "get_chat_nicklist", "do_logout","insert_message","get_chat_color_chooser","check_and_get_messages");
sajax_handle_client_request();
?>
