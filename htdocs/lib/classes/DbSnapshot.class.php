<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// DbSnapshot.class.php
// Class to provide snapshots of mysql result sets
// Uses PHPLib DB Abstraction
// Copyright (c) 2002 Andr� Noack <andre.noack@gmx.net>
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
* Class to provide snapshots of mysql result sets
*
* Uses DB abstraction layer of PHPLib
*
* @access	public	
* @author	Andr� Noack <andre.noack@gmx.net>
* @version	$Id$
* @package	DBTools
**/
class DbSnapshot {
	
	/**
	* the used db abstraction class
	*
	* 
	* @access	private
	* @var		string	$DbClass
	*/
	var $DbClass = "DB_Sql";
	/**
	* the used db result set
	*
	* 
	* @access	private
	* @var		object DB_Sql	$dbResult
	*/
	var $dbResult = NULL;
	/**
	* array to store the result set
	*
	* 
	* @access	private
	* @var		array	$result
	*/
	var $result = array();
	/**
	* array to store metadata oh the result set
	*
	* 
	* @access	private
	* @var		array	$metaData
	*/
	var $metaData = array();
	/**
	* the number of fields in the result set
	*
	* 
	* @access	public
	* @var		integer	$numFields
	*/
	var $numFields = 0;
	/**
	* the number of rows in the result set
	*
	* 
	* @access	public
	* @var		integer	$numRows
	*/
	var $numRows = 0;
	/**
	* the internal row pointer
	*
	* 
	* @access	private
	* @var		mixed	$pos
	*/
	var $pos = false;
	/**
	* turn on/off debugging
	*
	* 
	* @access	public
	* @var		boolean	$debug
	*/
	var $debug = false;
	
	/**
	* Constructor
	*
	* Pass instance of DbClass or nothing to specify result set later 
	* @access	public
	* @param	object DB_Sql	$dbresult
	*/
	function DbSnapshot($dbresult = NULL){
		if(is_object($dbresult)){
			$this->dbResult = $dbresult;
			$this->getSnapshot();
		}
		
	}
	
	function isDbResult(){
		if(!is_subclass_of($this->dbResult,$this->DbClass))
			$this->halt("Result set has wrong type!");
		if(!$this->dbResult->Query_ID)
			$this->halt("No result set (missing query?)");
		return true;
	}
	
	function getSnapshot(){
		if($this->isDbResult()){
			while($this->dbResult->next_record()){
				$this->result[] = $this->dbResult->Record;
			}
			$this->numFields = $this->dbResult->num_fields();
			$this->numRows = $this->dbResult->num_rows();
			$this->metaData = $this->dbResult->metadata();
			unset($this->dbResult);
			$this->pos = false;
			return true;
		}
		return false;
	}
	
	function nextRow(){
		if(!$this->numRows)
			$this->halt("No snapshot available or empty result!");
		if($this->pos===false){
			$this->pos = 0;
			return true;
		}
		if(++$this->pos < $this->numRows)
			return true;
		else 
			return false;
	}
	
	function resetPos(){
		$this->pos = false;
	}

	function isField($name){
		for ($i = 0; $i < $this->numFields; ++$i) {
			if ($name == $this->metaData[$i]['name']){
				return true;
			}
		}
		return true;
	}
		
	function getRow($row = false){
		if(!$row===false AND !$this->result[$row])
			$this->halt("Snapshot has only ".($this->numRows-1)." rows!");
		return ($row===false) ? $this->result[$this->pos] : $this->result[$row];
	}
	
	function getFieldList(){
		if(!$this->numRows)
			$this->halt("No snapshot available or empty result!");
		$ret = array();
		for ($i = 0; $i < $this->numFields; ++$i) {
			$ret[] = $this->metaData[$i]['name'];
		}
		return $ret;
	}
	
	function getField($field = 0){
		if(!$this->numRows)
			$this->halt("No snapshot available or empty result!");
		return ($this->pos===false) ? false : $this->result[$this->pos][$field];
	}
	
	function getRows($fieldname = 0){
		if(!$this->numRows)
			$this->halt("No snapshot available or empty result!");
		$ret = array();
		for($i = 0; $i < $this->numRows; ++$i){
			$ret[] = $this->result[$i][$fieldname];
		}
		return $ret;
	}
	
	function getDistinctRows($fieldname){
		if (!$this->isField($fieldname))
			$this->halt("Field: $fieldname not found in result set!");
		$ret = array();
		for($i = 0; $i < $this->numRows; ++$i){
			$ret[$this->result[$i][$fieldname]] = $this->result[$i];
			$ret[$this->result[$i][$fieldname]]['row'] = $i;
		}
		return $ret;
	}
	
	function sortRows($fieldname = 0, $order = "ASC", $stype = SORT_REGULAR){
		if(!$this->numRows)
			$this->halt("No snapshot available or empty result!");
		$sortfunc = ($order=="ASC") ? "asort" : "arsort";
		$sortfields = $this->getRows($fieldname);
		$sortfunc($sortfields,$stype);
		$sortresult = array();
		foreach($sortfields as $key => $value){
			$sortresult[] = $this->result[$key];
		}
		$this->result = $sortresult;
		$this->resetPos();
		return true;
	}
	
	function searchFields($fieldname, $searchstr){
		if(!$this->numRows)
			$this->halt("No snapshot available or empty result!");
		$ret = false;
		$sortfields = $this->getRows($fieldname);
		foreach($sortfields as $key => $value){
			if(preg_match($searchstr,$value)) {
				$ret = true;
				$this->pos = $key;
				break;
			}
		}
		return $ret;
	}
	
	function getGroupedResult($group_by_field, $fields_to_group = null){
		if (!$this->numRows)
			$this->halt("No snapshot available or empty result!");
		$fieldlist = $this->getFieldList();
		if (!in_array($group_by_field,$fieldlist))
			$this->halt("group_by_field not found in result set!");
		if (is_array($fields_to_group))
			$fieldlist = $fields_to_group;
		$num_fields = count($fieldlist);
		$ret = array();
		for($i = 0; $i < $this->numRows; ++$i){
			for ($j = 0; $j < $num_fields; ++$j){
				if ($fieldlist[$j] != $group_by_field){
					++$ret[$this->result[$i][$group_by_field]][$fieldlist[$j]][$this->result[$i][$fieldlist[$j]]];
				}
			}
		}
		return $ret;
	}
		
	function mergeSnapshot($m_snap, $key_field = false, $mode = "ADD"){
		if ($mode == "ADD"){
			for ($i = 0 ;$i < $m_snap->numRows; ++$i){
				$this->result[] = $m_snap->result[$i];
			}
		} elseif ($mode == "AND"){
			if (!$this->numRows || !$m_snap->numRows){
					$this->result = array();
				} elseif ($m_snap->numRows) {
					$m_result = $m_snap->getDistinctRows($key_field);
					for ($i = 0 ;$i < $this->numRows; ++$i){
						if (!($m_result[$this->result[$i][$key_field]] && $this->result[$i][$key_field])){
							unset($this->result[$i]);
						}
					}
				}
		} elseif ($mode == "OR"){
			if (!$this->numRows){
				$this->result = $m_snap->result;
			} elseif ($m_snap->numRows) {
				$result = $this->getDistinctRows($key_field);
				for ($i = 0 ;$i < $m_snap->numRows; ++$i){
					if (!$result[$m_snap->result[$i][$key_field]]){
						$this->result[] = $m_snap->result[$i];
					}
				}
			}
		}
		$this->result = array_merge(array(),$this->result);
		$this->numRows = count($this->result);
		$this->resetPos();
		return $this->numRows;
	}
	/**
	* print error message and exit script
	*
	* @access	private
	* @param	string	$msg	the message to print
	*/
	function halt($msg){
		echo "<hr>$msg<hr>";
		if ($this->debug){
			echo "<pre>";
			print_r($this);
			echo "</pre>";
			die;
		}

	}	
}
//
//$db = new DB_Seminar("SELECT * FROM auth_user_md5 WHERE username like 'suchi%' LIMIT 10");
//$snap1 = new DbSnapshot($db);
//$db->query("SELECT * FROM auth_user_md5 WHERE username like 'suchi@%' OR username like 'anoack' LIMIT 10");
//$snap2 = new DbSnapshot($db);
//echo "<pre>";
//print_r($snap1->result);
//print_r($snap2->result);
//echo "\n" .$snap1->mergeSnapshot($snap2,"user_id","OR") . "\n";
//print_r($snap1->result);
//$snap1->sortRows("Nachname","DESC",SORT_STRING);
//print_r($snap1->result);
//if ($snap1->searchFields("Nachname","/noack/i")) echo "Gefunden: ".$snap->getField("Vorname")." ".$snap->getField("Nachname");
//print_r($snap1->getRow());
//print_r($snap1->getFieldList());
//$snap1->resetPos();
//while($snap1->nextRow()) print($snap1->getField("username")."\n");
//print_r($snap1->getRow(4));
//
?>
