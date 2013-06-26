<?php

require_once('app/controllers/authenticated_controller.php');
require_once('app/models/courseset.php');
require_once('lib/classes/Institute.class.php');
require_once('lib/classes/admission/CourseSet.class.php');
require_once('lib/classes/admission/AdmissionUserList.class.php');
require_once('lib/classes/admission/RandomAlgorithm.class.php');
require_once('lib/classes/admission/WaitingList.class.php');

class Admission_CoursesetController extends AuthenticatedController {

    /**
     * Things to do before every page load.
     */
    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        // AJAX request, so no page layout.
        if (Request::isXhr()) {
            $this->via_ajax = true;
            $this->set_layout(null);
        // Open base layout for normal 
        } else {
            $layout = $GLOBALS['template_factory']->open('layouts/base');
            $this->set_layout($layout);
            PageLayout::setTitle(_('Anmeldesets'));
            Navigation::activateItem('/tools/coursesets/sets');
        }
        // Fetch the institutes that current user is assigned to...
        $institutes = Institute::getMyInstitutes();
        $this->myInstitutes = array();
        // ... with at least the permission "dozent".
        foreach ($institutes as $institute) {
            if (in_array($institute['inst_perms'], array('dozent', 'admin'))) {
                $this->myInstitutes[$institute['Institut_id']] = $institute;
            }
        }
        // Fetch all lists with special user chances.
        $this->myUserlists = AdmissionUserList::getUserLists($GLOBALS['user']->id);
        PageLayout::addSqueezePackage('admission');
    }

    /**
     * Show all coursesets the current user has access to.
     */
    public function index_action() {
        DBManager::get()->exec("ALTER TABLE `coursesets` ADD `conjunction` TINYINT(1) NOT NULL DEFAULT 1' AFTER `algorithm_run`");
        DBManager::get()->exec("ALTER TABLE `coursesets` DROP `invalidate_rules`");
        DBManager::get()->exec("ALTER TABLE `conditions` DROP `start_time`, DROP `end_time`");
        DBManager::get()->exec("ALTER TABLE `conditionaladmissions` DROP `conditions_stopped`");
        DBManager::get()->exec("ALTER TABLE `conditionaladmissions` ADD `start_time` INT( 11 ) NOT NULL DEFAULT '0' AFTER `message`, ADD `end_time` INT( 11 ) NOT NULL DEFAULT '0' AFTER `start_time`");
        DBManager::get()->exec("ALTER TABLE `limitedadmissions` ADD `start_time` INT( 11 ) NOT NULL DEFAULT '0' AFTER `message`, ADD `end_time` INT( 11 ) NOT NULL DEFAULT '0' AFTER `start_time`");
        DBManager::get()->exec("ALTER TABLE `lockedadmissions` ADD `start_time` INT( 11 ) NOT NULL DEFAULT '0' AFTER `message`, ADD `end_time` INT( 11 ) NOT NULL DEFAULT '0' AFTER `start_time`");
        $this->coursesets = array();
        foreach ($this->myInstitutes as $institute) {
            $sets = CourseSet::getCoursesetsByInstituteId($institute['Institut_id']);
            foreach ($sets as $set) {
                $courseset = new CourseSet($set['set_id']);
                $this->coursesets[$set['set_id']] = $courseset;
            }
        }
    }

    /**
     * Configure a new or existing course set.
     */
    public function configure_action($coursesetId='') {
        if ($coursesetId) {
            $this->courseset = new CourseSet($coursesetId);
            $this->selectedInstitutes = $this->courseset->getInstituteIds();
            $allCourses = CoursesetModel::getInstCourses($this->selectedInstitutes, $coursesetId);
            $selectedCourses = $this->courseset->getCourses();
        } else {
            if ($GLOBALS['perm']->have_perm('root')) {
                $this->selectedInstitutes = array();
            } else {
                $this->selectedInstitutes = $this->myInstitutes;
            }
            $allCourses = CoursesetModel::getInstCourses($this->myInstitutes, $coursesetId);
            $selectedCourses = array();
        }
        $fac = $this->get_template_factory();
        if ($GLOBALS['perm']->have_perm('root')) {
            $rangeTree = TreeAbstract::getInstance('StudipRangeTree', 
                array('visible_only' => true));
            $tpl = $fac->open('admission/courseset/institutes');
            $tpl->set_attribute('current', 'root');
            $tpl->set_attribute('tree', $rangeTree);
            $tpl->set_attribute('selected', $this->selectedInstitutes);
            $this->rangeTreeTpl = $tpl->render();
        }
        $tpl = $fac->open('admission/courseset/instcourses');
        $tpl->set_attribute('allCourses', $allCourses);
        $tpl->set_attribute('selectedCourses', $selectedCourses);
        $this->coursesTpl = $tpl->render();
    }

    public function save_action($coursesetId='') {
        if (Request::submitted('submit')) {
            $courseset = new CourseSet($coursesetId);
            $courseset->setName(Request::get('name'))
                ->setInstitutes(Request::getArray('institutes'))
                ->setCourses(Request::getArray('courses'))
                ->setUserLists(Request::getArray('userlists'))
                ->setRuleConjunction(Request::int('conjunction'))
                ->clearAdmissionRules();
            foreach (Request::getArray('rules') as $serialized) {
                $rule = unserialize(html_entity_decode($serialized, ENT_COMPAT | ENT_HTML401, 'iso-8859-1'));
                $courseset->addAdmissionRule($rule);
            }
            $algorithm = new RandomAlgorithm();
            $courseset->setAlgorithm($algorithm);
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
        if (Request::int('cancel')) {
            $this->redirect($this->url_for('admission/courseset'));
        }
    }

    public function instcourses_action($coursesetId='') {
        $this->allCourses = CoursesetModel::getInstCourses(
            array_flip(Request::getArray('institutes')), $coursesetId);
        $this->selectedCourses = array();
        if ($coursesetId && !Request::getArray('courses')) {
            $courseset = new CourseSet($coursesetId);
            $this->selectedCourses = $courseset->getCourses();
        } else if (Request::getArray('courses')) {
            $this->selectedCourses = Request::getArray('courses');
        }
    }

}

?>