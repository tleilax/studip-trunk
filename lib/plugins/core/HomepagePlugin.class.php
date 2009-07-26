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

interface HomepagePlugin {

  /**
   * Used to show an overview on the homepage of a user.
   *
   * @return type       <description>
   */
  function showOverview();

  /**
   * <MethodDescription>
   *
   * @return boolean    true:  overviewpage is enabled,
   *                    false: overviewpage is disabled
   */
  function getStatusShowOverviewPage();

  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return type       <description>
   */
  function setStatusShowOverviewPage($status);

  /**
   * Set the user for which the homepage is rendered
   *
   * @param  type       <description>
   *
   * @return void
   */
  function setRequestedUser($newuser);

  /**
   * <MethodDescription>
   *
   * @return type       <description>
   */
  function getRequestedUser();
}
