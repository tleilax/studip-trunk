<?
/**
* Tool to delete old files in the tmp-directory.
* 
* 
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup	export_modules
* @module		oscar
* @package		Export
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// oscar.inc.php
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
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

//page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
//$perm->check("dozent");

require_once ("./export_xslt_vars.inc.php"); // 

$dirstr = "" . $TMP_PATH;
//$dirstr = "/apache/htdocs/studip/" . $TMP_PATH;
if (!($handle=opendir( $dirstr )))
	echo "Das Verzeichnis existiert nicht!";
else
{
	echo "Verzeichnis: $handle<br>";
	echo "Dateien:<br>";

	while (($file = readdir($handle))!==false) 
	{
		$file_parts = explode(".", $file);
		$endung = $file_parts[ sizeof($file_parts)-1 ];
		echo $endung;
		if (filemtime($dirstr . "/" . $file) < (time() - 60*60 * 24) AND ($file != ".") AND ($file != "..") AND !is_dir($dirstr . "/" . $file) 
		AND (in_array($endung, array("xml", "pdf", "fo", "htm", "html", "rtf"))))
		{
			echo "<font color=\"FF0000#\">" . date("h:i d. m. y", filemtime($dirstr . "/" . $file)) . " $file</font><br>";
			if (unlink($dirstr . "/" . $file)) 
				$deleted++;
		}
		else
			echo date("h:i d. m. y", filemtime($dirstr . "/" . $file)) . " $file<br>";
	}

	closedir($handle); 
	if ($deleted<1)
		echo "Es wurden keine Dateien gel&ouml;scht.<br>";
	else
		echo "Es wurden $deleted Dateien gel&ouml;scht.<br>";
}
//page_close();
?>