<?php

/**
 * ConditionField.class.php
 * 
 * A specification of a Stud.IP condition that must be fulfilled. One
 * or more instances of the ConditionField subclasses make up a
 * StudipCondition.
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

class ConditionField
{
    // --- ATTRIBUTES ---

    /**
     * Which of the valid compare operators is currently chosen?
     */
    private $compareOperator = '';

    /**
     * ID of the StudipCondition this field belongs to.
     */
    private $conditionId = '';

    /**
     * Unique ID for this condition field.
     */
    private $id = '';

    /**
     * A display name for this condition field.
     */
    private $name = '';

    /**
     * The set of valid compare operators.
     */
    private $validCompareOperators = array('<', '>', '=', '!=');

    /**
     * All valid values for this field.
     */
    private $validValues = array();

    /**
     * Which of the valid values is currently chosen?
     */
    private $value = null;

    // --- OPERATIONS ---

    public function __construct($conditionId, $fieldId='') {
        $this->conditionId = $conditionId;
        $this->id = $fieldId;
    }

    /**
     * Checks whether the given value fits the configured condition. The
     * value is compared to the currently selected value by using the
     * currently selected compare operator.
     *
     * @param  Array values
     * @return Boolean
     */
    public function checkValue($values)
    {
        $result = false;
        foreach ($values as $value) {
            if (eval('return ('.$value.$this->compareOperator.
                $this->value.')'))
            {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Which compare operator is set?
     *
     * @return String
     */
    public function getCompareOperator()
    {
        return $this->compareOperator;
    }

    /**
     * Field ID.
     *
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the value for the given user that is relevant for this
     * condition field. For example, in an SubjectCondition, this
     * method would look up the subject of study for the user.
     * 
     * @param  String $userId User to check.
     * @param  Array additional conditions that are required for check.
     * @return Array The value(s) for this user.
     */
    public function getUserValues($userId, $additional=null) {
        return array();
    }

    /**
     * Returns all valid compare operators.
     *
     * @return Array Array of valid compare operators.
     */
    public function getValidCompareOperators()
    {
        return $this->validCompareOperators;
    }

    /**
     * Returns all valid values. Values can be loaded dynamically from
     * database or be returned as static array.
     *
     * @return Array Valid values in the form $value => $displayname.
     */
    public function getValidValues()
    {
        return $this->validValues;
    }

    /**
     * Which value is set?
     *
     * @return String
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets a new selected compare operator
     *
     * @param  String newOperator
     * @return ConditionField
     */
    public function setCompareOperator($newOperator)
    {
        if (in_array($newOperator, $this->validCompareOperators)) {
            $this->compareOperator = $newOperator;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Sets a new selected value.
     *
     * @param  String newValue
     * @return ConditionField
     */
    public function setValue($newValue)
    {
        $validValues = $this->getValidValues();
        if ($validValues[$newValue]) {
            $this->value = $newValue;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        // Generate new ID if field entry doesn't exist in DB yet.
        if (!$this->id) {
            do {
                $newid = md5(uniqid('ConditionField', true));
                $db = DBManager::get()->query("SELECT `field_id` 
                    FROM `conditionfields` WHERE `field_id`='.$newid.'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        // Store field data.
        $stmt = DBManager::get()->prepare("INSERT INTO `conditionfields` 
            (`field_id`, `condition_id`, `type`, `value`, `compare_op`, 
            `mkdate`, `chdate`)  VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE `condition_id`=VALUES(`condition_id`), 
            `type`=VALUES(`type`),`value`=VALUES(`value`), 
            `compare_op`=VALUES(`compare_op`), `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this-conditionId, get_class($this), 
            $this->value, $this->compareOperator, time(), time()));
    }

    /**
     * Helper function for loading data from DB.
     */
    private function load() {
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `conditionfields` WHERE `field_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->conditionId = $data['condition_id'];
            $this->value = $data['value'];
            $this->compareOperator = $data['compare_op'];
        }
    }

} /* end of class ConditionField */

?>