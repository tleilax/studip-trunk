<?php
/*
teilnehmer.php - Anzeige der Teilnehmer eines Seminares
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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

include "lib/seminar_open.php"; //hier werden die sessions initialisiert

require_once('lib/classes/DataFieldEntry.class.php');
require_once('lib/classes/ZebraTable.class.php');
require_once('lib/classes/AuxLockRules.class.php');
require_once('lib/dates.inc.php');

$GLOBALS['CURRENT_PAGE'] = $SessSemName["header_line"]. " - " . _("Zusatzangaben");

if (!$_REQUEST['display_type']) {
	// Start  of Output
	include ("lib/include/html_head.inc.php"); // Output of html head
	include ("lib/include/header.php");   //hier wird der "Kopf" nachgeladen

	checkObject();
	checkObjectModule("participants");

	include ("lib/include/links_openobject.inc.php");
}

$sem_id = $SessSemName[1];
$sem_type = $SessSemName["art_num"];
$user_id = $user->id;
$rule = AuxLockRules::getLockRuleBySemId($sem_id);

function filterDatafields($entries) {
	global $rule;

	$new_entries = array();
	if (isset($rule)) {
		foreach ($entries as $key => $val) {
			if ($rule['attributes'][$key] == 1) {
				$new_entries[$key] = $val;
			}
		}
	}

	return $new_entries;
}

function get_aux_data() {
	global $sem_id, $user, $sem_type, $rule;
	$db = new DB_Seminar();
	$entries[0] = filterDatafields(DataFieldStructure::getDataFieldStructures('usersemdata'));
	$entries[1] = filterDatafields(DataFieldStructure::getDataFieldStructures('user'));

	$entry_data = array();
	for ($i = 0; $i <= 1; $i++) {
		foreach ($entries[$i] as $id => $entry) {
			$header[$id] = $entry->getName();
			$entry_data[$id] = '';
		}
	}

	$semFields = filterDataFields(AuxLockRules::getSemFields());
	foreach ($semFields as $id => $name) {
		$header[$id] = $name;
		$entry_data[$id] = '';
	}

	$data = array();
	$db->query($query = "SELECT *, seminare.VeranstaltungsNummer as vanr, seminare.Name as vatitle, auth_user_md5.Vorname, auth_user_md5.Nachname FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN seminare ON (seminar_user.Seminar_id = seminare.Seminar_id) WHERE seminar_user.Seminar_id = '$sem_id' AND (seminar_user.status = 'autor' OR seminar_user.status = 'user')");
	while ($db->next_record()) {
		$data[$db->f('user_id')]['entry'] = $entry_data;
		$data[$db->f('user_id')]['fullname'] = $db->f('Vorname').' '.$db->f('Nachname');
		$data[$db->f('user_id')]['username'] = $db->f('username');

		$entries[0] = filterDatafields(DataFieldEntry::getDataFieldEntries(array($db->f('user_id'), $sem_id), 'usersemdata'));
		$entries[1] = filterDatafields(DataFieldEntry::getDataFieldEntries($db->f('user_id'), 'user'));

		for ($i = 0; $i <= 1; $i++) {
			foreach ($entries[$i] as $id => $entry) {
				if ($entry->getType() == 'bool') {
					if ($entry->getValue()) {
						$text = _("ja");
					} else {
						$text = _("nein");
					}
					$data[$db->f('user_id')]['entry'][$id] = $text;
				} else {
					$data[$db->f('user_id')]['entry'][$id] = $entry->getValue();
				}
			}
		}

		foreach ($semFields as $key => $name) {
			if ($key == 'vadozent') {
				if (!isset($vadozent)) {
					$db2 = new DB_Seminar();
					$db2->query($query = "SELECT ".$GLOBALS['_fullname_sql']['full']." as fullname FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.status = 'dozent' AND seminar_user.Seminar_id = '$sem_id'");
					$va_dozent = '';
					$first = true;
					while ($db2->next_record()) {
						if (!$first) $vadozent .= ', ';
						$vadozent .= $db2->f('fullname');
						$first = false;
					}
				}

				$data[$db->f('user_id')]['entry'][$key] = $vadozent;
			} else if ($key == 'vasemester') {
				if (!isset($vasemester)) {
					$vasemester = get_semester($sem_id);
				}
				$data[$db->f('user_id')]['entry'][$key] = $vasemester;
			} else {
				$data[$db->f('user_id')]['entry'][$key] = $db->f($key);
			}
		}
	}

	$order = $rule['order'];
	asort($order, SORT_NUMERIC);

	$new_header = array();
	foreach ($order as $key => $dontcare) {
		if (isset($header[$key])) {
			$new_header[$key] = $header[$key];
		}
	}

	return array('aux' => $data, 'header' => $new_header);
}

function aux_csv() {
	$sepp = ';';
	$aux_data = get_aux_data();

	$max = count($aux_data['header']);
	$max++;

	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=export.csv");

	$data = '"Name"'.$sepp;

	foreach ($aux_data['header'] as $id => $name) {
		$data .= '"'.$name.'"'.$sepp;
	}
	$data .= "\n";

	foreach ($aux_data['aux'] as $uid => $cur_user) {
		$data .= '"'.$cur_user['fullname'].'"'.$sepp;
		foreach ($aux_data['header'] as $showkey => $dontcare) {
		//foreach ($cur_user['entry'] as $id => $value) {
			$data .= '"'.$cur_user['entry'][$showkey].'"'.$sepp;
		}

		$data .= "\n";
	}

	echo $data;
}

function aux_rtf() {
	$aux_data = get_aux_data();

	$max = count($aux_data['header']) + 1;
	$step = floor(8305 / $max);
	$cellx = '\cellx'.join('\cellx', range($step, $max * $step, $step))."\n";

	header("Content-Type: application/rtf");
	header("Content-Disposition: attachment; filename=export.rtf");


	?>
{\rtf1\ansi\ansicpg1252\deff0\deflang1031{\fonttbl{\f0\fnil\fcharset0 Times New Roman;}}
{\pard
\trowd<?= $cellx ?>
\pard\intbl Name\cell
<? foreach ($aux_data['header'] as $name) : ?>
\pard\intbl <?= $name ?>\cell
<? endforeach ?>
\row

<? foreach ($aux_data['aux'] as $cur_user) : ?>
\trowd<?= $cellx ?>
\pard\intbl <?= $cur_user['fullname'] ?>\cell
<? foreach ($aux_data['header'] as $showkey => $dontcare) : ?>
\pard\intbl <?= $cur_user['entry'][$showkey] ?>\cell
<? endforeach ?>
\row

<? endforeach ?>
}
}
<?
}

function aux_html() {
	global $zt;

	$data = get_aux_data();

	echo $zt->openRow();
	$cell = '<form action="'.$PHP_SELF.'"><select name="display_type"><option value="rtf">RTF</option><option value="csv">Excel kompatibel</option></select>';
	$cell .= '&nbsp;&nbsp;&nbsp;<input type="image" '.makebutton('export','src').' style="{vertical-align: middle}"></form>';
	echo $zt->cell($cell, array('colspan' => '20', 'class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openHeaderRow();
	echo $zt->cell('<font size="-2">Name</font>', array('align' => 'center', 'valign' => 'top'));
	foreach ($data['header'] as $id => $name) {
		echo $zt->cell('<font size="-2">'.$name.'</font>', array('align' => 'left', 'valign' => 'top'));
	}
	echo $zt->closeRow();

	// einzelne Nutzerdaten ausgeben
	foreach ($data['aux'] as $uid => $cur_user) {
		echo $zt->openRow();
		echo $zt->cell('<font size="-2">&nbsp;<a href="about.php?username='.$cur_user['username'].'">'.$cur_user['fullname'].'</a></font>');
		foreach ($data['header'] as $showkey => $dontcare) {
		//foreach ($cur_user['entry'] as $id => $value) {
			echo $zt->cell('<font size="-2">'. $cur_user['entry'][$showkey] . '</font>', array('align' => 'left'));
		}
		echo $zt->closeRow();
	}

	echo $zt->close();
}

function aux_enter_data() {
	global $user_id, $sem_id, $user, $sem_type, $rule, $zt, $perm, $ct;
	global $datafield_id, $datafield_type, $datafield_sec_range_id, $datafield_content;

	unset($msgs);

	if (is_array($datafield_id)) {
		$ffCount = 0; // number of processed form fields
		foreach ($datafield_id as $i=>$id) {
			$struct = new DataFieldStructure($zw = array("datafield_id"=>$id, 'type'=>$datafield_type[$i]));
			$entry  = DataFieldEntry::createDataFieldEntry($struct, array($user_id, $datafield_sec_range_id[$i]));
			$numFields = $entry->numberOfHTMLFields(); // number of form fields used by this datafield
			if ($datafield_type[$i] == 'bool' && $datafield_content[$ffCount] != $id) { // unchecked checkbox?
				$entry->setValue('');
				$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
			}
			elseif ($numFields == 1)
				$entry->setValue($datafield_content[$ffCount]);
			else
				$entry->setValue(array_slice($datafield_content, $ffCount, $numFields));
			$ffCount += $numFields;

			$entry->structure->load();
			if ($entry->isValid()) {
				$entry->store();
			}	else {
				$invalidEntries[$struct->getID()] = $entry;
			}
		}
		/*// change visibility of role data
			foreach ($group_id as $groupID)
			setOptionsOfStGroup($groupID, $u_id, ($visible[$groupID] == '0') ? '0' : '1');*/
		if (is_array($invalidEntries))
			$msgs[] = 'error�<b>'. _("Sie haben fehlerhafte Eingaben gemacht (siehe unten). Ihre anderen Angaben wurden jedoch gespeichert.") .'</b>';
		else
			$msgs[] = 'msg�'. _("Die Daten wurden gespeichert!");
	}

	echo $ct->cell('&nbsp;', array('class' => 'blank', 'colspan' => '2'));

	if (is_array($msgs)) {
		foreach ($msgs as $msg) {
			parse_msg($msg,'�', "blank", 4, true);
		}
	}

	my_info( _("Bitte f�llen Sie die unten aufgef�hrten Felder - soweit m�glich und zutreffend - aus.").'<br/>'
		._("Sie k�nnen Ihre Daten noch nachtr�glich �ndern, bis die Liste geschlossen wird."), 'blank', '3', true);
	echo $ct->closeCell();
	echo $ct->closeRow();
	echo $ct->openRow();
	echo $ct->cell('&nbsp;', array('class' => 'blank'));
	echo $ct->openCell();

	$entries = filterDatafields(DataFieldEntry::getDataFieldEntries(array($user_id, $sem_id), 'usersemdata'));

	echo '<form action="'.$PHP_SELF.'" method="post">';
	foreach ($entries as $id => $entry) {
		if ($entry->structure->accessAllowed($perm)) {
			$color = 'black';
			if (isset($invalidEntries[$id])) {
				$color = 'red';
				$entry = $invalidEntries[$id];  // keep wrong entry to show it in corresponding form field
			}
			echo $zt->openRow();
			$data = "<font color='$color'>&nbsp;" . $entry->getName() . "</font></b>";
			echo $zt->cell($data);

			$data = $entry->getHTML('datafield_content[]', $entry->structure->getID());
			$data .= '<input type="HIDDEN" name="datafield_id[]" value="'.$entry->structure->getID().'">';
			$data .= '<input type="HIDDEN" name="datafield_type[]" value="'.$entry->getType().'">';
			$data .= '<input type="HIDDEN" name="datafield_sec_range_id[]" value="'.$sem_id.'">';
			echo $zt->cell($data);

			echo $zt->closeRow();
		}
	}

	echo $zt->openRow();
	echo $zt->cell('<br/><input type="image" '.makebutton('uebernehmen', 'src').'><br/><br/>', array('colspan' => '20', 'align' => 'center'));
	echo $zt->close();
}

$ct = new ContainerTable(array('width' => '100%', 'class' => 'blank'));
$zt = new ZebraTable(array('width' => '100%', 'padding' => '2'));

switch ($_REQUEST['display_type']) {
	case 'rtf':
		aux_rtf();
		page_close(NULL);
		break;

	case 'csv':
		aux_csv();
		page_close(NULL);
		break;

	default:
		echo $ct->openRow();
		echo $ct->cell('&nbsp;<b>'. _("Zusatzangaben") .'</b>', array('class' => 'topic', 'colspan' => '20'));
		echo $ct->closeRow();

		echo $ct->openRow(array('class' => 'blank'));
		echo $ct->cell('<br/>', array('colspan' => '20'));
		echo $ct->closeRow();

		echo $ct->openRow();
		echo $ct->cell('&nbsp;', array('class' => 'blank'));
		echo $ct->openCell();
		if ($rechte) {
			aux_html();
		} else {
			aux_enter_data();
		}
		echo $ct->closeCell();
		echo $ct->cell('&nbsp;', array('class' => 'blank'));
		echo $ct->closeRow();

		echo $ct->openRow(array('class' => 'blank'));
		echo $ct->cell('<br/>', array('colspan' => '20'));
		echo $ct->closeRow();

		echo $ct->close();
		page_close();
		break;
}

?>
