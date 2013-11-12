<?php
/**
 * enrolment.php - enrolment in courses
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

/**
 * @addtogroup notifications
 *
 * Enrolling in a course triggers a CourseDidEnroll
 * notification. The course's ID is transmitted as
 * subject of the notification.
 */
class Course_EnrolmentController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        $this->current_action = $action;
        parent::before_filter($action, $args);
        $this->course_id = $args[0];
        if (!in_array($action, words('apply'))) {
            $this->redirect($this->url_for('apply/' . $action));
            return false;
        }
        if (!get_object_type($this->course_id, array('sem'))) {
            throw new Trails_Exception(400);
        }
        $course = Seminar::GetInstance($this->course_id);
        $enrolment_info = $course->getEnrolmentInfo($GLOBALS['user']->id);
        //Ist bereits Teilnehmer/Admin/freier Zugriff -> gleich weiter
        if ($enrolment_info['enrolment_allowed'] && in_array($enrolment_info['cause'], words('root courseadmin member free_access'))) {
            $this->redirect(UrlHelper::getUrl('seminar_main.php', array('auswahl' => $this->course_id)));
            return false;
        }
        //Grundsätzlich verboten
        if (!$enrolment_info['enrolment_allowed']) {
            throw new AccessDeniedException($enrolment_info['description']);
        }
        PageLayout::setTitle(getHeaderLine($this->course_id)." - " . _("Veranstaltungsanmeldung"));
    }

    /**
     * 
     */
    function apply_action()
    {
        $user_id = $GLOBALS['user']->id;
        $courseset = array_pop(CourseSet::getSetsForCourse($this->course_id));
        if ($courseset) {
            $errors = $courseset->checkAdmission($user_id, $this->course_id);
            if (count($errors)) {
                $this->courseset_message = $courseset->toString(true);
                $this->admission_error = MessageBox::error(_("Die Anmeldung war nicht erfolgreich."), $errors);
                foreach ($courseset->getAdmissionRules() as $rule) {
                    $admission_form .= $rule->getInput();
                }
                if ($admission_form) {
                    $this->admission_form = $admission_form;
                }
            } else {
                $enrol_user = true;
            }
        } else {
            $enrol_user = true;
        }

        if ($enrol_user) {
            $course = Seminar::GetInstance($this->course_id);
            if ($course->admission_prelim) {
                if ($course->addPreliminaryMember($user_id)) {
                    if ($course->isStudygroup()) {
                        PageLayout::postMessage(MessageBox::success(sprintf(_("Sie wurden auf die Anmeldeliste der Studiengruppe %s eingetragen. Die Moderatoren der Studiengruppe können Sie jetzt freischalten.")), $course->getName()));
                    } else {
                        $success = sprintf(_("Sie wurden in die Veranstaltung %s vorläufig eingetragen."), $course->getName());
                        if ($course->admission_prelim_txt) {
                            $success .= '<br>' . _("Lesen Sie bitte folgenden Hinweistext:") . '<br>';
                            $success .= formatReady($course->admission_prelim_txt);
                        }
                        PageLayout::postMessage(MessageBox::success($success));
                    }
                }
            } else {
                $status = $course->read_level === 1 ? 'user' : 'autor';
                if ($course->addMember($user_id, $status)) {
                    $success = sprintf(_("Sie wurden in die Veranstaltung %s als %s eingetragen."), $course->getName(), get_title_for_status($status, 1));
                    PageLayout::postMessage(MessageBox::success($success));
                }
            }
            unset($this->courset_message);
        }
    }

    function url_for($to = '', $params = array())
    {
        $whereto = 'course/enrolment/';
        if ($to === '') {
            $whereto .=  $this->current_action;
        } else {
            $whereto .=  $to;
        }
        $url = URLHelper::getURL($this->dispatcher->trails_uri . '/' . $whereto, $params);
        return $url;
    }

    function link_for($to = '', $params = array())
    {
        $whereto = 'course/enrolment/';
        if ($to === '') {
            $whereto .=  $this->current_action;
        } else {
            $whereto .=  $to;
        }
        $link = URLHelper::getLink($this->dispatcher->trails_uri . '/' . $whereto, $params);
        return $link;
    }

    function render_json($data){
        $this->set_content_type('application/json;charset=utf-8');
        return $this->render_text(json_encode($data));
    }
}
