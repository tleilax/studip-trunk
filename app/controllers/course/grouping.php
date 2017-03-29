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
 * CourseDidRemoveFromGroup notification. The parent and the child course IDs
 * are transmitted as subjects of the notification.
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
            throw new AccessDeniedException(_('Sie haben leider nicht die notwendige Berechtigung für diese Aktion.'));
        }
    }

    /**
     * This course belongs to a parent or can be assigned to one.
     */
    public function parent_action()
    {
        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Zuordnung zu Hauptveranstaltung'));
        Navigation::activateItem('/course/admin/parent');

        $this->parent = $this->course->parent;

        // Prepare context for MyCoursesSearch...
        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = array(
                'semtypes' => SemType::getNonGroupingSemTypes(),
                'exclude' => array($this->parent_course ?: ''),
                'semesters' => array($this->parent->start_semester->id)
            );
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = array(
                'semtypes' => SemType::getNonGroupingSemTypes(),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude' => array($this->parent_course ?: ''),
                'semesters' => array($this->parent->start_semester->id)
            );

        } else {
            $parameters = array(
                'userid' => $GLOBALS['user']->id,
                'semtypes' => SemType::getNonGroupingSemTypes(),
                'exclude' => array($this->parent_course ?: ''),
                'semesters' => array($this->parent->start_semester->id)
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
                'exclude' => count($this->children) > 0 ? $this->children->pluck('seminar_id') : array(),
                'semesters' => array($this->course->start_semester->id)
            );
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = array(
                'semtypes' => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude' => count($this->children) > 0 ? $this->children->pluck('seminar_id') : array(),
                'semesters' => array($this->course->start_semester->id)
            );

        } else {
            $parameters = array(
                'userid' => $GLOBALS['user']->id,
                'semtypes' => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'exclude' => count($this->children) > 0 ? $this->children->pluck('seminar_id') : array(),
                'semesters' => array($this->course->start_semester->id)
            );
        }

        // Provide search object for finding groupable courses.
        $find = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);

        $this->search = QuickSearch::get('child', $find)
            ->setInputClass('target-seminar');
    }

    /**
     * Show a list of all members, grouped by child course.
     */
    public function members_action()
    {
        PageLayout::setTitle(sprintf('%s - %s',
            Course::findCurrent()->getFullname(),
            _('Teilnehmende in Unterveranstaltungen')));
        PageLayout::addScript('members.js');
        Navigation::activateItem('course/members/children');
        $this->courses = SimpleORMapCollection::createFromArray(Course::findByParent_Course($this->course->id))
            ->orderBy(Config::get()->IMPORTANT_SEMNUMBER ? 'veranstaltungsnummer, name' : 'name');

        if (count($this->course->children) > 0) {
            $this->parentOnly = DBManager::get()->fetchFirst(
                "SELECT DISTINCT s.`user_id`
                FROM `seminar_user` s WHERE s.`Seminar_id` = :parent AND NOT EXISTS (
                    SELECT `user_id` FROM `seminar_user` WHERE `user_id` = s.`user_id` AND `Seminar_id` IN (:children)
                    )",
                ['parent' => $this->course->id, 'children' => $this->course->children->pluck('seminar_id')]);
        } else {
            $this->parentOnly = $this->course->members;
        }
    }

    /**
     * Shows members of given child course.
     * @param $course_id
     */
    public function child_course_members_action($course_id)
    {
        $this->child = Course::find($course_id);
    }

    public function parent_only_members_action()
    {
        if (count($this->course->children) > 0) {
            $this->parentOnly = SimpleORMapCollection::createFromArray(DBManager::get()->fetchAll(
                "SELECT DISTINCT s.*
                FROM `seminar_user` s WHERE s.`Seminar_id` = :parent AND NOT EXISTS (
                    SELECT `user_id` FROM `seminar_user` WHERE `user_id` = s.`user_id` AND `Seminar_id` IN (:children)
                    )",
                ['parent' => $this->course->id, 'children' => $this->course->children->pluck('seminar_id')],
                'CourseMember::buildExisting'))->orderBy('nachname, vorname');
        } else {
            $this->parentOnly = $this->course->members;
        }
    }

    /**
     * Assign a (new) parent to the current course.
     */
    public function assign_parent_action()
    {
        if ($parent = Request::option('parent')) {
            $this->course->parent_course = $parent;
            NotificationCenter::postNotification('CourseWillAddToGroup', $this->course->id, $parent);
            if ($this->course->store()) {
                $this->sync_users($parent, $this->course->id);
                NotificationCenter::postNotification('CourseDidAddToGroup', $this->course->id, $parent);
                StudipLog::log('SEM_ADD_TO_GROUP', $this->course->id, $parent, null, null, $GLOBALS['user']->id);
                PageLayout::postSuccess(_('Die Hauptveranstaltung wurde zugeordnet.'));
            } else {
                PageLayout::postError(_('Die Hauptveranstaltung konnte nicht zugeordnet werden.'));
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
        $parent = $this->course->parent_course;
        $this->course->parent_course = null;
        NotificationCenter::postNotification('CourseWillRemoveFromGroup', $this->course->id, $parent);
        if ($this->course->store()) {
            NotificationCenter::postNotification('CourseDidRemoveFromGroup', $this->course->id, $parent);
            StudipLog::log('SEM_DEL_FROM_GROUP', $this->course->id, $parent, null, null, $GLOBALS['user']->id);
            PageLayout::postSuccess(_('Die Zuordnung zur Hauptveranstaltung wurde entfernt.'));
        } else {
            PageLayout::postError(_('Die Zuordnung zur Hauptveranstaltung konnte nicht entfernt werden.'));
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
            NotificationCenter::postNotification('CourseWillAddToGroup', $child, $this->course->id);
            if ($child_course->store()) {
                $this->sync_users($this->course->id, $child);
                NotificationCenter::postNotification('CourseDidAddToGroup', $child, $this->course->id);
                StudipLog::log('SEM_ADD_TO_GROUP', $child, $this->course->id, null, null, $GLOBALS['user']->id);
                PageLayout::postSuccess(_('Die Unterveranstaltung wurde hinzugefügt.'));
            } else {
                PageLayout::postError(_('Die Unterveranstaltung konnte nicht hinzugefügt werden.'));
            }
        } else {
            PageLayout::postError(_('Bitte geben Sie eine Veranstaltung an, die als Unterveranstaltung hinzugefügt werden soll.'));
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
        NotificationCenter::postNotification('CourseWillRemoveFromGroup', $child, $this->course->id);
        if ($child->store()) {
            NotificationCenter::postNotification('CourseDidRemoveFromGroup', $child, $this->course->id);
            StudipLog::log('SEM_DEL_FROM_GROUP', $id, $this->course->id, null, null, $GLOBALS['user']->id);
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
        foreach (words('user autor tutor dozent') as $permission) {
            $diff->execute(array(
                'course' => $child_id,
                'status' => $permission,
                'parent' => $parent_id
            ));
            foreach ($diff->fetchFirst() as $user) {
                $sem->addMember($user, $permission);

                // Add default deputies of current user if applicable.
                if ($permission == 'dozent' && Config::get()->DEPUTIES_ENABLE &&
                        Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                    foreach (Deputy::findByRange_id($user) as $deputy) {
                        if (!Deputy::exists(array($parent_id, $deputy->user_id)) &&
                                !CourseMember::exists(array($parent_id, $deputy->user_id))) {
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
                if (!Deputy::exists(array($parent_id, $deputy->user_id)) &&
                        !CourseMember::exists(array($parent_id, $deputy->user_id))) {
                    $d = new Deputy();
                    $d->range_id = $parent_id;
                    $d->user_id = $deputy->user_id;
                    $d->store();
                }
            }
        }

    }

}
