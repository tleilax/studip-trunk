<?php
/**
 * courses.php - Controller for admin and seminar related
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
 * @author      David Siegfried <david@ds-labs.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category    Stud.IP
 * @since       3.1
 */
require_once 'app/models/my_realm.php';
require_once 'app/controllers/authenticated_controller.php';
require_once 'lib/meine_seminare_func.inc.php';
require_once 'lib/object.inc.php';

class Admin_CoursesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException(_('Sie haben nicht die n�tigen Rechte, um diese Seite zu betreten.'));
        }

        $this->max_show_courses = 200;

        Navigation::activateItem('/browse/my_courses/list');

        // we are defintely not in an lecture or institute
        closeObject();

        //delete all temporary permission changes
        if (is_array($_SESSION)) {
            foreach (array_keys($_SESSION) as $key) {
                if (strpos($key, 'seminar_change_view_') !== false) {
                    unset($_SESSION[$key]);
                }
            }
        }

        $this->insts = Institute::getMyInstitutes($GLOBALS['user']->id);

        if(empty($this->insts) && !$GLOBALS['perm']->have_perm('root')) {
            PageLayout::postMessage(MessageBox::error(_('Sie wurden noch keiner Einrichtung zugeordnet')));
        }

        if (!$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT) {
            $GLOBALS['user']->cfg->store('MY_INSTITUTES_DEFAULT', $this->insts[0]['Institut_id']);
        }

        // Semester selection
        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE) {
            $this->semester = Semester::find($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE);
        }

        if (Request::submitted("search") || Request::get("reset-search")) {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_SEARCHTEXT', Request::get("search"));
        }
        if (Request::submitted("teacher_filter")) {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_TEACHERFILTER', Request::option("teacher_filter"));
        }

        PageLayout::setHelpKeyword("Basis.Veranstaltungen");
        PageLayout::setTitle(_("Verwaltung von Veranstaltungen und Einrichtungen"));

    }

    /**
     * Show all courses with more options
     */
    public function index_action()
    {
        $this->sem_create_perm = in_array(Config::get()->SEM_CREATE_PERM, array('root', 'admin', 'dozent'))
            ? Config::get()->SEM_CREATE_PERM
            : 'dozent';

        // get courses only if institutes available
        $this->actions = self::getActions();
        $config_my_course_type_filter = $GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER;

        // Get the view filter
        $config_view_filter = $GLOBALS['user']->cfg->MY_COURSES_ADMIN_VIEW_FILTER_ARGS;
        $this->view_filter = isset($config_view_filter) ? unserialize($config_view_filter) : array();
        if (!$this->view_filter) {
            $this->view_filter = $this->getViewFilters();
            $GLOBALS['user']->cfg->store('MY_COURSES_ADMIN_VIEW_FILTER_ARGS', serialize($this->view_filter));
        }

        $sortFlag = (Request::get('sortFlag') == 'asc') ? 'DESC' : 'ASC';

        if (Request::option('sortby')) {
            $GLOBALS['user']->cfg->store('MEINE_SEMINARE_SORT', Request::option('sortby'));
        }

        $this->selected_action = $GLOBALS['user']->cfg->MY_COURSES_ACTION_AREA;
        if (is_null($this->selected_action)) {
            $this->selected_action = 1;
        }

        $this->sortby = $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT;
        $this->sortFlag = $sortFlag;

        $this->courses = $this->getCourses(array(
            'sortby'      => $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT,
            'sortFlag'    => $sortFlag,
            'view_filter' => $this->view_filter,
            'typeFilter'  => $config_my_course_type_filter
        ));

        if (in_array('Inhalt', $this->view_filter)) {
            $this->nav_elements = MyRealmModel::calc_nav_elements(array($this->courses));
        }
        // get all available teacher for infobox-filter
        // filter by selected teacher
        $_SESSION['MY_COURSES_LIST'] = array_map(function ($c, $id) {
            return array('Name'       => $c['Name'],
                         'Seminar_id' => $id);
        }, array_values($this->courses), array_keys($this->courses));


        $this->all_lock_rules = array_merge(
            array(array(
                'name'    => '--' . _("keine Sperrebene") . '--',
                'lock_id' => 'none'
            )),
            LockRule::findAllByType('sem')
        );
        $this->aux_lock_rules = array_merge(
            array(array(
                'name'    => '--' . _("keine Zusatzangaben") . '--',
                'lock_id' => 'none'
            )),
            AuxLockRules::getAllLockRules()
        );


        $sidebar = Sidebar::get();
        $sidebar->setImage("sidebar/seminar-sidebar.png");


        $this->setSearchWiget();
        $this->setInstSelector();
        $this->setSemesterSelector();
        $this->setTeacherWidget();
        $this->setCourseTypeWidget($config_my_course_type_filter);
        $this->setActionsWidget($this->selected_action);

        if ($this->sem_create_perm) {
            $actions = new ActionsWidget();
            $actions->addLink(_('Neue Veranstaltung anlegen'),
                URLHelper::getLink('admin_seminare_assi.php',
                    array('new_session' => 'TRUE')), 'icons/16/blue/add/seminar.png');
            $sidebar->addWidget($actions, 'links');
        }

        $this->setViewWidget($this->view_filter);

        if ($this->sem_create_perm) {
            $params = array();

            if ($GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT) {
                $params['search'] = $GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT;
            }
            $export = new ExportWidget();
            $export->addLink(_('Als Excel exportieren'),
                URLHelper::getLink('dispatch.php/admin/courses/export_csv', $params),
                'icons/16/blue/file-excel.png');
            $sidebar->addWidget($export);
        }
    }

    /**
     * Export action
     */
    public function export_csv_action()
    {
        $config_view_filter = $GLOBALS['user']->cfg->MY_COURSES_ADMIN_VIEW_FILTER_ARGS;
        $view_filter = isset($config_view_filter) ? unserialize($config_view_filter) : array();
        if (!$view_filter) {
            $view_filter = $this->getViewFilters();
            $GLOBALS['user']->cfg->store('MY_COURSES_ADMIN_VIEW_FILTER_ARGS', serialize($view_filter));
        }

        if ($pos = array_search('Inhalt', $view_filter)) {
            unset($view_filter[$pos]);
        }
        $sortby = $GLOBALS['user']->cfg->getValue('MEINE_SEMINARE_SORT');
        $config_my_course_type_filter = $GLOBALS['user']->cfg->getValue('MY_COURSES_TYPE_FILTER');

        $courses = $this->getCourses(
            array('sortby'      => $sortby,
                  'sortFlag'    => 'asc',
                  'typeFilter'  => $config_my_course_type_filter,
                  'view_filter' => $view_filter)
        );


        if (empty($view_filter)) {
            return;
        }

        $captions = array_values($view_filter);


        foreach ($courses as $course_id => $course) {
            $sem = new Seminar($course_id);

            if (in_array('Nr.', $captions)) {
                $data[$course_id][array_search('Nr.', $captions)] = $course['VeranstaltungsNummer'];
            }

            if (in_array('Name', $captions)) {
                $data[$course_id][array_search('Name', $captions)] = $course['Name'];
            }

            if (in_array('Veranstaltungstyp', $captions)) {
                $data[$course_id][array_search('Veranstaltungstyp', $captions)]
                    = $course['sem_class_name'] . ': ' . $GLOBALS['SEM_TYPE'][$course['status']]['name'];
            }

            if (in_array('Raum/Zeit', $captions)) {
                $_room = $sem->getDatesExport(array(
                    'semester_id' => $this->semester->id,
                    'show_room'   => true
                ));
                $_room = $_room ?: _('nicht angegeben');
                $data[$course_id][array_search('Raum/Zeit', $captions)] = $_room;
            }

            if (in_array('DozentIn', $captions)) {
                $dozenten = array();
                array_walk($course['dozenten'], function ($a) use (&$dozenten) {
                    $user = User::findByUsername($a['username']);
                    $dozenten[] = $user->getFullName();
                });
                $data[$course_id][array_search('DozentIn', $captions)] = !empty($dozenten) ? implode(', ', $dozenten) : '';
            }

            if (in_array('TeilnehmerInnen', $captions)) {
                $data[$course_id][array_search('TeilnehmerInnen', $captions)] = $course['teilnehmer'];
            }

            if (in_array('TeilnehmerInnen auf Warteliste', $captions)) {
                $data[$course_id][array_search('TeilnehmerInnen auf Warteliste', $captions)] = $course['waiting'];
            }

            if (in_array('Vorl�ufige Anmeldungen', $captions)) {
                $data[$course_id][array_search('Vorl�ufige Anmeldungen', $captions)] = $course['prelim'];
            }
        }

        $tmpname = md5(uniqid('Veranstaltungsexport'));
        if (array_to_csv($data, $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
            $this->redirect(GetDownloadLink($tmpname, 'Veranstaltungen_Export.csv', 4, 'force'));
            return;
        }
    }

    /**
     * Set the selected institute or semester
     */
    public function set_selection_action()
    {
        if (Request::option('institute')) {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_TEACHERFILTER', null);
            $GLOBALS['user']->cfg->store('MY_INSTITUTES_DEFAULT', Request::option('institute'));
            PageLayout::postMessage(MessageBox::success('Die gew�nschte Einrichtung wurde ausgew�hlt!'));
        }

        if (Request::option('sem_select')) {
            $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_CYCLE', Request::option('sem_select'));
            if (Request::option('sem_select') !== "all") {
                PageLayout::postMessage(MessageBox::success(sprintf(_('Das %s wurde ausgew�hlt'), Semester::find(Request::option('sem_select'))->name)));
            } else {
                PageLayout::postMessage(MessageBox::success(_('Semesterfilter abgew�hlt')));
            }
        }

        $this->redirect('admin/courses/index');
    }


    /**
     * Set the lockrules of courses
     */
    public function set_lockrule_action()
    {
        $result = false;
        $courses = Request::getArray('lock_sem');

        if (!empty($courses)) {
            foreach ($courses as $course_id => $value) {
                // force to pre selection
                if (Request::get('lock_sem_all') && Request::submitted('all')) {
                    $value = Request::get('lock_sem_all');
                }

                $course = Course::find($course_id);
                if ($value == 'none') {
                    $value = null;
                }

                if ($course->lock_rule == $value) {
                    continue;
                }

                $course->setValue('lock_rule', $value);
                if (!$course->store()) {
                    PageLayout::postMessage(MessageBox::error(sprintf(_('Bei den folgenden Veranstaltungen ist ein
                    Fehler aufgetreten')), $course->name));
                    $this->redirect('admin/courses/index');

                    return;
                }
                $result = true;
            }

            if ($result) {
                PageLayout::postMessage(MessageBox::success(sprintf(_('Die gew�nschten �nderungen wurden erfolgreich durchgef�hrt!'))));
            }
        }
        $this->redirect('admin/courses/index');
    }

    /**
     * Set the visibility of a course
     */
    public function set_visibility_action()
    {
        $result = false;
        $visibilites = Request::getArray('visibility');
        $all_courses = Request::getArray('all_sem');

        if (!empty($all_courses)) {
            foreach ($all_courses as $course_id) {
                $course = Course::find($course_id);

                $visibility = isset($visibilites[$course_id]) ? 1 : 0;

                if ((int)$course->visible == $visibility) {
                    continue;
                }

                $course->setValue('visible', $visibility);
                if (!$course->store()) {
                    PageLayout::postMessage(MessageBox::error(sprintf(_('Bei den folgenden Veranstaltungen ist ein
                    Fehler aufgetreten')), $course->name));
                    $this->redirect('admin/courses/index');

                    return;
                }
                $result = true;
            }

            if ($result) {
                PageLayout::postMessage(MessageBox::success(sprintf(_('Die Sichtbarkeit wurde bei den gew�nschten Veranstatungen erfolgreich ge�ndert!'))));
            }
        }
        $this->redirect('admin/courses/index');
    }


    /**
     * Set the additional course informations
     */
    public function set_aux_lockrule_action()
    {
        $result = false;
        $courses = Request::getArray('lock_sem');

        if (!empty($courses)) {
            foreach ($courses as $course_id => $value) {
                // force to pre selection
                if (Request::get('lock_sem_all') && Request::submitted('all')) {
                    $value = Request::get('lock_sem_all');
                }

                $course = Course::find($course_id);

                if ($value == 'none') {
                    $value = null;
                }

                if ($course->aux_lock_rule == $value) {
                    continue;
                }

                $course->setValue('aux_lock_rule', $value);
                if (!$course->store()) {
                    PageLayout::postMessage(MessageBox::error(sprintf(_('Bei den folgenden Veranstaltungen ist ein
                    Fehler aufgetreten')), $course->name));
                    $this->redirect('admin/courses/index');

                    return;
                }
                $result = true;
            }

            if ($result) {
                PageLayout::postMessage(MessageBox::success(sprintf(_('Die gew�nschten �nderungen wurden erfolgreich durchgef�hrt!'))));
            }
        }
        $this->redirect('admin/courses/index');
    }


    /**
     * Set the selected view filter and store the selection in configuration
     */
    public function set_view_filter_action($filter = null, $state = true)
    {
        // store view filter in configuration
        if (!is_null($filter)) {
            $db_filter = unserialize($GLOBALS['user']->cfg->MY_COURSES_ADMIN_VIEW_FILTER_ARGS);
            $or_filter = $filters = $this->getViewFilters();
            $selected = $or_filter[$filter];

            if ($state) {
                $db_filter = array_filter($db_filter, function ($a) use ($selected) {
                    return $a != $selected;
                });

            } else {
                array_push($db_filter, $selected);
            }

            if (empty($db_filter)) {
                $GLOBALS['user']->cfg->store('MY_COURSES_ADMIN_VIEW_FILTER_ARGS', serialize(array()));
            } else {
                $GLOBALS['user']->cfg->store('MY_COURSES_ADMIN_VIEW_FILTER_ARGS', serialize($db_filter));
            }
        }

        $this->redirect('admin/courses/index');
    }

    /**
     * Set the selected action type and store the selection in configuration
     */
    public function set_action_type_action()
    {
        // select the action area
        if (Request::option('action_area')) {
            $GLOBALS['user']->cfg->store('MY_COURSES_ACTION_AREA', Request::option('action_area'));
            PageLayout::postMessage(MessageBox::success(_('Der Aktionsbereich wurde erfolgreich �bernommen!')));
        }

        $this->redirect('admin/courses/index');
    }

    /**
     * Set the selected course type filter and store the selection in configuration
     */
    public function set_course_type_action()
    {
        if (Request::option('course_type')) {
            $GLOBALS['user']->cfg->store('MY_COURSES_TYPE_FILTER', Request::option('course_type'));
            PageLayout::postMessage(MessageBox::success(_('Der gew�nschte Veranstaltungstyp wurde �bernommen!')));
        }
        $this->redirect('admin/courses/index');
    }


    /**
     * Return a specifically action oder all available actions
     * @param null $selected
     * @return array
     */
    private static function getActions($selected = null)
    {
        // array for the avaiable modules
        $actions = array(
            1  => array('name'       => 'Grunddaten',
                        'title'      => 'Grunddaten',
                        'url'        => 'dispatch.php/course/basicdata/view?cid=%s',
                        'attributes' => array(
                            'data-dialog' => 'size=50%'
                        )),
            2  => array('name'       => 'Studienbereiche',
                        'title'      => 'Studienbereiche',
                        'url'        => 'dispatch.php/course/study_areas/show?cid=%s',
                        'attributes' => array(
                            'data-dialog' => 'size=50%'
                        )),
            3  => array('name'  => 'Zeiten / R�ume',
                        'title' => 'Zeiten / R�ume',
                        'url'   => 'raumzeit.php?cid=%s'),
            8  => array('name'      => 'Sperrebene',
                        'title'     => 'Sperrebenen',
                        'url'       => 'dispatch.php/admin/courses/set_lockrule',
                        'multimode' => true),
            9  => array('name'      => 'Sichtbarkeit',
                        'title'     => 'Sichtbarkeit',
                        'url'       => 'dispatch.php/admin/courses/set_visibility',
                        'multimode' => true),
            10 => array('name'      => 'Zusatzangaben',
                        'title'     => 'Zusatzangaben',
                        'url'       => 'dispatch.php/admin/courses/set_aux_lockrule',
                        'multimode' => true),
            11 => array('name'  => 'Veranstaltung kopieren',
                        'title' => 'Kopieren',
                        'url'   => 'admin_seminare_assi.php?cmd=do_copy&start_level=1&class=1&cp_id=%s'),
            14 => array('name'       => 'Zugangsberechtigungen',
                        'title'      => 'Zugangsberechtigungen',
                        'url'        => 'dispatch.php/course/admission?cid=%s',
                        'attributes' => array(
                            'data-dialog' => 'size=50%'
                        )),
            16 => array('name'      => 'Archivieren',
                        'title'     => 'Archivieren',
                        'url'       => 'archiv_assi.php',
                        'multimode' => true)
        );
        if (get_config('RESOURCES_ALLOW_ROOM_REQUESTS')) {
            $actions[4] = array('name'  => 'Raumanfragen',
                                'title' => 'Raumanfragen',
                                'url'   => 'dispatch.php/course/room_requests/index?cid=%s');
        }
        foreach (PluginManager::getInstance()->getPlugins("AdminCourseAction") as $plugin) {
            $actions[get_class($plugin)] = array(
                'name'      => $plugin->getPluginName(),
                'title'     => $plugin->getPluginName(),
                'url'       => $plugin->getAdminActionURL(),
                'multimode' => $plugin->useMultimode()
            );
        }

        if (is_null($selected)) {
            return $actions;
        }

        return $actions[$selected];
    }


    /**
     * Set and return all needed view filters
     * @return array
     */
    private function getViewFilters()
    {
        return array(_('Nr.'),
            _('Name'),
            _('Veranstaltungstyp'),
            _('Raum/Zeit'),
            _('DozentIn'),
            _('TeilnehmerInnen'),
            _('TeilnehmerInnen auf Warteliste'),
            _('Vorl�ufige Anmeldungen'),
            _('Inhalt'));
    }

    private function getCourses($params = array())
    {
        // Init
        if ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === "all") {
            $inst = new SimpleCollection($this->insts);
            $inst->filter(function ($a) use (&$inst_ids) {
                $inst_ids[] = $a->Institut_id;
            });
        } else {
            $institut = new Institute($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT);
            $inst_ids[] = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;
            if ($institut->isFaculty()) {
                foreach ($institut->sub_institutes->pluck("Institut_id") as $institut_id) {
                    $inst_ids[] = $institut_id;
                }
            }
        }

        $filter = AdminCourseFilter::get(true);
        $filter->where("sem_classes.studygroup_mode = '0'");

        if (is_object($this->semester)) {
            $filter->filterBySemester($this->semester->getId());
        }
        if ($params['typeFilter'] && $params['typeFilter'] !== "all") {
            $filter->filterByType($params['typeFilter']);
        }
        if ($GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT) {
            $filter->filterBySearchString($GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT);
        }
        if ($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER && ($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER !== "all")) {
            $filter->filterByDozent($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER);
        }
        $filter->filterByInstitute($inst_ids);
        if ($params['sortby'] === "status") {
            $filter->orderBy(sprintf('sem_classes.name %s, sem_types.name %s, VeranstaltungsNummer', $params['sortFlag'], $params['sortFlag'], $params['sortFlag']), $params['sortFlag']);
        } elseif($params['sortby']) {
            $filter->orderBy($params['sortby'], $params['sortFlag']);
        }

        $this->count_courses = $filter->countCourses();
        if ($this->count_courses && $this->count_courses <= $this->max_show_courses) {
            $courses = $filter->getCourses();
        } else {
            return array();
        }

        if (in_array('Inhalt', $params['view_filter'])) {
            $sem_types = SemType::getTypes();
            $modules = new Modules();
        }
        $seminars = array_map('reset', $courses);

        if (!empty($seminars)) {
            foreach ($seminars as $seminar_id => $seminar) {
                $dozenten = $this->getTeacher($seminar_id);
                $seminars[$seminar_id]['dozenten'] = $dozenten;

                if (in_array('DozentIn',  $params['view_filter'])) {

                    if (SeminarCategories::getByTypeId($seminar['status'])->only_inst_user) {
                        $search_template = "user_inst_not_already_in_sem";
                    } else {
                        $search_template = "user_not_already_in_sem";
                    }

                    $dozentUserSearch = new PermissionSearch(
                        $search_template,
                        sprintf(_("%s suchen"), get_title_for_status('dozent', 1, $seminar['status'])),
                        "user_id",
                        array('permission' => 'dozent',
                              'seminar_id' => $this->course_id,
                              'sem_perm' => 'dozent',
                              'institute' => Seminar::GetInstance($seminar_id)->getInstitutes()
                        )
                    );

                    $seminars[$seminar_id]['teacher_search'] = MultiPersonSearch::get("add_member_dozent" . $seminar_id)
                        ->setTitle(_('Mehrere DozentInnen hinzuf�gen'))
                        ->setSearchObject($dozentUserSearch)
                        ->setDefaultSelectedUser(array_keys($dozenten))
                        ->setDataDialogStatus(Request::isXhr())
                        ->setExecuteURL(URLHelper::getLink('dispatch.php/course/basicdata/add_member/' . $seminar_id, array('from' => 'admin/courses')));
                }

                if (in_array('Inhalt', $params['view_filter'])) {
                    $seminars[$seminar_id]['sem_class'] = $sem_types[$seminar['status']]->getClass();
                    $seminars[$seminar_id]['modules'] = $modules->getLocalModules($seminar_id, 'sem', $seminar['modules'], $seminar['status']);
                    $seminars[$seminar_id]['navigation'] = MyRealmModel::getAdditionalNavigations($seminar_id, $seminars[$seminar_id], $seminars[$seminar_id]['sem_class'], $GLOBALS['user']->id);
                }
            }
        }

        return $seminars;
    }

    /**
     * Return the amount of courses in a institute and given type
     * @param $id
     * @return mixed
     */
    private function getCourseAmountForStatus(&$id)
    {
        $sql = "
            SELECT COUNT(seminar_id) FROM seminare
            WHERE Institut_id = :institut_id
                AND status = :status
                AND seminare.start_time <= :semester_beginn
                AND (:semester_beginn <= (seminare.start_time + seminare.duration_time)
                    OR seminare.duration_time = -1)";
        $statement = DBManager::get()->prepare($sql);
        $statement->execute(array(
            'institut_id'     => $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT,
            'status'          => $id,
            'semester_beginn' => $this->semester->beginn
        ));
        $count = $statement->fetch(PDO::FETCH_COLUMN);

        return $count;
    }

    /**
     * TODO: SORM
     * Returns the teacher for a given cours
     * @param $course_id
     * @return array
     */
    private function getTeacher($course_id)
    {
        $query = "SELECT DISTINCT user_id, username, Nachname, CONCAT(Nachname, ', ', Vorname) as fullname
                  FROM seminar_user
                  LEFT JOIN auth_user_md5 USING (user_id)
                  WHERE Seminar_id = ? AND status='dozent'
                  ORDER BY position, Nachname ASC";

        $teacher_statement = DBManager::get()->prepare($query);
        $teacher_statement->execute(array($course_id));

        $dozenten = array_map('reset', $teacher_statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC));
        $teacher_statement->closeCursor();

        return $dozenten;
    }


    /**
     * Adds view filter to the sidebar
     * @param array $configs
     */
    private function setViewWidget($configs = array())
    {
        $configs = $configs ?: array();
        $sidebar = Sidebar::Get();
        $filters = $this->getViewFilters();
        $checkbox_widget = new OptionsWidget();
        $checkbox_widget->setTitle(_('Darstellungs-Filter'));
        $size = count($filters);

        for ($i = 0; $i < $size; $i++) {
            $state = in_array($filters[$i], $configs);
            $checkbox_widget->addCheckbox($filters[$i], $state, $this->url_for('admin/courses/set_view_filter/' . $i . '/' . $state));
        }
        $sidebar->addWidget($checkbox_widget, "views");
    }

    /**
     * Adds the institutes selector to the sidebar
     */
    private function setInstSelector()
    {
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Einrichtung'), $this->url_for('admin/courses/set_selection'), 'institute');

        if ($GLOBALS['perm']->have_perm('root') || ($GLOBALS['perm']->have_perm('admin') && count($this->insts) > 1)) {
            $list->addElement(new SelectElement(
                'all',
                $GLOBALS['perm']->have_perm('root') ? _('Alle') : _("Alle meine Einrichtungen"),
                $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === 'all'),
                'select-all'
            );
        }

        foreach ($this->insts as $institut) {
            $list->addElement(
                new SelectElement(
                    $institut['Institut_id'],
                    (!$institut['is_fak'] ? "  " : "") . $institut['Name'],
                    $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === $institut['Institut_id']
                ),
                'select-' . $institut['Name']
            );
        }


        $sidebar->addWidget($list, "filter_institute");
    }

    /**
     * Adds the semester selector to the sidebar
     */
    private function setSemesterSelector()
    {
        $semesters = array_reverse(Semester::getAll());
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Semester'), $this->url_for('admin/courses/set_selection'), 'sem_select');
        $list->addElement(new SelectElement("all", _("Alle")), 'sem_select-all');
        foreach ($semesters as $semester) {
            $list->addElement(new SelectElement(
                $semester->id,
                $semester->name,
                $semester->id === $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE
            ), 'sem_select-' . $semester->id);
        }

        $sidebar->addWidget($list, "filter_semester");
    }


    /**
     * Adds HTML-Selector to the sidebar
     * @param null $selected_action
     * @return string
     */
    private function setActionsWidget($selected_action = null)
    {
        $actions = self::getActions();
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Aktionsbereich-Auswahl'), $this->url_for('admin/courses/set_action_type'), 'action_area');

        foreach ($actions as $index => $action) {
            $list->addElement(new SelectElement($index, $action['name'], $selected_action == $index), 'action-aria-' . $index);
        }
        $sidebar->addWidget($list, 'editmode');
    }


    /**
     * Returns a course type widthet depending on all available courses and theirs types
     * @param string $selected
     * @param array $params
     * @return ActionsWidget
     */
    private function setCourseTypeWidget($selected = 'all')
    {
        $sidebar = Sidebar::get();
        $this->url = $this->url_for('admin/courses/set_course_type');
        $result = array();
        $this->types = array();
        $semCats = SeminarCategories::GetAll();
        $this->selected = $selected;
        if (!empty($semCats)) {
            foreach ($semCats as $cat) {
                $types = $cat->getTypes();
                if (!empty($types)) {
                    if (count($types) > 1) {
                        asort($types, SORT_LOCALE_STRING);
                    }
                    $result[$cat->name] = $types;
                }
            }
        }

        foreach ($result as $cat => $types) {
            foreach ($types as $id => $name) {
                $amount = $this->getCourseAmountForStatus($id);
                if ($amount > 0) {
                    $this->types[$cat][$id]['name'] = $name;
                    $this->types[$cat][$id]['amount'] = $amount;
                }
            }
        }

        $this->render_template('admin/courses/filters/course_type_filter.php', null);
        $html = $this->response->body;
        $this->erase_response();
        $widget = new SidebarWidget();
        $widget->setTitle(_('Veranstaltungstyp-Filter'));
        $widget->addElement(new WidgetElement($html));
        $sidebar->addWidget($widget, 'filter_coursetypes');
    }

    /**
     * Returns a widget to selected a specific teacher
     * @param array $teachers
     * @return ActionsWidget|null
     */
    private function setTeacherWidget()
    {
        if (!$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT || $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === "all") {
            return;
        }
        $statement = DBManager::get()->prepare("
            SELECT auth_user_md5.*, user_info.*
            FROM auth_user_md5
                LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id)
                INNER JOIN user_inst ON (user_inst.user_id = auth_user_md5.user_id)
                INNER JOIN Institute ON (Institute.Institut_id = user_inst.Institut_id)
            WHERE (Institute.Institut_id = :institut_id OR Institute.fakultaets_id = :institut_id)
                AND auth_user_md5.perms = 'dozent'
            ORDER BY auth_user_md5.Nachname ASC, auth_user_md5.Vorname ASC
        ");
        $statement->execute(array(
            'institut_id' => $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT
        ));
        $teachers = $statement->fetchAll(PDO::FETCH_ASSOC);
        $teachers = array_map(function ($data) { return User::buildExisting($data); }, $teachers);

        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Dozenten-Filter'), $this->url_for('admin/courses/index'), 'teacher_filter');
        $list->addElement(new SelectElement('all', _('alle'), Request::get('teacher_filter') == 'all'), 'teacher_filter-all');

        foreach ($teachers as $teacher) {
            $list->addElement(new SelectElement(
                $teacher->getId(),
                $teacher->getFullName("full_rev"),
                $GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER === $teacher->getId()
            ), 'teacher_filter-' . $teacher->getId());
        }

        $sidebar->addWidget($list, 'filter_teacher');
    }


    private function setSearchWiget()
    {
        $sidebar = Sidebar::Get();
        $search = new SearchWidget(URLHelper::getLink('dispatch.php/admin/courses'));
        $search->addNeedle(_('Freie Suche'), 'search', true, null, null, $GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT);
        $sidebar->addWidget($search, 'filter_search');
    }
}
