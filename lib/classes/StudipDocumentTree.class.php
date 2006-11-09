<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipDocumentTree.class.php
// Class to handle structure of the document folders
// 
// Copyright (c) 2006 Andr� Noack <noack@data-quest.de> 
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
require_once("lib/classes/TreeAbstract.class.php");
require_once("lib/classes/Modules.class.php");
require_once("lib/dbviews/core.view.php");
require_once("functions.php");


/**
* class to handle structure of the document folders
*
* This class provides an interface to the structure of the document folders of one
* Stud.IP entity
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipDocumentTree extends TreeAbstract {
	
	var $range_id;
	var $entity_type;
	var $must_have_perm;
	var $perms = array('x' => 1, 'w' => 2, 'r' => 4);
	var $default_perm = 7;
	var $permissions_activated = false;
	
	/**
	* constructor
	*
	* do not use directly, call &TreeAbstract::GetInstance("StudipDocumentTree")
	* @access private
	*/ 
	function StudipDocumentTree($args) {
		$this->range_id = $args['range_id'];
		$this->entity_type = (!$args['entity_type'] ? get_object_type($this->range_id) : $args['entity_type']);
		if ($args['get_root_name']) list($name,) = array_values(get_object_name($this->range_id, $this->entity_type));
		$this->root_name = $name;
		$this->must_have_perm = $this->entity_type == 'sem' ? 'tutor' : 'autor';
		$this->default_perm = array_sum($this->perms);
		$modules = new Modules();
		$this->permissions_activated = $modules->getStatus('documents_folder_permissions', $this->range_id, $this->entity_type);
		parent::TreeAbstract(); //calling the baseclass constructor 
		$this->tree_data['root']['permission'] = $this->default_perm;
	}

	/**
	* initializes the tree
	*
	* stores all folders in array $tree_data
	* @access public
	*/
	function init(){
		parent::init();
		$p = 0;
		$top_folders[] = array($this->range_id,'FOLDER_GET_DATA_BY_RANGE');
		$top_folders[] = array(md5($this->range_id . 'top_folder'),'FOLDER_GET_DATA_BY_RANGE');
		if ($this->entity_type == 'sem') $top_folders[] = array($this->range_id,'FOLDER_GET_DATA_BY_TERMIN');

		foreach ($top_folders as $folder){
			$this->view->params[0] = $folder[0];
			$db = $this->view->get_query("view:" . $folder[1]);
			while ($db->next_record()){
				$this->storeItem($db->f('range_id'), 'root' , 'virtual' , $p++);
				$this->tree_data[$db->f("range_id")]["permission"] = $this->default_perm;
				$this->tree_data[$db->f("folder_id")]["entries"] = 0;
				$this->tree_data[$db->f("folder_id")]["permission"] = $db->f('permission');
				$this->storeItem($db->f("folder_id"), $db->f('range_id'), $db->f('name'), $p++);
				$this->initSubfolders($db->f("folder_id"));
			}
		}
		if (is_array($this->tree_childs['root'])){
			$this->tree_childs['root'] = array_unique($this->tree_childs['root']);
		}
	}
	
	function initSubfolders($parent_id){
		$view = new DbView();
		$view->params[0] = $parent_id;
		$db = $view->get_query("view:FOLDER_GET_DATA_BY_RANGE");
		$p = 0;
		while($db->next_record()){
			//$this->tree_data[$db->f("folder_id")] = $db->Record;
			$this->tree_data[$db->f("folder_id")]["entries"] = 0;
			$this->tree_data[$db->f("folder_id")]["permission"] = $db->f('permission');
			$this->storeItem($db->f("folder_id"), $parent_id, $db->f('name'), $p++);
			$this->initSubfolders($db->f("folder_id"));
		}
	}
	
	function getPermissionString($folder_id){
		$perm = (int)$this->getValue($folder_id,'permission');
		$r = array_flip($this->perms);
		foreach($this->perms as $v => $p) if(!($perm & $p)) $r[$p] = '-';
		return join('', array_reverse($r));
	}
	
	function checkPermission($folder_id, $perm, $user_id = null){
		if (!$this->permissions_activated || ($user_id && is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id))){
			return true;
		} else {
			return (bool)($this->getValue($folder_id, 'permission') & $this->perms[$perm]);
		}
	}
	
	function setPermission($folder_id, $perm){
		$this->tree_data[$folder_id]['permission'] |= $this->perms[$perm];
		$this->view->params[0] = $this->tree_data[$folder_id]['permission'];
		$this->view->params[1] = $folder_id;
		$db = $this->view->get_query("view:FOLDER_UPDATE_PERMISSION");
		if ($ar = $db->affected_rows()){
			$this->view->params[0] = $folder_id;
			$this->view->get_query("view:FOLDER_UPDATE_CHDATE");
		}
		return $ar;
	}
	
	function unsetPermission($folder_id, $perm){
		$this->tree_data[$folder_id]['permission'] &= ~$this->perms[$perm];
		$this->view->params[0] = $this->tree_data[$folder_id]['permission'];
		$this->view->params[1] = $folder_id;
		$db = $this->view->get_query("view:FOLDER_UPDATE_PERMISSION");
		if ($ar = $db->affected_rows()){
			$this->view->params[0] = $folder_id;
			$this->view->get_query("view:FOLDER_UPDATE_CHDATE");
		}
		return $ar;
	}
	
	function setDefaultPermission($folder_id){
		$this->tree_data[$folder_id]['permission'] = $this->default_perm;
		$this->view->params[0] = $this->tree_data[$folder_id]['permission'];
		$this->view->params[1] = $folder_id;
		$db = $this->view->get_query("view:FOLDER_UPDATE_PERMISSION");
		if ($ar = $db->affected_rows()){
			$this->view->params[0] = $folder_id;
			$this->view->get_query("view:FOLDER_UPDATE_CHDATE");
		}
		return $ar;
	}
	
	function isWritable($folder_id, $user_id = null){
		return $this->checkPermission($folder_id, 'w', $user_id);
	}
	
	function isReadable($folder_id, $user_id = null){
		return $this->checkPermission($folder_id,'r', $user_id);
	}
	
	function isExecutable($folder_id, $user_id = null){
		return $this->checkPermission($folder_id,'x', $user_id);
	}
	
	function getNextSuperFolder($folder_id){
		$parents = $this->getParents($folder_id);
		if (is_array($parents)){
			array_pop($parents);
			foreach($parents as $folder){
				if (!$this->isReadable($folder) || !$this->isExecutable($folder)) return $folder;
			}
		}
		return false;
	}
	
	function isLockedFolder($folder_id, $user_id = null){
		return ((!$this->isReadable($folder_id, $user_id) 
				&& !$this->isWritable($folder_id, $user_id))
				|| !$this->isExecutable($folder_id, $user_id));
	}
	
	function isExerciseFolder($folder_id, $user_id = null){
		return (!$this->isReadable($folder_id, $user_id) 
				&& $this->isWritable($folder_id, $user_id)
				&& $this->isExecutable($folder_id, $user_id));
	}
	
	function isDownloadFolder($folder_id, $user_id = null){
		if (!$this->isExecutable($folder_id, $user_id) || !$this->isReadable($folder_id, $user_id)){
			return false;
		} elseif ( ($s_folder = $this->getNextSuperFolder($folder_id))
		&& (!$this->isExecutable($s_folder, $user_id) || !$this->isReadable($s_folder, $user_id) )) {
			return false;
		} else {
			return true;
		}
	}
	
	function getReadableFolders($user_id){
		if(!$this->permissions_activated || (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id))){
			return $this->getKidsKids('root');
		} else {
			return $this->getReadableKidsKids('root', $user_id);
		}
	}
	
	function getUnreadableFolders($user_id){
		if(!$this->permissions_activated || (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id))){
			return array();
		} else {
			return array_diff($this->getKidsKids('root'), $this->getReadableKidsKids('root', $user_id));
		}
	}
	
	function getReadableKidsKids($item_id, $user_id, $in_recursion = false){
		static $kidskids;
		if (!$kidskids || !$in_recursion){
			$kidskids = array();
		}
		if (!$in_recursion && $item_id != 'root'){
			if (!($this->isReadable($item_id, $user_id) && $this->isExecutable($item_id, $user_id))) return $kidskids;
			else {
				$s_folder = $this->getNextSuperFolder($item_id);
				if ($s_folder && !($this->isReadable($s_folder, $user_id) && $this->isExecutable($s_folder, $user_id))) return $kidskids;
			}
		}
		$num_kids = $this->getNumKids($item_id);
		if ($num_kids){
			$kids = array();
			foreach($this->getKids($item_id) as $one){
				if($this->isReadable($one, $user_id) && $this->isExecutable($one, $user_id)) $kids[] = $one;
			}
			$kidskids = array_merge((array)$kidskids, (array)$kids);
			foreach($kids as $kid){
				$this->getReadableKidsKids($kid,$user_id, true);
			}
		}
		return (!$in_recursion) ? $kidskids : null;
	}
	
}
//test
/*
$f =& TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => '55c88e42f2cbbda0fa55b6c1af6121fc'));
echo "<pre>";
print_r($f->tree_childs);
print_r($f->tree_data);
//echo $f->getPermissionString('823b5c771f17d4103b1828251c29a7cb');
//echo var_dump($f->isWritable('823b5c771f17d4103b1828251c29a7cb'));
//echo "<br>";
//$f->unsetPermission('834499e2b8a2cd71637890e5de31cba3', 'x');
//echo $f->getPermissionString('823b5c771f17d4103b1828251c29a7cb');
//echo var_dump($f->isWritable('823b5c771f17d4103b1828251c29a7cb'));
//echo var_dump($f->getNextSuperFolder('823b5c771f17d4103b1828251c29a7cb'));
echo var_dump($f->getReadableFolders());
//echo var_Dump($f->getKids('823b5c771f17d4103b1828251c29a7cb'));
*/
?>
