<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// TreeAbstract.class.php
// Abstract Base Class to handle in-memory tree structures
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

/**
* Abstract Base Class to handle in-memory tree structures
*
* This class provides an interface to basic handling of structure of tree structures
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class TreeAbstract {
	
	/**
	* the name of the root element
	*
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
	* associative array, key is an unique identifier (eg primary key from DB table) 
	* value is another assoc. array containing the other fieldname/fieldvalue pairs
	* these fieldnames must be used : 
	* parent_id, name, priority
	* @access public
	* @var array	$tree_data
	*/
	var $tree_data = array();
	/**
	* array containing the direct childs of all items
	*
	* assoc. array, key is one from $tree_data, value is numeric array with keys from childs
	* @access private
	* @var array	$tree_childs
	*/
	var $tree_childs = array();
	
	/**
	* array containing the number of direct childs of all items
	*
	* assoc. array, key is one from $tree_data
	* @access private
	* @var array	$tree_num_childs
	*/
	var $tree_num_childs = array();
	
	/**
	* static method used to ensure that only one instance exists
	*
	* use this method if you need a reference to the tree object <br>
	* usage: <pre>$my_tree =& StudipRangeTree::GetInstance("name_of_tree_class")</pre>
	* @access public
	* @static
	* @param	string	$class_name		the name of the used tree_class
	* @param	mixed	$args			argumentlist passed to the constructor in the tree_class (if needed)
	* @return	mixed	always an object, type is one of AbstractTree s childclasses 
	*/
	function &GetInstance($class_name, $args = null){
		static $tree_instance;
		
		if ($args){
			$class_hash = $class_name . "_" . md5(serialize($args));
		} elseif ($args === false && is_array($tree_instance)){
			foreach ($tree_instance as $key => $value){
				$tmp_name = explode("_",$key);
				if ($tmp_name[0] == $class_name){
					$class_hash = $key;
					break;
				}
			}
			if (!$class_hash){
				$class_hash = $class_name;
			}
		} else {
			$class_hash = $class_name;
		}
		if (!is_object($tree_instance[$class_hash])){
			$tree_instance[$class_hash] = new $class_name($args);
		}
		return $tree_instance[$class_hash];
	}
	
	/**
	* constructor
	*
	* do not use directly, call &GetInstance()
	* @access private
	*/ 
	function TreeAbstract() {
		$this->view = new DbView();
		$this->init();
		}

	/**
	* initializes the tree
	*
	* stores all tree items in array $tree_data
	* must be overriden
	* @access public
	*/
	function init(){
		$this->tree_childs = array();
		$this->tree_num_childs = array();
		$this->tree_data = array();
		$this->tree_data['root'] = array('parent_id' => null, 'name' => $this->root_name);
	}
	
	/**
	* store one item in tree_data array
	*
	* store one item in tree_data array
	* @access	public
	* @param	string	$item_id
	* @param	string	$parent_id
	* @param	string	$name
	* @param	integer	$priority
	* 
	*/
	
	function storeItem($item_id,$parent_id,$name,$priority){
		$this->tree_data[$item_id]["parent_id"] = $parent_id; 
		$this->tree_data[$item_id]["priority"] = $priority;
		$this->tree_data[$item_id]["name"] = $name;
		$this->tree_childs[$parent_id][] = $item_id;
		return;
	}
	
	/**
	* returns all direct kids
	*
	* 
	* @access	public
	* @param	string	$item_id
	* @return	array
	*/
	function getKids($item_id){

		return (is_array($this->tree_childs[$item_id])) ? $this->tree_childs[$item_id] : null;
	}			
	
	/**
	* returns the number of all direct kids
	*
	* 
	* @access	public
	* @param	string	$item_id
	* @param	bool	$in_recursion
	* @return	int
	*/
	function getNumKids($item_id){
		if(!isset($this->tree_num_childs[$item_id])){
			$this->tree_num_childs[$item_id] = (is_array($this->tree_childs[$item_id])) ? count($this->tree_childs[$item_id]) : 0;
		}
		return $this->tree_num_childs[$item_id];
	}
	
	/**
	* returns all direct kids and kids of kids and so on...
	*
	* @access	public
	* @param	string	$item_id
	* @param	bool	$in_recursion	only used in recursion
	* @return	array
	*/
	function getKidsKids($item_id, $in_recursion = false){
		static $kidskids;
		if (!$kidskids || !$in_recursion){
			$kidskids = array();
		}
		$num_kids = $this->getNumKids($item_id);
		if ($num_kids){
			$kids = $this->getKids($item_id);
			$kidskids = array_merge($kidskids,$kids);
			for ($i = 0; $i < $num_kids; ++$i){
				$this->getKidsKids($kids[$i],true);
			}
		}
		return (!$in_recursion) ? $kidskids : null;
	}
	
	/**
	* returns the number of all kids and kidskids...
	*
	* 
	* @access	public
	* @param	string	$item_id
	* @param	bool	$in_recursion
	* @return	int
	*/
	function getNumKidsKids($item_id, $in_recursion = false){
		static $num_kidskids;
		if (!$num_kidskids || !$in_recursion){
			$num_kidskids = 0;
		}
		$num_kids = $this->getNumKids($item_id);
		if ($num_kids){
			$kids = $this->getKids($item_id);
			$num_kidskids += $num_kids;
			for ($i = 0; $i < $num_kids; ++$i){
				$this->getNumKidsKids($kids[$i],true);
			}
		}
		return (!$in_recursion) ? $num_kidskids : null;
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
		$num_kids = $this->getNumKids($parent_id);
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
		$num_kids = $this->getNumKids($parent_id);
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
		return ($this->getNumKids($item_id)) ? true : false;
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
	
	/**
	* Returns tree path as array of item_id s
	*
	* returns an array containing all parents of given item
	* @access	public
	* @param	string	$item_id
	* @return	array
	*/
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
?>
