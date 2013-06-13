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
        // Delete rule assignment to coursesets.
        $stmt = DBManager::get()->prepare("DELETE FROM `courseset_rule` 
            WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
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
     * 
     * @param  bool $activeOnly Show only active rules.
     * @return Array
     */
    public static function getAvailableAdmissionRules($activeOnly=true) {
        $rules = array();
        // Load all PHP class files found in the admission rule folder.
        /*foreach (glob(realpath(dirname(__FILE__).'/rules').'/*.class.php') as $file) {
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
        }*/
        $where = ($activeOnly ? " WHERE `active`=1" : "");
        $data = DBManager::get()->query("SELECT * FROM `admissionrules`".$where.
            " ORDER BY `id` ASC");
        while ($current = $data->fetch(PDO::FETCH_ASSOC)) {
            $className = $current['ruletype'];
            require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'].'admissionrules/'.
                strtolower($className).'/'.$className.'.class.php');
            $rules[$className] = array(
                    'name' => $className::getName(),
                    'description' => $className::getDescription()
                );
        }
        return $rules;
    }

    /**
     * Subclasses of AdmissionRule can require additional data to be entered on
     * admission (like PasswordAdmission which needs a password for course
     * access). Their corresponding method getInput only returns a HTML form
     * fragment as the output can be concatenated with output from other
     * rules.
     * This static method provides the frame for rendering a full HTML form
     * around the fragments from subclasses.
     * 
     * @return Array Start and end templates which wrap input form fragments
     *               from subclasses.
     */
    public static final function getInputFrame() {
        return array(
            $GLOBALS['template_factory']->open('admission/rules/input_start')->render(),
            $GLOBALS['template_factory']->open('admission/rules/input_end')->render()
        );
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
     * Gets the template that provides a configuration GUI for this rule.
     * 
     * @return String
     */
    public function getTemplate() {
        return '';
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
     * @return bool
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
     * Uses the given data to fill the object values. This can be used
     * as a generic function for storing data if the concrete rule type
     * isn't known in advance.
     * 
     * @param Array $data
     * @return AdmissionRule This object.
     */
    public function setAllData($data) {
        $this->message = $data['ajax'] ? utf8_decode($data['message']) : $data['message'];
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

    /**
     * Validates if the given request data is sufficient to configure this rule
     * (e.g. if required values are present).
     *
     * @param  Array Request data
     * @return Array Error messages.
     */
    public function validate($data)
    {
        return array();
    }

    /**
     * Standard string representation of this object.
     * 
     * @return String
     */
    public function __toString() {
        return $this->toString();
    }

} /* end of abstract class AdmissionRule */

?>