<?
/**
* resourcesFunc.php
* 
* functions for resources
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	resources
* @module		resourcesFunc.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// resourcesFunc.php
// Funktionen der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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


/*
* allowCreateRooms
*
* gets the status, if an user is allowed to create new room(objects)
*
* @param	string	the user_id, if not set, the actual user's id is used
* @return	boolean	
*
**/
function allowCreateRooms($user_id='') {
	global $user, $perm;
	
	if (!$user_id)
		$user_id = $user->id;
	
	switch (get_config("RESOURCES_ALLOW_CREATE_ROOMS")) {
		case 1:
			if ($perm->have_perm("tutor"))
				return TRUE;
			else
				return FALSE;
		break;
		case 2:
			if ($perm->have_perm("admin"))
				return TRUE;
			else
				return FALSE;
		break;
		case 3:
			if (getGlobalPerms($user_id) == ("admin"))
				return TRUE;
			else
				return FALSE;
		break;

	}
}

/*
* getLockPeriod
*
* gets a lock-period, if one is active (only the first lock period that matches will be returned)
*
* @param	int	the timestamp, if left, the actual time
* @return	array	the start- and end-timestamp
*
**/
function getLockPeriod($timestamp='') {
	static $cache;

	if ($cache[$timestamp % 60]) {
		return $cache[$timestamp % 60];
	}

	$db = new DB_Seminar;
	
	if (!$timestamp)
		$timestamp = time();
	
	if (!$GLOBALS['RESOURCES_LOCKING_ACTIVE']) {
		$cache[$timestamp % 60] = FALSE;	
		return FALSE;
	} else {
		$query = sprintf ("SELECT lock_begin, lock_end FROM resources_locks WHERE lock_begin <= '%s' AND lock_end >= '%s' ", $timestamp, $timestamp);
		$db->query($query);
		$db->next_record();
		if ($db->nf()) {
			$arr[0] = $db->f("lock_begin");
			$arr[1] = $db->f("lock_end");
			$cache[$timestamp % 60] = $arr;			
			return $arr;
		} else {
			$cache[$timestamp % 60] = FALSE;			
			return FALSE;
		}
	}
}

/**
* isLockPeriod
*
* determines, if a lock period could be found in resources_locks and locking is active
*
* @param	int	the timestamp, if left, the actual time
* @return	boolean	true or false
*
**/
function isLockPeriod($timestamp='') {
	static $cache;
	
	if ($cache[$timestamp % 60]) {
		return $cache[$timestamp % 60];
	}
	
	$db = new DB_Seminar;
	
	if (!$timestamp)
		$timestamp = time();
	
	if (!$GLOBALS['RESOURCES_LOCKING_ACTIVE']) {
		$cache[$timestamp % 60] = FALSE;
		return FALSE;
	} else {
		$query = sprintf ("SELECT * FROM resources_locks WHERE lock_begin <= '%s' AND lock_end >= '%s' ", $timestamp, $timestamp);
		$db->query($query);
		if ($db->nf()) {
			$cache[$timestamp % 60] = TRUE;
			return TRUE;
		} else {
			$cache[$timestamp % 60] = FALSE;
			return FALSE;
		}
	}
}

/**
* changeLockableRecursiv
*
* sets the lockale option for all childs to state
*
* @param	string	the key for the resource object
* @param	boolean	if set, all childs will be lockable
*
**/
function changeLockableRecursiv ($resource_id, $state) {
	global $resources_data;
	$db = new DB_Seminar;

	$query = sprintf ("UPDATE resources_objects SET lockable = '%s' WHERE resource_id = '%s' ", $state, $resource_id);
	$db->query($query);

	$query = sprintf ("SELECT resource_id FROM resources_objects WHERE parent_id = '%s' ", $resource_id);
	$db->query($query);
	while ($db->next_record()) {
		changeLockableRecursiv ($db->f("resource_id"), $state);
	}
}


/*
* getGlobalPerms
*
* this Funktion get the globals perms, the given user has in the 
* resources-management
*
* @param	string	the user_id
* @return	string	the perms-string	
*
**/
function getGlobalPerms ($user_id) {
	static $cache;
	global $perm;
	
	if ($cache[$user_id])
		return $cache[$user_id];
	
	$db = new DB_Seminar;
	
	if (!$perm->have_perm("root")) {
		$db->query("SELECT user_id, perms FROM resources_user_resources WHERE user_id='$user_id' AND resource_id = 'all' ");
		if ($db->next_record() && $db->f("perms")) 
			$res_perm = $db->f("perms");
		else
			$res_perm = "autor";
	} else
		$res_perm = "admin";

	$cache[$user_id] = $res_perm;
	return $res_perm;
}


/*****************************************************************************
a quick function to get the resource_id (only rooms!) for a assigned date
/*****************************************************************************/

function getDateAssigenedRoom($date_id){
	$db=new DB_Seminar;
	$query = sprintf ("SELECT resources_assign.resource_id FROM resources_assign LEFT JOIN resources_objects USING (resource_id) LEFT JOIN resources_categories USING (category_id) WHERE assign_user_id = '%s' AND resources_categories.is_room = 1 ", $date_id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("resource_id");
	else
		return FALSE;
}

/*****************************************************************************
a quick function to get a name from a resources object
/*****************************************************************************/

function getResourceObjectName($id){
	$db=new DB_Seminar;
	$query = sprintf ("SELECT name FROM resources_objects WHERE resource_id = '%s'", $id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("name");
	else
		return FALSE;
}

/*****************************************************************************
a quick function to get a category from a resources object
/*****************************************************************************/

function getResourceObjectCategory($id){
	$db=new DB_Seminar;
	$query = sprintf ("SELECT category_id FROM resources_objects WHERE resource_id = '%s'", $id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("category_id");
	else
		return FALSE;
}

function getDateRoomRequest($termin_id) {
	$db=new DB_Seminar;
	$query = sprintf("SELECT request_id FROM resources_requests WHERE termin_id = '%s' ",$termin_id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("request_id");
	else
		return FALSE;
}
	
function getSeminarRoomRequest($seminar_id) {
	$db=new DB_Seminar;
	$query = sprintf("SELECT request_id FROM resources_requests WHERE seminar_id = '%s' ",$seminar_id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("request_id");
	else
		return FALSE;
}


function getMyRoomRequests($user_id = '') {
	global $user, $perm, $RELATIVE_PATH_RESOURCES;

	require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
	
	$db = new DB_Seminar;
	$db2 = new DB_Seminar;

	if (!$user_id)
		$user_id = $user->id;
		
	if ((getGlobalPerms($user_id) == "admin") && ($perm->have_perm("root"))) {
		$query = sprintf("SELECT request_id, closed FROM resources_requests");
		$db->query($query);
		while ($db->next_record()) {
			$requests [$db->f("request_id")] = array("my_sem"=>TRUE, "my_res"=>TRUE, "closed"=>$db->f("closed"));
		}
	} else {
		//load all my resources
		$resList = new ResourcesUserRoomsList($user_id, FALSE, FALSE);
		$my_res = $resList->getRooms();
	
		//load all my seminars
		$my_sems = search_my_administrable_seminars();
		
		$in_resource_id =  "('".join("','",array_keys($my_res))."')";
		$in_seminar_id =  "('".join("','",array_keys($my_sems))."')";
		
		$query_sem = sprintf("SELECT request_id, closed FROM resources_request WHERE seminar_id IN %s", $in_seminar_id, $in_resource_id);
		$query_res = sprintf("SELECT request_id, closed FROM resources_request WHERE resource_id IN %s", $in_seminar_id, $in_resource_id);
		$db->query($query_sem);
		while ($db->next_record()) {
			$requests [$db->f("request_id")]["my_sem"] = TRUE;
			$requests [$db->f("request_id")]["closed"] = $db->f("closed");
		}
		$db2->query($query_res);
		while ($db2->next_record()) {
			$requests [$db2->f("request_id")]["my_res"] = TRUE;
			$requests [$db2->f("request_id")]["closed"] = $db2->f("closed");
		}
	}
	
	return $requests;
}


/*****************************************************************************
sort function to sort the AssignEvents by date
/*****************************************************************************/

function cmp_assign_events($a, $b){
	$start_a = $a->getBegin();
	$start_b = $b->getBegin();
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

/*****************************************************************************
sort function to sort the ResourceObject by name
/*****************************************************************************/
function cmp_resources($a, $b){
	$name_a = $a->getName();
	$name_b = $b->getName();
	if($name_a == $name_b)
		return 0;
	if($name_a < $name_b)
		return -1;
	return 1;
}


/*
* checkAvailableResources
*
* This Funktion searches for available resources for studip-objects (and users, too), 
* but it only work's properly with studip-objects, because it didn't pay attention for 
* inheritance of perms for a studip-user.
*
* @param	string	the obejct id
* @return 	boolean true, if resources are found, otherwise false
*
**/
function checkAvailableResources($id) { 
	$db = new DB_Seminar;
	
	//check if owner
	$db->query("SELECT COUNT(owner_id) AS count FROM resources_objects WHERE owner_id='$id' ");
	if ($count)
		return TRUE;
	
	//or additional perms avaiable
	$db->query("SELECT COUNT(perms) AS count FROM resources_user_resources  WHERE user_id='$id' ");
	if ($count)
		return TRUE;
	
	return FALSE;	
}

/*****************************************************************************
checkAssigns, a quick function to check if for a ressource
exists assigns
/*****************************************************************************/

function checkAssigns($id) {
	$db = new DB_Seminar;
	
	$db->query("SELECT COUNT(assign_id) AS count FROM resources_assign WHERE resource_id='$id' ");
	if ($count)
		return TRUE;
	return FALSE;	
}

/*****************************************************************************
checkObjektAdminstrablePerms checks, if I have the chance to change
the owner of the given object
/*****************************************************************************/

function checkObjektAdministrablePerms ($resource_object_owner_id, $user_id='') {
	global $user, $perm, $my_perms;

	if (!$user_id)
		$user_id = $user->id;
	
	//for root, it's quick!
	if ($perm->have_perm("root"))
		return TRUE;
	
	//for the resources admin too
	if ($my_perms ->getGlobalPerms() == "admin")
		return TRUE;
	
	//load all my administrable objects
	$my_objects=search_administrable_objects ($search_string='_');
	
	//ok, we as a user aren't interesting...
	unset ($my_objects[$user_id]);
	if (sizeof ($my_objects)) {
		if (($my_objects[$resource_object_owner_id]["perms"] == "admin") || ($resource_object_owner_id == $user_id)) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else
		return FALSE;
}
/*
* search_administrable_seminars
*
* this Funktion searches all my aministrable seminars
*
* @param	string	a search string, that could be used
* @param	string	the user_id
* @return 	array	result
*
**/
function search_administrable_seminars ($search_string='', $user_id='') {
	global $user, $perm, $auth;

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$db3 = new DB_Seminar;
	
	if (!$user_id)
		$user_id = $user->id;
		
	if (!$search_string)
		$search_string = "_";

	$user_global_perm=get_global_perm($this->user_id);
	switch ($user_global_perm) {
		case "root": 
			//Alle Seminare...
			$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$search_string%' OR Untertitel = '%$search_string%' OR Seminar_id = '$search_string' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
		break;
		case "admin": 
			//Alle meine Institute (unabhaengig von Suche fuer Rechte)...
			$db->query("SELECT Institute.Institut_id, Name, inst_perms FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE inst_perms = 'admin' AND user_inst.user_id='$user_id' ");
			while ($db->next_record()) {
				//...alle Seminare meiner Institute, in denen ich Admin bin....
				$db2->query("SELECT seminare.Seminar_id, Name FROM seminar_inst LEFT JOIN seminare USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_inst.institut_id = '".$db->f("Institut_id")."' ORDER BY Name");
				while ($db2->next_record()) {
					$my_objects[$db2->f("Seminar_id")]=array("name"=>$db2->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
				}
			}
		break;
		case "dozent": 
		case "tutor":
			//Alle meine Seminare
			$db->query("SELECT seminare.Seminar_id, Name FROM seminar_user LEFT JOIN seminare USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_user.status IN ('tutor', 'dozent')  AND seminar_user.user_id='$user_id' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
		break;
	}
	return $my_objects;
}


/*
* search_administrable_objects
*
* this Funktion searches all my aministrable objects (the object i've got tutor 
* or better perms, so I'am able to administrate most of the things).
*
* @param	string	a search string, that could be used
* @param	string	the user_id
* @param	boolean	should seminars searched`?
* @return 	array
*
**/
function search_administrable_objects ($search_string='', $user_id='', $sem=TRUE) {
	global $user, $perm, $auth, $_fullname_sql;

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$db3 = new DB_Seminar;
	
	if (!$user_id)
		$user_id = $user->id;
		
	if (!$search_string)
		$search_string = "_";

	if (getGlobalPerms($user_id) == "admin") 
		$my_objects["global"]=array("name"=>_("Global"), "perms" => "admin");
		
	$user_global_perm=get_global_perm($this->user_id);
	switch ($user_global_perm) {
		case "root": 
			//Alle Personen...
			$db->query("SELECT a.user_id,". $_fullname_sql['full_rev'] ." AS fullname , username FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR a.user_id = '$search_string' ORDER BY Nachname");
			while ($db->next_record())
					$my_objects[$db->f("user_id")]=array("name"=>$db->f("fullname")." (".$db->f("username").")", "art"=>_("Personen"), "perms" => "admin");
			//Alle Seminare...
			if ($sem) {
				$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$search_string%' OR Untertitel = '%$search_string%' OR Seminar_id = '$search_string' ORDER BY Name");
				while ($db->next_record())
					$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
			}
			//Alle Institute...
			$db->query("SELECT Institut_id, Name FROM Institute WHERE Name LIKE '%$search_string%' OR Institut_id = '$search_string' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => "admin");
		break;
		case "admin": 
			//Alle meine Institute (Suche)...
			$db->query("SELECT Institute.Institut_id, Name, inst_perms FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms = 'admin' AND user_inst.user_id='$user_id' ORDER BY Name");
			while ($db->next_record()) {
				$my_objects_inst[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => "admin");
			}
			//Alle meine Institute (unabhaengig von Suche fuer Rechte)...
			$db->query("SELECT Institute.Institut_id, Name, inst_perms FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE inst_perms = 'admin' AND user_inst.user_id='$user_id' ");
			while ($db->next_record()) {
				//...alle Mitarbeiter meiner Institute, in denen ich Admin bin....
				$db2->query ("SELECT auth_user_md5.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, username FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE (username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR auth_user_md5.user_id = '$search_string') AND Institut_id = '".$db->f("Institut_id")."' AND inst_perms IN ('autor', 'tutor', 'dozent') ORDER BY Nachname");
				while ($db2->next_record()) {
					$my_objects_user[$db2->f("user_id")]=array("name"=>$db2->f("fullname")." (".$db2->f("username").")", "art"=>_("Personen"), "perms" => "admin");
				}
				//...alle Seminare meiner Institute, in denen ich Admin bin....
				if ($sem) {
					$db2->query("SELECT seminare.Seminar_id, Name FROM seminar_inst LEFT JOIN seminare USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_inst.institut_id = '".$db->f("Institut_id")."' ORDER BY Name");
					while ($db2->next_record()) {
						$my_objects_sem[$db2->f("Seminar_id")]=array("name"=>$db2->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
					}
				}
			}
			if (is_array ($my_objects_user))
				foreach ($my_objects_user as $key=>$val) {
					$my_objects[$key]=$val;
			}
			if (is_array ($my_objects_sem))
				foreach ($my_objects_sem as $key=>$val) {
					$my_objects[$key]=$val;
			}
			if (is_array ($my_objects_inst))
				foreach ($my_objects_inst as $key=>$val) {
					$my_objects[$key]=$val;
			}
		break;
		case "dozent": 
			//Alle meine Seminare
			if ($sem) {
				$db->query("SELECT seminare.Seminar_id, Name FROM seminar_user LEFT JOIN seminare USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_user.status IN ('tutor', 'dozent')  AND seminar_user.user_id='$user_id' ORDER BY Name");
				while ($db->next_record())
					$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
			}
			//Alle meine Institute...
			$db->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms IN ('tutor', 'dozent')  AND user_inst.user_id='$user_id'  ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => $db->f("inst_perms"));
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>_("Personen"),  "perms" => "admin");
		break;
		case "tutor": 
			//Alle meine Seminare
			if ($sem) {
				$db->query("SELECT seminare.Seminar_id, Name FROM seminar_user LEFT JOIN seminare USING (seminar_id) WHERE  (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_user.status='tutor' AND seminar_user.user_id='$user_id' ORDER BY Name");
				while ($db->next_record())
					$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"),  "perms" => "tutor");
			}
			//Alle meine Institute...
			$db->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (institut_id)  WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms='tutor' AND user_inst.user_id='$user_id' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => "tutor");
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>_("Personen"),  "perms" => "admin");
		break;
		case "autor": 
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>_("Personen"),  "perms" => "admin");
		break;
	}
	return $my_objects;
}

/*
* search_my_objects
*
* this Funktion searches all my objects (only them with autor perms).
* the function works as an addition to the search administrable objects
* function above
*
* @param	string	a search string, that could be used
* @param	string	the user_id
* @param	boolean	should seminars searched`?
* @return 	array
*
**/
function search_my_objects ($search_string='', $user_id='', $sem=TRUE) {
	global $user, $perm, $auth, $_fullname_sql;

	$db = new DB_Seminar;
	
	if (!$user_id)
		$user_id = $user->id;
		
	if (!$search_string)
		$search_string = "_";

	//Alle meine Seminare
	if ($sem) {
		$db->query("SELECT seminare.Seminar_id, Name FROM seminar_user LEFT JOIN seminare USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_user.status = 'autor'  AND seminar_user.user_id='$user_id' ORDER BY Name");
		while ($db->next_record())
			$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "autor");
	}
	
	//Alle meine Institute...
	$db->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms = 'autor' AND user_inst.user_id='$user_id' ORDER BY Name");
	while ($db->next_record())
		$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => "autor");

	return $my_objects;
}


/*****************************************************************************
search_admin_user searches in all the admins
/*****************************************************************************/

function search_admin_user ($search_string='') {
	global $_fullname_sql;
	$db=new DB_Seminar;

	//In allen Admins suchen...
	$db->query("SELECT a.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, username FROM auth_user_md5  a LEFT JOIN user_info USING (user_id) WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR a.user_id = '$search_string' ORDER BY Nachname");
	while ($db->next_record())
			$my_objects[$db->f("user_id")]=array("name"=>$db->f("fullname")." (".$db->f("username").")", "art"=>_("Personen"));
	
	return $my_objects;
}


/*****************************************************************************
search_objects searches in all objects
/*****************************************************************************/

function search_objects ($search_string='', $user_id='', $sem=TRUE) {
	global $user, $perm, $auth, $_fullname_sql;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	
	if (!$user_id)
		$user_id=$user->id;
		
	//Alle Personen...
	$db->query("SELECT a.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, username FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR a.user_id = '$search_string' ORDER BY Nachname");
	while ($db->next_record())
		$my_objects[$db->f("user_id")]=array("name"=>$db->f("fullname")." (".$db->f("username").")", "art"=>_("Personen"));
	//Alle Seminare...
	if ($sem) {
		$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$search_string%' OR Untertitel = '%$search_string%' OR Seminar_id = '$search_string' ORDER BY Name");
		while ($db->next_record())
			$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"));
	}
	//Alle Institute...
	$db->query("SELECT Institut_id, Name FROM Institute WHERE Name LIKE '%$search_string%' OR Institut_id = '$search_string' ORDER BY Name");
	while ($db->next_record())
		$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"));

	return $my_objects;
}


/*****************************************************************************
Searchform, zur Erzeugung der oft gebrauchten Personen-Auswahl
u.a. Felder
/*****************************************************************************/

function showSearchForm($name, $search_string='', $user_only=FALSE, $administrable_objects_only=FALSE, $admins=FALSE, $allow_all=FALSE, $sem=TRUE, $img_dir="left") {

	if ($search_string) {
		if ($user_only) //Nur in Personen suchen
			if ($admins) //nur admins anzeigen
				$my_objects=search_admin_user($search_string);
			else //auch andere...
				;
		elseif ($administrable_objects_only)
			$my_objects=search_administrable_objects($search_string, FALSE, $sem);
		else //komplett in allen Objekten suchen
			$my_objects=search_objects($search_string, FALSE, $sem);
			
		?>
		<input type="HIDDEN" name="<? echo "search_string_".$name ?>" value="<? echo $search_string ?>" />
		<font size=-1><input type="IMAGE" align="absmiddle"  name="<? echo "send_".$name ?>" src="./pictures/move_<?=$img_dir.".gif\" ".tooltip (_("diesen Eintrag �bernehmen")) ?> border="0" value="<?=_("&uuml;bernehmen")?>"  /></font>
		<select align="absmiddle" name="<? echo "submit_".$name ?>">
		<?
		if ($allow_all)
			print "<option style=\"vertical-align: middle;\" value=\"all\">"._("jedeR")."</option>";

		foreach ($my_objects as $key=>$val) {
			if ($val["art"] != $old_art) {
				?>			
			<font size=-1><option value="FALSE"><? echo "-- ".$val["art"]." --"; ?></option></font>
				<?
			}
			?>
			<font size=-1><option value="<? echo $key ?>"><? echo my_substr($val["name"],0,30); ?></option></font>
			<?

			$old_art=$val["art"];
		}
		?></select>
		<font size=-1><input type="IMAGE" align="absmiddle" name="<? echo "reset_".$name ?>" src="./pictures/rewind.gif" <?=tooltip (_("Suche zur�cksetzen")) ?> border="0" value="<?=_("neue Suche")?>" /></font>
		<?
	} else {
		?>
		<font size=-1><input type="TEXT" align="absmiddle" name=" <? echo "search_string_".$name ?>" size=30 maxlength=255 /></font>
		<font size=-1><input type="IMAGE" align="absmiddle" name=" <? echo "do_".$name ?>" src="./pictures/suchen.gif" <?=tooltip (_("Starten Sie hier Ihre Suche")) ?> border=0 value="<?=_("suchen")?>" /></font>
		<?
	}
}
?>