<?php

/**
 * TODO
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class TrailsException extends Exception {

  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  public $headers;


  /**
   * @param  int     the status code to be set in the response
   * @param  string  a human readable presentation of the status code
   * @param  array   a hash of additional headers to be set in the response
   *
   * @return void
   */
  function __construct($status = 500, $reason = NULL, $headers = array()) {
    if ($reason === NULL) {
      $reason = TrailsResponse::get_reason($status);
    }
    parent::__construct($reason, $status);
    $this->headers = $headers;
  }


  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return type       <description>
   */
  function __toString() {
    return "{$this->code} {$this->message}";
  }
}


class TrailsDoubleRenderError extends TrailsException {

  function __construct() {
    $message =
      "Render and/or redirect were called multiple times in this action. ".
      "Please note that you may only call render OR redirect, and at most ".
      "once per action.";
    parent::__construct(500, $message);
  }
}


class TrailsMissingFile extends TrailsException {
  function __construct($message) {
    parent::__construct(500, $message);
  }
}


class TrailsRoutingError extends TrailsException {

  function __construct($message) {
    parent::__construct(400, $message);
  }
}


class TrailsUnknownAction extends TrailsException {

  function __construct($message) {
    parent::__construct(404, $message);
  }
}


class TrailsUnknownController extends TrailsException {

  function __construct($message) {
    parent::__construct(404, $message);
  }
}


class TrailsSessionRequiredException extends TrailsException {
  function __construct() {
    $message = "Tried to access a non existing session.";
    parent::__construct(500, $message);
  }
}
