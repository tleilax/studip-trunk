<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'lib/functions.php';
require_once 'lib/classes/Seminar.class.php';
require_once 'lib/webservices/api/studip_lecture_tree.php';
require_once 'app/controllers/authenticated_controller.php';
require_once 'lib/classes/coursewizardsteps/CourseWizardStep.php';
require_once 'lib/classes/coursewizardsteps/StudyAreasWizardStep.php';

class Course_StudyAreasController extends AuthenticatedController
{


    // see Trails_Controller#before_filter
    function before_filter(&$action, &$args)
    {

        global $perm;

        parent::before_filter($action, $args);

        $this->course = Course::find($args[0]);
        if(Request::get('cid') && ($this->course->id != Request::get('cid'))) {
            $this->course = Course::findCurrent();
        }

        if (!is_null($this->course)
            && !$perm->have_studip_perm("tutor", $this->course->id)
        ) {
            $this->set_status(403);
            return FALSE;
        }

        $this->set_content_type('text/html; charset=windows-1252');
        $this->step = new StudyAreasWizardStep();
        $this->values = array();
        $this->values['studyareas'] = $this->get_area_ids($this->course->id);
        $this->values['ajax_url'] = $this->url_for('course/study_areas/ajax');
        $this->values['no_js_url'] = $this->url_for('course/study_areas/show');

    }


    function show_action()
    {
        if(!Request::isXhr()) {
            PageLayout::setTitle(sprintf(_("%s - Studienbreiche"), $this->course->getFullname()));
            Navigation::activateItem('course/admin/study_areas');
            $sidebar = Sidebar::get();
            $sidebar->setImage('sidebar/admin-sidebar.png');

            if ($this->course) {
                $links = new ActionsWidget();
                foreach (Navigation::getItem('/course/admin/main') as $nav) {
                    if ($nav->isVisible(true)) {
                        $image = $nav->getImage();
                        $links->addLink($nav->getTitle(), URLHelper::getLink($nav->getURL(), array('studip_ticket' => Seminar_Session::get_ticket())), $image['src']);
                    }
                }
                $sidebar->addWidget($links);
                // Entry list for admin upwards.
                if ($GLOBALS['perm']->have_studip_perm("admin", $GLOBALS['SessionSeminar'])) {
                    $list = new SelectorWidget();
                    $list->setUrl("?#admin_top_links");
                    $list->setSelectParameterName("cid");
                    foreach (AdminCourseFilter::get()->getCourses(false) as $seminar) {
                        $list->addElement(new SelectElement($seminar['Seminar_id'], $seminar['Name']), 'select-' . $seminar['Seminar_id']);
                    }
                    $list->setSelection($this->course->id);
                    $sidebar->addWidget($list);
                }
            }
        }
        if(Request::get('open_node')) {
            $this->values['open_node'] = Request::get('open_node');
        }
        $this->tree = $this->step->getStepTemplate($this->values, 0, 0);
    }

    function ajax_action()
    {
        $parameter = Request::getArray('parameter');
        $method = Request::get('method');

        switch ($method) {
            case 'getSemTreeLevel':
                $json = $this->step->getSemTreeLevel($parameter[0]);
                break;
            case 'getAncestorTree':
                $json = $this->step->getAncestorTree($parameter[0]);
                break;
            default:
                $json = $this->step->getAncestorTree($parameter[0]);
                break;
        }

        $this->render_json($json);
    }

    function save_action()
    {
        if(Request::submitted('assign') || Request::submitted('unassign')) {
            if(Request::submitted('assign')) {
                $msg = $this->assign();
            }

            if(Request::submitted('unassign')) {
                $msg = $this->unassign();
            }
            $url = $this->url_for('course/study_areas/show/'. $this->course->id);

        } else {
            $studyareas = Request::getArray('studyareas');
            $url = $this->url_for('admin/courses');
            if (empty($studyareas)) {
                PageLayout::postMessage(MessageBox::error(_('Sie müssen mindesens einen Studienbereich auswählen')));
                $this->redirect($url);
                return;
            }

            $this->course->study_areas = SimpleORMapCollection::createFromArray(StudipStudyArea::findMany($studyareas));
            try {
                $msg = null;
                $this->course->store();
            } catch(UnexpectedValueException $e){
                $msg = $e->getMessage();
            }
        }

        if (!$msg) {
            PageLayout::postMessage(MessageBox::success(_('Die Studienbereichszuordnung wurde übernommen')));
        } else {
            PageLayout::postMessage(MessageBox::error($msg));
        }
        $this->redirect($url);
    }

    public function unassign() {
        if($this->course->study_areas) {
            foreach($this->course->study_areas as $area) {
                $assigned[] = $area->sem_tree_id;
            }

            foreach(array_keys(Request::getArray('unassign')) as $remove) {
                if($pos = array_search($remove, $assigned)) {
                    unset($assigned[$pos]);
                }
            }
        }

        $this->course->study_areas = SimpleORMapCollection::createFromArray(StudipStudyArea::findMany(array_values($assigned)));

        try {
            $msg = null;
            $this->course->store();
        } catch(UnexpectedValueException $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }

    public function assign() {

        if($this->course->study_areas) {
            foreach($this->course->study_areas as $area) {
                $assigned[] = $area->sem_tree_id;
            }

            foreach(array_keys(Request::getArray('assign')) as $new) {
                if(!in_array($new, $assigned)) {
                    $assigned[] = $new;
                }
            }
        }

        $this->course->study_areas = SimpleORMapCollection::createFromArray(StudipStudyArea::findMany($assigned));

        try {
            $msg = null;
            $this->course->store();
        } catch(UnexpectedValueException $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }


    function get_area_ids($course_id)
    {
        $selection = StudipStudyArea::getStudyAreasForCourse($course_id);

        return array_keys($selection->toGroupedArray('sem_tree_id'));
    }
}
