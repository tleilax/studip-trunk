<?php

/**
 * ConditionalAdmission.class.php
 * 
 * Contains users that get different probabilities than others in seat 
 * distribution algorithm.
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

class ConditionalAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * All conditions that must be fulfilled for successful admission.
     */
    private $conditions = array();

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String courseSetId
     *      The course set this rule belongs to.
     * @param  String ruleId If this rule has been saved previously, it 
     *      will be loaded from database.
     * @return AdmissionRule the current object (this).
     */
    public function __construct($courseSetId, $ruleId='')
    {
        parent::__construct();
        return $this;
    }

    /**
     * Short description of method addCondition
     *
     * @param  String conditionId
     * @return ConditionalAdmission
     */
    public function addCondition($condition)
    {
        $this->conditions[$condition->getId()] = $condition;
        return $this;
    }

    /**
     * Short description of method getConditions
     *
     * @return Array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Short description of method removeCondition
     *
     * @param  String conditionId
     * @return ConditionalAdmission
     */
    public function removeCondition($conditionId)
    {
        unset($this->conditions[$conditionId]);
        return $this;
    }

    /**
     * Checks whether the given user fulfills the configured
     * admission conditions. Only one of the conditions needs to be
     * fulfilled (logical operator OR). The fields in a condition are
     * in conjunction (logical operator AND).
     * 
     * @param String $userId
     * @param String $courseId
     * @return boolean Is the user allowed to register?
     */
    function ruleApplies($userId, $courseId) {
        $applies = false;
        // Check all configured conditions.
        foreach ($this->conditions as $condition) {
            $applies = $applies || $condition->isFulfilled($userId);
        }
        return $applies;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        parent::store();
        // Store all conditions.
        foreach ($this->conditions as $condition) {
            // Store each condition...
            $condition->store();
            // ... and its connection to the current admission rule.
            $stmt = DBManager::get()->prepare("INSERT INTO `admission_condition` 
                (`rule_id`, `condition_id`, `mkdate`, `chdate`) 
                VALUES (? ?, ?, ?) ON DUPLICATE KEY UPDATE 
                `condition_id`=VALUES(`condition_id`), `chdate`=VALUES(`chdate`)");
            $stmt->execute(array($this->id, $condition->getId(), time(), time()));
        }
        return $this;
    }

    public function toString() {
        $text = "";
        if ($this->conditions) {
            $text .= _("Zur Zulassung müssen folgende Bedingungen erfüllt sein:")."\n";
            foreach ($this->conditions as $condition) {
                $text .= $condition->toString()."\n";
            }
        }
        return $text;
    }

    /**
     * Helper function for loading data from DB. Generic AdmissionRule data is
     * loaded with the parent load() method.
     */
    private function load() {
        parent::load();
        // Retrieve conditions.
        $stmt = DBManager::get()->prepare("SELECT * 
            FROM `admissionconditions` WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
        $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($conditions as $condition) {
            $current = new StudipCondition($this->id, $condition['condition_id']);
            $this->conditions[$condition['condition_id']] = $current;
        }
    }

} /* end of class ConditionalAdmission */

?>