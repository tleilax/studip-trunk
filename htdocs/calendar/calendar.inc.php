<?
/**
* calendar.inc.php
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>
* @version		$Id$
* @access		public
* @modulegroup	calendar
* @module		calendar
* @package	calendar
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// calendar.inc.php
//
// Copyright (c) 2003 Peter Tienel <pthienel@web.de> 
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


require_once($ABSOLUTE_PATH_STUDIP . "config.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");
require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/calendar_func.inc.php");
require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/calendar_visual.inc.php");
require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/calendar_misc_func.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR
		. "/lib/driver/$CALENDAR_DRIVER/CalendarDriver.class.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEvent.class.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/SeminarEvent.class.php");

// -- hier muessen Seiten-Initialisierungen passieren --
// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

// bei Einsprung ohne $cmd wird im Header eine Erlaeuterung ausgegeben
if (!$cmd && !$atime)
	$intro = TRUE;

// Wird kein timestamp an das Skript uebergeben, benutze aktuellen
if (!$atime && !$termin_id)
	$atime = time();
	
if (isset($mod_s_x)) $mod = 'SINGLE';
if (isset($mod_d_x)) $mod = 'DAILY';
if (isset($mod_w_x)) $mod = 'WEEKLY';
if (isset($mod_m_x)) $mod = 'MONTHLY';
if (isset($mod_y_x)) $mod = 'YEARLY';

if ($mod)
	$cmd = 'edit';

if ($store_x || $change_x)
	$cmd = 'add';

if ($del_x && $termin_id)
	$cmd = 'del';

if ($back_recur_x)
	unset($set_recur_x);

if ($cancel_x) {
	if ($calendar_sess_control_data['source']) {
		$destination = $calendar_sess_control_data['source'];
		$calendar_sess_control_data['source'] = '';
		page_close();
		header("Location: $destination");
		exit;
	}
	if ($calendar_sess_control_data['view_prv'])
		$cmd = $calendar_sess_control_data['view_prv'];
	else
		$cmd = $calendar_user_control_data['view'];
}

// Zeitbereich eingrenzen
if (isset($atime) && ($atime < 0 || $atime > 2114377200))
	$atime = time();

// Datum fuer "Gehe-zu-Funktion" checken
if (check_date($jmp_m, $jmp_d, $jmp_y))
	$atime = mktime(12, 0, 0, $jmp_m, $jmp_d, $jmp_y);
else {
	$jmp_d = date('j', $atime);
	$jmp_m = date('n', $atime);
	$jmp_y = date('Y', $atime);
}

// Benutzereinstellungen uebernehmen
if ($cmd_cal == 'chng_cal_settings') {
	$calendar_user_control_data = array(
		'view'             => $cal_view,
		'start'            => $cal_start,
		'end'              => $cal_end,
		'step_day'         => $cal_step_day,
		'step_week'        => $cal_step_week,
		'type_week'        => $cal_type_week,
		'holidays'         => $cal_holidays,
		'sem_data'         => $cal_sem_data,
		'link_edit'        => $cal_link_edit,
		'bind_seminare'    => $calendar_user_control_data['bind_seminare'],
		'ts_bind_seminare' => $calendar_user_control_data['ts_bind_seminare'],
		'delete'           => $cal_delete
	);
}

// delete all expired events and count events
$db_control = new CalendarDriver();
if ($cmd == 'add') {
	$expire_delete = mktime(date('G', time()), date('i', time()), 0,
			date('n', time()) - $calendar_user_control_data['delete'],
			date('j', time()), date('Y', time()));
	$db_control->deleteFromDatabase('EXPIRED', '', 0, $expire_delete);
}
$db_control->openDatabase('COUNT', 'CALENDAR_EVENTS');
$count_events = $db_control->getCountEvents();

if (isset($calendar_user_control_data['number_of_events'])) {
	unset($calendar_user_control_data['number_of_events']);
	$calendar_user_control_data['delete'] = 6;
}

$db_check =& new DB_Seminar();

// updating seminars selected by the user
$db_check->query("SELECT Seminar_id, mkdate FROM seminar_user WHERE user_id='$user->id' ORDER BY mkdate DESC");
while ($db_check->next_record()
		&& ($db_check->f('mkdate') > $calendar_user_control_data['ts_bind_seminare']
		|| $db_check->f('mkdate') == 0)) {
	$calendar_user_control_data['bind_seminare'][$db_check->f('Seminar_id')] = 'TRUE';
}
$calendar_user_control_data['ts_bind_seminare'] = time();

// Wenn "Einbinden-Formular" abgeschickt wurde, dann ...["bind_seminare"] erneuern
if ($sem)
	$calendar_user_control_data['bind_seminare'] = $sem;
if (is_array($calendar_user_control_data['bind_seminare']))
	$bind_seminare = array_keys($calendar_user_control_data['bind_seminare'], 'TRUE');
else
	$bind_seminare = '';

if ($cmd == '') {
	if ($termin_id)
		// wird eine termin_id uebergeben immer in den Bearbeiten-Modus
		$cmd = 'edit';
	else
		$cmd = $calendar_user_control_data['view'];
}

if (!$calendar_sess_control_data)
	$sess->register('calendar_sess_control_data');

$accepted_vars = array('start_m', 'start_h', 'start_day', 'start_month', 'start_year', 'end_m',
											'end_h',	'end_day', 'end_month', 'end_year',	'exp_day', 'exp_month',
											'exp_year', 'cat', 'priority', 'txt', 'content', 'loc', 'linterval_d',
											'linterval_w', 'wdays', 'type_d', 'type_m', 'linterval_m2', 'sinterval_m',
											'linterval_m1', 'wday_m', 'day_m', 'type_y', 'sinterval_y', 'wday_y',
											'day_y', 'month_y1', 'month_y2', 'atime', 'termin_id', 'exp_c', 'via',
											'cat_text', 'mod_prv', 'exc_day', 'exc_month', 'exc_year', 'exceptions',
											'exc_delete', 'add_exc_x', 'del_exc_x', 'exp_count', 'wholeday');

if ($cmd == 'add' || $cmd == 'edit') {
	if (!isset($calendar_sess_forms_data))
		$sess->register('calendar_sess_forms_data');
	if (!empty($HTTP_POST_VARS)){
		// Formulardaten uebernehmen
		foreach ($HTTP_POST_VARS as $key => $value) {
			if(in_array($key, $accepted_vars))
				$calendar_sess_forms_data[$key] = $value;
		}
	}
	else
		$calendar_sess_control_data['mod'] = '';
}
else {
	unset($calendar_sess_forms_data);
	$sess->unregister('calendar_sess_forms_data');
}

if ($source_page && ($cmd == 'edit' || $cmd == 'add' || $cmd == 'delete')) {
	$calendar_sess_control_data['source'] = rawurldecode($source_page);
}

// Seitensteuerung
switch ($cmd) {
	case 'showday':
		$calendar_sess_control_data['view_prv'] = $cmd;
		$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
		break;
	case 'add':
		switch($calendar_sess_control_data['view_prv']){
			case 'showday':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
				break;
			case 'showweek':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Wochenansicht");
				break;
			case 'showmonth':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Monatsansicht");
				break;
			case 'showyear':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Jahresansicht");
		}
		break;
	case 'del':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
		$atermin =& new DbCalendarEvent($termin_id);
		$atermin->delete();
		
		if($calendar_sess_control_data['source']){
			$destination = $calendar_sess_control_data['source'];
			$calendar_sess_control_data['source'] = '';
			header("Location: $destination");
			page_close();
			die;
		}
		
		if(!empty($calendar_sess_control_data['view_prv']))
			$cmd = $calendar_sess_control_data['view_prv'];
		else
			$cmd = 'showday';
		
		unset($calendar_sess_forms_data);
		$sess->unregister('calendar_sess_forms_data');
		break;
		
	case 'showweek':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Wochenansicht");
		$calendar_sess_control_data['view_prv'] = $cmd;
		break;
		
	case 'showmonth':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Monatsansicht");
		$calendar_sess_control_data['view_prv'] = $cmd;
		break;
		
	case 'showyear':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Jahresansicht");
		$calendar_sess_control_data['view_prv'] = $cmd;
		break;
	
	case 'export':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Termindaten importieren, exportieren und synchronisieren");
		break;
		
	case 'bind':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Veranstaltungstermine einbinden");
		break;
		
	case 'edit':
		if ($termin_id) {
			
			if ($evtype == 'sem') {
				$atermin =& new SeminarEvent();
				if (!$atermin->restore($termin_id)) {
					// its something wrong... better to go back to the last view
					page_close();
					header("Location: " . $PHP_SELF	. "?cmd="
							. $calendar_sess_control_data['view_prv'] . "&atime=$atime");
					exit;
				}
				$title = _("Mein pers&ouml;nlicher Terminkalender - Veranstaltungstermin");
			}
			else {	
				$atermin =& new DbCalendarEvent($termin_id);
				if (!$mod)
					$mod = $atermin->getRepeat('rtype');
				$title = _("Mein pers&ouml;nlicher Terminkalender - Termin bearbeiten");
			}
		}
		else {
			
			// call from dayview for new event -> set default values
			if ($atime && empty($HTTP_POST_VARS)) {
				if ($devent) {
					$properties = array(
							'DTSTART' => mktime(0, 0, 0, date('n', $atime), date('j', $atime),
									date('Y', $atime)),
							'DTEND'   => mktime(23, 59, 59, date('n', $atime),
									date('j', $atime), date('Y', $atime)),
							'SUMMARY' => _("Keine Titel"),
							'STUDIP_CATEGORY' => 1,
							'CATEGORIES' => '',
							'CLASS' => 'PRIVATE');
					$atermin =& new CalendarEvent($properties);
					$atermin->setDayEvent(TRUE);
				}
				else {
					$properties = array(
							'DTSTART' => $atime,
							'DTEND'   => mktime(date('G', $atime) + 1, date('i', $atime), 0,
									date('n', $atime), date('j', $atime), date('Y', $atime)),
							'SUMMARY' => _("Keine Titel"),
							'STUDIP_CATEGORY' => 1,
							'CATEGORIES' => '',
							'CLASS' => 'PRIVATE');
					$atermin =& new CalendarEvent($properties);
				}
				
				$atermin->setRepeat(array('rtype' => 'SINGLE'));
			}
			else {
				$properties = array();
				$atermin =& new CalendarEvent($properties);
			}
		}
		if (empty($HTTP_POST_VARS)) {
			get_event_properties($calendar_sess_forms_data, $atermin);
		}
		else {
			$err = check_form_values($calendar_sess_forms_data);
			if (empty($err)) {
				set_event_properties($calendar_sess_forms_data, $atermin, $mod);
			}
			else {
				if ($back_recur_x)
					$set_recur_x = 1;
				elseif ($set_recur_x && $err['set_recur'])
					$mod = $mod_prv;
				elseif ($set_recur_x)
					unset($set_recur_x);
			}
		}
		extract($calendar_sess_forms_data, EXTR_OVERWRITE);
		
		$title = _("Mein pers&ouml;nlicher Terminkalender - Termin anlegen/bearbeiten");
		break;
}

// add an event to database *********************************************************

if ($cmd == 'add') {
	$atermin =& new DbCalendarEvent();
	set_event_properties($calendar_sess_forms_data, $atermin, $calendar_sess_forms_data['mod_prv']);
	// Ueberpruefung der Formulareingaben
	$err = check_form_values($calendar_sess_forms_data);
	// wenn alle Daten OK, dann Termin anlegen oder, wenn termin_id vorhanden,
	// updaten
	if (empty($err) && $count_events < $CALENDAR_MAX_EVENTS) {
		// wird eine termin_id uebergeben, wird ein update durchgefuehrt
		if ($termin_id) {
			$termin_old =& new DbCalendarEvent($termin_id);
			$termin_old->update($atermin);
			$termin_old->save();
		}
		else {
			$atermin->save();
			$atime = $atermin->getStart();
		}
				
		if ($calendar_sess_control_data['source']) {
			$destination = $calendar_sess_control_data['source'];
			$calendar_sess_control_data['source'] = '';
			page_close();
			header("Location: $destination");
			exit;
		}
		
		if (!empty($calendar_sess_control_data['view_prv']))
			$cmd = $calendar_sess_control_data['view_prv'];
		else
			$cmd = 'showday';
			
	}
	// wrong data? -> switch back to edit mode
	else {
		$cmd = 'edit';
		$mod = $mod_prv ? $mod_prv : 'SINGLE';
		if ($back_recur_x) {
			$set_recur_x = 1;
			unset($back_recur_x);
		}
	//	else
		//	unset($set_recur_x);
	}
	unset($calendar_sess_forms_data);
	$sess->unregister('calendar_sess_forms_data');
}

// Tagesuebersicht anzeigen ***************************************************

if ($cmd == 'showday') {
	
	$d_start = $calendar_user_control_data['start'];
	$d_end = $calendar_user_control_data['end'];

	$at = date('G', $atime);
	if ($at >=  $d_start && $at <= $d_end || !$atime) {
		$st = $d_start;
		$et = $d_end;
	}
	elseif ($at < $d_start) {
		$st = 0;
		$et = $d_start + 2;
	}
	else {
		$st = $d_end - 2;
		$et = 23;
	}
	
	include_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarDay.class.php");
	$aday =& new DbCalendarDay($atime);
	$aday->bindSeminarEvents($bind_seminare);
	$tab = createDayTable($aday, $st, $et, $calendar_user_control_data['step_day'],
							TRUE, TRUE, FALSE, 70, 20, 3, 1);
	
	include($RELATIVE_PATH_CALENDAR . "/views/day.inc.php");

}

// Wochenuebersicht anzeigen **************************************************

if ($cmd == 'showweek') {

	$w_start = $calendar_user_control_data['start'];
	$w_end = $calendar_user_control_data['end'];
	
	if (isset($wtime))
		$at = (int) $wtime;
	if (!($at > 0 && $at < 24))
		$at = $w_start;
	if ($at >=  $w_start && $at <= $w_end) {
		$st = $w_start;
		$et = $w_end;
	}
	else if ($at < $w_start) {
		$st = 0;
		$et = $w_start + 2;
	}
	else {
		$st = $w_end - 2;
		$et = 23;
	}
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/week.inc.php");

}

// Monatsuebersicht anzeigen **************************************************

if ($cmd == 'showmonth') {

	include_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarMonth.class.php");
	
	$amonth =& new DbCalendarMonth($atime);
	$calendar_sess_forms_data['bind_seminare'] = '';
	$amonth->bindSeminarEvents($bind_seminare);
	$amonth->sort();
	
	if ($mod == 'compact' || $mod == 'nokw') {
		$hday['name'] = '';
		$hday['col'] = '';
		$width = '20';
		$height = '20';
	}
	else {
		$width = '90';
		$height = '80';
	}
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/month.inc.php");
	
}

// Jahresuebersicht ***********************************************************

if ($cmd == 'showyear') {

	include_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarYear.class.php");
	
	$ayear =& new DbCalendarYear($atime);
	$ayear->bindSeminarEvents($bind_seminare);
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/year.inc.php");

}

// edit an event *********************************************************

// ist $termin_id an das Skript uebergeben worden, dann bearbeite diesen Termin
// ist $atime an das Skript uebergeben worden, dann erzeuge neuen Termin (s.o.)
if ($cmd == 'edit') {

		
	$edit_mode_out = '<b>';
	if (get_class($atermin) == 'seminarevent') {
		$edit_mode_out .= sprintf(_("Termin am %s"), ldate($atermin->getStart()));
	}
	elseif (get_class($atermin) == 'dbcalendarevent') {
		$edit_mode_out .= sprintf(_("Termin am %s bearbeiten"), ldate($atime));
	}
	elseif ($atime) {
	//	if (check_date($start_month, $start_day, $start_year)) {
			$edit_mode_out .= sprintf(_("Termin erstellen am %s"),
					ldate(mktime(0, 0, 0, $start_month, $start_day, $start_year)));
	//	}
	}
	else {
		page_close();
		die;
	}
	$edit_mode_out .= "</b>\n";
	if (!$mod)
		$mod = 'SINGLE';
	
	// transfer form->form
	if ($mod_prv && $set_recur_x) {
		$txt = htmlentities(stripslashes($txt), ENT_QUOTES);
		$content = htmlentities(stripslashes($content), ENT_QUOTES);
		$loc = htmlentities(stripslashes($loc), ENT_QUOTES);
		$cat_text = htmlentities(stripslashes($cat_text), ENT_QUOTES);
	}
	
	// start and end time in 5 minute steps	
	$start_m = $start_m - ($start_m % 5);
	$end_m = $end_m - ($end_m % 5);
	
	if ($atermin)
		$repeat = $atermin->getRepeat();
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/edit.inc.php");
}

// Seminartermine einbinden **************************************************

if ($cmd == 'bind') {
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/bind.inc.php");
	
}

// Termine importieren/exportieren/synchronisieren ***************************
if ($cmd == 'export') {
		
	include($RELATIVE_PATH_CALENDAR . "/views/export.inc.php");
	
}
	
// Ansicht anpassen **********************************************************

if ($cmd == 'changeview') {

	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/calendar_settings.inc.php");
	
}
	
include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/footer.inc.php");

// Save data back to database.
page_close();


function check_form_values ($post_vars) {
	$err = array();
	if (!check_date($post_vars['start_month'], $post_vars['start_day'], $post_vars['start_year']))
		$err['start_time'] = TRUE;
	if (!check_date($post_vars['end_month'], $post_vars['end_day'], $post_vars['end_year']))
		$err['end_time'] = TRUE;
	
	if (!$err['start_time'] && !$err['end_time']){
		$start = mktime($post_vars['start_h'], $post_vars['start_m'], 0, $post_vars['start_month'], $post_vars['start_day'], $post_vars['start_year']);
		$end = mktime($post_vars['end_h'], $post_vars['end_m'], 0, $post_vars['end_month'], $post_vars['end_day'], $post_vars['end_year']);
		if ($start > $end)
			$err['end_time'] = TRUE;
	}
	
	if (!preg_match('/^.*\S+.*$/', $post_vars['txt']))
		$err['titel'] = TRUE;
	switch ($post_vars['mod_prv']) {
		case 'DAILY':
			if (!preg_match("/^\d{1,3}$/", $post_vars['linterval_d'])) {
				$err['linterval_d'] = TRUE;
				$err['set_recur'] = TRUE;
			}
			break;
		case 'WEEKLY':
			if (!preg_match("/^\d{1,3}$/", $post_vars['linterval_w'])) {
				$err['linterval_w'] = TRUE;
				$err['set_recur'] = TRUE;
			}
			break;
		case 'MONTHLY':
			if ($post_vars['type_m'] == 'day') {
				if (!preg_match("/^\d{1,2}$/", $post_vars['day_m']) || $post_vars['day_m'] > 31 || $post_vars['day_m'] < 1) {
					$err['sinterval_m'] = TRUE;
					$err['set_recur'] = TRUE;
				}
				if (!preg_match("/^\d{1,3}$/", $post_vars['linterval_m1'])) {
					$err['linterval_m1'] = TRUE;
					$err['set_recur'] = TRUE;
				}
			}
			else {
				if (!preg_match("/^\d{1,3}$/", $post_vars['linterval_m2'])) {
					$err['linterval_m2'] = TRUE;
					$err['set_recur'] = TRUE;
				}
			}
			break;
		case 'YEARLY':
			// Jahr 2000 als Schaltjahr
			if (!check_date($post_vars['month_y1'], $post_vars['day_y'], 2000)) {
				$err['day_y'] = TRUE;
				$err['set_recur'] = TRUE;
			}
	}
	
	if ($post_vars['mod_prv'] != 'SINGLE' && $post_vars['exp_c'] == 'date') {
		if (!check_date($post_vars['exp_month'], $post_vars['exp_day'], $post_vars['exp_year'])) {
			$err['exp_time'] = TRUE;
			$err['set_recur'] = TRUE;
		}
		else {
			$exp = mktime(23, 59, 59, $post_vars['exp_month'], $post_vars['exp_day'], $post_vars['exp_year']);
			if (!$err['end_time'] && $exp < $end) {
				$err['exp_time'] = TRUE;
				$err['set_recur'] = TRUE;
			}
		}
	}
	elseif ($post_vars['mod_prv'] != 'SINGLE' && $post_vars['exp_c'] == 'count') {
		if (!(preg_match("/^\d{1,3}$/", $post_vars['exp_count']) && $post_vars['exp_count'] > 0)) {
			$err['exp_count'] = TRUE;
			$err['set_recur'] = TRUE;
		}
	}
	
	return $err;
}

function set_event_properties (&$post_vars, &$atermin, $mod) {
	if ($post_vars['wholeday']) {
		$atermin->properties['DTSTART'] = mktime(0, 0, 0, $post_vars['start_month'],
				$post_vars['start_day'], $post_vars['start_year']);
		$atermin->properties['DTEND'] = mktime(23, 59, 59, $post_vars['end_month'],
				$post_vars['end_day'], $post_vars['end_year']);
	}
	else {
		$atermin->properties['DTSTART'] = mktime($post_vars['start_h'], $post_vars['start_m'],
				0, $post_vars['start_month'], $post_vars['start_day'], $post_vars['start_year']);
		$atermin->properties['DTEND'] = mktime($post_vars['end_h'], $post_vars['end_m'], 0,
				$post_vars['end_month'], $post_vars['end_day'], $post_vars['end_year']);
	}
	$atermin->properties['SUMMARY']         = $post_vars['txt'];
	$atermin->properties['CATEGORIES']      = $post_vars['cat_text'];
	$atermin->properties['STUDIP_CATEGORY'] = $post_vars['cat'];
	$atermin->properties['PRIORITY']        = $post_vars['priority'];
	$atermin->properties['LOCATION']        = $post_vars['loc'];
	$atermin->properties['DESCRIPTION']     = $post_vars['content'];
	
	// exceptions
	$atermin->setExceptions($post_vars['exceptions']);
	// add exception
	if ($post_vars['add_exc_x']) {
		$exception = array(mktime(12, 0, 0, $post_vars['exc_month'],
				$post_vars['exc_day'], $post_vars['exc_year'], 0));
		$atermin->setExceptions(array_merge($atermin->getExceptions(), $exception));
		unset($post_vars['add_exc_x']);
	}
	// delete exceptions
	if ($post_vars['del_exc_x']) {
		$atermin->setExceptions(array_diff($atermin->getExceptions(), $post_vars['exc_delete']));
		unset($post_vars['del_exc_x']);
		unset($post_vars['exc_delete']);
	}
	$post_vars['exceptions'] = $atermin->getExceptions();
	
	switch ($post_vars['via']) {
		case 'PUBLIC':
			$atermin->setType('PUBLIC');
			break;
		case 'CONFIDENTIAL':
			$atermin->setType('CONFIDENTIAL');
			break;
		default:
			$atermin->setType('PRIVATE');
	}
	
	if ($mod != 'SINGLE' && $post_vars['exp_c'] == 'date') {
		$expire = mktime(23, 59, 59, $post_vars['exp_month'], $post_vars['exp_day'],
				$post_vars['exp_year']);
		$post_vars['exp_count'] = 0;
	}
	elseif ($post_vars['exp_c'] == 'never') {
		$expire = 2114377200;
		$post_vars['exp_count'] = 0;
	}
		
	switch ($mod) {
		case 'DAILY':
			if ($post_vars['type_d'] == 'daily') {
				$atermin->setRepeat(array('rtype' => 'DAILY', 'linterval' => $post_vars['linterval_d'],
						'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			elseif ($post_vars['type_d'] == 'wdaily') {
				$atermin->setRepeat(array('rtype' => 'WEEKLY', 'linterval' => '1',
						'wdays' => '12345', 'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			break;
			
		case 'WEEKLY':
			if (empty($post_vars['wdays'])) {
				$atermin->setRepeat(array('rtype' => 'WEEKLY', 'linterval' => $post_vars['linterval_w'],
						'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			else {
				$weekdays = implode('', $post_vars['wdays']);
				$atermin->setRepeat(array('rtype' => 'WEEKLY', 'linterval' => $post_vars['linterval_w'],
						'wdays' => $weekdays, 'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			break;
			
		case 'MONTHLY':
			if ($post_vars['type_m'] == 'day') {
				$atermin->setRepeat(array('rtype' => 'MONTHLY', 'linterval' => $post_vars['linterval_m1'],
						'day' => $post_vars['day_m'], 'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			else {
				$atermin->setRepeat(array('rtype' => 'MONTHLY', 'linterval' => $post_vars['linterval_m2'],
						'sinterval' => $post_vars['sinterval_m'], 'wdays' => $post_vars['$wday_m'],
						'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			break;
			
		case 'YEARLY':
			if ($post_vars['type_y'] == 'day') {
				$atermin->setRepeat(array('rtype' => 'YEARLY', 'month' => $post_vars['month_y1'],
						'day' => $post_vars['day_y'], 'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			else {
				$atermin->setRepeat(array('rtype' => 'YEARLY', 'sinterval' => $post_vars['sinterval_y'],
						'wdays' => $post_vars['wday_y'], 'month' => $post_vars['month_y2'],
						'expire' => $expire, 'count' => $post_vars['exp_count']));
			}
			break;
			
		default:
			$atermin->setRepeat(array('rtype' => 'SINGLE', 'expire' => $expire));
	}
	
}

function get_event_properties (&$post_vars, &$atermin) {
	$post_vars['start_h'] = date('G', $atermin->getStart());
	$post_vars['start_m'] = date('i', $atermin->getStart());
	$post_vars['start_day'] = date('j', $atermin->getStart());
	$post_vars['start_month'] = date('n', $atermin->getStart());
	$post_vars['start_year'] = date('Y', $atermin->getStart());
	$post_vars['end_h'] = date('G', $atermin->getEnd());
	$post_vars['end_m'] = date('i', $atermin->getEnd());
	$post_vars['end_day'] = date('j', $atermin->getEnd());
	$post_vars['end_month'] = date('n', $atermin->getEnd());
	$post_vars['end_year'] = date('Y', $atermin->getEnd());
	
	if ($atermin->isDayEvent())
		$post_vars['wholeday'] = 1;
	else
		unset($post_vars['wholeday']);
	
	$post_vars['cat'] = $atermin->properties['STUDIP_CATEGORY'];
	$post_vars['txt'] = htmlReady($atermin->getTitle());
	$post_vars['content'] = htmlReady($atermin->properties['DESCRIPTION']);
	$post_vars['loc'] = htmlReady($atermin->getLocation());
	
	if (get_class($atermin) != 'seminarevent') {
		
		// exceptions
		$post_vars['exceptions'] = $atermin->getExceptions();
		
		$post_vars['cat_text'] = htmlReady($atermin->properties['CATEGORIES']);
		
		switch ($atermin->getType()) {
			case 'PUBLIC':
				$post_vars['via'] = 'PUBLIC';
				break;
			case 'CONFIDENTIAL':
				$post_vars['via'] = 'CONFIDENTIAL';
				break;
			default:
				$post_vars['via'] = 'PRIVATE';
		}
				
		$post_vars['priority'] = $atermin->getPriority();
		$repeat = $atermin->getRepeat();
		if ($repeat['count']) {
			$post_vars['exp_count'] = $repeat['count'];
			$post_vars['exp_c'] = 'count';
		}
		else {
			$expire = $atermin->getExpire();
			if ($expire == 2114377200)
				$post_vars['exp_c'] = 'never';
			else
				$post_vars['exp_c'] = 'date';
			$post_vars['exp_day'] = date('j', $expire);
			$post_vars['exp_month'] = date('n', $expire);
			$post_vars['exp_year'] = date('Y', $expire);
		}
		
		switch ($repeat['rtype']) {
			case 'SINGLE':
				break;
			case 'DAILY':
				$post_vars['linterval_d'] = $repeat['linterval'];
				break;
			case 'WEEKLY':
				$post_vars['linterval_w'] = $repeat['linterval'];
				for ($i = 0;$i < strlen($repeat['wdays']);$i++)
					$post_vars['wdays'][$repeat['wdays']{$i}] = $repeat['wdays']{$i};
				break;
			case 'MONTHLY':
				if ($repeat['wdays']) {
					$post_vars['type_m'] = 'wday';
					$post_vars['linterval_m2'] = $repeat['linterval'];
					$post_vars['sinterval_m'] = $repeat['sinterval'];
					for ($i = 0;$i < strlen($repeat['wdays']);$i++)
						$post_vars['wday_m'] = $repeat['wdays'];
				}
				else {
					$post_vars['type_m'] = 'day';
					$post_vars['linterval_m1'] = $repeat['linterval'];
					$post_vars['day_m'] = $repeat['day'];
				}
				break;
			case 'YEARLY':
				if ($repeat['wdays']) {
					$post_vars['type_y'] = 'wday';
					$post_vars['sinterval_y'] = $repeat['sinterval'];
					$post_vars['wday_y'] = $repeat['wdays'];
					$post_vars['month_y2'] = $repeat['month'];
				}
				else {
					$post_vars['type_y'] = 'day';
					$post_vars['day_y'] = $repeat['day'];
					$post_vars['month_y1'] = $repeat['month'];
				}
		}
	}
}
?>
