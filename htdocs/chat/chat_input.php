<?
/**
* Input Window for the Chat
* 
* This script prints a HTML input form and handles color changing and quitting the chat with some JavaScript
*
* @author		Andr� Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup		chat_modules
* @module		chat_input
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
	page_close();
	die;
}
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php";
//Studip includes
require_once $ABSOLUTE_PATH_STUDIP."msg.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."visual.inc.php";

$chatServer =& ChatServer::GetInstance($CHAT_SERVER_NAME);
$chatServer->caching = true;

?>
<html>
<head>
	<title>ChatInput</title>
	<?php include $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_style.inc.php";?>
<script type="text/javascript">
	function strltrim() {
		return this.replace(/^\s+/,'');
	}
	function strrtrim() {
		return this.replace(/\s+$/,'');
	}
	function strtrim() {
		return this.replace(/^\s+/,'').replace(/\s+$/,'');
	}
	
	String.prototype.ltrim = strltrim;
	String.prototype.rtrim = strrtrim;
	String.prototype.trim = strtrim;
/**
* JavaScript 
*/
	function doQuit(){
		document.inputform.chatInput.value="/quit bye";
		document.inputform.submit();
	}

/**
* JavaScript 
*/
	function doColorChange(){
		for(i=0;i<document.inputform.chatColor.length;++i)
			if(document.inputform.chatColor.options[i].selected == true){
			document.inputform.chatInput.value="/color " +
			document.inputform.chatColor.options[i].value;
			document.inputform.submit();
			}
	}

/**
* JavaScript 
*/
	function doCheck(){
		var the_string = document.inputform.chatInput.value.trim();
		if (the_string.substring(0,the_string.indexOf(" ")) == "/password"){
			document.inputform.chatInput.value = "/password " + 
				parent.MD5(parent.chatuniqid + ":" + the_string.substring(the_string.indexOf(" "),the_string.length).trim());
			document.inputform.submit();
			return false;
		} else {
			return true;
		}
	}

	
</script>

</head>
<body>
<?
//darf ich �berhaupt hier sein ?
if (!$chatServer->isActiveUser($user->id,$chatid)) {
	?><table width="100%"><tr><?
	my_error(_("<font size=\"-1\">Sie sind nicht in diesem Chat angemeldet!</font>"),"chat",1,false);
	?></tr></table></body></html><?
	page_close();
	die;
}


//neue chatnachricht einf�gen
if ($chatInput) {
	if ($chatServer->isActiveUser($user->id,$chatid)){
		$chatInput = stripslashes($chatInput);
		$chatServer->addMsg($user->id,$chatid,$chatInput);
		//evtl Farbe umstellen
		$cmdStr = trim(substr($chatInput." ",1,strpos($chatInput," ")-1));
		$msgStr = trim(strstr($chatInput," "));
		if ($cmdStr == "color" && $msgStr != "" && $msgStr != "\n" && $msgStr != "\r")
			$chatServer->chatDetail[$chatid]["users"][$user->id]["color"] = $msgStr;
		}

}

?>
<form method="post" action="<?=$PHP_SELF?>" name="inputform" onSubmit="return doCheck();">
<input type="hidden" name="chatid" value="<?=$chatid?>">
<div align="center">
	<table width="98%" border="0" bgcolor="white" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td align="center" colspan=2 valign="center">
				<table width="100%" border="0" cellspacing="3">
				<tr valign="middle">
					<td align="left" valign="center">Message:</td>
					<td width="60%" valign="center">
						<div align="center" valign="center">
						<input type="text" name="chatInput" size=18 style="width: 100%" >
						</div>
					</td>
					<td align="left" valign="center">
						<select name="chatColor" onChange="doColorChange();">
						<?
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
						?>	
						</select>
					</td>
					<td align="center" valign="center">
						<input type="IMAGE" name="Submit"
							<?=makeButton("absenden","src") . tooltip(_("Nachricht senden"))?> border="0" value="senden">
					</td>
					<td align="right" valign="center">
						<a href="javascript:doQuit();"><img <?=tooltip(_("Chat verlassen")) . makeButton("abbrechen","src")?> border="0"></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>
</form>
<script  type="text/javascript">
document.inputform.chatInput.focus();
</script>
</body>
</html>
<?
page_close();
?>

