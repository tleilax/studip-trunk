<?php
/*
 * ActivityFeed.php - A portal plugin for activities
 *
 * Copyright (C) 2014 - André Klaßen <klassen@elan-ev.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class ActivityFeed extends StudIPPlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Meine Aktivitäten');
    }

    public function getPortalTemplate()
    {
        //PageLayout::addScript($this->getPluginUrl() . '/js/ActivityFeed.js');
        PageLayout::addStylesheet($this->getPluginURL(). '/css/style.css');


        $observer_id = $GLOBALS['user']->id;
        $contexts = array();
        $system_context = new \Studip\Activity\SystemContext();

        $contexts[] = $system_context;



        $semesters   = MyRealmModel::getSelectedSemesters('all');
        $min_sem_key = min($semesters);
        $max_sem_key = max($semesters);

        $courses = MyRealmModel::getCourses($min_sem_key, $max_sem_key);

        foreach ($courses as $course) {
            $contexts[] = new \Studip\Activity\CourseContext($course->seminar_id);

        }

        $institues = MyRealmModel::getMyInstitutes();
        foreach($institues as $institute){
            $contexts[] = new \Studip\Activity\InstituteContext($institute['institut_id']);
        }

        //TODO user_context (do we wanna add buddies as well?)
        $contexts[] = new \Studip\Activity\UserContext($GLOBALS['user']->id);




        // add filters
        $filter = new Studip\Activity\Filter();

        $filter->setMaxAge(time() - 14 * 86400); // set range of 2 weeks from today


        $stream = new \Studip\Activity\Stream($observer_id, $contexts, $filter);



        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('stream');
        $template->stream = $stream;

        /*
        $navigation = new Navigation('', '#');
        $navigation->setImage('icons/16/blue/edit.png', array(
                                  'title' => _('Konfigurieren'),
                                  'onclick' => "ActivityFeed.openDialog('". PluginEngine::getLink($this, array(), 'configuration') ."'); return false;"
                              ));

        $template->icons = array($navigation);
        */

        return $template;
    }

    /*
    private function getCourses(){

        $courses  = MyRealmModel::getPreparedCourses();


        return $courses;
    }*/
}
