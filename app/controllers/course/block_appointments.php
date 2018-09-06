<?php

/**
 * block_appointments.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */
class Course_BlockAppointmentsController extends AuthenticatedController
{
    /**
     * Common tasks for all actions
     *
     * @param String $action Called action
     * @param Array  $args   Possible arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $course_id = $args[0];

        $this->course_id = Request::option('cid', $course_id);
        if (!get_object_type($this->course_id, array('sem')) ||
            SeminarCategories::GetBySeminarId($this->course_id)->studygroup_mode ||
            !$GLOBALS['perm']->have_studip_perm("tutor", $this->course_id)
        ) {
            throw new Trails_Exception(400);
        }
        PageLayout::setHelpKeyword('Basis.VeranstaltungenVerwaltenAendernVonZeitenUndTerminen');
        PageLayout::setTitle(Course::findCurrent()->getFullname() . " - " . _('Blockveranstaltungstermine anlegen'));
    }

    /**
     * Display the block appointments
     */
    public function index_action()
    {
        if (!Request::isXhr() && Navigation::hasItem('/course/admin/timesrooms')) {
            Navigation::activateItem('/course/admin/timesrooms');
        }
        $this->linkAttributes   = array('fromDialog' => Request::int('fromDialog') ? 1 : 0);
        $this->start_ts         = strtotime('this monday');
        $this->request          = $this->flash['request'] ?: $_SESSION['block_appointments'];
    }

    /**
     * Saves the block appointments of a course
     *
     * @param String $course_id Id of the course
     */
    public function save_action($course_id)
    {
        $errors = array();

        $start_day = strtotime(Request::get('block_appointments_start_day'));
        $end_day = strtotime(Request::get('block_appointments_end_day'));

        if (!($start_day && $end_day && $start_day <= $end_day)) {
            $errors[] = _('Bitte geben Sie korrekte Werte für Start- und Enddatum an!');
        } else {
            $start_time = strtotime(Request::get('block_appointments_start_time'), $start_day);
            $end_time = strtotime(Request::get('block_appointments_end_time'), $end_day);

            if (!($start_time && $end_time && (strtotime(Request::get('block_appointments_start_time')) < strtotime(Request::get('block_appointments_end_time'))))) {
                $errors[] = _('Bitte geben Sie korrekte Werte für Start- und Endzeit an!');
            }
        }


        $termin_typ = (int)Request::int('block_appointments_termin_typ');
        $free_room_text = Request::get('block_appointments_room_text');
        $date_count = Request::int('block_appointments_date_count');
        $days = Request::getArray('block_appointments_days');

        if (!is_array($days)) {
            $errors[] = _('Bitte wählen Sie mindestens einen Tag aus!');
        }

        if (count($errors)) {
            $this->flash['request'] = Request::getInstance();
            PageLayout::postMessage(MessageBox::error(_('Bitte korrigieren Sie Ihre Eingaben:'), $errors));
            $this->redirect('course/block_appointments/index');
            return;
        } else {

            $dates = array();
            /*
             * Recalculate end hour of last day to first day, so we don't run
             * into problems with daylight saving time which would add or
             * remove an hour.
             */
            $delta = (strtotime(Request::get('block_appointments_start_day') . ' ' .
                Request::get('block_appointments_end_time')) - $start_time) % (24 * 60 * 60);
            $last_day = strtotime(Request::get('block_appointments_start_time'), $end_day);

            if (in_array('everyday', $days)) {
                $days = range(1, 7);
            }
            if (in_array('weekdays', $days)) {
                $days = range(1, 5);
            }

            $t = $start_time;
            while ($t <= $last_day) {
                if (in_array(date('N', $t), $days)) {
                    for ($i = 1; $i <= $date_count; $i++) {
                        $date = new CourseDate();
                        $date->range_id = $course_id;
                        $date->date_typ = $termin_typ;
                        $date->raum = $free_room_text;
                        $date->date = $t;
                        $date->end_time = $t + $delta;
                        $dates[] = $date;
                    }
                }
                $t = strtotime('+1 day', $t);
            }

            if (count($dates)) {
                if (Request::submitted('preview')) {
                    //TODO
                }

                if (Request::submitted('save')) {
                    // store last used values in session as defaults
                    $_SESSION['block_appointments'] = [
                        'block_appointments_start_day' =>  date('d.m.Y', $start_day),
                        'block_appointments_end_day' =>    date('d.m.Y', $end_day),
                        'block_appointments_start_time' => date('H:i', $start_time),
                        'block_appointments_end_time' =>   date('H:i', $end_time),
                        'block_appointments_termin_typ' => $termin_typ,
                        'block_appointments_room_text' =>  $free_room_text,
                        'block_appointments_date_count' => $date_count,
                        'block_appointments_days' =>       $days
                    ];
                    $dates_created = array_filter(array_map(function ($d) {
                        return $d->store() ? $d->getFullname() : null;
                    }, $dates));
                    if ($date_count > 1) {
                        $dates_created = array_count_values($dates_created);
                        $dates_created = array_map(function ($k, $v) {
                            return $k . ' (' . $v . 'x)';
                        }, array_keys($dates_created), array_values($dates_created));
                    }
                    PageLayout::postMessage(MessageBox::success(_('Folgende Termine wurden erstellt:'), $dates_created));

                }
            } else {
                PageLayout::postMessage(MessageBox::error(_('Keiner der ausgewählten Tage liegt in dem angegebenen Zeitraum!')));
                $this->redirect('course/block_appointments/index');
                return;
            }
        }

        if (Request::int('fromDialog')) {
            $this->redirect('course/timesrooms/index');
        } else {
            $this->relocate('course/timesrooms/index');
        }
    }
}
