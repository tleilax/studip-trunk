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
    public $compareOperator = '';

    /**
     * ID of the StudipCondition this field belongs to.
     */
    public $conditionId = '';

    /**
     * Unique ID for this condition field.
     */
    public $id = '';

    /**
     * The set of valid compare operators.
     */
    public $validCompareOperators = array('<', '>', '=', '!=');

    /**
     * All valid values for this field.
     */
    public $validValues = array();

    /**
     * Which of the valid values is currently chosen?
     */
    public $value = null;

    // --- OPERATIONS ---

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
        // For equality checks we must use the "==" operator.
        if ($this->compareOperator == '=') {
            $cOp = '==';
        } else {
            $cOp = $this->compareOperator;
        }
        foreach ($values as $value) {
            if (eval("return ('".$value."'".$cOp."'".$this->value."');"))
            {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Deletes the stored data for this condition field from DB.
     */
    public function delete() {
        // Delete condition data.
        $stmt = DBManager::get()->prepare("DELETE FROM `conditionfields` 
            WHERE `field_id`=?");
        $stmt->execute(array($this->id));
    }

    /**
     * Generate a new unique ID.
     * 
     * @param  String tableName
     */
    public function generateId() {
        do {
            $newid = md5(uniqid(get_class($this).microtime(), true));
            $db = DBManager::get()->query("SELECT `field_id` 
                FROM `conditionfields` WHERE `field_id`='.$newid.'");
        } while ($db->fetch());
        return $newid;
    }

    /**
     * Returns all users that fulfill the specified condition. This can be
     * an important informatione when checking on validity of a combination
     * of conditions.
     * 
     * @return Array All users that are affected by the current condition 
     * field.
     */
    public function getAffectedUsers() {
        return array();
    }

    /**
     * Reads all available ConditionField subclasses and loads their definitions.
     */
    public static function getAvailableConditionFields() {
        $fields = array();
        // Load all PHP class files found in the condition field folder.
        foreach (glob(realpath(dirname(__FILE__).'/conditions').'/*.class.php') as $file) {
            require_once($file);
            // Try to auto-calculate class name from file name.
            $className = substr(basename($file), 0, 
                strpos(basename($file), '.class.php'));
            $current = new $className();
            // Check if class is right.
            if (is_subclass_of($current, 'ConditionField')) {
                $fields[$className] = $className::getName();
            }
        }
        return $fields;
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
        return _("Stud.IP-Bedingung");
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
     * Helper function for loading data from DB.
     */
    public function load() {
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `conditionfields` WHERE `field_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->conditionId = $data['condition_id'];
            $this->value = $data['value'];
            $this->compareOperator = $data['compare_op'];
        }
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
     * Sets a new condition ID.
     * 
     * @param  String newId
     * @return ConditionField
     */
    public function setConditionId($newId) {
        $this->conditionId = $newId;
    } 

    /**
     * Sets a new selected value.
     *
     * @param  String newValue
     * @return ConditionField
     */
    public function setValue($newValue)
    {
        if ($this->validValues[$newValue]) {
            $this->value = $newValue;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Stores data to DB.
     * 
     * @param  String conditionId The condition this field belongs to.
     */
    public function store($conditionId) {
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
        $stmt->execute(array($this->id, $conditionId, get_class($this), 
            $this->value, $this->compareOperator, time(), time()));
    }

} /* end of class ConditionField */

?>