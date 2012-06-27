<?php

/**
 * LimitedAdmission.class.php
 * 
 * Represents rules for admission to a limited number of courses.
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

require_once('AdmissionRule.class.php');

class LimitedAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * Maximal number of courses that a user can register for.
     */
    private $maxNumber = 1;

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String courseSetId
     * @param  String ruleId
     * @return LimitedAdmission
     */
    public function __construct($courseSetId, $ruleId='')
    {
        parent::__construct($courseSetId, $ruleId);
        if ($ruleId) {
            $this->load();
        }
    }

    /**
     * Users can specify their own maximal number of courses they want 
     * to be registered for. This method gets the specified value for the
     * given user or the max number that has been specified  by the rule if no
     * custom number was set.
     *
     * @param  userId
     * @return Integer
     */
    public function getCustomMaxNumber($userId)
    {
        // Initially we use the number given per admission rule.
        $maxNumber = $this->maxNumber;
        $stmt = DBManager::get()->prepare("SELECT `maxnumber` 
            FROM `userlimits` WHERE rule_id=? AND user_id=?");
        $stmt->execute(array($this->id, $userId));
        // The user has given some custom number.
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Custom number must be smaller than rule max number.
            $maxNumber = min($maxNumber, $current['maxnumber']);
        }
        return $maxNumber;
    }

    /**
     * Gets the maximal number of courses that users can be registered for.
     *
     * @return Integer
     */
    public function getMaxNumber()
    {
        return $this->maxNumber;
    }

    /**
     * Does the current rule allow the given user to register as participant 
     * in the given course?
     *
     * @param  String userId
     * @param  String courseId
     * @return Boolean
     */
    public function ruleApplies($userId, $courseId)
    {
        $applies = true;
        $courseSet = new CourseSet($this->courseSetId);
        // How many courses from this set has the user already registered for?
        $stmt = DBManager::get()->prepare("SELECT COUNT(`user_id`) AS number 
            FROM `seminar_user` WHERE `user_id`=? AND `Seminar_id` IN (?)");
        $stmt->execute(array($userId, 
            implode("', '", array_keys($courseSet->getCourses()))));
        if ($current = $stmt->fetch()) {
            // Check if the number is smaller than admission rule limit 
            // or own user limit.
            $applies = ($current['number'] < 
                min($this->maxNumber, $this->getCustomMaxNumber($userId)));
        }
        return $applies;
    }

    /**
     * Sets a new maximal number of courses that the given user can 
     * register for.
     *
     * @param  String userId
     * @param  Integer maxNumber
     * @return LimitedAdmission
     */
    public function setCustomMaxNumber($userId, $maxNumber)
    {
        $stmt = DBManager::get()->prepare("INSERT INTO `userlimits` 
            (`rule_id`, `user_id`, `maxnumber`, `mkdate`, `chdate`) 
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
            `maxnumber`=VALUES(`maxnumber`), `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $userId, 
            min($this->maxNumber, $maxNumber), time(), time()));
        return $this;
    }

    /**
     * Sets a new maximal number of courses for registration of the same user.
     *
     * @param  Integer newMaxNumber
     * @return LimitedAdmission
     */
    public function setMaxNumber($newMaxNumber)
    {
        $this->maxNumber = $newMaxNumber;
        return $this;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        parent::store();
        // Store LimitedAdmission specific values.
        $stmt = DBManager::get()->prepare("INSERT INTO `admissionlimits` 
            (`rule_id`, `maxnumber`, `mkdate`, `chdate`) 
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
            `maxnumber`=VALUES(`maxnumber`), `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->maxNumber, time(), time()));
        return $this;
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    private function load() {
        parent::load();
        // Get generic data which is common to all subclasses of AdmissionRule.
        $stmt = DBManager::get()->prepare("SELECT * 
            FROM `admissionlimits` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetchRow(PDO::FETCH_ASSOC)) {
            $this->maxNumber = $current['maxnumber'];
        }
    }

} /* end of class LimitedAdmission */

?>