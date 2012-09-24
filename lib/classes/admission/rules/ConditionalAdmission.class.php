<?php

/**
 * ConditionalAdmission.class.php
 * 
 * An admission rule that specifies conditions for course admission, like
 * degree, study course or semester.
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

require_once(realpath(dirname(__FILE__).'/..').'/AdmissionRule.class.php');

class ConditionalAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * All conditions that must be fulfilled for successful admission.
     */
    public $conditions = array();

    /**
     * Flag to invalidate all conditions at once, can be used to open a
     * course set for admission after the seat distribution run. The conditions
     * themselves are NOT deleted.
     */
    public $conditionsStopped = false;

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId If this rule has been saved previously, it 
     *      will be loaded from database.
     * @return AdmissionRule the current object (this).
     */
    public function __construct($ruleId='')
    {
        $this->id = $ruleId;
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('conditionaladmissions');
        }
        return $this;
    }

    /**
     * Adds a new StudipCondition to this rule.
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
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `conditionaladmissions` 
            WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
        // Delete all associated conditions...
        foreach ($this->conditions as $condition) {
            $condition->delete();
        }
        // ... and their connection to this rule.
        $stmt = DBManager::get()->prepare("DELETE FROM `admission_condition` 
            WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
    }

    /**
     * Gets all users that are matched by thís rule.
     *
     * @return Array An array containing IDs of users who are matched by 
     *      this rule.
     */
    public function getAffectedUsers()
    {
        $users = array();
        foreach ($this->condition as $condition) {
            $users = array_intersect($users, $condition->getAffectedUsers());
        }
        return $users;
    }

    /**
     * Gets all defined conditions.
     *
     * @return Array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Gets whether the condition evaluation has been stopped.
     *
     * @return boolean
     */
    public function getConditionsStopped()
    {
        return $this->conditionsStopped;
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective 
     * subclass) does.
     */
    public static function getDescription() {
        return _("Über eine Menge von Bedingungen kann festgelegt werden, ".
            "wer zur Anmeldung zu den Veranstaltungen des Anmeldesets ".
            "zugelassen ist. Es muss nur eine der Bedingungen erfüllt sein, ".
            "innerhalb einer Bedingung müssen aber alle Datenfelder ".
            "zutreffen.");
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Bedingte Anmeldung");
    }

    /**
     * Invalidates the conditions without deleting them. Can be used to stop
     * condition evaluation after seat distribution in course set.
     */
    public function invalidate() {
        $this->conditionsStopped = true;
        $this->store();
    }

    /**
     * Helper function for loading data from DB. Generic AdmissionRule data is
     * loaded with the parent load() method.
     */
    public function load() {
        // Load basic data.
        $stmt = DBManager::get()->prepare("SELECT * 
            FROM `conditionaladmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->conditionsStopped = (bool) $current['conditions_stopped'];
            // Retrieve conditions.
            $stmt = DBManager::get()->prepare("SELECT * 
                FROM `admission_condition` WHERE `rule_id`=?");
            $stmt->execute(array($this->id));
            $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($conditions as $condition) {
                $current = new StudipCondition($condition['condition_id']);
                $this->conditions[$condition['condition_id']] = $current;
            }
        }
    }

    /**
     * Removes the condition with the given ID from the rule.
     *
     * @param  String conditionId
     * @return ConditionalAdmission
     */
    public function removeCondition($conditionId)
    {
        $this->conditions[$conditionId]->delete();
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
        // Is condition evaluation stopped?
        if (!$this->conditionsStopped) {
            // Check all configured conditions.
            foreach ($this->conditions as $condition) {
                $applies = $applies || $condition->isFulfilled($userId);
            }
        } else {
            $applies = true;
        }
        return $applies;
    }

    /**
     * Sets a new value for invalidation of conditions.
     * 
     * @param boolean $newValue
     * @return ConditionAdmission
     */
    function setConditionsStopped($newValue) {
        $this->conditionsStopped = $newValue;
        return $this;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        // Store rule data.
        $stmt = DBManager::get()->prepare("INSERT INTO `conditionaladmissions`
            (`rule_id`, `message`, `conditions_stopped`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `message`=VALUES(`message`),
            `conditions_stopped`=VALUES(`conditions_stopped`),
            `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->message, 
            $this->conditionsStopped, time(), time()));
        // Delete removed conditions from DB.
        DBManager::get()->exec("DELETE FROM `admission_condition`
            WHERE `rule_id`='".$this->id."' AND `condition_id` NOT IN ('".
            implode("', '", array_keys($this->conditions))."')");
        // Store all conditions.
        $queries = array();
        $parameters = array();
        foreach ($this->conditions as $condition) {
            // Store each condition...
            $condition->store();
            $queries[] = "(?, ?, ?)";
            $parameters[] = $this->id;
            $parameters[] = $condition->getId();
            $parameters[] = time();
        }
        // Store all assignments between rule and condition.
        $stmt = DBManager::get()->prepare("INSERT INTO `admission_condition`
            (`rule_id`, `condition_id`, `mkdate`)
            VALUES ".implode(",", $queries)." ON DUPLICATE KEY UPDATE
            `condition_id`=VALUES(`condition_id`)");
        $stmt->execute($parameters);
        return $this;
    }

    public function toString() {
        $text = "";
        if ($this->conditions && !$this->conditionsStopped) {
            $text .= _("Zur Zulassung müssen folgende Bedingungen erfüllt sein:")."\n";
            foreach ($this->conditions as $condition) {
                $text .= $condition->toString()."\n";
            }
        }
        return $text;
    }

} /* end of class ConditionalAdmission */

?>