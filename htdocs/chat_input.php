<?
/*
This file is part of StudIP -
chat_input.php
Erzeugt das Eingabefenster,f�gt geschriebene Nachrichten ein
Copyright (c) 2002 Andr� Noack <andre.noack@gmx.net>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

//chat eingeschaltet?
if (!$CHAT_ENABLE) {
	page_close();
	die;
}

require "ChatShmServer.class.php";
//Studip includes
require "msg.inc.php";

$chatServer=new ChatShmServer;

?>
<html>
<head>
       <title>ChatInput</title>
<link rel="stylesheet" href="style.css" type="text/css">
<script type="text/javascript">
    function doQuit(){
	    document.inputform.chatInput.value="/quit bye";
	    document.inputform.submit();
    }
    
    function doColorChange(){
    	for(i=0;i<document.inputform.chatColor.length;++i)
     		if(document.inputform.chatColor.options[i].selected == true){
			document.inputform.chatInput.value="/color " +
			document.inputform.chatColor.options[i].value;
			document.inputform.submit();
    		}
	}
</script>
<script type="text/javascript">
    function printhelp(){
	    document.inputform.chatInput.value="/help";
	    document.inputform.submit();
    }
 </script>

</head>
<body style="background-color:white;background-image:url('pictures/steel1.jpg');">
<?
//darf ich �berhaupt hier sein ?
if (!$chatServer->isActiveUser($user->id,$chatid)) {
     ?><table width="100%"><tr><?
     my_error("Du bist nicht in diesem Chat
     angemeldet!",$class="blank",$colspan=1,false);
     ?></tr></table></body></html><?
     page_close();
     die;
}


//neue chatnachricht einf�gen
if ($chatInput) {
	if ($chatServer->isActiveUser($user->id,$chatid)){
          $chatInput=stripslashes($chatInput);
          $chatServer->addMsg($user->id,$chatid,$chatInput);
          //evtl Farbe umstellen
          $cmdStr=trim(substr($chatInput." ",1,strpos($chatInput," ")-1));
          $msgStr=trim(strstr($chatInput," "));
          if ($cmdStr=="color" AND $msgStr!="" AND $msgStr!="\n" AND $msgStr!="\r")
               $chatServer->chatUser[$user->id][$chatid]["color"]=$msgStr;
          }

}

?>
<form method="post" action="<?=$PHP_SELF?>" name="inputform">
<input type="hidden" name="chatid" value="<?=$chatid?>">
<div align="center">
       <table width="100%" border="0" bgcolor="white" cellspacing="0" cellpadding="0" align="center">
          <tr>
                  <td width="99%" align="left" class="topic" ><b>&nbsp;Chat -
		  <?=$chatServer->chatDetail[$chatid]["name"]?></b></td><td width="1%" align="right" class="topic" ><a href="javascript:printhelp();"><img src="pictures/hilfe.gif" border=0 align="texttop" alt="zur Hilfe"></a>&nbsp; <a href="show_smiley.php" target=new><img src="pictures/smile/smile.gif" border=0 align="absmiddle" alt="Alle verf&uuml;gbaren Smileys anzeigen"></a>&nbsp; </td>
           </tr>
           <tr>
               <td align="center" colspan=2 valign="center">

                    <table width="100%" border="0" cellspacing="3">
                      <tr valign="middle">
                              <td align="left" valign="center">Message:</td>
                              <td width="60%" valign="center">
			<div align="center" valign="center">
                          <input type="text" name="chatInput" size=18
			  style="width: 100%" >
			  </div>
                              </td>
                              <td align="left" valign="center">
			      <select name="chatColor" onChange="doColorChange();">
			      <?
			      foreach($chatColors as $c){
			      	print "<option style=\"color:$c;\" value=\"$c\" ";
                         if ($chatServer->chatUser[$user->id][$chatid]["color"]==$c){
                         $selected=true;
                         print " selected ";
                         }
				print ">$c</option>\n";
				}
                    if (!$selected) print "<option style=\"color:".$chatServer->chatUser[$user->id][$chatid]["color"].";\" value=\"".$chatServer->chatUser[$user->id][$chatid]["color"]."\" selected>user</option>\n";
				?>	
			      </select>
			      </td>
			      <td align="center" valign="center">
			        <input type="IMAGE" name="Submit"
				src="pictures/buttons/absenden-button.gif" alt="Nachricht senden"
				border="0" 
				value="senden">
                		</td>
				<td align="right" valign="center">
                    		<a href="javascript:doQuit();"><img alt="Chat verlassen" src="pictures/buttons/abbrechen-button.gif" border="0"></a>
                   		</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
</div>
    </form>
<script>
document.inputform.chatInput.focus();
</script>
</body>
</html>
<?
page_close();
?>
