<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_lit_list.php
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
$perm->check("autor");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitListViewAdmin.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitClipBoard.class.php");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

$_attributes['lit_select'] = array('style' => 'font-size:8pt;width:100%');

if (!$_REQUEST['_range_id']){
	$_range_id = $SessSemName[1];
} elseif ($_REQUEST['_range_id'] == "self"){
	$_range_id = $auth->auth['uid'];
} else {
	$_range_id = $_REQUEST['_range_id'];
}
if (!$_range_id){
	$_range_id = $auth->auth['uid'];
}

$_the_treeview =& new StudipLitListViewAdmin($_range_id);
$_the_tree =& $_the_treeview->tree;

//checking rights
if (($_the_tree->range_type == "sem" && !$perm->have_studip_perm("tutor", $_range_id)) ||
	(($_the_tree->range_type == "inst" || $_the_tree->range_type == "fak") && !$perm->have_studip_perm("autor", $_range_id))){
		$perm->perm_invalid(0,0);
		page_close();
		die;
}

$_the_treeview->open_ranges['root'] = true;
$_the_clipboard =& new StudipLitClipBoard();
$_the_treeview->clip_board =& $_the_clipboard;
$_the_treeview->parseCommand();
$_the_clip_form =& $_the_clipboard->getFormObject();


if ( ($lists = $_the_tree->getListIds()) ){
	for ($i = 0; $i < count($lists); ++$i){
		if (isset($_the_treeview->open_items[$lists[$i]])){
			$_the_clip_form->form_fields['clip_cmd']['options'][] 
			= array('name' => my_substr(sprintf(_("In \"%s\" eintragen"), $_the_tree->tree_data[$lists[$i]]['name']),0,50),
			'value' => 'ins_' . $lists[$i]);
		}
	}
}

if ($_the_clip_form->isClicked("clip_ok")){
	$clip_cmd = explode("_",$_the_clip_form->getFormFieldValue("clip_cmd"));
	if ($clip_cmd[0] == "ins" && is_array($_the_clip_form->getFormFieldValue("clip_content"))){
		$inserted = $_the_tree->insertElementBulk($_the_clip_form->getFormFieldValue("clip_content"), $clip_cmd[1]);
		if ($inserted){
			$_the_tree->init();
			$_the_treeview->open_ranges[$clip_cmd[1]] = true;
			$_msg .= "msg�" . sprintf(_("%s Eintr&auml;ge aus ihrer Merkliste wurden in <b>%s</b> eingetragen."),
										$inserted, htmlReady($_the_tree->tree_data[$clip_cmd[1]]['name'])) . "�";
		}
	}
	$_the_clipboard->doClipCmd();
}

$_msg .= $_the_clipboard->msg;
	
?>
<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td class="topic" colspan="2"><b>&nbsp;<?=htmlReady($_the_tree->root_name) . " - " . _("Literaturlisten bearbeiten")?></b></td>
	</tr>
	<td class="blank" width="99%" align="left" valign="top">
	<?
if ($_msg)	{
	echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	parse_msg ($_msg,"�","blank",1,false);
	echo "\n</table>";
} else {
	echo "<br><br>";
}
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td align="center">
<?
$_the_treeview->showTree();
?>
</td></tr>
</table>
</td>
<td class="blank" align="center" valign="top">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="blank" width="270" align="right" valign="top">
<?
$infobox[0] = array ("kategorie" => _("Literaturlisten erstellen / bearbeiten"),
					"eintrag" =>	array(	
									array("icon" => "pictures/blank.gif","text"  =>	_("Hier k�nnen Sie Literaturlisten erstellen / bearbeiten.")),
									)
					);
$infobox[0]["eintrag"][] = array("icon" => "pictures/ausruf_small.gif","text"  => _("Listen blabla") );

$infobox[1] = array ("kategorie" => _("Aktionen:"));
$infobox[1]["eintrag"][] = array("icon" => "pictures/forumrot.gif","text"  => "<a href=\"lit_search.php\">" . _("Literatur suchen") . "</a>" );
$infobox[1]["eintrag"][] = array("icon" => "pictures/forumrot.gif","text"  => "<a href=\"admin_lit_element.php?_range_id=new_entry\">" . _("Neue Literatur anlegen") . "</a>" );

print_infobox ($infobox,"pictures/browse.jpg");

echo $_the_clip_form->getFormStart($_the_treeview->getSelf());
?>
</td>
</tr>
<tr>
	<td class="blank" align="center" valign="top">
	<b><?=_("Merkliste:")?></b>
	<br>
	<?=$_the_clip_form->getFormField("clip_cmd", $_attributes['lit_select'])?>
	<div align="right">
	<?=$_the_clip_form->getFormButton("clip_ok",array('style'=>'vertical-align:middle'))?>
	</div>
	<hr>
	<?=$_the_clip_form->getFormField("clip_content", array_merge(array('size' => $_the_clipboard->getNumElements()), $_attributes['lit_select']))?>
	</td>
</tr>
</table>
<?
echo $_the_clip_form->getFormEnd();
?>
</td>
</tr>
<tr><td class="blank" colspan="2">&nbsp;</td></tr>
</table>
</body>
<?
page_close()
?>
