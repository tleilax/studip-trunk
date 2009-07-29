<?php
# Lifter007: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * NOTE: This interface will change significantly in Stud.IP 1.11.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface AdministrationPlugin {

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
}
