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
        $this->is_locked = LockRules::Check($this->course_id, 'participants');

        PageLayout::setTitle(sprintf('%s - %s', Course::findCurrent()->getFullname(), _('Gruppen')));

        // Set default sidebar image
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/person-sidebar.png');

        if (!$this->is_locked) {
            $actions = new ActionsWidget();
            $actions->addLink(_('Neue Gruppe anlegen'),
                $this->url_for('course/statusgroups/edit'),
                Icon::create('add', 'clickable'))->asDialog('size=auto');
            $actions->addLink(_('Mehrere Gruppen anlegen'),
                $this->url_for('course/statusgroups/create_groups'),
                Icon::create('group2+add', 'clickable'))->asDialog('size=auto');
            $sidebar->addWidget($actions);
        }
        if ($this->is_tutor && Config::get()->EXPORT_ENABLE) {
            include_once $GLOBALS['PATH_EXPORT'] . '/export_linking_func.inc.php';

            $export = new ExportWidget();

            // create csv-export link
            $csvExport = export_link($this->course_id, "person", sprintf('%s %s', htmlReady($this->status_groups['autor']), htmlReady($this->course_title)), 'csv', 'csv-teiln', '', _('Teilnehmendenliste als csv-Dokument exportieren'), 'passthrough');
            $export->addLink(_('Gruppierte Teilnehmendenliste als CSV-Dokument exportieren'),
                $this->parseHref($csvExport), Icon::create('file-office', 'clickable'));
            // create csv-export link
            $rtfExport = export_link($this->course_id, "person", sprintf('%s %s', htmlReady($this->status_groups['autor']), htmlReady($this->course_title)), 'rtf', 'rtf-teiln', '', _('Teilnehmendenliste als rtf-Dokument exportieren'), 'passthrough');
            $export->addLink(_('Gruppierte Teilnehmendenliste als rtf-Dokument exportieren'),
                $this->parseHref($rtfExport), Icon::create('file-text', 'clickable'));
            $sidebar->addWidget($export);
        }
    }

    /**
     * Lists all available statusgroups.
     */
    public function index_action()
    {
        PageLayout::addSqueezePackage('statusgroups');
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

            $groupmembers = $g->members->pluck('user_id');
            if ($this->sort_group == $g->id) {
                $sorted = StatusgroupsModel::sortGroupMembers(
                    $allmembers->findBy('user_id', $groupmembers),
                    $this->sort_by, $this->order);
            } else {
                $sorted = StatusgroupsModel::sortGroupMembers(
                    $allmembers->findBy('user_id', $groupmembers));
            }

            $this->groups[] = array(
                'group' => $g,
                'members' => $sorted
            );
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
            $no_group = new StdClass();
            $no_group->id = 'nogroup';
            $no_group->name = _('keiner Gruppe zugeordnet');
            $no_group->size = 0;
            $no_group->selfassign = 0;
            $this->no_group = array(
                'group' => $no_group,
                'members' => $members
            );

        }

        // Prepare search object for MultiPersonSearch.
        $this->memberSearch = new PermissionSearch(
            'user_in_sem',
            _('Teilnehmende der Veranstaltung suchen'),
            'user_id',
            array('seminar_id' => $this->course_id,
                'sem_perm' => array('user', 'autor')
            )
        );
    }

    /**
     * Provides extended info about a status group, like maximum number of
     * participants, selfassign, exclusive entry, selfassign start and end
     * times.
     *
     * @param String $group_id The group to show info for.
     */
    public function groupinfo_action($group_id)
    {
        $group = Statusgruppen::find($group_id);
        $this->info = array($group->size > 0 ?
                    sprintf(_('Diese Gruppe ist auf **%u** Mitglieder beschränkt.'), $group->size) :
                    sprintf(_('Die Größe dieser Gruppe ist nicht beschränkt.')));
        if ($group->selfassign) {
            if ($group->selfassign == 1) {
                $this->info[] = _('Die Teilnehmenden dieser Veranstaltung können sich ' .
                        'selbst in beliebig viele der Gruppen eintragen, bei denen ' .
                        'kein Exklusiveintrag aktiviert ist.');
            } else if ($group->selfassign == 2) {
                $this->info[] = _('Die Teilnehmenden dieser Veranstaltung können sich ' .
                        'in genau einer der Gruppen eintragen, bei denen der ' .
                        'Exklusiveintrag aktiviert ist.');
            }
            if ($group->selfassign_start && $group->selfassign_end) {
                $this->info[] .= sprintf(_('Der Eintrag ist möglich **von %s bis %s**.'),
                        date('d.m.Y H:i', $group->selfassign_start),
                        date('d.m.Y H:i', $group->selfassign_end));
            } else if ($group->selfassign_start && !$group->selfassign_end) {
                $this->info[] = sprintf(_('Der Eintrag ist möglich **ab %s**.'),
                        date('d.m.Y H:i', $group->selfassign_start));
            } else if (!$group->selfassign_start && $group->selfassign_end) {
                $this->info[] = sprintf(_('Der Eintrag ist möglich **bis %s**.'),
                        date('d.m.Y H:i', $group->selfassign_end));
            }
        }
    }

    /**
     * Adds selected persons to given group. user_ids to add come from a
     * MultiPersonSearch object which was triggered in group actions.
     *
     * @param String $group_id
     */
    public function add_member_action($group_id)
    {
        $g = Statusgruppen::find($group_id);

        // Get selected persons.
        $mp = MultiPersonSearch::load('add_statusgroup_member' . $group_id);

        $success = 0;
        $fail = 0;

        foreach ($mp->getAddedUsers() as $a) {
            $s = new StatusgruppeUser();
            $s->statusgruppe_id = $group_id;
            $s->user_id = $a;
            if ($s->store() !== false) {
                $success++;
            } else {
                $fail++;
            }
        }

        if ($success > 0 && $fail == 0) {
            PageLayout::postSuccess(sprintf(ngettext(
                '%u Person wurde zu %s hinzugefügt.',
                '%u Personen wurden zu %s hinzugefügt.',
                $success), $success, $g->name
            ));
        } else if ($success > 0 && $fail > 0) {
            $successMsg = sprintf(ngettext(
                '%u Person wurde zu %s hinzugefügt.',
                '%u Personen wurden zu %s hinzugefügt.',
                $success), $success, $g->name
            );
            $failMsg = sprintf(ngettext(
                '%u Person konnte nicht zu %s hinzugefügt werden.',
                '%u Personen konnten nicht zu %s hinzugefügt werden.',
                $fail), $fail, $g->name
            );
            PageLayout::postWarning($successMsg . ' ' . $failMsg);
        } else if ($success == 0 && $fail > 0) {
            PageLayout::postError(sprintf(ngettext(
                '%u Person konnte nicht zu %s hinzugefügt werden.',
                '%u Personen konnten nicht zu %s hinzugefügt werden.',
                $success), $success, $g->name
            ));
        }

        $this->relocate('course/statusgroups');

    }

    /**
     * Allows editing of a given statusgroup or creating a new one.
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function edit_action($group_id = '')
    {
        Statusgruppen::expireTableScheme();
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
     *
     * @param String $group_id ID of the group to edit
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function save_action($group_id = '')
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $group = StatusgroupsModel::updateGroup($group_id, Request::get('name'),
                0, $this->course_id, Request::int('size', 0),
                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                strtotime(Request::get('selfassign_start', 'now')),
                strtotime(Request::get('selfassign_end', 0)),
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
     *
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

    /**
     * Removes the given user from the given statusgroup.
     *
     * @param String $user_id user to remove
     * @param String $group_id affected group
     */
    public function delete_member_action($user_id, $group_id)
    {
        if ($this->is_tutor || $user_id == $GLOBALS['user']->id) {
            $g = Statusgruppen::find($group_id);
            $s = StatusgruppeUser::find(array($group_id, $user_id));
            $name = $s->user->getFullname();
            if ($s->delete()) {
                if ($user_id == $GLOBALS['user']->id) {
                    PageLayout::postSuccess(sprintf(
                        _('Sie wurden aus der Gruppe %s ausgetragen.'),
                        $g->name));
                } else {
                    PageLayout::postSuccess(sprintf(
                        _('%s wurde aus der Gruppe %s ausgetragen.'),
                        $name, $g->name));
                }
            } else {
                if ($user_id == $GLOBALS['user']->id) {
                    PageLayout::postError(sprintf(
                        _('Sie konnten nicht aus der Gruppe %s ausgetragen werden.'),
                        $g->name));
                } else {
                    PageLayout::postSuccess(sprintf(
                        _('%s konnte nicht aus der Gruppe %s ausgetragen werden.'),
                        $name, $g->name));
                }
            }
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    public function move_member_action($user_id, $group_id)
    {
        if ($this->is_tutor) {
            $this->source_group = $group_id;

            $this->members = array($user_id);

            // Find possible target groups.
            $this->target_groups = SimpleCollection::createFromArray(
                Statusgruppen::findByRange_id($this->course_id))
                ->orderBy('position, name')
                ->filter(function ($g) use ($group_id) { return $g->id != $group_id; });
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Provides the possibility to batch create several groups at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
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
     * Adds the current user to the given group.
     *
     * @throws Trails_Exception 403 if current user may not join the given group.
     */
    public function join_action($group_id)
    {

        $g = Statusgruppen::find($group_id);

        if ($g->userMayJoin($GLOBALS['user']->id)) {
            $s = new StatusgruppeUser();
            $s->user_id = $GLOBALS['user']->id;
            $s->statusgruppe_id = $group_id;
            if ($s->store()) {
                PageLayout::postSuccess(sprintf(
                    _('Sie wurden als Mitglied der Gruppe %s eingetragen.'), $g->name));
            } else {
                PageLayout::postSuccess(sprintf(
                    _('Sie konnten nicht als Mitglied der Gruppe %s eingetragen werden.'), $g->name));
            }
        } else {
            throw new Trails_Exception(403);
        }

        $this->relocate('course/statusgroups');
    }

    /**
     * Removes the current user from the given group.
     *
     * @throws Trails_Exception 403 if current user may not join the given group.
     */
    public function leave_action($group_id)
    {

        $g = Statusgruppen::find($group_id);

        if ($g->isMember($GLOBALS['user']->id)) {
            $s = StatusgruppeUser::find(array($group_id, $GLOBALS['user']->id));
            if ($s->delete()) {
                PageLayout::postSuccess(sprintf(
                    _('Sie wurden aus der Gruppe %s ausgetragen.'), $g->name));
            } else {
                PageLayout::postSuccess(sprintf(
                    _('Sie konnten nicht aus der Gruppe %s ausgetragen werden.'), $g->name));
            }
        } else {
            throw new Trails_Exception(403);
        }

        $this->relocate('course/statusgroups');
    }

    /**
     * Batch creation of statusgroups according to given settings.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
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
                        $counter + 1, $this->course_id, Request::int('size', 0),
                        Request::int('selfassign', 0) + Request::int('exclusive', 0),
                        strtotime(Request::get('selfassign_start', 'now')),
                        strtotime(Request::get('selfassign_end', 0)),
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
                                $t->priority, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
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
                                $counter + 1, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
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
                                $counter + 1, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
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
                                $l->position, $this->course_id, Request::int('size', 0),
                                Request::int('selfassign', 0) + Request::int('exclusive', 0),
                                strtotime(Request::get('selfassign_start', 'now')),
                                strtotime(Request::get('selfassign_end', 0)),
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

    /**
     * Batch action for several groups or group members at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_action_action()
    {
        if ($this->is_tutor) {

            // Actions for selected groups.
            if (Request::submitted('batch_groups')) {
                if ($groups = Request::getArray('groups')) {
                    $this->groups = SimpleCollection::createFromArray(
                        Statusgruppen::findMany($groups))->orderBy('position, name');
                    switch (Request::option('groups_action')) {
                        case 'edit':
                            PageLayout::setTitle('Einstellungen bearbeiten');
                            $this->edit = true;
                            $sizes = array();
                            $selfassign = 0;
                            $exclusive = 0;
                            $selfassign_start = array();
                            $selfassign_end = array();

                            // Check for diverging values on all groups.
                            foreach ($this->groups as $group) {
                                $sizes[$group->size] = true;
                                if ($group->selfassign == 1) {
                                    $selfassign++;
                                }
                                if ($group->selfassign == 2) {
                                    $selfassign++;
                                    $exclusive++;
                                }
                                if ($group->selfassign_start) {
                                    $selfassign_start[$group->selfassign_start] = true;
                                }
                                if ($group->selfassign_end) {
                                    $selfassign_end[$group->selfassign_end] = true;
                                }
                            }

                            // Get default group size
                            $this->size = max(array_keys($sizes));

                            // Only one entry => all groups have same size.
                            if (count($sizes) == 1) {
                                $this->different_sizes = 0;
                            } else {
                                $this->different_sizes = 1;
                            }

                            // Selfassign enabled for all selected groups?
                            if ($selfassign == 0) {
                                $this->selfassign = 0;
                            } else if ($selfassign == count($groups)) {
                                $this->selfassign = 1;
                            } else {
                                $this->selfassign = -1;
                            }

                            // Exclusive entry set for all selected groups?
                            if ($exclusive == 0) {
                                $this->exclusive = 0;
                            } else if ($exclusive == count($groups)) {
                                $this->exclusive = 1;
                            } else {
                                $this->exclusive = -1;
                            }

                            // Selfassign start time set for all selected groups?
                            if (count($selfassign_start) == 1) {
                                // Just one entry, take it as value for all.
                                $start = array_pop(array_keys($selfassign_start));
                                $this->selfassign_start = $start ? date('d.m.Y H:i', $start) : date('d.m.Y H:i');
                            } else {
                                // Different entries, mark this.
                                $this->selfassign_start = -1;
                            }

                            // Selfassign end time set for all selected groups?
                            if (count($selfassign_end) == 1) {
                                // Just one entry, take it as value for all.
                                $end = array_pop(array_keys($selfassign_end));
                                $this->selfassign_end = $end ? date('d.m.Y H:i', $end) : date('d.m.Y H:i');
                            } else {
                                // Different entries, mark this.
                                $this->selfassign_end = -1;
                            }

                            break;
                        case 'delete':
                            PageLayout::setTitle('Gruppe(n) löschen?');
                            $this->askdelete = true;
                            break;
                        default:
                            $this->relocate('course/statusgroups');
                    }
                } else {
                    PageLayout::postError(_('Sie haben keine Gruppe ausgewählt.'));
                }
            // Actions for selected group members.
            } else if (Request::submitted('batch_members')) {
                // Which group is selected?
                $group_id = key(Request::getArray('batch_members'));

                // Get selected group members.
                $group = Request::getArray('group');
                $this->members = array_keys($group[$group_id]);

                // Get selected action for group members.
                $actions = Request::getArray('members_action');
                $action = $actions[$group_id];

                switch ($action) {
                    case 'move':
                        PageLayout::setTitle(_('Gruppenmitglieder verschieben'));
                        $this->movemembers = true;
                        $this->source_group = $group_id;
                        // Find possible target groups.
                        $this->target_groups = SimpleCollection::createFromArray(
                            Statusgruppen::findByRange_id($this->course_id))
                            ->orderBy('position, name')
                            ->filter(function ($g) use ($group_id) { return $g->id != $group_id; });
                        break;
                    case 'delete':
                }

            }

        } else {
            // Non-tutors may not access this.
            throw new Trails_Exception(403);
        }
    }

    /**
     * Deletes several groups at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_delete_groups_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $groups = Statusgruppen::findMany(Request::getArray('groups'));
            foreach ($groups as $g) {
                $g->delete();
            }
            PageLayout::postSuccess(_('Die ausgewählten Gruppen wurden gelöscht.'));
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Sets data for several groups at once.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_save_groups_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $groups = Statusgruppen::findMany(Request::getArray('groups'));
            foreach ($groups as $g) {
                StatusgroupsModel::updateGroup($g->id, $g->name,
                    $g->position, $this->course_id,
                    Request::int('size', 0),
                    Request::int('selfassign', 0) + Request::int('exclusive', 0),
                    strtotime(Request::get('selfassign_start', 'now')),
                    strtotime(Request::get('selfassign_end', 0)),
                    false);
            }
            PageLayout::postSuccess(_('Die Einstellungen der ausgewählten Gruppen wurden gespeichert.'));
            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    /**
     * Moves selected group members to another group.
     *
     * @throws Trails_Exception 403 if access not allowed with current permission level.
     */
    public function batch_move_members_action()
    {
        if ($this->is_tutor) {
            CSRFProtection::verifyUnsafeRequest();
            $success = 0;
            $error = 0;
            $members = Request::getArray('members');
            foreach ($members as $m) {

                $stored = false;

                // Add user to target statusgroup (if not already in there).
                if (!StatusgruppeUser::exists(array(Request::option('target_group'), $m))) {
                    $s = new StatusgruppeUser();
                    $s->user_id = $m;
                    $s->statusgruppe_id = Request::option('target_group');
                    if ($s->store()) {
                        $stored = true;
                    }
                }

                // Delete old group membership.
                $source = Request::option('source');
                if ($stored) {
                    if ($source != 'nogroup') {
                        $old = StatusgruppeUser::find(array($source, $m));
                        if ($old->delete()) {
                            $success++;
                        } else {
                            $error++;
                        }
                    } else {
                        $success++;
                    }
                }
            }
            $groupname = Statusgruppen::find(Request::option('target_group'))->name;

            // Everything completed successfully => success message.
            if ($success && !$error) {
                PageLayout::postSuccess(sprintf(ngettext('%u Person wurde in die Gruppe %s verschoben.',
                    '%u Personen wurden in die Gruppe %s verschoben.',
                    $success), $success, $groupname));

            // Some entries worked, some didn't => warning message.
            } else if ($success && $error) {
                PageLayout::postWarning(
                    sprintf(ngettext('%u Person wurde in die Gruppe %s verschoben.',
                    '%u Personen wurden in die Gruppe %s verschoben.',
                    $success), $success, $groupname) . '<br>' .
                    sprintf(ngettext('%u Person konnten nicht in die Gruppe %s verschoben werden.',
                        '%u Personen konnten nicht in die Gruppe %s verschoben werden.',
                        $error), $error, $groupname)
                );

            // All is lost => error message.
            } else if ($error) {
                PageLayout::postError(sprintf(ngettext('%u Person konnten nicht in die Gruppe %s verschoben werden.',
                    '%u Personen konnten nicht in die Gruppe %s verschoben werden.',
                    $error), $error, $groupname));
            }

            $this->relocate('course/statusgroups');
        } else {
            throw new Trails_Exception(403);
        }
    }

    private function parseHref($string)
    {
        $temp = preg_match('/href="(.*?)"/', $string, $match); // Yes, you're absolutely right - this IS horrible!
        return $match[1];
    }

}
