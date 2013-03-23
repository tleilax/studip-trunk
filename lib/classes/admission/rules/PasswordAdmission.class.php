<?php

/**
 * PasswordAdmission.class.php
 * 
 * Represents a rule for course access with a given password.
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
require_once('vendor/phpass/PasswordHash.php');

class PasswordAdmission extends AdmissionRule
{

    // --- ATTRIBUTES ---
    /*
     * Password hasher (phpass library)
     */
    var $hasher = null;

    /*
     * Crypted password.
     */
    var $password = '';

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId
     */
    public function __construct($ruleId='')
    {
        $this->id = $ruleId;
        // Create a new bcrypt password hasher (exclude weaker algorithms).
        $this->hasher = new PasswordHash(8, false);
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('passwordadmissions');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `passwordadmissions` 
            WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective 
     * subclass) does.
     */
    public static function getDescription() {
        return _("Mit dieser Anmelderegel können Sie ein Passwort für Zugang ".
            "zu den zugeordneten Veranstaltungen vergeben. Die Anmeldung ist ".
            "dann nur für Personen möglich, die dieses Passwort kennen.");
    }

    /**
     * Shows an input form where the user can enter a password and try to get
     * past the holy gates.
     * 
     * @return String A template-based input form.
     */
    public function getInput() {
        $tpl = $GLOBALS['template_factory']->open('admission/rules/passwordadmission/input');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Anmeldung mit Passwort");
    }

    /**
     * Gets the bcrypted hash of the current password.
     * 
     * @return String
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     * 
     * @return String
     */
    public function getTemplate() {
        $tpl = $GLOBALS['template_factory']->open('admission/rules/passwordadmission/configure');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    public function load() {
        $stmt = DBManager::get()->prepare("SELECT * FROM `passwordadmissions`
            WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->password = $current['password'];
        }
    }

    /**
     * Does the current rule allow the given user to register as participant 
     * in the given course? Here, a given password (via the getInput method) is
     * compared to the stored encrypted one.
     *
     * @param  String userId
     * @param  String courseId
     * @return Boolean
     */
    public function ruleApplies($userId, $courseId)
    {
        return $this->hasher->CheckPassword(Request::get('pwarule_password'),
            $this->getPassword());
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
        parent::setAllData($data);
        $this->setPassword($data['password1']);
        return $this;
    }

    /**
     * Sets the password by bcrypting the given clear text password.
     * 
     * @param  String $clearText The clear text password to be set.
     * @return PasswordAdmission
     */
    public function setPassword($clearText) {
        $this->password = $this->hasher->HashPassword($clearText);
        return $this;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `passwordadmissions`
            (`rule_id`, `message`, `password`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `message`=VALUES(`message`), `password`=VALUES(`password`), 
            `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->message, $this->password, 
            time(), time()));
        return $this;
    }

    public function toString() {
        $tpl = $GLOBALS['template_factory']->open('admission/rules/passwordadmission/info');
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
        if (!$data['password1']) {
            $errors[] = _('Das Passwort darf nicht leer sein.');
        }
        if ($data['password1'] != $data['password2']) {
            $errors[] = _('Das Passwort stimmt nicht mit der Wiederholung überein.');
        }
        return $errors;
    }

} /* end of class PasswordAdmission */

?>