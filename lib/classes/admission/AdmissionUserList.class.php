<?php

/**
 * AdmissionUserList.class.php
 * 
 * Contains users that get different probabilities than others in seat 
 * distribution algorithm.
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

class AdmissionUserList
{
    // --- ATTRIBUTES ---

    /**
     * Unique identifier of this list.
     */
    public $id = '';

    /**
     * A factor for seat distribution algorithm ("1" means normal algorithm, 
     * everything between 0 and 1 decreases the chance to get a seat, 
     * everything above 1 increases it.)
     */
    public $factor = 1;

    /**
     * Some name to display for this list.
     */
    public $name = '';

    /**
     * All user IDs that are on this list.
     */
    public $users = array();

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     * 
     * @param String id If this is an existing list, here is its ID.
     * @return This object.
     */
    public function __construct($id='') {
        $this->factor = $factor;
        if ($id) {
            $this->id = $id;
            $this->load();
        }
        return $this;
    }

    /**
     * Adds the given user to the list.
     *
     * @param  String userId
     * @return AdmissionUserList
     */
    public function addUser($userId)
    {
        $this->users[$userId] = true;
        return $this;
    }

    /**
     * Gets the currently set manipulation factor for this list.
     *
     * @return Integer
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * Gets the list name.
     *
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Helper function for loading data from DB.
     */
    public function load() {
        // Load basic data.
        $stmt = DBManager::get()->prepare("SELECT * 
            FROM `admissionfactor` WHERE `list_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->factor = $current['factor'];
            $this->name = $current['name'];
            // Load user IDs.
            $stmt2 = DBManager::get()->prepare("SELECT * 
                FROM `user_factorlist` WHERE `list_id`=?");
            $stmt2->execute(array($this->id));
            while ($user = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $this->users[$user['user_id']] = true;
            }
        }
    }

    /**
     * Removes the given user from the list.
     *
     * @param  String userId
     * @return AdmissionUserList
     */
    public function removeUser($userId)
    {
        unset($this->users[$userId]);
        return $this;
    }

    /**
     * Sets a factor.
     *
     * @param int $newFactor The new factor to be set.
     * @return AdmissionUserList
     */
    public function setFactor($newFactor)
    {
        $this->factor = $newFactor;
        return $this;
    }

    /**
     * Sets a name.
     *
     * @param  String $newName New list name.
     * @return AdmissionUserList
     */
    public function setName($newName)
    {
        $this->name = $newName;
        return $this;
    }

    /**
     * Function for storing the data to DB. Is not called automatically on 
     * changing object values.
     */
    public function store() {
        // Generate new ID if list doesn't exist in DB yet.
        if (!$this->id) {
            do {
                $newid = md5(uniqid('AdmissionUserList', true));
                $db = DBManager::get()->query("SELECT `list_id` 
                    FROM `admissionfactor` WHERE `list_id`='.$newid.'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        // Store basic list data.
        $stmt = DBManager::get()->prepare("INSERT INTO `admissionfactor` 
            (`list_id`, `name`, `factor`, `mkdate`, `chdate`) 
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
            `name`=VALUES(`name`), `factor`=VALUES(`factor`), 
            `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->name, $this->factor, 
            time(), time()));
        // Delete removed users from DB.
        // Clear all old user assignments to this list.
        DBManager::get()->exec("DELETE FROM `user_factorlist` WHERE `list_id`='".
            $this->id."' AND `user_id` NOT IN ('".
            implode("', '", array_keys($this->users))."')");
        // Store assigned users.
        $stmt = DBManager::get()->prepare("INSERT INTO `user_factorlist` 
            (`list_id`, `user_id`, `factor`, `mkdate`, `chdate`) 
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
            `name`=VALUES(`name`), `factor`=VALUES(`factor`), 
            `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($this->id, $this->name, $this->factor, time(), 
            time()));
        return $this;
    }

} /* end of class AdmissionUserList */
?>