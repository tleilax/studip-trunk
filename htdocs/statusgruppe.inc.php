<?
/**
* helper functions for handling statusgruppen 
* 
* helper functions for handling statusgruppen 
*
* @author				Ralf Stockmann <rstockm@gwdg.de>
* @version			$Id$
* @access				public
* @package			studip_core
* @modulegroup	library
* @module				statusgruppe.inc.php
*/
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
function MakeUniqueStatusgruppeID () {
	// baut eine ID die es noch nicht gibt

	$hash_secret = "kertoiisdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));

	$db->query ("SELECT statusgruppe_id FROM statusgruppen WHERE statusgruppe_id = '$tmp_id'");	
	IF ($db->next_record()) 	
		$tmp_id = MakeUniqueStatusgruppeID(); //ID gibt es schon, also noch mal
	RETURN $tmp_id;
}


// Funktionen zum veraendern der Gruppen

function AddNewStatusgruppe ($new_statusgruppe_name, $range_id, $new_statusgruppe_size, $new_selfassign="0") {

	$statusgruppe_id = MakeUniqueStatusgruppeID();
	$mkdate = time();
	$chdate = time();
	$db=new DB_Seminar;
	$db->query ("SELECT position FROM statusgruppen WHERE range_id = '$range_id' ORDER BY position DESC");
	if ($db->next_record()) {
		$position = $db->f("position")+1;
	} else {
		$position = "1";
	}
	$db->query("INSERT INTO statusgruppen SET statusgruppe_id = '$statusgruppe_id', name = '$new_statusgruppe_name', range_id= '$range_id', position='$position', size = '$new_statusgruppe_size', selfassign = '$new_selfassign', mkdate = '$mkdate', chdate = '$chdate'");
	return $statusgruppe_id;	
} 

function CheckSelfAssign ($statusgruppe_id) {
	$db=new DB_Seminar;
	$db->query ("SELECT selfassign FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id' AND selfassign='1'");
	if ($db->next_record()) {
		$tmp = TRUE;
	} else {
		$tmp = FALSE;
	}
	return $tmp;		
}

function CheckAssignRights($statusgruppe_id, $user_id) {
	global $perm;
	if (CheckSelfAssign($statusgruppe_id) && !CheckUserStatusgruppe($statusgruppe_id, $user_id) && !$perm->have_perm("admin")) 
		$assign = TRUE;
	else
		$assign = FALSE;
	return $assign;	
}

function SetSelfAssign ($statusgruppe_id, $flag="0") {
	$db=new DB_Seminar;
	$db->query("UPDATE statusgruppen SET selfassign = '$flag' WHERE statusgruppe_id = '$statusgruppe_id'");
}

function GetAllSelected ($range_id) {	
	$zugeordnet[] = "";
  	$db3=new DB_Seminar;
	$db3->query ("SELECT DISTINCT user_id FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE range_id = '$range_id'");
	while ($db3->next_record()) {
		if (!in_array($db3->f("user_id"), $zugeordnet)) {
			$zugeordnet[] = $db3->f("user_id");
		}
	}
	return $zugeordnet;
}

function EditStatusgruppe ($new_statusgruppe_name, $new_statusgruppe_size, $edit_id, $new_selfassign="0") {

	$chdate = time();
	$db=new DB_Seminar;
	$db->query("UPDATE statusgruppen SET name = '$new_statusgruppe_name', size = '$new_statusgruppe_size', chdate = '$chdate', selfassign = '$new_selfassign' WHERE statusgruppe_id = '$edit_id'");
}

function InsertPersonStatusgruppe ($user_id, $statusgruppe_id) {
	$position = CountMembersPerStatusgruppe($statusgruppe_id)+1;
	$db=new DB_Seminar;
	$db->query("SELECT * FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
	if (!$db->next_record()) {			
		$db->query("INSERT INTO statusgruppe_user SET statusgruppe_id = '$statusgruppe_id', user_id = '$user_id', position = '$position'");
		$writedone = TRUE;
	} else {
		$writedone = FALSE;
	}
	return $writedone;
}

function RemovePersonStatusgruppe ($username, $statusgruppe_id) {

	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db->query("SELECT position FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
	if ($db->next_record())
		$position = $db->f("position");
	$db->query("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
	
	// Neusortierung
	$db->query("SELECT * FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND position > '$position'");
	while ($db->next_record()) {
		$new_position = $db->f("position")-1;
		$alt_user_id = $db->f("user_id");
		$db2=new DB_Seminar;
		$db2->query("UPDATE statusgruppe_user SET position =  '$new_position' WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$alt_user_id'");
	}
}

function RemovePersonFromAllStatusgruppen ($username) {

	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db->query("DELETE FROM statusgruppe_user WHERE user_id = '$user_id'");
	$result = $db->affected_rows();
	return $result;
}

function RemovePersonStatusgruppeComplete ($username, $range_id) {

	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db->query("SELECT DISTINCT statusgruppe_user.statusgruppe_id FROM statusgruppe_user LEFT JOIN statusgruppen USING(statusgruppe_id) WHERE range_id = '$range_id' AND user_id = '$user_id'");	
	while ($db->next_record()) {
		RemovePersonStatusgruppe($username, $db->f("statusgruppe_id"));
	}
}

function DeleteStatusgruppe ($statusgruppe_id) {

	$db=new DB_Seminar;
	$db->query("SELECT position, range_id FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id'");
	if ($db->next_record()) {
		$position = $db->f("position");
		$range_id = $db->f("range_id");
	}
	$db=new DB_Seminar;
	$db->query("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id'");
	$db->query("DELETE FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id'");

	// Neusortierung
		
	$db->query("SELECT * FROM statusgruppen WHERE range_id = '$range_id' AND position > '$position'");
	while ($db->next_record()) {
		$new_position = $db->f("position")-1;
		$statusgruppe_id = $db->f("statusgruppe_id");
		$db2=new DB_Seminar;
		$db2->query("UPDATE statusgruppen SET position =  '$new_position' WHERE statusgruppe_id = '$statusgruppe_id'");
	}
}

function MovePersonPosition ($username, $statusgruppe_id, $direction) {
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db->query("SELECT position FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
	if ($db->next_record()) {
		if ($direction == "up")
			$position = $db->f("position")-1;
		if ($direction == "down")
			$position = $db->f("position")+1;
		$position_alt = $db->f("position");
		$db->query("UPDATE statusgruppe_user SET position =  '$position_alt' WHERE statusgruppe_id = '$statusgruppe_id' AND position = '$position'");
		$db->query("UPDATE statusgruppe_user SET position =  '$position' WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
	}
}

function DeleteAllStatusgruppen ($range_id) {

	$db=new DB_Seminar;
	$i = 0;
	$db->query("SELECT statusgruppe_id FROM statusgruppen WHERE range_id = '$range_id'");
	while ($db->next_record()) {
		$statusgruppe_id = $db->f("statusgruppe_id");
		DeleteStatusgruppe($statusgruppe_id);
		$i++;
	}
	return $i;
}

function SwapStatusgruppe ($statusgruppe_id) {

	$db=new DB_Seminar;
	$db->query("SELECT * FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id'");
	if ($db->next_record()) {
		$current_position = $db->f("position");
		$range_id = $db->f("range_id");
		$next_position = $current_position + 1;
		$db2=new DB_Seminar;
		$db2->query("UPDATE statusgruppen SET position =  '$next_position' WHERE statusgruppe_id = '$statusgruppe_id'");
		$db2->query("UPDATE statusgruppen SET position =  '$current_position' WHERE range_id = '$range_id' AND position = '$next_position' AND statusgruppe_id != '$statusgruppe_id'");
	}
}

function CheckStatusgruppe ($range_id, $name) {

	$db=new DB_Seminar;
	$db->query("SELECT * FROM statusgruppen WHERE range_id = '$range_id' AND name = '$name'");
	if ($db->next_record()) {
		$exists = $db->f("statusgruppe_id");
	} else {
		$exists = FALSE;
	}
	return $exists;
}

function CheckUserStatusgruppe ($group_id, $object_id) {
		$db=new DB_Seminar;
		$db->query("SELECT * FROM statusgruppe_user WHERE statusgruppe_id = '$group_id' AND user_id = '$object_id'");
		if ($db->next_record()) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
function GetRangeOfStatusgruppe ($statusgruppe_id) {
	$db=new DB_Seminar;
	$db->query("SELECT range_id FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id'");
	if ($db->next_record()) {
		$range = $db->f("range_id");
	} else {
		$range = FALSE;
	}
	return $range;
}


/**
* get all statusgruppen for one user and one range
*
* get all statusgruppen for one user and one range
*
* @access	public
* @param	string	$range_id
* @param	string	$user_id
* @return	array	(structure statusgruppe_id => name)
*/
function GetStatusgruppen ($range_id, $user_id) {
	$db = new DB_Seminar();
	$db->query("SELECT a.statusgruppe_id,a.name FROM statusgruppen a 
				LEFT JOIN statusgruppe_user b USING(statusgruppe_id) WHERE user_id='$user_id' AND range_id='$range_id'");
	while ($db->next_record()) {
		$ret[$db->f("statusgruppe_id")] = $db->f("name");
	}
	return (is_array($ret)) ? $ret : FALSE;
}

/**
* Returns the number of persons who are grouped in Statusgruppen for one range.
* 
* Persons who are members in more than one Statusgruppe will be count only once
*
* @access public
* @param string $range_id The ID of a range with Statusgruppen
* @return int The number of members
*/
function CountMembersStatusgruppen ($range_id) {
	$db = new DB_Seminar();
	$db->query("SELECT COUNT(DISTINCT user_id) AS count FROM statusgruppen
							LEFT JOIN statusgruppe_user USING(statusgruppe_id)
							WHERE range_id = '$range_id'");
	$db->next_record();
	return $db->f("count");
}

function CountMembersPerStatusgruppe ($group_id) {
	$db = new DB_Seminar();
	$db->query("SELECT COUNT(user_id) AS count FROM statusgruppen
							LEFT JOIN statusgruppe_user USING(statusgruppe_id)
							WHERE statusgruppen.statusgruppe_id = '$group_id'");
	$db->next_record();
	return $db->f("count");
}


/**
* Returns all statusgruppen for the given range.
*
* If there is no statusgruppe for the given range, it returns FALSE.
*
* @access	public
* @param	string	$range_id
* @param	string	$user_id
* @return	array	(structure statusgruppe_id => name)
*/
function GetAllStatusgruppen ($range_id) {
	$ret = "";
	$db = new DB_Seminar();
	$db->query("SELECT statusgruppe_id, name FROM statusgruppen
							WHERE range_id='$range_id' ORDER BY position ASC");
	while ($db->next_record()) {
		$ret[$db->f("statusgruppe_id")] = $db->f("name");
	}
	return (is_array($ret)) ? $ret : FALSE;
}


function GetStatusgruppeName ($group_id) {
	$db = new DB_Seminar();
	$db->query("SELECT name FROM statusgruppen WHERE statusgruppe_id='$group_id' ");
	
	if ($db->next_record())
		return $db->f("name");
	else
		return FALSE;
}
?>
