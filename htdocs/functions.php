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
* @modulegroup		library
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

require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemTree.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipRangeTree.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/Modules.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/SemesterData.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/HolidayData.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/object.inc.php");

/**
* This function creates the header line for studip-objects
*
* you will get a line like this "Veranstaltung: Name..."
*
* @param		string	the id of the Veranstaltung
* @retunr		string	the header-line
*
*/

function getHeaderLine($id) {
	$object_name = get_object_name($id, get_object_type($id));
	$header_line = $object_name['type'] . ": ". htmlReady(substr($object_name['name'], 0, 60));
	if (strlen($object_name['name']) > 60)
			$header_line.= "... ";
	return $header_line; 
}

function get_object_name($range_id, $object_type){
	
	global $SEM_TYPE,$INST_TYPE, $SEM_TYPE_MISC_NAME;
	
	$db = new DB_Seminar();
	if ($object_type == "sem") {
		$query = sprintf ("SELECT status, Name FROM seminare WHERE Seminar_id = '%s' ", $range_id);
		$db->query($query);
		$db->next_record();
		if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME){
			$type = _("Veranstaltung");
		} else {
			$type = $SEM_TYPE[$db->f("status")]["name"];
		}
		if (!$type){
			$type = _("Veranstaltung");
		}
		$name = $db->f("Name");
	} else if ($object_type == "inst" || $object_type == "fak") {
		$query = sprintf ("SELECT type, Name FROM Institute WHERE Institut_id = '%s' ", $range_id);
		$db->query($query);
		$db->next_record();		
		$type = $INST_TYPE[$db->f("type")]["name"];
		if (!$type){
			$type = _("Einrichtung");
		}
		$name = $db->f("Name");
	}
		
	return array('name' => $name, 'type' => $type);
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
		$SessSemName["art_generic"]= _("Veranstaltung");
		$SessSemName["class"]="sem";
		$SessSemName["art_num"]=$db->f("status");
		if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME)
			$SessSemName["art"] = _("Veranstaltung");
		else
			$SessSemName["art"] = $SEM_TYPE[$db->f("status")]["name"];
		$nr = $db->f("Seminar_id");
		$SessSemName["header_line"] = getHeaderLine ($sem_id);
		object_set_visit($sem_id, "sem");
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
		$SessSemName["art_generic"]= _("Einrichtung");
		$SessSemName["art"]=$INST_TYPE[$db->f("type")]["name"];
		if (!$SessSemName["art"])
			$SessSemName["art"]=$SessSemName["art_generic"];
		$SessSemName["class"] = "inst";
		$SessSemName["is_fak"] = $db->f("is_fak");
		$SessSemName["art_num"]=$db->f("type");
		$SessSemName["fak"] = $db->f("fakultaets_id");
		$SessSemName["header_line"] = getHeaderLine ($inst_id);		
		$nr = $db->f("Institut_id");
		object_set_visit($inst_id, "inst");
	}
}

/**
* This function checks, if there is an open Veranstaltung or Einrichtung
*/
function checkObject() {
	global $SessSemName, $AUTH_LIFETIME;
	if ($SessSemName[1] =="") {
		parse_window ("error�" . _("Sie haben kein Objekt gew&auml;hlt.") . " <br /><font size=-1 color=black>" . _("Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt (Veranstaltung oder Einrichtung) gew&auml;hlt haben.") . "<br /><br /> " . sprintf(_("Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich l�nger als %s Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zur�ck zur Anmeldung zu gelangen."), $AUTH_LIFETIME) . " </font>", "�",
				_("Kein Objekt gew&auml;hlt"), 
				sprintf(_("%sHier%s geht es wieder zur Anmeldung beziehungsweise Startseite."), "<a href=\"index.php\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;");
		die;
	}
}


/**
* This function checks, if given modul is allowed in this stud-ip object
*/
function checkObjectModule($modul) {
	global $SessSemName, $AUTH_LIFETIME;
	
	if ($SessSemName[1]) {
		$Modules=new Modules;
		
		$name = strtoupper($modul{0}).substr($modul, 1, strlen($modul));
		
		if (!$Modules->checkLocal($modul, $SessSemName[1])) {
			parse_window ("error�" . sprintf(_("Das Modul &raquo;%s&laquo; ist f&uuml;r dieses Objekt leider nicht verf&uuml;gbar."), $name), "�",
					_("Modul nicht verf&uuml;gbar"), 
					sprintf(_("%sHier%s geht es wieder zur Anmeldung beziehungsweise Startseite."), "<a href=\"index.php\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;");
			die;
		}
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

	//Folder
	$db->query("SELECT chdate FROM folder WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//Dokuments
	$db->query("SELECT chdate FROM dokumente WHERE seminar_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//SCM
	$db->query("SELECT chdate FROM scm WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//Dates
	$db->query("SELECT chdate FROM termine WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
		$timestamp = $db->f("chdate");

	//News
	$db->query("SELECT date FROM news_range LEFT JOIN news USING (news_id)  WHERE range_id = '$sem_id' ORDER BY date DESC LIMIT 1");
	$db->next_record();
	if ($db->f("date") > $timestamp)
		$timestamp = $db->f("date");
	
	//Literature
	$db->query("SELECT MAX(chdate) as chdate FROM lit_list WHERE range_id='$sem_id' GROUP BY range_id");
	$db->next_record();
	if ($db->f("chdate") > $timestamp)
			$timestamp = $db->f("chdate");
	
	//Votes
	if ($GLOBALS['VOTE_ENABLE']) {
		$db->query("SELECT chdate FROM vote WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
		$db->next_record();
		if ($db->f("chdate") > $timestamp)
			$timestamp = $db->f("chdate");
	}

	//Wiki
	if ($GLOBALS['WIKI_ENABLE']) {
		$db->query("SELECT chdate FROM wiki WHERE range_id = '$sem_id' ORDER BY chdate DESC LIMIT 1");
		$db->next_record();
		if ($db->f("chdate") > $timestamp)
			$timestamp = $db->f("chdate");
	}

	//correct the timestamp, if date in the future (news can be in the future!)
	if ($timestamp > time())
		$timestamp = time();
		
	return $timestamp;
}


/**
* This function determines, from which type an id is from.
*
* The function recognizes the following types at this moment:
* Einrichtungen, Veranstaltungen, Statusgruppen and Fakultaeten
* 
* @param		string	id	the id of the object
* @return		string	return "ins" (Einrichtung), "sem" (Veranstaltung), "fak" (Fakultaeten), "group" (Statusgruppe), "dokument" (Dateien)
*
*/
function get_object_type($id) {
	static $object_type_cache;
	if ($id){
		if (!$object_type_cache[$id]){
			$db=new DB_Seminar;
			$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id = '$id' ");
			if ($db->next_record())
				return $object_type_cache[$id] = "sem";
	
			$db->query("SELECT Institut_id,IF(Institut_id=fakultaets_id,1,0) AS is_fak FROM Institute WHERE Institut_id = '$id' ");
			if ($db->next_record())
				return $object_type_cache[$id] = ($db->f("is_fak")) ? "fak" : "inst";
		
			$db->query("SELECT termin_id FROM termine WHERE termin_id = '$id' ");
			if ($db->next_record())
				return $object_type_cache[$id] = "date";
			
			$db->query("SELECT user_id FROM auth_user_md5 WHERE user_id = '$id' ");
			if ($db->next_record())
				return $object_type_cache[$id] = "user";
			
			$db->query("SELECT statusgruppe_id FROM statusgruppen WHERE statusgruppe_id = '$id' ");
			if ($db->next_record())
				return $object_type_cache[$id] = "group";
		
			$db->query("SELECT dokument_id FROM dokumente WHERE dokument_id = '$id' ");
			if ($db->next_record())	
				return $object_type_cache[$id] = "dokument";
		
			$db->query("SELECT item_id FROM range_tree WHERE item_id = '$id' ");
			if ($db->next_record())
				return $object_type_cache[$id] = "range_tree";
		} else {
			return $object_type_cache[$id];
		}
	}
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
* @return		string	the error msg. If no msg is returned, the user has write permission
*
*/
function have_sem_write_perm () {

global $SemSecLevelWrite, $SemUserStatus, $perm, $rechte;

$error_msg="";
if (!($perm->have_perm("root"))) {
	if (!($rechte || ($SemUserStatus=="autor") || ($SemUserStatus=="tutor") || ($SemUserStatus=="dozent"))) {
		//Auch eigentlich uberfluessig...
		//$error_msg = "<br><b>Sie haben nicht die Berechtigung in dieser Veranstaltung zu schreiben!</b><br><br>";
		switch ($SemSecLevelWrite) {
			case 2 : 
				$error_msg=$error_msg."error�" . _("In dieser Veranstaltung ist ein Passwort f&uuml;r den Schreibzugriff n&ouml;tig.") . "<br>" . sprintf(_("Zur %sPassworteingabe%s"), "<a href=\"sem_verify.php\">", "</a>") . "�";
				break;
			case 1 :
				if ($perm->have_perm("autor"))
					$error_msg=$error_msg."info�" . _("Sie m�ssen sich erneut f�r diese Veranstaltung anmelden, um Dateien hochzuladen und Beitr&auml;ge im Forum schreiben zu k�nnen!") . "<br>" . sprintf(_("Hier kommen sie zur %sFreischaltung%s der Veranstaltung."), "<a href=\"sem_verify.php\">", "</a>") . "�";
				elseif ($perm->have_perm("user"))
					$error_msg=$error_msg."info�" . _("Bitte folgen Sie den Anweisungen in der Registrierungsmail.") . "�";
				else
					$error_msg=$error_msg."info�" . _("Bitte melden Sie sich an.") . "<br>" . sprintf(_("Hier geht es zur %sRegistrierung%s wenn Sie noch keinen Account im System haben."), "<a href=\"register1.php\">", "</a>") . "�";
				break;
			default :
				//Wenn Schreiben fuer Nobody jemals wieder komplett verboten werden soll, diesen Teil bitte wieder einkommentieren (man wei&szlig; ja nie...)
				//$error_msg=$error_msg."Bitte melden Sie sich an.<br><br><a href=\"register1.php\"><b>Registrierung</b></a> wenn Sie noch keinen Account im System haben.<br><a href=\"index.php?again=yes\"><b>Login</b></a> f&uuml;r registrierte Benutzer.<br><br>";
				break; 
			}
		$error_msg=$error_msg."info�" . _("Dieser Fehler kann auch auftreten, wenn Sie zu lange inaktiv gewesen sind.") . " <br />" . sprintf(_("Wenn sie l&auml;nger als %s Minuten keine Aktion mehr ausgef&uuml;hrt haben, m&uuml;ssen sie sich neu anmelden."), $AUTH_LIFETIME) . "�";
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
* @deprecated	use $GLOBALS['perm']->get_perm($user_id)
* @param		string	if omitted, current user_id is used
* @return		string	the perm level or an error msg
*
*/
function get_global_perm($user_id="") {
	global $perm;
	$status = $perm->get_perm($user_id);
	return (!$status) ? _("Fehler!") : $status;
}

/**
* Returns permission for given range_id and user_id
*
* Function works for Veranstaltungen, Einrichtungen, Fakultaeten.
* admins get status 'admin' if range_id is a seminar
* 
* @deprecated	use $GLOBALS['perm']->get_studip_perm($range_id, $user_id)
* @param		string	an id a Veranstaltung, Einrichtung or Fakultaet
* @param		string	if omitted,current user_id is used
* @return		string	the perm level
*
*/
function get_perm($range_id,$user_id="") {
	global $perm;
	$status = $perm->get_studip_perm($range_id,$user_id);
	return (!$status) ? _("Fehler!") : $status;
}


/**
* Retrieves the fullname for a given user_id
*
* 
* @param		string	if omitted, current user_id is used
* @param		string	output format
* @return		string	
*
*/
function get_fullname($user_id = "", $format = "full" ){
	static $cache;
	global $user,$_fullname_sql;
	$author = _("unbekannt");
	if (!($user_id)) $user_id=$user->id;
	if (isset($cache[md5($user_id . $format)])){
		return $cache[md5($user_id . $format)];
	} else {
		$db=new DB_Seminar;
		$db->query ("SELECT " . $_fullname_sql[$format] . " AS fullname FROM auth_user_md5 a LEFT JOIN user_info USING(user_id) WHERE a.user_id = '$user_id'");
		if ($db->next_record()){
			$author = $db->f("fullname");
		}
		return ($cache[md5($user_id . $format)] = $author);
	}
 }

/**
* Retrieves the fullname for a given username
* 
* @param		string	if omitted, current user_id is used
* @param		string	output format
* @return		string	
*
*/
function get_fullname_from_uname($uname = "", $format = "full"){
	static $cache;
	global $auth,$_fullname_sql;
	$author = _("unbekannt");
	if (!$uname) $uname=$auth->auth["uname"];
	if (isset($cache[md5($uname . $format)])){
		return $cache[md5($uname . $format)];
	} else {
		$db=new DB_Seminar;
		$db->query ("SELECT " . $_fullname_sql[$format] . " AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE username = '$uname'");
		if ($db->next_record()){
			$author = $db->f("fullname");
		}
		return ($cache[md5($uname . $format)] = $author);
	}
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
 if ($author=="") $author= _("unbekannt");

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
 if ($author=="") $author= _("unbekannt");

 return $author;
 }

/**
* Retrieves the username for a given user_id
*
* 
* @param		string	if omitted, current username will be returned
* @return		string	
*
*/
function get_username($user_id="") {
	static $cache;
	global $auth;
	$author = "";
	if (!$user_id || $user_id == $auth->auth['uid'])
		return $auth->auth["uname"];
	if (isset($cache[$user_id])){
		return $cache[$user_id];
	} else {
		$db=new DB_Seminar;
		$db->query ("SELECT username , user_id FROM auth_user_md5 WHERE user_id = '$user_id'");
		while ($db->next_record()){
			$author = $db->f("username");
		}
		return ($cache[$user_id] = $author);
	}
}

/**
* Retrieves the userid for a given username
*
* uses global $online array if user is online
* 
* @param		string	if omitted, current user_id will be returned
* @return		string	
*
*/
function get_userid($username="") {
	static $cache;
	global $auth;
	$author = "";
	if (!$username || $username == $auth->auth['uname'])
		return $auth->auth['uid'];
	if (isset($cache[$username])){
		return $cache[$username];
	} else {
		$db=new DB_Seminar;
		$db->query ("SELECT user_id  FROM auth_user_md5 WHERE username = '$username'");
		while ($db->next_record()){
			$author=$db->f("user_id");
		}
		return ($cache[$username] = $author);
	}
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
			$db->query ("UPDATE dokumente SET downloads = downloads + 1 WHERE dokument_id = '$id'");
			break;
	}
}


function get_sem_tree_path($seminar_id, $depth = false, $delimeter = ">"){
	$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
	$view = new DbView();
	$ret = null;
	$view->params[0] = $seminar_id;
	$rs = $view->get_query("view:SEMINAR_SEM_TREE_GET_IDS");
	while ($rs->next_record()){
		$ret[$rs->f('sem_tree_id')] = $the_tree->getShortPath($rs->f('sem_tree_id'),$depth,$delimeter);
	}
	return $ret;
}

function get_range_tree_path($institut_id, $depth = false, $delimeter = ">"){
	$the_tree =& TreeAbstract::GetInstance("StudipRangeTree");
	$view = new DbView();
	$ret = null;
	$view->params[0] = $institut_id;
	$rs = $view->get_query("view:TREE_ITEMS_OBJECT");
	while ($rs->next_record()){
		$ret[$rs->f('item_id')] = $the_tree->getShortPath($rs->f('item_id'),$depth,$delimeter);
	}
	return $ret;
}


/**
 * check_and_set_date
 *
 * Checks if given date is valid and sets field in array accordingly.
 * (E.g. $admin_admission_data['admission_enddate'])
 *
 * @param	mixed	day or placeholder for day
 * @param	mixed	month or placeholder for month
 * @param	mixed	year or placeholder for year
 * @param	mixed	hours or placeholder for hours
 * @param	mixed	minutes or placeholder for minutes
 * @param	array	Reference to array to update. If NULL, only check is performed
 * @param	mixed	Name of field in array to be set
 *
 * @return	bool	true if date was valid, false else
 *
 **/
function check_and_set_date($tag, $monat, $jahr, $stunde, $minute, &$arr, $field) {

	$check=TRUE; // everything ok?
	if (($jahr>0) && ($jahr<100))
		$jahr=$jahr+2000;

	if ($monat == _("mm")) $monat=0;
	if ($tag == _("tt")) $tag=0;
	if ($jahr == _("jjjj")) $jahr=0;	
	if ($stunde == _("ss")) $stunde=0;
	if ($minute == _("mm")) $minute=0;

	if (($monat) && ($tag) && ($jahr)) {
		if ($stunde==_("hh")) {
			$check=FALSE;
		} 

		if ((!checkdate((int)$monat, (int)$tag, (int)$jahr) && ((int)$monat) && ((int)$tag) && ((int)$jahr))) {
			$check=FALSE;
		}

		if (($stunde > 24) || ($minute > 59)) {
			$check=FALSE;			
		}

		if ($arr) {
			if ($check) {
				$arr[$field] = mktime($stunde,$minute,59,$monat,$tag,$jahr);
			} else {
				$arr[$field] = -1;
			}
		}
	}
	return $check;
}

/**
 * write_config
 *
 * writes an entry into the studip configuration table
 *
 * @param	string	the key for the config entry
 * @param	string	the value that should be set
 * @param	array	an array with key=>value to write into config
 *
 * @return	bool	true if date was valid, else false
 *
 **/
function write_config ($key='', $val='', $arr='') {
	$db = new DB_Seminar;
	
	if (func_num_args() == 2) {
		$arr[$key] = $val;
	}
	if (is_array($arr)) {
		foreach ($arr as $key=>$val) {
			$GLOBALS[$key] = $val;
			$query = sprintf ("SELECT * FROM config WHERE `key` = '%s' ", $key);
			$db->query($query);
		
			if ($db->nf()) {
				$query = sprintf ("UPDATE config SET `key` = '%s', value = '%s', chdate = '%s' WHERE `key` = '%s' ", $key, $val, time(), $key);
			} else {
				$query = sprintf ("INSERT INTO config SET config_id = '%s', `key` = '%s', value = '%s', chdate = '%s'", md5(uniqid("configID")), $key, $val, time());
			}
			$db->query($query);
		}
		return TRUE;
	} else
		return FALSE;
}

/**
 * get_config
 *
 * gets an entry from the studip configuration table
 *
 * @param	string	the key for the config entry
 * @param	boolean	if set, the default value will we returned
 *
 * @return	sttring	the value
 *
 **/
function get_config ($key, $default = FALSE) {
	$db = new DB_Seminar;
	
	$query = sprintf ("SELECT value, default_value FROM config WHERE `key` = '%s' ", $key);
	$db->query($query);
	if ($db->next_record()) {
		if ($default)
			return $db->f("default_value");
		else
			return $db->f("value");
	} else
		return FALSE;
}

// folgende Funktion ist nur notwendig, wenn die zu kopierende Veranstaltung nicht vom Dozenten selbst,
// sondern vom Admin oder vom root kopiert wird (sonst wird das Dozentenfeld leer gelassen, was ja keiner will...)
function get_seminar_dozent($seminar_id) {
	$db = new DB_Seminar;
	$sql = "SELECT user_id FROM seminar_user WHERE Seminar_id='".$seminar_id."' AND status='dozent'";
	if (!$db->query($sql)) {
		echo "Fehler bei DB-Abfrage in get_seminar_user!";
		return 0;
	}
	if (!$db->num_rows()) {
		echo "Fehler in get_seminar_dozent: Kein Dozent gefunden";
		return 0;
	}
	while ($db->next_record()) {
		$dozent[$db->f("user_id")] = TRUE;
	}
	return $dozent;
}

function get_seminar_tutor($seminar_id) {
	$db = new DB_Seminar;
	$sql = "SELECT user_id FROM seminar_user WHERE Seminar_id='".$seminar_id."' AND status='tutor'";
	if (!$db->query($sql)) {
		echo "Fehler bei DB-Abfrage in get_seminar_user!";
		return 0;
	}
	if (!$db->num_rows()) {
		return null;
	}
	while ($db->next_record()) {
		$tutor[$db->f("user_id")] = TRUE;
	}
	return $tutor;
}

function get_seminar_sem_tree_entries($seminar_id) {
	// get sem_tree_entries for copy 
	/*
	$db = new DB_Seminar;
	$sql = "SELECT sem_tree_id FROM seminar_sem_tree WHERE seminar_id='".$seminar_id."'";
	if (!$db->query($sql)) {
		return 0;
	}
	$i=0;
	while ($db->next_record()) {
		$sem_tree[$i] = $db->f("sem_tree_id");
		$i++;
	}
	$i=0;
	// check whether entries exist in sem_tree
	// we do not need to copy non-existent entries
	for ($j=0;$j<count($sem_tree);$j++) {
		$sql = "SELECT * FROM sem_tree WHERE sem_tree_id='".$sem_tree[$j]."'";
		if (!$db->query($sql)) {
			echo "FEHLER beim Holen der sem_tree";
		}
		if ($db->num_rows()) {
			$sem_tree_final[$i] = $sem_tree[$j];
			$i++;
		}
	}
	return $sem_tree_final;
	*/
	$view = new DbView();
	$ret = null;
	$view->params[0] = $seminar_id;
	$rs = $view->get_query("view:SEMINAR_SEM_TREE_GET_IDS");
	while ($rs->next_record()){
		$ret[] = $rs->f('sem_tree_id');
	}
	return $ret;
}


function get_seminars_user($user_id) {
	$db = new DB_Seminar;
	$sql = 	"SELECT seminare.name, seminare.Seminar_id, seminare.mkdate, seminare.VeranstaltungsNummer as va_nummer ".
			"FROM seminare ".
			"LEFT JOIN seminar_user ON seminare.Seminar_id=seminar_user.Seminar_id ".
			"WHERE user_id = '".$user_id."'";
	$db->query($sql);
	
	$seminars = array();
	$i = 0;
	
	while ($db->next_record()) {
		$i++;
		$seminars[$i]["name"] = $db->f("name");
		$seminars[$i]["id"] = $db->f("Seminar_id");
		$seminars[$i]["mkdate"] = $db->f("mkdate");
		$seminars[$i]["va_nummer"] = $db->f("va_nummer");
	}	
	return $seminars;
}

?>
