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
            throw new AccessDeniedException(_('Sie haben nicht die n�tigen Rechte, um diese Seite zu betreten.'));
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
        PageLayout::setTitle(sprintf(_('%sVerwaltung von Zeiten und R�umen'),
            isset($this->course) ? $this->course->getFullname() . ' - ' : ''));

        if (isset($this->flash['question'])) {
            PageLayout::addBodyElements($this->flash['question']);
        }
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
        $semesters = $this->semester;
        if (!Request::isXhr() && isset($_SESSION['selectedTimesRoomSemester']) && $_SESSION['selectedTimesRoomSemester'] != 'all') {
            $semesters = array_filter($semesters, function ($a) {
                return $_SESSION['selectedTimesRoomSemester'] == $a->beginn;
            });
        }

        /**
         * Get Cycles
         */
        $cycles = $this->course->metadate->getCycles();
        $cycle_dates = array();
        foreach ($cycles as $metadate_id => $cycle) {
            $cycle_dates[$metadate_id]['name'] = $cycle->toString('long');
            $dates = $this->course->getSingleDatesForCycle($metadate_id);
            foreach ($dates as $val) {
                foreach ($semesters as $sem) {
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
            foreach ($semesters as $sem) {
                if (($sem->beginn <= $val->getStartTime()) && ($sem->ende >= $val->getStartTime())) {
                    $single_dates[$sem->id][] = $val;
                }
            }
        }
        $this->single_dates = $single_dates;


        $semesterFormParams = array(
            'formaction' => $this->url_for('course/timesrooms/set_semester/' . $this->course->id)
        );

        $editParams = array();
        if (Request::isXhr()) {
            $asDialog['data-dialog'] = 'size=50%"';
            $semesterFormParams += $asDialog;
            $editParams['asDialog'] = true;
        }
        $this->semesterFormParams = $semesterFormParams;
        $this->editParams = $editParams;


        NotificationCenter::addObserver($this, 'addSemesterWidget', 'SidebarWillRender');

    }


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
                $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert und der Raum %s gebucht, etwaige freie Ortsangaben wurden entfernt.'),
                    '<strong>' . $termin->toString() . '</strong>',
                    '<strong>' . $resObj->getName() . '</strong>'));
            } else {
                $this->course->createError(sprintf(_('Der angegebene Raum konnte f�r den Termin %s nicht gebucht werden!'),
                    '<strong>' . $termin->toString() . '</strong>'));
            }
        } else if (Request::option('room') == 'noroom') {
            $termin->killAssign();
            $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert, etwaige freie Ortsangaben und Raumbuchungen wurden entfernt.'), '<b>' . $termin->toString() . '</b>'));
        } else if (Request::option('room') == 'freetext') {
            $termin->setFreeRoomText(Request::quoted('freeRoomText_sd'));
            $termin->killAssign();
            $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert, etwaige Raumbuchung wurden entfernt und stattdessen der angegebene Freitext eingetragen!'), '<b>' . $termin->toString() . '</b>'));
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


    public function createSingleDate_action()
    {
        if ($this->flash['request']) {
            foreach (words('date start_time end_time room related_teachers related_statusgruppen freeRoomText dateType') as $value) {
                Request::set($value, $this->flash['request'][$value]);
            }
        }
        $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        $this->teachers = $this->course->getMembers('dozent');
        $this->groups = Statusgruppen::findBySeminar_id($this->course_id);
    }

    public function saveSingleDate_action()
    {
        CSRFProtection::verifyRequest();
        $termin = new SingleDate(array('seminar_id' => $this->course->id));
        $start_time = strtotime(sprintf('%s %s', Request::get('date'), Request::get('start_time')));
        $end_time = strtotime(sprintf('%s %s', Request::get('date'), Request::get('end_time')));

        if ($start_time > $end_time) {
            $this->flash['request'] = Request::getInstance();
            PageLayout::postMessage(MessageBox::error(_('Die Zeitangaben sind nicht korrekt. Bitte �berpr�fen Sie diese!')));
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
        $this->course->createMessage(sprintf(_('Der Termin %s wurde hinzugef�gt!'), '<b>' . $termin->toString() . '</b>'));
        $this->course->store();
        $this->displayMessages();
        $this->redirect('course/timesrooms/index');
    }

    public function createCycle_action()
    {
        if ($this->flash['request']) {
            foreach (words('day start_time end_time description') as $value) {
                Request::set($value, $this->flash['request'][$value]);
            }
        }

        $this->start_weeks = $this->getStartWeeks();
    }

    public function saveCycle_action()
    {
        global $newCycle;
        if ($cycle_id = $this->course->addCycle($_REQUEST)) {
            $info = $this->sem->metadate->cycles[$cycle_id]->toString();
            $sem->createMessage(sprintf(_("Die regelm��ige Veranstaltungszeit \"%s\" wurde hinzugef�gt!"), '<b>' . $info . '</b>'));
        } else {
            $sem->createError(_("Die regelm��ige Veranstaltungszeit konnte nicht hinzugef�gt werden! Bitte �berpr�fen Sie Ihre Eingabe."));
            $newCycle = true;
        }
    }

    public function cancel_action($termin_id)
    {
        if (Request::get('asDialog')) {
            $this->asDialog = true;
        }
        $this->termin = SingleDate::getInstance($termin_id);
    }

    public function save_comment_action($termin_id)
    {
        $termin = SingleDate::getInstance($termin_id);
        $old_comment = $termin->getComment();
        $termin->setComment(Request::get('cancel_comment'));
        if ($termin->getComment() != $old_comment) {
            $this->course->createMessage(sprintf(_('Der Kommtentar des gel�schten Termins %s wurde ge�ndert.'), '<b>' . $termin->toString() . '</b>'));
        } else {
            $this->course->createInfo(sprintf(_('Der gel�schte Termin %s wurde nicht ver�ndert.'), '<b>' . $termin->toString() . '</b>'));
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

    public function deleteSingle_action($termin_id, $sub_cmd = 'delete')
    {
        $termin = SingleDate::getInstance($termin_id);
        if (Request::get('approveDelete')) {
            if (Config::get()->RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW) {
                $this->course->createMessage(sprintf(_('Sie haben den Termin %s gel�scht, dem ein Thema zugeorndet war. Sie k�nnen das Thema in der %sExpertenansicht des Ablaufplans%s einem anderen Termin (z.B. einem Ausweichtermin) zuordnen.'),
                    $termin->toString(), '<a href="' . URLHelper::getLink('themen.php?cmd=changeViewMode&newFilter=expert') . '">', '</a>'));
            } else {
                if ($termin->hasRoom()) {
                    $this->course->createMessage(sprintf(_('Der Termin %s wurde gel�scht! <br> Die Buchung f�r den Raum %s wurde gel�scht.'),
                        $termin->toString(), $termin->getRoom()));
                } else {
                    $this->course->createMessage(sprintf(_('Der Termin %s wurde gel�scht!'), $termin->toString()));
                }
            }
        } // no approval needed, delete unquestioned
        else {
            $this->course->createMessage(sprintf(_("Der Termin %s wurde gel�scht!"), $termin->toString()));
        }
        if ($sub_cmd == 'cancel') {
            $this->course->cancelSingleDate($termin_id, $termin->metadate_id);
        } else {
            $this->course->deleteSingleDate($termin_id, $termin->metadate_id);
        }
        $this->displayMessages();
        $params = array();
        if ($termin->metadate_id) {
            $params['contentbox_open'] = $termin->metadate_id;
        }
        $this->redirect($this->url_for('course/timesrooms/index' . ($termin->metadate_id ? '#' . $termin->metadate_id : ''), $params));
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
        $this->redirect($this->url_for('course/timesrooms/index' . ($termin->metadate_id ? '#' . $termin->metadate_id : ''), $params));
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

    public function addSemesterWidget()
    {
        $sidebar = Sidebar::Get();

        $widget = new SelectWidget(_('Semester'), $this->url_for('course/timesrooms/set_semester_filter'), 'newFilter');
        $selection = raumzeit_get_semesters($this->course, new SemesterData(), $_SESSION['selectedTimesRoomSemester']);
        foreach ($selection as $item) {
            $element = new SelectElement($item['value'], $item['linktext'], $item['is_selected']);
            $widget->addElement($element);
        }
        if ($sidebar->hasWidget('semesterselector')) {
            $sidebar->insertWidget($widget, 'semesterselector');
            $sidebar->removeWidget('semesterselector');
        }
    }

    public function set_semester_filter_action()
    {
        $_SESSION['selectedTimesRoomSemester'] = Request::get('newFilter');
        PageLayout::postMessage(MessageBox::success(_('Das gew�nschte Semester wurde ausgew�hlt!')));
        $this->redirect('course/timesrooms/index');
    }

    private function getStartWeeks()
    {
        // get possible start-weeks
        $start_weeks = array();
        $semester = new SemesterData();
        $semester_index = get_sem_num($this->course->getStartSemester());
        $tmp_first_date = getCorrectedSemesterVorlesBegin($semester_index);
        $_tmp_first_date = strftime('%d.%m.%Y', $tmp_first_date);
        $all_semester = $semester->getAllSemesterData();
        $end_date = $all_semester[$semester_index]['vorles_ende'];

        $i = 0;
        while ($tmp_first_date < $end_date) {
            $start_weeks[$i]['text'] = ($i + 1) . '. ' . _("Semesterwoche") . ' (' . _("ab") . ' ' . strftime("%d.%m.%Y", $tmp_first_date) . ')';
            $start_weeks[$i]['selected'] = ($this->course->getStartWeek() == $i);

            $i++;
            $tmp_first_date = strtotime(sprintf('+%u weeks %s', $i, $_tmp_first_date));
        }
        return $start_weeks;
    }
}

