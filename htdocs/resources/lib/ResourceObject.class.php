<?
/**
* ResourceObject.class.php
* 
* class for a resource-object
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		ResourceObject.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourceObject.class.php
// Klasse fuer ein Ressourcen-Object
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignObject.class.php");


/*****************************************************************************
ResourceObject, zentrale Klasse der Ressourcen Objekte
/*****************************************************************************/
class ResourceObject {
	var $id;				//resource_id des Objects;
	var $db;				//Datenbankanbindung;
	var $name;				//Name des Objects
	var $description;			//Beschreibung des Objects;
	var $owner_id;				//Owner_id;
	var $category_id;			//Die Kategorie des Objects
	var $category_name;			//name of the assigned catgory
	var $category_iconnr;			//iconnumber of the assigned catgory
	var $category_id;			//Die Kategorie des Objects
	
	//Konstruktor
	function ResourceObject($name='', $description='', $parent_bind='', $root_id='', $parent_id='', $category_id='', $owner_id='', $id = '') {
		global $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;
		
		if(func_num_args() == 1) {
			$id = func_get_arg(0);
			$this->restore($id);
		} elseif(func_num_args() == 7) {
			$this->name = func_get_arg(0);
			$this->description = func_get_arg(1);
			$this->parent_bind = func_get_arg(2);
			$this->root_id = func_get_arg(3);
			$this->parent_id = func_get_arg(4);
			$this->category_id = func_get_arg(5);
			$this->owner_id = func_get_arg(6);
			if (!$this->id)
				$this->id=$this->createId();
			if (!$this->root_id) {
				$this->root_id = $this->id;
				$this->parent_id = "0";
			}
			$this->changeFlg=FALSE;

		}
	}

	function createId() {
		return md5(uniqid("DuschDas"));
	}

	function create() {
		$query = sprintf("SELECT resource_id FROM resources_objects WHERE resource_id ='%s'", $this->id);
		$this->db->query($query);
		if ($this->db->nf()) {
			$this->chng_flag=TRUE;		
			return $this->store();
		} else
			return $this->store(TRUE);
	}
	
	function setName($name){
		$this->name= $name;
		$this->chng_flag = TRUE;
	}

	function setDescription($description){
		$this->description= $description;
		$this->chng_flag = TRUE;
	}

	function setCategoryId($category_id){
		$this->category_id=$category_id;
		$this->chng_flag = TRUE;
	}

	function setMultipleAssign($value){
		if ($value)
			$this->multiple_assign=TRUE;
		else
			$this->multiple_assign=FALSE;
		$this->chng_flag = TRUE;
	}

	function setParentBind($parent_bind){
		if ($parent_bind==on)
			$this->parent_bind=TRUE;
		else
			$this->parent_bind=FALSE;
		$this->chng_flag = TRUE;
	}

	function setLockable($lockable){
		if ($lockable == on)
			$this->lockable=TRUE;
		else
			$this->lockable=FALSE;
		$this->chng_flag = TRUE;
	}

	function setOwnerId($owner_id){
		$old_value = $this->owner_id;
		$this->owner_id=$owner_id;
		$this->chng_flag = TRUE;
		if ($old_value != $owner_id)
			return TRUE;
		else
			return FALSE;
	}
	
	function setInstitutId($institut_id){
		$this->institut_id=$institut_id;
		$this->chng_flag = TRUE;
	}


	function getId() {
		return $this->id;
	}

	function getRootId() {
		return $this->root_id;
	}

	function getParentId() {
		return $this->parent_id;
	}

	function getName() {
		return $this->name;
	}

	function getCategoryName() {
		return $this->category_name;
	}

	function getCategoryIconnr() {
		return $this->category_iconnr;
	}

	function getCategoryId() {
		return $this->category_id;
	}

	function getDescription() {
		return $this->description;
	}

	function getOwnerId() {
		return $this->owner_id;
	}

	function getInstitutId() {
		return $this->institut_id;
	}
	
	function getMultipleAssign() {
		return $this->multiple_assign;
	}
	
	function getParentBind() {
		return $this->parent_bind;
	}
	
	function getOwnerType($id='') {
		if (!$id)
			$id=$this->owner_id;

		//Is it a global?
		if ($id == "global")
			return "global";

		//Is it a entry for "everyone"?
		if ($id == "all")
			return "all";
		
		//Ist es eine Veranstaltung?
		$query = sprintf("SELECT Seminar_id FROM seminare WHERE Seminar_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "sem";

		//Ist es ein Nutzer?
		$query = sprintf("SELECT user_id FROM auth_user_md5 WHERE user_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "user";
		
		//Ist es ein Termin?
		$query = sprintf("SELECT termin_id FROM termine WHERE termin_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "date";

		//Ist es ein Institut?
		$query = sprintf("SELECT Institut_id FROM Institute WHERE Institut_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "inst";
	}
	
	function getOrgaName ($explain=FALSE, $id='') {
		if (!$id)
			$id=$this->institut_id;

		$query = sprintf("SELECT Name FROM Institute WHERE Institut_id='%s' ",$id);
		$this->db->query($query);
		
		if ($this->db->next_record())
			if (!$explain)
				return $this->db->f("Name");
			else
				return $this->db->f("Name")." ("._("Einrichtung").")";	
	}
	
	function getOwnerName($explain=FALSE, $id='') {
		if (!$id)
			$id=$this->owner_id;

		switch ($this->getOwnerType($id)) {
			case "all":
				if (!$explain)
					return _("jederR");
				else
					return _("jedeR (alle Nutzenden)");
			break;
			case "global":
				if (!$explain)
					return _("Global");
				else
					return _("Global (zentral verwaltet)");
			break;
			case "user";
				if (!$explain)
					return get_fullname($id);
				else
					return get_fullname($id)." ("._("NutzerIn").")";
			break;
			case "inst":
				$query = sprintf("SELECT Name FROM Institute WHERE Institut_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." ("._("Einrichtung").")";
			break;
			case "sem":
				$query = sprintf("SELECT Name FROM seminare WHERE Seminar_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name"). " ("._("Veranstaltung").")";
			break;
		}
	}
	
	function getLink($quick_view = FALSE, $view ="view_schedule", $view_mode = "no_nav") {
		if (!$id)
			$id=$this->id;
		return  sprintf ("resources.php?actual_object=%s&%sview=%s&%sview_mode=%s", $id, ($quick_view) ? "quick_" : "", $view, ($quick_view) ? "quick_" : "", $view_mode);	
	}
	
	function getFormattedLink($javaScript = TRUE, $target_new = TRUE, $quick_view = FALSE, $view ="view_schedule", $view_mode = "no_nav") {
		if ($this->id) {
			if (!$javaScript)
				return "<a ".(($target_new) ? "target=\"_new\"" : "")." href=\"".$this->getLink($quick_view, $view, $view_mode)."\">".$this->getName()."</a>";
			else
				return "<a href=\"".$PHP_SELF."#\" onClick=\"javascript:window.open('".$this->getLink($quick_view, $view, $view_mode)."','"._("Ressource anzeigen und bearbeiten")."','scrollbars=yes,width=1000,height=700,resizable=yes');\" >".$this->getName()."</a>";
		} else
			return FALSE;
	}
	
	function getOrgaLink ($id='') {
		if (!$id)
			$id=$this->institut_id;
		
		return  sprintf ("institut_main?auswahl=%s",$id);	
	}

	
	function getOwnerLink($id='') {
		global $PHP_SELF;
		
		if (!$id)
			$id=$this->owner_id;
		switch ($this->getOwnerType($id)) {
			case "global":
				return $PHP_SELF;
			case "all":
				return $PHP_SELF;
			break;
			case "user":
				return  sprintf ("about?username=%s",get_username($id));
			break;
			case "inst":
				return  sprintf ("institut_main?auswahl=%s",$id);
			break;
			case "sem":
				return  sprintf ("seminar_main?auswahl=%s",$id);
			break;
		}
	}
	
	function getPlainProperties($only_requestable = FALSE) {
		$query = sprintf("SELECT b.name, a.state, b.type, b.options FROM resources_objects_properties a LEFT JOIN resources_properties b USING (property_id) LEFT JOIN resources_categories_properties c USING (property_id) WHERE resource_id = '%s' AND c.category_id = '%s' %s ORDER BY b.name", $this->id, $this->category_id, ($only_requestable) ? "AND requestable = '1'" : "");		
		$this->db->query($query);
		
		$i=0;
		while ($this->db->next_record()) {
			if ($i)
				$plain_properties .= " \n";
			$plain_properties .= $this->db->f("name").": ".(($this->db->f("type") == "bool") ? (($this->db->f("state")) ? $this->db->f("options") : "-") : $this->db->f("state"));
			$i++;
		}
		
		return $plain_properties;
	}
	
	function isUnchanged() {
		if ($this->mkdate == $this->chdate)
			return TRUE;
		else
			return FALSE;
	}

	function isDeletable() {
		if ($this->isParent())
			return FALSE;
		else
			return TRUE;
	}

	function isParent() {
		$db = new DB_Seminar;
		$query = sprintf ("SELECT resource_id FROM resources_objects WHERE parent_id = '%s'", $this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else 
			return FALSE;
	}
	
	function isRoom() {
		$db = new DB_Seminar;
		$query = sprintf ("SELECT is_room FROM resources_objects LEFT JOIN resources_categories USING (category_id) WHERE resource_id = '%s'", $this->id);
		$this->db->query($query);
		$this->db->next_record();
		if ($this->db->f("is_room"))
			return TRUE;
		else 
			return FALSE;
	}
	
	function isLocked() {
		if (($this->isRoom()) 
		&& ($this->isLockable())
		&& (isLockPeriod()))
			return TRUE;
		else
			return FALSE;
	}

	function isLockable() {
		return $this->lockable;
	}
	
	function flushProperties() {
		$query = sprintf("DELETE FROM resources_objects_properties WHERE resource_id='%s' ",$this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else 
			return FALSE;
	}
	
	function storeProperty ($property_id, $state) {
		$query = sprintf("INSERT INTO resources_objects_properties SET resource_id='%s', property_id='%s', state='%s' ",$this->id, $property_id, $state);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else 
			return FALSE;
	}
	
	function deletePerms ($user_id) {
		$query = sprintf("DELETE FROM resources_user_resources WHERE user_id='%s' AND resource_id='%s'",$user_id, $this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else 
			return FALSE;
	}
	
	function storePerms ($user_id, $perms='') {
		$query = sprintf("SELECT user_id FROM resources_user_resources WHERE user_id='%s' AND resource_id='%s'",$user_id, $this->id);
		$this->db->query($query);
		
		//User_id zwingend notwendig
		if (!$user_id)
			return FALSE;
		
		//neuer Eintrag	
		if (!$this->db->num_rows()) {
			if (!$perms)
				$perms="autor";
			$query = sprintf("INSERT INTO resources_user_resources SET perms='%s', user_id='%s', resource_id='%s'",$perms, $user_id, $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;

		//alter Eintrag wird veraendert
		} elseif ($perms) {
			$query = sprintf("UPDATE resources_user_resources SET perms='%s' WHERE user_id='%s' AND resource_id='%s'",$perms, $user_id, $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;
		} else
			return FALSE;
	}
	
	function restore($id='') {

		if(func_num_args() == 1)
			$query = sprintf("SELECT resources_objects.*, resources_categories.name AS category_name, resources_categories.iconnr FROM resources_objects LEFT JOIN resources_categories USING (category_id) WHERE resource_id='%s' ",$id);
		else 
			$query = sprintf("SELECT resources_objects.*, resources_categories.name AS category_name, resources_categories.iconnr FROM resources_objects LEFT JOIN resources_categories USING (category_id) WHERE resource_id='%s' ",$this->id);
		$this->db->query($query);
		
		if($this->db->next_record()) {
			$this->id = $id;
			$this->name = $this->db->f("name");
			$this->description = $this->db->f("description");
			$this->owner_id = $this->db->f("owner_id");
			$this->institut_id = $this->db->f("institut_id");
			$this->category_id = $this->db->f("category_id");
			$this->category_name = $this->db->f("category_name");
			$this->category_iconnr = $this->db->f("iconnr");
			$this->parent_id =$this->db->f("parent_id");
			$this->lockable = $this->db->f("lockable");
			$this->multiple_assign = $this->db->f("multiple_assign");
			$this->root_id =$this->db->f("root_id");
			$this->mkdate =$this->db->f("mkdate");
			$this->chdate =$this->db->f("chdate");
			
			if ($this->db->f("parent_bind"))
				$this->parent_bind = TRUE;
			else
				$this->parent_bind = FALSE;
			
			return TRUE;
		}
		return FALSE;
	}

	function store($create=''){
		// Natuerlich nur Speichern, wenn sich was gaendert hat oder das Object neu angelegt wird
		if(($this->chng_flag) || ($create)) {
			$chdate = time();
			$mkdate = time();
			
			if($create) {
				//create level value
				if (!$this->parent_id)
					$level=0;
				else {
					$query = sprintf("SELECT level FROM resources_objects WHERE resource_id = '%s'", $this->parent_id);
					$this->db->query($query);
					$this->db->next_record();
					$level = $this->db->f("level") +1;
				}

				$query = sprintf("INSERT INTO resources_objects SET resource_id='%s', root_id='%s', " 
					."parent_id='%s', category_id='%s', owner_id='%s', institut_id = '%s', level='%s', name='%s', description='%s', "
					."lockable='%s', multiple_assign='%s',mkdate='%s', chdate='%s' "
							 , $this->id, $this->root_id, $this->parent_id, $this->category_id, $this->owner_id, $this->institut_id
							 , $level, $this->name, $this->description, $this->lockable, $this->multiple_assign 
							 , $mkdate, $chdate);
			} else
				$query = sprintf("UPDATE resources_objects SET root_id='%s'," 
					."parent_id='%s', category_id='%s', owner_id='%s', institut_id = '%s', name='%s', description='%s', "
					."lockable='%s', multiple_assign='%s' WHERE resource_id='%s' "
							 , $this->root_id, $this->parent_id, $this->category_id, $this->owner_id, $this->institut_id
							 , $this->name, $this->description, $this->lockable, $this->multiple_assign 
							 , $this->id);
			$this->db->query($query);

			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE resources_objects SET chdate='%s' WHERE resource_id='%s' ", $chdate, $this->id);
				$this->db->query($query);
				return TRUE;
			} else
				return FALSE;
		}
		return FALSE;
	}

	function delete() {
		$this->deleteResourceRecursive ($this->id);
	}
	
	//delete section, very privat :)
	
	//private
	function deleteAllAssigns($id='') {
		if (!$id)
			$id = $this->id;
		$query = sprintf("SELECT assign_id FROM resources_assign WHERE resource_id = '%s' ", $id);
		$this->db->query($query);
		while ($this->db->next_record()) {
			$killAssign = new AssignObject ($this->db->f("assign_id"));
			$killAssign->delete();
		}
	}

	//private
	function deleteAllPerms($id='') {
		if (!$id)
			$id = $this->id;
		$query = sprintf("DELETE FROM resources_user_resources WHERE resource_id = '%s' ", $id);
		$this->db->query($query);			
	}

	function deleteResourceRecursive($id) {
		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		
		//subcurse to subordinated resource-levels
		$query = sprintf("SELECT resource_id FROM resources_objects WHERE parent_id = '%s' ", $id);
		$db->query($query);
			
		while ($db->next_record()) 
			$this->deleteResourceRecursive($db->f("resource_id"), $recursive);

		$this->deleteAllAssigns($id);
		$this->deleteAllPerms($id);
		$this->flushProperties($id);
	
		$query2 = sprintf("DELETE FROM resources_objects WHERE resource_id = '%s' ", $id);
		$db2->query($query2);			
	}
}