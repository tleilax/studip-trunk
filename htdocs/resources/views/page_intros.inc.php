<?

$currentObject=new ResourceObject($resources_data["actual_object"]);
$currentObjectTitelAdd=$currentObject->getCategoryName();
if ($currentObjectTitelAdd)
	$currentObjectTitelAdd=": ";
$currentObjectTitelAdd=$currentObject->getName()."&nbsp;<font size=-1>(".$currentObject->getOwnerName().")</font>";

switch ($resources_data["view"]) {
	//Reiter "Uebersicht"
	case "resources":
	case "_resources":
	case "create_hierachie":
	case "search":
		$page_intro="Auf dieser Seite k&ouml;nnen Sie Ressourcen, auf die Sie Zugriff haben, Ebenen zuordnen. ";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	
	//Reiter "Listen"
	case "lists":
	case "_lists":
	case "export_lists":
	case "search_list":
		if ($resources_data["list_open"])
			$page_intro="Sie sehen alle Eintr&auml;ge in der Ebene <b>".getResourceObjectName($resources_data["list_open"])."</b>.";
		$title="Bearbeiten und Ausgeben von Listen";
		if ($resources_data["list_open"])
			$title.=" - Ebene: ".getResourceObjectName($resources_data["list_open"]);
		$infobox = array(
					array  ("kategorie"  => "Information:", 
							"eintrag" => array (
								array ("icon" => "pictures/ausruf_small.gif", 	
									"text"  => ($resources_data["list_recurse"]) ? "Untergeordnete Ebenen werden ausgegeben." : "Untergeordnete Ebenen werden <u>nicht</u> ausgegeben."))),
					array  ("kategorie" => "Aktionen:", 
							"eintrag" => array (
								array	("icon" =>  (!$resources_data["list_recurse"]) ? "pictures/on_small.gif" : "pictures/off_small.gif",
									"text"  => ($resources_data["list_recurse"]) ? "Untergeordnete Ebenen <br /><a href=\"$PHP_SELF?nrecurse_list=TRUE\"><u>nicht</u> ausgeben.</a>" : "Untergeordnete Ebenen <br /><a href=\"$PHP_SELF?recurse_list=TRUE\">mit ausgeben</a>"))));
	break;

	//Reiter "Objekt"
	case "objects":
	case "edit_object_assign":
	case "edit_object_properties":
	case "edit_object_schedules":
	case "view_schedule":
	case "search_object":
		$page_intro="Hier k&ouml;nnen Sie einzelen Objekte verwalten. Sie k&ouml;nnen Eigenschaften, Berechtigungen und Belegung verwalten.";
		$title="Objekt bearbeiten: ".$currentObjectTitelAdd;
	break;
	case "view_schedule":
		$page_intro="Hier k&ouml;nnen Sie sich den Belegungsplan des Objektes ausgeben lassen. Bitte w&auml;hlen Sie daf&uuml;r den Zeitraum aus.";
		$title="Belegung ausgeben - Objekt: ".$currentObjectTitelAdd;
	break;
	
	//Reiter "Anpassen"	
	case "settings":
	case "edit_types":
	case "edit_properties":
	case "edit_perms":
		$page_intro="Hier k&ouml;nnen Sie grundlegen Einstellungen der Ressourcenverwaltung vornehmen.";
		$title="Einstellungen bearbeiten";
	break;
	
	//all the intros in an open object (Veranstaltung, Einrichtung)
	case "openobject_main":
		$page_intro="Auf dieser Seite sehen sie alle der ".$SessSemName["art_generic"]." zugeordneten Ressourcen.";
		$title=$SessSemName["header_line"]." - Ressourcen&uuml;bersicht";
	break;
	case "openobject_details":
		if ($resources_data["actual_object"])
			$page_intro="Hier sehen Sie detaillierte Informationen der Ressource <b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").";
		$title=$SessSemName["header_line"]." - Ressourcendetails";
	break;
	case "openobject_schedule":
		if ($resources_data["actual_object"])
			$page_intro="Hier k&ouml;nnen Sie sich die Belegungszeiten der Ressource <b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().") ausgeben lassen.";
		$title=$SessSemName["header_line"]." - Ressourcenbelegung";
	break;
	case "openobject_assign":
		if ($resources_data["actual_object"])
			$page_intro="Bearbeiten von Belegungen der Ressource <b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").";
		$title=$SessSemName["header_line"]." - Bearbeiten der Belegung";
	break;
	case "edit_object_perms":
		if ($resources_data["actual_object"])
			$page_intro="Vergeben von Rechten auf die Ressource <b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").";
		$title="Vergeben von Berechtigungen - Objekt: ".$currentObjectTitelAdd;
	break;
	//default
	default:
		$resources_data["view"]="resources";
		$page_intro="Sie befinden sich in der Ressurcenverwaltung von Stud.IP. Sie k&ouml;nnen hier R&auml;ume, Geb&auml;ude, Ger&auml;te und andere Ressourcen verwalten.";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	}
?>