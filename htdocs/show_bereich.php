<?php
/*
show_bereich.php - Anzeige von Veranstaltungen eines Bereiches oder Institutes
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once "$ABSOLUTE_PATH_STUDIP/lib/classes/SemBrowse.class.php";


// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

if (($SessSemName[1]) && ($SessSemName["class"] == "inst")) {
	include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
}
		
	$sess->register ("show_bereich_data");
	$db=new DB_Seminar;

	if ($id){
		$show_bereich_data["id"]=$id;
		$show_bereich_data['level'] = $level;
	}
	
	if (!$_REQUEST['group_by']){
		$_REQUEST['group_by'] = 0;
	}
	unset($_REQUEST['level']);
	$save_me = $sem_browse_data;
	unset($sem_browse_data);
	$sem_browse_obj = new SemBrowse();
	$sem_browse_obj->sem_browse_data['default_sem'] = "all";
	$sem_browse_obj->sem_number = false;
	$sem_browse_obj->target_url="details.php";	//teilt der nachfolgenden Include mit, wo sie die Leute hinschicken soll
	$sem_browse_obj->target_id="sem_id"; 		//teilt der nachfolgenden Include mit, wie die id die &uuml;bergeben wird, bezeichnet werden soll
	$sem_browse_obj->sem_browse_data['level'] = $show_bereich_data['level'];
	switch ($show_bereich_data['level']) {
		case "sbb": 
			$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
			$bereich_typ = _("Studienbereich");
			$head_text = "&nbsp; " . _("&Uuml;bersicht aller Veranstaltungen eines Studienbereichs");
			$intro_text = sprintf(_("Alle Veranstaltungen, die dem Studienbereich: <br><b>%s</b><br> zugeordnet wurden."),
							htmlReady($the_tree->getShortPath($show_bereich_data["id"])));
			$sem_ids = $the_tree->getSemIds($show_bereich_data["id"],false);
			if (is_array($sem_ids)){
				$sem_browse_obj->sem_browse_data['search_result'] = array_flip($sem_ids);
			} else {
				$sem_browse_obj->sem_browse_data['search_result'] = array();
			}
			$sem_browse_obj->show_result = true;
			$sem_browse_obj->sem_browse_data['sset'] = false;
			$sem_browse_obj->sem_browse_data['start_item_id'] = $show_bereich_data["id"];
			break;
		case "s":
			$bereich_typ=_("Einrichtung");
			$db->query("SELECT Name FROM Institute WHERE Institut_id='".$show_bereich_data["id"]."'");
			$db->next_record();
			$head_text = "&nbsp;" . _("&Uuml;bersicht aller Veranstaltungen einer Einrichtung");
			$intro_text = sprintf(_("Alle Veranstaltungen der Einrichtung <b>%s</b>"),$db->f("Name"));
			$db->query("SELECT seminar_id FROM seminar_inst WHERE Institut_id='".$show_bereich_data["id"]."'");
			$sem_browse_obj->sem_browse_data['search_result'] = array();
			while ($db->next_record()){
				$sem_browse_obj->sem_browse_data['search_result'][$db->f("seminar_id")] = true;
			}
			$sem_browse_obj->show_result = true;
			break;
	}

?>
<body>
<table width="100%" border=0 cellpadding=2 cellspacing=0>
<tr>
	<td class="topic" colspan="2"><b><? echo $head_text ?></td>
</tr>
<tr>
	<td class="blank" valign="top"><br /><blockquote><font size="-1"><? echo $intro_text ?></font></blockquote><br>
<?
$sem_browse_obj->print_result();
?>
</td><td class="blank" width="270" align="right" valign="top">
<?
$goup_by_links = "";
for ($i = 0; $i < count($sem_browse_obj->group_by_fields); ++$i){
	if($sem_browse_data['group_by'] != $i){
		$group_by_links .= "<a href=\"$PHP_SELF?group_by=$i\"><img src=\"pictures/blank.gif\" width=\"10\" height=\"20\" border=\"0\">";
	} else {
		$group_by_links .= "<img src=\"pictures/forumrot.gif\" border=\"0\" align=\"bottom\">";
	}
	$group_by_links .= "&nbsp;" . $sem_browse_obj->group_by_fields[$i]['name'];
	if($sem_browse_data['group_by'] != $i){
		$group_by_links .= "</a>";
	}
	$group_by_links .= "<br>";
}
$infobox[] = 	array(	"kategorie" => _("Anzeige gruppieren:"),
						"eintrag" => array(array(	"icon" => "pictures/blank.gif",
													"text" => $group_by_links))
				);
if (($EXPORT_ENABLE) AND ($show_bereich_data['level'] == "s") AND ($perm->have_perm("tutor")))
{
	include_once($ABSOLUTE_PATH_STUDIP . $PATH_EXPORT . "/export_linking_func.inc.php");
	$infobox[] = 	array(	"kategorie" => _("Daten ausgeben:"),
							"eintrag" => array(array(	"icon" => "pictures/blank.gif",
														"text" => export_link($SessSemName[1], "veranstaltung", $SessSemName[0])))
					);
}
print_infobox ($infobox,"pictures/browse.jpg");
?>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;
	</td>
</tr>
</table>

<?
$sem_browse_data = $save_me;
page_close()
?>
</body>
</html>
