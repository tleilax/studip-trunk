<?
/**
* RoomRequest.class.php
* 
* class for room requests and room-property requests
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		AssignObject.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RoomRequest.class.php
// zentrale Klasse Raumwuensche und Raumeigenschaftswuensche
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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
* RoomRequest, class for room-requests and room-property-requests
*
* @access	public	
* @author	Cornelis Kater <kater@data-quest.de>
* @version	$Id$
* @package	resources
**/
class RoomRequest {
	var $db;					//db-connection
	var $id;					//request-id
	var $seminar_id;				//seminar_id from the assigned seminar
	var $properties = array();			//the assigned property-requests

	//Konstruktor
	function RoomRequest($id='') {
		global $RELATIVE_PATH_RESOURCES, $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;

		if($id) {
			$this->id =$id;
			if (!$this->restore($this->id)) 
				$this->isNewObject = TRUE;
		} else {
			if (!$this->id)
				$this->id=$this->createId();
			$this->isNewObject =TRUE;
		} 	
	}

	function createId() {
		return md5(uniqid("wintergoe"));
	}
	
	function create() {
		$query = sprintf("SELECT assign_id FROM resources_assign WHERE assign_id ='%s' ", $this->id);
		$this->db->query($query);
		if ($this->db->nf()) {
			$this->chng_flag=TRUE;
			return $this->store();
		} else
			return $this->store(TRUE);
	}

	function getId() {
		return $this->id;
	}
	
	function getResourceId() {
		return $this->resource_id;
	}
	
	function getSeminarId() {
		return $this->seminar_id;
	}

	function getTerminId() {
		return $this->termin_id;
	}
	
	function getUserId() {
		return $this->user_id;
	}
	
	function getCategoryId() {
		return $this->category_id;
	}
	
	function getComment() {
		return $this->comment;
	}
	
	function getClosed() {
		return $this->closed;
	}
	
	function getPropertyState($property_id) {
		return $this->properties[$property_id]["state"];
	}

	function getProperties() {
		return $this->properties;
	}
	
	function getAvailableProperties() {
		if ($this->category_id) {
			$query = sprintf("SELECT b.name, b.type, b.system, b.property_id FROM resources_categories_properties a LEFT JOIN resources_properties b USING (property_id) WHERE requestable ='1' AND category_id = '%s' ", $this->category_id);
			$this->db->query($query);
			
			while($this->db->next_record()) {
				$available_properties[$this->db->f("property_id")] = array("name"=>$this->db->f("name"), "type"=>$this->db->f("type"), "system"=>$this->db->f("system"), );
			}
			return $available_properties;
		} else 
			return FALSE;
	}
	
	function getSettedPropertiesCount() {
		foreach ($this->properties as $val) {
			if ($val)
				$count++;
		}
		return $count;
	}
	
	function getSeats() {
		foreach ($this->properties as $val) {
			if ($val["system"] == 2)
				return $val["state"];
		}
		return FALSE;
	}
	
	function isNew() {
		return $this->isNewObject;
	}
	
	function setResourceId($value) {
		$this->resource_id=$value;
		$this->chng_flag=TRUE;
	}

	function setUserId($value) {
		$this->user_id=$value;
		$this->chng_flag=TRUE;
	}
	
	function setSeminarId($value) {
		$this->seminar_id=$value;
		$this->chng_flag=TRUE;
	}
	
	function setCategoryId($value) {
		if ($value != $this->category_id) {
			$this->properties = array();
			$this->category_id=$value;
			$this->chng_flag=TRUE;			
			if ($this->default_seats) {
				foreach ($this->getAvailableProperties() as $key=>$val) {
					if ($val["system"] == 2) {
						$this->setPropertyState($key, $this->default_seats);
					}
				}
			}
		}
	}	

	function setComment($value) {
		$this->comment=$value;
		$this->chng_flag=TRUE;
	}
	
	function setClosed($value) {
		$this->closed=$value;
		$this->chng_flag=TRUE;
	}
	
	function setTerminId($value) {
		$this->termin_id=$value;
		$this->chng_flag=TRUE;
	}

	function setPropertyState($property_id, $value) {
		//if ($value == "on")
		//	$value = 1;
		if ($value)
			$this->properties[$property_id] = array("state" => $value);
		else
			$this->properties[$property_id] = FALSE;
	}
	
	function setDefaultSeats($value) {
		$this->default_seats=($value);
	}

	function searchRooms($search_exp, $properties = FALSE, $limit = 0) {
		//create the query
		if ($search_exp && !$properties)
			$query = sprintf ("SELECT b.resource_id, b.name FROM resources_categories a LEFT JOIN resources_objects b USING (category_id) WHERE is_room = '1' AND b.name LIKE '%%%s%%' ORDER BY b.name", $search_exp);

		//create the very complex query for room serach AND room propterties search...	
		if ($properties) {
			$availalable_properties = $this->getAvailableProperties();
			$setted_properties = $this->getSettedPropertiesCount();

			$query = sprintf ("SELECT a.resource_id, b.name %s FROM resources_objects_properties a LEFT JOIN resources_objects b USING (resource_id) WHERE ", ($setted_properties) ? ", COUNT(a.resource_id) AS resource_id_count" : "");

			$i=0;
			if ($setted_properties) {
				foreach ($this->properties as $key => $val) {
					if ($val) {
						//if ($val["state"] == "on")
						//	$val["state"] = 1;
						
						//let's create some possible wildcards
						if (ereg("<=", $val["state"])) {
							$val["state"] = trim(substr($val["state"], strpos($val["state"], "<")+2, strlen($val["state"])));
							$linking = "<=";
						} elseif (ereg(">=", $val["state"])) {
							$val["state"] = trim(substr($val["state"], strpos($val["state"], "<")+2, strlen($val["state"])));
							$linking = ">=";
						} elseif (ereg("<", $val["state"])) {
							$val["state"] = trim(substr($val["state"], strpos($val["state"], "<")+1, strlen($val["state"])));
							$linking = "<";
						} elseif (ereg(">", $val["state"])) {
							$val["state"] = trim(substr($val["state"], strpos($val["state"], "<")+1, strlen($val["state"])));
							$linking = ">";
						} elseif ($availalable_properties[$key]["system"] == "2") {
							$linking = ">=";
						} else $linking = "=";
						
						$query.= sprintf(" %s (property_id = '%s' AND state %s %s%s%s) ", ($i) ? "OR" : "", $key, $linking,  (!is_numeric($val["state"])) ? "'" : "", $val["state"], (!is_numeric($val["state"])) ? "'" : "");
						$i++;
					}
				}
			}
				
			if ($search_exp) 
				$query.= sprintf(" %s (b.name LIKE '%%%s%%' OR b.description LIKE '%%%s%%') ", ($setted_properties) ? "AND" : "", $search_exp, $search_exp);

			$query.= sprintf ("%s b.category_id ='%s' ", ($setted_properties) ? "AND" : "", $this->category_id);

			if ($setted_properties)
				$query.= sprintf (" GROUP BY a.resource_id  HAVING resource_id_count = '%s' ", $i);
			
			$query.= sprintf ("ORDER BY b.name %s", ($limit) ? "LIMIT 0, ".$limit : "");
		}

		$this->db->query($query);
		
		if ($this->db->affected_rows()) {
			while ($this->db->next_record()) {
				if ($this->db->f("name")) {
					$resources_found [$this->db->f("resource_id")] = $this->db->f("name");
				}
			}
			return $resources_found;
		} else
			return array();
	}

	function restore() {
		$query = sprintf("SELECT * FROM resources_requests WHERE request_id='%s' ",$this->id);
		$this->db->query($query);
		
		if($this->db->next_record()) {
			$this->seminar_id = $this->db->f("seminar_id");
			$this->termin_id = $this->db->f("termin_id");
			$this->mkdate = $this->db->f("mkdate");
			$this->resource_id = $this->db->f("resource_id");
			$this->user_id = $this->db->f("user_id");
			$this->category_id = $this->db->f("category_id");
			$this->comment = $this->db->f("comment");
			$this->closed = $this->db->f("closed");
			$this->chdate = $this->db->f("chdate");
			
			$query = sprintf("SELECT a.*, b.type, b.name, b.options, b.system FROM resources_requests_properties a LEFT JOIN resources_properties b USING (property_id) WHERE a.request_id='%s' ", $this->id);
			$this->db->query($query);
			while ($this->db->next_record()) {
				$this->properties[$this->db->f("property_id")] = array("state"=>$this->db->f("state"), "type"=>$this->db->f("type"), "name"=>$this->db->f("name"), "options"=>$this->db->f("options"), "system"=>$this->db->f("system"), "mkdate"=>$this->db->f("mkdate"), "chdate"=>$this->db->f("chdate"));
			}
			return TRUE;
		}
		return FALSE;
	}
	
	//private
	function cleanProperties() {
		foreach ($this->properties as $key => $val) {
			if ($val)
				$properties[] = $key;
		}
		if (is_array($properties)) {
			$in="('".join("','",$properties)."')";
		}
		$query = sprintf("DELETE FROM resources_requests_properties WHERE %s request_id = '%s' ", (is_array($properties)) ? "property_id  NOT IN ".$in." AND " : "", $this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;
	}
	
	//private
	function storeProperties() {
		foreach ($this->properties as $key=>$val) {
			$query = sprintf ("REPLACE INTO resources_requests_properties SET request_id = '%s', property_id = '%s', state = '%s', mkdate = '%s', chdate = '%s'", $this->id, $key, $val["state"], (!$val["mkdate"]) ? time() : $val["mkdate"], time());
			$this->db->query($query);

			if ($this->db->affected_rows())
				$changed = TRUE;
		}
		if ($this->cleanProperties())
			$changed = TRUE;
		
		return $changed;
	}
	
	function copy() {
		$this->id = $this->createId();
		$this->isNewObject = TRUE;
		$this->chang_flag = TRUE;
	}

	function store(){
		// save only, if changes were made or the object is new and we have a resource_id or properties
		if ((($this->chng_flag) || ($create)) && (($this->resource_id) || ($this->getSettedPropertiesCount()))) {
			$chdate = time();
			$mkdate = time();

			if ($this->isNew()) {
				$query = sprintf("INSERT INTO resources_requests SET request_id='%s', resource_id='%s', " 
					."user_id='%s', seminar_id= '%s', termin_id = '%s', category_id = '%s', closed='%s', comment='%s', "
					."mkdate='%s' "
							 , $this->id, $this->resource_id, $this->user_id, $this->seminar_id, $this->termin_id, $this->category_id
							 , $this->closed, $this->comment, $mkdate);
				$this->isNewObject = FALSE;
				$changed = TRUE;
			} else {
				$query = sprintf("UPDATE resources_requests SET resource_id='%s', " 
					."user_id='%s', seminar_id='%s', termin_id = '%s', category_id = '%s', comment='%s', "	
					."closed='%s' WHERE request_id='%s' "
							 , $this->resource_id, $this->user_id, $this->seminar_id, $this->termin_id, $this->category_id, $this->comment
							 , $this->closed, $this->id);
			}
			$this->db->query($query);
	
			$changed_prop = $this->storeProperties();
			
			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE resources_requests SET chdate='%s' WHERE request_id='%s' ", $chdate, $this->id);
				$this->db->query($query);

				$changed = TRUE;
			}
		}

		if ($changed || $changed_prop)
			return TRUE;
		else
			return FALSE;
		
	}

	function delete() {
		if (is_array($this->properties)) {
			foreach ($this->properties as $key => $val) {
				$properties[] = $key;
			}
			$in="('".join("','",$properties)."')";
			$query = sprintf("DELETE FROM resources_requests_properties WHERE property_id IN %s", $in);
			$this->db->query($query);
		}
		
		$query = sprintf("DELETE FROM resources_requests WHERE request_id='%s'", $this->id);
		if($this->db->query($query))
			return TRUE;
		else
			return FALSE;
	}

}