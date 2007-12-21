<?php

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/functions.php';
require_once 'app/models/autocomplete_person.php';

class Autocomplete_PersonController extends Trails_Controller {

  function given_action() {
    $search_term = strtr(self::get_param('value'), array('%' => '\%'));
    $this->persons = autocomplete_person_find_by_given($search_term);
    $this->render_template('autocomplete/person/list');
  }

  function family_action() {
    $search_term = strtr(self::get_param('value'), array('%' => '\%'));
    $this->persons = autocomplete_person_find_by_family($search_term);
    $this->render_template('autocomplete/person/list');
  }

  function name_action() {
    $search_term = strtr(self::get_param('value'), array('%' => '\%'));
    $this->persons = autocomplete_person_find_by_name($search_term);
  }

  private static function get_param($key) {
    return remove_magic_quotes(@$_POST[$key]);
  }

  function before_filter($action, &$args) {
    # open session
    page_open(array('sess' => 'Seminar_Session',
                    'auth' => 'Seminar_Auth',
                    'perm' => 'Seminar_Perm',
                    'user' => 'Seminar_User'));
    require_once 'lib/seminar_open.php';
    # user must be logged in
    $GLOBALS['auth']->login_if($_REQUEST['again']
                               && ($GLOBALS['auth']->auth['uid'] == 'nobody'));
  }

  function after_filter($action, &$args) {
    page_close();
  }
}
