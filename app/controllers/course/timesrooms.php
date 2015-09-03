<?php

/**
 * timesrooms.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       3.4
 */
require_once 'app/controllers/authenticated_controller.php';
require_once($GLOBALS['RELATIVE_PATH_RESOURCES'] . "/lib/ResourcesUserRoomsList.class.php");

class Course_TimesroomsController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Request::isXhr()) {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/dialog'));
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        $this->course_id = Request::get('cid', null);

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            throw new Trails_Exception(400);
        }

        if ($this->course_id) {
            $this->course = Seminar::GetInstance($this->course_id);
        }

        if (Navigation::hasItem('course/admin/timesrooms')) {
            Navigation::activateItem('course/admin/timesrooms');
        }
        $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => true);


        PageLayout::setHelpKeyword('Basis.Veranstaltungen');
        PageLayout::addSqueezePackage('raumzeit');
        PageLayout::setTitle(sprintf(_('%sVerwaltung von Zeiten und Räumen'),
            isset($this->course) ? $this->course->getFullname() . ' - ' : ''));

        if (isset($this->flash['question'])) {
            PageLayout::addBodyElements($this->flash['question']);
        }


        $_SESSION['raumzeitFilter'] = Request::quoted('newFilter');

        // bind linkParams for chosen semester and opened dates
        URLHelper::bindLinkParam('raumzeitFilter', $_SESSION['raumzeitFilter']);
        $GLOBALS['cmd'] = Request::option('cmd');
        $this->course->checkFilter();
        $this->setSidebar();
    }

    public function index_action($course_id = null)
    {
        Helpbar::get()->addPlainText(_('Rot'), _('Kein Termin hat eine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Gelb'), _('Mindestens ein Termin hat keine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Grün'), _('Alle Termine haben eine Raumbuchung.'));

        $editParams = array();
        $semesterFormParams = array(
            'formaction' => $this->url_for('course/timesrooms/setSemester/' . $this->course->id)
        );

        if (Request::isXhr()) {
            $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => false);
            $asDialog['data-dialog'] = 'size=50%"';
            $semesterFormParams += $asDialog;
            $editParams['asDialog'] = true;
            $editParams['fromDialog'] = 'true';
        } else {
            $editParams['fromDialog'] = 'false';
        }

        if ($course_id) {
            $this->course_id = $course_id;
            $this->course = Seminar::getInstance($course_id);
        }

        $this->semester = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();

        /**
         * Get Cycles
         */
        $cycles = $this->course->metadate->getCycles();

        $cycle_dates = array();
        foreach ($cycles as $metadate_id => $cycle) {
            $cycle_dates[$metadate_id]['name'] = $cycle->toString('long');
            $dates = $this->course->getSingleDatesForCycle($metadate_id);
            foreach ($dates as $val) {
                foreach ($this->semester as $sem) {
                    if ($_SESSION['raumzeitFilter'] != 'all' && $_SESSION['raumzeitFilter'] == $sem->id) {
                        continue;
                    }
                    if (($sem->beginn <= $val->getStartTime()) && ($sem->ende >= $val->getStartTime())) {
                        $cycle_dates[$metadate_id]['dates'][$sem->id][] = $val;
                    }
                }
            }
        }

        $this->cycle_dates = $cycle_dates;

        /**
         * GET Single Dates
         */
        $_single_dates = $this->course->getSingleDates(true, true, true);

        $single_dates = array();
        foreach ($_single_dates as $id => $val) {
            foreach ($this->semester as $sem) {
                if ($_SESSION['raumzeitFilter'] != 'all' && $_SESSION['raumzeitFilter'] == $sem->id) {
                    continue;
                }
                if (($sem->beginn <= $val->getStartTime()) && ($sem->ende >= $val->getStartTime())) {
                    $single_dates[$sem->id][] = $val;
                }
            }
        }

        $this->single_dates = $single_dates;
        $this->semesterFormParams = $semesterFormParams;
        $this->editParams = $editParams;
    }

    /**
     * Edit the start-semester of a course
     * @param null $course_id
     * @throws Trails_DoubleRenderError
     */
    public function editSemester_action($course_id = null)
    {
        if (!Request::isXhr()) {
            $this->redirect('course/timesrooms/index');

            return;
        }

        if ($course_id) {
            $this->course_id = $course_id;
            $this->course = Seminar::getInstance($course_id);
        }

        $this->semester = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();
        $this->cycles = $this->course->metadate->getCycles();
    }

    /**
     * Primary function to edit date-informations
     * @param      $termin_id
     * @param null $metadate_id
     */
    public function editDate_action($termin_id, $metadate_id = null)
    {
        if (!isset($metadate_id)) {
            $dates = $this->course->getSingleDates(true, true, true);
            $this->date_info = $dates[$termin_id];
        } else {
            $dates = $this->course->getSingleDatesForCycle($metadate_id);
            $this->date_info = $dates[$termin_id];
        }
        $this->termin_id = $termin_id;
        $this->termin = SingleDate::getInstance($termin_id);
        $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        $this->types = $GLOBALS['TERMIN_TYP'];

        if ($request = RoomRequest::findByDate($this->termin->getSingleDateID())) {
            $this->params = array('request_id' => $request->getId());
        } else {
            $this->params = array('new_room_request_type' => 'date_' . $this->termin->getSingleDateID());
        }

        $this->dozenten = $this->course->getMembers('dozent');
        $this->related_persons = $this->termin->getRelatedPersons();
        $this->related_groups = $this->termin->getRelatedGroups();
        $this->gruppen = Statusgruppen::findBySeminar_id($this->course->id);
    }


    /**
     * Save date-information
     * @param $termin_id
     * @throws Trails_DoubleRenderError
     */
    public function saveDate_action($termin_id)
    {
        $termin = SingleDate::getInstance($termin_id);
        $start_time = sprintf('%s %s', Request::get('date'), Request::get('start_time'));
        $end_time = sprintf('%s %s', Request::get('date'), Request::get('end_time'));
        $termin->setTime(strtotime($start_time), strtotime($end_time));
        $termin->setDateType(Request::int('course_type'));

        $related_groups = Request::get('related_statusgruppen');
        if (!empty($related_groups)) {
            $related_groups = explode(',', $related_groups);
            $termin->clearRelatedGroups();
            foreach ($related_groups as $group_id) {
                $termin->addRelatedGroup($group_id);
            }
        } else {
            $termin->clearRelatedGroups();
        }

        $related_users = Request::get('related_teachers');

        if (!empty($related_users)) {
            $related_users = explode(',', $related_users);
            $termin->clearRelatedPersons();
            foreach ($related_users as $user_id) {
                $termin->addRelatedPerson($user_id);
            }
        }


        // Set Room
        if (Request::option('room') == 'room') {
            if ($resObj = $termin->bookRoom(Request::option('room_sd'))) {
                $this->course->createMessage(sprintf(_('Der Termin %s wurde geändert und der Raum %s gebucht, etwaige freie Ortsangaben wurden entfernt.'),
                    $termin->toString(), $resObj->getName()));
            } else {
                $this->course->createError(sprintf(_('Der angegebene Raum konnte für den Termin %s nicht gebucht werden!'), $termin->toString()));
            }
        } else if (Request::option('room') == 'noroom') {
            $termin->killAssign();
            $this->course->createMessage(sprintf(_('Der Termin %s wurde geändert, etwaige freie Ortsangaben und Raumbuchungen wurden entfernt.'), '<b>' . $termin->toString() . '</b>'));
        } else if (Request::option('room') == 'freetext') {
            $termin->setFreeRoomText(Request::quoted('freeRoomText_sd'));
            $termin->killAssign();
            $this->course->createMessage(sprintf(_('Der Termin %s wurde geändert, etwaige Raumbuchung wurden entfernt und stattdessen der angegebene Freitext eingetragen!'), '<b>' . $termin->toString() . '</b>'));
        }

        if ($termin->store()) {
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this->course);
            $this->course->appendMessages($termin->getMessages());
            $this->displayMessages();
        }
        $this->redirect('course/timesrooms/index#' . $termin->metadate_id,
            array('contentbox_open' => $termin->metadate_id));
    }


    /**
     * Create Single Date
     */
    public function createSingleDate_action()
    {
        if ($this->flash['request']) {
            foreach (words('date start_time end_time room related_teachers related_statusgruppen freeRoomText dateType fromDialog') as $value) {
                Request::set($value, $this->flash['request'][$value]);
            }
        }
        $this->editParams = array('fromDialog' => Request::get('fromDialog'));
        $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        $this->teachers = $this->course->getMembers('dozent');
        $this->groups = Statusgruppen::findBySeminar_id($this->course_id);
    }

    /**
     * Save Single Date
     * @throws Trails_DoubleRenderError
     */
    public function saveSingleDate_action()
    {
        CSRFProtection::verifyRequest();
        $termin = new SingleDate(array('seminar_id' => $this->course->id));
        $start_time = strtotime(sprintf('%s %s', Request::get('date'), Request::get('start_time')));
        $end_time = strtotime(sprintf('%s %s', Request::get('date'), Request::get('end_time')));

        if ($start_time > $end_time) {
            $this->flash['request'] = Request::getInstance();
            PageLayout::postMessage(MessageBox::error(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!')));
            $this->redirect('course/timesrooms/createSingleDate');
            return;
        }

        $termin->setTime($start_time, $end_time);
        $termin->setDateType(Request::get('dateType'));
        $termin->store();

        if ($start_time < $this->course->filterStart || $end_time > $this->course->filterEnd) {
            $this->course->setFilter('all');
        }
        if (!Request::get('room') || Request::get('room') === 'nothing') {
            $termin->setFreeRoomText(Request::get('freeRoomText'));
            $termin->store();
            $this->course->addSingleDate($termin);
        } else {
            $this->course->addSingleDate($termin);
            $this->course->bookRoomForSingleDate($termin->getSingleDateID(), Request::get('room'));
        }
        $teachers = $this->course->getMembers('dozent');
        foreach (Request::getArray('related_teachers') as $dozent_id) {
            if (in_array($dozent_id, array_keys($teachers))) {
                $termin->addRelatedPerson($dozent_id);
            }
        }
        foreach (Request::getArray('related_statusgruppen') as $statusgruppe_id) {
            $termin->addRelatedGroup($statusgruppe_id);
        }
        $this->course->createMessage(sprintf(_('Der Termin %s wurde hinzugefügt!'), $termin->toString()));
        $this->course->store();
        $this->displayMessages();

        if (Request::get('fromDialog') == 'true') {
            $this->redirect('course/timesrooms/index');
        } else {
            $this->relocate('course/timesrooms/index');
        }
    }


    public function deleteSingle_action($termin_id, $sub_cmd = 'delete')
    {
        $this->deleteDate($termin_id, $sub_cmd, Request::option('cycle_id'));
        $this->displayMessages();
        $params = array();
        if (Request::option('cycle_id')) {
            $params['contentbox_open'] = Request::get('cycle_id');
        }
        $this->redirect('course/timesrooms/index' . (Request::get('cycle_id') ? '#' . Request::get('cycle_id') : ''), $params);
    }


    public function undeleteSingle_action($termin_id)
    {
        if ($this->course->unDeleteSingleDate($termin_id)) {
            $termin = SingleDate::getInstance($termin_id);
            $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'), $termin->toString()));
            $this->displayMessages();
        }
        $params = array();
        if ($termin->metadate_id) {
            $params['contentbox_open'] = $termin->metadate_id;
        }
        $this->redirect('course/timesrooms/index' . ($termin->metadate_id ? '#' . $termin->metadate_id : ''), $params);
    }


    public function stack_action($cycle_id = '')
    {
        $ids = Request::getArray('single_dates');
        switch (Request::get('method')) {
            case 'edit':
                $this->editStack($ids, $cycle_id);
                break;
            case 'delete':
                $this->deleteStack($ids, $cycle_id);
        }
    }

    public function editStack($ids, $cycle_id)
    {
        $this->cycle_id = $cycle_id;
        $this->flash['ids'] = $ids;
        $this->teachers = $this->course->getMembers('dozent');
        $this->gruppen = Statusgruppen::findBySeminar_id($this->course->id);
        $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        $this->render_template('course/timesrooms/editStack');
    }

    public function deleteStack($ids, $cycle_id = '')
    {
        if (!empty($ids)) {
            foreach($ids as $id) {
                $this->deleteDate($id, Request::get('sub_cmd'), $cycle_id);
            }
        }
        $this->displayMessages();
        if(Request::get('fromDialog') == 'true') {
            $this->redirect('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
        } else {
            $this->relocate('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
        }
    }

    public function saveStack_action($cycle_id)
    {
        $ids = $this->flash['ids'];

        if (empty($ids)) {
            PageLayout::postMessage(MessageBox::error(_('Sie haben keine Termine ausgewählt!')));
            $this->redirect('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
            return;
        }
        $this->ids = $ids;
        switch (Request::get('method')) {
            case 'edit':
                $this->saveEditedStack($cycle_id);
                break;
        }
    }

    public function saveEditedStack($cycle_id)
    {
        /**
         * TODO
         */
        PageLayout::postMessage(MessageBox::success(_('Die Änderungen wurden erfolgreich gespeichert!')));
        if(Request::get('fromDialog') == 'true') {
            $this->redirect('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
        } else {
            $this->relocate('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
        }
    }

    /**
     * Create a cycle
     */
    public function createCycle_action($cycle_id = null)
    {
        $this->set_content_type('text/html;charset=windows-1252');

        if ($this->flash['request']) {
            foreach (words('day start_time end_time description cycle startWeek teacher_sws fromDialog') as $value) {
                Request::set($value, $this->flash['request'][$value]);
            }
        }
        $this->editParams = array('fromDialog' => Request::get('fromDialog'));
        if (!is_null($cycle_id)) {
            $this->cycle = $this->course->metadate->cycles[$cycle_id];
            $this->has_bookings = false;

            foreach ($this->cycle->getSingleDates() as $singleDate) {
                if ($singleDate->getStarttime() > (time() - 3600) && $singleDate->hasRoom()) {
                    $this->has_bookings = true;
                    break;
                }
            }
        }

        $this->start_weeks = $this->getStartWeeks();
    }

    public function saveCycle_action()
    {
        CSRFProtection::verifyRequest();

        $now = time();
        $startHour = strftime('%H', strtotime(Request::get('start_time')));
        $startMinute = strftime('%M', strtotime(Request::get('start_time')));
        $endHour = strftime('%H', strtotime(Request::get('end_time')));
        $endMinute = strftime('%M', strtotime(Request::get('end_time')));

        if ($startHour > $endHour) {
            $this->flash['request'] = Request::getInstance();
            PageLayout::postMessage(MessageBox::error(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!')));
            $this->redirect('course/timesrooms/createCycle');
            return;
        }

        $cycle = new SeminarCycleDate();
        $cycle->id = md5(uniqid('metadate_id'));
        $cycle->seminar_id = $this->course->id;
        $cycle->weekday = Request::int('day');
        $cycle->description = Request::get('description');
        $cycle->sws = round(str_replace(',', '.', Request::get('teacher_sws')), 1);
        $cycle->cycle = Request::int('cycle');
        $cycle->week_offset = Request::int('startWeek');
        $cycle->end_offset = Request::int('endWeek') != 0 ? Request::int('endWeek') : null;
        $cycle->mkdate = $now;
        $cycle->chdate = $now;
        $cycle->start_time = sprintf('%02u:%02u:00', $startHour, $startMinute);
        $cycle->end_time = sprintf('%02u:%02u:00', $endHour, $endMinute);

        if ($cycle->store()) {
            $cycle_info = $cycle->toString();
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
            StudipLog::log("SEM_ADD_CYCLE", $this->course->id, NULL, $cycle_info);
            $this->course->createMessage(sprintf(_('Die regelmäßige Veranstaltungszeit %s wurde hinzugefügt!'), $cycle_info));
            $this->displayMessages();
            if (Request::get('fromDialog') == 'true') {
                $this->redirect('course/timesrooms/index');
            } else {
                $this->relocate('course/timesrooms/index');
            }
            return;
        } else {
            $this->flash['request'] = Request::getInstance();
            $this->course->createError(_('Die regelmäßige Veranstaltungszeit konnte nicht hinzugefügt werden! Bitte überprüfen Sie Ihre Eingabe.'));
            $this->displayMessages();
            $this->redirect('course/timesrooms/createSingleDate');
            return;
        }


    }

    /**
     * Save cycle
     * @throws Trails_DoubleRenderError
     */
    public function editCycle_action($cycle_id)
    {
        $cycle = $this->course->metadate->cycles[$cycle_id];
        // Prepare Request for saving Request
        $data['cycle_id'] = $cycle_id;
        $data['startWeek'] = Request::get('startWeek');
        $data['week_offset'] = Request::get('startWeek');
        $data['turnus'] = Request::get('cycle');
        $data['cycle'] = Request::get('cycle');
        $data['description'] = studip_utf8decode(Request::get('description'));
        $data['day'] = Request::int('day');
        $data['weekday'] = Request::int('day');
        $data['start_stunde'] = strftime('%H', strtotime(Request::get('start_time')));;
        $data['start_minute'] = strftime('%M', strtotime(Request::get('start_time')));;
        $data['end_stunde'] = strftime('%H', strtotime(Request::get('end_time')));;
        $data['end_minute'] = strftime('%M', strtotime(Request::get('end_time')));;
        $data['sws'] = Request::get('teacher_sws');


        $new_start = mktime($data['start_stunde'], $data['start_minute']);
        $new_end = mktime($data['end_stunde'], $data['end_minute']);
        $old_start = mktime($cycle->getStartStunde(), $cycle->getStartMinute());
        $old_end = mktime($cycle->getEndStunde(), $cycle->getEndMinute());

        $same_time = false;

        // only apply changes, if the user approved the change or
        // the change does not need any approval
        if ($data['description'] != $cycle->getDescription()) {
            $this->course->createMessage(_('Die Beschreibung des regelmäßigen Eintrags wurde geändert.'));
            $cycle->setDescription($data['description']);
            $cycle->store();
            $message = true;
        }

        if ($old_start == $new_start && $old_end == $new_end) {
            $same_time = true;
        }
        if ($data['startWeek'] != $cycle->week_offset) {
            $this->course->setStartWeek($data['startWeek'], $cycle->metadate_id);
            $message = true;
        }
        if ($data['turnus'] != $cycle->cycle) {
            $this->course->setTurnus($data['turnus'], $cycle->metadate_id);
            $message = true;
        }
        if ($data['day'] != $cycle->day) {
            $message = true;
            $same_time = false;
        }
        if (round(str_replace(',', '.', $data['sws']), 1) != $cycle->sws) {
            $cycle->sws = $data['sws'];
            $this->course->createMessage(_('Die Semesterwochenstunden für Dozenten des regelmäßigen Eintrags wurden geändert.'));
            $message = true;
        }

        $change_from = $cycle->toString();
        if ($this->course->metadate->editCycle($data)) {
            if (!$same_time) {
                // logging >>>>>>
                StudipLog::log("SEM_CHANGE_CYCLE", $this->course->getId(), NULL, $change_from . ' -> ' . $cycle->toString());
                NotificationCenter::postNotification("CourseDidChangeSchedule", $this->course);
                // logging <<<<<<
                $this->course->createMessage(sprintf(_('Die regelmäßige Veranstaltungszeit wurde auf "%s" für alle in der Zukunft liegenden Termine geändert!'),
                    '<b>' . getWeekday($data['day']) . ', ' . $data['start_stunde'] . ':' . $data['start_minute'] . ' - ' .
                    $data['end_stunde'] . ':' . $data['end_minute'] . '</b>'));
                $message = true;
            }
        } else {
            if (!$same_time) {
                $this->course->createInfo(sprintf(_('Die regelmäßige Veranstaltungszeit wurde auf "%s" geändert, jedoch gab es keine Termine die davon betroffen waren.'),
                    '<b>' . getWeekday($data['day']) . ', ' . $data['start_stunde'] . ':' . $data['start_minute'] . ' - ' .
                    $data['end_stunde'] . ':' . $data['end_minute'] . '</b>'));
                $message = true;
            }
        }
        $cycle->storeCycleDate();
        $this->course->metadate->sortCycleData();

        if (!$message) {
            $this->course->createInfo('Sie haben keine Änderungen vorgenommen!');
        }
        $this->displayMessages();
        $this->redirect('course/timesrooms/index');
    }

    /**
     * Add information to cancled / holiday date
     * @param $termin_id
     */
    public function cancel_action($termin_id)
    {
        if (Request::get('asDialog')) {
            $this->asDialog = true;
        }
        $this->termin = SingleDate::getInstance($termin_id);
    }

    /**
     * @param $termin_id
     * @throws Trails_DoubleRenderError
     */
    public function saveComment_action($termin_id)
    {
        $termin = SingleDate::getInstance($termin_id);
        $old_comment = $termin->getComment();
        $termin->setComment(Request::get('cancel_comment'));
        if ($termin->getComment() != $old_comment) {
            $this->course->createMessage(sprintf(_('Der Kommtentar des gelöschten Termins %s wurde geändert.'), $termin->toString()));
        } else {
            $this->course->createInfo(sprintf(_('Der gelöschte Termin %s wurde nicht verändert.'), $termin->toString()));
        }
        if (Request::int('cancel_send_message')) {
            $snd_messages = raumzeit_send_cancel_message(Request::get('cancel_comment'), $termin);
            if ($snd_messages) {
                $this->course->createInfo(sprintf(_('Es wurden %s Benachrichtigungen gesendet.'), $snd_messages));
            }
        }
        $termin->store();
        $this->displayMessages();
        $this->redirect('course/timesrooms/index#' . $termin->metadate_id, array('contentbox_open' => $termin->metadate_id));
    }

    function setSidebar()
    {
        $widget = new SelectWidget(_('Semester'), $this->url_for('course/timesrooms/index', array('cmd' => 'applyFilter')), 'newFilter');
        $selection = raumzeit_get_semesters($this->course, new SemesterData(), $_SESSION['raumzeitFilter']);
        foreach ($selection as $item) {
            $element = new SelectElement($item['value'], $item['linktext'], $item['is_selected']);
            $widget->addElement($element);
        }
        Sidebar::Get()->addWidget($widget);


        if ($GLOBALS['perm']->have_perm("admin")) {
            include_once 'app/models/AdminCourseFilter.class.php';

            $list = new SelectorWidget();
            $list->setUrl($this->url_for('/set_course'));
            $list->setSelectParameterName('cid');
            foreach (AdminCourseFilter::get()->getCourses(false) as $seminar) {
                $list->addElement(new SelectElement($seminar['Seminar_id'], $seminar['Name']), 'select-' . $seminar['Seminar_id']);
            }
            $list->setSelection($this->course_id);
            Sidebar::Get()->addWidget($list);
        }

    }


    function setSemester_action($course_id)
    {
        $current_semester = Semester::findCurrent();
        $start_semester = Semester::find(Request::get('startSemester'));
        if ((int)Request::get('endSemester') != -1) {
            $end_semester = Semester::find(Request::get('endSemester'));
        } else {
            $end_semester = -1;
        }
        $course = Seminar::GetInstance($course_id);

        // The user meant actually to choose "1 Semester"
        if ($start_semester == $end_semester) {
            $end_semester = 0;
        }

        // test, if start semester is before the end semester
        // btw.: end_semester == 0 means a duration of one semester (ja logisch! :) )
        if ($end_semester != 0 && $end_semester != -1 && $start_semester->beginn >= $end_semester->beginn) {
            PageLayout::postMessage(MessageBox::error(_('Das Startsemester liegt nach dem Endsemester!')));
            // Redirect
        } else {

            $course->setStartSemester($start_semester->beginn);
            if ($end_semester != -1) {
                $course->setEndSemester($end_semester->beginn);
            } else {
                $course->setEndSemester($end_semester);
            }
            $course->removeAndUpdateSingleDates();


            // If the new duration includes the current semester, we set the semester-chooser to the current semester
            if ($current_semester->beginn >= $course->getStartSemester() && $current_semester->beginn <= $course->getEndSemesterVorlesEnde()) {
                $course->setFilter($current_semester->beginn);
            } else {
                // otherwise we set it to the first semester
                $course->setFilter($course->getStartSemester());
            }

        }

        $course->store();

        $messages = $course->getStackedMessages();
        foreach ($messages as $type => $msg) {
            PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
        }

        if (Request::submitted('save_close')) {
            if (Request::isXhr()) {
                $this->relocate('admin/courses');
            } else {
                $this->relocate('course/timesrooms/index/' . $course_id);
            }
        } else {
            $this->redirect('course/timesrooms/index', array('cid' => $course_id));
        }

    }

    function set_course_action()
    {
        $this->redirect('course/timesrooms/index');

        return;//die('course');
    }


    private function displayMessages($messages = array())
    {
        $messages = $messages ?: $this->course->getStackedMessages();
        if (!empty($messages)) {
            foreach ($messages as $type => $msg) {
                PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
            }
        }
    }

    private function getStartWeeks()
    {
        $start_weeks = array();
        $all_semester = SemesterData::GetInstance()->getAllSemesterData();
        if ($this->course->duration_time != -1) {
            $end_index = SemesterData::GetSemesterIndexById($this->course->end_semester->id);
        } else {
            $end_index = array_pop(array_keys($all_semester));
        }
        $start_index = SemesterData::GetSemesterIndexById($this->course->start_semester->id);
        $tmp_first_date = getCorrectedSemesterVorlesBegin($start_index);
        $_tmp_first_date = strftime('%d.%m.%Y', $tmp_first_date);
        $end_date = $all_semester[$end_index]['vorles_ende'];

        $i = 0;

        while ($tmp_first_date < $end_date) {

            $start_weeks[$i]['text'] = ($i + 1) . '. ' . _("Semesterwoche") . ' (' . _("ab") . ' ' . strftime("%d.%m.%Y", $tmp_first_date) . ')';
            $start_weeks[$i]['selected'] = ($this->course->getStartWeek() == $i);
            $i++;
            $tmp_first_date = strtotime(sprintf('+%u weeks %s', $i, $_tmp_first_date));
        }

        return $start_weeks;
    }

    public function deleteDate($termin_id, $sub_cmd, $cycle_id)
    {
        $termin = SingleDate::getInstance($termin_id);

        if ($sub_cmd == 'cancel') {
            $this->course->cancelSingleDate($termin_id, $cycle_id);
        } else {
            if ($this->course->deleteSingleDate($termin_id, $cycle_id)) {
                if (Request::get('approveDelete')) {
                    if (Config::get()->RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW) {
                        $this->course->createMessage(sprintf(_('Sie haben den Termin %s gelöscht, dem ein Thema zugeorndet war.
                    Sie können das Thema in der %sExpertenansicht des Ablaufplans%s einem anderen Termin (z.B. einem Ausweichtermin) zuordnen.'),
                            $termin->toString(), '<a href="' . URLHelper::getLink('themen.php?cmd=changeViewMode&newFilter=expert') . '">', '</a>'));
                    } else {
                        if ($termin->hasRoom()) {
                            $this->course->createMessage(sprintf(_('Der Termin %s wurde gelöscht! Die Buchung für den Raum %s wurde gelöscht.'),
                                $termin->toString(), $termin->getRoom()));
                        } else {
                            $this->course->createMessage(sprintf(_('Der Termin %s wurde gelöscht!'), $termin->toString()));
                        }
                    }
                } // no approval needed, delete unquestioned
                else {
                    $this->course->createMessage(sprintf(_("Der Termin %s wurde gelöscht!"), $termin->toString()));
                }
            }
        }
    }
}

