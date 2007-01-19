<?php
/*
admin_semester.php - Semester-Verwaltung von Stud.IP.
Copyright (C) 2003 Mark Sievers <mark_sievers2000@yahoo.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("root");

include ("seminar_open.php"); // initialise Stud.IP-Session
require_once("lib/classes/SemesterData.class.php");
require_once("lib/classes/HolidayData.class.php");
// -- here you have to put initialisations for the current page

// Set this to something, just something different...
  $hash_secret = "humptydumpty";
  
// If is set 'cancel', we leave the adminstration form...
 if (isset($cancel_x)) unset ($i_view);

// Start of Output
	include ('include/html_head.inc.php'); // Output of html head
	include ('include/header.php');   // Output of Stud.IP head
	include ('include/links_admin.inc.php');  //Linkleiste fuer admins

	require_once ("msg.inc.php"); //Funktionen fuer Nachrichtenmeldungen
	require_once ("visual.inc.php");
	
	$cssSw=new cssClassSwitcher;
?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=5 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;<?=_("Verwaltung der Semester und Ferien")?></b></td>
</tr>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>


<?php
/*echo "<pre>";
print_r($_POST);
echo "</pre>";
*/
$db = new DB_Seminar;
$semester = new SemesterData;
$holiday = new HolidayData;
//got data, now check, whether data is correct
if ($create=="Anlegen") {
        // print_r($_POST);
        $checkForm = semester_check_form_field($semesterdata); 
    if ($checkForm!=1) {	// Formular war falsch ausgef�llt
        $checkForm = "error�".$checkForm;
        parse_msg($checkForm);
        echo semester_show_new_semester_form($PHP_SELF, $cssSw, $semesterdata);
        unset($new);
	} elseif (semester_check_overlap_semester($semesterdata)) { // Semesterdaten �berschneiden sich!
		$overlap = "error�"._("Semesterdaten &uuml;berschneiden sich!");
		parse_msg($overlap);
		echo semester_show_new_semester_form($PHP_SELF, $cssSw, $semesterdata);
		unset($new);
    } else {	// neu eingf�gen
		// wandle day, month, year in start bzw. enddate um
		$semesterdata = semester_make_single_data_to_timestamp($semesterdata);
		$inserted = $semester->insertNewSemester($semesterdata); 
        unset($checkForm);
        unset($new);
        if ($inserted) {
            $luck1 = "msg�";
            $luck2 = _("Erfolgreich eingef&uuml;gt!");
            $msg = $luck1.$luck2;
			parse_msg($msg);
			echo semester_show_overview($PHP_SELF); // �bersicht
        }
    }

}

// edit existing db-entry
else if ($create=="Bearbeiten") {
     //print_r($_POST);
    // is new Entry correct?
    $checkForm = semester_check_form_field($semesterdata); 
    if ($checkForm!=1) {	// neue Daten sind inkorrekt
        $checkForm = "error�".$checkForm;
        parse_msg($checkForm);
        echo semester_show_new_semester_form($PHP_SELF, $cssSw, $semesterdata, "change");
	} elseif (semester_check_overlap_semester($semesterdata)) { // Semesterdaten �berschneiden sich!
		$overlap = "error�"._("Semesterdaten &uuml;berschneiden sich!");
		parse_msg($overlap);
		echo semester_show_new_semester_form($PHP_SELF, $cssSw, $semesterdata, "change");
    } else {	// alle Daten korrekt, versuche upzudaten
		// wandle day, month, year in start bzw. enddate um
		$semesterdata = semester_make_single_data_to_timestamp($semesterdata);	
        $edited = $semester->updateExistingSemester($semesterdata);
        unset($checkForm);
        if ($edited) {
            $luck1 = "msg�";
            $luck2 = _("Erfolgreich ge&auml;ndert!");
            $msg = $luck1.$luck2;
			parse_msg($msg);
			echo semester_show_overview($PHP_SELF);
        }
    }
}
// nun die beiden Holiday-F�lle 

else if ($create=="Ferienanlegen") {
	$holidaydata = holiday_make_single_data_to_timestamp($holidaydata);
	$holidayCheckData = holiday_check_form_field($holidaydata);
	if ($holidayCheckData!=1) {
		$holidayCheckData = "error�".$holidayCheckData;
		parse_msg($holidayCheckData);
		echo holiday_show_new_holiday_form($PHP_SELF,$cssSw,$holidaydata);
		unset($new);
	} else {
		$insertedHoliday = $holiday->insertNewHoliday($holidaydata);
		if ($insertedHoliday) {
			$msg = "msg�"._("Erfolgreich eingef&uuml;gt");
			parse_msg($msg);
			echo semester_show_overview($PHP_SELF);
		}
	}
}

else if ($create=="Ferienbearbeiten") {
	$holidaydata = holiday_make_single_data_to_timestamp($holidaydata);
	$holidayCheckData = holiday_check_form_field($holidaydata);
	if ($holidayCheckData!=1) {
		$holidayCheckData = "error�".$holidayCheckData;
		parse_msg($holidayCheckData);
		echo holiday_show_new_holiday_form($PHP_SELF,$cssSw,$holidaydata);
		unset($new);
	} else {
		$updatedHoliday = $holiday->updateExistingHoliday($holidaydata);
		if ($updatedHoliday) {
			$msg = "msg�"._("Erfolgreich ge&auml;ndert");
			parse_msg($msg);
			echo semester_show_overview($PHP_SELF);
		}
	}
}
else if (isset($change) && isset($semester_id)) {  // zeige Form mit vordefinierten Werten fuer Semester (edit bzw. update)
    $semesterdata = $semester->getSemesterData($semester_id);
	$semesterdata = semester_make_timestamp_data_to_single_data($semesterdata);
	$editForm = semester_show_new_semester_form($PHP_SELF, $cssSw, $semesterdata, "change"); 
    echo $editForm;
} else if (isset($delete) && isset($semester_id) && (!$confirm)) {		// best�tige L�schen!
	$confirm_form = semester_confirm_delete($semester_id, $PHP_SELF);
	echo $confirm_form;
} else if (isset($delete) && isset($semester_id) && isset($confirm)){	// L�schen best�tigt
	$delete_check = semester_delete($semester_id);
	if ($delete_check) {
		$msg = "msg�"._("Semester wurde gel&ouml;scht");
	} else {
		$msg = "error�"._("Das Semester konnte nicht gel&ouml;scht werden!");
	}
	parse_msg($msg);	// nun wieder �bersicht anzeigen
	echo semester_show_overview($PHP_SELF);
} else if (isset($delete) && isset($holiday_id) && (!isset($confirm))) {	// best�tige Ferien l�schen
	$confirm_form = holiday_confirm_delete($holiday_id, $PHP_SELF);
	echo $confirm_form;
} else if (isset($delete) && isset($holiday_id) && (isset($confirm))) {		// l�schen best�tigt
	$deletedHoliday = holiday_delete($holiday_id);
	if ($deletedHoliday) {
		$msg = "msg�"._("Ferien wurden gel&ouml;scht");
	} else {
		$msg = "error�"._("Ferien konnten nicht gel&ouml;scht werden");	
	}
	parse_msg($msg);		// �bersicht
	echo semester_show_overview($PHP_SELF);
} else if (isset($new)) {	// zeige leere Semester Form (create)
    $newForm = semester_show_new_semester_form($PHP_SELF, $cssSw, 0);
    echo $newForm;
} else if (isset($newHoliday)) {	// zeige leere Holiday Form
	$newHolidayForm = holiday_show_new_holiday_form($PHP_SELF, $cssSw, 0);
	echo $newHolidayForm;
} else if (isset($holidayChange) && isset($holiday_id)) {	// zeige Holiday Form mit vordefinierten Werten
    $holidaydata = $holiday->getHolidayData($holiday_id);
	$holidaydata = holiday_make_timestamp_data_to_single_data($holidaydata);
	$editHolidayForm = holiday_show_new_holiday_form($PHP_SELF, $cssSw, $holidaydata, "change");
	echo $editHolidayForm;
} else if (!isset($checkForm)) {	// es ist nix passiert, zeige �bersicht
    // show all terms 
	echo semester_show_overview($PHP_SELF);
}
echo '</table>';
include ('include/html_end.inc.php');
page_close();
?>