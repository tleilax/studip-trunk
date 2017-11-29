<?php
/**
 * globalsearch.php - controller to perform global search operations and provide settings.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */

class GlobalSearchController extends AuthenticatedController
{

    /**
     * Perform search in all registered modules for the given search term.
     */
    public function search_action($searchterm)
    {
    }

    public function settings_action()
    {
        PageLayout::setTitle(_('Globale Suche: Einstellungen'));
        Navigation::activateItem('/admin/config/globalsearch');

        $this->config = Config::get()->GLOBALSEARCH_MODULES;

        $this->modules = [];

        foreach ($this->config as $className => $data) {
            $c = new $className();
        }

        // Search declared classes for GlobalSearchModules
        foreach (get_declared_classes() as $className) {
            if (is_a($className, 'GlobalSearchModule', true)
                    && $className !== 'GlobalSearchModule') {
                $class = new $className();

                // Add new classes at module array end and not activated.
                if (in_array($className, array_keys($this->config))) {
                    $active = $this->config[$className]['active'];
                    $order = $this->config[$className]['order'];
                } else {
                    $active = false;
                    $order = 100;
                }
                $this->modules[$order] = $class;
            }
        }

        ksort($this->modules);
    }

    public function saveconfig_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $config = [];

        $order = 1;
        foreach (Request::getArray('modules') as $module) {
            $config[$module['class']] = [
                'order' => $order,
                'active' => $module['active'] ? true : false,
                'fulltext' => is_a($module, 'GlobalSearchFulltext') && $module['fulltext'] ? true : false
            ];
            $order++;
        }

        $success = true;
        if (Request::int('async_queries', 0) != Config::get()->GLOBALSEARCH_ASYNC_QUERIES) {
            $success = Config::get()->store('GLOBALSEARCH_ASYNC_QUERIES',
                ['value' => Request::int('async_queries', 0)]);
            Config::get()->GLOBALSEARCH_ASYNC_QUERIES = Request::int('async_queries', 0);
        }

        if (Request::int('entries_per_type', 3) != Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE) {
            $success = Config::get()->store('GLOBALSEARCH_MAX_RESULT_OF_TYPE',
                ['value' => Request::int('entries_per_type', 3)]);
            Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE = Request::int('entries_per_type', 3);
        }

        if ($config != Config::get()->GLOBALSEARCH_MODULES) {
            $success = Config::get()->store('GLOBALSEARCH_MODULES', ['value' => $config]);
        }

        if ($success) {
            PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
        } else {
            PageLayout::postError(_('Die Einstellungen konnten nicht gespeichert werden.'));
        }

        $this->relocate('globalsearch/settings');
    }

}
