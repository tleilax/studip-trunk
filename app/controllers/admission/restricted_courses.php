<?php
/**
 * restricted_courses.php - administration of admission restrictions
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */

require_once 'app/controllers/authenticated_controller.php';
require_once 'lib/classes/admission/CourseSet.class.php';

class Admission_RestrictedCoursesController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        
        parent::before_filter($action, $args);
        PageLayout::setTitle(_('Anmeldesets'));
        Navigation::activateItem('/tools/coursesets/restricted_courses');
    }
    
    function index_action()
    {
        $this->setInfoboxImage(Assets::image_path('infobox/hoersaal.jpg'));
        $this->addToInfobox(_('Information'), _("Sie können hier alle Veranstaltungen mit eingeschränkter Teilnehmerzahl einsehen."), 'icons/16/black/info');
        $sem_condition = "AND EXISTS (SELECT * FROM seminar_courseset INNER JOIN courseset_rule USING(set_id) WHERE type='ParticipantRestrictedAdmission' AND seminar_courseset.seminar_id=seminare.seminar_id) ";
        if (Request::isPost()) {
            if (Request::submitted('choose_institut')) {
                $this->current_institut_id = Request::option('choose_institut_id');
                $this->current_semester_id = Request::get('select_semester_id', $_SESSION['default_sem']);
                $this->sem_name_prefix = trim(Request::get('sem_name_prefix'));
            }
            $semester = Semester::find($this->current_semester_id);
            $sem_condition .= "AND seminare.start_time <=" . (int)$semester["beginn"]." AND (" . (int)$semester["beginn"] . " <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
            if ($this->sem_name_prefix) {
                $sem_condition .= sprintf('AND (seminare.Name LIKE %1$s OR seminare.VeranstaltungsNummer LIKE %1$s) ', DBManager::get()->quote($this->sem_name_prefix . '%'));
            }
        }
        
        if ($GLOBALS['perm']->have_perm('admin')) {
            $this->my_inst = $this->get_institutes($sem_condition);
        }
    }
    
    function get_institutes($seminare_condition) {
        global $perm, $user;

        // Prepare institute statement
        $query = "SELECT a.Institut_id, a.Name, COUNT(seminar_id) AS num_sem
        FROM Institute AS a
        LEFT JOIN seminare ON (seminare.Institut_id = a.Institut_id {$seminare_condition})
        WHERE fakultaets_id = ? AND a.Institut_id != fakultaets_id
        GROUP BY a.Institut_id
        ORDER BY a.Name, num_sem DESC";
        $institute_statement = DBManager::get()->prepare($query);

        $parameters = array();
        if ($perm->have_perm('root')) {
            $query = "SELECT COUNT(*) FROM seminare WHERE 1 {$seminare_condition}";
            $statement = DBManager::get()->query($query);
            $num_sem = $statement->fetchColumn();

            $_my_inst['all'] = array(
                    'name'    => _('alle'),
                    'num_sem' => $num_sem
            );
            $query = "SELECT a.Institut_id, a.Name, 1 AS is_fak, COUNT(seminar_id) AS num_sem
            FROM Institute AS a
            LEFT JOIN seminare ON (seminare.Institut_id = a.Institut_id {$seminare_condition})
            WHERE a.Institut_id = fakultaets_id
            GROUP BY a.Institut_id
            ORDER BY is_fak, Name, num_sem DESC";
        } else {
            $query = "SELECT b.Institut_id, b.Name, b.Institut_id = b.fakultaets_id AS is_fak, COUNT( seminar_id ) AS num_sem
            FROM user_inst AS s
            LEFT JOIN Institute AS b USING ( Institut_id )
            LEFT JOIN seminare ON ( seminare.Institut_id = b.Institut_id {$seminare_condition})
            WHERE s.user_id = ? AND s.inst_perms = 'admin'
            GROUP BY b.Institut_id
            ORDER BY is_fak, Name, num_sem DESC";
            $parameters[] = $user->id;
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $temp = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($temp as $row) {
            $_my_inst[$row['Institut_id']] = array(
                    'name'    => $row['Name'],
                    'is_fak'  => $row['is_fak'],
                    'num_sem' => $Row['num_sem']
            );
            if ($row["is_fak"]) {
                $_my_inst[$row['Institut_id'] . '_all'] = array(
                        'name'    => sprintf(_('[Alle unter %s]'), $row['Name']),
                        'is_fak'  => 'all',
                        'num_sem' => $row['num_sem']
                );

                $num_inst = 0;
                $num_sem_alle = $row['num_sem'];

                $institute_statement->execute(array($row['Institut_id']));
                while ($institute = $institute_statement->fetch(PDO::FETCH_ASSOC)) {
                    if(!$_my_inst[$institute['Institut_id']]) {
                        $num_inst += 1;
                        $num_sem_alle += $institute['num_sem'];
                    }
                    $_my_inst[$institute['Institut_id']] = array(
                            'name'    => $institute['Name'],
                            'is_fak'  => 0,
                            'num_sem' => $institute["num_sem"]
                    );
                }
                $_my_inst[$row['Institut_id']]['num_inst']          = $num_inst;
                $_my_inst[$row['Institut_id'] . '_all']['num_inst'] = $num_inst;
                $_my_inst[$row['Institut_id'] . '_all']['num_sem']  = $num_sem_alle;
            }
        }
        return $_my_inst;
    }
}