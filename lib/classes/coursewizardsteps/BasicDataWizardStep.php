<?php
/**
 * BasicDataWizardStep.php
 * Course wizard step for getting the basic course data.
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

class BasicDataWizardStep implements CourseWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which number has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id)
    {
        $tpl = $GLOBALS['template_factory']->open('coursewizard/basicdata/index');
        // Get all available course types and their categories.
        $typestruct = array();
        foreach (SemType::getTypes() as $type)
        {
            $class = $type->getClass();
            if (!$class['course_creation_forbidden'])
            {
                $typestruct[$class['name']][] = $type;
            }
        }
        $tpl->set_attribute('types', $typestruct);
        // Select a default type if none is given.
        if (!$values['type']) {
            $values['type'] = 1;
        }
        $semesters = array();
        $now = mktime();
        // Allow only current or future semesters for selection.
        foreach (Semester::getAll() as $s) {
            if ($s->ende >= $now) {
                $semesters[] = $s;
            }
        }
        $tpl->set_attribute('semesters', array_reverse($semesters));
        // If no semester is set, use current as selected default.
        if (!$values['start_time']) {
            $values['start_time'] = Semester::findCurrent()->beginn;
        }
        // Get all allowed institutes (my own).
        $tpl->set_attribute('institutes', Institute::getMyInstitutes());
        // Quicksearch for lecturers.
        // No JS: Keep search value and results for displaying in search select box.
        if ($values['lecturer_id']) {
            Request::getInstance()->offsetSet('lecturer_id', $values['lecturer_id']);
        }
        if ($values['lecturer_id_parameter']) {
            Request::getInstance()->offsetSet('lecturer_id_parameter', $values['lecturer_id_parameter']);
        }
        $tpl->set_attribute('lsearch', $this->getSearch($values['type'],
            $values['institute'], $values['lecturers'] ? array_keys($values['lecturers']) : array()));
        // Check for deputies.
        $deputies = Config::get()->DEPUTIES_ENABLE;
        if ($deputies) {
            $deputysearch = new PermissionSearch('user',
                _('Vertretung hinzufügen'),
                'user_id',
                array('permission' => 'dozent',
                    'exclude_user' => $values['deputies'] ? array_keys($values['deputies']) : array()
                )
            );
            $tpl->set_attribute('dsearch', QuickSearch::get('dsearch', $deputysearch)
                ->withButton(array('search_button_name' => 'search_deputy', 'reset_button_name' => 'reset_dsearch'))
                ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addDeputy')
                ->render());
        }
        /*
         * No lecturers set, add yourself so that at least one lecturer is
         * present. But this can only be done if your own permission level
         * is 'dozent'.
         */
        if (!$values['lecturers'] && $GLOBALS['perm']->have_perm('dozent') && !$GLOBALS['perm']->have_perm('admin')) {
            $values['lecturers'][$GLOBALS['user']->id] = true;
            // Remove from deputies if set.
            if ($deputies && $values['deputies'][$GLOBALS['user']->id]) {
                unset($values['deputies'][$GLOBALS['user']->id]);
            }
            // Add your own default deputies if applicable.
            if ($deputies && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                $values['deputies'] = array_merge($values['deputies'] ?: array(), getDeputies($GLOBALS['user']->id));
            }
        }
        if (!$values['lecturers']) {
            $values['lecturers'] = array();
        }
        if ($deputies && !$values['deputies']) {
            $values['deputies'] = array();
        }
        $tpl->set_attribute('values', $values);
        return $tpl->render();
    }

    /**
     * The function does nothing here because everything needed is already
     * handled automatically via normal request processing.
     * @param Array $values currently set values for this class.
     * @return bool
     */
    public function processRequest($values)
    {
        return $values;
    }

    /**
     * Validates if given values are sufficient for completing the current
     * course wizard step and switch to another one. If not, all errors are
     * collected and shown via PageLayout::postMessage.
     *
     * @param mixed $values Array of stored values
     * @return bool Everything ok?
     */
    public function validate($values)
    {
        $ok = true;
        $errors = array();
        if (!$values['name']) {
            $ok = false;
            $errors[] = _('Bitte geben Sie den Namen der Veranstaltung an.');
        }
        if (!$values['lecturers']) {
            $ok = false;
            $errors[] = sprintf(_('Bitte tragen Sie mindestens eine Person als %s ein.'),
                get_title_for_status('dozent', 1, $values['type']));
        }
        if (!$values['lecturers'][$GLOBALS['user']->id] && !$GLOBALS['perm']->have_perm('admin')) {
            if (Config::get()->DEPUTIES_ENABLE) {
                if (!$values['deputies'][$GLOBALS['user']->id]) {
                    $ok = false;
                    $errors[] = sprintf(_('Sie selbst müssen entweder als %s oder als Vertretung eingetragen sein.'),
                        get_title_for_status('dozent', 1, $values['type']));
                }
            } else {
                $ok = false;
                $errors[] = sprintf(_('Sie müssen selbst als %s eingetragen sein.'),
                    get_title_for_status('dozent', 1, $values['type']));
            }
        }
        if ($errors) {
            PageLayout::postMessage(MessageBox::error(
                _('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors));
        }
        return $ok;
    }

    /**
     * Stores the given values to the given course.
     *
     * @param Course $course the course to store values for
     * @param Array $values values to set
     * @return Course The course object with updated values.
     */
    public function storeValues($course, $values)
    {
        $course->status = $values['coursetype'];
        $course->start_time = $values['start_time'];
        $course->duration_time = 0;
        $course->name = $values['name'];
        $course->veranstaltungsnummer = $values['number'];
        $course->institut_id = $values['institute'];
        $lecturers = array_map(function($l) use ($course)
        {
            return CourseMember::create(array(
                'Seminar_id' => $course->id,
                'user_id' => $l,
                'status' => 'dozent',
                'position' => 0,
                'gruppe' => 0,
                'notification' => 0,
                'comment' => '',
                'visible' => 'yes',
                'bind_calendar' => 1
            ));
        }, array_keys($values['lecturers']));
        $course->members = SimpleORMapCollection::createFromArray($lecturers);
        if (Config::get()->DEPUTIES_ENABLE && $values['deputies']) {
            foreach ($values['deputies'] as $d => $assigned) {
                addDeputy($d, $course->id);
            }
        }
        if ($course->store()) {
            return $course;
        } else {
            return false;
        }
    }

    /**
     * Checks if the current step needs to be executed according
     * to already given values. A good example are study areas which
     * are only needed for certain sem_classes.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values)
    {
        return true;
    }

    /**
     * Copy values for basic data wizard step from given course.
     * @param Course $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        $data = array(
            'coursetype' => $course->status,
            'start_time' => $course->start_time,
            'name' => $course->name,
            'number' => $course->veranstaltungsnummer,
            'institute' => $course->institut_id
        );
        $lecturers = array_map(function($l) {
                return $l->user_id;
            },
            $course->getMembersWithStatus('dozent'));
        $data['lecturers'] = array_flip($lecturers);
        if (Config::get()->DEPUTIES_ENABLE) {
            $deputies = getDeputies($course->id);
            $data['deputies'] = array_keys($deputies);
        }
        $values[__CLASS__] = $data;
        return $values;
    }

    public function getSearch($course_type, $institute_id, $exclude_users)
    {
        if (SeminarCategories::getByTypeId($course_type)->only_inst_user){
            $search = 'user_inst';
        } else {
            $search = 'user';
        }
        $psearch = new PermissionSearch($search,
            sprintf(_("%s hinzufügen"), get_title_for_status('dozent', 1, $course_type)),
            'user_id',
            array('permission' => 'dozent',
                'exclude_user' => $exclude_users ?: array(),
                'institute' => $institute_id
            )
        );
        $qsearch = QuickSearch::get('lecturer_id', $psearch)
            ->withButton(array('search_button_name' => 'search_lecturer', 'reset_button_name' => 'reset_lsearch'))
            ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addLecturer')
            ->render();
        return $qsearch;
    }

}