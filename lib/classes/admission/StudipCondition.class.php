<?php

/**
 * StudipCondition.class.php
 * 
 * Conditions for user selection in Stud.IP. A condition is a collection of
 * condition fields, e.g. degree, course of study or semester. Each 
 * condition can have a validity period. 
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

class StudipCondition
{
    // --- ATTRIBUTES ---

    /**
     * When does the validity end?
     */
    public $endTime = 0;

    /**
     * All condition fields that form this condition.
     */
    public $fields = array();

    /**
     * Unique identifier for this condition.
     */
    public $id = '';

    /**
     * When does the validity start?
     */
    public $startTime = 0;

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId
     * @param  String conditionId
     * @return StudipCondition
     */
    public function __construct($ruleId, $conditionId='')
    {
        $this->ruleId = $ruleId;
        if ($conditionId) {
            $this->load();
        }
        return $this;
    }

    /**
     * Add a new condition field.
     *
     * @param  ConditionField fieldId
     * @return StudipCondition
     */
    public function addField($field)
    {
        $this->fields[$field->getId()] = $field;
        return $this;
    }

    public function checkTimeFrame() {
        $valid = true;
        // Start time given, but still in the future.
        if ($this->startTime && $this->startTime > time()) {
            $valid = false;
        }
        // End time given, but already past.
        if ($this->endTime && $this->endTime < time()) {
            $valid = false;
        }
        return $valid;
    }

    /**
     * Get end of validity.
     *
     * @return Integer
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Get all fields (without checking for validity according 
     * to the current time).
     *
     * @return Array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get ID.
     *
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets start of validity.
     *
     * @return Integer
     */
    public function getStartTime()
    {
       return $this->startTime;
    }

    /**
     * Is the current condition fulfilled (that means, are all 
     * required field values matched)?
     * 
     * @return boolean
     */
    function isFulfilled($userId) {
        $fulfilled = true;
        // If we are not in specified time frame, we needn't check any further.
        if ($this->checkTimeFrame()) {
            // Check all fields.
            foreach ($this->fields as $field) {
                $fulfilled = $fulfilled && 
                    $field->checkValue($field->getUserValue($userId));
            }
        }
        return $fulfilled;
    }

    /**
     * Removes the field with the given ID from the condition fields.
     *
     * @param  String fieldId
     * @return StudipCondition
     */
    public function removeField($fieldId)
    {
        unset($this->fields[$fieldId]);
        return $this;
    }

    /**
     * Sets a new end time for condition validity.
     *
     * @param  Integer newEndTime
     * @return StudipCondition
     */
    public function setEndTime($newEndTime)
    {
        $this->endTime = $newEndTime;
        return $this;
    }

    /**
     * Sets a new start time for condition validity.
     *
     * @param  Integer newStartTime
     * @return StudipCondition
     */
    public function setStartTime($newStartTime)
    {
        $this->startTime = $newStartTime;
        return $this;
    }

    /**
     * Stores data to DB.
     */
    public function store() {
        // Generate new ID if condition entry doesn't exist in DB yet.
        if (!$this->id) {
            do {
                $newid = md5(uniqid('StudipCondition', true));
                $db = DBManager::get()->query("SELECT `condition_id` 
                    FROM `studipconditions` WHERE `condition_id`='.$newid.'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        // Store condition data.
        $stmt = DBManager::get()->prepare("INSERT INTO `studipconditions` 
            (`condition_id`, `start_time`, `end_time`, `mkdate`, `chdate`)  
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE `start_time`=VALUES(`start_time`), 
            `end_time`=VALUES(`end_time`),`chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->startTime, $this->endTime, 
            time(), time()));
        // Delete removed condition fields from DB.
        DBManager::get()->exec("DELETE FROM `conditionfields` 
            WHERE `condition_id`='".$this->id."' AND `field_id` NOT IN ('".
            implode("', '", array_keys($this->fields))."')");
        // Store all fields.
        foreach ($this->fields as $field) {
            $field->store();
        }
    }

    public function toString() {
        $text = "";
        // Start time but no end time given.
        if ($this->startTime && !$this->endTime) {
            $text .= sprintf(_("g�ltig ab %s"), 
                date("d.m.Y", $this->startTime))."\n";
        // End time but no start time given.
        } else if (!$this->startTime && $this->endTime) {
            $text .= sprintf(_("g�ltig bis %s"), 
                date("d.m.Y", $this->endTime))."\n";
        // Start and end time given.
        } else if ($this->startTime && $this->endTime) {
            $text .= sprintf(_("g�ltig von %s bis %s"), 
                date("d.m.Y", $this->startTime), 
                date("d.m.Y", $this->endTime))."\n";
        }
        foreach ($this->fields as $field) {
            $text .= $field->getName()." ".$field->getCompareOperator().
                " ".$field->getValue()."\n";
        }
        return $text;
    }

    /**
     * Internal helper function for loading data from DB.
     */
    private function load() {
        // Load basic condition data.
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `studipconditions` WHERE `condition_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $data['condition_id'];
            $this->startTime = $data['start_time'];
            $this->endTime = $data['end_time'];
            // Load the associated condition fields.
            $stmt = DBManager::get()->prepare(
                "SELECT `field_id`, `type` FROM `conditionfields` WHERE `condition_id`=? LIMIT 1");
            $stmt->execute(array($this->id));
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                /*
                 * Create instance of appropriate ConditionField subclass.
                 * We just "try" here because the class definition could have 
                 * been removed since saving data to DB.
                 */
                try {
                    $field = new $data['type']($data['id']);
                } catch (Exception $e) {}
            }
        }
    }

} /* end of class StudipCondition */

?>