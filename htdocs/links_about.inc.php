<?
/*
links_about.inc.php - Navigation fuer die Uebersichtsseiten.
Copyright (C) 2002	Stefan Suchi <suchi@gmx.de>, 
				Ralf Stockmann <rstockm@gwdg.de>, 
				Cornelis Kater <ckater@gwdg.de
				Suchi & Berg GmbH <info@data-quest.de> 

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

require_once "$ABSOLUTE_PATH_STUDIP/reiter.inc.php";

$reiter=new reiter;



//Create Reitersystem

//Topkats
$structure["alle"]=array (topKat=>"", name=>_("Alle"), link=>"about.php?username=$username", active=>FALSE);
$structure["bild"]=array (topKat=>"", name=>_("Bild"), link=>"edit_about.php?view=Bild&username=$username", active=>FALSE);
$structure["daten"]=array (topKat=>"", name=>_("Nutzerdaten"), link=>"edit_about.php?view=Daten&username=$username", active=>FALSE);
$structure["karriere"]=array (topKat=>"", name=>_("universit&auml;re Daten"), link=>"edit_about.php?view=Karriere&username=$username", active=>FALSE);
$structure["lebenslauf"]=array (topKat=>"", name=>_("Lebenslauf"), link=>"edit_about.php?view=Lebenslauf&username=$username", active=>FALSE);
$structure["sonstiges"]=array (topKat=>"", name=>_("Sonstiges"), link=>"edit_about.php?view=Sonstiges&username=$username", active=>FALSE);
if ($username==$auth->auth["uname"]) 
// if (!$perm->have_perm("admin"))
	$structure["mystudip"]=array (topKat=>"", name=>_("My Stud.IP"), link=>"edit_about.php?view=allgemein&username=$username", active=>FALSE);

//Bottomkats
$structure["_alle"]=array (topKat=>"alle", name=>_("Pers&ouml;nliche Homepage"), link=>"about.php?username=$username", active=>FALSE);
$structure["_bild"]=array (topKat=>"bild", name=>_("Hochladen des pers&ouml;nlichen Bildes"), link=>"edit_about.php?view=Bild&username=$username", active=>FALSE);
$structure["_daten"]=array (topKat=>"daten", name=>_("Nutzerdaten bearbeiten"), link=>"edit_about.php?view=Daten&username=$username", active=>FALSE);
$structure["_karriere"]=array (topKat=>"karriere", name=>_("universit&auml;re Daten"), link=>"edit_about.php?view=Karriere&username=$username", active=>FALSE);
if (!$perm->have_perm ("dozent")) {
	$structure["studiengaenge"]=array (topKat=>"karriere", name=>_("Zuordnung zu Studieng&auml;ngen"), link=>"edit_about.php?view=Karriere&username=$username#studiengaenge", active=>FALSE);
	$structure["einrichtungen"]=array (topKat=>"karriere", name=>_("Zuordnung zu Einrichtungen"), link=>"edit_about.php?view=Karriere&username=$username#einrichtungen", active=>FALSE);
}
$structure["_lebenslauf"]=array (topKat=>"lebenslauf", name=>_("Lebenslauf"), link=>"edit_about.php?view=Lebenslauf&username=$username", active=>FALSE);
if ($perm->have_perm ("dozent")) {
	$structure["schwerpunkte"]=array (topKat=>"lebenslauf", name=>_("Schwerpunkte"), link=>"edit_about.php?view=Lebenslauf&username=$username#schwerpunkte", active=>FALSE);
	$structure["publikationen"]=array (topKat=>"lebenslauf", name=>_("Publikationen"), link=>"edit_about.php?view=Lebenslauf&username=$username#publikationen", active=>FALSE);
}
$structure["_sonstiges"]=array (topKat=>"sonstiges", name=>_("eigene Kategorien bearbeiten"), link=>"edit_about.php?view=Sonstiges&username=$username", active=>FALSE);
$structure["allgemein"]=array (topKat=>"mystudip", name=>_("Allgemeines"), link=>"edit_about.php?view=allgemein&username=$username", active=>FALSE);
$structure["forum"]=array (topKat=>"mystudip", name=>_("Forum"), link=>"edit_about.php?view=Forum&username=$username", active=>FALSE);
if (!$perm->have_perm("admin")) {
	$structure["calendar"]=array (topKat=>"mystudip", name=>_("Terminkalender"), link=>"edit_about.php?view=calendar&username=$username", active=>FALSE);
	$structure["stundenplan"]=array (topKat=>"mystudip", name=>_("Stundenplan"), link=>"edit_about.php?view=Stundenplan&username=$username", active=>FALSE);
}
$structure["messaging"]=array (topKat=>"mystudip", name=>_("Messaging"), link=>"edit_about.php?view=Messaging&username=$username", active=>FALSE);
if (!$perm->have_perm("admin")) {
	$structure["login"]=array (topKat=>"mystudip", name=>_("Login"), link=>"edit_about.php?view=Login&username=$username", active=>FALSE);
}


//View festlegen
switch ($i_page) {
	case "about.php" : 
		$reiter_view="alle"; 
	break;
	case "edit_about.php" : 
		switch ($view) {
			case "Bild":
				$reiter_view="bild"; 
			break;
			case "Daten":
				$reiter_view="daten"; 
			break;
			case "Karriere":
				$reiter_view="karriere"; 
			break;
			case "Lebenslauf":
				$reiter_view="lebenslauf"; 
			break;
			case "Sonstiges":
				$reiter_view="sonstiges"; 
			break;
			case "Login":
				$reiter_view="login"; 
			break;
			case "allgemein":
				$reiter_view="allgemein"; 
			break;
			case "Forum":
				$reiter_view="forum"; 
			break;
			case "calendar":
				$reiter_view="calendar"; 
			break;
			case "Stundenplan":
				$reiter_view="stundenplan"; 
			break;
			case "Messaging":
				$reiter_view="messaging"; 
		}
	break;
	default :
		$reiter_view="alle";
	break;
}

$reiter->create($structure, $reiter_view, $alt, $js);
?>
