<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipRangeTree.class.php
// Class to handle structure of the "range tree"
// 
// Copyright (c) 2002 Andr� Noack <noack@data-quest.de> 
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
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES . "/DbView.class.php");
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES . "/DbSnapshot.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/range_tree.view.php");

/**
* class to handle the "range tree"
*
* This class provides an interface to the structure of the "range tree"
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipRangeTree {
	
	/**
	* the name of the root element
	*
	* defaults to the constant $UNI_NAME_CLEAN
	* @access private
	* @var string $root_name
	*/
	var $root_name;
	/**
	* object to handle database queries
	*
	* @access private
	* @var object DbView $view
	*/
	var $view;
	/**
	* array containing all tree items
	*
	* associative array, key is primary key of table range_tree <br>
	* value is another assoc. array containing the other fieldname/fieldvalue pairs
	* @access public
	* @var array	$tree_data
	*/
	var $tree_data = array();
	/**
	* array containing the direct childs of all items
	*
	* assoc. array, key is PK of range_tree, value is numeric array with keys from childs
	* @access private
	* @var array	$tree_childs
	*/
	var $tree_childs = array();
	
	/**
	* static method used to ensure that only one instance exists
	*
	* use this method if you need a reference to the tree object <br>
	* usage: <pre>$my_tree =& StudipRangeTree::GetInstance()</pre>
	* @access public
	* @static
	* @return	object StudipRangeTree
	*/
	function &GetInstance(){
		static $tree_instance;
		if (!is_object($tree_instance)){
			$tree_instance = new StudipRangeTree();
		}
		return $tree_instance;
	}
	
	/**
	* constructor
	*
	* do not use directly, call &GetInstance()
	* @access private
	*/ 
	function StudipRangeTree() {
		$this->root_name = $GLOBALS['UNI_NAME_CLEAN'];
		$this->view = new DbView();
		$this->studip_objects['inst'] = array('pk' => 'Institut_id', 'table' => 'Institute');
		$this->studip_objects['fak'] = array('pk' => 'Institut_id', 'table' => 'Institute');
		$this->init();
		}

	/**
	* initializes the tree
	*
	* stores all rows from table range_tree in array $tree_data
	* @access public
	*/
	function init(){
		$this->tree_childs = array();
		$db = $this->view->get_query("view:TREE_GET_DATA");
		while ($db->next_record()){
			$item_name = $db->f("name");
			if ($db->f("studip_object")){
				$item_name = $db->f("studip_object_name");
			}
			$this->tree_data[$db->f("item_id")] = array("parent_id" => $db->f("parent_id"), 
													"priority" => $db->f("priority"), "name" => $item_name,
													"studip_object" => $db->f("studip_object"),
													"studip_object_id" => $db->f("studip_object_id"),
													"fakultaets_id" => $db->f("fakultaets_id"));

			$this->tree_childs[$db->f("parent_id")][] = $db->f("item_id");
		}
		$item_kids = count($this->tree_childs['root']);
		$this->tree_data['root'] = array('parent_id' => null, 'name' => $this->root_name, 'studip_object_id' => 'root');
	}
	/**
	* returns all direct kids
	*
	* queries the database to get all childs, if no entry in $tree_childs is found
	* @access	public
	* @param	string	$item_id
	* @return	array
	*/
	function getKids($item_id){

		return (is_array($this->tree_childs[$item_id])) ? $this->tree_childs[$item_id] : null;
	}			
	
	/**
	* returns all direct kids and kids of kids and so on...
	*
	* @access	public
	* @param	string	$item_id
	* @param	array	$kidskids	only used in recursion
	* @return	array
	*/
	function getKidsKids($item_id, $kidskids = null){
		if (!$kidskids){
			$kidskids = array();
		}
		$kids = $this->getKids($item_id);
		$kidskids = array_merge($kidskids,$kids); 
		for ($i = 0; $i < count($kids); ++$i){
			$kidskids = $this->getKidsKids($kids[$i],$kidskids);
		}
		return $kidskids;
	}
	
	/**
	* checks if item is the last kid
	*
	* @access	public
	* @param	string	$item_id
	* @return	boolean
	*/
	function isLastKid($item_id){
		$parent_id = $this->tree_data[$item_id]['parent_id'];
		$num_kids = count($this->getKids($parent_id));
		if (!$parent_id || !$num_kids)
			return false;
		else
			return ($this->tree_childs[$parent_id][$num_kids-1] == $item_id);
	}
	
	/**
	* checks if item is the first kid
	*
	* @access	public
	* @param	string	$item_id
	* @return	boolean
	*/
	function isFirstKid($item_id){
		$parent_id = $this->tree_data[$item_id]['parent_id'];
		$num_kids = count($this->getKids($parent_id));
		if (!$parent_id || !$num_kids)
			return false;
		else
			return ($this->tree_childs[$parent_id][0] == $item_id);
	}
	
	/**
	* checks if given item is a kid or kidkid...of given ancestor
	*
	* checks if given item is a kid or kidkid...of given ancestor
	* @access	public
	* @param	string	$ancestor_id
	* @param	string	$item_id
	* @return	boolean
	*/
	function isChildOf($ancestor_id,$item_id){
		return in_array($item_id,$this->getKidsKids($ancestor_id));
	}
	
	/**
	* checks if item has one or more kids
	*
	* @access	public
	* @param	string	$item_id
	* @return	boolean
	*/
	function hasKids($item_id){
		return (count($this->getKids($item_id))) ? true : false;
	}
	
	/**
	* Returns Stud.IP range_id of the next "real" object
	*
	* This function finds the next item wich is a real Stud.IP Object, either an "Einrichtung" or a "Fakultaet"<br>
	* useful for the user rights management
	* @access	public
	* @param	string	$item_id
	* @return	string	is primary key from table "institute" or "fakultaeten"
	*/
	function getAdminRange($item_id){
		if (!$this->tree_data[$item_id])
			return false;
		$found = false;
		$ret = false;
		$next_link = $item_id;
		while(($next_link = $this->getNextLink($next_link)) != 'root'){
			if ($this->tree_data[$next_link]['studip_object'] == 'inst'){
				$found[] = $next_link;
			}
			if ($this->tree_data[$next_link]['studip_object'] == 'fak'){
				if (count($found)){
					for($i = 0; $i < count($found); ++$i){
						if ($this->tree_data[$found[$i]]['fakultaets_id'] == $this->tree_data[$next_link]['studip_object_id']){
							$ret[] = $this->tree_data[$found[$i]]['studip_object_id'];
						}
					}
				$ret[] = $this->tree_data[$next_link]['studip_object_id'];
				} else {
					$ret[] = $this->tree_data[$next_link]['studip_object_id'];
				}
				break;
			}
			$next_link = $this->tree_data[$next_link]['parent_id'];
		}
		if (!$ret){
			$ret[] = $next_link;
		}
		return $ret;
	}
	
	function getNextLink($item_id){
	if (!$this->tree_data[$item_id])
			return false;
		$ret_id = $item_id;
		while (!$this->tree_data[$ret_id]['studip_object_id']){
			$ret_id = $this->tree_data[$ret_id]['parent_id'];
		}
		return $ret_id;
	}
	
	/**
	* Returns tree path
	*
	* returns a string with the item and all parents separated with a slash
	* @access	public
	* @param	string	$item_id
	* @return	string	
	*/
	function getItemPath($item_id){
		if (!$this->tree_data[$item_id])
			return false;
		$path = $this->tree_data[$item_id]['name'];
		while($item_id != "root"){
			$item_id = $this->tree_data[$item_id]['parent_id'];
			$path = $this->tree_data[$item_id]['name'] . " / " . $path;
		}
		return $path;
	}
	
	function getParents($item_id){
		if (!$this->tree_data[$item_id])
			return false;
		if ($item_id == "root")
			return false;
		$ret = array();
		while($item_id != "root"){
			$item_id = $this->tree_data[$item_id]['parent_id'];
			$ret[] = $item_id;
		}
		return $ret;
	}
}

//$test = new StudipRangeTree();
//$test->init();
//echo "<pre>";
//print_r($test->tree_data);
//print_r($test->tree_childs);
//print_r($test->getKidsKids("c4ca4238a06f75849b"));
?>
