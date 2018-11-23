<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreIliasInterface implements StudipModule {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        $ilias_interface_config = Config::get()->ILIAS_INTERFACE_BASIC_SETTINGS;
        if (count($ilias_interface_config)) {
            $navigation = new Navigation(_('ILIAS'), "seminar_main.php?auswahl=".$course_id."&redirect_to=dispatch.php/course/ilias_interface/index");
            $navigation->setImage(Icon::create('learnmodule', 'inactive'));

            return $navigation;
        } else {
            return null;
        }
    }

    function getTabNavigation($course_id) {
        $ilias_interface_config = Config::get()->ILIAS_INTERFACE_BASIC_SETTINGS;
        if (count($ilias_interface_config)) {
            // load class
            require_once("lib/ilias_interface/IliasObjectConnections.class.php");
            
            $moduletitle = Config::get()->getValue('ILIAS_INTERFACE_MODULETITLE');
            if ($ilias_interface_config['edit_moduletitle']) {
                $moduletitle = CourseConfig::get($course_id)->getValue('ILIAS_INTERFACE_MODULETITLE');
            }

            $navigation = new Navigation($moduletitle);
            $navigation->setImage(Icon::create('learnmodule', 'info_alt'));
            $navigation->setActiveImage(Icon::create('learnmodule', 'info'));
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id) || ($GLOBALS['perm']->have_studip_perm('autor', $course_id) && IliasObjectConnections::isCourseConnected($course_id))) {
                if (get_object_type($course_id, ['inst'])) {
                    $navigation->addSubNavigation('view', new Navigation(_('Lernobjekte dieser Einrichtung'), 'dispatch.php/course/ilias_interface/index/' . $course_id));
                } else {
                    $navigation->addSubNavigation('view', new Navigation(_('Lernobjekte dieser Veranstaltung'), 'dispatch.php/course/ilias_interface/index/' . $course_id));
                }
            }
            
            return array('ilias_interface' => $navigation);
        } else {
            return null;
        }
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata()
    {
        return array(
            'summary' => _('Zugang zu extern erstellten ILIAS-Lernobjekten'),
            'description' => _('Über diese Schnittstelle ist es möglich, Lernobjekte aus '.
                                'einer ILIAS-Installation (ILIAS-Version >= 5.3.8) in Stud.IP zur Verfügung '.
                                'zu stellen. Lehrende haben die Möglichkeit, in '.
                                'ILIAS Selbstlerneinheiten zu erstellen und in Stud.IP bereit zu stellen.'),
            'displayname' => _('ILIAS-Schnittstelle'),
            'category' => _('Inhalte und Aufgabenstellungen'),
            'keywords' => _('Einbindung von ILIAS-Lernobjekten;
                            Zugang zu ILIAS;
                            Aufgaben- und Test-Erstellung'),
            'icon' => Icon::create('learnmodule', 'info'),
            'descriptionshort' => _('Zugang zu extern erstellten ILIAS-Lernobjekten'),
            'descriptionlong' => _('Über diese Schnittstelle ist es möglich, Lernobjekte aus '.
                            'einer ILIAS-Installation (> 5.1.8) in Stud.IP zur Verfügung '.
                            'zu stellen. Lehrende haben die Möglichkeit, in '.
                            'ILIAS Selbstlerneinheiten zu erstellen und in Stud.IP bereit zu stellen.'),
        );
    }
}
