<?php
/*
Statusgruppe.class.php - Statusgruppen-Klasse
Copyright (C) 2008 Till Gl�ggler <tgloeggl@uos.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/**
 * This class represents a single Statusgroup and additionally has some helper-functions
 * for working with multiple / structured groups
 * 
 * @author tgloeggl
 */
class Statusgruppe {
	var $new;
	var $messages = array();

	var $statusgruppe_id;
	var $name = '';
	var $range_id = '';
	var $position = 0;
	var $size = 0;
	var $selfassign = 0;
	var $mkdate = 0;
	var $chdate = 0;

	/*
	function &GetInstance($id = false, $refresh_cache = false) {

		static $statusgruppe_object_pool;

		if ($id){
			if ($refresh_cache){
				$statusgruppe_object_pool[$id] = null;
			}
			if (is_object($statusgruppe_object_pool[$id]) && $statusgruppe_object_pool[$id]->getId() == $id){
				return $statusgruppe_object_pool[$id];
			} else {
				$statusgruppe_object_pool[$id] = new Seminar($id);
				return $statusgruppe_object_pool[$id];
			}
		} else {
			return new Seminar(false);
		}
	}
	*/

	function Statusgruppe($statusgruppe_id = '') {
		if ($statusgruppe_id == '') {
			$this->new = true;
			$this->statusgruppe_id = md5(uniqid(rand()));
		} else {
			$this->new = false;
			$this->statusgruppe_id = $statusgruppe_id;
			$this->restore();
		}
	}

	/* * * * * * * * * * * * * * * * * * * *
	 * * G E T T E R   /   S E T T E R * * *
	 * * * * * * * * * * * * * * * * * * * */
	public function __call($method, $args)
  {
  	if (substr($method, 0, 3) == 'get') {
  		$variable = strtolower(substr($method, 3, strlen($method) -3));
  		if (isset($this->$variable)) {
  			return $this->$variable;
  		} else {
  			throw new Exception(__CLASS__ ."::$method() does not exist!");
  		}
  	} else if (substr($method, 0, 3) == 'set') {  		
  		$variable = strtolower(substr($method, 3, strlen($method) -3));
  		if (sizeof($args) != 1) {
  			throw new Exception("wrong parameter count: ".__CLASS__ ."::$method() expects 1 parameter!");
  		}
  		$this->$variable = $args[0];
  	}
  }	 
  
	function getId() {
		return $this->statusgruppe_id;
	}

	function setSelfassign($selfassign) {
		$this->selfassign = ($selfassign) ? '1' : '0';
	}

	/* * * * * * * * * * * * * * * * * * * *
	 * * * * * * D A T A B A S E * * * * * * 
	 * * * * * * * * * * * * * * * * * * * */
	function restore() {
		if (!$this->statusgruppe_id) return;

		try {
			$db = DBManager::get('studip');	

			$query = "SELECT * FROM statusgruppen WHERE statusgruppe_id = '{$this->statusgruppe_id}'";
			$result = $db->query($query);
	
			$statusgruppe = $result->fetch(PDO::FETCH_ASSOC);
			foreach ($statusgruppe as $key => $val) {
				$this->$key = $val;
			}
		} catch (PDException $e) {
			echo $e->getMessage();
			die;
		}
	}

	function store() {
		try {
			$db = DBManager::get('studip');	

			if ($this->new) {
				$query = "INSERT INTO statusgruppen
					(statusgruppe_id, name, range_id, position, size, selfassign, mkdate, chdate) VALUES
					('{$this->statusgruppe_id}', '{$this->name}', '{$this->range_id}', {$this->position}, {$this->size},
						{$this->selfassign}, '". time() ."', '". time() ."')";
			} else {
				$query = "UPDATE statusgruppen SET
					name = '{$this->name}', range_id = '{$this->range_id}', position = {$this->position}, 
					size = {$this->size}, selfassign = {$this->selfassign}, chdate = '". time() ."'
					WHERE statusgruppe_id = '{$this->statusgruppe_id}'";
			}

			$result = $db->exec($query);
		} catch (PDException $e) {
			echo $e->getMessage();
			die;
		}
	
	}

	function delete() {
		DeleteStatusgruppe($this->statusgruppe_id);
		
		/*$db = DBManager::get('studip');
		
		// cascade for statusgruppe_user
		$db->exec("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '{$this->statusgruppe_id}'");
		
		// cascade for datafields_entries
		$db->exec("DELETE FROM datafields_entries WHERE sec_range_id = '{$this->statusgruppe_id}'"); 
		
		// and now delete the statusgroup
		$db->exec("DELETE FROM statusgruppen WHERE statusgruppe_id = '{$this->statusgruppe_id}'");*/
	}
	
	/* * * * * * * * * * * * * * * * * * * *
	 * * H E L P E R   F U N C T I O N S * *
	 * * * * * * * * * * * * * * * * * * * */

	function isSeminar() {
		if (!isset($this->is_sem)) {			
			$db = DBManager::get('studip');
			
			$result = $db->query("SELECT * FROM seminare WHERE Seminar_id = '{$GLOBALS['range_id']}'");
			
			if ($seminar = $result->fetch(PDO::FETCH_ASSOC)) {
				$this->is_sem = true;
			} else {			
				$this->is_sem = false;
			}
		}
		
		return $this->is_sem;
	}

	function getData() {
		global $invalidEntries;
	
		$role = array(
			'id' => $this->statusgruppe_id,
			'name' => $this->name,
			'size' => $this->size,
			'selfassign' => $this->selfassign
		);

		if (!$this->isSeminar()) {
			$datafields = DataFieldEntry::getDataFieldEntries(array($this->range_id, $this->statusgruppe_id), 'roleinstdata');
	
			foreach ($datafields as $id => $field) {
	
				if (isset($invalidEntries[$id])) {
					$invalid = true;
				} else {
					$invalid = false;
				}
	
				$df[] = array (
					'name' =>$field->structure->getName(),
					'value' => $field->getValue(),
					'html' => $field->getHTML('datafield_content[]', $field->structure->getID()),
					'datafield_id' => $field->structure->getID(),
					'datafield_type' => $field->getType(),
					'invalid' => $invalid
				);
			}
	
			$role['datafields'] = $df;
		}
		
		return $role;		
	}
	
	function checkData() {
		global $datafield_id, $datafield_content, $datafield_type, $datafield_sec_range_id, $invalidEntries, $_REQUEST;

		// check the standard role data
		$this->name = $_REQUEST['new_name'];
		$this->size = $_REQUEST['new_size'];
		$this->selfassign = ($_REQUEST['new_selfassign']) ? '1' : '0';		

		// check the datafields
		if (!$this->isSeminar() && is_array($datafield_id)) {
			$ffCount = 0; // number of processed form fields
			foreach ($datafield_id as $i=>$id) {
				$struct = new DataFieldStructure(array("datafield_id"=>$id, 'type'=>$datafield_type[$i]));
				$entry  = DataFieldEntry::createDataFieldEntry($struct, array($this->range_id, $datafield_sec_range_id[$i]));
				$numFields = $entry->numberOfHTMLFields(); // number of form fields used by this datafield
				if ($datafield_type[$i] == 'bool' && $datafield_content[$ffCount] != $id) { // unchecked checkbox?
					$entry->setValue('');
					$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
				}
				elseif ($numFields == 1)
					$entry->setValue($datafield_content[$ffCount]);
				else
					$entry->setValue(array_slice($datafield_content, $ffCount, $numFields));
				$ffCount += $numFields;

				$entry->structure->load();
				if ($entry->isValid()) {
					$entry->store();
				} else {
					$invalidEntries[$struct->getID()] = $entry;
				}
			}
			/*// change visibility of role data
				foreach ($group_id as $groupID)
				setOptionsOfStGroup($groupID, $u_id, ($visible[$groupID] == '0') ? '0' : '1');*/
			//$msgs[] = 'error�<b>'. _("Fehlerhafte Eingaben (s.u.)") .'</b>';
		}

		// a group cannot be its own vather!
		if ($_REQUEST['vather'] == $this->statusgruppe_id) {
			$this->messages[] = 'error�' ._("Sie k�nne diese Gruppe nicht sich selbst unterordnen!");
		} else
		
		// check if the group shall be moved
		if ($_REQUEST['vather'] != 'nochange') {
			//$db = DBManager::get('studip');
			if ($_REQUEST['vather'] == 'root') {
				$vather_id = $GLOBALS['range_id'];
			} else {
				$vather_id = $_REQUEST['vather'];
			}
			if (!isVatherDaughterRelation($this->statusgruppe_id, $vather_id)) {
				$this->range_id = $vather_id;
				//$db->query("UPDATE statusgruppen SET range_id = '$vather_id' WHERE statusgruppe_id = '{$this->statusgruppe_id}'");
			} else {
				$this->messages[] = 'error�' ._("Sie k�nnen diese Gruppe nicht einer ihr untergeordneten Gruppe zuweisen!");
			}
		}
				
		if (!$this->isSeminar() && is_array($invalidEntries)) {
			$this->messages[] = 'error�' . _("Korrigieren Sie die fehlerhaften Eingaben!");
			return false;
		}
		return true;

	}
	
	/* * * * * * * * * * * * * * * * * * * *
	 * * * S T A T I C   M E T H O D S * * *
	 * * * * * * * * * * * * * * * * * * * */

	static function displayOptionsForRoles($roles, $level = 0) {
		foreach ($roles as $role_id => $role) {
			echo '<option value="'. $role_id .'">';
			for ($i = 1; $i <= $level; $i++) echo '&nbsp;&nbsp;&nbsp;';
			echo substr($role['role']->getName(), 0, 70).'</option>';
			if ($role['child']) Statusgruppe::displayOptionsForRoles($role['child'], $level+1);
		}
	}
	 
	static function getFlattenedRoles($roles, $level = 0, $parent_name = false) { 	
		$ret = array();
		
		//var_dump($roles);
		foreach ($roles as $id => $role) {
			if (!isset($role['name'])) $role['name'] = $role['role']->getName();
			$spaces = '';
			for ($i = 0; $i < $level; $i++) $spaces .= '&nbsp;&nbsp;';
	
			// generate an indented version of the role-name
			$role['name'] = $spaces . $role['name'];
			
			// generate a name with all parent-roles in the name
			if ($parent_name) {
				$role['name_long'] = $parent_name . ' &gt; ' . $role['role']->getName();
			} else {
				$role['name_long'] = $role['role']->getName();
			} 
			
			$ret[$id] = $role;
									
			if ($role['child']) {
				$ret = array_merge($ret, Statusgruppe::getFlattenedRoles($role['child'], $level + 1, $role['name_long']));			
			}
			
		}
		
		return $ret;
	}
	
	static function getFromArray($data) {		
		$statusgruppe = new Statusgruppe();
		$statusgruppe->new = false;

		$statusgruppe->statusgruppe_id = $data['statusgruppe_id'];
		$statusgruppe->name = $data['name'];
		$statusgruppe->range_id = $data['range_id'];
		$statusgruppe->position = $data['position'];
		$statusgruppe->size = $data['size'];
		$statusgruppe->selfassign = $data['selfassign'];
		$statusgruppe->mkdate = $data['mkdata'];
		$statusgruppe->chdate = $data['chdate'];	
		
		return $statusgruppe;
	}
	
	static function roleExists($id) {
		$db = DBManager::get('studip');
		
		$result = $db->query("SELECT * FROM statusgruppen WHERE statusgruppe_id = '$id'");
				
		if (!$statusgruppe = $result->fetch(PDO::FETCH_ASSOC)) {
			return false;
		}
		
		// if there is a statusgroup with this id, return true
		if (sizeof($statusgruppe) > 0) return true;
		
		return false;
	}
}
