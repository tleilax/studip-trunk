<?
/**
* links_resources.inc.php
* 
* navigation data for resources
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	resources
* @module		links_resources.inc.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// links_resources.inc.php
// Navigationsadaten der Ressourcenverwaltung
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


require_once ($ABSOLUTE_PATH_STUDIP."/reiter.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");


$reiter=new reiter;

//Create Reitersystem

//oberen Reiter 
$structure["resources"]=array (topKat=>"", name=>_("&Uuml;bersicht"), link=>"resources.php?view=resources#a", active=>FALSE);
if ($resources_data["list_open"])
	$structure["lists"]=array (topKat=>"", name=>_("Liste"), link=>"resources.php?view=lists#a", active=>FALSE);
if ($resources_data["actual_object"])
	$structure["objects"]=array (topKat=>"", name=>_("Ressource"), link=>"resources.php?view=objects", active=>FALSE);

if ((getGlobalPerms($user->id) == "admin") || ($perm->have_perm("root"))) {
	$resList = new ResourcesUserRoomsList($user_id, TRUE, FALSE);
	if (($resList->roomsExist()) && (get_config("RESOURCES_ALLOW_ROOM_REQUESTS"))) {
		$structure["room_planning"]=array (topKat=>"", name=>_("Raumplanung"), link=>"resources.php?view=requests_start", active=>FALSE);
		$top_kat_tools = TRUE;
	}

	$structure["settings"]=array (topKat=>"", name=>_("Anpassen"), link=>"resources.php?view=edit_types", active=>FALSE);
}

//Reiter "Uebersicht"
$structure["_resources"]=array (topKat=>"resources", name=>_("Struktur"), link=>"resources.php?view=_resources#a", active=>FALSE);
$structure["search"]=array (topKat=>"resources", name=>_("Suchen"), link=>"resources.php?view=search&new_search=TRUE", active=>FALSE);
$structure["create_hierarchie"]=array (topKat=>"resources", name=>_("Neue&nbsp;Hierarchieebene&nbsp;erzeugen"), link=>"resources.php?view=create_hierarchie#a", active=>FALSE);

//Reiter "Listen"
if ($resources_data["list_open"]) {
	$structure["_lists"]=array (topKat=>"lists", name=>_("Listenausgabe"), link=>"resources.php?view=_lists#a", active=>FALSE);
	//$structure["search_lists"]=array (topKat=>"lists", name=>_("Suchen"), link=>"resources.php?view=search_lists", active=>FALSE);
	//$structure["export_lists"]=array (topKat=>"lists", name=>_("Listen&nbsp;exportieren"), link=>"resources.php?view=export_lists", active=>FALSE);
}

//Reiter "Objekt"
if ($resources_data["actual_object"]) {
	if ($ActualObjectPerms->havePerm ("autor")) {
		$structure["view_details"]=array (topKat=>"objects", name=>_("Eigenschaften"), link=>"resources.php?view=view_details", active=>FALSE);
		if ($ActualObjectPerms->havePerm ("admin")) {
			$structure["edit_object_properties"]=array (topKat=>"objects", name=>_("Eigenschaften&nbsp;bearbeiten"), link=>"resources.php?view=edit_object_properties", active=>FALSE);
			$structure["edit_object_perms"]=array (topKat=>"objects", name=>_("Rechte&nbsp;bearbeiten"), link=>"resources.php?view=edit_object_perms", active=>FALSE);
		}
		if (getResourceObjectCategory($resources_data["actual_object"])) {
			$structure["view_schedule"]=array (topKat=>"objects", name=>_("Belegungsplan"), link=>"resources.php?view=view_schedule", active=>FALSE);
			$structure["edit_object_assign"]=array (topKat=>"objects", name=>_("Belegung&nbsp;bearbeiten"), link=>"resources.php?view=edit_object_assign", active=>FALSE);
		}
	}
 }

//Reiter "Raumplanung"
if ($top_kat_tools) {
	$structure["requests_start"]=array (topKat=>"room_planning", name=>_("&Uuml;bersicht"), link=>"resources.php?view=requests_start", active=>FALSE);
	$structure["edit_request"]=array (topKat=>"room_planning", name=>_("Anfragen&nbsp;bearbeiten"), link=>"resources.php?view=edit_request", active=>FALSE, "disabled"=>(($resources_data["requests_working_on"]) ? FALSE : TRUE));
}


//Reiter "Anpassen"
if ((getGlobalPerms($user->id) == "admin") || ($perm->have_perm("root"))){ //Grundlegende Einstellungen fuer alle Ressourcen Admins
	$structure["edit_types"]=array (topKat=>"settings", name=>_("Typen&nbsp;verwalten"), link=>"resources.php?view=edit_types", active=>FALSE);
	$structure["edit_properties"]=array (topKat=>"settings", name=>_("Eigenschaften&nbsp;verwalten"), link=>"resources.php?view=edit_properties", active=>FALSE);
	$structure["edit_settings"]=array (topKat=>"settings", name=>_("globale&nbsp;Einstellungen&nbsp;verwalten"), link=>"resources.php?view=edit_settings", active=>FALSE);
}
if ($perm->have_perm("root")) { //Rechtezuweisungen nur fuer Root
	$structure["edit_perms"]=array (topKat=>"settings", name=>_("globale&nbsp;Rechte&nbsp;verwalten"), link=>"resources.php?view=edit_perms", active=>FALSE);
}
//$structure["edit_personal_settings"]=array (topKat=>"settings", name=>_("pers&ouml;nliche&nbsp;Einstellungen"), link=>"resources.php?view=edit_personal_settings", active=>FALSE);
//

$reiter->create($structure, $resources_data["view"]);

?>