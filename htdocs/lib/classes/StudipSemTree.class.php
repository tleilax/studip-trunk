<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// SemTree.class.php
// Class to handle structure of the "seminar tree"
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
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/DbSnapshot.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/dbviews/sem_tree.view.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/TreeAbstract.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/SemesterData.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/config.inc.php");

/**
* class to handle the seminar tree
*
* This class provides an interface to the structure of the seminar tree
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipSemTree extends TreeAbstract {
	
	var $sem_dates = array();
	var $sem_number = null;
	var $enable_lonely_sem = true;
	/**
	* constructor
	*
	* do not use directly, call &TreeAbstract::GetInstance("StudipRangeTree")
	* @access private
	*/ 
	function StudipSemTree($args) {
		$semester=new SemesterData;
		$all_semester = $semester->getAllSemesterData();
		array_unshift($all_semester,0);
		$this->root_name = $GLOBALS['UNI_NAME_CLEAN'];
		if (isset($args['sem_number']) ){
			$this->sem_number = $args['sem_number'];
		}
		parent::TreeAbstract(); //calling the baseclass constructor 
		$this->sem_dates = $all_semester;
		$this->sem_dates[0] = array("name" => sprintf(_("vor dem %s"),$this->sem_dates[1]['name']));
	}

	/**
	* initializes the tree
	*
	* stores all rows from table sem_tree in array $tree_data
	* @access public
	*/
	function init(){
		parent::init();
		$this->view->params[0] = (isset($this->sem_number)) ? " IF(" . $GLOBALS['_views']['sem_number_sql'] . " IN(" . join(",",$this->sem_number) . "),b.seminar_id,NULL)"  : "b.seminar_id";
		$db = $this->view->get_query("view:SEM_TREE_GET_DATA");
		$view = new DbView();
		while ($db->next_record()){
			$this->tree_data[$db->f("sem_tree_id")] = array("info" => $db->f("info"),"studip_object_id" => $db->f("studip_object_id"),
															"entries" => $db->f("entries"));
			if ($db->f("studip_object_id")){
				$name = $db->f("studip_object_name");
			} else {
				$name = $db->f("name");
			}
			$this->storeItem($db->f("sem_tree_id"), $db->f("parent_id"), $name, $db->f("priority"));
		}
	}
	
	function getSemIds($item_id,$ids_from_kids = false){
		if (!$this->tree_data[$item_id])
			return false;
		if ($ids_from_kids){
			$this->view->params[0] = $this->getKidsKids($item_id);
		}
		$this->view->params[0][] = $item_id;
		$this->view->params[1] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end > " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
		$ret = false;
		$rs = $this->view->get_query("view:SEM_TREE_GET_SEMIDS");
		while($rs->next_record()){
			$ret[] = $rs->f(0);
		}
		return $ret;
	}
	
	function getSemData($item_id,$sem_data_from_kids = false){
		if (!$this->tree_data[$item_id])
			return false;
		if ($sem_data_from_kids){
			$this->view->params[0] = $this->getKidsKids($item_id);
		}
		$this->view->params[0][] = $item_id;
		$this->view->params[1] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end > " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
		return new DbSnapshot($this->view->get_query("view:SEM_TREE_GET_SEMDATA"));
	}
	
	function getLonelySemData($item_id){
		if (!$institut_id = $this->tree_data[$item_id]['studip_object_id'])
			return false;
		$this->view->params[0] = $institut_id;
		$this->view->params[1] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end > " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
		return new DbSnapshot($this->view->get_query("view:SEM_TREE_GET_LONELY_SEM_DATA"));
	}
	
	function getNumEntries($item_id, $num_entries_from_kids = false){
		if (!$this->tree_data[$item_id])
			return false;
		if ($this->enable_lonely_sem && $this->tree_data[$item_id]["studip_object_id"] && !isset($this->tree_data[$item_id]["lonely_sem"])){
				$this->view->params[0] = $this->tree_data[$item_id]["studip_object_id"];
				$this->view->params[1] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end > " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
				$db2 = $this->view->get_query("view:SEM_TREE_GET_NUM_LONELY_SEM");
				while ($db2->next_record()){
					$this->tree_data[$item_id]['entries'] += $db2->f(0);
					$this->tree_data[$item_id]['lonely_sem'] += $db2->f(0);
				}
		}			
		if (!$num_entries_from_kids){
			return $this->tree_data[$item_id]["entries"];
		} else {
			$item_list = $this->getKidsKids($item_id);
			$item_list[] = $item_id;
			$ret = 0;
			$num_items = count($item_list);
			for ($i = 0; $i < $num_items; ++$i){
				$ret += $this->tree_data[$item_list[$i]]["entries"];
			}
			return $ret;
		}
	}
	
	function getAdminRange($item_id){
		if (!$this->tree_data[$item_id])
			return false;
		if ($item_id == "root")
			return "root";
		$ret_id = $item_id;
		while (!$this->tree_data[$ret_id]['studip_object_id']){
			$ret_id = $this->tree_data[$ret_id]['parent_id'];
			if ($ret_id == "root")
				break;
		}
		return $ret_id;
	}
	
	function InsertItem($item_id, $parent_id, $item_name, $item_info, $priority, $studip_object_id){
		$view = new DbView();
		$view->params = array($item_id,$parent_id,$item_name,$priority,$item_info,$studip_object_id);
		$rs = $view->get_query("view:SEM_TREE_INS_ITEM");
		return $rs->affected_rows();
	}
	
	function UpdateItem($item_id, $item_name, $item_info){
		$view = new DbView();
		$view->params = array($item_name,$item_info,$item_id);
		$rs = $view->get_query("view:SEM_TREE_UPD_ITEM");
		return $rs->affected_rows();
	}	
	
	function DeleteItems($items_to_delete){
		$view = new DbView();
		$view->params[0] = (is_array($items_to_delete)) ? $items_to_delete : array($items_to_delete);
		$view->auto_free_params = false;
		$rs = $view->get_query("view:SEM_TREE_DEL_ITEM");
		$deleted['items'] = $rs->affected_rows();
		$rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_RANGE");
		$deleted['entries'] = $rs->affected_rows();
		return $deleted;
	}
	
	function DeleteSemEntries($item_ids = null, $sem_entries = null){
		$view = new DbView();
		if ($item_ids && $sem_entries){
			$view->params[0] = (is_array($item_ids)) ? $item_ids : array($item_ids);
			$view->params[1] = (is_array($sem_entries)) ? $sem_entries : array($sem_entries);
			$rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_SEM_RANGE");
			$ret = $rs->affected_rows();
		} elseif ($item_ids){
			$view->params[0] = (is_array($item_ids)) ? $item_ids : array($item_ids);
			$rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_RANGE");
			$ret = $rs->affected_rows();
		} elseif ($sem_entries){
			$view->params[0] = (is_array($sem_entries)) ? $sem_entries : array($sem_entries);
			$rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_SEMID_RANGE");
			$ret = $rs->affected_rows();
		} else {
			$ret = false;
		}
		return $ret;
	}
	
	function InsertSemEntry($sem_tree_id, $seminar_id){
		$view = new DbView();
		$view->params[0] = $seminar_id;
		$view->params[1] = $sem_tree_id;
		$rs = $view->get_query("view:SEMINAR_SEM_TREE_INS_ITEM");
		return $rs->affected_rows();
	}
}
//$test =& TreeAbstract::GetInstance("StudipSemTree");
//echo "<pre>";
//echo get_class($test) .  "\n";
//print_r($test->tree_data);
?>
