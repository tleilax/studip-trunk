<?php
/**
 * GlobalSearchModule for resources
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchResources extends GlobalSearchModule
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Ressourcen');
    }

    public static function getSQL($search)
    {
        if (!$search || !$GLOBALS['perm']->have_perm('admin')) {
            return null;
        }
        $query = DBManager::get()->quote("%$search%");
        return "SELECT `resource_id`, `name`, `description`
            FROM `resources_objects`
            WHERE `name` LIKE $query
                OR `description` LIKE $query
                OR REPLACE(`name`, ' ', '') LIKE $query
                OR REPLACE(`description`, ' ', '') LIKE $query";
    }

    public static function filter($res, $search)
    {
        return array(
            'name' => self::mark($res['name'], $search),
            'url' => URLHelper::getURL("resources.php",
                array('view' => 'view_schedule', 'show_object' => $res['resource_id'])),
            'additional' => self::mark($res['description'], $search),
            'expand' => URLHelper::getURL('resources.php',
                array('view' => 'search', 'search_exp' => $search, 'start_search' => ''))
        );
    }
}