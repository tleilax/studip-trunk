<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

namespace Studip\Activity;

require_once 'lib/activities/Context.php';

class CourseContext implements Context
{
    private
        $seminar_id,
        $provider;

    function __construct($seminar_id)
    {
        $this->seminar_id = $seminar_id;
    }

    private function addProvider($provider)
    {

        $class_name = 'Studip\Activity\\' . ucfirst($provider) . 'Provider';

        $reflectionClass = new \ReflectionClass($class_name);
        $this->provider[] =  $reflectionClass->newInstanceArgs();

    }

    private function getProviders()
    {
        if (!$this->provider) {


            $course = \Course::find($this->seminar_id);

            // todo check which modules are active globally
            $module_names = array('forum', 'participants', 'documents', 'literature', 'wiki', 'blubber');

            // get list of possible providers by checking the activated plugins and modules for the current seminar
            $modules = new \Modules();
            $activated_modules = $modules->getLocalModules($this->seminar_id, 'sem', false, $course->status);


            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$course->status]['class']];
            if (!$sem_class) {
                $sem_class = \SemClass::getDefaultSemClass();
            }

            // check modules
            foreach ($module_names as $name) {
                if (($activated_modules[$name] || $sem_class->isSlotMandatory($name))
                        && $sem_class->isModuleAllowed($sem_class->getSlotModule($name))) {
                    $this->addProvider($name);
                }
            }

            //news
            $this->addProvider('news');

            // add blubber-provider
            $this->addProvider('blubber');

            //plugins
            $standard_plugins = \PluginManager::getInstance()->getPlugins("StandardPlugin", $this->seminar_id);
            foreach ($standard_plugins as $plugin) {
                if (!$sem_class->isSlotModule(get_class($plugin))) {
                    if ($plugin instanceof \Studip\ActivityProvider) {
                        $this->provider[] = $plugin;
                    }
                }
            }
        }

        return $this->provider;
    }

    private function filterProviders($providers, Filter $filter){

        $filtered_providers = array();
        if(is_null($filter->getType())) {
            $filtered_providers = $providers;
        } else {
            foreach($providers as $provider) {
                $filtered_class = 'Studip\Activity\\' . ucfirst($filter->getType()) . 'Provider';
                if($provider instanceof $filtered_class) {
                    $filtered_providers[] =  $provider;
                }
            }
        }

        return $filtered_providers;

    }

    function getSeminarId()
    {
        return $this->seminar_id;
    }

    function getActivities($observer_id, Filter $filter)
    {
        $providers = $this->filterProviders($this->getProviders(), $filter);

        $activities = array_map(
            function ($provider) use($observer_id, $filter) {
                return $provider->getActivities($observer_id, $this, $filter);
            },
            $providers);

        return array_flatten($activities);
    }

}
