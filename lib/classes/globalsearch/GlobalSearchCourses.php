<?php
/**
 * Global search module for courses
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchCourses extends GlobalSearchModule implements GlobalSearchFulltext
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Veranstaltungen');
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
        if (!$search) {
            return null;
        }
        $search = str_replace(" ", "% ", $search);
        $query = DBManager::get()->quote("%$search%");

        // visibility
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $visibility = "`courses`.`visible` = 1 AND ";
            $seminaruser = " AND NOT EXISTS (
                SELECT 1 FROM `seminar_user`
                WHERE `seminar_id` = `courses`.`Seminar_id`
                    AND `user_id` = ".DBManager::get()->quote($GLOBALS['user']->id).") ";
        }

        $sql = "SELECT courses.`Seminar_id`, courses.`start_time`, courses.`Name`,
                courses.`VeranstaltungsNummer`, courses.`status`
            FROM `seminare` AS courses
                JOIN `sem_types` ON (courses.`status` = `sem_types`.`id`)
            WHERE $visibility
                (courses.`Name` LIKE $query
                    OR courses.`VeranstaltungsNummer` LIKE $query
                    OR CONCAT_WS(' ', `sem_types`.`name`, courses.`Name`, `sem_types`.`name`) LIKE $query
                )
                $seminaruser
            ORDER BY ABS(start_time - unix_timestamp()) ASC
            LIMIT ".Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE;
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
        $course = Course::buildExisting($data);
        $result = array(
            'id' => $course->id,
            'name' => self::mark($course->getFullname(), $search),
            'url' => URLHelper::getURL("dispatch.php/course/details/index/" . $course->id),
            'date' => $course->start_semester->name,
            'additional' => implode(', ', array_map(function ($u) {
                    return $u->getUserFullname();
                }, $course->getMembersWithStatus('dozent'))),
            'expand' => URLHelper::getURL("dispatch.php/search/courses", array(
                'reset_all' => 1,
                'search_sem_qs_choose' => 'title_lecturer_number',
                'search_sem_sem' => 'all',
                'search_sem_quick_search_parameter' => $search,
                'search_sem_1508068a50572e5faff81c27f7b3a72f' => 1 // Fuck you Stud.IP
            ))
        );
        $avatar = CourseAvatar::getAvatar($course->id);
        $result['img'] = $avatar->getUrl(Avatar::MEDIUM);
        return $result;
    }

    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE `seminare` ADD FULLTEXT INDEX globalsearch (`VeranstaltungsNummer`, `Name`)");
        DBManager::get()->exec("ALTER TABLE `sem_types` ADD FULLTEXT INDEX globalsearch (`Name`)");
    }

    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX globalsearch ON `seminare`");
        DBManager::get()->exec("DROP INDEX globalsearch ON `sem_types`");
    }

    public static function getFulltextSearch($search)
    {
        if (!$search) {
            return null;
        }

        $query = DBManager::get()->quote(preg_replace("/(\w+)[*]*\s?/", "+$1* ", $search));

        // visibility
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $visibility = "courses.visible = 1 AND ";
            $seminaruser = " AND NOT EXISTS (
                SELECT 1 FROM seminar_user
                WHERE seminar_id = courses.Seminar_id
                    AND user_id = ".DBManager::get()->quote($GLOBALS['user']->id).") ";
        }

        $semtype = DBManager::get()->query(
            "SELECT `id`, `name` FROM `sem_types` WHERE MATCH (`name`) AGAINST ($query IN BOOLEAN MODE)");
        while ($type = $semtype->fetch(PDO::FETCH_ASSOC)) {

            $semtypes[] = $type['id'];
            // Get up some order criteria with the semtypes
            // Remove semtypes form query
                $replace = "/".$type['name'][0].chunk_split(substr($type['name'], 1), 1, '?')."\*\s?/i";
                $query = preg_replace($replace, "", $query);
        }

        if (isset($semtypes)) {
            $semstatus = "`status` IN (".join(",",$semtypes) .") DESC, ";
        }

        $sql = "SELECT courses.`Seminar_id`, courses.`start_time`, courses.`Name`,
                courses.`VeranstaltungsNummer`, courses.`status`
            FROM `seminare` AS courses
            WHERE MATCH(`VeranstaltungsNummer`, `Name`) AGAINST ($query IN BOOLEAN MODE)
            ORDER BY $semstatus ABS(`start_time` - UNIX_TIMESTAMP()) ASC,
                MATCH(`VeranstaltungsNummer`, `Name`) AGAINST ($query IN BOOLEAN MODE) DESC
            LIMIT ".Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE;
        return $sql;
    }
}