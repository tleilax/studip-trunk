<?php

/**
 * AdmissionPriority.class.php
 * 
 * This class represents priorities a user has given to a set of courses.
 * No instance is needed, all methods are designed to be called statically.
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

class AdmissionPriority
{

    /**
     * Get all priorities for the given course set.
     * The priorities are stored in a 2-dimensional array in the form
     * priority[user_id][course_id] = x.
     *
     * @param  String courseSetId
     * @return A 2-dimensional array containing all priorities.
     */
    public static function getPriorities($courseSetId)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `priorities`
             WHERE `set_id`=?");
        $stmt->execute(array($courseSetId));
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $priorities[$current['user_id']][$current['seminar_id']] = $current['priority'];
        }
        return $priorities;
    }

    /**
     * Get all priorities for the given course in the given course set.
     * The priorities are stored in an array in the form
     * priority[user_id] = x.
     *
     * @param  String courseSetId
     * @param  String courseId
     * @return An array containing all priorities.
     */
    public static function getPrioritiesByCourse($courseSetId, $courseId)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `priorities`
             WHERE `set_id`=? AND `seminar_id`=?");
        $stmt->execute(array($courseSetId, $courseId));
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $priorities[$current['user_id']] = $current['priority'];
        }
        return $priorities;
    }

    /**
     * Get all priorities the given user has set in the given course set.
     * The priorities are stored in an array in the form
     * priority[course_id] = x.
     *
     * @param  String courseSetId
     * @param  String userId
     * @return An array containing all priorities.
     */
    public static function getPrioritiesByUser($courseSetId, $userId)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `priorities`
             WHERE `set_id`=? AND `user_id`=?");
        $stmt->execute(array($courseSetId, $userId));
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $priorities[$current['seminar_id']] = $current['priority'];
        }
        return $priorities;
    }

    /**
     * The given user sets a course in the given course set to priority x.
     *
     * @param  String courseSetId
     * @param  String userId
     * @param  String courseId
     * @param  int priority
     * @return int Number of affected rows, if any.
     */
    public static function setPriority($courseSetId, $userId, $courseId, $priority)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "INSERT INTO `priorities` (`user_id`, `set_id`, `seminar_id`, 
                    `priority`, `mkdate`, `chdate`)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE `priority`=VALUES(`priority`), 
                    `chdate`=VALUES(`chdate`)");
        return $stmt->execute(array($userId, $courseSetId, $courseId, 
            $priority, time(), time()));
    }

} /* end of class AdmissionPriority */

?>