<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export_xml.inc.php
// XML-functions for the Stud.IP database
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

if (($o_mode != "direct") AND ($o_mode != "passthrough")) 
	$perm->check("tutor");

$export_pagename = _("Datenexport");
require_once ("$ABSOLUTE_PATH_STUDIP/config_tools_semester.inc.php");   // Aktuelles Semester
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/RangeTreeObject.class.php");   // Uni-Baum-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xml_vars.inc.php");   // XML-Variablen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xml_func.inc.php");   // XML-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_studipdata_func.inc.php");   // Studip-Export-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");   // Datumsfunktionen

function CheckParamXML()
{
global $range_id, $ex_type, $xml_file_id, $o_mode, $export_error, $export_error_num, $export_o_modes, $export_ex_types;

	if ((($range_id != "") OR ($xml_file_id != "")) AND (in_array($o_mode, $export_o_modes) AND (in_array($ex_type, $export_ex_types))))
		return true;
	$export_error .= "<b>" . _("Unzul�ssiger Seitenaufruf!") . "</b><br>";
	$export_error_num++;
	return false;
}


if (!CheckParamXML()) 
{
	$infobox = array(			
	array ("kategorie"  => _("Information:"),
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => _("Die Parametern, mit denen diese Seite aufgerufen wurde, sind fehlerhaft.")
							 )
						)
		)
	);
}


if ($o_mode != "direct")
{
	$xml_file_id = md5(uniqid(rand())) . ".xml";
	$xml_file = fopen($TMP_PATH."/" . $xml_file_id, "w");
}



export_range( $range_id );



if ($o_mode != "direct")
{
	fclose($xml_file);
}

if (($o_mode == "file") OR ($o_mode == "choose"))
{

	if ($object_counter<1)
	{
		$link = "<a href=\"$PHP_SELF?range_id=$range_id&ex_type=$ex_type&ex_sem=$ex_sem&o_mode=start\">";
		$xml_export_text = _("Es wurden keine Daten gefunden!");
		$export_error = _("Es wurden keine Daten gefunden! Die &uuml;bergebene ID ist mit keinen Veranstaltungs- / Personendaten verbunden.");
//		$export_pagecontent .= sprintf(_("%s Hier %s gelangen Sie zur&uuml;ck zur Startseite des Exportmoduls. "), $link, "</a>");
		$export_pagecontent .= "<br><br><br><center>" . $link . makeButton("zurueck", "img") . "</a></center>";
		$export_error_num ++;
//		echo "</td></tr>";
//		die("</table></td></tr></table></body>");

	}
	else
	{
		$xml_export_text = _("Die Daten wurden erfolgreich exportiert.");
		if ($object_counter == 1)
			$export_msg = sprintf(_("%s Objekt wurde verarbeitet.") . " ", $object_counter);
		else
			$export_msg = sprintf(_("%s Objekte wurden verarbeitet.") . " ", $object_counter);

//		$export_info = _("Die Daten wurden in eine XML-Datei exportiert. <br>Wenn Sie die Datei in ein anderes Format konvertieren wollen, klicken Sie auf weiter.<br>Um die Datei herunterzuladen, klicken Sie auf den Dateinamen.");

//		$export_weiter_button = "<br><br><center><a href=\"" . $PHP_SELF . "?xml_file_id=" . $xml_file_id . "&ex_type=" . $ex_type . "&o_mode=choose\">" . makeButton("weiter") . "</a></center>";
		$export_weiter_button = "<br><br><center><input type=\"IMAGE\" " . makeButton("zurueck", "src") . " value=\"" . _("Zur&uuml;ck") . "\" name=\"back\">&nbsp;</center>";
		$xml_printimage = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\"><img src=\"./pictures/" . $export_icon["xml"] . "\" border=0></a>";
		$xml_printlink = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\" class=\"tree\">" . $xml_filename . "</a>";
		$xml_printdesc = _("XML-Daten");
		$xml_printcontent = _("In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags k�nnen mit einem XSLT-Script verarbeitet werden.") . "<br>";	
	}
	
	$infobox = array	(			
	array ("kategorie"  => _("Information:"),
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => $xml_export_text
							 )
						)
		)
	);
	if ($object_counter > 0)
	{
		$link = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">";
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/icon-disc.gif" ,
										"text"  => sprintf(_("Um die XML-Datei jetzt herunterzuladen klicken Sie %s hier %s."), $link, "</a>")
									);
//			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/forumgrau.gif" ,
//										"text"  => _("Wenn Sie die Daten in ein anderes Format konvertieren wollen, klicken Sie auf 'weiter'.")
//									);
	}
	
}
?>
