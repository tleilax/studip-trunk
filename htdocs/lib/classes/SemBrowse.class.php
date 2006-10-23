<?php

require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemSearch.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemTreeViewSimple.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemRangeTreeViewSimple.class.php");

class SemBrowse {

	var $sem_browse_data;
	var $persistent_fields = array("level","cmd","start_item_id","show_class","group_by","search_result","default_sem","sem_status","show_entries","sset");
	var $search_obj;
	var $sem_tree;
	var $range_tree;
	var $show_result;
	var $sem_number;
	var $group_by_fields = array();
	var $target_url;
	var $target_id;

	function SemBrowse($sem_browse_data_init = array()){
		global $sem_browse_data,$sess;

		$this->group_by_fields = array(	array('name' => _("Semester"), 'group_field' => 'sem_number'),
										array('name' => _("Bereich"), 'group_field' => 'bereich'),
										array('name' => _("DozentIn"), 'group_field' => 'fullname', 'unique_field' => 'username'),
										array('name' => _("Typ"), 'group_field' => 'status'),
										array('name' => _("Einrichtung"), 'group_field' => 'Institut', 'unique_field' => 'Institut_id'));

		if (!$sess->is_registered("sem_browse_data") || !$sem_browse_data){
			$sess->register("sem_browse_data");
			$sem_browse_data = $sem_browse_data_init;
		}
		$this->sem_browse_data =& $sem_browse_data;
		$level_change = isset($_REQUEST['start_item_id']);
		for ($i = 0; $i < count($this->persistent_fields); ++$i){
			if (isset($_REQUEST[$this->persistent_fields[$i]])){
			$this->sem_browse_data[$this->persistent_fields[$i]] = $_REQUEST[$this->persistent_fields[$i]];
			}
		}
		$this->search_obj = new StudipSemSearch("search_sem", false, !(is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root')));
		$this->search_obj->search_fields['qs_choose']['content'] = array('title' => _("Titel"), 'lecturer' => _("DozentIn"), 'comment' => _("Kommentar"));
		$this->search_obj->search_fields['type']['class'] = $this->sem_browse_data['show_class'];

		if (isset($_REQUEST[$this->search_obj->form_name . "_scope_choose"])){
			$this->sem_browse_data["start_item_id"] = $_REQUEST[$this->search_obj->form_name . "_scope_choose"];
		}
		if (isset($_REQUEST[$this->search_obj->form_name . "_range_choose"])){
			$this->sem_browse_data["start_item_id"] = $_REQUEST[$this->search_obj->form_name . "_range_choose"];
		}
		if (isset($_REQUEST[$this->search_obj->form_name . "_sem"])){
			$this->sem_browse_data['default_sem'] = $_REQUEST[$this->search_obj->form_name . "_sem"];
		}

		if (isset($_REQUEST['keep_result_set']) || $this->sem_browse_data['sset'] || (count($this->sem_browse_data['search_result']) && $this->sem_browse_data['show_entries'])){
			$this->show_result = true;
		}

		if ($this->sem_browse_data['cmd'] == "xts"){
			$this->sem_browse_data['level'] = "f";
			if($this->search_obj->new_search_button_clicked){
				$this->show_result = false;
				$this->sem_browse_data['sset'] = false;
				$this->sem_browse_data['search_result'] = array();
			}
		}

		if ($this->sem_browse_data['cmd'] == "qs"){
			$this->sem_browse_data['default_sem'] = "all";
		}

		if($this->sem_browse_data["default_sem"] != 'all'){
			$this->sem_number[0] = $this->sem_browse_data["default_sem"];
		} else {
			$this->sem_number = false;
		}

		$sem_status = (is_array($this->sem_browse_data['sem_status'])) ? $this->sem_browse_data['sem_status'] : false;

		if ($this->sem_browse_data['level'] == "vv"){
			if (!$this->sem_browse_data['start_item_id']){
				$this->sem_browse_data['start_item_id'] = "root";
			}
			$this->sem_tree = new StudipSemTreeViewSimple($this->sem_browse_data["start_item_id"], $this->sem_number, $sem_status, !(is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root')));
			$this->sem_browse_data['cmd'] = "qs";
			if ($_REQUEST['cmd'] != "show_sem_range" && $level_change && !$this->search_obj->search_button_clicked ){
				$this->get_sem_range($this->sem_browse_data["start_item_id"], false);
				$this->show_result = true;
				$this->sem_browse_data['show_entries'] = "level";
				$this->sem_browse_data['sset'] = false;
			}
		}

		if ($this->sem_browse_data['level'] == "ev"){
			if (!$this->sem_browse_data['start_item_id']){
				$this->sem_browse_data['start_item_id'] = "root";
			}
			$this->range_tree = new StudipSemRangeTreeViewSimple($sem_browse_data["start_item_id"],$this->sem_number,$sem_status, !(is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root')));
			$this->sem_browse_data['cmd'] = "qs";
			if ($_REQUEST['cmd'] != "show_sem_range_tree" && $level_change && !$this->search_obj->search_button_clicked ){
				$this->get_sem_range_tree($this->sem_browse_data["start_item_id"], false);
				$this->show_result = true;
				$this->sem_browse_data['show_entries'] = "level";
				$this->sem_browse_data['sset'] = false;
			}
		}


		if ($this->search_obj->search_button_clicked && !$this->search_obj->new_search_button_clicked){
			$this->search_obj->override_sem = $this->sem_number;
			$this->search_obj->doSearch();
			if ($this->search_obj->found_rows){
				$this->sem_browse_data['search_result'] = array_flip($this->search_obj->search_result->getRows("seminar_id"));
			} else {
				$this->sem_browse_data['search_result'] = array();
			}
			$this->show_result = true;
			$this->sem_browse_data['show_entries'] = false;
			$this->sem_browse_data['sset'] = true;
		}


		if ($_REQUEST['cmd'] == "show_sem_range"){
			$tmp = explode("_",$_REQUEST['item_id']);
			$this->get_sem_range($tmp[0],isset($tmp[1]));
			$this->show_result = true;
			$this->sem_browse_data['show_entries'] = (isset($tmp[1])) ? "sublevels" : "level";
			$this->sem_browse_data['sset'] = false;
		}

		if ($_REQUEST['cmd'] == "show_sem_range_tree"){
			$tmp = explode("_",$_REQUEST['item_id']);
			$this->get_sem_range_tree($tmp[0],isset($tmp[1]));
			$this->show_result = true;
			$this->sem_browse_data['show_entries'] = (isset($tmp[1])) ? "sublevels" : "level";
			$this->sem_browse_data['sset'] = false;
		}

		if (isset($_REQUEST['do_show_class']) && count($this->sem_browse_data['sem_status'])){
			$this->get_sem_class();
		}

		/*
		echo "<hr><pre>";
		print_r($this->sem_browse_data);
		print var_dump($this->show_result);
		echo "</pre><hr>";
		*/

	}

	function show_class(){
		if ($this->sem_browse_data['show_class'] == 'all'){
			return true;
		}
		if (!is_array($this->classes_show_class)){
			$this->classes_show_class = array();
			foreach ($GLOBALS['SEM_CLASS'] as $sem_class_key => $sem_class){
				if ($sem_class['bereiche']){
					$this->classes_show_class[] = $sem_class_key;
				}
			}
		}
		return in_array($this->sem_browse_data['show_class'], $this->classes_show_class);
	}

	function get_sem_class(){
		$db = new DB_Seminar("SELECT Seminar_id from seminare WHERE "
							. (!(is_object($GLOBALS['perm'] && $GLOBALS['perm']->have_perm('root'))) ? "seminare.visible=1 AND" : "" )
							. " seminare.status IN ('" . join("','", $this->sem_browse_data['sem_status']) . "')");
		$snap = new DbSnapshot($db);
		$sem_ids = $snap->getRows("Seminar_id");
		if (is_array($sem_ids)){
			$this->sem_browse_data['search_result'] = array_flip($sem_ids);
		}
		$this->sem_browse_data['sset'] = true;
		$this->show_result = true;
	}

	function get_sem_range($item_id, $with_kids){
		$sem_ids = $this->sem_tree->tree->getSemIds($item_id,$with_kids);
		if (is_array($sem_ids)){
			$this->sem_browse_data['search_result'] = array_flip($sem_ids);
		} else {
			$this->sem_browse_data['search_result'] = array();
		}
	}

	function get_sem_range_tree($item_id, $with_kids){
		$range_object =& RangeTreeObject::GetInstance($item_id);
		if ($with_kids){
			$inst_ids = $range_object->getAllObjectKids();
		}
		$inst_ids[] = $range_object->item_data['studip_object_id'];
		$db_view = new DbView();
		$db_view->params[0] = $inst_ids;
		$db_view->params[1] = (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root')) ? '' : ' AND c.visible=1';
		$db_view->params[1] .= (is_array($this->sem_browse_data['sem_status'])) ? " AND c.status IN('" . join("','",$this->sem_browse_data['sem_status']) ."')" : "";
		$db_view->params[2] = (is_array($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end > " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
		$db_snap = new DbSnapshot($db_view->get_query("view:SEM_INST_GET_SEM"));
		if ($db_snap->numRows){
			$sem_ids = $db_snap->getRows("Seminar_id");
			$this->sem_browse_data['search_result'] = array_flip($sem_ids);
		} else {
			$this->sem_browse_data['search_result'] = array();
		}
	}

	function print_qs(){
		global $PHP_SELF;
		//Quicksort Formular... fuer die eiligen oder die DAUs....
		echo "<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=0 width = \"99%\">\n";
		echo $this->search_obj->getFormStart("$PHP_SELF?send=yes");
		echo "<tr><td height=\"40\" class=\"steel1\" align=\"center\" valign=\"middle\" ><font size=\"-1\">";
		echo _("Schnellsuche:") . "&nbsp;";
		echo $this->search_obj->getSearchField("qs_choose",array('style' => 'vertical-align:middle;font-size:9pt;'));
		if ($this->sem_browse_data['level'] == "vv"){
			$this->search_obj->sem_tree =& $this->sem_tree->tree;
			if ($this->sem_tree->start_item_id != 'root'){
				$this->search_obj->search_scopes[] = $this->sem_tree->start_item_id;
			}
			echo "&nbsp;" . _("in:") . "&nbsp;" . $this->search_obj->getSearchField("scope_choose",array('style' => 'vertical-align:middle;font-size:9pt;'),$this->sem_tree->start_item_id);
			echo "\n<input type=\"hidden\" name=\"level\" value=\"vv\">";
		}
		if ($this->sem_browse_data['level'] == "ev"){
			$this->search_obj->range_tree =& $this->range_tree->tree;
			if ($this->range_tree->start_item_id != 'root'){
				$this->search_obj->search_ranges[] = $this->range_tree->start_item_id;
			}
			echo "&nbsp;" . _("in:") . "&nbsp;" . $this->search_obj->getSearchField("range_choose",array('style' => 'vertical-align:middle;font-size:9pt;'),$this->range_tree->start_item_id);
			echo "\n<input type=\"hidden\" name=\"level\" value=\"ev\">";
		}
		echo "&nbsp;";

		echo $this->search_obj->getSearchField("quick_search",array( 'style' => 'vertical-align:middle;font-size:9pt;','size' => 20));
		echo $this->search_obj->getSearchButton(array('style' => 'vertical-align:middle'));
		echo "</td></tr>";
		echo $this->search_obj->getFormEnd();
		echo "</table>\n";
	}

	function print_xts(){
		global $PHP_SELF;
		$this->search_obj->attributes_default = array('style' => 'width:100%;font-size:10pt;');
		$this->search_obj->search_fields['type']['size'] = 40 ;
		echo "<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
		echo $this->search_obj->getFormStart("$PHP_SELF?send=yes");
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Titel:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $this->search_obj->getSearchField("title");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Typ:") . "</td><td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $this->search_obj->getSearchField("type",array('style' => 'width:*;font-size:10pt;'));
		echo "</td></tr>\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Untertitel:") . " </td>";
		echo "<td  class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $this->search_obj->getSearchField("sub_title");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Semester:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $this->search_obj->getSearchField("sem",array('style' => 'width:*;font-size:10pt;'),$this->sem_browse_data['default_sem']);
		echo "</td></tr>";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Kommentar:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $this->search_obj->getSearchField("comment");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">&nbsp;</td><td class=\"steel1\" align=\"left\" width=\"35%\">&nbsp; </td></tr>\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("DozentIn:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $this->search_obj->getSearchField("lecturer");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Verkn&uuml;pfung:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $this->search_obj->getSearchField("combination",array('style' => 'width:*;font-size:10pt;'));
		echo "</td></tr>\n";
		if ($this->show_class()) {
			echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Bereich:") . " </td>";
			echo "<td class=\"steel1\" align=\"left\" width\"35%\">";
			echo $this->search_obj->getSearchField("scope");
			echo "</td><td class=\"steel1\" colspan=\"2\">&nbsp</td></tr>";
		}
		echo "<tr><td class=\"steel1\" align=\"center\" colspan=\"4\">";
		echo $this->search_obj->getSearchButton();
		echo "&nbsp;";
		echo $this->search_obj->getNewSearchButton();
		echo "&nbsp</td></tr>\n";
		echo $this->search_obj->getFormEnd();
		echo "</table>\n";
	}

	function do_output(){
		if ($this->sem_browse_data['cmd'] == "xts"){
			$this->print_xts();
		} else {
			$this->print_qs();
		}
		$this->print_level();
		if ($this->show_result){
			$this->print_result();
		}
	}

	function print_level(){
		ob_start();
		global $PHP_SELF, $_language_path;
		echo "\n<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=0 width = \"99%\">\n";
		if ($this->sem_browse_data['level'] == "f"){
			echo "\n<tr><td align=\"center\" class=\"steelgraulight\" height=\"40\" valign=\"middle\"><div style=\"margin-top:10px;margin-bottom:10px;\"><font size=\"-1\">";
			if (($this->show_result && count($this->sem_browse_data['search_result'])) || $this->sem_browse_data['cmd'] == "xts") {
				printf(_("Suche im %sEinrichtungsverzeichnis%s"),"<a href=\"$PHP_SELF?level=ev&cmd=qs&sset=0\">","</a>");
				if ($this->show_class()){
					printf(_(" / %sVorlesungsverzeichnis%s"),"<a href=\"$PHP_SELF?level=vv&cmd=qs&sset=0\">","</a>");
				}
			} else {
				printf ("<table align=\"center\" cellspacing=\"10\"><tr><td nowrap align=\"center\"><a href=\"%s?level=ev&cmd=qs&sset=0\"><b>%s</b><br><br><img src=\"".$GLOBALS['ASSETS_URL']."images/institute.jpg\" %s border=\"0\" /></a></td>", $PHP_SELF, _("Suche in Einrichtungen"), $_language_path, tooltip(_("Suche im Einrichtungsverzeichnis")));
				if ($this->show_class()){
					printf ("<td nowrap align=\"center\"><a href=\"%s?level=vv&cmd=qs&sset=0\"><b>%s</b><br><br><img src=\"".$GLOBALS['ASSETS_URL']."images/kommentar.jpg\" %s border=\"0\" /></a></td>", $PHP_SELF, _("Suche im Vorlesungsverzeichnis"), $_language_path,tooltip(_("Suche im Vorlesungsverzeichnis")));
				}
				printf ("</tr></table>");
			}
			echo "</font></div>";
		}
		if ($this->sem_browse_data['level'] == "vv"){
			echo "\n<tr><td align=\"center\">";
			$this->sem_tree->show_entries = $this->sem_browse_data['show_entries'];
			$this->sem_tree->showSemTree();
		}
		if ($this->sem_browse_data['level'] == "ev"){
			echo "\n<tr><td align=\"center\">";
			$this->range_tree->show_entries = $this->sem_browse_data['show_entries'];
			$this->range_tree->showSemRangeTree();
		}
		echo "</td></tr>\n</table>";
		ob_end_flush();
	}

function print_result(){
		ob_start();
		global $_fullname_sql,$_views,$PHP_SELF,$SEM_TYPE,$SEM_CLASS;
		
		if (is_array($this->sem_browse_data['search_result']) && count($this->sem_browse_data['search_result'])) {
			if ($this->sem_browse_data['group_by'] == 1){
				if (!is_object($this->sem_tree)){
						$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
					} else {
						$the_tree =& $this->sem_tree->tree;
					}
				if ($this->sem_browse_data['level'] == "vv" || $this->sem_browse_data['level'] == "sbb"){
					$allowed_ranges = $the_tree->getKidsKids($this->sem_browse_data['start_item_id']);
					$allowed_ranges[] = $this->sem_browse_data['start_item_id'];
					$sem_tree_query = " AND sem_tree_id IN('" . join("','", $allowed_ranges) . "') ";
				}
				$add_fields = "seminar_sem_tree.sem_tree_id AS bereich,";
				$add_query = "LEFT JOIN seminar_sem_tree ON (seminare.Seminar_id = seminar_sem_tree.seminar_id $sem_tree_query)";
			} else if ($this->sem_browse_data['group_by'] == 4){
				$add_fields = "Institute.Name AS Institut,Institute.Institut_id,";
				$add_query = "LEFT JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.Seminar_id) 
							LEFT JOIN Institute ON (Institute.Institut_id = seminar_inst.institut_id)";
			} else {
				$add_fields = "";
				$add_query = "";
			}
					
			$query = ("SELECT seminare.Seminar_id, seminare.status, IF(seminare.visible=0,CONCAT(seminare.Name, ' ". _("(versteckt)") ."'), seminare.Name) AS Name, seminare.metadata_dates,
					 $add_fields" . $_fullname_sql['no_title_short'] ." AS fullname, auth_user_md5.username,
				" . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminare 
				LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent') 
				LEFT JOIN auth_user_md5 USING (user_id) 
				LEFT JOIN user_info USING (user_id) 
				$add_query
				WHERE seminare.Seminar_id IN('" . join("','", array_keys($this->sem_browse_data['search_result'])) . "')");
			$db = new DB_Seminar($query);
			$snap = new DbSnapShot($db);
			$group_field = $this->group_by_fields[$this->sem_browse_data['group_by']]['group_field'];
			$data_fields[0] = "Seminar_id";
			if ($this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field']){
				$data_fields[1] = $this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field'];
			}
			$group_by_data = $snap->getGroupedResult($group_field, $data_fields);
			$sem_data = $snap->getGroupedResult("Seminar_id");
			if ($this->sem_browse_data['group_by'] == 0){
				$group_by_duration = $snap->getGroupedResult("sem_number_end", array("sem_number","Seminar_id"));
				foreach ($group_by_duration as $sem_number_end => $detail){
					if ($sem_number_end != -1 && ($detail['sem_number'][$sem_number_end] && count($detail['sem_number']) == 1)){
						continue;
					} else {
						foreach ($detail['Seminar_id'] as $seminar_id => $foo){
							$start_sem = key($sem_data[$seminar_id]["sem_number"]);
							if ($sem_number_end == -1){
								$sem_number_end = count($this->search_obj->sem_dates)-1;
							}
							for ($i = $start_sem; $i <= $sem_number_end; ++$i){
								if ($this->sem_number === false || (is_array($this->sem_number) && in_array($i,$this->sem_number))){
									if ($group_by_data[$i] && !$tmp_group_by_data[$i]){
										foreach($group_by_data[$i]['Seminar_id'] as $id => $bar){
											$tmp_group_by_data[$i]['Seminar_id'][$id] = true;
										}
									}
									$tmp_group_by_data[$i]['Seminar_id'][$seminar_id] = true;
								}
							}
						}
					}
				}
				if (is_array($tmp_group_by_data)){
					if ($this->sem_number !== false){
						unset($group_by_data);
					}
					foreach ($tmp_group_by_data as $start_sem => $detail){
						$group_by_data[$start_sem] = $detail;
					}
				}
			}

			//release memory
			unset($snap);
			unset($tmp_group_by_data);

			foreach ($group_by_data as $group_field => $sem_ids){
				foreach ($sem_ids['Seminar_id'] as $seminar_id => $foo){
					$name = strtolower(key($sem_data[$seminar_id]["Name"]));
					$name = str_replace("�","ae",$name);
					$name = str_replace("�","oe",$name);
					$name = str_replace("�","ue",$name);
					$group_by_data[$group_field]['Seminar_id'][$seminar_id] = $name;
				}
				uasort($group_by_data[$group_field]['Seminar_id'], 'strnatcmp');
			}

			echo "\n<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
			echo "\n<tr><td class=\"steelgraulight\" colspan=\"2\"><div style=\"margin-top:10px;margin-bottom:10px;\"><font size=\"-1\"><b>&nbsp;"
				. sprintf(_(" %s Veranstaltungen gefunden %s, Gruppierung: %s"),count($sem_data),
				(($this->sem_browse_data['sset']) ? _("(Suchergebnis)") : ""),
				$this->group_by_fields[$this->sem_browse_data['group_by']]['name'])
				. "</b></font></div></td></tr>";


			switch ($this->sem_browse_data["group_by"]){
					case 0:
					krsort($group_by_data, SORT_NUMERIC);
					break;

					case 1:
					uksort($group_by_data, create_function('$a,$b',
							'$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
							$the_tree->buildIndex();
							return (int)($the_tree->tree_data[$a]["index"] - $the_tree->tree_data[$b]["index"]);
							'));
					break;

					case 3:
					uksort($group_by_data, create_function('$a,$b',
							'global $SEM_CLASS,$SEM_TYPE;
							return strnatcasecmp($SEM_TYPE[$a]["name"]." (". $SEM_CLASS[$SEM_TYPE[$a]["class"]]["name"].")",
												$SEM_TYPE[$b]["name"]." (". $SEM_CLASS[$SEM_TYPE[$b]["class"]]["name"].")");'));
					break;
					default:
					uksort($group_by_data, 'strnatcasecmp');
					break;

			}

			foreach ($group_by_data as $group_field => $sem_ids){
				echo "\n<tr><td class=\"steelkante\" colspan=\"2\"><font size=-1><b>";
				switch ($this->sem_browse_data["group_by"]){
					case 0:
					echo $this->search_obj->sem_dates[$group_field]['name'];
					break;

					case 1:
					if ($the_tree->tree_data[$group_field]) {
						echo htmlReady($the_tree->getShortPath($group_field));
					} else {
						echo _("keine Studienbereiche eingetragen");
					}
					break;

					case 3:
					echo htmlReady($SEM_TYPE[$group_field]["name"]." (". $SEM_CLASS[$SEM_TYPE[$group_field]["class"]]["name"].")");
					break;

					default:
					echo htmlReady($group_field);
					break;

				}
				echo "</b></font></td></tr>";
				ob_end_flush();
				ob_start();
				if (is_array($sem_ids['Seminar_id'])){
					while(list($seminar_id,) = each($sem_ids['Seminar_id'])){
						$sem_name = key($sem_data[$seminar_id]["Name"]);
						$sem_number_start = key($sem_data[$seminar_id]["sem_number"]);
						$sem_number_end = key($sem_data[$seminar_id]["sem_number_end"]);
						if ($sem_number_start != $sem_number_end){
							$sem_name .= " (" . $this->search_obj->sem_dates[$sem_number_start]['name'] . " - ";
							$sem_name .= (($sem_number_end == -1) ? _("unbegrenzt") : $this->search_obj->sem_dates[$sem_number_end]['name']) . ")";
						} elseif ($this->sem_browse_data["group_by"]) {
							$sem_name .= " (" . $this->search_obj->sem_dates[$sem_number_start]['name'] . ")";
						}
						echo"<td class=\"steel1\" width=\"66%\"><font size=-1><a href=\"{$this->target_url}?{$this->target_id}={$seminar_id}&send_from_search=1&send_from_search_page="
						. $PHP_SELF. "?keep_result_set=1\">", htmlReady($sem_name), "</a><br>";
						//create Turnus field
						$temp_turnus_string = view_turnus($seminar_id, true, key($sem_data[$seminar_id]["metadata_dates"]),(!$this->sem_browse_data["group_by"]) ? $this->search_obj->sem_dates[$group_field]['beginn'] : false);
						//Shorten, if string too long (add link for details.php)
						if (strlen($temp_turnus_string) >70) {
							$temp_turnus_string = htmlReady(substr($temp_turnus_string, 0, strpos(substr($temp_turnus_string, 70, strlen($temp_turnus_string)), ",") +71));
							$temp_turnus_string .= "...&nbsp;<a href=\"".$this->target_url."?".$this->target_id."=".$seminar_id."&send_from_search=1&send_from_search_page={$PHP_SELF}?keep_result_set=1\">(mehr) </a>";
						}
						echo "</font><font size=\"-2\">" . $temp_turnus_string . "</font></td>";
						echo "<td class=\"steel1\" align=\"right\"><font size=-1>(";
						$doz_name = array_keys($sem_data[$seminar_id]['fullname']);
						$doz_uname = array_keys($sem_data[$seminar_id]['username']);
						if (is_array($doz_name)){
							uasort($doz_name, 'strnatcasecmp');
							$i = 0;
							foreach ($doz_name as $index => $value){
								echo "<a href=\"about.php?username=" . $doz_uname[$index] ."\">" . htmlReady($value) . "</a>";
								if($i != count($doz_name)-1){
									echo ", ";
								}
								if ($i == 3){
									echo "...&nbsp;<a href=\"".$this->target_url."?".$this->target_id."=".$seminar_id."&send_from_search=1&send_from_search_page={$PHP_SELF}?keep_result_set=1\">(mehr) </a>";
									break;
								}
								++$i;
							}
							echo ") </font></td></tr>";
						}
					}
				}
			}
			echo "</table>";
		} elseif($this->search_obj->search_button_clicked && !$this->search_obj->new_search_button_clicked){
			echo "\n<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
			echo "\n<tr><td class=\"steelgraulight\"><font size=\"-1\"><b>&nbsp;" . _("Ihre Suche ergab keine Treffer") ;
			if ($this->search_obj->found_rows === false){
				echo "<br>" . _("(Der Suchbegriff fehlt oder ist zu kurz)");
			}
			echo "</b></font></td></tr>";
			echo "\n</table>";
			$this->sem_browse_data["sset"] = 0;
		}
	ob_end_flush();
	}
}
?>
