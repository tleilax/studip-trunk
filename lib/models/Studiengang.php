<?php
/**
 * Studiengang.php
 * Model class for Studiengaenge (table mvv_studiengang)
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

class Studiengang extends ModuleManagementModelTreeItem
{
    
    private $count_dokumente;
    private $count_faecher;
    private $institut_name;
    private $kategorie_name;
    private $count_module;
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_studiengang';
        
        $config['belongs_to']['abschluss'] = array(
            'class_name' => 'Abschluss',
            'foreign_key' => 'abschluss_id'
        );
        $config['has_and_belongs_to_many']['studiengangteile'] = array(
            'class_name' => 'StudiengangTeil',
            'thru_table' => 'mvv_stg_stgteil',
            'thru_key' => 'studiengang_id',
            'thru_assoc_key' => 'stgteil_id'
        );
        $config['has_many']['stgteil_assignments'] = array(
            'class_name' => 'StudiengangStgteil',
            'foreign_key' => 'studiengang_id',
            'order_by' => 'ORDER BY position',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_and_belongs_to_many']['stgteil_bezeichnungen'] = array(
            'class_name' => 'StgteilBezeichnung',
            'thru_table' => 'mvv_stg_stgteil',
            'thru_key' => 'studiengang_id',
            'thru_assoc_key' => 'stgteil_bez_id',
            'order_by' => 'GROUP BY stgteil_bez_id ORDER BY position'
        );
        $config['has_many']['documents'] = array(
            'class_name' => 'Dokument',
            'assoc_func' => 'findByObject',
            'assoc_func_params_func' => function ($stg) { return $stg; }
        );
        $config['has_many']['document_assignments'] = array(
            'class_name' => 'DokumentZuord',
            'assoc_foreign_key' => 'range_id',
            'order_by' => 'ORDER BY position',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_one']['responsible_institute'] = array(
            'class_name' => 'Fachbereich',
            'foreign_key' => 'institut_id',
            'assoc_foreign_key' => 'institut_id'
        );
        
        $config['additional_fields']['count_dokumente']['get'] =
            function($stg) { return $stg->count_dokumente; };
        $config['additional_fields']['count_faecher']['get'] =
            function($stg) { return $stg->count_faecher; };
        $config['additional_fields']['count_module']['get'] =
            function($stg) { return $stg->count_module; };
        $config['additional_fields']['institut_name']['get'] =
            function($stg) { return $stg->institut_name; };
        $config['additional_fields']['kategorie_name']['get'] =
            function($stg) { return $stg->kategorie_name; };
        
        parent::configure($config);
    }
    
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Studiengang');
    }
    
    /**
     * Retrieves all Studiengaenge by given Abschluss.
     * 
     * @param string $abschluss_id The id of an Abschluss.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByAbschluss($abschluss_id)
    {
        return parent::getEnrichedByQuery('SELECT ms.* '
                . 'FROM mvv_studiengang ms '
                . 'WHERE ms.abschluss_id = ?', array($abschluss_id));
    }
    
    /**
     * Retrieves all Studiengaenge by a given combination of Fach/Abschluss.
     * 
     * @param string $fach_id The id of a Fach.
     * @param string $abschluss_id The id of an Abschluss.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByFachAbschluss($fach_id, $abschluss_id)
    {
        return parent::getEnrichedByQuery('SELECT ms.* '
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN mvv_stg_stgteil mss USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil mst USING(stgteil_id) '
                . 'WHERE ms.abschluss_id = ? AND mst.fach_id = ?',
                array($abschluss_id, $fach_id));
    }
    
    /**
     * Retrieves all Studiengaenge by given Fachbereich. The Fachbereich is an
     * institute assigned to the fach of a Studiengangteil which is assigned to
     * Studiengaenge. 
     * 
     * @param string $fachbereich_id The id of an institute.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByFachbereich($fachbereich_id)
    {
        return parent::getEnrichedByQuery('SELECT ms.*, '
                . 'COUNT(mst.fach_id) as `count_faecher`, '
                . 'mak.name AS `kategorie_name` '
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN mvv_abschl_zuord maz USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie mak USING(kategorie_id) '
                . 'LEFT JOIN mvv_stg_stgteil mss USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil mst USING(stgteil_id) '
                . 'INNER JOIN mvv_fach_inst mfi USING(fach_id) '
                . 'WHERE mfi.institut_id = ? '
                . 'GROUP BY studiengang_id '
                . 'ORDER BY name', array($fachbereich_id));
    }
    
    /**
     * Retrieves all Studieng�nge ba given Abschluss-Kategorie.
     * 
     * @param string $kategorie_id The id of an Abschluss-Kategorie.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByAbschlussKategorie($kategorie_id)
    {
        return parent::getEnrichedByQuery('SELECT ms.*, '
                . 'COUNT(mst.fach_id) AS `count_faecher`'
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN mvv_abschl_zuord maz USING(abschluss_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil mst USING(stgteil_id) '
                . 'WHERE maz.kategorie_id = ? '
                . 'GROUP BY studiengang_id '
                . 'ORDER BY name', array($kategorie_id));
    }
    
    /**
     * Retrieves all Studiengange by a given combination of Abschluss-Kategorie
     * and Fachbereich.
     * The Fachbereich is an institute assigned to the fach of a Studiengangteil
     * which is assigned to Studiengaenge. 
     * 
     * @param string $kategorie_id The id of an Abschluss-Kategorie.
     * @param string $fachbereich_id The id of an institute.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByAbschlussKategorieFachbereich($kategorie_id,
            $fachbereich_id)
    {
        return parent::getEnrichedByQuery('SELECT ms.*, '
                . 'COUNT(msf.fach_id) AS `count_faecher`'
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN mvv_abschl_zuord maz USING(abschluss_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil USING(stgteil_id) '
                . 'INNER JOIN mvv_fach_inst mfi USING(fach_id) '
                . 'WHERE maz.kategorie_id = ? AND mfi.institut_id = ? '
                . 'GROUP BY studiengang_id '
                . 'ORDER BY name', array($kategorie_id, $fachbereich_id));
    }
    
    /**
     * Retrieves all Studiengaenge the given Studiengangteil is assigned to.
     * 
     * @param string $stgteil_id The id of a Studiengangteil.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function findByStgTeil($stgteil_id)
    {
        return parent::getEnrichedByQuery('SELECT ms.* '
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN mvv_stg_stgteil mss USING(studiengang_id) '
                . 'WHERE mss.stgteil_id = ? ', array($stgteil_id));
    }
    
    /**
     * Retrieves all Studiengaenge the given Module are assigned to.
     * The assignment is done via Studiengangabschnitte, Studiengangteil-
     * Versionen and Studiengangteil.
     * Optionallay restricted to public visible Studiengaenge.
     * 
     * @param array $modul_ids An array of Modul ids.
     * @param boolean $only_public If true retrieve only public visible ones.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByModule($modul_ids, $only_public = true)
    {
        if ($only_public) {
            return parent::getEnrichedByQuery('SELECT ms.*, '
                    . 'COUNT(DISTINCT modul_id) AS count_module '
                    . 'FROM mvv_stgteilabschnitt_modul msm '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(abschnitt_id) '
                    . 'INNER JOIN mvv_stgteilversion msv USING(version_id) '
                    . 'INNER JOIN mvv_stg_stgteil USING(stgteil_id) '
                    . 'INNER JOIN mvv_studiengang ms USING(studiengang_id) '
                    . 'WHERE msm.modul_id IN (?) AND msv.stat IN (?) '
                    . 'AND ms.stat IN (?) '
                    . 'GROUP BY studiengang_id '
                    . 'ORDER BY count_module DESC',
                    array($modul_ids, StgteilVersion::getPublicStatus(),
                        Studiengang::getPublicStatus()));
        } else {
            return parent::getEnrichedByQuery('SELECT ms.*, '
                    . 'COUNT(DISTINCT modul_id) AS count_module '
                    . 'FROM mvv_stgteilabschnitt_modul msm '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(abschnitt_id) '
                    . 'INNER JOIN mvv_stgteilversion USING(version_id) '
                    . 'INNER JOIN mvv_stg_stgteil USING(stgteil_id) '
                    . 'INNER JOIN mvv_studiengang ms USING(studiengang_id) '
                    . 'WHERE msm.modul_id IN (?) '
                    . 'GROUP BY studiengang_id '
                    . 'ORDER BY count_module DESC',
                    array($modul_ids));
        }
    }
    
    /**
     * Returns an array with all studiengaenge filtered by Fachbereich and
     * Abschluss-Kategorie. The associated array contains only the name and
     * the id of the Studiengang with the id as key.
     * The content is utf8 encoded.
     * 
     * @param string $fachbereich_id The id of the Fachbereich
     * @param string $kategorie_id The id of the Abschluss-Kategorie
     * @return array The array with studiengaenge. Empty if no Studiengang
     * was found.
     */
    public static function toArrayFachbereichAbschlussKategorie($fachbereich_id,
            $kategorie_id)
    {
        $studiengaenge = array();
        $query = 'SELECT ms.studiengang_id, ms.name '
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN mvv_abschl_zuord maz USING(abschluss_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil USING(stgteil_id) '
                . 'INNER JOIN mvv_fach_inst mfi USING(fach_id) '
                . 'WHERE maz.kategorie_id = ? AND mfi.institut_id = ? '
                . 'GROUP BY studiengang_id '
                . 'ORDER BY name';
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute(array($kategorie_id, $fachbereich_id));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $studiengang) {
            $studiengaenge[$studiengang['studiengang_id']] =
                    array_map('studip_utf8encode', $studiengang);
        }
        return $studiengaenge;
    }
    
    /**
     * Returns all or a specified (by row count and offset) number of
     * Studiengaenge sorted and filtered by given parameters and enriched with
     * some additional fields. This function is mainly used in the list view.
     * 
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function getAllEnriched($sortby = 'name', $order = 'ASC',
            $filter = null, $row_count = null, $offset = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'name',
                words('abschluss_name kategorie_name count_faecher '
                        . 'count_stgteile count_dokumente institut_name'));
        return parent::getEnrichedByQuery('SELECT mvv_studiengang.*, '
                . 'abschluss.name AS `abschluss_name`, '
                . 'abschluss.name_en AS `abschluss_name_en`, '
                . 'mvv_abschl_kategorie.name AS `kategorie_name`, '
                . 'mvv_abschl_kategorie.name_en AS `kategorie_name_en`, '
                . 'mvv_abschl_kategorie.kategorie_id, '
                . 'Institute.Name AS institut_name, '
                . 'COUNT(mvv_stgteil.stgteil_id) AS `count_faecher`, '
                . 'COUNT(mvv_stg_stgteil.stgteil_bez_id) AS `count_stgteile`, '
                . 'COUNT(DISTINCT mvv_dokument_zuord.dokument_id) AS count_dokumente, '
                . 'GROUP_CONCAT(mvv_fach_inst.institut_id) AS fachbereich_ids '
                . 'FROM mvv_studiengang '
                . 'LEFT JOIN abschluss USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_zuord USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie USING(kategorie_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil USING(stgteil_id) '
                . 'LEFT JOIN mvv_fach_inst USING(fach_id) '
                . 'LEFT JOIN Institute ON (mvv_studiengang.institut_id = Institute.Institut_id) '
                . 'LEFT JOIN mvv_dokument_zuord '
                . 'ON mvv_studiengang.studiengang_id = mvv_dokument_zuord.range_id '
                . "AND mvv_dokument_zuord.object_type = '"
                . get_class() . "' "
                . 'LEFT JOIN semester_data start_sem '
                . 'ON (mvv_studiengang.start = start_sem.semester_id) '
                . 'LEFT JOIN semester_data end_sem '
                . 'ON (mvv_studiengang.end = end_sem.semester_id) '
                . self::getFilterSql($filter, true)
                . 'GROUP BY studiengang_id '
                . 'ORDER BY ' . $sortby, array(), $row_count, $offset);
    }
    
    /**
     * Returns the number of Studiengaenge optional filtered by $filter.
     * 
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of Studiengaenge
     */
    public static function getCount($filter = null)
    {
        $query = 'SELECT COUNT(DISTINCT(mvv_studiengang.studiengang_id)) '
                . 'FROM mvv_studiengang '
                . 'LEFT JOIN abschluss USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_zuord USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie USING(kategorie_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil USING(stgteil_id) '
                . 'LEFT JOIN mvv_fach_inst USING(fach_id) '
                . 'LEFT JOIN Institute ON (mvv_studiengang.institut_id = Institute.Institut_id) '
                . 'LEFT JOIN semester_data start_sem '
                . 'ON (mvv_studiengang.start = start_sem.semester_id) '
                . 'LEFT JOIN semester_data end_sem '
                . 'ON (mvv_studiengang.end = end_sem.semester_id) '
                . self::getFilterSql($filter, true);
        $db = DBManager::get()->query($query);
        return $db->fetchColumn(0);
    }
    
    /**
     * Retrieves the Studiengang and all related data and
     * some additional fields.
     * 
     * @param string $studiengang_id The id of the studiengang.
     * @return object The Studiengang with additional data or a new Studiengang.
     */
    public static function getEnriched($studiengang_id)
    {
        $studiengaenge = parent::getEnrichedByQuery('SELECT ms.*, '
                . 'a.name AS `abschluss_name`, mak.name AS `kategorie_name`, '
                . 'mak.kategorie_id, COUNT(mst.fach_id) AS `count_faecher`, '
                . 'COUNT(mss.stgteil_bez_id) AS `count_stgteile` '
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN abschluss a USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_zuord USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie mak USING(kategorie_id) '
                . 'LEFT JOIN mvv_stg_stgteil mss USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil mst USING(stgteil_id) '
                . 'WHERE ms.studiengang_id = ? '
                . 'GROUP BY studiengang_id',
                array($studiengang_id));
        if (sizeof($studiengaenge)) {
            return $studiengaenge->find($studiengang_id);
        }
        return self::get();
    }
    
    public function getDisplayName()
    {
        return $this->name . ($this->kategorie_name ?
                ' (' . $this->kategorie_name . ')' : '');
    }
    
    /**
     * Returns all institutes assigned to studiengaenge.
     * 
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return SimpleORMapCollection A collection of institutes.
     */
    public static function getAllAssignedInstitutes($filter = null)
    {
        return Fachbereich::getEnrichedByQuery('SELECT DISTINCT Institute.*, '
                . 'Institute.Name AS `name`, '
                . 'Institute.Institut_id AS institut_id, '
                . 'IF(Institute.Institut_id = Institute.fakultaets_id,1,0) '
                . 'AS is_faculty, faculties.Name AS faculty_name, '
                . 'COUNT(studiengang_id) AS count_objects '
                . 'FROM Institute '
                . 'INNER JOIN mvv_studiengang '
                . 'ON Institute.Institut_id = mvv_studiengang.institut_id '
                . 'LEFT JOIN Institute AS faculties '
                . 'ON Institute.fakultaets_id = faculties.Institut_id '
                . Fachbereich::getFilterSql($filter, true)
                . 'GROUP BY Institute.Institut_id '
                . 'ORDER BY faculty_name, is_faculty DESC, name', array());
    }
    
    /**
     * @see ModuleManagementModel::findBySearchTerm()
     */
    public static function findBySearchTerm($term, $filter = null)
    {   
        $quoted_term = DBManager::get()->quote('%' . $term . '%');
        return parent::getEnrichedByQuery('SELECT ms.*, '
                . 'a.name as `abschluss_name`, mak.name as `kategorie_name`, '
                . 'COUNT(mst.fach_id) as `count_faecher` '
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN abschluss a USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_zuord USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie mak USING(kategorie_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil mst USING(stgteil_id) '
                . 'WHERE ms.name LIKE ' . $quoted_term
                . ' OR ms.name_kurz LIKE ' . $quoted_term
                . ' OR a.name LIKE ' . $quoted_term
                . ' OR mak.name LIKE ' . $quoted_term
                . ' OR mst.zusatz LIKE ' . $quoted_term
                . ' GROUP BY studiengang_id ORDER BY `name`');
    }
    
    /**
     * Retrieves all Studiengaenge by given ids optionally filtered.
     * 
     * @see ModuleManagementModel::getFilterSql()
     * @param array $studiengang_ids An array of Studiengang ids.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByIds($studiengang_ids, $filter = null)
    {
        if ($filter['mvv_fach_inst.institut_id']) {
            $fach_sql = 'LEFT JOIN mvv_fach_inst USING(fach_id)';
        } else {
            $fach_sql = '';
        }
        return parent::getEnrichedByQuery('SELECT mvv_studiengang.*, '
                . 'abschluss.name as `abschluss_name`, '
                . 'mvv_abschl_kategorie.name as `kategorie_name`, '
                . 'COUNT(mvv_stgteil.fach_id) as `count_faecher` '
                . 'FROM mvv_studiengang '
                . 'LEFT JOIN abschluss USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_zuord USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie USING(kategorie_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil USING(stgteil_id) '
                . $fach_sql
                . 'WHERE mvv_studiengang.studiengang_id IN(?) '
                . self::getFilterSql($filter)
                . 'GROUP BY studiengang_id ORDER BY `name`',
                array((array) $studiengang_ids));
    }
    
    /**
     * Returns an array with all types of status found by given
     * studiengang ids as key and the number of associated Studiengaenge as
     * value.
     * 
     * @param array $studiengang_ids
     * @return array 
     */
    public static function findStatusByIds($studiengang_ids = array())
    {
        if (is_array($studiengang_ids) && sizeof($studiengang_ids)) {
            $stmt = DBManager::get()->prepare(
                "SELECT IFNULL(stat, '__undefined__') AS stat, "
                . 'COUNT(studiengang_id) AS count_objects '
                . 'FROM mvv_studiengang WHERE studiengang_id IN (?) '
                . 'GROUP BY stat');
            $stmt->execute(array($studiengang_ids));
        } else {
            $stmt = DBManager::get()->prepare(
                "SELECT IFNULL(stat, '__undefined__') AS stat, "
                . 'COUNT(studiengang_id) AS count_objects '
                . 'FROM mvv_studiengang GROUP BY stat');
            $stmt->execute();
        }
        
        $result = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $status) {
            $result[$status['stat']] = array(
                    'name' => $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$status['stat']]['name'],
                    'count_objects' => $status['count_objects']
                );
        }
        return $result;
    }
    
    /**
     * Returns an array with ids of all Studiengaenge found by the given filter.
     * 
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return array An array of Studiengang ids.
     */
    public static function findByFilter($filter)
    {
        $stmt = DBManager::get()->prepare('SELECT DISTINCT studiengang_id '
                . 'FROM mvv_studiengang '
                . 'LEFT JOIN abschluss USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_zuord USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie USING(kategorie_id) '
                . 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil USING(stgteil_id) '
                . 'LEFT JOIN mvv_fach_inst USING(fach_id) '
                . 'LEFT JOIN semester_data start_sem '
                . 'ON (mvv_studiengang.start = start_sem.semester_id) '
                . 'LEFT JOIN semester_data end_sem '
                . 'ON (mvv_studiengang.end = end_sem.semester_id) '
                . self::getFilterSql($filter, true));
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Returns an array with Modul ids from modules related to this Studiengang.
     * The relation is done via Studiengangteile, Studiengangteil-Versionen and
     * Studiengangteil-Abschnitte.
     * Optionally restricted to only public visible modules and filtered by an
     * array of Modul ids.
     * 
     * @param boolean $only_public If true only public visible modules will
     * be retrieved.
     * @param array $modul_ids An array of module ids. Only the intersection of
     * these modules and the found modules will be returned.
     * @return array An array of Modul ids.
     */
    public function getRelatedModules($only_public = true, $modul_ids = null)
    {
        if (only_public) {
            $query = 'SELECT DISTINCT modul_id '
                . 'FROM mvv_stg_stgteil '
                . 'INNER JOIN mvv_stgteilversion msv USING(stgteil_id) '
                . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id) '
                . 'INNER JOIN mvv_modul mm USING(modul_id) '
                . 'WHERE studiengang_id = ? AND msv.stat IN(?) '
                . 'AND mm.stat IN(?) ';
            $params = array($this->getId(), StgteilVersion::getPublicStatus(),
            Modul::getPublicStatus());
        } else {
            $query = 'SELECT DISTINCT modul_id '
                . 'FROM mvv_stg_stgteil '
                . 'INNER JOIN mvv_stgteilversion msv USING(stgteil_id) '
                . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id) '
                . 'INNER JOIN mvv_modul mm USING(modul_id) '
                . 'WHERE studiengang_id = ? ';
            $params = array($this->getId());
        }
        if (is_array($modul_ids)) {
            $query .= ' AND mm.modul_id IN (?)';
            $params[] = $modul_ids;
        }
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Studiengang');
    }
    
    /**
     * Returns the first semester this studiengang is active.
     * 
     * @return object semester
     */
    public function getStartSem()
    {
        return Semester::find($this->sem);
    }
    
    /**
     * Returns the last semester this studiengang is active.
     * 
     * @return object semester
     */
    public function getEndSem()
    {
        return Semester::find($this->end);
    }
    
    /**
     * @see ModuleManagementModel::getResponsibleInstitutes()
     */
    public function getResponsibleInstitutes()
    {   
        if ($this->responsible_institute) {
            return array($this->responsible_institute);
        }
        return parent::getResponsibleInstitutes();
    }
    
    /**
     * Returns whether this studiengang is active.
     * 
     * @return boolean true if active
     */
    public function isActive()
    {
        $start_sem = $this->getStartSem();
        if (is_null($start_sem)) {
            return false;
        }
        $time = time();
        $end_sem = $this->getEndSem();
        if (!$end_sem) {
            return $start_sem->beginn <= $time;
        }
        return $start_sem->beginn <= $time && $time <= $end_sem->ende;
    }
    
    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return $this->abschluss_id;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Abschluss::get($this->getTrailParentId());
    }
    
    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        return StudiengangTeil::findByStudiengang($this->getId());
    }
    
    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        $fachbereich = Fachbereich::find($this->institut_id);
        return array($fachbereich);
    }
    
    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }
    
    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = array();
            $rejected = false;
            // The name of the studiengang must be longer than 4 characters
            if (strlen($this->name) < 4) {
                $ret['name'] = true;
                $messages[] = _('Der Name des Studiengangs ist zu kurz (mindestens 4 Zeichen).');
                $rejected = true;
            }
            // if the short name is given it must be longer than 2 characters
            if ($this->name_kurz && strlen($this->name_kurz) < 2) {
                $ret['name_kurz'] = true;
                $messages[] = _('Die Kurzbezeichnung muss mindestens 2 Zeichen lang sein.');
                $rejected = true;
            }
            if ($this->abschluss_id) {
                $stmt = DBManager::get()->prepare('SELECT abschluss_id '
                        . 'FROM abschluss WHERE abschluss_id = ?');
                $stmt->execute(array($this->abschluss_id));
                if (!$stmt->fetch()) {
                    $ret['abschluss_id'] = true;
                    $messages[] = _('Unbekannter Abschluss.');
                    $rejected = true;
                }
            } else {
                $ret['abschluss_id'] = true;
                $messages[] = _('Bitte einen Abschluss angeben.');
                $rejected = true;
            }
            if ($this->institut_id) {
                $stmt = DBManager::get()->prepare('SELECT institut_id '
                        . 'FROM Institute WHERE Institut_id = ?');
                $stmt->execute(array($this->institut_id));
                if (!$stmt->fetch()) {
                    $ret['institut_id'] = true;
                    $messages[] = _('Unbekannte Einrichtung.');
                    $rejected = true;
                }
            } else {
                $ret['institut_id'] = true;
                $messages[] = _('Bitte eine verantwortliche Einrichtung angeben.');
                $rejected = true;
            }
            if (!$this->isNew() && $this->isFieldDirty('typ') && count($this->studiengangteile)) {
                $this->revertValue('typ');
                $ret['typ'] = true;
                $messages[] = _('Der Typ des Studiengangs kann nicht mehr ver�ndert werden, da bereits ein Studiengangteil zugeordnet wurde.');
                $rejected = true;
            } else {
                if (!in_array($this->typ, words('einfach mehrfach'))) {
                    $ret['typ'] = true;
                    $messages[] = _('Bitte den Typ des Studiengangs w�hlen.');
                    $rejected = true;
                }
            }
            if ($this->start) {
                $start_sem = Semester::find($this->start);
                if (!$start_sem) {
                    $ret['start'] = true;
                    $messages[] = _('Ung�ltiges Semester.');
                    $rejected = true;
                } else if ($this->end) {
                    $end_sem = Semester::find($this->end);
                    if ($end_sem) {
                        if ($start_sem->beginn > $end_sem->beginn) {
                            $ret['start'] = true;
                            $messages[] = _('Das Endsemester muss nach dem Startsemester liegen.');
                            $rejected = true;
                        }
                    } else {
                        $ret['end'] = true;
                        $messages[] = _('Ung�ltiges Endsemester.');
                        $rejected = true;
                    }
                }
            }  else {
                $ret['start'] = true;
                $messages[] = _('Bitte ein Startsemester angeben.');
                $rejected = true;
            }
            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }

}
