<?php
/**
 * Deputy.class.php
 * model class for table deputies
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string range_id database column
 * @property string user_id database column
 * @property string gruppe database column
 * @property string notification database column
 * @property string edit_about database column
 * @property User deputy belongs_to User
 * @property Course course belongs_to Course
 * @property User boss has_one User
 */
class Deputy extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'deputies';

        $config['belongs_to']['deputy'] = array(
            'class_name' => 'User',
            'foreign_key' => 'user_id'
        );
        $config['belongs_to']['course'] = array(
            'class_name' => 'Course',
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'seminar_id'
        );
        $config['has_one']['boss'] = array(
            'class_name' => 'User',
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'user_id'
        );

        $config['additional_fields']['vorname'] = array('deputy', 'vorname');
        $config['additional_fields']['nachname'] = array('deputy', 'nachname');
        $config['additional_fields']['username'] = array('deputy', 'username');
        $config['additional_fields']['boss_vorname'] = array('boss', 'vorname');
        $config['additional_fields']['boss_nachname'] = array('boss', 'nachname');
        $config['additional_fields']['course_name'] = array('course', 'name');
        $config['additional_fields']['course_number'] = array('course', 'veranstaltungsnummer');

        parent::configure($config);
    }

    function getDeputyFullname($format = 'full')
    {
        return $this->deputy->getFullname($format);
    }

    function getBossFullname($format = 'full')
    {
        if ($this->boss) {
            return $this->boss->getFullname($format);
        } else {
            return null;
        }
    }

    function getCourseFullname($format = 'default')
    {
        if ($this->course) {
            return $this->course->getFullname($format);
        } else {
            return null;
        }
    }

}
