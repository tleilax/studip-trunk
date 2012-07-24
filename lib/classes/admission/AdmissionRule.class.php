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
    public $id = '';

    /**
     * A message that is shown to users that are rejected for admission 
     * because of the current rule.
     */
    public $message = '';

    // --- OPERATIONS ---

    public function __construct($ruleId='') {
        $this->id = $ruleId;
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
    }

    /**
     * Generate a new unique ID.
     * 
     * @param  String tableName
     */
    public function generateId($tableName) {
        do {
            $newid = md5(uniqid(get_class($this).microtime(), true));
            $db = DBManager::get()->query("SELECT `rule_id` 
                FROM `".$tableName."` WHERE `rule_id`='.$newid.'");
        } while ($db->fetch());
        return $newid;
    }

    /**
     * Gets all users that are matched by thís rule.
     *
     * @return Array An array containing IDs of users who are matched by 
     *      this rule.
     */
    public function getAffectedUsers()
    {
        return array();
    }

    /**
     * Reads all available AdmissionRule subclasses and loads their definitions.
     */
    public static function getAvailableAdmissionRules() {
        $rules = array();
        // Load all PHP class files found in the admission rule folder.
        foreach (glob(realpath(dirname(__FILE__).'/rules').'/*.class.php') as $file) {
            require_once($file);
            // Try to auto-calculate class name from file name.
            $className = substr(basename($file), 0, 
                strpos(basename($file), '.class.php'));
            $current = new $className();
            // Check if class is right.
            if (is_subclass_of($current, 'AdmissionRule')) {
                $rules[$className] = array(
                        'name' => $className::getName(),
                        'description' => $className::getDescription()
                    );
            }
        }
        return $rules;
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective 
     * subclass) does.
     */
    public static function getDescription() {
        return _("Legt eine Regel fest, die erfüllt sein muss, um sich ".
            "erfolgreich zu einer Menge von Veranstaltungen anmelden zu ".
            "können.");
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
     * Return this rule's name.
     */
    public static function getName() {
        return _("Anmelderegel");
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    public function load() {
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

} /* end of abstract class AdmissionRule */

?>