<?
/**
* DataFields.class.php
* 
* generic data-fields for the Stud.IP objects Veranstaltungen, Einrichtungen and user
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		StartupChecks.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// DataFields.class.php
// generische Datenfelder fuer Veranstaltungen, Einrichtungen und Nutzer
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

require_once $ABSOLUTE_PATH_STUDIP.("functions.php");
require_once $ABSOLUTE_PATH_STUDIP.("config.inc.php");

class DataFields {
	var $db;
	var $db2;
	var $perms_mask = array(	//the perm's bitmask for assigned datafields depending from the global perms in field object_class
		"user" => 1, 
		"autor" => 2,
		"tutor" => 4,
		"dozent" => 8,
		"admin" => 16,
		"root" => 32);
	var $range_id;			//range_id from the stud.ip object
	
	function DataFields($range_id = '') {
		$this->range_id = $range_id;
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
	}

	function getLocalFields($range_id = '', $object_class='', $object_type='') {
		$local_datafields = array();
		
		if (!$range_id)
			$range_id = $this->range_id;
			
		if ((!$object_class) && ($range_id))
			$object_class = get_object_type($range_id);
			
	
		if ($object_class) {
			if (!$object_type) {
				switch ($object_class) {
					case "sem": 
						$query = sprintf ("SELECT status AS type FROM seminare WHERE seminar_id = '%s' ", $range_id);
					break;
					case "inst":
					case "fak":
						$query = sprintf ("SELECT type FROM Institute WHERE Institut_id = '%s' ", $range_id);
					break;
					case "user":
						$query = sprintf ("SELECT perms FROM auth_user_md5 WHERE user_id = '%s' ", $range_id);
					break;
				}
				
				$this->db->query($query);
				$this->db->next_record();
				
				$object_type = $this->db->f("type");
			}
	
			switch ($object_class) {
				case "sem": 
				case "inst":
				case "fak":
					if ($object_type)
						$clause = "object_class = ".$object_type." OR object_class IS NULL";
					else
						$clause = "object_class IS NULL";
				break;
				case "user":
					$clause = "(object_class & ".$this->perms_mask[$this->db->f("perms")].") OR object_class IS NULL";
				break;
			}
			

			if ($object_type == "fak")
				$object_type = "inst";
	
			$query = sprintf ("SELECT datafield_id, name, NULL as content, edit_perms FROM datafields WHERE object_type ='%s' AND (%s) ORDER BY object_class, priority", $object_class, $clause);

			$this->db->query($query);

			while ($this->db->next_record()) {
				$local_datafields[$this->db->f("datafield_id")] = $this->db->Record;
			}
			
			$query2 = sprintf ("SELECT datafields.datafield_id, name, content, edit_perms FROM datafields LEFT JOIN datafields_entries USING (datafield_id) WHERE range_id = '%s' AND object_type ='%s' AND (%s) ORDER BY object_class, priority", $range_id, $object_class, $clause);

			$this->db2->query($query2);

			while ($this->db2->next_record()) {
				$local_datafields[$this->db2->f("datafield_id")] = $this->db2->Record;
			}
		}
		
		return $local_datafields;		
	}

	function storeContent($content, $datafield_id, $range_id = '') {
		if (!$range_id)
			$range_id = $this->range_id;
			
		$query = sprintf ("REPLACE INTO datafields_entries SET content='%s', datafield_id ='%s', range_id = '%s', chdate ='%s' ", $content, $datafield_id, $range_id, time());
		
		$this->db->query($query);
		
		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;

	}
}
