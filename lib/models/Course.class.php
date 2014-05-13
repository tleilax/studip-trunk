<?php
/**
 * Course.class.php
 * model class for table seminare
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Andr� Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string seminar_id database column
 * @property string id alias column for seminar_id
 * @property string veranstaltungsnummer database column
 * @property string institut_id database column
 * @property string name database column
 * @property string untertitel database column
 * @property string status database column
 * @property string beschreibung database column
 * @property string ort database column
 * @property string sonstiges database column
 * @property string lesezugriff database column
 * @property string schreibzugriff database column
 * @property string start_time database column
 * @property string duration_time database column
 * @property string art database column
 * @property string teilnehmer database column
 * @property string vorrausetzungen database column
 * @property string lernorga database column
 * @property string leistungsnachweis database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string ects database column
 * @property string admission_turnout database column
 * @property string admission_binding database column
 * @property string admission_prelim database column
 * @property string admission_prelim_txt database column
 * @property string admission_disable_waitlist database column
 * @property string visible database column
 * @property string showscore database column
 * @property string modules database column
 * @property string aux_lock_rule database column
 * @property string aux_lock_rule_forced database column
 * @property string lock_rule database column
 * @property string admission_waitlist_max database column
 * @property string admission_disable_waitlist_move database column
 * @property string end_time computed column read/write
 * @property SimpleORMapCollection members has_many CourseMember
 * @property SimpleORMapCollection statusgruppen has_many Statusgruppen
 * @property SimpleORMapCollection admission_applicants has_many AdmissionApplication
 * @property SimpleORMapCollection datafields has_many DatafieldEntryModel
 * @property SimpleORMapCollection cycles has_many SeminarCycleDate
 * @property Semester start_semester belongs_to Semester
 * @property Semester end_semester belongs_to Semester
 * @property Institute home_institut belongs_to Institute
 * @property AuxLockRule aux belongs_to AuxLockRule
 * @property SimpleORMapCollection study_areas has_and_belongs_to_many StudipStudyArea
 * @property SimpleORMapCollection institutes has_and_belongs_to_many Institute
 */

class Course extends SimpleORMap
{
    /**
     * Returns the currently active course or false if none is active.
     *
     * @return mixed Course object of currently active course, false otherwise
     * @since 3.0
     */
    public static function findCurrent()
    {
        return empty($GLOBALS['SessSemName'][1])
            ? null
            : Course::find($GLOBALS['SessSemName'][1]);
    }

    protected static function configure($config = null)
    {
        $config = array();
        $config['db_table'] = 'seminare';
        $config['has_many'] = array(
            'members' => array(
                    'class_name' => 'CourseMember',
                    'on_delete' => 'delete',
                    'on_store' => 'store'),
            'statusgruppen' => array(
                    'class_name' => 'Statusgruppen',
                    'on_delete' => 'delete',
                    'on_store' => 'store'),
            'admission_applicants' => array(
                    'class_name' => 'AdmissionApplication',
                    'on_delete' => 'delete',
                    'on_store' => 'store'),
            'datafields' => array(
                    'class_name' => 'DatafieldEntryModel',
                    'assoc_foreign_key' =>
                        function($model,$params) {
                $model->setValue('range_id', $params[0]->id);
            },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
            function($course) {
                return array($course);
            }),
            'cycles' => array(
                    'class_name' => 'SeminarCycleDate',
                    'assoc_func' => 'findBySeminar',
                    'on_delete' => 'delete',
                    'on_store' => 'store'),
        );

        $config['belongs_to'] = array(
            'start_semester' => array(
                'class_name' => 'Semester',
                'foreign_key' => 'start_time',
                'assoc_func' => 'findByTimestamp',
                'assoc_foreign_key' => 'beginn'),
            'end_semester' => array(
                'class_name' => 'Semester',
                'foreign_key' => 'end_time',
                'assoc_func' => 'findByTimestamp',
                'assoc_foreign_key' => 'beginn'),
            'home_institut' => array(
                'class_name' => 'Institute',
                'foreign_key' => 'institut_id',
                'assoc_func' => 'find'),
            'aux' => array(
                'class_name' => 'AuxLockRule',
                'foreign_key' => 'aux_lock_rule')
        );
        $config['has_and_belongs_to_many'] = array(
            'study_areas' => array(
                'class_name' => 'StudipStudyArea',
                'thru_table' => 'seminar_sem_tree',
                'on_delete' => 'delete', 'on_store' => 'store'),
            'institutes' => array(
                'class_name' => 'Institute',
                'thru_table' => 'seminar_inst',
                'on_delete' => 'delete', 'on_store' => 'store'));
        $config['default_values']['beschreibung'] = '';
        $config['default_values']['lesezugriff'] = 1;
        $config['default_values']['schreibzugriff'] = 1;
        $config['default_values']['duration_time'] = 0;
        $config['default_values']['admission_endtime'] = -1;

        $config['additional_fields']['end_time'] = true;
        $config['notification_map']['after_create'] = 'CourseDidCreateOrUpdate CourseDidCreate';
        $config['notification_map']['after_store'] = 'CourseDidCreateOrUpdate CourseDidUpdate';
        $config['notification_map']['before_create'] = 'CourseWillCreate';
        $config['notification_map']['before_store'] = 'CourseWillUpdate';
        $config['notification_map']['after_delete'] = 'CourseDidDelete';
        $config['notification_map']['before_delete'] = 'CourseWillDelete';
        parent::configure($config);
    }

    function getEnd_Time()
    {
        return $this->duration_time == -1 ? -1 : $this->start_time + $this->duration_time;
    }

    function setEnd_Time($value)
    {
        if ($value == -1) {
            $this->duration_time = -1;
        } else if (($this->start_time > 0)  && ($value > $this->start_time)) {
            $this->duration_time = $value - $this->start_time;
        } else {
            $this->duration_time = 0;
        }
    }

    function getFreeSeats()
    {
        $free_seats = $this->admission_turnout - $this->getNumParticipants();
        return $free_seats > 0 ? $free_seats : 0;
    }

    function isWaitlistAvailable()
    {
        if ($this->admission_disable_waitlist) {
            return false;
        } else if ($this->admission_waitlist_max) {
            return ($this->admission_waitlist_max - $this->getNumWaiting()) > 0 ? true : false;
        } else {
            return true;
        }
    }

    /**
     * Retrieves all members of a status
     *
     * @param String|Array $status  the status to filter with
     *
     * @return Array an array of all those members.
     */
    function getMembersWithStatus($status)
    {
        return CourseMember::findBySQL('seminar_id = ? AND status = ? ORDER BY position', array($this->id, $status));
    }

    /**
     * Retrieves the number of all members of a status
     *
     * @param String|Array $status  the status to filter with
     *
     * @return int the number of all those members.
     */
    function countMembersWithStatus($status)
    {
        return CourseMember::countBySql('seminar_id = ? AND status = ? ORDER BY position', array($this->id, $status));
    }

    function getNumParticipants()
    {
        return $this->members->findBy('status', words('user autor'))->count() + $this->getNumPrelimParticipants();
    }

    function getNumPrelimParticipants()
    {
        return $this->admission_applicants->findBy('status', 'accepted')->count();
    }

    function getNumWaiting()
    {
        return $this->admission_applicants->findBy('status', 'awaiting')->count();
    }

    function getParticipantStatus($user_id)
    {
        $p_status = $this->members->findBy('user_id', $user_id)->val('status');
        if (!$p_status) {
            $p_status = $this->admission_applicants->findBy('user_id', $user_id)->val('status');
        }
        return $p_status;
    }
}
