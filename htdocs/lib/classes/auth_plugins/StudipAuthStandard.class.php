<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipAuthStandard.class.php
// Basic Stud.IP authentication, using the Stud.IP database
// 
// Copyright (c) 2003 Andr� Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
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

require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/auth_plugins/StudipAuthAbstract.class.php");
require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/dbviews/core.view.php");

/**
* Basic Stud.IP authentication, using the Stud.IP database
*
* Basic Stud.IP authentication, using the Stud.IP database 
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipAuthStandard extends StudipAuthAbstract {
	
	/**
	* indicates whether login form should use md5 challenge response auth
	*
	* this should only be true, if password is stored and accessible as md5 hash !
	*
	* @access	public
	* @var		bool
	*/
	var $md5_challenge_response = true;
	
	
	/**
	* Constructor
	*
	* 
	* @access public
	* 
	*/
	function StudipAuthStandard() {
		//calling the baseclass constructor
		parent::StudipAuthAbstract();
	}
	
	/**
	* 
	*
	* 
	* @access public
	* 
	*/
	function authenticateUser($username, $password, $jscript){
		$this->dbv->params[] = $username;
		$db = $this->dbv->get_query("view:AUTH_USER_UNAME");
		if (!$db->next_record()){
			$this->error_msg= _("Dieser Username existiert nicht!") ;
			return false;
		} elseif ($db->f("username") != $username) {
			$this->error_msg = _("Bitte achten Sie auf korrekte Gro&szlig;-Kleinschreibung beim Username!");
			return false;
		} elseif ($db->f("auth_plugin")){
			$this->error_msg = sprintf(_("Dieser Username wird bereits �ber %s authentifiziert!"),$db->f("auth_plugin")) ;
			return false;
		} else {
			$uid   = $db->f("user_id");
			$pass  = $db->f("password");   // Password is stored as a md5 hash
		}
		$expected_response = md5("$username:$pass:" . $this->challenge);
		// JS is disabled
		if (!$jscript || !$this->challenge) {
			if (md5($password) != $pass) {       // md5 hash for non-JavaScript browsers
				$this->error_msg= _("Das Passwort ist falsch!") ;
				return false;
			} else {
				return $uid;
			}
		} elseif ($this->challenge) {
			if ($expected_response != $password) {
				$this->error_msg= _("Das Passwort ist falsch!") ;
				return false;
			} else {
				return $uid;
			}
		}
		$this->error_msg = _("Unbekannter Fehler!");
		return false;
	}
	
	function isUsedUsername($username){
		$this->dbv->params[] = $username;
		$db = $this->dbv->get_query("view:AUTH_USER_UNAME");
		if (!$db->next_record()){
			$this->error_msg = _("Der Username wurde nicht gefunden.");
			return false;
		} else {
			return true;
		}
	}
	
}
?>
