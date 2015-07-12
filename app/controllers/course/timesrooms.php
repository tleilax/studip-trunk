<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'app/controllers/authenticated_controller.php';


class Course_TimesroomsController extends AuthenticatedController{
    
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException(_('Sie haben nicht die nötigen Rechte, um diese Seite zu betreten.'));
        }

        
        $this->course_id = Request::get('cid', NULL);
        if(isset($this->course_id)){
            $this->course = Seminar::getInstance($this->course_id);
        }
        
        if(Navigation::hasItem('course/admin/timesrooms')){
            Navigation::activateItem('course/admin/timesrooms');
        }
        $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => true);
        
        $this->setSidebar();
        PageLayout::setHelpKeyword('Basis.Veranstaltungen');
        PageLayout::setTitle(sprintf(_('%sVerwaltung von Zeiten und Räumen'),
                isset($this->course) ? $this->course->getFullname() . ' - ' : ''));
    }
    
    public function index_action($course_id = NULL)
    {
        if(request::isXhr()){
            $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => true);
        }
        if(isset($course_id)){
            $this->course_id = $course_id;
            $this->course = Seminar::getInstance($course_id);
        }
        $this->semester = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();
        $this->cycles = $this->course->metadate->getCycles();
    }
    
    public function editDate_action($cycle_id = NULL){
        $this->dozenten = $this->course->getMembers('dozent');
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
    $element  = new WidgetElement($template->render());
    $widget = new SidebarWidget();
    $widget->setTitle(_('Legende'));
    $widget->addElement($element);
    $sidebar->addWidget($widget);
}
        
    }
    
    function set_semester_action(){
        die('semster');
    }
    
    function set_course_action(){
        $this->redirect($this->url_for('course/timesrooms/index'));
        return;//die('course');
    }
    
}

