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
    private $endTime = 0;

    /**
     * Start of course admission.
     */
    private $startTime = 0;

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
            $this->generateId('admissiontimes');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `admissiontimes` 
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
        $stmt = DBManager::get()->prepare("INSERT INTO `admissiontimes` 
            (`rule_id`, `message`, `start_time`, `distribution_time`, 
            `end_time`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?) 
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
        $text = "";
        // Start time but no end time given.
        if ($this->startTime && !$this->endTime) {
            $text .= sprintf(_("Die Anmeldung ist möglich ab %s."), 
                date("d.m.Y, H:i", $this->startTime))."\n";
        // End time but no start time given.
        } else if (!$this->startTime && $this->endTime) {
            $text .= sprintf(_("Die Anmeldung ist möglich bis %s."), 
                date("d.m.Y, H:i", $this->endTime))."\n";
        // Start and end time given.
        } else if ($this->startTime && $this->endTime) {
            $text .= sprintf(_("Die Anmeldung ist möglich von %s bis %s."), 
                date("d.m.Y, H:i", $this->startTime), 
                date("d.m.Y, H:i", $this->endTime))."\n";
        }
        if ($this->distributionTime) {
            $text .= sprintf(_("Die Platzverteilung erfolgt am %s um %s."), 
                date("d.m.Y", $this->distributionTime),
                date("H:i", $this->distributionTime))."\n";
        }
        return $text;
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    private function load() {
        // Load basic data.
        parent::load();
        // Get TimedAdmission specific data.
        $stmt = DBManager::get()->prepare("SELECT * 
            FROM `admissiontimes` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetchRow(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->startTime = $current['start_time'];
            $this->distributionTime = $current['distribution_time'];
            $this->endTime = $current['end_time'];
        }
    }

} /* end of class TimedAdmission */

?>