<?php

/**
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class InstituteContext extends Context
{
    private
        $institute_id;

    /**
     * create new institute-context
     *
     * @param string $institute_id
     */
    function __construct($institute_id)
    {
        $this->institute_id = $institute_id;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        if (!$this->provider) {
            $institute = \Institute::find($this->institute_id);

            // todo check which modules are active globally (participantprovider isn't suitable here?)
            $module_names = array('forum', 'documents', 'wiki', 'literature');

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
                        $this->provider[$plugin->getPluginName()] = $plugin;
                    }
                }
            }
        }

        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    function getRangeId()
    {
        return $this->institute_id;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContextType()
    {
        return 'institute';
    }
}
