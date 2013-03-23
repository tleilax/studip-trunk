<?php

/**
 * LockedAdmission.class.php
 * 
 * Represents a rule for completely locking courses for admission.
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

class LockedAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId
     */
    public function __construct($ruleId='')
    {
        $this->id = $ruleId;
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('lockedadmissions');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `lockedadmissions` 
            WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective 
     * subclass) does.
     */
    public static function getDescription() {
        return _("Diese Art von Anmelderegel sperrt die Anmeldung ".
            "zu allen zugehörigen Veranstaltungen, sodass sich niemand ".
            "eintragen kann.");
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Anmeldung gesperrt");
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     * 
     * @return String
     */
    public function getTemplate() {
        $tpl = $GLOBALS['template_factory']->open('admission/rules/lockedadmission/configure');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    public function load() {
        $stmt = DBManager::get()->prepare("SELECT * FROM `lockedadmissions`
            WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
        }
    }

    /**
     * Does the current rule allow the given user to register as participant 
     * in the given course? Never happens here as admission is completely
     * locked.
     *
     * @param  String userId
     * @param  String courseId
     * @return Boolean
     */
    public function ruleApplies($userId, $courseId)
    {
        // YOU CANNOT PASS!
        return false;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `lockedadmissions`
            (`rule_id`, `message`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `message`=VALUES(`message`), `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->message, time(), time()));
        return $this;
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     */
    public function toString() {
        $tpl = $GLOBALS['template_factory']->open('admission/rules/lockedadmission/info');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

} /* end of class LockedAdmission */

?>