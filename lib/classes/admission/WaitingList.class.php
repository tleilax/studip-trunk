<?php

/**
 * WaitingList.class.php
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

class WaitingList {

    public $id;
    public $courseId;
    public $maxUsers;

    // --- OPERATIONS ---

    public function __construct($list_id='') {
        $this->id = $list_id;
        $this->courseId = '';
        $this->maxUsers = 0;
        if ($list_id) {
            $this->load();
        }
    }
    
    /**
     * Adds the user with the given ID to the waiting list. If no explicit
     * position is given, the user is inserted at the bottom of the waiting
     * list.
     *
     * @param  String userId
     * @return WaitingList
     */
    public function addUser($userId, $position=0) {
        if ($position==0) {
            // If a maximal number of allowed users is set:
            if ($this->maxUsers) {
                // Get number of persons already on waiting list.
                $stmt = DBManager::get()->prepare(
                    "SELECT COUNT(`user_id`) AS number
                    FROM `waitinglist_user` WHERE `list_id`=?");
                $stmt->execute(array($this->id));
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($data['number'] < $this->maxUsers) {
                    // Insert user at end of waiting list.
                    $query = "INSERT INTO `waitinglist_user`
                        (`list_id`, `user_id`, `position`, `mkdate`)
                        VALUES (?, ?, ?, ?)";
                    $parameters = array($this->id, $userId, $data['number']+1, 
                        time());
                }
            } else {
                // Insert user at end of waiting list.
                $query = "INSERT INTO `waitinglist_user`
                    (`list_id`, `user_id`, `position`, `mkdate`) (
                    SELECT ?, ?, MAX(`position`)+1, ? FROM `waitinglist_user`
                    WHERE `list_id`=?)";
                $parameters = array($this->id, $userId, time(), $this->id);
            }
        } else {
            // Move all users with same or greater position one position up.
            $query = "UPDATE `waitinglist_user` 
                SET `position`=`position`+1 
                WHERE `position`>=? AND `list_id`=?";
            $parameters = array($courseId, $userId, $position, time());
            $stmt = DBManager::get()->prepare($query);
            $stmt->execute($parameters);
            // Insert user at specified position.
            $query = "INSERT INTO `waitinglist_user` 
                (`list_id`, `user_id`, `position`, `mkdate`) 
                VALUES (?, ?, ?, ?)";
            $parameters = array($courseId, $userId, $position, time());
        }
        $stmt = DBManager::get()->prepare($query);
        return $stmt->execute($parameters);
    }

    /**
     * Deletes the current waiting list and moves all users as participants to
     * the corresponding course.
     */
    public function delete() {
        // Assign all users to associated course.
        $users = $this->getUsers();
        $course = new Seminar($this->courseId);
        foreach ($users as $user) {
            $course->addMember($user['user_id']);
        }
        // Delete list data from database.
        $stmt = DBManager::get()->prepare("DELETE FROM `waitinglist_config`
            WHERE `list_id`=?");
        $stmt->execute(array($this->id));
        $stmt = DBManager::get()->prepare("DELETE FROM `waitinglist_user`
            WHERE `list_id`=?");
        $stmt->execute(array($this->id));
        
    }

    /**
     * Gets the course ID this waiting list belongs to.
     */
    public function getCourseId() {
        return $this->courseId;
    }

    /**
     * Gets the maximum number of users permitted on this list.
     */
    public function getMaxUsers() {
        return $this->maxUsers;
    }

    /**
     * Gets all waiting lists for courses that are associated with the given
     * course set.
     * 
     * @param String $courseSetId Course set
     * @return Array
     */
    public static function getWaitingListsForCourseSet($courseSetId) {
        $lists = array();
        $stmt = DBManager::get()->prepare("SELECT `list_id`
            FROM `waitinglist_config` 
            WHERE `set_id`=?
            ORDER BY `mkdate`");
        $stmt->execute(array($courseSetId));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $current) {
            $lists[] = $current['list_id'];
        }
        return $lists;
    }

    /**
     * Gets the given user's position on the waiting list for the given course.
     * 
     * @param  String $userId User to check
     * @param  String $courseId Course ID
     * @return int 
     */
    public function getUserPosition($userId, $courseId) {
        $query = "SELECT `position` FROM `waitinglist_user`
            WHERE `list_id`=? AND `user_id`=? AND `seminar_id`=? LIMIT 1";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute(array($this->id, $userId, $courseid));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['position'];
    }

    /**
     * Gets the waiting list for the given course.
     * 
     * @param  String courseId
     * @return Array
     */
    public function getUsers() {
        $stmt = DBManager::get()->prepare("SELECT * FROM `waitinglist_user` 
            WHERE `list_id`=? AND `seminar_id`=? ORDER BY `position` ASC");
        $stmt->execute(array($this->id, $courseId));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper function for loading data from DB.
     */
    public function load() {
        // Load basic data.
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `waitinglist_config` WHERE list_id=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $data['list_id'];
            $this->maxUsers = $data['max_users'];
        }
    }

    /**
     * Removes the given user ID from the list.
     *
     * @param  String userId
     * @return WaitingList
     */
    public function removeUser($userId, $courseId) {
        // Get position of user in waiting list:
        $position = $this->getUserPosition($userId, $courseId);
        // Remove user entry from database...
        $query = "DELETE FROM `waitinglist_user`
            WHERE `list_id`=? AND `user_id`=? AND `seminar_id`=?";
        $stmt = DBManager::get()->prepare($query);
        $success = $stmt->execute(array($this->id, $userId, $courseid));
        // ... and update other waiting list positions.
        $query = "UPDATE `waitinglist_user` SET `position`=`position`-1
            WHERE `list_id`=? AND `seminar_id`=? AND `position`>?";
        $stmt = DBManager::get()->prepare($query);
        $success = $stmt->execute(array($this->id, $courseid, $position));
    }

    public function setCourseId($newId) {
        $this->courseId = $newId;
    }

    public function store() {
        // Generate new ID if course set doesn't exist in DB yet.
        if (!$this->id) {
            do {
                $newid = md5(uniqid(get_class($this), true));
                $db = DBManager::get()->query("SELECT `list_id` 
                    FROM `waitinglist_config` WHERE `list_id`='.$newid.'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        // Store basic data.
        $stmt = DBManager::get()->prepare("INSERT INTO `waitinglist_config`
            (`list_id`, `seminar_id`, `max_users`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `seminar_id`=VALUES(`seminar_id`), `max_users`=VALUES(`max_users`),
            `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->institutId, $this->name,
            $this->algorithm, time(), time()));
    }

    /**
     * Sets a new maximal number of allowed users on this waiting list.
     */
    public function setMaxUsers($newValue) {
        $this->maxUsers = $newValue;
        return $this;
    }

} /* end of class WaitingList */

?>