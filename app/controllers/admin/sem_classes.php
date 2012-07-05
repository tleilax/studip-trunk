<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
require_once 'app/controllers/authenticated_controller.php';

class Admin_SemClassesController extends AuthenticatedController
{
    function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException(_("Kein Zugriff"));
        }
        PageLayout::setHelpKeyword("Admins.SemClasses");
        PageLayout::setTitle("Seminarklassen");
    }

    public function overview_action()
    {
        Navigation::activateItem("/admin/config/sem_classes");
    }
    
    public function details_action() 
    {
        Navigation::activateItem("/admin/config/sem_classes");
        $modules = array(
            'CoreOverview' => array('id' => "CoreOverview", 'name' => _("Kern-�bersicht"), 'enabled' => true),
            'CoreAdmin' => array('id' => "CoreAdmin", 'name' => _("Kern-Verwaltung"), 'enabled' => true),
            'CoreForum' => array('id' => "CoreForum", 'name' => _("Kern-Forum"), 'enabled' => true),
            'CoreStudygroupAdmin' => array('id' => "CoreStudygroupAdmin", 'name' => _("Studiengruppen-Verwaltung"), 'enabled' => true),
            'CoreDocuments' => array('id' => "CoreDocuments", 'name' => _("Kern-Dateibereich"), 'enabled' => true),
            'CoreSchedule' => array('id' => "CoreSchedule", 'name' => _("Kern-Termine"), 'enabled' => true),
            'CoreParticipants' => array('id' => "CoreParticipants", 'name' => _("Kern-Teilnehmer"), 'enabled' => true),
            'CoreStudygroupParticipants' => array('id' => "CoreStudygroupParticipants", 'name' => _("Kern-Studiengruppen-Teilnehmer"), 'enabled' => true),
            'CoreLiterature' => array('id' => "CoreLiterature", 'name' => _("Kern-Literatur"), 'enabled' => true),
            'CoreScm' => array('id' => "CoreScm", 'name' => _("Kern-Freie-Informationen"), 'enabled' => true),
            'CoreWiki' => array('id' => "CoreWiki", 'name' => _("Kern-Wiki"), 'enabled' => true),
            'CoreResources' => array('id' => "CoreResources", 'name' => _("Kern-Ressourcen"), 'enabled' => true),
            'CoreCalendar' => array('id' => "CoreCalendar", 'name' => _("Kern-Kalender"), 'enabled' => true),
            'CoreElearningInterface' => array('id' => "CoreElearningInterface", 'name' => _("Kern-Lernmodule"), 'enabled' => true)
        );
        $plugin_infos = PluginManager::getInstance()->getPluginInfos("StandardPlugin");
        foreach ($plugin_infos as $plugin_info) {
            $modules[$plugin_info['class']] = $plugin_info;
        }
        $this->modules = $modules;
        $this->sem_class = $GLOBALS['SEM_CLASS'][Request::get("id")];
    }
    
    public function save_action() 
    {
        if (count($_POST) === 0) {
            throw new Exception("Kein Zugriff �ber GET");
        }
        $sem_class = $GLOBALS['SEM_CLASS'][Request::int("sem_class_id")];
        foreach (Request::getArray("core_module_slots") as $slot => $module) {
            $sem_class->setSlotModule($slot, studip_utf8decode($module));
        }
        $sem_class->setModules(Request::getArray("modules"));
        $sem_class->set('name', Request::get("sem_class_name"));
        $sem_class->set('title_dozent', Request::get("title_dozent") ? studip_utf8decode(Request::get("title_dozent")) : null);
        $sem_class->set('title_dozent_plural', Request::get("title_dozent_plural") ? studip_utf8decode(Request::get("title_dozent_plural")) : null);
        $sem_class->set('title_tutor', Request::get("title_tutor") ? studip_utf8decode(Request::get("title_tutor")) : null);
        $sem_class->set('title_tutor_plural', Request::get("title_tutor_plural") ? studip_utf8decode(Request::get("title_tutor_plural")) : null);
        $sem_class->set('title_autor', Request::get("title_autor") ? studip_utf8decode(Request::get("title_autor")) : null);
        $sem_class->set('title_autor_plural', Request::get("title_autor_plural") ? studip_utf8decode(Request::get("title_autor_plural")) : null);
        $sem_class->set('chat', Request::int("chat"));
        $sem_class->set('compact_mode', Request::int("compact_mode"));
        $sem_class->set('workgroup_mode', Request::int("workgroup_mode"));
        $sem_class->set('only_inst_user', Request::int("only_inst_user"));
        $sem_class->set('turnus_default', Request::int("turnus_default"));
        $sem_class->set('default_read_level', Request::int("default_read_level"));
        $sem_class->set('default_write_level', Request::int("default_write_level"));
        $sem_class->set('bereiche', Request::int("bereiche"));
        $sem_class->set('show_browse', Request::int("show_browse"));
        $sem_class->set('write_access_nobody', Request::int("write_access_nobody"));
        $sem_class->set('topic_create_autor', Request::int("topic_create_autor"));
        $sem_class->set('visible', Request::int("visible"));
        $sem_class->set('course_creation_forbidden', Request::int("course_creation_forbidden"));
        $sem_class->store();
        $output = array(
            'html' => studip_utf8encode(MessageBox::success(_("�nderungen wurden gespeichert.")))
        );
        echo json_encode($output);
        $this->render_nothing();
    }

    public function delete_sem_type_action() {
        if (count($_POST)) {
            $sem_type = $GLOBALS['SEM_TYPE'][Request::int("sem_type")];
            if (!$sem_type->delete()) {
                throw new Exception("Could not delete sem_type because it' still in use.");
            }
        }
        $this->render_nothing();
    }

}