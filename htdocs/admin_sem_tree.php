<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_sem_tree.php
// 
// 
// Copyright (c) 2003 Andr� Noack <noack@data-quest.de> 
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
if (!$perm->is_fak_admin()){
	$perm->perm_invalid(0,0);
	page_close();
	die;
}
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemTreeViewAdmin.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemSearch.class.php");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");	//Linkleiste fuer admins

$view = new DbView();
$the_tree = new StudipSemTreeViewAdmin($_REQUEST['start_item_id']);
$search_obj = new StudipSemSearch();

if ($search_obj->search_done){
	if ($search_obj->search_result->numRows > 50){
		$_msg = "error�" . _("Es wurden mehr als 50 Veranstaltungen gefunden! Bitte schr&auml;nken sie ihre Suche weiter ein.");
	} elseif ($search_obj->search_result->numRows > 0){
		$_msg = "msg�" .sprintf(_("Es wurden %s Veranstaltungen gefunden, und in ihre Merkliste eingef&uuml;gt"),$search_obj->search_result->numRows);
		if (is_array($_marked_sem) && count($_marked_sem)){
			$_marked_sem = array_merge($_marked_sem,$search_obj->search_result->getDistinctRows("seminar_id"));
		} else {
			$_marked_sem = $search_obj->search_result->getDistinctRows("seminar_id");
		}
	} else {
		$_msg = "info�" . _("Es wurden keine Veranstaltungen die zu ihren Suchkriterien passen gefunden.");
	}
}

if ($_REQUEST['cmd'] == "MarkList"){
	if (is_array($_REQUEST['sem_mark_list'])){
		if ($_REQUEST['mark_list_aktion'] == "del"){
			$count_del = 0;
			for ($i = 0; $i < count($_REQUEST['sem_mark_list']); ++$i){
				if (isset($_marked_sem[$_REQUEST['sem_mark_list'][$i]])){
					++$count_del;
					unset($_marked_sem[$_REQUEST['sem_mark_list'][$i]]);
				}
			}
			$_msg .= "msg�" . sprintf(_("%s Veranstaltung(en) wurde(n) aus der Merkliste entfernt."),$count_del);
		} else {
			$tmp = explode("_",$_REQUEST['mark_list_aktion']);
			$item_ids[0] = $tmp[1];
			if ($item_ids[0] == "all"){
				$item_ids = array();
				foreach ($_open_items as $key => $value){
					if($key != 'root')
						$item_ids[] = $key;
				}
			}
			for ($i = 0; $i < count($item_ids); ++$i){
				$count_ins = 0;
				for ($j = 0; $j < count($_REQUEST['sem_mark_list']); ++$j){
					if ($_REQUEST['sem_mark_list'][$j]){
						$count_ins += StudipSemTree::InsertSemEntry($item_ids[$i], $_REQUEST['sem_mark_list'][$j]);
					}
				}
				$_msg .= sprintf(_("%s Veranstaltung(en) in <b>" .htmlReady($the_tree->tree->tree_data[$item_ids[$i]]['name']) . "</b> eingetragen.<br>"), $count_ins);
			}
			if ($_msg)
				$_msg = "msg�" . $_msg;
			$the_tree->tree->init();
		}
	}
}
if ($the_tree->mode == "MoveItem"){
	if ($_msg){
		$_msg .= "�";
	}
	$_msg .= "info�" . sprintf(_("Der Verschiebemodus ist aktiviert. Bitte w&auml;hlen sie ein Einf�gesymbol %s aus,"
								." um das Element <b>%s</b> zu verschieben.%s"),
								"<img src=\"pictures/move.gif\" border=\"0\" " .tooltip(_("Einf�gesymbol")) . ">",
								htmlReady($the_tree->tree->tree_data[$the_tree->move_item_id]['name']),
								"<div align=\"right\"><a href=\"" . $the_tree->getSelf("cmd=Cancel&item_id=$the_tree->move_item_id") . "\">"
								. "<img " .makeButton("abbrechen","src") . tooltip(_("Verschieben abbrechen"))
								. " border=\"0\" align=\"top\"></a></div>");
}
		
	
?>
<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td class="topic" colspan="2"><b>&nbsp;<?=$UNI_NAME." - "._("Veranstaltungshierarchie bearbeiten")?></b></td>
	</tr>
	<tr>
	<td class="blank" width="75%" align="left" valign="top">
	<?
if ($_msg)	{
	echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	parse_msg ($_msg,"�","blank",1,false);
	echo "\n</table>";
} else {
	echo "<br><br>";
}
$the_tree->showSemTree();
	?>
	</td>
	<td class="blank" align="left" valign="top">
	<div>
	<b><?=_("Veranstaltungssuche:")?></b>
	</div>
	<?
	$search_obj->attributes_default = array('style' => 'width:100%;font-size:10pt;');
	$search_obj->search_fields['type']['class'] = 1;
	$search_obj->search_fields['type']['size'] = 30 ;
	echo $search_obj->getFormStart($the_tree->getSelf());
	?>
	<table border="0" width="100%" style="font-size:10pt">
	<tr>
	<td ><span style="font-size:10pt"><?=_("Titel:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("title")?></td>
	</tr>
	<tr>
	<td><span style="font-size:10pt"><?=_("Untertitel:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("sub_title")?></td>
	</tr>
	<tr>
	<td><span style="font-size:10pt"><?=_("Kommentar:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("comment")?></td>
	</tr>
	<tr>
	<td><span style="font-size:10pt"><?=_("Dozent:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("lecturer")?></td>
	</tr>
	<tr>
	<td><span style="font-size:10pt"><?=_("Bereich:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("scope")?></td>
	</tr>
	<tr>
	<td><span style="font-size:10pt"><?=_("Kombination:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("combination",array('style' => 'width:*;font-size:10pt;'))?></td>
	</tr>
	<tr>
	<td colspan="2"><hr></td>
	</tr>
	<tr>
	<td><span style="font-size:10pt"><?=_("Typ:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("type",array('style' => 'width:*;font-size:10pt;'))?></td>
	</tr>
	<tr>
	<td><span style="font-size:10pt"><?=_("Semester:")?></span></td><td style="font-size:10pt"><?=$search_obj->getSearchField("sem",array('style' => 'width:*;font-size:10pt;'))?></td>
	</tr>
	<tr>
	<td align="right" colspan="2"><?=$search_obj->getSearchButton();?>&nbsp;&nbsp;<?=$search_obj->getNewSearchButton();?></td>
	</tr>
	</table>
	<?=$search_obj->getFormEnd();?>
	<p>
	<b><?=_("Merkliste:")?></b>
	</p>
	<form action="<?=$the_tree->getSelf("cmd=MarkList")?>" method="post">
	<select multiple size="20" name="sem_mark_list[]" style="font-size:8pt;width:100%">
	<?
	$cols = 50;
	if (is_array($_marked_sem)){
		$view->params[0] = array_keys($_marked_sem);
		$entries = new DbSnapshot($view->get_query("view:SEMINAR_GET_SEMDATA"));
		$sem_data = $entries->getGroupedResult("seminar_id");
		$sem_number = -1;
		foreach($sem_data as $seminar_id => $data){
			if (key($data['sem_number']) != $sem_number){
				$sem_number = key($data['sem_number']);
				echo "\n<option value=\"0\" style=\"font-weight:bold;color:red;\">&nbsp;</option>";
				echo "\n<option value=\"0\" style=\"font-weight:bold;color:red;\">" . $the_tree->tree->sem_dates[$sem_number]['name'] . ":</option>";
				echo "\n<option value=\"0\" style=\"font-weight:bold;color:red;\">" . str_repeat("�",floor($cols * .8)) . "</option>";
			}
			$line = htmlReady(my_substr(key($data["Name"]),0,$cols));
			$tooltip = key($data["Name"]) . " (" . join(",",array_keys($data["doz_name"])) . ")";
			echo "\n<option value=\"$seminar_id\" " . tooltip($tooltip,false) . ">$line</option>";
		}
	}
	?>
	</select><br>&nbsp;<br><select name="mark_list_aktion" style="font-size:8pt;width:100%;">
	<?
	if (is_array($_open_items) && count($_open_items) && !(count($_open_items) == 1 && $_open_items['root'])){
		echo "\n<option  value=\"insert_all\">" . _("In alle ge&ouml;ffneten Bereiche eintragen") . "</option>";
		foreach ($_open_items as $item_id => $value){
			echo "\n<option value=\"insert_{$item_id}\">" 
				. sprintf(_("In \"%s\" eintragen"),htmlReady(my_substr($the_tree->tree->tree_data[$item_id]['name'],0,floor($cols * .8)))) . "</option>";
		}
	}
	?>
	<option value="del">Aus Merkliste l&ouml;schen</option>
	</select>
	<div align="right">
	<input border="0" type="image" <?=makeButton("ok","src") . tooltip(_("Gew�hlte Aktion starten"))?> >
	</div>
	</form>
</td></tr>
</td></tr>
</table>
</body>
<?
page_close()
?>
