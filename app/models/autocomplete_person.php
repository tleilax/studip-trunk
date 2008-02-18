<?

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

function autocomplete_person_find_by_given($fragment) {
  $db = DBManager::get();
  $stmt = $db->prepare("SELECT DISTINCT Vorname FROM auth_user_md5 "
                        ."WHERE Vorname LIKE ? "
                        ."AND " . get_vis_query() . " "
                        ."ORDER BY Vorname "
                        ."LIMIT 10");
  $stmt->execute(array("%{$fragment}%"));

  return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

function autocomplete_person_find_by_family($fragment) {
  $db = DBManager::get();
  $stmt = $db->prepare("SELECT user_id, username, Vorname, Nachname, ".
                       "title_front, title_rear, perms ".
                       "FROM auth_user_md5 ".
                       "LEFT JOIN user_info USING (user_id) ".
                       "WHERE Nachname LIKE ? ".
                       "AND " . get_vis_query() . " ".
                       "ORDER BY Nachname ".
                       "LIMIT 10");
  $stmt->execute(array("%{$fragment}%"));

  return $stmt->fetchAll();
}

function autocomplete_person_find_by_name($fragment) {
  global $_fullname_sql;
  $db = DBManager::get();
  $stmt = $db->prepare("SELECT user_id, username, Vorname, Nachname, ".
                       "title_front, title_rear, perms ".
                       "FROM auth_user_md5 ".
                       "LEFT JOIN user_info USING (user_id) ".
                       "WHERE (username LIKE ? OR ".
                       "Vorname LIKE ? OR ".
                       "Nachname LIKE ?) ".
                       "AND " . get_vis_query() . " ".
                       "ORDER BY Nachname ".
                       "LIMIT 10");
  $stmt->execute(array("%{$fragment}%", "%{$fragment}%", "%{$fragment}%"));

  return $stmt->fetchAll();
}

