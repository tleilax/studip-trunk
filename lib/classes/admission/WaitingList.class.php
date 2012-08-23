<?php

/**
 * WatingList.class.php
 * 
 * A waiting list for users who didn't get a seat in a course or course set.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class WaitingList
{
    // --- OPERATIONS ---

    /**
     * Adds the user with the given ID to the waiting list. If no explicit
     * position is given, the user is inserted at the bottom of the waiting
     * list.
     *
     * @param  String userId
     * @return WaitingList
     */
    public static function addUser($userId, $courseId, $position=0) {
        if ($position==0) {
            $query = "INSERT INTO `waitinglist` 
                (`seminar_id`, `user_id`, `position`, `mkdate`) ( 
                SELECT ?, ?, MAX(`position`)+1, ? FROM `waitinglist` 
                WHERE `seminar_id`=?)";
            $parameters = array($courseId, $userId, time(), $courseId);
        } else {
            $query = "INSERT INTO `waitinglist` 
                (`seminar_id`, `user_id`, `position`, `mkdate`) 
                VALUES (?, ?, ?, ?)";
            $parameters = array($courseId, $userId, $position, time());
        }
        $stmt = DBManager::get()->prepare($query);
        return $stmt->execute($parameters);
    }

    /**
     * Gets the given user's position on the waiting list for the given course.
     * 
     * @param  String $userId User to check
     * @param  String $courseId Course ID
     * @return int 
     */
    public static function getUserPosition($userId, $courseId) {
        $query = "SELECT `position` FROM `waitinglist`
            WHERE `user_id`=? AND `seminar_id`=? LIMIT 1";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute(array($userId, $courseid));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['position'];
    }

    /**
     * Gets the waiting list for the given course.
     * 
     * @param  String courseId
     * @return Array
     */
    public static function getWaitingList($courseId) {
        $stmt = DBManager::get()->prepare("SELECT * FROM `waitinglist` 
            WHERE `seminar_id`=? ORDER BY `mkdate` ASC");
        $stmt->execute(array($courseId));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Removes the given user ID from the list.
     *
     * @param  String userId
     * @return WaitingList
     */
    public static function removeUser($userId, $courseId) {
        // Get position of user in waiting list:
        $position = WaitingList::getUserPosition($userId, $courseId);
        // Remove user entry from database...
        $query = "DELETE FROM `waitinglist`
            WHERE `user_id`=? AND `seminar_id`=?";
        $stmt = DBManager::get()->prepare($query);
        $success = $stmt->execute(array($userId, $courseid));
        // ... and update other wating list positions.
        $query = "UPDATE `waitinglist` SET `position`=`position`-1
            WHERE `seminar_id`=? AND `position`>?";
        $stmt = DBManager::get()->prepare($query);
        $success = $stmt->execute(array($courseid, $position));
    }

} /* end of class WaitingList */

?>