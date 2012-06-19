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
 * @author      Thomas Hackl, <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class AdmissionUserList
{
    // --- ATTRIBUTES ---

    /**
     * Unique identifier of this list.
     */
    private $id = '';

    /**
     * A factor for seat distribution algorithm ("1" means normal algorithm, 
     * everything between 0 and 1 decreases the chance to get a seat, 
     * everything above 1 increases it.)
     */
    private $factor = 1;

    /**
     * All user IDs that are on this list.
     */
    private $users = array();

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     * 
     * @param int factor Factor for "luck manipulation"
     * @param String id If this is an existing list, here is its ID.
     * @return This object.
     */
    public function AdmissionUserList($factor=1, $id='') {
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
     * Helper function for loading data from DB.
     */
    private function load() {
        // Load basic data.
        $stmt = DBManager::get()->prepare("SELECT * 
            FROM `admissionfactor` WHERE `list_id`=? LIMIT 1");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->factor = $current['factor'];
            // Load user IDs.
            $stmt2 = DBManager::get()->prepare("SELECT * 
                FROM `user_factor` WHERE `list_id`=?");
            $stmt2->execute(array($this->id));
            while ($user = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $this->users[$user['user_id']] = true;
            }
        }
    }

    /**
     * Helper function for loading data from DB.
     */
    private function store() {
    }

} /* end of class AdmissionUserList */

?>