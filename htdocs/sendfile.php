<?
/**
* sendfile.php
* 
* Send files to the browser an does permchecks
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, Andr� Noack Andr� Noack <andre.noack@gmx.net>
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		sendfile.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sendfile.php - Datei an Browser senden
// Copyright (C) 2000 - 2002 Cornelis Kater <ckater@gwdg.de>
// Ralf Stockmann <rstockm@gwdg.de>, Andr� Noack Andr� Noack <andre.noack@gmx.net>
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

if(ini_get('zlib.output_compression'))
      ini_set('zlib.output_compression', 'Off');

$dont_put_headers=TRUE;
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/datei.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");

$db=new DB_Seminar;
$db2=new DB_Seminar;

switch ($type) {
	//We want to download from the archive
	case 1: 
		$path_file=$ARCHIV_PATH."/".$file_id;
	break;
	//We want to download from the tmp/export-folder
	case 2:
		$path_file=$TMP_PATH."/export/".$file_id;
	break;
	//We want to download an XSL-Script
	case 3:
		$path_file=$ABSOLUTE_PATH_STUDIP . $PATH_EXPORT . "/".$file_id;
	break;
	//We want to download from the regular upload-folder
	default:
		$path_file=$UPLOAD_PATH."/".$file_id;
	break;
}
	
if ($zip) {
	$tmp_id=md5(uniqid("suppe"));
	exec ("cp '$path_file' '$TMP_PATH/".rawurldecode($file_name)."'");
	exec ("$ZIP_PATH -9 -j $TMP_PATH/$tmp_id.zip '$TMP_PATH/".rawurldecode($file_name)."'");
	$tmp_file_name="'$TMP_PATH/".rawurldecode($file_name)."'";
	$file_name=$file_name.".zip";
	$path_file="$TMP_PATH/$tmp_id.zip";
	}


//Ersetzung von beim Speichern gefaehrlichen Zeichen. Ich weiss, es geht mit ereg_replace besser, hab ich aber grad keine Zeit, mir die Codes reinzuziehen :)
$file_name=str_replace(":", "", rawurldecode($file_name));
$file_name=str_replace(chr(92), "", rawurldecode($file_name));
$file_name=str_replace("/", "-", rawurldecode($file_name));
$file_name=str_replace("\"", "-", rawurldecode($file_name));
$file_name=str_replace("'", "", rawurldecode($file_name));
$file_name=str_replace(">", "", rawurldecode($file_name));
$file_name=str_replace("<", "", rawurldecode($file_name));
$file_name=str_replace("*", "", rawurldecode($file_name));
$file_name=str_replace("|", "", rawurldecode($file_name));
$file_name=str_replace("?", "", rawurldecode($file_name));

if ($force_download) {
	$content_type="application/octet-stream";
	$content_disposition="inline";
} else {
	switch (strtolower(getFileExtension ($file_name))) {
		case "txt": 
			$content_type="text/plain";
			$content_disposition="inline";
		break;
		case "css": 
			$content_type="text/css";
			$content_disposition="inline";		
		break;
		case "gif": 
			$content_type="image/gif";
			$content_disposition="inline";		
		break;
		case "jpeg": 
			$content_type="image/jpeg";
			$content_disposition="inline";		
		break;
		case "jpg": 
			$content_type="image/jpeg";
			$content_disposition="inline";		
		break;
		case "jpe": 
			$content_type="image/jpeg";
			$content_disposition="inline";		
		break;
		case "bmp": 
			$content_type="image/x-ms-bmp";
			$content_disposition="inline";		
		break;
		case "wav": 
			$content_type="audio/x-wav";
			$content_disposition="inline";		
		break;
		case "ra": 
			$content_type="application/x-pn-realaudio";
			$content_disposition="inline";		
		break;
		case "ram": 
			$content_type="application/x-pn-realaudio";
			$content_disposition="inline";		
		break;
		case "mpeg": 
			$content_type="video/mpeg";
			$content_disposition="inline";		
		break;
		case "mpg": 
			$content_type="video/mpeg";
			$content_disposition="inline";		
		break;
		case "mpe": 
			$content_type="video/mpeg";
			$content_disposition="inline";		
		break;
		case "qt": 
			$content_type="video/quicktime";
			$content_disposition="inline";		
		break;
		case "mov": 
			$content_type="video/quicktime";
			$content_disposition="inline";		
		break;
		case "avi": 
			$content_type="video/x-msvideo";
			$content_disposition="inline";		
		break;
		case "rtf": 
			$content_type="application/rtf";
			$content_disposition="inline";		
		break;
		case "pdf": 
			$content_type="application/pdf";
			$content_disposition="inline";
		break;
		case "doc": 
			$content_type="application/msword";
			$content_disposition="inline";		
		break;
		case "xls": 
			$content_type="application/ms-excel";
			$content_disposition="inline";		
		break;
		case "ppt": 
			$content_type="application/ms-powerpoint";
			$content_disposition="inline";		
		break;
		case "zip": 
		case "tgz": 
		case "gz": 	
			$content_type="application/zip";
			$content_disposition="inline";		
		break;
		default:
			$content_type="application/octet-stream";
			$content_disposition="inline";		
		break;
		}
	}

//override disposition, if available
if ($disposition)
	$content_disposition=$disposition;

//determine the type of the object we want to download a file from (only in type=0 mode!)
$db->query("SELECT seminar_id AS object_id, filesize FROM dokumente WHERE dokument_id = '".$file_id."' ");
$db->next_record();

$skip_check=FALSE;
if (!$type) {
	$object_type = get_object_type($db->f("object_id"));
	if ($object_type == "inst")
		$skip_check=TRUE;
}

//permcheck
if (($type != 2) && ($type != 3) && (!$skip_check)) { //if type 2 or type 3 we download from the tmp directory and skip permchecks
	if (!$perm->have_perm ("user")) {
		if (!$type) {
			$db->query("SELECT Lesezugriff FROM seminare LEFT JOIN dokumente USING (seminar_id) WHERE dokument_id = '".$file_id."' ");
			$db->next_record();
			if ($db->f("Lesezugriff") != 0)
				$no_access=TRUE;
			}
		else
			$no_access=TRUE; //nobody darf nie an das Archiv
		}
	elseif (!$perm->have_perm("root")) {
		if ($type) {
			if ($perm->have_perm("admin") && !$perm->have_perm("root"))
				$db->query ("SELECT archiv.seminar_id FROM archiv LEFT JOIN archiv_user USING (seminar_id) WHERE archiv_file_id = '".$file_id."' ");
			else
				$db->query ("SELECT archiv.seminar_id FROM archiv LEFT JOIN archiv_user USING (seminar_id) WHERE user_id = '".$user->id."' AND archiv_file_id = '".$file_id."' ");
			if (!$db->next_record())
				$no_access=TRUE;
			if ($perm->have_perm("admin") && !$perm->have_perm("root")) {
				$db2->query("SELECT archiv.seminar_id FROM archiv LEFT OUTER JOIN user_inst ON (heimat_inst_id = institut_id) WHERE user_inst.user_id = '".$user->id."' AND user_inst.inst_perms = 'admin'");
				while ($db2->next_record())  {
					if($db->f("seminar_id") == $db2->f("seminar_id"))
						$admin=TRUE;
					}
				if ($admin)
					$no_access=FALSE;
				else
					$no_accss=TRUE;
				}
			}
		else {
			if ($perm->have_perm("admin") && !$perm->have_perm("root"))
				$db->query ("SELECT Institut_id FROM dokumente LEFT JOIN seminare USING (seminar_id) WHERE dokument_id = '".$file_id."' ");
			else
				$db->query ("SELECT seminar_user.user_id, Institut_id FROM dokumente LEFT JOIN seminar_user USING (seminar_id) LEFT JOIN seminare USING (seminar_id) WHERE seminar_user.user_id = '".$user->id."' AND dokument_id = '".$file_id."' ");		
			if (!$db->next_record()) 
				$no_access=TRUE;
			if ($perm->have_perm("admin") && !$perm->have_perm("root")) {
				$db2->query("SELECT institut_id FROM user_inst  WHERE user_inst.user_id = '".$user->id."' AND user_inst.inst_perms = 'admin'");
				while ($db2->next_record())  {
					if($db->f("Institut_id") == $db2->f("institut_id"))
						$admin=TRUE;
					}
				if ($admin)
					$no_access=FALSE;
				else
					$no_accss=TRUE;
				}
			}
		}
	}
	
//Nachricht bei verbotenem Download
if ($no_access) {
	if ($type)
		$add_msg= sprintf(_("%sZur&uuml;ck%s zum Archiv"), "<a href=\"archiv.php?back=TRUE\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;" ;
	else
		$add_msg= sprintf(_("%sZur&uuml;ck%s zum Downloadbereich"), "<a href=\"archiv.php?back=TRUE\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;" ;

	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

	parse_window("error�" . _("Sie haben keine Zugriffsberechtigung f&uuml;r diesen Download!"), "�", _("Download nicht m&ouml;glich"), $add_msg);
	page_close();
	echo "</body>";
	die;
}

//Datei verschicken
$filesize = filesize($path_file);

header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Cache-Control: private");
header("Expires: 0");

header("Content-type: $content_type; name=\"".rawurldecode($file_name)."\"");
header("Content-length: $filesize");
header("Content-disposition: $content_disposition; filename=\"".rawurldecode($file_name)."\"");

readfile($path_file);
TrackAccess ($file_id);

//temporare Datein fuer zippen loeschen
if ($zip) {
	exec ("rm $tmp_file_name");
	exec ("rm $path_file");
	}

// Save data back to database.
page_close();
?>
