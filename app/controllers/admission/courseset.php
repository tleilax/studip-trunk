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
        // Fetch all lists with special user chances.
        $this->myUserlists = AdmissionUserList::getUserLists($GLOBALS['user']->id);
        PageLayout::addSqueezePackage('admission');
    }

    /**
     * Show all coursesets the current user has access to.
     */
    public function index_action() {
        // Fetch the institutes that current user is assigned to...
        $institutes = Institute::getMyInstitutes();
        $this->myInstitutes = array();
        // ... with at least the permission "dozent".
        foreach ($institutes as $institute) {
            if (in_array($institute['inst_perms'], array('dozent', 'admin'))) {
                $this->myInstitutes[$institute['Institut_id']] = $institute;
            }
        }
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
		if ($GLOBALS['perm']->have_perm('root')) {
			if ($coursesetId) {
				// Load course set data.
				$this->courseset = new CourseSet($coursesetId);
	    		$this->myInstitutes = array();
				$selectedInstitutes = $this->courseset->getInstituteIds();
				foreach ($selectedInstitutes as $id => $selected) {
					$this->myInstitutes[$id] = new Institute($id); 
				}
				$this->selectedInstitutes = $this->myInstitutes;
				$allCourses = CoursesetModel::getInstCourses($this->selectedInstitutes, $coursesetId);
				$selectedCourses = $this->courseset->getCourses();
			} else {
	    		$this->myInstitutes = array();
				$this->selectedInstitutes = array();
				$allCourses = array();
				$selectedCourses = array();
			}
            $this->instSearch = QuickSearch::get("institute_id", new StandardSearch("Institut_id"))
                ->withButton()
                ->render();
		} else {
			$myInstitutes = Institute::getMyInstitutes();
			foreach ($myInstitutes as $institute) {
				$this->myInstitutes[$institute['Institut_id']] = $institute;
			}
		}
		// If an institute search has been conducted, we need to consider parameters from flash.
        if ($this->flash['name'] || $this->flash['institutes'] || $this->flash['courses'] ||
				$this->flash['rules'] || $this->flash['userlists'] || $this->flash['infotext']) {
			if (!$this->courseset) {
				$this->courseset = new CourseSet($coursesetId);
			}
            if ($this->flash['name']) {
                $this->courseset->setName($this->flash['name']);
            }
            if ($this->flash['institutes']) {
            	$institutes = $this->flash['institutes'];
                $this->courseset->setInstitutes($institutes);
				if ($GLOBALS['perm']->have_perm('root')) {
					$this->myInstitutes = array();
					foreach ($institutes as $id) {
						$this->myInstitutes[$id] = new Institute($id); 
						$this->selectedInstitutes[$id] = true;
					}
				}
				$allCourses = CoursesetModel::getInstCourses(array_flip($institutes), $coursesetId);
				$selectedCourses = $this->courseset->getCourses();
            }
            if ($this->flash['courses']) {
            	$courses = $this->flash['courses'];
                $this->courseset->setCourses($courses);
				$selectedCourses = $courses;
            }
            if ($this->flash['rules']) {
                $this->courseset->setAdmissionRules($this->flash['rules']);
            }
            if ($this->flash['userlists']) {
                $this->courseset->setUserlists($this->flash['userlists']);
            }
            if ($this->flash['infotext']) {
                $this->courseset->setInfoText($this->flash['infotext']);
            }
        }
        $fac = $this->get_template_factory();
        $tpl = $fac->open('admission/courseset/instcourses');
        $tpl->set_attribute('allCourses', $allCourses);
        $tpl->set_attribute('selectedCourses', $selectedCourses);
        $this->coursesTpl = $tpl->render();
        $tpl = $fac->open('admission/courseset/institutes');
        if ($coursesetId) {
            $tpl->set_attribute('courseset', $this->courseset);
        }
        $tpl->set_attribute('instSearch', $this->instSearch);
        $tpl->set_attribute('selectedInstitutes', $this->selectedInstitutes);
        $tpl->set_attribute('myInstitutes', $this->myInstitutes);
        $tpl->set_attribute('controller', $this);
        $this->instTpl = $tpl->render();
    }

    public function save_action($coursesetId='') {
        if (Request::submitted('submit')) {
            $courseset = new CourseSet($coursesetId);
            $courseset->setName(Request::get('name'))
                ->setInstitutes(Request::getArray('institutes'))
                ->setCourses(Request::getArray('courses'))
                ->setUserLists(Request::getArray('userlists'))
                ->clearAdmissionRules();
            foreach (Request::getArray('rules') as $serialized) {
                $rule = unserialize(html_entity_decode($serialized, ENT_COMPAT | ENT_HTML401, 'iso-8859-1'));
                $courseset->addAdmissionRule($rule);
            }
            $algorithm = new RandomAlgorithm();
            $courseset->setAlgorithm($algorithm);
            $courseset->store();
	        $this->redirect('admission/courseset');
        } else {
            $this->flash['name'] = Request::get('name');
            $this->flash['institutes'] = Request::getArray('institutes');
            $this->flash['courses'] = Request::getArray('courses');
            $this->flash['rules'] = Request::getArray('rules');
            $this->flash['userlists'] = Request::getArray('userlists');
            $this->flash['infotext'] = Request::get('infotext');
            if (Request::submitted('add_institute')) {
                $this->flash['institutes'] = array_merge($this->flash['institutes'], array(Request::option('institute_id')));
            } else {
                $this->flash['institute_id'] = Request::get('institute_id');
                $this->flash['institute_id_parameter'] = Request::get('institute_id_parameter');
            }
            $this->redirect($this->url_for('admission/courseset/configure', $coursesetId));
        }
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
        $this->selectedCourses = array();
        if ($coursesetId && !Request::getArray('courses')) {
            $courseset = new CourseSet($coursesetId);
            $this->selectedCourses = $courseset->getCourses();
        } else if (Request::getArray('courses')) {
            $this->selectedCourses = Request::getArray('courses');
        }
        $this->allCourses = CoursesetModel::getInstCourses(
            array_flip(Request::getArray('institutes')), $coursesetId, $this->selectedCourses);
    }

    public function institutes_action() {
        $this->myInstitutes = Institute::getMyInstitutes();
        $this->selectedInstitutes = array();
        foreach(Request::getArray('institutes') as $institute) {
            $this->selectedInstitutes[$institute] = new Institute($institute);
        }
        $this->instSearch = QuickSearch::get("institute_id", new StandardSearch("Institut_id"))
            ->withButton()
            ->render();
    }

}

?>