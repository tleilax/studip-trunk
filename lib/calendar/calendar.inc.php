<?
/**
* calendar.inc.php
*
*
*
* @author		Peter Thienel <pthienel@web.de>
* @package	calendar
*/

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

/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);

require_once('config.inc.php');
require_once('lib/visual.inc.php');
require_once('lib/functions.php');
require_once('lib/calendar_functions.inc.php');
require_once($RELATIVE_PATH_CALENDAR . '/calendar_visual.inc.php');
require_once($RELATIVE_PATH_CALENDAR . '/lib/calendar_misc_func.inc.php');
require_once($RELATIVE_PATH_CALENDAR . '/lib/DbCalendarEvent.class.php');
require_once($RELATIVE_PATH_CALENDAR . '/lib/SeminarEvent.class.php');
require_once($RELATIVE_PATH_CALENDAR . '/lib/Calendar.class.php');
//require_once($RELATIVE_PATH_CALENDAR . '/lib/GroupCalendar.class.php');

// -- hier muessen Seiten-Initialisierungen passieren --
// -- wir sind jetzt definitiv in keinem Seminar, also... --
//closeObject();

if (!$calendar_sess_control_data) {
	$sess->register('calendar_sess_control_data');
}

switch ($cmd) {
	case 'edit':
		$HELP_KEYWORD="Basis.TerminkalenderBearbeiten";
		$CURRENT_PAGE=_("Terminkalender");
		break;
	case 'bind':
		$HELP_KEYWORD="Basis.TerminkalenderEinbinden";
		$CURRENT_PAGE=_("Terminkalender");
		break;
	case 'changeview':
		$HELP_KEYWORD="Basis.TerminkalenderEinstellungen";
		$CURRENT_PAGE=_("Einstellungen des Terminkalenders bearbeiten");
		break;
	default:
		$HELP_KEYWORD="Basis.Terminkalender";
		$CURRENT_PAGE=_("Terminkalender");
}

// switch to own calendar if called from header
if (!get_config('CALENDAR_GROUP_ENABLE') || $caluserid == 'self') {
	//	|| !isset($calendar_sess_control_data['cal_select'])) {
	//i
//	$calendar_sess_control_data['cal_select'] = 'user.' . get_username();
	closeObject();
	$calendar_sess_control_data['cal_select'] = 'user.' . $user->id;
}/*
else if ($cal_select) {
	$calendar_sess_control_data['cal_select'] = $cal_select;
} elseif ($cal_user) {
	$calendar_sess_control_data['cal_select'] = 'user.' . $cal_user;
} elseif ($cal_group) {
	$calendar_sess_control_data['cal_select'] = 'group.' . $cal_group;
}
*/

if (isset($_POST['show_project_events']) && isset($_POST['cal_select'])) {
	$calendar_sess_control_data['show_project_events'] = true;
} elseif (isset($_POST['cal_select'])) {
	$calendar_sess_control_data['show_project_events'] = false;
}

if (isset($cal_select)) {
	list($cal_select_range, $cal_select_id) = explode('.', $cal_select);
	if ($cal_select_range == 'user') {
		$cal_select_id = get_userid($cal_select_id);
	} elseif ($cal_select_range == 'sem') {
		header("Location: seminar_main.php?auswahl=$cal_select_id&redirect_to=calendar.php&{$_SERVER['QUERY_STRING']}");
	} else if ($cal_select_range == 'inst') {
		header("Location: institut_main.php?auswahl=$cal_select_id&redirect_to=calendar.php&{$_SERVER['QUERY_STRING']}");
	}
	$calendar_sess_control_data['cal_select'] = $cal_select_range . '.' . $cal_select_id;
} else if (isset($GLOBALS['SessSemName'][1]) && $GLOBALS['SessSemName'][1] != '') {
	checkObject();
	checkObjectModule('calendar');
	object_set_visit_module('calendar');
	$cal_select_range = 'sem';
	$cal_select_id = $GLOBALS['SessSemName'][1];
	$calendar_sess_control_data['cal_select'] = $cal_select_range . '.' . $cal_select_id;
} else if ($calendar_sess_control_data['cal_select']) {
	list($cal_select_range, $cal_select_id) = explode('.', $calendar_sess_control_data['cal_select']);
} else {
	$cal_select_range = 'user';
	$cal_select_id = $user->id;
	$calendar_sess_control_data['cal_select'] = $cal_select_range . '.' . $cal_select_id;
}

if ($_REQUEST['cmd'] == 'export'
    && array_shift(explode('.', $calendar_sess_control_data['cal_select'])) == 'group') {
   $_calendar = Calendar::getInstance(CALENDAR_RANGE_USER, $user->id);
} else {
	$_calendar = Calendar::getInstance($cal_select_id);
}

if ($_calendar->getRange() == CALENDAR_RANGE_USER) {
	if (is_array($calendar_user_control_data['bind_seminare'])) {
		$db_check1 =& new DB_Seminar();
	$db_check2 =& new DB_Seminar();
		$query = "SELECT Seminar_id FROM seminar_user WHERE user_id = '{$user->id}'";
		$db_check1->query($query);
		while ($db_check1->next_record()) {
			if ($calendar_user_control_data['bind_seminare'][$db_check1->f('Seminar_id')]) {
				$query = "UPDATE seminar_user SET bind_calendar = 1 WHERE Seminar_id = '"
						. $db_check1->f('Seminar_id') . "' AND user_id = '{$user->id}'";
			} else {
				$query = "UPDATE seminar_user SET bind_calendar = 0 WHERE Seminar_id = '"
					. $db_check1->f('Seminar_id') . "' AND user_id = '{$user->id}'";
			}
			$db_check2->query($query);
		}
		unset($calendar_user_control_data['bind_seminare']);
		if (isset($calendar_user_control_data['ts_bind_seminare'])) {
			unset($calendar_user_control_data['ts_bind_seminare']);
		}
	}
}

// restore user defined settings
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
		'delete'           => $cal_delete,
		'step_week_group'  => $cal_step_week_group,
		'step_day_group'   => $cal_step_day_group
	);
}

// use current timestamp if no timestamp is given
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
		$destination = $calendar_sess_control_data['source'] . '#a';
		$calendar_sess_control_data['source'] = '';
		page_close();
		header("Location: $destination");
		exit;
	}
	if ($calendar_sess_control_data['view_prv'])
		$cmd = $calendar_sess_control_data['view_prv'];
	else
		$cmd = $calendar_sess_control_data['view'];
}

// allowed time range
if (isset($atime) && ($atime < 0 || $atime > CALENDAR_END))
	$atime = time();

// check date of "go-to-function"
if (check_date($jmp_month, $jmp_day, $jmp_year))
	$atime = mktime(12, 0, 0, $jmp_month, $jmp_day, $jmp_year);
else {
	$jmp_day = date('j', $atime);
	$jmp_month = date('n', $atime);
	$jmp_year = date('Y', $atime);
}

// delete all expired events and count events
$db_control =& CalendarDriver::getInstance($user->id);
if ($cmd == 'add' && $calendar_user_control_data['delete'] > 0) {
	$expire_delete = mktime(date('G', time()), date('i', time()), 0,
			date('n', time()) - $calendar_user_control_data['delete'],
			date('j', time()), date('Y', time()));
	$db_control->deleteFromDatabase('EXPIRED', '', 0, $expire_delete);
}
$db_control->openDatabase('COUNT', 'CALENDAR_EVENTS');
$count_events = $db_control->getCountEvents();

if ($_POST['sem'] && $_calendar->getRange() == CALENDAR_RANGE_USER) {
	$_calendar->updateBindSeminare();
}

if ($cmd == '') {
	if ($termin_id) {
		// if termin_id is given always change in edit mode
		$cmd = 'edit';
	} else {
		$cmd = $calendar_user_control_data['view'];
    }
}

$_calendar->setUserSettings($calendar_user_control_data);

$accepted_vars = array('start_m', 'start_h', 'start_day', 'start_month', 'start_year', 'end_m',
                        'end_h', 'end_day', 'end_month', 'end_year', 'exp_day', 'exp_month',
                        'exp_year', 'cat', 'priority', 'txt', 'content', 'loc', 'linterval_d',
                        'linterval_w', 'wdays', 'type_d', 'type_m', 'linterval_m2', 'sinterval_m',
                        'linterval_m1', 'wday_m', 'day_m', 'type_y', 'sinterval_y', 'wday_y',
                        'day_y', 'month_y1', 'month_y2', 'atime', 'termin_id', 'exp_c', 'via',
                        'cat_text', 'mod_prv', 'exc_day', 'exc_month', 'exc_year', 'exceptions',
                        'exc_delete', 'add_exc_x', 'del_exc_x', 'exp_count', 'select_user', 'evtype');

if ($cmd == 'add' || $cmd == 'edit') {
    if (!isset($calendar_sess_forms_data))
        $sess->register('calendar_sess_forms_data');
    if (!empty($_POST)){
        // Formulardaten uebernehmen
        foreach ($accepted_vars as $key) {
            if (isset($_POST[$key]))
                $calendar_sess_forms_data[$key] = $_POST[$key];
        }
    }
    else
        $calendar_sess_control_data['mod'] = '';
    // checkbox-values
    if (!$set_recur_x)
        $calendar_sess_forms_data['wholeday'] = $_POST['wholeday'];
}
elseif ($cmd != 'export') {
    unset($calendar_sess_forms_data);
    $sess->unregister('calendar_sess_forms_data');
}

$write_permission = TRUE;

if ($source_page && ($cmd == 'edit' || $cmd == 'add' || $cmd == 'delete')) {
    $calendar_sess_control_data['source'] = preg_replace('![^0-9a-z+_?&#/=.-\[\]]!i', '', rawurldecode($source_page));
}

// Seitensteuerung
$HELP_KEYWORD="Basis.Terminkalender";

if ($_calendar->getRange() == CALENDAR_RANGE_SEM || $_calendar->getRange() == CALENDAR_RANGE_INST) {
    $active_range = '/calendar/calendar/';
} else {
    $active_range = '/calendar/calendar/';
}

switch ($cmd) {
    case 'showlist':
        if ($_calendar->getRange() == CALENDAR_RANGE_GROUP) {
            $cmd = 'showweek';
            Navigation::activateItem($active_item . 'week');
        } else {
            Navigation::activateItem($active_range . 'list');
        }
        $calendar_sess_control_data['view_prv'] = $cmd;
        break;
    case 'showweek':
        $calendar_sess_control_data['view_prv'] = $cmd;
        Navigation::activateItem($active_range . 'week');
        break;
    case 'showmonth':
        $calendar_sess_control_data['view_prv'] = $cmd;
        Navigation::activateItem($active_range . 'month');
        break;
    case 'showyear':
        $calendar_sess_control_data['view_prv'] = $cmd;
        Navigation::activateItem($active_range . 'year');
        break;
    case 'showday':
        $calendar_sess_control_data['view_prv'] = $cmd;
        Navigation::activateItem($active_range . 'day');
        break;

    case 'export':
        Navigation::activateItem($active_range . 'export');
        if ($_calendar->getRange() == CALENDAR_RANGE_SEM || $_calendar->getRange() == CALENDAR_RANGE_INST) {
            $_calendar->headline = getHeaderLine($_calendar->user_id) . ' - ' . _("Terminkalender - Termine exportieren");
        } else if ($_calendar->checkPermission(CALENDAR_PERMISSION_OWN)) {
            $_calendar->headline = _("Mein pers&ouml;nlicher Terminkalender - Termindaten importieren, exportieren und synchronisieren");
        } else {
            $_calendar->headline = sprintf(_("Terminkalender von %s %s - Termindaten exportieren"),
                    get_fullname($_calendar->getUserId()), $_calendar->perm_string);
        }
        break;

    case 'bind':
        $title = _("Mein pers&ouml;nlicher Terminkalender - Veranstaltungstermine einbinden");
        Navigation::activateItem($active_range . 'course');
        break;

    case 'add':
    case 'del':
        switch($calendar_sess_control_data['view_prv']) {
            case 'showday':
                $CURRENT_PAGE = _("Mein pers�nlicher Terminkalender - Tagesansicht");
                Navigation::activateItem($active_range . 'day');
                break;
            case 'showweek':
                $CURRENT_PAGE = _("Mein pers�nlicher Terminkalender - Wochenansicht");
                Navigation::activateItem($active_range . 'week');
                break;
            case 'showmonth':
                $CURRENT_PAGE = _("Mein pers�nlicher Terminkalender - Monatsansicht");
                Navigation::activateItem($active_range . 'month');
                break;
            case 'showyear':
                $CURRENT_PAGE = _("Mein pers�nlicher Terminkalender - Jahresansicht");
                Navigation::activateItem($active_range . 'year');
        }
        break;

    case 'edit':
        $HELP_KEYWORD = "Basis.TerminkalenderBearbeiten";
        Navigation::activateItem($active_range . 'edit');

        break;
}


if ($cmd == 'add') {
    // Ueberpruefung der Formulareingaben
    $err = Calendar::checkFormData($calendar_sess_forms_data);
    // wenn alle Daten OK, dann Termin anlegen, oder bei vorhandener
    // termin_id updaten
    if (empty($err) && $count_events < $CALENDAR_MAX_EVENTS) {
        $_calendar->addEvent($termin_id, $select_user);
        $atime = $_calendar->event->getStart();
        if ($calendar_sess_control_data['source']) {
            $destination = $calendar_sess_control_data['source'] . "#a";
            $calendar_sess_control_data['source'] = '';
            unset($calendar_sess_forms_data);
            $sess->unregister('calendar_sess_forms_data');
            page_close();
            header('Location: '.$destination);
            exit;
        }

        if (!empty($calendar_sess_control_data['view_prv'])) {
            $cmd = $calendar_sess_control_data['view_prv'];
        } else {
            $cmd = 'showday';
        }

        unset($calendar_sess_forms_data);
        $sess->unregister('calendar_sess_forms_data');
    } else {
         // wrong data? -> switch back to edit mode
        $cmd = 'edit';
        $_calendar->restoreEvent($termin_id);
        $_calendar->setEventProperties($calendar_sess_forms_data, $mod);
        $mod = $mod_prv ? $mod_prv : 'SINGLE';
        if ($back_recur_x) {
            $set_recur_x = 1;
            unset($back_recur_x);
        }
    }
}

if ($cmd == 'del') {
    $_calendar->deleteEvent($termin_id);

    if ($calendar_sess_control_data['source']) {
        $destination = $calendar_sess_control_data['source'];
        $calendar_sess_control_data['source'] = '';
        header("Location: $destination");
        page_close();
        die;
    }

    if (!empty($calendar_sess_control_data['view_prv'])) {
        $cmd = $calendar_sess_control_data['view_prv'];
    } else {
        $cmd = 'showday';
    }

    unset($calendar_sess_forms_data);
    $sess->unregister('calendar_sess_forms_data');
}

if ($cmd == 'edit') {
    if ($termin_id) {
        if ($evtype == 'sem' || $evtype == 'semcal') {
            $_calendar->createSeminarEvent($evtype);
            if (!$_calendar->event->restore($termin_id)) {
                // something wrong... better to go back to the last view
                page_close();
                header('Location: ' . $PHP_SELF . '?cmd='
                        . $calendar_sess_control_data['view_prv'] . "&atime=$atime");
                exit;
            }
            $atime = $_calendar->event->getStart();
        } else {
            // get event from database
            $_calendar->restoreEvent($termin_id);
            if (!$mod) {
                $mod = $_calendar->event->getRepeat('rtype');
            }
            $atime = $_calendar->event->getStart();
        }
        if ($_calendar->getRange() == CALENDAR_RANGE_SEM || $_calendar->getRange() == CALENDAR_RANGE_INST) {
            $_calendar->headline = getHeaderLine($_calendar->user_id) . ' - ' . _("Terminkalender - Termin bearbeiten");
        } else if (strtolower(get_class($_calendar)) == 'groupcalendar') {
            $_calendar->headline = sprintf(_("Terminkalender der Gruppe %s - Termin bearbeiten"),
                    $_calendar->getGroupName());
        } else if ($_calendar->checkPermission(CALENDAR_PERMISSION_OWN)) {
            $_calendar->headline = _("Mein pers&ouml;nlicher Terminkalender - Termin bearbeiten");
        } else {
            $_calendar->headline = sprintf(_("Terminkalender von %s %s - Termin bearbeiten"),
                    get_fullname($_calendar->getUserId()), $text_permission);
        }
    } elseif ($_calendar->havePermission(CALENDAR_PERMISSION_WRITABLE)) {
        if ($_calendar->getRange() == CALENDAR_RANGE_SEM || $_calendar->getRange() == CALENDAR_RANGE_INST) {
            $_calendar->headline = getHeaderLine($_calendar->user_id) . ' - ' . _("Terminkalender - Termin anlegen");
        } else if (strtolower(get_class($_calendar)) == 'groupcalendar') {
            $_calendar->headline = sprintf(_("Terminkalender der Gruppe %s - Termin anlegen"),
                    $_calendar->getGroupName());
        } else if ($_calendar->checkPermission(CALENDAR_PERMISSION_OWN)) {
            $_calendar->headline = _("Mein pers&ouml;nlicher Terminkalender - Termin anlegen");
        } else {
            $_calendar->headline = sprintf(_("Terminkalender von %s %s - Termin anlegen"),
                    get_fullname($_calendar->getUserId()), $text_permission);
        }
        // call from dayview for new event -> set default values
        if ($atime && empty($_POST)) {
            if ($devent) {
                $properties = array(
                        'DTSTART' => mktime(0, 0, 0, date('n', $atime), date('j', $atime),
                                date('Y', $atime)),
                        'DTEND'   => mktime(23, 59, 59, date('n', $atime),
                                date('j', $atime), date('Y', $atime)),
                        'SUMMARY' => _("Kein Titel"),
                        'STUDIP_CATEGORY' => 1,
                        'CATEGORIES' => '',
                        'CLASS' => 'PRIVATE',
                        'RRULE' => array('rtype' => 'SINGLE'));
                $_calendar->createEvent($properties);
                $_calendar->event->setDayEvent(TRUE);
            } else {
                $properties = array(
                        'DTSTART' => $atime,
                        'DTEND'   => mktime(date('G', $atime) + 1, date('i', $atime), 0,
                                date('n', $atime), date('j', $atime), date('Y', $atime)),
                        'SUMMARY' => _("Kein Titel"),
                        'STUDIP_CATEGORY' => 1,
                        'CATEGORIES' => '',
                        'CLASS' => 'PRIVATE',
                        'RRULE' => array('rtype' => 'SINGLE'));
                $_calendar->createEvent($properties);
            }

    //      $_calendar->event->setRepeat(array('rtype' => 'SINGLE'));
        }
        else {
            $properties = array();
            $_calendar->createEvent($properties);
        }
    }
    else {
        page_close();
        header('Location: ' . $PHP_SELF . '?cmd='
                . $calendar_sess_control_data['view_prv'] . "&atime=$atime");
        exit;
        $write_permission = FALSE;
        //$title = sprintf(_("Terminkalender von %s %s - Zugriff verweigert"),
        //      get_fullname($_calendar->getUserId()), $text_permission);
    }

    if ($write_permission) {
        if (empty($_POST)) {
            $_calendar->getEventProperties($calendar_sess_forms_data);
        }
        else {
            $err = Calendar::checkFormData($calendar_sess_forms_data);
            if (empty($err)) {
                $_calendar->setEventProperties($calendar_sess_forms_data, $mod);
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
    }
}

// Tagesuebersicht anzeigen ***************************************************

if ($cmd == 'showday') {

    $at = date('G', $atime);
    if ($at >=  $calendar_user_control_data['start']
            && $at <= $calendar_user_control_data['end'] || !$atime) {
        $st = $calendar_user_control_data['start'];
        $et = $calendar_user_control_data['end'];
    }
    elseif ($at < $calendar_user_control_data['start']) {
        $st = 0;
        $et = $calendar_user_control_data['start'] + 2;
    }
    else {
        $st = $calendar_user_control_data['end'] - 2;
        $et = 23;
    }

    include($RELATIVE_PATH_CALENDAR . "/views/day.inc.php");

}

// Wochenuebersicht anzeigen **************************************************

if ($cmd == 'showweek') {
/*
    // computes the hours which must be displayed, if the user has klicked the up/down arrows
    if(isset($wtime))
        $at = (int) $wtime;
    if (!($at > 0 && $at < 24))
        $at = $calendar_user_control_data['start'];
    if ($at >= $calendar_user_control_data['start'] && $at <= $calendar_user_control_data['end']) {
        $st = $calendar_user_control_data['start'];
        $et = $calendar_user_control_data['end'];
    }
    else if ($at < $calendar_user_control_data['start']) {
        $st = 0;
        $et = $calendar_user_control_data['start'] + 2;
    }
    else {
        $st = $calendar_user_control_data['end'] - 2;
        $et = 23;
    }

*/
    $at = date('G', $atime);
    if ($at >=  $calendar_user_control_data['start']
            && $at <= $calendar_user_control_data['end'] || !$atime) {
        $st = $calendar_user_control_data['start'];
        $et = $calendar_user_control_data['end'];
    }
    elseif ($at < $calendar_user_control_data['start']) {
        $st = 0;
        $et = $calendar_user_control_data['start'] + 2;
    }
    else {
        $st = $calendar_user_control_data['end'] - 2;
        $et = 23;
    }

    include($RELATIVE_PATH_CALENDAR . "/views/week.inc.php");

}

// Monatsuebersicht anzeigen **************************************************

if ($cmd == 'showmonth') {

    include($RELATIVE_PATH_CALENDAR . "/views/month.inc.php");

}

// Jahresuebersicht ***********************************************************

if ($cmd == 'showyear') {

    include($RELATIVE_PATH_CALENDAR . "/views/year.inc.php");

}

// Listenansicht ***************************************************************

if ($cmd == 'showlist') {
    require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEventList.class.php");
    $event_list_start = $atime;
    $event_list_end = mktime(23, 59, 59, date('n', $event_list_start), date('j', $event_list_start) + 14, date('Y', $event_list_start));

    if ($_calendar->getPermission() == CALENDAR_PERMISSION_OWN) {
        $view =& new DbCalendarEventList($_calendar, $event_list_start, $event_list_end, TRUE, Calendar::getBindSeminare(), $_REQUEST['cal_restrict']);
    } else {
        $view =& new DbCalendarEventList($_calendar, $event_list_start, $event_list_end, TRUE, Calendar::getBindSeminare($_calendar->getUserId()), $_REQUEST['cal_restrict']);
    }

    if (isset($_REQUEST['dopen'])) {
        $calendar_sess_control_data['dopen'] = htmlentities(substr($_REQUEST['dopen'], 0, 45));
    }
    if (isset($dclose)) {
        unset($calendar_sess_control_data['dopen']);
    }
    if (isset($calendar_sess_control_data['dopen'])) {
        $_REQUEST['dopen'] = $calendar_sess_control_data['dopen'];
    }

    if ($_calendar->getRange() == CALENDAR_RANGE_SEM || $_calendar->getRange() == CALENDAR_RANGE_INST) {
        $_calendar->headline = getHeaderLine($_calendar->user_id) . ' - ' . _("Terminkalender - Listenansicht");
    } else if ($_calendar->checkPermission(CALENDAR_PERMISSION_OWN)) {
        $_calendar->headline = _("Mein pers&ouml;nlicher Terminkalender - Listenansicht");
    } else {
        $_calendar->headline = sprintf(_("Terminkalender von %s %s - Listenansicht"),
                get_fullname($_calendar->getUserId()), $_calendar->perm_string);
    }

    include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/list.inc.php");

}

// edit an event *********************************************************

// ist $termin_id an das Skript uebergeben worden, dann bearbeite diesen Termin
// ist $atime an das Skript uebergeben worden, dann erzeuge neuen Termin (s.o.)
if ($cmd == 'edit') {
    if ($write_permission) {
        if (strtolower(get_class($_calendar->event)) == 'seminarevent' || strtolower(get_class($_calendar->event)) == 'seminarcalendarevent'
                || !$_calendar->event->havePermission(CALENDAR_EVENT_PERM_WRITABLE)) {
            $edit_mode_out .= sprintf(_("Termin am %s"), ldate($_calendar->event->getStart()));
        } elseif (strtolower(get_class($_calendar->event)) == 'dbcalendarevent') {
            $edit_mode_out .= sprintf(_("Termin am %s bearbeiten"), ldate($atime));
        } elseif ($atime) {
            if (check_date($start_month, $start_day, $start_year)) {
                $edit_mode_out .= sprintf(_("Termin erstellen am %s"),
                        ldate(mktime(0, 0, 0, $start_month, $start_day, $start_year)));
            }
        } else {
            page_close();
            die;
        }
        if (!$mod) {
            $mod = 'SINGLE';
        }

        // transfer form->form
        if ($set_recur_x || $back_recur_x) {
            $txt = htmlentities(stripslashes($txt), ENT_QUOTES);
            $content = htmlentities(stripslashes($content), ENT_QUOTES);
            $loc = htmlentities(stripslashes($loc), ENT_QUOTES);
            $cat_text = htmlentities(stripslashes($cat_text), ENT_QUOTES);
        }

        // start and end time in 5 minute steps
        $start_m = $start_m - ($start_m % 5);
        $end_m = $end_m - ($end_m % 5);

        if ($_calendar->event) {
            $repeat = $_calendar->event->getRepeat();
        }

        include $RELATIVE_PATH_CALENDAR . '/views/edit.inc.php';
    }
}

// Seminartermine einbinden **************************************************

if ($cmd == 'bind') {

    include $RELATIVE_PATH_CALENDAR . '/views/bind.inc.php';

}

// Termine importieren/exportieren/synchronisieren ***************************
if ($cmd == 'export') {

    include $RELATIVE_PATH_CALENDAR . '/views/export.inc.php';

}

// Ansicht anpassen **********************************************************

if ($cmd == 'changeview') {

    include $RELATIVE_PATH_CALENDAR . '/calendar_settings.inc.php';

}

include $RELATIVE_PATH_CALENDAR . '/views/footer.inc.php';


// Save data back to database.
page_close();

