<?php
# Lifter002: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemTreeViewSimple.class.php
// Class to print out the seminar tree
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
require_once("lib/classes/StudipSemTree.class.php");
require_once("lib/classes/TreeView.class.php");
require_once("config.inc.php");

/**
* class to print out the seminar tree
*
* This class prints out a html representation a part of the tree
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package
*/
class StudipSemTreeViewSimple {


	var $tree;
	var $show_entries;

	/**
	* constructor
	*
	* @access public
	*/
	function StudipSemTreeViewSimple($start_item_id = "root", $sem_number = false, $sem_status, $visible_only = false){
		$this->start_item_id = ($start_item_id) ? $start_item_id : "root";
		$this->root_content = $GLOBALS['UNI_INFO'];
		$args = null;
		if ($sem_number !== false){
			$args['sem_number'] = $sem_number;
		}
		if ($sem_status !== false){
			$args['sem_status'] =  $sem_status;
		}
		$args['visible_only'] = $visible_only;
		$this->tree =& TreeAbstract::GetInstance("StudipSemTree",$args);
		$this->tree->enable_lonely_sem = false;
		if (!$this->tree->tree_data[$this->start_item_id]){
			$this->start_item_id = "root";
		}
	}

	function showSemTree(){
		echo "\n<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		echo "\n<tr><td class=\"steelgraulight\" align=\"left\" valign=\"top\" style=\"font-size:10pt;\">"
			. "<div style=\"font-size:10pt;margin-left:10px\"><b>" . _("Studienbereiche:"). "</b><br>". $this->getSemPath();
		echo "</div></td>";
		echo "<td nowrap class=\"steelgraulight\" align=\"right\" valign=\"bottom\" style=\"font-size:10pt;\">";
		if ($this->start_item_id != "root"){
			echo "\n<a href=\"" .$this->getSelf("start_item_id={$this->tree->tree_data[$this->start_item_id]['parent_id']}", false) . "\">
			<img src=\"".$GLOBALS['ASSETS_URL']."images/move_left.gif\" border=\"0\" align=\"bottom\" hspace=\"3\">" . _("eine Ebene zur&uuml;ck") . "</a>";
		} else {
			echo "&nbsp;";
		}
		echo "</td></tr>";
		echo "\n<tr><td class=\"steel1\" colspan=\"2\" align=\"center\" valign=\"center\">";
		$this->showKids($this->start_item_id);
		echo "\n</td></tr><tr><td class=\"steelgraulight\" colspan=\"2\" align=\"left\" valign=\"center\">";
		$this->showContent($this->start_item_id);
		echo "\n</td></tr></table>";
	}

	function showKids($item_id){
		$num_kids = $this->tree->getNumKids($item_id);
		$kids = $this->tree->getKids($item_id);
		echo "\n<table width=\"95%\" border=\"0\" cellpadding=\"0\" cellspacing=\"10\"><tr>\n<td class=\"steel1\" width=\"50%\" align=\"left\" valign=\"top\">";
		for ($i = 0; $i < $num_kids; ++$i){
			$num_entries = $this->tree->getNumEntries($kids[$i],true);
			echo "<b><a " . tooltip(sprintf(_("%s Eintr�ge in allen Unterebenen vorhanden"), $num_entries)) . " href=\"" .$this->getSelf("start_item_id={$kids[$i]}", false) . "\">";
			echo "<span style=\"font-size:10pt;\">" . htmlReady($this->tree->tree_data[$kids[$i]]['name']);
			echo "&nbsp;($num_entries)</span>";
			echo "</a></b>";
			if ($i == ceil($num_kids / 2)-1){
				echo "</td>\n<td class=\"steel1\" align=\"left\" valign=\"top\">";
			} else {
				echo "<br><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\"><br>";
			}
		}
		if (!$num_kids){
			echo "<p style=\"font-size:10pt\"><b>";
			echo _("Auf dieser Ebene existieren keine weiteren Unterebenen.");
			echo "</b></p>";
		}

		echo "</td></tr></table>";
	}

	function getTooltip($item_id){
		if ($item_id == "root"){
			$ret = ($this->root_content) ? $this->root_content : _("Keine weitere Info vorhanden");
		} else {
			$ret = ($this->tree->tree_data[$item_id]['info']) ? $this->tree->tree_data[$item_id]['info'] :  _("Keine weitere Info vorhanden");
		}
		return $ret;
	}

	function showContent($item_id){
		echo "\n<div align=\"center\" style=\"margin-left:10px;margin-top:10px;margin-bottom:10px;font-size:10pt\">";
		if ($item_id != "root"){
			if ($this->tree->hasKids($item_id) && ($num_entries = $this->tree->getNumEntries($this->start_item_id,true))){
				if ($this->show_entries != "sublevels"){
					echo "<a " . tooltip(_("alle Eintr�ge in allen Unterebenen anzeigen")) ." href=\"" . $this->getSelf("cmd=show_sem_range&item_id={$this->start_item_id}_withkids") ."\">";
					echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/forumrot.gif\" border=\"0\">&nbsp;";
				} else {
					echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/forumrotrunt.gif\" border=\"0\">&nbsp;";
				}
				printf(_("<b>%s</b> Eintr&auml;ge in allen Unterebenen vorhanden"), $num_entries);
				if ($this->show_entries != "sublevels"){
					echo "</a>";
				}
				echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
			}
			if ($num_entries = $this->tree->getNumEntries($item_id)){
				if ($this->show_entries != "level"){
					echo "<a " . tooltip(_("alle Eintr�ge auf dieser Ebene anzeigen")) ." href=\"" . $this->getSelf("cmd=show_sem_range&item_id=$item_id") ."\">";
					echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/forumrot.gif\" border=\"0\">&nbsp;";
				} else {
					echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/forumrotrunt.gif\" border=\"0\">&nbsp;";
				}
				printf(_("<b>%s</b> Eintr&auml;ge auf dieser Ebene.&nbsp;"),$num_entries);
				if ($this->show_entries != "level"){
					echo "</a>";
				}
			} else {
					echo _("Keine Eintr&auml;ge auf dieser Ebene vorhanden!");
			}
		}
		echo "\n</div>";
	}

	function getSemPath(){

		if ($parents = $this->tree->getParents($this->start_item_id)){
			for($i = count($parents)-1; $i >= 0; --$i){
				$ret .= "&nbsp;&gt;&nbsp;<a href=\"" . $this->getSelf("start_item_id={$parents[$i]}",false)
					. "\">" .htmlReady($this->tree->tree_data[$parents[$i]]["name"]) . "</a>";
			}
		}
		if ($this->start_item_id == "root") {
			$ret = "&nbsp;&gt;&nbsp;<a href=\"" . $this->getSelf("start_item_id=root",false) . "\">" .htmlReady($this->tree->root_name) . "</a>";
		} else {
			$ret .= "&nbsp;&gt;&nbsp;<a href=\"" . $this->getSelf("start_item_id={$this->start_item_id}",false) . "\">" . htmlReady($this->tree->tree_data[$this->start_item_id]["name"]) . "</a>";

		}
		$ret .= "&nbsp;<a href=\"#\" " . tooltip(kill_format($this->getTooltip($this->start_item_id)),false,true) . "><img src=\"".$GLOBALS['ASSETS_URL']."images/info.gif\" border=\"0\" align=\"absmiddle\"></a>";
		return $ret;
	}



	function getSelf($param = "", $with_start_item = true){
		if ($param)
			$url = $GLOBALS['PHP_SELF'] . (($with_start_item) ? "?start_item_id=" . $this->start_item_id . "&" : "?") . $param ;
		else
			$url = $GLOBALS['PHP_SELF'] . (($with_start_item) ? "?start_item_id=" . $this->start_item_id : "") ;
		return $url;
	}
}
?>
