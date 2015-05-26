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
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values)
    {
        $tpl = $GLOBALS['template_factory']->open('coursewizard/basicdata');
        $types = DBManager::get()->fetchAll("SELECT t.`id`, t.`name`, c.`name` AS classname
            FROM `sem_types` t
                INNER JOIN `sem_classes` c ON (t.`class`=c.`id`)
            WHERE c.`course_creation_forbidden` = 0
            ORDER BY t.`class`, t.`name`");
        $typestruct = array();
        foreach ($types as $t) {
            $typestruct[$t['classname']][] = $t;
        }
        $tpl->set_attribute('types', $typestruct);
        $tpl->set_attribute('values', $values);
        $semesters = array();
        $now = mktime();
        // Allow only current or future semesters for selection.
        foreach (Semester::getAll() as $s) {
            if ($s->ende >= $now) {
                $semesters[] = $s;
            }
        }
        $tpl->set_attribute('semesters', $semesters);
        // If no semester is set, use current as selected default.
        if (!$values['start_time']) {
            $values['start_time'] = Semester::findCurrent()->beginn;
        }
        $tpl->set_attribute('institutes', Institute::getMyInstitutes());
        $lecturersearch = new PermissionSearch('user',
            _('Dozent/-in auswählen'),
            'user_id',
            array('permission' => 'dozent',
                'exclude_user' => $values['lecturers'] ? array_keys($values['lecturers']) : array()
            )
        );
        $tpl->set_attribute('lsearch', QuickSearch::get("lecturers", $lecturersearch)
            ->withButton(array('search_button_name' => 'search_lecturer', 'reset_button_name' => 'reset_lsearch'))
            ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addLecturer')
            ->render());
        $deputies = Config::get()->DEPUTIES_ENABLE;
        if ($deputies) {
            $deputysearch = new PermissionSearch('user',
                _('Vertretung auswählen'),
                'user_id',
                array('permission' => 'dozent',
                    'exclude_user' => $values['deputies'] ? array_keys($values['deputies']) : array()
                )
            );
            $tpl->set_attribute('dsearch', QuickSearch::get("lecturers", $deputysearch)
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
        $tpl->set_attribute('values', $values);
        return $tpl->render();
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
        $course->status = $values['type'];
        $course->start_time = $values['start_time'];
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
    public function isRequired($values) {
        return true;
    }
}