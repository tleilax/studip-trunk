<?php
/*
studipim.php - Instant Messenger for Studip
Copyright (C) 2001 Andr� Noack <andre.noack@gmx.net>

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

/**
* Close the actual window if PHPLib shows login screen
* @const CLOSE_ON_LOGIN_SCREEN
*/
define("CLOSE_ON_LOGIN_SCREEN",true);
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

	require_once ("$ABSOLUTE_PATH_STUDIP/seminar_open.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
	
if ($auth->auth["uid"]!="nobody"){
	
	($cmd=="write") ? $refresh=0 : $refresh=30;
	
	
	$db = new DB_Seminar;
	$sms= new messaging;
	
	$now = time(); // nach eingestellter Zeit (default = 5 Minuten ohne Aktion) zaehlt man als offline
	$query = "SELECT " . $_fullname_sql['full'] . " AS full_name,($now-UNIX_TIMESTAMP(changed)) AS lastaction,a.username,a.user_id,contact_id 
	FROM active_sessions LEFT JOIN auth_user_md5 a ON (a.user_id=sid) LEFT JOIN user_info USING(user_id) 
	LEFT JOIN contact ON (owner_id='".$auth->auth["uid"]."' AND contact.user_id=a.user_id AND buddy=1)
	WHERE changed > '".date("YmdHis",$now - ($my_messaging_settings["active_time"] * 60))."' 
	AND sid != 'nobody' AND sid != '".$auth->auth["uid"]."' 
	AND active_sessions.name = 'Seminar_User' ORDER BY changed DESC";
	$db->query($query);
	while ($db->next_record()){
		$online[$db->f("username")] = array("name"=>$db->f("full_name"),"last_action"=>$db->f("lastaction"),"userid"=>$db->f("user_id"),"is_buddy" => $db->f("contact_id"));      
	}
	$query =  "SELECT message_id,mkdate,user_id_snd,message,user_id_snd FROM globalmessages WHERE user_id_rec='".$auth->auth["uname"]."'";
	$db->query($query);
	$old_msg = 0;
	$new_msg = array();
	
	while ($db->next_record()){
		if ($cmd=="read" AND $msg_nr==$db->f("message_id")){
			$msg_text=$db->f("message");
			$msg_snd=$db->f("user_id_snd");
		}
		if ($db->f("mkdate") <= ($now-$refresh)){
			if (!preg_match("/chat_with_me/i", $db->f("message"))) {
				$old_msg++;
			}
		} else {
			if (preg_match("/chat_with_me/i", $db->f("message")) && $online[$db->f("user_id_snd")]){
				$new_msg[] = date("H:i",$db->f("mkdate")). sprintf(_(" Sie wurden von <b>%s</b> zum chatten eingeladen!"),htmlReady(get_fullname_from_uname($db->f("user_id_snd"))));
			} else {
				if ($db->f("user_id_snd") == "____%system%____"){
					$new_msg[]=date("H:i",$db->f("mkdate")) . sprintf(_(" Sie haben eine automatisch erzeugte <b>Systemnachricht</b> erhalten! %s[lesen]%s"),"<a href='$PHP_SELF?cmd=read&msg_nr=".$db->f("message_id")."'>","</a>");
				} else {
					$new_msg[]=date("H:i",$db->f("mkdate")). sprintf(_(" Sie haben eine Nachricht von <b>%s</b> erhalten! %s[lesen]%s"),htmlReady(get_fullname_from_uname($db->f("user_id_snd"))),"<a href='$PHP_SELF?cmd=read&msg_nr=".$db->f("message_id")."'>","</a>");
				}
			}
			$refresh+=10;
		}
		
	}
}
// Start of Output
$_html_head_title = "Stud.IP IM (" . $auth->auth["uname"] . ")";
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
?>
<script language="JavaScript">
<!--

<?if ($auth->auth["uid"]=="nobody") echo "close();"; //als nobody macht der IM keinen Sinn?>

function coming_home(url)
	{
     if (opener)
     	{
          opener.location.href = url;
		opener.focus();

          }
     else
     	{
          top.open(url,'');
          }
	}

function again_and_again()
	{
<? if ($cmd!="write")
	($cmd) ? print("location.replace('$PHP_SELF');\n") : print("location.reload();\n"); ?>
     }


setTimeout('again_and_again();',<? print($refresh*1000);?>);
<?
($new_msg[0] OR $cmd) ? print ("focus();\n") : print ("blur();\n");
?>
//-->
</script>

<table width="100%" border=0 cellpadding=2 cellspacing=0>
<tr>
	<td class="topic" colspan=2><img src="pictures/nutzer.gif" border="0" align="texttop"><b>&nbsp;Stud.IP-Messenger (<?=$auth->auth["uname"]?>)</b></td>
</tr>
<tr><td class="blank" width="50%" ><table width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
<?php

$c=0;
if (is_array($online)) {
	foreach($online as $tmp_uname => $detail){
		if ($detail['is_buddy']){
			if (!$c){
				echo "<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1><b>" . _("Buddies:") . "</b></td></tr>";
			}
			echo "<tr><td class='blank' width='90%' align='left'><font size=-1><a " . tooltip(sprintf(_("letztes Lebenszeichen: %s"),date("i:s",$detail['last_action'])),false) . " href=\"javascript:coming_home('about.php?username=$tmp_uname');\">".htmlReady($detail['name'])."</a></font></td>\n";
			echo "<td  class='blank' width='10%' align='middle'><font size=-1><a href='$PHP_SELF?cmd=write&msg_rec=$tmp_uname'><img src=\"pictures/nachricht1.gif\" ".tooltip(_("Nachricht an User verschicken"))." border=\"0\" width=\"24\" height=\"21\"></a></font></td></tr>";
			$c++;
		}
	}
} else {
	echo "<tr><td class='blank' colspan='2' align='left'><font size=-1>" . _("Kein Nutzer ist online.") . "</font>";
}

if (!$my_messaging_settings["show_only_buddys"]) {
	if ((sizeof($online)-$c) == 1) {
		echo "<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>" . _("Es ist ein anderer Nutzer online.");
		printf ("&nbsp;<a href=\"javascript:coming_home('online.php')\"><font size=-1>" . _("Wer?") . "</font></a>");		
	}
	elseif((sizeof($online)-$c) > 1) {
		printf ("<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>" . _("Es sind %s anderere Nutzer online.") , sizeof($online)-$c);
		printf ("&nbsp;<a href=\"javascript:coming_home('online.php')\"><font size=-1>" . _("Wer?") . "</font></a>");		
	}
}
?>
</td></tr></table></td><td class="blank" width="50%" valign="top"><br><font size=-1>
<?
($old_msg) ? printf(_("%s alte Nachricht(en)&nbsp;%s[lesen]%s"),$old_msg,"<a href=\"javascript:coming_home('sms.php')\">","</a><br>")
		 : print (_("Keine alten Nachrichten") . "<br>");
if ($new_msg[0]){
	echo implode("<br>",$new_msg);
}
else {
	echo"\n<br>" . _("Keine neuen Nachrichten");
}

?>
</font><br>&nbsp</td></tr>
<?

if ($cmd=="send_msg" AND $nu_msg AND $msg_rec) {
	$nu_msg=trim($nu_msg);
	if ($sms->insert_sms ($msg_rec, $nu_msg))
		echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1>"
			. sprintf(_("Ihre Nachricht an <b>%s</b> wurde verschickt!"),get_fullname_from_uname($msg_rec)) . "</font></td></tr>";
	else 
		echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1 color='red'><b>"
			. _("Ihre Nachricht konnte nicht verschickt werden!") . "</b></font></td></tr>";
}


if ($cmd=="read" AND $msg_text){
	if ($msg_snd == "____%system%____")
		echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1><b>"
		. _("automatisch erzeugte Systemnachricht:") . " </b><hr>".quotes_decode(formatReady($msg_text))."</font></td></tr>";
	else
		echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1>"
		. sprintf(_("Nachricht von: <b>%s</b>"),htmlReady(get_fullname_from_uname($msg_snd))) ."<hr>".quotes_decode(formatReady($msg_text))."</font></td></tr>";
	if ($msg_snd != "____%system%____")
		echo"\n<tr><td class='blank' colspan='2' valign='middle' align='right'><font size=-1><a href='$PHP_SELF?cmd=write&msg_rec=$msg_snd'>"
		. "<img " . makeButton("antworten","src") . tooltip(_("Diese Nachricht direkt beantworten")) . " border=0></a></td></tr>";
}

if ($cmd=="write" AND $msg_rec){
	echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1>"
		. sprintf(_("Ihre Nachricht an <b>%s:</b>"),htmlReady(get_fullname_from_uname($msg_rec))) . "</font></td></tr>";
	echo"\n<FORM  name='eingabe' action='$PHP_SELF?cmd=send_msg' method='POST'><INPUT TYPE='HIDDEN'  name='msg_rec' value='$msg_rec'>";
	echo"\n<tr><td class='blank' colspan='2' valign='middle'><TEXTAREA  style=\"width: 100%\" name='nu_msg' rows='4' cols='44' wrap='virtual'></TEXTAREA></font><br>";
	echo "<font size=-1><a target=\"_new\" href=\"show_smiley.php\">" . _("Smileys</a> k&ouml;nnen verwendet werden") . " </font>\n</td></tr>";
	echo"\n<tr><td class='blank' colspan='2' valign='middle' align='right'><font size=-1>&nbsp;<INPUT TYPE='IMAGE' name='none' "
		. makeButton("absenden","src") . tooltip(_("Nachricht versenden")) . " border=0 value='senden'>&nbsp; <a href=\"$PHP_SELF\"><img "
		. makeButton("abbrechen","src") . tooltip(_("Vorgang abbrechen")) . " border=0 /></a></FORM></font></td></tr>";
	echo"\n<script language=\"JavaScript\">\n<!--\ndocument.eingabe.nu_msg.focus();\n//-->\n</script>";
}
	?>
</table>
<?
// Save data back to database.
page_close();
?>
</body>
</html>