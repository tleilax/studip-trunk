<?php
# Lifter007: TODO
# Lifter003: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'lib/functions.php';
require_once 'lib/webservices/api/studip_lecture_tree.php';


class Course_StudyAreasController extends Trails_Controller {


  # see Trails_Controller#before_filter
  function before_filter(&$action, &$args) {

    global $perm, $_language_path, $_language;

    # open session
    page_open(array('sess' => 'Seminar_Session',
                    'auth' => 'Seminar_Auth',
                    'perm' => 'Seminar_Perm',
                    'user' => 'Seminar_User'));

    # set up language prefs
    #$_language_path = init_i18n($_language);
    include 'lib/seminar_open.php';

    # user must have tutor permission
    $perm->check('tutor');

    # user must be logged in
    $GLOBALS['auth']->login_if($_REQUEST['again']
                               && ($GLOBALS['auth']->auth['uid'] == 'nobody'));

    $course_id = current($args);
    if ($course_id && !$perm->have_studip_perm("tutor", $course_id)) {
      $this->set_status(403);
      return FALSE;
    }
  }


  # see Trails_Controller#after_filter  # see Trails_Controller#after_filter
  function after_filter($action, $args) {
    page_close();
  }


  /**
   * This method shows the study area selection form for a given course ID.
   *
   * @param  string     the MD5ish ID of the course
   *
   * @return void
   */
  function show_action($course_id = '') {

    # prepare layout
    $layout =
      $GLOBALS['template_factory']->open('layouts/base_without_infobox');
    $this->set_layout($layout);

    Navigation::activateItem('/admin/course/study_areas');

    # w/o a course ID show the admin search form
    if ($course_id === '') {
      $GLOBALS['CURRENT_PAGE'] = _('Studienbereichsauswahl');

      require_once 'lib/admin_search.inc.php';

      include 'lib/include/html_head.inc.php';
      include 'lib/include/header.php';
      include 'lib/include/admin_search_form.inc.php';  // will not return
    }

    $this->set_course($course_id);

    $GLOBALS['CURRENT_PAGE'] = sprintf('%s - %s',
                                       (getHeaderLine($course_id)),
                                       _('Studienbereichsauswahl'));

    # is locked?
    # TODO (mlunzena) shouldn't this be done in the before filter?
    $this->locked = LockRules::Check($course_id , 'sem_tree');

    # does the course's class permit "bereiche"?
    # TODO (mlunzena) shouldn't this be done in the before filter?
    $class = $GLOBALS['SEM_TYPE'][$this->course->getStatus()]["class"];
    $this->areas_not_allowed = !$GLOBALS['SEM_CLASS'][$class]["bereiche"];


    if (!$this->locked && !$this->areas_not_allowed) {

      # renew status
      $study_areas = isset($_REQUEST['study_area_selection'])
         ? remove_magic_quotes($_REQUEST['study_area_selection'])
         : array();

      if (isset($study_areas['last_selected'])) {
        $this->selection->setSelected((string) $study_areas['last_selected']);
      }
      if (isset($study_areas['showall'])) {
        $this->selection->setShowAll((boolean) $study_areas['showall']);
      }
      if (isset($study_areas['areas'])) {
        $this->selection->setAreas((array) $study_areas['areas']);
      }

      $this->update_selection($study_areas);

      $this->course->setStudyAreas($this->selection->getAreaIDs());
    }

    $this->url = $this->url_for('course/study_areas/show/'.$course_id);
  }


  function update_selection($study_areas) {

    # action: add
    if (isset($study_areas['add'])) {
      foreach ($study_areas['add'] as $key => $value) {
        $this->selection->add($key);
      }
    }

    # action: remove
    else if (isset($study_areas['remove'])) {

      # keep at least one
      if ($this->selection->size() >= 1 + sizeof($study_areas['remove'])) {
        foreach ($study_areas['remove'] as $key => $value) {
          $this->selection->remove($key);
        }
      }
      else {
        $this->error = _("Sie k�nnen diesen Studienbereich nicht l�schen, da eine Veranstaltung immer mindestens einem Studienbereich zugeordnet sein muss.");
      }
    }

    # action: switch show all
    else if (isset($study_areas['showall_button'])) {
      $this->selection->toggleShowAll();
    }

    # action: search
    else if (isset($study_areas['search_key']) &&
              $study_areas['search_key'] != '') {
      $this->selection->setSearchKey($study_areas['search_key']);
    }

    # action: expand
    else if (isset($study_areas['selected'])) {
      $this->selection->setSelected($study_areas['selected']);
    }
  }


  /**
   * This method is sent using AJAX to add a study area to a course.
   *
   * @param  string     the MD5ish ID of the course
   *
   * @return void
   */
  function add_action($course_id = NULL)  {

    $this->set_course($course_id);

    # retrieve the study area from the POST body
    # w/o a study area ID, render a BAD REQUEST
    $id = isset($_POST['id']) ? $_POST['id'] : NULL;
    if ($id === NULL) {
      $this->set_status(400);
      return $this->render_nothing();
    }

    $this->area = StudipStudyArea::find($id);

    $this->selection->add($this->area);

    $this->store_selection($course_id, $this->selection);

    $this->render_template('course/study_areas/selected_entries');
  }


  /**
   * This method is sent using AJAX to remove a study area from a course.
   *
   * @param  string     the MD5ish ID of the course
   *
   * @return void
   */
  function remove_action($course_id = NULL) {

    $id = isset($_POST['id']) ? $_POST['id'] : NULL;

    if ($id === NULL) {
      $this->set_status(400);
      return $this->render_nothing();
    }

    $selection = self::get_selection($course_id);

    # removing the last area, would put the server into an inconsistent state;
    # send a 409 Conflict back
    if ($selection->size() == 1) {
      $this->set_status(409);
      return $this->render_nothing();
    }

    $selection->remove($id);

    $this->store_selection($course_id, $selection);

    $this->render_nothing();
  }


  /**
   * This method is sent using AJAX to expand a study area subtree whose root is
   * the specified $id.
   *
   * @param  string     the MD5ish ID of the course
   * @param  string     the ID of the study area to expand
   *
   * @return void
   */
  function expand_action($course_id = NULL, $id = NULL) {

    $this->set_course($course_id);

    if ($id === NULL) {
      $this->set_status(400);
      return $this->render_nothing();
    }

    $this->selection->setSelected($id);

    $this->render_template('course/study_areas/tree');
  }


  /**
   * Returns a StudipStudyAreaSelection object for a given course ID.
   * If the course ID is falsy, use the session variable from
   * admin_seminare_assi.
   *
   * NOTE: This is a hack -- remove it ASAP.
   *
   * @param  string     either the MD5ish ID of a course or something falsy to
   *                    indicate a course that is currently being created
   *
   * @return mixed      a "bean" of class StudipStudyAreaSelection representing
   *                    the selection form
   */
  function get_selection($course_id) {
    if ($course_id) {
      $selection = new StudipStudyAreaSelection($course_id);
    }
    else {

      $areas = array();
      if (isset($GLOBALS['sem_create_data']) &&
          isset($GLOBALS['sem_create_data']['sem_bereich'])) {
        $areas = $GLOBALS['sem_create_data']['sem_bereich'];
      }

      $selection = new StudipStudyAreaSelection();
      $selection->setAreas($areas);
    }
    return $selection;
  }


  # TODO (mlunzena) this hack has to be removed
  function store_selection($course_id, $selection) {

    # w/ course ID, write the new study areas to the db
    if ($course_id) {
      $course = Seminar::getInstance($course_id);
      $course->setStudyAreas($selection->getAreaIDs());
    }

    # w/o a course ID, insert all the areas IDs into the session variable of
    # admin_seminare_assi.php
    else {
      $GLOBALS['sem_create_data']['sem_bereich'] = $selection->getAreaIDs();
    }
  }

  function set_course($course_id){
    $this->selection = self::get_selection($course_id);
    if ($course_id){
      $this->course_id = $course_id;
      $this->course = Seminar::getInstance($course_id);
      $this->semester_id = SemesterData::GetSemesterIdByDate($this->course->getSemesterStartTime());
    } else {
      $this->semester_id = SemesterData::GetSemesterIdByDate($GLOBALS['sem_create_data']['sem_start_time']);
    }
  }
}

