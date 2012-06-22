<?php

/**
 * CourseSet.class.php
 * 
 * Represents groups of Stud.IP courses that can be 
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

class CourseSet
{
    // --- ATTRIBUTES ---

    /**
     * Admission rules that are applied to the courses belonging to this set.
     */
    public $admissionRules = array();

    /**
     * Seat distribution algorithm.
     */
    public $algorithm = null;

    /**
     * IDs of courses that are aggregated into this set. The array is in the
     * form ($courseId1 => true, $courseId2 => true).
     */
    public $courses = array();

    /**
     * Unique identifier for this set.
     */
    public $id = '';

    /**
     * Lists of users that shall be treated differently in seat distribution 
     * (less or more chances).
     */
    public $userLists = array();

    // --- OPERATIONS ---

    public function __construct($setId='') {
        $this->id = $setId;
        if ($setId) {
            $this->load();
        }
    }

    /**
     * Adds the given admission rule to the list of rules for the course set.
     *
     * @param  AdmissionRule rule
     * @return CourseSet
     */
    public function addAdmissionRule($rule)
    {
        $this->admissionRules[$rule->getId()] = $rule;
        return $this;
    }

    /**
     * Adds the course with the given ID to the course set.
     *
     * @param  String courseId
     * @return CourseSet
     */
    public function addCourse($courseId)
    {
        $this->courses[$courseId] = true;
        return $this;
    }

    /**
     * Adds a bunch of courses to the course set. The array must be in the form
     * ($index1 => $courseId1, $index2 => $courseId2);
     *
     * @param  String courseId
     * @return CourseSet
     */
    public function addCourses($courses)
    {
        // Merge given array with current courses after bringing the given 
        // array in the correct form. 
        array_merge($this->courses, 
            array_fill_keys(array_flip($courses), true));
        return $this;
    }

    /**
     * Adds a UserList to the course set. The list contains several users and a 
     * factor that changes seat distribution chances for these users;
     *
     * @param  AdmissionUserList list
     * @return CourseSet
     */
    public function addUserList($list)
    {
        $this->userLists[$list->getId()] = $list;
        return $this;
    }

    /**
     * Removes all courses from this course set.
     *
     * @return CourseSet
     */
    public function clearCourses()
    {
        $this->courses = array();
        return $this;
    }

    /**
     * Get all admission rules belonging to the course set.
     *
     * @return Array
     */
    public function getAdmissionRules()
    {
        return $this->admissionRules;
    }

    /**
     * Get the currently used distribution algorithm.
     *
     * @return AdmissionAlgorithm
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * How many users will be allowed to register according to the defined 
     * rules? This can help in estimating whether the combination of the 
     * defined rules makes sense.
     *
     * @return int
     */
    public function getAllowedUserCount()
    {
        $users = array();
        foreach ($this->admissionRules as $rule) {
            $users = array_merge($users, $rule->getAffectedUsers());
        }
        return $sizeof($users);
    }

    /**
     * Gets the course IDs belonging to the course set.
     *
     * @return Array
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * Get the identifier of the course set.
     *
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retrieves the priorities given to the courses in this set.
     *
     * @return Array
     */
    public function getPriorities()
    {
        return AdmissionPriority::getPriorities($this->id);
    }

    /**
     * Gets all course sets the given course belongs to.
     *
     * @param  String courseId
     * @return Array
     */
    public static function getSetsForCourse($courseId)
    {
        $sets = array();
        $stmt = DBManager::get()->prepare("SELECT `set_id` 
            FROM `seminar_set`WHERE `course_id`=?");
        $stmt->execute(array($courseId));
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $entry) {
            $current = new CourseSet($entry['set_id']);
            $sets[] = $current;
        }
        return $sets;
    }

    /**
     * Retrieves the lists of users that are considered specially in 
     * seat distribution.
     *
     * @return Array
     */
    public function getUserLists()
    {
        return $this->userLists;
    }

    /**
     * Removes the course with the given ID from the set. 
     *
     * @param  String courseId
     * @return CourseSet
     */
    public function removeCourse($courseId)
    {
        unset($this->courses[$courseid]);
        return $this;
    }

    /**
     * Removes the rule with the given ID from the set.
     *
     * @param  String ruleId
     * @return CourseSet
     */
    public function removeAdmissionRule($ruleId)
    {
        unset($this->admissionRules[$ruleId]);
        return $this;
    }

    /**
     * Sets a seat distribution algorithm for this course set. This will only
     * have an effect in conjunction with a TimedAdmission, as the algorithm 
     * needs a defined point in time where it will start.
     *
     * @param  AdmissionAlgorithm newAlgorithm
     * @return CourseSet
     */
    public function setAlgorithm($newAlgorithm)
    {
        $this->algorithm = $newAlgorithm;
        return $this;
    }

} /* end of class CourseSet */

?>