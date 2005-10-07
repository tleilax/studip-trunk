<?
/**
* ShowList.class.php
* 
* creates a list
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		ShowList.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowList.class.php
// erzeugt eine Listenausgabe
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

require_once ($RELATIVE_PATH_RESOURCES."/views/ShowTreeRow.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/CheckMultipleOverlaps.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignObject.class.php");

/*****************************************************************************
ShowList, stellt Liste mit Hilfe von printThread dar
/*****************************************************************************/

class ShowList extends ShowTreeRow{
	var $db;
	var $db2;
	var $recurse_levels;			//How much Levels should the List recurse
	var $supress_hierachy_levels;		//show only resources with a category or show also the hierarhy-levels (that are resources too)
	var $admin_buttons;			//show admin buttons or not

	function ShowList() {
		$this->recurse_levels=-1;
		$this->supress_hierachy_levels=FALSE;
		$this->simple_list=FALSE;
	
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
	}

	function setRecurseLevels($levels) {
		$this->recurse_levels=$levels;
	}

	function setAdminButtons($value) {
		$this->admin_buttons=$value;
	}

	function setSimpleList($value) {
		$this->simple_list=$value;
	}
	
	function setViewHiearchyLevels($mode) {
		if ($mode)
			$this->supress_hierachy_levels=FALSE;
		else
			$this->supress_hierachy_levels=TRUE;
	}
	
	//private
	function showListObject ($resource_id, $admin_buttons=FALSE) {
		global $resources_data, $edit_structure_object, $RELATIVE_PATH_RESOURCES, $PHP_SELF, $ActualObjectPerms, $SessSemName, 
			$user, $perm, $clipObj, $view_mode, $view;
		
		//Object erstellen
		$resObject =& ResourceObject::Factory($resource_id);
		
		if (!$resObject->getId())
			return FALSE;

		//link add for special view mode (own window)
		if ($view_mode == "no_nav")
			$link_add = "&quick_view=".$view."&quick_view_mode=".$view_mode;

		if ($this->simple_list){
			//create a simple list intead of printhead/printcontent-design
			$return="<li><a href=\"$PHP_SELF?view=view_details&actual_object=".$resObject->getId().$link_add."\">".htmlReady($resObject->getName())."</a></li>\n";
			print $return;
		} else {
			//Daten vorbereiten
			if (!$resObject->getCategoryIconnr())
				$icon="<img src=\"pictures/cont_folder2.gif\" />";
			else
				$icon="<img src=\"$RELATIVE_PATH_RESOURCES/pictures/cont_res".$resObject->getCategoryIconnr().".gif\" />";
			
			if ($resources_data["structure_opens"][$resObject->id]) {			
				$link=$PHP_SELF."?structure_close=".$resObject->id.$link_add."#a";
				$open="open";
				if ($resources_data["actual_object"] == $resObject->id)
					echo "<a name=\"a\"></a>";
			} else {
				$link=$PHP_SELF."?structure_open=".$resObject->id.$link_add."#a";
				$open="close";
			}
			
			$titel='';
			if ($resObject->getCategoryName())
				$titel=$resObject->getCategoryName().": ";
			if ($edit_structure_object == $resObject->id) {
				echo "<a name=\"a\"></a>";
				$titel.="<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\" />";
			} else {
				$titel.=htmlReady($resObject->getName());
			}
	
			//create a link on the titel, too
			if (($link) && ($edit_structure_object != $resObject->id))
				$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
			
			if ($resObject->getOwnerLink())
				$zusatz=sprintf (_("verantwortlich:")." <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), htmlReady($resObject->getOwnerName()));
			else			
				$zusatz=sprintf (_("verantwortlich:")." %s", htmlReady($resObject->getOwnerName()));
			
			if ($perm->have_perm('root') || getGlobalPerms($user->id) == "admin"){
				$simple_perms = 'admin';
			} elseif (ResourcesUserRoomsList::CheckUserResource($resObject->getId())){
				$simple_perms = 'tutor';
			} else {
				$simple_perms = false;
			}
			
			//clipboard in/out
			if ((is_object($clipObj)) && $simple_perms && $resObject->getCategoryId())
				if ($clipObj->isInClipboard($resObject->getId()))
					$zusatz .= "<a href=\"".$PHP_SELF."?clip_out=".$resObject->getId().$link_add."\"><img src=\"pictures/forum_fav.gif\" border=\"0\" ".tooltip(_("Aus der Merkliste entfernen"))." /></a>";
				else
					$zusatz .= "<a href=\"".$PHP_SELF."?clip_in=".$resObject->getId().$link_add."\"><img src=\"pictures/forum_fav2.gif\" border=\"0\" ".tooltip(_("In Merkliste aufnehmen"))." /></a>";
				
			$new=TRUE;
			if ($open=="open") {
				//load the perms
				/*
				if (($ActualObjectPerms) && ($ActualObjectPerms->getId() == $resObject->getId()))
					$perms = $ActualObjectPerms->getUserPerm();
				else {
					$ThisObjectPerms =& ResourceObjectPerms::Factory($resObject->getId());
					$perms = $ThisObjectPerms->getUserPerm();
				}
				*/
				if ($edit_structure_object==$resObject->id) {
					$content= "<br /><textarea name=\"change_description\" rows=3 cols=40>".htmlReady($resObject->getDescription())."</textarea><br />";
					$content.= "<input type=\"image\" name=\"send\" align=\"absmiddle\" ".makeButton("uebernehmen", "src")." border=0 value=\""._("&Auml;nderungen speichern")."\" />";
					$content.= "&nbsp;<a href=\"$PHP_SELF?cancel_edit=$resObject->id\">".makeButton("abbrechen", "img")."</a>";						
					$content.= "<input type=\"hidden\" name=\"change_structure_object\" value=\"".$resObject->getId()."\" />";
					$open="open";
				} else {
					$content=htmlReady($resObject->getDescription());
				}
				if (($admin_buttons) && ($simple_perms == "admin")) {
					$edit.= "<a href=\"$PHP_SELF?create_object=$resObject->id\">".makeButton("neuesobjekt")."</a>";
					if ($resObject->isDeletable()) {
						$edit= "&nbsp;<a href=\"$PHP_SELF?kill_object=$resObject->id\">".makeButton("loeschen")."</a>";
					} 
					$edit.= "&nbsp;&nbsp;&nbsp;&nbsp;";
				} 
				if ($view_mode == "oobj"){
					if ($resObject->getCategoryId())
							$edit.= "<a href=\"$PHP_SELF?show_object=$resObject->id&view=openobject_schedule\">".makeButton("belegung")."</a>&nbsp;";
					$edit.= "<a href=\"$PHP_SELF?show_object=$resObject->id&view=openobject_details\">".makeButton("eigenschaften")."</a>";
				} else {
					if ($resObject->getCategoryId())
							$edit.= sprintf ("<a href=\"%s?show_object=%s&%sview=view_schedule%s\">".makeButton("belegung")."</a>&nbsp;", $PHP_SELF, $resObject->id, ($view_mode == "no_nav") ? "quick_" : "", ($view_mode == "no_nav") ? "&quick_view_mode=$view_mode" : "");
					$edit.= sprintf ("<a href=\"%s?show_object=%s&%sview=view_details%s\">".makeButton("eigenschaften")."</a>", $PHP_SELF, $resObject->id, ($view_mode == "no_nav") ? "quick_" : "", ($view_mode == "no_nav") ? "&quick_view_mode=$view_mode" : "");
				}
				
				//clipboard in/out
				if (is_object($clipObj) && $simple_perms && $resObject->getCategoryId())
					if ($clipObj->isInClipboard($resObject->getId()))
						$edit .= "&nbsp;<a href=\"".$PHP_SELF."?clip_out=".$resObject->getId().$link_add."\"><img ".makeButton("merkliste", "src")." border=\"0\" ".tooltip(_("Aus der Merkliste entfernen"))." align=\"absmiddle\"/></a>";
					else
						$edit .= "&nbsp;<a href=\"".$PHP_SELF."?clip_in=".$resObject->getId().$link_add."\"><img ".makeButton("merkliste", "src")." border=\"0\" ".tooltip(_("In Merkliste aufnehmen"))." align=\"absmiddle\"/></a>";
			}
			//Daten an Ausgabemodul senden
			$this->showRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
		}
		return TRUE;
	}
	
	function showListObjects ($start_id='', $level=0, $result_count=0) {

		$db=new DB_Seminar;	
		$db2=new DB_Seminar;
		
		//Let's start and load all the threads
		$query = sprintf ("SELECT resource_id FROM resources_objects ro LEFT JOIN resources_categories USING (category_id) WHERE parent_id = '%s' %s %s",
						$start_id,
						($this->supress_hierachy_levels) ? "AND ro.category_id != ''" : "",
						$this->show_only_rooms ? " AND is_room = 1" : "");
		$db->query($query);
		
		//if we have an empty result
		if ((!$db->num_rows()) && ($level==0))
			return FALSE;
			
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"), $this->admin_buttons);
			//in weitere Ebene abtauchen
			if (($this->recurse_levels == -1) || ($level + 1 < $this->recurse_levels)) {
				//Untergeordnete Objekte laden
				$db2->query("SELECT resource_id FROM resources_objects WHERE parent_id = '".$db->f("resource_id")."' ");
				
				while ($db2->next_record())
					$this->showListObjects($db2->f("resource_id"), $level+1, $result_count);
			}
			$result_count++;
		}
	return $result_count;
	}
	
	function showRangeList($range_id) {
		$db=new DB_Seminar;	

		//create the query for all objects owned by the range
		$query = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}

		//create the query for all additionale perms by the range to an object 
		$query = sprintf ("SELECT resource_id FROM  resources_user_resources WHERE user_id = '%s' ", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}
		
	return $result_count;		
	}
	
	function showSearchList($search_array, $check_assigns = FALSE) {
		$db=new DB_Seminar;	

		//create the query
		if ($search_array['resources_search_range']){
			$search_only = $this->getResourcesSearchRange($search_array['resources_search_range']);
		}
		
		if (($search_array["search_exp"]) && (!$search_array["search_properties"]))
			$query = sprintf ("SELECT resource_id FROM resources_objects ro LEFT JOIN resources_categories USING (category_id)
								WHERE ro.name LIKE '%%%s%%' %s %s %s ORDER BY ro.name",
								$search_array["search_exp"],
								$this->supress_hierachy_levels ? "AND ro.category_id != ''" : "",
								$this->show_only_rooms ? " AND is_room = 1" : "",
								$search_array['resources_search_range'] ? " AND ro.resource_id IN('".join("','", $search_only)."')" : "");
								

		if ($search_array["properties"]) {
			$query = sprintf ("SELECT a.resource_id %s FROM resources_objects_properties a LEFT JOIN resources_objects b USING (resource_id) %s ", ($search_array["properties"]) ? ", COUNT(a.resource_id) AS resource_id_count" : "", (($search_array["properties"]) || ($search_array["search_exp"])) ? "WHERE" : "");
			
			$i=0;
			foreach ($search_array["properties"] as $key => $val) {
				//if ($val == "on")
				//	$val = 1;
				
				//let's create some possible wildcards
				if (ereg("<=", $val)) {
					$val = trim(substr($val, strpos($val, "<")+2, strlen($val)));
					$linking = "<=";
				} elseif (ereg(">=", $val)) {
					$val = trim(substr($val, strpos($val, "<")+2, strlen($val)));
					$linking = ">=";
				} elseif (ereg("<", $val)) {
					$val = trim(substr($val, strpos($val, "<")+1, strlen($val)));
					$linking = "<";
				} elseif (ereg(">", $val)) {
					$val = trim(substr($val, strpos($val, "<")+1, strlen($val)));
					$linking = ">";
				} else $linking = "=";
				
				$query.= sprintf(" %s (property_id = '%s' AND state %s %s%s%s) ", ($i) ? "OR" : "", $key, $linking,  (!is_numeric($val)) ? "'" : "", $val, (!is_numeric($val)) ? "'" : "");
				$i++;
			}
			
			if ($search_array["search_exp"]) 
				$query.= sprintf(" %s (b.name LIKE '%%%s%%' OR b.description LIKE '%%%s%%') ", $search_array["properties"] ? "AND" : "", $search_array["search_exp"], $search_array["search_exp"]);
			
			if ($search_array["properties"]) 
				$query.= sprintf (" GROUP BY a.resource_id  HAVING resource_id_count = '%s' ", $i);
		}

		$db->query($query);

		//if we have an empty result
		if ((!$db->num_rows()) && ($level==0))
			return FALSE;
			

		while ($db->next_record()) {
			$found_resources[$db->f("resource_id")] = TRUE;
		}
		
		//do further checks to determine free resources inthe given time range
		if ($search_array["search_assign_begin"] && $check_assigns) {
			$multiOverlaps = new CheckMultipleOverlaps;
			$multiOverlaps->setTimeRange($search_array["search_assign_begin"], $search_array["search_assign_end"]);
			$assEvt = new AssignEvent('', $search_array["search_assign_begin"], $search_array["search_assign_end"], '', '');
			$event[$assEvt->getId()] = $assEvt;
			
			//add the found resources to the check-set
			foreach ($found_resources as $key=>$val) {
				$multiOverlaps->addResource($key);			
			}
			
			$multiOverlaps->checkOverlap($event, $result);
			
			//output
			foreach ($found_resources as $key=>$val) {
				if (!$result[$key]) {
					$this->showListObject($key);
					$result_count++;
				}
			}
		} else {
			//output
			foreach ($found_resources as $key=>$val) {
				$this->showListObject($key);
				$result_count++;
			}
		}
		
	return $result_count;
	}
	
	function getResourcesSearchRange($resource_id){
		static $children = array();
		$to_add = array();
		$this->db->query("SELECT resource_id FROM resources_objects WHERE parent_id='$resource_id'");
		while($this->db->next_record()){
			$to_add[] = $this->db->f(0);
		}
		foreach ($to_add as $rid){
			$children[] = $rid;
			$this->getResourcesSearchRange($rid);
		}
		return $children;
	}
}
?>
