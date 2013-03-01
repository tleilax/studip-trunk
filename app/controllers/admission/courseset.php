<?php

require_once('app/controllers/authenticated_controller.php');
require_once('app/models/courseset.php');
require_once('lib/classes/Institute.class.php');
require_once('lib/classes/admission/CourseSet.class.php');
require_once('lib/classes/admission/RandomAlgorithm.class.php');
require_once('lib/classes/admission/WaitingList.class.php');

class Admission_CoursesetController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        if (Request::isXhr()) {
            $this->via_ajax = true;
            $this->set_layout(null);
        } else {
            $layout = $GLOBALS['template_factory']->open('layouts/base');
            $this->set_layout($layout);
            PageLayout::setTitle(_('Anmeldesets'));
            Navigation::activateItem('/tools/coursesets');
        }
        $institutes = Institute::getMyInstitutes();
        $this->myInstitutes = array();
        foreach ($institutes as $institute) {
            if (in_array($institute['inst_perms'], array('dozent', 'admin'))) {
                $this->myInstitutes[$institute['Institut_id']] = $institute;
            }
        }
        PageLayout::addSqueezePackage('admission');
        PageLayout::addSqueezePackage('conditions');
    }

    public function index_action() {
        Navigation::activateItem('/tools/coursesets');
        $this->coursesets = array();
        foreach ($this->myInstitutes as $institute) {
            $sets = CourseSet::getCoursesetsByInstituteId($institute['Institut_id']);
            foreach ($sets as $set) {
                $courseset = new CourseSet($set['set_id']);
                $this->coursesets[$set['set_id']] = $courseset;
            }
        }
    }

    public function configure_action($coursesetId='') {
        $this->selectedInstitutes = $this->myInstitutes;
        if ($coursesetId) {
            $this->courseset = new CourseSet($coursesetId);
            $this->selectedInstitutes = $this->courseset->getInstituteIds();
        }
        $this->courses = CoursesetModel::getInstCourses($this->myInstitutes);
    }

    public function save_action($coursesetId='') {
        if (Request::submitted('submit')) {
            $courseset = new CourseSet($coursesetId);
            $courseset->setName(Request::get('name'));
            $courseset->setInstitutes(Request::getArray('institutes'));
            $courseset->setCourses(Request::getArray('courses'));
            $courseset->clearAdmissionRules();
            foreach (Request::getArray('rules') as $serialized) {
                $rule = unserialize($serialized);
                $courseset->addAdmissionRule($rule);
            }
            $algorithm = new RandomAlgorithm();
            $courseset->setAlgorithm($algorithm);
            $courseset->setInvalidateRules(false);
            $courseset->store();
        }
        $this->redirect('admission/courseset');
    }

    public function delete_action($coursesetId) {
        $this->courseset = new CourseSet($coursesetId);
        if (Request::int('really')) {
            $this->courseset->delete();
            $this->redirect($this->url_for('admission/courseset'));
        }
    }

    public function instcourses_action() {
        $this->courses = CoursesetModel::getInstCourses(array_flip(Request::getArray('institutes')));
    }

}

?>