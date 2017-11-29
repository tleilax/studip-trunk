<?php
/**
 * GlobalSearchModule for courses
 */
class PodiumNews extends GlobalSearchModule
{

    /**
     * Returns the id for this podium module. The search sql must also return this id as type
     *
     * @return String id for this module
     */
    public static function getId()
    {
        return 'news';
    }

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Ank�ndiungen');
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
            $visibility = "courses.visible = 1 AND ";
            $seminaruser = " AND NOT EXISTS (SELECT 1 FROM seminar_user WHERE seminar_id = courses.Seminar_id AND user_id = ".DBManager::get()->quote($GLOBALS['user']->id).") ";
        }

        $sql = "SELECT courses.Seminar_id,courses.start_time,courses.name,courses.veranstaltungsnummer,courses.status FROM seminare courses JOIN sem_types ON (courses.status = sem_types.id) WHERE $visibility(courses.Name LIKE $query OR courses.VeranstaltungsNummer LIKE $query OR CONCAT_WS(' ', sem_types.name,courses.Name) LIKE $query) $seminaruser ORDER BY ABS(start_time - unix_timestamp()) ASC LIMIT ".Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE;
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
            'expand' => URLHelper::getURL("dispatch.php/search/courses", array(
                'reset_all' => 1,
                'search_sem_qs_choose' => 'title_lecturer_number',
                'search_sem_sem' => 'all',
                'search_sem_quick_search_parameter' => $search,
                'search_sem_1508068a50572e5faff81c27f7b3a72f' => 1 // Fuck you Stud.IP
            ))
        );
        $avatar = CourseAvatar::getAvatar($course->id);
        if ($avatar->is_customized()) {
            $result['img'] = $avatar->getUrl(AVATAR::MEDIUM);
        }
        return $result;
    }
}