<?php

/**
 * AdmissionRule.class.php
 * 
 * An abstract representation of rules for course admission.
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

abstract class AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * A unique identifier for this rule.
     */
    private $id = '';

    /**
     * Which course set does this rule belong to?
     */
    private $courseSetId = '';

    /**
     * A message that is shown to users that are rejected for admission 
     * because of the current rule.
     */
    private $message = '';

    // --- OPERATIONS ---

    public function __construct($courseSetId, $ruleId='') {
        $this->courseSetId = $courseSetId;
        $this->id = $ruleId;
    }

    /**
     * Short description of method getAffectedUsers
     *
     * @return Array An array containing IDs of users who are matched by 
     *      this rule.
     */
    public function getAffectedUsers()
    {
        return array();
    }

    /**
     * Gets the rule ID.
     *
     * @return String This rule's ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the message that is shown to users rejected by this rule.
     *
     * @return String The message.
     */
    public function getMessage()
    {
        return $this->message;
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
        return true;
    }

    /**
     * Sets a new message to show to users.
     *
     * @param  String newMessage A new message text.
     * @return AdmissionRule This object
     */
    public function setMessage($newMessage)
    {
        $this->message = $newMessage;
        return $this;
    }

    /**
     * Helper function for storing rule definition to database.
     */
    public function store() {
        // Generate new ID if list doesn't exist in DB yet.
        if (!$this->id) {
            do {
                $newid = md5(uniqid(get_class($this), true));
                $db = DBManager::get()->query("SELECT `list_id` 
                    FROM `admissionrule` WHERE `rule_id`='.$newid.'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        // Store basic admission data.
        $stmt = DBManager::get()->prepare("INSERT INTO `admissionrules` 
            (`rule_id`, `set_id`, `type`, `message`, `mkdate`, `chdate`) 
            VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
            `set_id`=VALUES(`set_id`), `type`=VALUES(`type`), 
            `message`=VALUES(`message`), `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->courseSetId, get_class($this), 
            $this->message, time(), time()));
        // Delete removed conditions from DB.
        DBManager::get()->exec("DELETE FROM `admission_condition` 
            WHERE `rule_id`='".$this->id."' AND `condition_id` NOT IN ('".
            implode("', '", array_keys($this->conditions))."')");
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     */
    public function toString()
    {
        return '';
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    private function load() {
        // Get generic data which is common to all subclasses of AdmissionRule.
        $stmt = DBManager::get()->prepare("SELECT * 
            FROM `admissionrules` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetchRow(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
        }
    }

} /* end of abstract class AdmissionRule */

?>