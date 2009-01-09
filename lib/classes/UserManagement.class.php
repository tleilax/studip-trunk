<?php
# Lifter002: TODO
/**
* UserManagement.class.php
*
* Management for the Stud.IP global users
*
*
* @author		Stefan Suchi <suchi@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		UserManagement.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// UserManagement.class.php
// Management for the Stud.IP global users
// Copyright (C) 2003 Stefan Suchi <suchi@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once 'lib/functions.php';
require_once 'lib/language.inc.php';
require_once 'config.inc.php'; 		// We need the uni name for emails
require_once 'lib/admission.inc.php';	// remove user from waiting lists
require_once 'lib/datei.inc.php';	// remove documents of user
require_once 'lib/statusgruppe.inc.php';	// remove user from statusgroups
require_once 'lib/dates.inc.php';	// remove appointments of user
require_once 'lib/messaging.inc.php';	// remove messages send or recieved by user
require_once 'lib/contact.inc.php';	// remove user from adressbooks
require_once 'lib/classes/DataFieldEntry.class.php';	// remove extra data of user
require_once 'lib/classes/auth_plugins/StudipAuthAbstract.class.php';
require_once 'lib/classes/StudipNews.class.php';
require_once 'lib/object.inc.php';
if ($GLOBALS['RESOURCES_ENABLE']) {
	include_once ($GLOBALS['RELATIVE_PATH_RESOURCES']."/lib/DeleteResourcesUser.class.php");
}
if ($GLOBALS['ILIAS_CONNECT_ENABLE']) {
	include_once ($GLOBALS['RELATIVE_PATH_LEARNINGMODULES']."/lernmodul_db_functions.inc.php");
	include_once ($GLOBALS['RELATIVE_PATH_LEARNINGMODULES']."/lernmodul_user_functions.inc.php");
}
if ($GLOBALS['CALENDAR_ENABLE']) {
	include_once ($GLOBALS['RELATIVE_PATH_CALENDAR']
	. "/lib/driver/{$GLOBALS['CALENDAR_DRIVER']}/CalendarDriver.class.php");
}
if ($GLOBALS['ELEARNING_INTERFACE_ENABLE']){
	require_once ($GLOBALS['RELATIVE_PATH_ELEARNING_INTERFACE'] . "/ELearningUtils.class.php");
}
require_once "lib/log_events.inc.php";	// Event logging

require_once 'lib/classes/Avatar.class.php'; // remove Avatarture

class UserManagement {
	var $user_data = array();		// associative array, contains userdata from tables auth_user_md5 and user_info
	var $msg = ""; 		// contains all messages
	var $db;     			// database connection1
	var $db2;     		// database connection2
	var $validator;		// object used for checking input
	var $smtp;				// object used for sending mails
	var $hash_secret = "jdfiuwenxclka";  // set this to something, just something different...

	/**
	* Constructor
	*
	* Pass nothing to create a new user, or the user_id from an existing user to change or delete
	* @access	public
	* @param	string	$user_id	the user which should be retrieved
	*/
	function UserManagement($user_id = FALSE) {

		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		$this->validator = new email_validation_class;
		$this->validator->timeout = 10;					// How long do we wait for response of mailservers?
		$this->smtp = new studip_smtp_class;
		if ($user_id) {
			$this->getFromDatabase($user_id);
		}
	}


	/**
	* load user data from database into internal array
	*
	* @access	private
	* @param	string	$user_id	the user which should be retrieved
	*/
	function getFromDatabase($user_id) {

		$this->db->query("SELECT * FROM auth_user_md5 WHERE user_id = '$user_id'");  //ein paar userdaten brauchen wir schon mal
			$fields = $this->db->metadata();
			if ($this->db->next_record()) {
				for ($i=0; $i<count($fields); $i++) {
					$field_name = $fields[$i]["name"];
					$this->user_data["auth_user_md5.".$field_name] = $this->db->f("$field_name");
			}
		}

		$this->db->query("SELECT * FROM user_info WHERE user_id = '".$this->user_data["auth_user_md5.user_id"]."'");
		$fields = $this->db->metadata();
		if ($this->db->next_record()) {
			for ($i=0; $i<count($fields); $i++) {
				$field_name = $fields[$i]["name"];
				$this->user_data["user_info.".$field_name] = $this->db->f("$field_name");
			}
		}
		$this->original_user_data=$this->user_data; // save original setting for logging purposes
	}


	/**
	* store user data from internal array into database
	*
	* @access	private
	* @return	bool all data stored?
	*/
	function storeToDatabase() {

		if (!$this->user_data['auth_user_md5.user_id']) {
			$this->user_data['auth_user_md5.user_id'] = md5(uniqid($this->hash_secret));
			$this->db->query("INSERT INTO auth_user_md5 SET user_id = '".$this->user_data['auth_user_md5.user_id']."', username = '".$this->user_data['auth_user_md5.username']."', password = 'dummy'");
			if ($this->db->affected_rows() == 0) {
				return FALSE;
			}
			$this->db->query("INSERT INTO user_info SET user_id = '".$this->user_data['auth_user_md5.user_id']."', mkdate='".time()."'");
			if ($this->db->affected_rows() == 0) {
				return FALSE;
			}
			log_event("USER_CREATE",$this->user_data['auth_user_md5.user_id']);
		}

		if (!$this->user_data['auth_user_md5.auth_plugin']) {
			$this->user_data['auth_user_md5.auth_plugin'] = "standard"; // just to be sure
		}
		foreach($this->user_data as $key => $value) {
			$split = explode(".",$key);
			$table = $split[0];
			$field = $split[1];
			// update changed fields only
			if ($this->original_user_data["$table.$field"]!=$value) {
				$value_escaped = mysql_escape_string($value);
				$this->db->query("UPDATE $table SET $field = '$value_escaped' WHERE user_id = '".$this->user_data['auth_user_md5.user_id']."'");
				// logging >>>>>>
				if ($this->db->affected_rows() != 0) {
					switch ($field) {
						case 'username':
							log_event("USER_CHANGE_USERNAME",$this->user_data['auth_user_md5.user_id'],NULL,$this->original_user_data['auth_user_md5.username']." -> ".$value);
							break;
						case 'Vorname':
							log_event("USER_CHANGE_NAME",$this->user_data['auth_user_md5.user_id'],NULL,"Vorname: ".$this->original_user_data['auth_user_md5.Vorname']." -> ".$value);
							break;
						case 'Nachname':
							log_event("USER_CHANGE_NAME",$this->user_data['auth_user_md5.user_id'],NULL,"Nachname: ".$this->original_user_data['auth_user_md5.Nachname']." -> ".$value);
							break;
						case 'perms':
							log_event("USER_CHANGE_PERMS",$this->user_data['auth_user_md5.user_id'],NULL,$this->original_user_data['auth_user_md5.perms']." -> ".$value);
							break;
						case 'Email':
							log_event("USER_CHANGE_EMAIL",$this->user_data['auth_user_md5.user_id'],NULL,$this->original_user_data['auth_user_md5.Email']." -> ".$value);
							break;
						case 'title_front':
							log_event("USER_CHANGE_TITLE",$this->user_data['auth_user_md5.user_id'],NULL,"title_front: ".$this->original_user_data['user_info.title_front']." -> ".$value);
							break;
						case 'title_rear':
							log_event("USER_CHANGE_TITLE",$this->user_data['auth_user_md5.user_id'],NULL,"title_rear: ".$this->original_user_data['user_info.title_front']." -> ".$value);
							break;
					}
				}
				// <<<<<<< logging
			}

		}
		$this->db->query("UPDATE user_info SET chdate='".time()."' WHERE user_id = '".$this->user_data['auth_user_md5.user_id']."'");
		if ($this->db->affected_rows() == 0) {
			return FALSE;
		}
		return TRUE;
	}


	/**
	* generate a secure password of $length characters [a-z0-9]
	*
	* @access	private
	* @param	integer	$length	number of characters
	* @return	string password
	*/
	function generate_password($length) {
		mt_srand((double)microtime()*1000000);
		for ($i=1;$i<=$length;$i++) {
			$temp = mt_rand() % 36;
			if ($temp < 10)
				$temp += 48;	 // 0 = chr(48), 9 = chr(57)
			else
				$temp += 87;	 // a = chr(97), z = chr(122)
			$pass .= chr($temp);
		}
		return $pass;
	}


	/**
	* Check if Email-Adress is valid and reachable
	*
	* @access	private
	* @param	string	Email-Adress to check
	* @return	bool Email-Adress valid and reachable?
	*/
	function checkMail($Email) {
		// Adress correkt?
		if (!$this->validator->ValidateEmailAddress($Email)) {
			$this->msg .= "error�" . _("E-Mail-Adresse syntaktisch falsch!") . "�";
			return FALSE;
		}
		// E-Mail reachable?
		if (!$this->validator->ValidateEmailHost($Email)) {		 // Mailserver nicht erreichbar, ablehnen
			$this->msg .= "error�" . _("Mailserver ist nicht erreichbar!") . "�";
			return FALSE;
		}
		if (!$this->validator->ValidateEmailBox($Email)) {		// aber user unbekannt, ablehnen
			$this->msg .= "error�" . sprintf(_("E-Mail an <b>%s</b> ist nicht zustellbar!"), $Email) . "�";
			return FALSE;
		}
		return TRUE;
	}


	/**
	* Do auto inserts, if we created an autor, tutor or dozent
	*
	* @access	public
	* @param	string	old status before changes
	*/
	function autoInsertSem($old_status = FALSE) {
		global $AUTO_INSERT_SEM, $perm, $auth;

		if (!$perm->have_perm("admin") && $this->user_data['auth_user_md5.user_id'] != $auth->auth["uid"]) {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung diesen Account zu ver&auml;ndern.") . "�";
			return FALSE;
		}
		if (($old_status != "autor") && ($old_status != "tutor") && ($old_status != "dozent")) {
			if (($this->user_data['auth_user_md5.perms'] == "autor") || ($this->user_data['auth_user_md5.perms'] == "tutor") || ($this->user_data['auth_user_md5.perms'] == "dozent")) {
				if (is_array($AUTO_INSERT_SEM)){
					foreach ($AUTO_INSERT_SEM as $sem) {
						$this->db->query("SELECT Name, start_time, Schreibzugriff FROM seminare WHERE Seminar_id = '$sem'");
						if ($this->db->num_rows()) {
							$this->db->next_record();
							if ($this->db->f("Schreibzugriff") < 2) { // seminar exists and no password is set
								$this->db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$sem' AND user_id='".$this->user_data['auth_user_md5.user_id']."'");
								if ($this->db2->num_rows()) { // user has already subscribed
									$this->db2->next_record();
									if ($this->db2->f("status") == "user") { // we could uplift him
										$this->db2->query("UPDATE seminar_user SET status = 'autor' WHERE Seminar_id = '$sem' AND user_id='".$this->user_data['auth_user_md5.user_id']."'");
										if ($this->user_data['auth_user_md5.user_id'] == $auth->auth["uid"]) {
											$this->msg .= sprintf("msg�" . _("Ihnen wurden Schreibrechte in der Veranstaltung <b>%s</b> erteilt.") . "�", $this->db->f("Name"));
										} else {
											$this->msg .= sprintf("msg�" . _("Der Person wurden Schreibrechte in der Veranstaltung <b>%s</b> erteilt.") . "�", $this->db->f("Name"));
										}
									}
								} else {  // user has not subscribed until now, lets do it...
									$group = select_group ($this->db->f("start_time"));
									$this->db2->query("INSERT into seminar_user (Seminar_id, user_id, status, gruppe) values ('$sem', '".$this->user_data['auth_user_md5.user_id']."', 'autor', '$group')");
									if ($this->user_data['auth_user_md5.user_id'] == $auth->auth["uid"]) {
										$this->msg .= sprintf("msg�" . _("Sie wurden automatisch in die Veranstaltung <b>%s</b> eingetragen.") . "�", $this->db->f("Name"));
									} else {
										$this->msg .= sprintf("msg�" . _("Die Person wurde automatisch in die Veranstaltung <b>%s</b> eingetragen.") . "�", $this->db->f("Name"));
									}
								}
							}
						}
					}
				}
			}
		}
	}


	/**
	* Create a new studip user with the given parameters
	*
	* @access	public
	* @param	array	structure: array('string table_name.field_name'=>'string value')
	* @return	bool Creation successful?
	*/
	function createNewUser($newuser) {
		global $perm;

		// Do we have permission to do so?
		if (!$perm->have_perm("admin")) {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung Accounts anzulegen.") . "�";
			return FALSE;
		}
		if (!$perm->is_fak_admin() && $newuser['auth_user_md5.perms'] == "admin") {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung <b>Admin-Accounts</b> anzulegen.") . "�";
			return FALSE;
		}
		if (!$perm->have_perm("root") && $newuser['auth_user_md5.perms'] == "root") {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung <b>Root-Accounts</b> anzulegen.") . "�";
			return FALSE;
		}

		// Do we have all necessary data?
		if (empty($newuser['auth_user_md5.username']) || empty($newuser['auth_user_md5.perms']) || empty ($newuser['auth_user_md5.Email'])) {
			$this->msg .= "error�" . _("Bitte geben Sie <b>Username</b>, <b>Status</b> und <b>E-Mail</b> an!") . "�";
			return FALSE;
		}

		// Is the username correct?
		if (!$this->validator->ValidateUsername($newuser['auth_user_md5.username'])) {
			$this->msg .= "error�" .  _("Der gew�hlte Username ist zu kurz oder enth�lt unzul�ssige Zeichen!") . "�";
			return FALSE;
		}

		// Can we reach the email?
		if (!$this->checkMail($newuser['auth_user_md5.Email'])) {
			return FALSE;
		}

		// Store new values in internal array
		foreach ($newuser as $key => $value) {
			$this->user_data[$key] = $value;
		}

		$password = $this->generate_password(6);
		$this->user_data['auth_user_md5.password'] = md5($password);

		// Does the user already exist?
		// NOTE: This should be a transaction, but it is not...
		$this->db->query("select * from auth_user_md5 where username='{$newuser['auth_user_md5.username']}'");
		if ($this->db->nf()>0) {
			$this->msg .= "error�" . sprintf(_("BenutzerIn <b>%s</b> ist schon vorhanden!"), $newuser['auth_user_md5.username']) . "�";
			return FALSE;
		}

		if (!$this->storeToDatabase()) {
			$this->msg .= "error�" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "�";
			return FALSE;
		}

		$this->autoInsertSem();
		$this->msg .= "msg�" . sprintf(_("BenutzerIn \"%s\" angelegt."), $newuser['auth_user_md5.username']) . "�";

		// include language-specific subject and mailbody
		$user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']); // user has been just created, so we will get $DEFAULT_LANGUAGE
		$Zeit=date("H:i:s, d.m.Y",time());
		include("locale/$user_language/LC_MAILS/create_mail.inc.php");

		// send mail
		$this->smtp->SendMessage(
				$this->smtp->env_from,
				array($this->user_data['auth_user_md5.Email']),
				array("From: " . $this->smtp->from,
						"Reply-To:" . $this->smtp->abuse,
						"To: " . $this->user_data['auth_user_md5.Email'],
						"Subject: " . $subject),
				$mailbody);

		return TRUE;
	}


	/**
	* Change an existing studip user according to the given parameters
	*
	* @access	public
	* @param	array	structure: array('string table_name.field_name'=>'string value')
	* @return	bool Change successful?
	*/
	function changeUser($newuser) {
		global $perm, $auth;

		// Do we have permission to do so?
		if (!$perm->have_perm("admin")) {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung Accounts zu ver&auml;ndern.") . "�";
			return FALSE;
		}
		if (!$perm->is_fak_admin() && $newuser['auth_user_md5.perms'] == "admin") {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung, <b>Admin-Accounts</b> anzulegen.") . "�";
			return FALSE;
		}
		if (!$perm->have_perm("root") && $newuser['auth_user_md5.perms'] == "root") {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung, <b>Root-Accounts</b> anzulegen.") . "�";
			return FALSE;
		}
		if (!$perm->have_perm("root")) {
			if (!$perm->is_fak_admin() && $this->user_data['auth_user_md5.perms'] == "admin") {
				$this->msg .= "error�" . _("Sie haben keine Berechtigung <b>Admin-Accounts</b> zu ver&auml;ndern.") . "�";
				return FALSE;
			}
			if ($this->user_data['auth_user_md5.perms'] == "root") {
				$this->msg .= "error�" . _("Sie haben keine Berechtigung <b>Root-Accounts</b> zu ver&auml;ndern.") . "�";
				return FALSE;
			}
			if ($perm->is_fak_admin() && $this->user_data['auth_user_md5.perms'] == "admin") {
				$this->db->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id)
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '" . $auth->auth["uid"] . "' AND c.inst_perms='admin')
							WHERE a.user_id ='" . $this->user_data['auth_user_md5.user_id'] . "' AND a.inst_perms = 'admin'");
				$this->db->next_record();
				if (!$this->db->f("admin_ok")) {
					$this->msg .= "error�" . _("Sie haben keine Berechtigung diesen Admin-Account zu ver&auml;ndern.") . "�";
					return FALSE;
				}
			}
		}

		// active dozent?
		$this->db->query("SELECT count(*) AS count FROM seminar_user WHERE user_id = '" . $this->user_data['auth_user_md5.user_id'] . "' AND status = 'dozent' GROUP BY user_id");
		$this->db->next_record();
		if ($this->db->f("count") &&  isset($newuser['auth_user_md5.perms']) && $newuser['auth_user_md5.perms'] != "dozent") {
			$this->msg .= sprintf("error�" . _("Der Benutzer <b>%s</b> ist Dozent in %s aktiven Veranstaltungen und kann daher nicht in einen anderen Status versetzt werden!") . "�", $this->user_data['auth_user_md5.username'], $this->db->f("count"));
			return FALSE;
		}

		// active admin?
		if ($this->user_data['auth_user_md5.perms'] == 'admin' && $newuser['auth_user_md5.perms'] != 'admin') {
			// count number of institutes where the user is admin
			$stmt = DBManager::get()->query("SELECT COUNT(*) AS count FROM user_inst 
				WHERE user_id = '". $this->user_data['auth_user_md5.user_id'] ."' AND inst_perms = 'admin'
				GROUP BY Institut_id");

			// if there are institutes with admin-perms, add error-message and deny change	
			if ($count = $stmt->fetchColumn()) {
				$this->msg .= sprintf('error�'. _("Der Benutzer <b>%s</b> ist Admin in %s Einrichtungen und kann daher nicht in einen anderen Status versetzt werden!") .'�', $this->user_data['auth_user_md5.username'], $count);
				return false;
			}
		}

		// Is the username correct?
		if (isset($newuser['auth_user_md5.username'])) {
			if ($this->user_data['auth_user_md5.username'] != $newuser['auth_user_md5.username']) {
				if (!$this->validator->ValidateUsername($newuser['auth_user_md5.username'])) {
					$this->msg .= "error�" .  _("Der gew�hlte Username ist zu kurz oder enth�lt unzul�ssige Zeichen!") . "�";
					return FALSE;
				}
				$check_uname = StudipAuthAbstract::CheckUsername($newuser['auth_user_md5.username']);
				if ($check_uname['found']) {
					$this->msg .= "error�" . _("Der Username wird bereits von einem anderen User verwendet. Bitte w�hlen sie einen anderen Usernamen!") . "�";
					return false;
				} else {
					//$this->msg .= "info�" . $check_uname['error'] ."�";
				}
			} else
			unset($newuser['auth_user_md5.username']);
		}

		// Can we reach the email?
		if (isset($newuser['auth_user_md5.Email'])) {
			if (!$this->checkMail($newuser['auth_user_md5.Email'])) {
				return FALSE;
			}
		}

		// Store changed values in internal array if allowed
		$old_perms = $this->user_data['auth_user_md5.perms'];
		foreach ($newuser as $key => $value) {
			if (!StudipAuthAbstract::CheckField($key, $this->user_data['auth_user_md5.auth_plugin'])) {
				$this->user_data[$key] = $value;
			} else {
				$this->msg .= "error�" .  sprintf(_("Das Feld <b>%s</b> k�nnen Sie nicht �ndern!"), $key) . "�";
				return FALSE;
			}
		}

		if (!$this->storeToDatabase()) {
			$this->msg .= "error�" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "�";
			return FALSE;
		}

		if ($GLOBALS['ILIAS_CONNECT_ENABLE']) {
			$this_ilias_id = get_connected_user_id($this->user_data['auth_user_md5.user_id']);
			if ($this_ilias_id)
				edit_ilias_user($this_ilias_id, $this->user_data['auth_user_md5.username'], $this->user_data['user_info.geschlecht'], $this->user_data['auth_user_md5.Vorname'], $this->user_data['auth_user_md5.Nachname'], $this->user_data['user_info.title_front'], "Stud.IP", $this->user_data['auth_user_md5.Email'], $this->user_data['auth_user_md5.perms'], $this->user_data['user_info.preferred_language']);
		}

		$this->autoInsertSem($old_perms);
		$this->msg .= "msg�" . sprintf(_("User \"%s\" ver&auml;ndert."), $this->user_data['auth_user_md5.username']) . "�";

		// include language-specific subject and mailbody
		$user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);
		$Zeit=date("H:i:s, d.m.Y",time());
		include("locale/$user_language/LC_MAILS/change_mail.inc.php");

		// send mail
		$this->smtp->SendMessage(
				$this->smtp->env_from,
				array($this->user_data['auth_user_md5.Email']),
				array("From: " . $this->smtp->from,
						"Reply-To:" . $this->smtp->abuse,
						"To: " . $this->user_data['auth_user_md5.Email'],
						"Subject: " . $subject),
				$mailbody);

		// Upgrade to admin or root?
		if ($newuser['auth_user_md5.perms'] == "admin" || $newuser['auth_user_md5.perms'] == "root") {

         $this->re_sort_position_in_seminar_user();

			// delete all seminar entries
			$query = "SELECT seminar_id FROM seminar_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
			$query2 = "DELETE FROM seminar_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
			$this->db->query($query);
			$this->db2->query($query2);
			if (($db_ar = $this->db2->affected_rows()) > 0) {
				$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus Veranstaltungen gel&ouml;scht."), $db_ar) . "�";
				while ($this->db->next_record()) {
					update_admission($this->db->f("seminar_id"));
				}
			}
			// delete all entries from waiting lists
			$query = "SELECT seminar_id FROM admission_seminar_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
			$query2 = "DELETE FROM admission_seminar_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
			$this->db->query($query);
			$this->db2->query($query2);
			if (($db_ar = $this->db2->affected_rows()) > 0) {
				$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus Wartelisten gel&ouml;scht."), $db_ar) . "�";
				while ($this->db->next_record()) {
					update_admission($this->db->f("seminar_id"));
				}
			}
			// delete 'Studiengaenge'
			$query = "DELETE FROM user_studiengang WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
			$this->db->query($query);
			if (($db_ar = $this->db->affected_rows()) > 0) {
				$this->msg .= "info�" . sprintf(_("%s Zuordnungen zu Studieng&auml;ngen gel&ouml;scht."), $db_ar) . "�";
			}
			// delete all private appointments of this user
			if ($db_ar = delete_range_of_dates($this->user_data['auth_user_md5.user_id'], FALSE) > 0) {
				$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus den Terminen gel&ouml;scht."), $db_ar) . "�";
			}
		}

		if ($newuser['auth_user_md5.perms'] == "admin") {
			$query = "DELETE FROM user_inst WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "' AND inst_perms != 'admin'";
			$this->db->query($query);
			if (($db_ar = $this->db->affected_rows()) > 0) {
				$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus MitarbeiterInnenlisten gel&ouml;scht."), $db_ar) . "�";
			}
		}
		if ($newuser['auth_user_md5.perms'] == "root") {
			$query = "DELETE FROM user_inst WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
			$this->db->query($query);
			if (($db_ar = $this->db->affected_rows()) > 0) {
				$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus MitarbeiterInnenlisten gel&ouml;scht."), $db_ar) . "�";
			}
		}

		return TRUE;
	}


	/**
	* Create a new password and mail it to the user
	*
	* @access	public
	* @return	bool Password change successful?
	*/
	function setPassword() {
		global $perm, $auth;

		// Do we have permission to do so?
		if (!$perm->have_perm("admin")) {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung Accounts zu ver&auml;ndern.") . "�";
			return FALSE;
		}

		if (!$perm->have_perm("root")) {
			if ($this->user_data['auth_user_md5.perms'] == "root") {
				$this->msg .= "error�" . _("Sie haben keine Berechtigung <b>Root-Accounts</b> zu ver&auml;ndern.") . "�";
				return FALSE;
			}
			if ($perm->is_fak_admin() && $this->user_data['auth_user_md5.perms'] == "admin"){
				$this->db->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id)
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '" . $auth->auth["uid"] . "' AND c.inst_perms='admin')
							WHERE a.user_id ='" . $this->user_data['auth_user_md5.user_id'] . "' AND a.inst_perms = 'admin'");
				$this->db->next_record();
				if (!$this->db->f("admin_ok")) {
					$this->msg .= "error�" . _("Sie haben keine Berechtigung diesen Admin-Account zu ver&auml;ndern.") . "�";
					return FALSE;
				}
			}
		}

		// Can we reach the email?
		if (!$this->checkMail($this->user_data['auth_user_md5.Email'])) {
			return FALSE;
		}

		$password = $this->generate_password(6);
		$this->user_data['auth_user_md5.password'] = md5($password);

		if (!$this->storeToDatabase()) {
			$this->msg .= "error�" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "�";
			return FALSE;
		}

		$this->msg .= "msg�" . sprintf(_("Passwort von User \"%s\" neu gesetzt."), $this->user_data['auth_user_md5.username']) . "�";

		// include language-specific subject and mailbody
		$user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);
		$Zeit=date("H:i:s, d.m.Y",time());
		include("locale/$user_language/LC_MAILS/password_mail.inc.php");

		// send mail
		$this->smtp->SendMessage(
				$this->smtp->env_from,
				array($this->user_data['auth_user_md5.Email']),
				array("From: " . $this->smtp->from,
						"Reply-To:" . $this->smtp->abuse,
						"To: " . $this->user_data['auth_user_md5.Email'],
						"Subject: " . $subject),
				$mailbody);

		log_event("USER_NEWPWD",$this->user_data['auth_user_md5.user_id']);
		return TRUE;

	}


	/**
	* Delete an existing user from the database and tidy up
	*
	* @access	public
	* @return	bool Removal successful?
	*/
	function deleteUser() {
		global $perm, $auth;

		// Do we have permission to do so?
		if (!$perm->have_perm("admin")) {
			$this->msg .= "error�" . _("Sie haben keine Berechtigung Accounts zu l&ouml;schen.") . "�";
			return FALSE;
		}

		if (!$perm->have_perm("root")) {
			if ($this->user_data['auth_user_md5.perms'] == "root") {
				$this->msg .= "error�" . _("Sie haben keine Berechtigung <b>Root-Accounts</b> zu l&ouml;schen.") . "�";
				return FALSE;
			}
			if ($perm->is_fak_admin() && $this->user_data['auth_user_md5.perms'] == "admin"){
				$this->db->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id)
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '" . $auth->auth["uid"] . "' AND c.inst_perms='admin')
							WHERE a.user_id ='" . $this->user_data['auth_user_md5.user_id'] . "' AND a.inst_perms = 'admin'");
				$this->db->next_record();
				if (!$this->db->f("admin_ok")) {
					$this->msg .= "error�" . _("Sie haben keine Berechtigung diesen Admin-Account zu l&ouml;schen.") . "�";
					return FALSE;
				}
			}
		}

		// active dozent?
		$this->db->query("SELECT count(*) AS count FROM seminar_user WHERE user_id = '" . $this->user_data['auth_user_md5.user_id'] . "' AND status = 'dozent' GROUP BY user_id");
		$this->db->next_record();
		if ($this->db->f("count")) {
			$this->msg .= sprintf("error�" . _("Der Benutzer/die Benutzerin <b>%s</b> ist DozentIn in %s aktiven Veranstaltungen und kann daher nicht gel&ouml;scht werden.") . "�", $this->user_data['auth_user_md5.username'], $this->db->f("count"));
			return FALSE;
		}

		// store user preferred language for sending mail
		$user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);

		// delete documents of this user
		$temp_count = 0;
		$query = "SELECT dokument_id FROM dokumente WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		while ($this->db->next_record()) {
			if (delete_document($this->db->f("dokument_id")))
				$temp_count ++;
		}
		if ($temp_count) {
			$this->msg .= "info�" . sprintf(_("%s Dokumente gel&ouml;scht."), $temp_count) . "�";
		}

		// delete empty folders of this user
		$temp_count = 0;
		$query = "SELECT folder_id FROM folder WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "' ORDER BY mkdate DESC";
		$this->db->query($query);
		while ($this->db->next_record()) {
			$query = "SELECT count(*) AS count FROM folder WHERE range_id = '".$this->db->f("folder_id")."'";
			$this->db2->query($query);
			$this->db2->next_record();
			if (!$this->db2->f("count") && !doc_count($this->db->f("folder_id"))) {
				$query = "DELETE FROM folder WHERE folder_id ='".$this->db->f("folder_id")."'";
				$this->db2->query($query);
				$temp_count += $this->db2->affected_rows();
			}
		}
		if ($temp_count) {
			$this->msg .= "info�" . sprintf(_("%s leere Ordner gel&ouml;scht."), $temp_count) . "�";
		}

		// folder left?
		$query = "SELECT count(*) AS count FROM folder WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		$this->db->next_record();
		if ($this->db->f("count")) {
			$this->msg .= sprintf("info�" . _("%s Ordner konnten nicht gel&ouml;scht werden, da sie noch Dokumente anderer BenutzerInnen enthalten.") . "�", $this->db->f("count"));
		}

		// kill all the ressources that are assigned to the user (and all the linked or subordinated stuff!)
		if ($GLOBALS['RESOURCES_ENABLE']) {
			$killAssign = new DeleteResourcesUser($this->user_data['auth_user_md5.user_id']);
			$killAssign->delete();
		}

      $this->re_sort_position_in_seminar_user();

		// delete user from seminars (postings will be preserved)
		$query = "DELETE FROM seminar_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		if (($db_ar = $this->db->affected_rows()) > 0) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus Veranstaltungen gel&ouml;scht."), $db_ar) . "�";
		}

		// delete user from waiting lists
		$query2 = "SELECT seminar_id FROM admission_seminar_user where user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$query = "DELETE FROM admission_seminar_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		$this->db2->query($query2);
		if (($db_ar = $this->db->affected_rows()) > 0) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus Wartelisten gel&ouml;scht."), $db_ar) . "�";
		while ($this->db2->next_record())
			update_admission($this->db2->f("seminar_id"));
		}

		// delete user from instituts
		$query = "DELETE FROM user_inst WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		if (($db_ar = $this->db->affected_rows()) > 0) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus MitarbeiterInnenlisten gel&ouml;scht."), $db_ar) . "�";
		}

		// delete user from Statusgruppen
		if ($db_ar = RemovePersonFromAllStatusgruppen(get_username($this->user_data['auth_user_md5.user_id']))  > 0) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus Funktionen / Gruppen gel&ouml;scht."), $db_ar) . "�";
		}

		// delete user from archiv
		$query = "DELETE FROM archiv_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		if (($db_ar = $this->db->affected_rows()) > 0) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus den Zugriffsberechtigungen f&uuml;r das Archiv gel&ouml;scht."), $db_ar) . "�";
		}

		// delete all personal news from this user
		if (($db_ar = StudipNews::DeleteNewsByAuthor($this->user_data['auth_user_md5.user_id']))) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus den News gel&ouml;scht."), $db_ar) . "�";
		}
		if (($db_ar = StudipNews::DeleteNewsRanges($this->user_data['auth_user_md5.user_id']))) {
			$this->msg .= "info�" . sprintf(_("%s Verweise auf News gel&ouml;scht."), $db_ar) . "�";
		}

		//delete entry in news_rss_range
		StudipNews::UnsetRssId($this->user_data['auth_user_md5.user_id']);

		// delete 'Studiengaenge'
		$query = "DELETE FROM user_studiengang WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		if (($db_ar = $this->db->affected_rows()) > 0)
			$this->msg .= "info�" . sprintf(_("%s Zuordnungen zu Studieng&auml;ngen gel&ouml;scht."), $db_ar) . "�";

		// delete all private appointments of this user
		if ($GLOBALS['CALENDAR_ENABLE']) {
			$calendar = new CalendarDriver();
			if ($appkills = $calendar->deleteFromDatabase('ALL', NULL, 0, 0, $this->user_data['auth_user_md5.user_id']))
				$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus den Terminen gel&ouml;scht."), $appkills) ."�";
		}

		// delete all messages send or received by this user
		$messaging=new messaging;
		$messaging->delete_all_messages($this->user_data['auth_user_md5.user_id'], TRUE);

		// delete user from all foreign adressbooks and empty own adressbook
		$buddykills = RemoveUserFromBuddys($this->user_data['auth_user_md5.user_id']);
		if ($buddykills > 0) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus Adressb&uuml;chern gel&ouml;scht."), $buddykills) . "�";
		}
		$msg = DeleteAdressbook($this->user_data['auth_user_md5.user_id']);
		if ($msg) {
			$this->msg .= "info�" . $msg . "�";
		}

		// delete all guestbook entrys
		$query = "DELETE FROM guestbook WHERE range_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		if (($db_ar = $this->db->affected_rows()) > 0) {
			$this->msg .= "info�" . sprintf(_("%s Eintr&auml;ge aus dem G�stebuch gel&ouml;scht."), $db_ar) . "�";
		}

		// delete the datafields
		$localEntries = DataFieldEntry::removeAll($this->user_data['auth_user_md5.user_id']);

		$user_cfg = new UserConfig();
		$user_cfg->unsetAll($this->user_data['auth_user_md5.user_id']);

		// delete all remaining user data
		$query = "DELETE FROM seminar_user_schedule WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		$query = "DELETE FROM rss_feeds WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		$query = "DELETE FROM kategorien WHERE range_id = '" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		$query = "DELETE FROM user_info WHERE user_id= '" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		$GLOBALS['user']->that->ac_delete($this->user_data['auth_user_md5.user_id'], $GLOBALS['user']->name);
		object_kill_visits($this->user_data['auth_user_md5.user_id']);
		object_kill_views($this->user_data['auth_user_md5.user_id']);

		// delete picture
		$avatar = Avatar::getAvatar($this->user_data["auth_user_md5.user_id"]);
		if ($avatar->is_customized()) {
			$avatar->reset();
			$this->msg .= "info�" . _("Bild gel&ouml;scht.") . "�";
		}

		// delete ILIAS-Account (if it was automatically generated)
		if ($GLOBALS['ILIAS_CONNECT_ENABLE']) {
			$this_ilias_id = get_connected_user_id($this->user_data['auth_user_md5.user_id']);
			if (($this_ilias_id) AND (is_created_user($this->user_data['auth_user_md5.user_id'])))
				delete_ilias_user($this_ilias_id);
		}

		//delete connected users
		if ($GLOBALS['ELEARNING_INTERFACE_ENABLE']){
			if(ElearningUtils::initElearningInterfaces()){
				foreach($GLOBALS['connected_cms'] as $cms){
					if(is_object($cms->user)){
						$userclass = strtolower(get_class($cms->user));
						$connected_user = new $userclass($cms->cms_type, $this->user_data['auth_user_md5.user_id']);
						if($ok = $connected_user->deleteUser()){
							if($connected_user->is_connected){
								$this->msg .= "info�" . sprintf(_("Der verkn�pfte Nutzer %s wurde im System %s gel�scht."), $connected_user->login, $connected_user->cms_type) . "�";
							}
						}
					}
				}
			}
		}

		// delete Stud.IP account
		$query = "DELETE FROM auth_user_md5 WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		if (!$this->db->affected_rows()) {
			$this->msg .= "error�<b>" . _("Fehlgeschlagen:") . "</b> " . $query . "�";
		return FALSE;
		} else {
			$this->msg .= "msg�" . sprintf(_("User \"%s\" gel&ouml;scht."), $this->user_data['auth_user_md5.username']) . "�";
		}
		log_event("USER_DEL",$this->user_data['auth_user_md5.user_id'],NULL,sprintf("%s %s (%s)", $this->user_data['auth_user_md5.Vorname'], $this->user_data['auth_user_md5.Nachname'], $this->user_data['auth_user_md5.username'])); //log with Vorname Nachname (username) as info string

		// Can we reach the email?
		if ($this->checkMail($this->user_data['auth_user_md5.Email'])) {
			// include language-specific subject and mailbody
			$Zeit=date("H:i:s, d.m.Y",time());
			include("locale/$user_language/LC_MAILS/delete_mail.inc.php");

			// send mail
			$this->smtp->SendMessage(
					$this->smtp->env_from,
					array($this->user_data['auth_user_md5.Email']),
					array("From: " . $this->smtp->from,
							"Reply-To:" . $this->smtp->abuse,
							"To: " . $this->user_data['auth_user_md5.Email'],
							"Subject: " . $subject),
					$mailbody);

		}

		unset($this->user_data);
		return TRUE;

	}
   function re_sort_position_in_seminar_user()
   {

     $query = "SELECT Seminar_id, position, status FROM seminar_user WHERE user_id='" . $this->user_data['auth_user_md5.user_id'] . "'";
		$this->db->query($query);
		while ($this->db->next_record())
      {
          if ($this->db->f("status") == 'tutor')
          {
            re_sort_tutoren($this->db->f("Seminar_id"), $this->db->f("position"));
          }
          else if ($this->db->f("status") == 'dozent')
          {
            re_sort_dozenten($this->db->f("Seminar_id"), $this->db->f("position"));
          }
		}
   }

}
?>
