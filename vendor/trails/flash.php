<?php

/*
 * flash.php - Passing Objects Between Actions
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 * Copyright (C) 2006 Ruby-On-Rails-Team <http://www.rubyonrails.org/core>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * The flash provides a way to pass temporary objects between actions.
 * Anything you place in the flash will be exposed to the very next action and
 * then cleared out. This is a great way of doing notices and alerts, such as
 * a create action that sets
 * <tt>$flash->set('notice', "Successfully created")</tt>
 * before redirecting to a display action that can then expose the flash to its
 * template.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @author    Ruby-On-Rails-Team <http://www.rubyonrails.org/core>
 * @copyright (c) Authors
 * @version   $Id: flash.php 4570 2006-11-30 15:20:41Z mlunzena $
 */

class Trails_Flash {

  /**
   * @ignore
   */
  var
    $flash, $used;

  /**
   * Constructor
   *
   * @return void
   */
  function Trails_Flash() {
    $this->flash = array();
    $this->used  = array();
  }

  /**
   * Class field replacement.
   *
   * @access private
   *
   * @param object  the flash to set.
   *
   * @return object the stored flash.
   */
  function &flash($set = FALSE) {
    static $flash;

    if ($set !== FALSE)
      $flash = $set;

    return $flash;
  }

  /**
   * Used internally by the <tt>keep</tt> and <tt>discard</tt> methods
   *     use()               # marks the entire flash as used
   *     use('msg')          # marks the "msg" entry as used
   *     use(null, false)    # marks the entire flash as unused
   *                         # (keeps it around for one more action)
   *     use('msg', false)   # marks the "msg" entry as unused
   *                         # (keeps it around for one more action)
   *
   * @access private
   *
   * @param mixed  a key.
   * @param bool   used flag.
   *
   * @return void
   */
  function _use($k = NULL, $v = TRUE) {
    if ($k)
      $this->used[$k] = $v;
    else
      foreach ($this->used as $k => $value)
        $this->_use($k, $v);
  }

  /**
   * Marks the entire flash or a single flash entry to be discarded by the end
   * of the current action.
   *
   *     $flash->discard()             # discards entire flash
   *                                   # (it'll still be available for the
   *                                   # current action)
   *     $flash->discard('warning')    # discard the "warning" entry
   *                                   # (it'll still be available for the
   *                                   # current action)
   *
   * @param mixed  a key.
   *
   * @return void
   */
  function discard($k = NULL) {
    $this->_use($k);
  }

  /**
   * Marks flash entries as used and expose the flash to the view.
   *
   * @return void
   */
  function fire() {
    $flash = NULL;
    if (!isset($_SESSION['flash'])) {
      $flash =& Trails_Flash::flash(new Trails_Flash());
      $_SESSION['flash'] = $flash;
    }
    else {
      $flash =& Trails_Flash::flash(
        unserialize(base64_decode($_SESSION['flash'])));
    }

    $flash->discard();
    register_shutdown_function(array('Trails_Flash', 'sweep'));
  }

  /**
   * Returns the value to the specified key.
   *
   * @param mixed  a key.
   *
   * @return mixed the key's value.
   */
  function &get($k) {
    $return = NULL;
    if (isset($this->flash[$k]))
      $return =& $this->flash[$k];
    return $return;
  }

  /**
   * Keeps either the entire current flash or a specific flash entry available
   * for the next action:
   *
   *    $flash->keep()           # keeps the entire flash
   *    $flash->keep('notice')   # keeps only the "notice" entry, the rest of
   *                             # the flash is discarded
   *
   * @param mixed  a key.
   *
   * @return void
   */
  function keep($k = NULL) {
    $this->_use($k, FALSE);
  }

  /**
   * Sets a flash that will not be available to the next action, only to the
   * current.
   *
   *    $flash->now('message') = "Hello current action";
   *
   * This method enables you to use the flash as a central messaging system in
   * your app. When you need to pass an object to the next action, you use the
   * standard flash assign (<tt>set</tt>). When you need to pass an object to
   * the current action, you use <tt>now</tt>, and your object will vanish when
   * the current action is done.
   *
   * Entries set via <tt>now</tt> are accessed the same way as standard entries:
   * <tt>$flash->get('my-key')</tt>.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function now($k, $v) {
    $this->discard($k);
    $this->flash[$k] = $v;
  }

  /**
   * Sets a key's value.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function set($k, $v) {
    $this->keep($k);
    $this->flash[$k] = $v;
  }

  /**
   * Sets a key's value by reference.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function set_ref($k, &$v) {
    $this->keep($k);
    $this->flash[$k] =& $v;
  }

  /**
   * Deletes the flash entries that were not marked for keeping.
   *
   * @return void
   */
  function sweep(){

    # call sweep only once
# TODO
#     static $once;
#     if (!is_null($once)) return;
#     $once = TRUE;

    # no flash, no sweep
    if (!isset($_SESSION['flash']))
      return;

    # get flash
    $flash =& Trails_Flash::flash();

    // actually sweep
    $keys = array_keys($flash->flash);
    foreach ($keys as $k) {
      if (!$flash->used[$k]) {
        $flash->_use($k);
      } else {
        unset($flash->flash[$k]);
        unset($flash->used[$k]);
      }
    }

    // cleanup if someone meddled with flash or used
    $fkeys = array_keys($flash->flash);
    $ukeys = array_keys($flash->used);
    foreach (array_diff($fkeys, $ukeys) as $k => $v)
      unset($flash->used[$k]);

    // serialize it
    $_SESSION['flash'] = base64_encode(serialize($flash));
  }
}
