<?
/**
* page_intros.inc.php
* 
* library for the messages on the pages, contents of the infoboxes and stuff to display
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	resources
* @module		page_intros.inc.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// page_intros.inc.php
// Nachrichten, Inhalt der Infokaesten und andere Inhalte der Seiten der Ressourcenverwaltung
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


if ($resources_data["actual_object"]) {
	$currentObject=new ResourceObject($resources_data["actual_object"]);
	$currentObjectTitelAdd=": ".(($currentObject->getCategoryName()) ? $currentObject->getCategoryName() : _("Hierachieebene"));
	if ($currentObjectTitelAdd)
		$currentObjectTitelAdd=": ";
	$currentObjectTitelAdd=": ".$currentObject->getName()."&nbsp;<font size=-1>(".$currentObject->getOwnerName().")</font>";
}

switch ($resources_data["view"]) {
	//Reiter "Uebersicht"
	case "resources":
	case "_resources":
		$page_intro=_("Auf dieser Seite k&ouml;nnen Sie durch alle Ressourcen bzw. Ebenen, auf die Sie Zugriff haben, navigieren und Ressourcen verwalten.");
		$title=_("&Uuml;bersicht der Ressourcen");
	break;
	case "search":
		$page_intro=_("Sie k&ouml;nnen hier nach Ressourcen suchen. Sie haben die M&ouml;glichkeit, &uuml;ber ein Stichwort oder bestimmte Eigenschaften Ressourcen zu suchen oder sich durch die Ebenen zu navigieren.");
		$title=_("Suche nach Ressourcen");
		$infobox = array(
/*					array  ("kategorie"  => _("Information:"), 
							"eintrag" => array (
								array ("icon" => "pictures/ausruf_small.gif", 	
									"text"  => ($resources_data["list_recurse"]) ? _("Untergeordnete Ebenen werden ausgegeben.") : _("Untergeordnete Ebenen werden <u>nicht</u> ausgegeben.")))),*/
					array  ("kategorie" => "Aktionen:", 
							"eintrag" => array (
								array	("icon" => "pictures/suchen.gif",
									"text"  => (($resources_data["search_mode"] == "browse") || (!$resources_data["search_mode"]))? sprintf(_("Ressourcen &uuml;ber ihre %sEigenschaften%s suchen"), "<a href=\"$PHPSELF?view=search&mode=properties\">", "</a>") :  sprintf(_("%sEbenen%s durchsuchen"), "<a href=\"$PHP_SELF?view=search&mode=browse\">", "</a>")),
								array("icon" => "pictures/blank.gif",
									"text"  => "<br /><a href=\"$PHP_SELF?view=search&reset=TRUE\">".makeButton("neuesuche")."</a>"))));
		$infopic = "pictures/rooms.jpg";
	break;
	
	//Reiter "Listen"
	case "lists":
	case "_lists":
	case "export_lists":
	case "search_list":
		if ($resources_data["list_open"])
			$page_intro= sprintf(_("Sie sehen alle Eintr&auml;ge in der Ebene <b>%s</b>"), getResourceObjectName($resources_data["list_open"]));
		$title=_("Bearbeiten und ausgeben von Listen");
		if ($resources_data["list_open"])
			$title.=" - "._("Ebene").": ".getResourceObjectName($resources_data["list_open"]);
		$infobox = array(
					array  ("kategorie"  => _("Information:"), 
							"eintrag" => array (
								array ("icon" => "pictures/ausruf_small.gif", 	
									"text"  => ($resources_data["list_recurse"]) ? _("Untergeordnete Ebenen werden ausgegeben.") : _("Untergeordnete Ebenen werden <u>nicht</u> ausgegeben.")))),
					array  ("kategorie" => "Aktionen:", 
							"eintrag" => array (
								array	("icon" =>  (!$resources_data["list_recurse"]) ? "pictures/on_small.gif" : "pictures/off_small.gif",
									"text"  => ($resources_data["list_recurse"]) ? sprintf(_("Ressourcen in untergeordneten Ebenen %snicht ausgeben%s."), "<a href=\"$PHPSELF?nrecurse_list=TRUE\">", "</a>") :  sprintf(_("Ressourcen in untergeordneten Ebenen %s(mit) ausgeben%s"), "<a href=\"$PHP_SELF?recurse_list=TRUE\">", "</a>")))));
		$infopic = "pictures/rooms.jpg";
	break;

	//Reiter "Objekt"
	case "objects":
	case "edit_object_assign":
		$page_intro=_("Hier k&ouml;nnen Sie Ressourcen-Belegungen bearbeiten und neue anlegen.");
		$title=_("Belegungen bearbeiten").$currentObjectTitelAdd;
	break;
	case "edit_object_properties":
		$page_intro=_("Hier k&ouml;nnen Sie Ressourcen-Eigenschaften bearbeiten.");
		$title=_("Eigenschaften bearbeiten").$currentObjectTitelAdd;
	break;
	case "edit_object_perms":
		$page_intro=_("Hier k&ouml;nnen Sie Berechtigungen f&uuml;r den Zugriff auf die Ressource vergeben.")." <br /><font size=\"-1\">"._("<b>Achtung:</b> Alle hier erteilten Berechtigungen gelten ebenfalls f&uuml;r die Ressourcen, die der gew&auml;hlten Ressource untergeordnet sind!")."</font>";
		$title=_("Eigenschaften bearbeiten").$currentObjectTitelAdd;
	break;
	case "view_schedule":
		$page_intro=_("Hier k&ouml;nnen Sie sich die Belegungszeiten der Ressource anzeigen lassen und auf unterschiedliche Art darstellen lassen.");
		$title=_("Belegungszeiten ausgeben").$currentObjectTitelAdd;
	break;
	
	//Reiter "Anpassen"	
	case "settings":
	case "edit_types":
		$page_intro=_("Verwalten Sie auf dieser Seite die Ressourcen-Typen, wie etwa R&auml;ume, Ger&auml;te oder Geb&auml;ude. Sie k&ouml;nnen jedem Typ beliebig viele Eigenschaften zuordnen.");
		$title=_("Typen bearbeiten");
	break;
	case "edit_properties":
		$page_intro=_("Verwalten Sie auf dieser Seite die einzelnen Eigenschaften. Diese Eigenschaften k&ouml;nnen Sie beliebigen Ressourcen-Typen zuweisen.");
		$title=_("Eigenschaften bearbeiten");
	break;
	case "edit_perms":
		$page_intro=_("Verwalten Sie hier AdministratorInnen des Systems, die volle Rechte &uuml;ber alle Ressourcen erhalten.");
		$title=_("Ressourcen-AdministratorInnen bearbeiten");
	break;
	
	//all the intros in an open object (Veranstaltung, Einrichtung)
	case "openobject_main":
		$page_intro=sprintf(_("Auf dieser Seite sehen sie alle der %s zugeordneten Ressourcen."), $SessSemName["art_generic"]);
		$title=$SessSemName["header_line"]." - "._("Ressourcen&uuml;bersicht");
		$infobox = array(
					array  ("kategorie"  => _("Information:"), 
							"eintrag" => array (
								array ("icon" => "pictures/ausruf_small.gif", 	
									"text"  => _("Als Teilnehmer der Veranstaltung haben Sie die M&ouml;glichkeit, diese Ressourcen frei zu belegen oder den Belegungsplan einzusehen.")))));
		$infopic = "pictures/schedule.jpg";
	break;
	case "openobject_details":
	case "view_details":
		if ($resources_data["actual_object"])
			$page_intro= sprintf(_("Hier sehen Sie detaillierte Informationen der Ressource %s"), "<b>".$currentObject->getName()."</b> (".(($currentObject->getCategoryName()) ? $currentObject->getCategoryName() : _("Hierachieebene")).").");
		if ($resources_data["view_mode"] == "oobj")
			$title=$SessSemName["header_line"]." - "._("Ressourcendetails");
		else
			$title=_("Anzeige der Ressourceneigenschaften");
		$infobox = array(
					array  ("kategorie" => _("Aktionen:"), 
							"eintrag" => array (
								array	("icon" => "pictures/forumrot.gif",
									"text"  => (($resources_data["view_mode"] == "no_nav") || ($resources_data["search_array"])) ? "<a href=\"$PHP_SELF?view=search\">"._("zur&uuml;ck zur Suche")."</a>" : "<a href=\"$PHP_SELF?view=".(($SessSemName[1]) ? "openobject_main" : "resources")."\">"._("zur&uuml;ck zur &Uuml;bersicht")."</a>"))));
		$infopic = "pictures/schedule.jpg";
	break;
	case "openobject_schedule":
		if ($resources_data["actual_object"])
			$page_intro=sprintf(_("Hier k&ouml;nnen Sie sich die Belegungszeiten der Ressource %s"), "<b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().") ausgeben lassen.");
		$title=$SessSemName["header_line"]." - "._("Ressourcenbelegung");
	break;
	case "openobject_assign":
		if ($resources_data["actual_object"])
			$page_intro=sprintf(_("Bearbeiten von Belegungen der Ressource %s"), "<b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").");
		$title=$SessSemName["header_line"]." - ".("Bearbeiten der Belegung");
	break;
	case "edit_object_perms":
		if ($resources_data["actual_object"])
			$page_intro=sprintf(_("Vergeben von Rechten auf die Ressource %s"), "<b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").");
		$title=sprintf(_("Vergeben von Berechtigungen - Objekt%s"), $currentObjectTitelAdd);
	break;
	//default
	default:
		$page_intro=_("Sie befinden sich in der Ressourcenverwaltung von Stud.IP. Sie k&ouml;nnen hier R&auml;ume, Geb&auml;ude, Ger&auml;te und andere Ressourcen verwalten.");
		$title=_("&Uuml;bersicht der Ressourcen");
	break;
	}
?>