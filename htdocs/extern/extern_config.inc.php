<?
/**
* extern_config.inc.php
* 
* extern modules configuration file
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		extern_config
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_config.inc.php
// extern modules configuration file
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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

$EXTERN_SERVER_NAME = "localhost/studip";

$EXTERN_CONFIG_FILE_PATH = "/var/lib/studip/extern_config/";

$EXTERN_MODULE_TYPES[1] = array("module" => "Download", "name" => _("Download"), "level" => 1,
													"description" => _("Das Modul &quot;Download&quot; stellt alle Dateien aus dem Dateibereich einer Einrichtung zum Download zur Verf&uuml;gung."));

$EXTERN_MODULE_TYPES[2] = array("module" => "Persons", "name" => _("Personal"), "level" => 1,
													"description" => _("Das Modul &quot;Personal&quot; gibt ein Mitarbeiterverzeichnis einer Einrichtung aus."));

$EXTERN_MODULE_TYPES[3] = array("module" => "Semlecturetree", "name" => _("Bereichsbaum Veranstaltungen"), "level" => 1,
													"description" => _("Das Modul &quot;Veranstaltungen&quot; gibt alle Veranstaltungen einer Einrichtung aus."));

$EXTERN_MODULE_TYPES[4] = array("module" => "Rangelecturetree", "name" => _("Einrichtungsbaum Veranstaltungen"), "level" => 1,
													"description" => _("Das Modul &quot;Veranstaltungen&quot; gibt alle Veranstaltungen einer Einrichtung aus."));

$EXTERN_MODULE_TYPES[5] = array("module" => "News", "name" => _("News"), "level" => 1,
													"description" => _("Das Modul &quot;News&quot; gibt alle News einer Einrichtung aus."));

$EXTERN_MODULE_TYPES[6] = array("module" => "Persondetails", "name" => _("Mitarbeiterdetails"), "level" => 2,
													"description" => _("Das Modul &quot;Mitarbeiterdetails&quot; gibt die Daten eines Mitarbeiters einer Einrichtung aus."));
/*
$EXTERN_MODULE_TYPES[7] = array("module" => "Lecturedetails", "name" => _("Veranstaltungsdetails"), "level" => 2,
													"description" => _("Das Modul &quot;Veranstaltungsdetails&uot; gibt alle allgemeinen Daten einer Veranstaltung aus."));

$EXTERN_MODULE_TYPES[8] = array("module" => "Tree", "name" => _("Bereichsbaum"), "level" => 1,
													"description" => _("Das Modul &quot;Bereichsbaum&quot; gibt die Struktur einer Einrichtung aus, wenn diese im Bereichsbaum eingef�gt wurde. Die Ausgabe erfolgt jeweils vor der Datenausgabe eines anderen Moduls."));
*/
// Don't allow more than 10 configuration files for each module!
$EXTERN_MAX_CONFIGURATIONS = 4;

// print this message instead of data if an error occurs
$EXTERN_ERROR_MESSAGE = "<b>Ein Fehler ist aufgetreten. Die Daten k&ouml;nnen nicht angezeigt werden. Bitte wenden Sie sich an den Webmaster.</b>";

// Not yet implemented!
$EXTERN_ENABLE_ERROR_LOGGING = FALSE;

// Not yet implemented!
$EXTERN_LOG_FILE = "";

// don't edit below this line
//==============================================================================

if (substr($EXTERN_SERVER_NAME, -1) != "/")
	$EXTERN_SERVER_NAME .= "/";

?>
