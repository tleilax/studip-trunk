<?php
//diese Datei enth�lt die links der Kopfzeile, besser nur anzeigen wenn ein Objekt ausgew�hlt wurde
if (isset($SessSemName) && $SessSemName[0] != "") {
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."reiter.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."functions.php");

$db=new DB_Seminar;
$reiter=new reiter;
		
//Reitersytem erzeugen

//Topkats
if ($SessSemName["class"]=="inst") {
	$structure["institut_main"]=array (topKat=>"", name=>"&Uuml;bersicht", link=>"institut_main.php", active=>FALSE);
	$structure["forum"]=array (topKat=>"", name=>"Forum", link=>"forum.php", active=>FALSE);
	$structure["personal"]=array (topKat=>"", name=>"Personal", link=>"institut_members.php", active=>FALSE);
	$structure["folder"]=array (topKat=>"", name=>"Dateien", link=>"folder.php?cmd=tree", active=>FALSE);
	$structure["literatur"]=array (topKat=>"", name=>"Literatur zur Einrichtung", link=>"literatur.php", active=>FALSE);
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvaiableResources ($SessSemName[1]))
			$structure["resources"]=array (topKat=>"", name=>"Ressourcen", link=>"resources.php?view=openobject_main", active=>FALSE);
	}
} else {
	$structure["seminar_main"]=array (topKat=>"", name=>"&Uuml;bersicht", link=>"seminar_main.php", active=>FALSE);
	$structure["forum"]=array (topKat=>"", name=>"Forum", link=>"forum.php", active=>FALSE);
	$structure["teilnehmer"]=array (topKat=>"", name=>"TeilnehmerInnen", link=>"teilnehmer.php", active=>FALSE);
	$structure["folder"]=array (topKat=>"", name=>"Dateien", link=>"folder.php?cmd=tree", active=>FALSE);
	$structure["dates"]=array (topKat=>"", name=>"Ablaufplan", link=>"dates.php", active=>FALSE);
	$structure["literatur"]=array (topKat=>"", name=>"Literatur", link=>"literatur.php", active=>FALSE);
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvaiableResources ($SessSemName[1]))
			$structure["resources"]=array (topKat=>"", name=>"Ressourcen", link=>"resources.php?view=openobject_main", active=>FALSE);
	}
}

//Bottomkats
if ($SessSemName["class"]=="inst") {
	$structure["_institut_main"]=array (topKat=>"institut_main", name=>"Info", link=>"institut_main.php", active=>FALSE);
	$structure["institut_members"]=array (topKat=>"personal", name=>"MitarbeiterInnen", link=>"institut_members.php", active=>FALSE);
	$structure["institut_veranstaltungen"]=array (topKat=>"institut_main", name=>"Veranstaltungen", link=>"show_bereich.php?level=s&id=$SessSemName[1]", active=>FALSE);
	$structure["timetable"]=array (topKat=>"institut_main", name=>"Veranstaltungs-Timetable", link=>"mein_stundenplan.php?inst_id=$SessSemName[1]", active=>FALSE);
	// $structure["druckansicht_i"]=array (topKat=>"institut_main", name=>"Druckansicht", link=>"print_institut.php", target=>"_new", active=>FALSE);
	if ($rechte)
		if ($perm->have_perm("admin"))
			$structure["administration_e"]=array (topKat=>"institut_main", name=>"Administration der Einrichtung", link=>"admin_institut.php?new_inst=TRUE", active=>FALSE);
		else
			$structure["administration_e"]=array (topKat=>"institut_main", name=>"Administration der Einrichtung", link=>"admin_literatur.php?new_inst=TRUE&view=literatur_inst", active=>FALSE);		
} else {
//
	$structure["_seminar_main"]=array (topKat=>"seminar_main", name=>"Kurzinfo", link=>"seminar_main.php", active=>FALSE);
	$structure["details"]=array (topKat=>"seminar_main", name=>"Details", link=>"details.php", active=>FALSE);
	$structure["druckansicht_s"]=array (topKat=>"seminar_main", name=>"Druckansicht", link=>"print_seminar.php", target=>"_new", active=>FALSE);
	if ($rechte)
		$structure["administration_v"]=array (topKat=>"seminar_main", name=>"Administration dieser Veranstaltung", link=>"admin_seminare1.php?new_sem=TRUE", active=>FALSE);
}
//

$structure["_teilnehmer"]=array (topKat=>"teilnehmer", name=>"TeilnehmerInnen", link=>"teilnehmer.php", active=>FALSE);
$structure["_forum"]=array (topKat=>"forum", name=>"Themen", link=>"forum.php", active=>FALSE);
$structure["neue"]=array (topKat=>"forum", name=>"neue Beitr&auml;ge", link=>"forum.php?view=neue", active=>FALSE);
$structure["letzte"]=array (topKat=>"forum", name=>"letzte 5 Beitr&auml;ge", link=>"forum.php?view=letzte&mehr=1", active=>FALSE);
$structure["suchen"]=array (topKat=>"forum", name=>"Suchen", link=>"suchen.php", active=>FALSE);
$structure["forum_export"]=array (topKat=>"forum", name=>"Druckansicht", link=>"forum_export.php", target=>"_new", active=>FALSE);
if ($rechte)
	$structure["neues_thema"]=array (topKat=>"forum", name=>"neues Thema", link=>"forum.php?neuesthema=TRUE#anker", active=>FALSE);
//
if ($SessSemName["class"]=="sem") {
	$structure["_dates"]=array (topKat=>"dates", name=>"alle Termine", link=>"dates.php", active=>FALSE);
	$structure["sitzung"]=array (topKat=>"dates", name=>"Sitzungstermine", link=>"dates.php?show_not=sem", active=>FALSE);
	$structure["andere_t"]=array (topKat=>"dates", name=>"andere Termine", link=>"dates.php?show_not=other", active=>FALSE);
	if ($rechte)
		$structure["admin_dates"]=array (topKat=>"dates", name=>"Ablaufplan bearbeiten", link=>"admin_dates.php?new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
}
//
$structure["_folder"]=array (topKat=>"folder", name=>"Ordneransicht", link=>"folder.php?cmd=tree", active=>FALSE);
$structure["alle_dateien"]=array (topKat=>"folder", name=>"Alle Dateien", link=>"folder.php?cmd=all", active=>FALSE);
//
if ($SessSemName["class"]=="sem")
	$structure["_literatur"]=array (topKat=>"literatur", name=>"Literatur und Links", link=>"literatur.php?view=literatur_sem", active=>FALSE);
else
	$structure["_literatur"]=array (topKat=>"literatur", name=>"Literatur und Links", link=>"literatur.php?view=literatur_inst", active=>FALSE);
	

if ($SessSemName["class"]=="sem")
	$structure["statusgruppen"]=array (topKat=>"teilnehmer", name=>"Funktionen / Gruppen", link=>"statusgruppen.php?view=statusgruppe_sem", active=>FALSE);
//else
//	$structure["statusgruppen"]=array (topKat=>"personal", name=>"Statusgruppen", link=>"statusgruppen.php?view=statusgruppe_inst", active=>FALSE);


if ($rechte)
	if ($SessSemName["class"]=="sem")
		$structure["Statusgruppen verwalten"]=array (topKat=>"teilnehmer", name=>"Funktionen / Gruppen verwalten", link=>"admin_statusgruppe.php?view=statusgruppe_sem&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
	else
		if ($perm->have_perm("admin"))
			$structure["Statusgruppen verwalten"]=array (topKat=>"personal", name=>"Funktionen / Gruppen verwalten", link=>"admin_statusgruppe.php?view=statusgruppe_inst&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);


if ($rechte)
	if ($SessSemName["class"]=="sem")
		$structure["admin_literatur"]=array (topKat=>"literatur", name=>"Literatur und Links bearbeiten", link=>"admin_literatur.php?view=literatur_sem&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
	else
		$structure["admin_literatur"]=array (topKat=>"literatur", name=>"Literatur und Links bearbeiten", link=>"admin_literatur.php?view=literatur_inst&new_inst=TRUE&range_id=".$SessSemName[1], active=>FALSE);

if ($RESOURCES_ENABLE) {
	$structure["resources_overview"]=array (topKat=>"resources", name=>"&Uuml;bersicht", link=>"resources.php?view=openobject_main", active=>FALSE);
	$structure["resources_details"]=array (topKat=>"resources", name=>"Details", link=>"resources.php?view=openobject_details", active=>FALSE);
	$structure["resources_schedule"]=array (topKat=>"resources", name=>"Belegung", link=>"resources.php?view=openobject_schedule", active=>FALSE);
	$structure["resources_assign"]=array (topKat=>"resources", name=>"Belegungen bearbeiten", link=>"resources.php?view=openobject_assign", active=>FALSE);
	if ($rechte)
		$structure["resources_admin"]=array (topKat=>"resources", name=>"Ressourcen verwalten", link=>"resources.php?view=resources", active=>FALSE);
}


//Infofenstereintraege erzeugen
if ($SessSemName["class"]=="inst") {
	$tooltip="Sie befinden sich in der Einrichtung: ".$SessSemName[0].", letzter Besuch: ".date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]).", Ihr Status in dieser Einrichtung: ".$SemUserStatus;
} else {
	$tooltip="Sie befinden sich in der Veranstaltung: ".$SessSemName[0].", letzter Besuch: ".date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]).", Ihr Status in dieser Veranstaltung: ".$SemUserStatus;
}

//View festlegen
switch ($i_page) {
	case "show_bereich.php" : 
		$reiter_view="institut_veranstaltungen"; 
	break;
	case "institut_main.php" : 
		$reiter_view="institut_main"; 
	break;
	case "institut_members.php" :
		$reiter_view = "institut_members";
	break;
	case "seminar_main.php" : 
		$reiter_view="seminar_main"; 
	break;
	case "details.php" : 
		$reiter_view="details"; 
	break;
	case "teilnehmer.php" : 
		$reiter_view="teilnehmer"; 
	break;
	case "statusgruppen.php" : 
		$reiter_view="statusgruppen"; 
	break;
	case "institut_details.php": 
		$reiter_view="institut_details"; 
	break;
	case "inst_admin.php": 
		$reiter_view="inst_admin"; 
	break;
	case "admin_institut.php": 
		$reiter_view="admin_institut"; 
	break;
	case "admin_literatur.php": 
		$reiter_view="admin_literatur"; 
	break;
	case "admin_news.php": 
		$reiter_view="admin_news"; 
	break;
	case "forum.php": 
		switch ($view) {
			case "":
				$reiter_view="forum";
			break;
			case "neue":
				$reiter_view="neue";
			break;
			case "letzte":
				$reiter_view="letzte";
			break;
			default :
				$reiter_view="forum";
			break;
		}
	break;
	case "dates.php": 
		switch ($show_not) {
			case "":
				$reiter_view="dates";
			break;
			case "sem":
				$reiter_view="sitzung";
			break;
			case "other":
				$reiter_view="andere_t";
			break;
			default :
				$reiter_view="dates";
			break;
		}
	break;
	case "folder.php": 
		switch ($cmd) {
			case "":
				$reiter_view="folder";
			break;
			case "tree":
				$reiter_view="folder";
			break;
			case "all":
				$reiter_view="alle_dateien";
			break;
			default :
				$reiter_view="folder";
			break;
		}
	break;
	case "suchen.php": 
		$reiter_view="suchen"; 
	break;	
	case "mein_stundenplan.php": 
		$reiter_view="timetable"; 
	break;	
	case "literatur.php": 
		$reiter_view="literatur";
	break;
	case "resources.php": 
		switch ($view) {
			case "openobject_main":
				$reiter_view="resources";
			break;
			case "openobject_details":
				$reiter_view="resources_details";
			break;
			case "openobject_schedule":
				$reiter_view="resources_schedule";
			break;
			case "openobject_assign":
				$reiter_view="resources_assign";
			break;
			default :
				$reiter_view="resources";
			break;
		}
	break;
	default :
		if ($SessSemName["class"]=="inst")
			$reiter_view="institut_main";
		else
			$reiter_view="seminar_main";
	break;
}

$reiter->create($structure, $reiter_view, $tooltip);
}
?>