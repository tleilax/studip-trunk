<?php
# Lifter002: TODO
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

require_once ('lib/classes/StudipSemTree.class.php');
require_once ('lib/classes/StudipRangeTree.class.php');
require_once ('lib/classes/Modules.class.php');
require_once ('lib/classes/SemesterData.class.php');
require_once ('lib/classes/HolidayData.class.php');
require_once ('lib/visual.inc.php');
require_once ('lib/object.inc.php');
require_once ('lib/user_visible.inc.php');
require_once ('lib/exceptions/access_denied.php');

/**
* This function creates the header line for studip-objects
*
* you will get a line like this "Veranstaltung: Name..."
*
* @param		string	the id of the Veranstaltung
* @return		string	the header-line
*
*/

function getHeaderLine($id, $object_name = null) {
	if(!$object_name){
		$object_name = get_object_name($id, get_object_type($id));
	}
	$header_line = $object_name['type'];
	if ($object_name['name'])
		$header_line.=": ";
	$header_line.= substr($object_name['name'], 0, 60);
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
* This function "selects" a Veranstaltung to work with it
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
*
* @param		string	the id of the Veranstaltung
* @return		boolean	true if successful
*
*/
function selectSem ($sem_id) {
	global $perm, $SEM_TYPE, $SEM_TYPE_MISC_NAME, $SessionSeminar, $SessSemName, $SemSecLevelRead, $SemSecLevelWrite, $SemUserStatus, $rechte;

	$db = DBManager::get();

	closeObject();

	$st = $db->prepare("SELECT Institut_id, Name, Seminar_id, Untertitel, start_time, status, Lesezugriff, Schreibzugriff, Passwort FROM seminare WHERE Seminar_id = ?");
	$st->execute(array($sem_id));
	if ($row = $st->fetch()) {
		$SemSecLevelRead = $row["Lesezugriff"];
		$SemSecLevelWrite = $row["Schreibzugriff"];
		$rechte = $perm->have_studip_perm("tutor", $row["Seminar_id"]);
		if( !($SemUserStatus = $perm->get_studip_perm($row["Seminar_id"])) ){
			$SemUserStatus = "nobody";
			if ($SemSecLevelRead > 0){
				throw new Studip_AccessDeniedException(_("Keine Berechtigung."));
			}
		}
		$SessionSeminar = $row["Seminar_id"];
		$SessSemName[0] = $row["Name"];
		$SessSemName[1] = $row["Seminar_id"];
		$SessSemName[3] = $row["Untertitel"];
		$SessSemName[4] = $row["start_time"];
		$SessSemName[5] = $row["Institut_id"];
		$SessSemName["art_generic"] = _("Veranstaltung");
		$SessSemName["class"] = "sem";
		$SessSemName["art_num"] = $row["status"];
		if ($SEM_TYPE[$row["status"]]["name"] == $SEM_TYPE_MISC_NAME) {
			$SessSemName["art"] = _("Veranstaltung");
		} else {
			$SessSemName["art"] = $SEM_TYPE[$row["status"]]["name"];
		}
		$SessSemName["header_line"] = getHeaderLine ($sem_id, array('name' => $row["Name"], 'type' => $SessSemName["art"]));
		return true;
	} else {
		return false;
	}
}

/**
* This function "selects" an Einrichtung to work with it
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
*
* @param		string	the id of the Veranstaltung
* @return		boolean	true if successful
*
*/
function selectInst ($inst_id) {
	global $SessionSeminar, $SessSemName, $INST_TYPE, $SemUserStatus, $rechte, $perm;

	$db = DBManager::get();

	closeObject();

	$st = $db->prepare("SELECT Name, Institut_id, type,fakultaets_id, IF(Institut_id=fakultaets_id,1,0) AS is_fak FROM Institute WHERE Institut_id = ?");
	$st->execute(array($inst_id));
	if ($row = $st->fetch()) {
		if ( !($SemUserStatus = $perm->get_studip_perm($row["Institut_id"])) ) {
			$SemUserStatus = 'nobody';
		}
		$rechte = $perm->have_studip_perm("tutor", $row["Institut_id"]);
		$SessionSeminar = $row["Institut_id"];
		$SessSemName[0] = $row["Name"];
		$SessSemName[1] = $row["Institut_id"];
		$SessSemName["art_generic"] = _("Einrichtung");
		$SessSemName["art"] = $INST_TYPE[$row["type"]]["name"];
		if (!$SessSemName["art"]) {
			$SessSemName["art"] = $SessSemName["art_generic"];
		}
		$SessSemName["class"] = "inst";
		$SessSemName["is_fak"] = $row["is_fak"];
		$SessSemName["art_num"] = $row["type"];
		$SessSemName["fak"] = $row["fakultaets_id"];
		$SessSemName["header_line"] = getHeaderLine ($inst_id, array('name' => $row["Name"], 'type' => $SessSemName["art"]));
		return true;
	} else {
		return false;
	}
}

/**
 * This function "opens" a course to work with it. Does the same
 * as selectSem() but also sets the visit date.
 *
 * @param		string	the id of the course
 * @return		boolean	true if successful
 */
function openSem ($sem_id) {
	if (($result = selectSem($sem_id))) {
		object_set_visit($sem_id, "sem");
	}

	return $result;
}

/**
 * This function "opens" an institute to work with it. Does the same
 * as selectInst() but also sets the visit date.
 *
 * @param		string	the id of the institute
 * @return		boolean	true if successful
 */
function openInst ($inst_id) {
	if (($result = selectInst($inst_id))) {
		object_set_visit($inst_id, "inst");
	}

	return $result;
}

/**
* This function checks, if there is an open Veranstaltung or Einrichtung
*/
function checkObject() {
	global $SessSemName, $AUTH_LIFETIME;
	if ($SessSemName[1] =="") {
		$last_edited = stripslashes(trim($_REQUEST['content'] . $_REQUEST['description'] . $_REQUEST['body']));
		parse_window("error�"
		. _("Sie haben kein Objekt gew&auml;hlt.")
		. " <br /><font size=-1 color=black>"
		. _("Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt (Veranstaltung oder Einrichtung) gew&auml;hlt haben.")
		. "<br /><br /> "
		. sprintf(_("Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich l�nger als %s Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zur�ck zur Anmeldung zu gelangen."), $AUTH_LIFETIME)
		. ($last_edited ? '<br><br>'._("Folgender von ihnen eingegebener Text konnte nicht gespeichert werden:") . '<div class="steel1" style="margin-top:5px;padding:5px;border:1px solid">'.htmlReady($last_edited).'</div>' : "")
		. " </font>",
		"�",
		_("Kein Objekt gew&auml;hlt"),
		sprintf(_("%sHier%s geht es wieder zur Anmeldung beziehungsweise Startseite.")
				, "<a href=\"index.php\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;");
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
	global $SessionSeminar, $SessSemName, $SemSecLevelRead, $SemSecLevelWrite, $SemUserStatus, $rechte, $sess;

	$SessionSeminar = '';
	$SessSemName = array();
	$SemSecLevelRead = null;
	$SemSecLevelWrite = null;
	$SemUserStatus = null;
	$rechte = false;

	$sess->unregister('raumzeitFilter');
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
* @return		string	return "inst" (Einrichtung), "sem" (Veranstaltung), "fak" (Fakultaeten), "group" (Statusgruppe), "dokument" (Dateien)
*
*/
function get_object_type($id) {
	static $object_type_cache;
	if ($id){
		if (!$object_type_cache[$id]){
			if ($id == 'studip'){
				return 'global';
			}
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

global $SemSecLevelWrite, $SemUserStatus, $perm, $rechte, $AUTH_LIFETIME;

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
function get_fullname($user_id = "", $format = "full" , $htmlready = false){
	static $cache;
	global $user,$_fullname_sql;
	$author = _("unbekannt");
	if (!($user_id)) $user_id=$user->id;
	if (isset($cache[md5($user_id . $format)])){
		return ($htmlready ? htmlReady($cache[md5($user_id . $format)]) : $cache[md5($user_id . $format)]);
	} else {
		$db=new DB_Seminar;
		$db->query ("SELECT " . $_fullname_sql[$format] . " AS fullname FROM auth_user_md5 a LEFT JOIN user_info USING(user_id) WHERE a.user_id = '$user_id'");
		if ($db->next_record()){
			$author = $db->f('fullname');
		}
		$cache[md5($user_id . $format)] = $author;
		return ($htmlready ? htmlReady($cache[md5($user_id . $format)]) : $cache[md5($user_id . $format)]);
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
function get_fullname_from_uname($uname = "", $format = "full", $htmlready = false){
	static $cache;
	global $auth,$_fullname_sql;
	$author = _("unbekannt");
	if (!$uname) $uname=$auth->auth["uname"];
	if (isset($cache[md5($uname . $format)])){
		return ($htmlready ? htmlReady($cache[md5($uname . $format)]) : $cache[md5($uname . $format)]);
	} else {
		$db=new DB_Seminar;
		$db->query ("SELECT " . $_fullname_sql[$format] . " AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE username = '$uname'");
		if ($db->next_record()){
			$author = $db->f('fullname');
		}
		$cache[md5($uname . $format)] = $author;
		return ($htmlready ? htmlReady($cache[md5($uname . $format)]) : $cache[md5($uname . $format)]);
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
function TrackAccess ($id, $object_type = null) {
	if (!$object_type){
		$object_type = get_object_type($id);
	}
	switch ($object_type) { 		// what kind ob object shall we track
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
	//if ($stunde == _("hh")) $stunde=0;
	if ($minute == _("mm")) $minute=0;

	if (($monat) && ($tag) && ($jahr)) {
		if ($stunde==_("hh")) {
			$check=FALSE;
		}

		if ((!checkdate((int)$monat, (int)$tag, (int)$jahr) && ((int)$monat) && ((int)$tag) && ((int)$jahr))) {
			$check=FALSE;
		}

		if (($stunde > 24) || ($minute > 59)
			|| ($stunde == 24 && $minute > 0) ) {
			$check=FALSE;
		}

		if ($stunde == 24) {
			$stunde = 23;
			$minute = 59;
		}

		if ($arr) {
			if ($check) {
				$arr[$field] = mktime((int)$stunde,(int)$minute, 0,$monat,$tag,$jahr);
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
			$query = sprintf ("SELECT * FROM config WHERE `field` = '%s' AND `is_default` != '1' ", $key);
			$db->query($query);

			if ($db->nf()) {
				$query = sprintf ("UPDATE config SET `field` = '%s', value = '%s', chdate = '%s' WHERE `field` = '%s' AND `is_default` != '1' ", $key, $val, time(), $key);
			} else {
				$query = sprintf ("INSERT INTO config SET config_id = '%s', `field` = '%s', value = '%s', chdate = '%s'", md5(uniqid("configID")), $key, $val, time());
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
 * @return	string	the value
 *
 **/
function get_config($key, $default = FALSE) {
	if (isset($GLOBALS[$key]) && !isset($_REQUEST[$key]) && !$default){
		return $GLOBALS[$key];
	} else {
		$cfg =& Config::GetInstance();
		return ($default ? $cfg->getDefault($key) : $cfg->getValue($key));
	}
}

// folgende Funktion ist nur notwendig, wenn die zu kopierende Veranstaltung nicht vom Dozenten selbst,
// sondern vom Admin oder vom root kopiert wird (sonst wird das Dozentenfeld leer gelassen, was ja keiner will...)
function get_seminar_dozent($seminar_id) {
	$db = new DB_Seminar;
	$sql = "SELECT user_id, position FROM seminar_user WHERE Seminar_id='".$seminar_id."' AND status='dozent' ORDER BY position";
	if (!$db->query($sql)) {
		echo "Fehler bei DB-Abfrage in get_seminar_user!";
		return 0;
	}
	if (!$db->num_rows()) {
		echo "Fehler in get_seminar_dozent: Kein Dozent gefunden";
		return 0;
	}
	while ($db->next_record()) {
		$dozent[$db->f('user_id')] = $db->f('position');
	}
	return $dozent;
}
/*
function get_seminar_next_position($seminar_id)
{
   $db_pos = new DB_Seminar;
   $db_pos->query("SELECT max(position) + 1 as next_pos " .
                  " FROM seminar_user" .
                  " WHERE status = 'dozent' AND Seminar_id = '$seminar_id'");

   $db_pos->next_record();

   return $db_pos->f("next_pos");

}*/
function re_sort_dozenten($s_id, $position)
{
    $db = new DB_Seminar;
    $db->query("SELECT user_id, position" .
               " FROM seminar_user" .
               " WHERE Seminar_id = '$s_id'" .
               " AND status = 'dozent' " .
               " AND position > '$position'");

   while ($db->next_record())
   {
      $new_position = $db->f("position") - 1;
      $user_id = $db->f("user_id");
      $db2 = new DB_Seminar;
      $db2->query("UPDATE seminar_user" .
                  " SET position =  '$new_position'" .
                  " WHERE Seminar_id = '$s_id'" .
                  " AND user_id = '$user_id'");
   }
}
function re_sort_tutoren($s_id, $position)
{
    $db = new DB_Seminar;
    $db->query("SELECT user_id, position" .
               " FROM seminar_user" .
               " WHERE Seminar_id = '$s_id'" .
               " AND status = 'tutor' " .
               " AND position > '$position'");

   while ($db->next_record())
   {
      $new_position = $db->f("position") - 1;
      $user_id = $db->f("user_id");
      $db2 = new DB_Seminar;
      $db2->query("UPDATE seminar_user" .
                  " SET position =  '$new_position'" .
                  " WHERE Seminar_id = '$s_id'" .
                  " AND user_id = '$user_id'");
   }
}
function get_next_position($status,$seminar_id)
{
   $db_pos = new DB_Seminar;
   $db_pos->query("SELECT max(position) + 1 as next_pos " .
                  " FROM seminar_user" .
                  " WHERE status = '$status' " .
                  " AND   Seminar_id = '$seminar_id'");

   $db_pos->next_record();

   return $db_pos->f("next_pos");
}

function get_seminar_tutor($seminar_id) {
	$db = new DB_Seminar;
	$sql = "SELECT user_id, position FROM seminar_user WHERE Seminar_id='".$seminar_id."' AND status='tutor' ORDER BY position";
	if (!$db->query($sql)) {
		echo "Fehler bei DB-Abfrage in get_seminar_user!";
		return 0;
	}
	if (!$db->num_rows()) {
		return null;
	}
	while ($db->next_record()) {
		$tutor[$db->f('user_id')] = $db->f('position');
	}
	return $tutor;
}

function get_seminar_sem_tree_entries($seminar_id) {

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

/**
* converts a string to a float, depending on the locale
*
* @param		string
* @return		float
*
*/

function StringToFloat($str){
	$str = substr((string)$str,0,13);
	$locale = localeconv();
	$from = ($locale["thousands_sep"] ? $locale["thousands_sep"] : ',');
	$to = ($locale["decimal_point"] ? $locale["decimal_point"] : '.');
	if(strstr($str, $from)){
		$conv_str = str_replace($from, $to, $str);
		$my_float = (float)$conv_str;
		if ($conv_str === (string)$my_float) return $my_float;
	}
	return (float)$str;
}

function archiv_check_perm($seminar_id){
	static $archiv_perms;
	global $perm,$auth;
	$u_id = $auth->auth['uid'];
	$db = new DB_Seminar();
	if ($perm->have_perm("root")){  // root darf sowieso ueberall dran
		return "admin";
	}
	if (!is_array($archiv_perms)){
		$db->query("SELECT seminar_id,status FROM archiv_user WHERE user_id = '$u_id'");
		while ($db->next_record()) {
			$archiv_perms[$db->f("seminar_id")] = $db->f("status");
		}
		if ($perm->have_perm("admin")){
			$db->query("SELECT archiv.seminar_id FROM user_inst INNER JOIN archiv ON (heimat_inst_id = institut_id) WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin'");
			while ($db->next_record()) {
				$archiv_perms[$db->f("seminar_id")] = "admin";
			}
		}
		if ($perm->is_fak_admin()){
			$db->query("SELECT archiv.seminar_id FROM user_inst INNER JOIN Institute ON ( user_inst.institut_id = Institute.fakultaets_id ) INNER JOIN archiv ON ( archiv.heimat_inst_id = Institute.institut_id )  WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin'");
			while($db->next_record()){
				$archiv_perms[$db->f("seminar_id")] = "admin";
			}
		}
	}
	return $archiv_perms[$seminar_id];
}

function get_users_online($active_time = 5, $name_format = 'full_rev'){
	global $user, $_fullname_sql;
	$online = null;
	if (!isset($_fullname_sql[$name_format])) {
		reset($_fullname_sql);
		$name_format = key($_fullname_sql);
	}
	$now = time(); // nach eingestellter Zeit (default = 5 Minuten ohne Aktion) zaehlt man als offline
	$query = "SELECT a.username," . $_fullname_sql[$name_format] . " AS name,UNIX_TIMESTAMP()-UNIX_TIMESTAMP(changed) AS last_action, a.user_id as userid, contact_id as is_buddy, " . get_vis_query('a') . " AS is_visible
		FROM " . PHPLIB_USERDATA_TABLE . " LEFT JOIN auth_user_md5 a ON (a.user_id=sid) LEFT JOIN user_info USING(user_id)
		LEFT JOIN contact ON (owner_id='".$user->id."' AND contact.user_id=a.user_id AND buddy=1)
		WHERE changed > '" . date("YmdHis", ($now - ($active_time * 60)))."'
		AND sid != '".$user->id."' AND sid !='nobody'
		ORDER BY changed DESC";
	$rs = DBManager::get()->query($query);
	$online = $rs->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
	return array_map('array_shift', $online);
}

function get_users_online_count($active_time = 5){
	return 	DBManager::get()
			->query("SELECT COUNT(*) FROM " . PHPLIB_USERDATA_TABLE . " WHERE
					changed > '".date("YmdHis", (time() - ($active_time * 60))) . "' AND sid NOT IN
					('nobody', '".$GLOBALS['user']->id."')")
			->fetchColumn();
}

function get_ticket(){
	return Seminar_Session::get_ticket();
}

function check_ticket($studipticket){
	return Seminar_Session::check_ticket($studipticket);
}

function search_range($search_str = false, $search_user = false, $show_sem = true) {

	global $perm, $user, $_fullname_sql;

	$db = new DB_Seminar();

	$search_result = null;
	$show_sem_sql1 = ",sd1.name AS startsem,IF(s.duration_time=-1, '"._("unbegrenzt")."', sd2.name) AS endsem ";
	$show_sem_sql2 = "LEFT JOIN semester_data sd1 ON ( start_time BETWEEN sd1.beginn AND sd1.ende)
					LEFT JOIN semester_data sd2 ON ((start_time + duration_time) BETWEEN sd2.beginn AND sd2.ende)";


	if ($search_str && $perm->have_perm("root")) {

		if ($search_user){
			$query = "SELECT a.user_id,". $_fullname_sql['full'] . " AS full_name,username FROM auth_user_md5 a LEFT JOIN user_info USING(user_id) WHERE CONCAT(Vorname,' ',Nachname,' ',username) LIKE '%$search_str%' ORDER BY Nachname, Vorname";
			$db->query($query);
			while($db->next_record()) {
				$search_result[$db->f("user_id")]=array("type"=>"user","name"=>$db->f("full_name")."(".$db->f("username").")");
			}
		}

		$query = "SELECT Seminar_id,IF(s.visible=0,CONCAT(s.Name, ' "._("(versteckt)")."'), s.Name) AS Name "
				. ($show_sem ? $show_sem_sql1 : "")
				." FROM seminare s "
				. ($show_sem ? $show_sem_sql2 : "")
				." WHERE s.Name LIKE '%$search_str%' ORDER BY start_time DESC, Name";
		$db->query($query);
		while($db->next_record()) {
			$name = $db->f("Name")
				. ($show_sem ? " (".$db->f('startsem')
				. ($db->f('startsem') != $db->f('endsem') ? " - ".$db->f('endsem') : "")
				. ")" : "");
			$search_result[$db->f("Seminar_id")]=array("type"=>"sem",
														"startsem"=>$db->f('startsem'),
														"name"=>$name);
		}
		$query="SELECT Institut_id,Name, IF(Institut_id=fakultaets_id,'fak','inst') AS inst_type FROM Institute WHERE Name LIKE '%$search_str%' ORDER BY Name";
		$db->query($query);
		while($db->next_record()) {
			$search_result[$db->f("Institut_id")]=array("type"=>$db->f("inst_type"),"name"=>$db->f("Name"));
		}
	} elseif ($search_str && $perm->have_perm("admin")) {
		$query="SELECT s.Seminar_id,IF(s.visible=0,CONCAT(s.Name, ' "._("(versteckt)")."'), s.Name) AS Name "
				. ($show_sem ? $show_sem_sql1 : "")
				. " FROM user_inst AS a LEFT JOIN  seminare AS s USING (Institut_id) "
				. ($show_sem ? $show_sem_sql2 : "")
				. " WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND s.Name LIKE '%$search_str%' ORDER BY start_time DESC, Name";
		$db->query($query);
		while($db->next_record()) {
			$name = $db->f("Name")
				. ($show_sem ? " (".$db->f('startsem')
				. ($db->f('startsem') != $db->f('endsem') ? " - ".$db->f('endsem') : "")
				. ")" : "");
			$search_result[$db->f("Seminar_id")]=array("type"=>"sem",
														"startsem"=>$db->f('startsem'),
														"name"=>$name);
		}
		$query="SELECT b.Institut_id,b.Name from user_inst AS a LEFT JOIN	Institute AS b USING (Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND a.institut_id!=b.fakultaets_id AND  b.Name LIKE '%$search_str%' ORDER BY Name";
		$db->query($query);
		while($db->next_record()) {
			$search_result[$db->f("Institut_id")]=array("type"=>"inst","name"=>$db->f("Name"));
		}
		if ($perm->is_fak_admin()) {
			$query = "SELECT s.Seminar_id,IF(s.visible=0,CONCAT(s.Name, ' "._("(versteckt)")."'), s.Name) AS Name"
				. ($show_sem ? $show_sem_sql1 : "")
				. " FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
				LEFT JOIN Institute c ON(c.fakultaets_id = b.institut_id AND c.fakultaets_id!=c.institut_id)
				LEFT JOIN seminare s ON(s.institut_id = c.institut_id) "
				. ($show_sem ? $show_sem_sql2 : "")
				. "	WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id) AND s.Name LIKE '%$search_str%' ORDER BY start_time DESC, Name";
			$db->query($query);
			while($db->next_record()){
				$name = $db->f("Name")
					. ($show_sem ? " (".$db->f('startsem')
					. ($db->f('startsem') != $db->f('endsem') ? " - ".$db->f('endsem') : "")
					. ")" : "");
				$search_result[$db->f("Seminar_id")]=array("type"=>"sem",
														"startsem"=>$db->f('startsem'),
														"name"=>$name);
			}
			$query = "SELECT c.Institut_id,c.Name FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
			LEFT JOIN Institute c ON(c.fakultaets_id = b.institut_id AND c.fakultaets_id!=c.institut_id)
			WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id) AND c.Name LIKE '%$search_str%' ORDER BY Name";
			$db->query($query);
			while($db->next_record()){
				$search_result[$db->f("Institut_id")]=array("type"=>"inst","name"=>$db->f("Name"));
			}
			$query = "SELECT b.Institut_id,b.Name FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
			WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id) AND b.Name LIKE '%$search_str%' ORDER BY Name";
			$db->query($query);
			while($db->next_record()){
				$search_result[$db->f("Institut_id")]=array("type"=>"fak","name"=>$db->f("Name"));
			}
		}

	} elseif ($perm->have_perm("tutor")) {
		$query="SELECT s.Seminar_id,IF(s.visible=0,CONCAT(s.Name, ' "._("(versteckt)")."'), s.Name) AS Name"
				. ($show_sem ? $show_sem_sql1 : "")
				. "	FROM seminar_user AS a LEFT JOIN seminare AS s USING (Seminar_id)"
				. ($show_sem ? $show_sem_sql2 : "")
				. "	WHERE a.user_id='$user->id' AND a.status IN ('dozent','tutor') ORDER BY start_time DESC, Name";
		$db->query($query);
		while($db->next_record()) {
			$name = $db->f("Name")
				. ($show_sem ? " (".$db->f('startsem')
				. ($db->f('startsem') != $db->f('endsem') ? " - ".$db->f('endsem') : "")
				. ")" : "");
			$search_result[$db->f("Seminar_id")]=array("type"=>"sem",
														"startsem"=>$db->f('startsem'),
														"name"=>$name);
		}
		$query="SELECT b.Institut_id,b.Name,IF(Institut_id=fakultaets_id,'fak','inst') AS inst_type from user_inst AS a LEFT JOIN  Institute AS b USING (Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms IN ('dozent','tutor') ORDER BY Name";
		$db->query($query);
		while($db->next_record()) {
			$search_result[$db->f("Institut_id")]=array("type"=>$db->f("inst_type"),"name"=>$db->f("Name"));
		}
	}
	return $search_result;
}

/*
 * format_help_url($keyword)
 * returns URL for given help keyword
 */

function format_help_url($keyword) {
	global $auth;
	if (!$keyword) {
		$keyword="Basis.Allgemeines"; //default value
	}
	$helppage=$keyword;
	// encode current user's global perms for help wiki
	$helppage.="?setstudipview=".$auth->auth["perm"];
	// encode locationid for help wiki if set
	$locationid=get_config("EXTERNAL_HELP_LOCATIONID");
	if ($locationid) {
		$helppage.="&amp;setstudiplocationid=".$locationid;
	}
	// insert into URL-Template from config
	$help_query=sprintf(get_config("EXTERNAL_HELP_URL"),$helppage);
	return $help_query;
}

/**
 * Remove slashes if magic quotes are enabled
 *
 * @param  mixed  string or array to strip slashes from
 *
 * @return mixed  cleaned string or array
 */

function remove_magic_quotes($mixed) {
	if (get_magic_quotes_gpc()) {
		if (is_array($mixed)) {
			foreach ($mixed as $k => $v) {
				$mixed[$k] = remove_magic_quotes($v);
			}
		}
		else {
			$mixed = stripslashes($mixed);
		}
	}
	return $mixed;
}

/**
 * Unset all variables set by register_globals (if enabled).
 * Note: The session variables 'auth' and 'SessSemName' are preserved.
 */
function unregister_globals ()
{
    if (!ini_get('register_globals')) {
        return;
    }

    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
        die('GLOBALS overwrite attempt detected');
    }

    $noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE',
                     '_REQUEST', '_SERVER', '_ENV', '_FILES',
                     'auth', 'SessSemName');
    $vars = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES);

    if (isset($_SESSION)) {
        $vars = array_merge($vars, $_SESSION);
    }

    foreach ($vars as $var => $value) {
        if (!in_array($var, $noUnset) && isset($GLOBALS[$var])) {
            unset($GLOBALS[$var]);
        }
    }
}

/**
  * Extracts an excerpt from the 'text' surrounding the 'phrase' with a number
  * of characters on each side determined by 'radius'. If the phrase isn't
  * found, null is returned.
  * Ex: text_excerpt("hello my world", "my", 3) => "...lo my wo..."
  *
  * @param string  the text to excerpted
  * @param string  the search phrase
  * @param integer the radius around the phrase
  * @param integer the maximum length of the excerpt string
  * @param string  the excerpt string
  *
  * @return type <description>
*/
function text_excerpt($text, $phrase, $radius = 100, $length = 200,
                      $excerpt_string = '...') {
  if ($text == '' || $phrase == '') {
    return '';
  }

  $found_pos = strpos(strtolower($text), strtolower($phrase));

  if ($found_pos === FALSE) {
    $start_pos = 0;
  }
  else {
    $start_pos = max($found_pos - $radius, 0);
  }

  $end_pos = $start_pos + $length - strlen($excerpt_string);
  if ($start_pos !== 0) {
    $end_pos -= strlen($excerpt_string);
  }

  $end_pos = min($end_pos, strlen($text));

  $prefix = $start_pos > 0 ? $excerpt_string : '';
  $postfix = $end_pos < strlen($text) ? $excerpt_string : '';

  return $prefix.substr($text, $start_pos, $end_pos - $start_pos).$postfix;
}

/**
 * Splits a string by space characters and returns these words as an array.
 *
 * @param  string       the string to split
 *
 * @return array        the words of the string as array
 */
function words($string) {
  return preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);
}

/**
 * Encodes a string from Stud.IP encoding (WINDOWS-1252/ISO-8859-1 with numeric HTML-ENTITIES) to UTF-8
 *
 * @param  string		a string to encode in WINDOWS-1252/HTML-ENTITIES
 *
 * @return string		the string in UTF-8
 */
function studip_utf8encode($string){
	if(!preg_match('/[\200-\377]/', $string) && !preg_match("'&#[0-9]+;'", $string)){
		return $string;
	} else {
		return mb_decode_numericentity(mb_convert_encoding($string,'UTF-8', 'WINDOWS-1252'), array(0x100, 0xffff, 0, 0xffff), 'UTF-8');
	}
}

/**
 * Encodes a string from UTF-8 to Stud.IP encoding (WINDOWS-1252/ISO-8859-1 with numeric HTML-ENTITIES)
 *
 * @param  string		a string in UTF-8
 *
 * @return string		the string in WINDOWS-1252/HTML-ENTITIES
 */
function studip_utf8decode($string){
	if(!preg_match('/[\200-\377]/', $string)){
		return $string;
	} else {
		$windows1252 = array(
			"\x80" => '&#8364;',
			"\x81" => '&#65533;',
			"\x82" => '&#8218;',
			"\x83" => '&#402;',
			"\x84" => '&#8222;',
			"\x85" => '&#8230;',
			"\x86" => '&#8224;',
			"\x87" => '&#8225;',
			"\x88" => '&#710;',
			"\x89" => '&#8240;',
			"\x8A" => '&#352;',
			"\x8B" => '&#8249;',
			"\x8C" => '&#338;',
			"\x8D" => '&#65533;',
			"\x8E" => '&#381;',
			"\x8F" => '&#65533;',
			"\x90" => '&#65533;',
			"\x91" => '&#8216;',
			"\x92" => '&#8217;',
			"\x93" => '&#8220;',
			"\x94" => '&#8221;',
			"\x95" => '&#8226;',
			"\x96" => '&#8211;',
			"\x97" => '&#8212;',
			"\x98" => '&#732;',
			"\x99" => '&#8482;',
			"\x9A" => '&#353;',
			"\x9B" => '&#8250;',
			"\x9C" => '&#339;',
			"\x9D" => '&#65533;',
			"\x9E" => '&#382;',
			"\x9F" => '&#376;');
		return str_replace(	array_values($windows1252),
							array_keys($windows1252),
							utf8_decode(mb_encode_numericentity($string,
																array(0x100, 0xffff, 0, 0xffff),
																'UTF-8')
										)
							);
	}
}

function mark_public_course($course = NULL) {

	if ($course === NULL) {
		require_once "lib/classes/Seminar.class.php";
		$course = Seminar::getInstance($GLOBALS['SessSemName'][1]);
	}

	// change class attribute of the body tag and headline if this course is
	// publicly visible
	if ($course->isPublic()) {
		$GLOBALS["body_class"] = isset($GLOBALS["body_class"])
		                         ? $GLOBALS["body_class"] . " public_course"
		                         : "public_course";
		$name = $course->getName();
		$type = $GLOBALS["SessSemName"]["art"];
		$GLOBALS["SessSemName"]["header_line"] =
			getHeaderLine($course->getId(), compact('name', 'type')) .
			" (" . _("�ffentliche Veranstaltung") . ")";
	}
}
