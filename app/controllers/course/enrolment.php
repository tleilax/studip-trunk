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
        //Ist bereits Teilnehmer -> gleich weiter
        if ($GLOBALS['perm']->have_studip_perm('user', $this->course_id)) {
            $this->redirect(UrlHelper::getUrl('seminar_main.php', array('auswahl' => $this->course_id)));
            return false;
        }
        $course = Seminar::GetInstance($this->course_id);
        $enrolment_info = $course->getEnrolmentInfo($GLOBALS['user']->id);
        if (!$enrolment_info['enrolment_allowed']) {
            throw new AccessDeniedException($enrolment_info['description']);
        }
        PageLayout::setTitle(getHeaderLine($this->course_id)." - " . _("Veranstaltungsfreischaltung"));
    }

    /**
     * 
     */
    function apply_action()
    {
        $this->courseset = array_pop(CourseSet::getSetsForCourse($this->course_id));
        
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
