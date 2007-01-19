<?php
/**
* sem_notification.php
*
*
*
*
* @author		Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	studip
* @module		studip
* @package	studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sem_notification.php
//
// Copyright (C) 2005 Peter Thienel <thienel@data-quest.de>,
// data-quest Suchi & Berg GmbH <info@data-quest.de>
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

if (!$MAIL_NOTIFICATION_ENABLE) {
	if ($_REQUEST['view'] != 'notification') {
		page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
		$auth->login_if($auth->auth["uid"] == "nobody");
		include('include/html_head.inc.php'); // Output of html head
		include('include/header.php');   // Output of Stud.IP head
	} else {
		echo '<br><br>';
	}
	require_once ("msg.inc.php");
	$message = _("Die Benachrichtigungsfunktion ist nicht eingebunden. Die Benachrichtigungsfunktion wurde in den Systemeinstellungen nicht freigeschaltet. Wenden Sie sich bitte an die zust&auml;ndigen Administrierenden.");
	parse_window ("error�$message", "�", _("Benachrichtigungsfunktion ist nicht eingebunden!"));
	include ('include/html_end.inc.php');
	exit;
}

if ($_REQUEST['view'] != 'notification') {
	page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
	$auth->login_if($auth->auth["uid"] == "nobody");
	include("seminar_open.php"); // initialise Stud.IP-Session
	// Start of Output
	include('include/html_head.inc.php'); // Output of html head
	include('include/header.php');   // Output of Stud.IP head
	$link_param = '';
} else {
	$link_param = '&view=notification';
}

// -- here you have to put initialisations for the current page
require_once 'lib/functions.php';
require_once("visual.inc.php");
require_once("cssClassSwitcher.inc.php");
require_once("meine_seminare_func.inc.php");
require_once("lib/classes/ModulesNotification.class.php");


function print_module_icons ($m_enabled) {
	foreach ($m_enabled as $m_name => $m_data) {
		switch ($m_name) {
			case 'news' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-news.gif';
				break;
			case 'forum' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-posting.gif';
				break;
			case 'documents' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-disc.gif';
				break;
			case 'schedule' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-uhr.gif';
				break;
			case 'literature' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-lit.gif';
				break;
			case 'elearning_interface' :
			case 'ilias_connect' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-lern.gif';
				break;
			case 'wiki' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-wiki.gif';
				break;
			case 'scm' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-cont.gif';
				break;
			case 'votes' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-vote.gif';
				break;
			case 'basic_data' :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-guest.gif';
				break;
			default :
				$m_icon = $GLOBALS['ASSETS_URL'].'images/icon-posting.gif';
				break;
		}
		echo "<th><img border=\"0\" align=\"center\" src=\"$m_icon\" alt=\"";
		echo $m_data['name'] . "\" title=\"";
		echo $m_data['name'] . "\"></th>";
	}
}


if (isset($_REQUEST['open_my_sem']))
	$_my_sem_open[$_REQUEST['open_my_sem']] = true;
if (isset($_REQUEST['close_my_sem']))
	unset($_my_sem_open[$_REQUEST['close_my_sem']]);

if ($auth->is_authenticated() && $user->id != "nobody" && !$perm->have_perm("admin")) {
	$db = new DB_Seminar();
	
	if (isset($_my_sem_group_field)) {
		$group_field = $_my_sem_group_field;
	} else {
		$group_field = 'not_grouped';
	}
	
	if($group_field == 'sem_tree_id'){
		$add_fields = ',sem_tree_id';
		$add_query = "LEFT JOIN seminar_sem_tree sst ON (sst.seminar_id=seminar_user.seminar_id)";
	}
	
	if($group_field == 'dozent_id'){
		$add_fields = ', su1.user_id as dozent_id';
		$add_query = "LEFT JOIN seminar_user as su1 ON (su1.seminar_id=seminare.Seminar_id AND su1.status='dozent')";
	}
	
	
	$db->query ("SELECT seminare.Name, seminare.Seminar_id, seminare.status as sem_status, seminar_user.gruppe, seminare.visible,
				{$_views['sem_number_sql']} as sem_number, {$_views['sem_number_end_sql']} as sem_number_end $add_fields
				FROM seminar_user LEFT JOIN seminare  USING (Seminar_id)
				$add_query
				WHERE seminar_user.user_id = '$user->id'");
	
	if (!$db->num_rows()) {
		echo "<table class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "<tr><td class=\"blank\">&nbsp;</td></tr>";
		parse_msg("info�" . sprintf(_("Sie haben zur Zeit keine Veranstaltungen abonniert, an denen Sie teilnehmen k&ouml;nnen. Bitte nutzen Sie %s<b>Veranstaltung suchen / hinzuf&uuml;gen</b>%s um neue Veranstaltungen aufzunehmen."), "<a href=\"sem_portal.php\">", "</a>"),
				'�', 'blank', 0);
		echo "</table>";
	}
	
	$modules =& new ModulesNotification();
	// Update der Benachrichtigungsfunktion
	if ($_REQUEST['cmd'] == 'set_sem_notification') {
		if (is_array($_REQUEST['m_checked'])) {
			$modules->setModuleNotification($_REQUEST['m_checked'], 'sem');
		}
	}
	$enabled_modules = $modules->getGlobalEnabledNotificationModules('sem');
	$css =& new cssClassSwitcher();
	$css->enableHover();
	echo $css->GetHoverJSFunction();
	echo "\n<table width=\"75%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";
	if ($_REQUEST['view'] != 'notification') {
		echo '<tr><td class="topic" width=\"100%\">&nbsp;&nbsp;<img src="'.$GLOBALS['ASSETS_URL'].'images/gruppe.gif" alt="Gruppe &auml;ndern" border="0">';
		echo '&nbsp;&nbsp;<b>' . _("Benachrichtigung") . "</td></tr>\n";
		echo "<tr><td class=\"blank\" width=\"100%\" align=\"center\">\n";
		echo "<table width=\"90%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "<form method=\"post\" name=\"notification\" action=\"meine_seminare.php\">\n";
	} else {
		echo "<form method=\"post\" action=\"edit_about.php?view=notification\">\n";
	}
	echo '<tr><td class="blank" colspan="' . (sizeof($enabled_modules) + 3);
	echo "\">&nbsp;</td></tr>\n";
	echo '<tr><td class="blank" align="center" colspan="' . (sizeof($enabled_modules) + 3) . '">';
	echo '<blockquote style="font-size: small; font-weight: bold;">';
	echo _("Stud.IP kann Sie bei �nderungen in den einzelnen Inhaltsbereichen Ihrer Veranstaltungen automatisch per Email informieren.<br>Geben Sie hier an, �ber welche �nderungen Sie informiert werden wollen.");
	?>
	<br><br></blockquote>
	</td>
	</tr>
	<tr>
		<th rowspan="2" colspan="2" width="90%" valign="middle" align="center"><?=_("Veranstaltung")?></th>
		<th width="10%" colspan="<? echo sizeof($enabled_modules) + 1; ?>">&nbsp;</th>
	</tr>
	<tr>
<?
	print_module_icons($enabled_modules);
	echo '<th align="center" style="font-size:small;">';
	if ($GLOBALS['auth']->auth['jscript']) {
		echo _("Alle");
	}else {
		echo '';
	}
	echo "</th></tr>\n";

	$groups = array();
	$my_sem = array();
	while ($db->next_record()){
		$my_sem[$db->f("Seminar_id")] = array("obj_type" => "sem", "name" => $db->f("Name"), "visible" => $db->f("visible"), "gruppe" => $db->f("gruppe"),
		"sem_status" => $db->f("sem_status"),"sem_number" => $db->f("sem_number"),"sem_number_end" => $db->f("sem_number_end") );
		if ($group_field){
			fill_groups($groups, $db->f($group_field), array('seminar_id' => $db->f('Seminar_id'), 'name' => $db->f("Name"), 'gruppe' => $db->f('gruppe')));
		}
	}

	$sem_ids_cs = "'" . implode("','", array_keys($my_sem)) . "'";

	if ($group_field == 'sem_number') {
		correct_group_sem_number($groups, $my_sem);
	} else {
		add_sem_name($my_sem);
	}

	sort_groups($group_field, $groups);
	$group_names = get_group_names($group_field, $groups);
	$m_notifications = $modules->getModuleNotification();
	$c_checked = array();
	$s_count = 0;
	$out = '';
	foreach ($groups as $group_id => $group_members){
		if ($group_field != 'not_grouped') {
			$out .= '<tr><td class="blank" colspan="'.(sizeof($enabled_modules) + 3).'"><img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="1px" height="5px"></td></tr>';
			$out .= '<tr><td class="blue_gradient" valign="top" height="20" colspan="';
			$out .= (sizeof($enabled_modules) + 3) . '">';
			if (isset($_my_sem_open[$group_id])){
				$out .= '<a class="tree" style="font-weight:bold" name="' . $group_id;
				$out .= '" href="' . $PHP_SELF . '?close_my_sem=' . $group_id . $link_param;
				$out .= '#' .$group_id . '" ' . tooltip(_("Gruppierung schlie�en"), true) . '>';
				$out .= '<img src="'.$GLOBALS['ASSETS_URL'].'images/forumgraurunt.gif"   hspace="3" border="0">';
			} else {
				$out .= '<a class="tree"  name="' . $group_id . '" href="' . $PHP_SELF;
				$out .= '?open_my_sem=' . $group_id . $link_param . '#' .$group_id;
				$out .= '" ' . tooltip(_("Gruppierung �ffnen"), true) . '>';
				$out .= '<img src="'.$GLOBALS['ASSETS_URL'].'images/forumgrau.gif"  hspace="3" border="0">';
			}
			if (is_array($group_names[$group_id])){
				$group_name = $group_names[$group_id][1] . " > " . $group_names[$group_id][0];
			} else {
				$group_name = $group_names[$group_id];
			}
			$out .= htmlReady(my_substr($group_name,0,70));
			$out .= "</a></td></tr>\n";
		}

		if (isset($_my_sem_open[$group_id])) {
			$css->resetClass();
			$css->switchClass();
			$s_count++;
			foreach ($group_members as $member){
				$values = $my_sem[$member['seminar_id']];

				$out .= sprintf("<tr%s>\n<td class=\"gruppe%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" border=\"0\" width=\"7\" height=\"12\"></td>",
				$css->getHover(), $values['gruppe']);
				$out .= sprintf("<td%s><font size=\"-1\">&nbsp;<a href=\"seminar_main.php?auswahl=%s\">%s</a>%s</font>",
				$css->getFullClass(), $member['seminar_id'],
				htmlReady(my_substr($values["name"],0,70)),
				(!$values["visible"] ? '&nbsp;' . _("(versteckt)")  : ''));
				$out .= "\n<input type=\"hidden\" ";
				$out .= 'name="m_checked[' . $member['seminar_id'] . "][33]\" value=\"0\"></td>\n";
				$m_count = 0;
				$r_checked = 0;
				foreach ($enabled_modules as $m_name => $m_data) {
					$out .= '<td' . $css->getFullClass() . '>';
					$out .= '<input type="checkbox" name="m_checked[' . $member['seminar_id'] . "][$m_count]\" ";
					$out .= "value=\"" . pow(2, $m_data['id']) . '"';
					if ($modules->isBit($m_notifications[$member['seminar_id']], $m_data['id'])) {
						$out .= ' checked="checked"';
						$c_checked[$m_count]++;
						$r_checked++;
					}
					if ($GLOBALS['auth']->auth['jscript']) {
						$out .= " onClick=\"selectSingle('{$member['seminar_id']}', '$m_count', this)\"";
						$out .= " id=\"{$member['seminar_id']}_{$m_count}_{$group_id}\"";
					}
					$out .= "></td>\n";
					$m_count++;
				}
				if ($GLOBALS['auth']->auth['jscript']) {
					$out .= '<td' . $css->getFullClass() . 'nowrap="nowrap">&nbsp;&nbsp;';
					$out .= "<input type=\"checkbox\" id=\"{$member['seminar_id']}_{$group_id}\"";
					if ($r_checked == sizeof($enabled_modules)) {
						$out .= 'checked="checked"';
					}
					$out .= " onClick=\"selectRow('{$member['seminar_id']}', this)\">";
					$out .= '&nbsp;&nbsp;</td>';
				} else {
					$out .= '<td' . $css->getFullClass() . '>&nbsp</td>';
				}
				$out .= "</tr>\n";
				$css->switchClass();
			}
		}
	}

	?>
	<script type="text/javascript">
		<!--
			function selectSingle (sem_id, m_id, c_box) {
				var i;
				g_ids = new Array(<? echo "'" . implode("','", array_keys($groups)) . "'"; ?>);
				for (i = 0; i < g_ids.length; i++) {
					if (document.getElementById(sem_id + '_' + m_id + '_' + g_ids[i])) {
						document.getElementById(sem_id + '_' + m_id + '_' + g_ids[i]).checked = c_box.checked;
						checkRow(sem_id, g_ids[i]);
					}
				}
			}

			function selectRow (sem_id, c_box) {
  			var i;
				var n;
				g_ids = new Array(<? echo "'" . implode("','", array_keys($groups)) . "'"; ?>);
				for (n = 0; n < g_ids.length; n++) {
					if (document.getElementById(sem_id + '_' + g_ids[n])) {
						document.getElementById(sem_id + '_' + g_ids[n]).checked = c_box.checked;
					}
	  			for (i = 0; i < <? echo sizeof($enabled_modules); ?>; i++) {
						if (document.getElementById(sem_id + '_' + i + '_' + g_ids[n])) {
	  	    		document.getElementById(sem_id + '_' + i + '_' + g_ids[n]).checked = c_box.checked;
						}
					}
				}
			}

			function selectColumn (mod_id, c_box) {
				var i;
				sem_ids = new Array(<? echo $sem_ids_cs; ?>);
				for (i = 0; i < sem_ids.length; i++) {
					selectSingle(sem_ids[i], mod_id, c_box)
				}
			}

			function selectAll (mod_count, c_box) {
				var i;
				var c_checked;
				for (i = 0; i < mod_count; i++) {
					document.getElementById('mod_row_' + i).checked = c_box.checked;
					selectColumn(i, document.getElementById('mod_row_' + i));
				}
			}

			function checkRow (sem_id, g_id) {
				var i;
				var n = 0;
				var m_count = <? echo sizeof($enabled_modules); ?>;
				for (i = 0; i < m_count; i++) {
					if (document.getElementById(sem_id + '_' + i + '_' + g_id).checked) {
						n++;
					}
				}
				if (n == m_count) {
					document.getElementById(sem_id + '_' + g_id).checked = 1;
				} else {
					document.getElementById(sem_id + '_' + g_id).checked = 0;
				}
			}
	// -->
	</script>
	<?
	echo $out;
	if ($group_field != 'not_grouped') {
		echo '<tr><td class="blank" colspan="'.(sizeof($enabled_modules) + 3).'"><img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="1px" height="5px"></td></tr>';
	}

	echo '<tr><th colspan="2">&nbsp;</th>';
	print_module_icons($enabled_modules);
	if ($GLOBALS['auth']->auth['jscript']) {
		echo '<th align="center" style="font-size:small;">' . _("Alle") . '</th>';
	} else {
		echo '<th>&nbsp;</th>';
	}
	echo "</tr>\n";
	if ($GLOBALS['auth']->auth['jscript']) {
		echo '<tr><th colspan="2" align="right" style="font-size:small;">';
		echo _("Benachrichtigung f�r alle aufgelisteten Veranstaltungen:") . '</th>';
		for ($i = 0; $i < sizeof($enabled_modules); $i++) {
			echo "<th><input type=\"checkbox\" id=\"mod_row_$i\" ";
			if ($c_checked[$i] == $db->num_rows()) {
				echo 'checked="checked"';
			}
			echo "onClick=\"selectColumn($i, this)\"></th>";
		}
		echo '<th><input type="checkbox" onClick="selectAll(';
		echo sizeof($enabled_modules) . ', this)"';
		if (array_sum($c_checked) == $db->num_rows() * sizeof($enabled_modules)) {
			echo ' checked="checked"';
		}
		echo "></th></tr>\n";
	}
	echo '<tr><td class="blank" align="center" colspan="';
	echo (sizeof($enabled_modules) + 3) . '"><br>';
	echo "<input type=\"image\" " . makeButton("uebernehmen", "src");
	if ($_REQUEST['view'] != 'notification') {
		echo " border=\"0\" value=\"absenden\">&nbsp; <a href=\"$PHP_SELF\">";
	} else {
		echo " border=\"0\" value=\"absenden\">&nbsp; <a href=\"$PHP_SELF?view=notification\">";
	}
	echo '<img ' . makeButton('zuruecksetzen', 'src') . ' border="0"';
	echo tooltip(_("zur�cksetzen"));
	echo '><input type="hidden" name="cmd" value="set_sem_notification"><br />&nbsp; </td></tr></form>';
	echo "</table>\n";
}

if ($_REQUEST['view'] != 'notification') {
	echo "</td></tr></table>\n";
	
	include ('include/html_end.inc.php');
  // Save data back to database.
  page_close();
}

// <!-- $Id$ -->
?>