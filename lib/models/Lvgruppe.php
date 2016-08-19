<?php
/**
 * Lvgruppe.php
 * model class for Lehrveranstaltungsgruppen (table mvv_lvgruppe)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class Lvgruppe extends ModuleManagementModelTreeItem
{

    private $count_seminare;
    private $count_archiv;
    private $count_modulteile;
    private $count_semester;
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_lvgruppe';
        
        $config['has_and_belongs_to_many']['modulteile'] = array(
            'class_name' => 'Modulteil',
            'thru_table' => 'mvv_lvgruppe_modulteil',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_and_belongs_to_many']['courses'] = array(
            'class_name' => 'Course',
            'thru_table' => 'mvv_lvgruppe_seminar',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_and_belongs_to_many']['archived_courses'] = array(
            'class_name' => 'ArchivedCourse',
            'thru_table' => 'mvv_lvgruppe_seminar',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        
        $config['additional_fields']['count_seminare']['get'] =
            function($lvgruppe) { return $lvgruppe->count_seminare; };
        $config['additional_fields']['count_seminare']['set'] = false;
        $config['additional_fields']['count_archiv']['get'] =
            function($lvgruppe) { return $lvgruppe->count_archiv; };
        $config['additional_fields']['count_archiv']['set'] = false;
        $config['additional_fields']['count_modulteile']['get'] =
            function($lvgruppe) { return $lvgruppe->count_modulteile; };
        $config['additional_fields']['count_modulteile']['set'] = false;
        $config['additional_fields']['count_semester']['get'] =
            function($lvgruppe) { return $lvgruppe->count_semester; };
        $config['additional_fields']['count_semester']['set'] = false;
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Lehrveranstaltungsgruppe');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Lehrveranstaltungsgruppe');
    }
    
    /**
     * Returns all or a specified (by row count and offset) number of
     * Abschluesse sorted and filtered by given parameters and enriched with
     * some additional fields. This function is mainly used in the list view.
     * 
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @param string An id of a semester to restrict the result to groups
     * assigned to courses span the semesters time range.
     * @return object A SimpleORMapCollection of Abschluss objects.
     */
    public static function getAllEnriched($sortby = 'name', $order = 'ASC',
            $filter = null, $row_count = null, $offset = null,
            $semester_id = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'name',
                array('count_seminare', 'count_modulteile', 'count_archiv'));
        $params = array();
        if (!is_null($filter)) {
            $filter_sql = is_array($filter)
                    ? self::getFilterSql($filter, true) : $filter;
        } else {
            $filter_sql = '';
        }
        // get result filtered by a given semester
        $semester_join = '';
        if ($semester_id) {
            $semester = Semester::find($semester_id);
            if ($semester) {
                if (trim($filter_sql)) {
                    $filter_sql .= ' AND';
                } else {
                    $filter_sql .= ' WHERE';
                }
                $filter_sql = trim($filter_sql) ? $filter_sql  : ' AND';
                
                $filter_sql .= ' ((seminare.duration_time = -1 '
                        . 'AND seminare.start_time < :beginn) '
                        . 'OR (seminare.duration_time > 0 '
                        . 'AND seminare.start_time < :beginn '
                        . 'AND seminare.start_time + seminare.duration_time >= :ende) '
                        . 'OR (seminare.start_time = :beginn)) '
                        . 'AND (start_sem.beginn <= :beginn AND '
                        . 'IF(ISNULL(end_sem.ende), 1, end_sem.ende > :beginn)) ';
                $params = array(':beginn' => $semester->beginn,
                    ':ende' => $semester->ende);
                
                $semester_join = 'LEFT JOIN mvv_modul ON mvv_modul.modul_id = mvv_modulteil.modul_id '
                . 'LEFT JOIN semester_data as start_sem ON start_sem.semester_id = mvv_modul.start '
                . 'LEFT JOIN semester_data as end_sem ON end_sem.semester_id = mvv_modul.end ';
            }
        }
        
        $query = 'SELECT mvv_lvgruppe.*, '
                . 'COUNT(DISTINCT seminare.seminar_id) AS `count_seminare`, '
                . 'COUNT(DISTINCT archiv.seminar_id) AS `count_archiv`, '
                . 'COUNT(DISTINCT modulteil_id) AS `count_modulteile` '
                . 'FROM mvv_lvgruppe '
                . 'LEFT JOIN mvv_lvgruppe_seminar USING(lvgruppe_id) '
                . 'LEFT JOIN archiv ON mvv_lvgruppe_seminar.seminar_id = archiv.seminar_id '
                . 'LEFT JOIN seminare ON mvv_lvgruppe_seminar.seminar_id = seminare.seminar_id '
                . 'LEFT JOIN mvv_lvgruppe_modulteil USING(lvgruppe_id) '
                . 'LEFT JOIN mvv_modulteil USING(modulteil_id) '
                . 'LEFT JOIN mvv_modul_inst USING(modul_id) '
                . $semester_join
                . $filter_sql
                . 'GROUP BY lvgruppe_id '
                . 'ORDER BY ' . $sortby;
        return parent::getEnrichedByQuery($query, $params, $row_count, $offset);
    }
    
    /**
     * Rturns the number of LV-Gruppen optionally reduced by
     * filter criteria.
     * 
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result.
     * @param string An id of a semester to restrict the result to groups
     * assigned to courses span the semesters time range.
     * @return type
     */
    public static function getCount($filter = null /*, $semester_id = null*/)
    {
        if ($filter) {
            $filter_sql = self::getFilterSql($filter, true);
        } else {
            $filter_sql = '';
        }
        $num_args = func_num_args();
        // get result filtered by a given semester
        if ($num_args == 2) {
            $semester_id = func_get_arg(1);
            $semester = Semester::find($semester_id);
            if ($semester) {
                if (trim($filter_sql)) {
                    $filter_sql .= ' AND';
                } else {
                    $filter_sql .= ' WHERE';
                }
                $filter_sql = trim($filter_sql) ? $filter_sql  : ' AND';
                $filter_sql .= ' (seminare.start_time >= ? '
                        . 'AND seminare.start_time <= ?) '
                        . 'OR (seminare.start_time <= ? AND seminare.start_time '
                        . '+ seminare.duration_time >= ?) ';
                $params = array($semester->beginn, $semester->ende,
                    $semester->beginn, $semester->ende);
            }
        }
        $query = 'SELECT COUNT(DISTINCT(mvv_lvgruppe.lvgruppe_id)) '
                . 'FROM mvv_lvgruppe '
                . 'LEFT JOIN mvv_lvgruppe_seminar USING(lvgruppe_id) '
                . 'LEFT JOIN seminare ON mvv_lvgruppe_seminar.seminar_id = seminare.seminar_id '
                . 'LEFT JOIN mvv_lvgruppe_modulteil USING(lvgruppe_id) '
                . 'LEFT JOIN mvv_modulteil USING(modulteil_id) '
                . 'LEFT JOIN mvv_modul_inst USING(modul_id) '
                . $filter_sql;
        $db = DBManager::get()->prepare($query);
        $db->execute($params);
        return $db->fetchColumn(0);
    }
    
    /**
     * Retrieves all LV-Gruppen by given search term. The term is compared with
     * the name of the LV-Gruppe, the code and the name of related modules
     * 
     * @param string $term The search term.
     * @param null $filter Not used.
     * @return object A SimpleORMapCollection of LV-Gruppen.
     */
    public static function findBySearchTerm($term, $filter = null)
    {
        $term = '%' . $term . '%';
        return parent::getEnrichedByQuery(
                "SELECT mlg.*, GROUP_CONCAT(DISTINCT mm.modul_id SEPARATOR ',') "
                . 'AS `assigned_modul_ids` '
                . 'FROM mvv_lvgruppe mlg '
                . 'INNER JOIN mvv_lvgruppe_modulteil USING(lvgruppe_id) '
                . 'INNER JOIN mvv_modulteil USING(modulteil_id) '
                . 'INNER JOIN mvv_modul mm USING(modul_id) '
                . 'INNER JOIN mvv_modul_deskriptor mmd USING(modul_id)'
                . 'WHERE mlg.name LIKE :search_term OR mm.code LIKE :search_term '
                . 'OR mmd.bezeichnung LIKE :search_term '
                //. 'OR mmd.bezeichnung_kurz LIKE :search_term '
                . 'GROUP BY lvgruppe_id '
                . 'ORDER BY `name`', array(':search_term' => $term));
    }
    
    /**
     * Retrieves all LV-Gruppen related to the Modulteil with given id. 
     * 
     * @param string $modulteil_id The id of a Modulteil.
     * @return object A SimpleORMapCollection of LV-Gruppen.
     */
    public static function findByModulteil($modulteil_id)
    {
        return parent::getEnrichedByQuery('SELECT mlg.* '
                . 'FROM mvv_lvgruppe mlg '
                . 'INNER JOIN mvv_lvgruppe_modulteil mlm USING(lvgruppe_id) '
                . 'WHERE mlm.modulteil_id = ? '
                . 'ORDER BY `position`,`mkdate`', array($modulteil_id));
    }
    
    /**
     * Retrieves all LV-Gruppen related to the course with given id. 
     * 
     * @param string $seminar_id The id of a course.
     * @return object A SimpleORMapCollection of LV-Gruppen.
     */
    public static function findBySeminar($seminar_id)
    {
        return parent::getEnrichedByQuery('SELECT mlg.* '
                . 'FROM mvv_lvgruppe mlg '
                . 'INNER JOIN mvv_lvgruppe_seminar mls USING(lvgruppe_id) '
                . 'WHERE mls.seminar_id = ? '
                . 'ORDER BY `name`', array($seminar_id));
    }
    
    /**
     * Returns all institutes assigned to Module.
     * 
     * @param string $sortby DB field to sort by.
     * @param string $order ASC or DESC
     * @param array $filter Array of filter.
     * @return array Array of found Fachbereiche.
     */
    public static function getAllAssignedInstitutes($sortby = 'name',
            $order = 'ASC', $filter = null, $row_count = null, $offset = null)
    {
        $sortby = Fachbereich::createSortStatement($sortby, $order, 'name',
                array('count_module'));
        return Fachbereich::getEnrichedByQuery('SELECT Institute.*, '
                . 'Institute.Name as `name`, '
                . 'Institute.Institut_id AS institut_id, '
                . 'COUNT(DISTINCT(lvgruppe_id)) as count_objects '
                . 'FROM mvv_lvgruppe_modulteil '
                . 'INNER JOIN mvv_modulteil USING(modulteil_id) '
                . 'INNER JOIN mvv_modul_inst USING(modul_id) '
                . 'INNER JOIN Institute '
                . 'ON mvv_modul_inst.institut_id = Institute.Institut_id '
                . Fachbereich::getFilterSql($filter, true)
                . 'GROUP BY institut_id ORDER BY ' . $sortby
                , array(), $row_count, $offset);
    }
    
    /**
     * Assigns the given seminar to this lvgruppe.
     * 
     * @param string $seminar_id
     * @return int|boolean
     */
    public function addSeminar($seminar_id)
    {
        return LvgruppeSeminar::get(array($this->getId(), $seminar_id))
                ->store();
    }
    
    /**
     * Assigns the given course to the given LvGruppen.
     * 
     * @param array Array of ids
     * @return int The number of assigned LvGruppen.
     */
    public static function setLvgruppen($seminar_id, $lvgruppen_ids)
    {
        $old = Lvgruppe::findBySeminar($seminar_id);
        $removed = array_diff($old->pluck('id'), $lvgruppen_ids);
        $added = array_diff($lvgruppen_ids, $old->pluck('id'));
        foreach ($removed as $one) {
            $count_removed += $old->findOneBy('id', $one)->removeSeminar($seminar_id);
        }
        foreach ($added as $one) {
            $count_added += Lvgruppe::get($one)->addSeminar($seminar_id);
        }
        return count($old) + $count_added - $count_removed;
    }
    
    /**
     * Removes the seminar from this Lvgruppe.
     * 
     * @param type $seminar_id
     * @return boolean Always true...
     */
    public function removeSeminar($seminar_id)
    {
        return LvgruppeSeminar::get(array($this->getId(), $seminar_id))
                ->delete();
    }
    
    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return ($_SESSION['MVV/Lvgruppe/trail_parent_id']);
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Modul::get($this->getTrailParent_id());
    }
    
    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        return array();
    }
    
    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return false;
    }
    
    /**
     * @see MvvTreeItem::isAssignable()
     */
    public function isAssignable()
    {
        return true;
    }
    
    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
         return Modulteil::findByLvgruppe($this->getId());
    }
    
    /**
     * Retrieves corses this LV-Gruppe is assigned to. Filtered by a given
     * semester.
     * 
     * @param string $semester_id The id of a semester.
     * @return array An array of course data.
     */
    public function getAssignedCoursesBySemester($semester_id)
    {
        $semester = Semester::find($semester_id);
        if ($semester) {
            $stmt = DBManager::get()->prepare('SELECT seminar_id, Name, '
                . 'VeranstaltungsNummer FROM seminare sem '
                . 'LEFT JOIN mvv_lvgruppe_seminar mls USING(seminar_id) '
                . 'WHERE mls.lvgruppe_id = :id '
                . 'AND ((sem.start_time <= :semester_beginn '
                . 'AND sem.start_time + sem.duration_time >= :semester_beginn) '
                . 'OR (sem.start_time BETWEEN :semester_beginn AND :semester_ende) '
                . 'OR (sem.start_time <= :semester_beginn AND sem.duration_time = -1)) '
                . 'AND sem.visible = 1 ');
            $stmt->execute(array(
                ':id' => $this->getId(),
                ':semester_beginn' => $semester->beginn,
                ':semester_ende' => $semester->ende
            ));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return array();
    }
    
    /**
     * Returns all courses assigned to this LV-Gruppe grouped by semesters.
     * 
     * @param bool $only_visible Return only visible courses.
     * @param string $semester_id Return only this semester.
     * @return array All assigned courses grouped by semesters.
     */
    public function getAllAssignedCourses($only_visible = false, $semester_id = null)
    {   
        $sem_start_times = array();
        
        if ($semester_id) {
            $semester = Semester::find($semester_id);
            if (!$semester) {
                return array();
            }
            $sem_start_times[$semester->id] = $semester->beginn;
        } else {
            $sem_start_times = SimpleORMapCollection::createFromArray(
                    Semester::getAll())->toGroupedArray('id', 'beginn');
            $sem_start_times = array_map(
                    function ($sem) { return $sem['beginn']; }
                    , $sem_start_times);
        }
        $visible_sql = $only_visible ? ' AND visible = 1' : '';
        $courses = array();
        $stmt = DBManager::get()->prepare('SELECT seminar_id, Name, '
                . 'VeranstaltungsNummer, visible, INTERVAL(start_time,'
                . join(',', $sem_start_times)
                . ') AS sem_number, '
                . 'IF(duration_time=-1,' . count($sem_start_times)
                . ',INTERVAL(start_time+duration_time,'
                . join(',', $sem_start_times)
                . ')) AS sem_number_end FROM seminare '
                . 'INNER JOIN mvv_lvgruppe_seminar USING(seminar_id) '
                . 'WHERE lvgruppe_id = ? ' . $visible_sql
                . ' AND start_time <= ' . end($sem_start_times)
                . ' ORDER BY sem_number DESC, Name');
        $stmt->execute(array($this->getId()));
        $sem_ids = array_keys($sem_start_times);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $course) {
            if ($course['sem_number'] == 0) $course['sem_number'] = 1;
            for ($i = $course['sem_number']; $i <= $course['sem_number_end']; $i++) {
                $courses[$sem_ids[$i-1]][] = $course;
            }
        }
        return $courses;
    }
    
    /**
     * Returns all archived courses previously assigned to this LV-Gruppe.
     * 
     * @return array All archived courses.
     */
    public function getArchivedCourses()
    {
        $stmt = DBManager::get()->prepare('SELECT seminar_id, name, semester '
                . 'FROM mvv_lvgruppe_seminar '
                . 'INNER JOIN archiv USING(seminar_id) '
                . 'WHERE lvgruppe_id = ? '
                . 'ORDER BY start_time ASC');
        $stmt->execute(array($this->getId()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Returns a default name for a new LV-Gruppe derived from a given
     * modulteil.
     * 
     * @param string $modulteil_id The id of a Modulteil.
     * @return string The default name.
     */
    public static function getDefaultName($modulteil_id)
    {
        $name = '';
        /*
         * Gie�en
        $modul = Modul::find($modul_id);
        if ($modul) {
            $name = $modul->responsible_institute->institute->getShortName();
            $short_name_modul = $modul->getDeskriptor(
                    $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default'])
                    ->bezeichnung_kurz;
            $name .= $short_name_modul ? ' ' . $short_name_modul : '';
        }
         * 
         */
        // Augsburg
        $modulteil = Modulteil::find($modulteil_id);
        if ($modulteil) {
            $name = $modulteil->getDeskriptor()->bezeichnung;
            //$name = $name_modulteil ? ' ' . $name_modulteil : '';
        }
        return $name;
    }
    
    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = array();
            $rejected = false;
            
            // The name of the Fach must be longer than 4 characters
            if (strlen($this->name) < 4) {
                $ret['name'] = true;
                $messages[] = _('Der Name der Lehrveranstaltungsgruppe ist zu kurz (mindestens 4 Zeichen).');
                $rejected = true;
            } else {
                if ($this->isNew()) {
                    // The name of the Lehrveranstaltungsgruppe has to be unique
                    $existing = $this->findBySql('name = ' . DBManager::get()->quote($this->name));
                    if (sizeof($existing)) {
                        $ret['name'] = true;
                        $messages[] = sprintf(_('Es existiert bereits eine Lehrveranstaltungsgruppe mit dem Namen "%s"!'),
                                $this->name);
                        $rejected = true;
                    }
                }
            }
            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }
    
}