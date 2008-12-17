<?php

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
   * Verfügt dieses Plugin über einen Eintrag auf der Startseite des
   * Administrators?
   *
   * @return boolean    true, falls Hauptmenü vorhanden, sonst false
   */
  function hasTopNavigation();

  /**
   * Liefert den Menüeintrag zurück.
   *
   * @return StudipPluginNavigation  das Menü, oder null, wenn kein Menü
   *                                 vorhanden ist
   */
  function getTopNavigation();

  /**
   * Setzt das Hauptmenü des Plugins.
   *
   * @param  StudipPluginNavigation  das neue Hauptmenü
   *
   * @return void
   */
  function setTopnavigation(StudipPluginNavigation $newnavigation);
}
