<?php

/**
 * TimedAdmission.class.php
 * 
 * Specifies a time frame for course admission.
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

class TimedAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * Timestamp for execution of seat distribution algorithm
     */
    public $distributionTime = 0;

    /**
     * End of course admission.
     */
    public $endTime = 0;

    /**
     * Start of course admission.
     */
    public $startTime = 0;

    // --- OPERATIONS ---

    /**
     * Standard constructor
     *
     * @param  String ruleId
     */
    public function __construct($ruleId='')
    {
        parent::__construct($ruleId);
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('timedadmissions');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `timedadmissions` 
            WHERE `rule_id`=?");
        $stmt->execute(array($this->id));
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective 
     * subclass) does.
     */
    public static function getDescription() {
        return _("Anmelderegeln dieses Typs legen ein Zeitfenster fest, in ".
            "dem die Anmeldung zu Veranstaltungen möglich ist. Es kann auch ".
            "nur ein Start- oder Endzeitpunkt angegeben werden.");
    }

    /**
     * Gets the time for seat distribution algorithm.
     *
     * @return int
     */
    public function getDistributionTime()
    {
        return $this->distributionTime;
    }

    /**
     * Gets the end of course admission.
     *
     * @return int
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Zeitgesteuerte Anmeldung");
    }

    /**
     * Gets the start of course admission.
     *
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     * 
     * @return String
     */
    public function getTemplate() {
        global $auth;
        $tpl = $GLOBALS['template_factory']->open('admission/rules/timedadmission/configure');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Helper function for loading rule definition from database.
     */
    public function load() {
        // Load data.
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `timedadmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->startTime = $current['start_time'];
            $this->distributionTime = $current['distribution_time'];
            $this->endTime = $current['end_time'];
        }
    }

    /**
     * Is admission allowed according to the defined time frame?
     *
     * @param  String userId
     * @param  String courseId
     * @return Boolean
     */
    public function ruleApplies($userId, $courseId) {
        $applies = true;
        // Start time given, but still in the future.
        if ($this->startTime && $this->startTime > time()) {
            $applies = false;
        }
        // End time given, but already past.
        if ($this->endTime && $this->endTime < time()) {
            $applies = false;
        }
        return $applies;
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
        if ($data['startdate']) {
            $sdate = $data['startdate'];
            $shour = $data['starthour'];
            $sminute = $data['startminute'];
            $parsed = date_parse($sdate.' '.$shour.':'.$sminute);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0, $parsed['month'], $parsed['day'], $parsed['year']);
            $this->setStartTime($timestamp);
        }
        if ($data['enddate']) {
            $edate = $data['enddate'];
            $ehour = $data['endhour'];
            $eminute = $data['endminute'];
            $parsed = date_parse($edate.' '.$ehour.':'.$eminute);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0, $parsed['month'], $parsed['day'], $parsed['year']);
            $this->setEndTime($timestamp);
        }
        if ($data['distributiondate']) {
            $ddate = $data['distributiondate'];
            $dhour = $data['distributionhour'];
            $dminute = $data['distributionminute'];
            $parsed = date_parse($ddate.' '.$dhour.':'.$dminute);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0, $parsed['month'], $parsed['day'], $parsed['year']);
            $this->setDistributionTime($timestamp);
        }
        return $this;
    }

    /**
     * Sets a new timestamp for seat distribution algorithm execution.
     *
     * @param  int newDistributionTime
     * @return TimedAdmission
     */
    public function setDistributionTime($newDistributionTime)
    {
        $this->distributionTime = $newDistributionTime;
        return $this;
    }

    /**
     * Sets a new end timestamp for course admission.
     *
     * @param  int newEndTime
     * @return TimedAdmission
     */
    public function setEndTime($newEndTime)
    {
        $this->endTime = $newEndTime;
        return $this;
    }

    /**
     * Sets a new start timestamp for course admission.
     *
     * @param  int newStartTime
     * @return TimedAdmission
     */
    public function setStartTime($newStartTime)
    {
        $this->startTime = $newStartTime;
        return $this;
    }

    /**
     * Store rule definition to database.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `timedadmissions` 
            (`rule_id`, `message`, `start_time`, `distribution_time`, 
            `end_time`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE `start_time`=VALUES(`start_time`), 
            `distribution_time`=VALUES(`distribution_time`), 
            `end_time`=VALUES(`end_time`), `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->message, $this->startTime, 
            $this->distributionTime, $this->endTime, time(), time()));
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     */
    public function toString()
    {
        $tpl = $GLOBALS['template_factory']->open('admission/rules/timedadmission/display');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

} /* end of class TimedAdmission */

?>