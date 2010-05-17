<?php
/**
 * studycourse.php - controller class for the studycourses
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
 * @package     studycourses
 * @since       Stud.IP version 1.12
 */

require_once 'lib/trails/AuthenticatedController.php';
require_once 'app/models/studycourse.php';

/**
 *
 *
 */
class Admin_StudycourseController extends AuthenticatedController
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
        Navigation::activateItem('/admin/config/studycourse');
    }

    /**
     * Maintenance view for profession with the degrees
     */
    function profession_action()
    {
        $GLOBALS['CURRENT_PAGE'] = _('Verwaltung der Studieng�nge');

        $this->studycourses = StudycourseModel::getStudyCourses();
        //sorting
        if(Request::get('sortby') == 'users') {
            usort($this->studycourses, array('Admin_StudycourseController', 'sortByUsers'));
        } elseif (Request::get('sortby') == 'seminars') {
            usort($this->studycourses, array('Admin_StudycourseController', 'sortBySeminars'));
        }

        $this->infobox = $this->getInfobox();

        if (Request::submitted('delete')) {
            $this->delete_profession_action();
        }
    }

    /**
     * Maintenance view for degrees with the professions
     */
    function degree_action()
    {
        // set variables for view
        $GLOBALS['CURRENT_PAGE'] = _('Gruppierung von Studienabschl�ssen');

        $this->studydegrees = StudycourseModel::getStudyDegrees();
        //sorting
        if(Request::get('sortby') == 'users') {
            usort($this->studydegrees, array('Admin_StudycourseController', 'sortByUsers'));
        }

        $this->infobox = $this-> getInfobox();

        if (Request::submitted('delete')) {
            $this->delete_degree_action();
        }
    }

    /**
     * Edit the selected profession
     * @param $prof_id
     */
    function edit_profession_action($prof_id)
    {
        if (Request::submitted('uebernehmen')) {
            if (Request::get('professionname')) {
                $prof_name = Request::get('professionname');
                $prof_desc = Request::get('description');
                StudycourseModel::saveEditProfession($prof_id, $prof_name, $prof_desc);
                $this->flash['success'] = sprintf(_('Das Studienfach "%s" wurde erfolgreich aktualisiert!'), htmlReady($prof_name));
                $this->flash['success_detail'] = array(_("Beschreibung: ") . htmlReady($prof_desc));
                $this->redirect('admin/studycourse/profession');
            } else {
                $this->flash['error'] = _("Bitte geben Sie mindestens einen Namen f�r das Fach ein!");
            }
        }

        $GLOBALS['CURRENT_PAGE'] = _("F�cher editieren");

        // set variables for view
        $this->edit = StudycourseModel::getStudyCourseInfo($prof_id);
        $this->infobox = $this->getInfobox();
    }

    /**
     * Edit the selected degree
     * @param $deg_id
     */
    function edit_degree_action($deg_id)
    {
        if (Request::submitted('uebernehmen')) {
            if (Request::get('degreename')) {
                $deg_name = Request::get('degreename');
                $deg_desc = Request::get('description');
                StudycourseModel::saveEditDegree($deg_id, $deg_name, $deg_desc);
                $this->flash['success'] = sprintf(_('Der Abschluss "%s" wurde erfolgreich aktualisiert!'), htmlReady($deg_name));
                $this->flash['success_detail'] = array(_("Beschreibung: ") . htmlReady($deg_desc));
                $this->redirect('admin/studycourse/degree');
            } else {
                $this->flash['error'] = _("Bitte geben Sie mindestens einen Namen f�r den Abschluss ein!");
            }
        }

        $GLOBALS['CURRENT_PAGE'] = _("Abschl�sse editieren");

        // set variables for view
        $this->edit = StudycourseModel::getStudyDegreeInfo($deg_id);
        $this->infobox = $this->getInfobox();
    }

    /**
     * Delete a profession
     * Only if count_user=0
     * @param $delete_course
     */
    function delete_profession_action()
    {
        $prof_id = Request::get('prof_id');
        if (Request::submitted('delete')) {
            $profession = StudycourseModel::getStudyCourses($prof_id);
            //Check ob studiengang leer ist
            if ($profession[0][count_user] == 0) {
                if (StudycourseModel::deleteStudyCourse($prof_id)) {
                    $this->flash['success'] = _("Der Studiengang wurde erfolgreich gel�scht!");
                } else {
                    $this->flash['error'] = _("Interner Fehler im L�schvorgang! Bitte probieren Sie es erneut.");
                }
            } else {
                $this->flash['error']=_("Zu l�schende Studieng�nge m�ssen leer sein!");
            }
            $this->redirect('admin/studycourse/profession');
        } else {
            //Sicherheitsabfrage
            $this->flash['delete'] = StudycourseModel::getStudyCourses($prof_id);
            $this->redirect('admin/studycourse/profession');
        }
    }

    /**
     * Delete a degree
     * Only if count_user = 0
     * @param $delete_degree
     */
    function delete_degree_action()
    {
        $deg_id = Request::get('deg_id');
        if (Request::submitted('delete')) {
            $degree = StudycourseModel::getStudyDegrees($deg_id);
            //Check ob Abschluss leer ist
            if ($degree[0][count_user] == 0) {
                if (StudycourseModel::deleteStudyDegree($deg_id)) {
                    $this->flash['success'] = _("Der Abschluss wurde erfolgreich gel�scht!");
                } else {
                    $this->flash['error'] = _("Interner Fehler im L�schvorgang! Bitte probieren Sie es erneut.");
                }
            } else {
                $this->flash['error'] = _("Zu l�schende Abschl�sse m�ssen leer sein!");
            }
            $this->redirect('admin/studycourse/degree');
        } else {
            //Sicherheitsabfrage
            $this->flash['delete']=StudycourseModel::getStudyDegrees($deg_id);
            $this->redirect('admin/studycourse/degree');
        }
    }

    /**
     * Create a new profession
     */
    function newprofession_action()
    {
        if (Request::submitted('anlegen')) {
            if (Request::get('professionname')) {
                $prof_name = Request::get('professionname');
                $prof_desc = Request::get('description');
                if (!StudycourseModel::checkProfession($prof_name)) {
                    StudycourseModel::saveNewProfession($prof_name, $prof_desc);
                    $this->flash['success'] = sprintf(_('Das Studienfach "%s" wurde erfolgreich angelegt!'), htmlReady($prof_name));
                    $this->redirect('admin/studycourse/profession');
                } else {
                    $this->flash['error'] = sprintf(_('Ein Studienfach mit dem Namen "%s" existiert bereits!'), htmlReady($prof_name));
                }
            } else {
                $this->flash['error'] = _("Bitte geben Sie eine mindestens einen Namen f�r das Fach ein!");
            }
        }

        $GLOBALS['CURRENT_PAGE'] = _("Anlegen von Studienf�chern");

        $this->infobox = $this-> getInfobox();
    }

    /**
     * Create a new degree
     */
    function newdegree_action()
    {
        if (Request::submitted('anlegen')) {
            if (Request::get('degreename')) {
                $deg_name = Request::get('degreename');
                $deg_desc = Request::get('description');
                if (!StudycourseModel::checkDegree($deg_name)) {
                    StudycourseModel::saveNewDegree($deg_name, $deg_desc);
                    $this->flash['success'] = sprintf(_('Der Studienabschluss "%s" wurde erfolgreich angelegt!'), htmlReady($deg_name));
                    $this->redirect('admin/studycourse/degree');
                } else {
                    $this->flash['error'] = sprintf(_('Ein Studienabschluss mit dem Namen "%s" existiert bereits!'), htmlReady($deg_name));
                }
            } else {
                $this->flash['error'] = _("Bitte geben Sie mindestens einen Namen f�r den Abschluss ein!");
            }
        }

        $GLOBALS['CURRENT_PAGE'] = _("Anlegen von Studienabschl�ssen");

        $this->infobox = $this-> getInfobox();
    }

    /**
     * Create the messagebox
     */
    private function getInfobox()
    {
        $infobox = array('picture' => 'infoboxes/browse.jpg');
        $aktionen[] = array(
            "text" => '<a href="'.$this->url_for('admin/studycourse/profession').'">'._('Gruppierung nach F�cher').'</a>',
            "icon" => "icon-cont.gif"
        );
        $aktionen[] = array(
            "text" => '<a href="'.$this->url_for('admin/studycourse/degree').'">'._('Gruppierung nach Abschl�ssen').'</a>',
            "icon" => "icon-cont.gif"
        );
        $aktionen[] = array(
            "text" => '<a href="'.$this->url_for('admin/studycourse/newprofession').'">'._('Neue F�cher anlegen').'</a>',
            "icon" => "admin.gif"
        );
        $aktionen[] = array(
            "text" => '<a href="'.$this->url_for('admin/studycourse/newdegree').'">'._('Neue Abschl�sse anlegen').'</a>',
            "icon" => "admin.gif"
        );

        $infobox['content'] = array(
            array(
                'kategorie' => _("Aktionen"),
                'eintrag'   => $aktionen
            ),
            array(
                'kategorie' => _("Information"),
                'eintrag'   => array(
                    array(
                        "text" => _("Auf dieser Seite k�nnen Sie die Studieng�nge verwalten. Zus�tzlich k�nnen Sie allen Benutzern eines Studiengangs eine Nachricht senden."),
                        "icon" => "ausruf_small2.gif"
                    ),
                    array(
                        "text" => _("Studieng�nge bestehen aus einem Fach und einem oder mehreren Abschl�ssen. Bestehende Studieng�nge und Abschl�sse k�nnen nur gel�scht werden, wenn diese keine Nutzer haben."),
                        "icon" => "ausruf_small2.gif"
                    )
                )
            )
        );
        return $infobox;
    }

    private static function sortByUsers($a, $b)
    {
        if ($a['count_user'] == $b['count_user']) {
            return 0;
        }
        return ($a['count_user'] > $b['count_user']) ? -1 : 1;
    }

    private static function sortBySeminars($a, $b)
    {
        if ($a['count_sem'] == $b['count_sem']) {
            return 0;
        }
        return ($a['count_sem'] > $b['count_sem']) ? -1 : 1;
    }
}
?>