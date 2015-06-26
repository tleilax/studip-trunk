<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once 'lib/modules/StudipModule.class.php';

class CoreOverview implements StudipModule {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        return null;
    }

    function getTabNavigation($course_id) {
        $statement = DBManager::get()->prepare("SELECT status FROM seminare WHERE Seminar_id = :seminar_id");
        $statement->execute(array('seminar_id' => $course_id));
        $sem_type = $statement->fetch(PDO::FETCH_COLUMN, 0);
        if ($sem_type) {
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$sem_type]['class']];
        }
        $object_type = ($sem_type === false ? "inst" : "sem");

        $sem_class || $sem_class = SemClass::getDefaultSemClass();
        $studygroup_mode = $sem_class['studygroup_mode'];

        $result = DBManager::get()->query("SELECT admission_binding FROM seminare WHERE seminar_id = ".DBManager::get()->quote($_SESSION['SessionSeminar'])."");
        $admission_binding = $result->fetchColumn();

        $navigation = new Navigation(_('�bersicht'));
        $navigation->setImage('icons/16/white/seminar.png');
        $navigation->setActiveImage('icons/16/black/seminar.png');
        if ($object_type === 'inst') {
            $navigation->addSubNavigation('info', new Navigation(_('Kurzinfo'), 'dispatch.php/institute/overview'));
            $navigation->addSubNavigation('courses', new Navigation(_('Veranstaltungen'), 'show_bereich.php?level=s&id='.$course_id));
            $navigation->addSubNavigation('schedule', new Navigation(_('Veranstaltungs-Stundenplan'), 'dispatch.php/calendar/instschedule?cid='.$course_id));

            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id) && $GLOBALS['perm']->have_perm('admin')) {
                $navigation->addSubNavigation('admin', new Navigation(_('Administration der Einrichtung'), 'dispatch.php/institute/basicdata/index?new_inst=TRUE'));
            }
        } else {
            $navigation->addSubNavigation('info', new Navigation(_('Kurzinfo'), 'dispatch.php/course/overview'));
            if (!$studygroup_mode) {
                $navigation->addSubNavigation('details', new Navigation(_('Details'), 'dispatch.php/course/details/'));
            }

            if (!$admission_binding && $GLOBALS['perm']->get_studip_perm($_SESSION['SessionSeminar']) && !$GLOBALS['perm']->have_studip_perm('tutor', $_SESSION['SessionSeminar']) ) {
                $navigation->addSubNavigation('leave', new Navigation(_('Austragen aus der Veranstaltung'), 'dispatch.php/my_courses/decline/'.$course_id.'?cmd=suppose_to_kill'));
            }
        }
        return array('main' => $navigation);
    }

    function getNotificationObjects($course_id, $since, $user_id)
    {
        return null;
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata() {
         return array();
     }
}
