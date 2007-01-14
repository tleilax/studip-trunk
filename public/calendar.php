<?
/**
* calendar.php
* 
* Calendar-mainfile. Calls the submodules.
*
* @author		Peter Thienel <pthienel@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup	extern_modules
* @module		extern
* @package		Extern
*/


// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// calendar.php
//
// Copyright (c) 2002 Peter Tienel <pthienel@data-quest.de> 
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

/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);


// Default_Auth
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
if ($perm->have_perm("admin")) {
	$perm->perm_invalid($auth->auth["perm"], "");
	exit;
}
$perm->check("user");

include ("seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

switch ($cmd) {
	case 'edit':
		$HELP_KEYWORD="Basis.TerminkalenderBearbeiten";
		break;
	case 'bind':
		$HELP_KEYWORD="Basis.TerminkalenderEinbinden";
		break;
	case 'changeview':
		$HELP_KEYWORD="Basis.TerminkalenderEinstellungen";
		break;
	default:
		$HELP_KEYWORD="Basis.Terminkalender";
}
	
if ($CALENDAR_ENABLE)
	//Kalenderfrontend einbinden
	include($RELATIVE_PATH_CALENDAR."/calendar.inc.php");
else {
	require_once ("msg.inc.php");
	// Start of Output
	include ("html_head.inc.php"); // Output of html head
	include ("header.php");   // Output of Stud.IP head
	$message = _("Der Terminkalender ist nicht eingebunden. Der Terminkalender wurde in den Systemeinstellungen nicht freigeschaltet. Wenden Sie sich bitte an die zust&auml;ndigen Administrierenden.");
	parse_window ("error�$message", "�", _("Terminkalender ist nicht eingebunden!"));
include ('html_end.inc.php');
}
?>