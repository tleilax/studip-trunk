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
 * @author      Thomas Hackl, <thomas.hackl@uni-passau.de>
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
    public function StudipCondition($ruleId, $conditionId='')
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
     * Get all fields.
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

    public function toString() {
        $text = "";
        // Start time but no end time given.
        if ($this->startTime && !$this->endTime) {
            $text .= sprintf(_("gültig ab %s"), 
                date("d.m.Y", $this->startTime))."\n";
        // End time but no start time given.
        } else if (!$this->startTime && $this->endTime) {
            $text .= sprintf(_("gültig bis %s"), 
                date("d.m.Y", $this->endTime))."\n";
        // Start and end time given.
        } else if ($this->startTime && $this->endTime) {
            $text .= sprintf(_("gültig von %s bis %s"), 
                date("d.m.Y", $this->startTime), 
                date("d.m.Y", $this->endTime))."\n";
        }
        foreach ($this->fields as $field) {
            $text .= $field->getName()." ".$field->getCompareOperator().
                " ".$field->getValue()."\n";
        }
        return $text;
    }

    private function checkTimeFrame() {
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

} /* end of class StudipCondition */

?>