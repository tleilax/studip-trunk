<?php
/**
* my_archiv.php
* 
* overview for achived Veranstaltungen
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	views
* @module		my_archiv.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// my_archiv.php
// Anzeigeseite fuer persoenliche, archivierte Veranstaltungen
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


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");			 // htmlReady fuer die Veranstaltungsnamen
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");			 // Semester-Namen fuer Admins

$cssSw = new cssClassSwitcher;									// Klasse f�r Zebra-Design
$cssSw->enableHover();
$db = new DB_Seminar;

// we are defintely not in an lexture or institute
closeObject();
$links_admin_data='';	 //Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

echo "\n" . $cssSw->GetHoverJSFunction() . "\n";

if (!$perm->have_perm("root"))
	include ("$ABSOLUTE_PATH_STUDIP/links_seminare.inc.php");	   //hier wird die Navigation nachgeladen
	 
if (!isset($sortby))
	$sortby="name";
if ($sortby == "count")
	$sortby = "count DESC";
	
$db->query ("SELECT archiv.name, archiv.seminar_id, archiv_user.status, archiv.semester, archiv.archiv_file_id, archiv.forumdump FROM archiv_user LEFT JOIN archiv  USING (seminar_id) WHERE archiv_user.user_id = '$user->id' GROUP BY seminar_id ORDER BY start_time DESC, $sortby");
$num_my_sem=$db->num_rows();
if (!$num_my_sem)
	$meldung.= "info�" . _("Es befinden sich zur Zeit keine Veranstaltungen im Archiv, an denen Sie teilgenommen haben.");

 ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="topic" colspan="2">
			<img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b><? echo(_("Meine archivierten Veranstaltungen")) ?></b>
		</td>
	</tr>
<?

if ($num_my_sem) {
	?>
	<tr valign="top">
		<td class="blank" colspan="2">&nbsp;
		</td>
	</tr>
	<tr valign="top">
		<td valign="top" class="blank" align="center">
			<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center" valign="top" class="blank">
				<tr align="center" valign="top">
					<th width="1%"></th>
					<th width="82%" align="left"><a href="<? echo $PHP_SELF ?>?sortby=name&view=<? echo $view ?>"><? echo(_("Name")) ?></a></th>
					<th width="7%"><b><? echo(_("Inhalt")) ?></b></th>
					<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=status&view=<? echo $view ?>">&nbsp;<? echo(_("Status")) ?>&nbsp;</a></th>
				</tr>
	<?
	while ($db->next_record()) {
		$cssSw->switchClass();
		if ($last_sem != $db->f("semester")) {
			$cssSw->resetClass();
			$cssSw->switchClass();
			print "<tr><td class=\"steelkante\" colspan=\"4\">&nbsp; <b>".$db->f("semester")."</b></td></tr>";
			}
		echo "<tr ".$cssSw->getHover()." >";
		echo "<td class=\"".$cssSw->getClass()."\">&nbsp; </td>";
		// name-field
		echo "<td class=\"".$cssSw->getClass()."\" ><a href=\"archiv.php?dump_id=".$db->f('seminar_id')."\" target=\"new\">";
		echo "<font size=-1>".htmlReady($db->f("name"))."</font>";
		print ("</a></td>");
		// content-field
		echo "<td class=\"".$cssSw->getClass()."\" align=\"left\" nowrap>";
		// postings-field
		if ($db->f("forumdump"))
			echo "<a href=\"archiv.php?forum_dump_id=".$db->f('seminar_id')."\" target=\"blank\">&nbsp; <img src=\"pictures/icon-posting.gif\"border=0 ".tooltip(_("Beitr&auml;ge des Forums der Veranstaltung"))."></a>";
		else
			echo "&nbsp; <img src='pictures/icon-leer.gif' border=0>";
		 //documents-field
 		$file_name=rawurlencode(_("Dateisammlung") . " ".substr($db->f("name"),0,200).".zip");
		if ($db->f("archiv_file_id"))
			echo "<a href=\"sendfile.php?type=1&file_id=".$db->f("archiv_file_id")."&file_name=".$file_name."\">&nbsp; <img src=\"pictures/icon-disc.gif\" border=0 ".tooltip(_("Dateisammlung der Veranstaltung herunterladen"))."></a>";
		else
			echo "&nbsp; <img src='pictures/icon-leer.gif' border=0>";
		echo "</td>";
		//status-field
		echo "<td class=\"".$cssSw->getClass()."\"  align=\"center\" nowrap><font size=-1>". $db->f("status")."&nbsp;</font></td>";
		$last_sem=$db->f("semester");
	}
	echo "</table><br><br>";

} else {  // es sind keine Veranstaltungen abboniert
 
 ?>
 <tr>
 <tr>
 	<td class="blank" colspan="2">&nbsp; 
 	</td>
 </tr>
	 <td valign="top" class="blank">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
	<?
	if ($meldung)	{
		parse_msg($meldung);
	}?>
		</table>
<?			
}

//Info-field on the right side
?>

</td>
<td class="blank" width="270" align="right" valign="top">
<?

// Berechnung der uebrigen Seminare

$db->query("SELECT count(*) as count  FROM archiv");
$db->next_record(); 
$anzahltext = sprintf(_("Es befinden sich zur Zeit %s Veranstaltungen im Archiv."), ($db->f("count")));


// View for Teachers
$infobox = array	(	
	array  ("kategorie"  => _("Information:"),
		"eintrag" => array	(	
			array (	"icon" => "pictures/ausruf_small.gif",
							"text"  => $anzahltext
			)
		)
	),
	array  ("kategorie" => _("Aktionen:"),
		"eintrag" => array	(	
			array	 (	"icon" => "pictures/suchen.gif",
								"text"  => sprintf(_("Um Informationen &uuml;ber andere archivierte Veranstaltungen anzuzeigen nutzen Sie die <br />%sSuche im Archiv</a>"), "<a href=\"archiv.php\">")
			)
		)
	)
);

// print the info_box

print_infobox ($infobox,"pictures/folders.jpg");

?>

     	</td>
    </tr>
    <tr>
    	<td class="blank" colspan="2">&nbsp; 
    	</td>
    </tr>
</table>
</body>
</html>
<?
  // Save data back to database.
ob_end_flush(); //Outputbuffering beenden
page_close();
  ?>
