<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// lit_search.php
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
// $Id$
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitSearch.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitClipBoard.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitCatElement.class.php");

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("autor");


include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

$_attributes['lit_select'] = array('style' => 'font-size:8pt;width:100%');
$_attributes['text'] = array('style' => 'width:90%');
$_attributes['radio'] = array('style' => 'font-size:8pt;vertical-align:bottom;');
$_attributes['button'] = array('style' => 'vertical-align:middle;');

$_the_search =& new StudipLitSearch();
$_the_clipboard =& StudipLitClipBoard::GetInstance();
$_the_clip_form =& $_the_clipboard->getFormObject();

if ($_REQUEST['change_start_result']){
	$_the_search->start_result = $_REQUEST['change_start_result'];
}

if ($_the_clip_form->isClicked("clip_ok")){
	$_the_clipboard->doClipCmd();
}

if ($_the_search->outer_form->isClicked("search") 
	|| ($_the_search->outer_form->isSended() 
	&& !$_the_search->outer_form->isClicked("reset")
	&& !$_the_search->outer_form->isClicked("change")
	&& !$_the_search->outer_form->isClicked("search_add")
	&& !$_the_search->outer_form->isClicked("search_sub")
	&& !$_the_search->outer_form->isChanged("search_plugin") //scheiss IE
	)){
	$hits = $_the_search->doSearch();
	if(!$_the_search->search_plugin->getNumError()){
		$_msg .= "msg�" . sprintf(_("Ihre Suche ergab %s Treffer."), $_the_search->getNumHits()) . "�";
	}
	$_the_search->start_result = 1;
}

if ($_REQUEST['cmd'] == "add_to_clipboard"){
	$catalog_id = $_REQUEST['catalog_id'];
	if ($catalog_id{0} == "_"){
		$parts = explode("__", $catalog_id);
		if ( ($fields = $GLOBALS[$parts[0]][$parts[1]]) ){
			$cat_element = new StudipLitCatElement();
			$cat_element->setValues($fields);
			$cat_element->setValue("catalog_id", "new_entry");
			$cat_element->setValue("user_id", "studip");
			if ( ($existing_element = $cat_element->checkElement()) ){
				$cat_element->setValue('catalog_id', $existing_element);
			}
			$cat_element->insertData();
			$catalog_id = $cat_element->getValue("catalog_id");
			$GLOBALS[$parts[0]][$parts[1]]['catalog_id'] = $catalog_id;
			unset($cat_element);
		}
	}
	$_the_clipboard->insertElement($catalog_id);
}

$_msg .= $_the_clipboard->msg;
$_msg .= $_the_search->search_plugin->getError("msg");

?>
<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td class="topic" colspan="2"><img src="./pictures/icon-lit.gif" border="0" /><b>&nbsp;<?=_("Literatur suchen")?></b></td>
	</tr>
	<tr>
	<td class="blank" width="99%" align="left" valign="top">
	<?
if ($_msg)	{
	echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	parse_msg ($_msg,"�","blank",1,false);
	echo "\n</table>";
} else {
	echo "<br><br>";
}
$class_changer = new CssClassSwitcher();
$_attributes['search_plugin'] = $_attributes['text'];
$_attributes['search_plugin']['onChange'] = 'document.' . $_the_search->outer_form->form_name . '.submit()';
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td align="center">
<table width="99%" border="0" cellpadding="2" cellspacing="0" style="font-size:10pt">
<tr>
<?=$_the_search->outer_form->getFormStart();?>
<td colspan="3" class="steel2" align="center">&nbsp;
<?=$_the_search->outer_form->getFormButton('search',$_attributes['button']);?>
&nbsp;
<?=$_the_search->outer_form->getFormButton('reset',$_attributes['button']);?>
</td></tr>
<tr><td <?=$class_changer->getFullClass()?> width="30%">
<?=$_the_search->outer_form->getFormFieldCaption('search_plugin') ;?>
</td><td <?=$class_changer->getFullClass()?> width="40%" align="right">
<?=$_the_search->outer_form->getFormField('search_plugin',$_attributes['search_plugin']). $_the_search->outer_form->getFormFieldInfo('search_plugin',$_attributes['button']);?>
</td><td <?=$class_changer->getFullClass()?> width="30%" align="center">
<?=$_the_search->outer_form->getFormButton('change');?>
</td>
</tr>
<?
$class_changer->switchClass();
echo "<tr><td " . $class_changer->getFullClass() ." colspan=\"3\" align=\"center\"><img src=\"pictures/border.jpg\" width=\"99%\" border=\"0\"></td></tr>";
for ($i = 0 ; $i < $_the_search->term_count; ++$i){
	if ($i > 0){
		echo "<tr><td " . $class_changer->getFullClass() ." width=\"30%\">";
		echo $_the_search->inner_form->getFormFieldCaption("search_operator_" . $i);
		echo "</td><td " . $class_changer->getFullClass() ." width=\"40%\" align=\"right\">";
		echo $_the_search->inner_form->getFormField("search_operator_" . $i, $_attributes['radio']);
		echo "&nbsp;";
		echo $_the_search->inner_form->getFormFieldInfo("search_operator_" . $i);
		echo "</td><td " . $class_changer->getFullClass() ." width=\"30%\">&nbsp;</td></tr>";
	}
	echo "<tr><td " . $class_changer->getFullClass() ." width=\"30%\">";
	echo $_the_search->inner_form->getFormFieldCaption("search_field_" . $i);
	echo "</td><td " . $class_changer->getFullClass() ." width=\"40%\" align=\"right\">";
	echo $_the_search->inner_form->getFormField("search_field_" . $i, $_attributes['text']);
	echo $_the_search->inner_form->getFormFieldInfo("search_field_" . $i);
	echo "</td><td " . $class_changer->getFullClass() ." width=\"30%\">&nbsp;</td></tr>";
	echo "<tr><td " . $class_changer->getFullClass() ." width=\"30%\">";
	echo $_the_search->inner_form->getFormFieldCaption("search_truncate_" . $i);
	echo "</td><td " . $class_changer->getFullClass() ." width=\"40%\" align=\"right\">";
	echo $_the_search->inner_form->getFormField("search_truncate_" . $i, $_attributes['text']);
	echo $_the_search->inner_form->getFormFieldInfo("search_truncate_" . $i);
	echo "</td><td " . $class_changer->getFullClass() ." width=\"30%\">&nbsp;</td></tr>";
	echo "<tr><td " . $class_changer->getFullClass() ." width=\"30%\">";
	echo $_the_search->inner_form->getFormFieldCaption("search_term_" . $i);
	echo "</td><td " . $class_changer->getFullClass() ." width=\"40%\" align=\"right\">";
	echo $_the_search->inner_form->getFormField("search_term_" . $i, $_attributes['text']);
	echo $_the_search->inner_form->getFormFieldInfo("search_term_" . $i);
	echo "</td><td " . $class_changer->getFullClass() ." width=\"30%\" align=\"center\">";
	if ($i == $_the_search->term_count - 1){
		echo $_the_search->outer_form->getFormButton('search_add');
		if ($_the_search->term_count > 1){
			echo "&nbsp;" . $_the_search->outer_form->getFormButton('search_sub');
		}
	} else {
		echo "&nbsp;";
		$class_changer->switchClass();
	}
	echo "</td></tr>";
}
echo "<tr><td " . $class_changer->getFullClass() ." colspan=\"3\" align=\"center\"><img src=\"pictures/border.jpg\" width=\"99%\" border=\"0\"></td></tr>";

?>

<tr>
<td colspan="3" class="steel2" align="center">&nbsp;
<?=$_the_search->outer_form->getFormButton('search',$_attributes['button']);?>
&nbsp;
<?=$_the_search->outer_form->getFormButton('reset',$_attributes['button']);?>
</td></tr>
</table>
<?=$_the_search->outer_form->getFormEnd();?>
&nbsp;<br>
<?
if (($num_hits = $_the_search->getNumHits())){
	if ($_the_search->start_result < 1 || $_the_search->start_result > $num_hits){
		$_the_search->start_result = 1;
	}
	$end_result = (($_the_search->start_result + 5 > $num_hits) ? $num_hits : $_the_search->start_result + 4);
?>
<table width="99%" border="0" cellpadding="2" cellspacing="0" style="font-size:10pt">
<tr>
<td class="steel2" align="left">
<?printf(_("%s Treffer in ihrem Suchergebnis."), $num_hits);?>
</td><td class="steel2" align="right">
<?
echo _("Anzeige: ");
if ($_the_search->start_result > 1) {
	echo "<a href=\"$PHP_SELF?change_start_result=" . ($_the_search->start_result - 5) . "\"><img src=\"pictures/move_left.gif\" hspace=\"3\" border=\"0\"></a>";
} else {
	echo "<img src=\"pictures/forumleer.gif\" width=\"17\" height=\"18\" border=\"0\">";
}
echo $_the_search->start_result . " - " . $end_result;
if ($_the_search->start_result + 4 < $num_hits) {
	echo "<a href=\"$PHP_SELF?change_start_result=" . ($_the_search->start_result + 5) . "\"><img src=\"pictures/move_right.gif\" hspace=\"3\" border=\"0\"></a>";
} else {
	echo "<img src=\"pictures/forumleer.gif\" width=\"17\" height=\"18\" border=\"0\">";
}
?>
</td></tr>
<tr><td colspan="2">
<?
for ($i = $_the_search->start_result; $i <= $end_result; ++$i){
	$element = $_the_search->getSearchResult($i);
	if ($element){
		echo "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>";
		$addon = ($_the_clipboard->isInClipboard($element->getValue("catalog_id"))) ? "<img src=\"pictures/forum_fav.gif\" hspace=\"4\"  border=\"0\" " . 
				tooltip(_("Dieser Eintrag ist bereits in ihrer Merkliste")) . ">" : "<a href=\"". $PHP_SELF . "?cmd=add_to_clipboard&catalog_id=" . $element->getValue("catalog_id") . 
				"\"><img src=\"pictures/forum_fav2.gif\" hspace=\"4\"  border=\"0\" " . 
				tooltip(_("Eintrag in Merkliste aufnehmen")) . "></a>";
		printhead(0,0,false,"open",true,"<img src=\"pictures/cont_lit.gif\" border=\"0\" align=\"bottom\">",htmlReady(my_substr($element->getShortName(),0,85)),$addon);
		echo "\n</tr></table>";
		$content = "";
		$edit = "<a href=\"admin_lit_element.php?_catalog_id=" . $element->getValue("catalog_id") . "\">"
				. "<img " .makeButton("details","src") . tooltip(_("Detailansicht dieses Eintrages ansehen."))
				. " border=\"0\"></a>&nbsp;";
		if (!$_the_clipboard->isInClipboard($element->getValue("catalog_id"))){
			$edit .= "&nbsp;<a href=\"". $PHP_SELF . "?cmd=add_to_clipboard&catalog_id=" . $element->getValue("catalog_id")
				. "\"><img " . makeButton("merkliste","src") . " border=\"0\" " . 
				tooltip(_("Eintrag in Merkliste aufnehmen")) . "></a>";
		}
		echo "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
		$content .= "<b>" . _("Titel:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("dc_title"),true,true) . "<br>";
		$content .= "<b>" . _("Autor; weitere Beteiligte:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("authors"),true,true) . "<br>";
		$content .= "<b>" . _("Erschienen:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("published"),true,true) . "<br>";
		$content .= "<b>" . _("Identifikation:") ."</b>&nbsp;&nbsp;" . fixLinks(htmlReady($element->getValue("dc_identifier"),true,true)) . "<br>";
		$content .= "<b>" . _("Schlagw&ouml;rter:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("dc_subject"),true,true) . "<br>";
		if ($element->getValue("lit_plugin") != "Studip"){
			$content .= "<b>" . _("Externer Link:") ."</b>&nbsp;&nbsp;";
			if (($link = $element->getValue("external_link"))){
				$content.= formatReady(" [" . $element->getValue("lit_plugin"). "]" . $link);
			} else {
				$content .= _("(Kein externer Link vorhanden.)");
			}
			$content .= "<br>";
		}
		printcontent(0,0,$content,$edit);
		echo "\n</table>";
	}
}
?>
</td></tr>
<tr>
<td class="steel2" align="left">
<?printf(_("%s Treffer in ihrem Suchergebnis."), $num_hits);?>
</td><td class="steel2" align="right">
<?
echo _("Anzeige: ");
if ($_the_search->start_result > 1) {
	echo "<a href=\"$PHP_SELF?change_start_result=" . ($_the_search->start_result - 5) . "\"><img src=\"pictures/move_left.gif\" hspace=\"3\" border=\"0\"></a>";
} else {
	echo "<img src=\"pictures/forumleer.gif\" width=\"17\" height=\"18\" border=\"0\">";
}
echo $_the_search->start_result . " - " . $end_result;
if ($_the_search->start_result + 4 < $num_hits) {
	echo "<a href=\"$PHP_SELF?change_start_result=" . ($_the_search->start_result + 5) . "\"><img src=\"pictures/move_right.gif\" hspace=\"3\" border=\"0\"></a>";
} else {
	echo "<img src=\"pictures/forumleer.gif\" width=\"17\" height=\"18\" border=\"0\">";
}
?>
</td></tr>
</table>

<?
}
?>
</td></tr>
</table>
</td>
<td class="blank" align="center" valign="top">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="blank" width="270" align="right" valign="top">
<?
$infobox[0] = array ("kategorie" => _("Information:"),
					"eintrag" =>	array(	
									array("icon" => "pictures/blank.gif","text"  =>	_("Hier k�nnen Sie in verschiedenen Katalogen nach Literatur suchen.")),
									array("icon" => "pictures/blank.gif","text"  =>	"<b>" . _("Ausgew&auml;hlter Katalog:") . "</b><br>" . $_the_search->search_plugin->description),
									)
					);
if ($num_hits){
	$infobox[0]["eintrag"][] = array("icon" => "pictures/ausruf_small.gif","text"  => sprintf(_("Suchergebnis: %s Treffer"),$num_hits) );
} else {
	$infobox[0]["eintrag"][] = array("icon" => "pictures/ausruf_small.gif","text"  => _("Es liegt kein Suchergebnis vor.") );
}

$infobox[1] = array ("kategorie" => _("Aktionen:"));
$infobox[1]["eintrag"][] = array("icon" => "pictures/link_intern.gif","text"  => "<a href=\"admin_lit_list.php\">" . _("Literaturlisten bearbeiten") . "</a>" );
$infobox[1]["eintrag"][] = array("icon" => "pictures/link_intern.gif","text"  => "<a href=\"admin_lit_element.php?_range_id=new_entry\">" . _("Neue Literatur anlegen") . "</a>" );

print_infobox ($infobox,"pictures/browse.jpg");

?>
</td>
</tr>
<?=$_the_clip_form->getFormStart();?>
<tr>
	<td class="blank" align="center" valign="top">
	<b><?=_("Merkliste:")?></b>
	<br>
	<?=$_the_clip_form->getFormField("clip_content", array_merge(array('size' => $_the_clipboard->getNumElements()), $_attributes['lit_select']))?>
	<div align="center" style="background-image:url(pictures/border.jpg);background-repeat:repeat-y;margin:3px;"><img src="pictures/blank.gif" height="2" border="0"></div>
	<?=$_the_clip_form->getFormField("clip_cmd", $_attributes['lit_select'])?>
	<div align="center">
	<?=$_the_clip_form->getFormButton("clip_ok",array('style'=>'vertical-align:middle;margin:3px;'))?>
	</div>
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
