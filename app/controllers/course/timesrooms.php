<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
        if (!$GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException(_('Sie haben nicht die nötigen Rechte, um diese Seite zu betreten.'));
        }

        $this->course_id = Request::get('cid', null);

        if ($this->course_id) {
            $this->course = Seminar::GetInstance($this->course_id);
        }

        if (Navigation::hasItem('course/admin/timesrooms')) {
            Navigation::activateItem('course/admin/timesrooms');
        }
        $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => true);

        $this->setSidebar();
        PageLayout::setHelpKeyword('Basis.Veranstaltungen');
        PageLayout::addSqueezePackage('raumzeit');
        PageLayout::setTitle(sprintf(_('%sVerwaltung von Zeiten und Räumen'),
            isset($this->course) ? $this->course->getFullname() . ' - ' : ''));
    }

    public function index_action($course_id = null)
    {
        if (request::isXhr()) {
            $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => false);
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
        foreach($cycles as $metadate_id => $cycle) {
            $cycle_dates[$metadate_id]['name'] = $cycle->toString('long');
            $dates = $this->course->getSingleDatesForCycle($metadate_id);
            foreach ($dates as $val) {
                foreach ($this->semester as $sem) {
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
        foreach ($_single_dates as $val) {
            foreach ($this->semester as $sem) {
                if (($sem->beginn <= $val->getStartTime()) && ($sem->ende >= $val->getStartTime())) {
                    $single_dates[$sem->id][] = $val;
                }
            }
        }
        $this->single_dates = $single_dates;


        $semesterFormParams = array(
            'formaction' => $this->url_for('course/timesrooms/set_semester/' . $this->course->id)
        );

        if (Request::isXhr()) {
            $asDialog['data-dialog'] = 'size=50%"';
            $semesterFormParams += $asDialog;
        }
        $this->semesterFormParams = $semesterFormParams;
    }

    public function edit_semester_action($course_id = null)
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

        $this->dozenten = $this->course->getMembers('dozent');
        $this->related_persons = $this->termin->getRelatedPersons();
        $this->related_groups = $this->termin->getRelatedGroups();
        $this->gruppen = Statusgruppen::findBySeminar_id($this->course->id);;
    }

    public function editRoom_action($termin_id, $metadate_id = null)
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
    }

    public function editSingleDate_action($termin_id)
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
                    '<strong>' . $termin->toString() . '</strong>',
                    '<strong>' . $resObj->getName() . '</strong>'));
            } else {
                $this->course->createError(sprintf(_('Der angegebene Raum konnte für den Termin %s nicht gebucht werden!'),
                    '<strong>' . $termin->toString() . '</strong>'));
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
        $this->redirect($this->url_for('course/timesrooms/index#' . $termin->metadate_id, array('contentbox_open' => $termin->metadate_id)));
    }

    public function editCycle_action($cycle_id = null)
    {

    }

    public function editIrregular_action($id = 0)
    {


    }

    public function editBlock_action($id = 0)
    {

    }


    public function cancel_action($termin_id)
    {
        $this->termin = SingleDate::getInstance($termin_id);
    }

    public function save_comment_action($termin_id)
    {
        $termin = SingleDate::getInstance($termin_id);
        $old_comment = $termin->getComment();
        $termin->setComment(Request::get('cancel_comment'));
        if ($termin->getComment() != $old_comment) {
            $this->course->createMessage(sprintf(_('Der Kommtentar des gelöschten Termins %s wurde geändert.'), '<b>' . $termin->toString() . '</b>'));
        } else {
            $this->course->createInfo(sprintf(_('Der gelöschte Termin %s wurde nicht verändert.'), '<b>' . $termin->toString() . '</b>'));
        }
        if (Request::int('cancel_send_message')) {
            $snd_messages = raumzeit_send_cancel_message(Request::get('cancel_comment'), $termin);
            if ($snd_messages) {
                $this->course->createInfo(sprintf(_('Es wurden %s Benachrichtigungen gesendet.'), $snd_messages));
            }
        }
        $termin->store();
        $this->displayMessages();
        $this->redirect($this->url_for('course/timesrooms/index#' . $termin->metadate_id, array('contentbox_open' => $termin->metadate_id)));
    }

    public function undeleteSingle_action($termin_id) {
        if ($this->course->unDeleteSingleDate($termin_id)) {
            $termin = SingleDate::getInstance($termin_id);
            $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'), $termin->toString()));
            $this->displayMessages();
        }
        $params = array();
        if($termin->metadate_id) {
            $params['contentbox_open'] = $termin->metadate_id;
        }
        $this->redirect($this->url_for('course/timesrooms/index'. ($termin->metadate_id ? '#'. $termin->metadate_id : ''), $params));
    }

    function setSidebar()
    {
        $sidebar = Sidebar::get();
        $semesterSelect = new SemesterSelectorWidget($this->url_for('/set_semester'));
        $sidebar->addWidget($semesterSelect);

        if ($GLOBALS['perm']->have_perm("admin")) {
            include_once 'app/models/AdminCourseFilter.class.php';

            $list = new SelectorWidget();
            $list->setUrl($this->url_for('/set_course'));
            $list->setSelectParameterName("cid");
            foreach (AdminCourseFilter::get()->getCourses(false) as $seminar) {
                $list->addElement(new SelectElement($seminar['Seminar_id'], $seminar['Name']), 'select-' . $seminar['Seminar_id']);
            }
            $list->setSelection($this->course_id);
            $sidebar->addWidget($list);
        }

        if (Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ENABLE_BOOKINGSTATUS_COLORING) {
            $template = $GLOBALS['template_factory']->open('raumzeit/legend.php');
            $element = new WidgetElement($template->render());
            $widget = new SidebarWidget();
            $widget->setTitle(_('Legende'));
            $widget->addElement($element);
            $sidebar->addWidget($widget);
        }
    }


    function set_semester_action($course_id)
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
        $this->redirect($this->url_for('course/timesrooms/index'));

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
}

