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
require_once 'lib/meine_seminare_func.inc.php';
require_once 'lib/object.inc.php';
require_once 'lib/archiv.inc.php'; //for lastActivity in getCourses() method

class Admin_CoursesController extends AuthenticatedController
{

    /**
     * This helper method retrieves the values of datafields when
     * the user started a search for courses matching a specific value of
     * one or more datafields.
     * This method also checks if a datafield is activated by the user
     * and will reject any value for datafields that aren't activated by the user.
     *
     * @return Array Associative array, consisting of datafield names
     * (as array keys) and values for those datafields.
     */
    private function getDatafieldFilters()
    {
        //first get the active datafields of the user:
        $userConfig = UserConfig::get(User::findCurrent()->id);
        $userSelectedElements = json_decode($userConfig->getValue('ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS'), true);

        $activeDatafields = $userSelectedElements['datafields'];

        if (!$activeDatafields) {
            return array();
        }

        //Ok, we have a list of active datafields whose value may be searched for.
        //We must check for the request parameters (df_$DATAFIELD_ID)
        //and return their IDs with a value.

        $searchedDatafields = array();

        foreach ($activeDatafields as $activeField) {
            $requestParamValue = Request::get('df_'.$activeField);
            if ($requestParamValue) {
                $searchedDatafields[$activeField] = $requestParamValue;
            }
        }

        return $searchedDatafields;
    }


    /**
     * This method returns the appropriate widget for the given datafield.
     *
     * @param DataField datafield The datafield whose widget is requested.
     *
     * @return SidebarWidget|null Returns a SidebarWidget derivative or null in case of an error.
     */
    private function getDatafieldWidget(DataField $datafield)
    {
        if ($datafield->accessAllowed()) {
            //The current user is allowed to see this datafield.
            //Now we must distinguish between the different types of data fields:

            $type = $datafield->type;

            if ($type == 'bool') {
                //bool fields just need a checkbox for the states TRUE and FALSE

                $checkboxWidget = new OptionsWidget($datafield->name);
                $checkboxWidget->addCheckbox(
                    _('Feld gesetzt'),
                    (bool)Request::get('df_'.$datafield->id, false),
                    URLHelper::getLink(
                        'dispatch.php/admin/courses/index',
                        array('df_'.$datafield->id => '1')
                    ),
                    URLHelper::getLink(
                        'dispatch.php/admin/courses/index'
                    )
                );

                return $checkboxWidget;

            } elseif (($type == 'selectbox') or ($type == 'radio')
                or ($type == 'selectboxmultiple')) {
                //these field's options are displayed as select box

                $db = DBManager::get();

                $statement = $db->prepare(
                      'SELECT content FROM datafields_entries '
                    . 'WHERE datafield_id = :datafieldId '
                    . 'GROUP BY content;'
                );

                $statement->execute(array('datafieldId' => $datafield->id));
                $result = $statement->fetchAll();


                $options = array();
                foreach ($result as $row) {
                    $options[$row[0]] = $row[0];
                }

                if ($options) {
                    $options = array_merge(
                        array(' ' => _('(keine Auswahl)')),
                        $options
                    );
                    $selectWidget = new OptionsWidget($datafield->name);
                    $selectWidget->addSelect(
                        '',
                        '', //TODO
                        'df_'.$datafield->id,
                        $options,
                        Request::get('df_'.$datafield->id)
                    );
                    return $selectWidget;
                }

                return null;

            } else {
                //all other fields get a text field

                $textWidget = new SearchWidget();
                $textWidget->setTitle($datafield->name);
                $textWidget->addNeedle(
                    '',
                    'df_'.$datafield->id
                );

                return $textWidget;
            }

        }
    }

    /**
     * This method is responsible for building the sidebar.
     *
     * Depending on the sidebar elements the user has selected some of those
     * elements are shown or not. To find out what elements
     * the user has selected the user configuration is accessed.
     *
     * @param string courseTypeFilterConfig The selected value for the course type filter field, defaults to null.
     * @return null This method does not return any value.
     */
    private function buildSidebar($courseTypeFilterConfig = null)
    {
        /*
            Depending on the elements the user has selected
            some of the following elements may not be presented
            in the sidebar.

            To find out what elements the user has selected we're
            accessing the user configuration and check if the
            right configuration keys are stored in there.
        */
        $userConfig = UserConfig::get(User::findCurrent()->id);

        $userSelectedElements = $userConfig->getValue('ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS');

        $visibleElements = array();

        if ($userSelectedElements) {
            /*
                The array of user-selected elements is a JSON array:
                Decode it and set the visibleElements array.
            */
            $visibleElements = json_decode($userSelectedElements, true);

        } else {
           /*
                The user hasn't made a selection of sidebar elements.
                So the default selection is set:
            */

            $visibleElements = [
                'search' => true,
                'institute' => true,
                'semester' => true,
                'courseType' => true,
                'viewFilter' => true
            ];
        }

        $sidebar = Sidebar::get();
        $sidebar->setImage("sidebar/seminar-sidebar.png");

        //navigation was already drawn!

        /*
            Order of elements:
            * Navigation
            * selected filters (configurable)
            * selected actions widget
            * actions
            * view filter (configurable)
            * export
        */

        /*
            Now draw the configurable elements according
            to the values inside the visibleElements array.
        */
        if ($visibleElements['search']) {
            $this->setSearchWiget();
        }
        if ($visibleElements['institute']) {
            $this->setInstSelector();
        }
        if ($visibleElements['semester']) {
            $this->setSemesterSelector();
        }
        if ($visibleElements['courseType']) {
            $this->setCourseTypeWidget($courseTypeFilterConfig);
        }
        if ($visibleElements['teacher']) {
            $this->setTeacherWidget();
        }

        //if there are datafields in the list, draw their input fields, too:
        if ($visibleElements['datafields']) {
            //The datafields entry contains an array with datafield-IDs.
            //We must fetch them from the database and show an appropriate widget
            //for each datafield.

            $visibleDatafieldIds = $visibleElements['datafields'];

            $datafields = DataField::getDataFields('sem');

            if ($datafields) {
                foreach ($datafields as $datafield) {
                    if (in_array($datafield->id, $visibleDatafieldIds)) {
                        $widget = $this->getDatafieldWidget($datafield);

                        if ($widget) {
                            $sidebar->addWidget($widget);
                        }
                    }
                }
            }
        }


        //this shall be visible in every case:
        $this->setActionsWidget($this->selected_action);


        //actions: always visible, too
        if ($GLOBALS['perm']->have_perm($this->sem_create_perm)) {
            $actions = new ActionsWidget();
            $actions->addLink(_('Neue Veranstaltung anlegen'),
                              URLHelper::getLink('dispatch.php/course/wizard'),
                              Icon::create('seminar+add', 'clickable'))->asDialog('size=50%');
            $actions->addLink(
                _('Diese Seitenleiste konfigurieren'),
                URLHelper::getLink('dispatch.php/admin/courses/sidebar'),
                Icon::create('admin', 'clickable')
                )->asDialog();


            $sidebar->addWidget($actions, 'links');
        }

        //the view filter's visibility is configurable:
        if (in_array('viewFilter', $visibleElements)) {
            $this->setViewWidget($this->view_filter);
        }


        //"export as Excel" is always visible:
        if ($this->sem_create_perm) {
            $params = array();

            if ($GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT) {
                $params['search'] = $GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT;
            }
            $export = new ExportWidget();
            $export->addLink(_('Als Excel exportieren'),
                             URLHelper::getLink('dispatch.php/admin/courses/export_csv', $params),
                             Icon::create('file-excel', 'clickable'));
            $sidebar->addWidget($export);
        }
    }


    /**
     * Common tasks for all actions
     *
     * @param String $action Called action
     * @param Array  $args   Possible arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['perm']->have_perm('admin')) {
            Navigation::activateItem('/browse/my_courses/list');
        } else {
            Navigation::activateItem('/browse/admincourses');
        }

        // we are defintely not in an lecture or institute
        closeObject();

        //delete all temporary permission changes
        if (is_array($_SESSION)) {
            foreach (array_keys($_SESSION) as $key) {
                if (mb_strpos($key, 'seminar_change_view_') !== false) {
                    unset($_SESSION[$key]);
                }
            }
        }

        $this->insts = Institute::getMyInstitutes($GLOBALS['user']->id);

        if (empty($this->insts) && !$GLOBALS['perm']->have_perm('root')) {
            PageLayout::postMessage(MessageBox::error(_('Sie wurden noch keiner Einrichtung zugeordnet')));
        }

        if (!$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT) {
            $GLOBALS['user']->cfg->store('MY_INSTITUTES_DEFAULT', $this->insts[0]['Institut_id']);
        }

        // Semester selection
        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE) {
            $this->semester = Semester::find($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE);
        }

        if (Request::submitted("search")) {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_SEARCHTEXT', Request::get("search"));
        }
        if (Request::get("reset-search")) {
            $GLOBALS['user']->cfg->delete('ADMIN_COURSES_SEARCHTEXT');
        }
        if (Request::submitted("teacher_filter")) {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_TEACHERFILTER', Request::option("teacher_filter"));
        }

        PageLayout::setHelpKeyword("Basis.Veranstaltungen");
        PageLayout::setTitle(_("Verwaltung von Veranstaltungen und Einrichtungen"));
        Sidebar::Get()->setTitle(_('Veranstaltungsadministration'));
        PageLayout::addSqueezePackage('raumzeit');
        // Add admission functions.
        PageLayout::addSqueezePackage('admission');
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
        $this->actions = $this->getActions();

        $config_my_course_type_filter = $GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER;

        // Get the view filter
        $this->view_filter = $this->getFilterConfig();

        if (Request::get('sortFlag')) {
            $GLOBALS['user']->cfg->store('MEINE_SEMINARE_SORT_FLAG', Request::get('sortFlag') == 'asc' ? 'DESC' : 'ASC');
        }
        if (Request::option('sortby')) {
            $GLOBALS['user']->cfg->store('MEINE_SEMINARE_SORT', Request::option('sortby'));
        }

        $this->selected_action = $GLOBALS['user']->cfg->MY_COURSES_ACTION_AREA;
        if (is_null($this->selected_action) || (!is_numeric($this->selected_action) && !class_exists($this->selected_action))) {
            $this->selected_action = 1;
        }

        $this->sortby = $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT;
        $this->sortFlag = $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT_FLAG ?: 'ASC';

        $this->courses = $this->getCourses(array(
            'sortby'      => $this->sortby,
            'sortFlag'    => $this->sortFlag,
            'view_filter' => $this->view_filter,
            'typeFilter'  => $config_my_course_type_filter,
            'datafields' => $this->getDatafieldFilters()
        ));

        if (in_array('contents', $this->view_filter)) {
            $this->nav_elements = MyRealmModel::calc_nav_elements(array($this->courses));
        }
        // get all available teacher for infobox-filter
        // filter by selected teacher
        $_SESSION['MY_COURSES_LIST'] = array_map(function ($c, $id) {
            return array('Name'       => $c['Name'],
                         'Seminar_id' => $id);
        }, array_values($this->courses), array_keys($this->courses));


        $this->all_lock_rules = new SimpleCollection(array_merge(
            array(array(
                'name'    => '--' . _("keine Sperrebene") . '--',
                'lock_id' => 'none'
            )),
            LockRule::findAllByType('sem')
        ));
        $this->aux_lock_rules = array_merge(
            array(array(
                'name'    => '--' . _("keine Zusatzangaben") . '--',
                'lock_id' => 'none'
            )),
            AuxLockRules::getAllLockRules()
        );


        //build the sidebar:
        $this->buildSidebar($config_my_course_type_filter);

    }


    /**
     * The sidebar action is responsible for showing a dialog
     * that lets the user configure what elements of the sidebar are visible
     * and which will be invisible.
     *
     * @return null This method does not return any value.
     */
    public function sidebar_action()
    {
        if (Request::get('updateConfig', false)) {
            /*
                The user has changed the configuration.
                Collect the activated elements:
            */

            $searchActive = Request::get('searchActive');
            $instituteActive = Request::get('instituteActive');
            $semesterActive = Request::get('semesterActive');
            $courseTypeActive = Request::get('courseTypeActive');
            $teacherActive = Request::get('teacherActive');
            $viewFilterActive = Request::get('viewFilterActive');
            $activeDatafields = Request::getArray('activeDatafields');

            //echo "searchActive = " . $searchActive;

            //check for the standard configuration:
            if ($searchActive and $instituteActive and $semesterActive and $courseTypeActive
                and (!$teacherActive) and (!$activeDatafields) and (!$actionAreaActive) and $viewFilterActive) {
                /*
                    It is the standard configuration:
                    Remove the entry from the user configuration table,
                    if it exists.
                */

                $userConfig = UserConfig::get(User::findCurrent()->id);
                $userConfig->delete('ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS');
                $this->redirect('admin/courses/index');

            } else {
                /*
                    It's not the standard configuration:
                    Update or create an entry for the current user
                    in the user configuration table.
                */
                $activeArray = array();
                if ($searchActive) {
                    $activeArray['search'] = true;
                }
                if ($instituteActive) {
                    $activeArray['institute'] = true;
                }
                if ($semesterActive) {
                    $activeArray['semester'] = true;
                }
                if ($courseTypeActive) {
                    $activeArray['courseType'] = true;
                }
                if ($teacherActive) {
                    $activeArray['teacher'] = true;
                }
                if ($viewFilterActive) {
                    $activeArray['viewFilter'] = true;
                }

                if ($activeDatafields) {
                    $activeArray['datafields'] = $activeDatafields;
                }

                //the array is filled: now convert it to JSON:
                $activeArray = json_encode(studip_utf8encode($activeArray));

                //store the configuration value:
                $userConfig = UserConfig::get(User::findCurrent()->id);
                $success = $userConfig->store(
                    'ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS',
                    $activeArray
                );


                /*
                    We're done: redirect to the index page
                    to see the new sidebar in all of its glory!
                */
                $this->redirect('admin/courses/index');
            }
        } else {
            /*
                The user accesses the page to check the current configuration.
            */

            $this->datafields = DataField::getDataFields('sem');


            $userSelectedElements = UserConfig::get(User::findCurrent()->id)->getValue('ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS');
            if ($userSelectedElements) {
                // The array of selected elements is a JSON array: decode it!
                $this->userSelectedElements = json_decode($userSelectedElements, true);
            } else {
                /*
                    The user hasn't got his own sidebar configuration:
                    Use the default settings.
                */
                $this->userSelectedElements = [
                    'search' => true,
                    'institute' => true,
                    'semester' => true,
                    'courseType' => true,
                    'viewFilter' => true
                    ];
            }

            //add the last activity for each Course object:
            $this->lastActivities = array();

        }
    }


    /**
     * Export action
     */
    public function export_csv_action()
    {
        $filter_config = $this->getFilterConfig();
        unset($filter_config['contents']);

        if (empty($filter_config)) {
            return;
        }

        $sortby = $GLOBALS['user']->cfg->getValue('MEINE_SEMINARE_SORT');
        $config_my_course_type_filter = $GLOBALS['user']->cfg->getValue('MY_COURSES_TYPE_FILTER');

        $courses = $this->getCourses(array(
            'sortby'      => $sortby,
            'sortFlag'    => 'asc',
            'typeFilter'  => $config_my_course_type_filter,
            'view_filter' => $filter_config,
        ));

        $view_filters = $this->getViewFilters();

        $data = array();
        foreach ($courses as $course_id => $course) {
            $sem = new Seminar(Course::buildExisting($course));
            $row = array();

            if (in_array('number', $filter_config)) {
                $row['number'] = $course['VeranstaltungsNummer'];
            }

            if (in_array('name', $filter_config)) {
                $row['name'] = $course['Name'];
            }

            if (in_array('type', $filter_config)) {
                $row['type'] = sprintf('%s: %s',
                                       $sem->getSemClass()['name'],
                                       $sem->getSemType()['name']);
            }

            if (in_array('room_time', $filter_config)) {
                $_room = $sem->getDatesExport(array(
                    'semester_id' => $this->semester->id,
                    'show_room'   => true
                ));
                $row['room_time'] = $_room ?: _('nicht angegeben');
            }

            if (in_array('teachers', $filter_config)) {
                $row['teachers'] = implode(', ', array_map(function ($d) {return $d['fullname'];}, $course['dozenten']));
            }

            if (in_array('members', $filter_config)) {
                $row['members'] = $course['teilnehmer'];
            }

            if (in_array('waiting', $filter_config)) {
                $row['waiting'] = $course['waiting'];
            }

            if (in_array('preliminary', $filter_config)) {
                $row['preliminary'] = $course['prelim'];
            }

            if (in_array('last_activity', $filter_config)) {
                $row['last_activity'] = $course['lastActivity'];
            }

            $data[$course_id] = $row;
        }

        $captions = array();
        foreach ($filter_config as $index) {
            $captions[$index] = $view_filters[$index];
        }

        $tmpname = md5(uniqid('Veranstaltungsexport'));
        if (array_to_csv($data, $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
            $this->redirect(FileManager::getDownloadURLForTemporaryFile(
                $tmpname,
                'Veranstaltungen_Export.csv'
                )
            );
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
        if (!$GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }
        $result = false;
        $courses = Request::getArray('lock_sem');
        $errors = array();

        if (!empty($courses)) {
            foreach ($courses as $course_id => $value) {
                if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
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
                        $errors[] = $course->name;
                    } else {
                        $result = true;
                    }
                }
            }

            if ($result) {
                PageLayout::postMessage(MessageBox::success(_('Die gew�nschten �nderungen wurden erfolgreich durchgef�hrt!')));
            }
            if ($errors) {
                PageLayout::postMessage(MessageBox::error(_('Bei den folgenden Veranstaltungen ist ein Fehler aufgetreten'), array_map('htmlReady', $errors)));
            }
        }
        $this->redirect('admin/courses/index');
    }


    /**
     * Lock or unlock courses
     */
    public function set_locked_action()
    {
        $admission_locked = Request::getArray('admission_locked');

        $all_courses = Request::getArray('all_sem');

        $course_set_id = CourseSet::getGlobalLockedAdmissionSetId();

        foreach ($all_courses as $course_id) {
            if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
                $set = CourseSet::getSetForCourse($course_id);

                if (!is_null($set)) {
                    if (!$set->hasAdmissionRule('LockedAdmission')) {
                        continue;
                    }

                    if ($set->hasAdmissionRule('LockedAdmission') && !isset($admission_locked[$course_id])) {
                        if (CourseSet::removeCourseFromSet($set->getId(), $course_id)) {
                            $log_msg = _('Veranstaltung wurde entsperrt');
                        }
                    }
                }

                if (is_null($set) && isset($admission_locked[$course_id])) {
                    if (CourseSet::addCourseToSet($course_set_id, $course_id)) {
                        $log_msg = sprintf(_('Veranstaltung wurde gesperrt, set_id: %s'), $course_set_id);
                    }
                }

                if ($log_msg) {
                    StudipLog::log('SEM_CHANGED_ACCESS', $course_id, NULL, $log_msg);
                }
            }
        }

        PageLayout::postMessage(MessageBox::success(_('Die gew�nschten �nderungen wurden ausgef�hrt!')));
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
        $errors = array();

        if (!empty($all_courses)) {
            foreach ($all_courses as $course_id) {
                if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                    $course = Course::find($course_id);

                    $visibility = isset($visibilites[$course_id]) ? 1 : 0;

                    if ((int)$course->visible == $visibility) {
                        continue;
                    }

                    $course->setValue('visible', $visibility);
                    if (!$course->store()) {
                        $errors[] = $course->name;
                    } else {
                        $result = true;
                        StudipLog::log($visibility ? 'SEM_VISIBLE' : 'SEM_INVISIBLE', $course->id);
                    }
                }
            }

            if ($result) {
                PageLayout::postMessage(MessageBox::success(_('Die Sichtbarkeit wurde bei den gew�nschten Veranstatungen erfolgreich ge�ndert!')));
            }
            if ($errors) {
                PageLayout::postMessage(MessageBox::error(_('Bei den folgenden Veranstaltungen ist ein Fehler aufgetreten'), array_map('htmlReady', $errors)));
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
        $lock_sem_forced = Request::getArray('lock_sem_forced');
        $errors = array();
        if (!empty($courses)) {
            foreach ($courses as $course_id => $value) {
                if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                    // force to pre selection
                    if (Request::submitted('all')) {
                        $value = Request::get('lock_sem_all');
                        $value_forced = Request::int('aux_all_forced');
                    } else {
                        $value_forced = $lock_sem_forced[$course_id];
                    }

                    $course = Course::find($course_id);

                    if (!$value) {
                        $value_forced = 0;
                    }

                    $course->setValue('aux_lock_rule', $value);
                    $course->setValue('aux_lock_rule_forced', $value_forced);

                    $ok = $course->store();
                    if ($ok === false) {
                        $errors[] = $course->name;
                    } elseif ($ok) {
                        $result = true;
                    }
                }
            }

            if ($result) {
                PageLayout::postMessage(MessageBox::success(_('Die gew�nschten �nderungen wurden erfolgreich durchgef�hrt!')));
            }
            if ($errors) {
                PageLayout::postMessage(MessageBox::error(_('Bei den folgenden Veranstaltungen ist ein Fehler aufgetreten'), array_map('htmlReady', $errors)));
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
            $filters = $this->getFilterConfig();

            if ($state) {
                $filters = array_diff($filters, array($filter));
            } else {
                $filters[] = $filter;
            }

            $this->setFilterConfig($filters);
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
     * Marks a course as complete/incomplete.
     *
     * @param String $course_id Id of the course
     */
    public function toggle_complete_action($course_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
            throw new AccessDeniedException();
        }
        $course = Course::find($course_id);
        $course->is_complete = !$course->is_complete;
        $course->store();

        if (Request::isXhr()) {
            $this->render_json((bool)$course->is_complete);
        } else {
            $this->redirect('admin/courses/index#course-' . $course_id);
        }
    }

    /**
     * Return a specifically action or all available actions
     * @param null $selected
     * @return array
     */
    private function getActions($selected = null)
    {
        // array for the avaiable modules
        $sem_filter = $this->semester ? $this->semester->beginn : 'all';
        $actions = array(
            1 => array(
                'name'       => _('Grunddaten'),
                'title'      => _('Grunddaten'),
                'url'        => 'dispatch.php/course/basicdata/view?cid=%s',
                'attributes' => ['data-dialog' => 'size=big'],
            ),
            2 => array(
                'name'       => _('Studienbereiche'),
                'title'      => _('Studienbereiche'),
                'url'        => 'dispatch.php/course/study_areas/show/?cid=%s&from=admin/courses',
                'attributes' => ['data-dialog' => 'size=big'],
            ),
            3 => array(
                'name'       => _('Zeiten / R�ume'),
                'title'      => _('Zeiten / R�ume'),
                'url'        => 'dispatch.php/course/timesrooms/index?cid=%s',
                'attributes' => ['data-dialog' => 'size=big'],
                'params'     => array(
                    'newFilter' => $sem_filter,
                    'cmd'       => 'applyFilter'
                ),
            ),
            8 => array(
                'name'      => _('Sperrebene'),
                'title'     => _('Sperrebenen'),
                'url'       => 'dispatch.php/admin/courses/set_lockrule',
                'multimode' => true
            ),
            9 => array(
                'name'      => _('Sichtbarkeit'),
                'title'     => _('Sichtbarkeit'),
                'url'       => 'dispatch.php/admin/courses/set_visibility',
                'multimode' => true
            ),
            10 => array(
                'name'      => _('Zusatzangaben'),
                'title'     => _('Zusatzangaben'),
                'url'       => 'dispatch.php/admin/courses/set_aux_lockrule',
                'multimode' => true
            ),
            11 => array(
                'name'       => _('Veranstaltung kopieren'),
                'title'      => _('Kopieren'),
                'url'        => 'dispatch.php/course/wizard/copy/%s',
                'attributes' => ['data-dialog' => 'size=big'],
            ),
            14 => array(
                'name'       => 'Zugangsberechtigungen',
                'title'      => _('Zugangsberechtigungen'),
                'url'        => 'dispatch.php/course/admission?cid=%s',
                'attributes' => ['data-dialog' => 'size=big'],
            ),
            16 => array(
                'name'       => _('Archivieren'),
                'title'      => _('Archivieren'),
                'url'        => 'dispatch.php/course/archive/confirm',
                'multimode'  => true
            ),
            17 => array(
                'name'      => _('Gesperrte Veranstaltungen'),
                'title'     => _('Einstellungen speichern'),
                'url'       => 'dispatch.php/admin/courses/set_locked',
                'multimode' => true
            ),
            18 => array(
                'name'       => _('Startsemester'),
                'title'      => _('Startsemester'),
                'url'        => 'dispatch.php/course/timesrooms/editSemester?cid=%s&origin=admin_courses',
                'attributes' => ['data-dialog' => 'size=400'],
            ),
            19 => array(
                'name'       => _('LV-Gruppen'),
                'title'      => _('LV-Gruppen'),
                'url'        => 'plugins.php/mvvplugin/lvgselector?cid=%s&from=admin/courses',
                'attributes' => ['data-dialog' => 'size=big'],
            ),
        );

        if (!PluginEngine::getPlugin('MVVPlugin') || !PluginEngine::getPlugin('MVVPlugin')->isVisible()) {
            unset($actions[19]);
        }

        if (!$GLOBALS['perm']->have_perm('admin')) {
            unset($actions[8]);
            if (!get_config('ALLOW_DOZENT_ARCHIV')) {
                unset($actions[16]);
            }
        }
        if (!$GLOBALS['perm']->have_perm('dozent')) {
            unset($actions[11]);
            unset($actions[16]);
        }

        if (Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            $actions[4] = array(
                'name'  => 'Raumanfragen',
                'title' => _('Raumanfragen'),
                'url'   => 'dispatch.php/course/room_requests/index?cid=%s&origin=admin_courses',
                'attributes' => ['data-dialog' => 'size=big'],
            );
        }
        ksort($actions);

        foreach (PluginManager::getInstance()->getPlugins('AdminCourseAction') as $plugin) {
            $actions[get_class($plugin)] = array(
                'name'      => $plugin->getPluginName(),
                'title'     => $plugin->getPluginName(),
                'url'       => $plugin->getAdminActionURL(),
                'attributes' => ['data-dialog' => 'size=big'],
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
        return array(
            'number'        => _('Nr.'),
            'name'          => _('Name'),
            'type'          => _('Veranstaltungstyp'),
            'room_time'     => _('Raum/Zeit'),
            'semester'      => _('Semester'),
            'teachers'      => _('Lehrende'),
            'members'       => _('Teilnehmende'),
            'waiting'       => _('Personen auf Warteliste'),
            'preliminary'   => _('Vorl�ufige Anmeldungen'),
            'contents'      => _('Inhalt'),
            'last_activity' => _('letzte Aktivit�t'),
        );
    }

    /**
     * Returns all courses matching set criteria.
     *
     * @param Array $params Additional parameters
     * @return Array of courses
     */
    private function getCourses($params = array())
    {
        // Init
        if ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === "all") {
            $inst = new SimpleCollection($this->insts);
            $inst->filter(function ($a) use (&$inst_ids) {
                $inst_ids[] = $a->Institut_id;
            });
        } else {
            //We must check, if the institute ID belongs to a faculty
            //and has the string _i appended to it.
            //In that case we must display the courses of the faculty
            //and all its institutes.
            //Otherwise we just display the courses of the faculty.

            $instituteIdFields = explode('_', $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT);

            $institut = new Institute($instituteIdFields[0]);

            if (!$institut->isFaculty() || ($instituteIdFields[1] == 'withinst')) {
                //The institute is not a faculty or the institute-ID had the string _i appended:
                //Pick the institute IDs of the faculty/institute and of all sub-institutes.
                $inst_ids[] = $instituteIdFields[0];
                if ($institut->isFaculty()) {
                    foreach ($institut->sub_institutes->pluck("Institut_id") as $institut_id) {
                        $inst_ids[] = $institut_id;
                    }
                }
            } else {
                //The institute is a faculty and the string _i wasn't appended to the institute-ID:
                //Pick only the institute ID of the faculty:
                $inst_ids[] = $instituteIdFields[0];
            }
        }

        $filter = AdminCourseFilter::get(true);

        if ($params['datafields']) {
            //enable filtering by datafield values:
            $filter->settings['query']['joins']['datafields_entries'] = array(
                    'join' => "INNER JOIN",
                    'on' => "seminare.seminar_id = datafields_entries.range_id"
            );

            //and use the where-clause for each datafield:

            foreach ($params['datafields'] as $fieldId => $fieldValue) {
                $filter->where("datafields_entries.datafield_id = :fieldId "
                    . "AND datafields_entries.content = :fieldValue",
                    array(
                        'fieldId' => $fieldId,
                        'fieldValue' => $fieldValue
                    )
                );
            }

        }

        $filter->where("sem_classes.studygroup_mode = '0'");

        if (is_object($this->semester)) {
            $filter->filterBySemester($this->semester->getId());
        }
        if ($params['typeFilter'] && $params['typeFilter'] !== "all") {
            list($class_filter,$type_filter) = explode('_', $params['typeFilter']);
            if (!$type_filter && !empty($GLOBALS['SEM_CLASS'][$class_filter])) {
                $type_filter = array_keys($GLOBALS['SEM_CLASS'][$class_filter]->getSemTypes());
            }
            $filter->filterByType($type_filter);
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
        } elseif ($params['sortby'] === 'completion') {
            $filter->orderBy('is_complete', $params['sortFlag']);
        } elseif ($params['sortby']) {
            $filter->orderBy($params['sortby'], $params['sortFlag']);
        }
        $filter->storeSettings();
        $this->count_courses = $filter->countCourses();
        if ($this->count_courses && $this->count_courses <= $filter->max_show_courses) {
            $courses = $filter->getCourses();
        } else {
            return array();
        }

        if (in_array('contents', $params['view_filter'])) {
            $sem_types = SemType::getTypes();
            $modules = new Modules();
        }

        $seminars = array_map('reset', $courses);

        if (!empty($seminars)) {
            foreach ($seminars as $seminar_id => $seminar) {
                $seminars[$seminar_id]['seminar_id'] = $seminar_id;
                $dozenten = $this->getTeacher($seminar_id);
                $seminars[$seminar_id]['dozenten'] = $dozenten;

                if (in_array('contents', $params['view_filter'])) {
                    $seminars[$seminar_id]['sem_class'] = $sem_types[$seminar['status']]->getClass();
                    $seminars[$seminar_id]['modules'] = $modules->getLocalModules($seminar_id, 'sem', $seminar['modules'], $seminar['status']);
                    $seminars[$seminar_id]['navigation'] = MyRealmModel::getAdditionalNavigations($seminar_id, $seminars[$seminar_id], $seminars[$seminar_id]['sem_class'], $GLOBALS['user']->id);
                }
                //add last activity column:
                if (in_array('last_activity', $params['view_filter'])) {
                    $seminars[$seminar_id]['last_activity'] = lastActivity($seminar_id);
                }
                if ($this->selected_action == 17) {
                    $seminars[$seminar_id]['admission_locked'] = false;
                    if ($seminar['course_set']) {
                        $set = new CourseSet($seminar['course_set']);
                        if (!is_null($set) && $set->hasAdmissionRule('LockedAdmission')) {
                            $seminars[$seminar_id]['admission_locked'] = 'locked';
                        } else {
                            $seminars[$seminar_id]['admission_locked'] = 'disable';
                        }
                        unset($set);
                    }
                }
            }
        }

        return $seminars;
    }

    /**
     * Returns the teacher for a given cours
     *
     * @param String $course_id Id of the course
     * @return array of user infos [user_id, username, Nachname, fullname]
     */
    private function getTeacher($course_id)
    {
        $teachers   = CourseMember::findByCourseAndStatus($course_id, 'dozent');
        $collection = SimpleCollection::createFromArray($teachers);
        return $collection->map(function (CourseMember $teacher) {
            return array(
                'user_id'  => $teacher->user_id,
                'username' => $teacher->username,
                'Nachname' => $teacher->nachname,
                'fullname' => $teacher->getUserFullname('no_title_rev'),
            );
        });
    }


    /**
     * Adds view filter to the sidebar
     * @param array $configs
     */
    private function setViewWidget($configs = array())
    {
        $configs         = $configs ?: array();
        $checkbox_widget = new OptionsWidget();
        $checkbox_widget->setTitle(_('Darstellungs-Filter'));

        foreach ($this->getViewFilters() as $index => $label) {
            $state = in_array($index, $configs);
            $checkbox_widget->addCheckbox($label, $state, $this->url_for('admin/courses/set_view_filter/' . $index . '/' . $state));
        }
        Sidebar::get()->addWidget($checkbox_widget, 'views');
    }

    /**
     * Adds the institutes selector to the sidebar
     */
    private function setInstSelector()
    {
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(
            _('Einrichtung'),
            $this->url_for('admin/courses/set_selection'),
            'institute'
        );
        $list->class = 'institute-list';

        if ($GLOBALS['perm']->have_perm('root') || (count($this->insts) > 1)) {
            $list->addElement(new SelectElement(
                'all',
                $GLOBALS['perm']->have_perm('root') ? _('Alle') : _('Alle meine Einrichtungen'),
                $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === 'all'),
                'select-all'
            );
        }

        foreach ($this->insts as $institut) {
            $list->addElement(
                new SelectElement(
                    $institut['Institut_id'],
                    (!$institut['is_fak'] ? ' ' : '') . $institut['Name'],
                    $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === $institut['Institut_id']
                ),
                'select-' . $institut['Institut_id']
            );

            //check if the institute is a faculty.
            //If true, then add another option to display all courses
            //from that faculty and all its institutes.

            //$institut is an array, we can't use the method isFaculty() here!
            if ($institut['fakultaets_id'] == $institut['Institut_id']) {
                $list->addElement(
                    new SelectElement(
                        $institut['Institut_id'] . '_withinst', //_withinst = with institutes
                        ' ' . $institut['Name'] . ' +' . _('Institute'),
                        $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === $institut['Institut_id'] . '_withinst'
                    ),
                    'select-' . $institut['Name'] . '-with_institutes'
                );
            }
        }

        $sidebar->addWidget($list, 'filter_institute');
    }

    /**
     * Adds the semester selector to the sidebar
     */
    private function setSemesterSelector()
    {
        $semesters = array_reverse(Semester::getAll());
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Semester'), $this->url_for('admin/courses/set_selection'), 'sem_select');
        $list->addElement(new SelectElement('all', _('Alle')), 'sem_select-all');
        foreach ($semesters as $semester) {
            $list->addElement(new SelectElement(
                $semester->id,
                $semester->name,
                $semester->id === $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE
            ), 'sem_select-' . $semester->id);
        }

        $sidebar->addWidget($list, 'filter_semester');
    }


    /**
     * Adds HTML-Selector to the sidebar
     * @param null $selected_action
     * @return string
     */
    private function setActionsWidget($selected_action = null)
    {
        $actions = $this->getActions();
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
        $this->types = array();
        $this->selected = $selected;

        $list = new SelectWidget(
            _('Veranstaltungstyp-Filter'),
            $this->url_for('admin/courses/set_course_type'),
            'course_type'
        );
        $list->addElement(new SelectElement(
            'all', _('Alle'), $selected === 'all'
        ), 'course-type-all');
        foreach ($GLOBALS['SEM_CLASS'] as $class_id => $class) {
            if ($class['studygroup_mode']) {
                continue;
            }

            $element = new SelectElement(
                $class_id,
                $class['name'],
                $selected == $class_id
            );
            $list->addElement(
                $element->setAsHeader(),
                'course-type-' . $class_id
            );

            foreach ($class->getSemTypes() as $id => $result) {
                $element = new SelectElement(
                    $class_id . '_' . $id,
                    $result['name'],
                    $selected === $class_id . '_' . $id
                );
                $list->addElement(
                    $element->setIndentLevel(1),
                    'course-type-' . $class_id . '_' . $id
                );
            }
        }
        $sidebar->addWidget($list, 'filter-course-type');
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
        $teachers = DBManager::get()->fetchAll("
            SELECT auth_user_md5.*, user_info.*
            FROM auth_user_md5
                LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id)
                INNER JOIN user_inst ON (user_inst.user_id = auth_user_md5.user_id)
                INNER JOIN Institute ON (Institute.Institut_id = user_inst.Institut_id)
            WHERE (Institute.Institut_id = :institut_id OR Institute.fakultaets_id = :institut_id)
                AND auth_user_md5.perms = 'dozent'
            ORDER BY auth_user_md5.Nachname ASC, auth_user_md5.Vorname ASC
        ", array(
            'institut_id' => $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT
        ),
        function ($data) {
            $ret['user_id'] = $data['user_id'];
            unset($data['user_id']);
            $ret['fullname'] = User::build($data)->getFullName("full_rev");
            return $ret;
        }
        );


        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Dozenten-Filter'), $this->url_for('admin/courses/index'), 'teacher_filter');
        $list->addElement(new SelectElement('all', _('alle'), Request::get('teacher_filter') == 'all'), 'teacher_filter-all');

        foreach ($teachers as $teacher) {
            $list->addElement(new SelectElement(
                $teacher['user_id'],
                $teacher['fullname'],
                $GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER === $teacher['user_id']
            ), 'teacher_filter-' . $teacher['user_id']);
        }

        $sidebar->addWidget($list, 'filter_teacher');
    }

    /**
     * Adds a search widget to the sidebar
     */
    private function setSearchWiget()
    {
        $sidebar = Sidebar::Get();
        $search = new SearchWidget(URLHelper::getLink('dispatch.php/admin/courses'));
        $search->addNeedle(_('Freie Suche'), 'search', true, null, null, $GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT);
        $sidebar->addWidget($search, 'filter_search');
    }

    /**
     * Returns the filter configuration.
     *
     * @return array containing the filter configuration
     */
    private function getFilterConfig()
    {
        $available_filters = array_keys($this->getViewFilters());

        $temp = $GLOBALS['user']->cfg->MY_COURSES_ADMIN_VIEW_FILTER_ARGS;
        if ($temp) {
            $config = unserialize($temp);
            $config = array_intersect($config, $available_filters);
        } else {
            $config = array();
        }

        if (!$config) {
            $config = $this->setFilterConfig($available_filters);
        }

        return $config;
    }

    /**
     * Sets the filter configuration.
     *
     * @param Array $config Filter configuration
     * @return array containing the filter configuration
     */
    private function setFilterConfig($config)
    {
        $config = $config ?: array_keys($this->getViewFilters());
        $GLOBALS['user']->cfg->store('MY_COURSES_ADMIN_VIEW_FILTER_ARGS', serialize($config));

        return $config;
    }
}
