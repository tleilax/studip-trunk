<?php
/**
* chat client
* 
* prints messages, handles all communication
* 
*
* @author		Andr� Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup	chat_modules
* @module		chat_client
* @package		Chat
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_nicklist.php
// Shows the nicklist
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

/**
* Close the actual window if PHPLib shows login screen
* @const CLOSE_ON_LOGIN_SCREEN
*/
define("CLOSE_ON_LOGIN_SCREEN",true);
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

//chat eingeschaltet?
if (!$CHAT_ENABLE) {
	//page_close();
	die;
}

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php";
//Studip includes
require_once $ABSOLUTE_PATH_STUDIP."msg.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."visual.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."messaging.inc.php";

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

//Hilfsfunktion, druckt script tags
function printJs($code){
	echo "<script type=\"text/Javascript\">$code</script>\n";
}

function fullNick($userid) {
	global $chatServer,$chatid;
	return $chatServer->getNick($userid,$chatid);
}

//Hilfsfunktion, unterscheidet zwischen �ffentlichen und privaten System Nachrichten
function chatSystemMsg(&$msg,&$output){
	global $user,$chatServer;
	$id = substr(strrchr ($msg[0],":"),1);
	if (!$id) {
		printJs("if (parent.frames['frm_nicklist'].location.href) parent.frames['frm_nicklist'].location.href = parent.frames['frm_nicklist'].location.href;");
		$output = strftime("%T",floor($msg[2]))."<i> [chatbot] $msg[1]</i><br>";
	} elseif ($user->id == $id){
		$output = strftime("%T",floor($msg[2]))."<i> [chatbot] $msg[1]</i><br>";
	}
	return;
}
//Die Funktionen f�r die Chatkommandos, f�r jedes Kommando in $chatCmd muss es eine Funktion geben
function chatCommand_color($msgStr,&$output){
	global $user,$chatServer,$chatid;
	if (!$msgStr || $msgStr == "\n" || $msgStr == "\r")
		return;
	$chatServer->chatDetail[$chatid]['users'][$user->id]["color"] = htmlReady($msgStr);
	$chatServer->store();
	$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Ihre %sSchriftfarbe</font> wurde ge&auml;ndert!"),"<font color=\"".htmlReady($msgStr)."\">"));
	return;
}

function chatCommand_quit($msgStr,&$output){
	global $user,$chatServer,$chatid,$userQuit;
	$full_nick = fullNick($user->id);
	$chatServer->removeUser($user->id,$chatid);
	$chatServer->addMsg("system",$chatid,sprintf(_("%s verl&auml;sst den Chat und sagt: %s"),htmlReady($full_nick),formatReady($msgStr)));
	echo _("Sie haben den Chat verlassen!") . "<br>";
	echo _("Das Chatfenster wird in 3 s geschlossen!") . "<br>";
	printJs("window.scrollBy(0, 500);");
	printJs("setTimeout('parent.close()',3000);");
	flush();
	$userQuit=true;  //dirty deeds...

}

function chatCommand_me($msgStr,&$output){
	global $user,$chatServer,$chatid;
	$chatServer->addMsg("system",$chatid,"<b>".htmlReady(fullNick($user->id))." ".formatReady($msgStr)."</b>");
}

function chatCommand_help($msgStr,&$output){
	global $user,$chatServer,$chatid,$chatCmd;
	$str = _("M�gliche Chat Kommandos:");
	foreach($chatCmd as $cmd => $text)
		$str .= "<br><b>/$cmd</b>" . htmlReady($text);
	$chatServer->addMsg("system:$user->id",$chatid,$str);
}

function chatCommand_private($msgStr,&$output){
	global $user,$chatServer,$chatid;
	$recnick = trim(substr($msgStr." ",0,strpos($msgStr," ")));
	$recid = $chatServer->getIdFromNick($chatid,$recnick);
	$privMsgStr = trim(strstr($msgStr," "));
	if ($chatServer->isActiveUser($recid,$chatid)){
		$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Ihre Botschaft an %s wurde &uuml;bermittelt."),htmlReady(fullNick($recid))));
		$chatServer->addMsg("system:$recid",$chatid,sprintf(_("Eine geheime Botschaft von %s"),htmlReady(fullNick($user->id)))
			.":<br></i><font color=\"".$chatServer->chatDetail[$chatid]['users'][$user->id]["color"]."\"> " . formatReady($privMsgStr)
			."</font>");
	} elseif ($recnick) {
		$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("<b>%s</b> ist in diesem Chat nicht bekannt."),$recnick));
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler, falsche Kommandosyntax!"));
	}
}

function chatCommand_kick($msgStr,&$output){
	global $user,$chatServer,$chatid;
	$kicknick = trim(substr($msgStr." ",0,strpos($msgStr," ")-1));
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
		unset($kickids[$user->id]);
		if (is_array($kickids) && count($kickids)){
			foreach ($kickids as $kickid => $detail){
				if ($chatServer->removeUser($kickid,$chatid)){
					$chatServer->addMsg("system",$chatid,sprintf(_("%s wurde von %s aus dem Chat geworfen!"),htmlReady($detail['nick']),htmlReady(fullNick($user->id))));
				}
			}
		} else {
			$chatServer->addMsg("system:$user->id",$chatid,_("Kein Nutzer gefunden, der entfernt werden k&ouml;nnte."));
		}
	} elseif (!$kicknick){
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler, falsche Kommandosyntax!"));
	} else {
		$chatServer->addMsg("system:$user->id",$chatid,_("Sie d&uuml;rfen hier niemanden rauswerfen!"));
	}
}

function chatCommand_sms($msgStr,&$output){
	global $user,$chatServer,$chatid;
	$recUserName = trim(substr($msgStr." ",0,strpos($msgStr," ")));
	$smsMsgStr = trim(strstr($msgStr," "));
	if (!$recUserName || !$smsMsgStr){
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler, falsche Kommandosyntax!"));
		return;
	}
	if (messaging::insert_sms($recUserName,$smsMsgStr))
		$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Ihre SMS an <b>%s</b> wurde &uuml;bermittelt."),$recUserName));
	else
		$chatServer->addMsg("system:$user->id",$chatid,_("Fehler, deine SMS konnte nicht &uuml;bermittelt werden!"));
}

	 
//Simpler Kommandoparser
function chatCommand(&$msg,&$output){
	global $user,$chatServer,$chatCmd,$chatid;
	$cmdStr = trim(substr($msg[1]." ",1,strpos($msg[1]," ")-1));
	$msgStr = trim(strstr($msg[1]," "));
	if (!$chatCmd[$cmdStr]) {
		$chatServer->addMsg("system:$user->id",$chatid,sprintf(_("Unbekanntes Kommando: <b>%s</b>"),htmlReady($cmdStr)));
		return;
	}
	$chatFunc = "chatCommand_" . $cmdStr;
	$chatFunc($msgStr,&$output);       //variabler Funktionsaufruf!
}


//Die Ausgabeschleife, l�uft endlos wenn keine Abbruchbedingung erreicht wird
function outputLoop($chatid){
	global $user,$chatServer,$userQuit;
	$lastPingTime = 0;
	$lastMsgTime = time()-1;
	set_time_limit(0);       //wir sind nicht zu stoppen...
	ignore_user_abort(1);    //es sei denn wir werden brutal ausgebremst :)

	while(!connection_aborted()){

		$currentMsTime = $chatServer->getMsTime();
		//Timeout vorbeugen
		if (($currentMsTime - $lastPingTime) > CHAT_TO_PREV_TIME) {
			echo"<!-- -->\n";
			flush();
			$lastPingTime=$currentMsTime;
		}
		//Gibt es neue Nachrichten ?
		$newMsg = $chatServer->getMsg($chatid,$lastMsgTime);
		if ($newMsg) {
			foreach($newMsg as $msg){
				$output = "";
				if (substr($msg[0],0,6) == "system") {
					 chatSystemMsg(&$msg,&$output);
					 if (!$output) 
					 	continue;
				} elseif (substr($msg[1],0,1) == "/") {
					if ($msg[0] == $user->id) 
						chatCommand(&$msg,&$output);
					if (!$output) 
						continue;
				}
				if (!$output){
					$output = "<font color=\"".$chatServer->chatDetail[$chatid]['users'][$msg[0]]["color"]."\">"
					. strftime("%T",floor($msg[2]))." [".fullNick($msg[0])."] "
					. formatReady($msg[1])."</font><br>";
					}
				echo $output;
				printJs("window.scrollBy(0, 500);");
				flush();
				$lastPingTime = $currentMsTime;
			}
			$lastMsgTime = $msg[2];
		}

		if ($userQuit) break; //...done dirt cheap
		
//wurden wir zwischenzeitlich gekickt?
		if (!$chatServer->isActiveUser($user->id,$chatid)){
			echo _("Sie mussten den Chat verlassen...") ."<br>";
			echo "<a href=\"javascript:parent.location.href='chat_login.php?chatid=$chatid';\">"
					._("Hier</a> k&ouml;nnen sie versuchen wieder einzusteigen.<br>");
			printJs("window.scrollBy(0, 500);");
			flush();
			break;
		}
//Allzulange rumidlen soll keiner
		if ((!$chatServer->getPerm($user->id,$chatid) && (time()-$chatServer->getAction($user->id,$chatid)) > CHAT_IDLE_TIMEOUT) ||
			($chatServer->getPerm($user->id,$chatid) && (time()-$chatServer->getAction($user->id,$chatid)) > CHAT_ADMIN_IDLE_TIMEOUT)){
			echo _("<b>IDLE TIMOUT</b> - sie wurden aus dem Chat entfernt!<br>");
			$chatServer->removeUser($user->id,$chatid);
			echo "<a href=\"javascript:parent.location.href='chat_login.php?chatid=$chatid';\">"
				._("Hier</a> k&oumlnnen sie versuchen wieder einzusteigen.<br>");
			printJs("window.scrollBy(0, 500);");
			flush();
			break;
		}

		usleep(CHAT_SLEEP_TIME);
	}
	//echo "Output beendet";

}

//main()

$chatServer =& ChatServer::GetInstance($CHAT_SERVER_NAME);
$userQuit=false;

?>
<html>
<head>
	<title>ChatAusgabe</title>
	<?php include $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_style.inc.php";?>
</head>
<body style="font-size:10pt;">
<?
if (!$chatServer->isActiveUser($user->id,$chatid)) {
	?><table width="100%"><tr><?
	my_error(_("Sie sind nicht in diesem Chat angemeldet!"),"chat",1,false);
	?></tr></table></body></html><?
//PHPLib Session Variablen unangetastet lassen
	//page_close();
	die;
}
echo "\n<b>" . sprintf(_("Hallo %s,<br> willkommen im Raum: %s"),htmlReady(fullNick($user->id)),
	htmlReady($chatServer->chatDetail[$chatid]["name"])) . "</b><br>";

register_shutdown_function("chatLogout");   //f�r korrektes ausloggen am Ende!
outputLoop($chatid);
$userid = $user->id; //konservieren f�r shutdown_function
//PHPLib Session Variablen unangetastet lassen
//page_close();



//shutdown funktion, wird automatisch bei skriptende aufgerufen
function chatLogout(){
	global $userid,$chatid,$chatServer;
	$chatServer->removeUser($userid,$chatid);
}
?>

