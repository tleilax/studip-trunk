<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemTreeSearch.class.php
// Class to build search formular and execute search
// 
// Copyright (c) 2003 Andr� Noack <noack@data-quest.de>
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

require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/StudipSemTree.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");


/**
* Class to build search formular and execute search
*
* 
*
* @access	public	
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	DBTools
**/
class StudipSemTreeSearch {
	
	var $view;
	
	var $num_search_result = false;
	
	var $num_inserted;
	
	var $form_name;
	
	var $tree;
	
	var $seminar_id;
	
	var $institut_id;
	
	var $sem_tree_ranges = array();
	
	var $sem_tree_ids = array();
	
	var $selected = array();
	
	var $search_result = array();
	
	function StudipSemTreeSearch($seminar_id,$form_name = "search_sem_tree", $auto_search = true){
		global $_REQUEST;
		$this->view = new DbView();
		$this->form_name = $form_name;
		$this->tree = TreeAbstract::GetInstance("StudipSemTree");
		$this->seminar_id = $seminar_id;
		$this->view->params[0] = $seminar_id;
		$rs = $this->view->get_query("view:SEM_GET_INST");
		while($rs->next_record()){
			$this->institut_id = $rs->f(0);
		}
		$this->init();
		if($auto_search){
			$this->doSearch();
		}
	}
	
	function init(){
		unset($this->sem_tree_ranges);
		unset($this->sem_tree_ids);
		unset($this->selected);
		$this->view->params[0] = $this->seminar_id;
		$rs = $this->view->get_query("view:SEMINAR_SEM_TREE_GET_IDS");
		while($rs->next_record()){
			if (!$this->tree->hasKids($rs->f("sem_tree_id"))){
				$this->sem_tree_ranges[$rs->f("parent_id")][] = $rs->f("sem_tree_id");
				$this->sem_tree_ids[] = $rs->f("sem_tree_id");
				$this->selected[$rs->f("sem_tree_id")] = true;
			}
		}
	}
	/* fuzzy !!!
	function getExpectedRanges(){
		$this->view->params[0] = $this->institut_id;
		$this->view->params[1] = $this->sem_tree_ids;
		$rs = $this->view->get_query("view:SEMINAR_SEM_TREE_GET_EXP_IDS");
		while ($rs->next_record()){
			if (!$this->tree->hasKids($rs->f("sem_tree_id"))){
				$this->sem_tree_ranges[$rs->f("parent_id")][] = $rs->f("sem_tree_id");
				$this->sem_tree_ids[] = $rs->f("sem_tree_id");
			}
		}
	}
	*/
	
	//not fuzzy
	function getExpectedRanges(){
		$this->view->params[0] = $this->institut_id;
		$rs = $this->view->get_query("view:SEM_TREE_GET_FAK");
		if ($rs->next_record()){
			$the_kids = $this->tree->getKidsKids($rs->f("sem_tree_id"));
			for ($i = 0; $i < count($the_kids); ++$i){
				if (!$this->tree->hasKids($the_kids[$i]) && !in_array($the_kids[$i],$this->sem_tree_ids)){
					$this->sem_tree_ranges[$this->tree->tree_data[$the_kids[$i]]['parent_id']][] = $the_kids[$i];
					$this->sem_tree_ids[] = $the_kids[$i];
				}
			}
		}
	}
	
	function getChooserField($attributes = array(), $cols = 70){
		if ($this->institut_id){
			$this->getExpectedRanges();
		}
		$ret = "\n<select name=\"{$this->form_name}_chooser[]\" multiple ";
		foreach($attributes as $key => $value){
			$ret .= "$key=\"$value\"";
		}
		$ret .= ">";
		foreach ($this->sem_tree_ranges as $range_id => $sem_tree_id){
			$ret .= "\n<option value=\"0\">&nbsp;</option>";
			$ret .= "\n<option value=\"0\" style=\"font-weight:bold;color:red;\">" . htmlReady(my_substr($this->getPath($range_id),0,$cols)) ."</option>";
			$ret .= "\n<option value=\"0\" style=\"font-weight:bold;color:red;\">" . str_repeat("�",$cols) . "</option>";
			for ($i = 0; $i < count($sem_tree_id); ++$i){
				$ret .= "\n<option value=\"{$sem_tree_id[$i]}\" " 
						. (($this->selected[$sem_tree_id[$i]]) ? " selected " : "")
						. (($this->search_result[$sem_tree_id[$i]]) ? " style=\"color:blue;\" " : "")
						. ">&nbsp;-&nbsp;";
				$text = htmlReady(my_substr($this->tree->tree_data[$sem_tree_id[$i]]['name'],0,$cols));
				$ret .=$text . "</option>";
			}
		}
		$ret .= "</select>";
		return $ret;
	}
	
	function getPath($item_id,$delimeter = ">"){
		return $this->tree->getShortPath($item_id);
	}

	
	function getSearchField($attributes = array()){
		$ret = "\n<input type=\"text\" name=\"{$this->form_name}_search_field\" ";
		foreach($attributes as $key => $value){
			$ret .= "$key=\"$value\"";
		}
		$ret .= ">";
		return $ret;
	}
	
	function getSearchButton($attributes = array()){
		$ret = "\n<input border=\"0\" type=\"image\" name=\"{$this->form_name}_do_search\" src=\"pictures/suchen.gif\"" . tooltip(_("Suche nach Studienbereichen starten"));
		foreach($attributes as $key => $value){
			$ret .= "$key=\"$value\"";
		}
		$ret .= ">";
		return $ret;
	}
	
	function getFormStart($action = ""){
		if (!$action){
			$action = $GLOBALS['PHP_SELF'];
		}
		$ret = "\n<form action=\"$action\" method=\"post\" name=\"{$this->form_name}\">";
		return $ret;
	}
	
	function getFormEnd(){
		return "\n</form>";
	}
	
	function doSearch(){
		global $_REQUEST;
		if (isset($_REQUEST[$this->form_name . "_do_search_x"])){
			if(isset($_REQUEST[$this->form_name . "_search_field"]) && strlen($_REQUEST[$this->form_name . "_search_field"]) > 2){
				$this->view->params[0] = "%" . $_REQUEST[$this->form_name . "_search_field"] . "%";
				$this->view->params[1] = $this->sem_tree_ids;
				$rs = $this->view->get_query("view:SEM_TREE_SEARCH_ITEM");
				while($rs->next_record()){
					$this->sem_tree_ranges[$rs->f("parent_id")][] = $rs->f("sem_tree_id");
					$this->sem_tree_ids[] = $rs->f("sem_tree_id");
					$this->search_result[$rs->f("sem_tree_id")] = true;
				}
				$this->num_search_result = $rs->num_rows();
			}
			$this->search_done = true;
		}
		return;
	}
	
	function insertSelectedRanges($selected = null){
		global $_REQUEST;
		if (!$selected){
			for ($i = 0; $i < count($_REQUEST[$this->form_name . "_chooser"]); ++$i){
				if($_REQUEST[$this->form_name . "_chooser"][$i]){
					$selected[] = $_REQUEST[$this->form_name . "_chooser"][$i];
				}
			}
		}
		if (is_array($selected)){
			$count_intersect = count(array_intersect($selected,array_keys($this->selected)));
			if (count($this->selected) != $count_intersect || count($selected) != $count_intersect){
				$count_del = (count($this->selected)) ? $this->tree->DeleteSemEntries(array_keys($this->selected),$this->seminar_id) : 0;
				for ($i = 0; $i < count($selected); ++$i){
					$new_selected[$selected[$i]] = true;
					$count_ins += $this->tree->InsertSemEntry($selected[$i], $this->seminar_id);
				}
				$this->num_inserted = $count_ins - $count_intersect;
				$this->num_deleted = $count_del - $count_intersect;
				$this->selected = $new_selected;
			}
		}
		return;
	}
}
?>
