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
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

if ($auth->auth["uid"]!="nobody"){

($cmd=="write") ? $refresh=0 : $refresh=20;

require_once ("seminar_open.php");
require_once ("visual.inc.php");
require_once ("functions.php");
require_once ("config.inc.php");
require_once ("messaging.inc.php");

$db = new DB_Seminar;
$sms= new messaging;

$now = time();
$sqldate = date("YmdHis", ($now - ($my_messaging_settings["active_time"] * 60)));

$query = "SELECT CONCAT(Vorname,' ',Nachname) AS full_name,changed,username FROM active_sessions LEFT JOIN auth_user_md5 ON user_id=sid WHERE changed > '$sqldate' AND sid != 'nobody' AND sid != '".$auth->auth["uid"]."' AND active_sessions.name = 'Seminar_User' ORDER BY changed DESC";
$db->query($query);
while ($db->next_record())
      {
      $stamp=mktime(substr($db->f("changed"),8,2),substr($db->f("changed"),10,2),substr($db->f("changed"),12,2),substr($db->f("changed"),4,2),substr($db->f("changed"),6,2),substr($db->f("changed"),0,4));
      $online[$db->f("username")] = array("name"=>$db->f("full_name"),"last_action"=>($now-$stamp));      
      }

$query =  "SELECT message_id,mkdate,user_id_snd,message,user_id_snd FROM globalmessages WHERE user_id_rec='".$auth->auth["uname"]."'";
$db->query($query);
$old_msg = 0;
$new_msg = array();

while ($db->next_record())
      {
      if ($cmd=="read" AND $msg_nr==$db->f("message_id"))
      	{
          $msg_text=$db->f("message");
          $msg_snd=$db->f("user_id_snd");
          }
      if ($db->f("mkdate") <= ($now-$refresh))
         {
         if ($db->f("message")!="chat_with_me") $old_msg++;
         }
      else
          if ($db->f("message")=="chat_with_me" AND $online[$db->f("user_id_snd")])
           {
           $new_msg[]=date("H:i",$db->f("mkdate"))." Sie wurden von <b>".get_fullname_from_uname($db->f("user_id_snd"))."</b> zum Chatten eingeladen!";
           }
           else
               {
               $new_msg[]=date("H:i",$db->f("mkdate"))." Sie haben eine Nachricht von <b>".get_fullname_from_uname($db->f("user_id_snd"))."</b> erhalten! <a href='$PHP_SELF?cmd=read&msg_nr=".$db->f("message_id")."'>[lesen]</a>";
               $refresh+=10;
               }
      }
}
?>
<html>
<head>
<title>Stud.IP-Messenger (<?=$auth->auth["uname"]?>)</title>
        <link rel="stylesheet" href="style.css" type="text/css">
<script language="JavaScript">
<!--

<?if ($auth->auth["uid"]=="nobody") echo "self.close();"; //als nobody macht der IM keinen Sinn?>

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
($new_msg[0] OR $cmd) ? print ("self.focus();\n") : print ("self.blur();\n");
?>
//-->
</script>
</head>




<body bgcolor=white>
<table width="100%" border=0 cellpadding=2 cellspacing=0>
<tr>
	<td class="topic" colspan=2><img src="pictures/nutzer.gif" border="0" align="texttop"><b>&nbsp;Stud.IP-Messenger</b></td>
</tr>
<tr><td class="blank" width="50%" ><table width="100%" border=0 cellpadding=1 cellspacing=0>
<?php

$c=0;
if ((is_array($online)) && (is_array ($my_buddies))) {
       foreach($online as $key=>$value) {
		foreach ($my_buddies as $a) {
			if ($key == $a["username"]) {
				if (!$c)
					printf ("<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1><b>Buddies:</b></td></tr>");					
				echo "<tr><td class='blank' width='90%' align='left'><font size=-1><a href=\"javascript:coming_home('about.php?username=$key');\">".$value["name"]."</a></font></td>\n";
				echo "<td  class='blank' width='10%' align='middle'><font size=-1><a href='$PHP_SELF?cmd=write&msg_rec=$key'><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\" width=\"24\" height=\"21\"></a></font></td></tr>";
				$c++;
      				}
      			}
    		}
    	}
else print ("<tr><td class='blank' colspan='2' align='left'><font size=-1>Kein Buddy ist online.</font>");
if (!$my_messaging_settings["show_only_buddys"]) {
	if ((sizeof($online)-$c) == 1) {
		echo "<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>Es ist ein anderer Nutzer online.";
		printf ("&nbsp;<a href=\"javascript:coming_home('online.php')\"><font size=-1>Wer?</font></a>");		
	}
	elseif((sizeof($online)-$c) > 1) {
		printf ("<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>Es sind %s anderere Nutzer online.", sizeof($online)-$c);
		printf ("&nbsp;<a href=\"javascript:coming_home('online.php')\"><font size=-1>Wer?</font></a>");		
	}
}
?>
</td></tr></table></td><td class="blank" width="50%" valign="top"><br><font size=-1>
<?
($old_msg) ? print("$old_msg alte Nachricht(en)&nbsp;<a href=\"javascript:coming_home('sms.php')\">[lesen]</a><br>") : print ("Keine alten Nachrichten<br>");
if ($new_msg[0])
   {
       echo implode("<br>",$new_msg);
       //echo "\n<embed src='aah.wav'  hidden='true' autostart='true' loop='false' type='audio/x-wav'>";
   }
else echo"\n<br>Keine neuen Nachrichten";

?>
</font><br>&nbsp</td></tr>
<?

if ($cmd=="send_msg" AND $nu_msg AND $msg_rec) {
	$nu_msg=trim($nu_msg);
	if ($sms->insert_sms ($msg_rec, $nu_msg))
		echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1>Ihre Nachricht an <b>".get_fullname_from_uname($msg_rec)."</b> wurde verschickt!</font></td></tr>";
	else 
		echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1 color='red'><b>Ihre Nachricht konnte nicht verschickt werden!</b></font></td></tr>";
}


if ($cmd=="read" AND $msg_text)
	{
	echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1>Nachricht von <b>".get_fullname_from_uname($msg_snd)."</b><hr>".quotes_decode(formatReady($msg_text))."</font></td></tr>";
     echo"\n<tr><td class='blank' colspan='2' valign='middle' align='right'><font size=-1><a href='$PHP_SELF?cmd=write&msg_rec=$msg_snd'><img src=\"pictures/buttons/antworten-button.gif\" border=0></a></td></tr>";
     }

if ($cmd=="write" AND $msg_rec)
	{
     echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1>Ihre Nachricht an <b>".get_fullname_from_uname($msg_rec).":</b></font></td></tr>";
     echo"\n<FORM  name='eingabe' action='$PHP_SELF?cmd=send_msg' method='POST'><INPUT TYPE='HIDDEN'  name='msg_rec' value='$msg_rec'>";
     echo"\n<tr><td class='blank' colspan='2' valign='middle'><TEXTAREA  style=\"width: 100%\" name='nu_msg' rows='4' cols='44' wrap='virtual'></TEXTAREA></font><br>";
     echo "<font size=-1><a target=\"_new\" href=\"show_smiley.php\">Smileys</a> k&ouml;nnen verwendet werden</font>\n</td></tr>";
     echo"\n<tr><td class='blank' colspan='2' valign='middle' align='right'><font size=-1>&nbsp;<INPUT TYPE='IMAGE'  name='none' src=\"pictures/buttons/absenden-button.gif\" border=0value='senden'>&nbsp; <a href=\"$PHP_SELF\"><img src=\"pictures/buttons/abbrechen-button.gif\" border=0 /></a></FORM></font></td></tr>";
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