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

interface StudIPAdministrationPlugin {

  /**
   * Verf�gt dieses Plugin �ber einen Eintrag auf der Startseite des
   * Administrators?
   *
   * @return boolean    true, falls Hauptmen� vorhanden, sonst false
   */
  function hasTopNavigation();

  /**
   * Liefert den Men�eintrag zur�ck.
   *
   * @return StudipPluginNavigation  das Men�, oder null, wenn kein Men�
   *                                 vorhanden ist
   */
  function getTopNavigation();

  /**
   * Setzt das Hauptmen� des Plugins.
   *
   * @param  StudipPluginNavigation  das neue Hauptmen�
   *
   * @return void
   */
  function setTopnavigation(StudipPluginNavigation $newnavigation);
}
