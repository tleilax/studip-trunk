<?
/*
resourcesClasses.php - 0.8
Klassen fuer Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

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

/*****************************************************************************
resourceObjeckt, zentrale Klasse der Ressourcen Objecte
/*****************************************************************************/
class resourceObject {
	var $id;					//resource_id des Objects;
	var $db;					//Datenbankanbindung;
	var $name;				//Name des Objects
	var $description;			//Beschreibung des Objects;
	var $owner_id;			//Owner_id;
	var $category_id;			//Die Kategorie des Objects
	var $invetar_num;			//Die Inventarnummer des Objects;
	var $parent_bind=FALSE;	//Verkn&uuml;pfung mit Parent?

	
	//Konstruktor
	function resourceObject($name='', $description='', $inventar_num='', $parent_bind='', $root_id='', $parent_id='', $category_id='', $owner_id='', $resource_id='') {
		global $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;
		
		if(func_num_args() == 1) {
			$id = func_get_arg(0);
			$this->restore($id);
		} elseif(func_num_args() == 8) {
			$this->name = func_get_arg(0);
			$this->description = func_get_arg(1);
			$this->inventar_num = func_get_arg(2);
			$this->parent_bind = func_get_arg(3);
			$this->root_id = func_get_arg(4);
			$this->parent_id = func_get_arg(5);
			$this->category_id = func_get_arg(6);
			$this->owner_id = func_get_arg(7);
			$this->id=$this->createId();
			if (!$this->root_id)
			$this->root_id = $this->id;
			$this->changeFlg=FALSE;
		}
	}

	function createId() {
		return md5(uniqid("DuschDas"));
	}

	function createObject() {
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

	function setInventarNum($inventar_num){
		$this->inventar_num= $inventar_num;
		$this->chng_flag = TRUE;
	}

	function setParentBind($parent_bind){
		if ($parent_bind==on)
			$this->parent_bind=TRUE;
		else
			$this->parent_bind=FALSE;
		$this->chng_flag = TRUE;
	}

	function setOwnerId($owner_id){
		$this->owner_id=$owner_id;
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

	function getCategory() {
		$query = sprintf("SELECT name FROM resources_categories WHERE category_id='%s' ",$this->category_id);
		$this->db->query($query);
		if ($this->db->next_record())
			return $this->db->f("name");
		else
			return FALSE;
	}

	function getDescription() {
		return $this->description;
	}

	function getOwnerId() {
		return $this->owner_id;
	}

	function getCategoryId() {
		return $this->category_id;
	}

	function getInventarNum() {
		return $this->inventar_num;
	}

	function getParentBind() {
		return $this->parent_bind;
	}
	
	function getOwnerType($id='') {
		if (!$id)
			$id=$this->owner_id;
			
		//Ist es ein Nutzer?
		$query = sprintf("SELECT user_id FROM auth_user_md5 WHERE user_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "user";
		
		//Ist es ein Institut?
		$query = sprintf("SELECT Institut_id FROM Institute WHERE Institut_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "inst";

		//Ist es eine Verabstaltung?
		$query = sprintf("SELECT Seminar_id FROM seminare WHERE Seminar_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "sem";

		//Ist es eine Fakultaet?
		$query = sprintf("SELECT Fakultaets_id FROM Fakultaeten WHERE Fakultaets_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "fak";
		
		//dann wohl global
		return "global";
	}
	
	function getOwnerName($explain=FALSE, $id='') {
		if (!$id)
			$id=$this->owner_id;
		
		switch ($this->getOwnerType($id)) {
			case "global":
				return "Global";
			break;
			case "user";
				if (!$explain)
					return get_fullname($id);
				else
					return get_fullname($id)." (Nutzer)";
			break;
			case "inst":
				$query = sprintf("SELECT Name FROM Institute WHERE Institut_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." (Institut)";
			break;
			case "fak":
				$query = sprintf("SELECT Name FROM Fakultaeten WHERE Fakultaets_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." (Fakult&auml;t)";
			break;
			case "sem":
				$query = sprintf("SELECT Name FROM seminare WHERE Seminar_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name"). " (Veranstaltung)";	
			break;
		}
	}
	
	function getOwnerLink($id='') {
		if (!$id)
			$id=$this->owner_id;
		switch ($this->getOwnerType($id)) {
			case "global":
				return FALSE;
			break;
			case "user":
				return  sprintf ("about?username=%s",$id);
			break;
			case "inst":
				return  sprintf ("institut_main?auswahl=%s",$id);
			break;
			case "fak":
				return FALSE;
			break;
			case "sem":
				return  sprintf ("seminar_main?auswahl=%s",$id);
			break;
		}
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
				$perms="user";
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
			$query = sprintf("SELECT * FROM resources_objects WHERE resource_id='%s' ",$id);
		else 
			$query = sprintf("SELECT * FROM resources_objects WHERE resource_id='%s' ",$this->id);
		$this->db->query($query);
		
		if($this->db->next_record()) {
			$this->id = $id;
			$this->name = $this->db->f("name");
			$this->description = $this->db->f("description");
			$this->owner_id = $this->db->f("owner_id");
			$this->category_id = $this->db->f("category_id");
			$this->inventar_num = $this->db->f("inventar_num");
			$this->parent_id =$this->db->f("parent_id");
			$this->root_id =$this->db->f("root_id");
			
			if ($this->db->f("parent_bind"))
				$this->parent_bind = TRUE;
			else
				$this->parent_bind = FALSE;
			
			return TRUE;
		}
		return FALSE;
	}

	function store($create=''){
		// Nat�rlich nur Speichern, wenn sich was ge�ndert hat oder das Object neu angelegt wird
		if(($this->chng_flag) || ($create)) {
			$chdate = time();
			$mkdate = time();
			if($create)
				$query = sprintf("INSERT INTO resources_objects SET resource_id='%s', root_id='%s', " 
					."parent_id='%s', category_id='%s', owner_id='%s', name='%s', description='%s', "
					."inventar_num='%s', parent_bind='%s', mkdate='%s', chdate='%s' "
							 , $this->id, $this->root_id, $this->parent_id, $this->category_id, $this->owner_id
							 , $this->name, $this->description, $this->inventar_num, $this->parent_bind
							 , $mkdate, $chdate);
			else
				$query = sprintf("UPDATE resources_objects SET root_id='%s'," 
					."parent_id='%s', category_id='%s', owner_id='%s', name='%s', description='%s', "
					."inventar_num='%s', parent_bind='%s', chdate='%s' WHERE resource_id='%s' "
							 , $this->root_id, $this->parent_id, $this->category_id, $this->owner_id
							 , $this->name, $this->description, $this->inventar_num, $this->parent_bind
							 , $chdate, $this->id);
			if($this->db->query($query))
				return TRUE;
			return FALSE;
		}
		return FALSE;
	}

	function delete() {
		$query = sprintf("DELETE FROM resources_objects WHERE resource_id='%s'", $this->id);
		if($this->db->query($query))
			return TRUE;
		return FALSE;
	}
	
}

/*****************************************************************************
resourcesPerms, stellt Perms zur Verfuegung
/*****************************************************************************/

class resourcesPerms {
	var $user_id;
	var $db;
	var $master_string="all";			//So wird der Ressourcen-Root abgelegt
	
	function resourcesPerms ($user_id='') {
		global $user;
		
		$this->db=new DB_Seminar;
		if ($user_id)
			$this->$user_id=$user_id;
		else
			$this->user_id=$user->id;
	}
	
	function getGlobalPerms () {
		$this->db->query("SELECT user_id, perms FROM resources_user_resources WHERE user_id='$this->user_id' AND resource_id = '$this->master_string' ");
		if ($this->db->next_record() && $this->db->f("perms")) 
			return $this->db->f("perms");
		else
			return FALSE;
	}
			
}

/*****************************************************************************
resourcesUser, stellt Stamm-Ressourcen zur Verfuegung
/*****************************************************************************/

class resourcesUser {
	var $user_global_perm;			//Globaler Status des Users, fuer den Klasse initiert wird
	var $user_id;					//User_id des Users;
	var $my_roots;					//Alle meine Ressourcen-Staemme
	
	//Konstruktor
	function resourcesUser($user_id='') {
		global $user, $perm, $auth;
		
		$db=new DB_Seminar;
		$db2=new DB_Seminar;
		$db3=new DB_Seminar;
		
		if(func_num_args() == 1){
			$this->user_id = func_get_arg(0);
		}
		
		if (!$this->user_id)
			$this->user_id=$user->id;
			
		$this->user_global_perm=get_global_perm($this->user_id);
		
		//Bestimmen aller Root Straenge auf die ich Zugriff habe
		switch ($this->user_global_perm) {
			case "root": 
				 //Root hat Zugriff auf alles, also alle Stamm-Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
			case "admin": 
				//Alle meine Institute...
				$db->query("SELECT Institut_id, inst_perms FROM user_inst WHERE inst_perms IN ('tutor', 'dozent', 'admin') AND user_inst.user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Institut_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
					if ($db->f("inst_perms") == "admin") {
						//...alle Seminare meiner Institute, in denen ich Admin bin....
						$db2->query("SELECT Seminar_id FROM seminar_inst WHERE institut_id = '".$db->f("Institut_id")."' ");
						while ($db2->next_record()) {
							$db3->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db2->f("Seminar_id")."' AND parent_id='0'");
								while ($db3->next_record())
									$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
						}
						//...alle Mitarbeiter meiner Institute, in denen ich Admin bin....
						$db2->query("SELECT user_id FROM user_inst WHERE Institut_id = '".$db->f("Institut_id")."' AND inst_perms IN ('autor', 'tutor', 'dozent') ");
						while ($db2->next_record()) {
							$db3->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db2->f("user_id")."' AND parent_id='0'");
								while ($db3->next_record())
									$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
						}
						
					}
				}
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");					
			break;
			case "dozent": 
				//Alle meine Institute...
				$db->query("SELECT Institut_id FROM user_inst WHERE inst_perms IN ('tutor', 'dozent') AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Institut_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//..und alle meine Seminare
				$db->query("SELECT Seminar_id FROM seminar_user WHERE status IN ('tutor', 'dozent') AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Seminar_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
			case "tutor": 
				//Alle meine Institute...
				$db->query("SELECT Institut_id FROM user_inst WHERE inst_perms='tutor' AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Institut_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//..und alle meine Seminare
				$db->query("SELECT Seminar_id FROM seminar_user WHERE status='tutor' AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Seminar_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
			case "autor": 
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
		}
		
		//Bestimmen aller weiteren Straenge, die nicht oben schon ausgewaehlt wurden
		$db->query("SELECT resources_objects.resource_id, root_id FROM resources_user_resources LEFT JOIN resources_objects USING (resource_id) WHERE user_id='".$this->user_id."' ");
		while ($db->next_record())
			if (!$this->my_roots[$db->f("root_id")])
				$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			
	}
	
	//public
	function getRoots() {
		return $this->my_roots;
	}
}
?>