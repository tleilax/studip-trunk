<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
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
        $class_name = ucfirst($provider) . 'Provider';

        $this->provider[] = new $class_name();
    }

    private function getProviders()
    {
        if (!$this->provider) {
            $course = \Course::find($this->seminar_id);

            $module_names = array('forum', 'participants', 'documents', 'literature', 'wiki');

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

    function getSeminarId()
    {
        return $this->seminar_id;
    }

    function getActivities($observer_id, Filter $filter)
    {
        $providers = $this->getProviders();
        $activities = array_map(
            function ($provider) {
                return $provider->getActivities($observer_id, $this, $filter);
            },
            $providers);

        return array_flatten($activities);
    }

}
