<?php
/**
 * wizard.php
 * Controller for course creation wizard.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'app/controllers/authenticated_controller.php';

class Course_WizardController extends AuthenticatedController
{

    /**
     * @var Array steps the wizard has to execute in order to create a new course.
     */
    public $steps = array();

    public function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);
        global $perm;
        if ($perm->have_perm('admin')) {
            Navigation::activateItem('/admin/course/create');
        } else {
            $nav = Navigation::getItem('/browse/my_courses');
            $nav->addSubnavigation('create',
                new Navigation(_('Neue Veranstaltung'),
                $this->url_for('course/wizard/step', 0, $this->temp_id)));
            Navigation::activateItem('/browse/my_courses/create');
        }
        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
        $this->set_content_type('text/html;charset=windows-1252');
        $this->steps = CourseWizardStepRegistry::findBySQL("1 ORDER BY `number`");
        StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/classes/coursewizardsteps');
    }

    public function index_action() {
        $this->redirect($this->url_for('course/wizard/step', 0));
    }

    /**
     * Fetches the wizard step with the given number and gets the
     * corresponding template.
     *
     * @param int $number step number to show
     * @param String $temp_id temporary ID for the course to create
     */
    public function step_action($number=0, $temp_id='')
    {
        PageLayout::addSqueezePackage('coursewizard');
        $step = $this->getStep($number);
        if (!$temp_id) {
            $this->initialize();
        } else {
            $this->temp_id = $temp_id;
        }
        if ($number == 0) {
            $this->first_step = true;
        }
        $this->values = $this->getValues($step->classname);
        $this->content = $step->getStepTemplate($this->values);
    }

    /**
     * Processes a finished wizard step by saving the gathered values to
     * session.
     * @param int $step_number the step we are at.
     * @param String $temp_id temporary ID for the course to create
     */
    public function process_action($step_number, $temp_id)
    {
        $this->temp_id = $temp_id;
        $iterator = Request::getInstance()->getIterator();
        while ($iterator->valid()) {
            $iterator->next();
        }
        $this->setStepValues($this->steps[$step_number]['classname'], Request::getInstance());
        // Back or forward button clicked -> set next step accordingly.
        if (Request::submitted('back')) {
            $next_step = $this->getNextRequiredStep($step_number, 'down');
        } else if (Request::submitted('next')) {
            $next_step = $this->getNextRequiredStep($step_number, 'up');
        /*
         * Something other than "back" or "next" was clicked, e.g. QuickSearch
         * -> stay on step and process given values.
         */
        } else {
            $next_step = $this->getStep($step_number);
        }
        // Redirect to next step.
        if ($next_step < sizeof($this->steps)) {
            $this->redirect($this->url_for('course/wizard/step', $next_step, $this->temp_id));
        // We are after the last step -> all done, create course.
        } else {
            $this->course = $this->createCourse();
        }
    }

    /**
     * Creates a temporary ID for storing the wizard values in session.
     */
    private function initialize()
    {
        $temp_id = md5(uniqid(microtime()));
        $_SESSION['coursewizard'][$temp_id] = array();
        $this->temp_id = $temp_id;
    }

    /**
     * Wizard finished: we can create the course now. First store an empty,
     * invisible course for getting an ID. Then, iterate through steps and
     * set values from each step.
     * @return Course
     * @throws Exception
     */
    private function createCourse()
    {
        // Create a new (empty) course so that we get an ID.
        $course = new Course();
        $course->visible = 0;
        $course->store();
        // Each (required) step stores its own values at the course object.
        for ($i = 0; $i < sizeof($this->steps) ; $i++) {
            $step = $this->getStep($i);
            if ($step->isRequired($this->getValues())) {
                if ($stored = $step->storeValues($course, $this->getValues($this->steps[$i]['classname']))) {
                    $course = $stored;
                } else {
                    throw new Exception(_('Die Daten aus Schritt ' . $i . ' konnten nicht gespeichert werden, breche ab.'));
                }
            }
        }
        // Cleanup session data.
        unset($_SESSION['coursewizard'][$this->temp_id]);
        return $course;
    }

    private function getStep($number)
    {
        $classname = $this->steps[$number]['classname'];
        return new $classname();
    }

    /**
     * Not all steps are required for each course type, some sem_classes must
     * not have study areas, for example. So we need to check which step is
     * required next, starting from an index and going up or down, according
     * to navigation through the wizard.
     * @param $number
     * @param string $direction
     * @return mixed
     */
    private function getNextRequiredStep($number, $direction='up')
    {
        $found = false;
        switch ($direction) {
            case 'up':
                $i = $number + 1;
                while (!$found && $i < sizeof($this->steps)) {
                    $step = $this->getStep($i);
                    if ($step->isRequired($this->getValues())) {
                        $found = true;
                    } else {
                        $i++;
                    }
                }
                break;
            case 'down':
                $i = $number - 1;
                while (!$found && $i >= 0) {
                    $step = $this->getStep($i);
                    if ($step->isRequired($this->getValues())) {
                        $found = true;
                    } else {
                        $i--;
                    }
                }
                break;
        }
        return $i;
    }

    /**
     * Gets values stored in session for a given step, or all
     * @param string $classname the step to get values for, or all
     * @return Array
     */
    private function getValues($classname='')
    {
        if ($classname)
        {
            return $_SESSION['coursewizard'][$this->temp_id][$classname];
        } else {
            return $_SESSION['coursewizard'][$this->temp_id];
        }
    }

    /**
     * @param $stepclass class name of the current step.
     * @return Array
     */
    private function setStepValues($stepclass, $request) {
        $iterator = $request->getIterator();
        $values = array();
        while ($iterator->valid()) {
            $values[$iterator->key()] = $iterator->current();
            $iterator->next();
        }
        $_SESSION['coursewizard'][$this->temp_id][$stepclass] = $values;
    }

}