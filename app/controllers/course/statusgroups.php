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

require_once 'app/models/members.php';
require_once 'lib/messaging.inc.php'; //Funktionen des Nachrichtensystems

require_once 'lib/export/export_studipdata_func.inc.php'; // Funktionen für den Export

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
    }

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
        $groups = Statusgruppen::findBySeminar_id($this->course_id) ?: array();
        usort($groups, function ($a, $b) {
            return strnatcasecmp($a->name, $b->name);
        });

        // Helper array for collecting all group members.
        $grouped_users = array();

        // Now build actual groups.
        $this->groups = array();
        foreach ($groups as $g) {
            $group = array(
                'id' => $g->id,
                'name' => $g->name,
            );
            $groupmembers = $g->members->pluck('user_id');
            $group['members'] = $this->sortGroupMembers($group['id'],
                $allmembers->findBy('user_id', $groupmembers),
                $this->sort_group, $this->sort_by, $this->order);
            $this->groups[] = $group;
            $grouped_users = array_merge($grouped_users, $groupmembers);
        }

        // Find course members who are in no group at all.
        $ungrouped = $allmembers->filter(function($m) use ($grouped_users) {
            return !in_array($m->user_id, $grouped_users);
        });
        if ($ungrouped) {
            // Create dummy entry for "no group" users.
            $no_group = array(
                'id' => 'nogroup',
                'name' => _('keiner Gruppe zugeordnet'),
                'members' => $this->sortGroupMembers('nogroup',
                    $ungrouped,
                    $this->sort_group, $this->sort_by, $this->order)
            );

            array_unshift($this->groups, $no_group);
        }

    }

    /**
     * Allows editing of a given statusgroup.
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function edit_action($group_id)
    {
        if ($this->is_tutor) {
            $this->group = Statusgruppen::find($group_id);
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Saves changes to given statusgroup.
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function save_action($group_id)
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $group = Statusgruppen::find($group_id);
            $group->name = Request::get('name');
            $group->size = Request::int('size');
            $group->selfassign = Request::int('selfassign', 0);
            if ($group->isDirty()) {
                $group->store();
                PageLayout::postSuccess(sprintf(
                    _('Die Daten der Gruppe "%s" wurden gespeichert.'),
                    $group->name));
            } else {
                PageLayout::postInfo(sprintf(
                    _('Es wurden keine Daten der Gruppe "%s" geändert.'),
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
                _('Die Gruppe "%s" wurde gelöscht.'),
                $groupname));
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    private function sortGroupMembers($group_id, $members, $sort_group, $sort_by, $order)
    {
        $sorting = 'nachname asc, vorname asc';
        if ($group_id == $sort_group) {
            $sorting = $sort_by.' '.$order.', '.$sorting;
        }

        return $members->orderBy($sorting);
    }

}
