<?php

/**
 * timesrooms.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       3.4
 */

class Course_TimesroomsController extends AuthenticatedController
{
    protected $utf8decode_xhr = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Request::get('cid')) {
            $this->course = Seminar::GetInstance(Request::get('cid'));
        }

        if(!$this->course) {
            throw new Trails_Exception(404, _('Es wurd keine Veranstaltung ausgewählt!'));
        }

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            throw new Trails_Exception(400);
        }

        if (Navigation::hasItem('course/admin/dates')) {
            Navigation::activateItem('course/admin/dates');
        }
        $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => false);

        PageLayout::setHelpKeyword('Basis.Veranstaltungen');
        PageLayout::addSqueezePackage('raumzeit');

        $title = _('Verwaltung von Zeiten und Räumen');
        $title = $this->course->getFullname() . ' - ' . $title;

        PageLayout::setTitle($title);

        $_SESSION['raumzeitFilter'] = Request::quoted('newFilter');

        // bind linkParams for chosen semester and opened dates
        URLHelper::bindLinkParam('raumzeitFilter', $_SESSION['raumzeitFilter']);

        $this->checkFilter();

        $this->selection = raumzeit_get_semesters($this->course, new SemesterData(), $_SESSION['raumzeitFilter']);

        if (!Request::isXhr()) {
            $this->setSidebar();
        } elseif (Request::isXhr() && $this->flash['update-times']) {
            $semester_id = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
            if ($semester_id === 'all') {
                $semester_id = '';
            }
            $this->response->add_header('X-Raumzeit-Update-Times', json_encode(studip_utf8encode(array(
                'course_id' => $this->course->id,
                'html'      => Seminar::GetInstance($this->course->id)->getDatesHTML(array(
                    'semester_id' => $semester_id,
                    'show_room'   => true
                )) ?: _('nicht angegeben'),
            ))));
        }
    }

    public function index_action($course_id = null)
    {
        Helpbar::get()->addPlainText(_('Rot'), _('Kein Termin hat eine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Gelb'), _('Mindestens ein Termin hat keine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Grün'), _('Alle Termine haben eine Raumbuchung.'));

        $editParams = array(
            'fromDialog' => Request::isXhr() ? 'true' : 'false',
        );

        $linkAttributes = array();

        $semesterFormParams = array(
            'formaction' => $this->url_for('course/timesrooms/setSemester/' . $this->course->id)
        );

        if (Request::isXhr()) {
            $this->show = array('regular' => true, 'irregular' => true, 'roomRequest' => false);
            $semesterFormParams['data-dialog'] = 'size=big';
            $editParams['asDialog'] = true;
            $linkAttributes['data-dialog'] = 'size=big';
        }

        if ($course_id) {
            $this->course_id = $course_id;
            $this->course = Course::find($course_id);
        }

        $this->semester = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();

        // Get Cycles
        $this->cycle_dates = array();
        foreach ($this->course->cycles as $cycle) {
            foreach ($cycle->getAllDates() as $val) {
                foreach ($this->semester as $sem) {
                    if ($_SESSION['raumzeitFilter'] === $sem->id) {
                        continue;
                    }
                    if ($sem->beginn <= $val->date && $sem->ende >= $val->date) {
                        $this->cycle_dates[$cycle->metadate_id]['cycle'] = $cycle;
                        $this->cycle_dates[$cycle->metadate_id]['dates'][$sem->id][] = $val;
                    }
                }
            }
        }


        // Get Single Dates
        $this->single_dates = array();
        $_single_dates = $this->course->getDatesWithExdates();

        foreach ($_single_dates as $id => $val) {
            foreach ($this->semester as $sem) {
                if ($_SESSION['raumzeitFilter'] != 'all' && $_SESSION['raumzeitFilter'] == $sem->id) {
                    continue;
                }

                if (($sem->beginn <= $val->date) && ($sem->ende >= $val->date) && !isset($val->metadate_id)) {
                    $this->single_dates[$sem->id][] = $val;
                }
            }
        }
        $this->semesterFormParams = $semesterFormParams;
        $this->editParams = $editParams;
        $this->linkAttributes = $linkAttributes;
    }

    /**
     * Edit the start-semester of a course
     * @param null $course_id
     * @throws Trails_DoubleRenderError
     */
    public function editSemester_action($course_id = null)
    {
        if (!Request::isXhr()) {
            $this->redirect('course/timesrooms/index');

            return;
        }

        if ($course_id) {
            $this->course_id = $course_id;
            $this->course = Course::find($course_id);
        }

        $this->semester = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();
    }

    /**
     * Primary function to edit date-informations
     * @param      $termin_id
     * @param null $metadate_id
     */
    public function editDate_action($termin_id)
    {
        $this->date = CourseDate::find($termin_id);
        $this->attributes = array();
        if(empty($this->date)){
            $this->date = CourseExDate::find($termin_id);
        }

        if ($request = RoomRequest::findByDate($this->date->id)) {
            $this->params = array('request_id' => $request->getId());
        } else {
            $this->params = array('new_room_request_type' => 'date_' . $this->date->id);
        }

        $this->params['fromDialog'] = Request::get('fromDialog');

        if(Request::get('fromDialog') == "true") {
            $this->attributes['data-dialog'] = 'size=big';
        } else {
            $this->attributes['fromDialog'] = 'false';
        }

        $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        //UMSTELLEN AUF COURSE
        $this->dozenten = $this->course->getMembers('dozent');
        $this->gruppen = Statusgruppen::findBySeminar_id($this->course->id);
        
        $this->related_persons = array();
        foreach(User::findDozentenByTermin_id($this->date->id) as $user){
            $this->related_persons[] = $user->user_id;
        }
        
        $this->related_groups = array();
        foreach(Statusgruppen::findByTermin_id($this->date->id) as $group){
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
        $termin->date = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('start_time')));
        $termin->end_time = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('end_time')));
        $termin->date_typ = Request::get('course_type');
        
        $related_groups = Request::get('related_statusgruppen');
        $termin->statusgruppen = array();
        if (!empty($related_groups)) {
            $related_groups = explode(',', $related_groups);
            foreach ($related_groups as $group_id) {
                $termin->statusgruppen[] = Statusgruppen::find($group_id);
            }
        }

        $related_users = Request::get('related_teachers');
        $termin->dozenten = array();
        if (!empty($related_users)) {
            $related_users = explode(',', $related_users);
            foreach ($related_users as $user_id) {
                $termin->dozenten[] = User::find($user_id);
            }
        }

        // Set Room
        if (Request::option('room') == 'room') {
            $room_id = Request::option('room_sd', '0');
            
            if ($room_id != '0' && $room_id != $termin->room_assignment->resource_id) {
                ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                        array(':termin' => $termin->termin_id));
                $resObj = new ResourceObject($room_id);
                $termin->raum = '';
                $room = new ResourceAssignment();
                $room->assign_user_id = $termin->termin_id;
                $room->resource_id = Request::get('room_sd');
                $room->begin = $termin->date;
                $room->end = $termin->end_time;
                $room->repeat_end = $termin->end_time;
                $termin->room_assignment = $room;
                $this->course->createMessage(sprintf(_('Der Termin %s wurde geändert und der Raum %s gebucht, etwaige freie Ortsangaben wurden entfernt.'),
                    $termin->getFullname(), $resObj->getName()));
                
            } elseif ($room_id == '0') {
                $this->course->createError(sprintf(_('Der angegebene Raum konnte für den Termin %s nicht gebucht werden!'), $termin->getFullname()));
            }
        } elseif (Request::option('room') == 'noroom') {
            $termin->raum = '';
            ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                    array(':termin' => $termin->termin_id));
            $this->course->createMessage(sprintf(_('Der Termin %s wurde geändert, etwaige freie Ortsangaben und Raumbuchungen wurden entfernt.'), '<b>' . $termin->getFullname() . '</b>'));
        } elseif (Request::option('room') == 'freetext') {
            $termin->raum = Request::quoted('freeRoomText_sd');
            ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                    array(':termin' => $termin->termin_id));
            $this->course->createMessage(sprintf(_('Der Termin %s wurde geändert, etwaige Raumbuchungen wurden entfernt und stattdessen der angegebene Freitext eingetragen!'), '<b>' . $termin->getFullname() . '</b>'));
        }
        
        if ($termin->store()) {
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this->course);
            $this->displayMessages();
        }
        $this->redirect($this->url_for('course/timesrooms/index#' . $termin->metadate_id,
            array('contentbox_open' => $termin->metadate_id)));
    }


    /**
     * Create Single Date
     */
    public function createSingleDate_action()
    {
        if ($this->flash['request']) {
            foreach (words('date start_time end_time room related_teachers related_statusgruppen freeRoomText dateType fromDialog') as $value) {
                Request::set($value, $this->flash['request'][$value]);
            }
        }
        $this->editParams = array('fromDialog' => Request::get('fromDialog'));
        $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        $this->teachers = $this->course->getMembers('dozent');
        $this->groups = Statusgruppen::findBySeminar_id($this->course_id);
    }

    /**
     * Save Single Date
     * @throws Trails_DoubleRenderError
     */
    public function saveSingleDate_action()
    {
        CSRFProtection::verifyRequest();
        $start_time = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('start_time')));
        $end_time = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('end_time')));

        if ($start_time > $end_time) {
            $this->flash['request'] = Request::getInstance();
            PageLayout::postMessage(MessageBox::error(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!')));
            $this->redirect('course/timesrooms/createSingleDate');
            return;
        }
        $termin = new CourseDate();
        $termin->termin_id = $termin->getNewId();
        $termin->range_id = $this->course->id;
        $termin->date = $start_time;
        $termin->end_time = $end_time;
        $termin->autor_id = $GLOBALS['user']->id;
        $termin->date_typ = Request::get('dateType');
        
        $teachers = $this->course->getMembers('dozent');
        foreach (Request::getArray('related_teachers') as $dozent_id) {
            if (in_array($dozent_id, array_keys($teachers))) {
               $related_persons[] = User::find($dozent_id);
            }
        }
        if(isset($related_persons)){
            $termin->dozenten = $related_persons;
        }
        
        foreach (Request::getArray('related_statusgruppen') as $statusgruppe_id) {
            $related_groups[] = Statusgruppen::find($statusgruppe_id);
        }
        if(isset($related_groups)){
            $termin->statusgruppen = $related_groups;
        }
        
        if (!Request::get('room') || Request::get('room') === 'nothing') {
            $termin->raum = Request::get('freeRoomText');
            $termin->store();
        } else {
            $termin->store();
            $room = new ResourceAssignment();
            $room->assign_user_id = $termin->termin_id;
            $room->resource_id = Request::get('room');
            $room->begin = $termin->date;
            $room->end = $termin->end_time;
            $room->repeat_end = $termin->end_time;
            if(!$room->store()){
                $termin->delete();
            }
        }
        
        if ($start_time < $this->course->filterStart || $end_time > $this->course->filterEnd) {
            $this->course->setFilter('all');
        }
        
        $this->course->createMessage(sprintf(_('Der Termin %s wurde hinzugefügt!'), $termin->getFullname()));
        $this->course->store();
        $this->displayMessages();

        if (Request::get('fromDialog') == 'true') {
            $this->redirect('course/timesrooms/index');
        } else {
            $this->relocate('course/timesrooms/index');
        }
    }


    public function deleteSingle_action($termin_id, $sub_cmd = 'delete')
    {
        $cycle_id = Request::option('cycle_id');
        $sub_cmd = isset($cycle_id) ? 'cancel' : $sub_cmd;
        $this->deleteDate($termin_id, $sub_cmd, Request::option('cycle_id'));
        $this->displayMessages();
        $params = array();
        if (Request::option('cycle_id')) {
            $params['contentbox_open'] = Request::get('cycle_id');
        }
        $this->redirect($this->url_for('course/timesrooms/index' . (Request::option('cycle_id') ? '#' . Request::option('cycle_id') : ''), $params));
    }


    public function undeleteSingle_action($termin_id)
    {
        $ex_termin = CourseExDate::find($termin_id);
        $termin = $ex_termin->unCancelDate();
        if ($termin) {
            $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'), $termin->getFullname()));
            $this->displayMessages();
        }
        $params = array();
        if ($termin->metadate_id != '') {
            $params['contentbox_open'] = $termin->metadate_id;
        }
        $this->redirect($this->url_for('course/timesrooms/index' . ($termin->metadate_id ? '#' . $termin->metadate_id : ''), $params));
    }


    public function stack_action($cycle_id = '')
    {
        $_SESSION['_checked_dates'] = Request::getArray('single_dates');
        if (empty($_SESSION['_checked_dates']) && isset($_SESSION['_checked_dates'])) {
            PageLayout::postMessage(MessageBox::error(_('Sie haben keine Termine ausgewählt!')));
            if (Request::get('fromDialog') == 'true') {
                $this->redirect($this->url_for('course/timesrooms/index#' . $cycle_id,
                    array('contentbox_open' => $cycle_id)));
            } else {
                $this->relocate('course/timesrooms/index#' . $cycle_id,
                    array('contentbox_open' => $cycle_id));
            }
            return;
        }

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

    public function editStack($cycle_id)
    {
        $this->cycle_id = $cycle_id;
        $this->teachers = $this->course->getMembers('dozent');
        $this->gruppen = Statusgruppen::findBySeminar_id($this->course->id);
        $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, false, true);
        $this->editParams = array('fromDialog' => Request::get('fromDialog'));
        $this->render_template('course/timesrooms/editStack');
    }

    public function prepareCancel($cycle_id)
    {
        $this->cycle_id = $cycle_id;
        $this->editParams = array('fromDialog' => Request::get('fromDialog'));
        $this->render_template('course/timesrooms/cancelStack');
    }

    public function unDeleteStack($cycle_id = '')
    {
        foreach ($_SESSION['_checked_dates'] as $id) {
            $ex_termin = CourseExDate::find($id);
            if ($ex_termin === null) {
                continue;
            }
            $ex_termin->content = '';
            $termin = $ex_termin->unCancelDate();
            if ($termin !== null) {
                $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'),
                        $termin->getFullname()));
            }
        }
        $this->displayMessages();
        unset($_SESSION['_checked_dates']);

        if (Request::get('fromDialog') == 'true') {
            $this->redirect($this->url_for('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id)));
        } else {
            $this->relocate('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
        }
    }

    public function deleteStack($cycle_id = '')
    {
        foreach ($_SESSION['_checked_dates'] as $id) {
            $termin = CourseDate::find($id);
            if ($termin === null) {
                $termin = CourseExDate::find($id);
            }
            if($termin->metadate_id && $termin instanceof CourseDate){
                $this->deleteDate($id, 'cancel', $cycle_id);
            } elseif ($termin->metadate_id === null || $termin->metadate_id === '') {
                $this->deleteDate($id, 'delete', $cycle_id);
            } elseif ($termin->metadate_id && $termin instanceof CoursExDate) {
                //$this->deleteDate($id, 'delete', $cycle_id);
            }
        }
        $this->displayMessages();

        unset($_SESSION['_checked_dates']);

        if (Request::get('fromDialog') == 'true') {
            $this->redirect($this->url_for('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id)));
        } else {
            $this->relocate('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
        }
    }

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

        if (Request::get('fromDialog') == 'true') {
            $this->redirect($this->url_for('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id)));
        } else {
            $this->relocate('course/timesrooms/index#' . $cycle_id,
                array('contentbox_open' => $cycle_id));
        }
    }

    public function saveCanceledStack($cycle_id = '')
    {
        $msg = _('Folgende Termine wurden gelöscht') . '<ul>';
        $deleted_dates = array();

        foreach ($_SESSION['_checked_dates'] as $val) {
            $termin = CourseDate::find($val);
            if ($termin === null) {
                continue;
            }
            $termin->content = trim(Request::get('cancel_comment', ''));
            $new_ex_termin = $termin->cancelDate();
            if ($new_ex_termin !== null) {
                $msg .= sprintf('<li>%s</li>', $new_ex_termin->getFullname());
            }
        }
        $msg .= '</ul>';
        $this->course->createMessage($msg);
        if (Request::int('cancel_send_message') && count($deleted_dates)) {
            $snd_messages = raumzeit_send_cancel_message(Request::get('cancel_comment'), $deleted_dates);
            if ($snd_messages) {
                $this->course->createMessage(sprintf(_('Es wurden %s Benachrichtigungen gesendet.'), $snd_messages));
            }
        }
    }

    public function saveEditedStack($cycle_id = '')
    {
        $persons = Request::getArray('related_persons');
        $action = Request::get('related_persons_action');
        $groups = Request::getArray('related_groups');
        $group_action = Request::get('related_groups_action');
        $teacher_changed = false;
        $groups_changed = false;
        foreach ($_SESSION['_checked_dates'] as $singledate_id) {
            $singledate = CourseDate::find($singledate_id);
            if (!isset($singledate)) {
                $singledate = CourseExDate::find($singledate_id);
            }
            $singledates[] = $singledate;
        }
        
        // Update related persons
        if (in_array($action, array('add', 'delete'))) {
            foreach ($singledates as $key => $singledate) {
                $dozenten = User::findDozentenByTermin_id($singledate->termin_id);
                $dozenten_new = $dozenten;//array();
                if ($singledate->range_id === $this->course->id) {
                    foreach ($persons as $user_id) {
                        $is_in_list = false;
                        foreach($dozenten as $user_key => $user){
                            if($user->user_id == $user_id){
                                $is_in_list = $user_key;
                            }
                        }
                        if ($is_in_list !== false && $action === 'add'){
                            $dozenten_new[] = User::find($user_id);
                            $teacher_changed = true;
                        } else if ($is_in_list !== false && $action === 'delete'){
                            unset($dozenten_new[$is_in_list]);
                            $teacher_changed = true;
                        }
                    }
                }
                $singledates[$key]->dozenten = $dozenten_new;
            }
        }
        
        if ($teacher_changed) {
            $this->course->createMessage(_("Zuständige Personen für die Termine wurden geändert."));
        }
        
        
        if (in_array($group_action, array('add', 'delete'))) {
            foreach ($singledates as $key => $singledate) {
                $groups_db = Statusgruppen::findByTermin_id($singledate->termin_id);
                $groups_new = $groups_db;
                if ($singledate->range_id === $this->course->id) {
                    foreach ($groups as $statusgruppe_id) {
                        $is_in_list = false;
                        foreach($groups_db as $group_key => $group){
                            if($statusgruppe_id == $group->statusgruppe_id){
                                $is_in_list = $group_key;
                            }
                        }
                        if (!$is_in_list !== false && $group_action === 'add'){
                            $groups_new[] = Statusgruppen::find($statusgruppe_id);
                            $groups_changed = true;
                        } else if ($is_in_list !== false && $group_action === 'delete'){
                            unset($groups_new[$is_in_list]);
                            $groups_changed = true;
                        }
                    }
                }
                $singledates[$key]->statusgruppen = $groups_new;
            }
        }
        
        if ($groups_changed) {
            $this->course->createMessage(_("Zugewiesene Gruppen für die Termine wurden geändert."));
        }

        foreach($singledates as $key => $singledate){
            if (Request::option('action') == 'room') {
                $singledate->raum = '';
                ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                        array(':termin' => $singledate->termin_id));
                $resObj = new ResourceObject($room_id);
                $room = new ResourceAssignment();
                $room->assign_user_id = $singledate->termin_id;
                $room->resource_id = Request::get('room');
                $room->begin = $singledate->date;
                $room->end = $singledate->end_time;
                $room->repeat_end = $singledate->end_time;
                $singledates[$key]->room_assignment = $room;
            } else if (Request::option('action') == 'freetext') {
                ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                        array(':termin' => $singledate->termin_id));
                $singledates[$key]->raum = Request::get('freeRoomText');
                $this->course->createMessage(sprintf(_("Der Termin %s wurde geändert, etwaige "
                        . "Raumbuchungen wurden entfernt und stattdessen der angegebene Freitext"
                        . " eingetragen!"),
                        '<b>' . $singledate->getFullname() . '</b>'));
            } else if (Request::option('action') == 'noroom') {
                ResourceAssignment::deleteBySQL('assign_user_id = :termin',
                        array(':termin' => $singledate->termin_id));
                $singledates[$key]->raum = '';
            }
        }
        
        foreach($singledates as $singledate){
            $singledate->store();
        }
    }

    /**
     * Create a cycle
     */
    public function createCycle_action($cycle_id = null)
    {
        if ($this->flash['request']) {
            foreach (words('day start_time end_time description cycle startWeek teacher_sws fromDialog') as $value) {
                Request::set($value, $this->flash['request'][$value]);
            }
        }
        $this->editParams = array('fromDialog' => Request::get('fromDialog'));
        if (!is_null($cycle_id)) {
            $this->cycle = SeminarCycleDate::find($cycle_id);
            $this->has_bookings = false;
            foreach ($this->cycle->dates as $singleDate) {
                $ids[] = $singleDate->termin_id;
            }
            
            $bookings = ResourceAssignment::findBySQL('assign_user_id IN ('."'".implode("','",$ids)."'".')');
            if (!empty($bookings)) {
                $this->has_bookings = true;
            }
        }
        $this->start_weeks = $this->getStartWeeks();
    }

    public function saveCycle_action()
    {
        CSRFProtection::verifyRequest();

        $now = time();
        $startHour = strftime('%H', strtotime(Request::get('start_time')));
        $startMinute = strftime('%M', strtotime(Request::get('start_time')));
        $endHour = strftime('%H', strtotime(Request::get('end_time')));
        $endMinute = strftime('%M', strtotime(Request::get('end_time')));

        if ($startHour > $endHour) {
            $this->flash['request'] = Request::getInstance();
            PageLayout::postMessage(MessageBox::error(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!')));
            $this->redirect('course/timesrooms/createCycle');
            return;
        }

        $cycle = new SeminarCycleDate();
        $cycle->id = md5(uniqid('metadate_id'));
        $cycle->seminar_id = $this->course->id;
        $cycle->weekday = Request::int('day');
        $cycle->description = Request::get('description');
        $cycle->sws = round(str_replace(',', '.', Request::get('teacher_sws')), 1);
        $cycle->cycle = Request::int('cycle');
        $cycle->week_offset = Request::int('startWeek');
        $cycle->end_offset = Request::int('endWeek') != 0 ? Request::int('endWeek') : null;
        $cycle->mkdate = $now;
        $cycle->chdate = $now;
        $cycle->start_time = sprintf('%02u:%02u:00', $startHour, $startMinute);
        $cycle->end_time = sprintf('%02u:%02u:00', $endHour, $endMinute);

        if ($cycle->store()) {
            $cycle_info = $cycle->toString();
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
            StudipLog::log("SEM_ADD_CYCLE", $this->course->id, NULL, $cycle_info);
            $this->course->createMessage(sprintf(_('Die regelmäßige Veranstaltungszeit %s wurde hinzugefügt!'), $cycle_info));
            $this->displayMessages();
            if (Request::get('fromDialog') == 'true') {
                $this->redirect('course/timesrooms/index');
            } else {
                $this->relocate('course/timesrooms/index');
            }
        } else {
            $this->flash['request'] = Request::getInstance();
            $this->course->createError(_('Die regelmäßige Veranstaltungszeit konnte nicht hinzugefügt werden! Bitte überprüfen Sie Ihre Eingabe.'));
            $this->displayMessages();
            $this->redirect('course/timesrooms/createCycle');
        }
   }

    /**
     * Save cycle
     * @throws Trails_DoubleRenderError
     */
    public function editCycle_action($cycle_id)
    {
        $cycle = SeminarCycleDate::find($cycle_id);//  $this->course->metadate->cycles[$cycle_id];
        
        $startHour = strftime('%H', strtotime(Request::get('start_time')));
        $startMinute = strftime('%M', strtotime(Request::get('start_time')));
        $endHour = strftime('%H', strtotime(Request::get('end_time')));
        $endMinute = strftime('%M', strtotime(Request::get('end_time')));

        // Prepare Request for saving Request
        $cycle->start_time = sprintf('%02u:%02u:00', $startHour, $startMinute);
        $cycle->end_time = sprintf('%02u:%02u:00', $endHour, $endMinute);
        $cycle->weekday = Request::int('day');
        $cycle->description = studip_utf8decode(Request::get('description'));
        $cycle->sws = Request::get('teacher_sws');
        $cycle->cycle = Request::get('cycle');
        $cycle->week_offset = Request::get('startWeek');
        $cycle->end_offset = Request::int('endWeek') != 0 ? Request::int('endWeek') : null;
        if($cycle->isDirty()){
            $cycle->chdate = time();
            $cycle->store();
        } else {
            die('keine Änderungen');
        }
        $this->redirect('course/timesrooms/index');
        return;
        
        /*
        $data['startWeek'] = Request::get('startWeek');
        $data['week_offset'] = Request::get('startWeek');
        $data['turnus'] = Request::get('cycle');
        $data['cycle'] = Request::get('cycle');
        $data['description'] = studip_utf8decode(Request::get('description'));
        $data['day'] = Request::int('day');
        $data['weekday'] = Request::int('day');
        $data['start_stunde'] = strftime('%H', strtotime(Request::get('start_time')));
        $data['start_minute'] = strftime('%M', strtotime(Request::get('start_time')));
        $data['end_stunde'] = strftime('%H', strtotime(Request::get('end_time')));
        $data['end_minute'] = strftime('%M', strtotime(Request::get('end_time')));
        $data['sws'] = Request::get('teacher_sws');
        $data['endWeek'] = Request::get('endWeek');
        */
       /*
        $new_start = mktime($data['start_stunde'], $data['start_minute']);
        $new_end = mktime($data['end_stunde'], $data['end_minute']);
        $old_start = mktime($cycle->getStartStunde(), $cycle->getStartMinute());
        $old_end = mktime($cycle->getEndStunde(), $cycle->getEndMinute());

        $same_time = false;

        // only apply changes, if the user approved the change or
        // the change does not need any approval
        if ($data['description'] != $cycle->getDescription()) {
            $this->course->createMessage(_('Die Beschreibung des regelmäßigen Eintrags wurde geändert.'));
            $cycle->setDescription($data['description']);
            $cycle->store();
            $message = true;
        }


        if ($old_start == $new_start && $old_end == $new_end) {
            $same_time = true;
        }

        if ($cycle->end_offset != $data['endWeek']) {
            $message = true;
            $same_time = false;
            $this->course->createMessage(_('Die Endwoche wurde geändert!.'));
        }
        if ($data['startWeek'] != $cycle->week_offset) {
            $this->course->setStartWeek($data['startWeek'], $cycle->metadate_id);
            $message = true;
        }
        if ($data['turnus'] != $cycle->cycle) {
            $this->course->setTurnus($data['turnus'], $cycle->metadate_id);
            $message = true;
        }
        if ($data['day'] != $cycle->day) {
            $message = true;
            $same_time = false;
        }

        if (round(str_replace(',', '.', $data['sws']), 1) != $cycle->sws) {
            $cycle->sws = $data['sws'];
            $this->course->createMessage(_('Die Semesterwochenstunden für Dozenten des regelmäßigen Eintrags wurden geändert.'));
            $message = true;
        }


        $change_from = $cycle->toString();
        if ($this->course->metadate->editCycle($data)) {
            if (!$same_time) {
                // logging >>>>>>
                StudipLog::log("SEM_CHANGE_CYCLE", $this->course->getId(), NULL, $change_from . ' -> ' . $cycle->toString());
                NotificationCenter::postNotification("CourseDidChangeSchedule", $this->course);
                // logging <<<<<<
                $this->course->createMessage(sprintf(_('Die regelmäßige Veranstaltungszeit wurde auf "%s" für alle in der Zukunft liegenden Termine geändert!'),
                    '<b>' . getWeekday($data['day']) . ', ' . $data['start_stunde'] . ':' . $data['start_minute'] . ' - ' .
                    $data['end_stunde'] . ':' . $data['end_minute'] . '</b>'));
                $message = true;
            }
        } else {
            if (!$same_time) {
                $this->course->createInfo(sprintf(_('Die regelmäßige Veranstaltungszeit wurde auf "%s" geändert, jedoch gab es keine Termine die davon betroffen waren.'),
                    '<b>' . getWeekday($data['day']) . ', ' . $data['start_stunde'] . ':' . $data['start_minute'] . ' - ' .
                    $data['end_stunde'] . ':' . $data['end_minute'] . '</b>'));
                $message = true;
            }
        }
         *
         */
        $cycle->store();
        
        //WAS MACHT DAS HIER???
        $this->course->metadate->sortCycleData();

        if (!$message) {
            $this->course->createInfo('Sie haben keine Änderungen vorgenommen!');
        }
        $this->displayMessages();
        $this->redirect('course/timesrooms/index');
    }


    public function deleteCycle_action($cycle_id)
    {
        CSRFProtection::verifyRequest();
        $cycle = SeminarCycleDate::find($cycle_id);
        if($cycle !== null){
            if($cycle->delete()){
                $this->course->createMessage(sprintf(_('Der regelmäßige Eintrag "%s" wurde gelöscht.'), '<b>' . $cycle->toString() . '</b>'));
            }
        }
        $this->displayMessages();

        $this->redirect('course/timesrooms/index');
    }

    /**
     * Add information to cancled / holiday date
     * @param $termin_id
     */
    public function cancel_action($termin_id)
    {
        if (Request::get('asDialog')) {
            $this->asDialog = true;
        }
        $this->termin = CourseDate::find($termin_id);
        if(empty($this->termin)){
            $this->termin = CourseExDate::find($termin_id);
        }
    }

    /**
     * @param $termin_id
     * @throws Trails_DoubleRenderError
     */
    public function saveComment_action($termin_id)
    {
        $termin = CourseExDate::find($termin_id);
        if (Request::get('cancel_comment')  != $termin->content) {
            $termin->content = Request::get('cancel_comment');
            if($termin->store()){
                $this->course->createMessage(sprintf(_('Der Kommtentar des gelöschten Termins %s wurde geändert.'), $termin->getFullname));
            } else {
                $this->course->createInfo(sprintf(_('Der gelöschte Termin %s wurde nicht verändert.'), $termin->getFullname));
            }
        } else {
            $this->course->createInfo(sprintf(_('Der gelöschte Termin %s wurde nicht verändert.'), $termin->getFullname));
        }
        if (Request::int('cancel_send_message')) {
            $snd_messages = raumzeit_send_cancel_message(Request::get('cancel_comment'), $termin);
            if ($snd_messages) {
                $this->course->createInfo(sprintf(_('Es wurden %s Benachrichtigungen gesendet.'), $snd_messages));
            }
        }
        $this->displayMessages();
        $this->redirect($this->url_for('course/timesrooms/index#' . $termin->metadate_id, array('contentbox_open' => $termin->metadate_id)));
    }

    function setSidebar()
    {
        $widget = new SelectWidget(_('Semester'), $this->url_for('course/timesrooms/index', array('cmd' => 'applyFilter')), 'newFilter');
        foreach ($this->selection as $item) {
            $element = new SelectElement($item['value'], $item['linktext'], $item['is_selected']);
            $widget->addElement($element);
        }
        Sidebar::Get()->addWidget($widget);


        if ($GLOBALS['perm']->have_perm("admin")) {
            include_once 'app/models/AdminCourseFilter.class.php';

            $list = new SelectorWidget();
            $list->setUrl($this->url_for('/index'));
            $list->setSelectParameterName('cid');
            foreach (AdminCourseFilter::get()->getCourses(false) as $seminar) {
                $list->addElement(new SelectElement($seminar['Seminar_id'], $seminar['Name']), 'select-' . $seminar['Seminar_id']);
            }
            $list->setSelection($this->course_id);
            Sidebar::Get()->addWidget($list);
        }

    }


    function setSemester_action($course_id)
    {
        $current_semester = Semester::findCurrent();
        $start_semester = Semester::find(Request::get('startSemester'));
        if ((int)Request::get('endSemester') != -1) {
            $end_semester = Semester::find(Request::get('endSemester'));
        } else {
            $end_semester = -1;
        }
        $course = Seminar::GetInstance($course_id);

        if ($start_semester == $end_semester) {
            $end_semester = 0;
        }

        if ($end_semester != 0 && $end_semester != -1 && $start_semester->beginn >= $end_semester->beginn) {
            PageLayout::postMessage(MessageBox::error(_('Das Startsemester liegt nach dem Endsemester!')));
        } else {

            $course->setStartSemester($start_semester->beginn);
            if ($end_semester != -1) {
                $course->setEndSemester($end_semester->beginn);
            } else {
                $course->setEndSemester($end_semester);
            }
            $course->removeAndUpdateSingleDates();


            // If the new duration includes the current semester, we set the semester-chooser to the current semester
            if ($current_semester->beginn >= $course->getStartSemester() && $current_semester->beginn <= $course->getEndSemesterVorlesEnde()) {
                $course->setFilter($current_semester->beginn);
            } else {
                // otherwise we set it to the first semester
                $course->setFilter($course->getStartSemester());
            }

        }

        $course->store();

        $messages = $course->getStackedMessages();
        foreach ($messages as $type => $msg) {
            PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
        }

        if (Request::submitted('save_close')) {
            if (Request::isXhr()) {
                $this->relocate('admin/courses');
            } else {
                $this->relocate('course/timesrooms/index/' . $course_id);
            }
        } else {
            $this->redirect($this->url_for('course/timesrooms/index', array('cid' => $course_id)));
        }

    }

    private function displayMessages($messages = array())
    {
        $messages = $messages ?: $this->course->getStackedMessages();
        if (!empty($messages)) {
            foreach ($messages as $type => $msg) {
                PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
            }
        }
    }

    private function getStartWeeks()
    {
        $start_weeks = array();
        $all_semester = SemesterData::GetInstance()->getAllSemesterData();
        if ($this->course->duration_time != -1) {
            $end_index = SemesterData::GetSemesterIndexById($this->course->end_semester->id);
        } else {
            $end_index = array_pop(array_keys($all_semester));
        }
        $start_index = SemesterData::GetSemesterIndexById($this->course->start_semester->id);
        $tmp_first_date = getCorrectedSemesterVorlesBegin($start_index);
        $_tmp_first_date = strftime('%d.%m.%Y', $tmp_first_date);
        $end_date = $all_semester[$end_index]['vorles_ende'];

        $i = 0;

        while ($tmp_first_date < $end_date) {

            $start_weeks[$i]['text'] = ($i + 1) . '. ' . _("Semesterwoche") . ' (' . _("ab") . ' ' . strftime("%d.%m.%Y", $tmp_first_date) . ')';
            $start_weeks[$i]['selected'] = ($this->course->getStartWeek() == $i);
            $i++;
            $tmp_first_date = strtotime(sprintf('+%u weeks %s', $i, $_tmp_first_date));
        }

        return $start_weeks;
    }

    public function deleteDate($termin_id, $sub_cmd, $cycle_id)
    {
        //cancel cycledate entry
        if ($sub_cmd == 'cancel') {
            $termin = CourseDate::find($termin_id);
            $room = $termin->getRoom();
            $termin->cancelDate();
        //delete singledate entry
        } else if($sub_cmd == 'delete') {
            $termin = CourseDate::find($termin_id);
            if ($termin === null) {
                $termin = CourseExDate::find($termin_id);
            }
            $termin_room = $termin->getRoom();
            $termin_date = $termin->getFullname();
            if ($termin->delete()) {
                if (Request::get('approveDelete')) {
                    if (Config::get()->RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW) {
                        $this->course->createMessage(sprintf(_('Sie haben den Termin %s gelöscht, dem ein Thema zugeorndet war.
                    Sie können das Thema in der %sExpertenansicht des Ablaufplans%s einem anderen Termin (z.B. einem Ausweichtermin) zuordnen.'),
                            $termin_date, '<a href="' . URLHelper::getLink('themen.php?cmd=changeViewMode&newFilter=expert') . '">', '</a>'));
                    } else {
                        if ($room) {
                            $this->course->createMessage(sprintf(_('Der Termin %s wurde gelöscht! Die Buchung für den Raum %s wurde gelöscht.'),
                                $termin_date, $termin_room));
                        } else {
                            $this->course->createMessage(sprintf(_('Der Termin %s wurde gelöscht!'), $termin_date));
                        }
                    }
                } // no approval needed, delete unquestioned
                else {
                    $this->course->createMessage(sprintf(_("Der Termin %s wurde gelöscht!"), $termin_date));
                }
            }
        }
    }

    private function checkFilter()
    {
        if (Request::option('cmd') == 'applyFilter') {
            $_SESSION['raumzeitFilter'] = Request::quoted('newFilter');
        }

        if ($this->course->getEndSemester() == 0 && !$this->course->hasDatesOutOfDuration()) {
            $_SESSION['raumzeitFilter'] = $this->course->getStartSemester();
        }

        /* Zeitfilter anwenden */
        if ($_SESSION['raumzeitFilter'] == '') {
            $_SESSION['raumzeitFilter'] = 'all';
            /*
            $raumzeitFilter = $semester->getCurrentSemesterData();
            $raumzeitFilter = $raumzeitFilter['beginn'];
            */
        }

        if ($_SESSION['raumzeitFilter'] != 'all') {
            if (($_SESSION['raumzeitFilter'] < $this->course->getStartSemester()) || ($_SESSION['raumzeitFilter'] > $this->course->getEndSemesterVorlesEnde())) {
                $_SESSION['raumzeitFilter'] = $this->course->getStartSemester();
            }
            $semester = new SemesterData();
            $filterSemester = $semester->getSemesterDataByDate($_SESSION['raumzeitFilter']);
            $this->course->applyTimeFilter($filterSemester['beginn'], $filterSemester['ende']);
        }
    }

    public function redirect($to)
    {
        $arguments = func_get_args();

        if (Request::isXhr()) {
            $url = call_user_func_array('parent::url_for', $arguments);

            $url_chunk = Trails_Inflector::underscore(substr(get_class($this), 0, -10));
            $index_url = $url_chunk . '/index';

            if (strpos($url, $index_url) !== false) {
                $this->flash['update-times'] = $this->course->id;
            }
        }

        return call_user_func_array('parent::redirect', $arguments);
    }
}

