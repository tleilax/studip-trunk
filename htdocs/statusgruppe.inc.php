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
function MakeUniqueID ()
{	// baut eine ID die es noch nicht gibt

	$hash_secret = "kertoiisdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));

	$db->query ("SELECT statusgruppe_id FROM statusgruppen WHERE statusgruppe_id = '$tmp_id'");	
	IF ($db->next_record()) 	
		$tmp_id = MakeUniqueID(); //ID gibt es schon, also noch mal
	RETURN $tmp_id;
}


// Funktionen zum veraendern der Gruppen

function AddNewStatusgruppe ($new_statusgruppe_name, $range_id, $new_statusgruppe_size)
{
	$statusgruppe_id = MakeUniqueID();
	$mkdate = time();
	$chdate = time();
	$db=new DB_Seminar;
	$db->query ("SELECT position FROM statusgruppen WHERE range_id = '$range_id' ORDER BY position DESC");
	if ($db->next_record()) {
		$position = $db->f("position")+1;
	} else {
		$position = "1";
	}
	$db->query("INSERT INTO statusgruppen SET statusgruppe_id = '$statusgruppe_id', name = '$new_statusgruppe_name', range_id= '$range_id', position='$position', size = '$new_statusgruppe_size', mkdate = '$mkdate', chdate = '$chdate'");
	return $statusgruppe_id;	
} 

function GetAllSelected ($range_id)
{	
	$zugeordnet[] = "";
  	$db3=new DB_Seminar;
	$db3->query ("SELECT DISTINCT user_id FROM statusgruppe_user LEFT JOIN statusgruppen USING(statusgruppe_id) WHERE range_id = '$range_id'");
	while ($db3->next_record()) {
		if (!in_array($db3->f("user_id"), $zugeordnet)) {
			$zugeordnet[] = $db3->f("user_id");
		}
	}
	RETURN $zugeordnet;
}

function EditStatusgruppe ($new_statusgruppe_name, $new_statusgruppe_size, $edit_id)
{
	$chdate = time();
	$db=new DB_Seminar;
	$db->query("UPDATE statusgruppen SET name = '$new_statusgruppe_name', size = '$new_statusgruppe_size', chdate = '$chdate' WHERE statusgruppe_id = '$edit_id'");
}

function InsertPersonStatusgruppe ($user_id, $statusgruppe_id)
{
	$db=new DB_Seminar;
	$db->query("SELECT * FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
	if (!$db->next_record()) {			
		$db->query("INSERT INTO statusgruppe_user SET statusgruppe_id = '$statusgruppe_id', user_id = '$user_id'");
		$writedone = TRUE;
	} else {
		$writedone = FALSE;
	}
	return $writedone;
}

function RemovePersonStatusgruppe ($username, $statusgruppe_id)
{
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db->query("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
}

function RemovePersonStatusgruppeComplete ($username, $range_id)
{
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$db->query("SELECT DISTINCT statusgruppe_user.statusgruppe_id FROM statusgruppe_user LEFT JOIN statusgruppen USING(statusgruppe_id) WHERE range_id = '$range_id' AND user_id = '$user_id'");	
	while ($db->next_record()) {
		RemovePersonStatusgruppe($username, $db->f("statusgruppe_id"));
	}
}

function DeleteStatusgruppe ($statusgruppe_id)
{
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

function DeleteAllStatusgruppen ($range_id)
{
	$db=new DB_Seminar;
	$db->query("SELECT statusgruppe_id FROM statusgruppen WHERE range_id = '$range_id'");
	while ($db->next_record()) {
		$statusgruppe_id = $db->f("statusgruppe_id");
		DeleteStatusgruppe($statusgruppe_id);
	}
}

function SwapStatusgruppe ($statusgruppe_id)
{
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

function CheckStatusgruppe ($range_id, $name)
{
	$db=new DB_Seminar;
	$db->query("SELECT * FROM statusgruppen WHERE range_id = '$range_id' AND name = '$name'");
	if ($db->next_record()) {
		$exists = $db->f("statusgruppe_id");
	} else {
		$exists = FALSE;
	}
	return $exists;
}
?>