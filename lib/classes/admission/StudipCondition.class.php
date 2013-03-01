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

require_once('lib/classes/admission/ConditionField.class.php');

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
     * @param  String conditionId
     * @return StudipCondition
     */
    public function __construct($conditionId='')
    {
        ConditionField::getAvailableConditionFields();
        $this->id = $conditionId;
        if ($conditionId) {
            $this->load();
        } else {
            $this->id = $this->generateId();
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
        $field->setConditionId($this->id);
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
     * Deletes the condition and all associated fields.
     */
    public function delete() {
        // Delete condition data.
        $stmt = DBManager::get()->prepare("DELETE FROM `conditions` 
            WHERE `condition_id`=?");
        $stmt->execute(array($this->id));
        // Delete all defined condition fields.
        foreach ($this->fields as $field) {
            $field->delete();
        }
    }

    /**
     * Generate a new unique ID.
     * 
     * @param  String tableName
     */
    public function generateId() {
        do {
            $newid = md5(uniqid(get_class($this).microtime(), true));
            $db = DBManager::get()->query("SELECT `condition_id` 
                FROM `conditions` WHERE `condition_id`='.$newid.'");
        } while ($db->fetch());
        return $newid;
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
    public function isFulfilled($userId) {
        $fulfilled = true;
        // If we are not in specified time frame, we needn't check any further.
        if ($this->checkTimeFrame()) {
            // Check all fields.
            foreach ($this->fields as $field) {
                $fulfilled = $fulfilled && 
                    $field->checkValue($field->getUserValues($userId));
            }
        }
        return $fulfilled;
    }

    /**
     * Helper function for loading data from DB.
     */
    public function load() {
        // Load basic condition data.
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `conditions` WHERE `condition_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $data['condition_id'];
            $this->startTime = $data['start_time'];
            $this->endTime = $data['end_time'];
            // Load the associated condition fields.
            $stmt = DBManager::get()->prepare(
                "SELECT `field_id`, `type` FROM `conditionfields`
                WHERE `condition_id`=?");
            $stmt->execute(array($this->id));
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                /*
                 * Create instance of appropriate ConditionField subclass.
                 * We just "try" here because the class definition could have 
                 * been removed since saving data to DB.
                 */
                //try {
                    $field = new $data['type']($data['field_id']);
                    $this->fields[$field->getId()] = $field;
                //} catch (Exception $e) {}
            }
        }
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
                    FROM `conditions` WHERE `condition_id`='.$newid.'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        // Store condition data.
        $stmt = DBManager::get()->prepare("INSERT INTO `conditions` 
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
            $field->store($this->id);
        }
    }

    public function toString() {
        $tpl = $GLOBALS['template_factory']->open('conditions/display');
        $tpl->set_attribute('condition', $this);
        return $tpl->render();
    }

    public function __toString() {
        return $this->toString();
    }

} /* end of class StudipCondition */

?>