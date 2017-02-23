<?php
/**
 * grouping.php - grouping of courses
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */

/**
 * @addtogroup notifications
 *
 * Adding or removing a course to a group triggers a CourseDidAddToGroup or
 * CourseDidRemoveFromGroup notification. The course's ID is transmitted as
 * subject of the notification.
 */
class Course_GroupingController extends AuthenticatedController
{
    protected $allow_nobody = false;
    protected $utf8decode_xhr = true;

    /**
     * common tasks for all actions
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->course = Course::findCurrent();

        // Allow only tutor and upwards
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course->id)) {
            throw new AccessDeniedException(_('Sie haben leider nicht die notwendige Berechtigung f�r diese Aktion.'));
        }
    }

    /**
     * This course belongs to a parent or can be assigned to one.
     */
    public function parent_action()
    {
        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Zuordnung zu Veranstaltungsgruppe'));
        Navigation::activateItem('/course/admin/parent');

        $this->parent = $this->course->parent;

        // Prepare context for MyCoursesSearch...
        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = array(
                'semtypes' => SemType::getNonGroupingSemTypes(),
                'exclude' => array($GLOBALS['SessSemName'][1])
            );
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = array(
                'semtypes' => SemType::getNonGroupingSemTypes(),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude' => array($GLOBALS['SessSemName'][1])
            );

        } else {
            $parameters = array(
                'userid' => $GLOBALS['user']->id,
                'semtypes' => SemType::getNonGroupingSemTypes(),
                'exclude' => array($GLOBALS['SessSemName'][1])
            );
        }

        // Provide search object for finding groupable courses.
        $find = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);

        $this->search = QuickSearch::get('parent', $find)
            ->setInputClass('target-seminar');
    }

    /**
     * This course can be a parent with one or more children.
     */
    public function children_action()
    {
        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Unterveranstaltungen'));
        Navigation::activateItem('/course/admin/children');

        $this->children = $this->course->children;

        // Prepare context for MyCoursesSearch...
        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = array(
                'semtypes' => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'exclude' => array($GLOBALS['SessSemName'][1])
            );
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = array(
                'semtypes' => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude' => array($GLOBALS['SessSemName'][1])
            );

        } else {
            $parameters = array(
                'userid' => $GLOBALS['user']->id,
                'semtypes' => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'exclude' => array($GLOBALS['SessSemName'][1])
            );
        }

        // Provide search object for finding groupable courses.
        $find = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);

        $this->search = QuickSearch::get('child', $find)
            ->setInputClass('target-seminar');
    }

    /**
     * Assign a (new) parent to the current course.
     */
    public function assign_parent_action()
    {
        if ($parent = Request::option('parent')) {
            $this->course->parent_course = $parent;
            if ($this->course->store()) {
                $this->sync_users($parent, $this->course->id);
                PageLayout::postSuccess(_('Die Veranstaltungsgruppe wurde zugeordnet.'));
            } else {
                PageLayout::postError(_('Die Veranstaltungsgruppe konnte nicht zugeordnet werden.'));
            }
        } else {
            PageLayout::postError(_('Bitte geben Sie eine Veranstaltung an, zu der zugeordnet werden soll.'));
        }
        $this->relocate('course/grouping/parent');
    }

    /**
     * Remove this courses' current parent.
     */
    public function unassign_parent_action()
    {
        $this->course->parent_course = null;
        if ($this->course->store()) {
            PageLayout::postSuccess(_('Die Zuordnung zur Veranstaltungsgruppe wurde entfernt.'));
        } else {
            PageLayout::postError(_('Die Zuordnung zur Veranstaltungsgruppe konnte nicht entfernt werden.'));
        }
        $this->relocate('course/grouping/parent');
    }

    /**
     * Assign a (new) child to the current course.
     */
    public function assign_child_action()
    {
        if ($child = Request::option('child')) {

            $child_course = Course::find($child);
            $child_course->parent_course = $this->course->id;
            if ($child_course->store()) {
                $this->sync_users($this->course->id, $child);
                PageLayout::postSuccess(_('Die Unterveranstaltung wurde hinzugef�gt.'));
            } else {
                PageLayout::postError(_('Die Unterveranstaltung konnte nicht hinzugef�gt werden.'));
            }
        } else {
            PageLayout::postError(_('Bitte geben Sie eine Veranstaltung an, die als Unterveranstaltung hinzugef�gt werden soll.'));
        }
        $this->relocate('course/grouping/children');
    }

    /**
     * Remove the given child.
     * @param String $id The course ID to remove as child.
     */
    public function unassign_child_action($id)
    {
        $child = Course::find($id);
        $child->parent_course = null;
        if ($child->store()) {
            PageLayout::postSuccess(_('Die Unterveranstaltung wurde entfernt.'));
        } else {
            PageLayout::postError(_('Die Unterveranstaltung konnte nicht entfernt werden.'));
        }
        $this->relocate('course/grouping/children');
    }

    private function sync_users($parent_id, $child_id)
    {
        $sem = Seminar::getInstance($parent_id);
        $csem = Seminar::getInstance($child_id);
        /*
         * Find users that are in current course but not in parent.
         */
        $diff = DBManager::get()->prepare(
            "SELECT u.`user_id` FROM `seminar_user` u
            WHERE u.`Seminar_id` = :course
                AND u.`status` = :status
                AND NOT EXISTS (
                    SELECT `user_id` FROM `seminar_user`
                    WHERE `Seminar_id` = :parent
                        AND `status` = :status
                        AND `user_id` = u.`user_id`)");

        /*
         * Before synchronizing the lecturers, we add all institutes
         * from child course.
         */
        $sem->setInstitutes(array_merge($sem->getInstitutes(), $csem->getInstitutes()));

        /*
         * Synchronize all members (including lecturers, tutors
         * and deputies with parent course.
         */
        $text = '';
        foreach (words('user autor tutor dozent') as $permission) {
            $diff->execute(array(
                'course' => $child_id,
                'status' => $permission,
                'parent' => $parent_id
            ));
            foreach ($diff->fetchFirst() as $user) {
                $sem->addMember($user, $permission);
                $text .= $user . ' => ' . $permission . '<br>';

                // Add default deputies of current user if applicable.
                if ($permission == 'dozent' && Config::get()->DEPUTIES_ENABLE && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                    foreach (Deputy::findByRange_id($user) as $deputy) {
                        $text .= $deputy . ' => default deputy of ' . $user . '<br>';
                        if (!Deputy::exists(array($parent_id, $deputy->user_id)) && !CourseMember::exists(array($parent_id, $deputy->user_id))) {
                            $d = new Deputy();
                            $d->range_id = $parent_id;
                            $d->user_id = $user;
                            $d->store();
                        }
                    }
                }
            }
        }

        // Deputies.
        if (Config::get()->DEPUTIES_ENABLE) {
            foreach (Deputy::findByRange_id($child_id) as $deputy) {
                if (!Deputy::exists(array($parent_id, $deputy->user_id)) && !CourseMember::exists(array($parent_id, $deputy->user_id))) {
                    $text .= $user . ' => deputy<br>';
                    $d = new Deputy();
                    $d->range_id = $parent_id;
                    $d->user_id = $deputy->user_id;
                    $d->store();
                }
            }
        }
        PageLayout::postInfo('Adding users:<br>' . $text);

    }

}
