<?php
/**
 * my_courses.php - Controller for user and seminar related
 * pages under "Meine Veranstaltungen"
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 *              David Siegfried <david@ds-labs.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category    Stud.IP
 * @since       3.1
 */
require 'app/models/my_realm.php';
require_once 'app/controllers/authenticated_controller.php';
require_once 'lib/meine_seminare_func.inc.php';
require_once 'lib/object.inc.php';
require_once 'lib/modules/CoreDocuments.class.php';

class MyCoursesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['perm']->have_perm('admin')) {
            $this->redirect('admin/courses/index');
            return;
        }

        // we are defintely not in an lecture or institute
        closeObject();
        $_SESSION['links_admin_data'] = '';
    }


    /**
     * Autor / Tutor / Teacher action
     */
    public function index_action($order_by = null, $order = 'asc')
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        if ($GLOBALS['perm']->have_perm('admin')) {
            $this->redirect('my_courses/admin');
            return;
        }
        Navigation::activateItem('/browse/my_courses/list');
        PageLayout::setHelpKeyword("Basis.MeineVeranstaltungen");
        PageLayout::setTitle(_("Meine Veranstaltungen"));

        $config_sem = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
        if (!Config::get()->MY_COURSES_ENABLE_ALL_SEMESTERS && $config_sem == 'all') {
            $config_sem = 'future';
        }

        $group_field                  = $GLOBALS['user']->cfg->MY_COURSES_GROUPING;
        $deputies_enabled             = Config::get()->DEPUTIES_ENABLE;
        $default_deputies_enabled     = Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE;
        $deputies_edit_about_enabledt = Config::get()->DEPUTIES_EDIT_ABOUT_ENABLE;
        $studygroups_enabled          = Config::get()->MY_COURSES_ENABLE_STUDYGROUPS;

        $sem_create_perm = (in_array(Config::get()->SEM_CREATE_PERM, array('root', 'admin',
                                                                           'dozent')) ? Config::get()->SEM_CREATE_PERM : 'dozent');

        $this->sem_data = SemesterData::GetSemesterArray();

        $sem = ($config_sem && $config_sem != '0' ? $config_sem : 'last');
        if (Request::option('sem_select')) {
            $sem = Request::get('sem_select', $sem);
        }

        if (!in_array($sem, words('future all last current')) && isset($sem)) {
            Request::set('sem_select', $sem);
        }

        $forced_grouping = in_array(Config::get()->MY_COURSES_FORCE_GROUPING, getValidGroupingFields())
            ? Config::get()->MY_COURSES_FORCE_GROUPING
            : 'sem_number';

        if (!$group_field) {
            $group_field = 'sem_number';
        }
        if ($group_field == 'sem_number' && $forced_grouping != 'sem_number') {
            $group_field = $forced_grouping;
        }

        $this->group_field = $group_field === 'not_grouped' ? 'sem_number' : $group_field;
        // Needed parameters for selecting courses
        $params = array('group_field'         => $this->group_field,
                        'order_by'            => $order_by,
                        'order'               => $order,
                        'studygroups_enabled' => $studygroups_enabled,
                        'deputies_enabled'    => $deputies_enabled);


        // Save the semester in session
        $this->sem_courses  = MyRealmModel::getPreparedCourses($sem, $params);
        $this->waiting_list = MyRealmModel::getWaitingList($GLOBALS['user']->id);

        $this->sem                          = $sem;
        $this->order                        = $order;
        $this->order_by                     = $order_by;
        $this->default_deputies_enabled     = $default_deputies_enabled;
        $this->deputies_edit_about_enabledt = $deputies_edit_about_enabledt;
        $this->my_bosses                    = $default_deputies_enabled ? getDeputyBosses($GLOBALS['user']->id) : array();

        $this->reset_all = null;
        if ($this->check_for_new($this->sem_courses, $group_field)) {
            $this->reset_all = $this->url_for(sprintf('my_courses/tabularasa/sem/%s', $sem));
        }

        // create settings url depended on selected cycle
        if (!in_array($sem, words('future all last current')) && isset($sem)) {
            $this->settings_url = sprintf('dispatch.php/my_courses/groups/%s', $sem);
        } else {
            $this->settings_url = 'dispatch.php/my_courses/groups';
        }

        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path("sidebar/seminar-sidebar.png"));
        $setting_widget = new ActionsWidget();
        $setting_widget->setTitle(_("Aktionen"));

        if ($this->reset_all) {
            $setting_widget->addLink(_('Alles als gelesen markieren'), $this->reset_all, 'icons/16/black/refresh.png');
        }
        $setting_widget->addLink(_('Farbgruppierung �ndern'), URLHelper::getLink($this->settings_url), 'icons/16/black/group.png',
            array('data-dialog' => 'buttons=true'));

        if (Config::get()->MAIL_NOTIFICATION_ENABLE) {
            $setting_widget->addLink(_('Benachrichtigungen anpassen'),
                URLHelper::getLink('dispatch.php/settings/notification'),
                'icons/16/black/mail.png');
        }

        if ($sem_create_perm == 'dozent' && $GLOBALS['perm']->have_perm('dozent')) {
            $setting_widget->addLink(_('Neue Veranstaltung anlegen'),
                URLHelper::getLink('admin_seminare_assi.php',
                    array('new_session' => 'TRUE')), 'icons/16/black/add/seminar.png');
        }
        $sidebar->addWidget($setting_widget);
        $this->setSemesterWidget($sem);
        $this->setGroupingSelector($this->group_field);
    }

    /**
     * Seminar group administration - cluster your seminars by colors or
     * change grouping mechanism
     */
    public function groups_action($sem = null, $studygroups = false)
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $this->title = _('Meine Veranstaltungen') . ' - ' . _('Farbgruppierungen');

        if (Request::isXhr()) {
            $this->set_layout(null);
            $this->response->add_header('Content-Type', 'text/html;charset=Windows-1252');
            header('X-Title: ' . $this->title);
        } else {
            PageLayout::setTitle($this->title);
            PageLayout::setHelpKeyword('Basis.VeranstaltungenOrdnen');
            Navigation::activateItem('/browse/my_courses/list');
        }

        $this->current_semester = $sem ?: Semester::findCurrent()->semester_id;

        $this->semesters     = SemesterData::GetSemesterArray();
        $forced_grouping     = Config::get()->MY_COURSES_FORCE_GROUPING;
        $no_grouping_allowed = ($forced_grouping == 'sem_number' || !in_array($forced_grouping, getValidGroupingFields()));

        $group_field = $GLOBALS['user']->cfg->MY_COURSES_GROUPING ?: $forced_grouping;

        $groups     = array();
        $add_fields = '';
        $add_query  = '';

        if ($group_field == 'sem_tree_id') {
            $add_fields = ', sem_tree_id';
            $add_query  = "LEFT JOIN seminar_sem_tree sst ON (sst.seminar_id=seminare.Seminar_id)";
        } else if ($group_field == 'dozent_id') {
            $add_fields = ', su1.user_id as dozent_id';
            $add_query  = "LEFT JOIN seminar_user as su1 ON (su1.seminar_id=seminare.Seminar_id AND su1.status='dozent')";
        }

        $dbv = new DbView();

        $query = "SELECT seminare.VeranstaltungsNummer AS sem_nr, seminare.Name, seminare.Seminar_id,
                         seminare.status AS sem_status, seminar_user.gruppe, seminare.visible,
                         {$dbv->sem_number_sql} AS sem_number,
                         {$dbv->sem_number_end_sql} AS sem_number_end {$add_fields}
                  FROM seminar_user
                  JOIN semester_data sd
                  LEFT JOIN seminare USING (Seminar_id)
                  {$add_query}
                  WHERE seminar_user.user_id = ?";
        if (Config::get()->MY_COURSES_ENABLE_STUDYGROUPS && !$studygroups) {
            $query .= " AND seminare.status != 99";
        }

        if ($studygroups) {
            $query .= " AND seminare.status = 99";
        }
        if (get_config('DEPUTIES_ENABLE')) {
            $query .= " UNION "
                . getMyDeputySeminarsQuery('gruppe', $dbv->sem_number_sql, $dbv->sem_number_end_sql, $add_fields, $add_query);
        }
        $query .= " ORDER BY sem_nr ASC";

        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($GLOBALS['user']->id));
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $my_sem[$row['Seminar_id']] = array(
                'obj_type'       => 'sem',
                'name'           => $row['Name'],
                'visible'        => $row['visible'],
                'gruppe'         => $row['gruppe'],
                'sem_status'     => $row['sem_status'],
                'sem_number'     => $row['sem_number'],
                'sem_number_end' => $row['sem_number_end'],
            );
            if ($group_field) {
                fill_groups($groups, $row[$group_field], array(
                    'seminar_id' => $row['Seminar_id'],
                    'name'       => $row['Name'],
                    'gruppe'     => $row['gruppe']
                ));
            }
        }

        if ($group_field == 'sem_number') {
            correct_group_sem_number($groups, $my_sem);
        } else {
            add_sem_name($my_sem);
        }

        sort_groups($group_field, $groups);

        // Ensure that a seminar is never in multiple groups
        $sem_ids = array();
        foreach ($groups as $group_id => $seminars) {
            foreach ($seminars as $index => $seminar) {
                if (in_array($seminar['seminar_id'], $sem_ids)) {
                    unset($seminars[$index]);
                } else {
                    $sem_ids[] = $seminar['seminar_id'];
                }
            }
            if (empty($seminars)) {
                unset($groups[$group_id]);
            } else {
                $groups[$group_id] = $seminars;
            }
        }
        $this->studygroups         = $studygroups;
        $this->no_grouping_allowed = $no_grouping_allowed;
        $this->groups              = $groups;
        $this->group_names         = get_group_names($group_field, $groups);
        $this->group_field         = $group_field;
        $this->my_sem              = $my_sem;
    }


    /**
     * Storage function for the groups action.
     * Stores selected grouping category and actual group settings.
     */
    public function store_groups_action($studygroups = false)
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $deputies_enabled = Config::get()->DEPUTIES_ENABLE;
        $GLOBALS['user']->cfg->store('MY_COURSES_GROUPING', Request::get('select_group_field'));
        $gruppe = Request::getArray('gruppe');
        if (!empty($gruppe)) {
            $query          = "UPDATE seminar_user SET gruppe = ? WHERE Seminar_id = ? AND user_id = ?";
            $user_statement = DBManager::get()->prepare($query);

            $query            = "UPDATE deputies SET gruppe = ? WHERE range_id = ? AND user_id = ?";
            $deputy_statement = DBManager::get()->prepare($query);

            foreach ($gruppe as $key => $value) {
                $user_statement->execute(array($value,
                                               $key,
                                               $GLOBALS['user']->id));
                $updated = $user_statement->rowCount();

                if ($deputies_enabled && !$updated) {
                    $deputy_statement->execute(array($value,
                                                     $key,
                                                     $GLOBALS['user']->id));
                }
            }
        }

        PageLayout::postMessage(MessageBox::success(_('Ihre Einstellungen wurden �bernommen.')));
        $this->redirect($studygroups ? 'my_studygroups/index' : 'my_courses/index');
    }


    /**
     * TODO: Caching
     * @param string $type
     * @param string $sem
     */
    public function tabularasa_action($type = 'sem', $sem = 'all')
    {
        $semesters   = MyRealmModel::getSelectedSemesters($sem);
        $min_sem_key = min($semesters);
        $max_sem_key = max($semesters);
        $courses     = MyRealmModel::getCourses($min_sem_key, $max_sem_key);
        $courses     = $courses->toArray('seminar_id modules status');

        $modules = new Modules();
        foreach ($courses as $index => $course) {
            $courses[$index]['modules']  = $modules->getLocalModules($course['seminar_id'], $type, $course['modules'], $course['status']);
            $courses[$index]['obj_type'] = $type;
            MyRealmModel::setObjectVisits($courses[$index], $course['seminar_id'], $GLOBALS['user']->id);
        }
        NotificationCenter::postNotification('OverviewDidClear', $GLOBALS['user']->id);
        PageLayout::postMessage(MessageBox::success(_('Alles als gelesen markiert!')));

        $this->redirect('my_courses/index');
    }


    /**
     * This action display only a message
     */
    public function decline_binding_action()
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }
        PageLayout::postMessage(MessageBox::error(_('Das Abonnement ist bindend. Bitte wenden Sie sich an die Dozentin oder den Dozenten.')));
        $this->redirect('my_courses/index');
    }

    /**
     * This action remove a user from course
     * @param $course_id
     */
    public function decline_action($course_id, $waiting = null)
    {
        $current_seminar = Course::find($course_id);
        $ticket_check    = Seminar_Session::check_ticket(Request::option('studipticket'));
        if (LockRules::Check($course_id, 'participants')) {
            $lockdata = LockRules::getObjectRule($course_id);
            PageLayout::postMessage(MessageBox::error(sprintf(_("Sie k�nnen das Abonnement der Veranstaltung <b>%s</b> nicht aufheben."),
                htmlReady($current_seminar->name))));
            if ($lockdata['description']) PageLayout::postMessage(MessageBox::info(formatLinks($lockdata['description'])));
            $this->redirect('my_courses/index');
            return;
        } 

        if (Request::option('cmd') == 'back') {
            $this->redirect('my_courses/index');
            return;
        }

        if (Request::option('cmd') != 'kill' && Request::option('cmd') != 'kill_admission') {
            if ($current_seminar->admission_binding) {
                PageLayout::postMessage(MessageBox::error(sprintf(_("Die Veranstaltung <b>%s</b> ist als <b>bindend</b> angelegt.
                    Wenn Sie sich austragen wollen, m&uuml;ssen Sie sich an die Dozentin oder den Dozenten der Veranstaltung wenden."),
                    htmlReady($current_seminar->name))));
                $this->redirect('my_courses/index');
                return;
            }

            if (is_null($waiting)) {
                // check course admission
                $course_set = CourseSet::getSetForCourse($course_id);
                if ($course_set === null) $course_set = false;

                $admission_end_time = ($course_set && $course_set->hasAdmissionRule('TimedAdmission')) ?
                    $course_set->getAdmissionRule('TimedAdmission')->getEndTime() : null;

                $admission_endabled = ($course_set && $course_set->isSeatDistributionEnabled());
                $admission_locked   = ($course_set && $course_set->hasAdmissionRule('LockedAdmission'));

                if ($admission_endabled || $admission_locked || (int)$current_seminar->admission_prelim == 1) {
                    $message = sprintf(_('Wollen Sie das Abonnement der teilnahmebeschr�nkten Veranstaltung "%s" wirklich aufheben?
                Sie verlieren damit die Berechtigung f�r die Veranstaltung und m�ssen sich ggf. neu anmelden!'), $current_seminar->name);
                } else if (isset($admission_end_time) && $admission_end_time < time()) {
                    $message = sprintf(_('Wollen Sie das Abonnement der Veranstaltung "%s" wirklich aufheben?
                Der Anmeldzeitraum ist abgelaufen und Sie k�nnen sich nicht wieder anmelden!'), $current_seminar->name);
                } else {
                    $message = sprintf(_('Wollen Sie das Abonnement der Veranstaltung "%s" wirklich aufheben?'), $current_seminar->name);
                }
                $this->flash['cmd'] = 'kill';
            } else {
                if (admission_seminar_user_get_position($GLOBALS['user']->id, $course_id) === false) {
                    $message = sprintf(_('Wollen Sie den Eintrag auf der Anmeldeliste der Veranstaltung "%s" wirklich aufheben?'), $current_seminar->name);
                } else {
                    $message = sprintf(_('Wollen Sie den Eintrag auf der Warteliste der Veranstaltung "%s" wirklich aufheben?
                    Sie verlieren damit die bereits erreichte Position und m�ssen sich ggf. neu anmelden!'), $current_seminar->name);
                }
                $this->flash['cmd'] = 'kill_admission';
            }

            $this->flash['decline_course'] = true;
            $this->flash['course_id']      = $course_id;
            $this->flash['message']        = $message;
            $this->flash['studipticket']   = Seminar_Session::get_ticket();
            $this->redirect('my_courses/index');
        } else {
            if (!LockRules::Check($course_id, 'participants') && $ticket_check && Request::option('cmd') != 'back' && Request::get('cmd') != 'kill_admission') {
                // LOGGING
                StudipLog::log('SEM_USER_DEL', $course_id, $GLOBALS['user']->id, 'Hat sich selbst ausgetragen');
                $query     = "DELETE FROM seminar_user WHERE user_id = ? AND Seminar_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array($GLOBALS['user']->id,
                                          $course_id));
                if ($statement->rowCount() == 0) {
                    PageLayout::postMessage(MessageBox::error(_('Datenbankfehler!')));
                } else {
                    // enable others to do something after the user has been deleted
                    NotificationCenter::postNotification('UserDidLeaveCourse', $course_id, $GLOBALS['user']->id);

                    // Delete from statusgroups
                    RemovePersonStatusgruppeComplete(get_username(), $course_id);

                    // Are successor available
                    update_admission($course_id);

                    PageLayout::postMessage(MessageBox::success(sprintf(_("Das Abonnement der Veranstaltung <b>%s</b> wurde aufgehoben.
                        Sie sind nun nicht mehr als TeilnehmerIn dieser Veranstaltung im System registriert."),
                        htmlReady($current_seminar->name))));
                }
            } else {
                // LOGGING
                StudipLog::log('SEM_USER_DEL', $course_id, $GLOBALS['user']->id, 'Hat sich selbst aus der Wartliste ausgetragen');
                $cs = CourseSet::getSetForCourse($course_id);
                if ($cs) {
                    $prio_delete = AdmissionPriority::unsetPriority($cs->getId(), $GLOBALS['user']->id, $course_id);
                }
                $query     = "DELETE FROM admission_seminar_user WHERE user_id = ? AND seminar_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array($GLOBALS['user']->id,
                                          $course_id));
                if ($statement->rowCount() || $prio_delete) {
                    //Warteliste neu sortieren
                    renumber_admission($course_id);
                    //Pruefen, ob es Nachruecker gibt
                    update_admission($course_id);
                    PageLayout::postMessage(MessageBox::success(sprintf(_("Der Eintrag in der Anmelde- bzw. Warteliste der Veranstaltung <b>%s</b> wurde aufgehoben.
                    Wenn Sie an der Veranstaltung teilnehmen wollen, m&uuml;ssen Sie sich erneut bewerben."), htmlReady($current_seminar->name))));
                }
            }

            $this->redirect('my_courses/index');
        }
    }


    /**
     * Overview for achived courses
     * TODO: Caching?
     */
    public function archive_action()
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Meine archivierten Veranstaltungen'));
        PageLayout::setHelpKeyword('Basis.MeinArchiv');
        Navigation::activateItem('/browse/my_courses/archive');
        SkipLinks::addIndex(_('Hauptinhalt'), 'layout_content', 100);
        $sortby = Request::option('sortby', 'name');

        $query = "SELECT semester, name, seminar_id, status, archiv_file_id,
                         LENGTH(forumdump) > 0 AS forumdump, # Test for existence
                         LENGTH(wikidump) > 0 AS wikidump    # Test for existence
                  FROM archiv_user
                  LEFT JOIN archiv USING (seminar_id)
                  WHERE user_id = :user_id
                  GROUP BY seminar_id
                  ORDER BY start_time DESC, :sortby";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $GLOBALS['user']->id);
        $statement->bindValue(':sortby', $sortby, StudipPDO::PARAM_COLUMN);
        $statement->execute();
        $this->seminars = $statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC); // Groups by semester
    }

    /**
     * Checks the whole course selection deppending on grouping eneabled or not
     * @param $my_obj
     * @param string $group_field
     * @return bool
     */
    function check_for_new($my_obj, $group_field = 'sem_number')
    {
        if (empty($my_obj)) {
            return false;
        }

        foreach ($my_obj as $courses) {
            if ($group_field !== 'sem_number') {
                // tlx: If array is 2-dimensional, merge it into a 1-dimensional
                $courses = call_user_func_array('array_merge', $courses);
            }
            foreach ($courses as $course) {
                if ($this->check_course($course)) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Set the selected semester and redirects to index
     * @param null $sem
     */
    public function set_semester_action($sem = null)
    {
        $sem = Request::option('sem_select', $sem);
        // check input value
        if (!Request::option('sem_select') && !is_null($sem)) {
            if (!in_array($sem, array('future', 'last', 'current', 'all'))) {
                $this->redirect('my_courses/index');
                return;
            }
        }
        if (!is_null($sem)) {
            $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_CYCLE', $sem);
        }
        PageLayout::postMessage(MessageBox::success(_('Das gew�nschte Semester bzw.
            die gew�nschte Semester Filteroption wurde ausgew�hlt!')));
        $this->redirect('my_courses/index');
    }

    /**
     * Checks the selected courses for news (e.g. forum posts,...)
     * Returns true if something new happens and enables the reset function
     * @param $seminar_content
     * @return bool
     */
    function check_course($seminar_content)
    {
        if ($seminar_content['visitdate'] <= $seminar_content['chdate'] || $seminar_content['last_modified'] > 0) {
            $last_modified = $seminar_content['visitdate'] <= $seminar_content['chdate']
                              && $seminar_content['chdate'] > $seminar_content['last_modified']
                           ? $seminar_content['chdate']
                           : $seminar_content['last_modified'];
            if ($last_modified) {
                return true;
            }
        }

        $plugins_navigation = getPluginNavigationForSeminar($seminar_content['seminar_id'], $seminar_content['visitdate']);

        foreach ($plugins_navigation as $navigation) {
            if ($navigation && $navigation->isVisible(true) && $navigation->hasBadgeNumber()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get widget for grouping selected courses (e.g. by colors, ...)
     * @param      $action
     * @param bool $selected
     * @return string
     */
    private function setGroupingSelector(&$group_field)
    {
        $sidebar = Sidebar::Get();
        $groups  = array(
            'sem_number'  => _('Veranstaltungszentriert'),
            'sem_tree_id' => _('Studienbereich'),
            'sem_status'  => _('Typ'),
            'gruppe'      => _('Farbgruppen'),
            'dozent_id'   => _('Dozenten'),
        );
        $list = new OptionsWidget();        
        $list->setTitle(_('Kategorie zur Gliederung'));
        foreach ($groups as $key => $group) {
            $list->addRadioButton($group,
                                  $this->url_for('my_courses/store_groups?select_group_field=' . $key),
                                  $key === $group_field);
        }
        $sidebar->addWidget($list);
        return $list;
    }

    /**
     * Returns a widtget for semester selection
     * @param $sem
     * @return OptionsWidget
     */
    private function setSemesterWidget(&$sem)
    {
        $sidebar = Sidebar::Get();
        $widget  = new OptionsWidget();
        $widget->setTitle(_('Semesterfilter'));

        $widget->addRadioButton(_('Aktuelles Semester'),
                                $this->url_for('my_courses/set_semester/current'),
                                $sem == 'current' && !Request::option('sem_select'));

        $widget->addRadioButton(_('Aktuelles und zuk�nftiges Semester'),
                                $this->url_for('my_courses/set_semester/future'),
                                $sem == 'future' && !Request::option('sem_select'));

        $widget->addRadioButton(_('Aktuelles und letztes Semester'),
                                $this->url_for('my_courses/set_semester/last'),
                                $sem == 'last' && !Request::option('sem_select'));

        if (Config::get()->MY_COURSES_ENABLE_ALL_SEMESTERS) {
            $widget->addRadioButton(_('Alle Semester'),
                                    $this->url_for('my_courses/set_semester/all'),
                                    $sem == 'all' && !Request::option('sem_select'));
        }

        if (Request::option('sem_select')) {
            $widget->addRadioButton(_('Ausgew�hltes Semester'),
                                    '#',
                                     Request::option('sem_select'));
        }

        $sidebar->addWidget($widget);
        $semesters = new SimpleCollection(Semester::getAll());
        $semesters->orderBy('beginn desc');
        $list = new SelectWidget(_('Semester ausw�hlen'), $this->url_for('my_courses/set_semester'), 'sem_select');
        foreach ($semesters as $semester) {
            $list->addElement(new SelectElement($semester->id, $semester->name, $semester->id == Request::get('sem_select')), 'sem_select-' . $semester->id);
        }

        $sidebar->addWidget($list);
    }

}
