<?php
/**
 * GlobalSearchModule for SemTree
 */
class PodiumSemTree extends GlobalSearchModule
{

    /**
     * Returns the id for this podium module. The search sql must also return this id as type
     *
     * @return String id for this module
     */
    public static function getId()
    {
        return 'semtree';
    }

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return  _('Studienbereiche');
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
        $query = DBManager::get()->quote("%$search%");
        $sql = "SELECT * FROM sem_tree WHERE name LIKE $query ORDER BY name DESC LIMIT ".Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE;
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
    public static function filter($semtree_id, $search)
    {
        $semtree = StudipStudyArea::buildExisting($semtree_id);
        return array(
            'id' => $semtree->id,
            'name' => self::mark($semtree->name, $search),
            'url' => URLHelper::getURL("dispatch.php/search/courses", array('start_item_id' => $semtree->id, 'level' => 'vv', 'cmd' => 'qs'))
        );
    }
}