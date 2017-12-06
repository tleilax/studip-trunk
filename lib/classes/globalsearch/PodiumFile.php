<?php
/**
 * GlobalSearchModule for files
 */
class GlobalSearchFile extends GlobalSearchModule implements GlobalSearchFulltext
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Dateien');
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
        // Filter for own courses
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $user = DBManager::get()->quote($GLOBALS['user']->id);
            $ownseminars = "JOIN seminar_user ON (dokumente.seminar_id = seminar_user.seminar_id AND seminar_user.user_id = $user) ";
        }

        // Now check if we got a seminar
        if (strpos($search, '/') !== FALSE) {
            $args = explode('/', $search);
            $prequery = DBManager::get()->quote("%" . trim($args[0]) . "%");
            $query = DBManager::get()->quote("%" . trim($args[1]) . "%");
            $binary = DBManager::get()->quote('%' . join('%', str_split(strtoupper(trim($args[0])))) . '%');
            $comp = "AND";
            return "SELECT dokumente.* FROM dokumente "
            . "JOIN seminare USING (seminar_id) $ownseminars "
            . "WHERE (seminare.name LIKE BINARY $binary OR seminare.name LIKE $prequery ) "
            . "$comp dokumente.name LIKE $query "
            . "ORDER BY dokumente.chdate DESC LIMIT ".(2*Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE * 2);
        } else {
            $query = DBManager::get()->quote("%$search%");
            return "SELECT `file_refs`.* FROM `file_refs` IGNORE INDEX (chdate) "
            . " $ownseminars "
            . "WHERE dokumente.name LIKE $query "
            . "ORDER BY dokumente.chdate DESC LIMIT ".(Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE * 2);
        }
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
    public static function filter($file_id, $search)
    {
        $file = StudipDocument::buildExisting($file_id);
        if ($file->checkAccess($GLOBALS['user']->id)) {
            return array(
                'id' => $file->id,
                'name' => self::mark($file->name, $search),
                'url' => URLHelper::getURL("sendfile.php?type=0&file_id={$file->id}&file_name={$file->filename}"),
                'additional' => self::mark($file->course ? $file->course->getFullname() : '', $search, false),
                'date' => strftime('%x', $file->chdate),
                'expand' => URLHelper::getURL("folder.php", array("cid" => $file->seminar_id, "cmd" => "tree"))
            );
        }
    }

    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE dokumente ADD FULLTEXT INDEX podium (name)");
    }

    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX podium ON dokumente");
    }

    public static function getFulltextSearch($search)
    {
        // Filter for own courses
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $user = DBManager::get()->quote($GLOBALS['user']->id);
            $ownseminars = "JOIN seminar_user ON (dokumente.seminar_id = seminar_user.seminar_id AND seminar_user.user_id = $user) ";
        }

        // Now check if we got a seminar
        if (strpos($search, '/') !== FALSE) {
            $args = explode('/', $search);
            $prequery = DBManager::get()->quote("%" . trim($args[0]) . "%");
            $query = DBManager::get()->quote("%" . trim($args[1]) . "%");
            $binary = DBManager::get()->quote('%' . join('%', str_split(strtoupper(trim($args[0])))) . '%');
            $comp = "AND";
            return "SELECT dokumente.* FROM dokumente "
            . "JOIN seminare USING (seminar_id) $ownseminars "
            . "WHERE (seminare.name LIKE BINARY $binary OR seminare.name LIKE $prequery ) "
            . "$comp dokumente.name LIKE $query "
            . "ORDER BY dokumente.chdate DESC LIMIT ".(2*Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE * 2);
        } else {
            $query = DBManager::get()->quote(preg_replace("/(\w+)[*]*\s?/", "+$1* ", $search));
            return "SELECT dokumente.* FROM dokumente IGNORE INDEX (chdate) "
            . " $ownseminars "
            . "WHERE MATCH(dokumente.name) AGAINST($query IN BOOLEAN MODE) "
            . "ORDER BY dokumente.chdate DESC LIMIT ".(Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE * 2);
        }
    }
}