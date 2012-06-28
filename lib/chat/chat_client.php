<?php
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* chat client
*
* prints messages, handles all communication
*
*
* @author       Andr� Noack <andre.noack@gmx.net>
* @access       public
* @modulegroup  chat_modules
* @module       chat_client
* @package      Chat
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
include ('lib/seminar_open.php'); // initialise Stud.IP-Session

require_once $RELATIVE_PATH_CHAT.'/ChatServer.class.php';
//Studip includes
require_once 'lib/msg.inc.php';
require_once 'lib/visual.inc.php';
require_once 'lib/messaging.inc.php';
require_once 'lib/user_visible.inc.php';


//Hilfsfunktion, druckt script tags
function printJs($code){
    echo "<script type=\"text/Javascript\">$code</script>\n";
}

function fullNick($userid) {
    global $chatServer;
    return (CHAT_NICKNAME == 'username' ? $chatServer->getNick($userid,Request::option('chatid')) : $chatServer->getFullname($userid,Request::option('chatid')));
}

//Hilfsfunktion, unterscheidet zwischen �ffentlichen und privaten System Nachrichten
function chatSystemMsg($msg){
    global $user,$chatServer;
    $id = substr(strrchr ($msg[0],":"),1);
    if (!$id) {
        printJs("if (parent.frames['frm_nicklist'].location.href) parent.frames['frm_nicklist'].location.href = parent.frames['frm_nicklist'].location.href;");
        printJs("if (parent.frames['frm_status'].location.href) parent.frames['frm_status'].location.href = parent.frames['frm_status'].location.href;");
        $output = strftime("%H:%M:%S",$msg[2][1])."<i> [chatbot] $msg[1]</i><br>";
    } elseif ($user->id == $id){
        $output = strftime("%H:%M:%S",$msg[2][1])."<i> [chatbot] $msg[1]</i><br>";
    }
    return $output;
}
//Die Funktionen f�r die Chatkommandos, f�r jedes Kommando in $chatCmd muss es eine Funktion geben
function chatCommand_color($msgStr){
    global $user,$chatServer;
    if (!$msgStr || $msgStr == "\n" || $msgStr == "\r")
        return;
    $chatServer->chatDetail[Request::option('chatid')]['users'][$user->id]["color"] = htmlReady($msgStr);
    $chatServer->store();
    $chatServer->addMsg("system:$user->id",Request::option('chatid'),sprintf(_("Ihre %sSchriftfarbe%s wurde ge&auml;ndert!"),"<font color=\"".htmlReady($msgStr)."\">", '</font>'));
    return;
}

function chatCommand_quit($msgStr){
    global $user,$chatServer,$userQuit;
    $full_nick = fullNick($user->id);
    if ($chatServer->chatDetail[Request::option('chatid')]['log'][$user->id]){
            chatCommand_log("stop");
    }
    $chatServer->addMsg("system",Request::option('chatid'),sprintf(_("%s verl&auml;sst den Chat und sagt: %s"),htmlReady($full_nick),formatReady($msgStr)));
    echo _("Sie haben den Chat verlassen!") . "<br>";
    if (is_array($chatServer->chatDetail[Request::option('chatid')]['users'][$user->id]['log'])){
        echo _("Ihre letzte Aufzeichnung wird noch einmal zum Download angeboten.<br>Nachdem Sie dieses Fenster schlie�en wird diese Aufzeichnung gel&ouml;scht.");
        printJs("if (parent.frames['frm_dummy'].location.href) parent.frames['frm_dummy'].location.href = parent.frames['frm_dummy'].location.href;");
        flush();
        sleep(3);
    } else {
        echo _("Das Chatfenster wird in 3 Sekunden geschlossen!") . "<br>";
        printJs("window.scrollBy(0, 500);");
        printJs("setTimeout('parent.close()',3000);");
    }
    flush();
    $chatServer->removeUser($user->id,Request::option('chatid'));
    $userQuit = true;  //dirty deeds...
}

function chatCommand_me($msgStr){
    global $user,$chatServer;
    $chatServer->addMsg("system",Request::option('chatid'),"<b>".htmlReady(fullNick($user->id))." ".formatReady($msgStr)."</b>");
}

function chatCommand_help($msgStr){
    global $user,$chatServer,$chatCmd;
    $str = _("M�gliche Chat-Kommandos:");
    foreach($chatCmd as $cmd => $text)
        $str .= "<br><b>/$cmd</b>" . htmlReady($text);
    $chatServer->addMsg("system:$user->id",Request::option('chatid'),$str);
}

function chatCommand_private($msgStr){
    global $user,$chatServer;
    $recnick = trim(substr($msgStr." ",0,strpos($msgStr," ")));
    $recid = $chatServer->getIdFromNick(Request::option('chatid'),$recnick);
    $privMsgStr = trim(strstr($msgStr," "));
    if ($chatServer->isActiveUser($recid,Request::option('chatid'))){
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),sprintf(_("Ihre Botschaft an %s wurde verschickt."),htmlReady(fullNick($recid)))
            .":<br></i><font color=\"".$chatServer->chatDetail[Request::option('chatid')]['users'][$user->id]["color"]."\"> " . formatReady($privMsgStr)
            ."</font>");
        $chatServer->addMsg("system:$recid",Request::option('chatid'),sprintf(_("Eine geheime Botschaft von %s"),htmlReady(fullNick($user->id)))
            .":<br></i><font color=\"".$chatServer->chatDetail[Request::option('chatid')]['users'][$user->id]["color"]."\"> " . formatReady($privMsgStr)
            ."</font>");
    } elseif ($recnick) {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),sprintf(_("%s ist in diesem Chat nicht bekannt."),'<b>'.$recnick.'</b>'));
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Falsche Kommandosyntax!"));
    }
}

function chatCommand_kick($msgStr){
    global $user,$chatServer;
    //$kicknick = trim(substr($msgStr." ",0,strpos($msgStr," ")-1));
    $kicknick = $msgStr;
    if ($chatServer->getPerm($user->id,Request::option('chatid')) && $kicknick){
        $chat_users = $chatServer->getUsers(Request::option('chatid'));
        if ($kicknick != "all") {
            $kickid = $chatServer->getIdFromNick(Request::option('chatid'),$kicknick);
            if ($kickid){
                $kickids[$kickid] = $chat_users[$kickid];
            }
        } else {
            $kickids = $chat_users;
        }
        if (is_array($kickids)){
            foreach ($kickids as $kickid => $detail){
                if ($chatServer->getPerm($kickid,Request::option('chatid'))){
                    unset($kickids[$kickid]);
                }
            }
        }
        if (is_array($kickids) && count($kickids)){
            foreach ($kickids as $kickid => $detail){
                if ($chatServer->removeUser($kickid,Request::option('chatid'))){
                    $chatServer->addMsg("system",Request::option('chatid'),sprintf(_("%s wurde von %s aus dem Chat geworfen!"),htmlReady($detail['nick']),htmlReady(fullNick($user->id))));
                }
            }
        } else {
            $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Kein(e) Nutzer(in) zum entfernen gefunden."));
        }
    } elseif (!$kicknick){
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Falsche Kommandosyntax!"));
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie d&uuml;rfen hier niemanden rauswerfen!"));
    }
}

function chatCommand_sms($msgStr){
    global $user,$chatServer;
    $recUserName = trim(substr($msgStr." ",0,strpos($msgStr," ")));
    $smsMsgStr = trim(strstr($msgStr," "));
    if (!$recUserName || !$smsMsgStr){
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Falsche Kommandosyntax!"));
        return;
    }
    $msging = new messaging();
    if ($recUserName != get_username($user->id)) {
        if (get_visibility_by_username($recUserName) && $msging->insert_message(addslashes($smsMsgStr), $recUserName))
            $chatServer->addMsg("system:$user->id",Request::option('chatid'),sprintf(_("Ihre Nachricht an %s wurde verschickt."),'<b>'.$recUserName.'</b>'));
        else
            $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Ihre Nachricht konnte nicht verschickt werden!"));
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Es macht keinen Sinn, sich selbst Nachrichten zu schicken!"));
    }
}

function chatCommand_invite($msgStr){
    global $user,$chatServer;
    if ($chatServer->getPerm($user->id,Request::option('chatid'))){
        $recUserName = trim(substr($msgStr." ",0,strpos($msgStr." "," ")));
        $smsMsgStr = trim(strstr($msgStr," "));
        if (!$recUserName){
            $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Falsche Kommandosyntax!"));
            return;
        }
        $msging = new messaging();
        if ($recUserName != get_username($user->id)) {
            if ($msging->insert_chatinv(addslashes($smsMsgStr), $recUserName, Request::option('chatid'))) {
                $chatServer->addMsg("system:$user->id",Request::option('chatid'),sprintf(_("Ihre Einladung an %s wurde verschickt."),'<b>'.$recUserName.'</b>'));
            } else {
                $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Ihre Einladung konnte nicht verschickt werden!"));
            }
        } else {
            $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Es macht keinen Sinn, sich selbst in den Chat einzuladen!"));
        }
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie d&uuml;rfen f&uuml;r diesen Chat keine Einladungen verschicken!"));
    }
}

function chatCommand_password($msgStr){
    global $user,$chatServer;
    $password = $msgStr;
    if ($chatServer->getPerm($user->id,Request::option('chatid'))){
        if ($password){
            $chatServer->addMsg("system",Request::option('chatid'),sprintf(_("Dieser Chat wurde soeben von %s mit einem Passwort gesichert."),'<b>' . htmlReady(fullNick($user->id)).'</b>'));
            $chatServer->chatDetail[Request::option('chatid')]['password'] = $password;
            $chatServer->store();
        } elseif ($chatServer->chatDetail[Request::option('chatid')]['password']){
            $chatServer->addMsg("system",Request::option('chatid'),sprintf(_("Der Passwortschutz f&uuml;r diesen Chat wurde soeben von %s aufgehoben."),'<b>'.htmlReady(fullNick($user->id)).'</b>'));
            $chatServer->chatDetail[Request::option('chatid')]['password'] = false;
            $chatServer->store();
        } else {
            $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Dieser Chat ist nicht mit einem Passwort gesichert."));
        }
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie d&uuml;rfen hier kein Passwort setzen!"));
    }

}

function chatCommand_lock($msgStr){
    global $user,$chatServer;
    if ($chatServer->getPerm($user->id,Request::option('chatid'))){
        chatCommand_password(md5($chatServer->chatDetail[Request::option('chatid')]['id'] . ":" . uniqid("blablubb",1)));
        chatCommand_kick("all");
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie d&uuml;rfen diesen Chat nicht absichern!"));
    }
}

function chatCommand_unlock($msgStr){
    global $user,$chatServer;
    if ($chatServer->getPerm($user->id,Request::option('chatid'))){
        chatCommand_password("");
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie d&uuml;rfen diesen Chat nicht entsichern!"));
    }
}

function chatCommand_log($msgStr){
    global $user,$chatServer,$chat_log;
    $cmd = $msgStr;
    if ($chatServer->getPerm($user->id,Request::option('chatid'))){
        if ($cmd == "start"){
            if ($chatServer->chatDetail[Request::option('chatid')]['log'][$user->id]){
                $chatServer->addMsg("system:$user->id",Request::option('chatid'),sprintf(_("Sie lassen bereits seit %s eine Aufzeichnung laufen."),date("H:i",$chatServer->chatDetail[Request::option('chatid')]['log'][$user->id])));
            } else {
                $chatServer->addMsg("system",Request::option('chatid'),sprintf(_("Es wurde soeben von %s eine Aufzeichnung gestartet."),'<b>'.htmlReady(fullNick($user->id)).'</b>'));
                $chatServer->chatDetail[Request::option('chatid')]['log'][$user->id] = time();
                $chatServer->store();
                $chat_log = array();
            }
        } elseif ($cmd == "stop"){
            if ($chatServer->chatDetail[Request::option('chatid')]['log'][$user->id]){
                $chatServer->addMsg("system",Request::option('chatid'),sprintf(_("Die Aufzeichnung von %s wurde beendet."),'<b>'.htmlReady(fullNick($user->id)).'</b>'));
                $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Ihre Aufzeichnug wurde beendet und wird zu Ihrem Browser geschickt."));
                $chat_log[] = $chatServer->chatDetail[Request::option('chatid')]['log'][$user->id];
                $chat_log[] = time();
                $chatServer->chatDetail[Request::option('chatid')]['users'][$user->id]['log'] = $chat_log;
                unset($chatServer->chatDetail[Request::option('chatid')]['log'][$user->id]);
                $chatServer->store();
                printJs("if (parent.frames['frm_dummy'].location.href) parent.frames['frm_dummy'].location.href = parent.frames['frm_dummy'].location.href;");
            } else {
                $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie haben keine Aufzeichnung gestartet."));
            }
        } elseif ($cmd == "send"){
            if ($chatServer->chatDetail[Request::option('chatid')]['users'][$user->id]['log']){
                $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Ihre Aufzeichnung wird zu Ihrem Browser geschickt."));
                printJs("if (parent.frames['frm_dummy'].location.href) parent.frames['frm_dummy'].location.href = parent.frames['frm_dummy'].location.href;");
            } else {
                $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie haben keine gespeicherte Aufzeichnung."));
            }
        } else {
            $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Fehler: Falsche Kommandosyntax!"));
        }
    } else {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),_("Sie d&uuml;rfen hier keine Aufzeichnung starten!"));
    }
}


//Simpler Kommandoparser
function chatCommand($msg){
    global $user,$chatServer,$chatCmd;
    $cmdStr = trim(substr($msg[1]." ",1,strpos($msg[1]," ")-1));
    $msgStr = trim(strstr($msg[1]," "));
    if (!$chatCmd[$cmdStr]) {
        $chatServer->addMsg("system:$user->id",Request::option('chatid'),sprintf(_("Unbekanntes Kommando: %s"),'<b>'.htmlReady($cmdStr).'</b>'));
        return;
    }
    $chatFunc = "chatCommand_" . $cmdStr;
    $chatFunc($msgStr);       //variabler Funktionsaufruf!
}


//Die Ausgabeschleife, l�uft endlos wenn keine Abbruchbedingung erreicht wird
function outputLoop($chatid){
    global $user,$chatServer,$userQuit,$chat_log;


    $lastPingTime = 0;
    $lastMsgTime = $chatServer->getMsTime();
    --$lastMsgTime[1];
    if( !ini_get('safe_mode')) set_time_limit(0);       //wir sind nicht zu stoppen...
    ignore_user_abort(1);    //es sei denn wir werden brutal ausgebremst :)

    while(!connection_aborted()){

        $currentMsTime = $chatServer->msTimeToFloat();
        //Timeout vorbeugen
        if (($currentMsTime - $lastPingTime) > CHAT_TO_PREV_TIME) {
            echo"<!-- -->\n";
            flush();
            $lastPingTime = $currentMsTime;
        }
        //Gibt es neue Nachrichten ?
        $newMsg = $chatServer->getMsg($chatid,$lastMsgTime);

        if ($newMsg) {
            foreach($newMsg as $msg){
                var_dump('newxx');
                var_dump($newMsg);
                $output = "";
                if (substr($msg[0],0,6) == "system") {
                    $output = chatSystemMsg($msg);
                    if ($output){
                            if ($chatServer->chatDetail[$chatid]['log'][$user->id]){
                                $chat_log[] = strftime("%H:%M:%S",$msg[2][1])." [chatbot] $msg[1]";
                            }
                    } else {
                        continue;
                    }
                } elseif (substr($msg[1],0,1) == "/") {
                    if ($msg[0] == $user->id){
                        chatCommand($msg);
                    }
                    continue;
                }
                if (!$output){
                    $output = "<font color=\"".$chatServer->chatDetail[$chatid]['users'][$msg[0]]["color"]."\">"
                    . strftime("%H:%M:%S",$msg[2][1])." [".htmlReady(fullNick($msg[0]))."] "
                    . formatReady($msg[1])."</font><br>";
                    if ($chatServer->chatDetail[$chatid]['log'][$user->id]){
                        $chat_log[] = strftime("%H:%M:%S",$msg[2][1])." [".fullNick($msg[0])."] " . $msg[1];
                    }
                }
                echo $output;
                printJs("window.scrollBy(0, 500);");
                flush();
                $lastPingTime = $currentMsTime;
            }
            $lastMsgTime = $msg[2];
        }

        if ($userQuit) break; //...done dirt cheap

        $chatServer->setHeartbeat($user->id, $chatid);

//wurden wir zwischenzeitlich gekickt?
        if (!$chatServer->isActiveUser($user->id,$chatid)){
            echo _("Sie mussten den Chat verlassen...") ."<br>";
            echo sprintf(_("%sHier%s k&ouml;nnen Sie versuchen wieder einzusteigen."),"<a href=\"javascript:parent.location.href='chat_dispatcher.php?target=chat_login.php&chatid=$chatid';\">",'</a>').'<br>';
            printJs("window.scrollBy(0, 500);");
            flush();
            break;
        }
//Allzulange rumidlen soll keiner
        if ((!$chatServer->getPerm($user->id,$chatid) && (time()-$chatServer->getAction($user->id,$chatid)) > CHAT_IDLE_TIMEOUT) ||
            ($chatServer->getPerm($user->id,$chatid) && (time()-$chatServer->getAction($user->id,$chatid)) > CHAT_ADMIN_IDLE_TIMEOUT)){
            echo sprintf(_("%sIDLE TIMEOUT%s - Sie wurden aus dem Chat entfernt!"),'<b>','</b>').'<br>';
            $chatServer->removeUser($user->id,$chatid);
            echo sprintf(_("%sHier%s k&ouml;nnen Sie versuchen wieder einzusteigen."),"<a href=\"javascript:parent.location.href='chat_dispatcher.php?target=chat_dispatcher.php?target=chat_login.php&chatid=$chatid';\">",'</a>'). '<br>';
            printJs("window.scrollBy(0, 500);");
            flush();
            break;
        }


        usleep(CHAT_SLEEP_TIME);
    }
    //echo "Output beendet";

}

//main()
//globale Variablen
$chatServer = ChatServer::GetInstance($CHAT_SERVER_NAME);
$userQuit = false;
$userid = $user->id; //konservieren f�r shutdown_function
$chat_log = array();
?>
<html>
<head>
    <title>ChatAusgabe</title>
    <link rel="stylesheet" href="<?=$GLOBALS['ASSETS_URL']?>stylesheets/style.css" type="text/css">
</head>
<body style="font-size:10pt; background: #f3f5f8;">
<?
if (!$chatServer->isActiveUser($user->id,Request::option('chatid'))) {
    ?><table width="100%"><tr><?
    my_error('<font size="-1">'._("Sie sind nicht in diesem Chat angemeldet!").'</font>','chat',1,false);
    ?></tr></table></body></html><?
//PHPLib Session Variablen unangetastet lassen
    //page_close();
    die;
}
echo "\n<b>" . sprintf(_("Hallo %s,<br> willkommen im Raum: %s"),htmlReady(fullNick($user->id)),
    htmlReady($chatServer->chatDetail[Request::option('chatid')]["name"])) . "</b><br>";

register_shutdown_function("chatLogout");   //f�r korrektes ausloggen am Ende!
outputLoop(Request::option('chatid'));
//PHPLib Session Variablen unangetastet lassen
//page_close();



//shutdown funktion, wird automatisch bei skriptende aufgerufen
function chatLogout(){
    global $userid,$chatServer;
    $chatServer->removeUser($userid,Request::option('chatid'));
    $chatServer->isActiveChat(Request::option('chatid'));
}
?>

