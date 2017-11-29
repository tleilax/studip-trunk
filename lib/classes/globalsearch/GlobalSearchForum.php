<?php
/**
 * GlobalSearchModule for forum entries
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchForum extends GlobalSearchModule implements GlobalSearchFulltext
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Forenbeiträge');
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param $search the input query string
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search)
    {
        $search = str_replace(" ", "% ", $search);
        $query = DBManager::get()->quote("%$search%");

        // visibility
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $seminaruser = " AND EXISTS (
                SELECT 1 FROM `seminar_user`
                WHERE `forum_entries`.`seminar_id` = `seminar_user`.`seminar_id`
                    AND `seminar_user`.`user_id` = ".DBManager::get()->quote($GLOBALS['user']->id).") ";
        }

        // anonymous postings
        if (!$GLOBALS['perm']->have_perm('root') && Config::get()->FORUM_ANONYMOUS_POSTINGS) {
            $anonymous = "`anonymous` = 0 AND";
        } else {
            $anonymous = "";
        }

        $sql = "SELECT `forum_entries`.* FROM `forum_entries`
            WHERE $anonymous (
                    `name` LIKE $query
                    OR `content` LIKE $query
                ) $seminaruser
            ORDER BY `chdate` DESC
            LIMIT " . Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE;
        return $sql;
    }

    /**
     * Returns an array of information for the found element. Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Avatar for the
     *
     * @param $id
     * @param $search
     * @return mixed
     */
    public static function filter($data, $search)
    {
        $user = User::find($data['user_id']);
        $course = Course::find($data['seminar_id']);
        $result = array(
            'id' => $data['topic_id'],
            'name' => $data['name'] ? self::mark($data['name'], $search)  : ($course ? htmlReady($course->getFullname()) : _('Ohne Titel')),
            'url' => URLHelper::getURL("plugins.php/coreforum/index/index/" . $data['topic_id']."#".$data['topic_id'], array('cid' => $data['seminar_id'])),
            'date' => strftime('%x %X', $data['chdate']),
            'description' => self::mark($data['content'], $search, true),
            'additional' => htmlReady((($user && !$data['anonymous']) ? $user->getFullname() : _('Anonym'))." "._('in')." ".($course ? $course->getFullname() : '')),
            'expand' => URLHelper::getURL("plugins.php/coreforum/index/search", array(
                'cid' => $data['seminar_id'],
                'backend' => 'search',
                'searchfor' => $search,
                'search_title' => 1,
                'search_content' => 1,
                'search_author' => 1
            ))
        );
        return $result;
    }

    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE `forum_entries` ADD FULLTEXT INDEX globalsearch (`name`, `content`)");
    }

    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX globalsearch ON `forum_entries`");
    }

    public static function getFulltextSearch($search)
    {
        $search = str_replace(" ", "% ", $search);
        $query = DBManager::get()->quote(preg_replace("/(\w+)[*]*\s?/", "+$1* ", $search));
        $words = substr(preg_replace("/\W*(\w+)\W*/", "$1|", $search), 0, -1);
        $quoteRegex = '`content` REGEXP "[[]quote=.*['.$words.'].*[]]|[<]admin_msg autor=.*[.'.$words.'.].*[>]" ASC, ';

        // visibility
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $seminaruser = " AND EXISTS (
                SELECT 1 FROM `seminar_user`
                WHERE `forum_entries`.`seminar_id` = `seminar_user`.`seminar_id`
                    AND `seminar_user`.`user_id` = ".DBManager::get()->quote($GLOBALS['user']->id).") ";
        }

        // anonymous postings
        if (!$GLOBALS['perm']->have_perm('root') && Config::get()->FORUM_ANONYMOUS_POSTINGS) {
            $anonymous = "`anonymous` = 0 AND";
        } else {
            $anonymous = "";
        }

        $sql = "SELECT `forum_entries`.* FROM `forum_entries`
            WHERE $anonymous MATCH(`name`, `content`) AGAINST($query IN BOOLEAN MODE)
                $seminaruser
            ORDER BY $quoteRegex `chdate` DESC LIMIT " . Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE;
        return $sql;
    }
}
