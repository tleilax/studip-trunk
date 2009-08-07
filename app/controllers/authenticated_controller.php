<?php
# Lifter007: TODO
# Lifter003: TODO

/*
 * Copyright (C) 2009 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class AuthenticatedController extends Trails_Controller {

  /**
   * Callback function being called before an action is executed. If this
   * function does not return FALSE, the action will be called, otherwise
   * an error will be generated and processing will be aborted. If this function
   * already #rendered or #redirected, further processing of the action is
   * withheld.
   *
   * @param string  Name of the action to perform.
   * @param array   An array of arguments to the action.
   *
   * @return bool
   */
  function before_filter(&$action, &$args) {
    global $_language_path, $_language, $auth;

    # open session
    page_open(array('sess' => 'Seminar_Session',
                    'auth' => 'Seminar_Auth',
                    'perm' => 'Seminar_Perm',
                    'user' => 'Seminar_User'));

	// show login-screen, if authentication is "nobody"
	$auth->login_if($auth->auth["uid"] == "nobody"); 

    $this->flash = Trails_Flash::instance();

    # set up language prefs
    $_language_path = init_i18n($_language);

    # Set base layout
    #
    # If your controller needs another layout, overwrite your controller's
    # before filter:
    #
    #   class YourController extends AuthenticatedController {
    #     function before_filter(&$action, &$args) {
    #       parent::before_filter($action, $args);
    #       $this->set_layout("your_layout");
    #     }
    #   }
    #
    # or unset layout by sending:
    #
    #   $this->set_layout(NULL)
    #
    $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
  }

  /**
   * Callback function being called after an action is executed.
   *
   * @param string Name of the action to perform.
   * @param array  An array of arguments to the action.
   *
   * @return void
   */
  function after_filter($action, $args) {
    page_close();
  }

  /**
   * Exception handler called when the performance of an action raises an
   * exception.
   *
   * @param  object     the thrown exception
   *
   * @return object     a response object
   */
  function rescue($exception) {

    # erase former response
    if ($this->performed) {
      $this->erase_response();
    }

    $body = $GLOBALS['template_factory']->render('unhandled_exception',
                                                     compact("exception"));

    $this->response = new Trails_Response($body, array(), 500,
                                          $exception->getMessage());

    return $this->response;
  }
}
