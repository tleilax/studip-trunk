<?
/*
resourcesControl.php - 0.8
Steuerung fuer Ressourcenverwaltung von Stud.IP.
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
Requires & Registers
/*****************************************************************************/

require_once ($ABSOLUTE_PATH_STUDIP."reiter.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."msg.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."functions.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesClass.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesVisual.inc.php");

$sess->register("resources_data");
$my_perms= new ResourcesPerms;
$error = new ResourcesError;
$db=new DB_Seminar;

/*****************************************************************************
Kopf der Ausgabe
/*****************************************************************************/
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php");
include ("$ABSOLUTE_PATH_STUDIP/header.php");


/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

//View uebernehmen
if ($view)
	 $resources_data["view"]=$view;

//If we start the admin mode, kill open objects
if ($resources_data["view"] == "resources")
	closeObject();

//Beitrag aufklappen
if ($structure_open)
	$resources_data["structure_open"]=$structure_open;

//Beitrag schliessen
if ($structure_close)
	$resources_data["structure_open"]=FALSE;

//Listenstartpunkt festlegen
if ($open_list) {
	$resources_data["list_open"]=$open_list;
	$resources_data["view"]="_lists";
	}

//Neue Hierachieebene oder Unterebene anlegen
if ($resources_data["view"]=="create_hierarchie" || $create_hierachie_level) {
	if ($resources_data["view"]=="create_hierarchie") {
		$newHiearchie=new resourceObject("Neue Hierachie", "Dieses Objekt kennzeichnet eine Hierachie und kann jederzeit in eine Ressource umgewandelt werden"
						, '', '', '', "0", '', $user->id);
	} elseif ($create_hierachie_level) {
		$parent_Object=new resourceObject($create_hierachie_level);
		$newHiearchie=new resourceObject("Neue Hierachieebene", "Dieses Objekt kennzeichnet eine neue Hierachieebene und kann jederzeit in eine Ressource umgewandelt werden"
						, '', '', $parent_Object->getRootId(), $create_hierachie_level, '', $user->id);
	}
	$newHiearchie->create();
	$edit_structure_object=$newHiearchie->id;
	$resources_data["view"]="resources";
	}

//Neues Objekt anlegen
if ($create_object) {
	$parent_Object=new resourceObject($create_object);
	$new_Object=new resourceObject("Neues Objekt", "Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen."
					, FALSE, FALSE, $parent_Object->getRootId(), $create_object, "0", $user->id);
	$new_Object->create();
	$resources_data["view"]="edit_object_properties";
	$resources_data["structure_open"]=$new_Object->getId();
	}

//Object bearbeiten
if ($edit_object) {
	$resources_data["view"]="edit_object_properties";
	$resources_data["structure_open"]=$edit_object;
	}
	
//Object loeschen
if ($kill_object) {
	$ObjectPerms = new ResourcesObjectPerms($kill_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$killObject=new resourceObject($kill_object);
		if ($killObject->delete())
		;
		$resources_data["view"]="resources";
	} else {
		$error->displayMsg(1);
		die;
	}
}

//Name und Beschreibung aendern
if ($change_structure_object) {
	$ObjectPerms = new ResourcesObjectPerms($change_structure_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject=new resourceObject($change_structure_object);
		$changeObject->setName($change_name);
		$changeObject->setDescription($change_description);
		if ($changeObject->store())
		;
	} else {
		$error->displayMsg(1);
		die;
	}
	$resources_data["view"]="resources";
	$resources_data["structure_open"]=$change_structure_object;
}

//Objektbelegung erstellen/aendern
if ($change_object_schedules) {
	//load the perms
	if ($change_object_schedules == "NEW") 
		$ObjectPerms = new ResourcesObjectPerms($change_schedule_resource_id);
	else
		$ObjectPerms = new AssignObjectPerms($change_object_schedules);
	
	if ($ObjectPerms->getUserPerm () == "user" || $ObjectPerms->getUserPerm () == "admin") {
		if ($kill_assign_x) {
			$killAssign=new AssignObject($change_object_schedules);
			$killAssign->delete();
		} else {
			if ($change_object_schedules == "NEW")
				$change_schedule_id=FALSE;
			else
				$change_schedule_id=$change_object_schedules;
			
			if ($reset_search_user_x)
				$search_string_search_user=FALSE;

			if (($send_search_user) && ($submit_search_user !="FALSE") && (!$reset_search_user_x))
				$change_schedule_assign_user_id=$submit_search_user;

			//check, if the owner of the assign object is a Veranstaltung, which has own dates to insert
			if (get_object_type($change_schedule_assign_user_id) == "sem") {
			 	require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
		 		$veranstAssign = new VeranstaltungResourcesAssign($change_schedule_assign_user_id);
				$created_ids = $veranstAssign->changeAssign($change_schedule_resource_id, $change_schedule_user_free_name);

				//after a succesful insert show the first (maybe the only) assign-object
				if (is_array($created_ids))
					$assign_id=$created_ids[0];

			//create the "normal" assign object
			} else {
				//the user send infinity repeat (until date) as empty field, but it's -1 in the db
				if (($change_schedule_repeat_quantity_infinity) && (!$change_schedule_repeat_quantity))
					$change_schedule_repeat_quantity=-1;
	
				//create timestamps
				if ($change_schedule_year) {
					$change_schedule_begin=mktime($change_schedule_start_hour, $change_schedule_start_minute, 0, $change_schedule_month, $change_schedule_day, $change_schedule_year);
					$change_schedule_end=mktime($change_schedule_end_hour, $change_schedule_end_minute, 0, $change_schedule_month, $change_schedule_day, $change_schedule_year);
				}
		
				if ($change_schedule_repeat_end_year)
					$change_schedule_repeat_end=mktime(23, 59, 59, $change_schedule_repeat_end_month, $change_schedule_repeat_end_day, $change_schedule_repeat_end_year);
	
				if ($change_schedule_repeat_sem_end)
					foreach ($SEMESTER as $a)	
						if (($change_schedule_begin >= $a["beginn"]) &&($change_schedule_begin <= $a["ende"]))
							$change_schedule_repeat_end=$a["vorles_ende"];
	
				//create repeatdata
	
				//repeat = none
				if ($change_schedule_repeat_none_x) {
					$change_schedule_repeat_month_of_year='';
					$change_schedule_repeat_day_of_month='';
					$change_schedule_repeat_week_of_month='';
					$change_schedule_repeat_day_of_week='';
					$change_schedule_repeat_quantity='';
					$change_schedule_repeat_interval='';	
				}
	
				//repeat = year
				if ($change_schedule_repeat_year_x) {
					$change_schedule_repeat_month_of_year=date("n", $change_schedule_begin);
					$change_schedule_repeat_day_of_month=date("j", $change_schedule_begin);
					$change_schedule_repeat_week_of_month='';
					$change_schedule_repeat_day_of_week='';
					if (!$change_schedule_repeat_quantity	)
						$change_schedule_repeat_quantity=-1;
					if (!$change_schedule_repeat_interval)
						$change_schedule_repeat_interval=1;
				}

				//repeat = month
				if ($change_schedule_repeat_month_x)
					if (!$change_schedule_repeat_week_of_month) {
						$change_schedule_repeat_month_of_year='';
						$change_schedule_repeat_day_of_month=date("j", $change_schedule_begin);
						$change_schedule_repeat_week_of_month='';
						$change_schedule_repeat_day_of_week='';
						if (!$change_schedule_repeat_quantity	)
							$change_schedule_repeat_quantity=-1;
						if (!$change_schedule_repeat_interval)
							$change_schedule_repeat_interval=1;
					}

				//repeat = week
				if ($change_schedule_repeat_week_x) {
					$change_schedule_repeat_month_of_year='';
					$change_schedule_repeat_day_of_month='';
					$change_schedule_repeat_week_of_month='';
					$change_schedule_repeat_quantity='';
					if (!$change_schedule_repeat_day_of_week)
						$change_schedule_repeat_day_of_week=1;
					if (!$change_schedule_repeat_quantity	)
						$change_schedule_repeat_quantity=-1;
					if (!$change_schedule_repeat_interval)
						$change_schedule_repeat_interval=1;
				}

				//repeat = day
				if ($change_schedule_repeat_day_x) {
					$change_schedule_repeat_month_of_year='';
					$change_schedule_repeat_day_of_month='';
					$change_schedule_repeat_week_of_month='';
					$change_schedule_repeat_quantity='';
					$change_schedule_repeat_day_of_week='';
					if (!$change_schedule_repeat_quantity	)
						$change_schedule_repeat_quantity=-1;
					if (!$change_schedule_repeat_interval)
						$change_schedule_repeat_interval=1;
				}
	
				//repeat days, only if week�
				if ($change_schedule_repeat_day1_x)
					$change_schedule_repeat_day_of_week=1;
				if ($change_schedule_repeat_day2_x)
					$change_schedule_repeat_day_of_week=2;
				if ($change_schedule_repeat_day3_x)
					$change_schedule_repeat_day_of_week=3;
				if ($change_schedule_repeat_day4_x)
					$change_schedule_repeat_day_of_week=4;
				if ($change_schedule_repeat_day5_x)
					$change_schedule_repeat_day_of_week=5;
				if ($change_schedule_repeat_day6_x)
					$change_schedule_repeat_day_of_week=6;
				if ($change_schedule_repeat_day7_x)
					$change_schedule_repeat_day_of_week=7;

				//give data to the assignobject
				$changeAssign=new AssignObject(
					$change_schedule_id,
					$change_schedule_resource_id,
					$change_schedule_assign_user_id,
					$change_schedule_user_free_name,
					$change_schedule_begin,
					$change_schedule_end,
					$change_schedule_repeat_end,
					$change_schedule_repeat_quantity,
					$change_schedule_repeat_interval,
					$change_schedule_repeat_month_of_year,
					$change_schedule_repeat_day_of_month, 
					$change_schedule_repeat_month,
					$change_schedule_repeat_week_of_month,
					$change_schedule_repeat_day_of_week,
					$change_schedule_repeat_week);

				if ($change_object_schedules == "NEW") {
					if ($changeAssign->create())
						$assign_id=$changeAssign->getId();
				} else {
					$changeAssign->chng_flag=TRUE;
					if ($changeAssign->store()) {
						$assign_id=$changeAssign->getId();
						}
				}
			}
		}
	} else {
		$error->displayMsg(1);
		die;
	}
}

//Objekteigenschaften aendern
if ($change_object_properties) {
	$ObjectPerms = new ResourcesObjectPerms($change_object_properties);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject=new resourceObject($change_object_properties);
		$changeObject->setName($change_name);
		$changeObject->setDescription($change_description);
		$changeObject->setCategoryId($change_category_id);
		$changeObject->setParentBind($change_parent_bind);
	
		//Properties loeschen
		$changeObject->flushProperties();
	
		//Eigenschaften neu schreiben
		if (is_array($change_property_val))
			foreach ($change_property_val as $key=>$val) {
				if ((substr($val, 0, 4) == "_id_") && (substr($change_property_val[$key+1], 0, 4) != "_id_"))
					$changeObject->storeProperty(substr($val, 4, strlen($val)), $change_property_val[$key+1]);
			}
	
		//Object speichern
		if ($changeObject->store())
		;
	} else {
		$error->displayMsg(1);
		die;
	}
	
	$resources_data["view"]="edit_object_properties";
}

//Objektberechtigungen aendern
if ($change_object_perms) {
	$ObjectPerms = new ResourcesObjectPerms($change_object_perms);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject=new resourceObject($change_object_perms);
	
		if (is_array($change_user_id))
			foreach ($change_user_id as $key=>$val) {
				$changeObject->storePerms($val, $change_user_perms[$key]);
			}

		if ($delete_user_perms)
			$changeObject->deletePerms($delete_user_perms);
	
		if ($reset_search_owner_x)
			$search_string_search_owner=FALSE;

		if ($reset_search_perm_user_x)
			$search_string_search_perm_user=FALSE;
	
		if (($send_search_owner_x) && ($submit_search_owner !="FALSE") && (!$reset_search_owner_x)) 
 			$changeObject->setOwnerId($submit_search_owner);
	
		if (($send_search_perm_user_x) && ($submit_search_perm_user !="FALSE") && (!$reset_search_perm_user_x))
			$changeObject->storePerms($submit_search_perm_user);
	
		//Object speichern
		if ($changeObject->store())
		;
	} else {
		$error->displayMsg(1);
		die;
	}
	$resources_data["view"]="edit_object_perms";
}

//Typen bearbeiten
if (($add_type) || ($delete_type) || ($add_type_property_id) || ($delete_type_property_id)) {
	//if ($ObjectPerms->getUserPerm () == "admin") { --> da muss der Ressourcen Root check hin �
		if ($delete_type) {
			$db->query("DELETE FROM resources_categories WHERE category_id ='$delete_type'");
		}
	
		if (($add_type) && ($_add_type_x)) {
			$id=md5(uniqid("Sommer2002"));	
			$db->query("INSERT INTO resources_categories SET category_id='$id', name='$add_type', description='$insert_type_description' ");
		}
	
		if (($add_type_property_id) && ($add_type_category_id)) {	
			$db->query("INSERT INTO resources_categories_properties SET category_id='$add_type_category_id', property_id='$add_type_property_id' ");
		}
	
		if ($delete_type_property_id) {
			$db->query("DELETE FROM resources_categories_properties WHERE category_id='$delete_type_category_id' AND property_id='$delete_type_property_id' ");
		}
	/*} else {
		$error->displayMsg(1);
		die;
	}*/
}

//Eigenschaften bearbeiten
if (($add_property) || ($delete_property) || ($send_property_type_id)) {
	//if ($ObjectPerms->getUserPerm () == "admin") { { --> da muss der Ressourcen Root check hin �
		if ($delete_property) {
			$db->query("DELETE FROM resources_properties WHERE property_id ='$delete_property' ");
		}
	
		if ($add_property) {
			if ($add_property_type=="bool")
				$options="vorhanden";
			if ($add_property_type=="select")
				$options="Option 1;Option 2;Option 3";
			$id=md5(uniqid("Regen2002"));
			$db->query("INSERT INTO resources_properties SET options='$options', property_id='$id', name='$add_property', description='$insert_property_description', type='$add_property_type' ");
		}
	
		if ($send_property_type_id) {
			if ($send_property_type == "select") {
				$tmp_options=explode (";",$send_property_select_opt);
				$options='';
				$i=0;
				if (is_array($tmp_options))
					foreach ($tmp_options as $a) {
						if ($i)
							$options.=";";
						$options.=trim($a);						
						$i++;
					}
			} elseif ($send_property_type == "bool") {
				$options=$send_property_bool_desc;
			}
			else
				$options='';
			
			if (!$options)
				if ($send_property_type == "bool")
					$options="vorhanden";
				elseif ($send_property_type == "select")
					$options="Option 1;Option 2;Option 3";	
				
			$db->query("UPDATE resources_properties SET options='$options', type='$send_property_type' WHERE property_id='$send_property_type_id' ");
		}
	/*} else {
		$error->displayMsg(1);
		die;
	}*/
}

//Globale Perms bearbeiten
if (($add_root_user) || ($delete_root_user_id)){
	//if ($ObjectPerms->getUserPerm () == "admin") { { --> da muss der Ressourcen Root check hin �
		if ($reset_search_root_user_x)
			$search_string_search_root_user=FALSE;

		if (($send_search_root_user_x) && ($submit_search_root_user !="FALSE") && (!$reset_search_root_user_x))
			$db->query("INSERT resources_user_resources SET user_id='$submit_search_root_user', resource_id='all', perms='user' ");

		if ($delete_root_user_id)		
			$db->query("DELETE FROM resources_user_resources WHERE user_id='$delete_root_user_id' AND resource_id='all' ");
	
		if (is_array ($change_root_user_id))
			foreach ($change_root_user_id as $key => $val) {
				$db->query("UPDATE resources_user_resources SET perms='".$change_root_user_perms[$key]."' WHERE user_id='$val' ");
			}
	/*} else {
		$error->displayMsg(1);
		die;
	}*/
}

//evaluate the command from schedule navigator
if ($resources_data["view"]=="view_schedule" || $resources_data["view"]=="openobject_schedule") {
	if ($next_week)
		$resources_data["schedule_week_offset"]++;
	if ($previous_week)
		$resources_data["schedule_week_offset"]--;
	if ($navigate) {
		$resources_data["schedule_length_factor"] = $schedule_length_factor;
		$resources_data["schedule_length_unit"] = $schedule_length_unit;
		$resources_data["schedule_week_offset"] = 0;
		$resources_data["schedule_start_time"] = mktime (0,0,0,$schedule_begin_month, $schedule_begin_day, $schedule_begin_year);
		if (($start_list_x) || (($jump_x) && ($resources_data["schedule_mode"] == "list"))){
			$resources_data["schedule_mode"] = "list";
			if ($resources_data["schedule_start_time"] < 1)
				$resources_data["schedule_start_time"] = mktime (0, 0, 0, date("n", time()), date("j", time()), date("Y", time()));
			switch ($resources_data["schedule_length_unit"]) {
				case "y" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"]), date("j", $resources_data["schedule_start_time"]), date("Y",$resources_data["schedule_start_time"])+$resources_data["schedule_length_factor"]);
				break;
				case "m" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"])+$resources_data["schedule_length_factor"], date("j", $resources_data["schedule_start_time"]), date("Y",$resources_data["schedule_start_time"]));
				break;
				case "w" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"]), date("j", $resources_data["schedule_start_time"])+($resources_data["schedule_length_factor"] * 7), date("Y",$resources_data["schedule_start_time"]));
				break;
				case "d" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"]), date("j", $resources_data["schedule_start_time"])+$resources_data["schedule_length_factor"], date("Y",$resources_data["schedule_start_time"]));
				break;
			}
			if ($resources_data["schedule_end_time"]  < 1)
				$resources_data["schedule_end_time"] = mktime (23, 59, 0, date("n", time()), date("j", time())+1, date("Y", time()));
		} elseif (($start_graphical_x) || (!$resources_data["schedule_mode"]) || (($jump_x) && ($resources_data["schedule_mode"] == "graphical"))) {
			$resources_data["schedule_end_time"] = $resources_data["schedule_start_time"] + (7 * 24 * 60 * 60);
			$resources_data["schedule_mode"] = "graphical";			
		}
	} else {
		$resources_data["schedule_start_time"] = mktime (0, 0, 0, date("n", time()), date("j", time()), date("Y", time()));
		$resources_data["schedule_end_time"] = mktime (23, 59, 0, date("n", time()), date("j", time())+7, date("Y", time()));
		$resources_data["schedule_mode"] = "graphical";		
	}
}

//handle commands from the search 'n' browse modul
if ($resources_data["view"]=="search") {
	if ($open_level)
		 $resources_data["browse_open_level"]=$open_level;

	if ($mode == "properties")
		$resources_data["search_mode"]="properties";
	
	if ($mode == "browse")
		$resources_data["search_mode"]="browse";
	
	if ($start_search_x) {
		unset($resources_data["search_array"]);
		$resources_data["search_array"]["search_exp"]=$search_exp;
		if (is_array($search_property_val))
			foreach ($search_property_val as $key=>$val) {
				if ((substr($val, 0, 4) == "_id_") && (substr($search_property_val[$key+1], 0, 4) != "_id_") && ($search_property_val[$key+1]))
					$resources_data["search_array"]["properties"][substr($val, 4, strlen($val))]=$search_property_val[$key+1];
		}
	}
	
	if ($reset) {
		unset($resources_data["browse_open_level"]);
		unset($resources_data["search_array"]);
	}
}

//show object, this object will be edited or viewed
if ($show_object)
	$resources_data["structure_open"]=$show_object;

//Create Reitersystem
if ($SessSemName[1])
	include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
else
	include ("$RELATIVE_PATH_RESOURCES/views/links_resources.inc.php");

include ("$RELATIVE_PATH_RESOURCES/views/page_intros.inc.php");

?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="topic">&nbsp;<img src="pictures/meinetermine.gif" border="0" align="absmiddle" alt="Ressourcen"><b>&nbsp;<? echo $title; ?></b></td>
	</tr>
	<tr>
		<td class="blank">&nbsp;
			<blockquote>
			<? echo $page_intro ?>
			</blockquote>
		</td>
	</tr>	
	<tr>
		<td class="blank">

<?	

/*****************************************************************************
Treeview, die Strukturdarstellung, views: resources, _resources, make_hierarchie
/*****************************************************************************/
if ($resources_data["view"]=="resources" || $resources_data["view"]=="_resources"){

	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}

	$range_id = $user->id;

	$resUser=new ResourcesRootThreads($range_id);
	$thread=new getThread();
	
	$roots=$resUser->getRoots();
	if (is_array($roots)) {
		foreach ($roots as $a) {
			$thread->createThread($a);
		}
		echo "<br />&nbsp;";			
	} else {
		echo "</td></tr>";
		parse_msg ("info�Es sind keine Objekte beziehungsweise Ebene angelegt, auf die Sie Zugriff haben. <br />Sie k&ouml;nnen eine neue Ebene erzeugen, wenn sie \"Neue Hierarchie erzeugen\" anw&auml;hlen.");
	}

	if ($edit_structure_object) {
		echo "</form>";
	}
	
}

/*****************************************************************************
Listview, die Listendarstellung, views: lists, _lists, openobject_main
/*****************************************************************************/
if ($resources_data["view"]=="lists" || $resources_data["view"]=="_lists" || $resources_data["view"]=="openobject_main") {

	$list=new getList();
	$list->setRecurseLevels(-1);
	if ($resources_data["view"] != "openobject_main")
		$list->setAdminButtons(TRUE);
	
	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}
	
	if ($resources_data["view"]=="openobject_main") {
		if (!$list->createRangeList($SessSemName[1])) {
			echo "</td></tr>";
			parse_msg ("info�Es existieren keine Ressourcen, die Sie in dieser Veranstaltung belegen k&ouml;nnen.");
		}
	} else {
		if (!$list->createList($resources_data["list_open"])) {
			echo "</td></tr>";
			parse_msg ("info�Sie haben keine Ebene ausgew&auml;hlt. Daher kann keine Liste erzeugt werden. <br />Benutzen Sie die Suchfunktion oder w&auml;hlen Sie unter \"&Uuml;bersicht\" einen Startpunkt in der Hierachie aus.");
		}
	}
	
	if ($edit_structure_object) {
		echo "</form>";
	}
}

/*****************************************************************************
Objecteigenschaften bearbeiten, views: edit_object_properties
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_properties" || $resources_data["view"]=="objects") {

	if ($resources_data["structure_open"]) {
		$editObject=new editObject($resources_data["structure_open"]);
		$editObject->create_propertie_forms();
	} else {
		echo "</td></tr>";
		parse_msg ("info�Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}


/*****************************************************************************
Objecteigenschaften anzeigen, views: openobject_details
/*****************************************************************************/
if ($resources_data["view"]=="openobject_details") {

	if ($resources_data["structure_open"]) {
		$viewObject = new viewObject($resources_data["structure_open"]);
		$viewObject->view_properties();
	} else {
		echo "</td></tr>";
		parse_msg ("info�Sie haben keine Objekt zum Anzeigen ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
Objectberechtigungen bearbeiten, views: edit_object_perms
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_perms") {
	if ($resources_data["structure_open"]) {
		$editObject=new editObject($resources_data["structure_open"]);
		$editObject->create_perm_forms();
	} else {
		echo "</td></tr>";
		parse_msg ("info�Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
Objectbelegung bearbeiten, views: edit_object_assign, openobject_assign
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_assign" || $resources_data["view"]=="openobject_assign") {
	if ($resources_data["structure_open"]) {
		$editObject=new editObject($resources_data["structure_open"]);
		$editObject->setUsedView($resources_data["view"]);
		if ($edit_assign_object)
			$assign_id=$edit_assign_object;
		$editObject->create_schedule_forms($assign_id);
	} else {
		echo "</td></tr>";
		parse_msg ("info�Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
Typen verwalten, views: edit_types
/*****************************************************************************/
if (($resources_data["view"]=="edit_types") || ($resources_data["view"]=="settings")) {
	
	$editSettings=new editSettings;
	$editSettings->create_types_forms();
}

/*****************************************************************************
Eigenschaften verwalten, views: edit_properties
/*****************************************************************************/
if ($resources_data["view"]=="edit_properties") {
	
	$editSettings=new editSettings;
	$editSettings->create_properties_forms();
}

/*****************************************************************************
Berechtigungen verwalten, views: edit_perms
/*****************************************************************************/
if ($resources_data["view"]=="edit_perms") {
	
	$editSettings=new editSettings;
	$editSettings->create_perms_forms();
}

/*****************************************************************************
Belegungen ausgeben, views: view_schedule, openobject_schedule
/*****************************************************************************/
if ($resources_data["view"]=="view_schedule" || $resources_data["view"]=="openobject_schedule") {
	
	if ($resources_data["structure_open"]) {
		$ViewSchedules=new ViewSchedules($resources_data["structure_open"]);
		$ViewSchedules->setStartTime($resources_data["schedule_start_time"]);
		$ViewSchedules->setEndTime($resources_data["schedule_end_time"]);
		$ViewSchedules->setLengthFactor($resources_data["schedule_length_factor"]);
		$ViewSchedules->setLengthUnit($resources_data["schedule_length_unit"]);	
		$ViewSchedules->setWeekOffset($resources_data["schedule_week_offset"]);	
		$ViewSchedules->setUsedView($resources_data["view"]);	
		
		$ViewSchedules->navigator();
	
		if (($resources_data["schedule_start_time"]) && ($resources_data["schedule_end_time"]))
			if ($resources_data["schedule_mode"] == "list") //view List
				$ViewSchedules->create_schedule_list($schedule_start_time, $schedule_end_time);
			else
				$ViewSchedules->create_schedule_graphical($schedule_start_time, $schedule_end_time);
	} else {
		echo "</td></tr>";
		parse_msg ("info�Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
persoenliche Einstellungen verwalten, views: edit_personal_settings
/*****************************************************************************/
if ($resources_data["view"]=="edit_personal_settings") {
	
	$editSettings=new editPersonalSettings;
	$editSettings->create_personal_settings_forms();
}

/*****************************************************************************
Search 'n' browse
/*****************************************************************************/
if ($resources_data["view"]=="search") {
	
	$search=new ResourcesBrowse;
	$search->setStartLevel('');
	$search->setMode($resources_data["search_mode"]);
	$search->setSearchArray($resources_data["search_array"]);
	if ($resources_data["browse_open_level"])
		$search->setOpenLevel($resources_data["browse_open_level"]);
	$search->createSearch();
}



/*****************************************************************************
Seite abschliessen
/*****************************************************************************/
		?>
			</td>
		<tr>
			<td class="blank">
			&nbsp; 
			</td>
		</tr>
	</tr>
</table>
<?
page_close();
?>