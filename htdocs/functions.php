<?
/**
* functions.php
* 
* The Stud.IP-Core functions. Look to the descriptions to get further details
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, Ralf Stockmann <rstockm@gwdg.de>, Andr� Noack Andr� Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		functions.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, 
// Ralf Stockmann <rstockm@gwdg.de>, Andr� Noack Andr� Noack <andre.noack@gmx.net>
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
* This function creates the header line for studip-objects
*
* you will get a line like this "Veranstaltung: Name..."
*
* @param		string	the id of the Veranstaltung
* @retunr		string	the header-line
*
*/

function getHeaderLine ($id) {
	global $SEM_TYPE,$INST_TYPE;
	
	$db = new DB_Seminar;
	$object_type = get_object_type($id);
	
	//header-line for Veranstaltungen
	if ($object_type == "sem") {
		$query = sprintf ("SELECT status, Name FROM seminare WHERE Seminar_id = '%s' ", $id);
		$db->query($query);
		$db->next_record();

		if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME)
			$header_line = "Veranstaltung";
		else
			$header_line = $SEM_TYPE[$db->f("status")]["name"];

		if (!$header_line)
			$header_line = "Veranstaltung";
		
		$header_line.=": ". htmlReady(substr($db->f("Name"), 0, 60));
		
		if (strlen($db->f("Name")) > 60)
			$header_line.= "... ";
	
	//header-line for Einrichtungen
	} elseif($object_type == "inst" || $object_type == "fak") {
		$query = sprintf ("SELECT type, Name FROM Institute WHERE Institut_id = '%s' ", $id);
		$db->query($query);
		$db->next_record();		

		$header_line = $INST_TYPE[$db->f("type")]["name"];

		if (!$header_line)
			$header_line = "Einrichtung";
		
		$header_line.= ": ". htmlReady(substr($db->f("Name"), 0, 60));
		
		if (strlen($db->f("Name")) > 60)
			$header_line.= "... ";
	}
	
	return $header_line; 
}


/**
* This function "opens" a Veranstaltung to work with it
*
* The following variables will bet set:
*	$SessionSeminar					Veranstaltung id<br>
*	$SessSemName[0]					Veranstaltung name<br>
*	$SessSemName[1]					Veranstaltung id<br>
*	$SessSemName[2]					Veranstaltung ort (room)<br>
*	$SessSemName[3]					Veranstaltung Untertitel (subtitle)<br>
*	$SessSemName[4]					Veranstaltung start_time (the Semester start_time)<br>
*	$SessSemName[5]					Veranstaltung institut_id (the home-intitute)<br>
*	$SessSemName["art"]				Veranstaltung type in alphanumeric form<br>
*	$SessSemName["art_num"]			Veranstaltung type in numeric form<br>
*	$SessSemName["art_generic"]		Veranstaltung generic type in alhanumeric form (self description)<br>
*	$SessSemName["class"]				Veranstaltung class (sem or inst, in this function always sem)<br>
*	$SessSemName["header_line"]		the header-line to use on every page of the Veranstaltung<br />
*	$loginfilelast[$sem_id]				last login-time to the Veranstaltung<br>
*	$loginfilenowt[$sem_id]				current login-time to the Veranstaltung<br>
*
* @param		string	the id of the Veranstaltung
*
*/
function openSem ($sem_id) {
	global $SEM_TYPE, $SessionSeminar, $SessSemName, $loginfilenow, $loginfilelast;

	$db=new DB_Seminar;

	$SessionSeminar="$sem_id";
	$db->query ("SELECT Institut_id, Name, Seminar_id, Untertitel, start_time, status FROM seminare WHERE Seminar_id='$sem_id'");
	while ($db->next_record()) {
		$SessSemName[0] = $db->f("Name");
		$SessSemName[1] = $db->f("Seminar_id");
		$SessSemName[3] = $db->f("Untertitel");
		$SessSemName[4] = $db->f("start_time");
		$SessSemName[5] = $db->f("Institut_id");
		$SessSemName["art_generic"]="Veranstaltung";
		$SessSemName["class"]="sem";
		$SessSemName["art_num"]=$db->f("status");
		if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME)
			$SessSemName["art"] = "Veranstaltung";
		else
			$SessSemName["art"] = $SEM_TYPE[$db->f("status")]["name"];
		$nr = $db->f("Seminar_id");
		
		$SessSemName["header_line"] = getHeaderLine ($sem_id);

		$loginfilelast["$nr"] = $loginfilenow["$nr"];
		$loginfilenow["$nr"] = time();
	}
}

/**
* This function "opens" an Einrichtung to work with it
*
* Note: Stud.IP treats Einrichtungen like Veranstaltungen, yu can see this
* especially if you look at the variable names....
*
* The following variables will bet set:
*	$SessionSeminar					Einrichtung id<br>
*	$SessSemName[0]					Einrichtung name<br>
*	$SessSemName[1]					Einrichtung id<br>
*	$SessSemName["art"]				Einrichtung type in alphanumeric form<br>
*	$SessSemName["art_num"]			Einrichtung type in numeric form<br>
*	$SessSemName["art_generic"]		Einrichtung generic type in alhanumeric form (self description)<br>
*	$SessSemName["class"]				Einrichtung class (sem or inst, in this function always inst)<br>
*	$SessSemName["header_line"]		the header-line to use on every page of the Einrichtung<br />
*	$loginfilelast[$sem_id]				last login-time to the Einrichtung<br>
*	$loginfilenowt[$sem_id]				current login-time to the Einrichtung<br>
*
* @param		string	the id of the Veranstaltung
*
*/
function openInst ($inst_id) {
	global $SessionSeminar, $SessSemName, $loginfilenow, $loginfilelast, $INST_TYPE;

	$db=new DB_Seminar;

	$SessionSeminar="$inst_id";
	$db->query ("SELECT Name, Institut_id, type,fakultaets_id, IF(Institut_id=fakultaets_id,1,0) AS is_fak FROM Institute WHERE Institut_id='$inst_id'");
	while ($db->next_record()) {
		$SessSemName[0] = $db->f("Name");
		$SessSemName[1] = $db->f("Institut_id");
		$SessSemName["art_generic"]="Einrichtung";
		$SessSemName["art"]=$INST_TYPE[$db->f("type")]["name"];
		if (!$SessSemName["art"])
			$SessSemName["art"]=$SessSemName["art_generic"];
		$SessSemName["class"] = "inst";
		$SessSemName["is_fak"] = $db->f("is_fak");
		$SessSemName["art_num"]=$db->f("type");
		$SessSemName["fak"] = $db->f("fakultaets_id");
		
		$SessSemName["header_line"] = getHeaderLine ($inst_id);		
		
		$nr = $db->f("Institut_id");
		$loginfilelast["$nr"] = $loginfilenow["$nr"];
		$loginfilenow["$nr"] = time();
	}
}

/**
* This function closes a opened Veranstaltung or Einrichtung
*/
function closeObject() {
	global $SessionSeminar, $SessSemName;
	
	$SessionSeminar='';
	$SessSemName='';
}

/**
* This function returns the last activity in the Veranstaltung
*
* @param		string	the id of the Veranstaltung
* @return		integer	unix timestamp
*
*/
function lastActivity ($sem_id) {
	$db=new DB_Seminar;
	
	//Veranstaltungs-data
	$db->query("SELECT chdate FROM seminare WHERE Seminar_id = '$sem_id'");
	$db->next_record();
	$timestamp = $db->f("chdate");
	
	//Postings
	$db->query("SELECT chdate FROM px_topics WHERE Seminar_id = '$sem_id'  ORDER BY chdate DESC LIMIT 1");
	
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//Postings
	$db->query("SELECT chdate FROM folder WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//Dokuments
	$db->query("SELECT chdate FROM dokumente WHERE seminar_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//Postings
	$db->query("SELECT chdate FROM folder WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//Literatur
	$db->query("SELECT chdate FROM literatur WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//Dates
	$db->query("SELECT chdate FROM termine WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//News
	$db->query("SELECT date FROM news_range LEFT JOIN news USING (news_id)  WHERE range_id = '$sem_id' ORDER BY date LIMIT 1");
	$db->next_record();
	if ($db->f("date") > $timestamp)
		$timestamp = $db->f("date");
	
	//correct the timestamp, if date in the future (news can be in the future!)
	if ($timestamp > time())
		$timestamp = time();
		
	return $timestamp;
}


/**
* This function determines, which from which type an id is from.
*
* The function recognizes the following types at this moment:
* Einrichtungen, Veranstaltungen, Statusgruppen and Fakultaeten
* 
* @param		string	id	the id of the object
* @return		string	return "ins" (Einrichtung), "sem" (Veranstaltung), "fak" (Fakultaeten), "group" (Statusgruppe), "dokument" (Dateien)
*
*/
function get_object_type($id) {
	 $db=new DB_Seminar;

	$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id = '$id' ");
	if ($db->next_record())
		return "sem";

	$db->query("SELECT Institut_id,IF(Institut_id=fakultaets_id,1,0) AS is_fak FROM Institute WHERE Institut_id = '$id' ");
	if ($db->next_record())
		return ($db->f("is_fak")) ? "fak" : "inst";

	$db->query("SELECT user_id FROM auth_user_md5 WHERE user_id = '$id' ");
	if ($db->next_record())
		return "user";

	$db->query("SELECT item_id FROM range_tree WHERE item_id = '$id' ");
	if ($db->next_record())
		return "range_tree";

	$db->query("SELECT termin_id FROM termine WHERE termin_id = '$id' ");
	if ($db->next_record())
		return "date";

	$db->query("SELECT statusgruppe_id FROM statusgruppen WHERE statusgruppe_id = '$id' ");
	if ($db->next_record())
		return "group";

	$db->query("SELECT dokument_id FROM dokumente WHERE dokument_id = '$id' ");
	if ($db->next_record())
		return "dokument";

	return FALSE;
}

/**
* The function calculate one of the group colors unique for the Semester of the Veranstaltung
*
* It calculate a unique color number to create the initial entry for a new user in a Veranstaltung.
* It will create a unique number for every Semester and will start over, if the the max. numer 
* (7) is reached.
* 
* @param		integer	the timestamp of the start time from the Semester
* @param		string	this field is no more necessary but only for compatibilty reasons here
* @return		integer	the color number
*
*/
function select_group($sem_start_time, $user_id='') {
	global $SEMESTER;
	//Farben Algorhytmus, erzeugt eindeutige Farbe fuer jedes Semester. Funktioniert ab 2001 die naechsten 1000 Jahre.....
	$year_of_millenium=date ("Y", $sem_start_time) % 1000;
	$index=$year_of_millenium * 2;
	if (date ("n", $sem_start_time) > 6)
		$index++;
	$group=($index % 7) + 1;

	return $group;
}

/**
* The function shortens a string, but it uses the first 2/3 and the last 1/3
*
* The parts will be divided by a "[...]". The functions is to use like php's
* substr function.
* 
* @param		string	the original string
* @param		integer	start pos, 0 is the first pos
* @param		integer	end pos
* @return		string
*
*
*/
function my_substr($what, $start, $end) {
	$length=$end-$start;
	if (strlen($what) > $length) {
		$what=substr($what, $start, (($length / 3) * 2))."[...]".substr($what, strlen($what) -($length / 3), strlen($what)); 
		}
	return $what;
}


/**
* The function determines, if the current user have write perm in a Veranstaltung or Einrichtung
*
* It uses the Variables $SemSecLevelWrite, $SemUserStatus and $rechte, which are created in the
* modul check_sem_entry.inc.php and $perm from PHP-lib
* 
* @return		string	the error msg. If no masg is returned, the user has write permission
*
*/
function have_sem_write_perm () {

global $SemSecLevelWrite, $SemUserStatus, $perm, $rechte;

$error_msg="";
if (!($perm->have_perm("root"))) {
       if (!($rechte || ($SemUserStatus=="autor") || ($SemUserStatus=="tutor") || ($SemUserStatus=="dozent"))) // hier wohl eher kein Semikolon
	   {
		//Auch eigentlich uberfluessig...
		//$error_msg = "<br><b>Sie haben nicht die Berechtigung in dieser Veranstaltung zu schreiben!</b><br><br>";
		switch ($SemSecLevelWrite) {
			case 2 : 
				$error_msg=$error_msg."error�In dieser Veranstaltung ist ein Passwort f&uuml;r den Schreibzugriff n&ouml;tig.<br>Zur <a href=\"sem_verify.php\">Passworteingabe</a>�";
				break;
			case 1 :
				if ($perm->have_perm("autor"))
					$error_msg=$error_msg."info�Sie m�ssen sich erneut f�r diese Veranstaltung anmelden, um schreiben zu k�nnen!<br>Hie kommen sie zur <a href=\"sem_verify.php\">Freischaltung</a> der Veranstaltung.�";
				elseif ($perm->have_perm("user"))
					$error_msg=$error_msg."info�Bitte folgen Sie den Anweisungen in der Registrierungsmail.�";
				else
					$error_msg=$error_msg."info�Bitte melden Sie sich an.<br>Hier geht es zur <a href=\"register1.php\">Registrierung</a> wenn Sie noch keinen Account im System haben.�";
				break;
			default :
				//Wenn Schreiben fuer Nobody jemals wieder komplett verboten werden soll, diesen Teil bitte wieder einkommentieren (man wei&szlig; ja nie...)
				//$error_msg=$error_msg."Bitte melden Sie sich an.<br><br><a href=\"register1.php\"><b>Registrierung</b></a> wenn Sie noch keinen Account im System haben.<br><a href=\"index.php?again=yes\"><b>Login</b></a> f&uuml;r registrierte Benutzer.<br><br>";
				break; 
			}
		$error_msg=$error_msg."info�Dieser Fehler kann auch aufteten, wenn Sie zu lange inaktiv gewesen sind. <br />Wenn sie l&auml;nger als $AUTH_LIFETIME Minuten keine Aktion mehr ausgef&uuml;hrt haben, m&uuml;ssen sie sich neu anmelden.�";
		}
	}
return $error_msg;
}

/**
* The function gives the global perm of an user
*
* It ist recommended to use $auth->auth["perm"] for this query,
* but the function is useful, if you want to query an user_id from another user
* (which ist not the current user)
* 
* @param		string	if omitted, current user_id is used
* @return		string	the perm level or an error msg
*
*/
function get_global_perm($user_id="") {
	 global $auth;

	 if (!$user_id || $user_id == $auth->auth['uid'])
	 	return $auth->auth['perm'];
	
	 $db=new DB_Seminar;
	 $db->query("SELECT perms FROM auth_user_md5 WHERE user_id='$user_id'");
	 if ($db->next_record())
	 	return $db->f("perms");
	 else
	 	return ("Fehler");
}

/**
* Returns permission for given range_id and user_id
*
* Function works for Veranstaltungen, Einrichtunge, Fakultaeten.
* admins get status 'admin' if range_id is a seminar
* 
* @param		string	an id a Veranstaltung, Einrichtung or Fakultaet
* @param		string	if omitted,current user_id is used
* @return		string	the perm level
*
*/
function get_perm($range_id,$user_id="")
{
 /*
 global $user,$auth;
 $status="";
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query("SELECT status FROM seminar_user WHERE user_id='$user_id' AND Seminar_id='$range_id'");
 if ($db->num_rows())
 	{
	$db->next_record();
	$status=$db->f("status");
	}
 else
	{
	$db->query("SELECT inst_perms FROM user_inst WHERE user_id='$user_id' AND Institut_id='$range_id'");
	if ($db->num_rows())
		{
		$db->next_record();
		$status=$db->f("inst_perms");
		}
	else
		{
		$db->query("SELECT status FROM fakultaet_user WHERE user_id='$user_id' AND Fakultaets_id='$range_id'");
		if ($db->num_rows())
			{
			$db->next_record();
			$status=$db->f("status");
			}
	  }
	}
 if ($auth->auth["perm"]=="admin")   // Institutsadmins sind automagisch admins in Seminaren des Institus
	{
	$db->query("SELECT user_inst.Institut_id, seminare.Seminar_id FROM user_inst LEFT JOIN seminare ON (user_inst.Institut_id=seminare.Institut_id AND seminare.Seminar_id='$range_id') WHERE inst_perms='admin' AND user_id='$user_id'");
	if ($db->num_rows())
		{
		// Eintrag gefunden, also ein zum Instadmin geh�riges Seminar
		$status="admin";
		}
	}
 */
 global $perm;
 $status = $perm->get_studip_perm($range_id,$user_id);
 if (!($status)) $status="fehler!";

 return $status;
}


$_fullname_sql['full'] = "TRIM(CONCAT(title_front,' ',Vorname,' ',Nachname,IF(title_rear!='',CONCAT(', ',title_rear),'')))";
$_fullname_sql['full_rev'] = "TRIM(CONCAT(Nachname,', ',Vorname,IF(title_front!='',CONCAT(', ',title_front),''),IF(title_rear!='',CONCAT(', ',title_rear),'')))";
$_fullname_sql['no_title'] = "CONCAT(Vorname ,' ', Nachname)";
$_fullname_sql['no_title_rev'] = "CONCAT(Nachname ,' ', Vorname)";

/**
* Retrieves the fullname for a given user_id
*
* 
* @param		string	if omitted, current user_id is used
* @param		string	output format
* @return		string	
*
*/
function get_fullname($user_id = "", $format = "full" )
{
 global $user,$_fullname_sql;
 $author="unbekannt";
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT " . $_fullname_sql[$format] . " AS fullname FROM auth_user_md5 a LEFT JOIN user_info USING(user_id) WHERE a.user_id = '$user_id'");
	 if ($db->next_record())
			$author = $db->f("fullname");
 return $author;
 }

/**
* Retrieves the fullname for a given username
* 
* @param		string	if omitted, current user_id is used
* @param		string	output format
* @return		string	
*
*/
function get_fullname_from_uname($uname = "", $format = "full")
{
 global $auth,$_fullname_sql;
 $author = "unbekannt";
 if (!$uname) $uname=$auth->auth["uname"];
 $db=new DB_Seminar;
 $db->query ("SELECT " . $_fullname_sql[$format] . " AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE username = '$uname'");
	if ($db->next_record())
		$author = $db->f("fullname");
 return $author;
 }
 
/**
* Retrieves the Vorname for a given user_id
*
* @param		string	if omitted, current user_id is used
* @return		string	
*
*/
 function get_vorname($user_id="")
{
 global $user;
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT Vorname FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("Vorname");
 if ($author=="") $author="unbekannt";

 return $author;
 }

/**
* Retrieves the Nachname for a given user_id
* 
* @param		string	if omitted, current user_id is used
* @return		string	
*
*/
function get_nachname($user_id="")
{
 global $user;
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT Nachname FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("Nachname");
 if ($author=="") $author="unbekannt";

 return $author;
 }

/**
* Retrieves the username for a given user_id
* 
* @param		string	if omitted, current username will be returned
* @return		string	
*
*/
function get_username($user_id="")
{
  global $auth,$online;
 $author="";
 if (!($user_id)) return $auth->auth["uname"];
 if(count($online)) {
 	foreach($online as $key=>$value){
		if ($value["userid"]==$user_id) {
		    $author=$key;
		    break;
			}
		}
	}
if (!$author) {
 $db=new DB_Seminar;
 $db->query ("SELECT username , user_id FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("username");
}
 return $author;
}

/**
* Retrieves the userid for a given username
* 
* @param		string	if omitted, current user_id will be returned
* @return		string	
*
*/
function get_userid($username="")
{
 global $user,$online;
 $author="";
 if (!$username) return $user->id;
 if(count($online)) {
 	$author=$online[$username]["userid"];
	}
if (!$author) {
$db=new DB_Seminar;
 $db->query ("SELECT user_id  FROM auth_user_md5 WHERE username = '$username'");
				 while ($db->next_record())
					 $author=$db->f("user_id");
}
 return $author;
}

/**
* Retrieves the titel for a given studip score
*
* @param		integer	a score value
* @return		string	the titel
*
*/
function gettitel($score)

{
	IF ($score==0) $titel =		"Unbeschriebenes Blatt";
	IF ($score>0) $titel =		"Neuling";
	IF ($score>16) $titel =		"Greenhorn";
	IF ($score>32) $titel =		"Anf&auml;nger";
	IF ($score>64) $titel =		"Einsteiger";
	IF ($score>128) $titel =		"Beginner";
	IF ($score>256) $titel =		"Novize";
	IF ($score>512) $titel =		"Fortgeschrittener";
	IF ($score>1024) $titel =	"Kenner";
	IF ($score>2048) $titel =	"K&ouml;nner";
	IF ($score>4096) $titel =	"Experte";
	IF ($score>8192) $titel =	"Meister";
	IF ($score>16384) $titel =	"Gro&szlig;meister";
	IF ($score>32768) $titel =	"Guru";
	IF ($score>65536) $titel =	"Lichtgestalt";
	IF ($score>131072) $titel =	"Gott";

	return $titel;
}

/**
* Retrieves the score for the current user
*
* uses global $online array if user is online
* 
* @return		integer	the score
*
*/
function getscore()

{ global $user,$auth;

$user_id=$user->id; //damit keiner schummelt...

///// Werte holen...

$db=new DB_Seminar;
$db->query("SELECT count(*) as postings FROM px_topics WHERE user_id = '$user_id' ");
$db->next_record();
$postings=$db->f("postings");

$db->query("SELECT count(*) as dokumente FROM dokumente WHERE user_id = '$user_id' ");
$db->next_record();
$dokumente=$db->f("dokumente");

$db->query("SELECT count(*) as seminare FROM seminar_user WHERE user_id = '$user_id' ");
$db->next_record();
$seminare=$db->f("seminare");

$db->query("SELECT count(*) as archiv FROM archiv_user WHERE user_id = '$user_id' ");
$db->next_record();
$archiv=$db->f("archiv");

$db->query("SELECT count(*) as institut FROM user_inst WHERE user_id = '$user_id' ");
$db->next_record();
$institut=$db->f("institut");

$db->query("SELECT count(*) as news FROM news WHERE user_id = '$user_id' ");
$db->next_record();
$news=$db->f("news");


///////////////////////// Die HOCHGEHEIME Formel:

$score = (5*$postings) + (5*$news) + (20*$dokumente) + (5*$institut) + (5*($archiv+$seminare));
if(file_exists("./user/".$user_id.".jpg")) $score *=10;

/// Schreiben wenn hoeher

	
$query = "UPDATE user_info "
	." SET score = '$score'"
	." WHERE user_id = '$user_id' AND score > 0";
$db->query($query);
	
RETURN $score;
}


/**
* This function tracks user acces to several Data (only dokuments by now, to be extended)
*
*
* @param		string	the id of of the object to track
*
*/
function TrackAccess ($id) {

	switch (get_object_type($id)) { 		// what kind ob object shall we track
		case "dokument": 				// the object is a dokument, so downloads will be increased
			$db=new DB_Seminar;
			$db->query ("SELECT downloads FROM dokumente WHERE dokument_id = '$id'");
			$db->next_record();
			$newcount=$db->f("downloads")+1;
			$db->query ("UPDATE dokumente SET downloads = '$newcount' WHERE dokument_id = '$id'");
			break;
	}
}

?>