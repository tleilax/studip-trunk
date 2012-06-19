<?php

/**
 * SemesterOfStudyCondition.class.php
 * 
 * All conditions concerning the semester of study in Stud.IP can be specified here.
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

require_once('ConditionField.class.php');

class SemesterOfStudyCondition extends ConditionField
{
    // --- OPERATIONS ---

    /**
     * Standard constructor.
     */
    public function SemesterOfStudyCondition($field_id='') {
        parent::ConditionField();
        // Initialize to some value in case there are no semester numbers.
        $maxsem = 15;
        // Calculate the maximal available semester.
        $stmt = DBManager::get()->query("SELECT MAX(`semester`) AS maxsem ".
            "FROM `user_studiengang`");
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maxsem = $current['maxsem'];
            for ($i=1 ; $i<$maxsem ; $i++) {
                $this->validValues[$i] = $i;
            }
        }
        $this->name = _("Fachsemester");
        if ($field_id) {
            $this->id = $field_id;
            $this->load();
        }
    }

    /**
     * Gets the value for the given user that is relevant for this
     * condition field. Here, this method looks up the semester of study 
     * for the user. If the user studies more than one subject, these values
     * can be different for each entry, so as additional context a subject
     * or a degree or both can be given.
     * 
     * @param  String $userId User to check.
     * @param  Array additional conditions that are required for check.
     * @return The value(s) for this user.
     */
    public function getUserValues($userId, $additional=null) {
        $result = array();
        $query = "SELECT DISTINCT `semester` ".
            "FROM `user_studiengang` ".
            "WHERE `user_id`=?"; 
        $parameters = array($userId);
        // Additional requirements given...
        if (is_array($additional)) {
            // .. such as subject of study...
            if ($array['studiengang_id']) {
                $query .= " AND studiengang_id=?";
                $parameters[] = $array['studiengang_id'];
            }
            // ... or degree.
            if ($array['abschluss_id']) {
                $query .= " AND abschluss_id=?";
                $parameters[] = $array['abschluss_id'];
            }
        }
        // Get semester of study for user.
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($parameters);
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $current['semester'];
        }
        return $result;
    }

} /* end of class SemesterOfStudyCondition */

?>