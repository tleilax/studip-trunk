<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// institut_browse.php
// 
// 
// Copyright (c) 2002 Andr� Noack <noack@data-quest.de> 
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipRangeTreeView.class.php");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

$view = new DbView();
$the_tree = new StudipRangeTreeView();
$_open_ranges['root'] = true;
if ($_REQUEST['cmd']=="suche"){
	if ($_REQUEST['search_name'] && strlen($_REQUEST['search_name']) > 1){
		$view->params[0] = "%" . $_REQUEST['search_name'] . "%";
		$rs = $view->get_query("view:TREE_SEARCH_ITEM");
		while($rs->next_record()){
			$found_items[] = htmlReady($the_tree->tree->getItemPath($rs->f("item_id")));
			$the_tree->openItem($rs->f("item_id"));
		}
	}
	if ($_REQUEST['search_user'] && strlen($_REQUEST['search_user']) > 1){
		$view->params[0] = "%" . $_REQUEST['search_user'] . "%";
		$rs = $view->get_query("view:TREE_SEARCH_USER");
		while($rs->next_record()){
			$found_items[] = htmlReady($the_tree->tree->getItemPath($rs->f("item_id")));
			$the_tree->openItem($rs->f("item_id"));
		}
	}
	if ($_REQUEST['search_sem'] && strlen($_REQUEST['search_sem']) > 1){
		$view->params[0] = "%" . $_REQUEST['search_sem'] . "%";
		$rs = $view->get_query("view:TREE_SEARCH_SEM");
		while($rs->next_record()){
			$found_items[] = htmlReady($the_tree->tree->getItemPath($rs->f("item_id")));
			$the_tree->openItem($rs->f("item_id"));
		}
	}
	if (count($found_items)){
		$msg = "info�" . _("Gefundene Einrichtungen:"). "<div style=\"font-size:10pt;\">" . join("<br>",$found_items) ."</div>�";
	} else {
		$msg = "info�" . _("Es konnte keine Einrichtung gefunden werden, die Ihrer Suchanfrage entspricht."). "�";
	}
}
?>
<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td class="topic" colspan="2"><img src="pictures/suchen.gif" border="0" align="absbottom"><b>&nbsp;<?=_("Suche nach Einrichtungen")?></b></td>
	</tr>
	<tr>
	<td class="blank" width="100%" align="left" valign="top">
	<?
if ($msg)	{
	echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	parse_msg ($msg,"�","blank",1,false);
	echo "\n</table>";
} else {
	echo "<br><br>";
}
$the_tree->showTree();
	?>
	</td>
	<td class="blank" align = right valign=top>
	<?
$infobox = array(array("kategorie"  => "Information:",
						"eintrag" => array(array("icon" => "pictures/ausruf_small.gif",
												"text"  => _("Sie k&ouml;nnen sich durch den Einrichtungsbaum klicken, oder das Suchformular benutzen"))
										)
						)
				);
$such_form = "<form action=\"$PHP_SELF?cmd=suche\" method=\"post\">" . _("Bitte geben Sie hier Ihre Suchkriterien ein:") . "<br>
			" . _("Name der Einrichtung:") . "<br>
			<input type=\"text\" name=\"search_name\" style=\"width:95%;\"><br>
			" . _("Einrichtung dieses Mitarbeiters:") . "<br>
			<input type=\"text\" name=\"search_user\" style=\"width:95%;\"><br>
			" . _("Einrichtung dieser Veranstaltung:") . "<br>
			<input type=\"text\" name=\"search_sem\" style=\"width:95%;\">
			<div align=\"right\" style=\"width:95%;\"><input type=\"image\" border=\"0\" " . makeButton("suchestarten","src") . tooltip(_("Suche starten")) . " vspace=\"3\" >
			</div></form>
			";
$infobox[1]["kategorie"] = "Suchen";
$infobox[1]["eintrag"][] = array (	"icon" => "pictures/suchen.gif" ,
									"text" => $such_form
								);
print_infobox ($infobox,"pictures/einrichtungen.jpg");
?>
</td></tr>
</td></tr>
</table>
</body>
<?
page_close()
?>

