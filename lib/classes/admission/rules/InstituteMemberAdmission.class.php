<?php

/**
 * InstituteMemberAdmission.class.php
 * 
 * Allows admission only for people that belong to one or more given institute.
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

class InstituteMemberAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * Institutes that possible participants have to belong to.
     */
    public $institutes = 1;

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId
     * @return InstituteMemberAdmission
     */
    public function __construct($ruleId='')
    {
        $this->id = $ruleId;
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('instadmissions');
        }
    }

    /**
     * Adds the given institute ID to the list of allowed institutes.
     *
     * @param  String instituteId
     * @return InstituteMemberAdmission
     */
    public function addInstitute($instituteId)
    {
        $this->institutes[$institute_id] = true;
        return $this;
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `instadmissions` 
            WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective 
     * subclass) does.
     */
    public static function getDescription() {
        return _("Diese Art von Anmelderegel lässt eine Anmeldung nur für ".
            "Personen zu, die zu einer oder mehreren ausgewählten ".
            "Einrichtungen gehören.");
    }

    /**
     * Gets the maximal number of courses that users can be registered for.
     *
     * @return Integer
     */
    public function getInstitutes()
    {
        return $this->institutes;
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Anmeldung nur mit Einrichtungszugehörigkeit");
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     * 
     * @return String
     */
    public function getTemplate() {
        $tpl = $GLOBALS['template_factory']->open('admission/rules/institutememberadmission/configure');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    public function load() {
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `instadmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
        }
        // Get allowed institutes.
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `instadmission_institute` WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entry) { 
            $this->institutes[$entry['institute_id']] = true;
        }
    }

    /**
     * Does the current rule allow the given user to register as participant 
     * in the given course? That only happens when the user has been assigned
     * as member of the allowed institutes.
     *
     * @param  String userId
     * @param  String courseId
     * @return Boolean
     */
    public function ruleApplies($userId, $courseId)
    {
        $applies = false;
        // How many courses from this set has the user already registered for?
        $stmt = DBManager::get()->prepare("SELECT COUNT(`user_id`) AS number
            FROM `user_inst` WHERE `user_id`=? AND `Institute_id` IN ('".
            explode(array_keys($this->institutes))."') AND `inst_perms` IN ".
            "('autor', 'tutor', 'dozent')");
        $stmt->execute(array($userId));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($data['number'] > 0);
    }

    /**
     * Uses the given data to fill the object values. This can be used
     * as a generic function for storing data if the concrete rule type
     * isn't known in advance.
     * 
     * @param Array $data
     * @return InstituteMemberAdmission
     */
    public function setAllData($data) {
        parent::setAllData($data);
        foreach ($data['institutes'] as $institute) {
            $this->institutes[$institute] = true;
        }
        return $this;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `instadmissions`
            (`rule_id`, `message`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
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
        $tpl = $GLOBALS['template_factory']->open('admission/rules/institutememberadmission/info');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
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
        $errors = parent::validate($data);
        if (!$data['institutes']) {
            $errors[] = _('Bitte geben Sie mindestens eine Einrichtung an, ".
                "deren Mitglieder sich anmelden dürfen.');
        }
        return $errors;
    }

} /* end of class InstituteMemberAdmission */

?>