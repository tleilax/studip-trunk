<?php
# Lifter002: TODO
/**
* ResourcesBrowse.class.php
*
* search egine for resources
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		ResourcesBrowse.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesBrowse.class.php
// die Suchmaschine fuer Ressourcen
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoots.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/views/ShowList.class.php");


/*****************************************************************************
ResourcesBrowse, the search engine
/*****************************************************************************/

class ResourcesBrowse {
	var $start_object;		//where to start
	var $open_object;		//where we stay
	var $mode;			//the search mode
	var $searchArray;		//the array of search expressions (free search & properties)
	var $db;
	var $db2;
	var $db3;
	var $cssSw;			//the cssClassSwitcher

	function ResourcesBrowse() {
		$this->db = new DB_Seminar();
		$this->db2 = new DB_Seminar();
		$this->db3 = new DB_Seminar();
		$this->cssSw = new cssClassSwitcher();
		$this->list = new ShowList;

		$this->list->setRecurseLevels(0);
		$this->list->setViewHiearchyLevels(FALSE);
	}

	function setStartLevel($resource_id) {
		$this->start_object = $resource_id;
	}

	function setOpenLevel($resource_id) {
		$this->open_object = $resource_id;
	}

	function setMode($mode="browse") {
		$this->mode=$mode;
		if (!$this->mode)
			$this->mode="browse";
	}

	function setCheckAssigns($value) {
		$this->check_assigns=$value;
	}

	function setSearchOnlyRooms($value){
		$this->search_only_rooms = $this->list->show_only_rooms = $value;
	}

	function setSearchArray($array) {
		$this->searchArray=$array;
	}

	//private
	function searchForm() {
		?>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> align="center" <? echo ($this->mode == "browse") ? "colspan=\"2\"" : "" ?>>
				<font size=-1><?=_("freie Suche")?>:&nbsp;
					<select name="resources_search_range" style="vertical-align:middle">
					<option value="0" selected><?=htmlReady($GLOBALS['UNI_NAME_CLEAN'])?></option>
					<?if ($this->open_object){
						$res =& ResourceObject::Factory($this->open_object);
						?>
						<option value="<?=$this->open_object?>" selected><?=htmlReady($res->getName())?></option>
					<?}?>
					</select>
				<input name="search_exp"  type="TEXT" style="{vertical-align: middle;}" size=35 maxlength=255 value="<? echo stripslashes($this->searchArray["search_exp"]); ?>" />
				<input type="IMAGE" align="absmiddle"  <? echo makeButton ("suchestarten", "src") ?> name="start_search" border=0 value="<?=_("Suche starten")?>">
				&nbsp;
				<a href="<?=$PHP_SELF?>?quick_view=search&quick_view_mode=<?=$GLOBALS['view_mode']?>&reset=TRUE"><?=makeButton("neuesuche")?></a>
			</td>
		</tr>
		<?
	}

	//private
	function getHistory($id, $view = FALSE) {
		global $PHP_SELF, $UNI_URL, $UNI_NAME_CLEAN, $view, $view_mode;
		$top=FALSE;
		$k=0;
		while ((!$top) && ($id)) {
			$k++;
			$query = sprintf ("SELECT name, parent_id, resource_id, owner_id FROM resources_objects WHERE resource_id = '%s' ", $id);
			$this->db2->query($query);
			$this->db2->next_record();

			$result_arr[] = array("id" => $this->db2->f("resource_id"), "name" => $this->db2->f("name"), 'owner_id' => $this->db2->f('owner_id'));
			$id=$this->db2->f("parent_id");

			if ($this->db2->f("parent_id") == "0") {
				$top = TRUE;
			}
		}

		if (is_array($result_arr))
			switch (ResourceObject::getOwnerType($result_arr[count($result_arr)-1]["owner_id"])) {
				case "global":
					$top_level_name = $UNI_NAME_CLEAN;
				break;
				case "sem":
					$top_level_name = _("Veranstaltungsressourcen");
				break;
				case "inst":
					$top_level_name = _("Einrichtungsressourcen");
				break;
				case "fak":
					$top_level_name = _("Fakult&auml;tsressourcen");
				break;
				case "user":
					$top_level_name = _("pers&ouml;nliche Ressourcen");
				break;
			}
			$result = sprintf (" <font size=\"-1\">%s %s %s</font>", ($view=='search') ? "<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=$view_mode&reset=TRUE\">" : "", $top_level_name, ($view=='search') ? "</a>" : "");
			for ($i = sizeof($result_arr)-1; $i>=0; $i--) {
				if ($view)
					$result.= sprintf (" > <a href=\"%s?quick_view=%s&quick_view_mode=%s&%s=%s\"><font size = -1>%s</font></a>", $PHP_SELF, (!$view) ? "search" : $view, $view_mode, ($view=='search') ? "open_level" : "actual_object", $result_arr[$i]["id"], htmlReady($result_arr[$i]["name"]));
				else
					$result.= sprintf (" > %s", htmlReady($result_arr[$i]["name"]));
			}
		return $result;
	}

	//private
	function showTimeRange() {
		$colspan = $this->mode == 'browse' ? ' colspan="2" ' : '';
		?>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> <?=$colspan?> >
				<font size="-1"><?=_("gefundene Ressourcen sollen zu folgender Zeit <u>nicht</u> belegt sein:")?></font>
			<br />
			</td>
		</tr>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> <?=$colspan?> >
			<font size="-1">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?=_("Datum")?>:
					<input type="TEXT" style="{font-size:8pt;}" name="search_day" size="2" maxlength="2" value="<?=($this->searchArray["search_assign_begin"]) ? date("d", $this->searchArray["search_assign_begin"]) : _("tt")?>" />
					.<input type="TEXT" style="{font-size:8pt;}" name="search_month" size="2" maxlength="2" value="<?=($this->searchArray["search_assign_begin"]) ? date("m", $this->searchArray["search_assign_begin"]) : _("mm")?>" />
					.<input type="TEXT" style="{font-size:8pt;}" name="search_year" size="4" maxlength="4" value="<?=($this->searchArray["search_assign_begin"]) ? date("Y", $this->searchArray["search_assign_begin"]) : _("jjjj")?>" />
				&nbsp;&nbsp;<?=_("Beginn")?>:
					&nbsp;<input type="TEXT" style="{font-size:8pt;}" name="search_begin_hour" size="2" maxlength="2" value="<?=($this->searchArray["search_assign_begin"]) ? date("H", $this->searchArray["search_assign_begin"]) : _("ss")?>" />
					<input type="TEXT" style="{font-size:8pt;}" name="search_begin_minute" size="2" maxlength="2" value="<?=($this->searchArray["search_assign_begin"]) ? date("i", $this->searchArray["search_assign_begin"]) : _("mm")?>" />&nbsp;<?=_("Uhr")?>
				&nbsp;&nbsp;<?=_("Ende")?>:
					&nbsp;<input type="TEXT" style="{font-size:8pt;}" name="search_end_hour" size="2" maxlength="2" value="<?=($this->searchArray["search_assign_end"]) ? date("H", $this->searchArray["search_assign_end"]) : _("ss")?>" />
					<input type="TEXT" style="{font-size:8pt;}" name="search_end_minute" size="2" maxlength="2" value="<?=($this->searchArray["search_assign_end"]) ? date("i", $this->searchArray["search_assign_end"]) : _("mm")?>" />&nbsp;<?=_("Uhr")?>
			</font>
		</tr>
		<?
	}

	//private
	function showProperties() {
		global $PHP_SELF;

		?>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> >
				<font size="-1"><?=_("folgende Eigenschaften soll die Ressource besitzen (leer bedeutet egal):")?></font>
			<br />
			</td>
		</tr>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> >
				<table width="90%" cellpadding=5 cellspacing=0 border=0 align="center">
					<?
					$query = sprintf("SELECT category_id, name FROM resources_categories ORDER BY name");
					$this->db->query($query);
					$k=0;
					while ($this->db->next_record()) {
						$query = sprintf("SELECT resources_properties.property_id, name, type, options
										FROM resources_categories_properties LEFT JOIN resources_properties
										USING (property_id) WHERE category_id = '%s'
										%s ORDER BY name ", $this->db->f("category_id"),
										(get_config('RESOURCES_SEARCH_ONLY_REQUESTABLE_PROPERTY') ? " AND requestable=1 ": ""));
						$this->db2->query($query);
						if ($this->db2->num_rows()){

							print "<tr>\n";
							print "<td colspan=\"2\"> \n";
							if ($k)
								print "<hr /><br />";
							printf ("<font size=-1><b>%s:</b></font>", htmlReady($this->db->f("name")));
							print "</td>\n";
							print "</tr> \n";
							print "<tr>\n";
							print "<td width=\"50%\" valign=\"top\">";
							if ($this->db2->num_rows() % 2 == 1)
								$i=0;
							else
								$i=1;
							$switched = FALSE;
							while ($this->db2->next_record()) {
								if (($i > ($this->db2->num_rows() /2 )) && (!$switched)) {
									print "</td><td width=\"50%\" valign=\"top\">";
									$switched = TRUE;
								}
								print "<table width=\"100%\" border=\"0\"><tr>";
								printf ("<td width=\"50%%\">%s</td>", htmlReady($this->db2->f("name")));
								print "<td width=\"50%\">";
								printf ("<input type=\"HIDDEN\" name=\"search_property_val[]\" value=\"%s\" />", "_id_".$this->db2->f("property_id"));
								switch ($this->db2->f("type")) {
									case "bool":
										printf ("<input type=\"CHECKBOX\" name=\"search_property_val[]\" %s /><font size=-1>&nbsp;%s</font>", ($value) ? "checked":"", htmlReady($this->db2->f("options")));
									break;
									case "num":
										printf ("<input type=\"TEXT\" name=\"search_property_val[]\" value=\"%s\" size=20 maxlength=255 />", htmlReady($value));
									break;
									case "text";
										printf ("<textarea name=\"search_property_val[]\" cols=20 rows=2 >%s</textarea>", htmlReady($value));
									break;
									case "select";
										$options=explode (";",$this->db2->f("options"));
										print "<select name=\"search_property_val[]\">";
										print	"<option value=\"\">--</option>";
										foreach ($options as $a) {
											printf ("<option %s value=\"%s\">%s</option>", ($value == $a) ? "selected":"", $a, htmlReady($a));
										}
										printf ("</select>");
									break;
								}
								print "</td></tr></table>";
								$i++;
							}
						$k++;
						}
					}
					?>
				</table>
			</td>
		</tr>
		<?
	}

	//private
	function browseLevels() {
		global $PHP_SELF, $view_mode;

		if ($this->open_object) {
			$query = sprintf ("SELECT a.resource_id, a.name, a.description FROM resources_objects a LEFT JOIN resources_objects b ON (b.parent_id = a.resource_id)  WHERE a.parent_id = '%s' AND (a.category_id IS NULL OR b.resource_id IS NOT NULL) GROUP BY resource_id ORDER BY name", $this->open_object);
			$query2 = sprintf ("SELECT parent_id FROM resources_objects WHERE resource_id = '%s' ", $this->open_object);

			$this->db2->query($query2);
			$this->db2->next_record();
			if ($this->db2->f("parent_id") != "0")
				$way_back=$this->db2->f("parent_id");
		} else {
			$resRoots=new ResourcesUserRoots($range_id);

			$roots=$resRoots->getRoots();
			if (is_array($roots))
				$clause = "AND resource_id  IN('" . join("','",$roots) . "')";
			else
				$clause = "AND 1=2";

			$query = sprintf ("SELECT resource_id, name, description FROM resources_objects WHERE 1 %s ORDER BY name", $clause);
			$way_back=-1;
		}

		$this->db->query($query);

		//check for sublevels in current level
		$sublevels = FALSE;
		while ($this->db->next_record()) {
			$query2 = sprintf ("SELECT resource_id, name, description FROM resources_objects WHERE parent_id = '%s' ORDER BY name", $this->db->f("resource_id"));
			$this->db2->query($query2);
			if ($this->db2->nf() >0)
				$sublevels = TRUE;
		}
		if ($sublevels)
			$this->db->query($query);

		?>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?>>
				<?
				echo $this->getHistory($this->open_object);
				?>
			</td>
			<td <? echo $this->cssSw->getFullClass() ?>width="15%" align="right" nowrap valign="top">
				<?
				if ($way_back>=0) {
					printf ("<a href = \"%s?quick_view=search&quick_view_mode=%s&%s\">", $PHP_SELF, $view_mode, (!$way_back) ? "reset=TRUE" : "open_level=$way_back");
					print ("<img align=\"absmiddle\" src=\"".$GLOBALS['ASSETS_URL']."images/move_left.gif\" border=\"0\" />&nbsp; <font size=\"-1\">"._("eine Ebene zur&uuml;ck")."</font></a>");
				}
				?>
			</td>
		</tr>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> align="left" colspan="2">
				<?
				if ((!$this->db->num_rows()) || (!$sublevels)) {
					echo "<br /><font size=-1><img align=\"absmiddle\" src=\"".$GLOBALS['ASSETS_URL']."images/ausruf_small2.gif\" />&nbsp; <b>"._("Auf dieser Ebene existieren keine weiteren Unterebenen")."</b><br />&nbsp; </font>";
				} else {
				?>
				<table width="90%" cellpadding=5 cellspacing=0 border=0 align="center">
					<?
					if ($this->db->num_rows() % 2 == 1)
						$i=0;
					else
						$i=1;
					print "<td width=\"55%\" valign=\"top\">";
					while ($this->db->next_record()) {
						if (($i > ($this->db->num_rows() /2 )) && (!$switched)) {
							print "</td><td width=\"40%\" valign=\"top\">";
							$switched = TRUE;
						}
						printf ("<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=%s&open_level=%s\"><font size=\"-1\"><b>%s</b></font></a><br />", $view_mode, $this->db->f("resource_id"), htmlReady($this->db->f("name")));
						$i++;
					}
					print "</table>";
				}
				?>
			</td>
		</tr>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> align="left" colspan="2">
				<font size="-1"><?=_("Ressourcen auf dieser Ebene:")?></font>
			</td>
		</tr>
		<?
	}

	//private
	function showList() {
		?>
		<tr>
			<td <? echo ($this->mode == "browse") ? " colspan=\"2\"" : "" ?>>
				<?$result_count=$this->list->showListObjects($this->open_object);
		if (!$result_count) {
			?>
				<font size=-1><b><?=_("Es existieren keine Eintr&auml;ge auf dieser Ebene.")?></b></font>
			</td>
		</tr>
			<?
		}
}

	//private
	function showSearchList($check_assigns = FALSE) {
		?>
		<tr>
			<td <? echo ($this->mode == "browse") ? " colspan=\"2\"" : "" ?>>
				<?$result_count=$this->list->showSearchList($this->searchArray, $check_assigns);
		if (!$result_count) {
			?>
				<font size=-1><b><?=_("Es wurden keine Eintr&auml;ge zu Ihren Suchkriterien gefunden.")?></b></font>
			</td>
		</tr>
			<?
		}
	}

	//private
	function showSearch() {
		global $view_mode, $resources_data;
		?>
			<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
				<form method="POST" action="<?echo $PHP_SELF ?>?search_send=yes&quick_view=search&quick_view_mode=<?=$view_mode?>">
				<?
				$this->searchForm();
				if (!$this->searchArray) {
					if ($this->mode == "browse")
						$this->browseLevels();
					if ($this->check_assigns)
						$this->showTimeRange();
					if ($this->mode == "properties")
						$this->showProperties();
					if ($this->mode == "browse")
						$this->showList();
				} else {
					if ($this->check_assigns)
						$this->showTimeRange();
					$this->showSearchList(($resources_data["check_assigns"]) ? TRUE : FALSE);
				}
				?>
				</form>
			</table>
			<br />
		<?
	}
}
