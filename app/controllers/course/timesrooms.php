<?php

/**
 * @author  David Siegfried <david.siegfried@uni-vechta.de>
 * @license GPL2 or any later version
 * @since   3.4
 */
class Course_TimesroomsController extends AuthenticatedController
{
    protected $utf8decode_xhr = true;

    /**
     * Common actions before any other action
     *
     * @param String $action Action to be executed
     * @param Array $args Arguments passed to the action
     * @throws Trails_Exception when either no course was found or the user
     *                          may not access this area
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Try to find a valid course
        if (!Course::findCurrent()) {
            throw new Trails_Exception(404, _('Es wurde keine Veranstaltung ausgew�hlt!'));
        }

        if (!$GLOBALS['perm']->have_studip_perm('tutor', Course::findCurrent()->id)) {
            throw new AccessDeniedException();
        }

        // Get seminar instance
        $this->course = new Seminar(Course::findCurrent());

        if (Navigation::hasItem('course/admin/dates')) {
            Navigation::activateItem('course/admin/dates');
        }
        $this->locked = false;

        if(LockRules::Check($this->course->id, 'room_time')) {
            $this->locked       = true;
            $this->lock_rules   = LockRules::getObjectRule($this->course->id);
            PageLayout::postInfo(_('Diese Seite ist f�r die Bearbeitung gesperrt. Sie k�nnen die Daten einsehen, jedoch nicht ver�ndern.')
                     . ($this->lock_rules['description'] ? '<br>' . formatLinks($this->lock_rules['description']) : ''));
        }

        $this->show = array(
            'regular'     => true,
            'irregular'   => true,
            'roomRequest' => !$this->locked && Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS,
        );

        PageLayout::setHelpKeyword('Basis.Veranstaltungen');
        PageLayout::addSqueezePackage('raumzeit');

        $title = _('Verwaltung von Zeiten und R�umen');
        $title = $this->course->getFullname() . ' - ' . $title;

        PageLayout::setTitle($title);


        URLHelper::bindLinkParam('semester_filter', $this->semester_filter);

        if (empty($this->semester_filter)) {
            if (!$this->course->hasDatesOutOfDuration() && $this->course->duration_time == 0) {
                $this->semester_filter = $this->course->start_semester->id;
            } else {
                $this->semester_filter = 'all';
            }
        }
        if ($this->semester_filter == 'all') {
            $this->course->applyTimeFilter(0, 0);
        } else {
            $semester = Semester::find($this->semester_filter);
            $this->course->applyTimeFilter($semester['beginn'], $semester['ende']);
        }

        $selectable_semesters   = new SimpleCollection(Semester::getAll());
        $start                  = $this->course->start_time;
        $end                    = $this->course->duration_time == -1 ? PHP_INT_MAX : $this->course->end_time;
        $selectable_semesters   = $selectable_semesters->findBy('beginn', [$start, $end], '>=<=')->toArray();
        if (count($selectable_semesters) > 1 || (count($selectable_semesters) == 1 && $this->course->hasDatesOutOfDuration())) {
            $selectable_semesters[] = ['name' => _('Alle Semester'), 'semester_id' => 'all'];
        }
        $this->selectable_semesters = array_reverse($selectable_semesters);

        if (!Request::isXhr()) {
            $this->setSidebar();
        } elseif (Request::isXhr() && $this->flash['update-times']) {
            $semester_id = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
            if ($semester_id === 'all') {
                $semester_id = '';
            }
            $this->response->add_header('X-Raumzeit-Update-Times', json_encode(studip_utf8encode(array(
                'course_id' => $this->course->id,
                'html'      => $this->course->getDatesHTML(array(
                    'semester_id' => $semester_id,
                    'show_room'   => true,
                )) ?: _('nicht angegeben'),
            ))));
        }
    }

    /**
     * Displays the times and rooms of a course
     *
     * @param mixed $course_id Id of the course (optional, defaults to
     *                         globally selected)
     */
    public function index_action()
    {
        Helpbar::get()->addPlainText(_('Rot'), _('Kein Termin hat eine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Gelb'), _('Mindestens ein Termin hat keine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Gr�n'), _('Alle Termine haben eine Raumbuchung.'));

        if (Request::isXhr()) {
             $this->show = array(
                'regular'     => true,
                'irregular'   => true,
                'roomRequest' => false,
            );
        }
        $this->linkAttributes   = array('fromDialog' => Request::isXhr() ? 1 : 0);
        $this->semester         = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();
        $this->cycle_dates      = array();

        foreach ($this->course->cycles as $cycle) {
            foreach ($cycle->getAllDates() as $val) {
                foreach ($this->semester as $sem) {
                    if (!($this->semester_filter == 'all' || $this->semester_filter == $sem->id)) {
                        continue;
                    }

                    if ($sem->beginn <= $val->date && $sem->ende >= $val->date) {
                        if (!isset($this->cycle_dates[$cycle->metadate_id])) {
                            $this->cycle_dates[$cycle->metadate_id] = array(
                                'cycle'        => $cycle,
                                'dates'        => array(),
                                'room_request' => array(),
                            );
                        }
                        if (!isset($this->cycle_dates[$cycle->metadate_id]['dates'][$sem->id])) {
                            $this->cycle_dates[$cycle->metadate_id]['dates'][$sem->id] = array();
                        }
                        $this->cycle_dates[$cycle->metadate_id]['dates'][$sem->id][] = $val;
                        if ($val->getRoom()) {
                            $this->cycle_dates[$cycle->metadate_id]['room_request'][] = $val->getRoom();
                        }
                    }
                }
            }
        }

        $single_dates = array();

        foreach ($this->course->getDatesWithExdates() as $id => $val) {
            foreach ($this->semester as $sem) {
                if (!($this->semester_filter == 'all' || $this->semester_filter == $sem->id)
                ) {
                    continue;
                }

                if ($sem->beginn > $val->date || $sem->ende < $val->date || isset($val->metadate_id)) {
                    continue;
                }

                if (!isset($single_dates[$sem->id])) {
                    $single_dates[$sem->id] = new SimpleCollection();
                }
                $single_dates[$sem->id]->append($val);
            }
        }

        $this->single_dates = $single_dates;
    }

    /**
     * Edit the start-semester of a course
     * @throws Trails_DoubleRenderError
     */
    public function editSemester_action()
    {
        if (!Request::isXhr()) {
            $this->redirect('course/timesrooms/index');

            return;
        }
        $this->params           = array('origin' => Request::get('origin', 'course_timesrooms'));
        $this->semester         = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();
    }

    /**
     * Primary function to edit date-informations
     * @param      $termin_id
     * @param null $metadate_id
     */
    public function editDate_action($termin_id)
    {
        PageLayout::setTitle(_('Einzeltermin bearbeiten'));
        $this->date       = CourseDate::find($termin_id) ?: CourseExDate::find($termin_id);
        $this->attributes = array();

        if ($request = RoomRequest::findByDate($this->date->id)) {
            $this->params = array('request_id' => $request->getId());
        } else {
            $this->params = array('new_room_request_type' => 'date_' . $this->date->id);
        }

        if (Config::get()->RESOURCES_ENABLE) {
            $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        }

        $this->dozenten = $this->course->getMembers('dozent');
        $this->gruppen  = Statusgruppen::findBySeminar_id($this->course->id);

        $this->related_persons = array();
        foreach (User::findDozentenByTermin_id($this->date->id) as $user) {
            $this->related_persons[] = $user->user_id;
        }

        $this->related_groups = array();
        foreach (Statusgruppen::findByTermin_id($this->date->id) as $group) {
            $this->related_groups[] = $group->statusgruppe_id;
        }
    }


    /**
     * Save date-information
     * @param $termin_id
     * @throws Trails_DoubleRenderError
     */
    public function saveDate_action($termin_id)
    {
        $termin = CourseDate::find($termin_id);

        $date     = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('start_time')));
        $end_time = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('end_time')));

        //time changed for regular date. create normal singledate and cancel the regular date
        if (($termin->metadate_id != '' || isset($termin->metadate_id))
            && ($date != $termin->date || $end_time != $termin->end_time)
        ) {
            $termin_values = $termin->toArray();
            $termin_info   = $termin->getFullname();

            $termin->cancelDate();
            PageLayout::postInfo(sprintf(_('Der Termin %s wurde aus der Liste der regelm��igen Termine'
                                           . ' gel�scht und als unregelm��iger Termin eingetragen, da Sie die Zeiten des Termins ver�ndert haben,'
                                           . ' so dass dieser Termin nun nicht mehr regelm��ig ist.'), $termin_info));

            $termin = new CourseDate();
            unset($termin_values['metadate_id']);
            $termin->setData($termin_values);
            $termin->setId($termin->getNewId());
        }
        $termin->date     = $date;
        $termin->end_time = $end_time;
        $termin->date_typ = Request::get('course_type');

        $related_groups        = Request::get('related_statusgruppen');
        $termin->statusgruppen = array();
        if (!empty($related_groups)) {
            $related_groups        = explode(',', $related_groups);
            $termin->statusgruppen = Statusgruppen::findMany($related_groups);
        }

        $related_users    = Request::get('related_teachers');
        $termin->dozenten = array();
        if (!empty($related_users)) {
            $related_users    = explode(',', $related_users);
            $termin->dozenten = User::findMany($related_users);
        }

        // Set Room
        if (Request::option('room') == 'room') {
            $room_id = Request::option('room_sd', '0');

            if ($room_id != '0' && $room_id != $termin->room_assignment->resource_id) {

                $resObj                  = new ResourceObject($room_id);
                $room                    = new ResourceAssignment();
                $room->assign_user_id    = $termin->termin_id;
                $room->resource_id       = Request::get('room_sd');
                $room->begin             = $termin->date;
                $room->end               = $termin->end_time;
                $room->repeat_end        = $termin->end_time;

                if ($resObj->getMultipleAssign()) {
                    $termin->raum = '';
                    ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                            array(':termin' => $termin->termin_id));
                    $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert und der Raum %s gebucht, etwaige freie Ortsangaben wurden entfernt.'),
                            $termin->getFullname(), $resObj->getName()));
                    $room->store();

                } else {
                    $assignments = ResourceAssignment::findOneBySQL('(:tbegin BETWEEN begin AND end '
                            . 'OR :tend BETWEEN begin AND end '
                            . 'OR begin BETWEEN :tbegin AND :tend '
                            . 'OR end BETWEEN :tbegin AND :tend) '
                            . 'AND resource_id = :resource_id AND assign_user_id != :termin_id',
                            array(':tbegin' => $termin->date, ':tend' => $termin->end_time,
                                  ':resource_id' => $resObj->id, ':termin_id' => $termin->termin_id));

                    if (is_null($assignments)) {
                        $termin->raum = '';
                        ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                            array(':termin' => $termin->termin_id));
                        $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert und der Raum %s gebucht, etwaige freie Ortsangaben wurden entfernt.'),
                        $termin->getFullname(), $resObj->getName()));
                        $room->store();

                    } else {
                        $this->course->createError(sprintf(_('Der Raum %s konnte nicht f�r %s gebucht werden, da er schon belegt ist.'),
                                $resObj->getName(), $termin->getFullname()));
                    }
                }

            } elseif ($room_id == '0') {
                $this->course->createError(sprintf(_('Der angegebene Raum konnte f�r den Termin %s nicht gebucht werden!'), $termin->getFullname()));
            }
        } elseif (Request::option('room') == 'noroom') {
            $termin->raum = '';
            ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                array(':termin' => $termin->termin_id));
            $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert, etwaige freie Ortsangaben und Raumbuchungen wurden entfernt.'), '<b>' . $termin->getFullname() . '</b>'));
        } elseif (Request::option('room') == 'freetext') {
            $termin->raum = Request::get('freeRoomText_sd');
            ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                array(':termin' => $termin->termin_id));
            $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert, etwaige Raumbuchungen wurden entfernt und stattdessen der angegebene Freitext eingetragen!'), '<b>' . $termin->getFullname() . '</b>'));
        }

        if ($termin->store()) {
            NotificationCenter::postNotification('CourseDidChangeSchedule', $this->course);
        }
        $this->displayMessages();
        $this->redirect($this->url_for('course/timesrooms/index', array('contentbox_open' => $termin->metadate_id)));
    }


    /**
     * Create Single Date
     */
    public function createSingleDate_action()
    {
        PageLayout::setTitle(Course::findCurrent()->getFullname() . " - " . _('Einzeltermin anlegen'));
        $this->restoreRequest(words('date start_time end_time room related_teachers related_statusgruppen freeRoomText dateType fromDialog'));

        if (Config::get()->RESOURCES_ENABLE) {
            $this->resList    = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        }
        $this->teachers   = $this->course->getMembers('dozent');
        $this->groups     = Statusgruppen::findBySeminar_id($this->course->id);
    }

    /**
     * Save Single Date
     * @throws Trails_DoubleRenderError
     */
    public function saveSingleDate_action()
    {
        CSRFProtection::verifyRequest();

        $start_time = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('start_time')));
        $end_time   = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('end_time')));

        if ($start_time > $end_time) {
            $this->storeRequest();

            PageLayout::postError(_('Die Zeitangaben sind nicht korrekt. Bitte �berpr�fen Sie diese!'));
            $this->redirect('course/timesrooms/createSingleDate');
            return;
        }

        $termin            = new CourseDate();
        $termin->termin_id = $termin->getNewId();
        $termin->range_id  = $this->course->id;
        $termin->date      = $start_time;
        $termin->end_time  = $end_time;
        $termin->autor_id  = $GLOBALS['user']->id;
        $termin->date_typ  = Request::get('dateType');

        $teachers = $this->course->getMembers('dozent');
        foreach (Request::getArray('related_teachers') as $dozent_id) {
            if (in_array($dozent_id, array_keys($teachers))) {
                $related_persons[] = User::find($dozent_id);
            }
        }
        if (isset($related_persons)) {
            $termin->dozenten = $related_persons;
        }

        foreach (Request::getArray('related_statusgruppen') as $statusgruppe_id) {
            $related_groups[] = Statusgruppen::find($statusgruppe_id);
        }
        if (isset($related_groups)) {
            $termin->statusgruppen = $related_groups;
        }

        if (!Request::get('room') || Request::get('room') === 'nothing') {
            $termin->raum = Request::get('freeRoomText');
            $termin->store();
        } else {
            $termin->store();

            $room                 = new ResourceAssignment();
            $room->assign_user_id = $termin->termin_id;
            $room->resource_id    = Request::get('room');
            $room->begin          = $termin->date;
            $room->end            = $termin->end_time;
            $room->repeat_end     = $termin->end_time;

            if (!$room->store()) {
                $termin->delete();
            }
        }

        if ($start_time < $this->course->filterStart || $end_time > $this->course->filterEnd) {
            $this->course->setFilter('all');
        }

        $this->course->createMessage(sprintf(_('Der Termin %s wurde hinzugef�gt!'), $termin->getFullname()));
        $this->course->store();
        $this->displayMessages();

        $this->redirect('course/timesrooms/index');
    }

    /**
     * Removes a single date
     *
     * @param String $termin_id Id of the date
     * @param String $sub_cmd Sub command to be executed
     */
    public function deleteSingle_action($termin_id, $sub_cmd = 'delete')
    {
        $cycle_id = Request::option('cycle_id');
        if ($cycle_id) {
            $sub_cmd = 'cancel';
        }
        $this->deleteDate($termin_id, $sub_cmd, $cycle_id);
        $this->displayMessages();

        $params = array();
        if ($cycle_id) {
            $params['contentbox_open'] = $cycle_id;
        }
        $this->redirect($this->url_for('course/timesrooms/index', $params));
    }

    /**
     * Restores a previously removed date.
     *
     * @param String $termin_id Id of the previously removed date
     */
    public function undeleteSingle_action($termin_id)
    {
        $ex_termin = CourseExDate::find($termin_id);
        $termin    = $ex_termin->unCancelDate();
        if ($termin) {
            $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'), $termin->getFullname()));
            $this->displayMessages();
        }

        $params = array();
        if ($termin->metadate_id != '') {
            $params['contentbox_open'] = $termin->metadate_id;
        }
        $this->redirect($this->url_for('course/timesrooms/index', $params));
    }

    /**
     * Performs a stack action defined by url parameter method.
     *
     * @param String $cycle_id Id of the cycle the action should be performed
     *                         upon
     */
    public function stack_action($cycle_id = '')
    {
        $_SESSION['_checked_dates'] = Request::getArray('single_dates');
        if (empty($_SESSION['_checked_dates']) && isset($_SESSION['_checked_dates'])) {
            PageLayout::postError(_('Sie haben keine Termine ausgew�hlt!'));
            $this->redirect($this->url_for('course/timesrooms/index', array('contentbox_open' => $cycle_id)));
            return;
        }

        $this->linkAttributes = array('fromDialog' => Request::int('fromDialog') ? 1 : 0);

        switch (Request::get('method')) {
            case 'edit':
                $this->editStack($cycle_id);
                break;
            case 'preparecancel':
                $this->prepareCancel($cycle_id);
                break;
            case 'delete':
                $this->deleteStack($cycle_id);
                break;
            case 'undelete':
                $this->unDeleteStack($cycle_id);
        }
    }

    /**
     * Edits a stack/cycle.
     *
     * @param String $cycle_id Id of the cycle to be edited.
     */
    private function editStack($cycle_id)
    {
        $this->cycle_id   = $cycle_id;
        $this->teachers   = $this->course->getMembers('dozent');
        $this->gruppen    = Statusgruppen::findBySeminar_id($this->course->id);
        $this->resList    = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        $this->render_template('course/timesrooms/editStack');
    }

    /**
     * Prepares a stack/cycle to be canceled.
     *
     * @param String $cycle_id Id of the cycle to be canceled.
     */
    private function prepareCancel($cycle_id)
    {
        $this->cycle_id   = $cycle_id;
        $this->render_template('course/timesrooms/cancelStack');
    }

    /**
     * Deletes a stack/cycle.
     *
     * @param String $cycle_id Id of the cycle to be deleted.
     */
    private function deleteStack($cycle_id = '')
    {
        foreach ($_SESSION['_checked_dates'] as $id) {
            $termin = CourseDate::find($id);
            if ($termin === null) {
                $termin = CourseExDate::find($id);
            }
            if ($termin->metadate_id && $termin instanceof CourseDate) {
                $this->deleteDate($id, 'cancel', $cycle_id);
            } elseif ($termin->metadate_id === null || $termin->metadate_id === '') {
                $this->deleteDate($id, 'delete', $cycle_id);
            } elseif ($termin->metadate_id && $termin instanceof CoursExDate) {
                //$this->deleteDate($id, 'delete', $cycle_id);
            }
        }
        $this->displayMessages();

        unset($_SESSION['_checked_dates']);

        if(Request::int('fromDialog')) {
            $this->redirect($this->url_for('course/timesrooms/index', array('contentbox_open' => $cycle_id)));
        } else {
            $this->relocate('course/timesrooms/index', array('contentbox_open' => $cycle_id));
        }
    }

    /**
     * Restores a previously deleted stack/cycle.
     *
     * @param String $cycle_id Id of the cycle to be restored.
     */
    private function unDeleteStack($cycle_id = '')
    {
        foreach ($_SESSION['_checked_dates'] as $id) {
            $ex_termin = CourseExDate::find($id);
            if ($ex_termin === null) {
                continue;
            }
            $ex_termin->content = '';
            $termin             = $ex_termin->unCancelDate();
            if ($termin !== null) {
                $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'), $termin->getFullname()));
            }
        }
        $this->displayMessages();
        unset($_SESSION['_checked_dates']);

        if(Request::int('fromDialog')) {
            $this->redirect($this->url_for('course/timesrooms/index', array('contentbox_open' => $cycle_id)));
        } else {
            $this->relocate('course/timesrooms/index', array('contentbox_open' => $cycle_id));
        }
    }

    /**
     * Saves a stack/cycle.
     *
     * @param String $cycle_id Id of the cycle to be saved.
     */
    public function saveStack_action($cycle_id = '')
    {
        switch (Request::get('method')) {
            case 'edit':
                $this->saveEditedStack($cycle_id);
                break;
            case 'preparecancel':
                $this->saveCanceledStack($cycle_id);
                break;
        }

        $this->displayMessages();

        unset($_SESSION['_checked_dates']);

        if(Request::int('fromDialog')) {
            $this->redirect($this->url_for('course/timesrooms/index', array('contentbox_open' => $cycle_id)));
        } else {
            $this->relocate('course/timesrooms/index', array('contentbox_open' => $cycle_id));
        }
    }

    /**
     * Saves a canceled stack/cycle.
     *
     * @param String $cycle_id Id of the canceled cycle to be saved.
     */
    private function saveCanceledStack($cycle_id = '')
    {
        $msg           = _('Folgende Termine wurden gel�scht') . '<ul>';
        $deleted_dates = array();

        foreach ($_SESSION['_checked_dates'] as $val) {
            $termin = CourseDate::find($val);
            if ($termin === null) {
                continue;
            }
            $termin->content = trim(Request::get('cancel_comment', ''));
            $new_ex_termin   = $termin->cancelDate();
            if ($new_ex_termin !== null) {
                $msg .= sprintf('<li>%s</li>', $new_ex_termin->getFullname());
            }
        }
        $msg .= '</ul>';
        $this->course->createMessage($msg);

        if (Request::int('cancel_send_message') && count($deleted_dates) > 0) {
            $snd_messages = raumzeit_send_cancel_message(Request::get('cancel_comment'), $deleted_dates);
            if ($snd_messages) {
                $this->course->createMessage(sprintf(_('Es wurden %u Benachrichtigungen gesendet.'), $snd_messages));
            }
        }
    }

    /**
     * Saves an edited stack/cycle.
     *
     * @param String $cycle_id Id of the edited cycle to be saved.
     */
    private function saveEditedStack($cycle_id = '')
    {
        $persons      = Request::getArray('related_persons');
        $action       = Request::get('related_persons_action');
        $groups       = Request::getArray('related_groups');
        $group_action = Request::get('related_groups_action');

        $teacher_changed = false;
        $groups_changed  = false;

        foreach ($_SESSION['_checked_dates'] as $singledate_id) {
            $singledate = CourseDate::find($singledate_id);
            if (!isset($singledate)) {
                $singledate = CourseExDate::find($singledate_id);
            }
            $singledates[] = $singledate;
        }

        // Update related persons
        if (in_array($action, words('add delete'))) {
            foreach ($singledates as $key => $singledate) {
                $dozenten     = User::findDozentenByTermin_id($singledate->termin_id);
                $dozenten_new = $dozenten;
                if ($singledate->range_id === $this->course->id) {
                    foreach ($persons as $user_id) {
                        $is_in_list = false;
                        foreach ($dozenten as $user_key => $user) {
                            if ($user->user_id == $user_id) {
                                $is_in_list = $user_key;
                            }
                        }

                        if ($is_in_list === false && $action === 'add') {
                            $dozenten_new[]  = User::find($user_id);
                            $teacher_changed = true;
                        } else if ($is_in_list !== false && $action === 'delete') {
                            unset($dozenten_new[$is_in_list]);
                            $teacher_changed = true;
                        }
                    }
                }
                $singledates[$key]->dozenten = $dozenten_new;
            }
        }

        if ($teacher_changed) {
            $this->course->createMessage(_('Zust�ndige Personen f�r die Termine wurden ge�ndert.'));
        }

        if (in_array($group_action, words('add delete'))) {
            foreach ($singledates as $key => $singledate) {
                $groups_db  = Statusgruppen::findByTermin_id($singledate->termin_id);
                $groups_new = $groups_db;
                if ($singledate->range_id === $this->course->id) {
                    foreach ($groups as $statusgruppe_id) {
                        $is_in_list = false;
                        foreach ($groups_db as $group_key => $group) {
                            if ($statusgruppe_id == $group->statusgruppe_id) {
                                $is_in_list = $group_key;
                            }
                        }

                        if ($is_in_list === false && $group_action === 'add') {
                            $groups_new[]   = Statusgruppen::find($statusgruppe_id);
                            $groups_changed = true;
                        } elseif ($is_in_list !== false && $group_action === 'delete') {
                            unset($groups_new[$is_in_list]);
                            $groups_changed = true;
                        }
                    }
                }
                $singledates[$key]->statusgruppen = $groups_new;
            }
        }

        if ($groups_changed) {
            $this->course->createMessage(_('Zugewiesene Gruppen f�r die Termine wurden ge�ndert.'));
        }


        $room_assignments = array();
        foreach ($singledates as $key => $singledate) {
            if (Request::option('action') == 'room' && Request::get('room') != 0) {

                //die('dasd'. $room_id);
                $resObj                             = new ResourceObject(Request::get('room'));
                $room                               = new ResourceAssignment();
                $room->assign_user_id               = $singledate->termin_id;
                $room->resource_id                  = $resObj->id;
                $room->begin                        = $singledate->date;
                $room->end                          = $singledate->end_time;
                $room->repeat_end                   = $singledate->end_time;

                if ($resObj->getMultipleAssign()) {
                    $room_assignments[$singledate->termin_id] = $room;
                } else {
                    $assignments = ResourceAssignment::findOneBySQL('(:tbegin BETWEEN begin AND end '
                            . 'OR :tend BETWEEN begin AND end '
                            . 'OR begin BETWEEN :tbegin AND :tend '
                            . 'OR end BETWEEN :tbegin AND :tend) '
                            . 'AND resource_id = :resource_id AND assign_user_id != :termin_id',
                            array(':tbegin' => $singledate->date, ':tend' => $singledate->end_time,
                                  ':resource_id' => $resObj->id, ':termin_id' => $singledate->termin_id));
                    if (is_null($assignments)) {
                        $room_assignments[$singledate->termin_id] = $room;
                    } else {
                        $error[] = sprintf(_('Raum %s f�r %s'), $resObj->getName(), $singledate->getFullname());
                    }
                }

            } elseif (Request::option('action') == 'freetext') {
                ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                    array(':termin' => $singledate->termin_id));
                $singledates[$key]->raum = Request::get('freeRoomText');
                $this->course->createMessage(sprintf(_('Der Termin %s wurde ge�ndert, etwaige '
                                                       . 'Raumbuchungen wurden entfernt und stattdessen der angegebene Freitext'
                                                       . ' eingetragen!'),
                    '<strong>' . $singledate->getFullname() . '</strong>'));


            } elseif (Request::option('action') == 'noroom') {
                ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                    array(':termin' => $singledate->termin_id));
                $singledates[$key]->raum = '';
            }
        }

        if (!empty($room_assignments)
                && count($room_assignments) >=
                round(count($singledates) * Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE / 100))
            {
            foreach ($singledates as $singledate) {
                if (array_key_exists($singledate->termin_id, $room_assignments)) {
                    $singledate->raum = '';
                    ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                            array(':termin' => $singledate->termin_id));

                    $room_assignments[$singledate->termin_id]->store();
                }
                $singledate->store();
            }
        } else {
            foreach ($singledates as $singledate) {
                $singledate->store();
            }
            if (!empty($room_assignments)
                && count($room_assignments) <
                round(count($singledates) * Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE / 100))
            {
                PageLayout::postError(sprintf(_('Der gew�hlte Raum ist bei mehr als %s %s der gew�hlten Termine bereits belegt und wird daher f�r f�r alle Termine nicht �bernommen '),
                        Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE,'Prozent'));
            }
        }

        if(isset($error)) {
            PageLayout::postError(_('Der gew�hlte Raum ist belegt und wurde f�r folgende Termine nicht gebucht: '),$error);
        }

    }

    /**
     * Creates a cycle.
     *
     * @param String $cycle_id Id of the cycle to be created (optional)
     */
    public function createCycle_action($cycle_id = null)
    {
        PageLayout::setTitle(Course::findCurrent()->getFullname() . " - " . _('Regelm��ige Termine anlegen'));
        $this->restoreRequest(words('day start_time end_time description cycle startWeek teacher_sws fromDialog'));

        $this->cycle = new SeminarCycleDate($cycle_id);

        if ($this->cycle->isNew()) {
            $this->has_bookings = false;
        } else {
            $ids = $this->cycle->dates->pluck('termin_id');

            $count = ResourceAssignment::countBySQL('assign_user_id IN (?)', array($ids ?: ''));
            $this->has_bookings = $count > 0;
        }


        $duration = $this->course->duration_time;
        if($duration == -1 ) {
            $end_semester = Semester::findBySQL('beginn >= :beginn ORDER BY beginn',
                    array(':beginn' => $this->course->start_semester->beginn));
        } else if ($duration > 0) {
            $end_semester = Semester::findBySQL('beginn >= :beginn AND ende <= :ende ORDER BY beginn',
                    array(':beginn' => $this->course->start_semester->beginn,
                          ':ende' => $this->course->getEndSemester() + $duration));
        } else {
            $end_semester[] = $this->course->start_semester;
        }

        $this->start_weeks = $this->course->start_semester->getStartWeeks($duration);

        if (!empty($end_semester)) {
            $this->end_semester_weeks = array();

            foreach ($end_semester as $sem) {

                $sem_duration =  $sem->ende - $sem->beginn;
                $weeks = $sem->getStartWeeks($sem_duration);

                foreach($this->start_weeks as $key => $week) {
                    if(strpos($week, substr($weeks[0], -15)) !== false) {
                        $this->end_semester_weeks['start'][] = array('value' => $key, 'label' => sprintf(_('Anfang %s'), $sem->name));
                    }
                    if(strpos($week, substr($weeks[count($weeks)-1], -15)) !== false) {
                        $this->end_semester_weeks['ende'][] = array('value' => $key+1, 'label' => sprintf(_('Ende %s'), $sem->name));
                    }
                    foreach ($weeks as $val) {
                        if(strpos($week, substr($val, -15)) !== false) {
                            $this->clean_weeks[$sem->name][$key] = $val;
                        }
                    }
                }
            }
        }
    }

    /**
     * Saves a cycle
     */
    public function saveCycle_action()
    {
        CSRFProtection::verifyRequest();

        $start = strtotime(Request::get('start_time'));
        $end   = strtotime(Request::get('end_time'));


        if (date('H', $start) > date('H', $end)) {
            $this->storeRequest();
            PageLayout::postError(_('Die Zeitangaben sind nicht korrekt. Bitte �berpr�fen Sie diese!'));
            $this->redirect('course/timesrooms/createCycle');
            return;
        } elseif (Request::int('startWeek') > Request::int('endWeek') ) {
            $this->storeRequest();
            PageLayout::postError(_('Die Endwoche liegt vor der Startwoche. Bitte �berpr�fen Sie diese Angabe!'));
            $this->redirect('course/timesrooms/createCycle');
            return;
        }

        $cycle              = new SeminarCycleDate();
        $cycle->seminar_id  = $this->course->id;
        $cycle->weekday     = Request::int('day');
        $cycle->description = Request::get('description');
        $cycle->sws         = round(Request::float('teacher_sws'), 1);
        $cycle->cycle       = Request::int('cycle');
        $cycle->week_offset = Request::int('startWeek');
        $cycle->end_offset  = Request::int('endWeek') ?: null;
        $cycle->start_time  = date('H:i:00', $start);
        $cycle->end_time    = date('H:i:00', $end);

        if ($cycle->store()) {
            $cycle_info = $cycle->toString();
            NotificationCenter::postNotification('CourseDidChangeSchedule', $this);

            $this->course->createMessage(sprintf(_('Die regelm��ige Veranstaltungszeit %s wurde hinzugef�gt!'), $cycle_info));
            $this->displayMessages();
            $this->redirect('course/timesrooms/index');
        } else {
            $this->storeRequest();
            $this->course->createError(_('Die regelm��ige Veranstaltungszeit konnte nicht hinzugef�gt werden! Bitte �berpr�fen Sie Ihre Eingabe.'));
            $this->displayMessages();
            $this->redirect('course/timesrooms/createCycle');
        }
    }

    /**
     * Edits a cycle
     *
     * @param String $cycle_id Id of the cycle to be edited
     */
    public function editCycle_action($cycle_id)
    {
        $cycle = SeminarCycleDate::find($cycle_id);

        // Prepare Request for saving Request
        $cycle->start_time  = date('H:i:00', strtotime(Request::get('start_time')));
        $cycle->end_time    = date('H:i:00', strtotime(Request::get('end_time')));
        $cycle->weekday     = Request::int('day');
        $cycle->description = Request::get('description');
        $cycle->sws         = Request::get('teacher_sws');
        $cycle->cycle       = Request::get('cycle');
        $cycle->week_offset = Request::get('startWeek');
        $cycle->end_offset  = Request::int('endWeek') ?: null;

        if ($cycle->isDirty()) {
            $cycle->chdate = time();
            $cycle->store();
        } else {
            PageLayout::postInfo(_('Es wurden keine �nderungen vorgenommen'));
        }
        $this->redirect('course/timesrooms/index');
    }

    /**
     * Deletes a cycle
     *
     * @param String $cycle_id Id of the cycle to be deleted
     */
    public function deleteCycle_action($cycle_id)
    {
        CSRFProtection::verifyRequest();
        $cycle = SeminarCycleDate::find($cycle_id);
        if ($cycle === null) {
            $message = sprintf(_('Es gibt keinen regelm��igen Eintrag "%s".'), $cycle_id);
            PageLayout::postError($message);
        } else {
            $cycle_string = $cycle->toString();
            if ($cycle->delete()) {
                $message = sprintf(_('Der regelm��ige Eintrag "%s" wurde gel�scht.'),
                                   '<strong>' . $cycle_string . '</strong>');
                PageLayout::postSuccess($message);
            }
        }

        $this->redirect('course/timesrooms/index');
    }

    /**
     * Add information to canceled / holiday date
     *
     * @param String $termin_id Id of the date
     */
    public function cancel_action($termin_id)
    {
        PageLayout::setTitle(_('Kommentar hinzuf�gen'));
        $this->termin = CourseDate::find($termin_id) ?: CourseExDate::find($termin_id);
    }

    /**
     * Saves a comment for a given date.
     *
     * @param String $termin_id Id of the date
     */
    public function saveComment_action($termin_id)
    {
        $termin = CourseExDate::find($termin_id);

        if(is_null($termin)) {
            $termin = CourseDate::find($termin_id);
        }
        if (Request::get('cancel_comment') != $termin->content) {
            $termin->content = Request::get('cancel_comment');
            if ($termin->store()) {
                $this->course->createMessage(sprintf(_('Der Kommtentar des gel�schten Termins %s wurde ge�ndert.'), $termin->getFullname()));
            } else {
                $this->course->createInfo(sprintf(_('Der gel�schte Termin %s wurde nicht ver�ndert.'), $termin->getFullname()));
            }
        } else {
            $this->course->createInfo(sprintf(_('Der gel�schte Termin %s wurde nicht ver�ndert.'), $termin->getFullname()));
        }
        if (Request::int('cancel_send_message')) {
            $snd_messages = raumzeit_send_cancel_message(Request::get('cancel_comment'), $termin);
            if ($snd_messages) {
                $this->course->createInfo(sprintf(_('Es wurden %s Benachrichtigungen gesendet.'), $snd_messages));
            }
        }
        $this->displayMessages();
        $this->redirect($this->url_for('course/timesrooms/index', array('contentbox_open' => $termin->metadate_id)));
    }

    /**
     * Creates the sidebar
     */
    private function setSidebar()
    {
        if(!$this->locked) {
            $actions = new ActionsWidget();
            $actions->addLink(_('Startsemester �ndern'), $this->url_for('course/timesrooms/editSemester'), Icon::create('date', 'clickable'))->asDialog('size=400');
            Sidebar::Get()->addWidget($actions);
        }

        $widget = new SelectWidget(_('Semesterfilter'), $this->url_for('course/timesrooms/index'), 'semester_filter');
        foreach ($this->selectable_semesters as $item) {
            $element = new SelectElement($item['semester_id'],
                $item['name'],
                $item['semester_id'] == $this->semester_filter);
            $widget->addElement($element);
        }
        Sidebar::Get()->addWidget($widget);

        if ($GLOBALS['perm']->have_perm('admin')) {
            $list = new SelectorWidget();
            $list->setUrl($this->url_for('/index'));
            $list->setSelectParameterName('cid');
            foreach (AdminCourseFilter::get()->getCourses(false) as $seminar) {
                $element = new SelectElement($seminar['Seminar_id'],
                    $seminar['Name']);
                $list->addElement($element, 'select-' . $seminar['Seminar_id']);
            }
            $list->setSelection($this->course->id);
            Sidebar::Get()->addWidget($list);
        }
    }

    /**
     * Sets the start semester for the given course.
     *
     * @param String $course_id Id of the course
     */
    public function setSemester_action($course_id)
    {
        $current_semester = Semester::findCurrent();
        $start_semester   = Semester::find(Request::get('startSemester'));
        if (Request::int('endSemester') != -1) {
            $end_semester = Semester::find(Request::get('endSemester'));
        } else {
            $end_semester = -1;
        }

        $course = Seminar::GetInstance($course_id);
        $old_start_weeks = $this->course->start_semester->getStartWeeks($this->course->duration_time);

        if ($start_semester == $end_semester) {
            $end_semester = 0;
        }

        if ($end_semester != 0 && $end_semester != -1 && $start_semester->beginn >= $end_semester->beginn) {
            PageLayout::postError(_('Das Startsemester liegt nach dem Endsemester!'));
        } else {

            $course->setStartSemester($start_semester->beginn);
            if ($end_semester != -1) {
                $course->setEndSemester($end_semester->beginn);
            } else {
                $course->setEndSemester($end_semester);
            }

            // If the new duration includes the current semester, we set the semester-chooser to the current semester
            if ($current_semester->beginn >= $course->getStartSemester() && $current_semester->beginn <= $course->getEndSemesterVorlesEnde()) {
                $course->setFilter($current_semester->beginn);
            } else {
                // otherwise we set it to the first semester
                $course->setFilter($course->getStartSemester());
            }
        }

        $course->store();

        $new_start_weeks = $this->course->start_semester->getStartWeeks($this->course->duration_time);
        SeminarCycleDate::removeOutRangedSingleDates($course->getStartSemester(), $course->getEndSemesterVorlesEnde(), $course->id);
        $cycles = SeminarCycleDate::findBySeminar_id($course->seminar_id);
        foreach ($cycles as $cycle) {
            $cycle->end_offset = $this->getNewEndOffset($cycle, $old_start_weeks, $new_start_weeks);
            $cycle->store();
        }

        $messages = $course->getStackedMessages();
        foreach ($messages as $type => $msg) {
            PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
        }

        if (Request::submitted('save_close')) {
            $this->relocate(str_replace('_', '/', Request::get('origin')), array('cid' => $course_id));
        } else {
            $this->redirect($this->url_for('course/timesrooms/index', array('cid' => $course_id)));
        }
    }

    /**
     * Calculates new end_offset value for given SeminarCycleDate Object
     *
     * @param object of SeminarCycleDate
     * @param array
     * @param array
     * @return int
     */

    public function getNewEndOffset($cycle, $old_start_weeks, $new_start_weeks)
    {
        if(is_null($cycle->end_offset)){
            return count($new_start_weeks);
        }
        $old_offset_string = $old_start_weeks[$cycle->end_offset];
        $new_offset_value = 0;

        foreach($new_start_weeks as $value => $label) {
            if(strpos($label, substr($old_offset_string, -15)) !== false) {
                $new_offset_value = $value;
            }
        }
        if($new_offset_value == 0) {
            return count($new_start_weeks);
        }

        return $new_offset_value;
    }

    /**
     * Displays messages.
     *
     * @param Array $messages Messages to display (optional, defaults to
     *                        potential stored messages on course object)
     */
    private function displayMessages(array $messages = array())
    {
        $messages = $messages ?: $this->course->getStackedMessages();
        foreach ((array)$messages as $type => $msg) {
            PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
        }
    }

    /**
     * Deletes a date.
     *
     * @param String $termin_id Id of the date
     * @param String $sub_cmd Sub command to be executed
     * @param String $cycle_id Id of the associated cycle
     */
    private function deleteDate($termin_id, $sub_cmd, $cycle_id)
    {
        //cancel cycledate entry
        if ($sub_cmd === 'cancel') {
            $termin     = CourseDate::find($termin_id);
            $seminar_id = $termin->range_id;
            $termin->cancelDate();
            StudipLog::log('SEM_DELETE_SINGLEDATE', $termin_id, $seminar_id, 'Cycle_id: ' . $cycle_id);
        } else if ($sub_cmd === 'delete') {
            $termin      = CourseDate::find($termin_id) ?: CourseExDate::find($termin_id);
            $seminar_id  = $termin->range_id;
            $termin_room = $termin->getRoom();
            $termin_date = $termin->getFullname();
            $has_topics  = $termin->topics->count();
            if ($termin->delete()) {
                StudipLog::log("SEM_DELETE_SINGLEDATE", $termin_id, $seminar_id, 'appointment cancelled');
                if (Request::get('approveDelete')) {
                    if ($has_topics) {
                        $this->course->createMessage(sprintf(_('Sie haben den Termin %s gel�scht, dem ein Thema zugeordnet war.'
                                                               . 'Sie k�nnen das Thema im Ablaufplan einem anderen Termin (z.B. einem Ausweichtermin) zuordnen.'),
                            $termin_date, '<a href="' . URLHelper::getLink('dispatch.php/course/topics') . '">', '</a>'));
                    } elseif ($termin_room) {
                        $this->course->createMessage(sprintf(_('Der Termin %s wurde gel�scht! Die Buchung f�r den Raum %s wurde gel�scht.'),
                            $termin_date, $termin_room));
                    } else {
                        $this->course->createMessage(sprintf(_('Der Termin %s wurde gel�scht!'), $termin_date));
                    }
                } else {
                    // no approval needed, delete unquestioned
                    $this->course->createMessage(sprintf(_('Der Termin %s wurde gel�scht!'), $termin_date));
                }
            }
        }
    }


    /**
     * Redirects to another location.
     *
     * @param String $to New location
     */
    public function redirect($to)
    {
        $arguments = func_get_args();

        if (Request::isXhr()) {
            $url        = call_user_func_array('parent::url_for', $arguments);
            $url_chunk  = Trails_Inflector::underscore(substr(get_class($this), 0, -10));
            $index_url  = $url_chunk . '/index';

            if (strpos($url, $index_url) !== false) {
                $this->flash['update-times'] = $this->course->id;
            }
        }

        return call_user_func_array('parent::redirect', $arguments);
    }

    /**
     * Stores a request into trails' flash object
     */
    private function storeRequest()
    {
        $this->flash['request'] = Request::getInstance();
    }

    /**
     * Restores a previously stored request from trails' flash object
     */
    private function restoreRequest(array $fields)
    {
        $request = $this->flash['request'];

        if ($request) {
            foreach ($fields as $field) {
                Request::set($field, $request[$field]);
            }
        }
    }
}
