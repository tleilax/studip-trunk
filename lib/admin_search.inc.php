<?
# Lifter001: TODO - in progress (session variables)
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/*
admin_search_form.inc.php - Suche fuer die Verwaltungsseiten von Stud.IP.
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de

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

# necessary if you want to include admin_search_form.inc.php in function/method scope
global  $auth, $perm, $sess, $user;

global  $admin_dates_data,
        $archiv_assi_data,
        $archive_kill,
        $i_page,
        $i_view,
        $links_admin_data,
	$list,
        $new_inst,
        $new_sem,
        $sem_create_data,
        $SessSemName,
        $view_mode;


if ($perm->have_perm("autor")) {	// Navigationsleiste ab status "Autor", autors also need a navigation for studygroups

	require_once 'config.inc.php';
	require_once 'lib/dates.inc.php';
	require_once 'lib/functions.php';

	$db=new DB_Seminar;

	$sess->register("links_admin_data");
	$sess->register("sem_create_data");
	$sess->register("admin_dates_data");

	$userConfig=new UserConfig(); // tic #650

	/**
	* We use this helper-function, to reset all the data in the adminarea
	*
	* There are much pages with an own temporary set of data. Please use
	* only this function to add defaults or clear data.
	*/
	function reset_all_data($reset_search_fields = false)
	{
		global $links_admin_data, $sem_create_data, $admin_dates_data, $admin_admission_data,
		$archiv_assi_data, $term_metadata;

		if($reset_search_fields) $links_admin_data='';
		$sem_create_data='';
		$admin_dates_data='';
		$admin_admission_data='';
		$admin_rooms_data='';
		$archiv_assi_data='';
		$term_metadata='';
		$links_admin_data["select_old"]=TRUE;
		$links_admin_data['srch_sem'] =& $GLOBALS['_default_sem'];
	}


	//a Veranstaltung was selected in the admin-search kann viellecht weg
	if (isset($_REQUEST['select_sem_id'])) {
		reset_all_data();
		closeObject();
		openSem($_REQUEST['select_sem_id']);
	//a Veranstaltung which was already open should be administrated
	} elseif (($SessSemName[1]) && ($new_sem)) {
		reset_all_data();
		$links_admin_data["referred_from"]="sem";
	}

	//a Einrichtung was selected in the admin-search
	if ($_REQUEST['admin_inst_id'] && $_REQUEST['admin_inst_id'] != "NULL") {
		reset_all_data();
		closeObject();
		openInst($_REQUEST['admin_inst_id']);
	//a Einrichtung which was already open should be administrated
	} elseif (($SessSemName[1]) && ($new_inst)) {
		reset_all_data();
		$links_admin_data["referred_from"]="inst";
	}

	//Veranstaltung was selected but it is on his way to hell.... we close it at this point
	if (($archive_kill) && ($SessSemName[1] == $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])) {
		//reset_all_data();
		closeObject();
	}

	$list = $_REQUEST['list'];

	//a new session in the adminarea...
	if (($i_page == "adminarea_start.php" && $list) || $_REQUEST['quit']) {
		reset_all_data();
		closeObject();
	} elseif ($i_page== "adminarea_start.php")
		$list=TRUE;

	// start tic #650, sortierung in der userconfig merken
	if ($_REQUEST['adminarea_sortby']) {
		$links_admin_data["sortby"]=$_REQUEST['adminarea_sortby'];
		$list=TRUE;
	}
	if (!isset($links_admin_data["sortby"])) {
		$links_admin_data["sortby"]=$userConfig->getValue($user->id,'LINKS_ADMIN');

	    if ($links_admin_data["sortby"]=="" || $links_admin_data["sortby"]==false) {
			$links_admin_data["sortby"]="VeranstaltungsNummer";
	    }
	} else {
	    $userConfig->setValue($links_admin_data["sortby"],$user->id,'LINKS_ADMIN');
	}

	if (!$_REQUEST['srch_send']) {
		$_REQUEST['show_rooms_check']=$userConfig->getValue($user->id,'LINKS_ADMIN_SHOW_ROOMS');
	} else {
		if (!isset($_REQUEST['show_rooms_check'])) {
			$_REQUEST['show_rooms_check']="off";
		}
		$userConfig->setValue($_REQUEST['show_rooms_check'],$user->id,'LINKS_ADMIN_SHOW_ROOMS');
    }
    // end tic #650

	if ($_REQUEST['view'])
		$links_admin_data["view"]=$_REQUEST['view'];

	if ($_REQUEST['srch_send']) {
		$links_admin_data["srch_sem"]= trim($_REQUEST['srch_sem']);
		$links_admin_data["srch_doz"]= trim($_REQUEST['srch_doz']);
		$links_admin_data["srch_inst"]= trim($_REQUEST['srch_inst']);
		$links_admin_data["srch_fak"]= trim($_REQUEST['srch_fak']);
		$links_admin_data["srch_exp"]= trim($_REQUEST['srch_exp']);
		$links_admin_data["select_old"]=$_REQUEST['select_old'];
		$links_admin_data["select_inactive"]=$_REQUEST['select_inactive'];
		$links_admin_data["srch_on"]=TRUE;
		$list=TRUE;
	}

	if(isset($_REQUEST['links_admin_reset_search_x'])){
		reset_all_data(true);
		$view_mode = 'sem';
		$list = true;
	}

	//if the user selected the information field at Einrichtung-selection....
	if ($_REQUEST['admin_inst_id'] == "NULL")
		$list=TRUE;

	//user wants to create a new Einrichtung
	if ($i_view=="new")
		$links_admin_data='';

	//here are all the pages/views listed, which require the search form for Einrichtungen
	if ($i_page == "admin_institut.php"
			OR ($i_page == "admin_roles.php" AND $links_admin_data["view"] == "statusgruppe_inst")
			OR ($i_page == "admin_lit_list.php" AND $links_admin_data["view"] == "literatur_inst")
			OR $i_page == "inst_admin.php"
			OR ($i_page == "admin_news.php" AND $links_admin_data["view"] == "news_inst")
			OR ($i_page == "admin_modules.php" AND $links_admin_data["view"] == "modules_inst")
			OR ($i_page == "admin_extern.php" AND $links_admin_data["view"] == "extern_inst")
			OR ($i_page == "admin_vote.php" AND $links_admin_data["view"] == "vote_inst")
			OR ($i_page == "admin_evaluation.php" AND $links_admin_data["view"] == "eval_inst")
			) {

		$links_admin_data["topkat"]="inst";
	}

	//here are all the pages/views listed, which require the search form for Veranstaltungen
	if ($i_page == "admin_seminare1.php"
			OR $i_page == "themen.php"
			OR $i_page == "raumzeit.php"
			OR $i_page == "admin_admission.php"
			OR $i_page == "admin_room_requests.php"
			OR ($i_page == "admin_statusgruppe.php" AND $links_admin_data["view"]=="statusgruppe_sem")
			OR ($i_page == "admin_lit_list.php" AND $links_admin_data["view"]=="literatur_sem")
			OR $i_page == "archiv_assi.php"
			OR $i_page == "admin_visibility.php"
			OR $i_page == "admin_aux.php"
			OR $i_page == "admin_lock.php"
			OR $i_page == "copy_assi.php"
			OR $i_page == "adminarea_start.php"
			OR ($i_page == "admin_modules.php" AND $links_admin_data["view"] == "modules_sem")
			OR ($i_page == "admin_news.php" AND $links_admin_data["view"]=="news_sem")
			OR ($i_page == "admin_vote.php" AND $links_admin_data["view"]=="vote_sem")
			OR ($i_page == "admin_evaluation.php" AND $links_admin_data["view"]=="eval_sem")
			) {

		$links_admin_data["topkat"]="sem";
	}
	
	//here are all the pages/views listed, which require the search form for Veranstaltungen
	if ($i_page == "admin_extern.php" AND $links_admin_data["view"] == 'extern_global') {
	
		$links_admin_data["topkat"] = 'global';
	}
	
	//remember the open topkat
	if ($view_mode=="sem")
		$links_admin_data["topkat"]="sem";
	elseif ($view_mode=="inst")
		$links_admin_data["topkat"]="inst";
	if (!$links_admin_data["topkat"])
		$links_admin_data["topkat"]="global";
	if ($view_mode != 'user')
		$view_mode = $links_admin_data["topkat"];

	//Wenn nur ein Institut verwaltet werden kann, immer dieses waehlen (Auswahl unterdruecken)
	if ((!$SessSemName[1]) && ($list) && ($view_mode=="inst")) {
		if (!$perm->have_perm("root") && !$perm->is_fak_admin($user->id)) {
			$db->query("SELECT Institute.Institut_id FROM Institute LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms IN ('admin', 'dozent', 'tutor') ORDER BY Name");

			if ($db->nf() ==1) {
				$db->next_record();
				reset_all_data();
				openInst($db->f("Institut_id"));
			}
		}
	}
}
