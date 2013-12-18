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
        $this->user_id = $GLOBALS['user']->id;
        PageLayout::setHelpKeyword("Basis.VeranstaltungenVerwaltenZugangsberechtigungen");
        PageLayout::setTitle(getHeaderLine($this->course_id)." - " ._("Verwaltung von Zugangsberechtigungen"));
        PageLayout::addStylesheet('form.css');
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
        $lockrules = words('admission_turnout admission_type
                            admission_endtime admission_binding
                            passwort read_level
                            write_level admission_prelim
                            admission_prelim_txt admission_starttime
                            admission_endtime_sem admission_disable_waitlist
                            user_domain admission_binding admission_studiengang');
        foreach ($lockrules as $rule) {
            $this->is_locked[$rule] = LockRules::Check($this->course_id, $rule) ? 'disabled readonly' : '';
        }
        $this->all_domains = UserDomain::getUserDomains();
        $this->seminar_domains = array_map(function($d) {return $d->getId();}, UserDomain::getUserDomainsForSeminar($this->course_id));
        $this->current_courseset = CourseSet::getSetForCourse($this->course_id);
        if (!$this->current_courseset) {
            $this->available_coursesets = array();
            foreach (CourseSet::getCoursesetsByInstituteId($this->course->institut_id) as $cs) {
                $cs = new CourseSet($cs['set_id']);
                if ($cs->isUserAllowedToAssignCourse($this->user_id, $this->course_id)) {
                    $this->available_coursesets[] = $cs;
                }
            }
        }
    }
    
    function explain_course_set_action()
    {
        $cs = new CourseSet(Request::option('set_id'));
        if ($cs->getId()) {
            $template = $GLOBALS['template_factory']->open('shared/tooltip');
            $this->render_text($template->render(array('text' => $cs->toString())));
        } else {
            $this->render_nothing();
        }
    }
    
    function instant_course_set_action()
    {
        if (Request::isXhr()) {
            $this->response->add_header('X-Title', _('Neue Anmelderegel'));
            $this->response->add_header('X-No-Buttons', 1);
            foreach (array_keys($_POST) as $param) {
                Request::set($param, studip_utf8decode(Request::get($param)));
            }
        }
        $type = Request::option('type');
        $rule_id = Request::option('rule_id');
        $rule_types = AdmissionRule::getAvailableAdmissionRules(true);
        if (isset($rule_types[$type])) {
            $rule = new $type($type_id);
            $course_set = CourseSet::getSetForRule($rule_id) ?: new CourseSet();
            if (Request::isPost()) {
                CSRFProtection::verifyUnsafeRequest();
                $rule->setAllData(Request::getInstance());
                $errors = $rule->validate(Request::getInstance());
                if (!strlen(trim(Request::get('instant_course_set_name')))) {
                    $errors[] = _("Bitte geben Sie einen Namen für die Anmelderegel ein!");
                } else {
                    $course_set->setName(trim(Request::get('instant_course_set_name')));
                }
                if (count($errors)) {
                    PageLayout::postMessage(MessageBox::error(_("Speichern fehlgeschlagen"), array_map('htmlready', $errors)));
                } else {
                    $rule->store();
                    $course_set->setPrivate(true);
                    $course_set->addAdmissionRule($rule);
                    $course_set->setAlgorithm(new RandomAlgorithm());//TODO
                    $course_set->setCourses(array($this->course_id));
                    $course_set->store();
                    PageLayout::postMessage(MessageBox::success(_("Die Anmelderegel wurde erzeugt und der Veranstaltung zugewiesen.")));
                    if (Request::isXhr()) {
                        $this->response->add_header('X-Location', $this->url_for('/index'));
                        $this->render_nothing();
                    } else {
                        $this->redirect($this->url_for('/index'));
                    }
                    return;
                }
            }
            if (!$course_set->getId()) {
                $course_set->setName($rule->getName() . ': ' . $this->course->name);
            }
            $this->rule_template = $rule->getTemplate();
            $this->type = $type;
            $this->rule_id = $rule_id;
            $this->course_set_name = $course_set->getName();
        } else {
            throw new Trails_Exception(400);
        }
    }

}
