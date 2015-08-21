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
        $this->cycles = $this->course->metadate->getCycles();

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
        global $TERMIN_TYP;
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
        $this->types = $TERMIN_TYP;
        $this->dozenten = $this->course->getMembers('dozent');
        $this->dozenten_options = $this->course->getMembers('dozent');
        $this->groups_options = Statusgruppen::findBySeminar_id($this->course->getId());
        $this->groups = $this->date_info->getRelatedGroups();
    }

    public function editTeacher_action($termin_id)
    {
        PageLayout::setTitle(_('Durchführende Lehrende bearbeiten'));
        $this->termin = Termine::find($termin_id);
        $this->related_persons = $this->termin->getRelatedPersons();
        $this->dozenten = $this->course->getMembers('dozent');
        if (!count($this->related_persons)) {
            $this->related_persons = $this->dozenten;
        } else {
            $this->dozenten = array_diff_key($this->dozenten, $this->related_persons);
        }
    }

    public function editSingleDate_action($termin_id)
    {
        $termin = SingleDate::getInstance($termin_id);
        $start_time = sprintf('%s %s', Request::get('date'), Request::get('start_time'));
        $end_time = sprintf('%s %s', Request::get('date'), Request::get('end_time'));
        $termin->setTime(strtotime($start_time), strtotime($end_time));
        $termin->setDateType(Request::int('course_type'));
        if ($termin->store()) {
            PageLayout::postMessage(MessageBox::success(_('Die gewünschten Zeiten wurden übernommen!')));
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

    public function addRelatedPerson_action($termin_id)
    {
        $termin = Termine::find($termin_id);
        $related_persons = $termin->getRelatedPersons();
        $user_id = Request::get('add_teacher');
        if (!in_array($user_id, $related_persons)) {
            if ($termin->addRelatedPerson($user_id)) {
                $user = User::find($user_id);
                PageLayout::postMessage(MessageBox::success(sprintf(_('%s wurde als Lehrernder zu dem gewünschten Termin hinzugefügt!'), $user->getFullname())));
            }
        }
        /**
         * TODO: LOGGING
         */
        $this->redirect('course/timesrooms/editTeacher/' . $termin_id);
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

}

