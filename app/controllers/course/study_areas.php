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

        // user must have tutor permission
        $course_id = current($args);
        $this->course = Course::find($args[0]);
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

    }


    function show_action()
    {
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
        $studyareas = Request::getArray('studyareas');
        if (empty($studyareas)) {
            PageLayout::postMessage(MessageBox::error(_('Sie müssen mindesens einen Studienbereich auswählen')));
            $this->redirect('admin/courses');
            return;
        }

        $this->course->study_areas = SimpleORMapCollection::createFromArray(StudipStudyArea::findMany($studyareas));

        if ($this->course->store()) {
            PageLayout::postMessage(MessageBox::success(_('Die gewünschten Studienbereiche wurden zugordnet!')));
        } else {
            PageLayout::postMessage(MessageBox::error(_('Beim Speichern ist ein Fehler aufgetreten')));
        }
        $this->redirect('admin/courses');
    }


    function get_area_ids($course_id)
    {
        $selection = StudipStudyArea::getStudyAreasForCourse($course_id);

        return array_keys($selection->toGroupedArray('sem_tree_id'));
    }
}
