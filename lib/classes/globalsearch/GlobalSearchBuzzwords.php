<?php
/**
 * GlobalSearchModule for buzzwords: words that trigger some manual info,
 * e.g. links to Campus systems etc.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchBuzzwords extends SimpleORMap
{
    protected static function configure($config = array()) {
        $config['db_table'] = 'globalsearch_buzzwords';
        $config['additional_fields']['rightsname'] = true;
        parent::configure($config);
    }

    public function getRightsname() {
        return array_search($this->rights, $GLOBALS['perm']->permissions);
    }

    public static function getName() {
        return _('Stichwörter');
    }

    public static function getSQL($search) {
        if (!$search) {
            return null;
        }

        $query = DBManager::get()->quote("%$search%");
        $rights = $GLOBALS['perm']->permissions[$GLOBALS['perm']->get_perm()];
        return "SELECT * FROM `globalsearch_buzzwords` WHERE `buzzwords` LIKE $query AND $rights >= rights";
    }

    public static function filter($buzz, $search)
    {
        return array(
            'name' => htmlReady($buzz['name']),
            'url' => $buzz['url'],
            'additional' => $buzz['subtitle']
        );
    }
}