<?
/**
* helper functions for handling contacts
* 
* helper functions for handling contacts
*
* @author				Ralf Stockmann <rstockm@gwdg.de>
* @version			$Id$
* @access				public
* @package			studip_core
* @modulegroup	library
* @module				contact.inc.php
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",false);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// statusgruppe.inc.php
// Copyright (c) 2002 Ralf Stockmann <rstockm@gwdg.de>
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
* built a not existing ID
*
* @access private
* @return	string
*/
function MakeUniqueContactID ()
{	// baut eine ID die es noch nicht gibt

	$hash_secret = "kertoiisdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));
	$db->query ("SELECT contact_id FROM contact WHERE contact_id = '$tmp_id'");	
	IF ($db->next_record()) 	
		$tmp_id = MakeUniqueContactID(); //ID gibt es schon, also noch mal
	RETURN $tmp_id;
}

function MakeUniqueUserinfoID ()
{	// baut eine ID die es noch nicht gibt

	$hash_secret = "kertoiisdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));
	$db->query ("SELECT userinfo_id FROM contact_userinfo WHERE userinfo_id = '$tmp_id'");	
	IF ($db->next_record()) 	
		$tmp_id = MakeUniqueContactID(); //ID gibt es schon, also noch mal
	RETURN $tmp_id;
}

function ChangeBuddy($contact_id)
{
	$db=new DB_Seminar;
	$db->query ("SELECT buddy FROM contact WHERE contact_id = '$contact_id'");	
	while ($db->next_record()) {
		$buddynew = abs($db->f("buddy")-1);  //setzt buddy auf den anderen Wert
		$db->query("UPDATE contact SET buddy=$buddynew WHERE contact_id = '$contact_id'");
	}
}

function RemoveBuddy($username)
{ global $user;
	$owner_id = $user->id;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id' AND user_id = '$user_id'");	
	if ($db->next_record()) {
		$contact_id = $db->f("contact_id")	;
		$db2->query("UPDATE contact SET buddy='0' WHERE contact_id = '$contact_id'");
	}
}

function CheckBuddy($username)
{ global $user;
	$owner_id = $user->id;
	$buddy = "";
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT buddy FROM contact WHERE owner_id = '$owner_id' AND user_id = '$user_id' AND buddy = '1'");	
	if ($db->next_record()) {
		$buddy = TRUE;
	} else {
		$buddy = FALSE;
	}
	return $buddy;
}

function GetSizeofBook()
{ global $user;
	$owner_id = $user->id;
	$db=new DB_Seminar;
	$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id'");	
	if ($db->next_record()) {
		$size = $db->num_rows();
	} else {
		$size="keine";
	}
	return $size;
}

function GetSizeOfBookByLetter()
{ 	global $user;
 	$ret = false;
 	$db = new DB_Seminar();
 	$db->query("SELECT LCASE(LEFT(TRIM(Nachname),1)) AS first_letter, count(*) AS anzahl FROM contact LEFT JOIN auth_user_md5 USING(user_id)
 				WHERE owner_id='$user->id' AND NOT ISNULL(Nachname) GROUP BY first_letter");
 	while ($db->next_record()){
 		$ret[$db->f('first_letter')] = $db->f('anzahl');
 	}
 	return $ret;
 }
 

function AddBuddy($username)
{ global $user;

	$owner_id = $user->id;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id' AND user_id = '$user_id'");	
	if ($db->next_record()) {
		$contact_id = $db->f("contact_id")	;
		$db2->query("UPDATE contact SET buddy='1' WHERE contact_id = '$contact_id'");
	}
}

function AddNewContact ($user_id)
{ 	// Inserting an new contact
	global $user;
	$contact_id = MakeUniqueContactID();
	$owner_id = $user->id;
	$db=new DB_Seminar;
	$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id' AND user_id = '$user_id'");	
	if (!$db->next_record()) 	// nur wenn es die Kombination owner/user noch nicht gibt
		$db->query("INSERT INTO contact SET contact_id = '$contact_id', owner_id = '$owner_id', user_id= '$user_id', buddy='1'");
	return $contact_id;	
} 

function AddNewUserinfo ($contact_id, $name, $content)
{ 	// Inserting an new contact
	global $user;
	$userinfo_id = MakeUniqueUserinfoID();
	$db=new DB_Seminar;
	$db->query ("SELECT MAX(priority) as maximum FROM contact_userinfo WHERE contact_id = '$contact_id'");	
	if ($db->next_record()) {
		$priority = $db->f("maximum")+1;	
	}
	$db->query("INSERT INTO contact_userinfo SET userinfo_id = '$userinfo_id', contact_id = '$contact_id', name = '$name', content= '$content', priority= '$priority'");
	return $userinfo_id;	
} 

function GetExtraUserinfo ($contact_id)
{ 	// Build an array with extrauserinfos
		$output = "";	
		$db=new DB_Seminar;
		$db->query ("SELECT * FROM contact_userinfo WHERE contact_id = '$contact_id' ORDER BY priority");	
		while ($db->next_record()) 	{
			$userinfo[$db->f("name")] = $db->f("content");	
		}
		return $userinfo;
}

function GetUserInfo($user_id)
{
	$db=new DB_Seminar;
	$db->query ("SELECT * FROM user_info WHERE user_id = '$user_id'");	
	if ($db->next_record()) {	
		if ($db->f("Home")!="")
			$userinfo["Homepage"] = "<a href=\"".$db->f("Home")."\">".$db->f("Home")."</a>";
		if ($db->f("privatnr")!="")
			$userinfo["Privat Tel."] = $db->f("privatnr");
		if ($db->f("privadr")!="")
			$userinfo["Addresse"] = $db->f("privadr");
	}
	$db->query ("SELECT sprechzeiten, raum, user_inst.telefon, user_inst.fax, Name, Institute.Institut_id FROM user_inst LEFT JOIN Institute USING(Institut_id) WHERE user_id = '$user_id' AND inst_perms != 'user'");	
	while ($db->next_record()) {	
		$userinfo["Einrichtung"] = "<a href=\"institut_main.php?auswahl=".$db->f("Institut_id")."\">".htmlReady($db->f("Name"))."</a>";
		if ($db->f("raum")!="")
			$userinfo["Raum"] = $db->f("raum");
		if ($db->f("sprechzeiten")!="")
			$userinfo["Sprechzeiten"] = $db->f("sprechzeiten");
		if ($db->f("telefon")!="")
			$userinfo["Dienst Tel."] = $db->f("telefon");			
		if ($db->f("fax")!="")
			$userinfo["Dienst Fax"] = $db->f("fax");
	}
	return $userinfo;
}

function ShowUserInfo ($contact_id)
{ 	// Show the standard userinfo
	global $user, $open, $edit_id;

	$output = "";
	$db=new DB_Seminar;
	$db->query ("SELECT user_id FROM contact WHERE contact_id = '$contact_id'");	
	if ($db->next_record()) {	
		$user_id = $db->f("user_id");
	}
	$db->query ("SELECT Email, username FROM auth_user_md5 WHERE user_id = '$user_id'");	
	if ($db->next_record()) {	
		$basicinfo["Email"] = "<a href=\"mailto:".$db->f("Email")."\">".$db->f("Email")."</a>";
		$basicinfo["Stud.IP"] = "<a href=\"about.php?username=".$db->f("username")."\">Home</a>";
	}

	// diese Infos hat jeder
	while(list($key,$value) = each($basicinfo)) {
		$output .= "<tr><td class=\"steelgraulight\" width=\"100\"><font size=\"2\">".$key.":</font></td><td class=\"steelgraulight\" width=\"250\"><font size=\"2\">".$value."</font></td></tr>";
	}

	// hier Zusatzinfos

	if ($open == $contact_id || $open == "all" || $edit_id) {

		$userinfo = GetUserInfo($user_id);
		if (sizeof($userinfo)>0) {
			while(list($key,$value) = each($userinfo)) {
				$output .= "<tr><td class=\"steel1\" width=\"100\"><font size=\"2\">".$key.":</font></td><td class=\"steel1\" width=\"250\"><font size=\"2\">".$value."</font></td></tr>";
			}
		}

		$extra = GetExtraUserinfo ($contact_id);
		if (sizeof($extra)>0) {
			while(list($key,$value) = each($extra)) {
				$output .= "<tr><td class=\"steel1\" width=\"100\"><font size=\"2\">".htmlReady($key).":</font></td><td class=\"steel1\" width=\"250\"><font size=\"2\">".formatReady($value)."</font></td></tr>";
			}
		}

		if(file_exists("./user/".$user_id.".jpg")) {
			$output.="<tr><td align=\"center\" class=\"steel1\" colspan=\"2\" width=\"350\"><br><img src=\"./user/".$user_id.".jpg\" border=1></td>";
		}
		$owner_id = $user->id;
		$db->query ("SELECT DISTINCT name, statusgruppen.statusgruppe_id FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE user_id = '$user_id' AND range_id= '$owner_id'");	
		while ($db->next_record()) {		
			$output .= "<tr><td class=\"steel1\" width=\"100\"><font size=\"2\">Gruppe:</font></td><td class=\"steel1\" width=\"250\"><a href=\"$PHP_SELF?view=gruppen&filter=".$db->f("statusgruppe_id")."\"><font size=\"2\">".$db->f("name")."</font></a></td></tr>";		
		}		
	}
	return $output;	
} 

function ShowContact ($contact_id)
{	// Ausgabe eines Kontaktes
	global $PHP_SELF, $open, $filter, $forum, $auth;
	$db=new DB_Seminar;
	$db->query ("SELECT contact_id, user_id, buddy FROM contact WHERE contact_id = '$contact_id'");	
	if ($db->next_record()) {
		if ($open == $contact_id || $open == "all") {
			if ($db->f("buddy")=="1") {
				$buddy = "<a href=\"$PHP_SELF?cmd=changebuddy&contact_id=$contact_id#anker\"><img src=\"pictures/nutzeronline.gif\" border=\"0\"></a>&nbsp; ";
			} else {
				$buddy = "<a href=\"$PHP_SELF?cmd=changebuddy&contact_id=$contact_id#anker\"><img src=\"pictures/nutzer.gif\" border=\"0\"></a>&nbsp; ";			
			}
			$lastrow =  	"<tr><td colspan=\"2\" class=\"steel1\" align=\"right\">"
						.$buddy		
						."<a href=\"sms.php?sms_source_page=contact.php&cmd=write&rec_uname=".get_username($db->f("user_id"))."\"><img src=\"pictures/nachricht1.gif\" border=\"0\"></a>&nbsp; "
						."<a href=\"$PHP_SELF?edit_id=$contact_id\"><img src=\"pictures/einst.gif\" border=\"0\"></a>&nbsp; "
						."<a href=\"$PHP_SELF?cmd=delete&contact_id=$contact_id\"><img src=\"pictures/trash_att.gif\" border=\"0\"></a></td></tr>"
						."<tr><td colspan=\"2\" class=\"steelgraulight\" align=\"center\"><a href=\"$PHP_SELF?filter=$filter\"><img src=\"pictures/forumgraurauf.gif\" border=\"0\"></a></td></tr>";
		} else {
			if ($forum["jshover"]==1 AND $auth->auth["jscript"]) { // Hovern
				$description = "";	

				$userinfo = GetUserInfo($db->f("user_id"));
				if (sizeof($userinfo)>0) {
					while(list($key,$value) = each($userinfo)) {
//						$description .= "<tr><td class=\"steel1\" width=\"100\"><font size=\"2\">".$key.":</font></td><td class=\"steel1\" width=\"250\"><font size=\"2\">".$value."</font></td></tr>";
						$description .= "<b>".FormatReady($key).":</b>       ".$value."\n";
					}
				}

				$extra = GetExtraUserinfo ($contact_id);
				if (sizeof($extra)>0) {
					while(list($key,$value) = each($extra)) {
						$description .= "<b>".JSReady($key).":</b>      ".FormatReady($value)."\n";
					}
				}

				$hoverlink = "<a href=\"$PHP_SELF?filter=$filter&open=".$contact_id."#anker\" ";
				$name = "huhu";
				$txt = "<hr>Klicken zum Bearbeiten";
				$bild = "pictures/forumgraurunt.gif";
				$link =	$hoverlink
						."onMouseOver=\"return overlib('"
						.JSReady($description,"contact").$txt
						."', NOCLOSE, CSSOFF)\" "
						." onMouseOut=\"nd();\"><img src=\"".$bild."\" border=0></a>";
			} else {
				$link = "<a href=\"$PHP_SELF?filter=$filter&open=".$contact_id."#anker\"><img src=\"pictures/forumgraurunt.gif\" border=\"0\"></a>";
			}

			$lastrow = "<tr><td colspan=\"2\" class=\"steelgraulight\" align=\"center\">".$link."</td></tr>";
		}			
		if ($open == $contact_id) {		//es ist ein einzelner Beitrag aufgeklappt, also Anker setzen
			$output = "<a name=\"anker\"></a>";
		} else {
			$output = "";
		}
		$output .= "<table cellspacing=\"0\" width=\"350\" class=\"blank\">
					<tr>
						<td class=\"topic\" colspan=\"2\"><font size=\"2\"><b>"
							.get_fullname($db->f("user_id"), $format = "full_rev" )."</b></font></td>"
							."
						</td>
					</tr>"
						.ShowUserInfo ($contact_id)
						. $lastrow
				."</table>";
	} else {
		$output = "Fehler!";
	}
	return $output;
}

function SearchResults ($search_exp)
{ global $SessSemName, $_fullname_sql,$_range_type;
	$db=new DB_Seminar;
	$query = "SELECT DISTINCT auth_user_md5.user_id, " . $_fullname_sql['full_rev'] ." AS fullname, username, perms ".
		"FROM auth_user_md5 LEFT JOIN user_info USING (user_id) ".
		"WHERE (Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%') ORDER BY Nachname ";

	$db->query($query); // results all users which are not in the seminar
	if (!$db->num_rows()) {
		echo "&nbsp; keine Treffer&nbsp; ";
	} else {
		echo "&nbsp; <select name=\"Freesearch\">";
		while ($db->next_record()) {
			printf ("<option value=\"%s\">%s - %s\n", $db->f("username"), htmlReady(my_substr($db->f("fullname"),0,35)." (".$db->f("username").")"), $db->f("perms"));
		}
		echo "</select>";
	}
}

function ShowEditContact ($contact_id)
{	// Ausgabe eines zu editierenden Kontaktes
	global $PHP_SELF, $open, $filter, $edit_id;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT user_id FROM contact WHERE contact_id = '$contact_id'");	
	if ($db->next_record()) {

		$lastrow =	"<tr><td class=\"steel2\">"
					."<input type=\"text\" name=\"owninfolabel[]\" value=\"Neue Rubrik\"></td>"
					."<td colspan=\"2\" class=\"steel2\"><textarea style=\"width: 55%\" cols=\"20\" rows\"3\" wrap=virtual name=\"owninfocontent[]\" value=\"Inhalt\">Inhalt</textarea>"
					."\n"
					. "</td></tr>";
		$lastrow .= "<tr><td valign=\"middle\" colspan=\"3\" class=\"steelgraulight\" align=\"center\"><br><a href=\"$PHP_SELF?open=$contact_id#anker\"><img src= \"./pictures/buttons/zurueck-button.gif\" border=\"0\" ".tooltip("zur&uuml;ck zur &Uuml;bersicht")."></a>&nbsp; <input type=\"IMAGE\" name=\"search\" src= \"./pictures/buttons/uebernehmen-button.gif\" border=\"0\" value=\" Personen suchen\" ".tooltip("Seite aktualisieren")."></form></td></tr>";
		$output = "<table cellspacing=\"0\" width=\"700\" class=\"blank\">
					<tr>
						<td class=\"topicwrite\" colspan=\"3\">"
							.get_fullname($db->f("user_id"), $format = "full_rev" )."</td>"
							."
						</td>
					</tr>"
						.ShowUserInfo ($contact_id)."</table><table cellspacing=\"0\" width=\"700\" class=\"blank\">"
						."<form action=\"$PHP_SELF?edit_id=$contact_id\" method=\"POST\">";
						
		$db2->query ("SELECT * FROM contact_userinfo WHERE contact_id = '$contact_id' ORDER BY priority");	
		$i = 0;
		while ($db2->next_record()) 	{
			if ($i ==0) {
				$output .= "<tr><td class=\"steel1\" width=\"100\" NOWRAP><input type=\"HIDDEN\" name=\"userinfo_id[]\" value=\"".$db2->f("userinfo_id")."\"><input type=\"text\" name=\"existingowninfolabel[]\" value=\"".$db2->f("name")."\"></td><td class=\"steel1\" width=\"250\"><textarea name=\"existingowninfocontent[]\" value=\"".$db2->f("content")."\" style=\"width: 90%\" cols=\"20\" rows\"3\" wrap=virtual>".$db2->f("content")."</textarea></td><td class=\"steel1\" width=\"50\"><a href=\"$PHP_SELF?edit_id=$contact_id&deluserinfo=".$db2->f("userinfo_id")."\"><img src=\"pictures/trash.gif\" border=\"0\"></a></td></tr>";
			} else {
				$output .= "<tr><td class=\"steel1\" width=\"100\" NOWRAP><input type=\"HIDDEN\" name=\"userinfo_id[]\" value=\"".$db2->f("userinfo_id")."\"><input type=\"text\" name=\"existingowninfolabel[]\" value=\"".$db2->f("name")."\"></td><td NOWRAP class=\"steel1\" width=\"250\"><textarea name=\"existingowninfocontent[]\" value=\"".$db2->f("content")."\" style=\"width: 90%\" cols=\"20\" rows\"3\" wrap=virtual>".$db2->f("content")."</textarea></td><td class=\"steel1\" width=\"50\" nowrap><a href=\"$PHP_SELF?edit_id=$contact_id&deluserinfo=".$db2->f("userinfo_id")."\"><img src=\"pictures/trash.gif\" border=\"0\"></a>&nbsp; <a href=\"$PHP_SELF?edit_id=$contact_id&move=".$db2->f("userinfo_id")."\"><img src=\"pictures/move_up.gif\" border=\"0\"></a></td></tr>";			
			}
			$i++;
		}
		$output .= "<tr><td class=\"steel1\" colspan=\"3\">&nbsp; </td></tr>".$lastrow
				."</table>";
	} else {
		$output = "Fehler!";
	}
	return $output;
}

function MoveUserinfo($userinfo_id)
{
	$db=new DB_Seminar;
	$db->query ("SELECT * FROM contact_userinfo WHERE userinfo_id = '$userinfo_id'");	
	if ($db->next_record()) {
		$priority = $db->f("priority");		
		$prioritybevore = $db->f("priority")-1;		
		$contact_id = $db->f("contact_id");		
	}
	$db->query ("SELECT * FROM contact_userinfo WHERE contact_id = '$contact_id' AND priority = '$prioritybevore'");	
	if ($db->next_record()) {
		$userinfobevore_id = $db->f("userinfo_id");			
	}
	$db->query("UPDATE contact_userinfo SET priority = '$prioritybevore' WHERE userinfo_id = '$userinfo_id'");
	$db->query("UPDATE contact_userinfo SET priority = '$priority' WHERE userinfo_id = '$userinfobevore_id'");
}

function UpdateUserinfo($name, $content, $userinfo_id)
{
	$db=new DB_Seminar;
	$db->query("UPDATE contact_userinfo SET name =  '$name', content = '$content' WHERE userinfo_id = '$userinfo_id'");
}

function ResortUserinfo($contact_id)
{	// resort the userinfos after deleting an item etc.
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$i = 0;
	$db->query ("SELECT * FROM contact_userinfo WHERE contact_id = '$contact_id' ORDER BY priority");	
	while ($db->next_record()) {
		$userinfo_id = $db->f("userinfo_id");
		$db2->query("UPDATE contact_userinfo SET priority =  '$i' WHERE userinfo_id = '$userinfo_id'");
		$i++;
	}
}

function DeleteUserinfo ($userinfo_id)
{	// loeschen einer Userinfo
	$db=new DB_Seminar;
	$db->query ("SELECT contact_id FROM contact_userinfo WHERE userinfo_id = '$userinfo_id'");	
	if ($db->next_record()) {
		$contact_id = $db->f("contact_id");	
	}
	$db->query ("DELETE FROM contact_userinfo WHERE userinfo_id = '$userinfo_id'");	
	ResortUserinfo($contact_id);
}

function DeleteContact ($contact_id)
{	// loeschen eines Kontaktes
	global $user;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db->query ("SELECT owner_id, user_id FROM contact WHERE contact_id = '$contact_id'");	
	if ($db->next_record()) {
		if ($db->f("owner_id")!=$user->id) {
			$ouput = "Sie haben kein Zugriffsrecht auf diesen Kontakt!";	
		} else {
			$user_id = $db->f("user_id");
			$owner_id = $db->f("owner_id");
			$db->query ("DELETE FROM contact WHERE contact_id = '$contact_id'");	
			$db->query ("DELETE FROM contact_userinfo WHERE contact_id = '$contact_id'");
			$db2->query ("SELECT DISTINCT statusgruppe_user.statusgruppe_id FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE range_id = '$owner_id' AND user_id = '$user_id'");		
			WHILE ($db2->next_record()) {	
				$statusgruppe_id = $db2->f("statusgruppe_id");
				$db3->query ("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");	
			}
			$output = "Kontakt gel&ouml;scht";
		}
	}
	return $output;
}

function PrintEditContact($edit_id)
{	global $user;
	$owner_id = $user->id;
	$db=new DB_Seminar;
	$db->query ("SELECT contact_id, nachname FROM contact LEFT JOIN auth_user_md5 using(user_id) WHERE owner_id = '$owner_id' AND contact_id = '$edit_id'");	
	echo "<table class=\"blank\" width=\"700\" align=center cellpadding=\"10\"><tr><td valign=\"top\" width=\"700\" class=\"blank\">";
	while ($db->next_record()) {
			$contact_id = $db->f("contact_id");
			echo ShowEditContact ($contact_id);
			echo "<br>";
	}
	echo "</td></tr></table>";
}

function PrintAllContact($filter="")
{	global $user, $open, $filter, $contact;
	$i = 1;
	$owner_id = $user->id;
	$db=new DB_Seminar;

	if ($contact["view"]=="alpha" && $filter!="") 
		$db->query ("SELECT contact_id, nachname FROM contact LEFT JOIN auth_user_md5 using(user_id) WHERE owner_id = '$owner_id' AND LEFT(nachname,1) = '$filter' ORDER BY nachname");	
	if ($contact["view"]=="alpha" && $filter=="") 
		$db->query ("SELECT contact_id, nachname FROM contact LEFT JOIN auth_user_md5 using(user_id) WHERE owner_id = '$owner_id' ORDER BY nachname");		
	if ($contact["view"]=="gruppen" && $filter=="") 
		$db->query ("SELECT contact_id, nachname FROM contact LEFT JOIN auth_user_md5 using(user_id) WHERE owner_id = '$owner_id' ORDER BY nachname");		
	if ($contact["view"]=="gruppen" && $filter!="") 
		$db->query ("SELECT nachname, contact_id FROM contact LEFT JOIN statusgruppe_user USING(user_id) LEFT JOIN auth_user_md5 USING(user_id)  WHERE statusgruppe_id = '$filter' AND owner_id =  '$owner_id' ORDER BY nachname");		
	$middle = round($db->num_rows()/2);
	if ($middle == 0) {
		echo "<table class=\"blank\" width=\"700\" align=center cellpadding=\"10\"><tr><td valign=\"top\" width=\"350\" class=\"blank\">Keine Eintr&auml;ge in diesem Bereich.";	
		echo "</td><td valign=\"top\" width=\"350\" class=\"blank\">";
	} else {
		echo "<table class=\"blank\" width=\"700\" align=center cellpadding=\"10\"><tr><td valign=\"top\" width=\"350\" class=\"blank\">";
		while ($db->next_record()) {
			$contact_id = $db->f("contact_id");
			echo ShowContact ($contact_id);
			echo "<br>";
			if ($i==$middle) { //Spaltenumbruch
				echo "</td><td valign=\"top\" width=\"350\" class=\"blank\">";
			}
		$i++;
		}
	}
	echo "</td></tr></table>";
}

?>