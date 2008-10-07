<?php

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class Step00138Studienbereichszuordnung extends DBMigration {


  function description() {
    return 'Adds the new Value SEM_TREE_ALLOW_BRANCH_ASSIGN to table config.';
  }


  function up() {
    $this->announce("add new value SEM_TREE_ALLOW_BRANCH_ASSIGN to table config");

    $db = DBManager::get();
    $db->exec("INSERT IGNORE INTO `config` ".
              "VALUES ('34f348c06bbd5d9fc7bb36a8d829e12e', '', ".
              "'SEM_TREE_ALLOW_BRANCH_ASSIGN', '1', 1, 'boolean', 'global', ".
              "'', 0, 1222947575, 1222947575, ".
              "'Diese Option beeinflusst die M�glichkeit, Veranstaltungen ".
              "entweder nur an die Bl�tter oder �berall in der ".
              "Veranstaltungshierarchie einh�ngen zu d�rfen.', '', '')");

    $this->announce("done.");
  }


  function down() {
    $this->announce("remove value SEM_TREE_ALLOW_BRANCH_ASSIGN from table config");

    $this->db->query("DELETE FROM `config` ".
                     "WHERE config_id = '34f348c06bbd5d9fc7bb36a8d829e12e' ".
                     "LIMIT 1");

    $this->announce("done.");
  }
}
