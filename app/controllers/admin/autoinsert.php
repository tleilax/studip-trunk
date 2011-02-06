<?php
/**
 * autu_insert.php - controller class for the auto insert seminars
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico M�ller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       2.1
 */

//Imports
require_once 'app/controllers/authenticated_controller.php';

// classes required for global-specification-settings
require_once 'lib/classes/SemesterData.class.php';
require_once 'lib/classes/searchtypes/SeminarSearch.class.php';
require_once 'lib/classes/AutoInsert.class.php';
require_once 'lib/classes/UserLookup.class.php';

class Admin_AutoinsertController extends AuthenticatedController
{
    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;
        parent::before_filter($action, $args);

        // user must have root permission
        $perm->check('root');

        // set navigation
        Navigation::activateItem('/admin/config/auto_insert');

        //pagelayout
        PageLayout::setTitle(_('Automatisiertes Eintragen verwalten'));
        PageLayout::setHelpKeyword("Admins.AutomatisiertesEintragen");
    }

    /**
     * Maintenance view for the auto insert parameters
     *
     */
    function index_action()
    {
        $this->semester_data = SemesterData::getAllSemesterData();

        // search seminars
        if (Request::submitted('suchen')) {
            if (Request::get('sem_search')) {
                $this->sem_search = Request::get('sem_search');
                $search = new SeminarSearch();
                $this->seminar_search = $search->getResults(Request::get('sem_search'), array('search_sem_sem' => Request::get('sem_select')));
                if (count($this->seminar_search) == 0) {
                    $this->flash['message'] = _("Es wurden keine Veranstaltungen gefunden.");
                }
            } else {
                $this->flash['error'] = _("Bitte geben Sie einen Suchparameter ein.");
            }
        }
        $this->auto_sems = AutoInsert::getAllSeminars();
    }

    /**
     * Create a new seminar for auto insert
     */
    public function new_action()
    {
        if (Request::submitted('anlegen')) {
            $sem_id = Request::get('sem_id');
            $rechte = Request::getArray('rechte');
            if (empty($rechte)) {
                $this->flash['error'] = _('Mindestens ein Status sollte selektiert werden!');
            } elseif (!AutoInsert::checkSeminar($sem_id)) {
                AutoInsert::saveSeminar($sem_id, $rechte);
                $this->flash['success'] = _('Das Seminar wurde erfolgreich angelegt!');
            } else {
                $this->flash['error'] = _('Das Seminar wird bereits zu diesem Zweck verwendet!');
            }
        }
        $this->redirect('admin/autoinsert');
    }

    /**
     * Edit a rule
     *
     * @param md5 $seminar_id
     * @param string $status
     * @param int $remove
     */
    public function edit_action($seminar_id, $status, $remove = NULL)
    {
        AutoInsert::updateSeminar($seminar_id, $status, $remove);
        $this->flash['success'] = _("Die Statusgruppenanpassung wurde erfolgreich �bernommen!");
        $this->redirect('admin/autoinsert');
    }

    /**
     * Removes a seminar from the auto-insert list, with modal dialog
     *
     * @param md5 $seminar_id
     */
    public function delete_action($seminar_id)
    {
        if (Request::int('delete') == 1) {
            if (AutoInsert::deleteSeminar($seminar_id)) {
                $this->flash['success'] = _("Die Zuordnung der Veranstaltung wurde gel�scht!");
            }
        } elseif (!Request::get('back')) {
            $this->flash['delete'] = $seminar_id;
        }
        $this->redirect('admin/autoinsert');
    }

    /**
     * Maintenance view for the manual insert parameters
     *
     */
    function manual_action()
    {
        $_request = Request::GetInstance();

        if (Request::submitted('submit')) {
            $filters = array_filter(Request::getArray('filter'));
            if (!Request::get('sem_id') || Request::get('sem_id') == 'false') {
                $this->flash['error'] = _('Ung�ltiger Aufruf');
            } elseif (!count($filters)) {
                $this->flash['error'] = _('Keine Filterkriterien gew�hlt');
            } else {
                $seminar = Seminar::GetInstance(Request::get('sem_id'));
                $group = select_group($seminar->getSemesterStartTime());

                $userlookup = new UserLookup();
                foreach ($filters as $type => $values) {
                    $userlookup->set_filter($type, $values);
                }
                $user_ids = $userlookup->execute();
                $real_users = 0;

                foreach ($user_ids as $user_id) {
                    if (!AutoInsert::checkAutoInsertUser(Request::get('sem_id'), $user_id)) {
                        $seminar->addMember($user_id);
                        AutoInsert::saveAutoInsertUser(Request::get('sem_id'), $user_id);
                        $real_users++;
                    }
                }

                //messagebox
                $text = sprintf(
                    _('Es wurden %u Nutzer von %u m�glichen Nutzern in die Veranstaltung "<a href="%s">%s</a>" eingetragen.'),
                    $real_users,count($user_ids),
                    URLHelper::getLink('details.php', array('cid' => $seminar->getId())),
                    $seminar->getName()
                );
                if ($real_users > 0) {
                    $this->flash['success'] = $text;
                } else {
                    $this->flash['message'] = $text;
                }
                $this->flash['detail'] = array(_('Etwaige Abweichungen der Nutzerzahlen enstehen durch bereits vorhandene Nutzer bzw. wieder ausgetragene Nutzer.'));
                $this->redirect('admin/autoinsert/manual');
            }
        }

        $this->semester_data = SemesterData::getAllSemesterData();

        $this->sem_id = Request::Get('sem_id');
        $this->sem_search = Request::get('sem_search');
        $this->sem_select = Request::get('sem_select');
        $this->filtertype = Request::getArray('filtertype');
        $this->filter = Request::getArray('filter');

        if (Request::submitted('remove_filter')) {
            $this->filtertype = array_diff($this->filtertype, array(Request::get('remove_filter')));
        } elseif (Request::submitted('add_filter')) {
            array_push($this->filtertype, Request::get('add_filtertype'));
        }

        if (Request::get('sem_search') and Request::get('sem_select')) {
            if (Request::get('sem_search')) {
                $search_sem_sem = Request::get('sem_select');
                $search = new SeminarSearch('number-name');
                $this->seminar_search = $search->getResults(Request::get('sem_search'), array('search_sem_sem' => $search_sem_sem));
                if (count($this->seminar_search) == 0 ) {
                    $this->flash['message'] = _("Es wurden keine Veranstaltungen gefunden.");
                }
            } else {
                $this->flash['error'] = _("Im Suchfeld wurde nichts eingetragen!");
            }
        }

        $this->values = array();
        foreach ($this->filtertype as $type) {
            $this->values[$type] = UserLookup::GetValuesForType($type);
        }

        $this->available_filtertypes = array(
            'fach'         => _('Studienfach'),
            'abschluss'    => _('Studienabschluss'),
            'fachsemester' => _('Studienfachsemester'),
            'institut'     => _('Einrichtung'),
            'status'       => _('Statusgruppe'),
        );
    }

    /**
     * Count how many user a insert
     * @param $filter (e.g. studycourse, studydegree, institut, studysemester)
     */
    public function manual_count_action()
    {
        ob_end_clean();

        $filters = array_filter(Request::getArray('filter'));

        Header('Content-Type: text/plain;charset=utf-8');

        if (empty($filters)) {
            echo json_encode(array('error' => utf8_encode(_('Keine Filterkriterien gew�hlt'))));
        } else {
            $userlookup = new UserLookup();
            foreach ($filters as $type => $values) {
                $userlookup->set_filter($type, $values);
            }

            echo json_encode(array('users' => count($userlookup->execute())));
        }
        die;
    }
}
