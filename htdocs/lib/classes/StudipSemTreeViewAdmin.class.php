<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemTreeViewAdmin.class.php
// Class to print out the seminar tree in administration mode
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
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/StudipSemTree.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/TreeView.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");

/**
* class to print out the seminar tree (admin mode)
*
* This class prints out a html representation of the whole or part of the tree
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipSemTreeViewAdmin extends TreeView {
	
	var $admin_ranges = array();
	
	/**
	* constructor
	*
	* @access public
	*/
	function StudipSemTreeViewAdmin($start_item_id = "root"){
		global $sess,$_marked_item,$_marked_sem;
		$this->start_item_id = ($start_item_id) ? $start_item_id : "root";
		$this->root_content = "Eine gaaaaaanz tolle Uni.";
		parent::TreeView("StudipSemTree"); //calling the baseclass constructor 
		if (is_object($sess)){
			if (!$sess->is_registered("_marked_item"))
				$sess->register("_marked_item");
			if (!$sess->is_registered("_marked_sem"))
				$sess->register("_marked_sem");
			$this->marked_item =& $_marked_item;
			$this->marked_sem =& $_marked_sem;
		}
		$this->parseCommand();

	}
	
	/**
	* manages the session variables used for the open/close thing
	*
	* @access	private
	*/
	function handleOpenRanges(){
		global $_REQUEST;
		
		$this->open_ranges[$this->start_item_id] = true;
		
		if ($_REQUEST['close_item'] || $_REQUEST['open_item']){
			$toggle_item = ($_REQUEST['close_item']) ? $_REQUEST['close_item'] : $_REQUEST['open_item'];
			if (!$this->open_items[$toggle_item]){
				$this->open_items[$toggle_item] = true;
				if($this->tree->hasKids($_REQUEST['open_item'])){
					$this->start_item_id = $_REQUEST['open_item'];
					$this->open_ranges = null;
					$this->open_items = null;
					$this->open_items[$toggle_item] = true;
					$this->open_ranges[$toggle_item] = true;
				}
			} else {
				unset($this->open_items[$toggle_item]);
			}
		$this->anchor = $toggle_item;
		}
		if ($this->start_item_id == "root"){
			$this->open_ranges = null;
			$this->open_ranges[$this->start_item_id] = true;
		}
		if ($_REQUEST['item_id'])
			$this->anchor = $_REQUEST['item_id'];
			
		
	}
	
	function parseCommand(){
		global $_REQUEST;
		if ($_REQUEST['mode'])
			$this->mode = $_REQUEST['mode'];
		if ($_REQUEST['cmd']){
			$exec_func = "execCommand" . $_REQUEST['cmd'];
			if (method_exists($this,$exec_func)){
				if ($this->$exec_func()){
					$this->tree->init();
				}
			}
		}
		if ($this->mode == "MoveItem")
			$this->move_item_id = $this->marked_item;
	}
	
	function execCommandOrderItem(){
		global $_REQUEST;
		$direction = $_REQUEST['direction'];
		$item_id = $_REQUEST['item_id'];
		$items_to_order = $this->tree->getKids($this->tree->tree_data[$item_id]['parent_id']);
		if (!$this->isParentAdmin($item_id) || !$items_to_order)
			return false;
		for ($i = 0; $i < count($items_to_order); ++$i){
			if ($item_id == $items_to_order[$i])
				break;
		}
		if ($direction == "up" && isset($items_to_order[$i-1])){
			$items_to_order[$i] = $items_to_order[$i-1];
			$items_to_order[$i-1] = $item_id;
		} elseif (isset($items_to_order[$i+1])){
			$items_to_order[$i] = $items_to_order[$i+1];
			$items_to_order[$i+1] = $item_id;
		}
		$view = new DbView();
		for ($i = 0; $i < count($items_to_order); ++$i){
			$view->params = array($i, $items_to_order[$i]);
			$rs = $view->get_query("view:SEM_TREE_UPD_PRIO");
		}
		$this->mode = "";
		$this->msg[$item_id] = "msg�" . (($direction == "up") ? _("Element wurde eine Position nach oben verschoben.") : _("Element wurde eine Position nach unten verschoben."));
		return true;
	}
	
	function execCommandNewItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		if ($this->isItemAdmin($item_id)){
			$level_items = $this->tree->getKids($item_id);
			$new_item_id = DbView::get_uniqid();
			if (!is_array($level_items)){
				$level_items[0] = $new_item_id;
			} else {
				$level_items[] = $new_item_id;
			}
			$this->tree->tree_childs[$item_id] = $level_items;
			$this->tree->tree_data[$new_item_id] = array('parent_id' => $item_id, 'name' => _("Neuer Eintrag"), 'priority' => (count($level_items)-1));
			$this->anchor = $new_item_id;
			$this->edit_item_id = $new_item_id;
			$this->open_ranges[$item_id] = true;
			$this->open_items[$new_item_id] = true;
			unset ($this->open_items[$this->tree->tree_data[$item_id]['parent_id']]);
			$this->start_item_id = $item_id;
			if ($this->mode != "NewItem")
				$this->msg[$new_item_id] = "info�" . _("Hier k&ouml;nnen sie die Bezeichnung und die Kurzinformation zu diesem Bereich eingeben.");
			$this->mode = "NewItem";
		}
		return false;
	}
	
	function execCommandEditItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		if ($this->isItemAdmin($item_id) || $this->isParentAdmin($item_id)){
			$this->mode = "EditItem";
			$this->anchor = $item_id;
			$this->edit_item_id = $item_id;
			if($this->tree->tree_data[$this->edit_item_id]['studip_object_id']){
				$this->msg[$item_id] = "info�" . _("Hier k&ouml;nnen sie die Kurzinformation zu diesem Bereich eingeben. Der Name kann nicht ge&auml;ndert werden, da es sich um eine Stud.IP Einrichtung handelt.");
			} else {
				$this->msg[$item_id] = "info�" . _("Hier k&ouml;nnen sie die Bezeichnung und die Kurzinformation zu diesem Bereich eingeben");
			}
		}
		return false;
	}
	
	function execCommandInsertItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$parent_id = $_REQUEST['parent_id'];
		$item_name = $_REQUEST['edit_name'];
		$item_info = $_REQUEST['edit_info'];
		if ($this->mode == "NewItem" && $item_id){
			if ($this->isItemAdmin($parent_id)){
				$priority = count($this->tree->getKids($parent_id));
				if ($this->tree->InsertItem($item_id,$parent_id,$item_name,$item_info,$priority,'NULL')){
					$this->mode = "";
					$this->anchor = $item_id;
					$this->open_items[$item_id] = true;
					$this->msg[$item_id] = "msg�" . _("Dieser Bereich wurde neu eingef&uuml;gt.");
				}
			}
		}
		if ($this->mode == "EditItem"){
			if ($this->isParentAdmin($item_id)){
				if ($this->tree->UpdateItem($item_id, $item_name, $item_info)){
					$this->msg[$item_id] = "msg�" . _("Bereich wurde ge&auml;ndert.");
				} else {
					$this->msg[$item_id] = "info�" . _("Keine Ver&auml;nderungen vorgenommen.");
				}
				$this->mode = "";
				$this->anchor = $item_id;
				$this->open_items[$item_id] = true;
				
			}
		}
		return true;
	}
	
	function execCommandAssertDeleteItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		if ($this->isParentAdmin($item_id)){
			$this->mode = "AssertDeleteItem";
			$this->open_items[$item_id] = true;
			$this->msg[$item_id] = "info�" ._("Sie beabsichtigen diesen Bereich inklusive aller Unterbereiche zu l&ouml;schen. ")
						. sprintf(_("Es werden insgesamt %s Bereiche gel&ouml;scht !"),count($this->tree->getKidsKids($item_id))+1)
						. "<br>" . _("Wollen sie diese Bereiche wirklich l&ouml;schen ?") . "<br>"
						. "<a href=\"" . $this->getSelf("cmd=DeleteItem&item_id=$item_id") . "\">"
						. "<img " .makeButton("ja2","src") . tooltip(_("l�schen"))
						. " border=\"0\"></a>&nbsp;"
						. "<a href=\"" . $this->getSelf("cmd=Cancel&item_id=$item_id") . "\">"
						. "<img " .makeButton("nein","src") . tooltip(_("abbrechen"))
						. " border=\"0\"></a>";
		}
		return false;
	}
	
	function execCommandDeleteItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$item_name = $this->tree->tree_data[$item_id]['name'];
		if ($this->isParentAdmin($item_id) && $this->mode == "AssertDeleteItem"){
			$this->anchor = $this->tree->tree_data[$item_id]['parent_id'];
			$items_to_delete = $this->tree->getKidsKids($item_id);
			$items_to_delete[] = $item_id;
			$deleted = $this->tree->DeleteItems($items_to_delete);
			if ($deleted['items']){
				$this->msg[$this->anchor] = "msg�" . sprintf(_("Der Bereich <b>%s</b> und alle Unterbereiche (insgesamt %s) wurden gel&ouml;scht. "),htmlReady($item_name),$deleted['items']);
			} else {
				$this->msg[$this->anchor] = "error�" . _("Fehler, es konnten keine Bereiche gel&ouml;scht werden !");
			}
			if ($deleted['entries']){
				$this->msg[$this->anchor] .= sprintf(_("<br>Es wurden %s Veranstaltungszuordnungen gel&ouml;scht. "),$deleted['entries']);
			}
			$this->mode = "";
			$this->open_items = array();
			$this->open_ranges = array();
			$this->open_items[$this->anchor] = true;
			$this->start_item_id = $this->anchor;
		}
		return true;
	}
	
	function execCommandMoveItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$this->anchor = $item_id;
		$this->marked_item = $item_id;
		$this->mode = "MoveItem";
		return false;
	}
	
	function execCommandDoMoveItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$item_to_move = $this->marked_item;
		if ($this->mode == "MoveItem" && ($this->isItemAdmin($item_id) || $this->isParentAdmin($item_id))
			&& ($item_to_move != $item_id) && ($this->tree->tree_data[$item_to_move]['parent_id'] != $item_id)
			&& !$this->tree->isChildOf($item_to_move,$item_id)){
			$view = new DbView();
			$view->params = array($item_id, count($this->tree->getKids($item_id)), $item_to_move);
			$rs = $view->get_query("view:SEM_TREE_MOVE_ITEM");
			if ($rs->affected_rows()){
					$this->msg[$item_to_move] = "msg�" . _("Bereich wurde verschoben.");
				} else {
					$this->msg[$item_to_move] = "error�" . _("Keine Verschiebung durchgef�hrt.");
				}
			}
		$this->anchor = $item_to_move;
		$this->open_ranges[$item_id] = true;
		$this->open_items[$item_id] = true;
		$this->start_item_id = $item_id;
		$this->open_items[$item_to_move] = true;
		$this->mode = "";
		return true;
	}
	
	function execCommandInsertFak(){
		global $_REQUEST;
		if($this->isItemAdmin("root") && $_REQUEST['insert_fak']){
			$view = new DbView();
			$item_id = $view->get_uniqid();
			$view->params = array($item_id,'root','',$this->tree->getNumKids('root')+1,'',"'{$_REQUEST['insert_fak']}'");
			$rs = $view->get_query("view:SEM_TREE_INS_ITEM");
			if ($rs->affected_rows()){
				$this->anchor = $item_id;
				$this->open_items[$item_id] = true;
				$this->msg[$item_id] = "msg�" . _("Dieser Bereich wurde neu eingef&uuml;gt.");
				return true;
			}
		}
		return false;
	}
	
	function execCommandMarkSem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$marked_sem = $_REQUEST['marked_sem'];
		$sem_aktion = explode("_",$_REQUEST['sem_aktion']);
		if (($sem_aktion[0] == 'mark' || $sem_aktion[1] == 'mark') && count($marked_sem)){
			$count_mark = 0;
			for ($i = 0; $i < count($marked_sem); ++$i){
				if (!isset($this->marked_sem[$marked_sem[$i]])){
					++$count_mark;
					$this->marked_sem[$marked_sem[$i]] = true;
				}
			}
			if ($count_mark){
				$this->msg[$item_id] = "msg�" . sprintf(_("Es wurde(n) %s Veranstaltung(en) der Merkliste hinzugef&uuml;gt."),$count_mark);
			}
		}
		if ($this->isItemAdmin($item_id)){
			if (($sem_aktion[0] == 'del' || $sem_aktion[1] == 'del') && count($marked_sem)){
				$count_del = $this->tree->DeleteSemEntries($item_id, $marked_sem);
				if ($this->msg[$item_id]){
					$this->msg[$item_id] .= "<br>";
				} else {
					$this->msg[$item_id] = "msg�";
				}
				$this->msg[$item_id] .= sprintf(_("%s Veranstaltungszuordnung(en) wurde(n) aufgehoben."),$count_del);
			}
			$this->anchor = $item_id;
			$this->open_items[$item_id] = true;
			return true;
		}
		return false;
	}
	
	function execCommandCancel(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$this->mode = "";
		$this->anchor = $item_id;
		return false;
	}

	function showSemTree(){
		?>
		<script type="text/javascript">
			function invert_selection(the_form){
				my_elements = document.forms[the_form].elements['marked_sem[]'];
				if(!my_elements.length){
					if(my_elements.checked)
						my_elements.checked = false;
					else
						my_elements.checked = true;
				} else {
					for(i = 0; i < my_elements.length; ++i){
						if(my_elements[i].checked)
						my_elements[i].checked = false;
						else
						my_elements[i].checked = true;
					}
				}
		}
		</script>
		<?
		echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		if ($this->start_item_id != 'root'){
			echo "\n<tr><td class=\"printhead\" align=\"left\" valign=\"top\">" . $this->getSemPath() 
			. "<img src=\"pictures/forumleer.gif\"  border=\"0\" height=\"20\" width=\"1\"></td></tr>";
		}
		echo "\n<tr><td class=\"blank\"  align=\"left\" valign=\"top\">";
		$this->showTree($this->start_item_id);
		echo "\n</td></tr></table>";
	}
	
	function getSemPath(){
		//$ret = "<a href=\"" . parent::getSelf("start_item_id=root") . "\">" .htmlReady($this->tree->root_name) . "</a>";
		if ($parents = $this->tree->getParents($this->start_item_id)){
			for($i = count($parents)-1; $i >= 0; --$i){
				$ret .= " &gt; <a class=\"tree\" href=\"" . $this->getSelf("start_item_id={$parents[$i]}&open_item={$parents[$i]}",false) 
					. "\">" .htmlReady($this->tree->tree_data[$parents[$i]]["name"]) . "</a>";
			}
		}
		return $ret;
	}
			
	/**
	* returns html for the icons in front of the name of the item 
	*
	* @access	private
	* @param	string	$item_id
	* @return	string
	*/
	function getItemHeadPics($item_id){
		$head = "";
		$head .= "<a href=\"";
		$head .= ($this->open_items[$item_id])? $this->getSelf("close_item={$item_id}") . "\"" . tooltip(_("Dieses Element schlie�en"),true) . ">"
											: $this->getSelf("open_item={$item_id}") . "\"" . tooltip(_("Dieses Element �ffnen"),true) . ">";
		$head .= "<img src=\"pictures/";
		$head .= ($this->open_items[$item_id]) ? "forumrotrunt.gif" : "forumgrau.gif";
		$head .= "\" border=\"0\" align=\"baseline\" hspace=\"2\">";
		$head .= (!$this->open_items[$item_id]) ? "<img  src=\"pictures/forumleer.gif\" width=\"5\" border=\"0\">" : ""; 
		$head .= "</a>";
		if ($this->tree->hasKids($item_id)){
			$head .= "<img border=\"0\"  src=\"pictures/";
			$head .= ($this->open_ranges[$item_id]) ? "cont_folder3.gif" : "cont_folder.gif";
			$head .= "\" ";
			$head .= (!$this->open_ranges[$item_id])? tooltip(_("Alle Unterelement �ffnen")) : tooltip(_("Alle Unterelemente schliessen"));
			$head .= ">";
		} else { 
			$head .= "<img src=\"pictures/";
			$head .= ($this->open_items[$item_id]) ? "cont_folder4.gif" : "cont_folder2.gif";
			$head .= "\" " . tooltip(_("Dieses Element hat keine Unterelemente")) . "border=\"0\">";
		}
	return $head;
	}
	
	function getItemContent($item_id){
		if ($item_id == $this->edit_item_id )
			return $this->getEditItemContent();
		if ($item_id == $this->move_item_id){
			$this->msg[$item_id] = "info�" . sprintf(_("Dieses Element wurde zum Verschieben markiert. Bitte w&auml;hlen sie ein Einf�gesymbol %s aus,"
								." um das Element zu verschieben."), "<img src=\"pictures/move.gif\" border=\"0\" " .tooltip(_("Einf�gesymbol")) . ">");
			}
		$content = "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt;\">";
		$content .= $this->getItemMessage($item_id);
		$content .= "\n<tr><td style=\"font-size:10pt;\" align=\"center\">";
		if ($this->isItemAdmin($item_id) && $item_id != "root"){
			$content .= "<a href=\"" . $this->getSelf("cmd=NewItem&item_id=$item_id") . "\">"
						. "<img " .makeButton("neuesobjekt","src") . tooltip(_("Innerhalb dieser Ebene ein neues Element einf�gen"))
						. " border=\"0\"></a>&nbsp;";
		}
		if ($this->isParentAdmin($item_id) && $item_id != "root"){
			$content .= "<a href=\"" . $this->getSelf("cmd=EditItem&item_id=$item_id") . "\">"
			. "<img " .makeButton("bearbeiten","src") . tooltip(_("Dieses Element bearbeiten"))
			. " border=\"0\"></a>&nbsp;";
		
			$content .= "<a href=\"" . $this->getSelf("cmd=AssertDeleteItem&item_id=$item_id") . "\">"
			. "<img " .makeButton("loeschen","src") . tooltip(_("Dieses Element l�schen"))
			. " border=\"0\"></a>&nbsp;";
			if ($this->move_item_id == $item_id && $this->mode == "MoveItem"){
				$content .= "<a href=\"" . $this->getSelf("cmd=Cancel&item_id=$item_id") . "\">"
										. "<img " .makeButton("abbrechen","src") . tooltip(_("Verschieben abbrechen"))
										. " border=\"0\"></a>&nbsp;";
			} else {
				$content .= "<a href=\"" . $this->getSelf("cmd=MoveItem&item_id=$item_id") . "\">"
			. "<img " .makeButton("verschieben","src") . tooltip(_("Dieses Element in eine andere Ebene verschieben"))
			. " border=\"0\"></a>&nbsp;";
			}
		}
		if ($item_id == 'root' && $this->isItemAdmin($item_id)){
			$view = new DbView();
			$rs = $view->get_query("view:SEM_TREE_GET_LONELY_FAK");
			$content .= "\n<form action=\"" . $this->getSelf("cmd=InsertFak") . "\" method=\"post\">" . _("Stud.IP Fakult&auml;t einf&uuml;gen:")
					. "&nbsp;\n<select style=\"width:200px;vertical-align:middle;\" name=\"insert_fak\">";
			while($rs->next_record()){
				$content .= "\n<option value=\"" . $rs->f("Institut_id") . "\">" . htmlReady(my_substr($rs->f("Name"),0,50)) . "</option>";
			}
			$content .= "</select>&nbsp;<input border=\"0\" type=\"image\" style=\"vertical-align:middle;\" " .makeButton("eintragen","src") . tooltip(_("Fakult�t einf�gen")) . "></form>";
		}
		$content .= "</td></tr></table>";

		$content .= "\n<table border=\"0\" width=\"90%\" cellpadding=\"2\" cellspacing=\"0\" align=\"center\" style=\"font-size:10pt\">";
		if ($item_id == "root"){
			$content .= "\n<tr><td  class=\"topic\" align=\"left\" style=\"font-size:10pt;\">" . htmlReady($this->tree->root_name) ." </td></tr>";
			$content .= "\n<tr><td  class=\"steel1\" align=\"left\" style=\"font-size:10pt;\">" . htmlReady($this->root_content) ." </td></tr>";
			$content .= "\n</table>";
			return $content;
		}
		if ($this->tree->tree_data[$item_id]['info']){
			$content .= "\n<tr><td style=\"font-size:10pt;\" class=\"steel1\" align=\"left\" colspan=\"3\">";
			$content .= formatReady($this->tree->tree_data[$item_id]['info']) . "</td></tr>";
		}
		$content .= "<tr><td style=\"font-size:10pt;\"colspan=\"3\">&nbsp;</td></tr>";
		if ($this->tree->getNumEntries($item_id) - $this->tree->tree_data[$item_id]['lonely_sem']){
			$content .= "<tr><td class=\"steel1\" style=\"font-size:10pt;\" align=\"left\" colspan=\"3\"><b>" . _("Eintr&auml;ge auf dieser Ebene:");
			$content .= "</b>\n</td></tr>";
			$entries = $this->tree->getSemData($item_id);
			$content .= $this->getSemDetails($entries->getGroupedResult("seminar_id"),$item_id);
		} else {
			$content .= "\n<tr><td class=\"steel1\" style=\"font-size:10pt;\" colspan=\"3\">" . _("Keine Eintr&auml;ge auf dieser Ebene vorhanden!") . "</td></tr>";
		}
		if ($this->tree->tree_data[$item_id]['lonely_sem']){
			$content .= "<tr><td class=\"steel1\" align=\"left\" style=\"font-size:10pt;\" colspan=\"3\"><b>" . _("Nicht zugeordnete Veranstaltungen auf dieser Ebene:");
			$content .= "</b>\n</td></tr>";
			$entries = $this->tree->getLonelySemData($item_id);
			$content .= $this->getSemDetails($entries->getGroupedResult("seminar_id"),$item_id,true);
		}
		$content .= "</table>";
		return $content;
	}
	
	function getSemDetails($sem_data, $item_id, $lonely_sem = false){
		$form_name = DbView::get_uniqid();
		$content = "<form name=\"$form_name\" action=\"" . $this->getSelf("cmd=MarkSem") ."\" method=\"post\">
					<input type=\"hidden\" name=\"item_id\" value=\"$item_id\">";
		$sem_number = -1;
		foreach($sem_data as $seminar_id => $data){
				if (key($data['sem_number']) != $sem_number){
					$sem_number = key($data['sem_number']);
					$content .= "\n<tr><td class=\"steelkante\" colspan=\"3\" style=\"font-size:10pt;\" >" . $this->tree->sem_dates[$sem_number]['name'] . "</td></tr>";
				}
				$content .= "<tr><td class=\"steel1\" width=\"1%\"><input type=\"checkbox\" name=\"marked_sem[]\" value=\"$seminar_id\" style=\"vertical-align:middle\">
							</td><td class=\"steel1\" style=\"font-size:10pt;\"><a href=\"details.php?sem_id=". $seminar_id 
						."&send_from_search=true&send_from_search_page=" . rawurlencode($this->getSelf()) . "\">" . htmlReady(key($data["Name"])) . "</a>
						 </td><td class=\"steel1\" align=\"right\" style=\"font-size:10pt;\">(";
				for ($i = 0; $i < count($data["doz_name"]); ++$i){
					$content .= "<a href=\"about.php?username=" . key($data["doz_uname"]) ."\">" . htmlReady(key($data["doz_name"])) . "</a>";
					if($i != count($data["doz_name"])-1) {
						$content .= ", ";
					}
					next($data["doz_name"]);
					next($data["doz_uname"]);
				}
				$content .= ") </td></tr>";
			}
			$content .= "<tr><td class=\"steel1\" colspan=\"2\"><a href=\"#\" onClick=\"invert_selection('$form_name');return false;\">
						<img " . makeButton("auswahlumkehr","src") . "border=\"0\" align=\"middle\" hspace=\"3\""
						. tooltip(_("Auswahl umkehren")) . "></a></td><td class=\"steel1\" align=\"right\">
						<select name=\"sem_aktion\" style=\"font-size:8pt;vertical-align:bottom;\" " . tooltip(_("Aktion ausw�hlen"),true) . ">
						<option value=\"mark\">" . _("in Merkliste &uuml;bernehmen") . "</option>";
			if (!$lonely_sem && $this->isItemAdmin($item_id)){
				$content .= "<option value=\"del_mark\">" . _("l&ouml;schen und in Merkliste &uuml;bernehmen") . "</option>
						<option value=\"del\">" . _("l&ouml;schen") . "</option>";
			}
			$content .= "</select><input border=\"0\" type=\"image\" " . makeButton("ok","src") . tooltip(_("Gew�hlte Aktion starten")) . " style=\"vertical-align:middle\" hspace=\"3\">
						</td></tr> </form>";
			return $content;
	}
	
	function getEditItemContent(){
		$content = "\n<form name=\"item_form\" action=\"" . $this->getSelf("cmd=InsertItem&item_id={$this->edit_item_id}") . "\" method=\"POST\">";
		$content .= "\n<input type=\"HIDDEN\" name=\"parent_id\" value=\"{$this->tree->tree_data[$this->edit_item_id]['parent_id']}\">";
		$content .= "\n<table width=\"90%\" border =\"0\" style=\"border-style: solid; border-color: #000000;  border-width: 1px;font-size: 10pt;\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\">";
		$content .=  $this->getItemMessage($this->edit_item_id,2);
		$content .= "\n<tr><td colspan=\"2\" class=\"steelgraudunkel\" ><b>". _("Bereich editieren") . "</b></td></tr>";
		$content .= "\n<tr><td class=\"steel1\" width=\"1%\">". _("Name des Elements:") . "</td><td class=\"steel1\" width=\"99%\">";
		if($this->tree->tree_data[$this->edit_item_id]['studip_object_id']){
			$content .= htmlReady($this->tree->tree_data[$this->edit_item_id]['name']);
		} else {
			$content .= "<input type=\"TEXT\" name=\"edit_name\" size=\"50\" style=\"width:100%\" value=\"" . htmlReady($this->tree->tree_data[$this->edit_item_id]['name']) . "\">";
		}
		$content .= "</td></tr><tr><td class=\"steel1\"  width=\"1%\">" . _("Infotext:") . "</td><td class=\"steel1\">"
				. "<textarea style=\"width:100%\" rows=\"5\" name=\"edit_info\" wrap=\"virtual\">" .htmlReady($this->tree->tree_data[$this->edit_item_id]['info']) . "</textarea>"
				. "</td></tr><tr><td class=\"steel1\" align=\"right\" valign=\"top\" colspan=\"2\"><input type=\"image\" "
				. makeButton("absenden","src") . tooltip("Einstellungen �bernehmen") . " border=\"0\">"
				. "&nbsp;<a href=\"" . $this->getSelf("cmd=Cancel&item_id="  
				. (($this->mode == "NewItem") ? $this->tree->tree_data[$this->edit_item_id]['parent_id'] : $this->edit_item_id) ) . "\">"
				. "<img " .makeButton("abbrechen","src") . tooltip(_("Aktion abbrechen"))
				. " border=\"0\"></a></td></tr>";
		
		$content .= "\n</table>";
		
		return $content;
	}
	

	function isItemAdmin($item_id){
		global $auth;
		if ($auth->auth['perm'] == "root"){
			return true;
		}
		if (!($admin_id = $this->tree->tree_data[$this->tree->getAdminRange($item_id)]['studip_object_id'])){
			return false;
		}
		if(!isset($this->admin_ranges[$admin_id])){
			$view = new DbView();
			$view->params[0] = $auth->auth['uid'];
			$view->params[1] = $admin_id;
			$rs = $view->get_query("view:SEM_TREE_CHECK_PERM");
			$this->admin_ranges[$admin_id] = ($rs->next_record()) ? true : false;
		}
		if ($this->admin_ranges[$admin_id]){
			return true;
		} else {
			return false;
		}
	}
	
	function isParentAdmin($item_id){
		return $this->isItemAdmin($this->tree->tree_data[$item_id]['parent_id']);
	}
	
	function getItemHead($item_id){
		$head = "";
		if ($this->mode == "MoveItem" && ($this->isItemAdmin($item_id) || $this->isParentAdmin($item_id))
			&& ($this->move_item_id != $item_id) && ($this->tree->tree_data[$this->move_item_id]['parent_id'] != $item_id)
			&& !$this->tree->isChildOf($this->move_item_id,$item_id)){
			$head .= "<a href=\"" . $this->getSelf("cmd=DoMoveItem&item_id=$item_id") . "\">"
			. "<img src=\"pictures/move.gif\" border=\"0\" " .tooltip(_("An dieser Stelle einf�gen")) . "></a>&nbsp;";
		}
		$head .= parent::getItemHead($item_id);
		if ($item_id != "root"){
			$head .= " (" . $this->tree->getNumEntries($item_id,true) . ") " ;
		}
		if ($item_id != $this->start_item_id && $this->isParentAdmin($item_id) && $item_id != $this->edit_item_id){
			$head .= "</td><td align=\"rigth\" valign=\"bottom\" class=\"printhead\">";
			if (!$this->tree->isFirstKid($item_id)){
				$head .= "<a href=\"". $this->getSelf("cmd=OrderItem&direction=up&item_id=$item_id") .
				"\"><img src=\"pictures/move_up.gif\" hspace=\"4\" width=\"13\" height=\"11\" border=\"0\" " . 
				tooltip(_("Element nach oben")) ."></a>";
			}
			if (!$this->tree->isLastKid($item_id)){
				$head .= "<a href=\"". $this->getSelf("cmd=OrderItem&direction=down&item_id=$item_id") . 
				"\"><img src=\"pictures/move_down.gif\" hspace=\"4\" width=\"13\" height=\"11\" border=\"0\" " . 
				tooltip(_("Element nach unten")) . "></a>";
			}
			$head .= "&nbsp;";
		}
		return $head;
	}
	
	function getItemMessage($item_id,$colspan = 1){
		$content = "";
		if ($this->msg[$item_id]){
			$msg = split("�",$this->msg[$item_id]);
			$pics = array('error' => 'x.gif', 'info' => 'ausruf.gif', 'msg' => 'ok.gif');
			$content = "\n<tr><td colspan=\"{$colspan}\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\" style=\"font-size:10pt\">
						<tr><td class=\"blank\" align=\"center\" width=\"25\"><img width=\"16\" height=\"16\" src=\"pictures/" . $pics[$msg[0]] . "\" ></td>
						<td class=\"blank\" align=\"left\">" . $msg[1] . "</td></tr>
						</table></td></tr><tr>";
		}
		return $content;
	}
	
	function getSelf($param = "", $with_start_item = true){
		$url = $GLOBALS['PHP_SELF'] . "?" . "foo=" . DbView::get_uniqid();
		if ($this->mode)
			$url .= "&mode=" . $this->mode;
		if ($param)
			$url .= (($with_start_item) ? "&start_item_id=" . $this->start_item_id . "&" : "&") . $param . "#anchor";
		else
			$url .= (($with_start_item) ? "&start_item_id=" . $this->start_item_id : "") . "#anchor";
		return $url;
	}
}
//test
//page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
//include "html_head.inc.php";
//include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
//$test = new StudipSemTreeViewAdmin($_REQUEST['start_item_id']);
//$test->showSemTree();
//echo "<hr><pre>";
//print_r($_open_items);
//page_close();
?>
