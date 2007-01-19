<?php
/*
seminar_redirect.php - Setzt Sessionvariablen und springt danach direkt weiter ins Forum oder andere Teile von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>, Andr� Noack <andre.noack@gmx.net>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once("config.inc.php");
require_once("msg.inc.php");
require_once 'lib/functions.php';

// wichtiger Teil aus seminar_open.php
	
	if ($auth->is_authenticated() && $user->id != "nobody") {
		if ($SessionStart > $CurrentLogin) {      // gerade eingeloggt
			$LastLogin=$CurrentLogin;
			$CurrentLogin=$SessionStart;
			$user->register("loginfilelast");
			$user->register("loginfilenow");
			$user->register("CurrentLogin");
			$user->register("LastLogin");
		}
  }
	if ($SessionStart==0) { 
		$SessionStart=time(); 
		$SessionSeminar="";
		$SessSemName="";
		$sess->register("SessionStart");
		$sess->register("SessionSeminar");
		$sess->register("SessSemName");
	}		


if (isset($auswahl) && $auswahl!="") {
	//just opened Veranstaltung... here follows the init
	openSem($auswahl);
}	else {
		$auswahl=$SessSemName[1];
}
	

if ($SessSemName[1] =="") {
	// Start of Output
	include ('include/html_head.inc.php'); // Output of html head
	include ('include/header.php');   // Output of Stud.IP head
	parse_window ("error�Die aufgerufene Veranstaltung existiert nicht!<br /><font size=-1 color=black>Der Autor der aufrufenden Seite hat keine Veranstaltung gew&auml;hlt oder die angegebene Veranstaltung existiert nicht mehr.<br /></font>", "�",
				"Keine Veranstaltung gew&auml;hlt", 
				"&nbsp;Bitte informieren Sie den zust&auml;ndigen Webmaster.");
	die;

} else {

	switch ($target) {
		case "folder.php":
			header("Location: folder.php?cmd=tree");
			break;
		case "dates.php":
			header("Location: dates.php");
			break;
		case "literatur.php":
			header("Location: literatur.php");
			break;
		case "forum.php":
		default:
			header("Location: forum.php");
			break;
	}
	page_close();
	die;
}
