<?
/**
* ResourcesUserRoomsList.class.php
* 
* container for a list of rooms a user has rights for
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		AssignEvent.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesUserRoomsList.class.php
// Containerklasse, die eine Liste der Raeume, auf die ein User Zugriff hat, enthaelt
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");


/*****************************************************************************
ResourcesUserRoomsList, creates a list for all resources for one user
/*****************************************************************************/

class ResourcesUserRoomsList {
	var $user_id;    	// userId from PhpLib (String)
	var $resources;		// the results
	var $return_objects;	// should the complete objects be returned?
	var $only_rooms;	// we can do this stuff for rooms ar for all resources
	
	// Konstruktor
	function ResourcesUserRoomsList ($user_id ='', $sort= TRUE, $return_objects = TRUE, $only_rooms = TRUE) {
	 	global $RELATIVE_PATH_RESOURCES, $user;
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");

		$this->user_id = $user_id;
		if (!$this->user_id)
			$this->user_id = $user->id;
		$this->global_perms = GetGlobalPerms($this->user_id);
		$this->return_objects = $return_objects;
		$this->only_rooms = $only_rooms;
		//$this->category_id = $category_id;
		$this->restore();
		
		if($sort)
			$this->sort();
	}
	
	//public
	function setReturnObjects ($value) {
		$this->return_objects = $value;
	}
	
	//private
	function walkThread ($resource_list) {
		
		$db = new DB_Seminar;	
		
		$clause = " ('" . join("','", $resource_list) . "') "; 
		$query = sprintf ("SELECT is_room,resource_id, lockable, resources_objects.name FROM resources_objects  LEFT JOIN resources_categories USING (category_id) WHERE  parent_id IN %s ", $clause);
		$db->query($query);
		while($db->next_record()){
			if (!$this->only_rooms || ($this->only_rooms && $db->f("is_room"))){
				$this->insertResource($db->f("resource_id"), $db->f("name"), $db->f("lockable"));
			}
			$check_childs[] = $db->f("resource_id");
		}
		if (is_array($check_childs)){
			$this->walkThread($check_childs);
		}
	}
	
	function insertResource($resource_id, $name, $lockable = false){
		if  (!$lockable || ($lockable && !isLockPeriod(time()))) {
			if ($this->return_objects) {
				$this->resources[$resource_id] =& ResourceObject::Factory($resource_id);
			} else {
				$this->resources[$resource_id] = $name;
			}
		}
	}
	
	// private
	function restore() {
		global $perm, $user;
		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		
		//if perm is root or resources admin, load all rooms/objects
		if (($perm->have_perm ("root")) || ($this->global_perms == "admin")) { //hier muss auch admin rein!! {
			if ($this->only_rooms)
				$query = sprintf ("SELECT resource_id, resources_objects.name FROM resources_categories LEFT JOIN resources_objects USING (category_id) WHERE resources_categories.is_room = '1' ORDER BY resources_objects.name");
			else
				$query = sprintf ("SELECT resource_id, resources_objects.name FROM resources_objects ORDER BY resources_objects.name");			
			$db->query($query);
			while ($db->next_record()) {
				$this->insertResource($db->f("resource_id"),$db->f("name"));
			}
		//if tutor, dozent or admin, load all the rooms of all his administrable objects
		} elseif  ($perm->have_perm ("tutor")) {
			$my_objects=search_administrable_objects();
			$my_objects[$this->user_id]=TRUE;
			$my_objects["all"]=TRUE;
			if (is_array($my_objects) && count($my_objects)){
				$clause = " ('" . join("','", array_keys($my_objects)) . "') ";

				$query = sprintf ("SELECT is_room,resource_id, resources_objects.name,lockable FROM resources_objects LEFT JOIN resources_categories  USING (category_id) WHERE  owner_id IN %s ",$clause);
				$db->query($query);
				while ($db->next_record()) {
					if (!$this->only_rooms || ($this->only_rooms && $db->f("is_room"))){
						$this->insertResource($db->f("resource_id"), $db->f("name"), $db->f("lockable"));
					}
					$my_resources[$db->f("resource_id")] = true;
				}
				$query = sprintf ("SELECT is_room,resources_user_resources.resource_id, resources_objects.name,lockable FROM resources_user_resources INNER JOIN resources_objects USING(resource_id) LEFT JOIN resources_categories USING (category_id) WHERE resources_user_resources.user_id IN %s ",$clause);
				$db->query($query);
				while ($db->next_record()) {
					if (!isset($my_resources[$db->f("resource_id")])){
						if (!$this->only_rooms || ($this->only_rooms && $db->f("is_room"))){
							$this->insertResource($db->f("resource_id"), $db->f("name"), $db->f("lockable"));
						}
						$my_resources[$db->f("resource_id")] = true;
					}
				}
				if (is_array($my_resources)){
					$this->walkThread(array_keys($my_resources));
				}
			}
		}
		/*
		if (!$perm->have_perm("admin")) {
			$query = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $this->user_id);
			$db->query($query);

			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
			$query = sprintf ("SELECT resource_id FROM resources_user_resources WHERE user_id = '%s' ", $this->user_id);
			$db->query($query2);
			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
		}
		*/
	}
	
	function getRooms() {
		return ($this->resources);
	}
	
	//public
	function numberOfRooms() {
		return sizeof($this->resources);
	}
	
	//public
	function roomsExist() {
		return sizeof($this->resources) > 0 ? TRUE : FALSE;
	}
	
	//public
	function next() {
		if (is_array($this->resources))
			if(list($id,$name) = each($this->resources))
				return array("name" => $name, "resource_id" => $id);
		return FALSE;
	}

	//public
	function reset() {
		if (is_array($this->resources))
			reset($this->resources);
	}
	
	
	function sort(){
		if ($this->resources) 
			if ($return_objects)
				usort($this->resources,"cmp_resources");
			else
				asort ($this->resources, SORT_STRING);
	}
} 
