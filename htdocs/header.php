<?php
/**
* header
* 
* head line of Stud.IP
* 
*
* @author		Stefan Suchi <suchi@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	visual
* @module		header.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// header.php
// head line of Stud.IP
// Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@data-quest.de>
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


if ($SHOW_TERMS_ON_FIRST_LOGIN){
	require_once ("$ABSOLUTE_PATH_STUDIP/terms.inc.php");
	check_terms($user->id, $_language_path);	
} 


ob_start();
//Daten fuer Onlinefunktion einbinden
if (!$perm->have_perm("user"))
	$my_messaging_settings["active_time"]=5;

require_once ($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP . "functions.php");
require_once ($ABSOLUTE_PATH_STUDIP . "sms_functions.inc.php");

if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php"; 
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
	$sms = new messaging();
	$sms->delete_chatinv();
	echo "\t\t<script type=\"text/javascript\">\n";
	echo "\t\tfunction open_chat(chatid) {\n";
	echo "\t\t\tif(!chatid){\n";
	printf ("\t\t\t\talert('%s');\n", _("Sie sind bereits in diesem Chat angemeldet!"));
	echo "\t\t\t} else {\n\t\t\tfenster=window.open(\"". $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . $GLOBALS['RELATIVE_PATH_CHAT'] ."/chat_login.php?chatid=\" + chatid,\"chat_\" + chatid + \"_".$auth->auth["uid"]."\",\"scrollbars=no,width=640,height=480,resizable=yes\");\n";
	echo "\t\t}\nreturn false;\n}\n";
	echo "\t\t</script>\n";
}

// Initialisierung der Hilfe
$help_query = "?referrer_page=" . $i_page;
if (isset($i_query[0]) && $i_query[0] != "") {
	for ($i = 0; $i < count($i_query); $i++) { // alle Parameter durchwandern
		$help_query .= '&';
		$help_query .= $i_query[$i];
	}
}

if ($auth->auth["uid"] == "nobody") { ?>

		<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0" height="25">
			<tr>
			<td class="toolbar" align="left">
				<table class="toolbar" align="left" border="0" width="100%" cellspacing="0" cellpadding="0" height="25">
				<tr>

<?
				echo MakeToolbar("pictures/home.gif","index.php",_("Start"),_("Zur Startseite"),40,"_top","center", "FALSE", "1");
				echo MakeToolbar("pictures/meinesem.gif","freie.php",_("Freie"),_("Freie Veranstaltungen"),40, "_top","left", "FALSE", "2");
				
?>				
				</td></tr></table></td>
			<td class="toolbar" align="center" width=100%">											
				<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0" height="25">
				<tr>


<?				echo MakeToolbar("pictures/logo2.gif","impressum.php",_("Impressum"),$UNI_NAME_CLEAN." - "._("Informationen �ber das System"),40,"_top", "center", "FALSE", "5");
?>
				</td></tr></table></td>
			<td class="toolbar" align="right">											
				<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0" height="25">
				<tr>


<?				echo MakeToolbar("pictures/hilfe.gif","./help/index.php$help_query",_("Hilfe"),_("Hilfe zu dieser Seite"),40, "_new","right", "FALSE", "9");
				echo MakeToolbar("pictures/login.gif","index.php?again=yes",_("Login"),_("Am System anmelden"),40,"_top","right", "FALSE", "0");

?>
			</td></tr></table></td>
			</tr>
		</table>

<?php
} else {   // Benutzer angemeldet

		$db=new DB_Seminar;

		$myuname=$auth->auth["uname"]; //checken, ob noch gebraucht!
		
		$tmp_last_visit = ($my_messaging_settings["last_visit"]) ?  $my_messaging_settings["last_visit"] : time();
		$db->query("SELECT STRAIGHT_JOIN count(*) FROM message LEFT JOIN message_user USING (message_id) WHERE message_user.user_id = '{$user->id}' AND snd_rec = 'rec' AND chat_id IS NOT NULL");
		$db->next_record();
		$chatm = $db->f(0);
		$neum = count_messages_from_user('in', " AND message_user.readed = 0 ");
		$altm = count_messages_from_user('in', " AND message_user.readed = 1 ");
		$neux = count_x_messages_from_user('in', 'all', "AND message.mkdate > ".(int)$my_messaging_settings["last_box_visit"]." AND message_user.readed = 0 ");
		
		//globale Objekte z�hlen
		$db->query("SELECT  COUNT((IF(date < UNIX_TIMESTAMP(),range_id,NULL))) as count,
					COUNT(IF((chdate > IFNULL(b.visitdate,0) AND nw.user_id !='{$user->id}'),
					(IF(date < UNIX_TIMESTAMP(),range_id,NULL)), NULL)) AS neue 
					FROM   news_range a  LEFT JOIN news nw USING(news_id)
					LEFT JOIN object_user_visits b ON (b.object_id = nw.news_id AND b.user_id = '{$user->id}' AND b.type ='news')
					WHERE a.range_id='studip' GROUP BY a.range_id");
		if ($db->next_record()){
			$global_obj['news']['neue'] = $db->f('neue');
			$global_obj['news']['gesamt'] = $db->f('count');
		}
		if ($GLOBALS['VOTE_ENABLE']) {
			$db->query("SELECT  COUNT(vote_id) as count, 
						COUNT(IF((chdate > IFNULL(b.visitdate,0) AND a.author_id !='{$user->id}' AND a.state != 'stopvis'), vote_id, NULL)) AS neue 
						FROM  vote a LEFT JOIN object_user_visits b ON (b.object_id = vote_id AND b.user_id = '{$user->id}' AND b.type='vote') 
						WHERE a.range_id='studip'  AND a.state IN('active','stopvis')
						GROUP BY a.range_id");
			if ($db->next_record()){
				$global_obj['vote']['neue'] = $db->f('neue');
				$global_obj['vote']['gesamt'] = $db->f('count');
			}
		$db->query("SELECT  COUNT(a.eval_id) as count,
					COUNT(IF((chdate > IFNULL(b.visitdate,0) AND d.author_id !='{$user->id}' ), a.eval_id, NULL)) as neue
					FROM eval_range a INNER JOIN eval d ON ( a.eval_id = d.eval_id AND d.startdate < UNIX_TIMESTAMP( ) AND (d.stopdate > UNIX_TIMESTAMP( ) OR d.startdate + d.timespan > UNIX_TIMESTAMP( ) OR (d.stopdate IS NULL AND d.timespan IS NULL)))
					LEFT JOIN object_user_visits b ON (b.object_id = d.eval_id AND b.user_id = '{$user->id}' AND b.type='eval') 
					WHERE a.range_id='studip' GROUP BY a.range_id");
			if ($db->next_record()){
				$global_obj['eval']['neue'] = $db->f('neue');
				$global_obj['eval']['gesamt'] = $db->f('count');
			}
		}
		?>
		<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
		<td width="40%" class="toolbar">
			<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
<?
	$home_icon = ($global_obj['eval']['neue'] || $global_obj['vote']['neue'] || $global_obj['news']['neue'] ? "pictures/home_red.gif" : "pictures/home.gif");
	$home_info .= ($global_obj['news']['neue'] ? " - " . sprintf(_(" %s neue News"), $global_obj['news']['neue']) : "");
	$home_info .= (($global_obj['vote']['neue'] + $global_obj['eval']['neue']) ? " - " . sprintf(_(" %s neue Umfrage(n)"), ($global_obj['vote']['neue'] + $global_obj['eval']['neue'])) : "");
	echo MakeToolbar($home_icon  ,"index.php",_("Start"),_("Zur Startseite") . $home_info,40,"_top", "center", "FALSE", "1");
	echo MakeToolbar("pictures/meinesem.gif",($perm->have_perm("root")) ? "sem_portal.php" : "meine_seminare.php",_("Veranstaltungen"),_("Meine Veranstaltungen & Einrichtungen"),90, "_top","left", "FALSE", "2");

//Nachrichten anzeigen
	$text = _("Post");
	$link = "sms_box.php";
	if ($neum) {
		$icon = "pictures/nachricht2.gif";
	} else if (!$neum) {
		$icon = "pictures/nachricht1.gif";
	}
	$link .= "?sms_inout=in";
	if ($neux >= "1") {
		$tip = sprintf(_("Sie haben %s neue ungelesene Nachrichten!"), $neum);
	} else if ($neux == "1") {
		$tip = _("Sie haben eine neue ungelesene Nachricht!");
	}
	if ($neum > "1" && !$neux) {
		$tip = sprintf(_("Sie haben %s ungelesene Nachrichten!"), $neum);
	} else if ($neum == "1" && !$neux) {
		$tip = _("Sie haben eine ungelesene Nachricht!");
	}
	if ($altm > "1" && !$neum) {
		$tip = sprintf(_("Sie haben %s alte empfangene Nachrichten."), $altm);
	} else if ($altm == "1" && !$neum) {
		$tip = _("Sie haben eine alte empfangene Nachricht.");
	} else if (!$neum) {
		$tip = _("Sie haben keine alten empfangenen Nachrichten.");
	}

	echo MakeToolbar($icon,$link,$text,$tip,40, "_top", "center", "FALSE");

		if (!($perm->have_perm("admin") || $perm->have_perm("root"))) {
			if ($GLOBALS['CALENDAR_ENABLE'])
				echo MakeToolbar("pictures/meinetermine.gif","./calendar.php?caluserid=self",_("Planer"),_("Termine und Kontakte"),40, "_top", "center", "FALSE", "3");
			else
				echo MakeToolbar("pictures/meinetermine.gif","./mein_stundenplan.php",_("Planer"),_("Stundenplan und Kontakte"),40, "_top", "center", "FALSE", "3");
		}		

		if ($GLOBALS['CHAT_ENABLE']) {
			$chatter = $chatServer->getAllChatUsers();
			$active_chats = count($chatServer->chatDetail);
			if (!$chatter){
				$chat_tip = _("Es ist niemand im Chat");
			} elseif ($chatter == 1 && $chatServer->chatUser[$user->id]){
				$chat_tip =_("Nur Sie sind im Chat");
			} elseif ($chatter == 1){
				$chat_tip =_("Es ist eine Person im Chat");
			} else {
				$chat_tip = sprintf(_("Es sind %s Personen im Chat"), $chatter);
			}
			if ($active_chats == 1){
				$chat_tip .= ", " . _("ein aktiver Chatraum");
			} elseif ($active_chats > 1){
				$chat_tip .= ", " . sprintf(_("%s aktive Chatr�ume"), $active_chats);
			}
			if ($chatm){
				echo MakeToolbar("pictures/chateinladung.gif","chat_online.php",_("Chat"),(($chatm == 1) ? _("Sie haben eine Chateinladung") : sprintf(_("Sie haben %s Chateinladungen"),$chatm)) . ", " . $chat_tip,30,"_top","left");
			} elseif ($chatter == 1 && $chatServer->chatUser[$user->id]){
				echo MakeToolbar("pictures/chat3.gif","chat_online.php",_("Chat"),$chat_tip,40,"_top","left");
			} elseif ($chatter) {
				echo MakeToolbar("pictures/chat2.gif","chat_online.php",_("Chat"),$chat_tip,40,"_top","left");
			} else {
				echo MakeToolbar("pictures/chat1.gif","chat_online.php",_("Chat"),$chat_tip,40,"_top","left");
			}
			unset($chatter);
			unset($active_chats);
		}

		// Ist sonst noch wer da?
		$user_count = get_users_online_count($my_messaging_settings["active_time"]);
		if (!$user_count)
			echo MakeToolbar("pictures/nutzer.gif","online.php",_("Online"),_("Nur Sie sind online"),55, "_top","left", "FALSE", "4");
		else {
			if ($user_count == 1) {
				echo MakeToolbar("pictures/nutzeronline.gif","online.php",_("Online"),_("Au�er Ihnen ist eine Person online"),55, "_top","left", "FALSE", "4");
			} else {
				echo MakeToolbar("pictures/nutzeronline.gif","online.php",_("Online"),sprintf(_("Es sind au�er Ihnen %s Personen online"), $user_count),55, "_top","left", "FALSE", "4");
			}
		}

?>
		<td class="toolbar" width="99%">
		&nbsp; 
		</td>
	</tr>
	</table>
	</td>


	<td width="50%" align="center">
		<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr align="center">
<? //create (javascript) info tooltip/window
				echo MakeToolbar("pictures/logo2.gif","impressum.php",_("Impressum"),$UNI_NAME_CLEAN." - "._("Informationen �ber das System"),112, "_top", "center", "FALSE", "5");
?>
	</tr>
	</table>
	</td>
	<td width="40%" align="right">
		<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td class="toolbar" width="99%">
			&nbsp; 
			</td>
			
<?			
		if ($perm->have_perm("autor")) {
			if ($homepage_cache_own)
				$time = $homepage_cache_own;
			else
				$time = $LastLogin;
			
			$picture = "pictures/einst.gif";
			$hp_txt = _("Zu Ihrer Einstellungsseite");
			$hp_link = "about.php";
			
			$db->query("SELECT COUNT(post_id) AS count
				FROM guestbook 
				WHERE range_id='".$user->id."' 
				AND user_id!='".$user->id."' 
				AND mkdate > '".$time."'");
					
			if ($db->next_record()) {
				if ($db->f("count") == 1) {
					$hp_txt .= sprintf(_(", Sie haben %s neuen Eintrag im G�stebuch."), $db->f("count"));
					$picture = "pictures/einst2.gif";
					$hp_link .= "?guestbook=open#guest";
				}
				if ($db->f("count") > 1) {
					$hp_txt .= sprintf(_(", Sie haben %s neue Eintr�ge im G�stebuch."), $db->f("count"));
					$picture = "pictures/einst2.gif";
					$hp_link .= "?guestbook=open#guest";
				}
			}
			echo MakeToolbar($picture,$hp_link,_("Homepage"),$hp_txt,40, "_top","right", "FALSE", "6");

			echo MakeToolbar("pictures/suchen.gif","auswahl_suche.php",_("Suche"),_("Im System suchen"),40, "_top", "center", "FALSE", "7");
		}

		if ($perm->have_perm("tutor")) {
			echo MakeToolbar("pictures/admin.gif","adminarea_start.php?list=TRUE",_("Admin"),_("Zu Ihrer Administrationsseite"),40, "_top", "center", "FALSE", "8");
		} else {
			?>
			<td class="toolbar">
				<img border="0" src="pictures/blank.gif" heigth="5" width="12"> 
			</td>
			<?
		}
		
		$infotext = sprintf (_("Sie sind angemeldet als: %s mit der Berechtigung: %s. Beginn der Session: %s,  letztes Login: %s, %s,  Aufl�sung: %sx%s, eingestellte Sprache: %s"),
				$auth->auth["uname"], $auth->auth["perm"], date ("d. M Y, H:i:s", $SessionStart), date ("d. M Y, H:i:s", $LastLogin),
				($auth->auth["jscript"]) ? _("JavaScript eingeschaltet") : _("JavaScript ausgeschaltet"), $auth->auth["xres"], $auth->auth["yres"], $INSTALLED_LANGUAGES[$_language]["name"]);
		

		echo MakeToolbar("pictures/info_header.gif","#",trim(mila($auth->auth["uname"],7)),$infotext,68, "","left","TRUE");
?>
<?		
		echo MakeToolbar("pictures/hilfe.gif","./help/index.php$help_query",_("Hilfe"),_("Hilfe zu dieser Seite"),40, "_new","right","FALSE", "9");
		echo MakeToolbar("pictures/logout.gif","logout.php",_("Logout"),_("Aus dem System abmelden"),40, "_top", "center", "FALSE", "0");

?>
	</tr>
</table>
</td>
</tr>
</table>

<?

}
	
ob_end_flush();

include "check_sem_entry.inc.php"; //hier wird der Zugang zum Seminar ueberprueft
?>
<!-- $Id$ -->

