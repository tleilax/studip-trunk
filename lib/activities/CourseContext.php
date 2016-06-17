<?php

/**
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class CourseContext extends Context
{
    private
        $seminar_id;

    /**
     * create new course-context
     *
     * @param string $seminar_id
     */
    function __construct($seminar_id)
    {
        $this->seminar_id = $seminar_id;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        if (!$this->provider) {
            $course = \Course::find($this->seminar_id);

            // todo check which modules are active globally
            $module_names = array('forum', 'participants', 'documents', 'wiki', 'schedule', 'literature');

            // get list of possible providers by checking the activated plugins
            // and modules for the current seminar
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
                    # todo check if module is active or not
                    if ($modules->checkLocal($name, $this->seminar_id)) {
                        $this->addProvider($name);
                    }
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
    public function getRangeId()
    {
        return $this->seminar_id;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContextType()
    {
        return 'course';
    }
}
