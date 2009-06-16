<?php
# Lifter002: TODO
# Lifter007: TODO
/**
* StudipModulesInstance.class.php
* 
* 
* 
*
* @author   Andr� Noack <noack@data-quest.de>
* @version  $Id: Task.class.php,v 1.39 2005/09/15 13:34:03 thienel Exp $
* @access   public

* @package  
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipModulesInstance.class.php
// 
// Copyright (C) 2006
// Andr� Noack <noack@data-quest.de>,
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

define('STUDIPSTMINSTANCE_DB_TABLE', 'stm_instances');
define('LANGUAGE_ID',"09c438e63455e3e1b3deabe65fdbc087");

require_once "lib/classes/SimpleORMap.class.php";
require_once "lib/classes/StudipStmInstanceElement.class.php";
require_once "lib/classes/Seminar.class.php";


class StudipStmInstance extends SimpleORMap {
	
	var $elements = array();
	var $el_struct = array();
	var $assigns = array();
	
	function GetStmInstancesByUser($user_id, $semester_id = false){
		$ret = array();
		$query = "SELECT DISTINCT stm_instances.*,stm_abstract.*,stm_instances_text.*,semester_data.name as sem_name FROM stm_instances_user
				INNER JOIN stm_instances ON stm_instances.stm_instance_id = stm_instances_user.stm_instance_id
				INNER JOIN stm_abstract ON stm_instances.stm_abstr_id = stm_abstract.stm_abstr_id
				INNER JOIN stm_instances_text ON stm_instances.stm_instance_id = stm_instances_text.stm_instance_id AND stm_instances_text.lang_id='".LANGUAGE_ID."'
				LEFT JOIN semester_data ON semester_data.semester_id = stm_instances.semester_id
				WHERE stm_instances_user.user_id='$user_id'"
				. ($semester_id ? " AND stm_instances.semester_id='$semester_id' " : "")
				. " ORDER BY id_number, title";
		$db = new DB_Seminar($query);
		while($db->next_record()){
			$ret[$db->f('stm_instance_id')] = $db->Record;
		}
		return $ret;
	}
	
	function GetStmInstancesBySeminar($seminar_id, $semester_id = false){
		$ret = array();
		$query = "SELECT DISTINCT stm_instances.*,stm_abstract.*,stm_instances_text.*,semester_data.name as sem_name FROM stm_instances_elements
				INNER JOIN stm_instances ON stm_instances.stm_instance_id = stm_instances_elements.stm_instance_id
				INNER JOIN stm_abstract ON stm_instances.stm_abstr_id = stm_abstract.stm_abstr_id
				INNER JOIN stm_instances_text ON stm_instances.stm_instance_id = stm_instances_text.stm_instance_id AND stm_instances_text.lang_id='".LANGUAGE_ID."'
				LEFT JOIN semester_data ON semester_data.semester_id = stm_instances.semester_id
				WHERE stm_instances_elements.sem_id='$seminar_id'"
				. ($semester_id ? " AND stm_instances.semester_id='$semester_id' " : "")
				. " ORDER BY id_number, title";
		$db = new DB_Seminar($query);
		while($db->next_record()){
			$ret[$db->f('stm_instance_id')] = $db->Record;
		}
		return $ret;
	}
	
	function GetStmInstancesBySeminarAndUser($seminar_id, $user_id){
		$ret = array();
		$query = "SELECT DISTINCT stm_instances.*,stm_abstract.*,stm_instances_text.*,semester_data.name as sem_name FROM stm_instances_user
				INNER JOIN stm_instances_elements ON stm_instances_elements.element_id = stm_instances_user.element_id AND stm_instances_elements.stm_instance_id = stm_instances_user.stm_instance_id 
				INNER JOIN stm_instances ON stm_instances.stm_instance_id = stm_instances_elements.stm_instance_id
				INNER JOIN stm_abstract ON stm_instances.stm_abstr_id = stm_abstract.stm_abstr_id
				INNER JOIN stm_instances_text ON stm_instances.stm_instance_id = stm_instances_text.stm_instance_id AND stm_instances_text.lang_id='".LANGUAGE_ID."'
				LEFT JOIN semester_data ON semester_data.semester_id = stm_instances.semester_id
				WHERE stm_instances_user.user_id='$user_id' AND stm_instances_elements.sem_id='$seminar_id'"
				. " ORDER BY id_number, title";
		$db = new DB_Seminar($query);
		while($db->next_record()){
			$ret[$db->f('stm_instance_id')] = $db->Record;
		}
		return $ret;
	}
	
	function StudipStmInstance ($id = NULL, $stm_abstr_id = null) {
		parent::SimpleORMap($id);
		if ($this->is_new) {
			$this->setValue('stm_abstr_id', $stm_abstr_id);
			$this->setValue('lang_id', LANGUAGE_ID);
		}
	}
	
	function addElement ($element = null, $sem_id = null) {
		if (!is_object($element)){
			$obj =& new StudipStmInstanceElement($element, $this->getId(), $sem_id);
		} else {
			$obj =& $element;
		}
		$obj->setValue('stm_instance_id', $this->getId());
		$obj->setValue('sem_id', $sem_id);
		$this->elements[explode('-',$obj->getId())] =& $obj;
		$this->triggerChdate();
		return $obj->getId();
	}
	
	function deleteElement ($id) {
		$ret = false;
		if (isset($this->elements[$id])){
			$ret = $this->elements[$id]->delete();
			unset($this->elements[$id]);
			$this->triggerChdate();
		}
		return $ret;
	}
	
	function &getElement ($id) {
		if (isset($this->elements[$id])){
			return $this->elements[$id];
		} else {
			return null;
		}
	}
	
	function restoreElements (){
		$this->el_struct = array();
		$this->elements = StudipStmInstanceElement::GetElementsByInstance($this->getId(), true);
		foreach(array_keys($this->elements) as $element_id){
			$this->el_struct[$this->elements[$element_id]->getValue('elementgroup')][$this->elements[$element_id]->getValue('element_id')][] = $this->elements[$element_id]->getValue('sem_id');
		}
		return count($this->elements);
	}
	
	function addParticipant($user_id, $elementgroup, $groupsem){
		foreach($this->el_struct[$elementgroup] as $element => $sem_ids){
			if (count($sem_ids) > 1 && isset($groupsem[$element])){
				$sem_to_insert = $groupsem[$element];
			} else {
				$sem_to_insert = $sem_ids[0];
			}
			$this->db->query(sprintf("INSERT INTO stm_instances_user 
									(stm_instance_id,element_id,user_id,mkdate)
									VALUES ('%s','%s','%s',UNIX_TIMESTAMP())",
									$this->getId(),$element,$user_id));
			$inserted += $this->db->affected_rows();
			insert_seminar_user($sem_to_insert, $user_id, 'autor', FALSE);
		}
		return ($inserted == count($this->el_struct[$elementgroup]));
	}
	
	function deleteParticipant($user_id){
		$el = StudipStmInstanceElement::GetElementsByInstanceParticipant($this->getId(), $user_id);
		if (count($el)){
			foreach($el as $element){
				$this->db->query("DELETE FROM stm_instances_user WHERE stm_instance_id='".$this->getId()."' AND user_id='$user_id' AND element_id='{$element[0]}'");
				$this->db->query("DELETE FROM seminar_user WHERE user_id='$user_id' AND seminar_id='{$element[2]}'");
				// L�schen aus Statusgruppen
				RemovePersonStatusgruppeComplete(get_username($user_id), $element[2]);
				//Pruefen, ob es Nachruecker gibt
				update_admission($element[2]);
			}
			return true;
		}
		return false;
	}
	
	function getGroupCount(){
		return count($this->el_struct);
	}
	
	function getGroupedElementSemCount($elementgroup, $element_id){
		return count($this->getGroupedElementSem($elementgroup, $element_id));
	}
	
	function getGroupedElementSem($elementgroup, $element_id){
		$ret = $this->el_struct[$elementgroup][$element_id];
		if(!is_array($ret)) $ret = array();
		return $ret;
	}
	
	function isParticipant($user_id){
		$this->db->query("SELECT mkdate FROM stm_instances_user WHERE user_id='$user_id' AND stm_instance_id='".$this->getId()."' LIMIT 1");
		return $this->db->next_record();
	}
	
	function isAllowedToEnter($user_id, $check_semester = false){
		$abstr_id = $this->getValue('stm_abstr_id');
		if ($check_semester) $add = " AND sem BETWEEN earliest AND latest ";
		$this->db->query("SELECT * FROM stm_abstract_assign saa INNER JOIN his_stud_stg hss ON (hss.stg=saa.stg AND hss.abschl=saa.abschl)
						WHERE user_id='$user_id' AND stm_abstr_id='$abstr_id' $add LIMIT 1");
		return $this->db->next_record();
	}
	
	function isAllowedToEdit($user_id){
		if($GLOBALS['perm']->have_perm('root', $user_id)) return true;
		if($GLOBALS['perm']->have_perm('admin', $user_id) && ($GLOBALS['perm']->have_studip_perm('admin', $this->getValue('homeinst'), $user_id))) return true;
		if($GLOBALS['perm']->have_perm('dozent', $user_id) && $this->getValue('responsible') == $user_id) return true;
		return false;
	}
	
	function restoreAssigns(){
		$this->assigns = array();
		if ($stm_abstr_id = $this->getValue('stm_abstr_id')){
			$this->db->query("SELECT sam.*, his_stg.dtxt as stg_name,his_pvers.dtxt as p_version_name, his_abschl.ltxt as abschl_name, sat.name as type_name FROM stm_abstract_assign sam
								INNER JOIN his_stg ON his_stg.stg=sam.stg
								INNER JOIN his_abschl ON his_abschl.abint=sam.abschl
								INNER JOIN his_pvers ON his_pvers.pvers=sam.pversion
								INNER JOIN stm_abstract_types sat ON sat.stm_type_id = sam.stm_type_id AND sat.lang_id='".LANGUAGE_ID."'
								WHERE stm_abstr_id='$stm_abstr_id'");
			while($this->db->next_record()){
				$this->assigns[] = $this->db->Record;
			}
		}
		return count($this->assigns);
	}
	
	function restore () {
		$where_query = $this->getWhereQuery();
		if ($where_query){
			$query = "SELECT stm_instances.*,stm_abstract.id_number,stm_abstract.duration,credits,stm_abstract.workload,stm_abstract.turnus, stm_instances_text.*,stm_abstract_text.aims,semester_data.name as sem_name, Institute.Name as homeinst_name FROM stm_instances
						INNER JOIN stm_abstract ON stm_instances.stm_abstr_id = stm_abstract.stm_abstr_id
						INNER JOIN stm_instances_text ON stm_instances.stm_instance_id =stm_instances_text.stm_instance_id AND stm_instances_text.lang_id='".LANGUAGE_ID."'
						INNER JOIN stm_abstract_text ON stm_instances.stm_abstr_id = stm_abstract_text.stm_abstr_id AND stm_abstract_text.lang_id='".LANGUAGE_ID."'
						LEFT JOIN semester_data ON semester_data.semester_id = stm_instances.semester_id
						LEFT JOIN Institute ON Institute.Institut_id = stm_instances.homeinst WHERE "
					. join(" AND ", $where_query);
			$this->db->query($query);
			if ($this->db->next_record()) {
				$this->content = array();
				foreach($this->db->Record as $key => $value){
					if(!is_int($key)){
						$this->content[$key] = $value;
					}
				}
				$this->is_new = false;
			}
		} else {
			$this->is_new = true;
		}
		if (!$this->is_new){
			$this->restoreElements();
			$this->restoreAssigns();
		} else {
			$this->elements = array();
			$this->assigns = array();
		}
		return !$this->is_new;
	}
	
	function store () {
		$ret = 0;
		$e_stored = 0;
		foreach(array_keys($this->elements) as $e_id){
			if ($e_stored = $this->elements[$e_id]->store()) {
				$ret += $e_stored;
			}
		}
		if ($ret){
			$ret += $this->triggerChdate();
		}
		$ret += parent::store();
		return $ret;
	}
	
	function delete () {
		$ret = 0;
		$e_stored = 0;
		foreach(array_keys($this->elements) as $e_id){
			$ret += $this->elements[$e_id]->delete();
		}
		$ret += parent::delete();
		return $ret;
	}
	
	function getValue($field){
		switch ($field){
			case 'displayname':
			$ret = 	($this->getValue('id_number') ? $this->getValue('id_number') . ': ' : '') . $this->getValue('title') . ' (' . $this->getValue('sem_name').')';
			break;
			default:
			$ret = parent::getValue($field);
		}
		return $ret;
	}
}
?>
