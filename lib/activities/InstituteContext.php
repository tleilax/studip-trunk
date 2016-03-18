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

class InstituteContext extends Context
{
    private
        $institute_id;

    function __construct($institute_id)
    {
        $this->institute_id = $institute_id;
    }

    protected function getProvider()
    {
        if (!$this->provider) {
            $institute = \Institute::find($this->institute_id);

            // todo check which modules are active globally
            $module_names = array('forum', 'participants', 'documents', 'literature', 'wiki');

            // get list of possible providers by checking the activated plugins and modules for the current institute
            $modules = new \Modules();
            $activated_modules = $modules->getLocalModules($institute->institut_id, 'inst', $institute->modules, $institute->type ? : 1);


            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][1]['class']];
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
            $standard_plugins = \PluginManager::getInstance()->getPlugins("StandardPlugin", $this->institute_id);
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

    function getRangeId()
    {
        return $this->institute_id;
    }
}
