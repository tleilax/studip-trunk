<?php
/*
 * AdminList.class.php - contains AdminList
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.2
 */

require_once 'lib/classes/SemesterData.class.php';

/**
 * Singleton class for the admin search list. This is a singleton-class because
 * the result set is dependend on the session.
 *
 * @author Rasmus Fuhse <fuhse@data-quest.de>
 */
class AdminList {
    static protected $instance = null;

    protected $results = array();

    static public function getInstance() {
        if (!self::$instance) {
            include_once 'lib/admin_search.inc.php';
            self::$instance = new AdminList();
        }
        return self::$instance;
    }

    public function __construct() {
        $GLOBALS['view_mode'] = "sem";
        $this->search();
    }

    /**
     * Saves a search-result-set of seminars depending on the parameters of the session
     * to the AdminList object.
     */
    public function search()
    {
        global $perm, $user;
        //the search parameters are completely saved in the following session variable
        global $links_admin_data;
        //$links_admin_data = $SESSION['links_admin_data'];
        $semester = new SemesterData;
        $db = DBManager::get();
        if (!$perm->have_perm("root")) {
            $my_inst = $db->query(
                "SELECT a.Institut_id,Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak " .
                "FROM user_inst a " .
                    "LEFT JOIN Institute b USING (Institut_id) " .
                "WHERE a.user_id='$user->id' " .
                    "AND a.inst_perms='admin' " .
                "ORDER BY is_fak,Name" .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        $query=
            "SELECT DISTINCT seminare.*, Institute.Name AS Institut, sd1.name AS startsem,IF(duration_time=-1, '"._("unbegrenzt")."', sd2.name) AS endsem " .
            "FROM seminar_user " .
                "LEFT JOIN seminare USING (seminar_id) " .
                "LEFT JOIN Institute USING (institut_id) " .
                "LEFT JOIN auth_user_md5 ON (seminar_user.user_id = auth_user_md5.user_id) " .
                "LEFT JOIN semester_data sd1 ON ( start_time BETWEEN sd1.beginn AND sd1.ende) " .
                "LEFT JOIN semester_data sd2 ON ((start_time + duration_time) BETWEEN sd2.beginn AND sd2.ende) " .
            "WHERE seminar_user.status = 'dozent' " .
                "AND seminare.status NOT IN (:studygroup_sem_types) ";
        $params = array('studygroup_sem_types' => studygroup_sem_types());
        
        if ($links_admin_data["srch_sem"]) {
            $one_semester = $semester->getSemesterData($links_admin_data["srch_sem"]);
            $query.="AND seminare.start_time <= :semester_begin AND (:semester_begin <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
            $params['semester_begin'] = $one_semester["beginn"];
        }

        if (is_array($my_inst) && !$perm->have_perm("root")) {
            $query.="AND Institute.Institut_id IN (:my_inst) ";
            $params['my_inst'] = $my_inst;
        }

        if ($links_admin_data["srch_inst"]) {
            $query.="AND Institute.Institut_id = :special_institute ";
            $params['special_institute'] = $links_admin_data["srch_inst"];
        }

        if ($links_admin_data["srch_fak"]) {
            $query.="AND fakultaets_id = :special_faculty ";
            $params['special_faculty'] = $links_admin_data["srch_fak"];
        }

        if ($links_admin_data["srch_doz"]) {
            $query.="AND seminar_user.user_id = :dozent ";
            $params['dozent'] = $links_admin_data["srch_doz"];
        }

        if ($links_admin_data["srch_exp"]) {
            $query.="AND (seminare.Name LIKE :search_expression OR seminare.VeranstaltungsNummer LIKE :search_expression OR seminare.Untertitel LIKE :search_expression OR seminare.Beschreibung LIKE :search_expression OR auth_user_md5.Nachname LIKE :search_expression) ";
            $params['search_expression'] = "%".$links_admin_data["srch_exp"]."%";
        }

        $query.=" ORDER BY `".addslashes($links_admin_data["sortby"])."` ";
        if ($links_admin_data["sortby"] === 'start_time') {
            $query .= ' DESC';
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($params);
        $this->results = $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSelectTemplate($course_id)
    {
        $adminList = $GLOBALS['template_factory']->open('admin/adminList.php');
        $adminList->set_attribute('adminList', $this->results);
        $adminList->set_attribute('course_id', $course_id);
        return $adminList;
    }

    public function getTopLinkTemplate($course_id)
    {
        $adminTopLinks = $GLOBALS['template_factory']->open("admin/topLinks.php");
        $adminTopLinks->set_attribute('adminList', $this->results);
        $adminTopLinks->set_attribute('course_id', $course_id);
        return $adminTopLinks;
    }

    
}

