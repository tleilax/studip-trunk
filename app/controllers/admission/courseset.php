<?php

require_once('app/controllers/authenticated_controller.php');
require_once('lib/classes/Institute.class.php');
require_once('lib/classes/admission/CourseSet.class.php');

class Admission_CoursesetController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        $layout = $GLOBALS['template_factory']->open('layouts/base');
        $this->set_layout($layout);
        PageLayout::setTitle(_('Anmeldesets'));
        Navigation::activateItem('/tools/coursesets');
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

    public function overview_action() {
        Navigation::activateItem('/tools/coursesets');
        $this->coursesets = array();
        foreach ($this->myInstitutes as $institute) {
            $current = CourseSet::getCoursesetsByInstituteId($institute['Institut_id']);
            if ($current) {
                $courseset = new CourseSet($current['set_id']);
                $this->coursesets[$current['set_id']] = $courseset;
            }
        }
    }

    public function configure_action($coursesetId='') {
        if ($coursesetId) {
            $this->courseset = new CourseSet($coursesetId);
        }
        $this->instCourses = array();
        $query = "SELECT seminar_inst.seminar_id, s.VeranstaltungsNummer, s.Name
                  FROM seminar_inst
                  LEFT JOIN seminare AS s ON (seminar_inst.seminar_id = s.Seminar_id)
                  WHERE seminar_inst.Institut_id IN ('".
                  implode("', '", array_keys($this->myInstitutes))."')
                  ORDER BY s.start_time DESC, s.VeranstaltungsNummer ASC, s.Name ASC";
        $stmt = DBManager::get()->query($query);
        $this->courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>