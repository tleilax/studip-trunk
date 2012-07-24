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
     * Adds the user with the given ID to the waiting list.
     *
     * @param  String userId
     * @return WaitingList
     */
    public static function addUser($userId, $courseSetId, $courseId) {
        $stmt = DBManager::get()->prepare("INSERT INTO `waitinglist` 
            (`set_id`, `seminar_id`, `user_id`, `mkdate`) 
            VALUES (?, ?, ?, ?)");
        return $stmt->execute(array($courseSetId, $courseId, $userId, time()));
    }

    /**
     * Gets the waiting list for a single course.
     * 
     * @param  String courseId
     * @return Array
     */
    public static function getCourseWaitingList($courseId) {
        $stmt = DBManager::get()->prepare("SELECT * FROM `waitinglist` 
            WHERE `seminar_id`=? ORDER BY `mkdate` ASC");
        $stmt->execute(array($courseId));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets the waiting list for a CourseSet.
     * 
     * @param  String courseSetId
     * @return Array
     */
    public static function getCourseSetWaitingList($courseSetId) {
        $stmt = DBManager::get()->prepare("SELECT * FROM `waitinglist` 
            WHERE `set_id`=? ORDER BY `mkdate` ASC");
        $stmt->execute(array($courseSetId));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Removes the given user ID from the list.
     *
     * @param  String userId
     * @return WaitingList
     */
    public static function removeUser($userId, $courseSetId, $courseId) {
        $query = "DELETE FROM `waitinglist` WHERE `user_id`=?";
        $parameters = array($userId);
        if ($courseSetId) {
            $query .= " AND `set_id`=?";
            $parameters[] = $courseSetId;
        }
        if ($courseId) {
            $query .= " AND `seminar_id`=?";
            $parameters[] = $courseId;
        }
        $stmt = DBManager::get()->prepare($query);
        return $stmt->execute($parameters);
    }

} /* end of class WaitingList */

?>