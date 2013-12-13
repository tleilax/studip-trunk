<?php
/**
 * admission.php - administration of admission restrictions
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

class Course_AdmissionController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm;

        parent::before_filter($action, $args);

        $course_id = $args[0];

        $this->course_id = Request::option('cid', $course_id);
        
        if ($perm->have_perm('admin')) {
            //Navigation im Admin-Bereich:
            Navigation::activateItem('/admin/course/admission');
        } else {
            //Navigation in der Veranstaltung:
            Navigation::activateItem('/course/admin/admission');
        }

        if (!$this->course_id) {
            PageLayout::setTitle(_("Verwaltung von Zugangsberechtigungen"));
            $GLOBALS['view_mode'] = "sem";

            require_once 'lib/admin_search.inc.php';

            include 'lib/include/html_head.inc.php';
            include 'lib/include/header.php';
            include 'lib/include/admin_search_form.inc.php';  // will not return
            die(); //must not return
        }

        if (!get_object_type($this->course_id, array('sem')) ||
            SeminarCategories::GetBySeminarId($this->course_id)->studygroup_mode ||
            !$perm->have_studip_perm("tutor", $this->course_id)) {
            throw new Trails_Exception(400);
        }
        
        $this->course = Course::find($this->course_id);
        PageLayout::setHelpKeyword("Basis.VeranstaltungenVerwaltenZugangsberechtigungen");
        PageLayout::setTitle(getHeaderLine($this->course_id)." - " ._("Verwaltung von Zugangsberechtigungen"));
        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
        $this->set_content_type('text/html;charset=windows-1252');
    }

    /**
     * 
     */
    function index_action()
    {
        $this->setInfoboxImage(Assets::image_path('infobox/hoersaal.jpg'));
        $this->addToInfobox(_('Information'), _("Sie können hier die Zugangsberechtigungen bearbeiten."), 'icons/16/black/info');
        $courseset = CourseSet::getSetForCourse($this->course_id);
        
    }

}
