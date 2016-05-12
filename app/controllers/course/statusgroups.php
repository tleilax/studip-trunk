<?php

/*
 * StatusgroupController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

require_once 'app/models/statusgroups.php';
require_once 'lib/messaging.inc.php'; //Funktionen des Nachrichtensystems
require_once 'lib/export/export_studipdata_func.inc.php'; // Funktionen f�r den Export

class Course_StatusgroupsController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        global $perm;

        checkObject();
        checkObjectModule("participants");

        $course = Course::findCurrent();
        $this->course_id = $course->id;
        $this->course_title = $course->getFullname();

        // Check dozent-perms
        if ($perm->have_studip_perm('dozent', $this->course_id)) {
            $this->is_dozent = true;
        }

        // Check tutor-perms
        if ($perm->have_studip_perm('tutor', $this->course_id)) {
            $this->is_tutor = true;
        }

        // Check autor-perms
        if ($perm->have_studip_perm('autor', $this->course_id)) {
            $this->is_autor = true;
        }

        // Check lock rules
        $this->dozent_is_locked = LockRules::Check($this->course_id, 'dozent');
        $this->tutor_is_locked = LockRules::Check($this->course_id, 'tutor');
        $this->is_locked = LockRules::Check($this->course_id, 'participants');

        PageLayout::setTitle(sprintf('%s - %s', Course::findCurrent()->getFullname(), _('Gruppen')));

        // Set default sidebar image
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/person-sidebar.png');

        $actions = new ActionsWidget();
        $actions->addLink(_('Neue Gruppe anlegen'),
            $this->url_for('course/statusgroups/edit'),
            Icon::create('add', 'clickable'))->asDialog('size=auto');
        $actions->addLink(_('Mehrere Gruppen anlegen'),
            $this->url_for('course/statusgroups/create_groups'),
            Icon::create('group2+add', 'clickable'))->asDialog('size=auto');
        $sidebar->addWidget($actions);
    }

    /**
     * Lists all available statusgroups.
     */
    public function index_action()
    {
        Navigation::activateItem('/course/members/statusgroups');

        // Sorting as given by Request parameters
        $this->sort_by = Request::option('sortby', 'nachname');
        $this->order = Request::option('order', 'desc');
        $this->sort_group = Request::get('sort_group', '');

        // Get all course members (needed for mkdate).
        $allmembers = SimpleCollection::createFromArray(
            CourseMember::findByCourseAndStatus($this->course_id, array('user', 'autor')));

        // Find all statusgroups for this course.
        $groups = SimpleCollection::createFromArray(
            Statusgruppen::findBySeminar_id($this->course_id))->orderBy('position asc, name asc');

        // Helper array for collecting all group members.
        $grouped_users = array();

        // Now build actual groups.
        $this->groups = array();
        foreach ($groups as $g) {
            $group = array(
                'id' => $g->id,
                'name' => $g->name,
            );

            // Sort members alphabetically or by given criteria
            $groupmembers = $g->members->pluck('user_id');
            if ($this->sort_group == $g->id) {
                $group['members'] = StatusgroupsModel::sortGroupMembers(
                    $allmembers->findBy('user_id', $groupmembers),
                    $this->sort_by, $this->order);
            } else {
                $group['members'] = StatusgroupsModel::sortGroupMembers(
                    $allmembers->findBy('user_id', $groupmembers));
            }

            if ($dates = $g->findDates()) {
                $group['dates'] = $dates;
            }
            if ($topics = $g->findTopics()) {
                $group['topics'] = $topics;
            }
            $this->groups[] = $group;
            $grouped_users = array_merge($grouped_users, $groupmembers);
        }

        // Find course members who are in no group at all.
        $ungrouped = $allmembers->filter(function($m) use ($grouped_users) {
            return !in_array($m->user_id, $grouped_users);
        });
        if ($ungrouped) {

            if ($this->sort_group == 'nogroup') {
                $members = StatusgroupsModel::sortGroupMembers($ungrouped,
                    $this->sort_by, $this->order);
            } else {
                $members = StatusgroupsModel::sortGroupMembers($ungrouped);
            }

            // Create dummy entry for "no group" users.
            $no_group = array(
                'id' => 'nogroup',
                'name' => _('keiner Gruppe zugeordnet'),
                'members' => $members
            );

            array_unshift($this->groups, $no_group);
        }

    }

    /**
     * Allows editing of a given statusgroup or creating a new one.
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function edit_action($group_id = '')
    {
        if ($this->is_tutor) {

            // Fetch group with given ID or create a new one.
            if ($group_id) {
                $this->group = Statusgruppen::find($group_id);
            } else {
                $this->group = new Statusgruppen();
            }

            // Check if course has regular times.
            $this->cycles = SeminarCycleDate::findBySeminar_id($this->course_id);

            // Check if course has single dates, not belonging to a regular cycle.
            $dates = CourseDate::findBySeminar_id($this->course_id);
            $this->singledates = array_filter($dates, function ($d) { return !((bool) $d->metadate_id); });

        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Saves changes to given statusgroup or creates a new entry.
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function save_action($group_id = '')
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $group = StatusgroupsModel::updateGroup($group_id, Request::get('name'),
                0, Request::int('size', 0),
                Request::int('selfassign', 0),
                Request::int('exclusive', 0),
                Request::int('makefolder', 0),
                Request::getArray('dates'));

            if (!$group_id) {
                PageLayout::postSuccess(sprintf(
                    _('Die Gruppe "%s" wurde angelegt.'),
                    $group->name));
            } else {
                PageLayout::postSuccess(sprintf(
                    _('Die Daten der Gruppe "%s" wurden gespeichert.'),
                    $group->name));
            }

            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Deletes the given statusgroup.
     * @param String $group_id ID of the group to delete
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function delete_action($group_id)
    {
        if ($this->is_tutor) {
            $group = Statusgruppen::find($group_id);
            $groupname = $group->name;
            $group->delete();
            PageLayout::postSuccess(sprintf(
                _('Die Gruppe "%s" wurde gel�scht.'),
                $groupname));
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Provides the possibility to batch create several groups at once.
     */
    public function create_groups_action()
    {
        if ($this->is_tutor) {
            // Check if course has regular times.
            $this->has_cycles = (count(SeminarCycleDate::findBySeminar_id($this->course_id)) > 0);

            // Check if course has single dates, not belonging to a regular cycle.
            $dates = CourseDate::findBySeminar_id($this->course_id);
            $this->has_singledates = (count(array_filter($dates, function ($d) { return !((bool) $d->metadate_id); })) > 0);

            // Check if course has topics.
            $this->has_topics = (count(CourseTopic::findBySeminar_id($this->course_id)) > 0);
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Batch creation of statusgroups according to given settings.
     */
    public function batch_create_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();

            // Create a number of groups, sequentially named.
            if (Request::option('mode') == 'numbering') {

                $counter = 0;
                for ($i = 0 ; $i < Request::int('number') ; $i++) {
                    $group = StatusgroupsModel::updateGroup('', Request::get('prefix').' '.
                        (Request::int('startnumber', 1) + $i),
                        $counter + 1, Request::int('size', 0),
                        Request::int('selfassign', 0),
                        Request::int('exclusive', 0),
                        Request::int('makefolder', 0));
                    $counter++;
                }
                PageLayout::postSuccess(sprintf(
                    ngettext('Eine Gruppe wurde angelegt.', '%d Gruppen wurden angelegt.', $counter),
                    $counter));

            // Create groups by course metadata, like topics, dates or lecturers.
            } else if (Request::option('mode') == 'coursedata') {

                switch (Request::option('createmode')) {

                    // Create groups per topic.
                    case 'topics':
                        $topics = SimpleCollection::createFromArray(
                            CourseTopic::findBySeminar_id($this->course_id))->orderBy('priority');
                        $counter = 0;

                        foreach ($topics as $t) {
                            $group = StatusgroupsModel::updateGroup('', _('Thema:').' '.$t->title,
                                $t->priority, Request::int('size', 0),
                                Request::int('selfassign', 0),
                                Request::int('exclusive', 0),
                                Request::int('makefolder', 0));

                            // Connect group to dates that are assigned to the given topic.
                            $dates = CourseDate::findByIssue_id($t->id);
                            foreach ($dates as $d) {
                                $d->statusgruppen->append($group);
                                $d->store();
                            }

                            $counter++;
                        }

                        PageLayout::postSuccess(sprintf(
                            ngettext('Eine Gruppe wurde angelegt.', '%d Gruppen wurden angelegt.', count($topics)),
                            $counter));
                        break;

                    // Create groups per (regular and irregular) dates.
                    case 'dates':

                        // Find regular cycles first and create corresponding groups.
                        $cycles = SimpleCollection::createFromArray(
                            SeminarCycleDate::findBySeminar_id($this->course_id));

                        $counter = 0;
                        foreach ($cycles as $c) {
                            $group = StatusgroupsModel::updateGroup('', $c->toString(),
                                $counter + 1, Request::int('size', 0),
                                Request::int('selfassign', 0),
                                Request::int('exclusive', 0),
                                Request::int('makefolder', 0));

                            // Connect group to dates that are assigned to the given cycle.
                            foreach ($c->dates as $d) {
                                $d->statusgruppen->append($group);
                                $d->store();
                            }

                            $counter++;
                        }

                        // Now find irregular dates and create groups.
                        $dates = CourseDate::findBySeminar_id($this->course_id);
                        $singledates = array_filter($dates, function ($d) { return !((bool) $d->metadate_id); });
                        foreach ($singledates as $d) {
                            $group = StatusgroupsModel::updateGroup('', $d->toString(),
                                $counter + 1, Request::int('size', 0),
                                Request::int('selfassign', 0),
                                Request::int('exclusive', 0),
                                Request::int('makefolder', 0));

                            $d->statusgruppen->append($group);
                            $d->store();

                            $counter++;
                        }

                        PageLayout::postSuccess(sprintf(
                            ngettext('Eine Gruppe wurde angelegt.', '%d Gruppen wurden angelegt.', $counter),
                            $counter));
                        break;


                    // Create groups per lecturer.
                    case 'lecturers':
                        $lecturers = SimpleCollection::createFromArray(
                            CourseMember::findByCourseAndStatus($this->course_id, 'dozent'))->orderBy('position');
                        $counter = 0;

                        foreach ($lecturers as $l) {
                            StatusgroupsModel::updateGroup('', $l->getUserFullname('full'),
                                $l->position, Request::int('size', 0),
                                Request::int('selfassign', 0),
                                Request::int('exclusive', 0),
                                Request::int('makefolder', 0));
                            $counter++;
                        }

                        PageLayout::postSuccess(sprintf(
                            ngettext('Eine Gruppe wurde angelegt.', '%d Gruppen wurden angelegt.', count($lecturers)),
                            $counter));
                        break;
                }

            }

            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    public function batch_action_action()
    {
        if ($this->is_tutor) {

            // Actions for selected groups.
            if (Request::submitted('batch_groups') && Request::option('groups_action')) {
                if ($groups = Request::getArray('groups')) {
                    $this->groups = SimpleCollection::createFromArray(
                        Statusgruppen::findMany($groups))->orderBy('position');
                    switch (Request::option('groups_action')) {
                        case 'edit':
                            PageLayout::setTitle('Einstellungen bearbeiten');
                            $this->edit = true;
                            break;
                        case 'delete':
                            PageLayout::setTitle('Gruppe(n) l�schen?');
                            $this->askdelete = true;
                            break;
                        default:
                            $this->relocate('course/statusgroups');
                    }
                } else {
                    PageLayout::postError(_('Sie haben keine Gruppe ausgew�hlt.'));
                }
            // Actions for selected group members.
            } else if (Request::submitted('batch_members') && Request::option('members_action')) {

            // No action selected.
            } else {
                PageLayout::postError(_('Sie haben keine Aktion ausgew�hlt.'));
            }

        } else {
            // Non-tutors may not access this.
            throw new Trails_Exception(403);
        }
    }

    public function batch_delete_groups_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $groups = Statusgruppen::findMany(Request::getArray('groups'));
            foreach ($groups as $g) {
                $g->delete();
            }
            PageLayout::postInfo('Die ausgew�hlten Gruppen wurden gel�scht.');
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    public function batch_save_groups_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $groups = Statusgruppen::findMany(Request::getArray('groups'));
            foreach ($groups as $g) {
                StatusgroupsModel::updateGroup($g->id, $g->name, $g->position,
                    Request::int('size', 0),
                    Request::int('selfassign', 0),
                    Request::int('exclusive', 0),
                    false);
            }
            PageLayout::postInfo('Die Einstellungen der ausgew�hlten Gruppen wurden gespeichert.');
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

}
